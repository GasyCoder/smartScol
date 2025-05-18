<?php

namespace App\Livewire\Resultats;

use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Resultat;
use App\Models\EC;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $ecs
 * @property \Illuminate\Support\Collection $examens
 * @property \Illuminate\Support\Collection $resultats
 */
class ResultatsProvisoires extends Component
{
    use WithPagination;

    // Variables de filtrage
    public $niveau_id;
    public $parcours_id;
    public $examen_id;
    public $ec_id;
    public $search = '';
    public $orderBy = 'nom';
    public $orderAsc = true;
    public $statut = 'provisoire';

    // Listes des données pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $examens = [];
    public $ecs = [];

    // Pour l'impression
    public $printMode = false;

    // Session active
    public $sessionActive = null;

    protected $queryString = ['search', 'niveau_id', 'parcours_id', 'examen_id', 'ec_id', 'statut'];

    public function mount()
    {
        // Récupérer la session active
        $this->sessionActive = \App\Models\SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->with('anneeUniversitaire')
            ->first();

        $this->niveaux = Niveau::where('is_active', true)->orderBy('abr', 'desc')->get();

        // Initialiser les valeurs depuis les paramètres d'URL
        if ($this->niveau_id) {
            $this->updatedNiveauId();
        }
    }

    public function updatedNiveauId()
    {
        $this->parcours = collect();
        $this->examens = collect();
        $this->ecs = collect();
        $this->parcours_id = null;
        $this->examen_id = null;
        $this->ec_id = null;
        $this->resetPage();

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
        $this->examens = collect();
        $this->ecs = collect();
        $this->examen_id = null;
        $this->ec_id = null;
        $this->resetPage();

        if ($this->niveau_id && $this->parcours_id && $this->sessionActive) {
            // Récupérer seulement l'examen pour la session active
            $this->examens = Examen::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->where('session_id', $this->sessionActive->id)
                ->orderBy('id', 'desc')
                ->get();

            if ($this->examens->count() == 1) {
                $this->examen_id = $this->examens->first()->id;
                $this->updatedExamenId();
            }
        }
    }

    public function updatedExamenId()
    {
        $this->ecs = collect();
        $this->ec_id = null;
        $this->resetPage();

        if ($this->examen_id) {
            // Récupérer les ECs liées à cet examen
            $ecModels = EC::whereHas('examens', function($query) {
                    $query->where('examens.id', $this->examen_id);
                })
                ->orderBy('nom')
                ->get();

            // Convertir en collection régulière (pas Eloquent)
            $this->ecs = collect();

            // Ajouter d'abord l'option "Toutes les matières"
            $this->ecs->push((object)[
                'id' => 'all',
                'nom' => 'Toutes les matières'
            ]);

            // Puis ajouter les ECs réelles
            foreach ($ecModels as $ec) {
                $this->ecs->push((object)[
                    'id' => $ec->id,
                    'abr' => $ec->abr,
                    'nom' => $ec->nom,
                    'enseignant' => $ec->enseignant
                ]);
            }
        }
    }

    public function toggleOrder($field)
    {
        if ($this->orderBy === $field) {
            $this->orderAsc = !$this->orderAsc;
        } else {
            $this->orderBy = $field;
            $this->orderAsc = true;
        }
    }

    public function togglePrintMode()
    {
        $this->printMode = !$this->printMode;
    }

    public function render()
    {
        $query = Resultat::query()
            ->join('etudiants', 'resultats.etudiant_id', '=', 'etudiants.id')
            ->join('ecs', 'resultats.ec_id', '=', 'ecs.id')
            ->join('codes_anonymat', 'resultats.code_anonymat_id', '=', 'codes_anonymat.id')
            ->select(
                'resultats.*',
                'etudiants.matricule',
                'etudiants.nom as etudiant_nom',
                'etudiants.prenom',
                'ecs.nom as ec_nom',
                'ecs.abr as ec_abr',
                'ecs.enseignant',
                'codes_anonymat.code_complet'
            );

        if ($this->examen_id) {
            $query->where('resultats.examen_id', $this->examen_id);
        }

        if ($this->ec_id && $this->ec_id !== 'all') {
            $query->where('resultats.ec_id', $this->ec_id);
        }

        if ($this->statut) {
            $query->where('resultats.statut', $this->statut);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('etudiants.matricule', 'like', '%' . $this->search . '%')
                    ->orWhere('etudiants.nom', 'like', '%' . $this->search . '%')
                    ->orWhere('etudiants.prenom', 'like', '%' . $this->search . '%')
                    ->orWhere('codes_anonymat.code_complet', 'like', '%' . $this->search . '%')
                    ->orWhere('ecs.enseignant', 'like', '%' . $this->search . '%');
            });
        }

        // Tri
        $orderField = match($this->orderBy) {
            'matricule' => 'etudiants.matricule',
            'nom' => 'etudiants.nom',
            'prenom' => 'etudiants.prenom',
            'code' => 'codes_anonymat.code_complet',
            'matiere' => 'ecs.nom',
            'enseignant' => 'ecs.enseignant',
            'note' => 'resultats.note',
            default => 'etudiants.nom'
        };

        $query->orderBy($orderField, $this->orderAsc ? 'asc' : 'desc');

        // Ajouter un tri secondaire par matière et par nom
        if ($orderField !== 'ecs.nom') {
            $query->orderBy('ecs.nom', 'asc');
        }
        if ($orderField !== 'etudiants.nom') {
            $query->orderBy('etudiants.nom', 'asc');
        }

        $resultats = $this->printMode
            ? $query->get()
            : $query->paginate(25);

        return view('livewire.resultats.resultats-provisoires', [
            'resultats' => $resultats,
            'niveaux' => $this->niveaux,
            'parcours' => $this->parcours,
            'examens' => $this->examens,
            'ecs' => $this->ecs,
        ]);
    }
}
