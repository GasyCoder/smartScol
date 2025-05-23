<?php

namespace App\Livewire\Resultats;

use App\Models\EC;
use App\Models\Copie;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\Resultat;
use App\Models\SessionExam;
use Livewire\WithPagination;
use App\Services\FusionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 */
class FusionIndex extends Component
{
    use WithPagination;

    // Propriétés de sélection d'examen
    public $niveau_id;
    public $parcours_id;
    public $examen_id;
    public $examen = null;
    public $sessionActive = null;
    public $niveaux = [];
    public $parcours = [];
    public $ecs = [];
    public $estPACES = false;
    public $showResetButton = false;

    // Propriétés de suivi du processus de fusion
    public $statut = 'initial';
    public $etapeProgress = 0;
    public $etapeFusion = 0;
    public $isProcessing = false;
    public $showVerificationButton = false;

    // Interface utilisateur
    public $activeTab = 'process';
    public $messageType = '';
    public $message = '';

    // Données de rapport et statistiques
    public $rapportCoherence = [];
    public $resultatsStats = [];
    public $resultatsParMatiere = [];
    public $distributionNotes = [];

    // ✅ PROPRIÉTÉS POUR LA DÉLIBÉRATION
    public $showDeliberationInfo = false;
    public $deliberationData = null;
    public $requiresDeliberation = false;
    public $confirmingPublication = false;
    public $confirmingValidation = false;
    public $confirmingAnnulation = false;
    public $confirmingRevenirValidation = false;
    public $confirmingFusion = false;
    public $confirmingReset = false;
    public $confirmingVerification = false;
    public $contexteExamen = null;


    /**
     * Propriétés calculées pour l'interface
     */
    public function getShowVerificationButtonProperty()
    {
        return $this->statut === 'fusion' && $this->etapeFusion >= 1;
    }

    public function getShowPublicationButtonProperty()
    {
        return $this->statut === 'fusion' && $this->etapeFusion >= 3;
    }

    public function getShowResetButtonProperty()
    {
        return $this->statut === 'fusion' && $this->etapeFusion > 0;
    }

    protected $listeners = ['switchTab' => 'switchTab'];

