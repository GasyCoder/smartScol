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
use App\Models\Deliberation;
use App\Models\ResultatFusion;
use App\Models\ResultatFinal;
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

            $etudiants = Etudiant::where('niveau_id', $examen->niveau_id)
                ->where('parcours_id', $examen->parcours_id)
                ->where('is_active', true)
                ->get();

            $totalEtudiants = $etudiants->count();
            $resultatsExistants = ResultatFusion::where('examen_id', $examenId)->exists();

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

            Log::info('Vérification de cohérence terminée', [
                'examen_id' => $examenId,
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
     *
     * @param int $examenId
     * @param int $totalEtudiants
     * @return array
     */
    private function analyserResultatsExistants($examenId, $totalEtudiants)
    {
        $resultats = ResultatFusion::where('examen_id', $examenId)
            ->select('id', 'etudiant_id', 'ec_id', 'note', 'statut')
            ->with(['etudiant', 'ec'])
            ->get();

        $rapport = [];

        foreach ($resultats->groupBy('ec_id') as $ecId => $resultatsEc) {
            $ec = $resultatsEc->first()->ec;
            if (!$ec) {
                Log::warning('EC introuvable pour résultats existants', [
                    'examen_id' => $examenId,
                    'ec_id' => $ecId,
                ]);
                continue;
            }

            $etudiantsAvecNote = $resultatsEc->whereNotNull('note')->count();
            $etudiantsAvecResult = $resultatsEc->pluck('etudiant_id')->unique()->count();
            $complet = $totalEtudiants === $etudiantsAvecNote && $totalEtudiants > 0;

            $rapport[] = [
                'ec_id' => $ecId,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                'total_etudiants' => $totalEtudiants,
                'etudiants_avec_note' => $etudiantsAvecNote,
                'manchettes_count' => $etudiantsAvecResult,
                'copies_count' => $etudiantsAvecNote,
                'codes_count' => $etudiantsAvecResult,
                'complet' => $complet,
                'etudiants_sans_manchette' => $totalEtudiants - $etudiantsAvecResult,
                'codes_sans_manchettes' => ['count' => 0, 'codes' => []],
                'codes_sans_copies' => ['count' => 0, 'codes' => []],
                'issues' => [],
            ];
        }

        return $rapport;
    }

    /**
     * Analyse les données avant fusion
     *
     * @param int $examenId
     * @param int $totalEtudiants
     * @param Collection $etudiants
     * @return array
     */
    private function analyserDonneesPrefusion($examenId, $totalEtudiants, Collection $etudiants)
    {
        $copies = Copie::where('examen_id', $examenId)
            ->whereNotNull('code_anonymat_id')
            ->with(['ec', 'codeAnonymat'])
            ->get();

        $ecs = EC::whereHas('examens', function ($query) use ($examenId) {
            $query->where('examens.id', $examenId);
        })->get();

        $rapport = [];

        foreach ($ecs as $ec) {
            $copiesEc = $copies->where('ec_id', $ec->id);
            $copiesAvecNote = $copiesEc->whereNotNull('note');
            $codesEnCopies = $copiesEc->filter(function ($copie) {
                return $copie->codeAnonymat && $copie->codeAnonymat->code_complet;
            })->map(function ($copie) {
                return $copie->codeAnonymat->code_complet;
            })->unique();

            $manchettes = Manchette::where('examen_id', $examenId)
                ->whereNotNull('code_anonymat_id')
                ->whereHas('codeAnonymat', function ($query) use ($ec) {
                    $query->where('ec_id', $ec->id)
                          ->whereNotNull('code_complet')
                          ->where('code_complet', '!=', '');
                })
                ->with(['etudiant', 'codeAnonymat'])
                ->get();

            $manchettesAvecCodes = $manchettes->filter(function ($manchette) {
                return $manchette->codeAnonymat && $manchette->codeAnonymat->code_complet;
            });

            $codesEnManchettes = $manchettesAvecCodes->map(function ($manchette) {
                return $manchette->codeAnonymat->code_complet;
            })->unique();

            $manchettesCorrespondantes = $manchettesAvecCodes->filter(function ($manchette) use ($codesEnCopies) {
                return $codesEnCopies->contains($manchette->codeAnonymat->code_complet);
            });

            $codesSansManchettes = $codesEnCopies->diff($codesEnManchettes);
            $codesSansCopies = $codesEnManchettes->diff($codesEnCopies);

            $etudiantsAvecManchette = $manchettesCorrespondantes->pluck('etudiant_id')->unique();
            $etudiantsSansManchette = $etudiants->whereNotIn('id', $etudiantsAvecManchette)->count();

            $complet = ($totalEtudiants > 0) &&
                       ($copiesEc->count() === $totalEtudiants) &&
                       ($copiesEc->count() === $copiesAvecNote->count()) &&
                       ($copiesEc->count() === $manchettesCorrespondantes->count()) &&
                       ($codesSansManchettes->isEmpty()) &&
                       ($codesSansCopies->isEmpty());

            $issues = [];
            if ($manchettesAvecCodes->isEmpty()) {
                $issues[] = "Aucune manchette valide pour EC {$ec->id}";
            }
            if ($codesEnCopies->isEmpty() && $copiesEc->count() > 0) {
                $issues[] = "Les copies n'ont pas de codes d'anonymat valides pour EC {$ec->id}";
            }
            if ($etudiantsSansManchette > 0) {
                $issues[] = "$etudiantsSansManchette étudiant(s) sans manchette pour EC {$ec->id}";
            }
            if ($copiesEc->count() !== $copiesAvecNote->count()) {
                $issues[] = ($copiesEc->count() - $copiesAvecNote->count()) . " copie(s) sans note pour EC {$ec->id}";
            }

            $rapport[] = [
                'ec_id' => $ec->id,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr ?? $ec->code ?? 'N/A',
                'total_etudiants' => $totalEtudiants,
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
            ];
        }

        return $rapport;
    }

    /**
     * Effectue la fusion des manchettes et copies
     *
     * @param int $examenId
     * @param bool $force Forcer la refusion
     * @return array
     */
    public function fusionner($examenId, $force = false)
    {
        try {
            $examen = Examen::findOrFail($examenId);
            $currentEtape = ResultatFusion::where('examen_id', $examenId)->max('etape_fusion') ?? 0;

            if ($currentEtape >= 4 && !$force) {
                Log::warning('Fusion bloquée : déjà terminée', [
                    'examen_id' => $examenId,
                    'etape_actuelle' => $currentEtape,
                ]);
                return [
                    'success' => false,
                    'message' => 'La fusion est déjà terminée. Utilisez l\'option de refusion si nécessaire.',
                ];
            }

            $nextEtape = $force ? 1 : ($currentEtape + 1);
            DB::beginTransaction();

            switch ($nextEtape) {
                case 1:
                    $result = $this->executerEtape1($examenId);
                    break;
                case 2:
                    $result = $this->executerEtape2($examenId);
                    break;
                case 3:
                    $result = $this->executerEtape3($examenId);
                    break;
                default:
                    throw new \Exception("Étape de fusion invalide : $nextEtape");
            }

            if (!$result['success']) {
                DB::rollBack();
                Log::warning('Échec de l\'étape de fusion', [
                    'examen_id' => $examenId,
                    'etape' => $nextEtape,
                    'message' => $result['message'],
                ]);
                return $result;
            }

            $statistiques = [
                'resultats_generes' => ResultatFusion::where('examen_id', $examenId)
                    ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2, ResultatFusion::STATUT_VERIFY_3])
                    ->count(),
                'etape' => $nextEtape,
            ];

            DB::commit();

            Log::info('Fusion réussie', [
                'examen_id' => $examenId,
                'etape' => $nextEtape,
                'statistiques' => $statistiques,
            ]);

            return [
                'success' => true,
                'message' => "Fusion étape $nextEtape terminée avec succès.",
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
     *
     * @param int $examenId
     * @return array
     */
    private function executerEtape1($examenId)
    {
        $manchettes = Manchette::where('examen_id', $examenId)
            ->whereHas('codeAnonymat', function ($query) {
                $query->whereNotNull('code_complet')->where('code_complet', '!=', '');
            })
            ->with(['etudiant', 'codeAnonymat'])
            ->get();

        if ($manchettes->isEmpty()) {
            Log::warning('Aucune manchette valide pour la fusion', ['examen_id' => $examenId]);
            return [
                'success' => false,
                'message' => 'Aucune manchette valide trouvée pour cet examen.',
            ];
        }

        $copies = Copie::where('examen_id', $examenId)
            ->whereNotNull('code_anonymat_id')
            ->whereHas('codeAnonymat', function ($query) {
                $query->whereNotNull('code_complet')->where('code_complet', '!=', '');
            })
            ->whereNotNull('ec_id')
            ->with(['ec', 'codeAnonymat'])
            ->get();

        if ($copies->isEmpty()) {
            Log::warning('Aucune copie valide pour la fusion', ['examen_id' => $examenId]);
            return [
                'success' => false,
                'message' => 'Aucune copie valide trouvée pour cet examen.',
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
                Log::warning('Aucune copie pour le code d\'anonymat', [
                    'examen_id' => $examenId,
                    'code_anonymat' => $codeAnonymat,
                    'etudiant_id' => $manchette->etudiant_id,
                ]);
                $erreursIgnorees++;
                continue;
            }

            foreach ($copiesCorrespondantes as $copie) {
                $resultatExiste = ResultatFusion::where('examen_id', $examenId)
                    ->where('etudiant_id', $manchette->etudiant_id)
                    ->where('ec_id', $copie->ec_id)
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

                ResultatFusion::create([
                    'etudiant_id' => $manchette->etudiant_id,
                    'examen_id' => $examenId,
                    'code_anonymat_id' => $codeAnonymatRecord->id,
                    'ec_id' => $copie->ec_id,
                    'note' => $copie->note,
                    'genere_par' => Auth::id(),
                    'statut' => ResultatFusion::STATUT_VERIFY_1,
                    'etape_fusion' => 1,
                ]);

                $resultatsGeneres++;
            }
        }

        if ($resultatsGeneres === 0) {
            Log::warning('Aucun résultat généré lors de l\'étape 1', [
                'examen_id' => $examenId,
                'erreurs_ignorees' => $erreursIgnorees,
            ]);
            return [
                'success' => false,
                'message' => "Aucune donnée fusionnée. Erreurs ignorées : $erreursIgnorees.",
            ];
        }

        Log::info('Étape 1 de fusion terminée', [
            'examen_id' => $examenId,
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
     *
     * @param string $codeAnonymat
     * @return int|null
     */
    private function extraireSequence($codeAnonymat)
    {
        return preg_match('/(\d+)$/', $codeAnonymat, $matches) ? (int) $matches[1] : null;
    }

    /**
     * Étape 2 : Validation des résultats
     *
     * @param int $examenId
     * @return array
     */
    private function executerEtape2($examenId)
    {
        $resultats = ResultatFusion::where('examen_id', $examenId)
            ->where('statut', ResultatFusion::STATUT_VERIFY_1)
            ->where('etape_fusion', 1)
            ->get();

        if ($resultats->isEmpty()) {
            Log::warning('Aucun résultat à valider pour l\'étape 2', ['examen_id' => $examenId]);
            return [
                'success' => false,
                'message' => 'Aucun résultat à valider à l\'étape 1.',
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

        Log::info('Étape 2 de fusion terminée', [
            'examen_id' => $examenId,
            'resultats_valides' => $resultatsValides,
        ]);

        return [
            'success' => true,
            'resultats_valides' => $resultatsValides,
        ];
    }

    /**
     * Étape 3 : Troisième vérification avant finalisation
     *
     * @param int $examenId
     * @return array
     */
    private function executerEtape3($examenId)
    {
        $resultats = ResultatFusion::where('examen_id', $examenId)
            ->where('statut', ResultatFusion::STATUT_VERIFY_2)
            ->where('etape_fusion', 2)
            ->get();

        if ($resultats->isEmpty()) {
            Log::warning('Aucun résultat à traiter pour l\'étape 3', ['examen_id' => $examenId]);
            return [
                'success' => false,
                'message' => 'Aucun résultat à traiter à l\'étape 2.',
            ];
        }

        $resultatsFinalises = 0;
        foreach ($resultats as $resultat) {
            // Effectuer ici les vérifications supplémentaires pour l'étape 3
            if ($this->verifierResultatEtape3($resultat)) {
                $resultat->changerStatut(ResultatFusion::STATUT_VERIFY_3, Auth::id());
                $resultat->etape_fusion = 3;
                $resultat->save();
                $resultatsFinalises++;
            }
        }

        Log::info('Étape 3 de fusion terminée', [
            'examen_id' => $examenId,
            'resultats_traites' => $resultatsFinalises,
        ]);

        return [
            'success' => true,
            'resultats_traites' => $resultatsFinalises,
        ];
    }

    /**
     * Effectue des vérifications supplémentaires pour l'étape 3
     *
     * @param ResultatFusion $resultat
     * @return bool
     */
    private function verifierResultatEtape3(ResultatFusion $resultat)
    {
        // Vérifications additionnelles pour l'étape 3
        // Par exemple: vérifier la cohérence avec d'autres résultats,
        // contrôler les moyennes par groupe, etc.

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
     *
     * @param ResultatFusion $resultat
     * @return bool
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
     * Valide et publie les résultats finals après fusion
     *
     * @param int $examenId ID de l'examen concerné
     * @return array Résultat de l'opération
     */
    public function validerResultats($examenId)
    {
        try {
            DB::beginTransaction();

            $examen = Examen::with(['niveau', 'session'])->findOrFail($examenId);
            $niveau = $examen->niveau;
            $session = $examen->session;

            if (!$niveau || !$session) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Données d\'examen incomplètes (niveau ou session manquant).'
                ];
            }

            $resultats = ResultatFusion::where('examen_id', $examenId)
                ->where('statut', ResultatFusion::STATUT_VERIFY_3)  // ← CORRECT
                ->where('etape_fusion', 3)                          // ← CORRECT
                ->get();

            if ($resultats->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat validé à l\'étape 3 (VERIFY_3).'  // ← CORRECT
                ];
            }

            // Finalisation des résultats fusionnés
            $resultatsFinalises = 0;
            foreach ($resultats as $resultat) {
                $resultat->changerStatut(ResultatFusion::STATUT_VALIDE, Auth::id());
                $resultat->etape_fusion = 2; // Rester à l'étape 2, mais marqué comme validé
                $resultat->save();
                $resultatsFinalises++;
            }

            $requiresDeliberation = $session->isRattrapage() && !$niveau->is_concours;
            $deliberation = null;

            if ($requiresDeliberation) {
                $deliberation = $this->creerDeliberationAutomatique(
                    $niveau->id,
                    $session->id,
                    $session->annee_universitaire_id
                );
            }

            // Initialiser les statistiques par étudiant
            $statsEtudiants = [
                'total' => 0,
                'admis' => 0,
                'rattrapage' => 0
            ];

            $calculService = new CalculAcademiqueService();

            // Transférer vers ResultatFinal
            foreach ($resultats->groupBy('etudiant_id') as $etudiantId => $etudiantResultats) {
                $statsEtudiants['total']++;

                $moyenneUE = 0;
                $totalECs = 0;

                foreach ($etudiantResultats as $resultat) {
                    $moyenneUE += $resultat->note;
                    $totalECs++;
                }

                $moyenneGenerale = $totalECs > 0 ? round($moyenneUE / $totalECs, 2) : 0;
                $decision = $moyenneGenerale >= 10 ? 'admis' : 'rattrapage';

                if ($decision === 'admis') {
                    $statsEtudiants['admis']++;
                } else {
                    $statsEtudiants['rattrapage']++;
                }

                foreach ($etudiantResultats as $resultat) {
                    $exists = ResultatFinal::where('etudiant_id', $resultat->etudiant_id)
                        ->where('examen_id', $resultat->examen_id)
                        ->where('ec_id', $resultat->ec_id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    ResultatFinal::create([
                        'etudiant_id' => $resultat->etudiant_id,
                        'examen_id' => $resultat->examen_id,
                        'code_anonymat_id' => $resultat->code_anonymat_id,
                        'ec_id' => $resultat->ec_id,
                        'note' => $resultat->note,
                        'genere_par' => Auth::id(),
                        'statut' => $requiresDeliberation ? 'en_attente' : 'publie',
                        'status_history' => [[
                            'de' => 'en_attente',
                            'vers' => $requiresDeliberation ? 'en_attente' : 'publie',
                            'user_id' => Auth::id(),
                            'date' => now()->toDateTimeString(),
                            'avec_deliberation' => $requiresDeliberation,
                            'decision' => $decision
                        ]],
                        'decision' => $decision,
                        'date_publication' => $requiresDeliberation ? null : now(),
                        'hash_verification' => hash('sha256', $resultat->id . $resultat->note . time()),
                        'fusion_id' => $resultat->id,
                        'date_fusion' => now(),
                        'deliberation_id' => $deliberation ? $deliberation->id : null
                    ]);
                }
            }

            DB::commit();

            Log::info('Validation des résultats terminée', [
                'examen_id' => $examenId,
                'total_etudiants' => $statsEtudiants['total'],
                'admis' => $statsEtudiants['admis'],
                'rattrapage' => $statsEtudiants['rattrapage'],
                'avec_deliberation' => $requiresDeliberation,
                'deliberation_id' => $deliberation ? $deliberation->id : null
            ]);

            return [
                'success' => true,
                'message' => sprintf(
                    'Résultats validés avec succès. Total étudiants: %d, Admis: %d, Rattrapage: %d',
                    $statsEtudiants['total'],
                    $statsEtudiants['admis'],
                    $statsEtudiants['rattrapage']
                )
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation des résultats', [
                'examen_id' => $examenId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la validation : ' . $e->getMessage()
            ];
        }
    }



    /**
     * Transfère les résultats de resultats_fusion vers resultats_finaux
     *
     * @param array $resultatFusionIds
     * @param int $generePar
     * @param bool $avecDeliberation
     * @return array
     */
    public function transfererResultats(array $resultatFusionIds, int $generePar, bool $avecDeliberation = false)
    {
        try {
            DB::beginTransaction();

            $resultatsFusion = ResultatFusion::whereIn('id', $resultatFusionIds)
                ->where('statut', ResultatFusion::STATUT_VALIDE)
                ->get();

            if ($resultatsFusion->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Aucun résultat valide à transférer.',
                    'resultats_transférés' => 0,
                ];
            }

            $resultatsTransférés = 0;
            $etudiantsTraites = [];
            $examenId = $resultatsFusion->first()->examen_id;
            $examen = Examen::with('session')->findOrFail($examenId);
            $session = $examen->session;

            // Créer une instance de CalculAcademiqueService
            $calculService = new CalculAcademiqueService();

            foreach ($resultatsFusion as $resultatFusion) {
                $exists = ResultatFinal::where('etudiant_id', $resultatFusion->etudiant_id)
                    ->where('examen_id', $resultatFusion->examen_id)
                    ->where('ec_id', $resultatFusion->ec_id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Créer le résultat final (initialement sans décision)
                $resultatFinal = ResultatFinal::create([
                    'etudiant_id' => $resultatFusion->etudiant_id,
                    'examen_id' => $resultatFusion->examen_id,
                    'code_anonymat_id' => $resultatFusion->code_anonymat_id,
                    'ec_id' => $resultatFusion->ec_id,
                    'note' => $resultatFusion->note,
                    'genere_par' => $generePar,
                    'modifie_par' => null,
                    'statut' => 'en_attente',
                    'status_history' => [[
                        'de' => 'en_attente',
                        'vers' => 'en_attente',
                        'user_id' => $generePar,
                        'date' => now()->toDateTimeString(),
                        'avec_deliberation' => $avecDeliberation,
                        'decision' => null,
                    ]],
                    'decision' => null, // La décision sera mise à jour plus tard
                    'date_publication' => null,
                    'hash_verification' => hash('sha256', $resultatFusion->id . $resultatFusion->note . time()),
                    'fusion_id' => $resultatFusion->id,
                    'date_fusion' => now(),
                ]);

                $resultatsTransférés++;
                $etudiantsTraites[$resultatFusion->etudiant_id] = true;
            }

            // Une fois tous les résultats transférés, calculer et mettre à jour les décisions
            foreach (array_keys($etudiantsTraites) as $etudiantId) {
                // Calculer les résultats complets pour l'étudiant
                $resultatsEtudiant = $calculService->calculerResultatsComplets($etudiantId, $session->id, true);

                // Déterminer la décision en fonction de la moyenne
                $moyenneUE = $resultatsEtudiant['synthese']['moyenne_generale'];
                $decision = $moyenneUE >= 10 ? 'admis' : 'rattrapage';

                // Mettre à jour tous les résultats de l'étudiant avec la nouvelle décision
                ResultatFinal::where('etudiant_id', $etudiantId)
                    ->where('examen_id', $examenId)
                    ->update([
                        'decision' => $decision,
                        'statut' => 'publie',
                        'date_publication' => now()
                    ]);

                Log::info("Décision calculée et appliquée", [
                    'etudiant_id' => $etudiantId,
                    'moyenne_ue' => $moyenneUE,
                    'decision' => $decision
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Transfert effectué avec succès. $resultatsTransférés résultat(s) transféré(s).",
                'resultats_transférés' => $resultatsTransférés,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du transfert des résultats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Erreur lors du transfert : ' . $e->getMessage(),
                'resultats_transférés' => 0,
            ];
        }
    }


    /**
     * Annule les résultats publiés
     *
     * @param int $examenId
     * @return array
     */
    public function annulerResultats($examenId)
    {
        try {
            DB::beginTransaction();

            $resultats = ResultatFinal::where('examen_id', $examenId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat publié à annuler.',
                ];
            }

            $deliberationId = $resultats->first()->deliberation_id;
            $deliberation = $deliberationId ? Deliberation::find($deliberationId) : null;

            foreach ($resultats as $resultat) {
                $resultat->changerStatut(ResultatFinal::STATUT_ANNULE, Auth::id());
            }

            if ($deliberation && $deliberation->isValidee()) {
                $deliberation->statut = Deliberation::STATUT_ANNULEE;
                $deliberation->observations .= "\nAnnulée le " . now()->format('d/m/Y H:i') . " suite à l'annulation des résultats.";
                $deliberation->save();
                Log::info('Délibération annulée', ['deliberation_id' => $deliberationId]);
            }

            DB::commit();

            Log::info('Résultats annulés', [
                'examen_id' => $examenId,
                'resultats_annules' => $resultats->count(),
                'deliberation_id' => $deliberationId,
            ]);

            return [
                'success' => true,
                'message' => 'Résultats annulés avec succès.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation', [
                'examen_id' => $examenId,
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
     * Restaure les résultats annulés à l'état en attente
     *
     * @param int $examenId
     * @return array
     */
    public function revenirValidation($examenId)
    {
        try {
            DB::beginTransaction();

            $resultats = ResultatFinal::where('examen_id', $examenId)
                ->where('statut', ResultatFinal::STATUT_ANNULE)
                ->get();

            if ($resultats->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat annulé à restaurer.',
                ];
            }

            $deliberationId = $resultats->first()->deliberation_id;
            $deliberation = $deliberationId ? Deliberation::find($deliberationId) : null;

            foreach ($resultats as $resultat) {
                $resultat->changerStatut(ResultatFinal::STATUT_EN_ATTENTE, Auth::id());
            }

            if ($deliberation && $deliberation->isAnnulee()) {
                $deliberation->statut = Deliberation::STATUT_TERMINEE;
                $deliberation->observations .= "\nRéactivée le " . now()->format('d/m/Y H:i') . " suite à la réactivation des résultats.";
                $deliberation->save();
                Log::info('Délibération réactivée', ['deliberation_id' => $deliberationId]);
            }

            DB::commit();

            Log::info('Retour à l\'état en attente effectué', [
                'examen_id' => $examenId,
                'resultats_restaures' => $resultats->count(),
                'deliberation_id' => $deliberationId,
            ]);

            return [
                'success' => true,
                'message' => 'Retour à l\'état en attente effectué.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du retour à l\'état en attente', [
                'examen_id' => $examenId,
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
    public function resetExam($examenId)
    {
        try {
            DB::beginTransaction();

            $totalFusion = ResultatFusion::where('examen_id', $examenId)->count();
            $totalFinal = ResultatFinal::where('examen_id', $examenId)->count();

            if ($totalFusion === 0 && $totalFinal === 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat à supprimer pour cet examen.',
                ];
            }

            $deliberationIds = ResultatFinal::where('examen_id', $examenId)
                ->whereNotNull('deliberation_id')
                ->pluck('deliberation_id')
                ->unique()
                ->toArray();

            $deletedFusion = ResultatFusion::where('examen_id', $examenId)->delete();
            $deletedFinal = ResultatFinal::where('examen_id', $examenId)->delete();

            foreach ($deliberationIds as $deliberationId) {
                $autresResultats = ResultatFinal::where('deliberation_id', $deliberationId)->exists();
                if (!$autresResultats) {
                    $deliberation = Deliberation::find($deliberationId);
                    if ($deliberation && !$deliberation->isValidee()) {
                        $deliberation->statut = Deliberation::STATUT_ANNULEE;
                        $deliberation->observations .= "\nAnnulée le " . now()->format('d/m/Y H:i') . " suite à la réinitialisation.";
                        $deliberation->save();
                        Log::info('Délibération annulée', ['deliberation_id' => $deliberationId]);
                    }
                }
            }

            DB::commit();

            Log::info('Examen réinitialisé', [
                'examen_id' => $examenId,
                'resultats_fusion_supprimes' => $deletedFusion,
                'resultats_finaux_supprimes' => $deletedFinal,
                'deliberations_traitees' => count($deliberationIds),
            ]);

            return [
                'success' => true,
                'message' => "$deletedFusion résultat(s) fusion et $deletedFinal résultat(s) final supprimé(s).",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la réinitialisation', [
                'examen_id' => $examenId,
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
     * Crée une délibération automatique pour la session de rattrapage
     *
     * @param int $niveauId
     * @param int $sessionId
     * @param int $anneeUniversitaireId
     * @return Deliberation|null
     */
    private function creerDeliberationAutomatique($niveauId, $sessionId, $anneeUniversitaireId)
    {
        try {
            $session = SessionExam::find($sessionId);
            if (!$session || !$session->isRattrapage()) {
                Log::warning('Pas une session de rattrapage', ['session_id' => $sessionId]);
                return null;
            }

            $niveau = Niveau::find($niveauId);
            if (!$niveau) {
                Log::warning('Niveau non trouvé', ['niveau_id' => $niveauId]);
                return null;
            }

            if ($niveau->is_concours) {
                Log::warning('Niveau concours non éligible', [
                    'niveau_id' => $niveauId,
                    'niveau_abr' => $niveau->abr,
                ]);
                return null;
            }

            $dateDeliberation = $session->date_end
                ? Carbon::parse($session->date_end)->addDays(3)->startOfHour()->setTime(14, 0)
                : now()->startOfHour()->setTime(14, 0);

            while ($dateDeliberation->isWeekend()) {
                $dateDeliberation->addDay();
            }

            $parametresDefaut = Deliberation::getDefaultParamsForNiveau($niveau);

            $deliberation = Deliberation::create([
                'niveau_id' => $niveauId,
                'session_id' => $sessionId,
                'annee_universitaire_id' => $anneeUniversitaireId,
                'date_deliberation' => $dateDeliberation,
                'statut' => Deliberation::STATUT_PROGRAMMEE,
                'seuil_admission' => $parametresDefaut['seuil_admission'],
                'seuil_rachat' => $parametresDefaut['seuil_rachat'],
                'pourcentage_ue_requises' => $parametresDefaut['pourcentage_ue_requises'],
                'appliquer_regles_auto' => $parametresDefaut['appliquer_regles_auto'],
                'observations' => "Délibération automatique pour {$niveau->nom} ({$niveau->abr}).\n" .
                                 "Seuil admission: {$parametresDefaut['seuil_admission']}\n" .
                                 "Seuil rachat: {$parametresDefaut['seuil_rachat']}\n" .
                                 "UE requises: {$parametresDefaut['pourcentage_ue_requises']}%",
            ]);

            Log::info('Délibération créée', [
                'deliberation_id' => $deliberation->id,
                'niveau_id' => $niveauId,
                'session_id' => $sessionId,
                'date_deliberation' => $dateDeliberation->format('Y-m-d H:i'),
            ]);

            return $deliberation;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la délibération', [
                'niveau_id' => $niveauId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Valide les données avant la vérification de cohérence
     *
     * @param int $examenId
     * @return array
     */
    private function validateData($examenId)
    {
        $issues = [];

        $invalidCopies = Copie::where('examen_id', $examenId)
            ->where(function ($query) {
                $query->whereNull('code_anonymat_id')
                      ->orWhereNull('ec_id')
                      ->orWhereHas('codeAnonymat', function ($q) {
                          $q->whereNull('code_complet')->orWhere('code_complet', '');
                      });
            })
            ->count();

        if ($invalidCopies > 0) {
            $issues[] = "$invalidCopies copie(s) avec code_anonymat_id ou ec_id invalide(s)";
        }

        $invalidManchettes = Manchette::where('examen_id', $examenId)
            ->where(function ($query) {
                $query->whereNull('code_anonymat_id')
                      ->orWhereHas('codeAnonymat', function ($q) {
                          $q->whereNull('code_complet')->orWhere('code_complet', '');
                      });
            })
            ->count();

        if ($invalidManchettes > 0) {
            $issues[] = "$invalidManchettes manchette(s) avec code_anonymat_id ou code_complet invalide(s)";
        }

        $manchettesExist = Manchette::where('examen_id', $examenId)->exists();
        if (!$manchettesExist) {
            $issues[] = "Aucune manchette trouvée pour l'examen";
        }

        $codesExist = CodeAnonymat::where('examen_id', $examenId)->exists();
        if (!$codesExist) {
            $issues[] = "Aucun code d'anonymat trouvé pour l'examen";
        }

        if (!empty($issues)) {
            Log::warning('Validation des données échouée', [
                'examen_id' => $examenId,
                'issues' => $issues,
            ]);
            return ['valid' => false, 'issues' => $issues];
        }

        return ['valid' => true];
    }
}