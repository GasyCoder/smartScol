<?php

namespace App\Livewire\Copie;

use App\Models\Copie;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\EC;
use App\Models\SessionExam;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CopiesIndex extends Component
{
    use WithPagination;

    // Propriétés de filtrage
    public $niveau_id = null;
    public $parcours_id = null;
    public $ec_id = null;
    public $secretaire_id = null;
    public $search = '';

    // Propriétés de tri
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;

    // Données pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $ecs = [];
    public $secretaires = [];

    // Modal états
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editingCopy = null;
    public $copyToDelete = null;

    // Champs d'édition
    public $edit_code_copie = '';
    public $edit_note = null;

    // Session active
    public $sessionActive = null;
    public $sessionInfo = '';

    protected function rules()
    {
        return [
            'edit_note' => 'nullable|numeric|min:0|max:20',
        ];
    }

    protected function messages()
    {
        return [
            'edit_note.numeric' => 'La note doit être un nombre.',
            'edit_note.min' => 'La note ne peut pas être inférieure à 0.',
            'edit_note.max' => 'La note ne peut pas être supérieure à 20.',
        ];
    }

    public function mount()
    {
        $this->loadInitialData();
        $this->updateSessionInfo();
    }

    private function loadInitialData()
    {
        $this->niveaux = Niveau::where('is_active', true)->orderBy('nom')->get();
        $this->secretaires = User::role('secretaire')->orderBy('name')->get();
    }

    private function updateSessionInfo()
    {
        try {
            $this->sessionActive = SessionExam::where('is_active', true)
                ->where('is_current', true)
                ->first();

            if ($this->sessionActive) {
                $this->sessionInfo = "Session {$this->sessionActive->type} active";
            } else {
                $this->sessionInfo = 'Aucune session active';
            }
        } catch (\Exception $e) {
            $this->sessionInfo = 'Erreur session : ' . $e->getMessage();
        }
    }

    public function updatedNiveauId()
    {
        $this->parcours_id = null;
        $this->ec_id = null;
        $this->parcours = collect();
        $this->ecs = collect();

        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('nom')
                ->get();
        }

        $this->resetPage();
    }

    public function updatedParcoursId()
    {
        $this->ec_id = null;
        $this->ecs = collect();

        if ($this->niveau_id && $this->parcours_id) {
            $this->ecs = EC::whereHas('examenEc', function($query) {
                $query->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->niveau_id)
                    ->where('parcours_id', $this->parcours_id);
                });
            })->orderBy('nom')->get();
        }

        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSecretaireId()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function editCopy($id)
    {
        try {
            $this->editingCopy = Copie::find($id);
            
            if (!$this->editingCopy) {
                throw new \Exception('Copie introuvable.');
            }

            $this->edit_code_copie = $this->editingCopy->codeAnonymat->code_complet ?? '';
            $this->edit_note = $this->editingCopy->note;
            $this->showEditModal = true;
        } catch (\Exception $e) {
            toastr()->error('Erreur: ' . $e->getMessage());
        }
    }

    public function updateCopy()
    {
        $this->validate();

        try {
            if (!$this->editingCopy) {
                throw new \Exception('Aucune copie en cours d\'édition.');
            }

            $this->editingCopy->update([
                'note' => $this->edit_note,
                'modifie_par' => Auth::id(), // Enregistre l'ID de l'utilisateur qui modifie
                'updated_at' => now(),
            ]);

            $this->showEditModal = false;
            $this->reset(['editingCopy', 'edit_code_copie', 'edit_note']);
            toastr()->success('Copie modifiée avec succès.');
        } catch (\Exception $e) {
            toastr()->error('Erreur: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        try {
            $this->copyToDelete = Copie::find($id);
            
            if (!$this->copyToDelete) {
                throw new \Exception('Copie introuvable.');
            }

            $this->showDeleteModal = true;
        } catch (\Exception $e) {
            toastr()->error('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteCopy()
    {
        try {
            if (!$this->copyToDelete) {
                throw new \Exception('Aucune copie à supprimer.');
            }

            $this->copyToDelete->delete();
            
            $this->showDeleteModal = false;
            $this->copyToDelete = null;
            toastr()->success('Copie supprimée avec succès.');
        } catch (\Exception $e) {
            toastr()->error('Erreur: ' . $e->getMessage());
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['editingCopy', 'edit_code_copie', 'edit_note']);
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->copyToDelete = null;
    }

    public function resetFilters()
    {
        $this->reset([
            'niveau_id', 'parcours_id', 'ec_id', 
            'secretaire_id', 'search'
        ]);
        
        $this->parcours = collect();
        $this->ecs = collect();
        $this->resetPage();
    }

    public function render()
    {
        $query = Copie::with([
            'examen.niveau', 
            'examen.parcours',
            'ec.ue',
            'utilisateurSaisie',
            'utilisateurModification',
            'sessionExam',
            'codeAnonymat'
        ]);

        // Application des filtres
        if ($this->niveau_id) {
            $query->whereHas('examen', function($q) {
                $q->where('niveau_id', $this->niveau_id);
            });
        }

        if ($this->parcours_id) {
            $query->whereHas('examen', function($q) {
                $q->where('parcours_id', $this->parcours_id);
            });
        }

        if ($this->ec_id) {
            $query->where('ec_id', $this->ec_id);
        }

        if ($this->secretaire_id) {
            $query->where('saisie_par', $this->secretaire_id);
        }

        // Recherche
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('codeAnonymat', function($sq) {
                    $sq->where('code_complet', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Tri
        switch ($this->sortField) {
            case 'code_anonymat':
                $query->join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
                      ->orderBy('codes_anonymat.code_complet', $this->sortDirection)
                      ->select('copies.*');
                break;
            case 'secretaire':
                $query->join('users', 'copies.saisie_par', '=', 'users.id')
                      ->orderBy('users.name', $this->sortDirection)
                      ->select('copies.*');
                break;
            default:
                $query->orderBy($this->sortField, $this->sortDirection);
                break;
        }

        $copies = $query->paginate($this->perPage);

        return view('livewire.copie.copies-index', [
            'copies' => $copies
        ]);
    }
}