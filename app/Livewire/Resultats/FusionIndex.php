<?php

namespace App\Livewire\Resultats;

use App\Models\EC;
use App\Models\Copie;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\CodeAnonymat;
use Livewire\WithPagination;
use App\Models\ResultatFinal;
use App\Models\ResultatFusion;
use App\Services\FusionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\CalculAcademiqueService;

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

    // Propriétés de suivi du processus de fusion
    public $statut = 'initial';        // 'initial', 'verification', 'fusion', 'valide', 'publie', 'annule'
    public $etapeProgress = 0;         // Pourcentage de progression (0-100)
    public $etapeFusion = 0;           // Étape de fusion (1-4)
    public $isProcessing = false;

    // États des boutons
    public $showVerificationButton = false;  // Bouton "Vérifier la cohérence"
    public $showFusionButton = false;        // Bouton "Commencer la fusion"
    public $showResetButton = false;         // Bouton "Réinitialiser"

    // Interface utilisateur
    public $activeTab = 'process';
    public $messageType = '';
    public $message = '';

    // Données de rapport et statistiques
    public $rapportCoherence = [];
    public $resultatsStats = [];
    public $resultatsParMatiere = [];
    public $distributionNotes = [];

    // Propriétés pour les confirmations
    public $confirmingVerification = false;    // Étape 1: Vérification cohérence
    public $confirmingFusion = false;          // Étape 2: Démarrer fusion
    public $confirmingVerify2 = false;         // Étape 2: Passer à VERIFY_2
    public $confirmingVerify3 = false;         // Étape 2: Passer à VERIFY_3
    public $confirmingValidation = false;      // Étape 3: Validation
    public $confirmingPublication = false;     // Étape 4: Publication
    public $confirmingAnnulation = false;      // Annulation
    public $confirmingRevenirValidation = false; // Réactivation
    public $confirmingResetFusion = false;     // Reset
    public $confirmingExport = false;          // Export
    public string $motifAnnulation = '';

    // Méthodes de confirmation
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
            ->orderBy('id', 'asc')
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
            $this->resetInterface();
            Log::warning('Aucun examen trouvé pour les paramètres donnés', [
                'niveau_id' => $this->niveau_id,
                'parcours_id' => $this->parcours_id,
                'session_id' => $this->sessionActive->id,
            ]);
            toastr()->error('Aucun examen trouvé. Veuillez vérifier votre sélection de niveau et de parcours.');
        }
    }

    /**
     * Vérifie l'état actuel du processus de fusion
     */
    public function verifierEtatActuel()
    {
        if (!$this->examen_id) {
            $this->statut = 'initial';
            $this->etapeProgress = 0;
            $this->etapeFusion = 0;
            $this->resetInterface();
            return;
        }

        try {
            // Collecte d'informations
            $resultatFinalPublie = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->exists();

            $resultatFinalEnAttente = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->exists();

            $resultatFinalAnnule = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFinal::STATUT_ANNULE)
                ->exists();

            $resultatsFusion = ResultatFusion::where('examen_id', $this->examen_id)->get();
            $maxEtapeFusion = $resultatsFusion->max('etape_fusion') ?? 0;
            $statutsFusion = $resultatsFusion->pluck('statut')->unique();
            $coherenceVerifiee = !empty($this->rapportCoherence);

            // Priorité aux résultats annulés
            if ($resultatFinalAnnule && !$resultatFinalPublie && !$resultatFinalEnAttente) {
                $this->statut = 'annule';
                $this->etapeProgress = 100;
                $this->etapeFusion = 4;
                $this->resetInterface();
                return;
            }

            // Logique d'état normale
            if ($resultatFinalPublie && !$resultatFinalAnnule) {
                $this->statut = 'publie';
                $this->etapeProgress = 100;
                $this->etapeFusion = 4;
                $this->resetInterface();
            } elseif ($resultatFinalEnAttente && !$resultatFinalAnnule) {
                $this->statut = 'valide';
                $this->etapeProgress = 85;
                $this->etapeFusion = 4;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VALIDE)) {
                $this->statut = 'valide';
                $this->etapeProgress = 85;
                $this->etapeFusion = 4;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VERIFY_3)) {
                $this->statut = 'fusion';
                $this->etapeProgress = 60;
                $this->etapeFusion = 3;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VERIFY_2)) {
                $this->statut = 'fusion';
                $this->etapeProgress = 45;
                $this->etapeFusion = 2;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VERIFY_1)) {
                $this->statut = 'fusion';
                $this->etapeProgress = 30;
                $this->etapeFusion = 1;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
            } elseif ($coherenceVerifiee) {
                $this->statut = 'verification';
                $this->etapeProgress = 15;
                $this->etapeFusion = 0;
                $this->showVerificationButton = false;
                $this->showFusionButton = true;
                $this->showResetButton = true;
            } else {
                $this->statut = 'initial';
                $this->etapeProgress = 0;
                $this->etapeFusion = 0;
                $this->showVerificationButton = true;
                $this->showFusionButton = false;
                $this->showResetButton = false;
            }

            // Charger les statistiques
            if ($resultatsFusion->isNotEmpty() || $resultatFinalEnAttente || $resultatFinalPublie || $resultatFinalAnnule) {
                $this->chargerStatistiquesSimples();
            }

            Log::info('État actuel vérifié', [
                'examen_id' => $this->examen_id,
                'statut_final' => $this->statut,
                'etape_fusion' => $this->etapeFusion,
                'coherence_verifiee' => $coherenceVerifiee,
                'resultat_final_annule' => $resultatFinalAnnule,
                'resultat_final_publie' => $resultatFinalPublie,
                'resultat_final_en_attente' => $resultatFinalEnAttente,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de l\'état', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
            ]);
            $this->statut = 'initial';
            $this->etapeProgress = 0;
            $this->etapeFusion = 0;
            $this->showVerificationButton = true;
            $this->showFusionButton = false;
            $this->showResetButton = false;
            toastr()->error('Erreur lors de la vérification de l\'état: ' . $e->getMessage());
        }
    }

    /**
     * Remet l'interface à zéro
     */
    private function resetInterface()
    {
        $this->showVerificationButton = false;
        $this->showFusionButton = false;
        $this->showResetButton = false;
    }

    /**
     * ÉTAPE 1 : Vérifie la cohérence des données
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

                    // Passer à l'étape suivante
                    $this->statut = 'verification';
                    $this->etapeProgress = 15;
                    $this->showVerificationButton = false;
                    $this->showFusionButton = true;
                } else {
                    toastr()->warning('Aucune matière trouvée pour cet examen. Vérifiez les données des copies et manchettes.');
                }

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
     * ÉTAPE 2 : Lance le processus de fusion
     */
    public function lancerFusion()
    {
        $this->confirmingFusion = false;

        try {
            if ($this->statut !== 'verification' || !$this->showFusionButton) {
                toastr()->error('Impossible de commencer la fusion dans l\'état actuel.');
                return;
            }

            $fusionService = new FusionService();
            $result = $fusionService->fusionner($this->examen_id);

            if (!$result['success']) {
                toastr()->error($result['message']);
                return;
            }

            // Mise à jour de l'état après fusion réussie
            $this->statut = 'fusion';
            $this->etapeFusion = 1;
            $this->etapeProgress = 30;
            $this->showFusionButton = false;
            $this->showResetButton = true;

            toastr()->success('Fusion démarrée avec succès.');
            $this->verifierEtatActuel();

            Log::info('Fusion démarrée', [
                'examen_id' => $this->examen_id,
                'statut' => $this->statut,
                'etape_fusion' => $this->etapeFusion,
                'user_id' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du démarrage de la fusion', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            toastr()->error('Erreur lors du démarrage de la fusion : ' . $e->getMessage());
        }
    }

    /**
     * ÉTAPE 2 : Passe à la seconde vérification (VERIFY_2)
     */
    public function passerAVerify2()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->confirmingVerify2 = false;

        try {
            $resultats_fusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFusion::STATUT_VERIFY_1)
                ->get();

            if ($resultats_fusion->isEmpty()) {
                toastr()->error('Aucun résultat de fusion à l\'étape VERIFY_1 trouvé.');
                return;
            }

            $userId = Auth::id();
            $nbUpdated = 0;

            foreach ($resultats_fusion as $fusion) {
                $fusion->changerStatut(ResultatFusion::STATUT_VERIFY_2, $userId);
                $nbUpdated++;
            }

            // Mise à jour de l'état
            $this->etapeFusion = 2;
            $this->etapeProgress = 50;

            toastr()->success("$nbUpdated résultats passés à l'étape de seconde vérification avec succès.");
            $this->verifierEtatActuel();

        } catch (\Exception $e) {
            Log::error('Erreur dans passerAVerify2', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors du passage à VERIFY_2: ' . $e->getMessage());
        }
    }

    /**
     * ÉTAPE 2 : Troisième fusion (VERIFY_3)
     */
    public function passerAVerify3()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->confirmingVerify3 = false;

        try {
            $resultats_fusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFusion::STATUT_VERIFY_2)
                ->get();

            if ($resultats_fusion->isEmpty()) {
                toastr()->error('Aucun résultat de fusion à l\'étape VERIFY_2 trouvé.');
                return;
            }

            $userId = Auth::id();
            $nbUpdated = 0;

            foreach ($resultats_fusion as $fusion) {
                $fusion->changerStatut(ResultatFusion::STATUT_VERIFY_3, $userId);
                $nbUpdated++;
            }

            // Mise à jour de l'état
            $this->etapeFusion = 3;
            $this->etapeProgress = 60;

            toastr()->success("$nbUpdated résultats passés à la troisième vérification (VERIFY_3) avec succès.");
            $this->verifierEtatActuel();

        } catch (\Exception $e) {
            Log::error('Erreur dans passerAVerify3', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors du passage à VERIFY_3: ' . $e->getMessage());
        }
    }

    /**
     * ÉTAPE 3 : Valide les résultats après les 3 fusions
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
            $resultats_fusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFusion::STATUT_VERIFY_3)
                ->get();

            if ($resultats_fusion->isEmpty()) {
                toastr()->error('Aucun résultat de la 3ème fusion (VERIFY_3) trouvé.');
                $this->isProcessing = false;
                return;
            }

            $userId = Auth::id();
            $nbValidated = 0;

            foreach ($resultats_fusion as $fusion) {
                $fusion->changerStatut(ResultatFusion::STATUT_VALIDE, $userId);
                $nbValidated++;
            }

            // Mise à jour de l'état
            $this->statut = 'valide';
            $this->etapeFusion = 4;
            $this->etapeProgress = 75;

            toastr()->success("$nbValidated résultats validés avec succès après les 3 fusions.");
            $this->verifierEtatActuel();

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
     * ÉTAPE 4 : Publie les résultats
     */
    public function publierResultats()
    {
        if (!Auth::user()->hasPermissionTo('resultats.validation')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de publier les résultats');
            return;
        }

        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingPublication = false;

        try {
            // Vérifier d'abord s'il y a des ResultatFinal en attente (cas de réactivation)
            $resultatsFinauxEnAttente = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->get();

            if ($resultatsFinauxEnAttente->isNotEmpty()) {
                // Cas de réactivation : publier directement les ResultatFinal existants
                Log::info('Publication après réactivation', [
                    'examen_id' => $this->examen_id,
                    'resultats_en_attente' => $resultatsFinauxEnAttente->count()
                ]);

                // Créer une instance de CalculAcademiqueService
                $calculService = new CalculAcademiqueService();
                $etudiantsTraites = [];

                // Recalculer les décisions et publier
                foreach ($resultatsFinauxEnAttente->groupBy('etudiant_id') as $etudiantId => $resultatsEtudiant) {
                    $etudiantsTraites[$etudiantId] = true;

                    // Calculer les résultats complets pour l'étudiant
                    $resultatsEtudiant = $calculService->calculerResultatsComplets($etudiantId, $this->sessionActive->id, true);

                    // Déterminer la décision en fonction de la moyenne
                    $moyenneUE = $resultatsEtudiant['synthese']['moyenne_generale'];
                    $decision = $moyenneUE >= 10 ? 'admis' : 'rattrapage';

                    // Mettre à jour tous les résultats de l'étudiant
                    ResultatFinal::where('etudiant_id', $etudiantId)
                        ->where('examen_id', $this->examen_id)
                        ->update([
                            'decision' => $decision,
                            'statut' => 'publie',
                            'date_publication' => now()
                        ]);

                    Log::info("Décision recalculée après réactivation", [
                        'etudiant_id' => $etudiantId,
                        'moyenne_ue' => $moyenneUE,
                        'decision' => $decision
                    ]);
                }

                // Mise à jour de l'état
                $this->statut = 'publie';
                $this->etapeProgress = 100;

                toastr()->success("Publication après réactivation réussie. " . count($etudiantsTraites) . " étudiants traités.");
                $this->verifierEtatActuel();
                $this->isProcessing = false;
                return;
            }

            // Cas normal : Publication depuis ResultatFusion
            $query = ResultatFusion::where('examen_id', $this->examen_id);

            // Selon l'étape de fusion, chercher le bon statut
            if ($this->etapeFusion == 4) {
                $query->where('statut', ResultatFusion::STATUT_VALIDE);
            } elseif ($this->etapeFusion == 3) {
                $query->where('statut', ResultatFusion::STATUT_VERIFY_3);
            } else {
                toastr()->error('Les résultats ne sont pas prêts pour la publication. Étape actuelle: ' . $this->etapeFusion);
                $this->isProcessing = false;
                return;
            }

            $resultatIds = $query->pluck('id')->toArray();

            if (empty($resultatIds)) {
                $statutRecherche = $this->etapeFusion == 4 ? 'VALIDE' : 'VERIFY_3';
                toastr()->error("Aucun résultat trouvé avec le statut $statutRecherche à publier.");
                $this->isProcessing = false;
                return;
            }

            // Utiliser la méthode transfererResultats (sans délibération)
            $fusionService = new FusionService();
            $result = $fusionService->transfererResultats($resultatIds, Auth::id(), false);

            if ($result['success']) {
                // Mise à jour de l'état
                $this->statut = 'publie';
                $this->etapeProgress = 100;

                toastr()->success($result['message']);
                $this->verifierEtatActuel();
            } else {
                toastr()->error($result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la publication', [
                'examen_id' => $this->examen_id,
                'etape_fusion' => $this->etapeFusion,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de la publication: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * Réinitialise le processus de fusion
     */
    public function resetFusion()
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
        $this->confirmingResetFusion = false;

        try {
            // Supprimer tous les résultats
            ResultatFusion::where('examen_id', $this->examen_id)->delete();
            ResultatFinal::where('examen_id', $this->examen_id)->delete();

            // Réinitialiser l'état
            $this->statut = 'verification';
            $this->etapeFusion = 0;
            $this->etapeProgress = 15;
            $this->showResetButton = false;

            // Recharger le rapport de cohérence
            $this->chargerRapportCoherence();

            // Si rapport non vide, activer le bouton de fusion
            $this->showFusionButton = !empty($this->rapportCoherence);
            $this->showVerificationButton = false;

            toastr()->success('Fusion réinitialisée avec succès.');
            $this->switchTab('rapport-stats');

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
     * Annule les résultats publiés
     */
    public function annulerResultats()
    {
        if (!Auth::user()->hasPermissionTo('resultats.cancel')) {
            toastr()->error('Vous n\'avez pas l\'autorisation d\'annuler les résultats');
            $this->confirmingAnnulation = false;
            return;
        }

        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
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
            $fusionService = new FusionService();
            $result = $fusionService->annulerResultats($this->examen_id, $this->motifAnnulation);

            if ($result['success']) {
                // Mettre à jour l'examen
                if ($this->examen) {
                    $this->examen->update([
                        'statut_resultats' => 'annule',
                        'date_annulation_resultats' => now(),
                    ]);
                }

                $this->verifierEtatActuel();
                toastr()->success($result['message']);
            } else {
                toastr()->error($result['message']);
            }

            $this->motifAnnulation = '';
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation des résultats', [
                'examen_id' => $this->examen_id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            toastr()->error('Erreur lors de l\'annulation : ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * Réactive des résultats annulés vers l'étape de validation
     */
    public function revenirValidation()
    {
        if (!Auth::user()->hasPermissionTo('resultats.reactiver')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de réactiver les résultats');
            $this->confirmingRevenirValidation = false;
            return;
        }

        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
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
            $fusionService = new FusionService();
            $result = $fusionService->revenirValidation($this->examen_id);

            if ($result['success']) {
                // Mettre à jour l'examen
                if ($this->examen) {
                    $this->examen->update([
                        'statut_resultats' => 'en_attente_publication',
                        'date_annulation_resultats' => null,
                    ]);
                }

                $this->verifierEtatActuel();
                toastr()->success($result['message']);
            } else {
                toastr()->error($result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réactivation des résultats', [
                'examen_id' => $this->examen_id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            toastr()->error('Erreur lors de la réactivation : ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    // Méthodes de gestion des filtres et navigation
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

    public function updatedParcoursId()
    {
        $this->examen_id = null;
        $this->examen = null;
        $this->resetResults();

        if ($this->niveau_id && $this->parcours_id && $this->sessionActive) {
            $this->loadExamen();
        }
    }

    protected function resetResults()
    {
        $this->rapportCoherence = [];
        $this->resultatsParMatiere = [];
        $this->distributionNotes = [];
        $this->statut = 'initial';
        $this->etapeProgress = 0;
        $this->etapeFusion = 0;
        $this->resetInterface();
    }

    // Méthodes de chargement des données
    protected function chargerStatistiquesSimples()
    {
        if (!$this->examen_id) {
            return;
        }

        try {
            $resultatsFusionExistants = ResultatFusion::where('examen_id', $this->examen_id)
                ->whereIn('statut', [
                    ResultatFusion::STATUT_VERIFY_1,
                    ResultatFusion::STATUT_VERIFY_2,
                    ResultatFusion::STATUT_VERIFY_3,
                    ResultatFusion::STATUT_VALIDE
                ])
                ->exists();

            $resultatsFinauxExistants = ResultatFinal::where('examen_id', $this->examen_id)
                ->whereIn('statut', [ResultatFinal::STATUT_EN_ATTENTE, ResultatFinal::STATUT_PUBLIE, ResultatFinal::STATUT_ANNULE])
                ->exists();

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
            Log::error('Erreur lors du chargement des statistiques', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
            ]);
            $this->resultatsStats = [
                'totalMatieres' => 0,
                'etudiants' => 0,
            ];
        }
    }

    protected function chargerRapportCoherence()
    {
        if (!$this->examen_id) {
            $this->rapportCoherence = [];
            return;
        }

        try {
            $fusionService = new FusionService();
            $result = $fusionService->verifierCoherence($this->examen_id);

            if ($result['success']) {
                $this->rapportCoherence = $result['data'] ?? [];
            } else {
                $this->rapportCoherence = [];
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du rapport de cohérence', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage(),
            ]);
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

    /**
     * Rendu du composant avec toutes les variables nécessaires
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

            // Toutes les confirmations
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

            // Données
            'rapportCoherence' => $this->rapportCoherence,
            'resultatsStats' => $this->resultatsStats,

            // États des boutons
            'showVerificationButton' => $this->showVerificationButton,
            'showResetButton' => $this->showResetButton,
            'showFusionButton' => $this->showFusionButton,
        ]);
    }
}