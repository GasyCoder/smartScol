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

            try {
                $donneesPresence = $this->getPresenceDataForEC($examenId, $sessionActive, $ec->id);
                
                // ✅ CORRECTION : Utiliser les bonnes clés
                $expectedPresents = $donneesPresence['etudiants_presents'] ?? 0;
                $expectedAbsents = $donneesPresence['etudiants_absents'] ?? 0;
                $totalInscrits = $donneesPresence['total_inscrits'] ?? 0;

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
                
            } catch (\Exception $e) {
                \Log::error('Erreur analyse résultats existants EC', [
                    'ec_id' => $ecId,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
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
        try {
            // Récupérer les données de présence
            $donneesPresence = $this->getPresenceDataForEC($examenId, $sessionActive, $ec->id);
            $expectedPresents = $donneesPresence['etudiants_presents'] ?? 0;
            $expectedAbsents = $donneesPresence['etudiants_absents'] ?? 0;
            $totalInscrits = $donneesPresence['total_inscrits'] ?? 0;

            // Fallback si pas de données de présence
            if ($totalInscrits === 0) {
                $totalInscrits = $totalEtudiants;
                $expectedPresents = $totalEtudiants;
                $expectedAbsents = 0;
            }

            // Compter TOUTES les manchettes (présents + absents)
            $nbManchettes = Manchette::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->whereNotNull('code_anonymat_id')
                ->whereHas('codeAnonymat', function ($query) use ($ec) {
                    $query->where('ec_id', $ec->id)
                        ->whereNotNull('code_complet')
                        ->where('code_complet', '!=', '');
                })
                ->count();

            // Compter séparément les manchettes présentes
            $manchettesPresentes = Manchette::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->whereHas('codeAnonymat', function ($query) use ($ec) {
                    $query->where('ec_id', $ec->id)
                        ->whereNotNull('code_complet')
                        ->where('code_complet', '!=', '')
                        ->where(function($q) {
                            $q->where('is_absent', false)->orWhereNull('is_absent');
                        });
                })
                ->count();

            // Calculer les manchettes absentes
            $manchettesAbsentes = $nbManchettes - $manchettesPresentes;

            // Comptage copies
            $nbCopies = $copies->where('ec_id', $ec->id)->count();

            // Total attendu
            $totalAttendu = $expectedPresents + $expectedAbsents;

            // Calcul pourcentages
            $pctManchettes = $totalAttendu > 0 ? round(($nbManchettes / $totalAttendu) * 100) : 0;
            $pctCopies = $nbManchettes > 0 ? round(($nbCopies / $nbManchettes) * 100) : 0;
            $pctSync = min($pctManchettes, $pctCopies);

            // Complet si : manchettes totales >= attendu ET copies = manchettes
            $complet = ($nbManchettes >= $totalAttendu) && ($nbCopies >= $nbManchettes);

            $rapport[] = [
                'ec_id' => $ec->id,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                
                // Données de présence
                'presents' => $expectedPresents,
                'absents' => $expectedAbsents,
                
                // Manchettes (total + détails)
                'manchettes' => $nbManchettes,
                'manchettes_presentes' => $manchettesPresentes,
                'manchettes_absentes' => $manchettesAbsentes,
                
                // Copies
                'copies' => $nbCopies,
                
                // Statut
                'complet' => $complet,
                'pct_sync' => $pctSync,
                
                // Compatibilité
                'total_inscrits' => $totalAttendu,
                'session_type' => $sessionActive->type,
            ];

        } catch (\Exception $e) {
            Log::error('Erreur analyse EC dans analyserDonneesPrefusion', [
                'ec_id' => $ec->id,
                'examen_id' => $examenId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Ajouter quand même avec valeurs par défaut
            $rapport[] = [
                'ec_id' => $ec->id,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                'presents' => 0,
                'absents' => 0,
                'manchettes' => 0,
                'manchettes_presentes' => 0,
                'manchettes_absentes' => 0,
                'copies' => 0,
                'complet' => false,
                'pct_sync' => 0,
                'total_inscrits' => 0,
                'session_type' => $sessionActive->type,
                'error' => 'Erreur lors de l\'analyse : ' . $e->getMessage()
            ];
        }
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
                'etudiants_presents' => $statsEC['presents'] ?? 0,
                'etudiants_absents' => $statsEC['absents'] ?? 0,  // ✅ CORRECTION: Utiliser le bon nom
                'total_inscrits' => $statsEC['total_attendu'] ?? 0,
                'source' => $statsEC['source']
            ];
        }

        // 2. FALLBACK : Présence globale de l'examen
        $statsGlobales = PresenceExamen::getStatistiquesExamen($examenId, $sessionActive->id);
        
        return [
            'etudiants_presents' => $statsGlobales['presents'] ?? 0,
            'etudiants_absents' => $statsGlobales['absents'] ?? 0,  // ✅ CORRECTION: Utiliser le bon nom
            'total_inscrits' => $statsGlobales['total_attendu'] ?? 0,
            'source' => $statsGlobales['source']
        ];
    }

    /**
     * Effectue la fusion des manchettes et copies
     */
    public function fusionner(int $examenId, int $sessionId, bool $force = false)
    {
        // On travaille TOUJOURS sur la session passée en paramètre
        $sessionActive = SessionExam::findOrFail($sessionId);

        try {
            set_time_limit(300);
            ini_set('memory_limit', '512M');

            // On cherche l'étape max déjà réalisée pour CET examen + CETTE session
            $currentEtape = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->max('etape_fusion') ?? 0;

            if ($currentEtape >= 4 && !$force) {
                return [
                    'success' => false,
                    'message' => "La fusion est déjà terminée pour la session {$sessionActive->type}.",
                ];
            }

            $nextEtape = $force ? 1 : ($currentEtape + 1);

            DB::beginTransaction();

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
                    ->where('session_exam_id', $sessionActive->id)
                    ->whereIn('statut', [
                        ResultatFusion::STATUT_VERIFY_1,
                        ResultatFusion::STATUT_VERIFY_2,
                        ResultatFusion::STATUT_VERIFY_3,
                    ])
                    ->count(),
                'etape'        => $nextEtape,
                'session_type' => $sessionActive->type,
            ];

            DB::commit();

            return [
                'success'      => true,
                'message'      => "Fusion étape $nextEtape terminée avec succès pour la session {$sessionActive->type}.",
                'statistiques' => $statistiques,
                'etape'        => $nextEtape,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la fusion', [
                'examen_id' => $examenId,
                'session_id'=> $sessionActive->id ?? null,
                'error'     => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la fusion : ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Étape 1 : Fusion des manchettes et copies - VERSION CORRIGÉE
     * Remplacer dans FusionService.php à partir de la ligne ~330
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

        $copiesParCode = $copies->groupBy('codeAnonymat.code_complet');
        $resultatsAInserer = [];
        $batchSize = 500;
        $resultatsGeneres = 0;
        $resultatsUpdates = 0;
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

                // ✅ CORRECTION PRINCIPALE : Utiliser upsert au lieu de insert
                if (count($resultatsAInserer) >= $batchSize) {
                    try {
                        // Utiliser upsert pour gérer les doublons automatiquement
                        DB::table('resultats_fusion')->upsert(
                            $resultatsAInserer,
                            // Colonnes de la contrainte unique
                            ['etudiant_id', 'examen_id', 'ec_id', 'session_exam_id'],
                            // Colonnes à mettre à jour en cas de conflit
                            ['code_anonymat_id', 'note', 'statut', 'etape_fusion', 'genere_par', 'updated_at']
                        );
                        
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

        // ✅ CORRECTION : Insertion finale avec upsert
        if (!empty($resultatsAInserer)) {
            try {
                DB::table('resultats_fusion')->upsert(
                    $resultatsAInserer,
                    ['etudiant_id', 'examen_id', 'ec_id', 'session_exam_id'],
                    ['code_anonymat_id', 'note', 'statut', 'etape_fusion', 'genere_par', 'updated_at']
                );
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
    private function executerEtape1Rattrapage(int $examenId, int $sessionRattrapageId, SessionExam $sessionRattrapage)
    {
        try {
            // 1. Session Normale liée à la même année
            $sessionNormale = SessionExam::where('annee_universitaire_id', $sessionRattrapage->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                return ['success' => false, 'message' => 'Session normale introuvable pour cette année universitaire.'];
            }

            // 2. Étudiants éligibles au rattrapage (décision ou moyenne < 10)
            $eligibles = $this->getEtudiantsEligiblesRattrapage(
                $examenId,
                $sessionNormale->id,
                $sessionRattrapageId
            );

            if ($eligibles->isEmpty()) {
                return ['success' => false, 'message' => 'Aucun étudiant éligible au rattrapage.'];
            }

            $etudiantsEligiblesIds = $eligibles->pluck('etudiant_id')->toArray();

            // 3. Résultats de la session Normale (base de comparaison)
            $resultatsNormale = ResultatFinal::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionNormale->id)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->whereIn('etudiant_id', $etudiantsEligiblesIds)
                ->get()
                ->keyBy(function ($item) {
                    return $item->etudiant_id . '_' . $item->ec_id;
                });

            if ($resultatsNormale->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Aucune note de session normale trouvée pour les étudiants éligibles.',
                ];
            }

            // 4. Copies RATTRAPAGE réelles (sessionExamId = rattrapage)
            $copiesRattrapage = Copie::where('copies.examen_id', $examenId)
                ->where('copies.session_exam_id', $sessionRattrapageId)
                ->whereNotNull('copies.note')
                ->whereNotNull('copies.code_anonymat_id')
                ->join('manchettes as m', function ($join) use ($sessionRattrapageId, $examenId) {
                    $join->on('m.code_anonymat_id', '=', 'copies.code_anonymat_id')
                        ->where('m.session_exam_id', $sessionRattrapageId)
                        ->where('m.examen_id', $examenId);
                })
                ->join('etudiants as e', 'm.etudiant_id', '=', 'e.id')
                ->select(
                    'copies.id',
                    'copies.ec_id',
                    'copies.note',
                    'copies.code_anonymat_id',
                    'm.etudiant_id'
                )
                ->get()
                ->keyBy(function ($copie) {
                    return $copie->etudiant_id . '_' . $copie->ec_id;
                });

            // 5. Résultats fusion de cette session déjà existants (à ne pas dupliquer)
            $existants = ResultatFusion::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionRattrapageId)
                ->get()
                ->keyBy(function ($item) {
                    return $item->etudiant_id . '_' . $item->ec_id;
                });

            $batch = [];
            $compteur = 0;

            foreach ($resultatsNormale as $key => $resNormale) {
                [$etudiantId, $ecId] = explode('_', $key);

                // Ne pas regénérer un résultat déjà fusionné pour cette session
                if ($existants->has($key)) {
                    continue;
                }

                $noteNormale    = $resNormale->note;
                $noteRattrapage = null;
                $codeAnonymatId = $resNormale->code_anonymat_id; // fallback : code de la session normale

                // Si l'étudiant a EFFECTIVEMENT passé le rattrapage sur cet EC
                if ($copiesRattrapage->has($key)) {
                    $copie          = $copiesRattrapage->get($key);
                    $noteRattrapage = $copie->note;
                    $codeAnonymatId = $copie->code_anonymat_id;
                }

                // 🔥 Règle métier : toujours prendre la note de rattrapage si elle existe
                $noteFinale = $this->determinerMeilleureNote($noteNormale, $noteRattrapage);

                $batch[] = [
                    'etudiant_id'      => $etudiantId,
                    'examen_id'        => $examenId,
                    'ec_id'            => $ecId,
                    'code_anonymat_id' => $codeAnonymatId,
                    'note'             => $noteFinale,
                    'genere_par'       => Auth::id(),
                    'statut'           => ResultatFusion::STATUT_VERIFY_1,
                    'etape_fusion'     => 1,
                    'session_exam_id'  => $sessionRattrapageId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                $compteur++;
            }

            if ($compteur === 0) {
                return [
                    'success' => false,
                    'message' => 'Aucune ligne fusionnée pour la session de rattrapage.',
                ];
            }

            // Insertion / mise à jour en masse (clé unique par étudiant+EC+session)
            foreach (array_chunk($batch, 500) as $chunk) {
                DB::table('resultats_fusion')->upsert(
                    $chunk,
                    ['etudiant_id', 'examen_id', 'ec_id', 'session_exam_id'],
                    ['note', 'code_anonymat_id', 'statut', 'etape_fusion', 'genere_par', 'updated_at']
                );
            }

            return [
                'success'            => true,
                'resultats_generes'  => $compteur,
                'message'            => "$compteur notes fusionnées pour la session de rattrapage (note de rattrapage prioritaire).",
            ];
        } catch (\Exception $e) {
            Log::error('Erreur fusion rattrapage', [
                'examen_id'  => $examenId,
                'session_id' => $sessionRattrapageId,
                'error'      => $e->getMessage(),
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
        // Pas de rattrapage → on garde la normale
        if ($noteRattrapage === null) {
            return $noteNormale;
        }

        // Rattrapage = 0 → éliminatoire
        if ($noteRattrapage == 0) {
            return 0;
        }

        // Dès qu'il y a un rattrapage, on prend SA note, même si < normale
        return $noteRattrapage;
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

        $updated = ResultatFusion::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->where('statut', ResultatFusion::STATUT_VERIFY_1)
            ->where('etape_fusion', 1)
            ->update([
                'statut' => ResultatFusion::STATUT_VERIFY_2,
                'etape_fusion' => 2,
                'modifie_par' => Auth::id(),
                'updated_at' => now()
            ]);

        if ($updated === 0) {
            return ['success' => false, 'message' => 'Aucun résultat à valider'];
        }

        return ['success' => true, 'resultats_valides' => $updated];
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

        $updated = ResultatFusion::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->where('statut', ResultatFusion::STATUT_VERIFY_2)
            ->where('etape_fusion', 2)
            ->update([
                'statut' => ResultatFusion::STATUT_VERIFY_3,
                'etape_fusion' => 3,
                'modifie_par' => Auth::id(),
                'updated_at' => now()
            ]);

        if ($updated === 0) {
            return ['success' => false, 'message' => 'Aucun résultat à traiter'];
        }

        return ['success' => true, 'resultats_traites' => $updated];
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



    public function transfererResultatsOptimise(array $resultatFusionIds, int $generePar, $sessionActive)
    {
        try {
            set_time_limit(300);
            DB::beginTransaction();

            $resultatsFusion = ResultatFusion::whereIn('id', $resultatFusionIds)
                ->where('session_exam_id', $sessionActive->id)
                ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_3, ResultatFusion::STATUT_VALIDE])
                ->select('id', 'etudiant_id', 'examen_id', 'ec_id', 'code_anonymat_id', 'note')
                ->get();

            if ($resultatsFusion->isEmpty()) {
                DB::rollBack();
                return ['success' => false, 'message' => "Aucun résultat valide", 'resultats_transférés' => 0];
            }

            $examenId = $resultatsFusion->first()->examen_id;
            $now = now();

            // Vérifier existants EN MASSE
            $existingPairs = DB::table('resultats_finaux')
                ->where('examen_id', $examenId)
                ->where('session_exam_id', $sessionActive->id)
                ->select(DB::raw("CONCAT(etudiant_id, '-', ec_id) as pair_key"))
                ->pluck('pair_key')
                ->flip()
                ->toArray();

            $dataToInsert = [];
            $fusionIdsToValidate = [];
            $etudiantsTraites = [];

            foreach ($resultatsFusion as $rf) {
                $pairKey = "{$rf->etudiant_id}-{$rf->ec_id}";
                if (isset($existingPairs[$pairKey])) continue;

                $dataToInsert[] = [
                    'etudiant_id' => $rf->etudiant_id,
                    'examen_id' => $rf->examen_id,
                    'session_exam_id' => $sessionActive->id,
                    'code_anonymat_id' => $rf->code_anonymat_id,
                    'ec_id' => $rf->ec_id,
                    'note' => $rf->note,
                    'genere_par' => $generePar,
                    'statut' => ResultatFinal::STATUT_EN_ATTENTE,
                    'hash_verification' => hash('sha256', $rf->id . $rf->note . time()),
                    'fusion_id' => $rf->id,
                    'date_fusion' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $fusionIdsToValidate[] = $rf->id;
                $etudiantsTraites[$rf->etudiant_id] = true;
            }

            if (empty($dataToInsert)) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Tous les résultats existent déjà', 'resultats_transférés' => 0];
            }

            // Insertion EN MASSE
            $resultatsTransférés = 0;
            foreach (array_chunk($dataToInsert, 500) as $chunk) {
                DB::table('resultats_finaux')->insert($chunk);
                $resultatsTransférés += count($chunk);
            }

            // Marquer fusion comme VALIDE EN MASSE
            if (!empty($fusionIdsToValidate)) {
                ResultatFusion::whereIn('id', $fusionIdsToValidate)
                    ->update(['statut' => ResultatFusion::STATUT_VALIDE, 'modifie_par' => $generePar, 'updated_at' => $now]);
            }

            // Calculer décisions EN MASSE
            $etudiantIds = array_keys($etudiantsTraites);
            $decisions = $this->calculerDecisionsSQL($examenId, $sessionActive->id, $sessionActive->type, $etudiantIds);

            if (!empty($decisions)) {
                $this->appliquerDecisionsEnMasse($examenId, $sessionActive->id, $decisions);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Transfert effectué. $resultatsTransférés résultat(s) transféré(s)",
                'resultats_transférés' => $resultatsTransférés,
                'session_type' => $sessionActive->type,
                'etudiants_traites' => count($etudiantsTraites)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur transfert', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage(), 'resultats_transférés' => 0];
        }
    }

    // ✅ AJOUTER ces méthodes helper
    private function calculerDecisionsSQL($examenId, $sessionId, $sessionType, $etudiantIds)
    {
        if (empty($etudiantIds)) return [];

        $etudiantIdsStr = implode(',', $etudiantIds);

        if ($sessionType === 'Rattrapage') {
            $query = "
                SELECT etudiant_id,
                    CASE 
                        WHEN AVG(note) >= 10 THEN '" . ResultatFinal::DECISION_ADMIS . "'
                        ELSE '" . ResultatFinal::DECISION_REDOUBLANT . "'
                    END as decision
                FROM resultats_finaux
                WHERE examen_id = ? AND session_exam_id = ? AND etudiant_id IN ($etudiantIdsStr) AND statut = ?
                GROUP BY etudiant_id
            ";
        } else {
            $query = "
                SELECT etudiant_id,
                    CASE 
                        WHEN AVG(note) >= 10 THEN '" . ResultatFinal::DECISION_ADMIS . "'
                        WHEN AVG(note) >= 8 THEN '" . ResultatFinal::DECISION_RATTRAPAGE . "'
                        ELSE '" . ResultatFinal::DECISION_REDOUBLANT . "'
                    END as decision
                FROM resultats_finaux
                WHERE examen_id = ? AND session_exam_id = ? AND etudiant_id IN ($etudiantIdsStr) AND statut = ?
                GROUP BY etudiant_id
            ";
        }

        return DB::select($query, [$examenId, $sessionId, ResultatFinal::STATUT_EN_ATTENTE]);
    }



    private function appliquerDecisionsEnMasse($examenId, $sessionId, $decisions)
    {
        if (empty($decisions)) return;

        $cases = [];
        $etudiantIds = [];

        foreach ($decisions as $d) {
            $etudiantIds[] = $d->etudiant_id;
            $cases[] = "WHEN etudiant_id = {$d->etudiant_id} THEN '{$d->decision}'";
        }

        if (empty($etudiantIds)) return;

        $etudiantIdsStr = implode(',', $etudiantIds);
        $caseStr = implode(' ', $cases);

        DB::statement("
            UPDATE resultats_finaux 
            SET decision = CASE $caseStr END, updated_at = NOW()
            WHERE examen_id = $examenId AND session_exam_id = $sessionId AND etudiant_id IN ($etudiantIdsStr)
        ");
    }


}