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
use App\Models\ResultatFusion;
use App\Models\ResultatFinal;
use App\Models\ResultatFinalHistorique;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
            ->where('session_exam_id', $sessionActive->id) // IMPORTANT : session spécifique
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

            // NOUVEAU : Calculer selon le type de session
            $expectedStudents = $totalEtudiants;
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
            }

            $complet = $expectedStudents === $etudiantsAvecNote && $expectedStudents > 0;

            $rapport[] = [
                'ec_id' => $ecId,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                'total_etudiants' => $expectedStudents,
                'etudiants_avec_note' => $etudiantsAvecNote,
                'manchettes_count' => $etudiantsAvecResult,
                'copies_count' => $etudiantsAvecNote,
                'codes_count' => $etudiantsAvecResult,
                'complet' => $complet,
                'etudiants_sans_manchette' => $expectedStudents - $etudiantsAvecResult,
                'codes_sans_manchettes' => ['count' => 0, 'codes' => []],
                'codes_sans_copies' => ['count' => 0, 'codes' => []],
                'issues' => [],
                'session_type' => $sessionActive->type,
            ];
        }

        \Log::info('Analyse des résultats existants par session', [
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

        // CORRECTION : Chercher les copies dans la session active
        $copies = Copie::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->whereNotNull('code_anonymat_id')
            ->with(['ec', 'codeAnonymat'])
            ->get();

        \Log::info('Copies trouvées pour la session', [
            'examen_id' => $examenId,
            'session_id' => $sessionActive->id,
            'session_type' => $sessionActive->type,
            'nb_copies' => $copies->count(),
            'ec_ids_copies' => $copies->pluck('ec_id')->unique()->values()->toArray()
        ]);

        // CORRECTION : Obtenir les ECs depuis les copies existantes ET depuis l'examen
        $ecsDepuisCopies = $copies->pluck('ec_id')->unique();

        // ECs liées à l'examen via la table pivot
        $ecsDepuisExamen = EC::whereHas('examens', function ($query) use ($examenId) {
            $query->where('examens.id', $examenId);
        })->pluck('id');

        // Fusionner les deux listes
        $ecIds = $ecsDepuisCopies->merge($ecsDepuisExamen)->unique();
        $ecs = EC::whereIn('id', $ecIds)->get();

        \Log::info('ECs détectées pour analyse', [
            'examen_id' => $examenId,
            'session_id' => $sessionActive->id,
            'ecs_depuis_copies' => $ecsDepuisCopies->toArray(),
            'ecs_depuis_examen' => $ecsDepuisExamen->toArray(),
            'ecs_finales' => $ecs->pluck('id')->toArray()
        ]);

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

            // Filtrer et valider les codes en copies
            $codesEnCopies = $copiesEc->filter(function ($copie) {
                return $copie->codeAnonymat && is_string($copie->codeAnonymat->code_complet) && !empty($copie->codeAnonymat->code_complet);
            })->map(function ($copie) {
                return $copie->codeAnonymat->code_complet;
            })->unique()->values();

            // Chercher les manchettes pour cette EC dans la session active
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

            // Filtrer et valider les manchettes avec codes
            $manchettesAvecCodes = $manchettes->filter(function ($manchette) {
                return $manchette->codeAnonymat && is_string($manchette->codeAnonymat->code_complet) && !empty($manchette->codeAnonymat->code_complet);
            });

            $codesEnManchettes = $manchettesAvecCodes->map(function ($manchette) {
                return $manchette->codeAnonymat->code_complet;
            })->unique()->values();

            $manchettesCorrespondantes = $manchettesAvecCodes->filter(function ($manchette) use ($codesEnCopies) {
                return $codesEnCopies->contains($manchette->codeAnonymat->code_complet);
            });

            // Calculs pour le rapport
            $codesSansManchettes = collect($codesEnCopies)->diff(collect($codesEnManchettes));
            $codesSansCopies = collect($codesEnManchettes)->diff(collect($codesEnCopies));

            $etudiantsAvecManchette = $manchettesCorrespondantes->pluck('etudiant_id')->unique();

            // CORRECTION : Calcul des étudiants attendus selon la session
            $etudiantsAttendus = $this->calculerEtudiantsAttendus($examenId, $sessionActive, $totalEtudiants);
            $etudiantsSansManchette = $etudiantsAttendus - $etudiantsAvecManchette->count();

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
                'etudiants_avec_note' => $copiesAvecNote->count(),
                'manchettes_count' => $manchettesCorrespondantes->count(),
                'copies_count' => $copiesEc->count(),
                'codes_count' => $codesEnCopies->count(),
                'complet' => $complet,
                'etudiants_sans_manchette' => max(0, $etudiantsSansManchette),
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

            \Log::info('EC analysée', [
                'ec_id' => $ec->id,
                'ec_nom' => $ec->nom,
                'session_type' => $sessionActive->type,
                'copies_count' => $copiesEc->count(),
                'manchettes_count' => $manchettesCorrespondantes->count(),
                'etudiants_attendus' => $etudiantsAttendus,
                'complet' => $complet
            ]);
        }

        return $rapport;
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
        // Si pas de sessionId fourni, utiliser la session active
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

        $manchettes = Manchette::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId) // FILTRER PAR SESSION
            ->whereHas('codeAnonymat', function ($query) {
                $query->whereNotNull('code_complet')->where('code_complet', '!=', '');
            })
            ->with(['etudiant', 'codeAnonymat'])
            ->get();

        if ($manchettes->isEmpty()) {
            \Log::warning('Aucune manchette valide pour la fusion', [
                'examen_id' => $examenId,
                'session_exam_id' => $sessionId,
            ]);
            return [
                'success' => false,
                'message' => 'Aucune manchette valide trouvée pour cet examen dans cette session.',
            ];
        }

        $copies = Copie::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId) // FILTRER PAR SESSION
            ->whereNotNull('code_anonymat_id')
            ->whereHas('codeAnonymat', function ($query) {
                $query->whereNotNull('code_complet')->where('code_complet', '!=', '');
            })
            ->whereNotNull('ec_id')
            ->with(['ec', 'codeAnonymat'])
            ->get();

        if ($copies->isEmpty()) {
            \Log::warning('Aucune copie valide pour la fusion', [
                'examen_id' => $examenId,
                'session_exam_id' => $sessionId,
            ]);
            return [
                'success' => false,
                'message' => 'Aucune copie valide trouvée pour cet examen dans cette session.',
            ];
        }

        $resultatsGeneres = 0;
        $erreursIgnorees = 0;
        $codesTraites = [];

        foreach ($manchettes as $manchette) {
            $codeAnonymat = $manchette->codeAnonymat->code_complet;
            $cleUnique = $manchette->etudiant_id . '_' . $codeAnonymat;

            if (isset($codesTraites[$cleUnique])) {
                continue;
            }
            $codesTraites[$cleUnique] = true;

            $copiesCorrespondantes = $copies->where('codeAnonymat.code_complet', $codeAnonymat);

            if ($copiesCorrespondantes->isEmpty()) {
                \Log::warning('Aucune copie pour le code d\'anonymat', [
                    'examen_id' => $examenId,
                    'session_exam_id' => $sessionId,
                    'code_anonymat' => $codeAnonymat,
                    'etudiant_id' => $manchette->etudiant_id,
                ]);
                $erreursIgnorees++;
                continue;
            }

            foreach ($copiesCorrespondantes as $copie) {
                // IMPORTANT : Vérifier l'existence pour cette session spécifique
                $resultatExiste = ResultatFusion::where('examen_id', $examenId)
                    ->where('etudiant_id', $manchette->etudiant_id)
                    ->where('ec_id', $copie->ec_id)
                    ->where('session_exam_id', $sessionId) // FILTRER PAR SESSION
                    ->exists();

                if ($resultatExiste) {
                    continue;
                }

                $codeAnonymatRecord = CodeAnonymat::firstOrCreate(
                    [
                        'code_complet' => $codeAnonymat,
                        'examen_id' => $examenId,
                        'ec_id' => $copie->ec_id,
                    ],
                    [
                        'sequence' => $this->extraireSequence($codeAnonymat),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // IMPORTANT : Créer avec session_exam_id
                ResultatFusion::create([
                    'etudiant_id' => $manchette->etudiant_id,
                    'examen_id' => $examenId,
                    'code_anonymat_id' => $codeAnonymatRecord->id,
                    'ec_id' => $copie->ec_id,
                    'note' => $copie->note,
                    'genere_par' => Auth::id(),
                    'statut' => ResultatFusion::STATUT_VERIFY_1,
                    'etape_fusion' => 1,
                    'session_exam_id' => $sessionId, // IMPORTANT
                ]);

                $resultatsGeneres++;
            }
        }

        if ($resultatsGeneres === 0) {
            \Log::warning('Aucun résultat généré lors de l\'étape 1', [
                'examen_id' => $examenId,
                'session_exam_id' => $sessionId,
                'erreurs_ignorees' => $erreursIgnorees,
            ]);
            return [
                'success' => false,
                'message' => "Aucune donnée fusionnée. Erreurs ignorées : $erreursIgnorees.",
            ];
        }

        \Log::info('Étape 1 de fusion terminée pour session', [
            'examen_id' => $examenId,
            'session_exam_id' => $sessionId,
            'resultats_generes' => $resultatsGeneres,
            'erreurs_ignorees' => $erreursIgnorees,
        ]);

        return [
            'success' => true,
            'resultats_generes' => $resultatsGeneres,
            'erreurs_ignorees' => $erreursIgnorees,
        ];
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

        $resultats = ResultatFusion::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId) // FILTRER PAR SESSION
            ->where('statut', ResultatFusion::STATUT_VERIFY_1)
            ->where('etape_fusion', 1)
            ->get();

        if ($resultats->isEmpty()) {
            Log::warning('Aucun résultat à valider pour l\'étape 2', [
                'examen_id' => $examenId,
                'session_id' => $sessionId,
            ]);
            return [
                'success' => false,
                'message' => 'Aucun résultat à valider à l\'étape 1 pour cette session.',
            ];
        }

        $resultatsValides = 0;
        foreach ($resultats as $resultat) {
            if ($this->validerResultat($resultat)) {
                $resultat->changerStatut(ResultatFusion::STATUT_VERIFY_2, Auth::id());
                $resultat->etape_fusion = 2;
                $resultat->save();
                $resultatsValides++;
            }
        }

        Log::info('Étape 2 de fusion terminée pour session', [
            'examen_id' => $examenId,
            'session_id' => $sessionId,
            'resultats_valides' => $resultatsValides,
        ]);

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

        $resultats = ResultatFusion::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId) // FILTRER PAR SESSION
            ->where('statut', ResultatFusion::STATUT_VERIFY_2)
            ->where('etape_fusion', 2)
            ->get();

        if ($resultats->isEmpty()) {
            Log::warning('Aucun résultat à traiter pour l\'étape 3', [
                'examen_id' => $examenId,
                'session_id' => $sessionId,
            ]);
            return [
                'success' => false,
                'message' => 'Aucun résultat à traiter à l\'étape 2 pour cette session.',
            ];
        }

        $resultatsFinalises = 0;
        foreach ($resultats as $resultat) {
            if ($this->verifierResultatEtape3($resultat)) {
                $resultat->changerStatut(ResultatFusion::STATUT_VERIFY_3, Auth::id());
                $resultat->etape_fusion = 3;
                $resultat->save();
                $resultatsFinalises++;
            }
        }

        Log::info('Étape 3 de fusion terminée pour session', [
            'examen_id' => $examenId,
            'session_id' => $sessionId,
            'resultats_traites' => $resultatsFinalises,
        ]);

        return [
            'success' => true,
            'resultats_traites' => $resultatsFinalises,
        ];
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

                Log::info('Résultat transféré avec succès', [
                    'fusion_id' => $resultatFusion->id,
                    'final_id' => $resultatFinal->id,
                    'etudiant_id' => $resultatFusion->etudiant_id,
                    'session_id' => $sessionActive->id,
                    'session_type' => $sessionActive->type
                ]);
            }

            // CORRECTION 5: Calcul des décisions selon le type de session
            foreach (array_keys($etudiantsTraites) as $etudiantId) {
                // Calculer la décision selon le type de session
                if ($sessionActive->type === 'Rattrapage') {
                    $decision = ResultatFinal::determinerDecisionRattrapage($etudiantId, $sessionActive->id);
                } else {
                    $decision = ResultatFinal::determinerDecisionPremiereSession($etudiantId, $sessionActive->id);
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

                Log::info("Décision calculée pour session", [
                    'etudiant_id' => $etudiantId,
                    'session_id' => $sessionActive->id,
                    'session_type' => $sessionActive->type,
                    'decision' => $decision,
                    'nb_resultats_mis_a_jour' => $resultatsEtudiantFinaux->count()
                ]);
            }

            DB::commit();

            Log::info('Transfert terminé avec succès', [
                'session_id' => $sessionActive->id,
                'session_type' => $sessionActive->type,
                'examen_id' => $examenId,
                'resultats_transferes' => $resultatsTransférés,
                'etudiants_traites' => count($etudiantsTraites)
            ]);

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

            Log::info('Résultats annulés pour session', [
                'examen_id' => $examenId,
                'session_id' => $sessionId,
                'resultats_annules' => $updatedCount,
                'motif' => $motifAnnulation,
            ]);

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
