<?php

namespace App\Services;

use App\Models\EC;
use App\Models\UE;
use Carbon\Carbon;
use App\Models\Copie;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Etudiant;
use App\Models\Manchette;
use App\Models\SessionExam;
use App\Models\CodeAnonymat;
use App\Models\ResultatFinal;
use App\Models\PresenceExamen;
use App\Models\ResultatFusion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ResultatFinalHistorique;
use App\Services\CalculAcademiqueService;

class FusionService
{
    /**
     * Vérifie la cohérence des données avant la fusion
     *
     * @param int $examenId
     * @return array
     */
    public function verifierCoherence($examenId)
    {
        try {
            $examen = Examen::with(['niveau', 'parcours'])->findOrFail($examenId);

            // Valider les données
            $validation = $this->validateData($examenId);
            if (!$validation['valid']) {
                Log::warning('Vérification de cohérence échouée : données invalides', [
                    'examen_id' => $examenId,
                    'issues' => $validation['issues'],
                ]);
                return [
                    'success' => false,
                    'message' => 'Données invalides : ' . implode(', ', $validation['issues']),
                    'stats' => ['total' => 0, 'complets' => 0, 'incomplets' => 0],
                    'data' => [],
                ];
            }

            $sessionActive = SessionExam::where('is_active', true)
                ->where('is_current', true)
                ->first();

            if (!$sessionActive) {
                return [
                    'success' => false,
                    'message' => 'Aucune session active trouvée.',
                    'stats' => ['total' => 0, 'complets' => 0, 'incomplets' => 0],
                    'data' => [],
                ];
            }

            $etudiants = Etudiant::where('niveau_id', $examen->niveau_id)
                ->where('parcours_id', $examen->parcours_id)
                ->where('is_active', true)
                ->get();

            $totalEtudiants = $etudiants->count();

            // CORRECTION : Vérifier d'abord s'il y a des résultats fusion pour cette session
            $resultatsExistants = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->exists();

            $rapport = [];
            $stats = ['total' => 0, 'complets' => 0, 'incomplets' => 0];

            if ($resultatsExistants) {
                // S'il y a des résultats fusion, les analyser
                $rapport = $this->analyserResultatsExistants($examenId, $totalEtudiants);
            } else {
                // Sinon, analyser les données brutes (manchettes + copies)
                $rapport = $this->analyserDonneesPrefusion($examenId, $totalEtudiants, $etudiants);
            }

            foreach ($rapport as $item) {
                $stats[$item['complet'] ? 'complets' : 'incomplets']++;
            }
            $stats['total'] = count($rapport);

            Log::info('Vérification de cohérence terminée', [
                'examen_id' => $examenId,
                'session_id' => $sessionActive->id,
                'session_type' => $sessionActive->type,
                'resultats_existants' => $resultatsExistants,
                'stats' => $stats,
                'total_etudiants' => $totalEtudiants,
            ]);

            return [
                'success' => true,
                'stats' => $stats,
                'data' => $rapport,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de cohérence', [
                'examen_id' => $examenId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification : ' . $e->getMessage(),
                'stats' => ['total' => 0, 'complets' => 0, 'incomplets' => 0],
                'data' => [],
            ];
        }
    }

    /**
     * Analyse les résultats existants dans resultats_fusion
     */
    private function analyserResultatsExistants($examenId, $totalEtudiants)
    {
        $sessionActive = SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->first();

        if (!$sessionActive) {
            \Log::warning('Aucune session active trouvée pour l\'analyse des résultats existants', [
                'examen_id' => $examenId,
            ]);
            return [];
        }

        // CORRECTION : Filtrer explicitement par session
        $resultats = ResultatFusion::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->select('id', 'etudiant_id', 'ec_id', 'note', 'statut')
            ->with(['etudiant', 'ec'])
            ->get();

        $rapport = [];

        foreach ($resultats->groupBy('ec_id') as $ecId => $resultatsEc) {
            $ec = $resultatsEc->first()->ec;
            if (!$ec) {
                \Log::warning('EC introuvable pour résultats existants', [
                    'examen_id' => $examenId,
                    'ec_id' => $ecId,
                    'session_exam_id' => $sessionActive->id,
                ]);
                continue;
            }

            $etudiantsAvecNote = $resultatsEc->whereNotNull('note')->count();
            $etudiantsAvecResult = $resultatsEc->pluck('etudiant_id')->unique()->count();

            // ✅ CORRECTION PRINCIPALE : Utiliser les données de présence réelles
            $donneesPresence = $this->getPresenceDataForEC($examenId, $sessionActive, $ec->id);
            $expectedStudents = $donneesPresence['etudiants_presents'];
            
            // ✅ FALLBACK : Si pas de données de présence, utiliser la logique par type de session
            if ($expectedStudents === 0) {
                if ($sessionActive->type === 'Rattrapage') {
                    // Pour le rattrapage, calculer le nombre d'étudiants éligibles
                    $sessionNormale = SessionExam::where('annee_universitaire_id', $sessionActive->annee_universitaire_id)
                        ->where('type', 'Normale')
                        ->first();

                    if ($sessionNormale) {
                        $examen = Examen::find($examenId);
                        if ($examen) {
                            $expectedStudents = Etudiant::eligiblesRattrapage(
                                $examen->niveau_id,
                                $examen->parcours_id,
                                $sessionNormale->id
                            )->count();
                        }
                    }
                    
                    // Si toujours 0, utiliser le nombre d'étudiants avec résultats
                    if ($expectedStudents === 0) {
                        $expectedStudents = max($etudiantsAvecResult, $etudiantsAvecNote);
                    }
                } else {
                    // Session normale : utiliser totalEtudiants ou le nombre avec résultats
                    $expectedStudents = max($totalEtudiants, $etudiantsAvecResult, $etudiantsAvecNote);
                }
            }

            $complet = $expectedStudents === $etudiantsAvecNote && $expectedStudents > 0;

            $rapport[] = [
                'ec_id' => $ecId,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                'total_etudiants' => $expectedStudents,
                
                // ✅ CORRECTION : Utiliser les vraies données de présence
                'etudiants_presents' => $expectedStudents,
                'etudiants_attendus_theorique' => $donneesPresence['total_inscrits'],
                'source_presence' => $donneesPresence['source'],
                
                'etudiants_avec_note' => $etudiantsAvecNote,
                'manchettes_count' => $etudiantsAvecResult,
                'copies_count' => $etudiantsAvecNote,
                'codes_count' => $etudiantsAvecResult,
                'complet' => $complet,
                'etudiants_sans_manchette' => max(0, $expectedStudents - $etudiantsAvecResult),
                'codes_sans_manchettes' => ['count' => 0, 'codes' => []],
                'codes_sans_copies' => ['count' => 0, 'codes' => []],
                'issues' => [],
                'session_type' => $sessionActive->type,
            ];
        }

        \Log::info('Analyse des résultats existants par session CORRIGÉE', [
            'examen_id' => $examenId,
            'session_id' => $sessionActive->id,
            'session_type' => $sessionActive->type,
            'nb_ecs_analysees' => count($rapport),
            'total_resultats' => $resultats->count(),
        ]);

        return $rapport;
    }

