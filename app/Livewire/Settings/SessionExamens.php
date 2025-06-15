<?php

namespace App\Livewire\Settings;

use App\Models\SessionExam;
use App\Models\AnneeUniversitaire;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class SessionExamens extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $confirmDelete = false;

    // Propriétés du formulaire
    public $sessionId;
    public $type = 'Normale';
    public $annee_universitaire_id;
    public $date_start;
    public $date_end;
    public $is_active = false;
    public $is_current = false;

    // Propriétés de recherche et filtrage
    public $search = '';
    public $filterType = '';
    public $filterActive = '';
    public $filterAnnee = '';

    protected $rules = [
        'type' => 'required|in:Normale,Rattrapage',
        'annee_universitaire_id' => 'required|exists:annees_universitaires,id',
        'date_start' => 'required|date',
        'date_end' => 'required|date|after:date_start',
        'is_active' => 'boolean',
        'is_current' => 'boolean'
    ];

    protected $messages = [
        'type.required' => 'Le type de session est obligatoire.',
        'type.in' => 'Le type de session doit être Normale ou Rattrapage.',
        'annee_universitaire_id.required' => 'L\'année universitaire est obligatoire.',
        'annee_universitaire_id.exists' => 'L\'année universitaire sélectionnée n\'existe pas.',
        'date_start.required' => 'La date de début est obligatoire.',
        'date_start.date' => 'La date de début doit être une date valide.',
        'date_end.required' => 'La date de fin est obligatoire.',
        'date_end.date' => 'La date de fin doit être une date valide.',
        'date_end.after' => 'La date de fin doit être postérieure à la date de début.',
    ];

    public function mount()
    {
        $this->resetPage();
        // Sélectionner l'année universitaire active par défaut
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        if ($anneeActive) {
            $this->annee_universitaire_id = $anneeActive->id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterActive()
    {
        $this->resetPage();
    }

    public function updatingFilterAnnee()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;

        // Sélectionner l'année universitaire active par défaut
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        if ($anneeActive) {
            $this->annee_universitaire_id = $anneeActive->id;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->sessionId = null;
        $this->type = 'Normale';
        $this->annee_universitaire_id = null;
        $this->date_start = '';
        $this->date_end = '';
        $this->is_active = false;
        $this->is_current = false;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterType = '';
        $this->filterActive = '';
        $this->filterAnnee = '';
        $this->resetPage();
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'type' => $this->type,
                'annee_universitaire_id' => $this->annee_universitaire_id,
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'is_active' => $this->is_active,
                'is_current' => $this->is_current
            ];

            if ($this->editMode) {
                $session = SessionExam::findOrFail($this->sessionId);
                $session->update($data);

                toastr()->success('Session d\'examen modifiée avec succès.');
            } else {
                SessionExam::create($data);

                toastr()->success('Session d\'examen créée avec succès.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de l\'enregistrement.');
        }
    }

    public function edit($id)
    {
        $session = SessionExam::findOrFail($id);

        $this->sessionId = $session->id;
        $this->type = $session->type;
        $this->annee_universitaire_id = $session->annee_universitaire_id;
        $this->date_start = $session->date_start->format('Y-m-d');
        $this->date_end = $session->date_end->format('Y-m-d');
        $this->is_active = $session->is_active;
        $this->is_current = $session->is_current;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function confirmDeleteAction($id)
    {
        $this->sessionId = $id;
        $this->confirmDelete = true;
    }

    public function delete()
    {
        try {
            $session = SessionExam::findOrFail($this->sessionId);

            // Empêcher la suppression de la session courante
            if ($session->is_current) {
                toastr()->error('Impossible de supprimer la session d\'examen courante.');
                $this->confirmDelete = false;
                return;
            }

            $session->delete();
            toastr()->success('Session d\'examen supprimée avec succès.');
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de la suppression.');
        }

        $this->confirmDelete = false;
        $this->sessionId = null;
    }

    public function toggleActive($id)
    {
        try {
            $session = SessionExam::findOrFail($id);

            if ($session->is_active) {
                // Désactiver la session SANS toucher à is_current
                $session->update(['is_active' => false]);
                toastr()->success('Session d\'examen désactivée avec succès.');
            } else {
                // Activer cette session ET désactiver automatiquement les autres du même type

                // 1. Désactiver toutes les autres sessions du même type dans la même année universitaire
                SessionExam::where('annee_universitaire_id', $session->annee_universitaire_id)
                          ->where('type', $session->type)
                          ->where('id', '!=', $session->id)
                          ->update(['is_active' => false]);

                // 2. Activer cette session
                $session->update(['is_active' => true]);

                toastr()->success('Session d\'examen activée avec succès. Les autres sessions du même type ont été désactivées automatiquement.');
            }
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de la modification du statut.');
        }
    }

    public function setCurrent($id)
    {
        try {
            $session = SessionExam::findOrFail($id);

            // Vérifier que la session appartient à l'année universitaire active
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive || $session->annee_universitaire_id !== $anneeActive->id) {
                toastr()->error('Seules les sessions de l\'année universitaire active peuvent être courantes.');
                return;
            }

            // Désactiver seulement les sessions courantes du MÊME TYPE et de la même année universitaire
            SessionExam::where('annee_universitaire_id', $session->annee_universitaire_id)
                      ->where('type', $session->type)
                      ->where('is_current', true)
                      ->where('id', '!=', $session->id)
                      ->update(['is_current' => false]);

            // Définir cette session comme courante
            $session->update(['is_current' => true]);

            toastr()->success('Session d\'examen définie comme courante avec succès.');
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de la définition de la session courante.');
        }
    }

    public function removeCurrent($id)
    {
        try {
            $session = SessionExam::findOrFail($id);
            $session->update(['is_current' => false]);

            toastr()->success('Session n\'est plus courante.');
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue.');
        }
    }

    /**
     * Méthode pour synchroniser les sessions courantes avec l'année universitaire active
     */
    public function syncCurrentSessions()
    {
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();

            if ($anneeActive) {
                // Désactiver toutes les sessions courantes
                SessionExam::where('is_current', true)->update(['is_current' => false]);

                // Activer les sessions de l'année universitaire active
                SessionExam::where('annee_universitaire_id', $anneeActive->id)
                          ->update(['is_current' => true]);

                toastr()->success('Sessions synchronisées avec l\'année universitaire active.');
            }
        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la synchronisation.');
        }
    }

    public function render()
    {
        $query = SessionExam::with('anneeUniversitaire');

        // Filtrage par recherche
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('type', 'like', '%' . $this->search . '%')
                  ->orWhereHas('anneeUniversitaire', function($subQuery) {
                      $subQuery->whereYear('date_start', 'like', '%' . $this->search . '%')
                               ->orWhereYear('date_end', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filtrage par type
        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }

        // Filtrage par statut actif
        if ($this->filterActive !== '') {
            $query->where('is_active', $this->filterActive);
        }

        // Filtrage par année universitaire
        if ($this->filterAnnee !== '') {
            $query->where('annee_universitaire_id', $this->filterAnnee);
        }

        $sessions = $query->orderBy('date_start', 'asc')->paginate(10);
        $anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'asc')->get();

        return view('livewire.settings.session-examens', [
            'sessions' => $sessions,
            'anneesUniversitaires' => $anneesUniversitaires
        ]);
    }
}