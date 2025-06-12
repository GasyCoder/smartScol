<?php

namespace App\Livewire\Resultats;

use App\Models\UE;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\ResultatFinal;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ResultatsExport;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\CalculAcademiqueService;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 */
class ResultatsFinale extends Component
{
    // Filtres
    public $selectedNiveau;
    public $selectedParcours;
    public $selectedAnneeUniversitaire;

    // Options disponibles
    public $niveaux = [];
    public $parcours = [];
    public $anneesUniversitaires = [];

    // Tab active
    public $activeTab = 'session1';

    // Sessions et disponibilité
    public $sessionNormale;
    public $sessionRattrapage;
    public $showSession2 = false;

    // Paramètres simulation AMÉLIORÉS pour toutes les sessions
    public $simulationParams = [
        // Type de session à simuler
        'session_type' => 'session1', // 'session1' ou 'session2'

        // Paramètres Session 1 (Normale)
        'credits_admission_session1' => 60, // Crédits pour admission directe
        'appliquer_note_eliminatoire_s1' => false, // Bloquer admission si note 0

        // Paramètres Session 2 (Rattrapage)
        'credits_admission_session2' => 40, // Crédits minimum pour admission
        'credits_redoublement_session2' => 20, // En dessous = exclusion
        'appliquer_note_eliminatoire_s2' => true, // Note 0 = exclusion automatique
    ];

    // Résultats et statistiques
    public $resultatsSession1 = [];
    public $resultatsSession2 = [];
    public $statistiquesSession1 = [];
    public $statistiquesSession2 = [];
    public $simulationResults = [];
    public $uesStructure = [];

    protected $rules = [
        'simulationParams.session_type' => 'required|in:session1,session2',
        'simulationParams.credits_admission_session1' => 'required|integer|min:40|max:60',
        'simulationParams.appliquer_note_eliminatoire_s1' => 'boolean',
        'simulationParams.credits_admission_session2' => 'required|integer|min:30|max:60',
        'simulationParams.credits_redoublement_session2' => 'required|integer|min:0|max:40',
        'simulationParams.appliquer_note_eliminatoire_s2' => 'boolean',
    ];

    protected $messages = [
        'simulationParams.session_type.required' => 'Veuillez sélectionner le type de session à simuler.',
        'simulationParams.credits_admission_session1.required' => 'Le nombre de crédits pour admission en session 1 est obligatoire.',
        'simulationParams.credits_admission_session1.min' => 'Le minimum est 40 crédits.',
        'simulationParams.credits_admission_session1.max' => 'Le maximum est 60 crédits.',
        'simulationParams.credits_admission_session2.required' => 'Le nombre de crédits pour admission en session 2 est obligatoire.',
        'simulationParams.credits_admission_session2.min' => 'Le minimum est 30 crédits.',
        'simulationParams.credits_admission_session2.max' => 'Le maximum est 60 crédits.',
        'simulationParams.credits_redoublement_session2.required' => 'Le seuil de redoublement est obligatoire.',
        'simulationParams.credits_redoublement_session2.min' => 'Le minimum est 0 crédit.',
        'simulationParams.credits_redoublement_session2.max' => 'Le maximum est 40 crédits.',
    ];

    public function mount()
    {
        $this->initializeData();
        $this->setDefaultValues();
        $this->loadResultats();
    }

