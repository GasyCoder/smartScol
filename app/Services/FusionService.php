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

            $validation = $this->validateData($examenId);
            if (!$validation['valid']) {
                Log::warning('Vérification de cohérence échouée', [
                    'examen_id' => $examenId,
                    'issues' => $validation['issues'],
                ]);
                return [
                    'success' => false,
                    'message' => 'Données invalides détectées',
                    'stats' => ['total' => 0, 'complets' => 0, 'incomplets' => 0],
                    'data' => [],
                    'erreurs_coherence' => $validation['issues'],
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
                    'erreurs_coherence' => ['Aucune session active'],
                ];
            }

            $etudiants = Etudiant::where('niveau_id', $examen->niveau_id)
                ->where('parcours_id', $examen->parcours_id)
                ->where('is_active', true)
                ->get();

            $totalEtudiants = $etudiants->count();

            $resultatsExistants = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->exists();

            $rapport = [];
            $stats = ['total' => 0, 'complets' => 0, 'incomplets' => 0];

            if ($resultatsExistants) {
                $rapport = $this->analyserResultatsExistants($examenId, $totalEtudiants);
            } else {
                $rapport = $this->analyserDonneesPrefusion($examenId, $totalEtudiants, $etudiants);
            }

            foreach ($rapport as $item) {
                $stats[$item['complet'] ? 'complets' : 'incomplets']++;
            }
            $stats['total'] = count($rapport);

            return [
                'success' => true,
                'stats' => $stats,
                'data' => $rapport,
                'erreurs_coherence' => [],
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
                'erreurs_coherence' => ['Erreur système : ' . $e->getMessage()],
            ];
        }
    }



    /**
     * Analyse les résultats existants avec distinction présents/absents
     */
    private function analyserResultatsExistants($examenId, $totalEtudiants)
    {
        $sessionActive = SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->first();

        if (!$sessionActive) {
            return [];
        }

        $resultats = ResultatFusion::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->select('id', 'etudiant_id', 'ec_id', 'note', 'statut')
            ->with(['etudiant', 'ec'])
            ->get();

        $rapport = [];

        foreach ($resultats->groupBy('ec_id') as $ecId => $resultatsEc) {
            $ec = $resultatsEc->first()->ec;
            if (!$ec) {
                continue;
            }

            $donneesPresence = $this->getPresenceDataForEC($examenId, $sessionActive, $ec->id);
            $expectedPresents = $donneesPresence['etudiants_presents'];
            $expectedAbsents = $donneesPresence['etudiants_absents'];
            $totalInscrits = $donneesPresence['total_inscrits'];

            if ($totalInscrits === 0) {
                $totalInscrits = $totalEtudiants;
                $expectedPresents = $totalEtudiants;
                $expectedAbsents = 0;
            }

            $nbManchettes = Manchette::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->whereHas('codeAnonymat', function($q) use ($ec) {
                    $q->where('ec_id', $ec->id);
                })
                ->count();

            $nbResultats = $resultatsEc->whereNotNull('note')->count();

            $totalAttendu = $expectedPresents + $expectedAbsents;
            
            $pctManchettes = $totalAttendu > 0 ? round(($nbManchettes / $totalAttendu) * 100) : 0;
            $pctResultats = $totalAttendu > 0 ? round(($nbResultats / $totalAttendu) * 100) : 0;
            $pctSync = min($pctManchettes, $pctResultats);

            $complet = ($pctManchettes >= 100 && $pctResultats >= 100);

            $rapport[] = [
                'ec_id' => $ecId,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                
                'presents' => $expectedPresents,
                'absents' => $expectedAbsents,
                'manchettes' => $nbManchettes,
                'copies' => $nbResultats,
                
                'complet' => $complet,
                'pct_sync' => $pctSync,
                
                'total_inscrits' => $totalAttendu,
                'session_type' => $sessionActive->type,
            ];
        }

        return $rapport;
    }

    /**
     * Analyse les données avant fusion avec logique CORRIGÉE présents/absents
     */
    private function analyserDonneesPrefusion($examenId, $totalEtudiants, Collection $etudiants)
    {
        $sessionActive = SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->first();

        if (!$sessionActive) {
            return [];
        }

        if ($sessionActive->type === 'Rattrapage') {
            return $this->analyserDonneesPrefusionRattrapage($examenId, $totalEtudiants, $etudiants, $sessionActive);
        }

        $ecIdsExamen = DB::table('examen_ec')
            ->where('examen_id', $examenId)
            ->pluck('ec_id')
            ->toArray();

        if (empty($ecIdsExamen)) {
            return [];
        }

        $ecIdsValides = EC::whereIn('id', $ecIdsExamen)
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (empty($ecIdsValides)) {
            return [];
        }

        $copies = Copie::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->whereNotNull('code_anonymat_id')
            ->whereIn('ec_id', $ecIdsValides)
            ->with(['ec', 'codeAnonymat'])
            ->get();

        $ecs = EC::whereIn('id', $ecIdsValides)->get();

        if ($ecs->isEmpty()) {
            return [];
        }

        $rapport = [];

        foreach ($ecs as $ec) {
            // Données de présence
            $donneesPresence = $this->getPresenceDataForEC($examenId, $sessionActive, $ec->id);
            $expectedPresents = $donneesPresence['etudiants_presents'];
            $expectedAbsents = $donneesPresence['etudiants_absents'];
            $totalInscrits = $donneesPresence['total_inscrits'];

            // Fallback si pas de données de présence
            if ($totalInscrits === 0) {
                $totalInscrits = $totalEtudiants;
                $expectedPresents = $totalEtudiants;
                $expectedAbsents = 0;
            }

            // Comptage SIMPLE des manchettes
            $nbManchettes = Manchette::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->whereNotNull('code_anonymat_id')
                ->whereHas('codeAnonymat', function ($query) use ($ec) {
                    $query->where('ec_id', $ec->id)
                        ->whereNotNull('code_complet')
                        ->where('code_complet', '!=', '');
                })
                ->count();

            // Comptage SIMPLE des copies
            $nbCopies = $copies->where('ec_id', $ec->id)->count();

            // Total attendu = Présents + Absents
            $totalAttendu = $expectedPresents + $expectedAbsents;

            // Calcul pourcentage de synchronisation
            $pctManchettes = $totalAttendu > 0 ? round(($nbManchettes / $totalAttendu) * 100) : 0;
            $pctCopies = $totalAttendu > 0 ? round(($nbCopies / $totalAttendu) * 100) : 0;
            $pctSync = min($pctManchettes, $pctCopies);

            // Complet si manchettes ET copies >= 100%
            $complet = ($pctManchettes >= 100 && $pctCopies >= 100);

            $rapport[] = [
                'ec_id' => $ec->id,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                
                // Données simples
                'presents' => $expectedPresents,
                'absents' => $expectedAbsents,
                'manchettes' => $nbManchettes,
                'copies' => $nbCopies,
                
                // Statut
                'complet' => $complet,
                'pct_sync' => $pctSync,
                
                // Pour compatibilité
                'total_inscrits' => $totalAttendu,
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
        // 1. PRIORITÉ : Présence spécifique à l'EC
        $statsEC = PresenceExamen::getStatistiquesEC($examenId, $sessionActive->id, $ecId);
        
        if ($statsEC['source'] === 'presence_ec_specifique') {
            return [
                'etudiants_presents' => $statsEC['presents'],
                'etudiants_absents' => $statsEC['absents'],
                'total_inscrits' => $statsEC['total_attendu'],
                'source' => $statsEC['source']
            ];
        }

        // 2. FALLBACK : Présence globale de l'examen
        $statsGlobales = PresenceExamen::getStatistiquesExamen($examenId, $sessionActive->id);
        
        return [
            'etudiants_presents' => $statsGlobales['presents'],
            'etudiants_absents' => $statsGlobales['absents'],
            'total_inscrits' => $statsGlobales['total_attendu'],
            'source' => $statsGlobales['source']
        ];
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
        
        if ($sessionActive && $sessionActive->type === 'Rattrapage') {
            return $this->executerEtape1Rattrapage($examenId, $sessionId, $sessionActive);
        }

        // Validation préalable des EC
        $ecIdsExamen = DB::table('examen_ec')
            ->where('examen_id', $examenId)
            ->pluck('ec_id')
            ->toArray();

        if (empty($ecIdsExamen)) {
            return [
                'success' => false,
                'message' => 'Aucun EC associé à cet examen.',
            ];
        }

        $ecIdsValides = EC::whereIn('id', $ecIdsExamen)
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $ecIdsInvalides = array_diff($ecIdsExamen, $ecIdsValides);
        
        if (!empty($ecIdsInvalides)) {
            Log::error('EC invalides détectés', [
                'examen_id' => $examenId,
                'ec_invalides' => $ecIdsInvalides
            ]);
            
            return [
                'success' => false,
                'message' => 'Certains EC de cet examen sont invalides ou supprimés (IDs: ' . 
                            implode(', ', $ecIdsInvalides) . '). Supprimez les données associées avant de continuer.',
            ];
        }

        // Récupération des manchettes avec validation EC
        $manchettes = Manchette::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function ($query) use ($ecIdsValides) {
                $query->whereNotNull('code_complet')
                    ->where('code_complet', '!=', '')
                    ->whereIn('ec_id', $ecIdsValides);
            })
            ->with(['etudiant', 'codeAnonymat'])
            ->get();

        if ($manchettes->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Aucune manchette valide trouvée pour cet examen dans cette session.',
            ];
        }

        // Récupération des copies avec validation EC stricte
        $copies = Copie::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->whereNotNull('code_anonymat_id')
            ->whereIn('ec_id', $ecIdsValides)
            ->whereHas('codeAnonymat', function ($query) use ($ecIdsValides) {
                $query->whereNotNull('code_complet')
                    ->where('code_complet', '!=', '')
                    ->whereIn('ec_id', $ecIdsValides);
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

        // Vérification supplémentaire : toutes les copies ont un EC valide
        $copiesAvecECInvalides = $copies->filter(function($copie) use ($ecIdsValides) {
            return !in_array($copie->ec_id, $ecIdsValides);
        });

        if ($copiesAvecECInvalides->isNotEmpty()) {
            $idsInvalides = $copiesAvecECInvalides->pluck('ec_id')->unique()->toArray();
            
            return [
                'success' => false,
                'message' => 'Certaines copies référencent des EC invalides (IDs: ' . 
                            implode(', ', $idsInvalides) . '). Nettoyez ces copies avant la fusion.',
            ];
        }

        // Vérification des résultats existants
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
                    // Validation finale de l'EC avant insertion
                    if (!in_array($copie->ec_id, $ecIdsValides)) {
                        $erreursIgnorees++;
                        Log::warning('Copie avec EC invalide ignorée', [
                            'copie_id' => $copie->id,
                            'ec_id' => $copie->ec_id
                        ]);
                        continue;
                    }

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
                    try {
                        ResultatFusion::insert($resultatsAInserer);
                        $resultatsAInserer = [];
                        usleep(10000);
                    } catch (\Exception $e) {
                        Log::error('Erreur insertion batch ResultatFusion', [
                            'error' => $e->getMessage(),
                            'examen_id' => $examenId
                        ]);
                        throw $e;
                    }
                }
            }
        }

        if (!empty($resultatsAInserer)) {
            try {
                ResultatFusion::insert($resultatsAInserer);
            } catch (\Exception $e) {
                Log::error('Erreur insertion finale ResultatFusion', [
                    'error' => $e->getMessage(),
                    'examen_id' => $examenId
                ]);
                throw $e;
            }
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
     * Valide un résultat
     */
    private function validerResultat(ResultatFusion $resultat)
    {
        if (!$resultat->etudiant_id || !$resultat->ec_id || !$resultat->examen_id) {
            return false;
        }

        if ($resultat->note !== null && ($resultat->note < 0 || $resultat->note > 20)) {
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
            }

            DB::commit();

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

        $examen = Examen::find($examenId);
        if (!$examen) {
            $issues[] = "L'examen ID $examenId n'existe pas";
            return ['valid' => false, 'issues' => $issues];
        }

        // Récupération des EC de l'examen
        $ecIdsExamen = DB::table('examen_ec')
            ->where('examen_id', $examenId)
            ->pluck('ec_id')
            ->toArray();

        if (empty($ecIdsExamen)) {
            $issues[] = "Aucun EC associé à cet examen";
            return ['valid' => false, 'issues' => $issues];
        }

        // Vérification que tous les EC existent et sont actifs
        $ecIdsValides = EC::whereIn('id', $ecIdsExamen)
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $ecIdsInvalides = array_diff($ecIdsExamen, $ecIdsValides);
        
        if (!empty($ecIdsInvalides)) {
            $issues[] = "EC invalides ou supprimés dans l'examen (IDs: " . implode(', ', $ecIdsInvalides) . ")";
        }

        // Vérification des copies avec EC invalides
        $nbCopiesInvalides = Copie::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionActive->id)
            ->whereNotNull('ec_id')
            ->whereNotIn('ec_id', $ecIdsValides)
            ->count();

        if ($nbCopiesInvalides > 0) {
            $issues[] = "$nbCopiesInvalides copie(s) avec EC invalide détectée(s)";
        }

        // Vérification des codes d'anonymat avec EC invalides
        $nbCodesInvalides = CodeAnonymat::where('examen_id', $examenId)
            ->whereNotNull('ec_id')
            ->whereNotIn('ec_id', $ecIdsValides)
            ->count();

        if ($nbCodesInvalides > 0) {
            $issues[] = "$nbCodesInvalides code(s) d'anonymat avec EC invalide détecté(s)";
        }

        if (!empty($issues)) {
            return ['valid' => false, 'issues' => $issues];
        }

        // Validation selon le type de session
        if ($sessionActive->type === 'Rattrapage') {
            return ['valid' => true];
        } else {
            $codesAvecManchettes = DB::table('manchettes as m')
                ->join('codes_anonymat as ca', 'm.code_anonymat_id', '=', 'ca.id')
                ->where('m.examen_id', $examenId)
                ->where('m.session_exam_id', $sessionActive->id)
                ->whereIn('ca.ec_id', $ecIdsValides)
                ->count();

            if ($codesAvecManchettes === 0) {
                $issues[] = "Aucune manchette saisie pour cet examen dans la session {$sessionActive->type}";
                return ['valid' => false, 'issues' => $issues];
            }
        }

        return ['valid' => true];
    }
}