<?php

namespace App\Livewire\UEEC;

use App\Models\EC;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\UE;
use App\Models\User;
use Livewire\Component;

class AddUnite extends Component
{
    public $niveau_id;
    public $parcours_id;
    public $niveau;
    public $parcours;

    // Propriétés pour les champs du formulaire UE
    public $ueAbr = '';
    public $ueNom = '';
    public $ueCredits = 0; // Nouveau champ pour les crédits

    // Propriétés pour les EC
    public $ecs = [];

    // Règles de validation pour UE
    protected $rules = [
        'ueAbr' => 'required|max:10',
        'ueNom' => 'required|max:100',
        'ueCredits' => 'required|numeric|min:0', // Nouvelle règle pour les crédits
    ];

    // Règles de validation pour les EC
    protected $ecRules = [
        'ecs.*.abr' => 'required|max:10',
        'ecs.*.nom' => 'required|max:100',
        'ecs.*.enseignant' => 'required|max:100',
        'ecs.*.coefficient' => 'nullable|numeric|min:0.1|max:10', // Optionnel: règle pour le coefficient
    ];

    // Messages de validation
    protected $messages = [
        'ueAbr.required' => 'L\'abréviation est obligatoire',
        'ueAbr.max' => 'L\'abréviation ne doit pas dépasser 10 caractères',
        'ueNom.required' => 'Le nom est obligatoire',
        'ueNom.max' => 'Le nom ne doit pas dépasser 100 caractères',
        'ueCredits.required' => 'Le nombre de crédits est obligatoire',
        'ueCredits.numeric' => 'Le nombre de crédits doit être un nombre',
        'ueCredits.min' => 'Le nombre de crédits doit être positif ou nul',
        'ecs.*.abr.required' => 'L\'abréviation de l\'EC est obligatoire',
        'ecs.*.abr.max' => 'L\'abréviation de l\'EC ne doit pas dépasser 10 caractères',
        'ecs.*.nom.required' => 'Le nom de l\'EC est obligatoire',
        'ecs.*.nom.max' => 'Le nom de l\'EC ne doit pas dépasser 100 caractères',
        'ecs.*.enseignant.required' => 'L\'enseignant est obligatoire',
        'ecs.*.enseignant.exists' => 'L\'enseignant sélectionné n\'existe pas',
        'ecs.*.coefficient.numeric' => 'Le coefficient doit être un nombre',
        'ecs.*.coefficient.min' => 'Le coefficient minimal est 0.1',
        'ecs.*.coefficient.max' => 'Le coefficient maximal est 10',
    ];

    public function mount($niveau, $parcour)
    {
        $this->niveau_id = $niveau;
        $this->parcours_id = $parcour;

        $this->niveau = Niveau::findOrFail($this->niveau_id);
        $this->parcours = Parcour::findOrFail($this->parcours_id);

        // Initialiser avec 1 champ EC vide
        $this->addEC();
    }

    // Ajouter un nouveau champ EC
    public function addEC()
    {
        $this->ecs[] = [
            'abr' => '',
            'nom' => '',
            'enseignant' => '',
            'coefficient' => 1.0, // Valeur par défaut pour le coefficient
        ];
    }

    // Supprimer un champ EC
    public function removeEC($index)
    {
        if (count($this->ecs) > 1) {
            unset($this->ecs[$index]);
            $this->ecs = array_values($this->ecs); // Réindexer le tableau
        }
    }

    // Sauvegarder l'UE et les EC
    public function save()
    {
        // Ne valider que les champs de l'UE
        $this->validate($this->rules);

        // Créer l'UE et ses EC
        $this->createUE(true);
    }

    // Sauvegarder l'UE seulement
    public function saveUEOnly()
    {
        // Ne valider que les champs de l'UE
        $this->validate($this->rules);

        // Créer l'UE sans les EC
        $this->createUE(false);
    }

    // Méthode commune pour créer l'UE
    protected function createUE($withEC = true)
    {
        // Vérifier si une UE avec la même abréviation existe déjà pour ce niveau et parcours
        $existingUE = UE::where('abr', $this->ueAbr)
            ->where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->first();

        if ($existingUE) {
            $this->addError('ueAbr', 'Une UE avec cette abréviation existe déjà pour ce niveau et parcours');
            return;
        }

        // Créer la nouvelle UE avec le champ credits
        $ue = UE::create([
            'abr' => $this->ueAbr,
            'nom' => $this->ueNom,
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'credits' => $this->ueCredits, // Ajout du nouveau champ
        ]);

        // Si on doit créer les EC associés
        if ($withEC) {
            // Valider les EC
            $this->validate($this->ecRules);

            // Créer les EC associés
            foreach ($this->ecs as $ec) {
                EC::create([
                    'abr' => $ec['abr'],
                    'nom' => $ec['nom'],
                    'enseignant' => $ec['enseignant'],
                    'coefficient' => $ec['coefficient'] ?? 1.0, // Utiliser le coefficient si présent
                    'ue_id' => $ue->id,
                ]);
            }
            toastr()->success('L\'unité d\'enseignement et ses éléments constitutifs ont été ajoutés avec succès');
            return redirect()->route('add_ue', [
                'niveau' => $this->niveau_id,
                'parcour' => $this->parcours_id
            ]);
        } else {
            // Message de succès spécifique
            toastr()->error('L\'unité d\'enseignement a été ajoutée avec succès (sans éléments constitutifs)');
        }

        // Réinitialiser le formulaire pour permettre l'ajout d'une autre UE
        $this->reset(['ueAbr', 'ueNom', 'ueCredits']); // Réinitialiser aussi les crédits

        // Réinitialiser les EC
        $this->ecs = [];
        $this->addEC();
    }

    public function cancel()
    {
        return redirect()->route('unite_e', [
            'niveau' => $this->niveau_id,
            'parcours' => $this->parcours_id,
            'step' => 'ue'
        ]);
    }

    public function render()
    {
        return view('livewire.admin.ue.add-unite', [
            'niveau' => $this->niveau,
            'parcours' => $this->parcours,
        ]);
    }
}
