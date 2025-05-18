<?php

namespace App\Livewire\Resultats;

use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Services\DeliberationService;
use Livewire\Component;
use Livewire\WithPagination;

class DeliberationIndex extends Component
{
    use WithPagination;

    // Variables de filtrage
    public $niveau_id;
    public $parcours_id;
    public $examen_id;

    // Données de délibération
    public $donneesDeliberation = [];
    public $pointsJury = [];
    public $observations = [];

    // Contrôles UI
    public $confirmingDeliberation = false;

    // Listes des données pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $examens = [];

    public function mount()
    {
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();
    }

    // Méthodes de mise à jour des filtres (similaires à vos autres composants)
    // ...

    public function preparerDeliberation()
    {
        $deliberationService = new DeliberationService();
        $this->donneesDeliberation = $deliberationService->preparerDeliberation($this->examen_id);

        // Initialiser les points jury et observations
        foreach ($this->donneesDeliberation as $etudiant) {
            $this->pointsJury[$etudiant['etudiant_id']] = 0;
            $this->observations[$etudiant['etudiant_id']] = '';
        }
    }

    public function confirmerDeliberation()
    {
        $this->confirmingDeliberation = true;
    }

    public function appliquerDeliberation()
    {
        // Préparer les données de délibération
        $deliberations = [];

        foreach ($this->pointsJury as $etudiantId => $points) {
            if ($points != 0 || !empty($this->observations[$etudiantId])) {
                $deliberations[] = [
                    'etudiant_id' => $etudiantId,
                    'points_jury' => $points,
                    'observation' => $this->observations[$etudiantId] ?? ''
                ];
            }
        }

        // Appliquer la délibération
        $deliberationService = new DeliberationService();
        $result = $deliberationService->appliquerDeliberation($this->examen_id, $deliberations);

        // Message de confirmation
        toastr()->success($result['message']);

        // Réinitialiser
        $this->confirmingDeliberation = false;
        $this->donneesDeliberation = [];
        $this->pointsJury = [];
        $this->observations = [];
    }

    public function render()
    {
        return view('livewire.resultats.deliberation-index', [
            'niveaux' => $this->niveaux,
            'parcours' => $this->parcours,
            'examens' => $this->examens,
        ]);
    }
}
