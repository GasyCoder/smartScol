<?php

namespace App\Livewire\Manchette;

use App\Models\CodeAnonymat;
use App\Models\Manchette;
use App\Models\Etudiant;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Salle;
use App\Models\EC; // Ajout du modèle EC
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
class ManchettesIndex extends Component
{
    use WithPagination;

    // Variables de filtrage et contexte
    public $niveau_id;
    public $parcours_id;
    public $salle_id;
    public $examen_id;
    public $ec_id; // Ajout du filtre par EC/matière

    // Liste des données pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $salles = [];
    public $ecs = []; // Ajout de la liste des ECs/matières

    // Variables pour la modale de saisie
    public $showManchetteModal = false;
    public $code_anonymat = '';
    public $etudiant_id = null;
    public $matricule = '';
    public $editingManchetteId = null;
    public $selectedSalleCode = '';
    public $searchMode = 'matricule'; // 'matricule' ou 'nom'
    public $searchQuery = '';
    public $searchResults = [];

    // Variables pour la confirmation de suppression
    public $showDeleteModal = false;
    public $manchetteToDelete = null;

    // Messages de statut
    public $message = '';
    public $messageType = '';
    public $userManchettesCount = 0;

    public $search = '';

    // Règles de validation pour la modale
    protected $rules = [
        'code_anonymat' => 'required|string|max:20',
        'etudiant_id' => 'required|exists:etudiants,id',
    ];

