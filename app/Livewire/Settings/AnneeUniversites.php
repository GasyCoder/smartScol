<?php

namespace App\Livewire\Settings;

use App\Models\AnneeUniversitaire;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class AnneeUniversites extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $confirmDelete = false;

    // Propriétés du formulaire
    public $anneeId;
    public $date_start;
    public $date_end;
    public $is_active = false;

    // Propriétés de recherche et filtrage
    public $search = '';
    public $filterActive = '';

    protected $rules = [
        'date_start' => 'required|date',
        'date_end' => 'required|date|after:date_start',
        'is_active' => 'boolean'
    ];

    protected $messages = [
        'date_start.required' => 'La date de début est obligatoire.',
        'date_start.date' => 'La date de début doit être une date valide.',
        'date_end.required' => 'La date de fin est obligatoire.',
        'date_end.date' => 'La date de fin doit être une date valide.',
        'date_end.after' => 'La date de fin doit être postérieure à la date de début.',
    ];

    public function mount()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterActive()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->anneeId = null;
        $this->date_start = '';
        $this->date_end = '';
        $this->is_active = false;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterActive = '';
        $this->resetPage();
    }

    public function save()
    {
        $this->validate();

        try {
            // Si on active cette année, désactiver toutes les autres
            if ($this->is_active) {
                AnneeUniversitaire::where('is_active', true)->update(['is_active' => false]);
            }

            if ($this->editMode) {
                $annee = AnneeUniversitaire::findOrFail($this->anneeId);
                $annee->update([
                    'date_start' => $this->date_start,
                    'date_end' => $this->date_end,
                    'is_active' => $this->is_active
                ]);

                toastr()->success('Année universitaire modifiée avec succès.');
            } else {
                AnneeUniversitaire::create([
                    'date_start' => $this->date_start,
                    'date_end' => $this->date_end,
                    'is_active' => $this->is_active
                ]);

                toastr()->success('Année universitaire créée avec succès.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de l\'enregistrement.');
        }
    }

    public function edit($id)
    {
        $annee = AnneeUniversitaire::findOrFail($id);

        $this->anneeId = $annee->id;
        $this->date_start = $annee->date_start->format('Y-m-d');
        $this->date_end = $annee->date_end->format('Y-m-d');
        $this->is_active = $annee->is_active;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function confirmDeleteAction($id)
    {
        $this->anneeId = $id;
        $this->confirmDelete = true;
    }

    public function delete()
    {
        try {
            $annee = AnneeUniversitaire::findOrFail($this->anneeId);

            // Empêcher la suppression de l'année active
            if ($annee->is_active) {
                toastr()->error('Impossible de supprimer l\'année universitaire active.');
                $this->confirmDelete = false;
                return;
            }

            $annee->delete();
            toastr()->success('Année universitaire supprimée avec succès.');
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de la suppression.');
        }

        $this->confirmDelete = false;
        $this->anneeId = null;
    }

    public function toggleActive($id)
    {
        try {
            // Désactiver toutes les années
            AnneeUniversitaire::where('is_active', true)->update(['is_active' => false]);

            // Activer l'année sélectionnée
            AnneeUniversitaire::findOrFail($id)->update(['is_active' => true]);

            toastr()->success('Année universitaire activée avec succès.');
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de l\'activation.');
        }
    }

    public function render()
    {
        $query = AnneeUniversitaire::query();

        // Filtrage par recherche
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereYear('date_start', 'like', '%' . $this->search . '%')
                  ->orWhereYear('date_end', 'like', '%' . $this->search . '%');
            });
        }

        // Filtrage par statut actif
        if ($this->filterActive !== '') {
            $query->where('is_active', $this->filterActive);
        }

        $annees = $query->orderBy('date_start', 'asc')->paginate(10);

        return view('livewire.settings.annee-universites', [
            'annees' => $annees
        ]);
    }
}