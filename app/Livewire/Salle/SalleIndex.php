<?php

namespace App\Livewire\Salle;

use App\Models\Salle;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SalleIndex extends Component
{
    use WithPagination;

    // Variables pour la recherche et le tri
    public $search = '';
    public $sortField = 'nom';
    public $sortDirection = 'asc';

    // Variables pour les modales
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    // Variables pour le formulaire
    public $salle_id;
    public $code_base;
    public $nom;
    public $capacite = 30; // Valeur par défaut

    // Règles de validation pour l'ajout
    protected function rules()
    {
        return [
            'nom' => 'required|string|max:100',
            'code_base' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('salles')->ignore($this->salle_id),
            ],
            'capacite' => 'required|integer|min:1|max:500',
        ];
    }

    // Messages de validation personnalisés
    protected $messages = [
        'nom.required' => 'Le nom de la salle est obligatoire.',
        'code_base.required' => 'Le code de la salle est obligatoire.',
        'code_base.string' => 'Le code de la salle doit être une chaîne de caractères.',
        'capacite.required' => 'La capacité est obligatoire.',
        'capacite.integer' => 'La capacité doit être un nombre entier.',
        'capacite.min' => 'La capacité minimum est de 1 place.',
        'capacite.max' => 'La capacité maximum est de 500 places.',
    ];

    /**
     * Méthode pour le tri des colonnes
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Ouvre la modale d'ajout
     */
    public function openAddModal()
    {
        $this->resetValidation();
        $this->reset([
            'code_base', 
            'nom', 
            'capacite', 
            'salle_id'
        ]);
        $this->capacite = 30; // Valeur par défaut
        $this->showAddModal = true;
    }

    /**
     * Ferme la modale d'ajout
     */
    public function closeAddModal()
    {
        $this->showAddModal = false;
    }


    /**
     * Sauvegarde la nouvelle salle
     */
    public function saveSalle()
    {
        $validatedData = $this->validate();

        try {
            Salle::create($validatedData);

            $this->reset([
                'code_base',
                'nom', 
                'capacite'
            ]);
            $this->showAddModal = false;
            $this->dispatch('salle-saved');
            toastr()->success('Salle ajoutée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'ajout de la salle: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de l\'enregistrement.');
        }
    }

    /**
     * Ouvre la modale de modification
     */
    public function openEditModal($salleId)
    {
        $this->resetValidation();
        $this->salle_id = $salleId;

        $salle = Salle::find($salleId);
        if ($salle) {
            $this->code_base = $salle->code_base;
            $this->nom = $salle->nom;
            $this->capacite = $salle->capacite;
        }

        $this->showEditModal = true;
    }

    /**
     * Ferme la modale de modification
     */
    public function closeEditModal()
    {
        $this->showEditModal = false;
    }

    /**
     * Met à jour la salle
     */
    public function updateSalle()
    {
        $validatedData = $this->validate();

        try {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $salle->update($validatedData);

                $this->showEditModal = false;
                $this->dispatch('salle-updated');
                toastr()->success('Salle mise à jour avec succès.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la salle: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de la mise à jour.');
        }
    }

    /**
     * Ouvre la modale de confirmation de suppression
     */
    public function confirmDelete($salleId)
    {
        $this->salle_id = $salleId;
        $this->showDeleteModal = true;
    }

    /**
     * Ferme la modale de confirmation de suppression
     */
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    /**
     * Supprime une salle
     */
    public function deleteSalle()
    {
        try {
            $salle = Salle::findOrFail($this->salle_id);

            // Vérifier si la salle est utilisée
            if ($salle->placements()->count() > 0) {
                toastr()->error('Cette salle ne peut pas être supprimée car elle est utilisée dans des placements.');
                $this->showDeleteModal = false;
                return;
            }

            $salle->delete();
            toastr()->success('Salle supprimée avec succès.');
            $this->showDeleteModal = false;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la salle: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de la suppression.');
            $this->showDeleteModal = false;
        }
    }

    /**
     * Hook de mise à jour pour la validation à la volée
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Rend la vue avec les données nécessaires
     */
    public function render()
    {
        $salles = Salle::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nom', 'like', '%' . $this->search . '%')
                        ->orWhere('code_base', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.salle.salle-index', [
            'salles' => $salles
        ]);
    }
}