    // Pour stocker les filtres en session
    protected function storeFiltres()
    {
        session()->put('manchettes.filtres', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'salle_id' => $this->salle_id,
            'examen_id' => $this->examen_id,
            'ec_id' => $this->ec_id, // Ajout du stockage du filtre EC
        ]);
    }

    // Pour récupérer les filtres stockés
    protected function loadFiltres()
    {
        $filtres = session()->get('manchettes.filtres', []);

        if (isset($filtres['niveau_id'])) {
            $this->niveau_id = $filtres['niveau_id'];
            $this->updatedNiveauId();

            if (isset($filtres['parcours_id'])) {
                $this->parcours_id = $filtres['parcours_id'];
                $this->updatedParcoursId();

                if (isset($filtres['ec_id'])) {
                    $this->ec_id = $filtres['ec_id'];
                    $this->updatedEcId();
                }

                if (isset($filtres['salle_id'])) {
                    $this->salle_id = $filtres['salle_id'];
                    $this->updatedSalleId();
                }
            }
        }
    }

    // Pour réinitialiser les filtres
    public function resetFiltres()
    {
        $this->reset(['niveau_id', 'parcours_id', 'salle_id', 'examen_id', 'ec_id', 'selectedSalleCode']);
        session()->forget('manchettes.filtres');

        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();

        $this->resetPage();
    }

    public function mount()
    {
        // Charger les niveaux
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        $this->searchMode = 'matricule';
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
            // 1. Charger toutes les ECs pour ce niveau et parcours
            $this->ecs = DB::table('ecs')
                ->join('ues', 'ecs.ue_id', '=', 'ues.id')
                ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->whereNull('examens.deleted_at')
                ->select('ecs.*')
                ->distinct()
                ->get();

            // 2. Charger toutes les salles qui ont des examens pour ce niveau et parcours
            $this->salles = DB::table('salles')
                ->join('examen_ec', 'salles.id', '=', 'examen_ec.salle_id')
                ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->whereNull('examens.deleted_at')
                ->select('salles.*')
                ->distinct()
                ->get();

            // S'il n'y a qu'une seule EC, la sélectionner automatiquement
            if ($this->ecs->count() == 1) {
                $this->ec_id = $this->ecs->first()->id;
                $this->updatedEcId();
            }
            // S'il n'y a qu'une seule salle, la sélectionner automatiquement
            else if ($this->salles->count() == 1) {
                $this->salle_id = $this->salles->first()->id;
                $this->updatedSalleId();
            }
        }

        $this->storeFiltres();
        $this->resetPage();
    }

    // Nouvelle méthode pour gérer la sélection d'une matière
    public function updatedEcId()
    {
        $this->examen_id = null;
        $this->salle_id = null; // Réinitialiser la salle si on change de matière
        $this->selectedSalleCode = '';

        if ($this->ec_id) {
            // Trouver les examens associés à cette EC et récupérer le premier
            $examens = DB::table('examens')
                ->join('examen_ec', 'examens.id', '=', 'examen_ec.examen_id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->where('examen_ec.ec_id', $this->ec_id)
                ->select('examens.id')
                ->distinct()
                ->get()
                ->pluck('id');

            if ($examens->count() > 0) {
                $this->examen_id = $examens->first();
            }
            
            // Récupérer les salles associées à cet EC et filtrer dynamiquement
            $sallesForEC = DB::table('salles')
                ->join('examen_ec', 'salles.id', '=', 'examen_ec.salle_id')
                ->where('examen_ec.ec_id', $this->ec_id)
                ->select('salles.*')
                ->distinct()
                ->get();
                
            // Mettre à jour la liste des salles disponibles
            $this->salles = $sallesForEC;
            
            // S'il n'y a qu'une seule salle, la sélectionner
            if ($this->salles->count() == 1) {
                $this->salle_id = $this->salles->first()->id;
                $this->updatedSalleId();
            }
        }

        // Effacer tout message précédent lors du changement d'EC
        $this->message = '';

        $this->storeFiltres();
        $this->resetPage();
    }

    public function updatedSalleId()
    {
        // Si on a déjà un examen_id via l'ec, ne pas le réinitialiser
        if (!$this->examen_id) {
            $this->examen_id = null;
        }
        $this->selectedSalleCode = '';

        if ($this->salle_id) {
            // Récupérer le code de la salle
            $salle = DB::table('salles')->where('id', $this->salle_id)->first();
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base ?? '';
            }

            // Si l'examen_id n'est pas déjà défini par l'EC
            if (!$this->examen_id) {
                // Récupérer l'ID de l'examen
                $examens = DB::table('examens')
                    ->join('examen_ec', 'examens.id', '=', 'examen_ec.examen_id')
                    ->where('examens.niveau_id', $this->niveau_id)
                    ->where('examens.parcours_id', $this->parcours_id)
                    ->where('examen_ec.salle_id', $this->salle_id);
                
                // Si une matière est sélectionnée, filtrer par celle-ci
                if ($this->ec_id) {
                    $examens->where('examen_ec.ec_id', $this->ec_id);
                }
                
                $examens = $examens->select('examens.id')
                    ->distinct()
                    ->get()
                    ->pluck('id');

                if ($examens->count() > 0) {
                    $this->examen_id = $examens->first();
                }
            }
            
            // Si un EC n'est pas déjà sélectionné, trouver les ECs associés à cette salle
            if (!$this->ec_id && $this->salle_id) {
                $ecsForSalle = DB::table('ecs')
                    ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                    ->where('examen_ec.salle_id', $this->salle_id)
                    ->select('ecs.*')
                    ->distinct()
                    ->get();
                    
                // Mettre à jour la liste des ECs disponibles
                $this->ecs = $ecsForSalle;
                
                // S'il n'y a qu'une seule EC, la sélectionner
                if ($this->ecs->count() == 1) {
                    $this->ec_id = $this->ecs->first()->id;
                }
            }
        }

        // Effacer tout message précédent lors du changement de salle
        $this->message = '';

        $this->storeFiltres();
        $this->resetPage();
    }

    public function openManchetteModal()
    {
        // Vérifier que le contexte est complet
        if (!$this->examen_id || !$this->salle_id) {
            $this->message = 'Veuillez sélectionner une salle d\'examen';
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

        // Compter les manchettes existantes pour cette salle et cet examen
        $manchettesCount = Manchette::where('examen_id', $this->examen_id)
            ->whereHas('codeAnonymat', function($query) {
                $query->where('code_complet', 'like', $this->selectedSalleCode . '%');
            })
            ->count();

        // Suggérer un numéro pour le code d'anonymat
        $nextNumber = $manchettesCount + 1;

        // Trouver un code d'anonymat non utilisé
        $baseCode = $this->selectedSalleCode;
        $proposedCode = $baseCode . $nextNumber;

        // Vérifier si ce code est déjà utilisé
        while (CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('code_complet', $proposedCode)
            ->exists()) {
            $nextNumber++;
            $proposedCode = $baseCode . $nextNumber;
        }

        // Préinitialiser le code d'anonymat avec le code unique
        $this->code_anonymat = $proposedCode;
        $this->etudiant_id = null;
        $this->matricule = '';
        $this->searchQuery = '';
        $this->searchResults = [];

        // Ouvrir la modale
        $this->showManchetteModal = true;
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) >= 2) {
            $this->searchEtudiant();
        } else {
            $this->searchResults = [];
        }
    }

    public function updatedSearchMode()
    {
        // Vider la recherche lorsqu'on change de mode
        $this->searchQuery = '';
        $this->searchResults = [];
    }

    public function searchEtudiant()
    {
        if (empty($this->searchQuery) || strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            return;
        }

        \Log::info('Recherche d\'étudiants', [
            'mode' => $this->searchMode,
            'query' => $this->searchQuery
        ]);

        // Rechercher des étudiants selon le mode (matricule ou nom)
        $query = Etudiant::query();
        
        if ($this->searchMode === 'matricule') {
            $query->where('matricule', 'like', '%' . $this->searchQuery . '%');
        } else {
            // Recherche par nom ou prénom
            $searchTerm = '%' . $this->searchQuery . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('nom', 'like', $searchTerm)
                ->orWhere('prenom', 'like', $searchTerm);
            });
        }

        // Filtrer par niveau et parcours si définis
        if ($this->niveau_id) {
            $query->where('niveau_id', $this->niveau_id);
            
            if ($this->parcours_id) {
                $query->where('parcours_id', $this->parcours_id);
            }
        }

        $this->searchResults = $query->limit(10)->get();
    }

    public function selectEtudiant($id)
    {
        $etudiant = Etudiant::find($id);
        if ($etudiant) {
            $this->etudiant_id = $etudiant->id;
            $this->matricule = $etudiant->matricule;
            // Important: vider les résultats de recherche pour éviter 
            // l'affichage du message "Aucun étudiant trouvé"
            $this->searchResults = [];
            $this->searchQuery = '';
        }
    }

    public function saveManchette()
    {
        $this->validate();

        try {
            // Vérifier si l'examen existe
            $examen = Examen::find($this->examen_id);
            if (!$examen) {
                throw new \Exception("L'examen sélectionné n'existe pas.");
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

            // Vérifier si une manchette supprimée existe avec ce code
            $deletedManchette = Manchette::withTrashed()
                ->where('examen_id', $this->examen_id)
                ->where('code_anonymat_id', $codeAnonymat->id)
                ->whereNotNull('deleted_at')
                ->first();

            if ($deletedManchette) {
                // Restaurer et mettre à jour la manchette supprimée
                $deletedManchette->restore();
                $deletedManchette->update([
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);

                $this->message = 'Manchette restaurée et mise à jour avec succès';
                $restoredManchette = true;
            } elseif (isset($this->editingManchetteId)) {
                // Mode édition
                $manchette = Manchette::find($this->editingManchetteId);

                if (!$manchette) {
                    throw new \Exception('La manchette à modifier est introuvable.');
                }

                // Vérifier si le code d'anonymat a changé
                if ($manchette->code_anonymat_id != $codeAnonymat->id) {
                    // Vérifier si le nouveau code est déjà utilisé
                    $codeExists = Manchette::where('examen_id', $this->examen_id)
                        ->where('code_anonymat_id', $codeAnonymat->id)
                        ->where('id', '!=', $manchette->id)
                        ->exists();

                    if ($codeExists) {
                        throw new \Exception("Ce code d'anonymat est déjà utilisé. Veuillez utiliser un autre code.");
                    }
                }

                // Vérifier si l'étudiant a changé
                if ($manchette->etudiant_id != $this->etudiant_id) {
                    // Vérifier si l'étudiant est déjà associé à une autre manchette
                    $etudiantExists = Manchette::where('examen_id', $this->examen_id)
                        ->where('etudiant_id', $this->etudiant_id)
                        ->where('id', '!=', $manchette->id)
                        ->exists();

                    if ($etudiantExists) {
                        throw new \Exception("Cet étudiant est déjà associé à un code d'anonymat pour cet examen.");
                    }
                }

                // Mise à jour de la manchette
                $manchette->update([
                    'code_anonymat_id' => $codeAnonymat->id,
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);

                $this->message = 'Manchette modifiée avec succès';
                $restoredManchette = false;
            } else {
                // Mode création - Vérifier si une manchette active existe déjà avec ce code
                $existingManchette = Manchette::where('examen_id', $this->examen_id)
                    ->where('code_anonymat_id', $codeAnonymat->id)
                    ->first();

                if ($existingManchette) {
                    throw new \Exception("Ce code d'anonymat est déjà utilisé. Veuillez utiliser un autre code.");
                }

                // Vérifier si l'étudiant est déjà associé à une manchette
                $etudiantAssigned = Manchette::where('examen_id', $this->examen_id)
                    ->where('etudiant_id', $this->etudiant_id)
                    ->first();

                if ($etudiantAssigned) {
                    throw new \Exception("Cet étudiant est déjà associé au code " . ($etudiantAssigned->codeAnonymat?->code_complet ?? 'inconnu') . ".");
                }

                // Créer une nouvelle manchette
                Manchette::create([
                    'examen_id' => $this->examen_id,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);

                $this->message = 'Manchette enregistrée avec succès';
                $restoredManchette = false;
            }

            $this->messageType = 'success';

            // Fermer la modale et réinitialiser les champs
            $this->reset(['code_anonymat', 'etudiant_id', 'matricule', 'editingManchetteId', 'searchResults', 'searchQuery']);
            $this->showManchetteModal = false;

            // Notification
            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: '.$e->getMessage();
            $this->messageType = 'error';

            toastr()->error($this->message);
        }
    }

    public function editManchette($id)
    {
        $manchette = Manchette::with(['codeAnonymat', 'etudiant'])->find($id);

        if (!$manchette) {
            $this->message = 'Manchette introuvable.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Remplir les champs du formulaire avec les données existantes
        $this->code_anonymat = $manchette->codeAnonymat->code_complet;
        $this->etudiant_id = $manchette->etudiant_id;
        $this->matricule = $manchette->etudiant->matricule;

        // Stocker l'ID de la manchette à éditer
        $this->editingManchetteId = $id;

        // Ouvrir la modale
        $this->showManchetteModal = true;
    }

    public function confirmDelete($id)
    {
        $this->manchetteToDelete = Manchette::find($id);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->manchetteToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteManchette()
    {
        try {
            if (!$this->manchetteToDelete) {
                throw new \Exception('Manchette introuvable.');
            }

            // Vérifier que la manchette n'est pas associée à un résultat
            if ($this->manchetteToDelete->isAssociated()) {
                throw new \Exception('Cette manchette est déjà associée à un résultat et ne peut pas être supprimée.');
            }

            $this->manchetteToDelete->delete();
            $this->message = 'Manchette supprimée avec succès';
            $this->messageType = 'success';
            $this->showDeleteModal = false;
            $this->manchetteToDelete = null;

            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: '.$e->getMessage();
            $this->messageType = 'error';
            $this->showDeleteModal = false;
            toastr()->error($this->message);
        }
    }

    public function render()
    {
        $query = Manchette::query();
        
        if ($this->examen_id) {
            $query->where('examen_id', $this->examen_id);
            
            // Filtrer par EC si sélectionné
            if ($this->ec_id) {
                $query->whereHas('examen', function($q) {
                    $q->whereExists(function($subQuery) {
                        $subQuery->from('examen_ec')
                            ->whereColumn('examen_ec.examen_id', 'examens.id')
                            ->where('examen_ec.ec_id', $this->ec_id);
                    });
                });
            }
            // Filtrer par salle si sélectionnée
            if ($this->salle_id) {
                $query->whereHas('codeAnonymat', function($q) {
                    $salle = Salle::find($this->salle_id);
                    if ($salle && $salle->code_base) {
                        $q->where('code_complet', 'like', $salle->code_base . '%');
                    }
                });
            }
            
            if ($this->search) {
                $query->where(function($q) {
                    $q->whereHas('codeAnonymat', function($sq) {
                        $sq->where('code_complet', 'like', '%'.$this->search.'%');
                    })->orWhereHas('etudiant', function($sq) {
                        $sq->where('matricule', 'like', '%'.$this->search.'%')
                          ->orWhere('nom', 'like', '%'.$this->search.'%');
                    });
                });
            }
            
            // Calculer le nombre de manchettes pour l'utilisateur actuel
            $this->userManchettesCount = Manchette::where('examen_id', $this->examen_id)
                ->where('saisie_par', Auth::id())
                ->count();
            
            $manchettes = $query->with(['codeAnonymat', 'etudiant', 'utilisateurSaisie'])
                ->orderBy('created_at', 'desc')
                ->paginate(25);
        } else {
            $manchettes = Manchette::where('id', 0)->paginate(25);
            $this->userManchettesCount = 0;
        }

        return view('livewire.manchette.manchettes-index', [
            'manchettes' => $manchettes,
        ]);
    }
}