    /**
     * Analyse les données avant fusion
     */
    private function analyserDonneesPrefusion($examenId, $totalEtudiants, Collection $etudiants)
    {
        $sessionActive = SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->first();

        if (!$sessionActive) {
            \Log::warning('Aucune session active trouvée pour l\'analyse pré-fusion', [
                'examen_id' => $examenId,
            ]);
            return [];
        }

        // CORRECTION : Si session de rattrapage, utiliser logique spéciale
        if ($sessionActive->type === 'Rattrapage') {
            return $this->analyserDonneesPrefusionRattrapage($examenId, $totalEtudiants, $etudiants, $sessionActive);
        }

        // Logique normale existante pour session normale...
        $copies = Copie::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->whereNotNull('code_anonymat_id')
            ->with(['ec', 'codeAnonymat'])
            ->get();

        // Reste de la logique normale existante...
        $ecsDepuisCopies = $copies->pluck('ec_id')->unique();
        $ecsDepuisExamen = EC::whereHas('examens', function ($query) use ($examenId) {
            $query->where('examens.id', $examenId);
        })->pluck('id');

        $ecIds = $ecsDepuisCopies->merge($ecsDepuisExamen)->unique();
        $ecs = EC::whereIn('id', $ecIds)->get();

        if ($ecs->isEmpty()) {
            \Log::warning('Aucune EC trouvée pour l\'analyse', [
                'examen_id' => $examenId,
                'session_id' => $sessionActive->id,
            ]);
            return [];
        }

        $rapport = [];

        foreach ($ecs as $ec) {
            $copiesEc = $copies->where('ec_id', $ec->id);
            $copiesAvecNote = $copiesEc->whereNotNull('note');

            // Logique normale existante...
            $codesEnCopies = $copiesEc->filter(function ($copie) {
                return $copie->codeAnonymat && is_string($copie->codeAnonymat->code_complet) && !empty($copie->codeAnonymat->code_complet);
            })->map(function ($copie) {
                return $copie->codeAnonymat->code_complet;
            })->unique()->values();

            $manchettes = Manchette::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->whereNotNull('code_anonymat_id')
                ->whereHas('codeAnonymat', function ($query) use ($ec) {
                    $query->where('ec_id', $ec->id)
                        ->whereNotNull('code_complet')
                        ->where('code_complet', '!=', '');
                })
                ->with(['etudiant', 'codeAnonymat'])
                ->get();

            $manchettesAvecCodes = $manchettes->filter(function ($manchette) {
                return $manchette->codeAnonymat && is_string($manchette->codeAnonymat->code_complet) && !empty($manchette->codeAnonymat->code_complet);
            });

            $codesEnManchettes = $manchettesAvecCodes->map(function ($manchette) {
                return $manchette->codeAnonymat->code_complet;
            })->unique()->values();

            $manchettesCorrespondantes = $manchettesAvecCodes->filter(function ($manchette) use ($codesEnCopies) {
                return $codesEnCopies->contains($manchette->codeAnonymat->code_complet);
            });

            $codesSansManchettes = collect($codesEnCopies)->diff(collect($codesEnManchettes));
            $codesSansCopies = collect($codesEnManchettes)->diff(collect($codesEnCopies));

            $etudiantsAvecManchette = $manchettesCorrespondantes->pluck('etudiant_id')->unique();

            $donneesPresence = $this->getPresenceDataForEC($examenId, $sessionActive, $ec->id);
            $etudiantsAttendus = $donneesPresence['etudiants_presents'];
            $etudiantsSansManchette = max(0, $etudiantsAttendus - $etudiantsAvecManchette->count());

            $complet = ($etudiantsAttendus > 0) &&
                ($copiesEc->count() === $etudiantsAttendus) &&
                ($copiesEc->count() === $copiesAvecNote->count()) &&
                ($copiesEc->count() === $manchettesCorrespondantes->count()) &&
                ($codesSansManchettes->isEmpty()) &&
                ($codesSansCopies->isEmpty());

            $issues = [];
            if ($manchettesAvecCodes->isEmpty() && $copiesEc->isNotEmpty()) {
                $issues[] = "Copies présentes mais aucune manchette valide pour EC {$ec->nom}";
            }
            if ($codesEnCopies->isEmpty() && $copiesEc->count() > 0) {
                $issues[] = "Les copies n'ont pas de codes d'anonymat valides pour EC {$ec->nom}";
            }
            if ($etudiantsSansManchette > 0 && $sessionActive->type === 'Normale') {
                $issues[] = "$etudiantsSansManchette étudiant(s) sans manchette pour EC {$ec->nom}";
            }
            if ($copiesEc->count() !== $copiesAvecNote->count()) {
                $issues[] = ($copiesEc->count() - $copiesAvecNote->count()) . " copie(s) sans note pour EC {$ec->nom}";
            }

            $rapport[] = [
                'ec_id' => $ec->id,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                'total_etudiants' => $etudiantsAttendus,
                'etudiants_presents' => $etudiantsAttendus,
                'etudiants_attendus_theorique' => $donneesPresence['total_inscrits'],
                'source_presence' => $donneesPresence['source'],
                'etudiants_avec_note' => $copiesAvecNote->count(),
                'manchettes_count' => $manchettesCorrespondantes->count(),
                'copies_count' => $copiesEc->count(),
                'codes_count' => $codesEnCopies->count(),
                'complet' => $complet,
                'etudiants_sans_manchette' => $etudiantsSansManchette,
                'codes_sans_manchettes' => [
                    'count' => $codesSansManchettes->count(),
                    'codes' => $codesSansManchettes->take(5)->toArray(),
                ],
                'codes_sans_copies' => [
                    'count' => $codesSansCopies->count(),
                    'codes' => $codesSansCopies->take(5)->toArray(),
                ],
                'issues' => $issues,
                'session_type' => $sessionActive->type,
            ];
        }

        return $rapport;
    }


