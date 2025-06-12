<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CalculAcademiqueService;

class Deliberation extends Model
{
    use HasFactory;

    protected $fillable = [
        'niveau_id',
        'session_id',
        'examen_id',
        'annee_universitaire_id',
        'date_deliberation',
        'statut',
        'seuil_admission',
        'seuil_rachat',
        'pourcentage_ue_requises',
        'appliquer_regles_auto',
        'observations',
        'decisions_speciales',
        'nombre_admis',
        'nombre_ajournes',
        'nombre_exclus',
        'nombre_rachats',
        'date_finalisation',
        'date_publication',
        'finalise_par'
    ];

    protected $casts = [
        'date_deliberation' => 'datetime',
        'date_finalisation' => 'datetime',
        'date_publication' => 'datetime',
        'seuil_admission' => 'decimal:2',
        'seuil_rachat' => 'decimal:2',
        'pourcentage_ue_requises' => 'integer',
        'appliquer_regles_auto' => 'boolean',
        'decisions_speciales' => 'json',
        'nombre_admis' => 'integer',
        'nombre_ajournes' => 'integer',
        'nombre_exclus' => 'integer',
        'nombre_rachats' => 'integer'
    ];

    // Constantes de statut
    const STATUT_PROGRAMMEE = 'programmee';
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_TERMINEE = 'terminee';
    const STATUT_VALIDEE = 'validee';
    const STATUT_ANNULEE = 'annulee';

    // Constantes des règles métier
    const CREDITS_TOTAL_ANNEE = 60;
    const CREDITS_PAR_UE = 5;
    const CREDITS_ADMISSION_SESSION_1 = 60;
    const CREDITS_ADMISSION_CONDITIONNELLE_SESSION_2 = 40;
    const NOTE_VALIDATION_UE = 10.00;
    const NOTE_ELIMINATOIRE = 0;

