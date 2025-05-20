<?php

namespace App\Services;

use App\Models\EC;
use App\Models\UE;
use App\Models\Copie;
use App\Models\Examen;
use App\Jobs\FusionJob;
use App\Models\Etudiant;
use App\Models\Resultat;
use App\Models\Manchette;
use Illuminate\Support\Str;
use App\Models\CodeAnonymat;
use App\Models\FusionOperation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\ReinitialiserFusionJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FusionService
{
    // Préfixe pour les clés de cache
    private $cachePrefix = 'fusion_results_';

    // Durée de cache en minutes
    private $cacheDuration = 60;

    /**
     * Détermine l'état actuel du processus de fusion pour un examen
     *
     * @param int $examen_id
     * @return string
     */
    public function getStatutActuel($examen_id)
    {
        // Vérifier l'état des résultats
        $resultatPublie = Resultat::where('examen_id', $examen_id)
            ->where('statut', Resultat::STATUT_PUBLIE)
            ->exists();

        $resultatValide = Resultat::where('examen_id', $examen_id)
            ->where('statut', Resultat::STATUT_VALIDE)
            ->exists();

        $resultatProvisoire = Resultat::where('examen_id', $examen_id)
            ->where('statut', Resultat::STATUT_PROVISOIRE)
            ->exists();

        // Déterminer l'état actuel
        if ($resultatPublie) {
            return FusionProcessStatus::PUBLICATION;
        } elseif ($resultatValide) {
            return FusionProcessStatus::VALIDATION;
        } elseif ($resultatProvisoire) {
            return FusionProcessStatus::FUSION_PROVISOIRE;
        } else {
            // Vérifier si la cohérence a été vérifiée via le cache
            $cacheKey = $this->cachePrefix . 'coherence_' . $examen_id;
            if (Cache::has($cacheKey)) {
                return FusionProcessStatus::COHERENCE_VERIFIEE;
            }

            return FusionProcessStatus::INITIAL;
        }
    }

    /**
     * Vérifie si une transition d'état est valide et possible
     *
     * @param int $examen_id
     * @param string $statutCible
     * @return array
     */
    public function verifierTransitionPossible($examen_id, $statutCible)
    {
        $statutActuel = $this->getStatutActuel($examen_id);

        if (!FusionProcessStatus::transitionAutorisee($statutActuel, $statutCible)) {
            return [
                'success' => false,
                'message' => "Transition impossible: $statutActuel → $statutCible",
                'statut_actuel' => $statutActuel
            ];
        }

        return [
            'success' => true,
            'statut_actuel' => $statutActuel,
            'statut_cible' => $statutCible
        ];
    }

    /**
     * Vérifie la cohérence des données avant la fusion
     *
     * @param int $examen_id ID de l'examen
     * @return array Rapport de cohérence par matière
     */
    public function verifierCoherence($examen_id)
    {
        // Créer une opération de fusion pour suivre le processus
        $operation = $this->creerOperation($examen_id, 'coherence');
        $operation->updateStatus('processing');

        try {
            $examen = Examen::findOrFail($examen_id);
            Log::info('Vérification de cohérence pour l\'examen ' . $examen_id, ['operation_id' => $operation->id]);

            // Utiliser une clé de cache unique pour cette vérification de cohérence
            $cacheKey = $this->cachePrefix . 'coherence_' . $examen_id;

            // Vérifier si nous avons déjà les résultats en cache
            if (Cache::has($cacheKey)) {
                $rapport = Cache::get($cacheKey);
                $operation->updateStatus('completed', ['rapport' => 'Rapport récupéré du cache']);
                return $rapport;
            }

            // Récupérer toutes les matières pour cet examen avec une seule requête
            $ecs = EC::with(['examens' => function($query) use ($examen_id) {
                $query->where('examens.id', $examen_id);
            }])->whereHas('examens', function($query) use ($examen_id) {
                $query->where('examens.id', $examen_id);
            })->get();

            $rapport = [];

            // Nombre total d'étudiants pour ce niveau et parcours
            $totalEtudiants = Etudiant::where('niveau_id', $examen->niveau_id)
                ->where('parcours_id', $examen->parcours_id)
                ->where('is_active', true)
                ->count();

            // Récupérer tous les codes d'anonymat de cet examen en une seule requête
            $tousLesCodes = CodeAnonymat::where('examen_id', $examen_id)
                ->get()
                ->groupBy('ec_id');

            // Récupérer toutes les manchettes de cet examen en une seule requête
            $toutesLesManchettes = Manchette::where('examen_id', $examen_id)
                ->with(['codeAnonymat', 'etudiant'])
                ->get()
                ->groupBy('code_anonymat_id');

            // Récupérer toutes les copies de cet examen en une seule requête
            $toutesLesCopies = Copie::where('examen_id', $examen_id)
                ->with(['codeAnonymat', 'ec'])
                ->get()
                ->groupBy('code_anonymat_id');

            // Liste des étudiants pour ce niveau/parcours
            $tousLesEtudiants = Etudiant::where('niveau_id', $examen->niveau_id)
                ->where('parcours_id', $examen->parcours_id)
                ->where('is_active', true)
                ->select('id', 'matricule', 'nom', 'prenom')
                ->get();

            // Analyser chaque EC
            foreach ($ecs as $ec) {
                // Codes d'anonymat pour cette matière
                $codes = $tousLesCodes->get($ec->id, collect());

                if ($codes->isEmpty()) {
                    // Pas de codes pour cette matière, continuer au prochain EC
                    continue;
                }

                // Récupérer les IDs des codes pour cette matière
                $codeIds = $codes->pluck('id')->toArray();

                // Manchettes pour ces codes
                $manchettes = collect();
                foreach ($codeIds as $codeId) {
                    if ($toutesLesManchettes->has($codeId)) {
                        $manchettes = $manchettes->merge($toutesLesManchettes->get($codeId));
                    }
                }

                // Copies pour cette matière
                $copies = collect();
                foreach ($codeIds as $codeId) {
                    if ($toutesLesCopies->has($codeId)) {
                        $copies = $copies->merge($toutesLesCopies->get($codeId));
                    }
                }

                // Codes sans manchettes
                $manchetteCodeIds = $manchettes->pluck('code_anonymat_id')->unique()->toArray();
                $codesSansManchettesIds = array_diff($codeIds, $manchetteCodeIds);
                $codesSansManchettes = $codes->whereIn('id', $codesSansManchettesIds);

                // Codes sans copies
                $copiesCodeIds = $copies->pluck('code_anonymat_id')->unique()->toArray();
                $codesSansCopiesIds = array_diff($codeIds, $copiesCodeIds);
                $codesSansCopies = $codes->whereIn('id', $codesSansCopiesIds);

                // Étudiants avec manchette pour cette matière
                $etudiantsAvecManchetteIds = $manchettes->pluck('etudiant_id')->unique()->toArray();
                $etudiantsAvecManchette = count($etudiantsAvecManchetteIds);

                // Étudiants sans manchette
                $etudiantsSansManchette = $totalEtudiants - $etudiantsAvecManchette;

                // Vérifier les étudiants qui n'ont pas de manchette mais devraient en avoir
                $etudiantsSansManchetteDetails = $tousLesEtudiants
                    ->whereNotIn('id', $etudiantsAvecManchetteIds)
                    ->toArray();

                $rapport[$ec->id] = [
                    'ec_id' => $ec->id,
                    'ec_nom' => $ec->nom,
                    'ec_abr' => $ec->abr,
                    'codes_count' => $codes->count(),
                    'manchettes_count' => $manchettes->count(),
                    'copies_count' => $copies->count(),
                    'total_etudiants' => $totalEtudiants,
                    'etudiants_avec_manchette' => $etudiantsAvecManchette,
                    'etudiants_sans_manchette' => $etudiantsSansManchette,
                    'etudiants_sans_manchette_details' => $etudiantsSansManchetteDetails,
                    'codes_sans_manchettes' => [
                        'count' => $codesSansManchettes->count(),
                        'codes' => $codesSansManchettes->pluck('code_complet')->toArray()
                    ],
                    'codes_sans_copies' => [
                        'count' => $codesSansCopies->count(),
                        'codes' => $codesSansCopies->pluck('code_complet')->toArray()
                    ],
                    'complet' => $manchettes->count() == $copies->count()
                        && $etudiantsSansManchette == 0
                        && $codesSansManchettes->count() == 0
                        && $codesSansCopies->count() == 0
                ];
            }

            // Calculer des statistiques pour le rapport
            $stats = [
                'total_ecs' => count($rapport),
                'complets' => collect($rapport)->where('complet', true)->count(),
                'incomplets' => collect($rapport)->where('complet', false)->count()
            ];

            // Enregistrement des statistiques pour le débogage
            Log::info('Rapport de cohérence généré', [
                'examen_id' => $examen_id,
                'operation_id' => $operation->id,
                'stats' => $stats
            ]);

            // Mettre en cache le résultat pour accélérer les futures vérifications
            Cache::put($cacheKey, $rapport, $this->cacheDuration);

            // Marquer l'opération comme terminée
            $operation->updateStatus('completed', [
                'rapport_stats' => $stats
            ]);

            return $rapport;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de cohérence', [
                'examen_id' => $examen_id,
                'operation_id' => $operation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Marquer l'opération comme échouée
            $operation->updateStatus('failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Vérifie si la fusion est possible en fonction du rapport de cohérence
     *
     * @param array $rapportCoherence
     * @return bool
     */
    public function estFusionPossible($rapportCoherence)
    {
        // Vérifier s'il y a des erreurs critiques qui empêcheraient la fusion
        // Par exemple, s'il y a des matières sans aucune copie ou manchette
        $matieresSansRien = collect($rapportCoherence)->filter(function ($matiere) {
            return $matiere['manchettes_count'] == 0 || $matiere['copies_count'] == 0;
        });

        // Si des matières n'ont ni copies ni manchettes, la fusion est impossible
        if ($matieresSansRien->count() > 0) {
            return false;
        }

        // On peut autoriser la fusion même s'il y a des petites incohérences
        // qui seront gérées comme des cas particuliers
        return true;
    }

    /**
     * Crée une nouvelle opération de fusion
     *
     * @param int $examen_id
     * @param string $type
     * @param array $params
     * @return FusionOperation
     */
    protected function creerOperation($examen_id, $type, $params = [])
    {
        $operation = new FusionOperation();
        $operation->id = (string) Str::uuid();
        $operation->examen_id = $examen_id;
        $operation->user_id = Auth::id() ?: 1; // Si pas d'utilisateur authentifié, utiliser l'admin
        $operation->type = $type;
        $operation->status = 'pending';
        $operation->parameters = $params;
        $operation->save();

        return $operation;
    }

    /**
     * Dispatch the fusion job
     *
     * @param int $examen_id ID de l'examen
     * @param bool $force Forcer la fusion même après validation
     * @return array Résultat du lancement de la fusion
     */
    public function fusionner($examen_id, $force = false)
    {
        Log::info('Dispatch fusion', ['examen_id' => $examen_id, 'force' => $force]);


        // Vérifier si une fusion est déjà en cours
        $lockKey = 'fusion_lock_' . $examen_id;

        if (Cache::has($lockKey)) {
            $lockedBy = Cache::get($lockKey);
            return [
                'success' => false,
                'message' => 'Une fusion est déjà en cours pour cet examen',
                'locked_by' => $lockedBy
            ];
        }

        // Acquérir le verrou
        Cache::put($lockKey, Auth::id() ?: 1, 600); // 10 minutes

        try {
            // Vérifier si l'examen existe
            $examen = Examen::findOrFail($examen_id);

            // Vérifier si les résultats sont déjà publiés
            $resultatsPublies = Resultat::where('examen_id', $examen_id)
                ->where('statut', Resultat::STATUT_PUBLIE)
                ->exists();

            if ($resultatsPublies && !$force) {
                Cache::forget($lockKey);
                return [
                    'success' => false,
                    'message' => 'Impossible de fusionner: les résultats sont déjà publiés',
                    'resultats' => collect(),
                    'statistiques' => []
                ];
            }

            // Créer une opération pour suivre le processus
            $operation = $this->creerOperation($examen_id, 'fusion', ['force' => $force]);

            // Dispatch the job
            FusionJob::dispatch($examen_id, Auth::id() ?: 1, $force, $operation->id);

            // Libérer le verrou
            Cache::forget($lockKey);

            return [
                'success' => true,
                'operation_id' => $operation->id,
                'message' => $force
                    ? 'Refusion en cours. Le processus se terminera en arrière-plan.'
                    : 'Fusion en cours. Le processus se terminera en arrière-plan.',
                'resultats' => collect(),
                'statistiques' => []
            ];
        } catch (\Exception $e) {
            // Libérer le verrou en cas d'erreur
            Cache::forget($lockKey);

            Log::error('Erreur lors du dispatch fusion', [
                'examen_id' => $examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du lancement de la fusion: ' . $e->getMessage(),
                'resultats' => collect(),
                'statistiques' => []
            ];
        }
    }


    /**
     * Calcule les moyennes des UEs et globales pour chaque étudiant
     *
     * @param int $examen_id ID de l'examen
     * @return void
     */
    protected function calculerMoyennes($examen_id)
    {
        // Récupérer tous les résultats groupés par étudiant
        $resultatsParEtudiant = Resultat::where('examen_id', $examen_id)
            ->with(['ec.ue'])
            ->get()
            ->groupBy('etudiant_id');

        foreach ($resultatsParEtudiant as $etudiantId => $resultatsEtudiant) {
            // Grouper les résultats par UE
            $resultatsParUE = $resultatsEtudiant->groupBy(function($resultat) {
                return $resultat->ec->ue_id;
            });

            $moyennesUE = [];
            $coefficientsTotaux = [];

            // Calculer la moyenne par UE
            foreach ($resultatsParUE as $ueId => $resultatsUE) {
                $sommePonderee = 0;
                $sommeCoefficients = 0;

                foreach ($resultatsUE as $resultat) {
                    $coefficient = $resultat->ec->coefficient;
                    $sommePonderee += $resultat->note * $coefficient;
                    $sommeCoefficients += $coefficient;
                }

                $moyenneUE = $sommeCoefficients > 0 ? $sommePonderee / $sommeCoefficients : 0;
                $moyennesUE[$ueId] = $moyenneUE;
                $coefficientsTotaux[$ueId] = $sommeCoefficients;
            }

            // Calculer la moyenne générale
            $sommePondereeGenerale = 0;
            $sommeCoefficientsGeneral = 0;

            foreach ($moyennesUE as $ueId => $moyenneUE) {
                // On peut utiliser les crédits (ECTS) de l'UE comme coefficient si disponible
                $ue = UE::find($ueId);
                $coefficient = $ue ? $ue->credits : $coefficientsTotaux[$ueId];

                $sommePondereeGenerale += $moyenneUE * $coefficient;
                $sommeCoefficientsGeneral += $coefficient;
            }

            $moyenneGenerale = $sommeCoefficientsGeneral > 0
                ? $sommePondereeGenerale / $sommeCoefficientsGeneral
                : 0;

            // Mettre à jour chaque résultat avec la moyenne UE et la moyenne générale
            foreach ($resultatsEtudiant as $resultat) {
                $ueId = $resultat->ec->ue_id;
                $resultat->moyenne_ue = $moyennesUE[$ueId] ?? null;
                $resultat->moyenne_generale = $moyenneGenerale;
                $resultat->save();
            }
        }
    }

    /**
     * Vérifie si une opération de fusion est en cours pour un examen
     *
     * @param int $examen_id ID de l'examen
     * @return bool
     */
    public function estFusionEnCours($examen_id)
    {
        return FusionOperation::where('examen_id', $examen_id)
            ->whereIn('type', ['fusion', 'reset', 'validation', 'publication'])
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
    }


    /**
     * Calcule et met en cache les statistiques des résultats
     *
     * @param int $examen_id ID de l'examen
     * @return array
     */
    public function calculerStatistiques($examen_id)
    {
        $cacheKey = 'resultats_stats_' . $examen_id;

        return Cache::remember($cacheKey, 60, function() use ($examen_id) {
            $totalResultats = Resultat::where('examen_id', $examen_id)->count();
            $statutResultats = Resultat::where('examen_id', $examen_id)
                ->selectRaw('statut, COUNT(*) as total')
                ->groupBy('statut')
                ->pluck('total', 'statut')
                ->toArray();

            $notesStats = Resultat::where('examen_id', $examen_id)
                ->selectRaw('COUNT(*) as total, AVG(note) as moyenne, MIN(note) as min, MAX(note) as max')
                ->first();

            $passRate = Resultat::where('examen_id', $examen_id)
                ->where('note', '>=', 10)
                ->count();

            if ($totalResultats > 0) {
                $passRate = round(($passRate / $totalResultats) * 100, 2);
            } else {
                $passRate = 0;
            }

            // Distribution des notes par plage
            $distribution = [
                '0-4' => Resultat::where('examen_id', $examen_id)
                    ->whereBetween('note', [0, 4.99])
                    ->count(),
                '5-9' => Resultat::where('examen_id', $examen_id)
                    ->whereBetween('note', [5, 9.99])
                    ->count(),
                '10-14' => Resultat::where('examen_id', $examen_id)
                    ->whereBetween('note', [10, 14.99])
                    ->count(),
                '15-20' => Resultat::where('examen_id', $examen_id)
                    ->whereBetween('note', [15, 20])
                    ->count()
            ];

            return [
                'total' => $totalResultats,
                'statuts' => $statutResultats,
                'notes' => $notesStats ? [
                    'moyenne' => round($notesStats->moyenne, 2),
                    'min' => $notesStats->min,
                    'max' => $notesStats->max
                ] : null,
                'passRate' => $passRate,
                'distribution' => $distribution,
                'etape_fusion' => $this->determinerEtapeFusion($examen_id)
            ];
        });
    }


    /**
     * Effectue la fusion des données (appelée par le job)
     *
     * @param int $examen_id
     * @param int $user_id
     * @param bool $force
     * @param string $operation_id
     * @return array
     */
    public function performFusion($examen_id, $user_id, $force = false, $operation_id = null)
    {
        // Récupérer l'opération si elle existe
        $operation = $operation_id ? FusionOperation::find($operation_id) : null;

        if ($operation) {
            $operation->started_at = now();
            $operation->status = 'processing';
            $operation->save();
        }

        try {
            DB::beginTransaction();

            // Log détaillé pour le débogage
            Log::info('Début performFusion', [
                'examen_id' => $examen_id,
                'user_id' => $user_id,
                'force' => $force,
                'operation_id' => $operation_id
            ]);

            // Déterminer l'étape de fusion si ce n'est pas un forçage
            $etapeFusion = $force ? 1 : $this->determinerEtapeFusion($examen_id);

            // Log l'étape de fusion
            Log::info('Étape de fusion déterminée', [
                'etape' => $etapeFusion,
                'examen_id' => $examen_id
            ]);

            // Vérifier d'abord si la fusion est possible
            $coherenceResult = $this->verifierCoherence($examen_id);
            if (!$this->estFusionPossible($coherenceResult)) {
                DB::rollBack();

                if ($operation) {
                    $operation->status = 'failed';
                    $operation->error_message = 'Impossible de fusionner: incohérences critiques détectées';
                    $operation->completed_at = now();
                    $operation->save();
                }

                return [
                    'success' => false,
                    'message' => 'Impossible de fusionner: incohérences critiques détectées',
                    'coherence' => $coherenceResult
                ];
            }

            // Supprimer les résultats existants si force = true
            if ($force) {
                Log::info('Suppression des résultats existants (force=true)', [
                    'examen_id' => $examen_id
                ]);

                $deleted = Resultat::where('examen_id', $examen_id)
                    ->whereIn('statut', [Resultat::STATUT_PROVISOIRE, Resultat::STATUT_VALIDE])
                    ->delete();

                Log::info('Résultats supprimés', ['count' => $deleted]);
            }

            // Récupérer toutes les données en une seule requête pour optimiser
            $manchettes = Manchette::with(['etudiant', 'codeAnonymat'])
                ->where('examen_id', $examen_id)
                ->get();

            $copies = Copie::with(['ec'])
                ->where('examen_id', $examen_id)
                ->get()
                ->keyBy(function($copie) {
                    return $copie->code_anonymat_id;
                });

            // Log le nombre de manchettes et copies trouvées
            Log::info('Données récupérées', [
                'manchettes_count' => $manchettes->count(),
                'copies_count' => $copies->count(),
            ]);

            // Création des résultats
            $resultats = [];
            $erreurs = [];

            foreach ($manchettes as $manchette) {
                $copie = $copies->get($manchette->code_anonymat_id);

                if ($copie) {
                    // Création ou mise à jour du résultat
                    $resultat = Resultat::updateOrCreate(
                        [
                            'examen_id' => $examen_id,
                            'etudiant_id' => $manchette->etudiant_id,
                            'ec_id' => $copie->ec_id
                        ],
                        [
                            'code_anonymat_id' => $manchette->code_anonymat_id,
                            'note' => $copie->note,
                            'genere_par' => $user_id,
                            'statut' => Resultat::STATUT_PROVISOIRE,
                            'operation_id' => $operation_id
                        ]
                    );

                    $resultats[] = $resultat;
                } else {
                    $erreurs[] = [
                        'type' => 'manchette_sans_copie',
                        'manchette_id' => $manchette->id,
                        'code_anonymat' => $manchette->codeAnonymat->code_complet,
                        'etudiant' => $manchette->etudiant->nom . ' ' . $manchette->etudiant->prenom
                    ];
                }
            }

            // Pour la 2ème étape et plus, calculer les moyennes
            if ($etapeFusion >= 2) {
                Log::info('Calcul des moyennes (étape >= 2)', [
                    'etape' => $etapeFusion,
                    'examen_id' => $examen_id
                ]);
                $this->calculerMoyennes($examen_id);
            }

            // Calculer les statistiques
            $stats = [
                'etape_fusion' => $etapeFusion,
                'total_manchettes' => $manchettes->count(),
                'total_copies' => $copies->count(),
                'resultats_generes' => count($resultats),
                'erreurs_count' => count($erreurs)
            ];

            DB::commit();

            Log::info('Fusion effectuée avec succès', [
                'examen_id' => $examen_id,
                'operation_id' => $operation_id,
                'etape_fusion' => $etapeFusion,
                'resultats_generes' => $stats['resultats_generes'],
                'erreurs_count' => $stats['erreurs_count']
            ]);

            // Mettre à jour le statut de l'opération
            if ($operation) {
                $operation->status = 'completed';
                $operation->result = [
                    'stats' => $stats,
                    'erreurs' => $erreurs
                ];
                $operation->completed_at = now();
                $operation->save();

                // Déclencher l'événement
                event(new FusionOperationCompleted($operation));
            }

            $messageEtape = '';
            switch ($etapeFusion) {
                case 1:
                    $messageEtape = 'Première fusion effectuée. ';
                    break;
                case 2:
                    $messageEtape = 'Deuxième fusion avec calcul des moyennes effectuée. ';
                    break;
                case 3:
                    $messageEtape = 'Dernière fusion (consolidation) effectuée. ';
                    break;
            }

            return [
                'success' => true,
                'operation_id' => $operation_id,
                'etape_fusion' => $etapeFusion,
                'message' => $force
                    ? 'Données refusionnées avec succès'
                    : $messageEtape . "{$stats['resultats_generes']} résultats générés avec {$stats['erreurs_count']} erreurs",
                'resultats' => $resultats,
                'erreurs' => $erreurs,
                'statistiques' => $stats
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Exception lors de la fusion', [
                'examen_id' => $examen_id,
                'operation_id' => $operation_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mettre à jour le statut de l'opération
            if ($operation) {
                $operation->status = 'failed';
                $operation->error_message = $e->getMessage();
                $operation->completed_at = now();
                $operation->save();

                // Déclencher l'événement même en cas d'échec
                event(new FusionOperationCompleted($operation));
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la fusion: ' . $e->getMessage(),
                'resultats' => [],
                'statistiques' => []
            ];
        }
    }

    /**
     * Dispatch the reset fusion job
     *
     * @param int $examen_id ID de l'examen
     * @return array Résultat de la réinitialisation
     */
    public function reinitialiserFusion($examen_id)
    {
        Log::info('Dispatch réinitialisation fusion', ['examen_id' => $examen_id]);

        try {
            // Vérifier si l'examen existe
            $examen = Examen::findOrFail($examen_id);

            // Vérifier si les résultats sont déjà publiés
            $resultatsPublies = Resultat::where('examen_id', $examen_id)
                ->where('statut', Resultat::STATUT_PUBLIE)
                ->exists();

            if ($resultatsPublies) {
                return [
                    'success' => false,
                    'message' => 'Impossible de réinitialiser: les résultats sont déjà publiés'
                ];
            }

            // Créer une opération pour suivre le processus
            $operation = $this->creerOperation($examen_id, 'reset');

            // Dispatch the job
            ReinitialiserFusionJob::dispatch($examen_id, Auth::id() ?: 1, $operation->id);

            return [
                'success' => true,
                'operation_id' => $operation->id,
                'message' => 'Réinitialisation en cours. Le processus se terminera en arrière-plan.',
                'count' => 0 // Count will be logged by the job
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du dispatch réinitialisation fusion', [
                'examen_id' => $examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du lancement de la réinitialisation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Effectue la réinitialisation de la fusion
     *
     * @param int $examen_id
     * @param int $user_id
     * @param string $operation_id
     * @return array
     */
    public function performResetFusion($examen_id, $user_id, $operation_id = null)
    {
        // Récupérer l'opération si elle existe
        $operation = $operation_id ? FusionOperation::find($operation_id) : null;

        if ($operation) {
            $operation->updateStatus('processing');
        }

        try {
            DB::beginTransaction();

            // Supprimer les résultats provisoires et validés par lots
            $count = 0;
            Resultat::where('examen_id', $examen_id)
                ->whereIn('statut', [Resultat::STATUT_PROVISOIRE, Resultat::STATUT_VALIDE])
                ->chunk(1000, function ($resultats) use (&$count) {
                    $count += $resultats->count();
                    Resultat::whereIn('id', $resultats->pluck('id'))->delete();
                });

            // Invalider les caches associés
            $this->invalidateCaches($examen_id);

            DB::commit();

            Log::info('Réinitialisation fusion effectuée', [
                'examen_id' => $examen_id,
                'operation_id' => $operation_id,
                'resultats_supprimes' => $count
            ]);

            // Mettre à jour le statut de l'opération
            if ($operation) {
                $operation->updateStatus('completed', [
                    'resultats_supprimes' => $count
                ]);
            }

            return [
                'success' => true,
                'message' => "$count résultats réinitialisés avec succès",
                'count' => $count
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la réinitialisation fusion', [
                'examen_id' => $examen_id,
                'operation_id' => $operation_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mettre à jour le statut de l'opération
            if ($operation) {
                $operation->updateStatus('failed', [
                    'error' => $e->getMessage()
                ]);
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Valide les résultats (passage de provisoire à validé)
     *
     * @param int $examen_id ID de l'examen
     * @return array Résultat de la validation
     */
    public function validerResultats($examen_id)
    {
        // Créer une opération pour suivre le processus
        $operation = $this->creerOperation($examen_id, 'validation');
        $operation->updateStatus('processing');

        try {
            DB::beginTransaction();

            // Vérifier la cohérence avant validation
            $rapport = $this->verifierCoherence($examen_id);
            $incomplets = collect($rapport)->where('complet', false)->count();
            if ($incomplets > 0) {
                DB::rollBack();

                Log::warning('Validation tentée avec données incomplètes', [
                    'examen_id' => $examen_id,
                    'operation_id' => $operation->id,
                    'matieres_incompletes' => $incomplets
                ]);

                $operation->updateStatus('failed', [
                    'message' => "Impossible de valider: $incomplets matières incomplètes"
                ]);

                return [
                    'success' => false,
                    'message' => "Impossible de valider: $incomplets matières incomplètes. Veuillez résoudre les erreurs."
                ];
            }

            // Mettre à jour les résultats par lots
            $count = 0;
            $timestamp = now();

            Resultat::where('examen_id', $examen_id)
                ->where('statut', Resultat::STATUT_PROVISOIRE)
                ->chunk(1000, function ($resultats) use (&$count, $timestamp, $operation) {
                    foreach ($resultats as $resultat) {
                        $resultat->changerStatut(Resultat::STATUT_VALIDE, Auth::id() ?: 1);
                        $resultat->operation_id = $operation->id;
                        $resultat->date_validation = $timestamp;
                        $resultat->save();
                        $count++;
                    }
                });

            DB::commit();

            Log::info('Validation des résultats', [
                'examen_id' => $examen_id,
                'operation_id' => $operation->id,
                'resultats_valides' => $count,
                'validé_par' => Auth::id() ?: 1
            ]);

            $this->invalidateCaches($examen_id);

            $operation->updateStatus('completed', [
                'resultats_valides' => $count
            ]);

            return [
                'success' => true,
                'count' => $count,
                'message' => "{$count} résultats validés avec succès"
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la validation des résultats', [
                'examen_id' => $examen_id,
                'operation_id' => $operation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $operation->updateStatus('failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Publie les résultats (passage de validé à publié)
     *
     * @param int $examen_id ID de l'examen
     * @param bool $estPACES Si c'est pour PACES 1ère année (pour traiter comme concours sans délibération)
     * @return array Résultat de la publication
     */
    public function publierResultats($examen_id, $estPACES = false)
    {
        // Créer une opération pour suivre le processus
        $operation = $this->creerOperation($examen_id, 'publication', ['estPACES' => $estPACES]);
        $operation->updateStatus('processing');

        try {
            DB::beginTransaction();

            // Vérifier si c'est une première session (publication directe)
            // ou une session de rattrapage (nécessite délibération)
            $examen = Examen::with('session', 'niveau')->findOrFail($examen_id);

            // Pour PACES 1ère année, traiter comme concours sans délibération
            if ($examen->session->type === 'Rattrapage' && !$estPACES && !$examen->niveau->is_concours) {
                DB::rollBack();

                $operation->updateStatus('failed', [
                    'message' => 'Les résultats de rattrapage nécessitent une délibération avant publication'
                ]);

                return [
                    'success' => false,
                    'message' => 'Les résultats de rattrapage nécessitent une délibération avant publication'
                ];
            }

            // Mettre à jour les résultats en masse
            $count = 0;
            $timestamp = now();

            Resultat::where('examen_id', $examen_id)
                ->where('statut', Resultat::STATUT_VALIDE)
                ->chunk(1000, function ($resultats) use (&$count, $timestamp, $operation) {
                    foreach ($resultats as $resultat) {
                        $resultat->changerStatut(Resultat::STATUT_PUBLIE, Auth::id() ?: 1);
                        $resultat->operation_id = $operation->id;
                        $resultat->date_publication = $timestamp;
                        $resultat->save();
                        $count++;
                    }
                });

            // Calculer les décisions (admis/ajourné) pour chaque étudiant
            $this->calculerDecisions($examen_id);

            DB::commit();

            Log::info('Publication des résultats', [
                'examen_id' => $examen_id,
                'operation_id' => $operation->id,
                'session_type' => $examen->session->type,
                'estPACES' => $estPACES,
                'resultats_publies' => $count,
                'publié_par' => Auth::id() ?: 1
            ]);

            // Après publication, invalider les caches existants
            $this->invalidateCaches($examen_id);

            $operation->updateStatus('completed', [
                'resultats_publies' => $count
            ]);

            return [
                'success' => true,
                'count' => $count,
                'message' => "{$count} résultats publiés avec succès"
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la publication des résultats', [
                'examen_id' => $examen_id,
                'operation_id' => $operation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $operation->updateStatus('failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Calcule les décisions (admis/ajourné) pour chaque étudiant
     *
     * @param int $examen_id ID de l'examen
     */
    private function calculerDecisions($examen_id)
    {
        // Récupérer tous les résultats groupés par étudiant
        $resultatsParEtudiant = Resultat::where('examen_id', $examen_id)
            ->where('statut', Resultat::STATUT_PUBLIE)
            ->get()
            ->groupBy('etudiant_id');

        foreach ($resultatsParEtudiant as $etudiantId => $resultatsEtudiant) {
            // Vérifier si toutes les matières sont réussies (note >= 10)
            $toutesReussies = $resultatsEtudiant->every(function($resultat) {
                return $resultat->note >= 10;
            });

            // Moyenne générale de l'étudiant
            $moyenne = $resultatsEtudiant->avg('note');

            // Déterminer la décision
            $decision = $toutesReussies ? 'admis' : 'ajourne';

            // Mettre à jour la décision pour chaque résultat
            foreach ($resultatsEtudiant as $resultat) {
                $resultat->decision = $decision;
                $resultat->moyenne_generale = $moyenne;
                $resultat->save();
            }

            // Enregistrer des statistiques détaillées pour débogage
            Log::info('Décision calculée pour étudiant', [
                'etudiant_id' => $etudiantId,
                'moyenne' => $moyenne,
                'decision' => $decision,
                'nb_matieres' => $resultatsEtudiant->count(),
                'matieres_reussies' => $resultatsEtudiant->where('note', '>=', 10)->count()
            ]);
        }
    }

    /**
     * Invalide les caches liés à un examen
     *
     * @param int|null $examen_id ID de l'examen
     */
    private function invalidateCaches($examen_id = null)
    {
        if ($examen_id) {
            // Supprimer tous les caches liés à cet examen
            $patterns = [
                $this->cachePrefix . 'coherence_' . $examen_id,
                $this->cachePrefix . 'fusion_' . $examen_id . '_0',
                $this->cachePrefix . 'fusion_' . $examen_id . '_1'
            ];

            foreach ($patterns as $pattern) {
                Cache::forget($pattern);
            }

            Log::info('Caches invalidés pour l\'examen', ['examen_id' => $examen_id]);
        } else {
            // Rechercher et supprimer tous les caches avec le préfixe de fusion
            $cacheKeys = Cache::get('fusion_cache_keys', []);

            foreach ($cacheKeys as $key) {
                if (strpos($key, $this->cachePrefix) === 0) {
                    Cache::forget($key);
                }
            }

            Log::info('Tous les caches de fusion ont été invalidés');
        }
    }

    /**
     * Exporte les résultats au format Excel
     *
     * @param int $examen_id ID de l'examen
     * @return string|null Chemin du fichier exporté
     */
    public function exporterResultats($examen_id)
    {
        // Placeholder - Implémentation réelle requise selon vos besoins
        Log::info('Demande d\'export de résultats', ['examen_id' => $examen_id]);

        // Retourner le chemin du fichier ou null en cas d'échec
        return null;
    }

    /**
     * Détermine l'étape de fusion actuelle (1ère, 2ème, etc.)
     *
     * @param int $examen_id ID de l'examen
     * @return int Numéro de l'étape (1, 2, 3)
     */
    public function determinerEtapeFusion($examen_id)
    {
        // Vérifier si des résultats existent déjà
        $resultatsCount = Resultat::where('examen_id', $examen_id)->count();

        if ($resultatsCount == 0) {
            return 1; // Première fusion
        }

        // Vérifier si les moyennes sont déjà calculées
        $avecMoyennes = Resultat::where('examen_id', $examen_id)
            ->whereNotNull('moyenne_ue')
            ->exists();

        if (!$avecMoyennes) {
            return 2; // Deuxième fusion (calcul des moyennes)
        }

        return 3; // Dernière fusion (consolidation)
    }



    public function verifierEtatCourant($examen_id)
    {
        // Vérifier s'il y a une opération active en cours
        $operationActive = FusionOperation::where('examen_id', $examen_id)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($operationActive) {
            return [
                'en_cours' => true,
                'operation_id' => $operationActive->id,
                'type' => $operationActive->type,
                'started_at' => $operationActive->started_at
            ];
        }

        return ['en_cours' => false];
    }

}
