<?php

namespace App\Livewire\Fusion;

use App\Models\EC;
use App\Models\Copie;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\Manchette;
use App\Models\SessionExam;
use App\Models\CodeAnonymat;
use Livewire\WithPagination;
use App\Models\ResultatFinal;
use App\Models\PresenceExamen;
use App\Models\ResultatFusion;
use App\Services\FusionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $ecs
 */
class FusionIndex extends Component
{
    use WithPagination;

    // Propriétés de sélection d'examen
    public $niveau_id, $parcours_id, $examen_id, $examen = null, $sessionActive = null;
    public $niveaux = [], $parcours = [], $ecs = [], $estPACES = false;

    // Propriétés de suivi du processus
    public $statut = 'initial', $etapeProgress = 0, $etapeFusion = 0, $isProcessing = false;

    // États des boutons
    public $showVerificationButton = false, $showFusionButton = false, $showResetButton = false;

    // Interface utilisateur
    public $activeTab = 'process', $messageType = '', $message = '';

    // Données de rapport et statistiques
    public $rapportCoherence = [], $resultatsStats = [], $resultatsParMatiere = [], $distributionNotes = [];

    // Propriétés pour les confirmations
    public $confirmingVerification = false, $confirmingFusion = false, $confirmingVerify2 = false;
    public $confirmingVerify3 = false, $confirmingValidation = false, $confirmingPublication = false;
    public $confirmingAnnulation = false, $confirmingRevenirValidation = false, $confirmingResetFusion = false;
    public $confirmingExport = false;
    public string $motifAnnulation = '';

    public $fusionProgress = 0;
    public $fusionStep = '';
    public $showProgress = false;

    // Méthodes de confirmation raccourcies
    public function confirmVerification() { $this->confirmingVerification = true; }
    public function confirmFusion() { $this->confirmingFusion = true; }
    public function confirmVerify2() { $this->confirmingVerify2 = true; }
    public function confirmVerify3() { $this->confirmingVerify3 = true; }
    public function confirmValidation() { $this->confirmingValidation = true; }
    public function confirmPublication() { $this->confirmingPublication = true; }
    public function confirmResetFusion() { $this->confirmingResetFusion = true; }
    public function confirmAnnulation() { $this->confirmingAnnulation = true; }
    public function confirmRevenirValidation() { $this->confirmingRevenirValidation = true; }
    public function confirmExport() { $this->confirmingExport = true; }

    protected $listeners = ['switchTab' => 'switchTab'];
    protected $queryString = ['niveau_id' => ['except' => ''], 'parcours_id' => ['except' => '']];

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

        $this->niveaux = Niveau::where('is_active', true)->orderBy('id', 'asc')->get();

        if ($this->niveau_id) {
            $this->loadParcours();
            if ($this->parcours_id) {
                $this->loadExamen();
            }
        }

