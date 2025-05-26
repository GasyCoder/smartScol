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
    // Constantes pour les seuils et règles métier
    const CREDIT_TOTAL_ANNUEL = 60;
    const NOTE_VALIDATION = 10;
    const NOTE_ELIMINATOIRE = 0;

    /**
     * Calcule toutes les moyennes et crédits pour un étudiant dans une session
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

            // Récupérer les résultats avec relations
            $query = $modelClass::whereHas('examen', function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
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

            // Grouper par UE pour calculer les moyennes
            $resultatsParUE = $resultats->groupBy('ec.ue_id');

            $uesResultats = [];
            $totalCreditsValides = 0;
            $totalCreditsUE = 0;
            $sommeMoyennesUE = 0;
            $nombreUE = 0;
            $aUneNoteEliminatoire = false;
            $notesEliminatoires = [];

            // Récupérer toutes les UEs du niveau pour vérifier si des UEs manquent
            $ues = UE::whereHas('ecs.examens', function ($query) use ($sessionId, $etudiant) {
                $query->where('session_id', $sessionId)
                      ->where('niveau_id', $etudiant->niveau_id);
            })->get();

            foreach ($ues as $ue) {
                $resultatsUE = $resultatsParUE->get($ue->id, collect());
                $resultatUE = $this->calculerResultatUE($ue, $resultatsUE);

                if ($resultatUE['a_note_eliminatoire']) {
                    $aUneNoteEliminatoire = true;
                    $notesEliminatoires[] = [
                        'ue' => $ue->nom,
                        'ue_code' => $ue->abr ?? $ue->nom,
                        'ecs' => $resultatUE['ecs_eliminatoires']
                    ];
                }

                $totalCreditsUE += $ue->credits;
                if ($resultatUE['validee']) {
                    $totalCreditsValides += $resultatUE['credits_obtenus'];
                }

                if ($resultatUE['moyenne'] !== null && !$resultatUE['a_note_eliminatoire']) {
                    $sommeMoyennesUE += $resultatUE['moyenne'];
                    $nombreUE++;
                }

                $uesResultats[] = $resultatUE;
            }

            // Calcul de la moyenne générale
            $moyenneGenerale = $nombreUE > 0 ? round($sommeMoyennesUE / $nombreUE, 2) : 0;

            // Si note éliminatoire, moyenne générale = 0
            if ($aUneNoteEliminatoire) {
                $moyenneGenerale = 0;
            }

            // Déterminer la décision
            $decision = $this->determinerDecision(
                $session,
                $totalCreditsValides,
                $moyenneGenerale,
                $aUneNoteEliminatoire,
                $etudiant->niveau
            );

            $resultat = [
                'etudiant' => [
                    'id' => $etudiant->id,
                    'matricule' => $etudiant->matricule,
                    'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'niveau' => $etudiant->niveau->nom,
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
                    'credits_valides' => $totalCreditsValides,
                    'credits_total' => $totalCreditsUE,
                    'credits_requis' => self::CREDIT_TOTAL_ANNUEL,
                    'pourcentage_credits' => $totalCreditsUE > 0 ?
                        round(($totalCreditsValides / self::CREDIT_TOTAL_ANNUEL) * 100, 2) : 0,
                    'a_note_eliminatoire' => $aUneNoteEliminatoire,
                    'notes_eliminatoires' => $notesEliminatoires
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

            Log::info('Calcul académique effectué', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => $totalCreditsValides,
                'decision' => $decision['code'],
                'source' => $useResultatFinal ? 'resultats_finaux' : 'resultats_fusion'
            ]);

            return $resultat;

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul académique', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('Erreur lors du calcul des résultats : ' . $e->getMessage());
        }
    }

    /**
     * Calcule le résultat d'une UE spécifique
     *
     * @param UE $ue L'unité d'enseignement à évaluer
     * @param Collection $resultatsECs Collection des résultats des éléments constitutifs
     * @return array Tableau détaillé des résultats de l'UE
     */
    public function calculerResultatUE(UE $ue, Collection $resultatsECs)
    {
        // Initialisation des variables
        $notes = [];
        $coefficients = [];
        $aUneNoteEliminatoire = false;
        $ecsEliminatoires = [];
        $detailsECs = [];
        $totalECs = 0;
        $ecsAvecNotes = 0;

        // Récupérer tous les ECs attendus pour cette UE
        $ecsAttendus = $ue->ecs()->pluck('id')->toArray();
        $ecsPresents = $resultatsECs->pluck('ec_id')->toArray();
        $ecsManquants = array_diff($ecsAttendus, $ecsPresents);

        // Vérifier si des ECs sont manquants
        if (!empty($ecsManquants)) {
            Log::warning("ECs manquants pour l'UE", [
                'ue_id' => $ue->id,
                'ue_nom' => $ue->nom,
                'ecs_manquants' => $ecsManquants,
                'date' => now()->format('Y-m-d H:i:s'),
                'user' => Auth::user() ? Auth::user()->name : 'System'
            ]);

            return [
                'ue_id' => $ue->id,
                'ue_code' => $ue->abr ?? $ue->nom,
                'ue_nom' => $ue->nom,
                'credits' => $ue->credits,
                'moyenne' => 0,
                'validee' => false,
                'credits_obtenus' => 0,
                'a_note_eliminatoire' => false,
                'ecs_eliminatoires' => [],
                'details_ecs' => [],
                'ecs_manquants' => $ecsManquants,
                'motif_non_validation' => 'Notes incomplètes : ECs manquants'
            ];
        }

        // Traitement de chaque résultat d'EC
        foreach ($resultatsECs as $resultat) {
            $ec = $resultat->ec;
            $totalECs++;

            // Vérification de la note
            if ($resultat->note === null) {
                continue; // Passer au suivant si pas de note
            }
            $ecsAvecNotes++;

            $note = $resultat->note;
            // Récupération du coefficient (par défaut 1 si non défini)
            $coefficient = $ec->coefficient ?? 1;

            // Vérification note éliminatoire
            if ($note == ReglesDeliberation::NOTE_ELIMINATOIRE) {
                $aUneNoteEliminatoire = true;
                $ecsEliminatoires[] = [
                    'ec_id' => $ec->id,
                    'code' => $ec->abr ?? $ec->code ?? $ec->nom,
                    'nom' => $ec->nom,
                    'note' => $note
                ];
            }

            // Stockage des notes et coefficients pour le calcul de la moyenne
            $notes[] = $note;
            $coefficients[] = $coefficient;

            // Construction des détails pour chaque EC
            $detailsECs[] = [
                'ec_id' => $ec->id,
                'code' => $ec->abr ?? $ec->code ?? $ec->nom,
                'nom' => $ec->nom,
                'coefficient' => $coefficient,
                'note' => $note,
                'credits' => $ec->credits,
                'validee' => $note >= ReglesDeliberation::NOTE_VALIDATION_UE,
                'eliminatoire' => $note == ReglesDeliberation::NOTE_ELIMINATOIRE
            ];
        }

        // Vérification si tous les ECs ont des notes
        if ($ecsAvecNotes < $totalECs) {
            Log::warning("Notes manquantes dans l'UE", [
                'ue_id' => $ue->id,
                'total_ecs' => $totalECs,
                'ecs_avec_notes' => $ecsAvecNotes
            ]);

            return [
                'ue_id' => $ue->id,
                'ue_code' => $ue->abr ?? $ue->nom,
                'ue_nom' => $ue->nom,
                'credits' => $ue->credits,
                'moyenne' => 0,
                'validee' => false,
                'credits_obtenus' => 0,
                'a_note_eliminatoire' => false,
                'ecs_eliminatoires' => [],
                'details_ecs' => $detailsECs,
                'motif_non_validation' => 'Notes incomplètes : certains ECs sans note'
            ];
        }

        // Si note éliminatoire, pas besoin de calculer la moyenne
        if ($aUneNoteEliminatoire) {
            return [
                'ue_id' => $ue->id,
                'ue_code' => $ue->abr ?? $ue->nom,
                'ue_nom' => $ue->nom,
                'credits' => $ue->credits,
                'moyenne' => 0,
                'validee' => false,
                'credits_obtenus' => 0,
                'a_note_eliminatoire' => true,
                'ecs_eliminatoires' => $ecsEliminatoires,
                'details_ecs' => $detailsECs,
                'motif_non_validation' => 'UE éliminée (note 0 dans un ou plusieurs ECs)'
            ];
        }

        // Calcul de la moyenne pondérée
        $totalPoints = 0;
        $totalCoefficients = 0;

        foreach ($notes as $index => $note) {
            $totalPoints += $note * $coefficients[$index];
            $totalCoefficients += $coefficients[$index];
        }

        // Calcul de la moyenne finale
        $moyenneUE = $totalCoefficients > 0 ?
            round($totalPoints / $totalCoefficients, 2) : 0;

        // Détermination de la validation de l'UE
        $ueValidee = $moyenneUE >= ReglesDeliberation::NOTE_VALIDATION_UE;
        $creditsObtenus = $ueValidee ? $ue->credits : 0;

        // Log du résultat pour debugging
        Log::info('Calcul résultat UE terminé', [
            'ue_id' => $ue->id,
            'moyenne' => $moyenneUE,
            'validee' => $ueValidee,
            'credits_obtenus' => $creditsObtenus,
            'date_calcul' => now()->format('Y-m-d H:i:s')
        ]);

        // Retour du résultat complet
        return [
            'ue_id' => $ue->id,
            'ue_code' => $ue->abr ?? $ue->nom,
            'ue_nom' => $ue->nom,
            'credits' => $ue->credits,
            'moyenne' => $moyenneUE,
            'validee' => $ueValidee,
            'credits_obtenus' => $creditsObtenus,
            'a_note_eliminatoire' => false,
            'ecs_eliminatoires' => [],
            'details_ecs' => $detailsECs,
            'nombre_ecs' => $totalECs,
            'coefficients_total' => $totalCoefficients,
            'motif_non_validation' => !$ueValidee ?
                sprintf("Moyenne insuffisante (%.2f/20 < %.2f/20)",
                    $moyenneUE,
                    ReglesDeliberation::NOTE_VALIDATION_UE
                ) : null,
            'date_calcul' => now()->format('Y-m-d H:i:s')
        ];
    }
    /**
     * Détermine la décision finale pour un étudiant
     *
     * @param SessionExam $session
     * @param int $creditsValides
     * @param float $moyenneGenerale
     * @param bool $aUneNoteEliminatoire
     * @param Niveau $niveau
     * @return array
     */
    private function determinerDecision($session, $creditsValides, $moyenneGenerale, $aUneNoteEliminatoire, $niveau)
    {
        if ($niveau->is_concours) {
            return $this->determinerDecisionConcours($moyenneGenerale, $aUneNoteEliminatoire);
        }

        $isRattrapage = $session->type === 'Rattrapage';
        $decisionCode = ReglesDeliberation::determinerDecision($creditsValides, $aUneNoteEliminatoire, $isRattrapage);
        $libelle = ReglesDeliberation::getLibelleDecision($decisionCode, $creditsValides);

        return [
            'code' => $decisionCode,
            'libelle' => $libelle,
            'motif' => $this->getMotifDecision($decisionCode, $creditsValides, $aUneNoteEliminatoire, $isRattrapage),
            'admis' => $decisionCode === 'admis',
            'passe_rattrapage' => $decisionCode === 'rattrapage',
            'redouble' => $decisionCode === 'redoublant'
        ];
    }

    /**
     * Détermine la décision pour un niveau concours
     */
    private function determinerDecisionConcours($moyenneGenerale, $aUneNoteEliminatoire)
    {
        if ($aUneNoteEliminatoire) {
            return [
                'code' => 'exclus',
                'libelle' => 'Exclu du concours',
                'motif' => 'Note éliminatoire au concours',
                'admis' => false,
                'passe_rattrapage' => false,
                'redouble' => false
            ];
        }

        if ($moyenneGenerale >= self::NOTE_VALIDATION) {
            return [
                'code' => 'admis',
                'libelle' => 'Admis au concours',
                'motif' => "Moyenne suffisante au concours ({$moyenneGenerale}/20)",
                'admis' => true,
                'passe_rattrapage' => false,
                'redouble' => false
            ];
        }

        return [
            'code' => 'exclus',
            'libelle' => 'Non admis au concours',
            'motif' => "Moyenne insuffisante au concours ({$moyenneGenerale}/20)",
            'admis' => false,
            'passe_rattrapage' => false,
            'redouble' => false
        ];
    }

    /**
     * Génère le motif pour une décision
     */
    private function getMotifDecision($decisionCode, $creditsValides, $aUneNoteEliminatoire, $isRattrapage)
    {
        if ($aUneNoteEliminatoire) {
            return 'Note éliminatoire (0) détectée';
        }

        if ($decisionCode === 'admis') {
            return "Tous les crédits validés ({$creditsValides}/" . self::CREDIT_TOTAL_ANNUEL . ")";
        }

        if ($decisionCode === 'rattrapage') {
            return "Crédits insuffisants ({$creditsValides}/" . self::CREDIT_TOTAL_ANNUEL . ") - Passage en session de rattrapage";
        }

        if ($decisionCode === 'redoublant') {
            return "Crédits insuffisants après rattrapage ({$creditsValides}/" . self::CREDIT_TOTAL_ANNUEL . ")";
        }

        return 'Motif non spécifié';
    }

    /**
     * Structure vide pour les cas sans résultats
     */
    private function resultatsVides($etudiant, $session)
    {
        $isRattrapage = $session->type === 'Rattrapage';
        $decisionCode = $isRattrapage ? 'redoublant' : 'rattrapage';
        $libelle = ReglesDeliberation::getLibelleDecision($decisionCode, 0);

        return [
            'etudiant' => [
                'id' => $etudiant->id,
                'matricule' => $etudiant->matricule,
                'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                'niveau' => $etudiant->niveau->nom,
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
                'credits_requis' => self::CREDIT_TOTAL_ANNUEL,
                'pourcentage_credits' => 0,
                'a_note_eliminatoire' => false,
                'notes_eliminatoires' => []
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
     * Applique automatiquement les décisions à tous les étudiants d'une session
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

            // Récupérer les étudiants distincts
            $etudiantsIds = $modelClass::whereHas('examen', function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
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

            // Traiter chaque étudiant
            foreach ($etudiantsIds as $etudiantId) {
                // Calculer les résultats complets pour l'étudiant
                $resultat = $this->calculerResultatsComplets($etudiantId, $sessionId, $useResultatFinal);
                $decision = $resultat['decision']['code'];

                // Appliquer la décision
                $success = $this->appliquerDecision($etudiantId, $sessionId, $decision, $useResultatFinal);

                if ($success) {
                    $stats['decisions'][$decision]++;
                } else {
                    $stats['erreurs'][] = "Échec application décision pour étudiant $etudiantId";
                }
            }

            DB::commit();

            // Logger les résultats
            Log::info('Application des décisions terminée', [
                'session_id' => $sessionId,
                'stats' => $stats
            ]);

            return [
                'success' => empty($stats['erreurs']),
                'message' => empty($stats['erreurs'])
                    ? "Décisions appliquées avec succès pour {$stats['total_etudiants']} étudiants."
                    : "Décisions appliquées avec " . count($stats['erreurs']) . " erreurs.",
                'statistiques' => $stats
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'application des décisions de session', [
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

            // Récupérer les résultats de l'étudiant pour cette session
            $resultats = $modelClass::whereHas('examen', function ($query) use ($sessionId) {
                    $query->where('session_id', $sessionId);
                })
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
                        'de' => $resultat->decision,
                        'vers' => $decision,
                        'user_id' => Auth::id() ?? 1,
                        'date' => now()->toDateTimeString(),
                        'avec_deliberation' => false,
                        'decision' => $decision
                    ];

                    $resultat->status_history = $historique;
                }

                $resultat->save();
            }

            DB::commit();

            Log::info('Décision appliquée avec succès', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'decision' => $decision,
                'resultats_maj' => $resultats->count(),
                'table' => $useResultatFinal ? 'resultats_finaux' : 'resultats_fusion'
            ]);

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

    /**
     * Calcule les statistiques globales pour un niveau/parcours
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
                    'credits_moyens' => 0
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
            Log::error('Erreur lors du calcul des statistiques globales', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors du calcul des statistiques : ' . $e->getMessage());
        }
    }
}