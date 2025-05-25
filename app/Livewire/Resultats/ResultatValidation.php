<?php

namespace App\Livewire\Resultats;

use App\Models\Examen;
use App\Models\FusionOperation;
use App\Services\FusionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ResultatValidation extends Component
{
    public $examen_id;
    public $loading = false;
    public $statut = null;
    public $estPACES = false;

    protected $listeners = ['examenChanged' => 'setExamenId', 'fusionEffectuee' => 'onFusionEffectuee'];

    public function setExamenId($examen_id)
    {
        $this->examen_id = $examen_id;

        if ($this->examen_id) {
            // Vérifier si c'est un examen PACES
            $examen = Examen::with('niveau')->find($this->examen_id);
            if ($examen) {
                $this->estPACES = $examen->niveau->abr == 'PACES' && $examen->niveau->id == 1;
            }

            // Déterminer le statut actuel
            $fusionService = new FusionService();
            $statutActuel = $fusionService->getStatutActuel($this->examen_id);
            $this->statut = $statutActuel;
        }
    }

    public function onFusionEffectuee($result)
    {
        // Mettre à jour le statut après une fusion réussie
        $fusionService = new FusionService();
        $this->statut = $fusionService->getStatutActuel($this->examen_id);
    }

    public function validerResultats()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->loading = true;

        try {
            $fusionService = new FusionService();
            $result = $fusionService->validerResultats($this->examen_id);

            if ($result['success']) {
                toastr()->success($result['message']);
                $this->statut = 'validation';
                $this->dispatch('resultatsValides', $result);
            } else {
                toastr()->error($result['message']);
            }
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la validation: ' . $e->getMessage());
        }

        $this->loading = false;
    }

    public function publierResultats()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->loading = true;

        try {
            $fusionService = new FusionService();
            $result = $fusionService->publierResultats($this->examen_id, $this->estPACES);

            if ($result['success']) {
                toastr()->success($result['message']);
                $this->statut = 'publie';
                $this->dispatch('resultatsPublies', $result);
            } else {
                toastr()->error($result['message']);
            }
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la publication: ' . $e->getMessage());
        }

        $this->loading = false;
    }

    public function resetFusion()
    {
        $this->dispatchBrowserEvent('openConfirmModal', [
            'title' => 'Confirmation',
            'message' => 'Êtes-vous sûr de vouloir réinitialiser la fusion ? Tous les résultats provisoires seront supprimés.',
            'action' => 'resetFusionConfirmed'
        ]);
    }

    public function resetFusionConfirmed()
    {
        if (!$this->examen_id) {
            toastr()->error('Aucun examen sélectionné');
            return;
        }

        $this->loading = true;

        try {
            $fusionService = new FusionService();
            $result = $fusionService->performResetFusion($this->examen_id, Auth::id());

            if ($result['success']) {
                toastr()->success($result['message']);
                $this->statut = 'verification';
                $this->dispatch('resultatsAnnules');
            } else {
                toastr()->error($result['message']);
            }
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la réinitialisation: ' . $e->getMessage());
        }

        $this->loading = false;
    }

    public function render()
    {
        $operationEnCours = FusionOperation::where('examen_id', $this->examen_id)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        return view('livewire.resultats.resultat-validation', [
            'operationEnCours' => $operationEnCours
        ]);
    }
}
