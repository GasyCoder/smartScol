<?php

namespace App\Livewire\Resultats;

use App\Models\AnneeUniversitaire;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\SessionExam;
use App\Models\ResultatFinal;
use App\Models\Etudiant;
use App\Models\UE;
use App\Exports\ResultatsExport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Composant pour afficher les résultats finaux avec sessions distinctes
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

    // Paramètres simulation
    public $simulationParams = [
        'credits_requis_session2' => 40,
        'appliquer_note_eliminatoire' => true,
    ];

    // Résultats et statistiques
    public $resultatsSession1 = [];
    public $resultatsSession2 = [];
    public $statistiquesSession1 = [];
    public $statistiquesSession2 = [];
    public $simulationResults = [];
    public $uesStructure = [];

    protected $rules = [
        'simulationParams.credits_requis_session2' => 'required|integer|min:0|max:60',
        'simulationParams.appliquer_note_eliminatoire' => 'boolean',
    ];

    protected $messages = [
        'simulationParams.credits_requis_session2.required' => 'Le nombre de crédits requis est obligatoire.',
        'simulationParams.credits_requis_session2.integer' => 'Le nombre de crédits doit être un nombre entier.',
        'simulationParams.credits_requis_session2.min' => 'Le nombre de crédits ne peut pas être négatif.',
        'simulationParams.credits_requis_session2.max' => 'Le nombre de crédits ne peut pas dépasser 60.',
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
     * LOGIQUE CORRIGÉE : Vérification de disponibilité session 2
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

                // 1. Vérifier s'il y a une note éliminatoire (0) dans cette UE
                $hasNoteZeroInUE = $notesUE->contains('note', 0);

                if ($hasNoteZeroInUE) {
                    // UE éliminée à cause d'une note de 0
                    $hasNoteEliminatoire = true;
                    $moyenneUE = 0;
                    $ueValidee = false;
                } else {
                    // 2. Calculer la moyenne UE = somme notes / nombre EC
                    $moyenneUE = $notesUE->avg('note');

                    // 3. UE validée si moyenne >= 10 ET aucune note = 0
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
                    'moyenne_ue' => round($moyenneUE, 2),
                    'credits' => $ue->credits ?? 0,
                    'validee' => $ueValidee,
                    'eliminee' => $hasNoteZeroInUE
                ];
            }

            // 4. Moyenne générale = moyenne des moyennes UE (pas pondérée)
            $moyenneGenerale = count($moyennesUE) > 0 ?
                array_sum($moyennesUE) / count($moyennesUE) : 0;

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul académique: ' . $e->getMessage());
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
            // SESSION 1 (NORMALE) :
            // - Si crédits validés = total (60) → Admis
            // - Sinon → Rattrapage (même avec note éliminatoire)
            return $creditsValides >= $totalCredits ?
                ResultatFinal::DECISION_ADMIS :
                ResultatFinal::DECISION_RATTRAPAGE;
        } else {
            // SESSION 2 (RATTRAPAGE) :
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
     * SIMULATION avec paramètres personnalisables
     */
    public function simulerDeliberation()
    {
        $this->validate();

        if (!$this->showSession2 || empty($this->resultatsSession2)) {
            $this->addError('simulation', 'La simulation nécessite une session de rattrapage avec des résultats.');
            return;
        }

        try {
            $this->simulationResults = collect($this->resultatsSession2)->map(function($resultat) {
                $creditsValides = $resultat['credits_valides'];
                $hasNoteEliminatoire = $resultat['has_note_eliminatoire'];

                $decisionActuelle = $resultat['decision'];
                $decisionSimulee = $this->determinerDecisionAvecParametres($creditsValides, $hasNoteEliminatoire);

                return [
                    'etudiant' => $resultat['etudiant'],
                    'credits_valides' => $creditsValides,
                    'moyenne_generale' => $resultat['moyenne_generale'],
                    'has_note_eliminatoire' => $hasNoteEliminatoire,
                    'decision_actuelle' => $decisionActuelle,
                    'decision_simulee' => $decisionSimulee,
                    'changement' => $decisionActuelle !== $decisionSimulee
                ];
            })->toArray();

            $this->dispatch('simulation-complete');

        } catch (\Exception $e) {
            $this->addError('simulation', 'Erreur lors de la simulation: ' . $e->getMessage());
            Log::error('Erreur simulation délibération: ' . $e->getMessage());
        }
    }

    private function determinerDecisionAvecParametres($creditsValides, $hasNoteEliminatoire)
    {
        if ($this->simulationParams['appliquer_note_eliminatoire'] && $hasNoteEliminatoire) {
            return ResultatFinal::DECISION_EXCLUS;
        }

        return $creditsValides >= $this->simulationParams['credits_requis_session2'] ?
            ResultatFinal::DECISION_ADMIS :
            ResultatFinal::DECISION_REDOUBLANT;
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
                $sessionType === 'session1' ? 'session1' : 'session2',
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
                })
            ];

            // Compteurs spécifiques aux résultats
            if (!empty($this->resultatsSession1)) {
                $compteurs['notes_session1'] = collect($this->resultatsSession1)->sum(function($resultat) {
                    return count($resultat['notes']);
                });
            }

            if (!empty($this->resultatsSession2)) {
                $compteurs['notes_session2'] = collect($this->resultatsSession2)->sum(function($resultat) {
                    return count($resultat['notes']);
                });
            }

            return $compteurs;
        } catch (\Exception $e) {
            Log::error('Erreur calcul compteurs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * NOUVELLE MÉTHODE : Étudiants éligibles au rattrapage depuis session 1
     */
    public function getEtudiantsEligiblesRattrapage()
    {
        if (empty($this->resultatsSession1)) {
            return collect();
        }

        try {
            return collect($this->resultatsSession1)
                ->filter(function($resultat) {
                    return $resultat['decision'] === ResultatFinal::DECISION_RATTRAPAGE;
                })
                ->map(function($resultat) {
                    return [
                        'etudiant' => $resultat['etudiant'],
                        'credits_valides' => $resultat['credits_valides'],
                        'total_credits' => $resultat['total_credits'],
                        'moyenne_generale' => $resultat['moyenne_generale'],
                        'has_note_eliminatoire' => $resultat['has_note_eliminatoire'],
                        'details_ue' => $resultat['details_ue']
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Erreur récupération étudiants éligibles: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * NOUVELLE MÉTHODE : Analyse comparative des sessions
     */
    public function getAnalyseComparative()
    {
        if (empty($this->resultatsSession1) || empty($this->resultatsSession2)) {
            return null;
        }

        try {
            $etudiantsSession1 = collect($this->resultatsSession1)->keyBy('etudiant.id');
            $etudiantsSession2 = collect($this->resultatsSession2)->keyBy('etudiant.id');

            $analyse = [
                'etudiants_communs' => 0,
                'progressions' => [],
                'regressions' => [],
                'stables' => []
            ];

            foreach ($etudiantsSession2 as $etudiantId => $resultatS2) {
                if (isset($etudiantsSession1[$etudiantId])) {
                    $resultatS1 = $etudiantsSession1[$etudiantId];
                    $analyse['etudiants_communs']++;

                    $moyenneS1 = $resultatS1['moyenne_generale'];
                    $moyenneS2 = $resultatS2['moyenne_generale'];
                    $difference = $moyenneS2 - $moyenneS1;

                    $comparaison = [
                        'etudiant' => $resultatS2['etudiant'],
                        'moyenne_s1' => $moyenneS1,
                        'moyenne_s2' => $moyenneS2,
                        'difference' => round($difference, 2),
                        'decision_s1' => $resultatS1['decision'],
                        'decision_s2' => $resultatS2['decision']
                    ];

                    if ($difference > 0.5) {
                        $analyse['progressions'][] = $comparaison;
                    } elseif ($difference < -0.5) {
                        $analyse['regressions'][] = $comparaison;
                    } else {
                        $analyse['stables'][] = $comparaison;
                    }
                }
            }

            return $analyse;
        } catch (\Exception $e) {
            Log::error('Erreur analyse comparative: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Validation des données de cohérence
     */
    public function validateCoherence()
    {
        $erreurs = [];

        try {
            // Vérification 1: Tous les étudiants de session 2 doivent être éligibles depuis session 1
            if (!empty($this->resultatsSession1) && !empty($this->resultatsSession2)) {
                $eligiblesS1 = collect($this->resultatsSession1)
                    ->filter(fn($r) => $r['decision'] === ResultatFinal::DECISION_RATTRAPAGE)
                    ->pluck('etudiant.id');

                $participantsS2 = collect($this->resultatsSession2)->pluck('etudiant.id');

                $intrus = $participantsS2->diff($eligiblesS1);
                if ($intrus->isNotEmpty()) {
                    $erreurs[] = "Des étudiants en session 2 n'étaient pas éligibles depuis la session 1";
                }

                $absents = $eligiblesS1->diff($participantsS2);
                if ($absents->isNotEmpty()) {
                    $erreurs[] = count($absents) . " étudiant(s) éligible(s) n'ont pas participé au rattrapage";
                }
            }

            // Vérification 2: Cohérence des crédits par UE
            foreach ([$this->resultatsSession1, $this->resultatsSession2] as $session => $resultats) {
                foreach ($resultats as $resultat) {
                    $creditsCalcules = collect($resultat['details_ue'])
                        ->where('validee', true)
                        ->sum('credits');

                    if ($creditsCalcules !== $resultat['credits_valides']) {
                        $erreurs[] = "Incohérence crédits pour " . $resultat['etudiant']->nom .
                                   " (session " . ($session + 1) . ")";
                    }
                }
            }

            // Vérification 3: Cohérence des décisions
            foreach ([$this->resultatsSession1, $this->resultatsSession2] as $session => $resultats) {
                foreach ($resultats as $resultat) {
                    $sessionObj = $session === 0 ? $this->sessionNormale : $this->sessionRattrapage;
                    $decisionAttendue = $this->determinerDecision([
                        'credits_valides' => $resultat['credits_valides'],
                        'total_credits' => $resultat['total_credits'],
                        'has_note_eliminatoire' => $resultat['has_note_eliminatoire']
                    ], $sessionObj);

                    if ($decisionAttendue !== $resultat['decision']) {
                        $erreurs[] = "Décision incorrecte pour " . $resultat['etudiant']->nom .
                                   " (session " . ($session + 1) . ")";
                    }
                }
            }

        } catch (\Exception $e) {
            $erreurs[] = "Erreur lors de la validation: " . $e->getMessage();
        }

        return $erreurs;
    }

    /**
     * NOUVELLE MÉTHODE : Mise à jour du tab actif avec validation
     */
    public function setActiveTab($tab)
    {
        $tabsValides = ['session1', 'session2', 'simulation'];

        if (in_array($tab, $tabsValides)) {
            // Vérifier la disponibilité pour les tabs session2 et simulation
            if (($tab === 'session2' || $tab === 'simulation') && !$this->showSession2) {
                $this->activeTab = 'session1';
                $this->addError('tab', 'La session 2 n\'est pas disponible pour ce niveau/parcours.');
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
            'donnees' => [
                'resultats_s1' => count($this->resultatsSession1),
                'resultats_s2' => count($this->resultatsSession2),
                'ues_structure' => count($this->uesStructure)
            ],
            'stats' => [
                's1' => $this->statistiquesSession1,
                's2' => $this->statistiquesSession2
            ],
            'erreurs_coherence' => $this->validateCoherence()
        ];
    }
}
