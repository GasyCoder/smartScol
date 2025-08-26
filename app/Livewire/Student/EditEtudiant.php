<?php

namespace App\Livewire\Student;

use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EditEtudiant extends Component
{
    // Propriétés du formulaire
    public $etudiant;
    public $etudiant_id;
    public $matricule;
    public $nom;
    public $prenom;
    public $date_naissance;
    public $is_active;

    // IDs pour les relations
    public $niveau_id;
    public $parcours_id;

    // Variables pour l'affichage
    public $niveau;
    public $parcours;
    public $niveaux;
    public $parcoursList = [];

    // Règles de validation
    protected function rules()
    {
        return [
            'matricule' => [
                'required',
                'string',
                'max:20',
                Rule::unique('etudiants', 'matricule')
                    ->ignore($this->etudiant_id)
                    ->whereNull('deleted_at')
            ],
            'nom' => 'required|string|max:50',
            'prenom' => 'nullable|string|max:50',
            'date_naissance' => 'nullable|date_format:d/m/Y|before:today',
            'niveau_id' => 'required|exists:niveaux,id',
            'parcours_id' => 'required|exists:parcours,id',
            'is_active' => 'boolean',
        ];
    }

    // Messages de validation personnalisés
    protected $messages = [
        'matricule.required' => 'Le matricule est obligatoire.',
        'matricule.unique' => 'Ce matricule est déjà utilisé par un autre étudiant.',
        'nom.required' => 'Le nom est obligatoire.',
        'date_naissance.date_format' => 'La date de naissance doit être au format JJ/MM/AAAA.',
        'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
        'niveau_id.required' => 'Le niveau d\'étude est obligatoire.',
        'parcours_id.required' => 'Le parcours est obligatoire.',
    ];

    public function mount($etudiant)
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Accès non autorisé.');
        }
        try {
            // Charger l'étudiant
            $this->etudiant = Etudiant::findOrFail($etudiant);

            // Remplir les propriétés
            $this->etudiant_id = $this->etudiant->id;
            $this->matricule = $this->etudiant->matricule;
            $this->nom = $this->etudiant->nom;
            $this->prenom = $this->etudiant->prenom;
            $this->date_naissance = $this->etudiant->date_naissance;
            $this->is_active = $this->etudiant->is_active;
            $this->niveau_id = $this->etudiant->niveau_id;
            $this->parcours_id = $this->etudiant->parcours_id;

            // Charger les niveaux et parcours
            $this->niveaux = Niveau::where('is_active', true)->get();
            $this->loadParcoursList();

            // Charger les objets niveau et parcours
            $this->niveau = Niveau::find($this->niveau_id);
            $this->parcours = Parcour::find($this->parcours_id);

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de l\'étudiant: ' . $e->getMessage());
            toastr()->error('Étudiant non trouvé.');
            $this->redirect(route('students'));
        }
    }

    /**
     * Chargement des parcours en fonction du niveau sélectionné
     */
    public function loadParcoursList()
    {
        if ($this->niveau_id) {
            $this->parcoursList = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->get();
        } else {
            $this->parcoursList = [];
        }
    }

    /**
     * Mise à jour des parcours quand le niveau change
     */
    public function updatedNiveauId()
    {
        $this->loadParcoursList();
        $this->parcours_id = null; // Réinitialiser le parcours
        $this->niveau = Niveau::find($this->niveau_id);
    }

    /**
     * Mise à jour du parcours quand la sélection change
     */
    public function updatedParcoursId()
    {
        $this->parcours = Parcour::find($this->parcours_id);
    }

    /**
     * Validation à la volée
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Mise à jour de l'étudiant
     */
    public function update()
    {
        $validatedData = $this->validate();

        try {
            $etudiant = Etudiant::findOrFail($this->etudiant_id);
            $etudiant->update($validatedData);
            toastr()->success('Étudiant mis à jour avec succès.');
            return $this->redirect(route('students', [
                'niveau' => $this->niveau_id,
                'parcours' => $this->parcours_id,
                'step' => 'etudiants'
            ]));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'étudiant: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de la mise à jour. Veuillez réessayer.');
        }
    }

    public function render()
    {
        return view('livewire.admin.student.edit-etudiant');
    }
}
