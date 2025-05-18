<?php

namespace App\Livewire\Resultats;

use App\Models\EC;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\SessionExam;
use App\Services\FusionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 * @property Examen $examen
 * @property \Illuminate\Support\Collection $resultatFusion
 */
class FusionIndex extends Component
{
    use WithPagination;

    // Variables de filtrage
    public $niveau_id;
    public $parcours_id;
    public $examen_id;

    // Listes des données pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $examens = [];

    // Variables d'objets récupérés
    public $examen = null;
    public $sessionActive = null;

    // Variables de résultats
    public $rapportCoherence = [];
    public $resultatFusion = null;
    public $statistiques = null;

    // Variables de contrôle d'interface
    public $showCoherenceModal = false;
    public $showFusionResultModal = false;
    public $showResolutionModal = false;
    public $showStatistiquesModal = false;
    public $confirmingFusion = false;
    public $processingAction = false;
    public $messageType = '';
    public $message = '';
    public $showStatistiques = false;

    // Résolutions manuelles d'erreurs
    public $resolutions = [];

    public function mount()
    {
        // Récupérer la session active
        $this->sessionActive = SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->with('anneeUniversitaire')
            ->first();

        if (!$this->sessionActive) {
            $this->message = 'Aucune session active trouvée. Veuillez configurer une session active dans les paramètres.';
            $this->messageType = 'error';
            toastr()->error($this->message);
        }

        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();
    }

    public function updatedNiveauId()
    {
        $this->parcours = collect();
        $this->parcours_id = null;
        $this->examen_id = null;
        $this->examen = null;
        $this->resultatFusion = null;
        $this->rapportCoherence = [];
        $this->statistiques = null;

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

    public function updatedParcoursId()
    {
        // Réinitialiser les résultats précédents
        $this->resultatFusion = null;
        $this->rapportCoherence = [];
        $this->statistiques = null;
        $this->examen = null;
        $this->examen_id = null;

        // Récupérer automatiquement l'examen correspondant au niveau et parcours pour la session active
        if ($this->niveau_id && $this->parcours_id && $this->sessionActive) {
            $this->examen = Examen::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->where('session_id', $this->sessionActive->id)
                ->first();

            // Définir l'ID de l'examen qui sera utilisé pour les opérations
            if ($this->examen) {
                $this->examen_id = $this->examen->id;
            } else {
                $this->message = 'Aucun examen trouvé pour ce niveau, parcours et session active';
                $this->messageType = 'error';
                toastr()->error($this->message);
            }
        }
    }

    public function verifierCoherence()
    {
        if (!$this->examen_id) {
            $this->message = "Aucun examen sélectionné. Veuillez choisir un niveau et un parcours.";
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        try {
            $fusionService = new FusionService();
            $this->rapportCoherence = $fusionService->verifierCoherence($this->examen_id);
            $this->showCoherenceModal = true;
        } catch (\Exception $e) {
            $this->message = 'Erreur lors de la vérification: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
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

    public function fusionner($forcer = false)
    {
        if (!$this->examen_id) {
            $this->message = "Aucun examen sélectionné. Veuillez choisir un niveau et un parcours.";
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        $this->processingAction = true;
        $this->confirmingFusion = false;

        try {
            $fusionService = new FusionService();
            $this->resultatFusion = $fusionService->fusionner($this->examen_id, $forcer);
            $this->showFusionResultModal = true;
            $this->message = 'Fusion effectuée avec succès. '
                . $this->resultatFusion['resultats']->count() . ' résultats générés. '
                . count($this->resultatFusion['erreurs']) . ' erreurs détectées.';
            $this->messageType = 'success';
            toastr()->success($this->message);
        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }

        $this->processingAction = false;
    }

    public function afficherResolutionErreurs()
    {
        if (!$this->resultatFusion || empty($this->resultatFusion['erreurs'])) {
            toastr()->info('Aucune erreur à résoudre.');
            return;
        }

        // Préparer la structure des résolutions
        $this->resolutions = [];
        foreach ($this->resultatFusion['erreurs'] as $index => $erreur) {
            $this->resolutions[$index] = [
                'id' => $index,
                'type' => $erreur['type'],
                'action' => 'ignorer_erreur', // Par défaut
                'manchette_id' => isset($erreur['manchette']) ? $erreur['manchette']['id'] : null,
                'copie_id' => isset($erreur['copie']) ? $erreur['copie']['id'] : null,
                'message' => $erreur['message'],
            ];
        }

        $this->showResolutionModal = true;
    }

    public function appliquerResolutions()
    {
        try {
            $resolutionsAAppliquer = collect($this->resolutions)
                ->filter(function ($resolution) {
                    return $resolution['action'] !== 'ignorer_erreur';
                })
                ->toArray();

            if (empty($resolutionsAAppliquer)) {
                $this->message = 'Aucune résolution à appliquer.';
                $this->messageType = 'info';
                toastr()->info($this->message);
                $this->showResolutionModal = false;
                return;
            }

            $fusionService = new FusionService();
            $resultatResolutions = $fusionService->resoudreErreurs($resolutionsAAppliquer);

            if ($resultatResolutions['success']) {
                $this->message = 'Résolutions appliquées : ' . count($resultatResolutions['resultats']) . ' résultats générés';
                $this->messageType = 'success';
                toastr()->success($this->message);

                // Rafraîchir les résultats de fusion
                $this->resultatFusion = $fusionService->fusionner($this->examen_id, true);
                $this->showResolutionModal = false;
            } else {
                $this->message = 'Erreur: ' . $resultatResolutions['message'];
                $this->messageType = 'error';
                toastr()->error($this->message);
            }
        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    public function validerResultats()
    {
        if (!$this->examen_id) {
            $this->message = "Aucun examen sélectionné. Veuillez choisir un niveau et un parcours.";
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        try {
            $fusionService = new FusionService();
            $resultat = $fusionService->validerResultats($this->examen_id);

            if ($resultat['success']) {
                $this->message = $resultat['count'] . ' résultats validés avec succès';
                $this->messageType = 'success';
                toastr()->success($this->message);
            } else {
                $this->message = 'Erreur: ' . $resultat['message'];
                $this->messageType = 'error';
                toastr()->error($this->message);
            }
        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    public function calculerStatistiques()
    {
        if (!$this->examen_id) {
            $this->message = "Aucun examen sélectionné. Veuillez choisir un niveau et un parcours.";
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        try {
            $fusionService = new FusionService();
            $this->statistiques = $fusionService->calculerStatistiques($this->examen_id);

            if ($this->statistiques['success']) {
                $this->showStatistiquesModal = true;
            } else {
                $this->message = $this->statistiques['message'];
                $this->messageType = 'error';
                toastr()->error($this->message);
            }
        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    public function imprimer()
    {
        if (!$this->examen_id) {
            $this->message = "Aucun examen sélectionné pour l'impression";
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // return redirect()->route('resultats.imprimer', ['examen' => $this->examen_id]);
    }

    public function render()
    {
        return view('livewire.resultats.fusion-index', [
            'niveaux' => $this->niveaux,
            'parcours' => $this->parcours,
        ]);
    }
}
