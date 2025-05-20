<?php

namespace App\Livewire\Resultats;

use App\Models\EC;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Resultat;
use App\Models\SessionExam;
use App\Models\FusionOperation;
use App\Services\FusionService;
use App\Services\FusionProcessStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property \Illuminate\Support\Collection $parcours
 */
class FusionIndex extends Component
{
    use WithPagination;

    // Variables de filtrage
    public $niveau_id;
    public $parcours_id;
    public $examen_id;

    // Variables d'état
    public $examen = null;
    public $sessionActive = null;
    public $niveaux = [];
    public $parcours = [];
    public $ecs = [];
    public $estPACES = false;
    public $statut = 'initial'; // 'initial', 'verification', 'fusion', 'validation', 'publie'
    public $etapeProgress = 0;
    public $activeTab = 'process'; // 'process', 'rapport', 'stats'


    // Variables de résultats
    public $rapportCoherence = [];
    public $resultatsStats = null;
    public $resultatsParMatiere = [];
    public $errorCount = 0;
    public $totalManchettes = 0;
    public $totalCopies = 0;
    public $totalResultats = 0;
    public $confirmingFusion = false;
    public $confirmingReset = false;
    public $messageType = '';
    public $message = '';

    // Variables pour le traitement
    public $isProcessing = false;
    public $currentOperationId = null;
    public $currentOperationType = null;
    public $etapeFusion = 1;

    // Distribution des notes (pour le graphique)
    public $distributionNotes = [];


    protected $queryString = [
        'niveau_id' => ['except' => ''],
        'parcours_id' => ['except' => '']
    ];


    /**
     * Méthode permettant de générer dynamiquement les listeners en fonction de l'examen actuel
     *
     * @return array
     */
    public function getListeners()
    {
        $listeners = [
            'switchTab' => 'switchTab',
            'operationCompleted' => 'onOperationCompleted',
            'pollOperationStatus' => 'pollOperationStatus'
        ];

        // Ajouter le listener Echo seulement si un examen est sélectionné
        if ($this->examen_id) {
            $listeners["echo:fusion.{$this->examen_id},FusionOperationCompleted"] = 'handleOperationCompleted';
        }

        return $listeners;
    }

    /**
     * Réinitialise les filtres et redirige vers la page de fusion
     */
    public function reinitialiserFiltres()
    {
        $this->niveau_id = null;
        $this->parcours_id = null;
        $this->examen_id = null;
        $this->examen = null;
        $this->parcours = collect();
        $this->resetResults();

        return redirect()->route('resultats.fusion');
    }

