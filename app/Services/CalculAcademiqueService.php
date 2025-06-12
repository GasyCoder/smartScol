<?php

namespace App\Services;

use App\Models\EC;
use App\Models\UE;
use App\Models\Niveau;
use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\Deliberation;
use App\Models\ResultatFinal;
use App\Models\ResultatFusion;
use App\Config\ReglesDeliberation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CalculAcademiqueService
{
    // Constantes selon la logique exacte de la faculté de médecine
    const CREDIT_TOTAL_REQUIS = 60;
    const CREDIT_MINIMUM_SESSION2 = 40;
    const SEUIL_VALIDATION_UE = 10.0;
    const NOTE_ELIMINATOIRE = 0;

    /**
     * LOGIQUE MÉDECINE EXACTE : Calcule les résultats académiques d'un étudiant
     * selon la logique précise de la faculté de médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @param bool $useResultatFinal
     * @return array
     */
    public function calculerResultatsComplets($etudiantId, $sessionId, $useResultatFinal = false)
    {
        try {
            // Validation des entrées
            $etudiant = Etudiant::findOrFail($etudiantId);
            $session = SessionExam::findOrFail($sessionId);

            // Sélection du modèle en fonction de la table à utiliser
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            // Récupération des résultats pour cette session
            $query = $modelClass::where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->with(['ec.ue', 'examen']);

            if ($useResultatFinal) {
                $query->where('statut', '!=', ResultatFinal::STATUT_ANNULE);
            } else {
                $query->where('statut', ResultatFusion::STATUT_VALIDE);
            }

            $resultats = $query->get();

            if ($resultats->isEmpty()) {
                return $this->resultatsVides($etudiant, $session);
            }

            // Grouper par UE pour calculer les moyennes selon logique médecine
            $resultatsParUE = $resultats->groupBy('ec.ue_id');

            $uesResultats = [];
            $creditsValides = 0;
            $totalCredits = 0;
            $moyennesUE = [];
            $aUneNoteEliminatoire = false;
            $uesEliminees = [];

            // LOGIQUE MÉDECINE : Traitement de chaque UE
            foreach ($resultatsParUE as $ueId => $notesUE) {
                $resultatUE = $this->calculerResultatUE_LogiqueMedecine($notesUE);

                $totalCredits += $resultatUE['credits'];

                // Si UE éliminée (note 0 dans un EC)
                if ($resultatUE['ue_eliminee']) {
                    $aUneNoteEliminatoire = true;
                    $uesEliminees[] = $resultatUE['ue_nom'];
                }

                // Crédits validés seulement si UE validée ET non éliminée
                if ($resultatUE['validee'] && !$resultatUE['ue_eliminee']) {
                    $creditsValides += $resultatUE['credits_obtenus'];
                }

                // Collecter les moyennes UE pour la moyenne générale (sauf UE éliminées)
                if (!$resultatUE['ue_eliminee']) {
                    $moyennesUE[] = $resultatUE['moyenne'];
                }

                $uesResultats[] = $resultatUE;
            }

            // LOGIQUE MÉDECINE : Moyenne générale = somme moyennes UE / nombre d'UE
            $moyenneGenerale = count($moyennesUE) > 0 ?
                round(array_sum($moyennesUE) / count($moyennesUE), 2) : 0;

            // Si note éliminatoire, moyenne générale = 0
            if ($aUneNoteEliminatoire) {
                $moyenneGenerale = 0;
            }

            // Déterminer la décision selon la logique médecine
            $decision = $this->determinerDecision_LogiqueMedecine(
                $session,
                $creditsValides,
                $totalCredits,
                $aUneNoteEliminatoire
            );

            $resultat = [
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
                    'annee_universitaire' => $session->anneeUniversitaire->libelle ?? null
                ],
                'resultats_ue' => $uesResultats,
                'synthese' => [
                    'moyenne_generale' => $moyenneGenerale,
                    'credits_valides' => $creditsValides,
                    'credits_total' => $totalCredits,
                    'credits_requis' => self::CREDIT_TOTAL_REQUIS,
                    'pourcentage_credits' => $totalCredits > 0 ?
                        round(($creditsValides / self::CREDIT_TOTAL_REQUIS) * 100, 2) : 0,
                    'a_note_eliminatoire' => $aUneNoteEliminatoire,
                    'ues_eliminees' => $uesEliminees,
                    'nb_ue_total' => count($uesResultats),
                    'nb_ue_validees' => count(array_filter($uesResultats, function($ue) {
                        return $ue['validee'] && !$ue['ue_eliminee'];
                    }))
                ],
                'decision' => [
                    'code' => $decision['code'],
                    'libelle' => $decision['libelle'],
                    'motif' => $decision['motif'],
                    'admis' => $decision['admis'],
                    'passe_rattrapage' => $decision['passe_rattrapage'],
                    'redouble' => $decision['redouble']
                ],
                'calcule_le' => now()->format('Y-m-d H:i:s'),
                'source_table' => $useResultatFinal ? 'resultats_finaux' : 'resultats_fusion'
            ];

            Log::info('Calcul académique médecine effectué', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'session_type' => $session->type,
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => $creditsValides,
                'decision' => $decision['code'],
                'has_eliminatoire' => $aUneNoteEliminatoire,
                'source' => $useResultatFinal ? 'resultats_finaux' : 'resultats_fusion'
            ]);

            return $resultat;

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul académique médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('Erreur lors du calcul des résultats : ' . $e->getMessage());
        }
    }

    /**
     * LOGIQUE MÉDECINE EXACTE : Calcule le résultat d'une UE
     *
     * Règles strictes faculté de médecine :
     * 1. Moyenne UE = somme des notes EC / nombre d'EC
     * 2. UE validée si moyenne >= 10 ET aucune note = 0
     * 3. UE éliminée si au moins une note = 0
     * 4. Si UE éliminée, crédits = 0 automatiquement
     *
     * @param Collection $resultatsECs Collection des résultats des EC de l'UE
     * @return array Résultat détaillé de l'UE
     */
    public function calculerResultatUE_LogiqueMedecine(Collection $resultatsECs)
    {
        $ue = $resultatsECs->first()->ec->ue;
        $notes = $resultatsECs->pluck('note')->toArray();
        $creditsUE = $ue->credits ?? 0;

        // Détails des EC pour traçabilité
        $detailsECs = [];
        $ecsEliminatoires = [];

        foreach ($resultatsECs as $resultat) {
            $ec = $resultat->ec;
            $note = $resultat->note;

            $detailsECs[] = [
                'ec_id' => $ec->id,
                'ec_nom' => $ec->nom,
                'ec_code' => $ec->abr ?? $ec->code ?? 'N/A',
                'note' => $note,
                'est_eliminatoire' => $note == self::NOTE_ELIMINATOIRE,
                'est_validee' => $note >= self::SEUIL_VALIDATION_UE
            ];

            // Collecter les EC éliminatoires
            if ($note == self::NOTE_ELIMINATOIRE) {
                $ecsEliminatoires[] = [
                    'ec_id' => $ec->id,
                    'code' => $ec->abr ?? $ec->code ?? $ec->nom,
                    'nom' => $ec->nom,
                    'note' => $note
                ];
            }
        }

        // RÈGLE MÉDECINE 1 : Vérifier les notes éliminatoires (0)
        $hasNoteZero = in_array(self::NOTE_ELIMINATOIRE, $notes);

        if ($hasNoteZero) {
            // UE éliminée à cause d'une note de 0
            return [
                'ue_id' => $ue->id,
                'ue_code' => $ue->abr ?? $ue->nom,
                'ue_nom' => $ue->nom,
                'credits' => $creditsUE,
                'moyenne' => 0, // Moyenne = 0 si UE éliminée
                'validee' => false,
                'ue_eliminee' => true,
                'credits_obtenus' => 0, // Aucun crédit si éliminée
                'ecs_eliminatoires' => $ecsEliminatoires,
                'details_ecs' => $detailsECs,
                'nombre_ecs' => count($notes),
                'nb_ecs_eliminees' => count($ecsEliminatoires),
                'motif_non_validation' => 'UE éliminée - Note 0 détectée dans un ou plusieurs EC'
            ];
        }

        // RÈGLE MÉDECINE 2 : Calcul moyenne UE = somme notes / nombre d'EC
        $moyenneUE = count($notes) > 0 ? array_sum($notes) / count($notes) : 0;
        $moyenneUE = round($moyenneUE, 2);

        // RÈGLE MÉDECINE 3 : UE validée si moyenne >= 10
        $ueValidee = $moyenneUE >= self::SEUIL_VALIDATION_UE;
        $creditsObtenus = $ueValidee ? $creditsUE : 0;

        return [
            'ue_id' => $ue->id,
            'ue_code' => $ue->abr ?? $ue->nom,
            'ue_nom' => $ue->nom,
            'credits' => $creditsUE,
            'moyenne' => $moyenneUE,
            'validee' => $ueValidee,
            'ue_eliminee' => false,
            'credits_obtenus' => $creditsObtenus,
            'ecs_eliminatoires' => [], // Pas d'EC éliminatoire dans ce cas
            'details_ecs' => $detailsECs,
            'nombre_ecs' => count($notes),
            'nb_ecs_eliminees' => 0,
            'motif_non_validation' => !$ueValidee ?
                sprintf("Moyenne insuffisante (%.2f/20 < %.2f/20)",
                    $moyenneUE,
                    self::SEUIL_VALIDATION_UE
                ) : null,
            'date_calcul' => now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * LOGIQUE MÉDECINE EXACTE : Détermine la décision finale
     *
     * Règles strictes faculté de médecine :
     *
     * SESSION 1 (Normale) :
     * - Si crédits validés = 60 → Admis
     * - Sinon → Rattrapage (même avec note éliminatoire)
     *
     * SESSION 2 (Rattrapage) :
     * - Si note éliminatoire → Exclu
     * - Si crédits validés >= 40 → Admis
     * - Sinon → Redoublant
     *
     * @param SessionExam $session
     * @param int $creditsValides
     * @param int $totalCredits
     * @param bool $aUneNoteEliminatoire
     * @return array
     */
    private function determinerDecision_LogiqueMedecine($session, $creditsValides, $totalCredits, $aUneNoteEliminatoire)
    {
        if ($session->type === 'Normale') {
            // SESSION 1 : Logique simple selon médecine
            if ($creditsValides >= self::CREDIT_TOTAL_REQUIS) {
                return [
                    'code' => 'admis',
                    'libelle' => 'Admis en 1ère session',
                    'motif' => "Tous les crédits validés ({$creditsValides}/" . self::CREDIT_TOTAL_REQUIS . ")",
                    'admis' => true,
                    'passe_rattrapage' => false,
                    'redouble' => false
                ];
            } else {
                return [
                    'code' => 'rattrapage',
                    'libelle' => 'Autorisé au rattrapage',
                    'motif' => "Crédits insuffisants ({$creditsValides}/" . self::CREDIT_TOTAL_REQUIS . ") - Passage en session de rattrapage",
                    'admis' => false,
                    'passe_rattrapage' => true,
                    'redouble' => false
                ];
            }
        } else {
            // SESSION 2 : Logique avec note éliminatoire selon médecine
            if ($aUneNoteEliminatoire) {
                return [
                    'code' => 'exclus',
                    'libelle' => 'Exclu définitivement',
                    'motif' => 'Note éliminatoire (0) en session de rattrapage',
                    'admis' => false,
                    'passe_rattrapage' => false,
                    'redouble' => false
                ];
            }

            if ($creditsValides >= self::CREDIT_MINIMUM_SESSION2) {
                return [
                    'code' => 'admis',
                    'libelle' => 'Admis en 2ème session',
                    'motif' => "Minimum de crédits atteint ({$creditsValides}/" . self::CREDIT_MINIMUM_SESSION2 . ") en rattrapage",
                    'admis' => true,
                    'passe_rattrapage' => false,
                    'redouble' => false
                ];
            } else {
                return [
                    'code' => 'redoublant',
                    'libelle' => 'Redoublant',
                    'motif' => "Crédits insuffisants après rattrapage ({$creditsValides}/" . self::CREDIT_MINIMUM_SESSION2 . ")",
                    'admis' => false,
                    'passe_rattrapage' => false,
                    'redouble' => true
                ];
            }
        }
    }

    /**
     * Structure vide pour les cas sans résultats
     */
    private function resultatsVides($etudiant, $session)
    {
        $isRattrapage = $session->type === 'Rattrapage';
        $decisionCode = $isRattrapage ? 'redoublant' : 'rattrapage';
        $libelle = $isRattrapage ? 'Redoublant' : 'Autorisé au rattrapage';

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
                'annee_universitaire' => $session->anneeUniversitaire->libelle ?? null
            ],
            'resultats_ue' => [],
            'synthese' => [
                'moyenne_generale' => 0,
                'credits_valides' => 0,
                'credits_total' => 0,
                'credits_requis' => self::CREDIT_TOTAL_REQUIS,
                'pourcentage_credits' => 0,
                'a_note_eliminatoire' => false,
                'ues_eliminees' => [],
                'nb_ue_total' => 0,
                'nb_ue_validees' => 0
            ],
            'decision' => [
                'code' => $decisionCode,
                'libelle' => $libelle,
                'motif' => 'Aucun résultat trouvé pour cet étudiant dans cette session',
                'admis' => false,
                'passe_rattrapage' => $decisionCode === 'rattrapage',
                'redouble' => $decisionCode === 'redoublant'
            ],
            'calcule_le' => now()->format('Y-m-d H:i:s'),
            'source_table' => 'aucune'
        ];
    }

    /**
     * MÉTHODE MISE À JOUR : Applique automatiquement les décisions selon logique médecine
     *
     * @param int $sessionId
     * @param bool $useResultatFinal
     * @return array
     */
    public function appliquerDecisionsSession($sessionId, $useResultatFinal = false)
    {
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

                        Log::info('Décision médecine appliquée', [
                            'etudiant_id' => $etudiantId,
                            'session_id' => $sessionId,
                            'decision' => $decision,
                            'credits_valides' => $resultat['synthese']['credits_valides'],
                            'moyenne_generale' => $resultat['synthese']['moyenne_generale'],
                            'has_eliminatoire' => $resultat['synthese']['a_note_eliminatoire']
                        ]);
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

            // Logger les résultats
            Log::info('Application des décisions médecine terminée', [
                'session_id' => $sessionId,
                'stats' => $stats
            ]);

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

    /**
     * Applique une décision à un étudiant
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @param string $decision
     * @param bool $useResultatFinal
     * @return bool
     */
    public function appliquerDecision($etudiantId, $sessionId, $decision, $useResultatFinal = false)
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
                $resultat->decision = $decision;

                // Si c'est un résultat final, mettre à jour l'historique
                if ($useResultatFinal) {
                    $historique = $resultat->status_history ?? [];
                    if (!is_array($historique)) {
                        $historique = json_decode($historique, true) ?? [];
                    }

                    // Ajouter l'entrée de décision dans l'historique
                    $historique[] = [
                        'type_action' => 'decision_appliquee',
                        'decision_precedente' => $resultat->getOriginal('decision'),
                        'decision_nouvelle' => $decision,
                        'user_id' => Auth::id() ?? 1,
                        'date_action' => now()->toDateTimeString(),
                        'methode' => 'logique_medecine_automatique'
                    ];

                    $resultat->status_history = $historique;
                }

                $resultat->save();
            }

            DB::commit();

            Log::info('Décision médecine appliquée avec succès', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'decision' => $decision,
                'resultats_maj' => $resultats->count(),
                'table' => $useResultatFinal ? 'resultats_finaux' : 'resultats_fusion'
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'application de la décision médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'decision' => $decision,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Calcule les statistiques selon logique médecine
     *
     * @param int $niveauId
     * @param int|null $parcoursId
     * @param int $sessionId
     * @param bool $useResultatFinal
     * @return array
     */
    public function calculerStatistiquesGlobales($niveauId, $parcoursId, $sessionId, $useResultatFinal = false)
    {
        try {
            $etudiantsQuery = Etudiant::where('niveau_id', $niveauId)
                ->where('is_active', true);

            if ($parcoursId) {
                $etudiantsQuery->where('parcours_id', $parcoursId);
            }

            $etudiants = $etudiantsQuery->get();
            $totalEtudiants = $etudiants->count();

            if ($totalEtudiants === 0) {
                return [
                    'total_etudiants' => 0,
                    'decisions' => [
                        'admis' => 0,
                        'rattrapage' => 0,
                        'redoublant' => 0,
                        'exclus' => 0
                    ],
                    'moyenne_generale_promotion' => 0,
                    'taux_reussite' => 0,
                    'credits_moyens' => 0,
                    'taux_elimination' => 0
                ];
            }

            $decisions = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0
            ];

            $sommeMoyennes = 0;
            $sommeCredits = 0;
            $etudiantsAvecResultats = 0;
            $etudiantsAvecNoteEliminatoire = 0;

            foreach ($etudiants as $etudiant) {
                try {
                    $resultat = $this->calculerResultatsComplets($etudiant->id, $sessionId, $useResultatFinal);

                    if (!empty($resultat['resultats_ue'])) {
                        $etudiantsAvecResultats++;
                        $sommeMoyennes += $resultat['synthese']['moyenne_generale'];
                        $sommeCredits += $resultat['synthese']['credits_valides'];

                        if ($resultat['synthese']['a_note_eliminatoire']) {
                            $etudiantsAvecNoteEliminatoire++;
                        }

                        $decisionCode = $resultat['decision']['code'];
                        if (isset($decisions[$decisionCode])) {
                            $decisions[$decisionCode]++;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Erreur lors du calcul pour un étudiant', [
                        'etudiant_id' => $etudiant->id,
                        'session_id' => $sessionId,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            $moyenneGeneralePromotion = $etudiantsAvecResultats > 0 ?
                round($sommeMoyennes / $etudiantsAvecResultats, 2) : 0;

            $creditsMoyens = $etudiantsAvecResultats > 0 ?
                round($sommeCredits / $etudiantsAvecResultats, 2) : 0;

            $totalAdmis = $decisions['admis'];

            $tauxReussite = $etudiantsAvecResultats > 0 ?
                round(($totalAdmis / $etudiantsAvecResultats) * 100, 2) : 0;

            return [
                'total_etudiants' => $totalEtudiants,
                'etudiants_avec_resultats' => $etudiantsAvecResultats,
                'etudiants_avec_note_eliminatoire' => $etudiantsAvecNoteEliminatoire,
                'decisions' => $decisions,
                'moyenne_generale_promotion' => $moyenneGeneralePromotion,
                'taux_reussite' => $tauxReussite,
                'taux_elimination' => $etudiantsAvecResultats > 0 ?
                    round(($etudiantsAvecNoteEliminatoire / $etudiantsAvecResultats) * 100, 2) : 0,
                'credits_moyens' => $creditsMoyens,
                'calcule_le' => now()->format('Y-m-d H:i:s'),
                'source' => $useResultatFinal ? 'resultats_finaux' : 'resultats_fusion'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques globales médecine', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors du calcul des statistiques : ' . $e->getMessage());
        }
    }

    /**
     * NOUVELLE MÉTHODE : Calcule la moyenne d'une UE pour un étudiant selon logique médecine
     *
     * @param int $etudiantId
     * @param int $ueId
     * @param int $sessionId
     * @param bool $useResultatFinal
     * @return float|null
     */
    public function calculerMoyenneUE($etudiantId, $ueId, $sessionId, $useResultatFinal = false)
    {
        try {
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            $resultats = $modelClass::where('session_exam_id', $sessionId)
                ->whereHas('ec', function($q) use ($ueId) {
                    $q->where('ue_id', $ueId);
                })
                ->where('etudiant_id', $etudiantId);

            if ($useResultatFinal) {
                $resultats->where('statut', '!=', ResultatFinal::STATUT_ANNULE);
            } else {
                $resultats->where('statut', ResultatFusion::STATUT_VALIDE);
            }

            $resultats = $resultats->get();

            if ($resultats->isEmpty()) {
                return null;
            }

            // LOGIQUE MÉDECINE : Vérifier s'il y a une note éliminatoire (0)
            $hasNoteZero = $resultats->contains('note', 0);

            if ($hasNoteZero) {
                return 0; // UE éliminée
            }

            // LOGIQUE MÉDECINE : Moyenne UE = somme notes / nombre EC
            return round($resultats->avg('note'), 2);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul de moyenne UE médecine', [
                'etudiant_id' => $etudiantId,
                'ue_id' => $ueId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Calcule la moyenne générale d'un étudiant selon logique médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @param bool $useResultatFinal
     * @return float
     */
    public function calculerMoyenneGenerale($etudiantId, $sessionId, $useResultatFinal = false)
    {
        try {
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            $query = $modelClass::where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->with('ec.ue');

            if ($useResultatFinal) {
                $query->where('statut', '!=', ResultatFinal::STATUT_ANNULE);
            } else {
                $query->where('statut', ResultatFusion::STATUT_VALIDE);
            }

            $resultats = $query->get();

            if ($resultats->isEmpty()) {
                return 0;
            }

            // LOGIQUE MÉDECINE : Grouper par UE
            $resultatsParUE = $resultats->groupBy('ec.ue_id');
            $moyennesUE = [];

            foreach ($resultatsParUE as $ueId => $notesUE) {
                // LOGIQUE MÉDECINE : Vérifier s'il y a une note éliminatoire (0)
                $hasNoteZeroInUE = $notesUE->contains('note', 0);

                if ($hasNoteZeroInUE) {
                    $moyennesUE[] = 0; // UE éliminée
                } else {
                    // LOGIQUE MÉDECINE : Moyenne UE = somme notes / nombre EC
                    $moyenneUE = $notesUE->avg('note');
                    $moyennesUE[] = $moyenneUE;
                }
            }

            // LOGIQUE MÉDECINE : Moyenne générale = somme moyennes UE / nombre UE
            $moyenneGenerale = count($moyennesUE) > 0 ?
                array_sum($moyennesUE) / count($moyennesUE) : 0;

            return round($moyenneGenerale, 2);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul de moyenne générale médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Vérifie si un étudiant valide une UE selon logique médecine
     *
     * @param int $etudiantId
     * @param int $ueId
     * @param int $sessionId
     * @param bool $useResultatFinal
     * @return bool
     */
    public function etudiantValideUE($etudiantId, $ueId, $sessionId, $useResultatFinal = false)
    {
        try {
            $moyenneUE = $this->calculerMoyenneUE($etudiantId, $ueId, $sessionId, $useResultatFinal);

            if ($moyenneUE === null) {
                return false;
            }

            // LOGIQUE MÉDECINE : UE validée si moyenne >= 10 ET pas de note 0
            return $moyenneUE >= self::SEUIL_VALIDATION_UE && $moyenneUE > 0;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de validation UE médecine', [
                'etudiant_id' => $etudiantId,
                'ue_id' => $ueId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Détermine automatiquement la décision pour première session selon logique médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @return string
     */
    public static function determinerDecisionPremiereSession($etudiantId, $sessionId)
    {
        try {
            $service = new self();
            $resultat = $service->calculerResultatsComplets($etudiantId, $sessionId, true);

            $creditsValides = $resultat['synthese']['credits_valides'];

            // LOGIQUE MÉDECINE SESSION 1 : Si 60 crédits → Admis, sinon → Rattrapage
            return $creditsValides >= self::CREDIT_TOTAL_REQUIS ?
                ResultatFinal::DECISION_ADMIS :
                ResultatFinal::DECISION_RATTRAPAGE;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la détermination de la décision première session médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return ResultatFinal::DECISION_RATTRAPAGE;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Détermine automatiquement la décision pour session rattrapage selon logique médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @return string
     */
    public static function determinerDecisionRattrapage($etudiantId, $sessionId)
    {
        try {
            $service = new self();
            $resultat = $service->calculerResultatsComplets($etudiantId, $sessionId, true);

            $creditsValides = $resultat['synthese']['credits_valides'];
            $hasNoteEliminatoire = $resultat['synthese']['a_note_eliminatoire'];

            // LOGIQUE MÉDECINE SESSION 2 :
            // 1. Si note éliminatoire → Exclu
            // 2. Si >= 40 crédits → Admis
            // 3. Sinon → Redoublant
            if ($hasNoteEliminatoire) {
                return ResultatFinal::DECISION_EXCLUS;
            }

            return $creditsValides >= self::CREDIT_MINIMUM_SESSION2 ?
                ResultatFinal::DECISION_ADMIS :
                ResultatFinal::DECISION_REDOUBLANT;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la détermination de la décision rattrapage médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return ResultatFinal::DECISION_REDOUBLANT;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Valide la cohérence des calculs selon logique médecine
     *
     * @param int $sessionId
     * @param int|null $etudiantId
     * @param bool $useResultatFinal
     * @return array
     */
    public function validerCoherenceCalculsMedecine($sessionId, $etudiantId = null, $useResultatFinal = false)
    {
        $erreurs = [];

        try {
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            $query = $modelClass::where('session_exam_id', $sessionId);
            if ($etudiantId) {
                $query->where('etudiant_id', $etudiantId);
            }

            $resultats = $query->with(['etudiant', 'ec.ue'])->get();
            $etudiantsGroupes = $resultats->groupBy('etudiant_id');

            foreach ($etudiantsGroupes as $etudiantId => $resultatsEtudiant) {
                try {
                    $calculResultat = $this->calculerResultatsComplets($etudiantId, $sessionId, $useResultatFinal);

                    // Vérifier la cohérence des décisions selon logique médecine
                    $decisionsDB = $resultatsEtudiant->pluck('decision')->unique();
                    $decisionCalculee = $calculResultat['decision']['code'];

                    if ($decisionsDB->count() > 1) {
                        $erreurs[] = "Étudiant {$etudiantId}: Décisions incohérentes en base " . $decisionsDB->implode(', ');
                    }

                    if ($decisionsDB->first() !== $decisionCalculee) {
                        $erreurs[] = "Étudiant {$etudiantId}: Décision DB ({$decisionsDB->first()}) ≠ Décision médecine calculée ({$decisionCalculee})";
                    }

                    // Vérifier la cohérence des moyennes UE selon logique médecine
                    foreach ($calculResultat['resultats_ue'] as $resultUE) {
                        $notesUE = $resultatsEtudiant->where('ec.ue_id', $resultUE['ue_id']);

                        // LOGIQUE MÉDECINE : Vérifier calcul moyenne
                        $hasNoteZero = $notesUE->contains('note', 0);
                        $moyenneAttendue = $hasNoteZero ? 0 : round($notesUE->avg('note'), 2);
                        $moyenneCalculee = $resultUE['moyenne'];

                        if (abs($moyenneAttendue - $moyenneCalculee) > 0.01) {
                            $erreurs[] = "Étudiant {$etudiantId}, UE {$resultUE['ue_nom']}: Moyenne attendue ({$moyenneAttendue}) ≠ Moyenne calculée ({$moyenneCalculee})";
                        }

                        // LOGIQUE MÉDECINE : Vérifier validation UE
                        $validationAttendue = !$hasNoteZero && $moyenneAttendue >= self::SEUIL_VALIDATION_UE;
                        $validationCalculee = $resultUE['validee'] && !$resultUE['ue_eliminee'];

                        if ($validationAttendue !== $validationCalculee) {
                            $erreurs[] = "Étudiant {$etudiantId}, UE {$resultUE['ue_nom']}: Validation attendue ({$validationAttendue}) ≠ Validation calculée ({$validationCalculee})";
                        }
                    }

                } catch (\Exception $e) {
                    $erreurs[] = "Erreur validation étudiant {$etudiantId}: " . $e->getMessage();
                }
            }

        } catch (\Exception $e) {
            $erreurs[] = "Erreur globale validation médecine: " . $e->getMessage();
        }

        return $erreurs;
    }

    /**
     * NOUVELLE MÉTHODE : Obtient les étudiants éligibles au rattrapage selon logique médecine
     *
     * @param int $niveauId
     * @param int $parcoursId
     * @param int $sessionNormaleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getEtudiantsEligiblesRattrapage($niveauId, $parcoursId, $sessionNormaleId)
    {
        try {
            $service = new self();

            // Récupérer tous les étudiants du niveau/parcours
            $etudiants = Etudiant::where('niveau_id', $niveauId)
                ->where('parcours_id', $parcoursId)
                ->where('is_active', true)
                ->get();

            $etudiantsEligibles = collect();

            foreach ($etudiants as $etudiant) {
                try {
                    // Calculer selon logique médecine
                    $resultat = $service->calculerResultatsComplets($etudiant->id, $sessionNormaleId, true);

                    // LOGIQUE MÉDECINE : Éligible si décision = rattrapage
                    if ($resultat['decision']['code'] === 'rattrapage') {
                        $etudiantsEligibles->push([
                            'etudiant' => $etudiant,
                            'credits_valides' => $resultat['synthese']['credits_valides'],
                            'moyenne_generale' => $resultat['synthese']['moyenne_generale'],
                            'has_note_eliminatoire' => $resultat['synthese']['a_note_eliminatoire'],
                            'ues_eliminees' => $resultat['synthese']['ues_eliminees']
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Erreur vérification éligibilité rattrapage médecine', [
                        'etudiant_id' => $etudiant->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Étudiants éligibles rattrapage selon médecine', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_normale_id' => $sessionNormaleId,
                'total_etudiants' => $etudiants->count(),
                'eligibles' => $etudiantsEligibles->count()
            ]);

            return $etudiantsEligibles;

        } catch (\Exception $e) {
            Log::error('Erreur récupération étudiants éligibles médecine', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_normale_id' => $sessionNormaleId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * CONSERVÉES : Toutes les autres méthodes existantes restent inchangées
     * pour maintenir la compatibilité avec le système existant
     */

    // ... (Conserver toutes les autres méthodes existantes du fichier original)

    /**
     * Obtient les résultats d'un étudiant pour une session
     */
    public function getResultatsEtudiant($etudiantId, $sessionId, $useResultatFinal = false, $statuts = null)
    {
        try {
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            $query = $modelClass::where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->with(['ec', 'ec.ue', 'examen']);

            if ($statuts) {
                $query->whereIn('statut', $statuts);
            } else {
                if ($useResultatFinal) {
                    $query->where('statut', '!=', ResultatFinal::STATUT_ANNULE);
                } else {
                    $query->where('statut', ResultatFusion::STATUT_VALIDE);
                }
            }

            return $query->get();

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des résultats étudiant', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Calcule les statistiques d'une session
     */
    public function calculerStatistiquesSession($sessionId, $useResultatFinal = false)
    {
        try {
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            $query = $modelClass::where('session_exam_id', $sessionId);

            if ($useResultatFinal) {
                $query->where('statut', '!=', ResultatFinal::STATUT_ANNULE);
            } else {
                $query->where('statut', ResultatFusion::STATUT_VALIDE);
            }

            $resultats = $query->get();

            $stats = [
                'total_resultats' => $resultats->count(),
                'moyenne_session' => round($resultats->avg('note'), 2),
                'notes_eliminatoires' => $resultats->where('note', 0)->count(),
                'decisions' => [
                    'admis' => $resultats->where('decision', 'admis')->count(),
                    'rattrapage' => $resultats->where('decision', 'rattrapage')->count(),
                    'redoublant' => $resultats->where('decision', 'redoublant')->count(),
                    'exclus' => $resultats->where('decision', 'exclus')->count(),
                ]
            ];

            return $stats;

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques de session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtient tous les résultats d'une session
     */
    public function getResultatsSession($sessionId, $useResultatFinal = false, $statuts = null)
    {
        try {
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            $query = $modelClass::where('session_exam_id', $sessionId)
                ->with(['etudiant', 'ec', 'ec.ue', 'examen']);

            if ($statuts) {
                $query->whereIn('statut', $statuts);
            } else {
                if ($useResultatFinal) {
                    $query->where('statut', '!=', ResultatFinal::STATUT_ANNULE);
                } else {
                    $query->where('statut', ResultatFusion::STATUT_VALIDE);
                }
            }

            return $query->get();

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des résultats de session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Calcule les résultats par niveau/parcours dans une session
     */
    public function calculerResultatsNiveauParcours($sessionId, $niveauId = null, $parcoursId = null, $useResultatFinal = false)
    {
        try {
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            $query = $modelClass::where('session_exam_id', $sessionId)
                ->with(['etudiant', 'ec', 'ec.ue', 'examen']);

            if ($useResultatFinal) {
                $query->where('statut', '!=', ResultatFinal::STATUT_ANNULE);
            } else {
                $query->where('statut', ResultatFusion::STATUT_VALIDE);
            }

            if ($niveauId) {
                $query->whereHas('examen', function($q) use ($niveauId) {
                    $q->where('niveau_id', $niveauId);
                });
            }

            if ($parcoursId) {
                $query->whereHas('examen', function($q) use ($parcoursId) {
                    $q->where('parcours_id', $parcoursId);
                });
            }

            return $query->get();

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des résultats niveau/parcours', [
                'session_id' => $sessionId,
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Transfère les décisions de ResultatFusion vers ResultatFinal
     */
    public function transfererDecisions($sessionId)
    {
        try {
            DB::beginTransaction();

            // Récupérer tous les résultats de fusion avec décisions
            $resultats = ResultatFusion::where('session_exam_id', $sessionId)
                ->where('statut', ResultatFusion::STATUT_VALIDE)
                ->whereNotNull('decision')
                ->get();

            $stats = [
                'total_traites' => 0,
                'transferes' => 0,
                'erreurs' => 0,
                'decisions' => [
                    'admis' => 0,
                    'rattrapage' => 0,
                    'redoublant' => 0,
                    'exclus' => 0
                ]
            ];

            foreach ($resultats as $resultatFusion) {
                $stats['total_traites']++;

                try {
                    // Chercher le résultat final correspondant
                    $resultatFinal = ResultatFinal::where('session_exam_id', $sessionId)
                        ->where('etudiant_id', $resultatFusion->etudiant_id)
                        ->where('ec_id', $resultatFusion->ec_id)
                        ->first();

                    if ($resultatFinal) {
                        $resultatFinal->decision = $resultatFusion->decision;
                        $resultatFinal->save();

                        $stats['transferes']++;
                        if (isset($stats['decisions'][$resultatFusion->decision])) {
                            $stats['decisions'][$resultatFusion->decision]++;
                        }
                    }
                } catch (\Exception $e) {
                    $stats['erreurs']++;
                    Log::error('Erreur lors du transfert d\'une décision', [
                        'resultat_fusion_id' => $resultatFusion->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            Log::info('Transfert des décisions terminé', [
                'session_id' => $sessionId,
                'stats' => $stats
            ]);

            return [
                'success' => true,
                'message' => "Transfert terminé : {$stats['transferes']} décisions transférées sur {$stats['total_traites']} traitées",
                'statistiques' => $stats
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du transfert des décisions', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du transfert : ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }
}