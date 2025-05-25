<?php

namespace App\Livewire\UEEC;

use App\Models\EC;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\UE;
use App\Models\User;
use Livewire\Component;

class EditUnite extends Component
{
    public $ue_id;
    public $niveau_id;
    public $parcours_id;
    public $ueAbr = '';
    public $ueNom = '';
    public $ueCredits = 0; // Nouveau champ pour les crédits

    // Objets complets
    public $ue;
    public $niveau;
    public $parcours;

    // Tableaux pour les EC existants et nouveaux
    public $existingEcs = [];
    public $newEcs = [];
    public $removedEcIds = [];

    // Règles de validation pour UE
    protected $rules = [
        'ueAbr' => 'required|max:10',
        'ueNom' => 'required|max:100',
        'ueCredits' => 'required|numeric|min:0', // Nouvelle règle pour les crédits
    ];

    // Règles de validation pour les EC
    protected $ecRules = [
        'existingEcs.*.abr' => 'required|max:10',
        'existingEcs.*.nom' => 'required|max:100',
        'existingEcs.*.enseignant' => 'required|max:100',
        'existingEcs.*.coefficient' => 'nullable|numeric|min:0.1|max:10', // Règle pour coefficient
        'newEcs.*.abr' => 'required|max:10',
        'newEcs.*.nom' => 'required|max:100',
        'newEcs.*.enseignant' => 'required|max:100',
        'newEcs.*.coefficient' => 'nullable|numeric|min:0.1|max:10', // Règle pour coefficient
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
        'existingEcs.*.abr.required' => 'L\'abréviation de l\'EC est obligatoire',
        'existingEcs.*.abr.max' => 'L\'abréviation de l\'EC ne doit pas dépasser 10 caractères',
        'existingEcs.*.nom.required' => 'Le nom de l\'EC est obligatoire',
        'existingEcs.*.nom.max' => 'Le nom de l\'EC ne doit pas dépasser 100 caractères',
        'existingEcs.*.enseignant.required' => 'L\'enseignant est obligatoire',
        'existingEcs.*.enseignant.exists' => 'L\'enseignant sélectionné n\'existe pas',
        'existingEcs.*.coefficient.numeric' => 'Le coefficient doit être un nombre',
        'existingEcs.*.coefficient.min' => 'Le coefficient minimal est 0.1',
        'existingEcs.*.coefficient.max' => 'Le coefficient maximal est 10',
        'newEcs.*.abr.required' => 'L\'abréviation de l\'EC est obligatoire',
        'newEcs.*.abr.max' => 'L\'abréviation de l\'EC ne doit pas dépasser 10 caractères',
        'newEcs.*.nom.required' => 'Le nom de l\'EC est obligatoire',
        'newEcs.*.nom.max' => 'Le nom de l\'EC ne doit pas dépasser 100 caractères',
        'newEcs.*.enseignant.required' => 'L\'enseignant est obligatoire',
        'newEcs.*.enseignant.exists' => 'L\'enseignant sélectionné n\'existe pas',
        'newEcs.*.coefficient.numeric' => 'Le coefficient doit être un nombre',
        'newEcs.*.coefficient.min' => 'Le coefficient minimal est 0.1',
        'newEcs.*.coefficient.max' => 'Le coefficient maximal est 10',
    ];

    public function mount($ue)
    {
        $this->ue_id = $ue;
        $this->ue = UE::with('ecs')->findOrFail($this->ue_id);

        // Charger les données de l'UE
        $this->ueAbr = $this->ue->abr;
        $this->ueNom = $this->ue->nom;
        $this->ueCredits = $this->ue->credits ?? 0; // Charger les crédits existants
        $this->niveau_id = $this->ue->niveau_id;
        $this->parcours_id = $this->ue->parcours_id;

        // Charger les données du niveau et du parcours
        $this->niveau = Niveau::findOrFail($this->niveau_id);
        $this->parcours = Parcour::findOrFail($this->parcours_id);

        // Charger les EC existants
        foreach ($this->ue->ecs as $ec) {
            $this->existingEcs[] = [
                'id' => $ec->id,
                'abr' => $ec->abr,
                'nom' => $ec->nom,
                'coefficient' => $ec->coefficient,
                'enseignant' => $ec->enseignant,
            ];
        }

        // Si aucun EC existant, ajouter un champ vide pour nouveau EC
        if (count($this->existingEcs) === 0) {
            $this->addNewEC();
        }
    }

