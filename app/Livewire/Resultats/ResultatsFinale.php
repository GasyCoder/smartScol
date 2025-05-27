<?php

namespace App\Livewire\Resultats;

use App\Models\EC;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\Deliberation;
use Livewire\WithPagination;
use App\Models\ResultatFinal;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ResultatsExport;
use App\Models\AnneeUniversitaire;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class ResultatsFinale extends Component
{
    use WithPagination;

    // Filtres
    public $selectedNiveau;
    public $selectedParcours;
    public $selectedSession;
    public $selectedAnneeUniversitaire;

    // Options disponibles
    public $niveaux = [];
    public $parcours = [];
    public $sessions = [];
    public $anneesUniversitaires = [];

    // Tab active
    public $activeTab = 'session1';

    // Données pour simulation délibération
    public $simulationParams = [
        'seuil_admission' => 10.00,
        'seuil_rachat' => 9.75,
        'credits_requis_session1' => 60,
        'credits_requis_session2' => 40,
    ];

    // Résultats
    public $resultatsSession1 = [];
    public $resultatsSession2 = [];
    public $statistiquesSession1 = [];
    public $statistiquesSession2 = [];
    public $simulationResults = [];

    protected $rules = [
        'simulationParams.seuil_admission' => 'required|numeric|min:0|max:20',
        'simulationParams.seuil_rachat' => 'required|numeric|min:0|max:20',
        'simulationParams.credits_requis_session1' => 'required|integer|min:0|max:100',
        'simulationParams.credits_requis_session2' => 'required|integer|min:0|max:100',
    ];

    public function mount()
    {
        $this->initializeData();
        $this->setDefaultValues();
        $this->loadResultats();
    }

    public function initializeData()
    {
        // Charger les années universitaires
        $this->anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();

        // Charger les niveaux
        $this->niveaux = Niveau::where('is_active', true)->orderBy('id', 'desc')->get();

        // Charger toutes les sessions
        $this->sessions = SessionExam::with('anneeUniversitaire')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function setDefaultValues()
    {
        // Année universitaire active par défaut
        $anneeActive = AnneeUniversitaire::active();
        $this->selectedAnneeUniversitaire = $anneeActive ? $anneeActive->id : null;

        // Session active par défaut
        if ($this->selectedAnneeUniversitaire) {
            $sessionActive = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
                ->where('is_current', true)
                ->first();
            $this->selectedSession = $sessionActive ? $sessionActive->id : null;
        }

        // Premier niveau par défaut
        if ($this->niveaux->isNotEmpty()) {
            $this->selectedNiveau = $this->niveaux->first()->id;
            $this->updatedSelectedNiveau();
        }
    }

    public function updatedSelectedNiveau()
    {
        if ($this->selectedNiveau) {
            $niveau = Niveau::find($this->selectedNiveau);
            if ($niveau && $niveau->has_parcours) {
                $this->parcours = Parcour::where('niveau_id', $this->selectedNiveau)
                    ->where('is_active', true)
                    ->get();
            } else {
                $this->parcours = collect();
                $this->selectedParcours = null;
            }
        } else {
            $this->parcours = collect();
            $this->selectedParcours = null;
        }

        $this->loadResultats();
    }

    public function updatedSelectedParcours()
    {
        $this->loadResultats();
    }

    public function updatedSelectedSession()
    {
        $this->loadResultats();
    }

    public function updatedSelectedAnneeUniversitaire()
    {
        // Recharger les sessions pour cette année
        if ($this->selectedAnneeUniversitaire) {
            $this->sessions = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
                ->orderBy('date_start')
                ->get();

            // Sélectionner la session active si elle existe
            $sessionActive = $this->sessions->where('is_current', true)->first();
            $this->selectedSession = $sessionActive ? $sessionActive->id :
                ($this->sessions->isNotEmpty() ? $this->sessions->first()->id : null);
        }

        $this->loadResultats();
    }

    public function loadResultats()
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->resultatsSession1 = [];
            $this->resultatsSession2 = [];
            return;
        }

        // Charger les sessions normale et rattrapage
        $sessionNormale = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
            ->where('type', 'Normale')
            ->first();

        $sessionRattrapage = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
            ->where('type', 'Rattrapage')
            ->first();

        // Charger les résultats pour chaque session
        $this->resultatsSession1 = $this->loadResultatsForSession($sessionNormale);
        $this->resultatsSession2 = $this->loadResultatsForSession($sessionRattrapage);

        // Calculer les statistiques
        $this->calculateStatistics();
    }

    private function loadResultatsForSession($session)
    {
        if (!$session) {
            return [];
        }

        $query = ResultatFinal::with(['etudiant', 'ec.ue', 'examen'])
            ->whereHas('examen', function($q) use ($session) {
                $q->where('session_id', $session->id)
                  ->where('niveau_id', $this->selectedNiveau);

                if ($this->selectedParcours) {
                    $q->where('parcours_id', $this->selectedParcours);
                }
            })
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->orderBy('etudiant_id')
            ->get();

        // Grouper par étudiant
        $resultatsGroupes = $query->groupBy('etudiant_id')->map(function($resultatsEtudiant) use ($session) {
            $etudiant = $resultatsEtudiant->first()->etudiant;
            $notes = $resultatsEtudiant->keyBy('ec_id');

            // Calculer la moyenne et les crédits
            $moyenneGenerale = $this->calculerMoyenneEtudiant($resultatsEtudiant);
            $creditsValides = $this->calculerCreditsValides($resultatsEtudiant);
            $decision = $resultatsEtudiant->first()->decision;

            return [
                'etudiant' => $etudiant,
                'notes' => $notes,
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => $creditsValides,
                'decision' => $decision,
                'decision_libelle' => ResultatFinal::getLibellesDecisions()[$decision] ?? 'Non définie'
            ];
        });

        return $resultatsGroupes->values()->toArray();
    }

    private function calculerMoyenneEtudiant($resultatsEtudiant)
    {
        if ($resultatsEtudiant->isEmpty()) {
            return 0;
        }

        $totalNotes = $resultatsEtudiant->sum('note');
        $nombreNotes = $resultatsEtudiant->count();

        return round($totalNotes / $nombreNotes, 2);
    }

    private function calculerCreditsValides($resultatsEtudiant)
    {
        $creditsValides = 0;

        // Grouper par UE pour calculer les moyennes UE
        $resultatsParUE = $resultatsEtudiant->groupBy('ec.ue_id');

        foreach ($resultatsParUE as $ueId => $notesUE) {
            $hasNoteZero = $notesUE->contains('note', 0);

            if (!$hasNoteZero) {
                $moyenneUE = $notesUE->avg('note');
                if ($moyenneUE >= 10) {
                    $ue = $notesUE->first()->ec->ue;
                    $creditsValides += $ue->credits ?? 5; // 5 crédits par défaut
                }
            }
        }

        return $creditsValides;
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
                'taux_reussite' => 0
            ];
        }

        $total = count($resultats);
        $decisions = array_count_values(array_column($resultats, 'decision'));
        $moyennes = array_column($resultats, 'moyenne_generale');

        $admis = ($decisions[ResultatFinal::DECISION_ADMIS] ?? 0);
        $rattrapage = ($decisions[ResultatFinal::DECISION_RATTRAPAGE] ?? 0);
        $redoublant = ($decisions[ResultatFinal::DECISION_REDOUBLANT] ?? 0);
        $exclus = ($decisions[ResultatFinal::DECISION_EXCLUS] ?? 0);

        return [
            'total_etudiants' => $total,
            'admis' => $admis,
            'rattrapage' => $rattrapage,
            'redoublant' => $redoublant,
            'exclus' => $exclus,
            'moyenne_promo' => $total > 0 ? round(array_sum($moyennes) / $total, 2) : 0,
            'taux_reussite' => $total > 0 ? round(($admis / $total) * 100, 2) : 0
        ];
    }

    public function simulerDeliberation()
    {
        $this->validate();

        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->addError('simulation', 'Veuillez sélectionner un niveau et une année universitaire.');
            return;
        }

        // Obtenir la session de rattrapage
        $sessionRattrapage = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
            ->where('type', 'Rattrapage')
            ->first();

        if (!$sessionRattrapage) {
            $this->addError('simulation', 'Aucune session de rattrapage trouvée pour cette année.');
            return;
        }

        // Simuler les décisions avec les nouveaux paramètres
        $this->simulationResults = $this->simulateDeliberationDecisions($sessionRattrapage);

        $this->dispatch('simulation-complete');
    }

    private function simulateDeliberationDecisions($session)
    {
        $resultats = [];

        // Obtenir tous les étudiants concernés
        $etudiants = Etudiant::where('niveau_id', $this->selectedNiveau)
            ->when($this->selectedParcours, function($q) {
                $q->where('parcours_id', $this->selectedParcours);
            })
            ->where('is_active', true)
            ->get();

        foreach ($etudiants as $etudiant) {
            $creditsValides = $this->calculerCreditsValidesEtudiant($etudiant->id, $session->id);
            $moyenneGenerale = $this->calculerMoyenneGeneraleEtudiant($etudiant->id, $session->id);
            $hasNoteEliminatoire = $this->verifierNoteEliminatoire($etudiant->id, $session->id);

            // Appliquer les nouveaux critères
            $decision = $this->determinerDecisionSimulee($creditsValides, $hasNoteEliminatoire, $moyenneGenerale);

            $resultats[] = [
                'etudiant' => $etudiant,
                'credits_valides' => $creditsValides,
                'moyenne_generale' => $moyenneGenerale,
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'decision_actuelle' => $this->getDecisionActuelle($etudiant->id, $session->id),
                'decision_simulee' => $decision,
                'changement' => $this->getDecisionActuelle($etudiant->id, $session->id) !== $decision
            ];
        }

        return $resultats;
    }

    private function determinerDecisionSimulee($creditsValides, $hasNoteEliminatoire, $moyenneGenerale)
    {
        if ($hasNoteEliminatoire) {
            return ResultatFinal::DECISION_EXCLUS;
        }

        if ($creditsValides >= $this->simulationParams['credits_requis_session2']) {
            return ResultatFinal::DECISION_ADMIS;
        }

        if ($moyenneGenerale >= $this->simulationParams['seuil_rachat']) {
            return ResultatFinal::DECISION_RATTRAPAGE;
        }

        return ResultatFinal::DECISION_REDOUBLANT;
    }

    private function calculerCreditsValidesEtudiant($etudiantId, $sessionId)
    {
        // Implémentation similaire à celle du modèle Deliberation
        return ResultatFinal::where('etudiant_id', $etudiantId)
            ->whereHas('examen', function($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->get()
            ->groupBy('ec.ue_id')
            ->sum(function($notesUE) {
                $hasNoteZero = $notesUE->contains('note', 0);
                if (!$hasNoteZero && $notesUE->avg('note') >= 10) {
                    return $notesUE->first()->ec->ue->credits ?? 5;
                }
                return 0;
            });
    }

    private function calculerMoyenneGeneraleEtudiant($etudiantId, $sessionId)
    {
        $moyenne = ResultatFinal::where('etudiant_id', $etudiantId)
            ->whereHas('examen', function($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->avg('note');

        return round($moyenne ?? 0, 2);
    }

    private function verifierNoteEliminatoire($etudiantId, $sessionId)
    {
        return ResultatFinal::where('etudiant_id', $etudiantId)
            ->whereHas('examen', function($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->where('note', 0)
            ->exists();
    }

    private function getDecisionActuelle($etudiantId, $sessionId)
    {
        $resultat = ResultatFinal::where('etudiant_id', $etudiantId)
            ->whereHas('examen', function($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->first();

        return $resultat ? $resultat->decision : null;
    }

    public function exportResults($sessionType)
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->addError('export', 'Veuillez d\'abord sélectionner un niveau et une année universitaire.');
            return;
        }

        $session = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
            ->where('type', $sessionType === 'session1' ? 'Normale' : 'Rattrapage')
            ->first();

        if (!$session) {
            $this->addError('export', 'Session non trouvée.');
            return;
        }

        $resultats = $sessionType === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;

        if (empty($resultats)) {
            $this->addError('export', 'Aucun résultat à exporter.');
            return;
        }

        // Créer l'export Excel
        $export = new ResultatsExport($resultats, $this->getEcsForNiveau(), $session);
        $filename = $this->generateExportFilename($sessionType);

        return Excel::download($export, $filename);
    }

    public function exportPDF($sessionType)
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->addError('export', 'Veuillez d\'abord sélectionner un niveau et une année universitaire.');
            return;
        }

        $session = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
            ->where('type', $sessionType === 'session1' ? 'Normale' : 'Rattrapage')
            ->first();

        if (!$session) {
            $this->addError('export', 'Session non trouvée.');
            return;
        }

        $resultats = $sessionType === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;

        if (empty($resultats)) {
            $this->addError('export', 'Aucun résultat à exporter.');
            return;
        }

        $niveau = Niveau::find($this->selectedNiveau);
        $parcours = $this->selectedParcours ? Parcour::find($this->selectedParcours) : null;
        $anneeUniversitaire = AnneeUniversitaire::find($this->selectedAnneeUniversitaire);
        $statistics = $sessionType === 'session1' ? $this->statistiquesSession1 : $this->statistiquesSession2;

        $data = [
            'resultats' => $resultats,
            'session' => $session,
            'niveau' => $niveau,
            'parcours' => $parcours,
            'anneeUniversitaire' => $anneeUniversitaire,
            'statistics' => $statistics,
            'ecs' => $this->getEcsForNiveau(),
            'dateGeneration' => now()
        ];

        $pdf = Pdf::loadView('exports.resultats-finale-pdf', $data)
                 ->setPaper('a4', 'landscape')
                 ->setOptions(['defaultFont' => 'sans-serif']);

        $filename = $this->generateExportFilename($sessionType, 'pdf');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    private function generateExportFilename($sessionType, $extension = 'xlsx')
    {
        $niveau = Niveau::find($this->selectedNiveau);
        $parcours = $this->selectedParcours ? Parcour::find($this->selectedParcours) : null;
        $annee = AnneeUniversitaire::find($this->selectedAnneeUniversitaire);

        $filename = 'resultats_' . ($sessionType === 'session1' ? 'session1' : 'session2');
        $filename .= '_' . ($niveau ? $niveau->abr : 'niveau');

        if ($parcours) {
            $filename .= '_' . $parcours->abr;
        }

        if ($annee) {
            $filename .= '_' . $annee->libelle;
        }

        $filename .= '_' . now()->format('Y-m-d_H-i-s');
        $filename .= '.' . $extension;

        return $filename;
    }

    public function render()
    {
        return view('livewire.resultats.resultats-finale', [
            'ecs' => $this->getEcsForNiveau(),
            'canExport' => !empty($this->resultatsSession1) || !empty($this->resultatsSession2)
        ]);
    }

    private function getEcsForNiveau()
    {
        if (!$this->selectedNiveau) {
            return collect();
        }

        return \App\Models\EC::whereHas('ue', function($q) {
            $q->where('niveau_id', $this->selectedNiveau);
        })->with('ue')->get()->groupBy('ue.nom');
    }
}