    // Relations
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function session()
    {
        return $this->belongsTo(SessionExam::class, 'session_id');
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function anneeUniversitaire()
    {
        return $this->belongsTo(AnneeUniversitaire::class);
    }

    public function presidentJury()
    {
        return $this->belongsTo(User::class, 'president_jury');
    }

    public function finalisePar()
    {
        return $this->belongsTo(User::class, 'finalise_par');
    }

    public function resultatsFinaux()
    {
        return $this->hasMany(ResultatFinal::class);
    }

    // Accesseurs et méthodes statiques
    public static function getLibellesStatuts()
    {
        return [
            self::STATUT_PROGRAMMEE => 'Programmée',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_TERMINEE => 'Terminée',
            self::STATUT_VALIDEE => 'Validée',
            self::STATUT_ANNULEE => 'Annulée'
        ];
    }

    public static function getLibellesDecisions()
    {
        return ResultatFinal::getLibellesDecisions();
    }

    public function getLibelleStatutAttribute()
    {
        $libelles = self::getLibellesStatuts();
        return $libelles[$this->statut] ?? 'Statut inconnu';
    }

    // Vérifications et prérequis
    public function verifierPrerequisDeliberation()
    {
        $errors = [];

        if (!$this->session || !$this->session->isRattrapage()) {
            $errors[] = "La délibération ne peut avoir lieu que pour une session de rattrapage";
        }

        if ($this->niveau && $this->niveau->is_concours) {
            $errors[] = "Aucune délibération n'est prévue pour les niveaux de concours";
        }

        if (!$this->examen_id) {
            $errors[] = "Aucun examen spécifié pour cette délibération";
        }

        if (!$this->tousResultatsFinauxSaisis()) {
            $errors[] = "Tous les résultats finaux doivent être saisis (statut 'en_attente') avant la délibération";
        }

        return [
            'valide' => empty($errors),
            'erreurs' => $errors
        ];
    }

    public function tousResultatsFinauxSaisis()
    {
        if (!$this->examen_id) {
            return false;
        }

        $etudiants = $this->getEtudiantsConcernes();

        foreach ($etudiants as $etudiantId) {
            $resultatCount = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->count();

            if ($resultatCount === 0) {
                return false;
            }
        }

        return true;
    }

    public function getEtudiantsConcernes()
    {
        $etudiantsQuery = Etudiant::where('niveau_id', $this->niveau_id)
            ->where('is_active', true);

        if ($this->niveau->has_parcours && isset($this->parcours_id)) {
            $etudiantsQuery->where('parcours_id', $this->parcours_id);
        }

        if ($this->session && $this->session->isRattrapage()) {
            $etudiantsEnRattrapage = $this->getEtudiantsEnRattrapage();
            $etudiantsQuery->whereIn('id', $etudiantsEnRattrapage);
        }

        return $etudiantsQuery->pluck('id')->toArray();
    }

    private function getEtudiantsEnRattrapage()
    {
        $sessionNormale = SessionExam::where('annee_universitaire_id', $this->annee_universitaire_id)
            ->where('type', 'Normale')
            ->first();

        if (!$sessionNormale) {
            return [];
        }

        $deliberationsNormale = Deliberation::where('session_id', $sessionNormale->id)
            ->where('niveau_id', $this->niveau_id)
            ->pluck('id');

        $etudiantsAjournés = [];
        foreach ($deliberationsNormale as $delibId) {
            $decisions = Deliberation::find($delibId)->decisions_speciales ?? [];
            foreach ($decisions as $etudiantId => $decisionData) {
                if ($decisionData['decision'] === ResultatFinal::DECISION_ADMIS ||
                    $decisionData['decision'] === ResultatFinal::DECISION_RATTRAPAGE) {
                    $etudiantsAjournés[] = $etudiantId;
                }
            }
        }

        return array_unique($etudiantsAjournés);
    }

    // Processus de délibération
    public function demarrer($userId)
    {
        $verification = $this->verifierPrerequisDeliberation();
        if (!$verification['valide']) {
            throw new \Exception('Impossible de démarrer la délibération : ' . implode(', ', $verification['erreurs']));
        }

        DB::beginTransaction();
        try {
            $this->statut = self::STATUT_EN_COURS;
            $this->save();

            if ($this->appliquer_regles_auto) {
                $this->appliquerReglesAutomatiques();
            }

            DB::commit();
            Log::info('Délibération démarrée', [
                'deliberation_id' => $this->id,
                'niveau' => $this->niveau->nom,
                'examen' => $this->examen->nom,
                'session' => $this->session->type,
                'demarre_par' => $userId
            ]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du démarrage de la délibération', [
                'deliberation_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function appliquerReglesAutomatiques()
    {
        if ($this->statut !== self::STATUT_EN_COURS) {
            throw new \Exception('La délibération doit être en cours pour appliquer les règles automatiques');
        }

        $etudiants = $this->getEtudiantsConcernes();
        $decisions = [];
        $statistiques = [
            'admis' => 0,
            'admis_conditionnellement' => 0,
            'ajournes' => 0,
            'redoublants' => 0,
            'elimines' => 0
        ];

        $isSessionRattrapage = $this->session->isRattrapage();

        foreach ($etudiants as $etudiantId) {
            // Calculer les crédits validés et vérifier les notes éliminatoires
            $resultatsCredits = $this->calculerCreditsValidesEtudiant($etudiantId);
            $creditsValides = $resultatsCredits['total_credits_valides'];

            // Vérifier s'il y a des notes éliminatoires
            $hasNoteEliminatoire = $this->verifierNoteEliminatoire($etudiantId);

            // Calculer la moyenne générale
            $moyenneGenerale = $this->calculerMoyenneGeneraleEtudiant($etudiantId);

            // Déterminer la décision selon les règles métier
            $decision = $this->determinerDecisionAutomatique($creditsValides, $hasNoteEliminatoire, $isSessionRattrapage);

            // Convertir la décision en format attendu par le système
            $decisionFinale = $this->convertirDecision($decision, $isSessionRattrapage);

            // Enregistrer la décision avec tous les détails
            $decisions[$etudiantId] = [
                'decision' => $decisionFinale,
                'decision_brute' => $decision,
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => $creditsValides,
                'credits_requis' => self::CREDITS_TOTAL_ANNEE,
                'pourcentage_credits' => $resultatsCredits['pourcentage_credits'],
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'details_ue' => $resultatsCredits['details_ue'],
                'observations' => $this->genererObservationsEtudiant($decision, $creditsValides, $hasNoteEliminatoire),
                'points_jury' => 0,
                'date_deliberation' => now()
            ];

            // Mettre à jour le résultat final
            $this->mettreAJourResultatFinal($etudiantId, $decisionFinale, $decisions[$etudiantId]);

            // Mettre à jour les statistiques
            $this->mettreAJourStatistiquesDecision($statistiques, $decision);
        }

        // Sauvegarder toutes les décisions et statistiques
        $this->decisions_speciales = $decisions;
        $this->nombre_admis = $statistiques['admis'];
        $this->nombre_ajournes = $statistiques['ajournes'] + $statistiques['redoublants'];
        $this->nombre_rachats = $statistiques['admis_conditionnellement'];
        $this->nombre_exclus = $statistiques['elimines'];
        $this->save();

        Log::info('Délibération automatique appliquée', [
            'deliberation_id' => $this->id,
            'session_type' => $isSessionRattrapage ? 'Rattrapage' : 'Normale',
            'nombre_etudiants' => count($etudiants),
            'statistiques' => $statistiques
        ]);

        return true;
    }

    // Calculs académiques
    public function calculerCreditsValidesEtudiant($etudiantId)
    {
        $ues = UE::where('niveau_id', $this->niveau_id)
            ->with(['ecs' => function($query) use ($etudiantId) {
                $query->with(['resultatsFinaux' => function($q) use ($etudiantId) {
                    $q->where('etudiant_id', $etudiantId)
                      ->where('examen_id', $this->examen_id);
                }]);
            }])
            ->get();

        $totalCreditsValides = 0;
        $detailsUE = [];

        foreach ($ues as $ue) {
            $notesEC = [];
            $hasNoteZero = false;

            foreach ($ue->ecs as $ec) {
                $resultat = $ec->resultatsFinaux->first();
                if ($resultat) {
                    $note = $resultat->note;
                    $notesEC[] = $note;

                    if ($note == self::NOTE_ELIMINATOIRE) {
                        $hasNoteZero = true;
                    }
                }
            }

            $moyenneUE = 0;
            $creditsUE = 0;

            if ($hasNoteZero) {
                $moyenneUE = 0;
                $creditsUE = 0;
            } elseif (count($notesEC) > 0) {
                $moyenneUE = array_sum($notesEC) / count($notesEC);

                if ($moyenneUE >= self::NOTE_VALIDATION_UE) {
                    $creditsUE = $ue->credits;
                    $totalCreditsValides += $creditsUE;
                }
            }

            $detailsUE[] = [
                'ue_id' => $ue->id,
                'ue_nom' => $ue->nom,
                'moyenne' => round($moyenneUE, 2),
                'credits_obtenus' => $creditsUE,
                'credits_ue' => $ue->credits,
                'validee' => $creditsUE > 0,
                'eliminee' => $hasNoteZero
            ];
        }

        return [
            'total_credits_valides' => $totalCreditsValides,
            'total_credits_requis' => self::CREDITS_TOTAL_ANNEE,
            'details_ue' => $detailsUE,
            'pourcentage_credits' => round(($totalCreditsValides / self::CREDITS_TOTAL_ANNEE) * 100, 2)
        ];
    }

    public function calculerMoyenneGeneraleEtudiant($etudiantId)
    {
        $resultatsCredits = $this->calculerCreditsValidesEtudiant($etudiantId);
        $moyennesUE = [];

        foreach ($resultatsCredits['details_ue'] as $ue) {
            if (!$ue['eliminee'] && $ue['moyenne'] > 0) {
                $moyennesUE[] = $ue['moyenne'];
            }
        }

        $moyenneGenerale = count($moyennesUE) > 0
            ? array_sum($moyennesUE) / count($moyennesUE)
            : 0;

        return round($moyenneGenerale, 2);
    }

    private function verifierNoteEliminatoire($etudiantId)
    {
        return ResultatFinal::where('examen_id', $this->examen_id)
            ->where('etudiant_id', $etudiantId)
            ->where('note', self::NOTE_ELIMINATOIRE)
            ->exists();
    }

    public function determinerDecisionAutomatique($creditsValides, $hasNoteEliminatoire, $isSessionRattrapage)
    {
        if ($isSessionRattrapage) {
            // Session 2 (Rattrapage)
            if ($hasNoteEliminatoire) {
                return 'REDOUBLANT'; // Note 0 = redoublement obligatoire
            }

            if ($creditsValides >= self::CREDITS_ADMISSION_CONDITIONNELLE_SESSION_2) {
                return 'ADMIS_CONDITIONNELLEMENT';
            } else {
                return 'REDOUBLANT';
            }
        } else {
            // Session 1 (Normale)
            if ($creditsValides >= self::CREDITS_ADMISSION_SESSION_1) {
                return 'ADMIS';
            } else {
                return 'AJOURNE'; // Passe en session 2
            }
        }
    }

    private function convertirDecision($decision, $isSessionRattrapage)
    {
        $mapping = [
            'ADMIS' => ResultatFinal::DECISION_ADMIS,
            'RATTRAPAGE' => ResultatFinal::DECISION_RATTRAPAGE,
            'REDOUBLANT' => ResultatFinal::DECISION_REDOUBLANT,
            'EXCLUS' => ResultatFinal::DECISION_EXCLUS
        ];

        return $mapping[$decision] ?? ResultatFinal::DECISION_RATTRAPAGE;
    }

    private function genererObservationsEtudiant($decision, $creditsValides, $hasNoteEliminatoire)
    {
        $observations = [];

        if ($hasNoteEliminatoire) {
            $observations[] = "⚠️ L'étudiant a au moins une note éliminatoire (0)";
        }

        $observations[] = "Crédits validés : {$creditsValides}/" . self::CREDITS_TOTAL_ANNEE;
        $observations[] = $this->getLibelleDecisionDetaille($decision, $creditsValides);

        return implode("\n", $observations);
    }

    private function getLibelleDecisionDetaille($decision, $creditsValides)
    {
        $libelles = [
            'ADMIS' => "Admis - L'étudiant a validé tous les 60 crédits",
            'ADMIS_CONDITIONNELLEMENT' => "Admis conditionnellement - L'étudiant a validé {$creditsValides}/60 crédits (minimum 40 requis)",
            'AJOURNE' => "Ajourné - L'étudiant doit passer en session de rattrapage ({$creditsValides}/60 crédits)",
            'REDOUBLANT' => "Redoublant - L'étudiant a validé moins de 40 crédits ({$creditsValides}/60)",
            'ELIMINE' => "Éliminé - L'étudiant a au moins une note éliminatoire (0)"
        ];

        return $libelles[$decision] ?? $decision;
    }

    private function mettreAJourResultatFinal($etudiantId, $decision, $details)
    {
        ResultatFinal::where('examen_id', $this->examen_id)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
            ->update([
                'decision' => $decision,
                'moyenne_generale' => $details['moyenne_generale'] ?? null,
                'credits_valides' => $details['credits_valides'] ?? null,
                'observations' => $details['observations'] ?? null
            ]);
    }

    private function mettreAJourStatistiquesDecision(&$statistiques, $decision)
    {
        switch ($decision) {
            case 'ADMIS':
                $statistiques['admis']++;
                break;
            case 'ADMIS_CONDITIONNELLEMENT':
                $statistiques['admis_conditionnellement']++;
                break;
            case 'AJOURNE':
                $statistiques['ajournes']++;
                break;
            case 'REDOUBLANT':
                $statistiques['redoublants']++;
                break;
            case 'ELIMINE':
                $statistiques['elimines']++;
                break;
        }
    }

    public function enregistrerDecision($etudiantId, $decision, $moyenne, $pointsJury = 0, $observations = null)
    {
        $decisions = $this->decisions_speciales ?? [];
        $resultatsCredits = $this->calculerCreditsValidesEtudiant($etudiantId);

        $decisions[$etudiantId] = [
            'moyenne' => $moyenne,
            'moyenne_generale' => $this->calculerMoyenneGeneraleEtudiant($etudiantId),
            'credits_valides' => $resultatsCredits['total_credits_valides'],
            'pourcentage_credits' => $resultatsCredits['pourcentage_credits'],
            'decision' => $decision,
            'points_jury' => $pointsJury,
            'observations' => $observations,
            'details_ue' => $resultatsCredits['details_ue']
        ];

        $this->decisions_speciales = $decisions;

        ResultatFinal::where('examen_id', $this->examen_id)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
            ->update(['decision' => $decision]);

        $this->save();
        return $decisions[$etudiantId];
    }

    // Finalisation et publication
    public function finaliser($userId)
    {
        if ($this->statut !== self::STATUT_EN_COURS) {
            throw new \Exception('La délibération doit être en cours pour être finalisée');
        }

        DB::beginTransaction();
        try {
            $this->statut = self::STATUT_TERMINEE;
            $this->date_finalisation = now();
            $this->finalise_par = $userId;
            $this->mettreAJourStatistiques();
            $this->save();

            DB::commit();
            Log::info('Délibération finalisée', [
                'deliberation_id' => $this->id,
                'finalise_par' => $userId,
                'admis' => $this->nombre_admis,
                'ajournes' => $this->nombre_ajournes,
                'exclus' => $this->nombre_exclus,
                'rachats' => $this->nombre_rachats
            ]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la finalisation de la délibération', [
                'deliberation_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function mettreAJourStatistiques()
    {
        $decisions = $this->decisions_speciales ?? [];
        $admis = 0;
        $ajournes = 0;
        $exclus = 0;
        $rachats = 0;

        foreach ($decisions as $decision) {
            switch ($decision['decision']) {
                case ResultatFinal::DECISION_ADMIS:
                    $admis++;
                    break;
                case ResultatFinal::DECISION_RATTRAPAGE:
                    $ajournes++;
                    break;
                case ResultatFinal::DECISION_EXCLUS:
                    $exclus++;
                    break;
                case ResultatFinal::DECISION_REDOUBLANT:
                    $rachats++;
                    break;
            }
        }

        $this->nombre_admis = $admis;
        $this->nombre_ajournes = $ajournes;
        $this->nombre_exclus = $exclus;
        $this->nombre_rachats = $rachats;
    }

    public function publier($userId)
    {
        if ($this->statut !== self::STATUT_TERMINEE) {
            throw new \Exception('La délibération doit être terminée pour être publiée');
        }

        DB::beginTransaction();
        try {
            ResultatFinal::where('deliberation_id', $this->id)
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->each(function ($resultat) use ($userId) {
                    $resultat->changerStatut(ResultatFinal::STATUT_PUBLIE, $userId, true);
                });

            $this->statut = self::STATUT_VALIDEE;
            $this->date_publication = now();
            $this->save();

            DB::commit();
            Log::info('Délibération publiée', [
                'deliberation_id' => $this->id,
                'publie_par' => $userId
            ]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la publication de la délibération', [
                'deliberation_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function annuler($userId, $raison = null)
    {
        if ($this->statut === self::STATUT_VALIDEE) {
            throw new \Exception('Impossible d\'annuler une délibération déjà validée et publiée');
        }

        DB::beginTransaction();
        try {
            ResultatFinal::where('deliberation_id', $this->id)
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->each(function ($resultat) use ($userId, $raison) {
                    $resultat->changerStatut(ResultatFinal::STATUT_ANNULE, $userId, false, null);
                });

            $this->decisions_speciales = [];
            $this->statut = self::STATUT_ANNULEE;
            $this->observations = $this->observations . "\n\nAnnulée le " . now()->format('d/m/Y H:i') .
                                " par " . User::find($userId)->name .
                                ($raison ? ". Raison : $raison" : "");
            $this->save();

            DB::commit();
            Log::info('Délibération annulée', [
                'deliberation_id' => $this->id,
                'annule_par' => $userId,
                'raison' => $raison
            ]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation de la délibération', [
                'deliberation_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // Rapports et analyses
    public function genererRapportComplet()
    {
        $rapport = [
            'informations_generales' => $this->getInformationsGenerales(),
            'statistiques_globales' => $this->getStatistiquesGlobales(),
            'details_par_etudiant' => $this->getDetailsParEtudiant(),
            'cas_speciaux' => $this->identifierCasSpeciaux(),
            'synthese_ue' => $this->getSyntheseParUE(),
            'recommandations' => $this->genererRecommandations()
        ];

        return $rapport;
    }

    private function getInformationsGenerales()
    {
        return [
            'niveau' => $this->niveau->nom,
            'session' => $this->session->type,
            'annee_universitaire' => $this->anneeUniversitaire->libelle,
            'date_deliberation' => $this->date_deliberation->format('d/m/Y H:i'),
            'statut' => $this->libelle_statut,
            'nombre_etudiants' => count($this->getEtudiantsConcernes()),
            'seuils' => [
                'admission' => $this->seuil_admission,
                'rachat' => $this->seuil_rachat,
                'credits_requis_session_1' => self::CREDITS_ADMISSION_SESSION_1,
                'credits_requis_session_2' => self::CREDITS_ADMISSION_CONDITIONNELLE_SESSION_2
            ]
        ];
    }

    private function getStatistiquesGlobales()
    {
        $decisions = $this->decisions_speciales ?? [];
        $totalEtudiants = count($decisions);

        $moyennes = array_column($decisions, 'moyenne_generale');
        $credits = array_column($decisions, 'credits_valides');

        $statistiques = [
            'total_etudiants' => $totalEtudiants,
            'decisions' => [
                'admis' => $this->nombre_admis,
                'admis_conditionnellement' => $this->nombre_rachats,
                'ajournes' => $this->nombre_ajournes,
                'exclus' => $this->nombre_exclus,
                'redoublants' => $this->compterRedoublants()
            ],
            'moyennes' => [
                'moyenne_generale_promo' => $totalEtudiants > 0 ? round(array_sum($moyennes) / $totalEtudiants, 2) : 0,
                'moyenne_min' => $totalEtudiants > 0 ? min($moyennes) : 0,
                'moyenne_max' => $totalEtudiants > 0 ? max($moyennes) : 0,
                'ecart_type' => $this->calculerEcartType($moyennes)
            ],
            'credits' => [
                'moyenne_credits_valides' => $totalEtudiants > 0 ? round(array_sum($credits) / $totalEtudiants, 2) : 0,
                'etudiants_60_credits' => $this->compterEtudiantsAvecCredits(60),
                'etudiants_40_59_credits' => $this->compterEtudiantsAvecCredits(40, 59),
                'etudiants_moins_40_credits' => $this->compterEtudiantsAvecCredits(0, 39)
            ],
            'taux_reussite' => $totalEtudiants > 0 ? round((($this->nombre_admis + $this->nombre_rachats) / $totalEtudiants) * 100, 2) : 0
        ];

        return $statistiques;
    }

    private function getDetailsParEtudiant()
    {
        $details = [];
        $decisions = $this->decisions_speciales ?? [];

        foreach ($decisions as $etudiantId => $decision) {
            $etudiant = Etudiant::find($etudiantId);
            if (!$etudiant) continue;

            $detailsUE = $decision['details_ue'] ?? [];

            $details[] = [
                'etudiant' => [
                    'id' => $etudiant->id,
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'matricule' => $etudiant->matricule
                ],
                'resultats' => [
                    'moyenne_generale' => $decision['moyenne_generale'] ?? 0,
                    'credits_valides' => $decision['credits_valides'] ?? 0,
                    'pourcentage_credits' => $decision['pourcentage_credits'] ?? 0,
                    'has_note_eliminatoire' => $decision['has_note_eliminatoire'] ?? false
                ],
                'decision' => [
                    'code' => $decision['decision'],
                    'libelle' => ResultatFinal::getLibelleDecision($decision['decision']),
                    'observations' => $decision['observations']
                ],
                'ue_details' => $detailsUE,
                'points_jury' => $decision['points_jury'] ?? 0,
                'rang' => $this->calculerRangEtudiant($etudiantId, $decisions)
            ];
        }

        usort($details, function($a, $b) {
            return $a['rang'] <=> $b['rang'];
        });

        return $details;
    }

    private function identifierCasSpeciaux()
    {
        $casSpeciaux = [];
        $decisions = $this->decisions_speciales ?? [];

        foreach ($decisions as $etudiantId => $decision) {
            $etudiant = Etudiant::find($etudiantId);
            if (!$etudiant) continue;

            // Cas 1: Étudiants avec note éliminatoire
            if ($decision['has_note_eliminatoire'] ?? false) {
                $casSpeciaux['notes_eliminatoires'][] = [
                    'etudiant' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'matricule' => $etudiant->matricule
                ];
            }

            // Cas 2: Étudiants à la limite (proche des seuils)
            if ($this->estEtudiantLimite($decision)) {
                $casSpeciaux['cas_limites'][] = [
                    'etudiant' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'matricule' => $etudiant->matricule,
                    'moyenne' => $decision['moyenne_generale'] ?? 0,
                    'credits' => $decision['credits_valides'] ?? 0
                ];
            }
        }

        return $casSpeciaux;
    }

    private function getSyntheseParUE()
    {
        $ues = UE::where('niveau_id', $this->niveau_id)->get();
        $synthese = [];

        foreach ($ues as $ue) {
            $resultatsUE = $this->analyserResultatsUE($ue->id);

            $synthese[] = [
                'ue' => [
                    'id' => $ue->id,
                    'nom' => $ue->nom,
                    'code' => $ue->code,
                    'credits' => $ue->credits
                ],
                'statistiques' => [
                    'moyenne_ue' => $resultatsUE['moyenne'],
                    'taux_validation' => $resultatsUE['taux_validation'],
                    'nombre_elimines' => $resultatsUE['nombre_elimines'],
                    'ecart_type' => $resultatsUE['ecart_type']
                ],
                'alerte' => $resultatsUE['taux_validation'] < 50
            ];
        }

        return $synthese;
    }

    private function genererRecommandations()
    {
        $recommandations = [];
        $statistiques = $this->getStatistiquesGlobales();
        $casSpeciaux = $this->identifierCasSpeciaux();

        // Recommandation 1: Sur le taux de réussite global
        if ($statistiques['taux_reussite'] < 50) {
            $recommandations[] = [
                'type' => 'alerte',
                'titre' => 'Taux de réussite faible',
                'description' => "Le taux de réussite global est de {$statistiques['taux_reussite']}%. Une analyse approfondie des méthodes pédagogiques est recommandée.",
                'actions' => [
                    'Identifier les UE avec les plus faibles taux de validation',
                    'Organiser des sessions de soutien pour les étudiants en difficulté',
                    'Revoir les modalités d\'évaluation'
                ]
            ];
        }

        // Recommandation 2: Sur les cas limites
        if (!empty($casSpeciaux['cas_limites'])) {
            $nombreCasLimites = count($casSpeciaux['cas_limites']);
            $recommandations[] = [
                'type' => 'attention',
                'titre' => 'Étudiants en situation limite',
                'description' => "{$nombreCasLimites} étudiants sont très proches des seuils de validation.",
                'actions' => [
                    'Examiner attentivement ces dossiers en jury',
                    'Considérer l\'évolution de ces étudiants sur l\'année',
                    'Vérifier la cohérence des notations'
                ]
            ];
        }

        // Recommandation 3: Sur les notes éliminatoires
        if (!empty($casSpeciaux['notes_eliminatoires'])) {
            $nombreElimines = count($casSpeciaux['notes_eliminatoires']);
            $recommandations[] = [
                'type' => 'important',
                'titre' => 'Notes éliminatoires détectées',
                'description' => "{$nombreElimines} étudiants ont au moins une note éliminatoire (0).",
                'actions' => [
                    'Vérifier les raisons des absences (justifiées ou non)',
                    'Proposer des sessions de rattrapage exceptionnelles si justifié',
                    'Mettre en place un suivi individualisé pour ces étudiants'
                ]
            ];
        }

        return $recommandations;
    }

    // Méthodes utilitaires et calculs
    private function calculerEcartType($valeurs)
    {
        $n = count($valeurs);
        if ($n <= 1) return 0;

        $moyenne = array_sum($valeurs) / $n;
        $variance = 0;

        foreach ($valeurs as $valeur) {
            $variance += pow($valeur - $moyenne, 2);
        }

        return round(sqrt($variance / ($n - 1)), 2);
    }

    private function estEtudiantLimite($decision)
    {
        $isSessionRattrapage = $this->session->isRattrapage();
        $credits = $decision['credits_valides'] ?? 0;

        if ($isSessionRattrapage) {
            return $credits >= 35 && $credits < 40;
        } else {
            return $credits >= 50 && $credits < 60;
        }
    }

    private function calculerRangEtudiant($etudiantId, $decisions)
    {
        $moyennes = [];
        foreach ($decisions as $id => $decision) {
            $moyennes[$id] = $decision['moyenne_generale'] ?? 0;
        }

        arsort($moyennes);
        $rang = 1;

        foreach ($moyennes as $id => $moyenne) {
            if ($id == $etudiantId) {
                return $rang;
            }
            $rang++;
        }

        return 0;
    }

    private function compterRedoublants()
    {
        $decisions = $this->decisions_speciales ?? [];
        $count = 0;

        foreach ($decisions as $decision) {
            if (isset($decision['decision_brute']) && $decision['decision_brute'] === 'REDOUBLANT') {
                $count++;
            }
        }

        return $count;
    }

    private function compterEtudiantsAvecCredits($min, $max = null)
    {
        $decisions = $this->decisions_speciales ?? [];
        $count = 0;

        foreach ($decisions as $decision) {
            $credits = $decision['credits_valides'] ?? 0;
            if ($max === null) {
                if ($credits >= $min) $count++;
            } else {
                if ($credits >= $min && $credits <= $max) $count++;
            }
        }

        return $count;
    }

    private function analyserResultatsUE($ueId)
    {
        $resultats = [];
        $totalValidations = 0;
        $totalEliminés = 0;
        $moyennes = [];

        $decisions = $this->decisions_speciales ?? [];

        foreach ($decisions as $decision) {
            $detailsUE = $decision['details_ue'] ?? [];
            foreach ($detailsUE as $ue) {
                if ($ue['ue_id'] == $ueId) {
                    $moyennes[] = $ue['moyenne'];
                    if ($ue['validee']) $totalValidations++;
                    if ($ue['eliminee']) $totalEliminés++;
                    break;
                }
            }
        }

        $totalEtudiants = count($moyennes);

        return [
            'moyenne' => $totalEtudiants > 0 ? round(array_sum($moyennes) / $totalEtudiants, 2) : 0,
            'taux_validation' => $totalEtudiants > 0 ? round(($totalValidations / $totalEtudiants) * 100, 2) : 0,
            'nombre_elimines' => $totalEliminés,
            'ecart_type' => $this->calculerEcartType($moyennes)
        ];
    }

    // Méthodes de statut
    public function isProgrammee()
    {
        return $this->statut === self::STATUT_PROGRAMMEE;
    }

    public function isEnCours()
    {
        return $this->statut === self::STATUT_EN_COURS;
    }

    public function isTerminee()
    {
        return $this->statut === self::STATUT_TERMINEE;
    }

    public function isValidee()
    {
        return $this->statut === self::STATUT_VALIDEE;
    }

    public function isAnnulee()
    {
        return $this->statut === self::STATUT_ANNULEE;
    }

    // Scopes
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeActiveAnnee($query)
    {
        return $query->whereHas('anneeUniversitaire', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeRattrapage($query)
    {
        return $query->whereHas('session', function ($q) {
            $q->where('type', 'Rattrapage');
        });
    }

    public function scopeNiveauxReguliers($query)
    {
        return $query->whereHas('niveau', function ($q) {
            $q->where('is_concours', false);
        });
    }

    public function scopeEnAttente($query)
    {
        return $query->whereIn('statut', [
            self::STATUT_PROGRAMMEE,
            self::STATUT_EN_COURS
        ]);
    }

    public function scopeTerminees($query)
    {
        return $query->whereIn('statut', [
            self::STATUT_TERMINEE,
            self::STATUT_VALIDEE
        ]);
    }

    // Paramètres par défaut
    public static function getDefaultParamsForNiveau($niveau)
    {
        $defaultParams = [
            'seuil_admission' => 10.00,
            'seuil_rachat' => 9.75,
            'pourcentage_ue_requises' => 80,
            'appliquer_regles_auto' => true
        ];

        $niveauCode = null;

        if ($niveau instanceof Niveau) {
            $niveauCode = $niveau->abr;
        } elseif (is_numeric($niveau)) {
            $niveauObj = Niveau::find($niveau);
            if ($niveauObj) {
                $niveauCode = $niveauObj->abr;
            }
        } else {
            $niveauCode = (string) $niveau;
        }

        if (!$niveauCode) {
            Log::warning('Impossible de déterminer le code du niveau pour les paramètres de délibération', [
                'niveau_input' => $niveau
            ]);
            return $defaultParams;
        }

        $niveauCode = strtoupper($niveauCode);
        switch ($niveauCode) {
            case 'L1':
                break;
            case 'L2':
                $defaultParams['seuil_rachat'] = 9.50;
                break;
            case 'L3':
                break;
            case 'M1':
                $defaultParams['pourcentage_ue_requises'] = 85;
                break;
            case 'M2':
                $defaultParams['seuil_admission'] = 10.50;
                $defaultParams['pourcentage_ue_requises'] = 90;
                break;
            case 'D1':
                $defaultParams['seuil_admission'] = 12.00;
                $defaultParams['seuil_rachat'] = 11.50;
                $defaultParams['pourcentage_ue_requises'] = 95;
                break;
            default:
                Log::info('Utilisation des paramètres par défaut pour le niveau', [
                    'niveau_code' => $niveauCode
                ]);
                break;
        }

        return $defaultParams;
    }

    public function applyDefaultParams()
    {
        if (!$this->niveau) {
            return false;
        }

        $params = self::getDefaultParamsForNiveau($this->niveau->abr);

        $this->seuil_admission = $params['seuil_admission'];
        $this->seuil_rachat = $params['seuil_rachat'];
        $this->pourcentage_ue_requises = $params['pourcentage_ue_requises'];
        $this->appliquer_regles_auto = $params['appliquer_regles_auto'];

        return true;
    }
}