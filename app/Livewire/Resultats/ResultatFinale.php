<?php

namespace App\Livewire\Resultats;

use App\Models\Examen;
use App\Models\FusionOperation;
use App\Services\FusionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ResultatFinale extends Component
{
    public function render()
    {

        return view('livewire.resultats.resultat-finale');
    }
}
