<?php

namespace App\Services;

use App\Models\EC;
use App\Models\UE;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\Deliberation;
use App\Models\ResultatFinal;
use App\Models\ResultatFusion;
use App\Config\ReglesDeliberation;
use App\Models\AnneeUniversitaire;
use App\Models\DeliberationConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CalculAcademiqueService
{
    // ✅ Vos constantes existantes
    const CREDIT_TOTAL_REQUIS = 60;
    const CREDIT_MINIMUM_SESSION2 = 40;
    const SEUIL_VALIDATION_UE = 10.0;
    const NOTE_ELIMINATOIRE = 0;

    // ✅ CORRECTION : Filtrer par examen spécifique du niveau sélectionné
    public function calculerResultatsComplets($etudiantId, $sessionId, $useResultatFinal = true, $niveauId = null, $parcoursId = null)
    {
        try {
            // 1. Récupérer la session
            $session = SessionExam::findOrFail($sessionId);

            // 2. Récupérer l'étudiant
            $etudiant = Etudiant::findOrFail($etudiantId);

            // 3. ✅ CORRECTION : Récupérer l'examen spécifique pour ce niveau/parcours
            $examenQuery = Examen::where('niveau_id', $niveauId ?: $etudiant->niveau_id);
            if ($parcoursId !== null || $etudiant->parcours_id) {
                $examenQuery->where('parcours_id', $parcoursId ?: $etudiant->parcours_id);
            }
            
            $examen = $examenQuery->first();
            
            if (!$examen) {
                throw new \Exception("Aucun examen trouvé pour le niveau {$niveauId} et parcours {$parcoursId}");
            }

            // 4. ✅ CORRECTION : Filtrer strictement par cet examen spécifique
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            $query = $modelClass::where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('examen_id', $examen->id); // ✅ AJOUT CRUCIAL

            if ($useResultatFinal) {
                $query->whereIn('statut', [ResultatFinal::STATUT_PUBLIE, ResultatFinal::STATUT_EN_ATTENTE]);
            } else {
                $query->where('statut', 'valide');
            }

            $resultats = $query->with([
                    'ec' => function($query) {
                        $query->with(['ue' => function($subQuery) {
                            $subQuery->whereNotNull('id');
                        }]);
                    },
                    'etudiant', 
                    'codeAnonymat.sessionExam'
                ])
                ->orderBy('ec_id')
                ->get();

            if ($resultats->isEmpty()) {
                throw new \Exception("Aucun résultat trouvé pour l'étudiant {$etudiantId} dans l'examen {$examen->id} de la session {$sessionId}");
            }

            // ✅ Le reste du code reste identique...
            $resultatsValides = $resultats->filter(function($resultat) {
                return $resultat->ec && $resultat->ec->ue && $resultat->ec->ue->id;
            });

            if ($resultatsValides->isEmpty()) {
                throw new \Exception("Aucun résultat avec EC/UE valides pour l'étudiant {$etudiantId}");
            }

            $resultatsUE = $this->calculerResultatsUE_LogiqueMedecine($resultatsValides);
            $synthese = $this->calculerSyntheseGenerale($resultatsUE);
            $decision = $this->determinerDecision_LogiqueMedecine($synthese, $session);

            return [
                'etudiant' => [
                    'id' => $etudiant->id,
                    'matricule' => $etudiant->matricule,
                    'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'niveau' => $etudiant->niveau->nom ?? 'N/A',
                    'parcours' => $etudiant->parcours ? $etudiant->parcours->nom : null
                ],
                'session' => [
                    'id' => $session->id,
                    'type' => $session->type,
                    'nom' => $session->nom ?? "Session {$session->type}",
                    'annee' => $session->anneeUniversitaire->libelle ?? 'N/A',
                    'niveau_id' => $niveauId ?: $etudiant->niveau_id
                ],
                'examen' => [ // ✅ AJOUT
                    'id' => $examen->id,
                    'niveau_id' => $examen->niveau_id,
                    'parcours_id' => $examen->parcours_id
                ],
                'resultats_ue' => $resultatsUE,
                'synthese' => $synthese,
                'decision' => $decision,
                'metadonnees' => [
                    'date_calcul' => now()->format('Y-m-d H:i:s'),
                    'methode' => 'logique_medecine_avec_examen_specifique',
                    'nb_ue' => count($resultatsUE),
                    'nb_ec' => $resultatsValides->count(),
                    'session_exam_id' => $sessionId,
                    'examen_id' => $examen->id // ✅ AJOUT
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erreur calcul résultats complets avec examen spécifique', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors du calcul des résultats: ' . $e->getMessage());
        }
    }


    // ✅ MÉTHODE : Calcule les résultats par UE selon logique médecine
    private function calculerResultatsUE_LogiqueMedecine($resultats)
    {
        $resultatsUE = [];

        // Grouper par UE
        $resultatsParUE = $resultats->groupBy('ec.ue_id');

        foreach ($resultatsParUE as $ueId => $notesUE) {
            // ✅ VÉRIFICATIONS DE SÉCURITÉ
            if (!$ueId || $notesUE->isEmpty()) {
                Log::warning('UE ID manquant ou notes vides', ['ue_id' => $ueId]);
                continue;
            }

            $premierResultat = $notesUE->first();
            if (!$premierResultat || !$premierResultat->ec) {
                Log::warning('EC manquant pour UE', ['ue_id' => $ueId]);
                continue;
            }

            $ue = $premierResultat->ec->ue;
            if (!$ue || !$ue->id) {
                Log::warning('UE manquante ou invalide', [
                    'ue_id' => $ueId,
                    'ec_id' => $premierResultat->ec_id
                ]);
                continue;
            }

            // Récupérer toutes les notes et détails EC de l'UE
            $notes = $notesUE->pluck('note')->toArray();
            $detailsEC = [];
            $creditsECValides = 0;
            $creditsECTotaux = 0;
            
            $notesEC = $notesUE->map(function($resultat) use (&$detailsEC, &$creditsECValides, &$creditsECTotaux) {
                if (!$resultat->ec) {
                    return null;
                }
                
                $noteEC = $resultat->note;
                $creditsEC = $resultat->ec->credits ?? 0;
                $ecValidee = ($noteEC >= 10) && ($noteEC != 0); // EC validée si >= 10 et pas éliminatoire
                
                // ✅ NOUVEAU : Calculer crédits EC validés
                if ($ecValidee) {
                    $creditsECValides += $creditsEC;
                }
                $creditsECTotaux += $creditsEC;
                
                // ✅ NOUVEAU : Détail pour affichage
                $detailsEC[] = [
                    'ec_id' => $resultat->ec_id,
                    'ec_nom' => $resultat->ec->nom ?? 'EC_' . $resultat->ec_id,
                    'ec_abr' => $resultat->ec->abr ?? substr($resultat->ec->nom ?? 'EC', 0, 10),
                    'note' => $noteEC,
                    'credits_ec' => $creditsEC,
                    'ec_validee' => $ecValidee,
                    'est_eliminatoire' => $noteEC == self::NOTE_ELIMINATOIRE
                ];

                return [
                    'ec_id' => $resultat->ec_id,
                    'ec_nom' => $resultat->ec->nom ?? 'EC_' . $resultat->ec_id,
                    'ec_abr' => $resultat->ec->abr ?? substr($resultat->ec->nom ?? 'EC', 0, 10),
                    'note' => $noteEC,
                    'credits_ec' => $creditsEC,
                    'ec_validee' => $ecValidee,
                    'est_eliminatoire' => $noteEC == self::NOTE_ELIMINATOIRE
                ];
            })->filter()->toArray();

            // Vérifier qu'on a des notes valides
            if (empty($notes) || empty($notesEC)) {
                Log::warning('Aucune note valide pour UE', ['ue_id' => $ueId]);
                continue;
            }

            // ✅ CORRECTION : Toujours calculer la vraie moyenne
            $moyenneUE = count($notes) > 0 ? array_sum($notes) / count($notes) : 0;
            $moyenneUE = round($moyenneUE, 2);

            // ✅ VÉRIFICATION : Est-ce qu'il y a une note éliminatoire (0)
            $hasNoteEliminatoire = in_array(self::NOTE_ELIMINATOIRE, $notes);

            // ✅ VALIDATION UE : Une UE avec note éliminatoire n'est jamais validée
            if ($hasNoteEliminatoire) {
                $ueValidee = false;
                $statutUE = 'eliminee';
                $creditsUEValides = 0; // UE éliminée = 0 crédit validé pour l'UE
            } else {
                // ✅ VALIDATION UE selon seuil (10.0)
                $ueValidee = $moyenneUE >= self::SEUIL_VALIDATION_UE;
                $statutUE = $ueValidee ? 'validee' : 'non_validee';
                
                // ✅ NOUVEAU : Crédits UE validés
                if ($ueValidee) {
                    $creditsUEValides = $ue->credits ?? 0; // Tous les crédits UE si UE validée
                } else {
                    $creditsUEValides = 0; // Aucun crédit UE si UE non validée
                }
            }

            $resultatsUE[] = [
                'ue_id' => $ueId,
                'ue_nom' => $ue->nom ?? 'UE_' . $ueId,
                'ue_abr' => $ue->abr ?? substr($ue->nom ?? 'UE', 0, 10),
                'ue_credits' => $ue->credits ?? 0,
                'moyenne_ue' => $moyenneUE,
                'validee' => $ueValidee,
                'credits_valides' => $creditsUEValides,
                'statut' => $statutUE,
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'notes_ec' => $notesEC,
                'nb_ec' => count($notesEC),
                
                // ✅ NOUVEAU : Détails crédits par EC
                'details_credits' => [
                    'credits_ec_valides' => $creditsECValides,
                    'credits_ec_totaux' => $creditsECTotaux,
                    'credits_ue_valides' => $creditsUEValides,
                    'credits_ue_totaux' => $ue->credits ?? 0,
                    'pourcentage_ec_valides' => $creditsECTotaux > 0 ? 
                        round(($creditsECValides / $creditsECTotaux) * 100, 1) : 0,
                    'details_ec' => $detailsEC
                ]
            ];
        }

        if (empty($resultatsUE)) {
            Log::warning('Aucun résultat UE calculé - vérifier les relations EC/UE');
        }

        return $resultatsUE;
    }

    // ✅ MÉTHODE : Calcule la synthèse générale
    private function calculerSyntheseGenerale($resultatsUE)
    {
        $totalCredits = array_sum(array_column($resultatsUE, 'ue_credits'));
        $creditsValides = array_sum(array_column($resultatsUE, 'credits_valides'));

        // Vérifier s'il y a des notes éliminatoires
        $hasNoteEliminatoire = collect($resultatsUE)->contains('has_note_eliminatoire', true);

        // ✅ CORRECTION : Toujours calculer la vraie moyenne générale
        $moyennesUE = array_column($resultatsUE, 'moyenne_ue');
        $moyenneGenerale = count($moyennesUE) > 0 ?
            array_sum($moyennesUE) / count($moyennesUE) : 0;
        $moyenneGenerale = round($moyenneGenerale, 2);

        // Statistiques UE
        $nbUE = count($resultatsUE);
        $nbUEValidees = count(array_filter($resultatsUE, fn($ue) => $ue['validee']));
        $nbUEEliminees = count(array_filter($resultatsUE, fn($ue) => $ue['has_note_eliminatoire']));

        return [
            'moyenne_generale' => $moyenneGenerale, // ✅ VRAIE MOYENNE TOUJOURS
            'moyenne_generale_reelle' => $moyenneGenerale, // ✅ AJOUT : Moyenne réelle
            'credits_valides' => $creditsValides,
            'total_credits' => $totalCredits,
            'pourcentage_credits' => $totalCredits > 0 ?
                round(($creditsValides / $totalCredits) * 100, 2) : 0,
            'a_note_eliminatoire' => $hasNoteEliminatoire,
            'nb_ue_totales' => $nbUE,
            'nb_ue_validees' => $nbUEValidees,
            'nb_ue_eliminees' => $nbUEEliminees,
            'nb_ue_non_validees' => $nbUE - $nbUEValidees - $nbUEEliminees
        ];
    }

    
    // Dans App\Services\CalculAcademiqueService.php

    /**
     * ✅ NOUVELLE MÉTHODE : Vérifie si le niveau peut avoir des exclusions
     */
    private function niveauPeutExclure($niveauId)
    {
        try {
            $niveau = Niveau::find($niveauId);
            
            if (!$niveau) {
                return false; // Par défaut, pas d'exclusion si niveau non trouvé
            }
            
            // Seul PACES peut exclure
            return in_array($niveau->abr, ['PACES', 'L1']);
            
        } catch (\Exception $e) {
            Log::error('Erreur vérification niveau exclusion', [
                'niveau_id' => $niveauId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ✅ MÉTHODE MODIFIÉE : Détermine la décision selon logique médecine avec gestion niveau
     */
    private function determinerDecision_LogiqueMedecine($synthese, $session)
    {
        $creditsValides = $synthese['credits_valides'];
        $totalCreditsDisponibles = $synthese['total_credits'];
        $hasNoteEliminatoire = $synthese['a_note_eliminatoire'];
        $moyenneGenerale = $synthese['moyenne_generale'];
        
        // ✅ AJOUT : Récupérer le niveau depuis la session
        $niveauId = $session['niveau_id'] ?? null;
        $peutExclure = $this->niveauPeutExclure($niveauId);

        if ($session['type'] === 'Normale') {
            // SESSION NORMALE
            
            if ($hasNoteEliminatoire) {
                return [
                    'code' => 'rattrapage',
                    'libelle' => 'Autorisé(e) au rattrapage',
                    'motif' => 'Présence de note(s) éliminatoire(s)',
                    'credits_requis' => $totalCreditsDisponibles,
                    'credits_obtenus' => $creditsValides,
                    'moyenne_reelle' => $moyenneGenerale,
                    'conforme_criteres' => false
                ];
            }

            if ($moyenneGenerale >= 10.0 && $creditsValides >= $totalCreditsDisponibles && $totalCreditsDisponibles > 0) {
                return [
                    'code' => 'admis',
                    'libelle' => 'Admis(e)',
                    'motif' => 'Moyenne suffisante et validation de tous les crédits',
                    'credits_requis' => $totalCreditsDisponibles,
                    'credits_obtenus' => $creditsValides,
                    'moyenne_reelle' => $moyenneGenerale,
                    'conforme_criteres' => true
                ];
            }

            if ($moyenneGenerale < 10.0) {
                return [
                    'code' => 'rattrapage',
                    'libelle' => 'Autorisé(e) au rattrapage',
                    'motif' => 'Moyenne générale insuffisante (' . $moyenneGenerale . ' < 10.0)',
                    'credits_requis' => $totalCreditsDisponibles,
                    'credits_obtenus' => $creditsValides,
                    'moyenne_reelle' => $moyenneGenerale,
                    'conforme_criteres' => false
                ];
            }

            return [
                'code' => 'rattrapage',
                'libelle' => 'Autorisé(e) au rattrapage',
                'motif' => 'Crédits insuffisants (' . $creditsValides . '/' . $totalCreditsDisponibles . ')',
                'credits_requis' => $totalCreditsDisponibles,
                'credits_obtenus' => $creditsValides,
                'moyenne_reelle' => $moyenneGenerale,
                'conforme_criteres' => false
            ];

        } else {
            // ✅ SESSION 2 (rattrapage) - LOGIQUE ADAPTÉE SELON NIVEAU
            
            // ✅ MODIFICATION : Exclusion seulement pour PACES
            if ($hasNoteEliminatoire && $peutExclure) {
                return [
                    'code' => 'exclus',
                    'libelle' => 'Exclu(e)',
                    'motif' => 'Note éliminatoire en session de rattrapage',
                    'credits_requis' => $totalCreditsDisponibles,
                    'credits_obtenus' => $creditsValides,
                    'moyenne_reelle' => $moyenneGenerale,
                    'conforme_criteres' => false
                ];
            } elseif ($hasNoteEliminatoire && !$peutExclure) {
                // ✅ Pour L2+ : Note éliminatoire = redoublant
                return [
                    'code' => 'redoublant',
                    'libelle' => 'Autorisé(e) à redoubler',
                    'motif' => 'Note éliminatoire en session de rattrapage',
                    'credits_requis' => $totalCreditsDisponibles,
                    'credits_obtenus' => $creditsValides,
                    'moyenne_reelle' => $moyenneGenerale,
                    'conforme_criteres' => false
                ];
            }

            $seuilAdmission = $totalCreditsDisponibles;
            $seuilRedoublement = round($totalCreditsDisponibles * 0.67);

            if ($moyenneGenerale >= 10.0 && $creditsValides >= $seuilAdmission) {
                return [
                    'code' => 'admis',
                    'libelle' => 'Admis(e)',
                    'motif' => 'Moyenne suffisante et validation complète en rattrapage',
                    'credits_requis' => $seuilAdmission,
                    'credits_obtenus' => $creditsValides,
                    'moyenne_reelle' => $moyenneGenerale,
                    'conforme_criteres' => true
                ];
            }
            
            if ($moyenneGenerale < 10.0) {
                if ($creditsValides >= $seuilRedoublement) {
                    return [
                        'code' => 'redoublant',
                        'libelle' => 'Autorisé(e) à redoubler',
                        'motif' => 'Moyenne insuffisante (' . $moyenneGenerale . ' < 10.0) mais crédits partiels',
                        'credits_requis' => $seuilAdmission,
                        'credits_obtenus' => $creditsValides,
                        'moyenne_reelle' => $moyenneGenerale,
                        'conforme_criteres' => false
                    ];
                } else {
                    // ✅ MODIFICATION : Exclusion uniquement pour PACES
                    if ($peutExclure) {
                        return [
                            'code' => 'exclus',
                            'libelle' => 'Exclu(e)',
                            'motif' => 'Moyenne et crédits insuffisants en rattrapage',
                            'credits_requis' => $seuilRedoublement,
                            'credits_obtenus' => $creditsValides,
                            'moyenne_reelle' => $moyenneGenerale,
                            'conforme_criteres' => false
                        ];
                    } else {
                        // Pour L2+ : redoublant au lieu d'exclus
                        return [
                            'code' => 'redoublant',
                            'libelle' => 'Autorisé(e) à redoubler',
                            'motif' => 'Moyenne et crédits insuffisants en rattrapage',
                            'credits_requis' => $seuilRedoublement,
                            'credits_obtenus' => $creditsValides,
                            'moyenne_reelle' => $moyenneGenerale,
                            'conforme_criteres' => false
                        ];
                    }
                }
            }
            
            if ($creditsValides >= $seuilRedoublement) {
                return [
                    'code' => 'redoublant',
                    'libelle' => 'Autorisé(e) à redoubler',
                    'motif' => 'Moyenne suffisante mais crédits incomplets pour admission',
                    'credits_requis' => $seuilAdmission,
                    'credits_obtenus' => $creditsValides,
                    'moyenne_reelle' => $moyenneGenerale,
                    'conforme_criteres' => false
                ];
            } else {
                // ✅ MODIFICATION : Dernière ligne aussi
                if ($peutExclure) {
                    return [
                        'code' => 'exclus',
                        'libelle' => 'Exclu(e)',
                        'motif' => 'Crédits très insuffisants en rattrapage',
                        'credits_requis' => $seuilRedoublement,
                        'credits_obtenus' => $creditsValides,
                        'moyenne_reelle' => $moyenneGenerale,
                        'conforme_criteres' => false
                    ];
                } else {
                    return [
                        'code' => 'redoublant',
                        'libelle' => 'Autorisé(e) à redoubler',
                        'motif' => 'Crédits très insuffisants en rattrapage',
                        'credits_requis' => $seuilRedoublement,
                        'credits_obtenus' => $creditsValides,
                        'moyenne_reelle' => $moyenneGenerale,
                        'conforme_criteres' => false
                    ];
                }
            }
        }
    }


    // ✅ NOUVELLE MÉTHODE : Applique la délibération selon la configuration
    public function appliquerDeliberationAvecConfig($niveauId, $parcoursId, $sessionId, $parametres = [])
    {
        try {
            DB::beginTransaction();

            // ✅ TROUVER L'EXAMEN SPÉCIFIQUE
            $examenQuery = Examen::where('niveau_id', $niveauId);
            if ($parcoursId) {
                $examenQuery->where('parcours_id', $parcoursId);
            }
            
            $examen = $examenQuery->first();
            if (!$examen) {
                throw new \Exception("Aucun examen trouvé pour le niveau {$niveauId} et parcours {$parcoursId}");
            }

            // 1. Récupérer ou créer la configuration de délibération
            $config = DeliberationConfig::getOrCreateConfig($niveauId, $parcoursId, $sessionId);

            // 2. Mettre à jour les paramètres si fournis
            if (!empty($parametres)) {
                $config->update($parametres);
            }

            // 3. Récupérer la session pour déterminer le type
            $session = SessionExam::findOrFail($sessionId);

            // 4. ✅ FILTRER PAR EXAMEN SPÉCIFIQUE
            $etudiantsIds = ResultatFinal::where('session_exam_id', $sessionId)
                ->where('examen_id', $examen->id) // ✅ AJOUT CRUCIAL
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->distinct('etudiant_id')
                ->pluck('etudiant_id');

            $statistiques = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'total' => $etudiantsIds->count()
            ];

            // 5. Appliquer les décisions selon la configuration
            foreach ($etudiantsIds as $etudiantId) {
                $nouvelleDecision = $this->calculerDecisionAvecConfig($etudiantId, $sessionId, $config, $niveauId, $parcoursId);

                $this->mettreAJourResultatsEtudiantDeliberation($etudiantId, $sessionId, $nouvelleDecision, $config->id, $examen->id);

                $statistiques[$nouvelleDecision]++;
            }

            // 6. Marquer la configuration comme délibérée
            $config->marquerDelibere(Auth::id());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Délibération appliquée avec succès',
                'statistiques' => $statistiques,
                'config' => $config,
                'examen_id' => $examen->id
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Erreur lors de la délibération avec config: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erreur lors de la délibération: ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }


    private function calculerDecisionAvecConfig($etudiantId, $sessionId, DeliberationConfig $config, $niveauId, $parcoursId)
    {
        $resultat = $this->calculerResultatsComplets($etudiantId, $sessionId, true, $niveauId, $parcoursId);

        $creditsValides = $resultat['synthese']['credits_valides'];
        $hasNoteEliminatoire = $resultat['synthese']['a_note_eliminatoire'];
        $moyenneGenerale = $resultat['synthese']['moyenne_generale'];
        $session = $resultat['session'];

        if ($session['type'] === 'Normale') {
            // SESSION 1 - RÈGLE ACADÉMIQUE STRICTE
            
            if ($hasNoteEliminatoire) {
                return 'rattrapage';
            }

            if ($moyenneGenerale >= 10.0 && $creditsValides >= $config->credits_admission_s1) {
                return 'admis';
            }
            
            if ($moyenneGenerale < 10.0) {
                return 'rattrapage';
            }

            return 'rattrapage';

        } else {
            // SESSION 2 - RÈGLE ACADÉMIQUE STRICTE
            
            // ✅ CORRECTION : Utiliser 'exclus' au lieu de 'excluss'
            if ($hasNoteEliminatoire) {
                return 'exclus'; // ✅ CORRIGÉ
            }

            if ($moyenneGenerale >= 10.0 && $creditsValides >= $config->credits_admission_s2) {
                return 'admis';
            }
            
            if ($moyenneGenerale < 10.0) {
                if ($creditsValides >= $config->credits_redoublement_s2) {
                    return 'redoublant';
                } else {
                    return 'exclus'; // ✅ CORRIGÉ
                }
            }
            
            if ($creditsValides >= $config->credits_redoublement_s2) {
                return 'redoublant';
            }
            
            return 'exclus'; // ✅ CORRIGÉ
        }
    }



    // ✅ NOUVELLE MÉTHODE : Validation stricte des critères d'admission
    public function validerCriteresAdmission($etudiantId, $sessionId, $configDeliberation, $niveauId = null, $parcoursId = null)
    {
        try {
            $resultat = $this->calculerResultatsComplets($etudiantId, $sessionId, true, $niveauId, $parcoursId);
            
            $moyenneGenerale = $resultat['synthese']['moyenne_generale'];
            $creditsValides = $resultat['synthese']['credits_valides'];
            $hasNoteEliminatoire = $resultat['synthese']['a_note_eliminatoire'];
            $sessionType = $resultat['session']['type'];
            
            $erreurs = [];
            $avertissements = [];
            
            // Critères selon le type de session
            if ($sessionType === 'Normale') {
                $moyenneRequise = 10.0;
                $creditsRequis = $configDeliberation->credits_admission_s1 ?? 60;
                
                // Validation moyenne
                if ($moyenneGenerale < $moyenneRequise) {
                    $erreurs[] = "Moyenne insuffisante: {$moyenneGenerale} < {$moyenneRequise}";
                }
                
                // Validation crédits
                if ($creditsValides < $creditsRequis) {
                    $erreurs[] = "Crédits insuffisants: {$creditsValides} < {$creditsRequis}";
                }
                
                // Validation note éliminatoire
                if ($hasNoteEliminatoire) {
                    $erreurs[] = "Présence de note(s) éliminatoire(s)";
                }
                
            } else {
                // Session 2
                $moyenneRequise = 10.0;
                $creditsRequis = $configDeliberation->credits_admission_s2 ?? 40;
                
                if ($moyenneGenerale < $moyenneRequise) {
                    $erreurs[] = "Moyenne insuffisante: {$moyenneGenerale} < {$moyenneRequise}";
                }
                
                if ($creditsValides < $creditsRequis) {
                    $erreurs[] = "Crédits insuffisants: {$creditsValides} < {$creditsRequis}";
                }
                
                if ($hasNoteEliminatoire) {
                    $erreurs[] = "Note éliminatoire en session de rattrapage";
                }
            }
            
            $peutEtreAdmis = empty($erreurs);
            
            return [
                'peut_etre_admis' => $peutEtreAdmis,
                'erreurs' => $erreurs,
                'avertissements' => $avertissements,
                'details' => [
                    'moyenne' => $moyenneGenerale,
                    'moyenne_requise' => $moyenneRequise ?? 10.0,
                    'credits' => $creditsValides,
                    'credits_requis' => $creditsRequis ?? 60,
                    'has_eliminatoire' => $hasNoteEliminatoire,
                    'session_type' => $sessionType
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur validation critères admission', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'peut_etre_admis' => false,
                'erreurs' => ['Erreur lors de la validation: ' . $e->getMessage()],
                'avertissements' => [],
                'details' => []
            ];
        }
    }


    // ✅ NOUVELLE MÉTHODE : Met à jour les résultats avec traçabilité délibération
    private function mettreAJourResultatsEtudiantDeliberation($etudiantId, $sessionId, $nouvelleDecision, $configId, $examenId = null)
    {
        $query = ResultatFinal::where('session_exam_id', $sessionId)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE);
        
        // ✅ FILTRER PAR EXAMEN SI FOURNI
        if ($examenId) {
            $query->where('examen_id', $examenId);
        }
        
        $resultats = $query->get();

        foreach ($resultats as $resultat) {
            $ancienneDecision = $resultat->decision;

            // ✅ MISE À JOUR avec force
            $updateData = [
                'decision' => $nouvelleDecision,
                'jury_validated' => true,
                'modifie_par' => Auth::id(),
                'updated_at' => now()
            ];

            $resultat->update($updateData);

            // Ajouter à l'historique JSON
            $statusHistory = $resultat->status_history ?? [];
            $statusHistory[] = [
                'type_action' => 'deliberation_appliquee',
                'decision_precedente' => $ancienneDecision,
                'decision_nouvelle' => $nouvelleDecision,
                'user_id' => Auth::id(),
                'date_action' => now()->toDateTimeString(),
                'config_deliberation_id' => $configId,
                'examen_id' => $examenId,
                'source' => 'deliberation_avec_configuration'
            ];

            $resultat->update(['status_history' => $statusHistory]);
        }
    }


    // ✅ NOUVELLE MÉTHODE : Annule une délibération
    public function annulerDeliberationAvecConfig($niveauId, $parcoursId, $sessionId)
    {
        try {
            DB::beginTransaction();

            $config = DeliberationConfig::where('niveau_id', $niveauId)
                ->where('parcours_id', $parcoursId)
                ->where('session_id', $sessionId)
                ->first();

            if (!$config || !$config->delibere) {
                throw new \Exception('Aucune délibération à annuler');
            }

            // Remettre jury_validated à false pour tous les résultats concernés
            ResultatFinal::where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                })
                ->update([
                    'jury_validated' => false,
                    'modifie_par' => Auth::id()
                ]);

            // Annuler la configuration
            $config->annulerDeliberation();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Délibération annulée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur annulation délibération: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ];
        }
    }

    // ✅ NOUVELLE MÉTHODE : Vérifie si une session est délibérée
    public function estDelibere($niveauId, $parcoursId, $sessionId)
    {
        $config = DeliberationConfig::where('niveau_id', $niveauId)
            ->where('parcours_id', $parcoursId)
            ->where('session_id', $sessionId)
            ->first();

        return $config && $config->delibere;
    }

    // ✅ MISE À JOUR DE VOTRE MÉTHODE EXISTANTE : appliquerDecisionsSession
    public function appliquerDecisionsSession($sessionId, $useResultatFinal = false, $avecConfiguration = false, $niveauId = null, $parcoursId = null)
    {
        // Si avec configuration, utiliser la nouvelle méthode
        if ($avecConfiguration && $niveauId) {
            return $this->appliquerDeliberationAvecConfig($niveauId, $parcoursId, $sessionId);
        }

        // Sinon, garder votre logique existante
        try {
            DB::beginTransaction();

            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            // Récupérer les étudiants distincts via session_exam_id
            $etudiantsIds = $modelClass::where('session_exam_id', $sessionId)
                ->distinct('etudiant_id')
                ->pluck('etudiant_id');

            // Initialiser les statistiques
            $stats = [
                'total_etudiants' => $etudiantsIds->count(),
                'decisions' => [
                    'admis' => 0,
                    'rattrapage' => 0,
                    'redoublant' => 0,
                    'exclus' => 0
                ],
                'erreurs' => []
            ];

            // Traiter chaque étudiant selon logique médecine
            foreach ($etudiantsIds as $etudiantId) {
                try {
                    // Calculer les résultats complets selon logique médecine
                    $resultat = $this->calculerResultatsComplets($etudiantId, $sessionId, $useResultatFinal);
                    $decision = $resultat['decision']['code'];

                    // Appliquer la décision
                    $success = $this->appliquerDecision($etudiantId, $sessionId, $decision, $useResultatFinal);

                    if ($success) {
                        $stats['decisions'][$decision]++;
                    } else {
                        $stats['erreurs'][] = "Échec application décision pour étudiant $etudiantId";
                    }
                } catch (\Exception $e) {
                    $stats['erreurs'][] = "Erreur étudiant $etudiantId: " . $e->getMessage();
                    Log::error('Erreur application décision médecine', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => empty($stats['erreurs']),
                'message' => empty($stats['erreurs'])
                    ? "Décisions appliquées selon logique médecine pour {$stats['total_etudiants']} étudiants."
                    : "Décisions appliquées avec " . count($stats['erreurs']) . " erreurs.",
                'statistiques' => $stats
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'application des décisions médecine', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'application des décisions : ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }

    // ✅ MISE À JOUR DE VOTRE MÉTHODE EXISTANTE : appliquerDecision
    public function appliquerDecision($etudiantId, $sessionId, $decision, $useResultatFinal = false, $avecDeliberation = false)
    {
        try {
            DB::beginTransaction();

            // Vérifier la validité de la décision
            if (!in_array($decision, ['admis', 'rattrapage', 'redoublant', 'exclus'])) {
                throw new \Exception("Décision invalide : $decision");
            }

            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            // Récupérer les résultats de l'étudiant pour cette session via session_exam_id
            $resultats = $modelClass::where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->get();

            if ($resultats->isEmpty()) {
                Log::warning("Aucun résultat trouvé pour l'étudiant", [
                    'etudiant_id' => $etudiantId,
                    'session_id' => $sessionId
                ]);
                return false;
            }

            // Mettre à jour chaque résultat
            foreach ($resultats as $resultat) {
                $ancienneDecision = $resultat->decision;

                $updateData = [
                    'decision' => $decision,
                    'modifie_par' => Auth::id()
                ];

                // ✅ NOUVEAU : Si c'est une délibération, marquer jury_validated = true
                if ($avecDeliberation) {
                    $updateData['jury_validated'] = true;
                }

                $resultat->update($updateData);

                // Si c'est un résultat final, mettre à jour l'historique
                if ($useResultatFinal) {
                    $historique = $resultat->status_history ?? [];
                    if (!is_array($historique)) {
                        $historique = json_decode($historique, true) ?? [];
                    }

                    // Ajouter l'entrée de décision dans l'historique
                    $historique[] = [
                        'type_action' => $avecDeliberation ? 'decision_deliberation' : 'decision_appliquee',
                        'decision_precedente' => $ancienneDecision,
                        'decision_nouvelle' => $decision,
                        'user_id' => Auth::id() ?? 1,
                        'date_action' => now()->toDateTimeString(),
                        'methode' => $avecDeliberation ? 'deliberation_avec_config' : 'logique_medecine_automatique',
                        'jury_validated' => $avecDeliberation
                    ];

                    $resultat->status_history = $historique;
                    $resultat->save();
                }
            }

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'application de la décision', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'decision' => $decision,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    // ✅ NOUVELLE MÉTHODE : Récupère la configuration de délibération
    public function getConfigurationDeliberation($niveauId, $parcoursId, $sessionId)
    {
        return DeliberationConfig::where('niveau_id', $niveauId)
            ->where('parcours_id', $parcoursId)
            ->where('session_id', $sessionId)
            ->with(['niveau', 'parcours', 'session', 'deliberePar'])
            ->first();
    }

    // ✅ NOUVELLE MÉTHODE : Récupère toutes les configurations de délibération actives
    public function getConfigurationsDeliberationActives($sessionId = null)
    {
        $query = DeliberationConfig::with(['niveau', 'parcours', 'session', 'deliberePar']);

        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        return $query->orderBy('delibere', 'desc')
            ->orderBy('date_deliberation', 'desc')
            ->get();
    }

    // ✅ NOUVELLE MÉTHODE : Obtient les statistiques de délibération
    public function getStatistiquesDeliberation($niveauId, $parcoursId, $sessionId)
    {
        try {
            $config = $this->getConfigurationDeliberation($niveauId, $parcoursId, $sessionId);

            if (!$config) {
                return [
                    'configuration_existante' => false,
                    'delibere' => false,
                    'statistiques' => []
                ];
            }

            // Compter les résultats par décision
            $stats = ResultatFinal::where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->selectRaw('
                    decision,
                    COUNT(DISTINCT etudiant_id) as nb_etudiants,
                    COUNT(CASE WHEN jury_validated = 1 THEN 1 END) as nb_valides_jury
                ')
                ->groupBy('decision')
                ->get()
                ->keyBy('decision');

            $totalEtudiants = $stats->sum('nb_etudiants');
            $totalValidesJury = $stats->sum('nb_valides_jury');

            return [
                'configuration_existante' => true,
                'delibere' => $config->delibere,
                'date_deliberation' => $config->date_deliberation,
                'delibere_par' => $config->deliberePar?->name,
                'parametres' => [
                    'credits_admission_s1' => $config->credits_admission_s1,
                    'credits_admission_s2' => $config->credits_admission_s2,
                    'credits_redoublement_s2' => $config->credits_redoublement_s2,
                    'note_eliminatoire_bloque_s1' => $config->note_eliminatoire_bloque_s1,
                    'note_eliminatoire_exclusion_s2' => $config->note_eliminatoire_exclusion_s2,
                ],
                'statistiques' => [
                    'total_etudiants' => $totalEtudiants,
                    'total_valides_jury' => $totalValidesJury,
                    'pourcentage_valides_jury' => $totalEtudiants > 0 ?
                        round(($totalValidesJury / $totalEtudiants) * 100, 2) : 0,
                    'decisions' => [
                        'admis' => $stats->get('admis')?->nb_etudiants ?? 0,
                        'rattrapage' => $stats->get('rattrapage')?->nb_etudiants ?? 0,
                        'redoublant' => $stats->get('redoublant')?->nb_etudiants ?? 0,
                        'exclus' => $stats->get('exclus')?->nb_etudiants ?? 0,
                    ],
                    'decisions_validees_jury' => [
                        'admis' => $stats->get('admis')?->nb_valides_jury ?? 0,
                        'rattrapage' => $stats->get('rattrapage')?->nb_valides_jury ?? 0,
                        'redoublant' => $stats->get('redoublant')?->nb_valides_jury ?? 0,
                        'exclus' => $stats->get('exclus')?->nb_valides_jury ?? 0,
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erreur récupération statistiques délibération', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'configuration_existante' => false,
                'delibere' => false,
                'statistiques' => [],
                'erreur' => $e->getMessage()
            ];
        }
    }

    // ✅ NOUVELLE MÉTHODE : Simule une délibération sans l'appliquer
    public function simulerDeliberationAvecConfig($niveauId, $parcoursId, $sessionId, $parametresSimulation)
    {
        try {
            // Créer une config temporaire pour la simulation
            $configTemp = new DeliberationConfig($parametresSimulation);
            $configTemp->niveau_id = $niveauId;
            $configTemp->parcours_id = $parcoursId;
            $configTemp->session_id = $sessionId;

            // Récupérer tous les étudiants concernés
            $etudiantsIds = ResultatFinal::where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->distinct('etudiant_id')
                ->pluck('etudiant_id');

            $resultatsSimulation = [];
            $statistiques = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'changements' => 0
            ];

            foreach ($etudiantsIds as $etudiantId) {
                // Calculer la décision actuelle et simulée
                $decisionActuelle = $this->getDecisionActuelleEtudiant($etudiantId, $sessionId);
                $decisionSimulee = $this->calculerDecisionAvecConfig($etudiantId, $sessionId, $configTemp);

                $changement = $decisionActuelle !== $decisionSimulee;

                if ($changement) {
                    $statistiques['changements']++;
                }

                $statistiques[$decisionSimulee]++;

                $resultatsSimulation[] = [
                    'etudiant_id' => $etudiantId,
                    'decision_actuelle' => $decisionActuelle,
                    'decision_simulee' => $decisionSimulee,
                    'changement' => $changement
                ];
            }

            return [
                'success' => true,
                'total_etudiants' => $etudiantsIds->count(),
                'statistiques' => $statistiques,
                'resultats_detailles' => $resultatsSimulation,
                'parametres_simulation' => $parametresSimulation
            ];

        } catch (\Exception $e) {
            Log::error('Erreur simulation délibération', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la simulation: ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }

    // ✅ MÉTHODE UTILITAIRE : Récupère la décision actuelle d'un étudiant
    private function getDecisionActuelleEtudiant($etudiantId, $sessionId)
    {
        $resultat = ResultatFinal::where('session_exam_id', $sessionId)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->first();

        return $resultat ? $resultat->decision : 'rattrapage';
    }

    // ✅ NOUVELLE MÉTHODE : Valide la cohérence d'une configuration
    public function validerCoherenceConfiguration($parametres)
    {
        $erreurs = [];

        // Validation crédits session 1
        if ($parametres['credits_admission_s1'] < 40 || $parametres['credits_admission_s1'] > 60) {
            $erreurs[] = 'Les crédits d\'admission session 1 doivent être entre 40 et 60';
        }

        // Validation crédits session 2
        if ($parametres['credits_admission_s2'] < 30 || $parametres['credits_admission_s2'] > 50) {
            $erreurs[] = 'Les crédits d\'admission session 2 doivent être entre 30 et 50';
        }

        // Validation cohérence session 2
        if ($parametres['credits_redoublement_s2'] >= $parametres['credits_admission_s2']) {
            $erreurs[] = 'Les crédits de redoublement doivent être inférieurs aux crédits d\'admission session 2';
        }

        // Validation logique médecine
        if ($parametres['credits_admission_s1'] != 60) {
            $erreurs[] = 'ATTENTION: La logique médecine standard requiert 60 crédits en session 1';
        }

        if ($parametres['credits_admission_s2'] != 40) {
            $erreurs[] = 'ATTENTION: La logique médecine standard requiert 40 crédits en session 2';
        }

        return $erreurs;
    }

    // ✅ NOUVELLE MÉTHODE : Exporte les résultats de délibération
    public function exporterResultatsDeliberation($niveauId, $parcoursId, $sessionId, $format = 'array')
    {
        try {
            $config = $this->getConfigurationDeliberation($niveauId, $parcoursId, $sessionId);
            $stats = $this->getStatistiquesDeliberation($niveauId, $parcoursId, $sessionId);

            // Récupérer les résultats détaillés
            $resultats = ResultatFinal::where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->with(['etudiant', 'ec.ue'])
                ->get()
                ->groupBy('etudiant_id');

            $exportData = [];

            foreach ($resultats as $etudiantId => $resultatsEtudiant) {
                $etudiant = $resultatsEtudiant->first()->etudiant;
                $resultatComplet = $this->calculerResultatsComplets($etudiantId, $sessionId, true);

                $exportData[] = [
                    'etudiant' => [
                        'id' => $etudiant->id,
                        'matricule' => $etudiant->matricule,
                        'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                        'niveau' => $etudiant->niveau->nom ?? 'N/A',
                        'parcours' => $etudiant->parcours ? $etudiant->parcours->nom : null
                    ],
                    'resultats_academiques' => $resultatComplet['synthese'],
                    'decision' => $resultatComplet['decision']['code'],
                    'jury_validated' => $resultatsEtudiant->first()->jury_validated ?? false,
                    'date_derniere_modification' => $resultatsEtudiant->max('updated_at')
                ];
            }

            $rapport = [
                'configuration' => $config ? $config->toArray() : null,
                'statistiques' => $stats,
                'resultats_etudiants' => $exportData,
                'metadonnees' => [
                    'date_export' => now()->format('Y-m-d H:i:s'),
                    'export_par' => Auth::user()->name ?? 'Système',
                    'niveau_id' => $niveauId,
                    'parcours_id' => $parcoursId,
                    'session_id' => $sessionId
                ]
            ];

            Log::info('Export délibération généré', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'nb_etudiants' => count($exportData)
            ]);

            return $rapport;

        } catch (\Exception $e) {
            Log::error('Erreur export délibération', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    // ✅ MÉTHODES UTILITAIRES SUPPLÉMENTAIRES

    /**
     * Calcule les statistiques globales d'une session
     */
    public function calculerStatistiquesGlobales($sessionId, $niveauId = null, $parcoursId = null)
    {
        try {
            $query = ResultatFinal::where('session_exam_id', $sessionId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE);

            if ($niveauId) {
                $query->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                });
            }

            $resultats = $query->get();

            if ($resultats->isEmpty()) {
                return [
                    'total_etudiants' => 0,
                    'decisions' => [],
                    'moyennes' => [],
                    'credits' => []
                ];
            }

            // Grouper par étudiant
            $etudiantsStats = $resultats->groupBy('etudiant_id')->map(function($resultatsEtudiant) use ($sessionId) {
                $etudiantId = $resultatsEtudiant->first()->etudiant_id;

                try {
                    $calcul = $this->calculerResultatsComplets($etudiantId, $sessionId, true);
                    return [
                        'decision' => $calcul['decision']['code'],
                        'moyenne' => $calcul['synthese']['moyenne_generale'],
                        'credits' => $calcul['synthese']['credits_valides'],
                        'has_eliminatoire' => $calcul['synthese']['a_note_eliminatoire']
                    ];
                } catch (\Exception $e) {
                    Log::warning('Erreur calcul stats pour étudiant', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionId,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter();

            // Calculer les statistiques finales
            $decisions = $etudiantsStats->pluck('decision')->countBy();
            $moyennes = $etudiantsStats->pluck('moyenne')->filter();
            $credits = $etudiantsStats->pluck('credits')->filter();

            return [
                'total_etudiants' => $etudiantsStats->count(),
                'decisions' => [
                    'admis' => $decisions->get('admis', 0),
                    'rattrapage' => $decisions->get('rattrapage', 0),
                    'redoublant' => $decisions->get('redoublant', 0),
                    'exclus' => $decisions->get('exclus', 0)
                ],
                'moyennes' => [
                    'moyenne_generale' => $moyennes->isNotEmpty() ? round($moyennes->avg(), 2) : 0,
                    'moyenne_min' => $moyennes->isNotEmpty() ? round($moyennes->min(), 2) : 0,
                    'moyenne_max' => $moyennes->isNotEmpty() ? round($moyennes->max(), 2) : 0
                ],
                'credits' => [
                    'credits_moyen' => $credits->isNotEmpty() ? round($credits->avg(), 2) : 0,
                    'credits_min' => $credits->isNotEmpty() ? $credits->min() : 0,
                    'credits_max' => $credits->isNotEmpty() ? $credits->max() : 0
                ],
                'taux_reussite' => $etudiantsStats->count() > 0 ?
                    round(($decisions->get('admis', 0) / $etudiantsStats->count()) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques globales', [
                'session_id' => $sessionId,
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors du calcul des statistiques: ' . $e->getMessage());
        }
    }

    /**
     * Vérifie la cohérence des calculs selon logique médecine
     */
    public function validerCoherenceCalculsMedecine($etudiantId, $sessionId)
    {
        try {
            $resultats = $this->calculerResultatsComplets($etudiantId, $sessionId, true);

            $erreurs = [];
            $avertissements = [];

            // Vérifier cohérence credits/moyennes
            $synthese = $resultats['synthese'];

            if ($synthese['a_note_eliminatoire'] && $synthese['moyenne_generale'] > 0) {
                $erreurs[] = 'Incohérence: note éliminatoire présente mais moyenne > 0';
            }

            if ($synthese['credits_valides'] > $synthese['total_credits']) {
                $erreurs[] = 'Erreur: crédits validés > crédits totaux';
            }

            // Vérifier cohérence décision
            $decision = $resultats['decision']['code'];
            $session = $resultats['session'];

            if ($session['type'] === 'Normale') {
                if ($decision === 'admis' && $synthese['credits_valides'] < self::CREDIT_TOTAL_REQUIS) {
                    $erreurs[] = 'Incohérence S1: admis avec moins de 60 crédits';
                }
                if ($decision === 'admis' && $synthese['a_note_eliminatoire']) {
                    $avertissements[] = 'Attention S1: admis malgré note éliminatoire';
                }
            } else {
                if ($decision === 'admis' && $synthese['credits_valides'] < self::CREDIT_MINIMUM_SESSION2) {
                    $erreurs[] = 'Incohérence S2: admis avec moins de 40 crédits';
                }
                if ($decision === 'exclus' && !$synthese['a_note_eliminatoire'] && $synthese['credits_valides'] >= 20) {
                    $avertissements[] = 'Attention S2: exclusion sans note éliminatoire avec crédits suffisants';
                }
            }

            return [
                'valide' => empty($erreurs),
                'erreurs' => $erreurs,
                'avertissements' => $avertissements,
                'resultats' => $resultats
            ];

        } catch (\Exception $e) {
            return [
                'valide' => false,
                'erreurs' => ['Erreur lors de la validation: ' . $e->getMessage()],
                'avertissements' => [],
                'resultats' => null
            ];
        }
    }

    /**
     * Récupère les étudiants éligibles au rattrapage
     */
    public function getEtudiantsEligiblesRattrapage($sessionNormaleId, $niveauId = null, $parcoursId = null)
    {
        try {
            $query = ResultatFinal::where('session_exam_id', $sessionNormaleId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->where('decision', 'rattrapage');

            if ($niveauId) {
                $query->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                });
            }

            $etudiantsRattrapage = $query->distinct('etudiant_id')
                ->with(['etudiant'])
                ->get()
                ->pluck('etudiant_id')
                ->unique();

            return $etudiantsRattrapage->map(function($etudiantId) use ($sessionNormaleId) {
                try {
                    $resultats = $this->calculerResultatsComplets($etudiantId, $sessionNormaleId, true);
                    return [
                        'etudiant' => $resultats['etudiant'],
                        'synthese' => $resultats['synthese'],
                        'eligible' => true,
                        'motif_rattrapage' => $resultats['decision']['motif']
                    ];
                } catch (\Exception $e) {
                    Log::warning('Erreur calcul éligibilité rattrapage', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionNormaleId,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter()->values();

        } catch (\Exception $e) {
            Log::error('Erreur récupération étudiants rattrapage', [
                'session_normale_id' => $sessionNormaleId,
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors de la récupération des étudiants éligibles: ' . $e->getMessage());
        }
    }


    public function corrigerToutesLesDecisions()
    {
        try {
            DB::beginTransaction();
            
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive) {
                return ['success' => false, 'message' => 'Aucune année active'];
            }

            $sessions = SessionExam::where('annee_universitaire_id', $anneeActive->id)->get();
            $stats = ['admis' => 0, 'rattrapage' => 0, 'redoublant' => 0, 'exclus' => 0, 'corriges' => 0];

            foreach ($sessions as $session) {
                Log::info("🔧 Correction session: {$session->type} (ID: {$session->id})");
                
                $etudiantsIds = ResultatFinal::where('session_exam_id', $session->id)
                    ->where('statut', ResultatFinal::STATUT_PUBLIE)
                    ->distinct('etudiant_id')
                    ->pluck('etudiant_id');

                foreach ($etudiantsIds as $etudiantId) {
                    try {
                        $etudiant = Etudiant::find($etudiantId);
                        if (!$etudiant) continue;

                        $resultats = $this->calculerResultatsComplets(
                            $etudiantId, 
                            $session->id, 
                            true, 
                            $etudiant->niveau_id, 
                            $etudiant->parcours_id
                        );
                        
                        $nouvelleDecision = $resultats['decision']['code'];
                        
                        $ancienneDecision = ResultatFinal::where('session_exam_id', $session->id)
                            ->where('etudiant_id', $etudiantId)
                            ->where('statut', ResultatFinal::STATUT_PUBLIE)
                            ->first()?->decision;

                        if ($ancienneDecision !== $nouvelleDecision) {
                            $updated = ResultatFinal::where('session_exam_id', $session->id)
                                ->where('etudiant_id', $etudiantId)
                                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                                ->update([
                                    'decision' => $nouvelleDecision,
                                    'modifie_par' => Auth::id() ?? 1,
                                    'updated_at' => now()
                                ]);
                            
                            if ($updated > 0) {
                                $stats['corriges']++;
                                Log::info("✅ Étudiant {$etudiantId}: {$ancienneDecision} → {$nouvelleDecision}");
                            }
                        }

                        $stats[$nouvelleDecision]++;

                    } catch (\Exception $e) {
                        Log::error("❌ Erreur recalcul étudiant {$etudiantId}: " . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'statistiques' => $stats,
                'message' => "Correction terminée ! {$stats['corriges']} étudiants corrigés."
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Erreur correction globale: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
