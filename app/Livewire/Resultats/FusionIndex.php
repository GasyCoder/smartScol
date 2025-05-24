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
use App\Models\Deliberation;
use Livewire\WithPagination;
use App\Models\ResultatFinal;
use App\Models\ResultatFusion;
use App\Services\FusionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Composant de gestion du processus de fusion des résultats d'examen
 * 
 * NOUVELLE LOGIQUE DES ÉTAPES :
 * Étape 1: Vérification de cohérence (statut = 'verification')
 * Étape 2: Fusion des données (statut = 'fusion', etapeFusion = 1,2)
 * Étape 3: Vérification et Validation (statut = 'fusion', etapeFusion = 3)
 * Étape 4: Publication/Transfert (statut = 'valide' puis 'publie')
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

    // Propriétés de suivi du processus de fusion - NOUVELLE LOGIQUE
    public $statut = 'initial';        // 'initial', 'verification', 'fusion', 'valide', 'publie', 'annule'
    public $etapeProgress = 0;         // Pourcentage de progression (0-100)
    public $etapeFusion = 0;           // Étape de fusion (1-4)
    public $isProcessing = false;

    // États des boutons - SIMPLIFIÉS
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

    // Propriétés pour les confirmations - TOUTES LES MODALES
    public $confirmingVerification = false;    // Étape 1: Vérification cohérence
    public $confirmingFusion = false;          // Étape 2: Démarrer fusion
    public $confirmingVerify2 = false;         // Étape 2: Passer à VERIFY_2
    public $confirmingVerify3 = false;         // Étape 2: Passer à VERIFY_3 (maintenant étape 3)
    public $confirmingValidation = false;      // Étape 3: Validation
    public $confirmingPublication = false;     // Étape 4: Publication
    public $confirmingAnnulation = false;      // Annulation
    public $confirmingRevenirValidation = false; // Réactivation
    public $confirmingResetFusion = false;     // Reset
    public $confirmingExport = false; 
    public string $motifAnnulation = '';         // Export

    // Données pour la délibération
    public $showDeliberationInfo = false;
    public $deliberationData = null;
    public $requiresDeliberation = false;

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
     * VERSION CORRIGÉE : Méthode qui vérifie l'état sans créer automatiquement de rapport
     * Cette version sépare clairement la vérification d'existence de la création de rapport
     */
    public function verifierEtatActuel()
    {
        // Vérification préliminaire
        if (!$this->examen_id) {
            $this->statut = 'initial';
            $this->etapeProgress = 0;
            $this->etapeFusion = 0;
            $this->resetInterface();
            return;
        }

        try {
            // === PHASE 1 : Collecte d'informations sans effets de bord ===
            
            // Vérifier les résultats finaux
            $resultatFinalPublie = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->exists();

            $resultatFinalEnAttente = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->exists();

            $resultatFinalAnnule = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFinal::STATUT_ANNULE)
                ->exists();

            // Vérifier les résultats de fusion
            $resultatsFusion = ResultatFusion::where('examen_id', $this->examen_id)->get();
            $maxEtapeFusion = $resultatsFusion->max('etape_fusion') ?? 0;
            $statutsFusion = $resultatsFusion->pluck('statut')->unique();
            $coherenceVerifiee = !empty($this->rapportCoherence);
            if (!$coherenceVerifiee) {
                // Pour l'instant, nous laissons false si pas en mémoire
                $coherenceVerifiee = false;
            }
            // === PHASE 2 : Détection de scénarios spéciaux ===
            $repriseApresAnnulation = $resultatFinalAnnule && 
                                    !$resultatFinalPublie && 
                                    !$resultatFinalEnAttente;

            $fusionAbandonnee = $resultatsFusion->isNotEmpty() && 
                            !$statutsFusion->contains(ResultatFusion::STATUT_VALIDE) &&
                            !$resultatFinalEnAttente &&
                            !$resultatFinalPublie;

            // === PHASE 3 : Logique de détermination d'état corrigée ===
            if ($resultatFinalPublie && !$resultatFinalAnnule) {
                // CAS FINAL : Résultats officiellement publiés
                $this->statut = 'publie';
                $this->etapeProgress = 100;
                $this->etapeFusion = 4;
                $this->resetInterface();
                
            } elseif ($resultatFinalEnAttente && !$resultatFinalAnnule) {
                // CAS QUASI-FINAL : Résultats validés en attente de publication
                $this->statut = 'valide';
                $this->etapeProgress = 85;
                $this->etapeFusion = 4;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
                
            } elseif ($resultatFinalAnnule && ($resultatFinalPublie || $resultatFinalEnAttente)) {
                // CAS SPÉCIAL : Annulation avec autres versions
                $this->statut = 'annule';
                $this->etapeProgress = 100;
                $this->etapeFusion = 4;
                $this->resetInterface();
                
            } elseif ($repriseApresAnnulation && $coherenceVerifiee && $resultatsFusion->isEmpty()) {
                // CAS DE REPRISE : Résultats annulés, cohérence déjà vérifiée
                $this->statut = 'verification';
                $this->etapeProgress = 15;
                $this->etapeFusion = 0;
                $this->showVerificationButton = false;
                $this->showFusionButton = true;
                $this->showResetButton = true;
                
            } elseif ($repriseApresAnnulation && !$coherenceVerifiee) {
                // CAS DE REPRISE COMPLÈTE : Résultats annulés, cohérence pas faite
                $this->statut = 'initial';
                $this->etapeProgress = 0;
                $this->etapeFusion = 0;
                $this->showVerificationButton = true;  // ✅ Doit vérifier cohérence
                $this->showFusionButton = false;
                $this->showResetButton = false;
                
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VALIDE)) {
                // ÉTAPE 4 : Fusions validées, prêt pour publication
                $this->statut = 'valide';
                $this->etapeProgress = 85;
                $this->etapeFusion = 4;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
                
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VERIFY_3)) {
                // ÉTAPE 3 : Troisième fusion en cours
                $this->statut = 'fusion';
                $this->etapeProgress = 60;
                $this->etapeFusion = 3;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
                
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VERIFY_2)) {
                // ÉTAPE 2 : Seconde fusion en cours
                $this->statut = 'fusion';
                $this->etapeProgress = 45;
                $this->etapeFusion = 2;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
                
            } elseif ($statutsFusion->contains(ResultatFusion::STATUT_VERIFY_1)) {
                // ÉTAPE 1 : Première fusion en cours
                $this->statut = 'fusion';
                $this->etapeProgress = 30;
                $this->etapeFusion = 1;
                $this->showResetButton = true;
                $this->showVerificationButton = false;
                $this->showFusionButton = false;
                
            } elseif ($coherenceVerifiee && !$fusionAbandonnee) {
                // FLUX NORMAL : Cohérence déjà vérifiée → Prêt pour fusion
                $this->statut = 'verification';
                $this->etapeProgress = 15;
                $this->etapeFusion = 0;
                $this->showVerificationButton = false;  // Cohérence déjà faite
                $this->showFusionButton = true;         // Peut commencer fusion
                $this->showResetButton = true;
                
            } else {
                // 🎯 ÉTAT INITIAL : Ce qui devrait s'afficher pour un nouvel examen
                $this->statut = 'initial';
                $this->etapeProgress = 0;
                $this->etapeFusion = 0;
                $this->showVerificationButton = true;   // ✅ Afficher "Vérifier cohérence"
                $this->showFusionButton = false;        // Masquer "Commencer fusion"
                $this->showResetButton = false;
            }

            // Charger les statistiques si des résultats existent
            if ($resultatsFusion->isNotEmpty() || $resultatFinalEnAttente || $resultatFinalPublie || $resultatFinalAnnule) {
                $this->chargerStatistiquesSimples();
            }

            // Log de vérification final
            Log::info('État actuel vérifié', [
                'examen_id' => $this->examen_id,
                'statut_final' => $this->statut,
                'etape_fusion' => $this->etapeFusion,
                'coherence_verifiee' => $coherenceVerifiee,
                'interface_boutons' => [
                    'showVerificationButton' => $this->showVerificationButton,
                    'showFusionButton' => $this->showFusionButton,
                    'showResetButton' => $this->showResetButton
                ],
                'correction_appliquee' => 'Suppression de l\'effet de bord dans chargerRapportCoherence'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de l\'état corrigée', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage()
            ]);
            
            // En cas d'erreur, retour à l'état initial sûr
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
     * NOUVELLE MÉTHODE : Remet l'interface à zéro
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

            Log::info('Fusion démarrée - NOUVELLE LOGIQUE', [
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
     * ÉTAPE 2 : Troisième fusion (VERIFY_3) - LA DERNIÈRE DES 3 FUSIONS
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

            // Mise à jour de l'état - TROISIÈME FUSION (toujours étape 2)
            $this->etapeFusion = 3;
            $this->etapeProgress = 60; // 3ème fusion terminée
            
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
            // Récupérer les résultats de la 3ème fusion (VERIFY_3)
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

            // Mise à jour de l'état - ÉTAPE 3 (Validation terminée)
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
            // Récupérer tous les IDs des résultats validés pour cet examen
            $resultatIds = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFusion::STATUT_VALIDE)
                ->pluck('id')
                ->toArray();

            if (empty($resultatIds)) {
                toastr()->error('Aucun résultat validé trouvé à publier.');
                $this->isProcessing = false;
                return;
            }

            // Déterminer si une délibération est nécessaire
            $session = $this->examen->session;
            $niveau = $this->examen->niveau;
            $requiresDeliberation = $session && $session->isRattrapage() && $niveau && !$niveau->is_concours;

            // Utiliser la méthode existante transfererResultats
            $fusionService = new FusionService();
            $result = $fusionService->transfererResultats(
                $resultatIds, 
                Auth::id(), 
                $requiresDeliberation
            );

            if ($result['success']) {
                // Mise à jour de l'état - PUBLICATION TERMINÉE
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
            Deliberation::where('examen_id', $this->examen_id)
                ->where('statut', '!=', 'validee')
                ->delete();

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

    // Autres méthodes existantes (gestion des filtres, onglets, etc.)
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

    // Méthodes de chargement des données (inchangées)
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
                    ResultatFusion::STATUT_VERIFY_3,  // 3ème fusion au lieu de VERIFICATION
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

            if ($resultatsFinauxExistants) {
                $admis = ResultatFinal::where('examen_id', $this->examen_id)
                    ->where('note', '>=', 10)
                    ->distinct('etudiant_id')
                    ->count('etudiant_id');
            } elseif ($resultatsFusionExistants) {
                $admis = ResultatFusion::where('examen_id', $this->examen_id)
                    ->where('note', '>=', 10)
                    ->distinct('etudiant_id')
                    ->count('etudiant_id');
            }

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
     * Obtient le contexte de l'examen pour la délibération
     */
    public function getContexteExamenProperty()
    {
        if (!$this->examen) {
            return null;
        }

        try {
            $niveau = $this->examen->niveau;
            $session = $this->examen->session;

            $requiresDeliberation = $session && $session->isRattrapage() && $niveau && !$niveau->is_concours;

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
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du contexte examen', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Annule les résultats publiés
     * Transition : 'publie' → 'annule'
     */
    public function annulerResultats()
    {
        // Vérifications de sécurité et d'autorisation
        if (!Auth::user()->hasPermissionTo('resultats.annuler')) {
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
            // Démarrer une transaction pour garantir la cohérence
            \DB::beginTransaction();

            // Mettre à jour le statut des résultats finaux vers 'annule'
            $nbResultatsAnnules = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->update([
                    'statut' => ResultatFinal::STATUT_ANNULE,
                    'motif_annulation' => $this->motifAnnulation ?: null,
                    'date_annulation' => now(),
                    'annule_par' => Auth::id(),
                    'updated_at' => now()
                ]);

            if ($nbResultatsAnnules === 0) {
                throw new \Exception('Aucun résultat publié trouvé à annuler.');
            }

            // Mettre à jour l'examen si vous avez un champ pour tracker le statut global
            if ($this->examen) {
                $this->examen->update([
                    'statut_resultats' => 'annule',
                    'date_annulation_resultats' => now()
                ]);
            }

            // Enregistrer l'action dans les logs pour audit
            Log::info('Annulation des résultats', [
                'examen_id' => $this->examen_id,
                'user_id' => Auth::id(),
                'motif' => $this->motifAnnulation,
                'nb_resultats_annules' => $nbResultatsAnnules,
                'date_annulation' => now()->toISOString()
            ]);

            \DB::commit();

            // Réinitialiser les propriétés de la modal
            $this->motifAnnulation = '';
            
            // Vérifier l'état actuel pour mettre à jour l'interface
            $this->verifierEtatActuel();
            
            toastr()->success("$nbResultatsAnnules résultats annulés avec succès. Ils peuvent être réactivés si nécessaire.");

            Log::info('Résultats annulés avec succès - NOUVELLE LOGIQUE', [
                'examen_id' => $this->examen_id,
                'nouveau_statut' => $this->statut,
                'nb_resultats' => $nbResultatsAnnules,
                'user_id' => Auth::id(),
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            Log::error('Erreur lors de l\'annulation des résultats', [
                'examen_id' => $this->examen_id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            toastr()->error('Erreur lors de l\'annulation : ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }

    /**
     * Réactive des résultats annulés vers l'étape de validation
     * Transition : 'annule' → 'valide' (étape 3 - prêt pour nouvelle publication)
     */
    public function revenirValidation()
    {
        // Vérifications de sécurité et d'autorisation
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
            \DB::beginTransaction();

            // Remettre les résultats à l'état 'en_attente' pour permettre une nouvelle publication
            $nbResultatsReactives = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('statut', ResultatFinal::STATUT_ANNULE)
                ->update([
                    'statut' => ResultatFinal::STATUT_EN_ATTENTE,
                    'motif_annulation' => null,
                    'date_annulation' => null,
                    'annule_par' => null,
                    'date_reactivation' => now(),
                    'reactive_par' => Auth::id(),
                    'updated_at' => now()
                ]);

            if ($nbResultatsReactives === 0) {
                throw new \Exception('Aucun résultat annulé trouvé à réactiver.');
            }

            // Mettre à jour l'examen pour refléter la réactivation
            if ($this->examen) {
                $this->examen->update([
                    'statut_resultats' => 'en_attente_publication',
                    'date_annulation_resultats' => null
                ]);
            }

            // Enregistrer l'action dans les logs pour audit
            Log::info('Réactivation des résultats', [
                'examen_id' => $this->examen_id,
                'user_id' => Auth::id(),
                'nb_resultats_reactives' => $nbResultatsReactives,
                'date_reactivation' => now()->toISOString()
            ]);

            \DB::commit();

            // Vérifier l'état actuel pour mettre à jour l'interface
            $this->verifierEtatActuel();
            
            toastr()->success("$nbResultatsReactives résultats réactivés avec succès. Ils sont maintenant prêts pour une nouvelle publication.");

            Log::info('Résultats réactivés avec succès - NOUVELLE LOGIQUE', [
                'examen_id' => $this->examen_id,
                'nouveau_statut' => $this->statut,
                'nb_resultats' => $nbResultatsReactives,
                'user_id' => Auth::id(),
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            Log::error('Erreur lors de la réactivation des résultats', [
                'examen_id' => $this->examen_id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            toastr()->error('Erreur lors de la réactivation : ' . $e->getMessage());
        }

        $this->isProcessing = false;
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