    // Ajouter un nouveau champ EC
    public function addNewEC()
    {
        $this->newEcs[] = [
            'abr' => '',
            'nom' => '',
            'coefficient' => '1.00',
            'enseignant' => '',
        ];
    }

    // Supprimer un EC existant (marquer pour suppression)
    public function removeExistingEC($index)
    {
        if (isset($this->existingEcs[$index]['id'])) {
            $this->removedEcIds[] = $this->existingEcs[$index]['id'];
        }

        unset($this->existingEcs[$index]);
        $this->existingEcs = array_values($this->existingEcs); // Réindexer le tableau

        // Si plus aucun EC existant ou nouveau, ajouter un champ vide
        if (count($this->existingEcs) === 0 && count($this->newEcs) === 0) {
            $this->addNewEC();
        }
    }

    // Supprimer un nouveau EC
    public function removeNewEC($index)
    {
        unset($this->newEcs[$index]);
        $this->newEcs = array_values($this->newEcs); // Réindexer le tableau

        // Si plus aucun EC existant ou nouveau, ajouter un champ vide
        if (count($this->existingEcs) === 0 && count($this->newEcs) === 0) {
            $this->addNewEC();
        }
    }

    // Mettre à jour l'UE et ses EC
    public function save()
    {
        // Valider l'UE
        $this->validate($this->rules);

        // Vérifier si l'abréviation a changé et si elle existe déjà pour ce niveau et parcours
        if ($this->ueAbr !== $this->ue->abr) {
            $existingUE = UE::where('abr', $this->ueAbr)
                ->where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->where('id', '!=', $this->ue_id)
                ->first();

            if ($existingUE) {
                $this->addError('ueAbr', 'Une UE avec cette abréviation existe déjà pour ce niveau et parcours');
                return;
            }
        }

        // Valider les EC si présents
        if (count($this->existingEcs) > 0 || count($this->newEcs) > 0) {
            $this->validate($this->ecRules);
        }

        // Mettre à jour l'UE
        $this->ue->update([
            'abr' => $this->ueAbr,
            'nom' => $this->ueNom,
            'credits' => $this->ueCredits, // Mise à jour des crédits
        ]);

        // Supprimer les EC marqués pour suppression
        if (!empty($this->removedEcIds)) {
            EC::whereIn('id', $this->removedEcIds)->delete();
        }

        // Mettre à jour les EC existants
        foreach ($this->existingEcs as $ecData) {
            EC::find($ecData['id'])->update([
                'abr' => $ecData['abr'],
                'nom' => $ecData['nom'],
                'coefficient' => $ecData['coefficient'],
                'enseignant' => $ecData['enseignant'],
            ]);
        }

        // Ajouter les nouveaux EC
        foreach ($this->newEcs as $ecData) {
            // Ne créer que si au moins l'abréviation et le nom sont remplis
            if (!empty($ecData['abr']) && !empty($ecData['nom']) && !empty($ecData['enseignant'])) {
                EC::create([
                    'abr' => $ecData['abr'],
                    'nom' => $ecData['nom'],
                    'coefficient' => $ecData['coefficient'] ?? 1.00,
                    'ue_id' => $this->ue_id,
                    'enseignant' => $ecData['enseignant'],
                ]);
            }
        }

        // Message de succès
        toastr()->success('L\'unité d\'enseignement et ses éléments constitutifs ont été mis à jour avec succès');

        // Redirection vers la liste des UE avec les paramètres de query string
        return redirect()->route('unite_e', [
            'step' => 'ue',
            'niveau' => $this->niveau_id,  // Notez que c'est 'niveau' et non 'niveauId'
            'parcours' => $this->parcours_id  // Notez que c'est 'parcours' et non 'parcoursId'
        ]);
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
        return view('livewire.admin.ue.edit-unite', [
            'niveau' => $this->niveau,
            'parcours' => $this->parcours,
        ]);
    }
}