    protected $queryString = [
        'niveau_id' => ['except' => ''],
        'parcours_id' => ['except' => '']
    ];

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
            return;
        }

        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();

        if ($this->niveau_id) {
            $this->loadParcours();
            if ($this->parcours_id && $this->sessionActive) {
                $this->loadExamen();
            }
        }

        $this->verifierEtatActuel();
    }

    /**
     * Charge les parcours disponibles pour le niveau sélectionné
     */
    private function loadParcours()
    {
        $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Charge l'examen correspondant au niveau et parcours sélectionnés
     */
    private function loadExamen()
    {
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
            $this->examen_id = null;
            $this->examen = null;
            $this->showVerificationButton = false;
            Log::warning('Aucun examen trouvé pour les paramètres donnés', [
                'niveau_id' => $this->niveau_id,
                'parcours_id' => $this->parcours_id,
                'session_id' => $this->sessionActive->id,
            ]);
            toastr()->error('Aucun examen trouvé. Veuillez vérifier votre sélection de niveau et de parcours.');
        }
    }

    /**
     * Réinitialise tous les filtres et données
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
     * Change l'onglet actif et charge les données correspondantes
     */
    public function switchTab($tab)
    {
        $this->activeTab = $tab;

        if ($tab === 'rapport-stats') {
            $this->chargerRapportCoherence();
            $this->chargerStatistiquesSimples();
        }
    }

    /**
     * Charge les statistiques simples des résultats
     */
    protected function chargerStatistiquesSimples()
    {
        if (!$this->examen_id) {
            $this->resultatsStats = [
                'totalMatieres' => 0,
                'etudiants' => 0,
                'passRate' => 0,
            ];
            return;
        }

        try {
            $resultatsExistants = Resultat::where('examen_id', $this->examen_id)
                ->where('statut', Resultat::STATUT_PROVISOIRE)
                ->exists();

            $totalMatieres = EC::whereHas('examens', function($query) {
                $query->where('examens.id', $this->examen_id);
            })->count();

            $etudiants = Etudiant::where('niveau_id', $this->examen->niveau_id)
                ->where('parcours_id', $this->examen->parcours_id)
                ->where('is_active', true)
                ->count();

            $passRate = 0;

            if ($resultatsExistants) {
                // Calculer à partir des résultats existants
                $admis = Resultat::where('examen_id', $this->examen_id)
                    ->where('note', '>=', 10)
                    ->distinct('etudiant_id')
                    ->count('etudiant_id');

                $passRate = $etudiants > 0 ? round(($admis / $etudiants) * 100, 2) : 0;
            } else {
                // Calculer à partir des copies valides
                $copiesValides = Copie::where('examen_id', $this->examen_id)
                    ->whereNotNull('code_anonymat_id')
                    ->whereHas('codeAnonymat', function ($query) {
                        $query->whereNotNull('code_complet')
                            ->where('code_complet', '!=', '');
                    })
                    ->whereNotNull('note')
                    ->where('note', '>=', 10)
                    ->count();

                // Chaque étudiant devrait avoir une copie par EC
                $totalExpectedCopies = $etudiants * $totalMatieres;
                $passRate = $totalExpectedCopies > 0 ? round(($copiesValides / $totalExpectedCopies) * 100, 2) : 0;
            }

            $this->resultatsStats = [
                'totalMatieres' => $totalMatieres,
                'etudiants' => $etudiants,
                'passRate' => $passRate,
            ];

            Log::info('Statistiques simples chargées', [
                'examen_id' => $this->examen_id,
                'totalMatieres' => $totalMatieres,
                'etudiants' => $etudiants,
                'passRate' => $passRate
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des statistiques', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->resultatsStats = [
                'totalMatieres' => 0,
                'etudiants' => 0,
                'passRate' => 0,
            ];
        }
    }

    /**
     * Charge le rapport de cohérence
     */
    protected function chargerRapportCoherence()
    {
        if (!$this->examen_id) {
            $this->rapportCoherence = [];
            return;
        }

        try {
            $etudiants = Etudiant::where('niveau_id', $this->examen->niveau_id)
                ->where('parcours_id', $this->examen->parcours_id)
                ->where('is_active', true)
                ->get();

            $fusionService = new FusionService();
            $result = $fusionService->verifierCoherence($this->examen_id);

            if ($result['success']) {
                $this->rapportCoherence = $result['data'] ?? [];

                Log::info('Rapport de cohérence chargé', [
                    'examen_id' => $this->examen_id,
                    'nombre_ecs' => count($this->rapportCoherence),
                    'stats' => $result['stats'] ?? []
                ]);
            } else {
                $this->rapportCoherence = [];
                toastr()->error($result['message'] ?? 'Erreur lors du chargement du rapport de cohérence');
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du rapport de cohérence', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->rapportCoherence = [];
            toastr()->error('Erreur lors du chargement du rapport: ' . $e->getMessage());
        }
    }

    /**
     * ✅ CORRECTION : Vérifie l'état actuel du processus de fusion sans référence à STATUT_VALIDE
     */
    public function verifierEtatActuel()
    {
        if (!$this->examen_id) {
            $this->statut = 'initial';
            $this->etapeProgress = 0;
            $this->etapeFusion = 0;
            $this->showVerificationButton = false;
            $this->showResetButton = false;
            return;
        }

        try {
            $resultatProvisoire = Resultat::where('examen_id', $this->examen_id)
                ->where('statut', Resultat::STATUT_PROVISOIRE)
                ->exists();

            // ✅ CORRECTION : Plus de référence à STATUT_VALIDE
            $resultatPublie = Resultat::where('examen_id', $this->examen_id)
                ->where('statut', Resultat::STATUT_PUBLIE)
                ->exists();

            $resultatAnnule = Resultat::where('examen_id', $this->examen_id)
                ->where('statut', Resultat::STATUT_ANNULE)
                ->exists();

            // Logique simplifiée pour le bouton reset
            $this->showResetButton = Resultat::where('examen_id', $this->examen_id)->exists();

            $this->etapeFusion = Resultat::where('examen_id', $this->examen_id)
                ->max('etape_fusion') ?? 0;

            $this->showVerificationButton = $this->examen_id && $this->etapeFusion >= 1;

            // ✅ LOGIQUE SIMPLIFIÉE : Plus que 3 états possibles
            if ($resultatAnnule) {
                $this->statut = 'annule';
                $this->etapeProgress = 100;
            } elseif ($resultatPublie) {
                // Les résultats publiés sont maintenant validés ET publiés en une seule étape
                $this->statut = 'publie';
                $this->etapeProgress = 100;
            } elseif ($resultatProvisoire) {
                $this->statut = 'fusion';
                $this->etapeProgress = match($this->etapeFusion) {
                    1 => 40,
                    2 => 55,
                    3 => 65,
                    default => 25
                };
            } else {
                $this->statut = 'verification';
                $this->etapeProgress = 25;
            }

            // Charger les statistiques si des résultats existent
            if ($resultatProvisoire || $resultatPublie || $resultatAnnule) {
                $this->chargerStatistiquesSimples();
            }

            Log::info('État actuel vérifié', [
                'examen_id' => $this->examen_id,
                'statut' => $this->statut,
                'etapeFusion' => $this->etapeFusion,
                'etapeProgress' => $this->etapeProgress,
                'showVerificationButton' => $this->showVerificationButton,
                'showResetButton' => $this->showResetButton
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de l\'état', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->statut = 'initial';
            $this->etapeProgress = 0;
            $this->etapeFusion = 0;
            $this->showVerificationButton = false;
            $this->showResetButton = false;

            toastr()->error('Erreur lors de la vérification de l\'état: ' . $e->getMessage());
        }
    }

    /**
     * Vérifie si l'examen concerne le niveau PACES
     */
    protected function verifierSiPACES()
    {
        if (!$this->examen) {
            $this->estPACES = false;
            return;
        }

        try {
            $niveau = Niveau::find($this->examen->niveau_id);
            $this->estPACES = ($niveau && $niveau->abr == 'PACES' && $niveau->id == 1);
        } catch (\Exception $e) {
            $this->estPACES = false;
            Log::warning('Erreur lors de la vérification PACES', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Charge les éléments constitutifs (EC) associés à l'examen
     */
    protected function chargerEcs()
    {
        if (!$this->examen_id) {
            $this->ecs = collect();
            return;
        }

        try {
            $this->ecs = EC::whereHas('examens', function($query) {
                $query->where('examens.id', $this->examen_id);
            })->get();
        } catch (\Exception $e) {
            $this->ecs = collect();
            Log::error('Erreur lors du chargement des EC', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Gère le changement de niveau sélectionné
     */
    public function updatedNiveauId()
    {
        $this->parcours = collect();
        $this->parcours_id = null;
        $this->examen_id = null;
        $this->examen = null;
        $this->resetResults();

        if ($this->niveau_id) {
            $this->loadParcours();
            if ($this->parcours->count() == 1) {
                $this->parcours_id = $this->parcours->first()->id;
                $this->updatedParcoursId();
            }
        }
    }

    /**
     * Gère le changement de parcours sélectionné
     */
    public function updatedParcoursId()
    {
        $this->examen_id = null;
        $this->examen = null;
        $this->resetResults();

        if ($this->niveau_id && $this->parcours_id && $this->sessionActive) {
            $this->loadExamen();
        }
    }

    /**
     * Réinitialise les données de résultats
     */
    protected function resetResults()
    {
        $this->rapportCoherence = [];
        $this->resultatsStats = [];
        $this->resultatsParMatiere = [];
        $this->distributionNotes = [];
        $this->statut = 'initial';
        $this->etapeProgress = 0;
        $this->etapeFusion = 0;
        $this->showVerificationButton = false;
        $this->showResetButton = false;
    }

    // Méthodes de confirmation des actions
    public function confirmResetFusion()
    {
        $this->confirmingReset = true;
    }

    public function confirmVerification()
    {
        $this->confirmingVerification = true;
    }

    public function confirmValidation()
    {
        $this->confirmingValidation = true;
    }

    public function confirmPublication()
    {
        $this->confirmingPublication = true;
    }

    public function confirmAnnulation()
    {
        $this->confirmingAnnulation = true;
    }

    public function confirmRevenirValidation()
    {
        $this->confirmingRevenirValidation = true;
    }

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
     * Effectue la vérification de cohérence des données
     */
    public function verifierCoherence()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingVerification = false;

        try {
            $fusionService = new FusionService();
            $result = $fusionService->verifierCoherence($this->examen_id);

            if ($result['success']) {
                $this->rapportCoherence = $result['data'] ?? [];

                $stats = $result['stats'] ?? ['total' => 0, 'complets' => 0];
                $total = $stats['total'];
                $complets = $stats['complets'];

                if ($total > 0) {
                    $completionRate = round(($complets / $total) * 100);
                    toastr()->success("Vérification terminée : $complets/$total matières complètes ($completionRate%)");
                } else {
                    toastr()->warning('Aucune matière trouvée pour cet examen. Vérifiez les données des copies et manchettes.');
                }

                $this->verifierEtatActuel();
                $this->switchTab('rapport-stats');
            } else {
                toastr()->error($result['message'] ?? 'Erreur lors de la vérification');
                $this->rapportCoherence = [];
            }

        } catch (\Exception $e) {
            Log::error('Erreur dans verifierCoherence', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de la vérification: ' . $e->getMessage());
            $this->rapportCoherence = [];
        }

        $this->isProcessing = false;
    }

    /**
     * Lance le processus de fusion
     */
    public function fusionner($force = false)
    {
        if (!Auth::user()->hasPermissionTo('resultats.create')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de fusionner les données');
            return;
        }

        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingFusion = false;

        try {
            $fusionService = new FusionService();
            $result = $fusionService->fusionner($this->examen_id, $force);

            if ($result['success']) {
                $this->etapeFusion = $result['etape'] ?? $this->etapeFusion;
                $this->verifierEtatActuel();
                toastr()->success($result['message']);

                if (isset($result['statistiques']) && $result['statistiques']['resultats_generes'] > 0) {
                    $this->switchTab('rapport-stats');
                }
            } else {
                toastr()->error($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la fusion', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de la fusion: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * Réinitialise le processus de fusion
     */
    public function reinitialiserFusion()
    {
        if (!Auth::user()->hasPermissionTo('resultats.reset-fusion')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de réinitialiser la fusion');
            return;
        }

        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingReset = false;

        try {
            $fusionService = new FusionService();
            $result = $fusionService->resetExam($this->examen_id);

            if ($result['success']) {
                // Remettre les états à zéro
                $this->statut = 'verification';
                $this->etapeProgress = 25;
                $this->etapeFusion = 0;
                $this->showResetButton = false;
                $this->resetResults();

                toastr()->success($result['message']);
                $this->verifierEtatActuel();
                $this->switchTab('rapport-stats');
            } else {
                toastr()->error($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de la réinitialisation: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * ✅ CORRECTION : Valide ET publie les résultats en une seule étape
     */
    public function validerResultats()
    {
        if (!Auth::user()->hasPermissionTo('resultats.validation')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de valider les résultats');
            return;
        }

        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingValidation = false;

        try {
            $fusionService = new FusionService();
            // ✅ CORRECTION : validerResultats passe maintenant directement au statut PUBLIE
            $result = $fusionService->validerResultats($this->examen_id);

            if ($result['success']) {
                toastr()->success($result['message']);
                $this->verifierEtatActuel();
            } else {
                toastr()->error($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de la validation: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * ✅ SUPPRESSION : Cette méthode n'est plus nécessaire car validation et publication sont fusionnées
     * La validation passe directement au statut PUBLIE
     */
    public function publierResultats()
    {
        // Cette méthode n'est plus utilisée dans la logique simplifiée
        toastr()->info('La validation publie automatiquement les résultats.');
    }


    /**
     * Obtient le contexte de l'examen pour déterminer le comportement de délibération
     */
    public function getContexteExamenProperty()
    {
        if (!$this->examen) {
            return null;
        }

        $niveau = $this->examen->niveau;
        $session = $this->examen->session;

        $requiresDeliberation = false;

        // Une délibération est nécessaire si:
        // 1. C'est une session de rattrapage
        // 2. Ce n'est pas un niveau de type concours
        if ($session && $session->isRattrapage() && $niveau && !$niveau->is_concours) {
            $requiresDeliberation = true;
        }

        return [
            'requires_deliberation' => $requiresDeliberation,
            'is_concours' => $niveau ? $niveau->is_concours : false,
            'has_rattrapage' => $niveau ? $niveau->has_rattrapage : false,
            'session_type' => $session ? $session->type : 'N/A',
            'niveau' => $niveau,
            'annee_universitaire' => $session && $session->anneeUniversitaire
                ? $session->anneeUniversitaire->libelle
                : 'N/A'
        ];
    }

    /**
     * Annule les résultats publiés
     */
    public function annulerResultats()
    {
        if (!Auth::user()->hasPermissionTo('resultats.cancel')) {
            toastr()->error('Vous n\'avez pas l\'autorisation d\'annuler les résultats');
            return;
        }

        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingAnnulation = false;

        try {
            $fusionService = new FusionService();
            $result = $fusionService->annulerResultats($this->examen_id);

            if ($result['success']) {
                toastr()->success($result['message']);
                $this->verifierEtatActuel();
            } else {
                toastr()->error($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de l\'annulation: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * ✅ CORRECTION : Remet les résultats annulés à l'état provisoire ou les republie
     */
    public function revenirValidation()
    {
        if (!Auth::user()->hasPermissionTo('resultats.revert-validation')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de revenir à l\'état validé');
            return;
        }

        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingRevenirValidation = false;

        try {
            $fusionService = new FusionService();
            // ✅ CORRECTION : Cette méthode va maintenant revenir au statut PROVISOIRE
            $result = $fusionService->revenirValidation($this->examen_id);

            if ($result['success']) {
                toastr()->success($result['message']);
                $this->verifierEtatActuel();
            } else {
                toastr()->error($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du retour à la validation', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors du retour à la validation: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * Fonction d'export des résultats (en développement)
     */
    public function exporterResultats()
    {
        toastr()->info('Fonctionnalité d\'export en cours de développement');
    }

    /**
     * Réinitialise l'examen
     */
    public function resetExam()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingReset = false;

        try {
            $fusionService = new FusionService();
            $result = $fusionService->resetExam($this->examen_id);

            if ($result['success']) {
                toastr()->success($result['message']);

                // Remettre l'interface à l'état initial
                $this->verifierEtatActuel();
                $this->rapportCoherence = [];
                $this->resultatsStats = [];
                $this->resetResults();

                // Recharger la page de rapport pour voir l'état vide
                $this->switchTab('rapport-stats');
            } else {
                toastr()->error($result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation dans FusionIndex', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de la réinitialisation: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * Confirme la réinitialisation complète
     */
    public function confirmResetExam()
    {
        $this->confirmingReset = true;
    }

    /**
     * Annule la confirmation de reset
     */
    public function cancelReset()
    {
        $this->confirmingReset = false;
    }

    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.resultats.fusion-index', [
            'examen' => $this->examen,
            'statut' => $this->statut,
            'etapeFusion' => $this->etapeFusion,
            'etapeProgress' => $this->etapeProgress,
            'isProcessing' => $this->isProcessing,
            'activeTab' => $this->activeTab,
            'examen_id' => $this->examen_id,
            'estPACES' => $this->estPACES,
            'confirmingFusion' => $this->confirmingFusion,
            'confirmingReset' => $this->confirmingReset,
            'confirmingVerification' => $this->confirmingVerification,
            'confirmingValidation' => $this->confirmingValidation,
            'confirmingPublication' => $this->confirmingPublication,
            'confirmingAnnulation' => $this->confirmingAnnulation,
            'confirmingRevenirValidation' => $this->confirmingRevenirValidation,
            'rapportCoherence' => $this->rapportCoherence,
            'resultatsStats' => $this->resultatsStats,
            'showVerificationButton' => $this->showVerificationButton,
            'showResetButton' => $this->showResetButton,
        ]);
    }
}