    public function initializeData()
    {
        try {
            $this->anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();
            $this->niveaux = Niveau::where('is_active', true)->orderBy('id', 'asc')->get();
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'initialisation des données: ' . $e->getMessage());
            $this->anneesUniversitaires = collect();
            $this->niveaux = collect();
        }
    }

    public function setDefaultValues()
    {
        try {
            // Année universitaire active par défaut
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            $this->selectedAnneeUniversitaire = $anneeActive?->id;

            // Premier niveau par défaut
            if ($this->niveaux->isNotEmpty()) {
                $this->selectedNiveau = $this->niveaux->first()->id;
                $this->updatedSelectedNiveau();
            }

            // Session par défaut pour simulation
            $this->simulationParams['session_type'] = 'session1';

            // Charger les sessions
            $this->loadSessions();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la définition des valeurs par défaut: ' . $e->getMessage());
        }
    }

    /**
     * NOUVELLE MÉTHODE : Chargement des sessions distinctes
     */
    private function loadSessions()
    {
        if (!$this->selectedAnneeUniversitaire) {
            $this->sessionNormale = null;
            $this->sessionRattrapage = null;
            $this->showSession2 = false;
            return;
        }

        try {
            // Session normale
            $this->sessionNormale = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
                ->where('type', 'Normale')
                ->first();

            // Session rattrapage
            $this->sessionRattrapage = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
                ->where('type', 'Rattrapage')
                ->first();

            // Vérifier la disponibilité de la session 2
            $this->checkSession2Availability();

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des sessions: ' . $e->getMessage());
            $this->sessionNormale = null;
            $this->sessionRattrapage = null;
            $this->showSession2 = false;
        }
    }

    public function updatedSelectedNiveau()
    {
        if ($this->selectedNiveau) {
            try {
                $niveau = Niveau::find($this->selectedNiveau);
                if ($niveau?->has_parcours) {
                    $this->parcours = Parcour::where('niveau_id', $this->selectedNiveau)
                        ->where('is_active', true)
                        ->orderBy('id', 'asc')
                        ->get();
                } else {
                    $this->parcours = collect();
                    $this->selectedParcours = null;
                }

                $this->loadUEStructure();
            } catch (\Exception $e) {
                Log::error('Erreur lors de la mise à jour du niveau: ' . $e->getMessage());
                $this->parcours = collect();
                $this->selectedParcours = null;
            }
        }

        $this->checkSession2Availability();
        $this->loadResultats();
    }

    public function updatedSelectedParcours()
    {
        $this->checkSession2Availability();
        $this->loadResultats();
    }

    public function updatedSelectedAnneeUniversitaire()
    {
        $this->loadSessions();
        $this->checkSession2Availability();
        $this->loadResultats();
    }

    /**
     * LOGIQUE AMÉLIORÉE : Vérification de disponibilité session 2
     */
    private function checkSession2Availability()
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire || !$this->sessionRattrapage) {
            $this->showSession2 = false;
            return;
        }

        try {
            // Vérifier s'il y a des résultats publiés en session de rattrapage
            $hasResultsRattrapage = ResultatFinal::where('session_exam_id', $this->sessionRattrapage->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->selectedNiveau);
                    if ($this->selectedParcours) {
                        $q->where('parcours_id', $this->selectedParcours);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->exists();

            $this->showSession2 = $hasResultsRattrapage;

            Log::info('Vérification session 2', [
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'session_rattrapage_id' => $this->sessionRattrapage->id,
                'has_results' => $hasResultsRattrapage,
                'show_session2' => $this->showSession2
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de la session 2: ' . $e->getMessage());
            $this->showSession2 = false;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Gestion du changement de type de session pour simulation
     */
    public function updatedSimulationParamsSessionType()
    {
        // Réinitialiser les résultats de simulation si on change de session
        $this->simulationResults = [];

        // Ajuster les paramètres par défaut selon le type de session
        if ($this->simulationParams['session_type'] === 'session1') {
            // Valeurs par défaut pour session 1
            if (!isset($this->simulationParams['credits_admission_session1'])) {
                $this->simulationParams['credits_admission_session1'] = 60;
            }
            if (!isset($this->simulationParams['appliquer_note_eliminatoire_s1'])) {
                $this->simulationParams['appliquer_note_eliminatoire_s1'] = false;
            }
        } elseif ($this->simulationParams['session_type'] === 'session2') {
            // Valeurs par défaut pour session 2
            if (!isset($this->simulationParams['credits_admission_session2'])) {
                $this->simulationParams['credits_admission_session2'] = 40;
            }
            if (!isset($this->simulationParams['credits_redoublement_session2'])) {
                $this->simulationParams['credits_redoublement_session2'] = 20;
            }
            if (!isset($this->simulationParams['appliquer_note_eliminatoire_s2'])) {
                $this->simulationParams['appliquer_note_eliminatoire_s2'] = true;
            }
        }

        Log::info('Type de session simulation changé', [
            'nouveau_type' => $this->simulationParams['session_type'],
            'parametres' => $this->simulationParams
        ]);
    }

    private function loadUEStructure()
    {
        if (!$this->selectedNiveau) {
            $this->uesStructure = [];
            return;
        }

        try {
            $this->uesStructure = UE::where('niveau_id', $this->selectedNiveau)
                ->with(['ecs' => function($query) {
                    $query->orderBy('id', 'asc');
                }])
                ->orderBy('id', 'asc')
                ->get()
                ->map(function($ue) {
                    return [
                        'ue' => $ue,
                        'ecs' => $ue->ecs->map(function($ec, $index) {
                            return [
                                'ec' => $ec,
                                'display_name' => 'EC' . ($index + 1) . '. ' . $ec->nom
                            ];
                        })
                    ];
                });

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de la structure UE: ' . $e->getMessage());
            $this->uesStructure = collect();
        }
    }

    public function loadResultats()
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->resultatsSession1 = [];
            $this->resultatsSession2 = [];
            $this->resetValidation();
            return;
        }

        try {
            // Charger résultats session 1 (normale)
            $this->resultatsSession1 = $this->loadResultatsForSession($this->sessionNormale);

            // Charger résultats session 2 (rattrapage) si disponible
            if ($this->showSession2 && $this->sessionRattrapage) {
                $this->resultatsSession2 = $this->loadResultatsForSession($this->sessionRattrapage);
            } else {
                $this->resultatsSession2 = [];
            }

            // Ajuster le type de session par défaut pour la simulation
            if (empty($this->resultatsSession1) && !empty($this->resultatsSession2)) {
                $this->simulationParams['session_type'] = 'session2';
            } elseif (!empty($this->resultatsSession1)) {
                $this->simulationParams['session_type'] = 'session1';
            }

            $this->calculateStatistics();
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des résultats: ' . $e->getMessage());
            $this->resultatsSession1 = [];
            $this->resultatsSession2 = [];
        }
    }

    /**
     * MÉTHODE CORRIGÉE : Chargement des résultats pour une session spécifique
     */
    private function loadResultatsForSession($session)
    {
        if (!$session) return [];

        try {
            $query = ResultatFinal::with(['etudiant', 'ec.ue', 'examen'])
                ->where('session_exam_id', $session->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->selectedNiveau);
                    if ($this->selectedParcours) {
                        $q->where('parcours_id', $this->selectedParcours);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->get();

            $resultatsGroupes = $query->groupBy('etudiant_id');

            return $resultatsGroupes->map(function($resultatsEtudiant) use ($session) {
                $etudiant = $resultatsEtudiant->first()->etudiant;
                $notes = $resultatsEtudiant->keyBy('ec_id');

                $calculAcademique = $this->calculerResultatsAcademiques($resultatsEtudiant);

                return [
                    'etudiant' => $etudiant,
                    'notes' => $notes,
                    'moyennes_ue' => $calculAcademique['moyennes_ue'],
                    'moyenne_generale' => $calculAcademique['moyenne_generale'],
                    'credits_valides' => $calculAcademique['credits_valides'],
                    'total_credits' => $calculAcademique['total_credits'],
                    'has_note_eliminatoire' => $calculAcademique['has_note_eliminatoire'],
                    'decision' => $this->determinerDecision($calculAcademique, $session),
                    'details_ue' => $calculAcademique['details_ue']
                ];
            })
            ->sortBy('etudiant.nom')
            ->values()
            ->toArray();

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des résultats pour la session: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * CALCUL ACADÉMIQUE selon votre logique exacte
     */
    private function calculerResultatsAcademiques($resultatsEtudiant)
    {
        $moyennesUE = [];
        $creditsValides = 0;
        $totalCredits = 0;
        $hasNoteEliminatoire = false;
        $detailsUE = [];

        try {
            $resultatsParUE = $resultatsEtudiant->groupBy('ec.ue_id');

            foreach ($resultatsParUE as $ueId => $notesUE) {
                $ue = $notesUE->first()->ec->ue;
                $totalCredits += $ue->credits ?? 0;

                // LOGIQUE MÉDECINE 1 : Collecter toutes les notes de l'UE
                $notes = $notesUE->pluck('note')->toArray();

                // LOGIQUE MÉDECINE 2 : Vérifier s'il y a une note éliminatoire (0) dans cette UE
                $hasNoteZeroInUE = in_array(0, $notes);

                if ($hasNoteZeroInUE) {
                    // UE éliminée à cause d'une note de 0
                    $hasNoteEliminatoire = true;
                    $moyenneUE = 0;
                    $ueValidee = false;
                } else {
                    // LOGIQUE MÉDECINE 3 : Calculer la moyenne UE = somme notes / nombre EC
                    $moyenneUE = count($notes) > 0 ? array_sum($notes) / count($notes) : 0;
                    $moyenneUE = round($moyenneUE, 2);

                    // LOGIQUE MÉDECINE 4 : UE validée si moyenne >= 10 ET aucune note = 0
                    $ueValidee = $moyenneUE >= 10;

                    if ($ueValidee) {
                        $creditsValides += $ue->credits ?? 0;
                    }
                }

                $moyennesUE[$ueId] = $moyenneUE;
                $detailsUE[] = [
                    'ue_id' => $ueId,
                    'ue_nom' => $ue->nom,
                    'ue_abr' => $ue->abr,
                    'moyenne_ue' => $moyenneUE,
                    'credits' => $ue->credits ?? 0,
                    'validee' => $ueValidee,
                    'eliminee' => $hasNoteZeroInUE,
                    'notes_ec' => $notesUE->map(function($resultat) {
                        return [
                            'ec_nom' => $resultat->ec->nom,
                            'note' => $resultat->note,
                            'est_eliminatoire' => $resultat->note == 0
                        ];
                    })->toArray()
                ];
            }

            // LOGIQUE MÉDECINE 5 : Moyenne générale = somme moyennes UE / nombre UE
            $moyenneGenerale = count($moyennesUE) > 0 ?
                array_sum($moyennesUE) / count($moyennesUE) : 0;

            // LOGIQUE MÉDECINE 6 : Si note éliminatoire, moyenne générale = 0
            if ($hasNoteEliminatoire) {
                $moyenneGenerale = 0;
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul académique médecine: ' . $e->getMessage());
            $moyenneGenerale = 0;
        }

        return [
            'moyennes_ue' => $moyennesUE,
            'moyenne_generale' => round($moyenneGenerale, 2),
            'credits_valides' => $creditsValides,
            'total_credits' => $totalCredits,
            'has_note_eliminatoire' => $hasNoteEliminatoire,
            'details_ue' => $detailsUE
        ];
    }

    /**
     * DÉCISIONS selon votre logique métier exacte
     */
    private function determinerDecision($calculAcademique, $session)
    {
        $creditsValides = $calculAcademique['credits_valides'];
        $totalCredits = $calculAcademique['total_credits'];
        $hasNoteEliminatoire = $calculAcademique['has_note_eliminatoire'];

        if ($session->type === 'Normale') {
            // LOGIQUE MÉDECINE SESSION 1 (NORMALE) :
            // - Si crédits validés = 60 (total) → Admis
            // - Sinon → Rattrapage (même avec note éliminatoire)
            return $creditsValides >= 60 ?
                ResultatFinal::DECISION_ADMIS :
                ResultatFinal::DECISION_RATTRAPAGE;
        } else {
            // LOGIQUE MÉDECINE SESSION 2 (RATTRAPAGE) :
            // - Si note éliminatoire → Exclu
            // - Si crédits validés >= 40 → Admis
            // - Sinon → Redoublant
            if ($hasNoteEliminatoire) {
                return ResultatFinal::DECISION_EXCLUS;
            }

            return $creditsValides >= 40 ?
                ResultatFinal::DECISION_ADMIS :
                ResultatFinal::DECISION_REDOUBLANT;
        }
    }

    private function calculateStatistics()
    {
        $this->statistiquesSession1 = $this->calculateSessionStatistics($this->resultatsSession1);
        $this->statistiquesSession2 = $this->calculateSessionStatistics($this->resultatsSession2);
    }

    private function calculateSessionStatistics($resultats)
    {
        if (empty($resultats)) {
            return [
                'total_etudiants' => 0,
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'moyenne_promo' => 0,
                'taux_reussite' => 0,
                'credits_moyen' => 0
            ];
        }

        try {
            $total = count($resultats);
            $decisions = array_count_values(array_column($resultats, 'decision'));
            $moyennes = array_column($resultats, 'moyenne_generale');
            $credits = array_column($resultats, 'credits_valides');

            $admis = $decisions[ResultatFinal::DECISION_ADMIS] ?? 0;
            $rattrapage = $decisions[ResultatFinal::DECISION_RATTRAPAGE] ?? 0;
            $redoublant = $decisions[ResultatFinal::DECISION_REDOUBLANT] ?? 0;
            $exclus = $decisions[ResultatFinal::DECISION_EXCLUS] ?? 0;

            return [
                'total_etudiants' => $total,
                'admis' => $admis,
                'rattrapage' => $rattrapage,
                'redoublant' => $redoublant,
                'exclus' => $exclus,
                'moyenne_promo' => $total > 0 ? round(array_sum($moyennes) / $total, 2) : 0,
                'credits_moyen' => $total > 0 ? round(array_sum($credits) / $total, 2) : 0,
                'taux_reussite' => $total > 0 ? round(($admis / $total) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques: ' . $e->getMessage());
            return [
                'total_etudiants' => 0,
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'moyenne_promo' => 0,
                'taux_reussite' => 0,
                'credits_moyen' => 0
            ];
        }
    }

    /**
     * NOUVELLE MÉTHODE AMÉLIORÉE : Simulation de délibération pour toutes les sessions
     */
    public function simulerDeliberation()
    {
        $this->validate();

        // Vérifier qu'on a des résultats pour la session sélectionnée
        $resultatsSession = $this->simulationParams['session_type'] === 'session1'
            ? $this->resultatsSession1
            : $this->resultatsSession2;

        if (empty($resultatsSession)) {
            $this->addError('simulation', 'Aucun résultat disponible pour la session sélectionnée.');
            return;
        }

        try {
            $this->simulationResults = collect($resultatsSession)->map(function($resultat) {
                $creditsValides = $resultat['credits_valides'];
                $hasNoteEliminatoire = $resultat['has_note_eliminatoire'];
                $moyenneGenerale = $resultat['moyenne_generale'];

                $decisionActuelle = $resultat['decision'];
                $decisionSimulee = $this->determinerDecisionSimulation($creditsValides, $hasNoteEliminatoire);

                return [
                    'etudiant' => $resultat['etudiant'],
                    'credits_valides' => $creditsValides,
                    'moyenne_generale' => $moyenneGenerale,
                    'has_note_eliminatoire' => $hasNoteEliminatoire,
                    'decision_actuelle' => $decisionActuelle,
                    'decision_simulee' => $decisionSimulee,
                    'changement' => $decisionActuelle !== $decisionSimulee,
                    'details_ue' => $resultat['details_ue'] ?? []
                ];
            })->toArray();

            // Calculer les statistiques de simulation
            $statsSimulation = [
                'total' => count($this->simulationResults),
                'changements' => count(array_filter($this->simulationResults, fn($r) => $r['changement'])),
                'decisions_simulees' => array_count_values(array_column($this->simulationResults, 'decision_simulee'))
            ];

            $sessionName = $this->simulationParams['session_type'] === 'session1' ? 'Session 1' : 'Session 2';

            toastr()->success(
                "Simulation {$sessionName} terminée : {$statsSimulation['changements']} changements détectés sur {$statsSimulation['total']} étudiants."
            );

            $this->dispatch('simulation-complete', $statsSimulation);

            Log::info('Simulation délibération terminée', [
                'session_type' => $this->simulationParams['session_type'],
                'parametres' => $this->simulationParams,
                'statistiques' => $statsSimulation
            ]);

        } catch (\Exception $e) {
            $this->addError('simulation', 'Erreur lors de la simulation: ' . $e->getMessage());
            Log::error('Erreur simulation délibération: ' . $e->getMessage());
        }
    }

    /**
     * NOUVELLE MÉTHODE : Détermine la décision selon les paramètres de simulation
     */
    private function determinerDecisionSimulation($creditsValides, $hasNoteEliminatoire)
    {
        if ($this->simulationParams['session_type'] === 'session1') {
            // SIMULATION SESSION 1
            // Vérifier la règle note éliminatoire si activée
            if ($this->simulationParams['appliquer_note_eliminatoire_s1'] && $hasNoteEliminatoire) {
                return ResultatFinal::DECISION_RATTRAPAGE; // Forcer rattrapage si note 0
            }

            // Critère principal : crédits
            return $creditsValides >= $this->simulationParams['credits_admission_session1'] ?
                ResultatFinal::DECISION_ADMIS :
                ResultatFinal::DECISION_RATTRAPAGE;

        } else {
            // SIMULATION SESSION 2
            // Règle note éliminatoire prioritaire
            if ($this->simulationParams['appliquer_note_eliminatoire_s2'] && $hasNoteEliminatoire) {
                return ResultatFinal::DECISION_EXCLUS;
            }

            // Critères basés sur les crédits
            if ($creditsValides >= $this->simulationParams['credits_admission_session2']) {
                return ResultatFinal::DECISION_ADMIS;
            } elseif ($creditsValides >= $this->simulationParams['credits_redoublement_session2']) {
                return ResultatFinal::DECISION_REDOUBLANT;
            } else {
                return ResultatFinal::DECISION_EXCLUS;
            }
        }
    }

    public function appliquerDecisionsLogiqueMedecine($sessionType)
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->addError('decisions', 'Veuillez sélectionner un niveau et une année universitaire.');
            return;
        }

        try {
            $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

            if (!$session) {
                $this->addError('decisions', 'Session non trouvée.');
                return;
            }

            // Utiliser le service de calcul médecine
            $calculService = new CalculAcademiqueService();
            $result = $calculService->appliquerDecisionsSession($session->id, true);

            if ($result['success']) {
                $stats = $result['statistiques'];
                toastr()->success(
                    "Décisions appliquées selon la logique médecine : " .
                    "{$stats['decisions']['admis']} admis, " .
                    "{$stats['decisions']['rattrapage']} rattrapage, " .
                    "{$stats['decisions']['redoublant']} redoublant, " .
                    "{$stats['decisions']['exclus']} exclus"
                );

                // Recharger les résultats
                $this->loadResultats();
            } else {
                $this->addError('decisions', $result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Erreur application décisions médecine: ' . $e->getMessage());
            $this->addError('decisions', 'Erreur lors de l\'application des décisions: ' . $e->getMessage());
        }
    }

    public function validerCoherenceCalculs($sessionType)
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->addError('validation', 'Veuillez sélectionner un niveau et une année universitaire.');
            return;
        }

        try {
            $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

            if (!$session) {
                $this->addError('validation', 'Session non trouvée.');
                return;
            }

            // Utiliser le service de calcul médecine pour valider
            $calculService = new CalculAcademiqueService();
            $erreurs = $calculService->validerCoherenceCalculsMedecine($session->id, null, true);

            if (empty($erreurs)) {
                toastr()->success('Validation réussie : Tous les calculs sont cohérents selon la logique médecine.');
            } else {
                $messageErreurs = "Erreurs détectées :\n" . implode("\n", array_slice($erreurs, 0, 10));
                if (count($erreurs) > 10) {
                    $messageErreurs .= "\n... et " . (count($erreurs) - 10) . " autres erreurs.";
                }

                $this->addError('validation', $messageErreurs);
                Log::warning('Erreurs de cohérence médecine détectées', [
                    'session_id' => $session->id,
                    'erreurs' => $erreurs
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur validation cohérence médecine: ' . $e->getMessage());
            $this->addError('validation', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    /**
     * EXPORT Excel
     */
    public function exportResults($sessionType)
    {
        if (!$this->validateExport()) {
            return;
        }

        try {
            $session = $this->getSessionForType($sessionType);
            $resultats = $sessionType === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;

            if (empty($resultats)) {
                $this->addError('export', 'Aucun résultat à exporter pour cette session.');
                return;
            }

            $export = new ResultatsExport($resultats, $this->uesStructure, $session);
            $filename = $this->generateExportFilename($sessionType);

            return Excel::download($export, $filename);

        } catch (\Exception $e) {
            $this->addError('export', 'Erreur lors de l\'export: ' . $e->getMessage());
            Log::error('Erreur export Excel: ' . $e->getMessage());
        }
    }

    /**
     * NOUVELLE MÉTHODE : Exporte les résultats avec détails logique médecine
     */
    public function exportResultatsDetaillesMedecine($sessionType)
    {
        if (!$this->validateExport()) {
            return;
        }

        try {
            $session = $this->getSessionForType($sessionType);
            $resultats = $sessionType === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;

            if (empty($resultats)) {
                $this->addError('export', 'Aucun résultat à exporter pour cette session.');
                return;
            }

            // Enrichir les données avec les détails médecine
            $donneesExport = collect($resultats)->map(function($resultat) {
                return [
                    'etudiant' => $resultat['etudiant'],
                    'resultats_academiques' => $resultat,
                    'details_calcul_medecine' => [
                        'methode_calcul' => 'Logique Faculté de Médecine',
                        'formule_moyenne_ue' => 'Somme notes EC / Nombre EC',
                        'formule_moyenne_generale' => 'Somme moyennes UE / Nombre UE',
                        'seuil_validation_ue' => '10/20',
                        'regle_note_eliminatoire' => 'Note 0 élimine l\'UE complète',
                        'credits_requis_session1' => '60 crédits',
                        'credits_requis_session2' => '40 crédits'
                    ]
                ];
            })->toArray();

            // Utiliser l'export existant avec données enrichies
            $export = new ResultatsExport($donneesExport, $this->uesStructure, $session);
            $filename = $this->generateExportFilename($sessionType . '_details_medecine');

            return Excel::download($export, $filename);

        } catch (\Exception $e) {
            $this->addError('export', 'Erreur lors de l\'export détaillé: ' . $e->getMessage());
            Log::error('Erreur export détaillé médecine: ' . $e->getMessage());
        }
    }

    /**
     * NOUVELLE MÉTHODE : Export des résultats de simulation
     */
    public function exportSimulation()
    {
        if (empty($this->simulationResults)) {
            $this->addError('export', 'Aucun résultat de simulation à exporter.');
            return;
        }

        try {
            $sessionType = $this->simulationParams['session_type'];
            $session = $this->getSessionForType($sessionType);

            // Préparer les données d'export avec informations de simulation
            $donneesExport = collect($this->simulationResults)->map(function($resultat) use ($sessionType) {
                return [
                    'etudiant' => $resultat['etudiant'],
                    'resultats_simulation' => $resultat,
                    'parametres_simulation' => [
                        'session_simulee' => ucfirst($sessionType),
                        'criteres_appliques' => $this->getParametresSimulationDescription(),
                        'date_simulation' => now()->format('Y-m-d H:i:s')
                    ]
                ];
            })->toArray();

            $export = new ResultatsExport($donneesExport, $this->uesStructure, $session);
            $filename = $this->generateExportFilename('simulation_' . $sessionType);

            return Excel::download($export, $filename);

        } catch (\Exception $e) {
            $this->addError('export', 'Erreur lors de l\'export de simulation: ' . $e->getMessage());
            Log::error('Erreur export simulation: ' . $e->getMessage());
        }
    }

    /**
     * NOUVELLE MÉTHODE : Description des paramètres de simulation
     */
    private function getParametresSimulationDescription()
    {
        if ($this->simulationParams['session_type'] === 'session1') {
            return [
                'credits_admission' => $this->simulationParams['credits_admission_session1'],
                'note_eliminatoire_bloque_admission' => $this->simulationParams['appliquer_note_eliminatoire_s1'],
                'regle' => 'Si ≥ X crédits → Admis, sinon → Rattrapage'
            ];
        } else {
            return [
                'credits_admission' => $this->simulationParams['credits_admission_session2'],
                'credits_redoublement' => $this->simulationParams['credits_redoublement_session2'],
                'note_eliminatoire_exclusion' => $this->simulationParams['appliquer_note_eliminatoire_s2'],
                'regle' => 'Note 0 → Exclu, ≥ X crédits → Admis, < Y crédits → Exclu, autre → Redoublant'
            ];
        }
    }

    /**
     * EXPORT PDF
     */
    public function exportPDF($sessionType)
    {
        if (!$this->validateExport()) {
            return;
        }

        try {
            $session = $this->getSessionForType($sessionType);
            $resultats = $sessionType === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;
            $statistics = $sessionType === 'session1' ? $this->statistiquesSession1 : $this->statistiquesSession2;

            if (empty($resultats)) {
                $this->addError('export', 'Aucun résultat à exporter pour cette session.');
                return;
            }

            $data = [
                'resultats' => $resultats,
                'session' => $session,
                'niveau' => Niveau::find($this->selectedNiveau),
                'parcours' => $this->selectedParcours ? Parcour::find($this->selectedParcours) : null,
                'anneeUniversitaire' => AnneeUniversitaire::find($this->selectedAnneeUniversitaire),
                'statistics' => $statistics,
                'uesStructure' => $this->uesStructure,
                'dateGeneration' => now()
            ];

            $pdf = Pdf::loadView('exports.resultats-pdf', $data)
                     ->setPaper('a4', 'landscape');

            $filename = $this->generateExportFilename($sessionType, 'pdf');

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Exception $e) {
            $this->addError('export', 'Erreur lors de l\'export PDF: ' . $e->getMessage());
            Log::error('Erreur export PDF: ' . $e->getMessage());
        }
    }

    private function validateExport()
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->addError('export', 'Veuillez sélectionner un niveau et une année universitaire.');
            return false;
        }
        return true;
    }

    private function getSessionForType($sessionType)
    {
        return $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;
    }

    private function generateExportFilename($sessionType, $extension = 'xlsx')
    {
        try {
            $niveau = Niveau::find($this->selectedNiveau);
            $parcours = $this->selectedParcours ? Parcour::find($this->selectedParcours) : null;
            $annee = AnneeUniversitaire::find($this->selectedAnneeUniversitaire);

            $parts = [
                'resultats',
                $sessionType,
                $niveau?->abr ?? $niveau?->nom ?? 'niveau',
                $parcours?->abr ?? $parcours?->nom,
                str_replace(['/', ' '], '_', $annee?->libelle ?? 'annee'),
                now()->format('Y-m-d_H-i-s')
            ];

            $filename = implode('_', array_filter($parts)) . '.' . $extension;
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Erreur génération nom fichier: ' . $e->getMessage());
            return 'resultats_export_' . now()->format('Y-m-d_H-i-s') . '.' . $extension;
        }
    }

    /**
     * NOUVELLES MÉTHODES : Statistiques complètes pour intégration
     */
    public function getStatistiquesCompletes()
    {
        if (!$this->sessionNormale) {
            return null;
        }

        try {
            // Compter depuis la session normale
            $admis = ResultatFinal::where('session_exam_id', $this->sessionNormale->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->selectedNiveau);
                    if ($this->selectedParcours) {
                        $q->where('parcours_id', $this->selectedParcours);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->where('decision', ResultatFinal::DECISION_ADMIS)
                ->distinct('etudiant_id')
                ->count('etudiant_id');

            $rattrapage = ResultatFinal::where('session_exam_id', $this->sessionNormale->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->selectedNiveau);
                    if ($this->selectedParcours) {
                        $q->where('parcours_id', $this->selectedParcours);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->where('decision', ResultatFinal::DECISION_RATTRAPAGE)
                ->distinct('etudiant_id')
                ->count('etudiant_id');

            $totalInscrits = $this->getTotalEtudiantsInscrits();
            $participantsRattrapage = 0;

            if ($this->sessionRattrapage) {
                $participantsRattrapage = ResultatFinal::where('session_exam_id', $this->sessionRattrapage->id)
                    ->whereHas('examen', function($q) {
                        $q->where('niveau_id', $this->selectedNiveau);
                        if ($this->selectedParcours) {
                            $q->where('parcours_id', $this->selectedParcours);
                        }
                    })
                    ->where('statut', ResultatFinal::STATUT_PUBLIE)
                    ->distinct('etudiant_id')
                    ->count('etudiant_id');
            }

            return [
                'total_inscrits' => $totalInscrits,
                'admis_premiere_session' => $admis,
                'eligibles_rattrapage' => $rattrapage,
                'participants_rattrapage' => $participantsRattrapage
            ];

        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques complètes: ' . $e->getMessage());
            return null;
        }
    }

    private function getTotalEtudiantsInscrits()
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            return 0;
        }

        try {
            $query = Etudiant::whereHas('inscriptions', function($q) {
                $q->where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
                  ->where('niveau_id', $this->selectedNiveau);

                if ($this->selectedParcours) {
                    $q->where('parcours_id', $this->selectedParcours);
                }
            });

            return $query->count();
        } catch (\Exception $e) {
            Log::error('Erreur calcul total inscrits: ' . $e->getMessage());
            return 0;
        }
    }

    public function render()
    {
        // Préparer les statistiques complètes pour la vue
        $statistiquesCompletes = null;
        if ($this->sessionNormale && (!empty($this->resultatsSession1) || !empty($this->resultatsSession2))) {
            $statistiquesCompletes = $this->getStatistiquesCompletes();
        }

        return view('livewire.resultats.resultats-finale', [
            'canExport' => !empty($this->resultatsSession1) || !empty($this->resultatsSession2),

            // Données pour intégration avec le système
            'statistiquesCompletes' => $statistiquesCompletes,
            'sessionActive' => $this->sessionNormale, // Session de référence
            'compteursDonnees' => $this->getCompteursDonnees(),

            // États des sessions
            'sessionNormale' => $this->sessionNormale,
            'sessionRattrapage' => $this->sessionRattrapage,

            // Informations sur la simulation
            'simulationDisponible' => !empty($this->resultatsSession1) || !empty($this->resultatsSession2),
            'parametresSimulation' => $this->simulationParams,
        ]);
    }

    /**
     * NOUVELLE MÉTHODE : Compteurs pour données opérationnelles
     */
    public function getCompteursDonnees()
    {
        try {
            $compteurs = [
                'etudiants_session1' => count($this->resultatsSession1),
                'etudiants_session2' => count($this->resultatsSession2),
                'total_ues' => count($this->uesStructure),
                'total_ecs' => collect($this->uesStructure)->sum(function($ue) {
                    return count($ue['ecs']);
                }),
                'simulations_disponibles' => 0
            ];

            // Compteurs spécifiques aux résultats
            if (!empty($this->resultatsSession1)) {
                $compteurs['notes_session1'] = collect($this->resultatsSession1)->sum(function($resultat) {
                    return count($resultat['notes']);
                });
                $compteurs['simulations_disponibles']++;
            }

            if (!empty($this->resultatsSession2)) {
                $compteurs['notes_session2'] = collect($this->resultatsSession2)->sum(function($resultat) {
                    return count($resultat['notes']);
                });
                $compteurs['simulations_disponibles']++;
            }

            return $compteurs;
        } catch (\Exception $e) {
            Log::error('Erreur calcul compteurs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * NOUVELLE MÉTHODE : Mise à jour du tab actif avec validation améliorée
     */
    public function setActiveTab($tab)
    {
        $tabsValides = ['session1', 'session2', 'simulation'];

        if (in_array($tab, $tabsValides)) {
            // Vérifier la disponibilité pour le tab session2
            if ($tab === 'session2' && !$this->showSession2) {
                $this->activeTab = 'session1';
                $this->addError('tab', 'La session 2 n\'est pas disponible pour ce niveau/parcours.');
                return;
            }

            // Vérifier la disponibilité pour le tab simulation
            if ($tab === 'simulation' && empty($this->resultatsSession1) && empty($this->resultatsSession2)) {
                $this->activeTab = 'session1';
                $this->addError('tab', 'La simulation n\'est pas disponible sans résultats.');
                return;
            }

            $this->activeTab = $tab;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Rafraîchissement complet des données
     */
    public function refreshData()
    {
        try {
            $this->loadSessions();
            $this->loadUEStructure();
            $this->loadResultats();
            $this->simulationResults = []; // Réinitialiser les résultats de simulation

            $this->dispatch('data-refreshed');
        } catch (\Exception $e) {
            Log::error('Erreur lors du rafraîchissement: ' . $e->getMessage());
            $this->addError('refresh', 'Erreur lors du rafraîchissement des données.');
        }
    }

    /**
     * MÉTHODE AMÉLIORÉE : Debug et information système
     */
    public function getDebugInfo()
    {
        return [
            'filtres' => [
                'niveau' => $this->selectedNiveau,
                'parcours' => $this->selectedParcours,
                'annee' => $this->selectedAnneeUniversitaire
            ],
            'sessions' => [
                'normale_id' => $this->sessionNormale?->id,
                'rattrapage_id' => $this->sessionRattrapage?->id,
                'show_session2' => $this->showSession2
            ],
            'simulation' => [
                'disponible' => !empty($this->resultatsSession1) || !empty($this->resultatsSession2),
                'type_session' => $this->simulationParams['session_type'],
                'parametres' => $this->simulationParams,
                'nb_resultats' => count($this->simulationResults)
            ],
            'donnees' => [
                'resultats_s1' => count($this->resultatsSession1),
                'resultats_s2' => count($this->resultatsSession2),
                'ues_structure' => count($this->uesStructure)
            ],
            'stats' => [
                's1' => $this->statistiquesSession1,
                's2' => $this->statistiquesSession2
            ]
        ];
    }
}
