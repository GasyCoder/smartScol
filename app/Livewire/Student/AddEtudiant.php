<?php

namespace App\Livewire\Student;

use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AddEtudiant extends Component
{
    // Propriétés du formulaire
    public $matricule;
    public $nom;
    public $prenom;
    public $date_naissance;
    public $is_active = true;

    // IDs passés via les paramètres
    public $niveau_id;
    public $parcours_id;

    // Variables pour l'affichage
    public $niveau;
    public $parcours;

    // Règles de validation
    protected function rules()
    {
        return [
            'matricule' => [
                'required',
                'string',
                'max:20',
                Rule::unique('etudiants', 'matricule')->whereNull('deleted_at')
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
        'matricule.unique' => 'Ce matricule est déjà utilisé.',
        'nom.required' => 'Le nom est obligatoire.',
        'date_naissance.date_format' => 'La date de naissance doit être au format JJ/MM/AAAA.',
        'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
    ];

    // Définir les attributs pour les messages
    protected $validationAttributes = [
        'matricule' => 'matricule',
        'nom' => 'nom',
        'prenom' => 'prénom',
        'date_naissance' => 'date de naissance',
        'is_active' => 'statut',
    ];

    public function mount($niveau, $parcour)
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Accès non autorisé.');
        }
        $this->niveau_id = $niveau;
        $this->parcours_id = $parcour;

        try {
            // Charger les informations du niveau et du parcours
            $this->niveau = Niveau::findOrFail($niveau);
            $this->parcours = Parcour::findOrFail($parcour);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du niveau ou du parcours: ' . $e->getMessage());
            session()->flash('error', 'Niveau ou parcours non trouvé.');
            $this->redirect(route('students'));
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function save()
    {
        $validatedData = $this->validate();

        try {
            $student = Etudiant::create($validatedData);
            toastr()->success('Étudiant ajouté avec succès.');
            return redirect()->route('add_etudiant', [
                'niveau' => $this->niveau_id,
                'parcour' => $this->parcours_id
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'ajout de l\'étudiant: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.');
        }


    }

    public function render()
    {
        return view('livewire.admin.student.add-etudiant');
    }
}