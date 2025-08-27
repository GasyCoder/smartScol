<?php

namespace App\Livewire\Manchette;

use App\Models\EC;
use App\Models\Salle;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\Manchette;
use App\Models\SessionExam;
use App\Models\CodeAnonymat;
use Livewire\WithPagination;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ManchettesIndex extends Component
{
    use WithPagination;

    // Propriétés de filtrage essentielles
    public $niveau_id;
    public $parcours_id;
    public $examen_id;
    public $ec_id;
    public $session_exam_id;
    public $saisie_par;

    // Collections pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $ecs = [];
    public $secretaires = [];

    // Propriétés d'affichage et tri
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;
    public $search = '';

    // Session active
    public $sessionActive = null;
    public $currentSessionType = '';

    // Modal de modification
    public $showEditModal = false;
    public $editingManchetteId = null;
    public $code_anonymat = '';
    public $etudiant_id = null;

    // Modal de suppression
    public $showDeleteModal = false;
    public $manchetteToDelete = null;

    // SUPPRESSION DES VARIABLES DE MESSAGE - on utilise dispatch uniquement
    // public $message = '';
    // public $messageType = '';

    protected $rules = [
        'code_anonymat' => 'required|string|max:20',
        'etudiant_id' => 'required|exists:etudiants,id',
    ];

    public function mount()
    {
        // Initialiser toutes les collections comme des Collections Laravel
        $this->niveaux = collect();
        $this->parcours = collect();
        $this->ecs = collect();
        $this->secretaires = collect();

        // Charger les niveaux
        try {
            $this->niveaux = Niveau::where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();
                
            // Charger les secrétaires qui ont saisi des manchettes
            $this->secretaires = DB::table('users')
                ->join('manchettes', 'users.id', '=', 'manchettes.saisie_par')
                ->select('users.id', 'users.name')
                ->distinct()
                ->orderBy('users.name')
                ->get();
        } catch (\Exception $e) {
            Log::error('Erreur chargement niveaux', ['error' => $e->getMessage()]);
            $this->niveaux = collect();
        }

        // Récupérer la session active
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if ($anneeActive) {
                $this->sessionActive = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                    ->where('is_active', true)
                    ->where('is_current', true)
                    ->first();

                if ($this->sessionActive) {
                    $this->session_exam_id = $this->sessionActive->id;
                    $this->currentSessionType = $this->sessionActive->type;
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur session active', ['error' => $e->getMessage()]);
        }

        $this->loadFilters();
    }

    /**
     * Tri des colonnes
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    /**
     * Mise à jour du nombre d'éléments par page
     */
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Mise à jour de la recherche
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Mise à jour du niveau - charge les parcours
     */
    public function updatedNiveauId()
    {
        $this->resetDependentFilters(['parcours_id', 'examen_id', 'ec_id']);
        
        if ($this->niveau_id) {
            try {
                $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                    ->where('is_active', true)
                    ->orderBy('id', 'asc')
                    ->get();
            } catch (\Exception $e) {
                Log::error('Erreur chargement parcours', ['error' => $e->getMessage()]);
                $this->parcours = collect();
            }
        } else {
            $this->parcours = collect();
        }
        
        $this->ecs = collect();
        $this->storeFilters();
        $this->resetPage();
    }

    /**
     * Mise à jour du parcours - charge les ECs
     */
    public function updatedParcoursId()
    {
        $this->resetDependentFilters(['examen_id', 'ec_id']);

        if ($this->niveau_id && $this->parcours_id) {
            $this->loadExamensAndEcs();
        } else {
            $this->ecs = collect();
        }

        $this->storeFilters();
        $this->resetPage();
    }

    /**
     * Mise à jour de l'EC
     */
    public function updatedEcId()
    {
        $this->storeFilters();
        $this->resetPage();
    }

    /**
     * Mise à jour du filtre secrétaire
     */
    public function updatedSaisiePar()
    {
        $this->storeFilters();
        $this->resetPage();
    }

    /**
     * Charger les examens et ECs pour le niveau/parcours sélectionné
     */
    private function loadExamensAndEcs()
    {
        try {
            $examens = DB::table('examens')
                ->where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            if (empty($examens)) {
                $this->ecs = collect();
                return;
            }

            // Récupérer le premier examen pour l'utiliser par défaut
            $this->examen_id = $examens[0];

            // Charger les ECs
            $this->ecs = DB::table('ecs')
                ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                ->join('ues', 'ecs.ue_id', '=', 'ues.id')
                ->whereIn('examen_ec.examen_id', $examens)
                ->whereNull('ecs.deleted_at')
                ->select(
                    'ecs.*',
                    'ues.nom as ue_nom',
                    'ues.abr as ue_abr',
                    'examen_ec.date_specifique',
                    'examen_ec.heure_specifique'
                )
                ->distinct()
                ->orderBy('ues.nom')
                ->orderBy('ecs.nom')
                ->get();
        } catch (\Exception $e) {
            Log::error('Erreur chargement ECs', ['error' => $e->getMessage()]);
            $this->ecs = collect();
        }
    }

    /**
     * Réinitialiser les filtres dépendants
     */
    private function resetDependentFilters($filters)
    {
        foreach ($filters as $filter) {
            $this->$filter = null;
        }
    }

    /**
     * Effacer un filtre spécifique
     */
    public function clearFilter($filterName)
    {
        $this->$filterName = null;
        
        // Réinitialiser les filtres dépendants
        switch ($filterName) {
            case 'niveau_id':
                $this->resetDependentFilters(['parcours_id', 'examen_id', 'ec_id']);
                $this->parcours = collect();
                $this->ecs = collect();
                break;
            case 'parcours_id':
                $this->resetDependentFilters(['examen_id', 'ec_id']);
                $this->ecs = collect();
                break;
        }

        $this->storeFilters();
        $this->resetPage();
    }

    /**
     * Réinitialiser tous les filtres
     */
    public function resetFilters()
    {
        $this->reset([
            'niveau_id', 'parcours_id', 'examen_id', 'ec_id', 'search', 'saisie_par'
        ]);
        
        $this->parcours = collect();
        $this->ecs = collect();
        
        session()->forget('manchettes.filters');
        $this->resetPage();
    }

    /**
     * Sauvegarder les filtres en session
     */
    private function storeFilters()
    {
        session()->put('manchettes.filters', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'examen_id' => $this->examen_id,
            'ec_id' => $this->ec_id,
            'saisie_par' => $this->saisie_par,
        ]);
    }

    /**
     * Charger les filtres depuis la session
     */
    private function loadFilters()
    {
        $filters = session()->get('manchettes.filters', []);
        
        if (!empty($filters['niveau_id'])) {
            $this->niveau_id = $filters['niveau_id'];
            $this->updatedNiveauId();
            
            if (!empty($filters['parcours_id'])) {
                $this->parcours_id = $filters['parcours_id'];
                $this->updatedParcoursId();
                
                if (!empty($filters['ec_id'])) {
                    $this->ec_id = $filters['ec_id'];
                }
            }
            
            if (!empty($filters['saisie_par'])) {
                $this->saisie_par = $filters['saisie_par'];
            }
        }
    }

    /**
     * Ouvrir la modal de modification - TOUTES NOTIFICATIONS CORRIGÉES
     */
    public function editManchette($id)
    {
        $manchette = Manchette::with(['codeAnonymat', 'etudiant'])->find($id);
        
        if (!$manchette) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Manchette introuvable.'
            ]);
            return;
        }

        // Vérifier que la manchette appartient à la session active
        if ($this->session_exam_id && $manchette->session_exam_id !== $this->session_exam_id) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Cette manchette appartient à une autre session.'
            ]);
            return;
        }

        $this->editingManchetteId = $id;
        $this->code_anonymat = $manchette->codeAnonymat->code_complet;
        $this->etudiant_id = $manchette->etudiant_id;
        $this->showEditModal = true;
        $this->resetErrorBag();
    }

    /**
     * Sauvegarder les modifications - TOUTES NOTIFICATIONS CORRIGÉES
     */
    public function updateManchette()
    {
        $this->validate();

        try {
            $manchette = Manchette::find($this->editingManchetteId);
            
            if (!$manchette) {
                throw new \Exception('Manchette introuvable.');
            }

            // Vérifier si le code existe déjà pour un autre étudiant
            $existingCode = CodeAnonymat::where('code_complet', $this->code_anonymat)
                ->where('examen_id', $manchette->examen_id)
                ->where('ec_id', $manchette->codeAnonymat->ec_id)
                ->where('id', '!=', $manchette->code_anonymat_id)
                ->first();

            if ($existingCode) {
                $existingManchette = Manchette::where('code_anonymat_id', $existingCode->id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->first();
                
                if ($existingManchette) {
                    throw new \Exception('Ce code d\'anonymat est déjà utilisé par un autre étudiant.');
                }
            }

            // Vérifier si l'étudiant a déjà une autre manchette pour cette EC
            $existingStudentManchette = Manchette::where('etudiant_id', $this->etudiant_id)
                ->where('examen_id', $manchette->examen_id)
                ->where('session_exam_id', $this->session_exam_id)
                ->whereHas('codeAnonymat', function ($query) use ($manchette) {
                    $query->where('ec_id', $manchette->codeAnonymat->ec_id);
                })
                ->where('id', '!=', $this->editingManchetteId)
                ->first();

            if ($existingStudentManchette) {
                throw new \Exception('Cet étudiant a déjà une manchette pour cette matière.');
            }

            // Sauvegarder les infos pour la notification
            $manchetteInfo = [
                'code' => $this->code_anonymat,
                'etudiant' => $manchette->etudiant->nom . ' ' . $manchette->etudiant->prenom
            ];

            // Mettre à jour ou créer le code d'anonymat
            $codeAnonymat = $manchette->codeAnonymat;
            $codeAnonymat->update(['code_complet' => $this->code_anonymat]);

            // Mettre à jour la manchette
            $manchette->update([
                'etudiant_id' => $this->etudiant_id,
                'updated_at' => now(),
            ]);

            // Réinitialisation de l'état
            $this->showEditModal = false;
            $this->resetEditForm();
            
            // Notification de succès
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => "Manchette {$manchetteInfo['code']} de {$manchetteInfo['etudiant']} modifiée avec succès."
            ]);

            // Rafraîchir les données
            $this->dispatch('refresh-page');
            
        } catch (\Exception $e) {
            // Notification d'erreur
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
            
            Log::error('Erreur modification manchette', [
                'error' => $e->getMessage(),
                'manchette_id' => $this->editingManchetteId
            ]);
        }
    }

    /**
     * Annuler la modification - ÉTAT RÉINITIALISÉ
     */
    public function cancelEdit()
    {
        $this->showEditModal = false;
        $this->resetEditForm();
        
        // Force le re-rendu du composant
        $this->dispatch('modal-closed');
    }

    /**
     * Réinitialiser le formulaire de modification
     */
    private function resetEditForm()
    {
        $this->editingManchetteId = null;
        $this->code_anonymat = '';
        $this->etudiant_id = null;
        $this->resetErrorBag();
    }

    /**
     * Confirmer la suppression - CORRIGÉE
     */
    public function confirmDelete($id)
    {
        $manchette = Manchette::with(['codeAnonymat.ec', 'etudiant'])->find($id);
        
        if (!$manchette) {
            toastr()->error('Veuillez sélectionner au moins une manchette à supprimer.');
            return;
        }

        // Vérifier que la manchette appartient à la session active
        if ($this->session_exam_id && $manchette->session_exam_id !== $this->session_exam_id) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Cette manchette appartient à une autre session.'
            ]);
            return;
        }

        $this->manchetteToDelete = $manchette;
        $this->showDeleteModal = true;
    }

    /**
     * Supprimer la manchette - VERSION FONCTIONNELLE IMMÉDIATE
     */
    public function deleteManchette()
    {
        // Sauvegarder l'ID au début pour les logs
        $manchetteId = $this->manchetteToDelete ? $this->manchetteToDelete->id : null;
        
        try {
            if (!$this->manchetteToDelete) {
                throw new \Exception('Manchette introuvable.');
            }

            // Sauvegarder les infos pour la notification AVANT suppression
            $manchetteInfo = [
                'code' => $this->manchetteToDelete->codeAnonymat->code_complet ?? 'N/A',
                'etudiant' => $this->manchetteToDelete->etudiant->nom . ' ' . $this->manchetteToDelete->etudiant->prenom
            ];

            // Supprimer la manchette DIRECTEMENT - sans vérification d'association
            $this->manchetteToDelete->delete();
            
            // Réinitialisation COMPLÈTE de l'état
            $this->showDeleteModal = false;
            $this->manchetteToDelete = null;
            
            // Notification de succès
            toastr()->success("Manchette {$manchetteInfo['code']} de {$manchetteInfo['etudiant']} supprimée avec succès.");


            // Rafraîchir les données
            $this->dispatch('refresh-page');
            
        } catch (\Exception $e) {
            // Réinitialisation en cas d'erreur
            $this->showDeleteModal = false;
            $this->manchetteToDelete = null;
            
            // Notification d'erreur
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
            
            // Log avec l'ID sauvegardé
            Log::error('Erreur suppression manchette', [
                'error' => $e->getMessage(),
                'manchette_id' => $manchetteId
            ]);
        }
    }

    /**
     * Annuler la suppression - ÉTAT RÉINITIALISÉ
     */
    public function cancelDelete()
    {
        $this->manchetteToDelete = null;
        $this->showDeleteModal = false;
        
        // Force le re-rendu du composant
        $this->dispatch('modal-closed');
    }

    /**
     * Rendu du composant
     */
    public function render()
    {
        $manchettes = $this->getManchettes();

        return view('livewire.manchette.manchettes-index', [
            'manchettes' => $manchettes,
            'sessionInfo' => [
                'active' => $this->sessionActive,
                'type' => $this->currentSessionType,
                'session_libelle' => $this->sessionActive ? $this->sessionActive->type : null
            ]
        ]);
    }

    /**
     * Récupérer les manchettes avec filtres et pagination
     */
    private function getManchettes()
    {
        // Base query - toujours commencer par toutes les manchettes
        $query = Manchette::with(['codeAnonymat.ec.ue', 'etudiant', 'utilisateurSaisie', 'sessionExam']);

        // Filtre par session active (priorité haute - toujours appliqué si disponible)
        if ($this->session_exam_id) {
            $query->where('session_exam_id', $this->session_exam_id);
        }

        // FILTRES PROGRESSIFS - chaque filtre s'applique s'il est défini
        
        // 1. Filtre par niveau et parcours (via examens)
        if ($this->niveau_id && $this->parcours_id) {
            $examensIds = DB::table('examens')
                ->where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();
                
            if (!empty($examensIds)) {
                $query->whereIn('examen_id', $examensIds);
            } else {
                // Aucun examen pour cette combinaison niveau/parcours
                return $query->where('id', 0)->paginate($this->perPage);
            }
        }
        // 2. Sinon, filtre par niveau seulement (si sélectionné)
        elseif ($this->niveau_id) {
            $examensIds = DB::table('examens')
                ->where('niveau_id', $this->niveau_id)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();
                
            if (!empty($examensIds)) {
                $query->whereIn('examen_id', $examensIds);
            } else {
                return $query->where('id', 0)->paginate($this->perPage);
            }
        }

        // 3. Filtre par EC spécifique (si sélectionnée)
        if ($this->ec_id) {
            $query->whereHas('codeAnonymat', function ($q) {
                $q->where('ec_id', $this->ec_id);
            });
        }

        // 4. Filtre par secrétaire (si sélectionné)
        if ($this->saisie_par) {
            $query->where('saisie_par', $this->saisie_par);
        }

        // 5. Filtre de recherche textuelle
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('codeAnonymat', function ($sq) {
                    $sq->where('code_complet', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('etudiant', function ($sq) {
                    $sq->where('matricule', 'like', '%' . $this->search . '%')
                      ->orWhere('nom', 'like', '%' . $this->search . '%')
                      ->orWhere('prenom', 'like', '%' . $this->search . '%');
                });
            });
        }

        // TRI avec gestion des relations
        try {
            switch ($this->sortField) {
                case 'code_anonymat_id':
                    $query->orderBy(
                        CodeAnonymat::select('code_complet')
                            ->whereColumn('codes_anonymat.id', 'manchettes.code_anonymat_id')
                            ->limit(1),
                        $this->sortDirection
                    );
                    break;
                case 'etudiant_id':
                    $query->orderBy(
                        Etudiant::select('nom')
                            ->whereColumn('etudiants.id', 'manchettes.etudiant_id')
                            ->limit(1),
                        $this->sortDirection
                    )->orderBy(
                        Etudiant::select('prenom')
                            ->whereColumn('etudiants.id', 'manchettes.etudiant_id')
                            ->limit(1),
                        $this->sortDirection
                    );
                    break;
                case 'saisie_par':
                    $query->join('users', 'users.id', '=', 'manchettes.saisie_par')
                          ->orderBy('users.name', $this->sortDirection)
                          ->select('manchettes.*');
                    break;
                default:
                    $query->orderBy($this->sortField, $this->sortDirection);
                    break;
            }

            return $query->paginate($this->perPage);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans getManchettes', [
                'error' => $e->getMessage(),
                'sortField' => $this->sortField,
                'search' => $this->search,
                'niveau_id' => $this->niveau_id,
                'parcours_id' => $this->parcours_id,
            ]);
            
            // Fallback simple en cas d'erreur de tri
            return Manchette::with(['codeAnonymat.ec.ue', 'etudiant', 'utilisateurSaisie', 'sessionExam'])
                ->when($this->session_exam_id, function($q) {
                    return $q->where('session_exam_id', $this->session_exam_id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        }
    }
}