    /**
     * NOUVELLE MÉTHODE : Analyse spéciale pour session de rattrapage
     */
    private function analyserDonneesPrefusionRattrapage($examenId, $totalEtudiants, Collection $etudiants, $sessionActive)
    {
        try {
            // 1. Trouver la session normale correspondante
            $sessionNormale = SessionExam::where('annee_universitaire_id', $sessionActive->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                \Log::warning('Session normale introuvable pour le rapport rattrapage', [
                    'examen_id' => $examenId,
                    'session_rattrapage_id' => $sessionActive->id,
                ]);
                return [];
            }

            // 2. Récupérer TOUTES les ECs de l'examen
            $ecs = EC::whereHas('examens', function ($query) use ($examenId) {
                $query->where('examens.id', $examenId);
            })->get();

            if ($ecs->isEmpty()) {
                return [];
            }

            // 3. Récupérer les résultats de session normale (NOTES VALIDÉES)
            $resultatsNormale = ResultatFinal::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionNormale->id)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->get()
                ->groupBy('ec_id');

            // 4. Récupérer copies de rattrapage RÉELLES
            $copiesRattrapage = Copie::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->whereNotNull('note')
                ->get()
                ->groupBy('ec_id');

            $rapport = [];

            foreach ($ecs as $ec) {
                // Notes de session normale pour cette EC
                $notesNormales = $resultatsNormale->get($ec->id, collect());
                $nbNotesNormales = $notesNormales->count();

                // Copies de rattrapage RÉELLES pour cette EC
                $copiesRattrapEc = $copiesRattrapage->get($ec->id, collect());
                $nbCopiesRattrapage = $copiesRattrapEc->count();

                // CORRECTION : Déterminer le type d'affichage
                if ($nbCopiesRattrapage > 0) {
                    // CAS 1: EC avec copies de rattrapage + récupération normale
                    $rapport[] = [
                        'ec_id' => $ec->id,
                        'ec_nom' => $ec->nom,
                        'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                        
                        // Affichage correct pour fusion
                        'total_etudiants' => $nbNotesNormales,
                        'etudiants_presents' => $nbNotesNormales,
                        'etudiants_avec_note' => $nbNotesNormales,
                        
                        // CORRECTION : Vraies valeurs
                        'manchettes_count' => $nbNotesNormales,     // Notes normales récupérées
                        'copies_count' => $nbCopiesRattrapage,      // Copies rattrapage réelles
                        'codes_count' => $nbNotesNormales,
                        
                        'complet' => true,
                        'etudiants_sans_manchette' => 0,
                        'codes_sans_manchettes' => ['count' => 0, 'codes' => []],
                        'codes_sans_copies' => ['count' => 0, 'codes' => []],
                        'issues' => [],
                        'session_type' => 'Rattrapage',
                        'type_fusion' => 'avec_rattrapage',
                        'fusion_automatique' => true,
                    ];
                } else if ($nbNotesNormales > 0) {
                    // CAS 2: EC avec SEULEMENT récupération automatique (PAS de copies rattrapage)
                    $rapport[] = [
                        'ec_id' => $ec->id,
                        'ec_nom' => $ec->nom,
                        'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                        
                        // Affichage pour récupération pure
                        'total_etudiants' => $nbNotesNormales,
                        'etudiants_presents' => $nbNotesNormales,
                        'etudiants_avec_note' => $nbNotesNormales,
                        
                        // CORRECTION : Affichage spécial pour récupération auto
                        'manchettes_count' => 0,                    // PAS de vraies manchettes rattrapage
                        'copies_count' => 0,                        // PAS de copies rattrapage
                        'codes_count' => $nbNotesNormales,
                        
                        // Données spéciales pour récupération auto
                        'notes_recuperees_auto' => $nbNotesNormales,
                        'affichage_special' => 'AUTO',
                        
                        'complet' => true,
                        'etudiants_sans_manchette' => 0,
                        'codes_sans_manchettes' => ['count' => 0, 'codes' => []],
                        'codes_sans_copies' => ['count' => 0, 'codes' => []],
                        'issues' => [],
                        'session_type' => 'Rattrapage',
                        'type_fusion' => 'recuperation_auto',
                        'fusion_automatique' => true,
                    ];
                } else {
                    // CAS 3: EC sans données (ne devrait pas arriver)
                    $rapport[] = [
                        'ec_id' => $ec->id,
                        'ec_nom' => $ec->nom,
                        'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                        'total_etudiants' => 0,
                        'etudiants_presents' => 0,
                        'etudiants_avec_note' => 0,
                        'manchettes_count' => 0,
                        'copies_count' => 0,
                        'codes_count' => 0,
                        'complet' => false,
                        'etudiants_sans_manchette' => 0,
                        'codes_sans_manchettes' => ['count' => 0, 'codes' => []],
                        'codes_sans_copies' => ['count' => 0, 'codes' => []],
                        'issues' => ['Aucune donnée trouvée'],
                        'session_type' => 'Rattrapage',
                        'type_fusion' => 'vide',
                        'fusion_automatique' => false,
                    ];
                }
            }

            \Log::info('Analyse rapport rattrapage avec affichage correct terminée', [
                'examen_id' => $examenId,
                'session_rattrapage_id' => $sessionActive->id,
                'session_normale_id' => $sessionNormale->id,
                'nb_ecs_analysees' => count($rapport),
                'total_notes_normales' => $resultatsNormale->flatten()->count(),
                'total_copies_rattrapage' => $copiesRattrapage->flatten()->count(),
            ]);

            return $rapport;

        } catch (\Exception $e) {
            \Log::error('Erreur analyse données pré-fusion rattrapage', [
                'examen_id' => $examenId,
                'session_id' => $sessionActive->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Récupérer les données de présence pour une EC spécifique
     */
    private function getPresenceDataForEC($examenId, $sessionActive, $ecId)
    {
        // 1. Chercher une présence spécifique à cette EC
        $presenceSpecifique = PresenceExamen::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->where('ec_id', $ecId)
            ->first();

        if ($presenceSpecifique && $presenceSpecifique->etudiants_presents > 0) {
            return [
                'etudiants_presents' => $presenceSpecifique->etudiants_presents,
                'total_inscrits' => $presenceSpecifique->total_attendu ?: $presenceSpecifique->total_etudiants,
                'source' => 'presence_ec_specifique'
            ];
        }

        // 2. Si pas de présence spécifique, utiliser la présence globale
        $presenceGlobale = PresenceExamen::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->whereNull('ec_id') // Présence globale
            ->first();

        if ($presenceGlobale && $presenceGlobale->etudiants_presents > 0) {
            return [
                'etudiants_presents' => $presenceGlobale->etudiants_presents,
                'total_inscrits' => $presenceGlobale->total_attendu ?: $presenceGlobale->total_etudiants,
                'source' => 'presence_globale'
            ];
        }

        // ✅ 3. AMÉLIORATION : Calculer à partir des résultats fusion s'ils existent
        $etudiantsAvecResultats = ResultatFusion::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->where('ec_id', $ecId)
            ->distinct('etudiant_id')
            ->count();

        if ($etudiantsAvecResultats > 0) {
            // Estimer le total théorique
            $examen = Examen::find($examenId);
            $totalTheorique = 0;
            if ($examen) {
                $totalTheorique = Etudiant::where('niveau_id', $examen->niveau_id)
                    ->where('parcours_id', $examen->parcours_id)
                    ->where('is_active', true)
                    ->count();
            }

            return [
                'etudiants_presents' => $etudiantsAvecResultats,
                'total_inscrits' => $totalTheorique,
                'source' => 'calcule_resultats_fusion'
            ];
        }

        // 4. Fallback : compter les manchettes existantes pour cette EC
        $manchettesCount = Manchette::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->whereHas('codeAnonymat', function($q) use ($ecId) {
                $q->where('ec_id', $ecId);
            })
            ->distinct('etudiant_id')
            ->count();

        // Estimer le total théorique
        $examen = Examen::find($examenId);
        $totalTheorique = 0;
        if ($examen) {
            $totalTheorique = Etudiant::where('niveau_id', $examen->niveau_id)
                ->where('parcours_id', $examen->parcours_id)
                ->where('is_active', true)
                ->count();
        }

        return [
            'etudiants_presents' => max($manchettesCount, 0),
            'total_inscrits' => $totalTheorique,
            'source' => 'calcule_manchettes'
        ];
    }

    /**
     * Récupérer le nombre d'étudiants attendus depuis les données de présence
     */
    private function getEtudiantsAttendusByPresence($examenId, $sessionActive, $ecId)
    {
        // Chercher d'abord une présence spécifique à cette EC
        $presenceSpecifique = PresenceExamen::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->where('ec_id', $ecId)
            ->first();

        if ($presenceSpecifique) {
            return $presenceSpecifique->etudiants_presents;
        }

        // Si pas de présence spécifique, utiliser la présence globale
        $presenceGlobale = PresenceExamen::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->whereNull('ec_id') // Présence globale
            ->first();

        if ($presenceGlobale) {
            return $presenceGlobale->etudiants_presents;
        }

        // Fallback : compter les manchettes existantes
        return Manchette::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->whereHas('codeAnonymat', function($q) use ($ecId) {
                $q->where('ec_id', $ecId);
            })
            ->distinct('etudiant_id')
            ->count();
    }

    
    private function calculerEtudiantsAttendus($examenId, $sessionActive, $totalEtudiants)
    {
        if ($sessionActive->type === 'Rattrapage') {
            // Pour le rattrapage, compter les étudiants qui ont eu une moyenne < 10 en session normale
            $sessionNormale = SessionExam::where('annee_universitaire_id', $sessionActive->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if ($sessionNormale) {
                // CORRECTION : Utiliser Eloquent au lieu de SQL brut pour éviter les erreurs de colonnes
                $examen = Examen::find($examenId);
                if ($examen) {
                    $etudiantsEligibles = 0;

                    $etudiants = Etudiant::where('niveau_id', $examen->niveau_id)
                        ->where('parcours_id', $examen->parcours_id)
                        ->where('is_active', true)
                        ->get();

                    foreach ($etudiants as $etudiant) {
                        $moyenne = ResultatFusion::where('examen_id', $examenId)
                            ->where('session_exam_id', $sessionNormale->id)
                            ->where('etudiant_id', $etudiant->id)
                            ->avg('note');

                        if ($moyenne && $moyenne < 10) {
                            $etudiantsEligibles++;
                        }
                    }

                    if ($etudiantsEligibles > 0) {
                        \Log::info('Étudiants éligibles au rattrapage calculés', [
                            'examen_id' => $examenId,
                            'session_normale_id' => $sessionNormale->id,
                            'session_rattrapage_id' => $sessionActive->id,
                            'etudiants_eligibles' => $etudiantsEligibles
                        ]);
                        return $etudiantsEligibles;
                    }
                }

                // Si aucun résultat fusion, estimer depuis les manchettes de rattrapage existantes
                $etudiantsRattrapage = Manchette::where('examen_id', $examenId)
                    ->where('session_exam_id', $sessionActive->id)
                    ->distinct('etudiant_id')
                    ->count('etudiant_id');

                \Log::info('Étudiants de rattrapage estimés depuis manchettes', [
                    'examen_id' => $examenId,
                    'session_rattrapage_id' => $sessionActive->id,
                    'etudiants_rattrapage' => $etudiantsRattrapage
                ]);

                return $etudiantsRattrapage > 0 ? $etudiantsRattrapage : $totalEtudiants;
            }
        }

        return $totalEtudiants; // Session normale
    }


    /**
     * Effectue la fusion des manchettes et copies
     */
    public function fusionner($examenId, $force = false)
    {
        // Augmenter temporairement les limites
        set_time_limit(300); // 5 minutes
        ini_set('memory_limit', '512M');
        
        try {
            $examen = Examen::findOrFail($examenId);

            // NOUVEAU : Récupérer la session active
            $sessionActive = SessionExam::where('is_active', true)
                ->where('is_current', true)
                ->first();

            if (!$sessionActive) {
                Log::error('Aucune session active trouvée pour la fusion', [
                    'examen_id' => $examenId,
                ]);
                return [
                    'success' => false,
                    'message' => 'Aucune session active trouvée.',
                ];
            }

            // CORRECTION : Vérifier l'étape actuelle POUR LA SESSION ACTIVE
            $currentEtape = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id) // IMPORTANT
                ->max('etape_fusion') ?? 0;

            if ($currentEtape >= 4 && !$force) {
                Log::warning('Fusion bloquée : déjà terminée pour cette session', [
                    'examen_id' => $examenId,
                    'session_id' => $sessionActive->id,
                    'session_type' => $sessionActive->type,
                    'etape_actuelle' => $currentEtape,
                ]);
                return [
                    'success' => false,
                    'message' => "La fusion est déjà terminée pour la session {$sessionActive->type}. Utilisez l'option de refusion si nécessaire.",
                ];
            }

            $nextEtape = $force ? 1 : ($currentEtape + 1);
            DB::beginTransaction();

            // Passer sessionActive->id aux méthodes d'étape
            switch ($nextEtape) {
                case 1:
                    $result = $this->executerEtape1($examenId, $sessionActive->id);
                    break;
                case 2:
                    $result = $this->executerEtape2($examenId, $sessionActive->id);
                    break;
                case 3:
                    $result = $this->executerEtape3($examenId, $sessionActive->id);
                    break;
                default:
                    throw new \Exception("Étape de fusion invalide : $nextEtape");
            }

            if (!$result['success']) {
                DB::rollBack();
                Log::warning('Échec de l\'étape de fusion', [
                    'examen_id' => $examenId,
                    'session_id' => $sessionActive->id,
                    'etape' => $nextEtape,
                    'message' => $result['message'],
                ]);
                return $result;
            }

            $statistiques = [
                'resultats_generes' => ResultatFusion::where('examen_id', $examenId)
                    ->where('session_exam_id', $sessionActive->id) // FILTRER PAR SESSION
                    ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2, ResultatFusion::STATUT_VERIFY_3])
                    ->count(),
                'etape' => $nextEtape,
                'session_type' => $sessionActive->type,
            ];

            DB::commit();

            Log::info('Fusion réussie pour session', [
                'examen_id' => $examenId,
                'session_id' => $sessionActive->id,
                'session_type' => $sessionActive->type,
                'etape' => $nextEtape,
                'statistiques' => $statistiques,
            ]);

            return [
                'success' => true,
                'message' => "Fusion étape $nextEtape terminée avec succès pour la session {$sessionActive->type}.",
                'statistiques' => $statistiques,
                'etape' => $nextEtape,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la fusion', [
                'examen_id' => $examenId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la fusion : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Étape 1 : Fusion des manchettes et copies
     */
    private function executerEtape1($examenId, $sessionId = null)
    {
        if (!$sessionId) {
            $sessionActive = SessionExam::where('is_active', true)
                ->where('is_current', true)
                ->first();

            if (!$sessionActive) {
                return [
                    'success' => false,
                    'message' => 'Aucune session active trouvée.',
                ];
            }
            $sessionId = $sessionActive->id;
        }

        $sessionActive = SessionExam::find($sessionId);
        
        // ✅ AJOUT SIMPLE : Si c'est du rattrapage, utiliser la logique spéciale
        if ($sessionActive && $sessionActive->type === 'Rattrapage') {
            return $this->executerEtape1Rattrapage($examenId, $sessionId, $sessionActive);
        }

        // Logique normale existante pour session normale...
        $manchettes = Manchette::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function ($query) {
                $query->whereNotNull('code_complet')->where('code_complet', '!=', '');
            })
            ->with(['etudiant', 'codeAnonymat'])
            ->get();

        if ($manchettes->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Aucune manchette valide trouvée pour cet examen dans cette session.',
            ];
        }

        $copies = Copie::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->whereNotNull('code_anonymat_id')
            ->whereHas('codeAnonymat', function ($query) {
                $query->whereNotNull('code_complet')->where('code_complet', '!=', '');
            })
            ->whereNotNull('ec_id')
            ->with(['ec', 'codeAnonymat'])
            ->get();

        if ($copies->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Aucune copie valide trouvée pour cet examen dans cette session.',
            ];
        }

        // Reste du code existant pour session normale...
        $resultatsExistants = ResultatFusion::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->get()
            ->groupBy(function($item) {
                return $item->etudiant_id . '_' . $item->ec_id;
            });

        $copiesParCode = $copies->groupBy('codeAnonymat.code_complet');
        $resultatsAInserer = [];
        $batchSize = 500;
        $resultatsGeneres = 0;
        $erreursIgnorees = 0;

        foreach ($manchettes->chunk($batchSize) as $manchettesChunk) {
            foreach ($manchettesChunk as $manchette) {
                $codeAnonymat = $manchette->codeAnonymat->code_complet;
                
                if (!isset($copiesParCode[$codeAnonymat])) {
                    $erreursIgnorees++;
                    continue;
                }

                foreach ($copiesParCode[$codeAnonymat] as $copie) {
                    $cleUnique = $manchette->etudiant_id . '_' . $copie->ec_id;
                    
                    if ($resultatsExistants->has($cleUnique)) {
                        continue;
                    }

                    $resultatsAInserer[] = [
                        'etudiant_id' => $manchette->etudiant_id,
                        'examen_id' => $examenId,
                        'code_anonymat_id' => $manchette->code_anonymat_id,
                        'ec_id' => $copie->ec_id,
                        'note' => $copie->note,
                        'genere_par' => Auth::id(),
                        'statut' => ResultatFusion::STATUT_VERIFY_1,
                        'etape_fusion' => 1,
                        'session_exam_id' => $sessionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $resultatsGeneres++;
                }

                if (count($resultatsAInserer) >= $batchSize) {
                    ResultatFusion::insert($resultatsAInserer);
                    $resultatsAInserer = [];
                    usleep(10000);
                }
            }
        }

        if (!empty($resultatsAInserer)) {
            ResultatFusion::insert($resultatsAInserer);
        }

        if ($resultatsGeneres === 0) {
            return [
                'success' => false,
                'message' => "Aucune donnée fusionnée. Erreurs ignorées : $erreursIgnorees.",
            ];
        }

        return [
            'success' => true,
            'resultats_generes' => $resultatsGeneres,
            'erreurs_ignorees' => $erreursIgnorees,
        ];
    }


    /**
     * ✅ MÉTHODE CORRIGÉE : Étape 1 spécialisée pour rattrapage (RÉCUPÉRATION AUTO)
     */
    private function executerEtape1Rattrapage($examenId, $sessionRattrapageId, $sessionRattrapage)
    {
        try {
            // 1. Trouver la session normale correspondante
            $sessionNormale = SessionExam::where('annee_universitaire_id', $sessionRattrapage->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                return [
                    'success' => false,
                    'message' => 'Session normale correspondante introuvable pour ce rattrapage.',
                ];
            }

            // ✅ CORRECTION PRINCIPALE : Récupérer SEULEMENT les étudiants éligibles au rattrapage
            $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage($examenId, $sessionNormale->id, $sessionRattrapage->id);
            
            if ($etudiantsEligibles->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Aucun étudiant éligible au rattrapage trouvé.',
                ];
            }

            $etudiantsEligiblesIds = $etudiantsEligibles->pluck('etudiant_id')->toArray();

            // 2. Récupérer SEULEMENT les résultats des étudiants éligibles (pas les admis)
            $resultatsNormale = ResultatFinal::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionNormale->id)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->whereIn('etudiant_id', $etudiantsEligiblesIds) // ✅ FILTRAGE CRUCIAL
                ->get()
                ->groupBy(function($item) {
                    return $item->etudiant_id . '_' . $item->ec_id;
                });

            Log::info('Étudiants filtrés pour rattrapage', [
                'examen_id' => $examenId,
                'session_normale_id' => $sessionNormale->id,
                'session_rattrapage_id' => $sessionRattrapageId,
                'total_etudiants_eligibles' => count($etudiantsEligiblesIds),
                'resultats_normaux_eligibles' => $resultatsNormale->count(),
            ]);

            if ($resultatsNormale->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Aucun résultat de session normale trouvé pour les étudiants éligibles au rattrapage.',
                ];
            }

            // 3. Récupérer copies de rattrapage RÉELLES
            $copiesRattrapage = Copie::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionRattrapageId)
                ->whereNotNull('note')
                ->with(['codeAnonymat'])
                ->get();

