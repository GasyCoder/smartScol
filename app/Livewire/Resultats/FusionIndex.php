<?php

namespace App\Livewire\Resultats;

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
            \Log::error('Aucune session active trouvée lors du montage du composant FusionIndex');
            toastr()->error('Aucune session active trouvée. Veuillez configurer une session active dans les paramètres.');
            return;
        }

        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        if ($this->niveau_id) {
            $this->loadParcours();
            if ($this->parcours_id) {
                $this->loadExamen();
            }
        }

        $this->verifierEtatActuel();

        \Log::info('Composant FusionIndex initialisé', [
            'session_active_id' => $this->sessionActive->id,
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
        ]);
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
        // CORRECTION : Chercher les examens qui ont des données dans la session active OU dans d'autres sessions
        $this->examen = Examen::where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->whereNull('deleted_at')
            ->where(function($query) {
                // Examens avec manchettes dans la session active
                $query->whereHas('manchettes', function($subQuery) {
                    $subQuery->where('session_exam_id', $this->sessionActive->id);
                })
                // OU examens avec copies dans la session active
                ->orWhereHas('copies', function($subQuery) {
                    $subQuery->where('session_exam_id', $this->sessionActive->id);
                })
                // OU examens avec des données dans d'autres sessions (pour permettre la continuité)
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

            // Log avec détails des données trouvées
            $manchettesSession = $this->examen->manchettes()->where('session_exam_id', $this->sessionActive->id)->count();
            $copiesSession = $this->examen->copies()->where('session_exam_id', $this->sessionActive->id)->count();

            \Log::info('Examen chargé avec succès', [
                'examen_id' => $this->examen_id,
                'niveau_id' => $this->niveau_id,
                'parcours_id' => $this->parcours_id,
                'session_active_id' => $this->sessionActive->id,
                'session_type' => $this->sessionActive->type,
                'manchettes_session_active' => $manchettesSession,
                'copies_session_active' => $copiesSession,
            ]);
        } else {
            $this->examen_id = null;
            $this->examen = null;
            $this->resetInterface();

            // Message informatif selon le contexte
            $examensDisponibles = Examen::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->whereNull('deleted_at')
                ->withCount(['manchettes', 'copies'])
                ->get();

            if ($examensDisponibles->isNotEmpty()) {
                $examensAvecDonnees = $examensDisponibles->filter(function($ex) {
                    return $ex->manchettes_count > 0 || $ex->copies_count > 0;
                });

                if ($examensAvecDonnees->isNotEmpty()) {
                    $details = $examensAvecDonnees->map(function($ex) {
                        return "Examen {$ex->id} ({$ex->manchettes_count} manchettes, {$ex->copies_count} copies)";
                    })->implode(', ');

                    toastr()->info("Examens avec données trouvés : $details. Sélectionnez ou créez des données pour la session {$this->sessionActive->type}.");
                } else {
                    toastr()->error('Aucun examen trouvé avec des données. Créez d\'abord un examen avec des manchettes et copies.');
                }
            } else {
                toastr()->error('Aucun examen trouvé pour ce niveau et parcours.');
            }
        }
    }

    /**
     * Vérifie l'état actuel du processus de fusion
     */
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
            // CORRECTION PRINCIPALE : Filtrer par session active
            $sessionId = $this->sessionActive->id;

            // Collecte d'informations POUR LA SESSION ACTIVE UNIQUEMENT
            $resultatFinalPublie = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId) // AJOUTÉ
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->exists();

            $resultatFinalEnAttente = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId) // AJOUTÉ
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->exists();

            $resultatFinalAnnule = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId) // AJOUTÉ
                ->where('statut', ResultatFinal::STATUT_ANNULE)
                ->exists();

            $resultatsFusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId) // AJOUTÉ
                ->get();

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

            // Charger les statistiques pour la session active
            if ($resultatsFusion->isNotEmpty() || $resultatFinalEnAttente || $resultatFinalPublie || $resultatFinalAnnule) {
                $this->chargerStatistiquesSimples();
            }

            Log::info('État actuel vérifié avec session', [
                'examen_id' => $this->examen_id,
                'session_id' => $sessionId,
                'session_type' => $this->sessionActive->type,
                'statut_final' => $this->statut,
                'etape_fusion' => $this->etapeFusion,
                'coherence_verifiee' => $coherenceVerifiee,
                'resultat_final_annule' => $resultatFinalAnnule,
                'resultat_final_publie' => $resultatFinalPublie,
                'resultat_final_en_attente' => $resultatFinalEnAttente,
                'nb_resultats_fusion' => $resultatsFusion->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de l\'état', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive ? $this->sessionActive->id : null,
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

                \Log::info('Résultat vérification cohérence', [
                    'examen_id' => $this->examen_id,
                    'session_type' => $this->sessionActive->type,
                    'total' => $total,
                    'complets' => $complets,
                    'rapport_count' => count($this->rapportCoherence)
                ]);

                if ($total > 0) {
                    $completionRate = round($complets > 0 ? ($complets / $total) * 100 : 0);

                    // Messages adaptés selon le type de session et les résultats
                    if ($this->sessionActive->type === 'Rattrapage') {
                        if ($complets === 0) {
                            // Compter les ECs avec des données partielles
                            $ecsAvecDonnees = collect($this->rapportCoherence)->filter(function($item) {
                                return $item['copies_count'] > 0 || $item['manchettes_count'] > 0;
                            })->count();

                            if ($ecsAvecDonnees > 0) {
                                toastr()->info("Session de rattrapage : $total matière(s) disponible(s), $ecsAvecDonnees avec des données. Prêt pour finalisation de la saisie.");
                            } else {
                                toastr()->info("Session de rattrapage : $total matière(s) disponible(s). Aucune donnée saisie - Commencez la saisie des manchettes et copies.");
                            }
                        } else {
                            toastr()->success("Session de rattrapage : $complets/$total matières complètes ($completionRate%)");
                        }
                    } else {
                        toastr()->success("Vérification terminée : $complets/$total matières complètes ($completionRate%)");
                    }

                    // Toujours permettre de continuer si on a au moins une matière détectée
                    $this->statut = 'verification';
                    $this->etapeProgress = 15;
                    $this->showVerificationButton = false;

                    // Activer la fusion seulement si on a des données complètes
                    $this->showFusionButton = $complets > 0;

                    $this->switchTab('rapport-stats');
                } else {
                    // Aucune matière détectée
                    if ($this->sessionActive->type === 'Rattrapage') {
                        toastr()->info('Session de rattrapage initialisée. Créez d\'abord les manchettes et copies pour les étudiants éligibles.');
                    } else {
                        toastr()->warning('Aucune matière trouvée pour cet examen. Vérifiez les données des copies et manchettes.');
                    }

                    // Rester en état initial si aucune donnée
                    $this->statut = 'initial';
                    $this->etapeProgress = 0;
                    $this->showVerificationButton = true;
                    $this->showFusionButton = false;
                }
            } else {
                toastr()->error($result['message'] ?? 'Erreur lors de la vérification');
                $this->rapportCoherence = [];
            }
        } catch (\Exception $e) {
            Log::error('Erreur dans verifierCoherence', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'session_type' => $this->sessionActive->type,
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
        if (!$this->examen_id || !$this->sessionActive) {
            toastr()->error('Aucun examen ou session sélectionné');
            return;
        }

        $this->confirmingVerify2 = false;

        try {
            // FILTRER PAR SESSION ACTIVE
            $resultats_fusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id) // IMPORTANT
                ->where('statut', ResultatFusion::STATUT_VERIFY_1)
                ->get();

            if ($resultats_fusion->isEmpty()) {
                toastr()->error("Aucun résultat de fusion à l'étape VERIFY_1 trouvé pour la session {$this->sessionActive->type}.");
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

            toastr()->success("$nbUpdated résultats passés à l'étape de seconde vérification pour la session {$this->sessionActive->type}.");
            $this->verifierEtatActuel();

        } catch (\Exception $e) {
            Log::error('Erreur dans passerAVerify2', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
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
        if (!$this->examen_id || !$this->sessionActive) {
            toastr()->error('Aucun examen ou session sélectionné');
            return;
        }

        $this->confirmingVerify3 = false;

        try {
            // FILTRER PAR SESSION ACTIVE
            $resultats_fusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id) // IMPORTANT
                ->where('statut', ResultatFusion::STATUT_VERIFY_2)
                ->get();

            if ($resultats_fusion->isEmpty()) {
                toastr()->error("Aucun résultat de fusion à l'étape VERIFY_2 trouvé pour la session {$this->sessionActive->type}.");
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

            toastr()->success("$nbUpdated résultats passés à la troisième vérification (VERIFY_3) pour la session {$this->sessionActive->type}.");
            $this->verifierEtatActuel();

        } catch (\Exception $e) {
            Log::error('Erreur dans passerAVerify3', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
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

        if (!$this->examen_id || !$this->sessionActive) {
            toastr()->error('Aucun examen ou session sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingValidation = false;

        try {
            // FILTRER PAR SESSION ACTIVE
            $resultats_fusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id) // IMPORTANT
                ->where('statut', ResultatFusion::STATUT_VERIFY_3)
                ->get();

            if ($resultats_fusion->isEmpty()) {
                toastr()->error("Aucun résultat de la 3ème fusion (VERIFY_3) trouvé pour la session {$this->sessionActive->type}.");
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

            toastr()->success("$nbValidated résultats validés avec succès après les 3 fusions pour la session {$this->sessionActive->type}.");
            $this->verifierEtatActuel();

        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de la validation: ' . $e->getMessage());
        }

        $this->isProcessing = false;
    }


    /**
     * ÉTAPE 4 : Publie les résultats - VERSION MISE À JOUR
     *
     */
    public function publierResultats()
    {
        if (!Auth::user()->hasPermissionTo('resultats.validation')) {
            toastr()->error('Vous n\'avez pas l\'autorisation de publier les résultats');
            return;
        }

        if (!$this->examen_id || !$this->sessionActive) {
            toastr()->error('Aucun examen ou session sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingPublication = false;

        try {
            Log::info('Début publication des résultats', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'session_type' => $this->sessionActive->type,
                'etape_fusion' => $this->etapeFusion
            ]);

            // Vérifier d'abord s'il y a des ResultatFinal en attente (cas de réactivation)
            $resultatsFinauxEnAttente = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id) // IMPORTANT: Filtrer par session
                ->where('statut', ResultatFinal::STATUT_EN_ATTENTE)
                ->get();

            Log::info('Vérification des résultats finaux en attente', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'resultats_en_attente' => $resultatsFinauxEnAttente->count()
            ]);

            if ($resultatsFinauxEnAttente->isNotEmpty()) {
                // Cas de réactivation : publier directement les ResultatFinal existants
                Log::info('Publication après réactivation', [
                    'examen_id' => $this->examen_id,
                    'session_id' => $this->sessionActive->id,
                    'resultats_en_attente' => $resultatsFinauxEnAttente->count()
                ]);

                $etudiantsTraites = [];

                // Publier les résultats en recalculant les décisions
                foreach ($resultatsFinauxEnAttente->groupBy('etudiant_id') as $etudiantId => $resultatsEtudiant) {
                    $etudiantsTraites[$etudiantId] = true;

                    // Calculer la décision selon le type de session
                    if ($this->sessionActive->type === 'Rattrapage') {
                        $decision = ResultatFinal::determinerDecisionRattrapage($etudiantId, $this->sessionActive->id);
                    } else {
                        $decision = ResultatFinal::determinerDecisionPremiereSession($etudiantId, $this->sessionActive->id);
                    }

                    // Publier chaque résultat de l'étudiant avec la nouvelle méthode
                    foreach ($resultatsEtudiant as $resultat) {
                        $resultat->changerStatut(
                            ResultatFinal::STATUT_PUBLIE,
                            Auth::id(),
                            false,
                            $decision
                        );
                    }

                    Log::info("Décision recalculée après réactivation", [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $this->sessionActive->id,
                        'session_type' => $this->sessionActive->type,
                        'decision' => $decision
                    ]);
                }

                // Mise à jour de l'état
                $this->statut = 'publie';
                $this->etapeProgress = 100;

                toastr()->success("Publication après réactivation réussie pour la session {$this->sessionActive->type}. " . count($etudiantsTraites) . " étudiants traités.");
                $this->verifierEtatActuel();
                $this->isProcessing = false;
                return;
            }

            // Cas normal : Publication depuis ResultatFusion
            $query = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id); // IMPORTANT: Filtrer par session

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

            $resultatsIds = $query->pluck('id')->toArray();

            Log::info('Résultats fusion pour publication', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'session_type' => $this->sessionActive->type,
                'etape_fusion' => $this->etapeFusion,
                'statut_recherche' => $this->etapeFusion == 4 ? 'VALIDE' : 'VERIFY_3',
                'nb_resultats_trouves' => count($resultatsIds)
            ]);

            if (empty($resultatsIds)) {
                $statutRecherche = $this->etapeFusion == 4 ? 'VALIDE' : 'VERIFY_3';
                toastr()->error("Aucun résultat trouvé avec le statut $statutRecherche à publier pour la session {$this->sessionActive->type}.");
                $this->isProcessing = false;
                return;
            }

            $fusionService = new FusionService();
            $result = $fusionService->transfererResultats($resultatsIds, Auth::id());

            if ($result['success']) {
                // Mise à jour de l'état
                $this->statut = 'publie';
                $this->etapeProgress = 100;

                $message = $result['message'];
                if (isset($result['etudiants_traites'])) {
                    $message .= " (" . $result['etudiants_traites'] . " étudiants traités)";
                }

                toastr()->success($message);
                $this->verifierEtatActuel();

                Log::info('Publication réussie', [
                    'examen_id' => $this->examen_id,
                    'session_id' => $this->sessionActive->id,
                    'session_type' => $this->sessionActive->type,
                    'resultats_transferes' => $result['resultats_transférés'] ?? 0
                ]);
            } else {
                toastr()->error($result['message']);
                Log::error('Échec de la publication', [
                    'examen_id' => $this->examen_id,
                    'session_id' => $this->sessionActive->id,
                    'message' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la publication', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'session_type' => $this->sessionActive->type,
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

        if (!$this->examen_id || !$this->sessionActive) {
            toastr()->error('Aucun examen ou session sélectionné');
            return;
        }

        $this->isProcessing = true;
        $this->confirmingResetFusion = false;

        try {
            // SUPPRIMER SEULEMENT LES RÉSULTATS DE LA SESSION ACTIVE
            $deletedFusion = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id) // IMPORTANT
                ->delete();

            $deletedFinal = ResultatFinal::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id) // IMPORTANT
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

            toastr()->success("Fusion réinitialisée avec succès pour la session {$this->sessionActive->type}. $deletedFusion résultats fusion et $deletedFinal résultats finaux supprimés.");
            $this->switchTab('rapport-stats');

            Log::info('Fusion réinitialisée par session', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'session_type' => $this->sessionActive->type,
                'deleted_fusion' => $deletedFusion,
                'deleted_final' => $deletedFinal,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
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

        if (!$this->examen_id || !$this->sessionActive) {
            toastr()->error('Aucun examen ou session sélectionné');
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
            $result = $fusionService->annulerResultats($this->examen_id, $this->motifAnnulation, $this->sessionActive->id);

            if ($result['success']) {
                // Mettre à jour l'examen
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
            Log::error('Erreur lors de l\'annulation des résultats', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
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

        if (!$this->examen_id || !$this->sessionActive) {
            toastr()->error('Aucun examen ou session sélectionné');
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
            $result = $fusionService->revenirValidation($this->examen_id, $this->sessionActive->id);

            if ($result['success']) {
                // Mettre à jour l'examen
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
            Log::error('Erreur lors de la réactivation des résultats', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
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
     * NOUVEAU : Méthode pour afficher les informations de session
     */
    public function getInfosSession()
    {
        if (!$this->examen_id || !$this->sessionActive) {
            return [
                'session_type' => 'Inconnue',
                'data_count' => 0,
                'etudiants_concernes' => 0
            ];
        }

        $manchettes = Manchette::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->count();

        $copies = Copie::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->count();

        $etudiantsConcernes = Manchette::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->distinct('etudiant_id')
            ->count('etudiant_id');

        return [
            'session_type' => $this->sessionActive->type,
            'session_libelle' => $this->sessionActive->type,
            'manchettes_count' => $manchettes,
            'copies_count' => $copies,
            'data_count' => $manchettes + $copies,
            'etudiants_concernes' => $etudiantsConcernes
        ];
    }

    /**
     * NOUVEAU : Méthode pour créer les données de rattrapage depuis la session normale
     */
    public function initialiserDonneesRattrapage()
    {
        if (!$this->examen_id || $this->sessionActive->type !== 'Rattrapage') {
            toastr()->error('Cette action n\'est disponible que pour les sessions de rattrapage');
            return;
        }

        try {
            // Récupérer la session normale
            $sessionNormale = SessionExam::where('annee_universitaire_id', $this->sessionActive->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                toastr()->error('Aucune session normale trouvée pour cette année universitaire');
                return;
            }

            // Récupérer les étudiants qui ont échoué en session normale
            $etudiantsEchecs = DB::table('resultats_fusion as rf')
                ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                ->where('rf.examen_id', $this->examen_id)
                ->where('rf.session_exam_id', $sessionNormale->id)
                ->where('e.is_active', true)
                ->select('e.id', 'e.nom', 'e.prenom', DB::raw('AVG(rf.note) as moyenne'))
                ->groupBy('e.id', 'e.nom', 'e.prenom')
                ->havingRaw('AVG(rf.note) < 10')
                ->get();

            if ($etudiantsEchecs->isEmpty()) {
                toastr()->info('Aucun étudiant en échec trouvé en session normale. Tous les étudiants ont réussi.');
                return;
            }

            // Récupérer les ECs de l'examen
            $ecs = EC::whereHas('examens', function($query) {
                $query->where('examens.id', $this->examen_id);
            })->get();

            $manchettesCreees = 0;
            $codesCreés = 0;

            DB::beginTransaction();

            foreach ($etudiantsEchecs as $etudiant) {
                foreach ($ecs as $ec) {
                    // Vérifier si le code d'anonymat existe déjà pour cette combinaison
                    $codeExistant = CodeAnonymat::where('examen_id', $this->examen_id)
                        ->where('ec_id', $ec->id)
                        ->where('code_complet', 'like', "RAT-{$ec->id}-{$etudiant->id}%")
                        ->first();

                    if (!$codeExistant) {
                        // Créer un nouveau code d'anonymat pour le rattrapage
                        $codeAnonymat = CodeAnonymat::create([
                            'examen_id' => $this->examen_id,
                            'ec_id' => $ec->id,
                            'code_complet' => "RAT-{$ec->id}-{$etudiant->id}-" . now()->format('His'),
                            'sequence' => $etudiant->id * 1000 + $ec->id
                        ]);
                        $codesCreés++;
                    } else {
                        $codeAnonymat = $codeExistant;
                    }

                    // Vérifier si la manchette existe déjà
                    $manchetteExiste = Manchette::where('examen_id', $this->examen_id)
                        ->where('session_exam_id', $this->sessionActive->id)
                        ->where('etudiant_id', $etudiant->id)
                        ->where('code_anonymat_id', $codeAnonymat->id)
                        ->exists();

                    if (!$manchetteExiste) {
                        // Créer la manchette de rattrapage
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

            if ($manchettesCreees > 0 || $codesCreés > 0) {
                toastr()->success("Initialisation réussie : $manchettesCreees manchettes et $codesCreés codes créés pour " . $etudiantsEchecs->count() . " étudiant(s) éligible(s) au rattrapage");
                $this->verifierEtatActuel();
                $this->verifierCoherence(); // Re-vérifier pour mettre à jour l'interface
            } else {
                toastr()->info('Toutes les données de rattrapage existent déjà');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'initialisation des données de rattrapage', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'error' => $e->getMessage()
            ]);
            toastr()->error('Erreur lors de l\'initialisation: ' . $e->getMessage());
        }
    }

    /**
     * NOUVEAU : Obtenir la liste des étudiants éligibles au rattrapage
    */
    public function getEtudiantsEligiblesRattrapage()
    {
        if (!$this->examen_id || $this->sessionActive->type !== 'Rattrapage') {
            return collect();
        }

        try {
            // ÉTAPE 1: Trouver la session normale correspondante
            $sessionNormale = SessionExam::where('annee_universitaire_id', $this->sessionActive->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                Log::warning('Aucune session normale trouvée pour déterminer les éligibles rattrapage', [
                    'annee_universitaire_id' => $this->sessionActive->annee_universitaire_id
                ]);
                return collect();
            }

            // ÉTAPE 2: Chercher les résultats finaux de la session normale
            $resultatsFinauxNormale = ResultatFinal::where('session_exam_id', $sessionNormale->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->examen->niveau_id)
                    ->where('parcours_id', $this->examen->parcours_id);
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->with(['etudiant', 'examen'])
                ->get();

            if ($resultatsFinauxNormale->isNotEmpty()) {
                // Utiliser les ResultatFinal pour identifier les éligibles
                $etudiantsEligibles = $resultatsFinauxNormale->groupBy('etudiant_id')
                    ->map(function($resultats) use ($sessionNormale) {
                        $etudiant = $resultats->first()->etudiant;
                        if (!$etudiant || !$etudiant->is_active) return null;

                        // LOGIQUE CLAIRE: Éligible = a au moins un RATTRAPAGE et aucun ADMIS
                        $decisions = $resultats->pluck('decision')->unique()->toArray();
                        $aRattrapage = in_array(ResultatFinal::DECISION_RATTRAPAGE, $decisions);
                        $aAdmis = in_array(ResultatFinal::DECISION_ADMIS, $decisions);

                        // ÉLIGIBLE si: a des rattrapages ET n'est pas admis globalement
                        if ($aRattrapage && !$aAdmis) {
                            $moyenneGenerale = $this->calculerMoyenneGeneraleEtudiant($etudiant->id, $sessionNormale->id);

                            return [
                                'etudiant_id' => $etudiant->id,
                                'etudiant' => $etudiant,
                                'moyenne_normale' => $moyenneGenerale,
                                'decision_normale' => 'rattrapage',
                                'nb_resultats' => $resultats->count(),
                                'nb_rattrapages' => $resultats->where('decision', ResultatFinal::DECISION_RATTRAPAGE)->count(),
                                'notes_eliminatoires' => $resultats->where('note', 0)->count(),
                                'source' => 'resultats_final_publies',
                                'statut_global' => $aAdmis ? 'admis' : 'rattrapage'
                            ];
                        }
                        return null;
                    })
                    ->filter()
                    ->values();

                Log::info('=== ÉLIGIBILITÉ RATTRAPAGE (ResultatFinal) ===', [
                    'session_normale_id' => $sessionNormale->id,
                    'session_rattrapage_id' => $this->sessionActive->id,
                    'total_resultats_finaux' => $resultatsFinauxNormale->count(),
                    'etudiants_uniques' => $resultatsFinauxNormale->groupBy('etudiant_id')->count(),
                    'eligibles_rattrapage' => $etudiantsEligibles->count(),
                    'details_decisions' => $resultatsFinauxNormale->groupBy('etudiant_id')->map(function($resultats) {
                        return [
                            'etudiant_id' => $resultats->first()->etudiant_id,
                            'nom' => $resultats->first()->etudiant->nom ?? 'N/A',
                            'decisions' => $resultats->pluck('decision')->unique()->toArray(),
                            'eligible' => in_array(ResultatFinal::DECISION_RATTRAPAGE, $resultats->pluck('decision')->toArray())
                                    && !in_array(ResultatFinal::DECISION_ADMIS, $resultats->pluck('decision')->toArray())
                        ];
                    })->toArray()
                ]);

                return $etudiantsEligibles;
            }

            // ÉTAPE 3: Si pas de ResultatFinal, chercher dans ResultatFusion
            $resultsFusionNormale = ResultatFusion::where('session_exam_id', $sessionNormale->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->examen->niveau_id)
                    ->where('parcours_id', $this->examen->parcours_id);
                })
                ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_3, ResultatFusion::STATUT_VALIDE])
                ->with(['etudiant', 'examen'])
                ->get();

            if ($resultsFusionNormale->isNotEmpty()) {
                $etudiantsEligibles = $resultsFusionNormale->groupBy('etudiant_id')
                    ->map(function($resultats) use ($sessionNormale) {
                        $etudiant = $resultats->first()->etudiant;
                        if (!$etudiant || !$etudiant->is_active) return null;

                        // Calculer la moyenne générale
                        $moyenneGenerale = $resultats->avg('note');

                        // ÉLIGIBLE si moyenne < 10 (seuil d'admission)
                        if ($moyenneGenerale < 10) {
                            return [
                                'etudiant_id' => $etudiant->id,
                                'etudiant' => $etudiant,
                                'moyenne_normale' => $moyenneGenerale,
                                'decision_normale' => 'rattrapage',
                                'nb_resultats' => $resultats->count(),
                                'notes_eliminatoires' => $resultats->where('note', 0)->count(),
                                'source' => 'resultats_fusion_valides',
                                'statut_global' => $moyenneGenerale >= 10 ? 'admis' : 'rattrapage'
                            ];
                        }
                        return null;
                    })
                    ->filter()
                    ->values();

                Log::info('=== ÉLIGIBILITÉ RATTRAPAGE (ResultatFusion) ===', [
                    'session_normale_id' => $sessionNormale->id,
                    'session_rattrapage_id' => $this->sessionActive->id,
                    'total_resultats_fusion' => $resultsFusionNormale->count(),
                    'etudiants_uniques' => $resultsFusionNormale->groupBy('etudiant_id')->count(),
                    'eligibles_rattrapage' => $etudiantsEligibles->count(),
                    'details_moyennes' => $resultsFusionNormale->groupBy('etudiant_id')->map(function($resultats) {
                        return [
                            'etudiant_id' => $resultats->first()->etudiant_id,
                            'nom' => $resultats->first()->etudiant->nom ?? 'N/A',
                            'moyenne' => round($resultats->avg('note'), 2),
                            'eligible' => $resultats->avg('note') < 10
                        ];
                    })->toArray()
                ]);

                return $etudiantsEligibles;
            }

            Log::warning('Aucune donnée de session normale trouvée pour déterminer les éligibles', [
                'examen_id' => $this->examen_id,
                'session_normale_id' => $sessionNormale->id,
                'niveau_id' => $this->examen->niveau_id,
                'parcours_id' => $this->examen->parcours_id
            ]);

            return collect();

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des étudiants éligibles rattrapage', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect();
        }
    }


    /**
     * Méthode utilitaire pour calculer la moyenne générale d'un étudiant
     */
    private function calculerMoyenneGeneraleEtudiant($etudiantId, $sessionId)
    {
        try {
            $resultats = ResultatFinal::where('etudiant_id', $etudiantId)
                ->where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->examen->niveau_id)
                    ->where('parcours_id', $this->examen->parcours_id);
                })
                ->get();

            if ($resultats->isEmpty()) {
                return 0;
            }

            // Moyenne pondérée par les coefficients si disponibles
            $totalPoints = 0;
            $totalCoefficients = 0;

            foreach ($resultats as $resultat) {
                $coefficient = $resultat->examen->elementConstitutif->coefficient ?? 1;
                $totalPoints += $resultat->note * $coefficient;
                $totalCoefficients += $coefficient;
            }

            return $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;

        } catch (\Exception $e) {
            Log::error('Erreur calcul moyenne générale étudiant', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function getStatistiquesCompletes()
    {
        if (!$this->examen_id) {
            return [
                'total_etudiants_niveau' => 0,
                'etudiants_eligibles_rattrapage' => 0,
                'etudiants_participants_rattrapage' => 0,
                'etudiants_admis_session_normale' => 0,
                'taux_eligibilite_rattrapage' => 0,
                'taux_participation_rattrapage' => 0
            ];
        }

        try {
            // Total étudiants du niveau/parcours
            $totalEtudiants = Etudiant::whereHas('inscriptions', function($q) {
                $q->where('niveau_id', $this->examen->niveau_id)
                ->where('parcours_id', $this->examen->parcours_id)
                ->where('annee_universitaire_id', $this->sessionActive->annee_universitaire_id);
            })->where('is_active', true)->count();

            if ($this->sessionActive->type === 'Rattrapage') {
                // Étudiants éligibles au rattrapage
                $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
                $nbEligibles = $etudiantsEligibles->count();

                // Étudiants participants (ayant des manchettes rattrapage)
                $participantsRattrapage = Manchette::where('examen_id', $this->examen_id)
                    ->where('session_exam_id', $this->sessionActive->id)
                    ->distinct('etudiant_id')
                    ->count();

                // Étudiants admis en session normale
                $sessionNormale = SessionExam::where('annee_universitaire_id', $this->sessionActive->annee_universitaire_id)
                    ->where('type', 'Normale')
                    ->first();

                $admisSessionNormale = 0;
                if ($sessionNormale) {
                    $admisSessionNormale = ResultatFinal::where('session_exam_id', $sessionNormale->id)
                        ->whereHas('examen', function($q) {
                            $q->where('niveau_id', $this->examen->niveau_id)
                            ->where('parcours_id', $this->examen->parcours_id);
                        })
                        ->where('decision', ResultatFinal::DECISION_ADMIS)
                        ->distinct('etudiant_id')
                        ->count();
                }

                return [
                    'total_etudiants_niveau' => $totalEtudiants,
                    'etudiants_eligibles_rattrapage' => $nbEligibles,
                    'etudiants_participants_rattrapage' => $participantsRattrapage,
                    'etudiants_admis_session_normale' => $admisSessionNormale,
                    'taux_eligibilite_rattrapage' => $totalEtudiants > 0 ? round(($nbEligibles / $totalEtudiants) * 100, 1) : 0,
                    'taux_participation_rattrapage' => $nbEligibles > 0 ? round(($participantsRattrapage / $nbEligibles) * 100, 1) : 0
                ];
            }

            // Session normale
            return [
                'total_etudiants_niveau' => $totalEtudiants,
                'etudiants_eligibles_rattrapage' => 0,
                'etudiants_participants_rattrapage' => 0,
                'etudiants_admis_session_normale' => 0,
                'taux_eligibilite_rattrapage' => 0,
                'taux_participation_rattrapage' => 0
            ];

        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques complètes', [
                'examen_id' => $this->examen_id,
                'error' => $e->getMessage()
            ]);

            return [
                'total_etudiants_niveau' => 0,
                'etudiants_eligibles_rattrapage' => 0,
                'etudiants_participants_rattrapage' => 0,
                'etudiants_admis_session_normale' => 0,
                'taux_eligibilite_rattrapage' => 0,
                'taux_participation_rattrapage' => 0
            ];
        }
    }


    public function diagnosticEligiblesRattrapage()
    {
        if (!$this->examen_id || $this->sessionActive->type !== 'Rattrapage') {
            toastr()->error('Cette méthode ne fonctionne que pour les sessions de rattrapage');
            return;
        }

        try {
            $sessionNormale = SessionExam::where('annee_universitaire_id', $this->sessionActive->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            // Compter les différentes sources de données
            $manchettesRattrapage = Manchette::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $this->sessionActive->id)
                ->count();

            $resultatsFinauxNormale = $sessionNormale ? ResultatFinal::where('session_exam_id', $sessionNormale->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->examen->niveau_id)
                    ->where('parcours_id', $this->examen->parcours_id);
                })
                ->count() : 0;

            $resultsFusionNormale = $sessionNormale ? ResultatFusion::where('session_exam_id', $sessionNormale->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->examen->niveau_id)
                    ->where('parcours_id', $this->examen->parcours_id);
                })
                ->count() : 0;

            Log::info('=== DIAGNOSTIC ÉLIGIBLES RATTRAPAGE ===', [
                'examen_id' => $this->examen_id,
                'session_rattrapage_id' => $this->sessionActive->id,
                'session_normale_id' => $sessionNormale ? $sessionNormale->id : null,
                'manchettes_rattrapage' => $manchettesRattrapage,
                'resultats_finaux_normale' => $resultatsFinauxNormale,
                'resultats_fusion_normale' => $resultsFusionNormale,
                'niveau_id' => $this->examen->niveau_id,
                'parcours_id' => $this->examen->parcours_id
            ]);

            toastr()->info("Diagnostic effectué - Consultez les logs. Manchettes rattrapage: $manchettesRattrapage, Résultats normale: $resultatsFinauxNormale");

        } catch (\Exception $e) {
            Log::error('Erreur diagnostic éligibles', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * NOUVEAU : Compter les données existantes pour la session active
     */
    public function getCompteursDonneesSession()
    {
        if (!$this->examen_id || !$this->sessionActive) {
            return [
                'manchettes' => 0,
                'copies' => 0,
                'etudiants' => 0,
                'ecs' => 0
            ];
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


    /**
     * Calcul des statistiques basées sur la présence
     */
    public function getStatistiquesAvecPresence()
    {
        if (!$this->examen_id || !$this->sessionActive) {
            return null;
        }

        $sessionId = $this->sessionActive->id;

        // Récupérer les données de présence
        $presenceGlobale = PresenceExamen::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $sessionId)
            ->whereNull('ec_id') // Présence globale
            ->first();

        if (!$presenceGlobale) {
            // Fallback : calculer depuis les manchettes
            $etudiantsPresents = Manchette::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId)
                ->distinct('etudiant_id')
                ->count();
            
            $totalInscrits = $this->getTotalEtudiantsInscrits();
            
            return [
                'total_inscrits' => $totalInscrits,
                'etudiants_presents' => $etudiantsPresents,
                'etudiants_absents' => $totalInscrits - $etudiantsPresents,
                'taux_presence' => $totalInscrits > 0 ? round(($etudiantsPresents / $totalInscrits) * 100, 2) : 0,
                'source' => 'manchettes'
            ];
        }

        return [
            'total_inscrits' => $presenceGlobale->total_attendu ?: $presenceGlobale->total_etudiants,
            'etudiants_presents' => $presenceGlobale->etudiants_presents,
            'etudiants_absents' => $presenceGlobale->etudiants_absents,
            'taux_presence' => $presenceGlobale->taux_presence,
            'source' => 'presence_enregistree'
        ];
    }




    /**
     * NOUVELLE MÉTHODE : Obtient les statistiques complètes pour le rattrapage
     * avec la logique Total → Admis + Éligibles → Participants
     */
    public function getStatistiquesCompletesRattrapage()
    {
        if (!$this->examen_id || !$this->sessionActive || $this->sessionActive->type !== 'Rattrapage') {
            return [
                'total_inscrits' => 0,
                'admis_premiere_session' => 0,
                'eligibles_rattrapage' => 0,
                'participants_rattrapage' => 0
            ];
        }

        try {
            // 1. TOTAL INSCRITS pour ce niveau/parcours
            $totalInscrits = Etudiant::where('niveau_id', $this->examen->niveau_id)
                ->where('parcours_id', $this->examen->parcours_id)
                ->where('is_active', true)
                ->count();

            // 2. Trouver la session normale correspondante
            $sessionNormale = SessionExam::where('annee_universitaire_id', $this->sessionActive->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                Log::warning('Aucune session normale trouvée pour les statistiques rattrapage', [
                    'session_rattrapage_id' => $this->sessionActive->id,
                    'annee_universitaire_id' => $this->sessionActive->annee_universitaire_id
                ]);

                return [
                    'total_inscrits' => $totalInscrits,
                    'admis_premiere_session' => 0,
                    'eligibles_rattrapage' => 0,
                    'participants_rattrapage' => 0
                ];
            }

            // 3. ADMIS EN 1ÈRE SESSION (décision = ADMIS)
            $admisPremiereSession = ResultatFinal::where('session_exam_id', $sessionNormale->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->examen->niveau_id)
                    ->where('parcours_id', $this->examen->parcours_id);
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->where('decision', ResultatFinal::DECISION_ADMIS)
                ->distinct('etudiant_id')
                ->count('etudiant_id');

            // 4. ÉLIGIBLES RATTRAPAGE (décision = RATTRAPAGE)
            $eligiblesRattrapage = ResultatFinal::where('session_exam_id', $sessionNormale->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->examen->niveau_id)
                    ->where('parcours_id', $this->examen->parcours_id);
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->where('decision', ResultatFinal::DECISION_RATTRAPAGE)
                ->distinct('etudiant_id')
                ->count('etudiant_id');

            // ✅ 5. PARTICIPANTS RATTRAPAGE : Utiliser les données de présence si disponibles
            $statsPresence = $this->getStatistiquesAvecPresence();
            
            if ($statsPresence && $statsPresence['source'] === 'presence_enregistree') {
                // Utiliser les données de présence officielles
                $participantsRattrapage = $statsPresence['etudiants_presents'];
                Log::info('Participants rattrapage depuis données de présence', [
                    'participants' => $participantsRattrapage,
                    'source' => $statsPresence['source']
                ]);
            } else {
                // Fallback : compter les manchettes
                $participantsRattrapage = Manchette::where('examen_id', $this->examen_id)
                    ->where('session_exam_id', $this->sessionActive->id)
                    ->distinct('etudiant_id')
                    ->count('etudiant_id');
            }

            // 6. Vérification de cohérence logique
            $sommeVerification = $admisPremiereSession + $eligiblesRattrapage;

            if ($sommeVerification !== $totalInscrits && $totalInscrits > 0) {
                Log::warning('Incohérence dans les statistiques rattrapage', [
                    'total_inscrits' => $totalInscrits,
                    'admis_premiere' => $admisPremiereSession,
                    'eligibles_rattrapage' => $eligiblesRattrapage,
                    'somme' => $sommeVerification,
                    'session_normale_id' => $sessionNormale->id,
                    'session_rattrapage_id' => $this->sessionActive->id
                ]);
            }

            Log::info('Statistiques complètes rattrapage calculées avec présence', [
                'total_inscrits' => $totalInscrits,
                'admis_premiere_session' => $admisPremiereSession,
                'eligibles_rattrapage' => $eligiblesRattrapage,
                'participants_rattrapage' => $participantsRattrapage,
                'source_participants' => $statsPresence['source'] ?? 'manchettes',
                'coherence' => $sommeVerification === $totalInscrits ? 'OK' : 'ERREUR'
            ]);

            return [
                'total_inscrits' => $totalInscrits,
                'admis_premiere_session' => $admisPremiereSession,
                'eligibles_rattrapage' => $eligiblesRattrapage,
                'participants_rattrapage' => $participantsRattrapage,
                'taux_presence' => $statsPresence['taux_presence'] ?? null,
                'source_donnees' => $statsPresence['source'] ?? 'manchettes'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques complètes rattrapage', [
                'examen_id' => $this->examen_id,
                'session_id' => $this->sessionActive->id,
                'error' => $e->getMessage()
            ]);

            return [
                'total_inscrits' => 0,
                'admis_premiere_session' => 0,
                'eligibles_rattrapage' => 0,
                'participants_rattrapage' => 0
            ];
        }
    }



    /**
     * MÉTHODE UTILITAIRE : Obtient le total d'étudiants inscrits
     */
    public function getTotalEtudiantsInscrits()
    {
        if (!$this->examen) {
            return 0;
        }

        return Etudiant::where('niveau_id', $this->examen->niveau_id)
            ->where('parcours_id', $this->examen->parcours_id)
            ->where('is_active', true)
            ->count();
    }
     /**
     * NOUVELLE MÉTHODE SIMPLE : Obtient les statistiques pour session normale
    */
    public function getStatistiquesSessionNormale()
    {
        if (!$this->examen_id || !$this->sessionActive) {
            return null;
        }

        // Compter directement depuis resultats_finaux si publiés
        $admis = \DB::table('resultats_finaux')
            ->where('session_exam_id', $this->sessionActive->id)
            ->where('statut', 'publie')
            ->where('decision', 'admis')
            ->distinct('etudiant_id')
            ->count('etudiant_id');

        $rattrapage = \DB::table('resultats_finaux')
            ->where('session_exam_id', $this->sessionActive->id)
            ->where('statut', 'publie')
            ->where('decision', 'rattrapage')
            ->distinct('etudiant_id')
            ->count('etudiant_id');

        $totalInscrits = $this->getTotalEtudiantsInscrits();

        return [
            'total_inscrits' => $totalInscrits,
            'admis_premiere_session' => $admis,
            'eligibles_rattrapage' => $rattrapage,
            'participants_rattrapage' => 0 // N/A pour session normale
        ];
    }

    /**
     * Mise à jour du rendu pour inclure les nouvelles propriétés
     */
    public function render()
    {
        // Préparer les données selon le type de session
        $statistiquesCompletes = null;
        $compteursDonnees = $this->getCompteursDonneesSession();
        $etudiantsEligibles = collect();

        if ($this->sessionActive && $this->sessionActive->type === 'Rattrapage') {
            // Pour le rattrapage : obtenir les statistiques complètes
            $statistiquesCompletes = $this->getStatistiquesCompletesRattrapage();
            $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
        } elseif ($this->sessionActive && $this->sessionActive->type === 'Normale') {
            // Pour la session normale : créer les statistiques si des résultats sont publiés
            if ($this->statut === 'publie') {
                $statistiquesCompletes = $this->getStatistiquesSessionNormale();
            }
        }

        return view('livewire.resultats.fusion-index', [
            'examen' => $this->examen,
            'statut' => $this->statut,
            'etapeFusion' => $this->etapeFusion,
            'etapeProgress' => $this->etapeProgress,
            'isProcessing' => $this->isProcessing,
            'activeTab' => $this->activeTab,
            'examen_id' => $this->examen_id,
            'estPACES' => $this->estPACES,

            // Données préparées pour la vue
            'statistiquesCompletes' => $statistiquesCompletes,
            'compteursDonnees' => $compteursDonnees,
            'etudiantsEligibles' => $etudiantsEligibles,

            // Confirmations
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

            // Données existantes
            'rapportCoherence' => $this->rapportCoherence,
            'resultatsStats' => $this->resultatsStats,

            // États des boutons
            'showVerificationButton' => $this->showVerificationButton,
            'showResetButton' => $this->showResetButton,
            'showFusionButton' => $this->showFusionButton,
        ]);
    }

}