<?php

namespace App\Livewire\Examen;

use App\Models\Salle;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\EC;
use App\Models\UE;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EditExamen extends Component
{
    public $examen;
    public $niveauInfo;
    public $parcoursInfo;
    
    // Propriétés du formulaire
    public $duree;
    public $note_eliminatoire;
    public $ecs_data = [];
    public $ues = [];
    public $salles = [];
    
    // États du formulaire
    public $showConflits = false;
    public $conflits = [];
    public $isSubmitting = false;

    protected $rules = [
        'duree' => 'required|integer|min:30|max:300',
        'note_eliminatoire' => 'nullable|numeric|between:0,20',
        'ecs_data.*.ec_id' => 'required|exists:ecs,id',
        'ecs_data.*.date_specifique' => 'required|date',
        'ecs_data.*.heure_specifique' => 'required|date_format:H:i',
        'ecs_data.*.salle_id' => 'nullable|exists:salles,id',
        'ecs_data.*.code_base' => 'nullable|string|max:10',
    ];

    protected $messages = [
        'duree.required' => 'La durée est obligatoire.',
        'duree.min' => 'La durée minimum est de 30 minutes.',
        'duree.max' => 'La durée maximum est de 5 heures (300 minutes).',
        'note_eliminatoire.between' => 'La note éliminatoire doit être entre 0 et 20.',
        'ecs_data.*.date_specifique.required' => 'La date est obligatoire pour chaque matière.',
        'ecs_data.*.date_specifique.date' => 'La date doit être une date valide.',
        'ecs_data.*.heure_specifique.required' => 'L\'heure est obligatoire pour chaque matière.',
        'ecs_data.*.salle_id.exists' => 'La salle sélectionnée n\'existe pas.',
        'ecs_data.*.code_base.max' => 'Le code ne peut pas dépasser 10 caractères.',
    ];

    public function mount(Examen $examen)
    {
        // Vérification des permissions
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Accès non autorisé.');
        }

        Log::info("🚀 DÉBUT MOUNT EDIT EXAMEN", [
            'examen_id_recu' => $examen->id ?? 'NULL',
            'route_param' => request()->route('examen'),
            'url_actuelle' => request()->url(),
            'user_id' => Auth::id()
        ]);

        $this->examen = $examen->load(['ecs.ue', 'niveau', 'parcours']);
        
        // Charger les informations du niveau et parcours
        $this->niveauInfo = [
            'id' => $this->examen->niveau->id,
            'nom' => $this->examen->niveau->nom,
            'abr' => $this->examen->niveau->abr,
        ];
        
        $this->parcoursInfo = [
            'id' => $this->examen->parcours->id,
            'nom' => $this->examen->parcours->nom,
            'abr' => $this->examen->parcours->abr,
        ];

        // Initialiser les propriétés du formulaire
        $this->duree = $this->examen->duree;
        $this->note_eliminatoire = $this->examen->note_eliminatoire;

        // Charger les données des ECs
        $this->loadEcsData();
        
        // Charger les UEs et salles
        $this->loadUEs();
        $this->loadSalles();

        Log::info("✅ ÉDITION EXAMEN INITIALISÉE", [
            'examen_id' => $this->examen->id,
            'niveau' => $this->niveauInfo['nom'],
            'parcours' => $this->parcoursInfo['nom'],
            'nombre_ecs' => count($this->ecs_data)
        ]);
    }

    private function loadEcsData()
    {
        $this->ecs_data = [];
        
        foreach ($this->examen->ecs as $ec) {
            $this->ecs_data[] = [
                'ec_id' => $ec->id,
                'ec_nom' => $ec->nom,
                'ec_abr' => $ec->abr,
                'ue_nom' => $ec->ue->nom ?? 'UE inconnue',
                'enseignant' => $ec->enseignant,
                'date_specifique' => $ec->pivot->date_specifique ? Carbon::parse($ec->pivot->date_specifique)->format('Y-m-d') : '',
                'heure_specifique' => $ec->pivot->heure_specifique ? Carbon::parse($ec->pivot->heure_specifique)->format('H:i') : '',
                'salle_id' => $ec->pivot->salle_id,
                'code_base' => $ec->pivot->code_base,
            ];
        }
    }

    private function loadUEs()
    {
        $this->ues = UE::where('niveau_id', $this->examen->niveau_id)
                      ->where('parcours_id', $this->examen->parcours_id)
                      ->with('ecs')
                      ->get();
    }

    private function loadSalles()
    {
        // Utiliser le scope pour les salles actives si la colonne existe
        $this->salles = Salle::orderBy('nom')->get();
    }

    public function addEC($ecId)
    {
        $ec = EC::with('ue')->find($ecId);
        
        if (!$ec) {
            toastr()->error('Matière introuvable.');
            return;
        }

        // Vérifier si l'EC n'est pas déjà ajoutée
        $exists = collect($this->ecs_data)->where('ec_id', $ecId)->first();
        if ($exists) {
            toastr()->warning('Cette matière est déjà ajoutée à l\'examen.');
            return;
        }

        $this->ecs_data[] = [
            'ec_id' => $ec->id,
            'ec_nom' => $ec->nom,
            'ec_abr' => $ec->abr,
            'ue_nom' => $ec->ue->nom ?? 'UE inconnue',
            'enseignant' => $ec->enseignant,
            'date_specifique' => '',
            'heure_specifique' => '',
            'salle_id' => '',
            'code_base' => '',
        ];

        toastr()->success("Matière \"{$ec->nom}\" ajoutée à l'examen.");

        Log::info("➕ EC AJOUTÉE", [
            'ec_id' => $ec->id,
            'ec_nom' => $ec->nom,
            'total_ecs' => count($this->ecs_data)
        ]);
    }

    public function removeEC($index)
    {
        if (isset($this->ecs_data[$index])) {
            $ec_nom = $this->ecs_data[$index]['ec_nom'];
            unset($this->ecs_data[$index]);
            $this->ecs_data = array_values($this->ecs_data); // Réindexer

            Log::info("➖ EC SUPPRIMÉE", [
                'ec_nom' => $ec_nom,
                'total_ecs' => count($this->ecs_data)
            ]);

            toastr()->success("Matière \"{$ec_nom}\" supprimée de l'examen.");
        }
    }

    public function verifierConflits()
    {
        $this->validate();

        if (empty($this->ecs_data)) {
            toastr()->error('Vous devez ajouter au moins une matière à l\'examen.');
            return;
        }

        // Préparer les données pour la vérification de conflits
        $ecsDataFormatted = [];
        foreach ($this->ecs_data as $ecData) {
            if (!empty($ecData['date_specifique']) && !empty($ecData['heure_specifique']) && !empty($ecData['salle_id'])) {
                $ecsDataFormatted[] = [
                    'ec_id' => $ecData['ec_id'],
                    'date' => $ecData['date_specifique'], // Utiliser 'date' au lieu de 'date_specifique'
                    'heure' => $ecData['heure_specifique'], // Utiliser 'heure' au lieu de 'heure_specifique'
                    'salle_id' => $ecData['salle_id'],
                ];
            }
        }

        if (empty($ecsDataFormatted)) {
            toastr()->info('Aucune matière avec salle, date et heure définies pour vérifier les conflits.');
            return;
        }

        // Vérifier les conflits de salles
        $conflits = Examen::verifierConflitsSalles($ecsDataFormatted, $this->duree, $this->examen->id);
        
        $this->conflits = $conflits;
        $this->showConflits = true;

        if (empty($conflits)) {
            toastr()->success('Aucun conflit détecté ! Vous pouvez enregistrer l\'examen.');
        } else {
            toastr()->warning(count($conflits) . ' conflit(s) détecté(s). Veuillez vérifier les détails.');
        }

        Log::info("🔍 VÉRIFICATION CONFLITS", [
            'conflits_detectes' => count($conflits),
            'details' => $conflits
        ]);
    }

    public function save()
    {
        if ($this->isSubmitting) return;
        
        $this->isSubmitting = true;

        try {
            $this->validate();

            if (empty($this->ecs_data)) {
                throw new \Exception('Vous devez ajouter au moins une matière à l\'examen.');
            }

            // Vérifier si des dates sont dans le passé (avertissement)
            $datesPasses = [];
            foreach ($this->ecs_data as $ecData) {
                if (!empty($ecData['date_specifique'])) {
                    $dateExamen = \Carbon\Carbon::parse($ecData['date_specifique']);
                    if ($dateExamen->isPast()) {
                        $datesPasses[] = $ecData['ec_nom'] . ' (' . $dateExamen->format('d/m/Y') . ')';
                    }
                }
            }

            if (!empty($datesPasses)) {
                toastr()->warning('Attention : Vous modifiez un examen avec des dates passées : ' . implode(', ', $datesPasses));
                Log::info("⚠️ MODIFICATION EXAMEN PASSÉ", [
                    'examen_id' => $this->examen->id,
                    'dates_passees' => $datesPasses,
                    'user_id' => Auth::id()
                ]);
            }

            // Vérifier les conflits avant sauvegarde
            $ecsDataFormatted = [];
            foreach ($this->ecs_data as $ecData) {
                if (!empty($ecData['date_specifique']) && !empty($ecData['heure_specifique']) && !empty($ecData['salle_id'])) {
                    $ecsDataFormatted[] = [
                        'ec_id' => $ecData['ec_id'],
                        'date' => $ecData['date_specifique'], // Utiliser 'date' au lieu de 'date_specifique'
                        'heure' => $ecData['heure_specifique'], // Utiliser 'heure' au lieu de 'heure_specifique'
                        'salle_id' => $ecData['salle_id'],
                    ];
                }
            }

            $conflits = Examen::verifierConflitsSalles($ecsDataFormatted, $this->duree, $this->examen->id);
            
            if (!empty($conflits)) {
                $this->conflits = $conflits;
                $this->showConflits = true;
                throw new \Exception('Il y a des conflits de salles. Veuillez les résoudre avant de sauvegarder.');
            }

            DB::transaction(function () {
                // Mettre à jour l'examen
                $this->examen->update([
                    'duree' => $this->duree,
                    'note_eliminatoire' => $this->note_eliminatoire,
                ]);

                // Supprimer les anciennes relations EC
                $this->examen->ecs()->detach();

                // Ajouter les nouvelles relations EC
                foreach ($this->ecs_data as $ecData) {
                    $this->examen->ecs()->attach($ecData['ec_id'], [
                        'date_specifique' => $ecData['date_specifique'],
                        'heure_specifique' => $ecData['heure_specifique'],
                        'salle_id' => $ecData['salle_id'] ?: null,
                        'code_base' => $ecData['code_base'] ?: null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                Log::info("✅ EXAMEN MODIFIÉ", [
                    'examen_id' => $this->examen->id,
                    'duree' => $this->duree,
                    'nombre_ecs' => count($this->ecs_data)
                ]);
            });

            toastr()->success('Examen modifié avec succès !');

            // Rediriger vers la liste des examens après un délai
            $this->dispatch('redirect', [
                'url' => route('examens.index'),
                'delay' => 2000
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            toastr()->error('Veuillez corriger les erreurs dans le formulaire.');
        } catch (\Exception $e) {
            Log::error("❌ ERREUR MODIFICATION EXAMEN", [
                'examen_id' => $this->examen->id,
                'error' => $e->getMessage()
            ]);

            toastr()->error($e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function retourIndex()
    {
        return redirect()->route('examens.index');
    }

    public function render()
    {
        return view('livewire.examen.edit-examen', [
            'ues' => $this->ues,
            'salles' => $this->salles,
        ]);
    }
}