    /**
     * Initialisation du composant
     */
    public function mount()
    {
        $this->sessionActive = SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->with('anneeUniversitaire')
            ->first();

        if (!$this->sessionActive) {
            toastr()->error('Aucune session active trouvée. Veuillez configurer une session active dans les paramètres.');
        }

        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();

        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();

            if ($this->parcours_id && $this->sessionActive) {
                $this->examen = Examen::where('niveau_id', $this->niveau_id)
                    ->where('parcours_id', $this->parcours_id)
                    ->where('session_id', $this->sessionActive->id)
                    ->first();

                if ($this->examen) {
                    $this->examen_id = $this->examen->id;
                    $this->chargerEcs();
                    $this->verifierSiPACES();
                    $this->verifierEtatActuel();
                }
            }
        }
    }

    /**
     * Change l'onglet actif
     *
     * @param string $tab
     */
    public function switchTab($tab)
    {
        if (in_array($tab, ['process', 'rapport', 'stats'])) {
            $this->activeTab = $tab;
        }
    }

    /**
     * Confirme la réinitialisation de la fusion
     */
    public function confirmResetFusion()
    {
        $this->confirmingReset = true;
    }

    /**
     * Charge un examen spécifique
     */
    public function chargerExamen()
    {
        if (!$this->examen_id) return;

        $this->examen = Examen::with(['niveau', 'parcours', 'session'])->find($this->examen_id);

        if ($this->examen) {
            $this->niveau_id = $this->examen->niveau_id;
            $this->parcours_id = $this->examen->parcours_id;
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();

            $this->chargerEcs();
            $this->verifierSiPACES();
            $this->verifierEtatActuel();

            if ($this->resultatsStats && $this->totalResultats > 0) {
                $this->prepareGraphData();
            }
        }
    }

    /**
     * Prépare les données pour les graphiques
     */
    protected function prepareGraphData()
    {
        // Distribution des notes par plage
        $this->distributionNotes = [
            '0-4' => Resultat::where('examen_id', $this->examen_id)
                ->whereBetween('note', [0, 4.99])
                ->count(),
            '5-9' => Resultat::where('examen_id', $this->examen_id)
                ->whereBetween('note', [5, 9.99])
                ->count(),
            '10-14' => Resultat::where('examen_id', $this->examen_id)
                ->whereBetween('note', [10, 14.99])
                ->count(),
            '15-20' => Resultat::where('examen_id', $this->examen_id)
                ->whereBetween('note', [15, 20])
                ->count()
        ];

        // Statistiques par matière
        $this->resultatsParMatiere = [];

        foreach ($this->ecs as $ec) {
            $resultatsEC = Resultat::where('examen_id', $this->examen_id)
                ->where('ec_id', $ec->id)
                ->get();

            if ($resultatsEC->count() > 0) {
                $passCount = $resultatsEC->where('note', '>=', 10)->count();
                $passRate = $resultatsEC->count() > 0 ? round(($passCount / $resultatsEC->count()) * 100, 1) : 0;

                $this->resultatsParMatiere[$ec->id] = [
                    'ec_nom' => $ec->nom,
                    'ec_abr' => $ec->abr,
                    'moyenne' => round($resultatsEC->avg('note'), 2),
                    'min' => $resultatsEC->min('note'),
                    'max' => $resultatsEC->max('note'),
                    'passRate' => $passRate
                ];
            }
        }
    }

    /**
     * Vérifie l'état actuel de la fusion
     */
    public function verifierEtatActuel()
    {
        if (!$this->examen_id) return;

        try {
            $fusionService = new FusionService();
            $statutActuel = $fusionService->getStatutActuel($this->examen_id);

            // Si nous sommes en mode fusion, récupérer également l'étape de fusion
            if ($statutActuel === FusionProcessStatus::FUSION_PROVISOIRE) {
                $this->etapeFusion = $fusionService->determinerEtapeFusion($this->examen_id);
            }

            // Convertir le statut du service en statut UI
            switch ($statutActuel) {
                case FusionProcessStatus::INITIAL:
                    $this->statut = 'initial';
                    $this->etapeProgress = 0;
                    break;
                case FusionProcessStatus::COHERENCE_VERIFIEE:
                    $this->statut = 'verification';
                    $this->etapeProgress = 25;
                    break;
                case FusionProcessStatus::FUSION_PROVISOIRE:
                    $this->statut = 'fusion';
                    $this->etapeProgress = 50;
                    break;
                case FusionProcessStatus::VALIDATION:
                    $this->statut = 'validation';
                    $this->etapeProgress = 75;
                    break;
                case FusionProcessStatus::PUBLICATION:
                case FusionProcessStatus::DELIBERATION:
                    $this->statut = 'publie';
                    $this->etapeProgress = 100;
                    break;
                default:
                    $this->statut = 'initial';
                    $this->etapeProgress = 0;
            }

            // Charger les statistiques si on a déjà des résultats
            if ($this->statut !== 'initial') {
                $this->chargerStatistiquesResultats();
            }
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la vérification de l\'état: ' . $e->getMessage());
            Log::error('Erreur verifierEtatActuel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Charge les statistiques des résultats
     */
    public function chargerStatistiquesResultats()
    {
        if (!$this->examen_id) return;

        $fusionService = new FusionService();
        $stats = $fusionService->calculerStatistiques($this->examen_id);

        $this->resultatsStats = $stats;
        $this->totalResultats = $stats['total'];
        $this->distributionNotes = $stats['distribution'];
        $this->etapeFusion = $stats['etape_fusion'];

        $this->prepareGraphData();
    }

    /**
     * Vérifie si l'examen concerne PACES
     */
    protected function verifierSiPACES()
    {
        if (!$this->examen) return;

        try {
            $niveau = Niveau::find($this->examen->niveau_id);
            $this->estPACES = ($niveau && $niveau->abr == 'PACES' && $niveau->id == 1);
        } catch (\Exception $e) {
            $this->estPACES = false;
        }
    }

    /**
     * Charge les ECs associés à l'examen
     */
    protected function chargerEcs()
    {
        if (!$this->examen_id) return;

        $this->ecs = EC::whereHas('examens', function($query) {
            $query->where('examens.id', $this->examen_id);
        })->get();
    }

    /**
     * Mise à jour du niveau sélectionné
     */
    public function updatedNiveauId()
    {
        $this->parcours = collect();
        $this->parcours_id = null;
        $this->examen_id = null;
        $this->examen = null;
        $this->resetResults();

        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();

            if ($this->parcours->count() == 1) {
                $this->parcours_id = $this->parcours->first()->id;
                $this->updatedParcoursId();
            }
        }
    }

    /**
     * Mise à jour du parcours sélectionné
     */
    public function updatedParcoursId()
    {
        $this->examen_id = null;
        $this->examen = null;
        $this->resetResults();

        if ($this->niveau_id && $this->parcours_id && $this->sessionActive) {
            $this->examen = Examen::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->where('session_id', $this->sessionActive->id)
                ->first();

            if ($this->examen) {
                $this->examen_id = $this->examen->id;
                $this->chargerEcs();
                $this->verifierSiPACES();
                $this->verifierEtatActuel();
            } else {
                toastr()->warning('Aucun examen trouvé pour ce niveau, parcours et session active');
            }
        }
    }

    /**
     * Réinitialise les résultats
     */
    protected function resetResults()
    {
        $this->rapportCoherence = [];
        $this->resultatsStats = null;
        $this->resultatsParMatiere = [];
        $this->distributionNotes = [];
        $this->errorCount = 0;
        $this->totalManchettes = 0;
        $this->totalCopies = 0;
        $this->totalResultats = 0;
        $this->statut = 'initial';
        $this->etapeProgress = 0;
        $this->currentOperationId = null;
        $this->currentOperationType = null;
    }

    /**
     * Vérifie la cohérence des données
     */
    public function verifierCoherence()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        // Vérifier les permissions
        if (!Auth::user()->hasPermissionTo('resultats.verifier')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de vérifier la cohérence');
            return;
        }

        $this->isProcessing = true;

        try {
            $fusionService = new FusionService();
            $this->rapportCoherence = $fusionService->verifierCoherence($this->examen_id);

            $complets = collect($this->rapportCoherence)->where('complet', true)->count();
            $total = count($this->rapportCoherence);

            if ($total > 0) {
                $completionRate = round(($complets / $total) * 100);
                toastr()->success("Vérification terminée : $complets/$total matières complètes ($completionRate%)");
            } else {
                toastr()->warning('Aucune matière trouvée pour cet examen');
            }

            $this->statut = 'verification';
            $this->etapeProgress = 25;
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la vérification: ' . $e->getMessage());
            Log::error('Erreur verifierCoherence', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->isProcessing = false;
    }

    /**
     * Confirme la fusion
     */
    public function confirmerFusion()
    {
        if (!$this->examen_id) {
            $this->message = "Aucun examen sélectionné. Veuillez choisir un niveau et un parcours.";
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        $this->confirmingFusion = true;
    }

    /**
     * Lance le processus de fusion
     *
     * @param bool $force Forcer la fusion même si les résultats sont déjà validés
     */
    public function fusionner($force = false)
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        // Vérifier les permissions
        if (!Auth::user()->hasPermissionTo('resultats.fusion')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de fusionner les données');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingFusion = false;

        try {
            $fusionService = new FusionService();

            // Vérifier si une fusion est déjà en cours
            if ($fusionService->estFusionEnCours($this->examen_id)) {
                toastr()->warning('Une opération de fusion est déjà en cours pour cet examen');
                $this->isProcessing = false;
                return;
            }

            // Log pour le débogage
            Log::info('Démarrage de la fusion', [
                'examen_id' => $this->examen_id,
                'force' => $force
            ]);

            $result = $fusionService->fusionner($this->examen_id, $force);

            if ($result['success']) {
                // Stocker l'ID de l'opération pour le suivi
                $this->currentOperationId = $result['operation_id'];
                $this->currentOperationType = 'fusion';

                // Démarrer le polling pour suivre l'avancement
                $this->dispatchBrowserEvent('startPolling', ['operationId' => $this->currentOperationId]);

                // Mise à jour optimiste de l'UI
                if (!$force && ($this->statut === 'initial' || $this->statut === 'verification')) {
                    $this->statut = 'fusion';
                    $this->etapeProgress = 50;
                }

                toastr()->info($result['message']);
            } else {
                // Log l'erreur pour investigation
                Log::error('Échec du lancement de la fusion', [
                    'examen_id' => $this->examen_id,
                    'message' => $result['message']
                ]);
                toastr()->error($result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Exception lors du lancement de la fusion', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors du lancement de la fusion: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * Interroge le statut d'une opération de fusion en cours
     */
    public function pollOperationStatus()
    {
        if (!$this->currentOperationId) return;

        $operation = FusionOperation::find($this->currentOperationId);
        if (!$operation) return;

        if ($operation->status === 'completed' || $operation->status === 'failed') {
            $this->handleOperationCompleted($operation->id);
        }
    }

    /**
     * Méthode de polling pour les mises à jour de fusion
     * Alias de pollOperationStatus pour la rétrocompatibilité
     */
    public function pollFusionStatus()
    {
        $this->pollOperationStatus();
    }


    /**
     * Gère la fin d'une opération de fusion
     *
     * @param string $operationId
     */
    public function handleOperationCompleted($operationId)
    {
        if ($this->currentOperationId !== $operationId) return;

        $operation = FusionOperation::find($operationId);
        if (!$operation) return;

        // Mettre fin au polling
        $this->dispatchBrowserEvent('stopPolling');

        // Log pour débogage
        Log::info('Opération terminée', [
            'operation_id' => $operationId,
            'type' => $operation->type,
            'status' => $operation->status
        ]);

        // Mettre à jour l'interface en fonction du résultat
        if ($operation->status === 'completed') {
            $result = $operation->result ?? [];

            if ($operation->type === 'fusion') {
                // Récupérer l'étape de fusion
                $this->etapeFusion = $result['stats']['etape_fusion'] ?? 1;

                // Adapter le message selon l'étape
                $etapeLabel = "";
                switch ($this->etapeFusion) {
                    case 1:
                        $etapeLabel = "Première fusion";
                        break;
                    case 2:
                        $etapeLabel = "Deuxième fusion avec calcul des moyennes";
                        break;
                    case 3:
                        $etapeLabel = "Fusion finale (consolidation)";
                        break;
                }

                toastr()->success($etapeLabel . ' terminée avec succès');
                $this->statut = 'fusion';
                $this->etapeProgress = 50;
            } elseif ($operation->type === 'validation') {
                toastr()->success('Validation terminée avec succès');
                $this->statut = 'validation';
                $this->etapeProgress = 75;
            } elseif ($operation->type === 'publication') {
                toastr()->success('Publication terminée avec succès');
                $this->statut = 'publie';
                $this->etapeProgress = 100;
            } elseif ($operation->type === 'reset') {
                toastr()->success('Réinitialisation terminée avec succès');
                $this->statut = 'verification';
                $this->etapeProgress = 25;
                $this->etapeFusion = 1; // Réinitialiser l'étape de fusion
            }
        } else {
            // Log l'erreur pour investigation
            Log::error('Erreur lors de l\'opération', [
                'operation_id' => $operationId,
                'type' => $operation->type,
                'error' => $operation->error_message ?? 'Erreur inconnue'
            ]);
            toastr()->error('Erreur lors de l\'opération: ' . ($operation->error_message ?? 'Une erreur est survenue'));
        }

        // Rafraîchir les statistiques
        $this->verifierEtatActuel();
        $this->currentOperationId = null;
        $this->currentOperationType = null;
        $this->isProcessing = false;
    }

    /**
     * Exécute une refusion
     */
    public function refusionner()
    {
        $this->fusionner(true);
    }

    /**
     * Réinitialise la fusion
     */
    public function resterFusion()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        // Vérifier les permissions
        if (!Auth::user()->hasPermissionTo('resultats.reset-fusion')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de réinitialiser la fusion');
            return;
        }

        $this->isProcessing = true;

        try {
            $fusionService = new FusionService();
            $result = $fusionService->reinitialiserFusion($this->examen_id);

            if ($result['success']) {
                // Stocker l'ID de l'opération pour le suivi
                $this->currentOperationId = $result['operation_id'];
                $this->currentOperationType = 'reset';

                toastr()->info($result['message']);
                // Démarrer le polling pour suivre l'avancement
                $this->dispatchBrowserEvent('startPolling', ['operationId' => $this->currentOperationId]);
            } else {
                toastr()->error($result['message']);
            }
        } catch (\Exception $e) {
            toastr()->error('Erreur lors du lancement de la réinitialisation: ' . $e->getMessage());
            Log::error('Erreur lancement réinitialisation fusion', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->isProcessing = false;
        $this->confirmingReset = false;
    }

    /**
     * Valide les résultats
     */
    public function validerResultats()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        // Vérifier les permissions
        if (!Auth::user()->hasPermissionTo('resultats.validation')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de valider les résultats');
            return;
        }

        $this->isProcessing = true;

        try {
            $fusionService = new FusionService();
            $validation = $fusionService->validerResultats($this->examen_id);

            if ($validation['success']) {
                toastr()->success($validation['message']);
                $this->statut = 'validation';
                $this->etapeProgress = 75;
                $this->verifierEtatActuel();
            } else {
                toastr()->error($validation['message']);
            }
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la validation: ' . $e->getMessage());
            Log::error('Erreur validation résultats', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->isProcessing = false;
    }

    /**
     * Publie les résultats
     */
    public function publierResultats()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        // Vérifier les permissions
        if (!Auth::user()->hasPermissionTo('resultats.publication')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de publier les résultats');
            return;
        }

        $this->isProcessing = true;

        try {
            $fusionService = new FusionService();
            $publication = $fusionService->publierResultats($this->examen_id, $this->estPACES);

            if ($publication['success']) {
                toastr()->success($publication['message']);
                $this->statut = 'publie';
                $this->etapeProgress = 100;
                $this->verifierEtatActuel();
            } else {
                toastr()->error($publication['message']);
            }
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la publication: ' . $e->getMessage());
            Log::error('Erreur publication résultats', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->isProcessing = false;
    }

    /**
     * Exporte les résultats
     */
    public function exporterResultats()
    {
        toastr()->info('Fonctionnalité d\'export en cours de développement');
    }

    /**
     * Rendu de la vue
     */
    public function render()
    {
        $this->statut = 'verification';
        return view('livewire.resultats.fusion-index', [
            'niveaux' => $this->niveaux,
            'parcours' => $this->parcours,
            'ecs' => $this->ecs,
            'distributionNotes' => $this->distributionNotes,
            'activeTab' => $this->activeTab
        ]);
    }
}