        $this->verifierEtatActuel();
    }
    

    private function loadParcours()
    {
        $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();
    }

    private function loadExamen()
    {
        $this->examen = Examen::where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->whereNull('deleted_at')
            ->where(function($query) {
                $query->whereHas('manchettes', function($subQuery) {
                    $subQuery->where('session_exam_id', $this->sessionActive->id);
                })
                ->orWhereHas('copies', function($subQuery) {
                    $subQuery->where('session_exam_id', $this->sessionActive->id);
                })
                ->orWhereHas('manchettes')
                ->orWhereHas('copies');
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if ($this->examen) {
            $this->examen_id = $this->examen->id;
            $this->chargerEcs();
            $this->verifierSiPACES();
            $this->verifierEtatActuel();
        } else {
            $this->examen_id = null;
            $this->examen = null;
            $this->resetInterface();
            toastr()->error('Aucun examen trouvé avec des données pour ce niveau et parcours.');
        }
    }

    public function verifierEtatActuel()
    {
        if (!$this->examen_id || !$this->sessionActive) {
            $this->statut = 'initial';
            $this->etapeProgress = 0;
            $this->etapeFusion = 0;
            $this->resetInterface();
            return;
        }

        try {
            $sessionId = $this->sessionActive->id;

            $resultatFinalPublie = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->exists();

            $resultatFinalEnAttente = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId)
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->exists();

            $resultatFinalAnnule = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId)
                ->where('statut', ResultatFinal::STATUT_ANNULE)
                ->exists();

            $resultatsFusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId)
                ->get();

            $maxEtapeFusion = $resultatsFusion->max('etape_fusion') ?? 0;
            $statutsFusion = $resultatsFusion->pluck('statut')->unique();
            
            // Vérifier si rapport existe déjà
            $coherenceVerifiee = !empty($this->rapportCoherence) && isset($this->rapportCoherence['stats']);

            // Logique d'état simplifiée
            if ($resultatFinalAnnule && !$resultatFinalPublie && !$resultatFinalEnAttente) {
                $this->setEtat('annule', 100, 4);
            } elseif ($resultatFinalPublie && !$resultatFinalAnnule) {
                $this->setEtat('publie', 100, 4);
            } elseif ($resultatFinalEnAttente && !$resultatFinalAnnule) {
                $this->setEtat('valide', 85, 4, true);
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VALIDE)) {
                $this->setEtat('valide', 85, 4, true);
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VERIFY_3)) {
                $this->setEtat('fusion', 60, 3, true);
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VERIFY_2)) {
                $this->setEtat('fusion', 45, 2, true);
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VERIFY_1)) {
                $this->setEtat('fusion', 30, 1, true);
            } elseif ($coherenceVerifiee) {
                $this->setEtat('verification', 15, 0, false, true);
            } else {
                $this->setEtat('initial', 0, 0, false, false, true);
            }

            if ($resultatsFusion->isNotEmpty() || $resultatFinalEnAttente || $resultatFinalPublie || $resultatFinalAnnule) {
                $this->chargerStatistiquesSimples();
            }

        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la vérification de l\'état: ' . $e->getMessage());
            $this->setEtat('initial', 0, 0, false, false, true);
        }
    }

    private function setEtat($statut, $progress, $etape, $showReset = false, $showFusion = false, $showVerif = false)
    {
        $this->statut = $statut;
        $this->etapeProgress = $progress;
        $this->etapeFusion = $etape;
        $this->showResetButton = $showReset;
        $this->showFusionButton = $showFusion;
        $this->showVerificationButton = $showVerif;
    }

    private function resetInterface()
    {
        $this->showVerificationButton = false;
        $this->showFusionButton = false;
        $this->showResetButton = false;
    }

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
                $this->rapportCoherence = [
                    'data' => $result['data'] ?? [],
                    'stats' => $result['stats'] ?? ['total' => 0, 'complets' => 0, 'incomplets' => 0],
                    'erreurs_coherence' => [], // Réinitialiser si succès
                    'last_check' => now()->format('d/m/Y H:i')
                ];
                
                $stats = $result['stats'] ?? ['total' => 0, 'complets' => 0];
                $total = $stats['total'];
                $complets = $stats['complets'];

                if ($total > 0) {
                    $completionRate = $complets > 0 ? round(($complets / $total) * 100) : 0;
                    
                    if ($this->sessionActive->type === 'Rattrapage') {
                        $message = $complets === 0 
                            ? "Session de rattrapage : $total matière(s) disponible(s). Prêt pour finalisation de la saisie."
                            : "Session de rattrapage : $complets/$total matières complètes ($completionRate%)";
                    } else {
                        $message = "Vérification terminée : $complets/$total matières complètes ($completionRate%)";
                    }

                    toastr()->success($message);
                    $this->statut = 'verification';
                    $this->etapeProgress = 15;
                    $this->showVerificationButton = false;
                    $this->showFusionButton = $complets > 0;
                    $this->switchTab('rapport-stats');
                } else {
                    $message = $this->sessionActive->type === 'Rattrapage' 
                        ? 'Session de rattrapage initialisée. Créez d\'abord les manchettes et copies pour les étudiants éligibles.'
                        : 'Aucune matière trouvée pour cet examen. Vérifiez les données des copies et manchettes.';
                    
                    toastr()->warning($message);
                    $this->setEtat('initial', 0, 0, false, false, true);
                }
            } else {
                // En cas d'échec, stocker les erreurs de cohérence
                $this->rapportCoherence = [
                    'data' => [],
                    'stats' => ['total' => 0, 'complets' => 0, 'incomplets' => 0],
                    'erreurs_coherence' => $result['erreurs_coherence'] ?? ['Erreur inconnue'],
                    'last_check' => now()->format('d/m/Y H:i')
                ];

                $messageErreur = $result['message'] ?? 'Erreur lors de la vérification';
                toastr()->error($messageErreur);
                
                // Afficher l'onglet rapport pour montrer les erreurs
                $this->switchTab('rapport-stats');
            }
        } catch (\Exception $e) {
            \Log::error('Erreur verifierCoherence', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->rapportCoherence = [
                'data' => [],
                'stats' => ['total' => 0, 'complets' => 0, 'incomplets' => 0],
                'erreurs_coherence' => ['Erreur système : ' . $e->getMessage()],
                'last_check' => now()->format('d/m/Y H:i')
            ];

            toastr()->error('Erreur lors de la vérification: ' . $e->getMessage());
            $this->switchTab('rapport-stats');
        }

        $this->isProcessing = false;
    }

    public function lancerFusion()
    {
        $this->confirmingFusion = false;
        $this->showProgress = true;
        $this->fusionProgress = 0;
        $this->fusionStep = 'Initialisation...';

        try {
            if ($this->statut !== 'verification' || !$this->showFusionButton) {
                toastr()->error('Impossible de commencer la fusion dans l\'état actuel.');
                $this->showProgress = false;
                return;
            }

            $this->fusionStep = 'Préparation des données...';
            $this->fusionProgress = 10;

            // 🔥 IMPORTANT : on passe explicitement la session active
            $result = (new FusionService())->fusionner(
                $this->examen_id,
                $this->sessionActive->id,   // 👈 session exacte (Normale OU Rattrapage)
            );

            if (!$result['success']) {
                toastr()->error($result['message']);
                $this->showProgress = false;
                return;
            }

            $this->fusionProgress = 100;
            $this->fusionStep = 'Fusion terminée !';
            $this->dispatch('hide-progress-after-delay');

            $this->setEtat('fusion', 30, 1, true);
            toastr()->success('Fusion démarrée avec succès.');
            $this->verifierEtatActuel();

        } catch (\Exception $e) {
            $this->showProgress = false;
            toastr()->error('Erreur lors du démarrage de la fusion : ' . $e->getMessage());
        }
    }


    public function passerAVerify2()
    {
        $this->processVerifyStep(ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2, 2, 50, 'confirmingVerify2');
    }

    public function passerAVerify3()
    {
        $this->processVerifyStep(ResultatFusion::STATUT_VERIFY_2, ResultatFusion::STATUT_VERIFY_3, 3, 60, 'confirmingVerify3');
    }


    private function processVerifyStep($fromStatus, $toStatus, $etape, $progress, $confirmProperty)
    {
        $this->$confirmProperty = false;

        try {
            DB::beginTransaction();
            
            $updated = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id)
                ->where('statut', $fromStatus)
                ->update([
                    'statut' => $toStatus,
                    'etape_fusion' => $etape,
                    'modifie_par' => Auth::id(),
                    'updated_at' => now()
                ]);

            if ($updated === 0) {
                DB::rollBack();
                toastr()->error("Aucun résultat trouvé");
                return;
            }

            DB::commit();
            $this->setEtat('fusion', $progress, $etape, true);
            $this->verifierEtatActuel();
            $this->dispatch('$refresh');

            toastr()->success("$updated résultats mis à jour");

        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Erreur: ' . $e->getMessage());
        }
    }


    public function validerResultats()
    {
        if (!Auth::user()->hasPermissionTo('resultats.validation')) {
            toastr()->error('Vous n\'avez pas l\'autorisation');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingValidation = false;

        try {
            DB::beginTransaction();
            
            $updated = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id)
                ->where('statut', ResultatFusion::STATUT_VERIFY_3)
                ->update([
                    'statut' => ResultatFusion::STATUT_VALIDE,
                    'modifie_par' => Auth::id(),
                    'updated_at' => now()
                ]);

            if ($updated === 0) {
                DB::rollBack();
                toastr()->error("Aucun résultat trouvé");
                return;
            }

            DB::commit();
            $this->setEtat('valide', 85, 4);
            toastr()->success("$updated résultats validés");
            $this->verifierEtatActuel();

        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Erreur: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }


    public function publierResultats()
    {
        if (!Auth::user()->hasPermissionTo('resultats.validation')) {
            toastr()->error('Vous n\'avez pas l\'autorisation');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingPublication = false;

        try {
            DB::beginTransaction();

            $resultatsEnAttente = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id)
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->exists();

            if ($resultatsEnAttente) {
                $this->publierResultatsEnAttente();
            } else {
                $this->publierDepuisFusion();
            }

            DB::commit();
            
            // ✅ Forcer mise à jour statut
            $this->statut = 'publie';
            $this->etapeProgress = 100;
            $this->etapeFusion = 4;
            
            $this->verifierEtatActuel();
            $this->dispatch('$refresh'); // ✅ Forcer rafraîchissement UI

        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Erreur: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }


    private function publierResultatsEnAttente()
    {
        $decisions = DB::table('resultats_finaux')
            ->selectRaw("
                etudiant_id,
                CASE 
                    WHEN AVG(note) >= 10 THEN '" . ResultatFinal::DECISION_ADMIS . "'
                    WHEN AVG(note) >= 8 THEN '" . ResultatFinal::DECISION_RATTRAPAGE . "'
                    ELSE '" . ResultatFinal::DECISION_REDOUBLANT . "'
                END as decision
            ")
            ->where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
            ->groupBy('etudiant_id')
            ->get();

        foreach ($decisions as $d) {
            ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id)
                ->where('etudiant_id', $d->etudiant_id)
                ->update([
                    'statut' => ResultatFinal::STATUT_PUBLIE,
                    'decision' => $d->decision,
                    'date_publication' => now(),
                    'modifie_par' => Auth::id()
                ]);
        }

        toastr()->success("Publication réussie");
    }


    private function publierDepuisFusion()
    {
        $statusToFind = $this->etapeFusion == 4 ? ResultatFusion::STATUT_VALIDE : ResultatFusion::STATUT_VERIFY_3;
        
        $resultatsIds = ResultatFusion::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->where('statut', $statusToFind)
            ->pluck('id')
            ->toArray();

        if (empty($resultatsIds)) {
            throw new \Exception("Aucun résultat à publier");
        }

        // 1. Transférer vers resultats_finaux
        $result = (new FusionService())->transfererResultatsOptimise($resultatsIds, Auth::id(), $this->sessionActive);

        if (!$result['success']) {
            throw new \Exception($result['message']);
        }

        // 2. ✅ PUBLIER immédiatement après transfert
        $this->publierResultatsEnAttente();
    }


    public function resetFusion()
    {
        if (!Auth::user()->hasPermissionTo('resultats.reset-fusion')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de réinitialiser la fusion');
            return;
        }

        if (!$this->examen_id || !$this->sessionActive) {
            toastr()->error('Aucun examen ou session sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingResetFusion = false;

        try {
            $deletedFusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id)
                ->delete();

            $deletedFinal = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id)
                ->delete();

            $this->setEtat('verification', 15, 0, false, !empty($this->rapportCoherence));
            $this->chargerRapportCoherence();

            toastr()->success("Fusion réinitialisée pour la session {$this->sessionActive->type}. $deletedFusion fusion et $deletedFinal finaux supprimés.");
            $this->switchTab('rapport-stats');

        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la réinitialisation: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    public function annulerResultats()
    {
        if (!Auth::user()->hasPermissionTo('resultats.cancel')) {
            toastr()->error('Vous n\'avez pas l\'autorisation d\'annuler les résultats');
            $this->confirmingAnnulation = false;
            return;
        }

        if ($this->statut !== 'publie') {
            toastr()->error('Seuls les résultats publiés peuvent être annulés');
            $this->confirmingAnnulation = false;
            return;
        }

        $this->isProcessing = true;
        $this->confirmingAnnulation = false;

        try {
            $result = (new FusionService())->annulerResultats($this->examen_id, $this->motifAnnulation, $this->sessionActive->id);

            if ($result['success']) {
                if ($this->examen) {
                    $this->examen->update([
                        'statut_resultats' => 'annule',
                        'date_annulation_resultats' => now(),
                    ]);
                }

                $this->verifierEtatActuel();
                toastr()->success($result['message'] . " pour la session {$this->sessionActive->type}");
            } else {
                toastr()->error($result['message']);
            }

            $this->motifAnnulation = '';
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de l\'annulation : ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    public function revenirValidation()
    {
        if (!Auth::user()->hasPermissionTo('resultats.reactiver')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de réactiver les résultats');
            $this->confirmingRevenirValidation = false;
            return;
        }

        if ($this->statut !== 'annule') {
            toastr()->error('Seuls les résultats annulés peuvent être réactivés');
            $this->confirmingRevenirValidation = false;
            return;
        }

        $this->isProcessing = true;
        $this->confirmingRevenirValidation = false;

        try {
            $result = (new FusionService())->revenirValidation($this->examen_id, $this->sessionActive->id);

            if ($result['success']) {
                if ($this->examen) {
                    $this->examen->update([
                        'statut_resultats' => 'en_attente_publication',
                        'date_annulation_resultats' => null,
                    ]);
                }

                $this->verifierEtatActuel();
                toastr()->success($result['message'] . " pour la session {$this->sessionActive->type}");
            } else {
                toastr()->error($result['message']);
            }
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la réactivation : ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    // Méthodes utilitaires simplifiées
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

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'rapport-stats') {
            $this->chargerRapportCoherence();
            $this->chargerStatistiquesSimples();
        }
    }

    public function updatedNiveauId()
    {
        $this->parcours = collect();
        $this->parcours_id = null;
        $this->resetExamenAndResults();

        if ($this->niveau_id) {
            $this->loadParcours();
            if ($this->parcours->count() == 1) {
                $this->parcours_id = $this->parcours->first()->id;
                $this->updatedParcoursId();
            }
        }
    }

    public function updatedParcoursId()
    {
        $this->resetExamenAndResults();
        if ($this->niveau_id && $this->parcours_id && $this->sessionActive) {
            $this->loadExamen();
        }
    }

    private function resetExamenAndResults()
    {
        $this->examen_id = null;
        $this->examen = null;
        $this->resetResults();
    }

    protected function resetResults()
    {
        $this->rapportCoherence = [];
        $this->resultatsParMatiere = [];
        $this->distributionNotes = [];
        $this->setEtat('initial', 0, 0);
    }

    protected function chargerStatistiquesSimples()
    {
        if (!$this->examen_id) return;

        try {
            $totalMatieres = EC::whereHas('examens', function($query) {
                $query->where('examens.id', $this->examen_id);
            })->count();

            $etudiants = Etudiant::where('niveau_id', $this->examen->niveau_id)
                ->where('parcours_id', $this->examen->parcours_id)
                ->where('is_active', true)
                ->count();

            $this->resultatsStats = [
                'totalMatieres' => $totalMatieres,
                'etudiants' => $etudiants,
            ];
        } catch (\Exception $e) {
            $this->resultatsStats = ['totalMatieres' => 0, 'etudiants' => 0];
        }
    }

    protected function chargerRapportCoherence()
    {
        if (!$this->examen_id) {
            $this->rapportCoherence = [];
            return;
        }

        // CORRECTION: Ne recharger que si pas déjà chargé
        if (!empty($this->rapportCoherence) && isset($this->rapportCoherence['stats'])) {
            return;
        }

        try {
            $result = (new FusionService())->verifierCoherence($this->examen_id);
            if ($result['success']) {
                // CORRECTION: Structure complète des données
                $this->rapportCoherence = [
                    'data' => $result['data'] ?? [],
                    'stats' => $result['stats'] ?? ['total' => 0, 'complets' => 0, 'incomplets' => 0],
                    'last_check' => now()->format('d/m/Y H:i')
                ];
            } else {
                $this->rapportCoherence = [];
            }
        } catch (\Exception $e) {
            $this->rapportCoherence = [];
        }
    }

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
        }
    }

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
        }
    }

    // Méthodes pour rattrapage simplifiées
    public function initialiserDonneesRattrapage()
    {
        if (!$this->examen_id || $this->sessionActive->type !== 'Rattrapage') {
            toastr()->error('Cette action n\'est disponible que pour les sessions de rattrapage');
            return;
        }

        try {
            $sessionNormale = SessionExam::where('annee_universitaire_id', $this->sessionActive->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                toastr()->error('Aucune session normale trouvée');
                return;
            }

            $etudiantsEchecs = DB::table('resultats_fusion as rf')
                ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                ->where('rf.examen_id', $this->examen_id)
                ->where('rf.session_exam_id', $sessionNormale->id)
                ->where('e.is_active', true)
                ->select('e.id', 'e.nom', 'e.prenom')
                ->groupBy('e.id', 'e.nom', 'e.prenom')
                ->havingRaw('AVG(rf.note) < 10')
                ->get();

            if ($etudiantsEchecs->isEmpty()) {
                toastr()->info('Aucun étudiant en échec trouvé en session normale.');
                return;
            }

            $ecs = EC::whereHas('examens', function($query) {
                $query->where('examens.id', $this->examen_id);
            })->get();

            $manchettesCreees = 0;

            DB::beginTransaction();

            foreach ($etudiantsEchecs as $etudiant) {
                foreach ($ecs as $ec) {
                    $codeAnonymat = CodeAnonymat::firstOrCreate([
                        'examen_id' => $this->examen_id,
                        'ec_id' => $ec->id,
                        'code_complet' => "RAT-{$ec->id}-{$etudiant->id}-" . now()->format('His'),
                    ], [
                        'sequence' => $etudiant->id * 1000 + $ec->id
                    ]);

                    if (!Manchette::where([
                        'examen_id' => $this->examen_id,
                        'session_exam_id' => $this->sessionActive->id,
                        'etudiant_id' => $etudiant->id,
                        'code_anonymat_id' => $codeAnonymat->id,
                    ])->exists()) {
                        Manchette::create([
                            'examen_id' => $this->examen_id,
                            'session_exam_id' => $this->sessionActive->id,
                            'etudiant_id' => $etudiant->id,
                            'code_anonymat_id' => $codeAnonymat->id,
                            'saisie_par' => Auth::id(),
                            'date_saisie' => now()
                        ]);
                        $manchettesCreees++;
                    }
                }
            }

            DB::commit();

            if ($manchettesCreees > 0) {
                toastr()->success("Initialisation réussie : $manchettesCreees manchettes créées pour " . $etudiantsEchecs->count() . " étudiant(s) éligible(s).");
                $this->verifierEtatActuel();
            } else {
                toastr()->info('Toutes les données de rattrapage existent déjà');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Erreur lors de l\'initialisation: ' . $e->getMessage());
        }
    }

    public function getEtudiantsEligiblesRattrapage()
    {
        if (!$this->examen_id || $this->sessionActive->type !== 'Rattrapage') {
            return collect();
        }

        $sessionNormale = SessionExam::where('annee_universitaire_id', $this->sessionActive->annee_universitaire_id)
            ->where('type', 'Normale')
            ->first();

        if (!$sessionNormale) return collect();

        // ✅ FILTRER PAR DÉCISION (pas les admis)
        $resultatsFinaux = ResultatFinal::where('session_exam_id', $sessionNormale->id)
            ->whereHas('examen', function($q) {
                $q->where('niveau_id', $this->examen->niveau_id)
                ->where('parcours_id', $this->examen->parcours_id);
            })
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->whereIn('decision', [
                ResultatFinal::DECISION_RATTRAPAGE,
                ResultatFinal::DECISION_REDOUBLANT
                // ✅ NE PAS inclure DECISION_ADMIS
            ])
            ->with('etudiant')
            ->get();

        return $resultatsFinaux->groupBy('etudiant_id')
            ->map(function($resultats) {
                $etudiant = $resultats->first()->etudiant;
                if (!$etudiant || !$etudiant->is_active) return null;

                return [
                    'etudiant_id' => $etudiant->id,
                    'etudiant' => $etudiant,
                    'decision_normale' => $resultats->first()->decision,
                    'source' => 'decision_officielle'
                ];
            })
            ->filter()
            ->values();
    }
    
    private function calculerMoyenneEtudiant($etudiantId, $sessionId)
    {
        try {
            $resultats = ResultatFinal::where('etudiant_id', $etudiantId)
                ->where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->examen->niveau_id)
                      ->where('parcours_id', $this->examen->parcours_id);
                })
                ->get();

            if ($resultats->isEmpty()) return 0;

            $totalPoints = 0;
            $totalCoeff = 0;

            foreach ($resultats as $resultat) {
                $coeff = $resultat->examen->elementConstitutif->coefficient ?? 1;
                $totalPoints += $resultat->note * $coeff;
                $totalCoeff += $coeff;
            }

            return $totalCoeff > 0 ? round($totalPoints / $totalCoeff, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCompteursDonneesSession()
    {
        if (!$this->examen_id || !$this->sessionActive) {
            return ['manchettes' => 0, 'copies' => 0, 'etudiants' => 0, 'ecs' => 0];
        }

        $manchettes = Manchette::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->count();

        $copies = Copie::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->count();

        $etudiants = Manchette::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->distinct('etudiant_id')
            ->count('etudiant_id');

        $ecs = Copie::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->distinct('ec_id')
            ->count('ec_id');

        return [
            'manchettes' => $manchettes,
            'copies' => $copies,
            'etudiants' => $etudiants,
            'ecs' => $ecs
        ];
    }

    public function diagnosticEligiblesRattrapage()
    {
        if (!$this->examen_id || $this->sessionActive->type !== 'Rattrapage') {
            toastr()->error('Cette méthode ne fonctionne que pour les sessions de rattrapage');
            return;
        }

        $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
        $compteurs = $this->getCompteursDonneesSession();
        
        toastr()->info("Diagnostic : {$etudiantsEligibles->count()} éligibles, {$compteurs['manchettes']} manchettes.");
    }

    // ===== MÉTHODES MANQUANTES AJOUTÉES =====

    /**
     * Classes CSS pour le guide selon la session
     */
    public function getSessionGuideClasses()
    {
        if ($this->sessionActive && $this->sessionActive->type === 'Rattrapage') {
            return 'border-orange-300 bg-orange-50';
        }
        return 'border-blue-300 bg-blue-50';
    }

    /**
     * Classes CSS pour le titre selon la session
     */
    public function getSessionTitleClasses()
    {
        if ($this->sessionActive && $this->sessionActive->type === 'Rattrapage') {
            return 'text-orange-800';
        }
        return 'text-blue-800';
    }

    /**
     * Classes CSS pour le texte selon la session
     */
    public function getSessionTextClasses()
    {
        if ($this->sessionActive && $this->sessionActive->type === 'Rattrapage') {
            return 'text-orange-700';
        }
        return 'text-blue-700';
    }

    /**
     * Guide pour l'état initial
     */
    public function getGuideInitial()
    {
        if ($this->sessionActive && $this->sessionActive->type === 'Rattrapage') {
            return '<p>Session de rattrapage : Initialisez d\'abord les données pour les étudiants éligibles.</p>';
        }
        return '<p>Commencez par vérifier la cohérence des données en cliquant sur le bouton "Vérifier cohérence".</p>';
    }

    /**
     * Guide pour l'état vérification
     */
    public function getGuideVerification()
    {
        $stats = $this->rapportCoherence['stats'] ?? ['complets' => 0, 'total' => 0];
        $complets = $stats['complets'] ?? 0;
        $total = $stats['total'] ?? 0;
        
        return "<p>Vérification terminée : {$complets}/{$total} matières complètes. Vous pouvez maintenant lancer la fusion.</p>";
    }

    /**
     * Guide pour l'état fusion
     */
    public function getGuideFusion()
    {
        $etapeTexte = match($this->etapeFusion) {
            1 => 'Première vérification en cours',
            2 => 'Deuxième vérification en cours', 
            3 => 'Troisième vérification en cours',
            4 => 'Fusion terminée, prête pour validation',
            default => 'Fusion en cours'
        };
        
        return "<p>{$etapeTexte}. Étape {$this->etapeFusion}/4.</p>";
    }

    /**
     * Guide pour l'état validation
     */
    public function getGuideValidation()
    {
        return '<p>Les résultats sont validés et prêts pour la publication. Vous pouvez maintenant publier les résultats.</p>';
    }

    /**
     * Guide pour l'état publié
     */
    public function getGuidePublie()
    {
        return '<p>Les résultats ont été publiés avec succès. Ils sont maintenant visibles pour les étudiants.</p>';
    }

    /**
     * Guide pour l'état annulé
     */
    public function getGuideAnnule()
    {
        return '<p>Les résultats ont été annulés. Vous pouvez les réactiver si nécessaire.</p>';
    }

    /**
     * Informations sur l'état du rattrapage
     */
    public function getEtatRattrapageInfo()
    {
        if (!$this->examen || $this->sessionActive->type !== 'Rattrapage') {
            return '';
        }
        
        $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
        $compteurs = $this->getCompteursDonneesSession();
        
        return "<p class='text-xs mt-2'>Étudiants éligibles : {$etudiantsEligibles->count()}, Manchettes créées : {$compteurs['manchettes']}</p>";
    }

    public function render()
    {
        $statistiquesCompletes = null;
        $compteursDonnees = $this->getCompteursDonneesSession();
        $etudiantsEligibles = collect();

        if ($this->sessionActive && $this->sessionActive->type === 'Rattrapage') {
            $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
        }

        return view('livewire.fusion.fusion-index', [
            'examen' => $this->examen,
            'statut' => $this->statut,
            'etapeFusion' => $this->etapeFusion,
            'etapeProgress' => $this->etapeProgress,
            'isProcessing' => $this->isProcessing,
            'activeTab' => $this->activeTab,
            'examen_id' => $this->examen_id,
            'estPACES' => $this->estPACES,
            'statistiquesCompletes' => $statistiquesCompletes,
            'compteursDonnees' => $compteursDonnees,
            'etudiantsEligibles' => $etudiantsEligibles,
            'confirmingFusion' => $this->confirmingFusion,
            'confirmingResetFusion' => $this->confirmingResetFusion,
            'confirmingVerification' => $this->confirmingVerification,
            'confirmingValidation' => $this->confirmingValidation,
            'confirmingAnnulation' => $this->confirmingAnnulation,
            'confirmingRevenirValidation' => $this->confirmingRevenirValidation,
            'confirmingPublication' => $this->confirmingPublication,
            'confirmingVerify2' => $this->confirmingVerify2,
            'confirmingVerify3' => $this->confirmingVerify3,
            'confirmingExport' => $this->confirmingExport,
            'rapportCoherence' => $this->rapportCoherence,
            'resultatsStats' => $this->resultatsStats,
            'showVerificationButton' => $this->showVerificationButton,
            'showResetButton' => $this->showResetButton,
            'showFusionButton' => $this->showFusionButton,
        ]);
    }


    public function nettoyerDonneesIncoherentes()
    {
        if (!$this->examen_id || !$this->sessionActive) {
            toastr()->error('Aucun examen ou session sélectionné');
            return;
        }

        $this->isProcessing = true;

        try {
            DB::beginTransaction();

            $totalNettoye = 0;
            $details = [];

            // 1. Récupération des EC valides pour cet examen
            $ecIdsExamen = DB::table('examen_ec')
                ->where('examen_id', $this->examen_id)
                ->pluck('ec_id')
                ->toArray();

            $ecIdsValides = EC::whereIn('id', $ecIdsExamen)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            $ecIdsInvalides = array_diff($ecIdsExamen, $ecIdsValides);

            // 2. Suppression des EC invalides de examen_ec
            if (!empty($ecIdsInvalides)) {
                $deletedExamenEc = DB::table('examen_ec')
                    ->where('examen_id', $this->examen_id)
                    ->whereIn('ec_id', $ecIdsInvalides)
                    ->delete();
                
                if ($deletedExamenEc > 0) {
                    $details[] = "$deletedExamenEc association(s) EC invalide(s) dans examen_ec";
                    $totalNettoye += $deletedExamenEc;
                }
            }

            // 3. Suppression des copies avec EC invalides (toutes sessions)
            $copiesInvalides = Copie::where('examen_id', $this->examen_id)
                ->whereNotNull('ec_id')
                ->where(function($query) use ($ecIdsValides) {
                    $query->whereNotIn('ec_id', $ecIdsValides)
                        ->orWhereDoesntHave('ec', function($q) {
                            $q->whereNull('deleted_at')->where('is_active', true);
                        });
                })
                ->delete();

            if ($copiesInvalides > 0) {
                $details[] = "$copiesInvalides copie(s) avec EC invalide";
                $totalNettoye += $copiesInvalides;
            }

            // 4. Suppression des codes d'anonymat avec EC invalides
            $codesInvalides = CodeAnonymat::where('examen_id', $this->examen_id)
                ->whereNotNull('ec_id')
                ->where(function($query) use ($ecIdsValides) {
                    $query->whereNotIn('ec_id', $ecIdsValides)
                        ->orWhereDoesntHave('ec', function($q) {
                            $q->whereNull('deleted_at')->where('is_active', true);
                        });
                })
                ->delete();

            if ($codesInvalides > 0) {
                $details[] = "$codesInvalides code(s) d'anonymat avec EC invalide";
                $totalNettoye += $codesInvalides;
            }

            // 5. Suppression des manchettes orphelines (sans code valide)
            $manchettesOrphelines = Manchette::where('examen_id', $this->examen_id)
                ->where(function($query) {
                    $query->whereNull('code_anonymat_id')
                        ->orWhereDoesntHave('codeAnonymat');
                })
                ->delete();

            if ($manchettesOrphelines > 0) {
                $details[] = "$manchettesOrphelines manchette(s) orpheline(s)";
                $totalNettoye += $manchettesOrphelines;
            }

            // 6. Suppression des ResultatFusion avec EC invalides
            $resultatsFusionInvalides = ResultatFusion::where('examen_id', $this->examen_id)
                ->whereNotNull('ec_id')
                ->where(function($query) use ($ecIdsValides) {
                    $query->whereNotIn('ec_id', $ecIdsValides)
                        ->orWhereDoesntHave('ec', function($q) {
                            $q->whereNull('deleted_at')->where('is_active', true);
                        });
                })
                ->delete();

            if ($resultatsFusionInvalides > 0) {
                $details[] = "$resultatsFusionInvalides résultat(s) fusion avec EC invalide";
                $totalNettoye += $resultatsFusionInvalides;
            }

            DB::commit();

            if ($totalNettoye > 0) {
                $message = "Nettoyage effectué : " . implode(', ', $details);
                toastr()->success($message);
                
                \Log::info('Nettoyage données incohérentes effectué', [
                    'examen_id' => $this->examen_id,
                    'session_id' => $this->sessionActive->id,
                    'total_nettoye' => $totalNettoye,
                    'details' => $details,
                    'user_id' => Auth::id()
                ]);
            } else {
                toastr()->info('Aucune donnée incohérente à nettoyer');
            }

            // Recharger la vérification
            $this->verifierCoherence();

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur nettoyage données incohérentes', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            toastr()->error('Erreur lors du nettoyage : ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }
}