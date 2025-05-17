<?php

namespace App\Livewire\Copie;

use App\Models\CodeAnonymat;
use App\Models\Copie;
use App\Models\EC;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Salle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 */
class CopiesIndex extends Component
{
    use WithPagination;

    // Variables de filtrage et contexte
    public $niveau_id;
    public $parcours_id;
    public $salle_id;  // Pour sélectionner la salle d'examen
    public $ec_id;
    public $examen_id; // Maintenu pour la relation avec les copies

    // Liste des données pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $salles = [];
    public $ecs = [];

    // Variables pour la modale de saisie
    public $showCopieModal = false;
    public $code_anonymat = '';
    public $note = '';
    public $editingCopieId = null;
    public $selectedSalleCode = '';

    // Messages de statut
    public $message = '';
    public $messageType = '';

    public $search = '';
    public $showDeleteModal = false;
    public $copieToDelete = null;

    // Ajoutez cette méthode
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Règles de validation pour la modale
    protected $rules = [
        'code_anonymat' => 'required|string|max:20',
        'note' => 'required|numeric|min:0|max:20',
        'ec_id' => 'required|exists:ecs,id',
    ];

    // Pour stocker les filtres en session
    protected function storeFiltres()
    {
        session()->put('copies.filtres', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'salle_id' => $this->salle_id,
            'ec_id' => $this->ec_id,
            'examen_id' => $this->examen_id,
        ]);
    }

    // Pour récupérer les filtres stockés
    protected function loadFiltres()
    {
        $filtres = session()->get('copies.filtres', []);

        if (isset($filtres['niveau_id'])) {
            $this->niveau_id = $filtres['niveau_id'];
            $this->updatedNiveauId();

            if (isset($filtres['parcours_id'])) {
                $this->parcours_id = $filtres['parcours_id'];
                $this->updatedParcoursId();

                if (isset($filtres['salle_id'])) {
                    $this->salle_id = $filtres['salle_id'];
                    $this->updatedSalleId();

                    if (isset($filtres['ec_id'])) {
                        $this->ec_id = $filtres['ec_id'];
                        $this->updatedEcId();
                    }
                }
            }
        }
    }

    // Pour réinitialiser les filtres
    public function resetFiltres()
    {
        $this->reset(['niveau_id', 'parcours_id', 'salle_id', 'ec_id', 'examen_id', 'selectedSalleCode']);
        session()->forget('copies.filtres');

        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();

        $this->resetPage();
    }

    public function mount()
    {
        // Charger les niveaux
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();

        // Initialiser avec des collections vides
        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();

        // Charger les filtres enregistrés
        $this->loadFiltres();
    }

    public function updatedNiveauId()
    {
        // Réinitialiser les dépendances
        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();
        $this->parcours_id = null;
        $this->salle_id = null;
        $this->ec_id = null;
        $this->examen_id = null;
        $this->selectedSalleCode = '';

        // Charger les parcours pour ce niveau
        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();

            // S'il n'y a qu'un seul parcours, le sélectionner automatiquement
            if ($this->parcours->count() == 1) {
                $this->parcours_id = $this->parcours->first()->id;
                $this->updatedParcoursId();
            }
        }

        $this->storeFiltres();
        $this->resetPage();
    }

    public function updatedParcoursId()
    {
        // Réinitialiser les dépendances
        $this->salles = collect();
        $this->ecs = collect();
        $this->salle_id = null;
        $this->ec_id = null;
        $this->examen_id = null;
        $this->selectedSalleCode = '';

        if ($this->niveau_id && $this->parcours_id) {
            // Charger toutes les salles qui ont des examens pour ce niveau et parcours
            $this->salles = DB::table('salles')
                ->join('examen_ec', 'salles.id', '=', 'examen_ec.salle_id')
                ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->whereNull('examens.deleted_at')
                ->select('salles.*')
                ->distinct()
                ->orderBy('id', 'desc')
                ->get();

            // S'il n'y a qu'une seule salle, la sélectionner automatiquement
            if ($this->salles->count() == 1) {
                $this->salle_id = $this->salles->first()->id;
                $this->updatedSalleId();
            }
        }

        $this->storeFiltres();
        $this->resetPage();
    }


    public function updatedSalleId()
    {
        $this->ecs = collect();
        $this->ec_id = null;
        $this->examen_id = null;
        $this->selectedSalleCode = '';

        if ($this->salle_id) {
            // Récupérer le code de la salle
            $salle = DB::table('salles')->where('id', $this->salle_id)->first();
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base ?? '';
            }

            // Récupérer l'ID de l'examen
            $examens = DB::table('examens')
                ->join('examen_ec', 'examens.id', '=', 'examen_ec.examen_id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->where('examen_ec.salle_id', $this->salle_id)
                ->select('examens.id')
                ->distinct()
                ->get()
                ->pluck('id');

            if ($examens->count() > 0) {
                $this->examen_id = $examens->first();

                // Récupérer les matières
                $ecData = DB::table('ecs')
                    ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                    ->where('examen_ec.examen_id', $this->examen_id)
                    ->where('examen_ec.salle_id', $this->salle_id)
                    ->whereNull('ecs.deleted_at')
                    ->select(
                        'ecs.*',
                        'examen_ec.examen_id'
                    )
                    ->distinct()
                    ->get();

                // Compter les copies pour chaque matière
                $ecIds = $ecData->pluck('id')->toArray();

                // Compter le total des copies par EC
                $copiesCounts = DB::table('copies')
                    ->where('examen_id', $this->examen_id)
                    ->whereIn('ec_id', $ecIds)
                    ->select('ec_id', DB::raw('count(*) as total'))
                    ->groupBy('ec_id')
                    ->pluck('total', 'ec_id')
                    ->toArray();

                // Compter les copies saisies par l'utilisateur actuel pour chaque EC
                $currentUserCopiesCounts = DB::table('copies')
                    ->where('examen_id', $this->examen_id)
                    ->whereIn('ec_id', $ecIds)
                    ->where('saisie_par', Auth::id())
                    ->select('ec_id', DB::raw('count(*) as total'))
                    ->groupBy('ec_id')
                    ->pluck('total', 'ec_id')
                    ->toArray();

                // Transformer en collection d'objets
                $this->ecs = $ecData->map(function($item) use ($copiesCounts, $currentUserCopiesCounts) {
                    $ec = new \stdClass();
                    foreach ((array)$item as $key => $value) {
                        $ec->$key = $value;
                    }
                    $ec->has_copies = isset($copiesCounts[$ec->id]) && $copiesCounts[$ec->id] > 0;
                    $ec->copies_count = $copiesCounts[$ec->id] ?? 0;
                    $ec->user_copies_count = $currentUserCopiesCounts[$ec->id] ?? 0;
                    return $ec;
                });
            }
        }

        $this->storeFiltres();
        $this->resetPage();
    }

    public function updatedEcId()
    {
        // Récupérer l'ID de l'examen correspondant à cet EC et cette salle
        if ($this->ec_id && $this->salle_id) {
            $examenEc = DB::table('examen_ec')
                ->where('ec_id', $this->ec_id)
                ->where('salle_id', $this->salle_id)
                ->first();

            if ($examenEc) {
                $this->examen_id = $examenEc->examen_id;
            }
        }

        // Effacer tout message précédent lors du changement d'EC
        $this->message = '';

        $this->storeFiltres();
        $this->resetPage();
    }

    public function openCopieModal()
    {
        // Vérifier que le contexte est complet
        if (!$this->examen_id || !$this->ec_id || !$this->salle_id) {
            $this->message = 'Veuillez sélectionner une salle et une matière';
            $this->messageType = 'error';
            return;
        }

        // S'assurer que le code de salle est défini
        if (empty($this->selectedSalleCode)) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base;
            }
        }

        // Récupérer le compteur de copies pour la matière sélectionnée
        $currentEc = null;
        foreach ($this->ecs as $ec) {
            if (property_exists($ec, 'id') && $ec->id == $this->ec_id) {
                $currentEc = $ec;
                break;
            }
        }

        // Suggérer un numéro pour le code d'anonymat basé sur le compteur actuel
        $nextNumber = ($currentEc && isset($currentEc->copies_count)) ? $currentEc->copies_count + 1 : 1;

        // Trouver un code d'anonymat non utilisé pour cette matière
        $baseCode = $this->selectedSalleCode;
        $proposedCode = $baseCode . $nextNumber;

        // Vérifier si ce code est déjà utilisé pour cette matière en utilisant une requête avec les relations
        while (Copie::whereHas('codeAnonymat', function($query) use ($proposedCode) {
            $query->where('code_complet', $proposedCode);
        })
        ->where('examen_id', $this->examen_id)
        ->where('ec_id', $this->ec_id)
        ->exists()) {
            $nextNumber++;
            $proposedCode = $baseCode . $nextNumber;
        }

        // Préinitialiser le code d'anonymat avec le code unique
        $this->code_anonymat = $proposedCode;
        $this->note = '';

        // Ouvrir la modale
        $this->showCopieModal = true;
    }


    public function saveCopie()
    {
        $this->validate();

        try {
            // Vérifier si l'examen existe
            $examen = Examen::find($this->examen_id);
            if (!$examen) {
                throw new \Exception("L'examen sélectionné n'existe pas.");
            }

            // Vérifier si l'EC est bien associé à l'examen
            $ecBelongsToExamen = DB::table('examen_ec')
                ->where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('salle_id', $this->salle_id)
                ->exists();

            if (!$ecBelongsToExamen) {
                throw new \Exception("La matière sélectionnée n'est pas associée à cet examen et cette salle.");
            }

            // Rechercher ou créer le code d'anonymat
            $codeAnonymat = CodeAnonymat::firstOrCreate(
                [
                    'examen_id' => $this->examen_id,
                    'code_complet' => $this->code_anonymat,
                ],
                [
                    'sequence' => null, // Sera extrait automatiquement dans le modèle
                ]
            );

            // Vérifier si une copie supprimée existe avec ce code et cette matière
            $existingDeletedCopie = Copie::withTrashed()
                ->where('examen_id', $this->examen_id)
                ->where('code_anonymat_id', $codeAnonymat->id)
                ->where('ec_id', $this->ec_id)
                ->whereNotNull('deleted_at')
                ->first();

            if ($existingDeletedCopie) {
                // Restaurer et mettre à jour la copie supprimée
                $existingDeletedCopie->restore();
                $existingDeletedCopie->update([
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                ]);
                $this->message = 'Note restaurée et mise à jour avec succès';
            } else if (isset($this->editingCopieId)) {
                // Mode édition
                $copie = Copie::find($this->editingCopieId);

                if (!$copie) {
                    throw new \Exception('La copie à modifier est introuvable.');
                }

                // Mise à jour de la copie existante
                $copie->update([
                    'code_anonymat_id' => $codeAnonymat->id,
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                ]);

                $this->message = 'Copie modifiée avec succès';
            } else {
                // Vérifier si une copie existe déjà pour ce code et cette matière (non supprimée)
                $existingCopie = Copie::where('examen_id', $this->examen_id)
                    ->where('code_anonymat_id', $codeAnonymat->id)
                    ->where('ec_id', $this->ec_id)
                    ->first();

                if ($existingCopie) {
                    throw new \Exception("Ce code d'anonymat est déjà utilisé pour cette matière. Veuillez utiliser un autre code.");
                }

                // Mode création - Créer une nouvelle copie
                Copie::create([
                    'examen_id' => $this->examen_id,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'ec_id' => $this->ec_id,
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                ]);

                $this->message = 'Note enregistrée avec succès';
            }

            $this->messageType = 'success';

            // Fermer la modale et réinitialiser les champs
            $this->reset(['code_anonymat', 'note', 'editingCopieId']);
            $this->showCopieModal = false;

            // Mettre à jour les compteurs dans l'interface
            if ($this->ec_id) {
                foreach ($this->ecs as $index => $ec) {
                    if (property_exists($ec, 'id') && $ec->id == $this->ec_id) {
                        $this->ecs[$index]->has_copies = true;
                        $this->ecs[$index]->copies_count = ($this->ecs[$index]->copies_count ?? 0) + 1;
                        $this->ecs[$index]->user_copies_count = ($this->ecs[$index]->user_copies_count ?? 0) + 1;
                        break;
                    }
                }
            }

            // Notification
            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: '.$e->getMessage();
            $this->messageType = 'error';

            toastr()->error($this->message);
        }
    }

    /**
     * Prépare l'édition d'une copie
     */
    public function editCopie($id)
    {
        $copie = Copie::with('codeAnonymat')->find($id);

        if (! $copie) {
            $this->message = 'Copie introuvable.';
            $this->messageType = 'error';
            toastr()->error($this->message);

            return;
        }

        // Remplir les champs du formulaire avec les données existantes
        $this->code_anonymat = $copie->codeAnonymat->code_complet;
        $this->note = $copie->note;

        // Stocker l'ID de la copie à éditer pour le traitement par saveCopie
        $this->editingCopieId = $id;

        // Ouvrir la modale
        $this->showCopieModal = true;
    }


    public function confirmDelete($id)
    {
        $this->copieToDelete = Copie::with('codeAnonymat')->find($id);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->copieToDelete = null;
        $this->showDeleteModal = false;
    }

    public function confirmDeleteCopie()
    {
        if (!$this->copieToDelete) {
            $this->message = 'Copie introuvable.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            $this->showDeleteModal = false;
            return;
        }

        $copieId = $this->copieToDelete->id;
        $this->showDeleteModal = false;
        $this->deleteCopie($copieId);
    }

    public function deleteCopie($id)
    {
        try {
            $copie = Copie::find($id);
            if (! $copie) {
                throw new \Exception('Copie introuvable.');
            }

            // Vérifier que la copie n'est pas associée à un résultat
            if ($copie->isAssociated()) {
                throw new \Exception('Cette copie est déjà associée à un résultat et ne peut pas être supprimée.');
            }

            // Récupérer l'identifiant EC et le code anonymat avant suppression
            $ec_id_deleted = $copie->ec_id;

            $copie->delete();
            $this->message = 'Copie supprimée avec succès';
            $this->messageType = 'success';

            // Vérifier s'il reste des copies pour cet EC
            $remainingCopies = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $ec_id_deleted)
                ->count();

            $remainingUserCopies = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $ec_id_deleted)
                ->where('saisie_par', Auth::id())
                ->count();

            // Mettre à jour l'indicateur has_copies pour l'EC dans la collection
            if ($ec_id_deleted) {
                foreach ($this->ecs as $index => $ec) {
                    if (property_exists($ec, 'id') && $ec->id == $ec_id_deleted) {
                        $this->ecs[$index]->has_copies = ($remainingCopies > 0);
                        $this->ecs[$index]->copies_count = $remainingCopies;
                        $this->ecs[$index]->user_copies_count = $remainingUserCopies;
                        break;
                    }
                }
            }

            // Réinitialiser les variables de suivi
            $this->copieToDelete = null;

            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: '.$e->getMessage();
            $this->messageType = 'error';

            toastr()->error($this->message);
        }
    }



    public function render()
    {
        if ($this->niveau_id && $this->parcours_id && $this->salle_id && $this->ec_id && $this->examen_id) {
            $query = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id);

            // Filtrer par recherche sur le code d'anonymat
            if ($this->search) {
                $query->whereHas('codeAnonymat', function ($q) {
                    $q->where('code_complet', 'like', '%'.$this->search.'%');
                });
            }

            $copies = $query->with(['codeAnonymat', 'utilisateurSaisie'])
                ->orderBy('created_at', 'desc')
                ->paginate(25);
        } else {
            // Toujours retourner un objet de pagination (vide)
            $copies = Copie::where('id', 0)->paginate(25);
        }

        return view('livewire.copie.copies-index', [
            'copies' => $copies,
        ]);
    }
}
