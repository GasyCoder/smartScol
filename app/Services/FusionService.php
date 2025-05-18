<?php

namespace App\Services;

use App\Models\Copie;
use App\Models\Examen;
use App\Models\Manchette;
use App\Models\Resultat;
use App\Models\CodeAnonymat;
use App\Models\EC;
use App\Models\Etudiant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FusionService
{
    /**
     * Vérifie la cohérence des données avant la fusion
     *
     * @param int $examen_id ID de l'examen
     * @return array Rapport de cohérence par matière
     */
    public function verifierCoherence($examen_id)
    {
        $examen = Examen::findOrFail($examen_id);

        // Récupérer toutes les matières pour cet examen
        $ecs = EC::whereHas('examens', function($query) use ($examen_id) {
            $query->where('examens.id', $examen_id);
        })->get();

        $rapport = [];

        // Nombre total d'étudiants pour ce niveau et parcours
        $totalEtudiants = Etudiant::where('niveau_id', $examen->niveau_id)
            ->where('parcours_id', $examen->parcours_id)
            ->where('is_active', true)
            ->count();

        foreach ($ecs as $ec) {
            // Codes d'anonymat pour cette matière
            $codes = CodeAnonymat::where('examen_id', $examen_id)
                ->where('ec_id', $ec->id)
                ->get();

            // Manchettes pour ces codes
            $codeIds = $codes->pluck('id')->toArray();
            $manchettes = Manchette::whereIn('code_anonymat_id', $codeIds)->get();

            // Copies pour cette matière
            $copies = Copie::where('examen_id', $examen_id)
                ->where('ec_id', $ec->id)
                ->get();

            // Codes sans manchettes
            $codesSansManchettesIds = collect($codeIds)
                ->diff($manchettes->pluck('code_anonymat_id'));
            $codesSansManchettes = CodeAnonymat::whereIn('id', $codesSansManchettesIds)->get();

            // Codes sans copies
            $codesSansCopiesIds = collect($codeIds)
                ->diff($copies->pluck('code_anonymat_id'));
            $codesSansCopies = CodeAnonymat::whereIn('id', $codesSansCopiesIds)->get();

            // Étudiants avec manchette pour cette matière
            $etudiantsAvecManchette = $manchettes->pluck('etudiant_id')->unique()->count();

            // Étudiants sans manchette
            $etudiantsSansManchette = $totalEtudiants - $etudiantsAvecManchette;

            $rapport[$ec->id] = [
                'ec_id' => $ec->id,
                'ec_nom' => $ec->nom,
                'codes_count' => $codes->count(),
                'manchettes_count' => $manchettes->count(),
                'copies_count' => $copies->count(),
                'total_etudiants' => $totalEtudiants,
                'etudiants_avec_manchette' => $etudiantsAvecManchette,
                'etudiants_sans_manchette' => $etudiantsSansManchette,
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

        return $rapport;
    }

    /**
     * Fusionne les manchettes et copies pour générer les résultats
     *
     * @param int $examen_id ID de l'examen
     * @param bool $forcer Forcer la fusion malgré les erreurs
     * @return array Résultats de la fusion
     */
    public function fusionner($examen_id, $forcer = false)
    {
        return DB::transaction(function() use ($examen_id, $forcer) {
            // Récupérer toutes les manchettes pour cet examen
            $manchettes = Manchette::where('examen_id', $examen_id)
                ->with(['codeAnonymat.ec', 'etudiant'])
                ->get();

            // Récupérer toutes les copies pour cet examen
            $copies = Copie::where('examen_id', $examen_id)
                ->with(['codeAnonymat', 'ec'])
                ->get()
                ->keyBy('code_anonymat_id');

            $resultats = collect();
            $erreurs = [];

            // Traiter chaque manchette
            foreach ($manchettes as $manchette) {
                $copie = $copies->get($manchette->code_anonymat_id);

                if (!$copie) {
                    $erreurs[] = [
                        'type' => 'manchette_sans_copie',
                        'manchette' => $manchette,
                        'message' => "Aucune note trouvée pour l'étudiant {$manchette->etudiant->nom} (Code: {$manchette->codeAnonymat->code_complet})"
                    ];
                    continue;
                }

                // Vérifier que l'EC de la copie correspond à celle du code d'anonymat
                if ($copie->ec_id != $manchette->codeAnonymat->ec_id) {
                    $erreurs[] = [
                        'type' => 'ec_mismatch',
                        'manchette' => $manchette,
                        'copie' => $copie,
                        'message' => "L'EC de la copie ({$copie->ec->nom}) ne correspond pas à celle du code d'anonymat ({$manchette->codeAnonymat->ec->nom})"
                    ];
                    continue;
                }

                // Créer ou mettre à jour le résultat
                try {
                    $resultat = Resultat::updateOrCreate(
                        [
                            'etudiant_id' => $manchette->etudiant_id,
                            'examen_id' => $manchette->examen_id,
                            'ec_id' => $copie->ec_id
                        ],
                        [
                            'code_anonymat_id' => $manchette->code_anonymat_id,
                            'note' => $copie->note,
                            'genere_par' => Auth::id(),
                            'date_generation' => now(),
                            'statut' => 'provisoire'
                        ]
                    );

                    $resultats->push($resultat);
                } catch (\Exception $e) {
                    Log::error("Erreur lors de la création d'un résultat", [
                        'manchette_id' => $manchette->id,
                        'copie_id' => $copie->id,
                        'error' => $e->getMessage()
                    ]);

                    $erreurs[] = [
                        'type' => 'erreur_creation',
                        'manchette' => $manchette,
                        'copie' => $copie,
                        'message' => "Erreur lors de la création du résultat: " . $e->getMessage()
                    ];
                }
            }

            // Vérifier les copies sans manchettes
            $codesAvecManchettes = $manchettes->pluck('code_anonymat_id')->toArray();

            foreach ($copies as $code_id => $copie) {
                if (!in_array($code_id, $codesAvecManchettes)) {
                    $erreurs[] = [
                        'type' => 'copie_sans_manchette',
                        'copie' => $copie,
                        'message' => "Aucune manchette trouvée pour le code {$copie->codeAnonymat->code_complet}"
                    ];
                }
            }

            return [
                'resultats' => $resultats,
                'erreurs' => $erreurs,
                'statistiques' => [
                    'total_manchettes' => $manchettes->count(),
                    'total_copies' => $copies->count(),
                    'resultats_generes' => $resultats->count(),
                    'erreurs_count' => count($erreurs)
                ]
            ];
        });
    }

    /**
     * Résout manuellement les erreurs de fusion
     *
     * @param array $resolutions Résolutions à appliquer
     * @return array Résultats des résolutions
     */
    public function resoudreErreurs(array $resolutions)
    {
        $resultatsGeneres = [];
        $erreurs = [];

        try {
            DB::beginTransaction();

            foreach ($resolutions as $resolution) {
                switch ($resolution['type']) {
                    case 'associer_manchette_copie':
                        $manchette = Manchette::find($resolution['manchette_id']);
                        $copie = Copie::find($resolution['copie_id']);

                        if (!$manchette || !$copie) {
                            $erreurs[] = [
                                'resolution' => $resolution,
                                'message' => 'Manchette ou copie introuvable'
                            ];
                            continue;
                        }

                        // Créer un résultat
                        $resultat = Resultat::updateOrCreate(
                            [
                                'etudiant_id' => $manchette->etudiant_id,
                                'examen_id' => $manchette->examen_id,
                                'ec_id' => $copie->ec_id
                            ],
                            [
                                'code_anonymat_id' => $manchette->code_anonymat_id,
                                'note' => $copie->note,
                                'genere_par' => Auth::id(),
                                'date_generation' => now(),
                                'statut' => 'provisoire'
                            ]
                        );

                        $resultatsGeneres[] = $resultat;
                        break;

                    default:
                        $erreurs[] = [
                            'resolution' => $resolution,
                            'message' => 'Type de résolution non reconnu'
                        ];
                }
            }

            DB::commit();

            return [
                'success' => true,
                'resultats' => $resultatsGeneres,
                'erreurs' => $erreurs
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'resultats' => [],
                'erreurs' => $erreurs
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
        try {
            $count = Resultat::where('examen_id', $examen_id)
                ->where('statut', 'provisoire')
                ->update([
                    'statut' => 'valide',
                    'modifie_par' => Auth::id(),
                    'date_modification' => now()
                ]);

            return [
                'success' => true,
                'count' => $count,
                'message' => "{$count} résultats validés avec succès"
            ];
        } catch (\Exception $e) {
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
     * @return array Résultat de la publication
     */
    public function publierResultats($examen_id)
    {
        try {
            // Vérifier si c'est une première session (publication directe)
            // ou une session de rattrapage (nécessite délibération)
            $examen = Examen::with('session')->findOrFail($examen_id);

            if ($examen->session->type === 'Rattrapage') {
                return [
                    'success' => false,
                    'message' => 'Les résultats de rattrapage nécessitent une délibération avant publication'
                ];
            }

            $count = Resultat::where('examen_id', $examen_id)
                ->where('statut', 'valide')
                ->update([
                    'statut' => 'publie',
                    'modifie_par' => Auth::id(),
                    'date_modification' => now()
                ]);

            return [
                'success' => true,
                'count' => $count,
                'message' => "{$count} résultats publiés avec succès"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Calcule des statistiques sur les résultats
     *
     * @param int $examen_id ID de l'examen
     * @return array Statistiques
     */
    public function calculerStatistiques($examen_id)
    {
        try {
            $resultats = Resultat::where('examen_id', $examen_id)
                ->with(['etudiant', 'ec'])
                ->get();

            if ($resultats->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Aucun résultat trouvé pour cet examen'
                ];
            }

            // Statistiques globales
            $statsGlobales = [
                'nombre_total' => $resultats->count(),
                'moyenne_generale' => round($resultats->avg('note'), 2),
                'note_min' => $resultats->min('note'),
                'note_max' => $resultats->max('note'),
                'nombre_reussis' => $resultats->filter(function($r) {
                    return $r->note >= 10;
                })->count(),
                'nombre_echoues' => $resultats->filter(function($r) {
                    return $r->note < 10;
                })->count(),
                'pourcentage_reussite' => round(($resultats->filter(function($r) {
                    return $r->note >= 10;
                })->count() / $resultats->count()) * 100, 2),
                'repartition' => [
                    'excellent' => $resultats->filter(function($r) { return $r->note >= 16; })->count(),
                    'tres_bien' => $resultats->filter(function($r) {
                        return $r->note >= 14 && $r->note < 16;
                    })->count(),
                    'bien' => $resultats->filter(function($r) {
                        return $r->note >= 12 && $r->note < 14;
                    })->count(),
                    'assez_bien' => $resultats->filter(function($r) {
                        return $r->note >= 10 && $r->note < 12;
                    })->count(),
                    'insuffisant' => $resultats->filter(function($r) {
                        return $r->note < 10;
                    })->count()
                ]
            ];

            // Statistiques par matière
            $statsParMatiere = [];

            $resultatsParEc = $resultats->groupBy('ec_id');

            foreach ($resultatsParEc as $ecId => $resultatsEc) {
                $ec = $resultatsEc->first()->ec;

                $statsParMatiere[$ecId] = [
                    'ec_id' => $ecId,
                    'ec_nom' => $ec->nom,
                    'nombre' => $resultatsEc->count(),
                    'moyenne' => round($resultatsEc->avg('note'), 2),
                    'note_min' => $resultatsEc->min('note'),
                    'note_max' => $resultatsEc->max('note'),
                    'nombre_reussis' => $resultatsEc->filter(function($r) {
                        return $r->note >= 10;
                    })->count(),
                    'nombre_echoues' => $resultatsEc->filter(function($r) {
                        return $r->note < 10;
                    })->count(),
                    'pourcentage_reussite' => round(($resultatsEc->filter(function($r) {
                        return $r->note >= 10;
                    })->count() / $resultatsEc->count()) * 100, 2)
                ];
            }

            // Moyennes par étudiant
            $moyennesParEtudiant = [];

            $resultatsParEtudiant = $resultats->groupBy('etudiant_id');

            foreach ($resultatsParEtudiant as $etudiantId => $resultatsEtudiant) {
                $etudiant = $resultatsEtudiant->first()->etudiant;

                $moyennesParEtudiant[$etudiantId] = [
                    'etudiant_id' => $etudiantId,
                    'etudiant_nom' => $etudiant->nom,
                    'etudiant_prenom' => $etudiant->prenom,
                    'moyenne' => round($resultatsEtudiant->avg('note'), 2),
                    'nombre_ecs' => $resultatsEtudiant->count(),
                    'nombre_reussis' => $resultatsEtudiant->filter(function($r) {
                        return $r->note >= 10;
                    })->count(),
                    'est_admis' => $resultatsEtudiant->filter(function($r) {
                        return $r->note >= 10;
                    })->count() == $resultatsEtudiant->count()
                ];
            }

            return [
                'success' => true,
                'statistiques_globales' => $statsGlobales,
                'statistiques_par_matiere' => $statsParMatiere,
                'moyennes_par_etudiant' => $moyennesParEtudiant
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