            // 4. Vérifier les résultats fusion existants
            $resultatsExistants = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionRattrapageId)
                ->get()
                ->groupBy(function($item) {
                    return $item->etudiant_id . '_' . $item->ec_id;
                });

            // 5. Grouper les copies par étudiant + EC
            $copiesParEtudiantEc = collect();
            foreach ($copiesRattrapage as $copie) {
                $etudiant = $copie->etudiant;
                
                if ($etudiant) {
                    $cle = $etudiant->id . '_' . $copie->ec_id;
                    $copiesParEtudiantEc->put($cle, [
                        'etudiant_id' => $etudiant->id,
                        'ec_id' => $copie->ec_id,
                        'note' => $copie->note,
                        'code_anonymat_id' => $copie->code_anonymat_id,
                        'copie' => $copie
                    ]);
                }
            }

            $resultatsAInserer = [];
            $resultatsGeneres = 0;

            // 6. LOGIQUE PRINCIPALE : Pour chaque résultat de session normale (étudiants éligibles seulement)
            foreach ($resultatsNormale as $cleNormale => $resultatsGroupe) {
                $resultatNormale = $resultatsGroupe->first();
                $etudiantId = $resultatNormale->etudiant_id;
                $ecId = $resultatNormale->ec_id;
                $noteNormale = $resultatNormale->note;

                // ✅ VÉRIFICATION SUPPLÉMENTAIRE : S'assurer que l'étudiant est bien éligible
                if (!in_array($etudiantId, $etudiantsEligiblesIds)) {
                    Log::warning('Étudiant non éligible détecté dans les résultats', [
                        'etudiant_id' => $etudiantId,
                        'examen_id' => $examenId,
                        'session_rattrapage_id' => $sessionRattrapageId
                    ]);
                    continue;
                }

                // Vérifier si déjà traité
                if ($resultatsExistants->has($cleNormale)) {
                    continue;
                }

                // Chercher s'il y a une copie de rattrapage pour cet étudiant/EC
                $copieRattrapageData = $copiesParEtudiantEc->get($cleNormale);
                $noteRattrapage = null;
                $codeAnonymatId = null;

                if ($copieRattrapageData) {
                    $noteRattrapage = $copieRattrapageData['note'];
                    $codeAnonymatId = $copieRattrapageData['code_anonymat_id'];
                }

                // FUSION DES NOTES : Appliquer la logique médecine
                $noteFinale = $this->determinerMeilleureNote($noteNormale, $noteRattrapage);
                $sourceNote = $this->determinerSourceNote($noteNormale, $noteRattrapage, $noteFinale);

                // Créer le code anonymat pour rattrapage si pas existant
                if (!$codeAnonymatId) {
                    $codeAnonymat = CodeAnonymat::firstOrCreate([
                        'examen_id' => $examenId,
                        'ec_id' => $ecId,
                        'code_complet' => "RAT-{$ecId}-{$etudiantId}-" . time(),
                    ], [
                        'sequence' => $etudiantId * 1000 + $ecId
                    ]);
                    $codeAnonymatId = $codeAnonymat->id;
                }

                // Créer le résultat fusion SANS metadata
                $resultatsAInserer[] = [
                    'etudiant_id' => $etudiantId,
                    'examen_id' => $examenId,
                    'code_anonymat_id' => $codeAnonymatId,
                    'ec_id' => $ecId,
                    'note' => $noteFinale,
                    'genere_par' => Auth::id(),
                    'statut' => ResultatFusion::STATUT_VERIFY_1,
                    'etape_fusion' => 1,
                    'session_exam_id' => $sessionRattrapageId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $resultatsGeneres++;
            }

            // 7. Insérer les résultats en masse
            if (!empty($resultatsAInserer)) {
                $chunks = array_chunk($resultatsAInserer, 500);
                foreach ($chunks as $chunk) {
                    ResultatFusion::insert($chunk);
                }
            }

            Log::info('Fusion rattrapage corrigée terminée', [
                'examen_id' => $examenId,
                'session_rattrapage_id' => $sessionRattrapageId,
                'session_normale_id' => $sessionNormale->id,
                'etudiants_eligibles' => count($etudiantsEligiblesIds),
                'resultats_generes' => $resultatsGeneres,
                'resultats_normale_recuperes' => $resultatsNormale->count(),
                'copies_rattrapage_trouvees' => $copiesRattrapage->count()
            ]);

            return [
                'success' => true,
                'resultats_generes' => $resultatsGeneres,
                'fusion_rattrapage' => true,
                'etudiants_eligibles_traites' => count($etudiantsEligiblesIds),
                'notes_normales_recuperees' => $resultatsNormale->count(),
                'copies_rattrapage_integrees' => $copiesRattrapage->count()
            ];

        } catch (\Exception $e) {
            Log::error('Erreur fusion rattrapage corrigée', [
                'examen_id' => $examenId,
                'session_rattrapage_id' => $sessionRattrapageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la fusion rattrapage : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Récupère les étudiants éligibles au rattrapage
     */
    private function getEtudiantsEligiblesRattrapage($examenId, $sessionNormaleId, $sessionRattrapageId)
    {
        try {
            // Récupérer les étudiants avec décision "rattrapage" ou "redoublant" en session normale
            $etudiantsEligibles = ResultatFinal::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionNormaleId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->whereIn('decision', [
                    ResultatFinal::DECISION_RATTRAPAGE,
                    ResultatFinal::DECISION_REDOUBLANT
                    // ✅ NE PAS inclure DECISION_ADMIS
                ])
                ->select('etudiant_id')
                ->distinct()
                ->with('etudiant')
                ->get();

            // Fallback : si pas de décisions enregistrées, utiliser la moyenne < 10
            if ($etudiantsEligibles->isEmpty()) {
                Log::info('Aucune décision trouvée, utilisation du fallback moyenne < 10', [
                    'examen_id' => $examenId,
                    'session_normale_id' => $sessionNormaleId
                ]);

                $etudiantsAvecMoyenne = ResultatFinal::where('examen_id', $examenId)
                    ->where('session_exam_id', $sessionNormaleId)
                    ->where('statut', ResultatFinal::STATUT_PUBLIE)
                    ->select('etudiant_id')
                    ->selectRaw('AVG(note) as moyenne')
                    ->groupBy('etudiant_id')
                    ->havingRaw('AVG(note) < 10')
                    ->with('etudiant')
                    ->get();

                return $etudiantsAvecMoyenne->map(function($resultat) {
                    return [
                        'etudiant_id' => $resultat->etudiant_id,
                        'etudiant' => $resultat->etudiant,
                        'moyenne_normale' => $resultat->moyenne,
                        'decision_normale' => 'rattrapage',
                        'source' => 'moyenne_calculee'
                    ];
                });
            }

            return $etudiantsEligibles->map(function($resultat) {
                return [
                    'etudiant_id' => $resultat->etudiant_id,
                    'etudiant' => $resultat->etudiant,
                    'decision_normale' => $resultat->decision,
                    'source' => 'decision_officielle'
                ];
            });

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des étudiants éligibles', [
                'examen_id' => $examenId,
                'session_normale_id' => $sessionNormaleId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }
    /**
     * ✅ MÉTHODE SIMPLE : Détermine la meilleure note selon logique médecine
     */
    private function determinerMeilleureNote($noteNormale, $noteRattrapage)
    {
        // Si pas de note de rattrapage, garder la normale
        if ($noteRattrapage === null) {
            return $noteNormale;
        }

        // Si note de rattrapage = 0 et normale > 0 → Note éliminatoire
        if ($noteRattrapage == 0 && $noteNormale > 0) {
            return 0;
        }

        // Sinon prendre la meilleure note
        return max($noteNormale, $noteRattrapage);
    }

    /**
     * ✅ MÉTHODE SIMPLE : Détermine la source de la note finale
     */
    private function determinerSourceNote($noteNormale, $noteRattrapage, $noteFinale)
    {
        if ($noteRattrapage === null) {
            return 'normale_conservee';
        }
        
        if ($noteFinale == 0 && $noteRattrapage == 0) {
            return 'rattrapage_eliminatoire';
        }
        
        if ($noteFinale == $noteRattrapage && $noteRattrapage >= $noteNormale) {
            return 'rattrapage_ameliore';
        }
        
        if ($noteFinale == $noteNormale && $noteNormale > $noteRattrapage) {
            return 'normale_meilleure';
        }
        
        return 'fusion_auto';
    }

    /**
     * Insérer les résultats par batch
     */
    private function insererResultatsBatch(array $resultatsAInserer, array $codesAnonymatACreer, int $examenId)
    {
        // 1. Insérer/récupérer les codes d'anonymat
        $codesExistants = CodeAnonymat::where('examen_id', $examenId)
            ->whereIn('code_complet', array_column($codesAnonymatACreer, 'code_complet'))
            ->get()
            ->keyBy(function($item) {
                return $item->code_complet . '_' . $item->ec_id;
            });

        // Insérer les nouveaux codes
        $nouveauxCodes = [];
        foreach ($codesAnonymatACreer as $key => $codeData) {
            if (!$codesExistants->has($key)) {
                $nouveauxCodes[] = $codeData;
            }
        }

        if (!empty($nouveauxCodes)) {
            CodeAnonymat::insert($nouveauxCodes);
            
            // Récupérer les codes nouvellement insérés
            $codesInseres = CodeAnonymat::where('examen_id', $examenId)
                ->whereIn('code_complet', array_column($nouveauxCodes, 'code_complet'))
                ->get()
                ->keyBy(function($item) {
                    return $item->code_complet . '_' . $item->ec_id;
                });
            
            $codesExistants = $codesExistants->merge($codesInseres);
        }

        // 2. Mettre à jour les IDs des codes d'anonymat dans les résultats
        foreach ($resultatsAInserer as &$resultat) {
            $codeKey = $resultat['_code_key'];
            if ($codesExistants->has($codeKey)) {
                $resultat['code_anonymat_id'] = $codesExistants[$codeKey]->id;
            }
            unset($resultat['_code_key']); // Supprimer la clé temporaire
        }

        // 3. Insérer les résultats en masse
        $resultatsValides = array_filter($resultatsAInserer, function($r) {
            return !is_null($r['code_anonymat_id']);
        });

        if (!empty($resultatsValides)) {
            ResultatFusion::insert($resultatsValides);
        }
    }

    /**
     * Extrait la séquence d'un code d'anonymat
     */
    private function extraireSequence($codeAnonymat)
    {
        return preg_match('/(\d+)$/', $codeAnonymat, $matches) ? (int) $matches[1] : null;
    }

    /**
     * Étape 2 : Validation des résultats
     */
    private function executerEtape2($examenId, $sessionId = null)
    {
        if (!$sessionId) {
            $sessionActive = SessionExam::where('is_active', true)->where('is_current', true)->first();
            $sessionId = $sessionActive ? $sessionActive->id : null;
        }

        // OPTIMISATION: Traitement par batch pour éviter le timeout
        $batchSize = 1000;
        $resultatsValides = 0;
        $offset = 0;

        do {
            $resultats = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionId)
                ->where('statut', ResultatFusion::STATUT_VERIFY_1)
                ->where('etape_fusion', 1)
                ->offset($offset)
                ->limit($batchSize)
                ->get();

            if ($resultats->isEmpty()) {
                break;
            }

            // Traitement par batch
            $idsToUpdate = [];
            foreach ($resultats as $resultat) {
                if ($this->validerResultat($resultat)) {
                    $idsToUpdate[] = $resultat->id;
                    $resultatsValides++;
                }
            }

            // Mise à jour en masse
            if (!empty($idsToUpdate)) {
                ResultatFusion::whereIn('id', $idsToUpdate)
                    ->update([
                        'statut' => ResultatFusion::STATUT_VERIFY_2,
                        'etape_fusion' => 2,
                        'modifie_par' => Auth::id(),
                        'updated_at' => now()
                    ]);
            }

            $offset += $batchSize;
            
            // Petit délai pour éviter la surcharge
            usleep(5000); // 5ms

        } while ($resultats->count() === $batchSize);

        if ($resultatsValides === 0) {
            return [
                'success' => false,
                'message' => 'Aucun résultat à valider à l\'étape 1 pour cette session.',
            ];
        }

        return [
            'success' => true,
            'resultats_valides' => $resultatsValides,
        ];
    }

    /**
     * Étape 3 : Troisième vérification avant finalisation
     */
    private function executerEtape3($examenId, $sessionId = null)
    {
        if (!$sessionId) {
            $sessionActive = SessionExam::where('is_active', true)->where('is_current', true)->first();
            $sessionId = $sessionActive ? $sessionActive->id : null;
        }

        // OPTIMISATION: Traitement par batch
        $batchSize = 1000;
        $resultatsFinalises = 0;
        $offset = 0;

        // Pré-charger les étudiants actifs pour optimiser la validation
        $etudiantsActifs = Etudiant::where('is_active', true)
            ->pluck('id')
            ->flip(); // Pour une recherche O(1)

        do {
            $resultats = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionId)
                ->where('statut', ResultatFusion::STATUT_VERIFY_2)
                ->where('etape_fusion', 2)
                ->offset($offset)
                ->limit($batchSize)
                ->get();

            if ($resultats->isEmpty()) {
                break;
            }

            // Traitement par batch
            $idsToUpdate = [];
            foreach ($resultats as $resultat) {
                if ($this->verifierResultatEtape3Optimise($resultat, $etudiantsActifs)) {
                    $idsToUpdate[] = $resultat->id;
                    $resultatsFinalises++;
                }
            }

            // Mise à jour en masse
            if (!empty($idsToUpdate)) {
                ResultatFusion::whereIn('id', $idsToUpdate)
                    ->update([
                        'statut' => ResultatFusion::STATUT_VERIFY_3,
                        'etape_fusion' => 3,
                        'modifie_par' => Auth::id(),
                        'updated_at' => now()
                    ]);
            }

            $offset += $batchSize;
            usleep(5000); // 5ms

        } while ($resultats->count() === $batchSize);

        if ($resultatsFinalises === 0) {
            return [
                'success' => false,
                'message' => 'Aucun résultat à traiter à l\'étape 2 pour cette session.',
            ];
        }

        return [
            'success' => true,
            'resultats_traites' => $resultatsFinalises,
        ];
    }


    /**
     * Version optimisée de la vérification étape 3
     */
    private function verifierResultatEtape3Optimise(ResultatFusion $resultat, $etudiantsActifs)
    {
        // Vérifier que la note est valide
        if ($resultat->note !== null && ($resultat->note < 0 || $resultat->note > 20)) {
            return false;
        }

        // Vérifier que l'étudiant est actif (recherche O(1))
        if (!isset($etudiantsActifs[$resultat->etudiant_id])) {
            return false;
        }

        return true;
    }

    /**
     * Effectue des vérifications supplémentaires pour l'étape 3
     */
    private function verifierResultatEtape3(ResultatFusion $resultat)
    {
        // Vérifier que la note est valide et dans la plage autorisée
        if ($resultat->note !== null && ($resultat->note < 0 || $resultat->note > 20)) {
            Log::warning('Résultat invalide pour étape 3 : note hors plage', [
                'resultat_id' => $resultat->id,
                'examen_id' => $resultat->examen_id,
                'note' => $resultat->note,
            ]);
            return false;
        }

        // Vérifier que l'étudiant existe et est actif
        $etudiantActif = Etudiant::where('id', $resultat->etudiant_id)
            ->where('is_active', true)
            ->exists();

        if (!$etudiantActif) {
            Log::warning('Résultat invalide pour étape 3 : étudiant inactif', [
                'resultat_id' => $resultat->id,
                'etudiant_id' => $resultat->etudiant_id,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Valide un résultat
     */
    private function validerResultat(ResultatFusion $resultat)
    {
        if (!$resultat->etudiant_id || !$resultat->ec_id || !$resultat->examen_id) {
            Log::warning('Résultat invalide : champs manquants', [
                'resultat_id' => $resultat->id,
                'examen_id' => $resultat->examen_id,
                'etudiant_id' => $resultat->etudiant_id,
                'ec_id' => $resultat->ec_id,
            ]);
            return false;
        }

        if ($resultat->note !== null && ($resultat->note < 0 || $resultat->note > 20)) {
            Log::warning('Résultat invalide : note hors plage', [
                'resultat_id' => $resultat->id,
                'examen_id' => $resultat->examen_id,
                'note' => $resultat->note,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Transfère les résultats de resultats_fusion vers resultats_finaux
     */
    public function transfererResultats(array $resultatFusionIds, int $generePar)
    {
        try {
            DB::beginTransaction();

            // CORRECTION 1: Récupérer la session active pour filtrer correctement
            $sessionActive = SessionExam::where('is_active', true)
                ->where('is_current', true)
                ->first();

            if (!$sessionActive) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucune session active trouvée.',
                    'resultats_transférés' => 0,
                ];
            }

            // CORRECTION 2: Filtrer les résultats fusion par session ET par IDs
            $resultatsFusion = ResultatFusion::whereIn('id', $resultatFusionIds)
                ->where('session_exam_id', $sessionActive->id) // IMPORTANT: Filtrer par session
                ->whereIn('statut', [
                    ResultatFusion::STATUT_VERIFY_3,
                    ResultatFusion::STATUT_VALIDE
                ])
                ->get();

            Log::info('Résultats fusion récupérés pour transfert', [
                'session_id' => $sessionActive->id,
                'session_type' => $sessionActive->type,
                'ids_demandes' => count($resultatFusionIds),
                'resultats_trouves' => $resultatsFusion->count(),
                'premier_resultat_session_id' => $resultatsFusion->first()?->session_exam_id ?? 'aucun'
            ]);

            if ($resultatsFusion->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Aucun résultat valide à transférer pour la session {$sessionActive->type} (statuts acceptés: VERIFY_3, VALIDE).",
                    'resultats_transférés' => 0,
                ];
            }

            $resultatsTransférés = 0;
            $etudiantsTraites = [];
            $examenId = $resultatsFusion->first()->examen_id;

            foreach ($resultatsFusion as $resultatFusion) {
                // CORRECTION 3: Vérifier l'existence pour la session spécifique
                $exists = ResultatFinal::where('etudiant_id', $resultatFusion->etudiant_id)
                    ->where('examen_id', $resultatFusion->examen_id)
                    ->where('ec_id', $resultatFusion->ec_id)
                    ->where('session_exam_id', $sessionActive->id) // IMPORTANT: Vérifier pour la session actuelle
                    ->exists();

                if ($exists) {
                    Log::info('Résultat final existe déjà', [
                        'etudiant_id' => $resultatFusion->etudiant_id,
                        'examen_id' => $resultatFusion->examen_id,
                        'ec_id' => $resultatFusion->ec_id,
                        'session_id' => $sessionActive->id
                    ]);
                    continue;
                }

                // CORRECTION 4: Créer le résultat final avec session_exam_id
                $resultatFinal = ResultatFinal::create([
                    'etudiant_id' => $resultatFusion->etudiant_id,
                    'examen_id' => $resultatFusion->examen_id,
                    'session_exam_id' => $sessionActive->id, // IMPORTANT: Ajouter la session
                    'code_anonymat_id' => $resultatFusion->code_anonymat_id,
                    'ec_id' => $resultatFusion->ec_id,
                    'note' => $resultatFusion->note,
                    'genere_par' => $generePar,
                    'modifie_par' => null,
                    'statut' => ResultatFinal::STATUT_EN_ATTENTE,
                    'decision' => null, // Sera calculée plus tard
                    'date_publication' => null,
                    'hash_verification' => hash('sha256', $resultatFusion->id . $resultatFusion->note . time()),
                    'fusion_id' => $resultatFusion->id,
                    'date_fusion' => now(),
                ]);

                $resultatsTransférés++;
                $etudiantsTraites[$resultatFusion->etudiant_id] = true;

                // Marquer le résultat fusion comme valide
                if ($resultatFusion->statut !== ResultatFusion::STATUT_VALIDE) {
                    $resultatFusion->changerStatut(ResultatFusion::STATUT_VALIDE, $generePar);
                }
            }

            // CORRECTION 5: Calcul des décisions selon le type de session
            foreach (array_keys($etudiantsTraites) as $etudiantId) {
                // Calculer la décision selon le type de session
                if ($sessionActive->type === 'Rattrapage') {
                    $decision = ResultatFinal::determinerDecisionRattrapage($etudiantId, $sessionActive->id);
                } else {
                    $decision = ResultatFinal::determinerDecisionPremiereSession($etudiantId, $sessionActive->id); // ⬅️ CETTE MÉTHODE
                }

                // Mettre à jour tous les résultats de l'étudiant pour cette session
                $resultatsEtudiantFinaux = ResultatFinal::where('etudiant_id', $etudiantId)
                    ->where('examen_id', $examenId)
                    ->where('session_exam_id', $sessionActive->id) // IMPORTANT: Filtrer par session
                    ->get();

                foreach ($resultatsEtudiantFinaux as $resultat) {
                    $resultat->decision = $decision;
                    $resultat->save();
                }

            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Transfert effectué avec succès pour la session {$sessionActive->type}. $resultatsTransférés résultat(s) transféré(s).",
                'resultats_transférés' => $resultatsTransférés,
                'session_type' => $sessionActive->type,
                'etudiants_traites' => count($etudiantsTraites)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du transfert des résultats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $sessionActive->id ?? null,
                'session_type' => $sessionActive->type ?? null
            ]);
            return [
                'success' => false,
                'message' => 'Erreur lors du transfert : ' . $e->getMessage(),
                'resultats_transférés' => 0,
            ];
        }
    }

    /**
     * Annule les résultats publiés - VERSION MISE À JOUR
     *
     * @param int $examenId
     * @param string|null $motifAnnulation
     * @return array
     */
    public function annulerResultats($examenId, $motifAnnulation = null, $sessionId = null)
    {
        try {
            // Récupérer la session si pas fournie
            if (!$sessionId) {
                $sessionActive = SessionExam::where('is_active', true)->where('is_current', true)->first();
                $sessionId = $sessionActive ? $sessionActive->id : null;
            }

            if (!$sessionId) {
                return [
                    'success' => false,
                    'message' => 'Aucune session active trouvée.',
                ];
            }

            DB::beginTransaction();

            // FILTRER PAR SESSION
            $resultats = ResultatFinal::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionId) // IMPORTANT
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat publié à annuler pour cette session.',
                ];
            }

            $fusionIds = $resultats->pluck('fusion_id')->filter()->unique()->toArray();

            // Annuler les résultats finaux en utilisant la nouvelle méthode
            $updatedCount = 0;
            foreach ($resultats as $resultat) {
                $resultat->annuler(Auth::id(), $motifAnnulation);
                $updatedCount++;
            }

            // Marquer les résultats fusionnés comme annulés pour cette session
            if (!empty($fusionIds)) {
                ResultatFusion::whereIn('id', $fusionIds)
                    ->where('session_exam_id', $sessionId) // FILTRER PAR SESSION
                    ->update([
                        'statut' => ResultatFusion::STATUT_ANNULE,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Résultats annulés avec succès. $updatedCount résultats mis à jour.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation des résultats', [
                'examen_id' => $examenId,
                'session_id' => $sessionId,
                'motif' => $motifAnnulation,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'annulation : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Restaure les résultats annulés à l'état en attente - VERSION MISE À JOUR
     *
     * @param int $examenId
     * @return array
     */
    public function revenirValidation($examenId, $sessionId = null)
    {
        try {
            // Récupérer la session si pas fournie
            if (!$sessionId) {
                $sessionActive = SessionExam::where('is_active', true)->where('is_current', true)->first();
                $sessionId = $sessionActive ? $sessionActive->id : null;
            }

            if (!$sessionId) {
                return [
                    'success' => false,
                    'message' => 'Aucune session active trouvée.',
                ];
            }

            DB::beginTransaction();

            // FILTRER PAR SESSION
            $resultats = ResultatFinal::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionId) // IMPORTANT
                ->where('statut', ResultatFinal::STATUT_ANNULE)
                ->get();

            if ($resultats->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat annulé à restaurer pour cette session.',
                ];
            }

            $fusionIds = $resultats->pluck('fusion_id')->filter()->unique()->toArray();

            // Restaurer les résultats finaux en utilisant la nouvelle méthode
            foreach ($resultats as $resultat) {
                $resultat->reactiver(Auth::id());
            }

            // Restaurer les résultats fusionnés au statut VALIDE pour cette session
            if (!empty($fusionIds)) {
                $nbFusionRestored = ResultatFusion::whereIn('id', $fusionIds)
                    ->where('session_exam_id', $sessionId) // FILTRER PAR SESSION
                    ->where('statut', ResultatFusion::STATUT_ANNULE)
                    ->update([
                        'statut' => ResultatFusion::STATUT_VALIDE,
                        'updated_at' => now(),
                    ]);

                Log::info('Résultats fusion restaurés pour session', [
                    'examen_id' => $examenId,
                    'session_id' => $sessionId,
                    'fusion_ids_restored' => $fusionIds,
                    'nb_restored' => $nbFusionRestored
                ]);
            }

            DB::commit();

            Log::info('Retour à l\'état en attente effectué pour session', [
                'examen_id' => $examenId,
                'session_id' => $sessionId,
                'resultats_restaures' => $resultats->count(),
                'fusion_ids_restored' => $fusionIds,
            ]);

            return [
                'success' => true,
                'message' => 'Retour à l\'état en attente effectué.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du retour à l\'état en attente', [
                'examen_id' => $examenId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Erreur lors du retour : ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Réinitialise tous les résultats de l'examen
     *
     * @param int $examenId
     * @return array
     */
    public function resetExam($examenId, $sessionId = null)
    {
        try {
            // Récupérer la session si pas fournie
            if (!$sessionId) {
                $sessionActive = SessionExam::where('is_active', true)->where('is_current', true)->first();
                $sessionId = $sessionActive ? $sessionActive->id : null;
            }

            if (!$sessionId) {
                return [
                    'success' => false,
                    'message' => 'Aucune session active trouvée.',
                ];
            }

            DB::beginTransaction();

            // COMPTEURS AVEC FILTRAGE PAR SESSION
            $totalFusion = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionId)
                ->count();

            $totalFinal = ResultatFinal::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionId)
                ->count();

            if ($totalFusion === 0 && $totalFinal === 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat à supprimer pour cet examen dans cette session.',
                ];
            }

            // Supprimer d'abord les historiques associés pour cette session
            $resultatFinalIds = ResultatFinal::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionId)
                ->pluck('id');

            if ($resultatFinalIds->isNotEmpty()) {
                ResultatFinalHistorique::whereIn('resultat_final_id', $resultatFinalIds)->delete();
            }

            // SUPPRESSION AVEC FILTRAGE PAR SESSION
            $deletedFusion = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionId)
                ->delete();

            $deletedFinal = ResultatFinal::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionId)
                ->delete();

            DB::commit();

            Log::info('Examen réinitialisé pour session', [
                'examen_id' => $examenId,
                'session_id' => $sessionId,
                'resultats_fusion_supprimes' => $deletedFusion,
                'resultats_finaux_supprimes' => $deletedFinal,
            ]);

            return [
                'success' => true,
                'message' => "$deletedFusion résultat(s) fusion et $deletedFinal résultat(s) final supprimé(s) pour cette session.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la réinitialisation pour session', [
                'examen_id' => $examenId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation : ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Valide les données avant la vérification de cohérence - VERSION CORRIGÉE
     */
    private function validateData($examenId)
    {
        $sessionActive = SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->first();

        if (!$sessionActive) {
            return ['valid' => false, 'issues' => ['Aucune session active trouvée.']];
        }

        $issues = [];

        // Vérifier l'examen
        $examen = Examen::find($examenId);
        if (!$examen) {
            $issues[] = "L'examen ID $examenId n'existe pas";
            return ['valid' => false, 'issues' => $issues];
        }

        // CORRECTION : Validation différente selon le type de session
        if ($sessionActive->type === 'Rattrapage') {
            // Pour le rattrapage, vérifier qu'il y a des données (même partielles)
            $hasData = Manchette::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->exists()
                ||
                Copie::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->exists();

            \Log::info('Validation rattrapage', [
                'examen_id' => $examenId,
                'session_id' => $sessionActive->id,
                'has_data' => $hasData
            ]);

            // Pour le rattrapage, OK même sans données (elles peuvent être créées)
            return ['valid' => true];
        } else {
            // Validation normale pour session normale
            $codesAvecManchettes = DB::table('manchettes as m')
                ->join('codes_anonymat as ca', 'm.code_anonymat_id', '=', 'ca.id')
                ->where('m.examen_id', $examenId)
                ->where('m.session_exam_id', $sessionActive->id)
                ->count();

            if ($codesAvecManchettes === 0) {
                $issues[] = "Aucune manchette saisie pour cet examen dans la session {$sessionActive->type}";
                return ['valid' => false, 'issues' => $issues];
            }
        }

        return ['valid' => true];
    }
}