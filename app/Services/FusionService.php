<?php

namespace App\Services;

use App\Models\EC;
use Carbon\Carbon;
use App\Models\Copie;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Etudiant;
use App\Models\Resultat;
use App\Models\Manchette;
use App\Models\SessionExam;
use App\Models\CodeAnonymat;
use App\Models\Deliberation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
            $examen = Examen::findOrFail($examenId);

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
            $resultatsExistants = Resultat::where('examen_id', $examenId)
                ->where('statut', Resultat::STATUT_PROVISOIRE)
                ->exists();

            $rapport = [];
            $stats = ['total' => 0, 'complets' => 0, 'incomplets' => 0];

            if ($resultatsExistants) {
                $rapport = $this->analyserResultatsExistants($examenId, $totalEtudiants);
            } else {
                $rapport = $this->analyserDonneesPrefusionReel($examenId, $totalEtudiants, $etudiants);
            }

            foreach ($rapport as $item) {
                $stats[$item['complet'] ? 'complets' : 'incomplets']++;
            }
            $stats['total'] = count($rapport);

            Log::info('Vérification de cohérence terminée', [
                'examen_id' => $examenId,
                'stats' => $stats,
                'total_etudiants' => $totalEtudiants,
                'rapport' => $rapport,
                'debug' => [
                    'manchette_ids' => Manchette::where('examen_id', $examenId)
                        ->pluck('id')
                        ->toArray(),
                    'code_anonymat_ids' => CodeAnonymat::where('examen_id', $examenId)
                        ->pluck('id')
                        ->toArray(),
                    'copie_ids' => Copie::where('examen_id', $examenId)
                        ->pluck('id')
                        ->toArray(),
                ],
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
     * Analyse les résultats existants après fusion
     *
     * @param int $examenId
     * @param int $totalEtudiants
     * @return array
     */
    private function analyserResultatsExistants($examenId, $totalEtudiants)
    {
        $resultats = Resultat::where('examen_id', $examenId)
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
                'codes_sans_manchettes' => [
                    'count' => 0,
                    'codes' => [],
                ],
                'codes_sans_copies' => [
                    'count' => 0,
                    'codes' => [],
                ],
                'issues' => [],
                'debug' => [
                    'resultat_ids' => $resultatsEc->pluck('id')->toArray(),
                    'etudiant_ids' => $resultatsEc->pluck('etudiant_id')->unique()->toArray(),
                ],
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
    private function analyserDonneesPrefusionReel($examenId, $totalEtudiants, Collection $etudiants)
    {
        $copies = Copie::where('examen_id', $examenId)
            ->whereNotNull('code_anonymat_id')
            ->with(['ec', 'codeAnonymat' => function ($query) {
                $query->whereNotNull('code_complet')
                    ->where('code_complet', '!=', '');
            }])
            ->get();

        $ecs = EC::whereHas('examens', function ($query) use ($examenId) {
            $query->where('examens.id', $examenId);
        })->get();

        $rapport = [];

        foreach ($ecs as $ec) {
            $copiesEc = $copies->where('ec_id', $ec->id);
            $copiesAvecNote = $copiesEc->whereNotNull('note');
            $codesEnCopies = $copiesEc->filter(function ($copie) {
                return $copie->codeAnonymat && !is_null($copie->codeAnonymat->code_complet);
            })->map(function ($copie) {
                return $copie->codeAnonymat->code_complet;
            })->filter()->unique();

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
                return $manchette->codeAnonymat && !is_null($manchette->codeAnonymat->code_complet) && $manchette->codeAnonymat->code_complet !== '';
            });

            // ✅ CORRECTION : Utilisation de $manchettesAvecCodes au lieu de $manchettesAvec_codes
            $codesEnManchettes = $manchettesAvecCodes->map(function ($manchette) {
                return $manchette->codeAnonymat->code_complet;
            })->filter()->unique();

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
                $issues[] = "Aucune manchette valide trouvée pour EC {$ec->id}";
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
                'debug' => [
                    'manchette_ids' => $manchettesCorrespondantes->pluck('id')->toArray(),
                    'copie_ids' => $copiesEc->pluck('id')->toArray(),
                    'code_anonymat_ids' => $codesEnCopies->keys()->toArray(),
                    'total_copies' => $copiesEc->count(),
                    'copies_avec_note' => $copiesAvecNote->count(),
                    'total_manchettes' => $manchettesCorrespondantes->count(),
                    'codes_en_copies' => $codesEnCopies->count(),
                    'codes_en_manchettes' => $codesEnManchettes->count(),
                    'invalid_copies' => $copiesEc->whereNull('codeAnonymat')->count(),
                ],
            ];

            if (!empty($issues)) {
                Log::warning('Problèmes détectés pour EC lors de l\'analyse pré-fusion', [
                    'examen_id' => $examenId,
                    'ec_id' => $ec->id,
                    'issues' => $issues,
                    'debug' => $rapport[count($rapport) - 1]['debug'],
                ]);
            }
        }

        return $rapport;
    }

    /**
     * Effectue la fusion des manchettes et copies
     *
     * @param int $examenId
     * @param bool $force
     * @return array
     */
    public function fusionner($examenId, $force = false)
    {
        try {
            $examen = Examen::findOrFail($examenId);
            $currentEtape = Resultat::where('examen_id', $examenId)->max('etape_fusion') ?? 0;

            if ($currentEtape >= 3 && !$force) {
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
                    $result = $this->executerEtape1Corrigee($examenId);
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
                'resultats_generes' => Resultat::where('examen_id', $examenId)
                    ->where('statut', Resultat::STATUT_PROVISOIRE)
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
    private function executerEtape1Corrigee($examenId)
    {
        $manchettes = Manchette::where('examen_id', $examenId)
            ->whereHas('codeAnonymat', function ($query) {
                $query->whereNotNull('code_complet')
                    ->where('code_complet', '!=', '');
            })
            ->with(['etudiant', 'codeAnonymat'])
            ->get();

        if ($manchettes->isEmpty()) {
            Log::warning('Aucune manchette valide pour la fusion', [
                'examen_id' => $examenId,
            ]);
            return [
                'success' => false,
                'message' => 'Aucune manchette valide trouvée pour cet examen.',
            ];
        }

        $copies = Copie::where('examen_id', $examenId)
            ->whereNotNull('code_anonymat_id')
            ->whereHas('codeAnonymat', function ($query) {
                $query->whereNotNull('code_complet')
                    ->where('code_complet', '!=', '');
            })
            ->whereNotNull('ec_id')
            ->with(['ec', 'codeAnonymat'])
            ->get();

        if ($copies->isEmpty()) {
            Log::warning('Aucune copie valide pour la fusion', [
                'examen_id' => $examenId,
            ]);
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
                Log::warning('Aucune copie trouvée pour le code d\'anonymat', [
                    'examen_id' => $examenId,
                    'code_anonymat' => $codeAnonymat,
                    'etudiant_id' => $manchette->etudiant_id,
                ]);
                $erreursIgnorees++;
                continue;
            }

            foreach ($copiesCorrespondantes as $copie) {
                $resultatExiste = Resultat::where('examen_id', $examenId)
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

                Resultat::create([
                    'etudiant_id' => $manchette->etudiant_id,
                    'examen_id' => $examenId,
                    'code_anonymat_id' => $codeAnonymatRecord->id,
                    'ec_id' => $copie->ec_id,
                    'note' => $copie->note,
                    'genere_par' => Auth::id(),
                    'statut' => Resultat::STATUT_PROVISOIRE,
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
        $resultats = Resultat::where('examen_id', $examenId)
            ->where('statut', Resultat::STATUT_PROVISOIRE)
            ->where('etape_fusion', 1)
            ->get();

        if ($resultats->isEmpty()) {
            Log::warning('Aucun résultat à valider pour l\'étape 2', [
                'examen_id' => $examenId,
            ]);
            return [
                'success' => false,
                'message' => 'Aucun résultat à valider à l\'étape 1.',
            ];
        }

        $resultatsValides = 0;
        foreach ($resultats as $resultat) {
            if ($this->validerResultat($resultat)) {
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
     * Étape 3 : Finalisation des résultats
     *
     * @param int $examenId
     * @return array
     */
    private function executerEtape3($examenId)
    {
        $resultats = Resultat::where('examen_id', $examenId)
            ->where('statut', Resultat::STATUT_PROVISOIRE)
            ->where('etape_fusion', 2)
            ->get();

        if ($resultats->isEmpty()) {
            Log::warning('Aucun résultat à finaliser pour l\'étape 3', [
                'examen_id' => $examenId,
            ]);
            return [
                'success' => false,
                'message' => 'Aucun résultat à finaliser à l\'étape 2.',
            ];
        }

        foreach ($resultats as $resultat) {
            $resultat->etape_fusion = 3;
            $resultat->save();
        }

        Log::info('Étape 3 de fusion terminée', [
            'examen_id' => $examenId,
            'resultats_finalises' => $resultats->count(),
        ]);

        return [
            'success' => true,
            'resultats_finalises' => $resultats->count(),
        ];
    }

    /**
     * Valide un résultat
     *
     * @param Resultat $resultat
     * @return bool
     */
    private function validerResultat(Resultat $resultat)
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
     * Valide ET publie les résultats en une seule action
     * Intègre la logique de délibération si nécessaire
     *
     * @param int $examenId ID de l'examen
     * @return array Résultat de l'opération
     */
    public function validerResultats($examenId)
    {
        try {
            DB::beginTransaction();

            // Récupérer l'examen et ses données contextuelles
            $examen = Examen::findOrFail($examenId);
            $niveau = $examen->niveau;
            $session = $examen->session;

            if (!$niveau || !$session) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Données d\'examen incomplètes (niveau ou session manquant).'
                ];
            }

            // Récupérer les résultats à valider
            $resultats = Resultat::where('examen_id', $examenId)
                ->where('statut', Resultat::STATUT_PROVISOIRE)
                ->where('etape_fusion', 3)
                ->get();

            if ($resultats->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat à valider à l\'étape 3. Assurez-vous que la fusion est terminée.'
                ];
            }

            // Vérifier si une délibération est nécessaire
            $requiresDeliberation = false;
            $deliberation = null;

            // Une délibération est nécessaire si:
            // 1. C'est une session de rattrapage
            // 2. Ce n'est pas un niveau de type concours (comme PACES)
            if ($session && $session->isRattrapage() && $niveau && !$niveau->is_concours) {
                $requiresDeliberation = true;

                // Rechercher une délibération existante pour cette session/niveau
                $deliberation = Deliberation::where('niveau_id', $niveau->id)
                    ->where('session_id', $session->id)
                    ->where('annee_universitaire_id', $session->annee_universitaire_id)
                    ->whereIn('statut', [
                        Deliberation::STATUT_PROGRAMMEE,
                        Deliberation::STATUT_EN_COURS
                    ])
                    ->first();

                // Si aucune délibération n'existe, on en crée une automatiquement
                if (!$deliberation) {
                    $deliberation = $this->creerDeliberationAutomatique(
                        $niveau->id,
                        $session->id,
                        $session->annee_universitaire_id
                    );

                    if (!$deliberation) {
                        Log::warning('Impossible de créer une délibération automatique', [
                            'examen_id' => $examenId,
                            'niveau_id' => $niveau->id,
                            'session_id' => $session->id
                        ]);
                        // On continue sans délibération
                        $requiresDeliberation = false;
                    }
                }
            }

            // Obtenir la liste des étudiants concernés
            $etudiants = $resultats->pluck('etudiant_id')->unique();
            $decisions = [];

            // Si une délibération est requise et existe, la démarrer
            if ($requiresDeliberation && $deliberation && $deliberation->isProgrammee()) {
                try {
                    // Démarrer la délibération (passe en statut EN_COURS)
                    $deliberation->demarrer(Auth::id());

                    Log::info('Délibération démarrée automatiquement lors de la validation des résultats', [
                        'deliberation_id' => $deliberation->id,
                        'examen_id' => $examenId
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur lors du démarrage automatique de la délibération', [
                        'deliberation_id' => $deliberation->id,
                        'examen_id' => $examenId,
                        'error' => $e->getMessage()
                    ]);

                    // On continue sans délibération si erreur
                    $requiresDeliberation = false;
                    $deliberation = null;
                }
            }

            // Déterminer les décisions pour chaque étudiant
            foreach ($etudiants as $etudiantId) {
                if ($requiresDeliberation && $deliberation) {
                    // Cas de la délibération: utiliser la logique du modèle Deliberation
                    $moyenne = $deliberation->calculerMoyenneEtudiant($etudiantId);
                    $pourcentageUE = $deliberation->calculerPourcentageUEValidees($etudiantId);
                    $decision = $deliberation->determinerDecisionAutomatique($moyenne, $pourcentageUE);

                    // Enregistrer la décision dans la délibération
                    $deliberation->enregistrerDecision($etudiantId, $decision, $moyenne);

                    $decisions[$etudiantId] = $decision;
                } else {
                    // Cas sans délibération (1ère session, concours, etc.)
                    // Utiliser la logique du modèle Resultat
                    $decision = Resultat::determinerDecisionPremiereSession($etudiantId, $session->id);
                    $decisions[$etudiantId] = $decision;
                }
            }

            // Mettre à jour les résultats avec les décisions et publier
            foreach ($resultats as $resultat) {
                $decision = $decisions[$resultat->etudiant_id] ?? null;

                // Associer le résultat à la délibération si elle existe
                if ($deliberation) {
                    $resultat->deliberation_id = $deliberation->id;
                    $resultat->save(); // Sauvegarder avant de changer le statut
                }

                // Changer le statut avec la décision
                $resultat->changerStatut(
                    Resultat::STATUT_PUBLIE,
                    Auth::id(),
                    $requiresDeliberation,
                    $decision
                );
            }

            // Si une délibération a été utilisée, finaliser et publier ses résultats
            if ($deliberation && $deliberation->isEnCours()) {
                try {
                    // Mettre à jour les statistiques de la délibération
                    $deliberation->mettreAJourStatistiques();

                    // Si configuré pour appliquer automatiquement les règles, on finalise
                    if ($deliberation->appliquer_regles_auto) {
                        // Finaliser la délibération (passe en statut TERMINEE)
                        $deliberation->finaliser(Auth::id());

                        // Puis publier les résultats de la délibération (passe en statut VALIDEE)
                        $deliberation->publier(Auth::id());

                        Log::info('Délibération automatiquement finalisée et publiée', [
                            'deliberation_id' => $deliberation->id,
                            'examen_id' => $examenId
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la finalisation/publication automatique de la délibération', [
                        'deliberation_id' => $deliberation->id,
                        'examen_id' => $examenId,
                        'error' => $e->getMessage()
                    ]);

                    // On continue car les résultats ont déjà été publiés
                }
            }

            DB::commit();

            Log::info('Résultats validés et publiés avec succès', [
                'examen_id' => $examenId,
                'resultats_traites' => $resultats->count(),
                'avec_deliberation' => $requiresDeliberation,
                'deliberation_id' => $deliberation ? $deliberation->id : null
            ]);

            return [
                'success' => true,
                'message' => $requiresDeliberation
                    ? 'Résultats validés, délibérés et publiés avec succès.'
                    : 'Résultats validés et publiés avec succès.',
                'avec_deliberation' => $requiresDeliberation,
                'deliberation_id' => $deliberation ? $deliberation->id : null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation et publication des résultats', [
                'examen_id' => $examenId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Erreur lors de la validation : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * ✅ SUPPRESSION : Cette méthode n'est plus nécessaire car la publication est intégrée à validerResultats()
     * Conservée pour compatibilité descendante mais redirige vers validerResultats()
     *
     * @param int $examenId
     * @param bool $estPACES
     * @return array
     */
    public function publierResultats($examenId, $estPACES = false)
    {
        Log::info('Appel obsolète de publierResultats - redirection vers validerResultats', [
            'examen_id' => $examenId,
            'est_paces' => $estPACES
        ]);

        // ✅ REDIRECTION : Dans la nouvelle logique, la validation publie automatiquement
        return [
            'success' => false,
            'message' => 'Cette méthode est obsolète. La validation publie automatiquement les résultats.',
        ];
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

            $resultats = Resultat::where('examen_id', $examenId)
                ->where('statut', Resultat::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat publié à annuler.',
                ];
            }

            // Vérifier si ces résultats sont liés à une délibération
            $deliberationId = $resultats->first()->deliberation_id;
            $deliberation = null;

            if ($deliberationId) {
                $deliberation = Deliberation::find($deliberationId);
            }

            foreach ($resultats as $resultat) {
                $resultat->changerStatut(Resultat::STATUT_ANNULE, Auth::id());
                // Ne pas effacer la référence à la délibération
            }

            // Si une délibération est associée, mettre à jour son statut
            if ($deliberation && $deliberation->isValidee()) {
                $deliberation->statut = Deliberation::STATUT_ANNULEE;
                $deliberation->observations = $deliberation->observations . "\n\nAnnulée le " . now()->format('d/m/Y H:i') .
                                            " suite à l'annulation des résultats.";
                $deliberation->save();

                Log::info('Délibération associée annulée', [
                    'deliberation_id' => $deliberation->id
                ]);
            }

            DB::commit();

            Log::info('Résultats annulés avec succès', [
                'examen_id' => $examenId,
                'resultats_annules' => $resultats->count(),
                'deliberation_id' => $deliberationId
            ]);

            return [
                'success' => true,
                'message' => 'Résultats annulés avec succès.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation des résultats', [
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
     * ✅ CORRECTION : Remet les résultats annulés à l'état provisoire
     * Dans la nouvelle logique, il n'y a plus d'état "validé" intermédiaire
     *
     * @param int $examenId
     * @return array
     */
    public function revenirValidation($examenId)
    {
        try {
            DB::beginTransaction();

            $resultats = Resultat::where('examen_id', $examenId)
                ->where('statut', Resultat::STATUT_ANNULE)
                ->get();

            if ($resultats->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat annulé à restaurer.',
                ];
            }

            // Vérifier si ces résultats sont liés à une délibération
            $deliberationId = $resultats->first()->deliberation_id;
            $deliberation = null;

            if ($deliberationId) {
                $deliberation = Deliberation::find($deliberationId);
            }

            foreach ($resultats as $resultat) {
                $resultat->changerStatut(Resultat::STATUT_PROVISOIRE, Auth::id());
                // Ne pas effacer la référence à la délibération
            }

            // Si une délibération est associée, restaurer son statut
            if ($deliberation && $deliberation->isAnnulee()) {
                $deliberation->statut = Deliberation::STATUT_TERMINEE;
                $deliberation->observations = $deliberation->observations . "\n\nRéactivée le " . now()->format('d/m/Y H:i') .
                                            " suite à la réactivation des résultats.";
                $deliberation->save();

                Log::info('Délibération associée réactivée', [
                    'deliberation_id' => $deliberation->id
                ]);
            }

            DB::commit();

            Log::info('Retour à l\'étape provisoire effectué', [
                'examen_id' => $examenId,
                'resultats_restaures' => $resultats->count(),
                'deliberation_id' => $deliberationId
            ]);

            return [
                'success' => true,
                'message' => 'Retour à l\'étape provisoire effectué. Vous pouvez maintenant revalider les résultats.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du retour à l\'état provisoire', [
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
     * Valide les données avant la vérification de cohérence
     *
     * @param int $examenId
     * @return array
     */
    private function validateData($examenId)
    {
        $issues = [];

        // Vérifier les copies invalides
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

        // Vérifier les manchettes invalides
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

        // Vérifier l'absence de manchettes
        $manchettesExist = Manchette::where('examen_id', $examenId)->exists();
        if (!$manchettesExist) {
            $issues[] = "Aucune manchette trouvée pour l'examen";
        }

        // Vérifier l'absence de codes d'anonymat
        $codesExist = CodeAnonymat::where('examen_id', $examenId)->exists();
        if (!$codesExist) {
            $issues[] = "Aucun code d'anonymat trouvé pour l'examen";
        }

        if (!empty($issues)) {
            Log::warning('Validation des données échouée', [
                'examen_id' => $examenId,
                'issues' => $issues,
                'debug' => [
                    'invalid_copies' => $invalidCopies,
                    'invalid_manchettes' => $invalidManchettes,
                    'manchettes_exist' => $manchettesExist,
                    'codes_exist' => $codesExist,
                ],
            ]);
            return [
                'valid' => false,
                'issues' => $issues,
            ];
        }

        return ['valid' => true];
    }

    /**
     * Réinitialise simplement tous les résultats de l'examen
     * Supprime tous les enregistrements de la table resultats pour cet examen
     *
     * @param int $examenId
     * @return array
     */
    public function resetExam($examenId)
    {
        try {
            DB::beginTransaction();

            // Compter les résultats avant suppression
            $totalResultats = Resultat::where('examen_id', $examenId)->count();

            if ($totalResultats === 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat à supprimer pour cet examen.',
                ];
            }

            // Vérifier si ces résultats sont liés à une délibération
            $deliberationIds = Resultat::where('examen_id', $examenId)
                ->whereNotNull('deliberation_id')
                ->pluck('deliberation_id')
                ->unique()
                ->toArray();

            // Supprimer TOUS les résultats de cet examen
            $deletedCount = Resultat::where('examen_id', $examenId)->delete();

            // Nettoyer les délibérations liées si elles sont vides
            if (!empty($deliberationIds)) {
                foreach ($deliberationIds as $deliberationId) {
                    // Vérifier si d'autres résultats utilisent cette délibération
                    $autresResultats = Resultat::where('deliberation_id', $deliberationId)->exists();

                    if (!$autresResultats) {
                        // Aucun autre résultat, on peut annuler la délibération
                        $deliberation = Deliberation::find($deliberationId);
                        if ($deliberation && !$deliberation->isValidee()) {
                            $deliberation->statut = Deliberation::STATUT_ANNULEE;
                            $deliberation->observations = $deliberation->observations . "\n\nAnnulée automatiquement le " .
                                                        now()->format('d/m/Y H:i') . " suite à la réinitialisation des résultats.";
                            $deliberation->save();

                            Log::info('Délibération annulée suite à reset', [
                                'deliberation_id' => $deliberationId
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            Log::info('Examen réinitialisé - Résultats supprimés', [
                'examen_id' => $examenId,
                'resultats_supprimes' => $deletedCount,
                'deliberations_traitees' => count($deliberationIds)
            ]);

            return [
                'success' => true,
                'message' => "Examen réinitialisé avec succès. {$deletedCount} résultat(s) supprimé(s).",
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la réinitialisation', [
                'examen_id' => $examenId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation : ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Crée automatiquement une délibération pour une session de rattrapage
     *
     * @param int $niveauId ID du niveau
     * @param int $sessionId ID de la session
     * @param int $anneeUniversitaireId ID de l'année universitaire
     * @return Deliberation|null
     */
    private function creerDeliberationAutomatique($niveauId, $sessionId, $anneeUniversitaireId)
    {
        try {
            // Vérifier que c'est bien une session de rattrapage
            $session = SessionExam::find($sessionId);
            if (!$session || !$session->isRattrapage()) {
                Log::warning('Impossible de créer une délibération automatique: pas une session de rattrapage', [
                    'session_id' => $sessionId
                ]);
                return null;
            }

            // Récupérer l'objet niveau complet
            $niveau = Niveau::find($niveauId);
            if (!$niveau) {
                Log::warning('Impossible de créer une délibération automatique: niveau non trouvé', [
                    'niveau_id' => $niveauId
                ]);
                return null;
            }

            // Vérifier que ce n'est pas un niveau concours
            if ($niveau->is_concours) {
                Log::warning('Impossible de créer une délibération automatique: niveau concours non éligible', [
                    'niveau_id' => $niveauId,
                    'niveau_abr' => $niveau->abr
                ]);
                return null;
            }

            // Calculer la date de délibération (3 jours après fin de session)
            $dateDeliberation = now();
            if ($session->date_end) {
                $dateDeliberation = Carbon::parse($session->date_end)->addDays(3);
                // Éviter les weekends
                while ($dateDeliberation->isWeekend()) {
                    $dateDeliberation->addDay();
                }
            }
            $dateDeliberation->setTime(14, 0, 0); // 14h00

            // Récupérer les paramètres par défaut pour ce niveau
            $parametresDefaut = Deliberation::getDefaultParamsForNiveau($niveau);

            // Créer la délibération avec ces paramètres
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
                'observations' => "Délibération créée automatiquement lors de la validation des résultats.\n" .
                                "Critères appliqués pour niveau {$niveau->nom} ({$niveau->abr}) :\n" .
                                "- Seuil d'admission : {$parametresDefaut['seuil_admission']}\n" .
                                "- Seuil de rachat : {$parametresDefaut['seuil_rachat']}\n" .
                                "- Pourcentage d'UE requises : {$parametresDefaut['pourcentage_ue_requises']}%"
            ]);

            Log::info('Délibération créée automatiquement', [
                'deliberation_id' => $deliberation->id,
                'niveau_id' => $niveauId,
                'niveau_nom' => $niveau->nom,
                'niveau_abr' => $niveau->abr,
                'session_id' => $sessionId,
                'annee_universitaire_id' => $anneeUniversitaireId,
                'date_deliberation' => $dateDeliberation->format('Y-m-d H:i:s'),
                'parametres' => $parametresDefaut
            ]);

            return $deliberation;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création automatique de délibération', [
                'niveau_id' => $niveauId,
                'session_id' => $sessionId,
                'annee_universitaire_id' => $anneeUniversitaireId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
