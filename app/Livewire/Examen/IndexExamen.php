<?php

namespace App\Livewire\Examen;

use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\SessionExam;
use App\Models\Salle;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class IndexExamen extends Component
{
    use WithPagination;

    // Étape actuelle de navigation avec queryString pour persister dans l'URL
    protected $queryString = [
        'step' => ['except' => 'niveau'],
        'niveauId' => ['except' => '', 'as' => 'niveau'],
        'parcoursId' => ['except' => '', 'as' => 'parcours'],
        'search' => ['except' => ''],
        'session_id' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'salle_id' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    // Étape actuelle de navigation
    public $step = 'niveau'; // 'niveau', 'parcours', 'examens'

    // Filtres et sélecteurs
    public $niveauId = '';
    public $parcoursId = '';
    public $search = '';
    public $session_id = '';
    public $date_from = '';
    public $date_to = '';
    public $salle_id = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Modaux
    public $showDeleteModal = false;
    public $examenToDelete = null;

    // Données de contexte
    public $niveauInfo = null;
    public $parcoursInfo = null;

    // Méthodes pour réinitialiser la pagination lors de l'update des filtres
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingSessionId()
    {
        $this->resetPage();
    }

    public function updatingSalleId()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'session_id', 'date_from', 'date_to', 'salle_id']);
        $this->resetPage();
    }

    /**
     * Initialise le composant à partir des paramètres d'URL
     */
    public function mount()
    {
        // Force l'étape initiale à 'niveau' si pas de paramètres
        if (empty($this->niveauId) && empty($this->parcoursId)) {
            $this->step = 'niveau';
            return;
        }

        // Charge les données à partir des paramètres d'URL
        $this->loadDataFromQueryParams();
    }

    /**
     * Charge les données initiales à partir des paramètres d'URL
     */
    private function loadDataFromQueryParams()
    {
        // Nettoyer les valeurs des paramètres
        $this->niveauId = $this->cleanInputValue($this->niveauId);
        $this->parcoursId = $this->cleanInputValue($this->parcoursId);

        // Charger les données du niveau si disponible
        if (!empty($this->niveauId)) {
            if ($this->loadNiveauInfo()) {
                $this->step = 'parcours';
            } else {
                // Réinitialiser si le niveau n'est pas valide
                $this->reset(['niveauId', 'niveauInfo', 'parcoursId', 'parcoursInfo']);
                $this->step = 'niveau';
                return;
            }
        }

        // Charger les données du parcours si disponible
        if (!empty($this->parcoursId)) {
            if ($this->loadParcoursInfo()) {
                $this->step = 'examens';
            } else {
                // Réinitialiser le parcours si non valide
                $this->reset(['parcoursId', 'parcoursInfo']);
                $this->step = 'parcours';
            }
        }
    }

    /**
     * Nettoie une valeur d'entrée
     */
    private function cleanInputValue($value)
    {
        // Si la valeur est un tableau (comme dans niveauId[value]=1)
        if (is_array($value) && isset($value['value'])) {
            return $value['value'];
        }
        return $value;
    }

    /**
     * Réinitialise complètement l'état du composant
     */
    public function resetAll()
    {
        $this->reset([
            'step', 'niveauId', 'parcoursId', 'niveauInfo', 'parcoursInfo',
            'search', 'session_id', 'date_from', 'date_to', 'salle_id'
        ]);
        $this->step = 'niveau';
        $this->resetPage();
    }

    // Navigation entre les étapes
    public function selectionnerNiveau()
    {
        if (empty($this->niveauId)) {
            return;
        }

        if ($this->loadNiveauInfo()) {
            $this->parcoursId = null;
            $this->parcoursInfo = null;
            $this->step = 'parcours';
        }
    }

    public function selectionnerParcours()
    {
        if (empty($this->parcoursId)) {
            return;
        }

        if ($this->loadParcoursInfo()) {
            $this->resetPage();
            $this->step = 'examens';
        }
    }

    public function retourANiveau()
    {
        $this->step = 'niveau';
        $this->parcoursId = null;
        $this->parcoursInfo = null;
    }

    public function retourAParcours()
    {
        $this->step = 'parcours';
        $this->parcoursInfo = null;
        $this->parcoursId = null;
    }

    /**
     * Met à jour le niveauId lorsqu'il change dans l'interface
     */
    public function updatedNiveauId($value)
    {
        // Nettoyer la valeur
        $value = $this->cleanInputValue($value);
        $this->niveauId = $value;

        if ($value) {
            $this->parcoursId = '';
            $this->parcoursInfo = null;

            // Charger les données du niveau
            if ($this->loadNiveauInfo()) {
                $this->step = 'parcours';
            }
        } else {
            $this->reset(['niveauInfo', 'parcoursId', 'parcoursInfo']);
        }
    }

    /**
     * Met à jour le parcoursId lorsqu'il change dans l'interface
     */
    public function updatedParcoursId($value)
    {
        // Nettoyer la valeur
        $value = $this->cleanInputValue($value);
        $this->parcoursId = $value;

        if ($value) {
            // Charger les données du parcours
            if ($this->loadParcoursInfo()) {
                $this->step = 'examens';
            }
        } else {
            $this->parcoursInfo = null;
        }
    }

    // Chargement des informations contextuelles
    private function loadNiveauInfo()
    {
        try {
            $niveau = Niveau::find($this->niveauId);
            if ($niveau) {
                $this->niveauInfo = [
                    'id' => $niveau->id,
                    'nom' => $niveau->nom,
                    'abr' => $niveau->abr,
                    'has_parcours' => $niveau->has_parcours
                ];
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des données du niveau: ' . $e->getMessage());
        }
        return false;
    }

    private function loadParcoursInfo()
    {
        try {
            $parcours = Parcour::find($this->parcoursId);
            if ($parcours) {
                $this->parcoursInfo = [
                    'id' => $parcours->id,
                    'nom' => $parcours->nom,
                    'abr' => $parcours->abr
                ];

                // Vérifier que le parcours appartient au niveau sélectionné
                if (!$this->niveauInfo || $parcours->niveau_id != $this->niveauId) {
                    $this->niveauId = $parcours->niveau_id;
                    $this->loadNiveauInfo();
                }

                return true;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des données du parcours: ' . $e->getMessage());
        }
        return false;
    }

    // Gestion du tri
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Gestion de la suppression
    public function confirmDelete($examenId)
    {
        $this->examenToDelete = Examen::find($examenId);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->examenToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteExamen()
    {
        if (!$this->examenToDelete) {
            return;
        }

        $examen = $this->examenToDelete;

        // Vérifier si des données sont liées à cet examen avant suppression
        if ($examen->copies()->count() > 0 || $examen->manchettes()->count() > 0 || $examen->resultats()->count() > 0) {
            toastr('error', 'Cet examen possède des données liées (copies, manchettes ou résultats) et ne peut pas être supprimé.');
            $this->showDeleteModal = false;
            $this->examenToDelete = null;
            return;
        }
        // Supprimer les codes d'anonymat associés
        $examen->codesAnonymat()->delete();
        // Supprimer l'examen
        $examen->delete();
        toastr('success', 'Examen supprimé avec succès.');
        $this->showDeleteModal = false;
        $this->examenToDelete = null;
    }

    // Méthodes utilitaires
    public function getEtudiantsCount()
    {
        return Examen::where('niveau_id', $this->niveauId)
                    ->where('parcours_id', $this->parcoursId)
                    ->count();
    }

    // Export et import
    public function exportExamens()
    {
        // Code pour exporter les examens
    }

    public function render()
    {
        // Toujours charger les niveaux pour le premier écran
        $niveaux = Niveau::where('is_active', true)->get();

        // Charger les parcours si nous sommes à cette étape
        $parcours = collect();
        if ($this->step === 'parcours' && $this->niveauId) {
            $parcours = Parcour::where('niveau_id', $this->niveauId)->get();
        }

        // Récupérer les salles pour les filtres
        $salles = Salle::orderBy('nom')->get();

        // Récupérer la session courante
        $currentSession = null;
        $sessions = SessionExam::whereHas('anneeUniversitaire', function($q) {
            $q->where('is_active', true);
        })->get();

        // Obtenir la session active et courante
        $currentSession = $sessions->where('is_active', true)
                                ->where('is_current', true)
                                ->first();

        // Si aucune session courante n'est trouvée, prendre la première session active
        if (!$currentSession && $sessions->isNotEmpty()) {
            $currentSession = $sessions->where('is_active', true)->first();
        }

        // Charger les examens si nous sommes à cette étape
        $examens = collect();
        if ($this->step === 'examens' && $this->niveauId && $this->parcoursId) {
            $baseQuery = Examen::with([
                'ecs.ue',
                'niveau',
                'session',
                'parcours',
                'copies',
                'manchettes',
            ])
            ->where('niveau_id', $this->niveauId)
            ->where('parcours_id', $this->parcoursId);

            // Appliquer le filtre de recherche
            if ($this->search) {
                $baseQuery->where(function($q) {
                    $q->whereHas('ecs', function($subQ) {
                        $subQ->where('nom', 'like', '%' . $this->search . '%')
                          ->orWhere('abr', 'like', '%' . $this->search . '%');
                    });
                    // On a supprimé la recherche sur le code car il n'existe plus
                });
            }

            // Appliquer le filtre de session
            if ($this->session_id) {
                $baseQuery->where('session_id', $this->session_id);
            }

            // Appliquer les filtres de date
            if ($this->date_from) {
                $baseQuery->whereHas('ecs', function($query) {
                    $query->whereDate('examen_ec.date_specifique', '>=', $this->date_from);
                });
            }

            if ($this->date_to) {
                $baseQuery->whereHas('ecs', function($query) {
                    $query->whereDate('examen_ec.date_specifique', '<=', $this->date_to);
                });
            }

            // Appliquer le filtre de salle
            if ($this->salle_id) {
                $baseQuery->whereHas('ecs', function($query) {
                    $query->where('examen_ec.salle_id', $this->salle_id);
                });
            }

            // Gestion du tri
            if ($this->sortField === 'date') {
                // Pour le tri par date, nous utilisons la table pivot
                $columns = [
                    'examens.id', 'examens.session_id',
                    'examens.niveau_id', 'examens.parcours_id', 'examens.duree',
                    'examens.note_eliminatoire', 'examens.created_at',
                    'examens.updated_at', 'examens.deleted_at'
                ];

                $examens = $baseQuery
                    ->select('examens.*', DB::raw('MIN(examen_ec.date_specifique) as min_date'))
                    ->join('examen_ec', 'examens.id', '=', 'examen_ec.examen_id')
                    ->groupBy($columns)
                    ->orderBy('min_date', $this->sortDirection)
                    ->paginate($this->perPage);
            } else {
                // Pour les autres champs, tri normal
                $examens = $baseQuery
                    ->orderBy($this->sortField, $this->sortDirection)
                    ->paginate($this->perPage);
            }
        }

        return view('livewire.examen.index-examen', [
            'niveaux' => $niveaux,
            'parcours' => $parcours,
            'examens' => $examens,
            'sessions' => $sessions,
            'salles' => $salles,
            'currentSession' => $currentSession,
        ]);
    }
}
