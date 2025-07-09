<?php

namespace App\Livewire\Examen;

use App\Models\Salle;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\EC;
use App\Models\UE;
use App\Models\Copie;
use App\Models\Manchette;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class IndexExamen extends Component
{
    use WithPagination;

    protected $queryString = [
        'step' => ['except' => 'niveau'],
        'niveauId' => ['except' => '', 'as' => 'niveau'],
        'parcoursId' => ['except' => '', 'as' => 'parcours'],
        'search' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'enseignant_filter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'asc'],
    ];

    // Navigation
    public $step = 'niveau';
    public $niveauId = '';
    public $parcoursId = '';
    public $niveauInfo = null;
    public $parcoursInfo = null;

    // Filtres
    public $search = '';
    public $date_from = '';
    public $date_to = '';
    public $enseignant_filter = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'asc';

    // Modal suppression examen complet
    public $showDeleteModal = false;
    public $examenToDelete = null;

    // Modal édition EC
    public $showEditECModal = false;
    public $editingExamenId = null;
    public $editingEC = null;
    public $editingECData = [
        'date_specifique' => '',
        'heure_specifique' => '',
        'salle_id' => '',
        'code_base' => ''
    ];

    // Modal suppression EC
    public $showDeleteECModal = false;
    public $ecToDelete = null;
    public $examenToDeleteFrom = null;

    // États additionnels
    public $isLoading = false;
    public $hasFilters = false;
    public $showAdvancedFilters = false;

    protected $rules = [
        'editingECData.date_specifique' => 'required|date',
        'editingECData.heure_specifique' => 'required|date_format:H:i',
        'editingECData.salle_id' => 'nullable|exists:salles,id',
        'editingECData.code_base' => 'nullable|string|max:10',
    ];

    protected $messages = [
        'editingECData.date_specifique.required' => 'La date est obligatoire.',
        'editingECData.date_specifique.date' => 'La date doit être au format valide.',
        'editingECData.heure_specifique.required' => 'L\'heure est obligatoire.',
        'editingECData.heure_specifique.date_format' => 'L\'heure doit être au format HH:MM.',
        'editingECData.salle_id.exists' => 'La salle sélectionnée n\'existe pas.',
        'editingECData.code_base.max' => 'Le code ne peut pas dépasser 10 caractères.',
    ];

    protected $listeners = [
        'examensUpdated' => '$refresh',
        'closeAllModals' => 'closeAllModals',
    ];

    public function mount()
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Accès non autorisé.');
        }
        
        if (empty($this->niveauId) && empty($this->parcoursId)) {
            $this->step = 'niveau';
            return;
        }

        $this->loadDataFromQueryParams();
        $this->checkFiltersStatus();
    }

    // Navigation methods avec amélioration
    public function resetAll()
    {
        $this->reset([
            'step', 'niveauId', 'parcoursId', 'niveauInfo', 'parcoursInfo',
            'search', 'date_from', 'date_to', 'enseignant_filter',
            'showAdvancedFilters', 'hasFilters'
        ]);
        $this->step = 'niveau';
        $this->resetPage();
        
        // Nettoyer le cache si nécessaire
        Cache::forget("examens_stats_{$this->niveauId}_{$this->parcoursId}");
        
        toastr()->info('Retour à l\'accueil');
    }

    public function updatedStep($value)
    {
        // Réinitialiser les données selon l'étape
        switch($value) {
            case 'niveau':
                $this->reset([
                    'niveauId', 'parcoursId', 'niveauInfo', 'parcoursInfo',
                    'search', 'date_from', 'date_to', 'enseignant_filter'
                ]);
                $this->resetPage();
                break;
                
            case 'parcours':
                $this->reset([
                    'parcoursId', 'parcoursInfo',
                    'search', 'date_from', 'date_to', 'enseignant_filter'
                ]);
                $this->resetPage();
                break;
                
            case 'examens':
                $this->reset(['search', 'date_from', 'date_to', 'enseignant_filter']);
                $this->resetPage();
                break;
        }
        
        $this->checkFiltersStatus();
    }

    public function updatedNiveauId($value)
    {
        $value = $this->cleanInputValue($value);
        $this->niveauId = $value;

        if ($value) {
            $this->parcoursId = '';
            $this->parcoursInfo = null;
            $this->resetFilters();

            if ($this->loadNiveauInfo()) {
                $this->step = 'parcours';
            }
        } else {
            $this->reset(['niveauInfo', 'parcoursId', 'parcoursInfo']);
        }
        
        $this->resetPage();
    }

    public function updatedParcoursId($value)
    {
        $value = $this->cleanInputValue($value);
        $this->parcoursId = $value;

        if ($value) {
            if ($this->loadParcoursInfo()) {
                $this->step = 'examens';
                $this->resetPage();
            }
        } else {
            $this->parcoursInfo = null;
        }
    }

    // Filter methods avec amélioration
    public function updatingSearch()
    {
        $this->resetPage();
        $this->checkFiltersStatus();
    }

    public function updatingEnseignantFilter()
    {
        $this->resetPage();
        $this->checkFiltersStatus();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
        $this->checkFiltersStatus();
        
        // Validation côté client
        if (!empty($this->date_from) && !empty($this->date_to)) {
            if (strtotime($this->date_from) > strtotime($this->date_to)) {
                $this->date_to = '';
                toastr()->warning('La date de fin doit être postérieure à la date de début.');
            }
        }
    }

    public function updatedDateTo()
    {
        $this->resetPage();
        $this->checkFiltersStatus();
        
        // Validation côté client
        if (!empty($this->date_from) && !empty($this->date_to)) {
            if (strtotime($this->date_to) < strtotime($this->date_from)) {
                $this->date_from = '';
                toastr()->warning('La date de début doit être antérieure à la date de fin.');
            }
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'date_from', 'date_to', 'enseignant_filter']);
        $this->resetPage();
        $this->checkFiltersStatus();
        toastr()->success('Filtres réinitialisés');
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    private function checkFiltersStatus()
    {
        $this->hasFilters = !empty($this->search) || 
                           !empty($this->date_from) || 
                           !empty($this->date_to) || 
                           !empty($this->enseignant_filter);
    }

    // Méthodes de tri
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

    // EC Edition methods avec amélioration
    public function editEC($examenId, $ecId)
    {
        try {
            $this->editingExamenId = $examenId;
            $this->editingEC = EC::find($ecId);
            
            if (!$this->editingEC) {
                toastr()->error('Matière introuvable.');
                return;
            }

            // Récupérer les données de la relation pivot
            $examenEc = DB::table('examen_ec')
                ->where('examen_id', $examenId)
                ->where('ec_id', $ecId)
                ->first();

            if ($examenEc) {
                $this->editingECData = [
                    'date_specifique' => $examenEc->date_specifique,
                    'heure_specifique' => $examenEc->heure_specifique ? 
                        \Carbon\Carbon::parse($examenEc->heure_specifique)->format('H:i') : '',
                    'salle_id' => $examenEc->salle_id,
                    'code_base' => $examenEc->code_base
                ];
            } else {
                // Valeurs par défaut si pas de données pivot
                $this->editingECData = [
                    'date_specifique' => now()->format('Y-m-d'),
                    'heure_specifique' => '08:00',
                    'salle_id' => '',
                    'code_base' => ''
                ];
            }

            $this->showEditECModal = true;

            Log::info("✏️ DÉBUT ÉDITION EC", [
                'examen_id' => $examenId,
                'ec_id' => $ecId,
                'ec_nom' => $this->editingEC->nom,
                'user_id' => Auth::id()
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ ERREUR OUVERTURE ÉDITION EC", [
                'examen_id' => $examenId,
                'ec_id' => $ecId,
                'error' => $e->getMessage()
            ]);
            
            toastr()->error('Erreur lors de l\'ouverture de l\'édition.');
        }
    }

    public function saveEC()
    {
        $this->validate();

        try {
            // Vérifier les conflits de salle si une salle est sélectionnée
            if (!empty($this->editingECData['salle_id'])) {
                $examen = Examen::find($this->editingExamenId);
                $conflits = Examen::verifierConflitsSalles([[
                    'ec_id' => $this->editingEC->id,
                    'date' => $this->editingECData['date_specifique'],
                    'heure' => $this->editingECData['heure_specifique'],
                    'salle_id' => $this->editingECData['salle_id'],
                ]], $examen->duree, $this->editingExamenId);

                if (!empty($conflits)) {
                    toastr()->warning('Conflit de salle détecté pour cette matière !');
                    return;
                }
            }

            // Validation supplémentaire de la date
            $dateExamen = \Carbon\Carbon::parse($this->editingECData['date_specifique']);
            if ($dateExamen->isPast() && !$dateExamen->isToday()) {
                toastr()->warning('Attention: Vous programmez un examen dans le passé.');
            }

            // Mettre à jour la relation pivot
            DB::table('examen_ec')
                ->where('examen_id', $this->editingExamenId)
                ->where('ec_id', $this->editingEC->id)
                ->update([
                    'date_specifique' => $this->editingECData['date_specifique'],
                    'heure_specifique' => $this->editingECData['heure_specifique'],
                    'salle_id' => $this->editingECData['salle_id'] ?: null,
                    'code_base' => $this->editingECData['code_base'] ?: null,
                    'updated_at' => now(),
                ]);

            Log::info("✅ EC MODIFIÉE", [
                'examen_id' => $this->editingExamenId,
                'ec_id' => $this->editingEC->id,
                'ec_nom' => $this->editingEC->nom,
                'nouvelles_donnees' => $this->editingECData,
                'user_id' => Auth::id()
            ]);

            toastr()->success("Matière \"{$this->editingEC->nom}\" modifiée avec succès !");
            $this->closeEditECModal();

            // Rafraîchir les données
            $this->dispatch('examensUpdated');

        } catch (\Exception $e) {
            Log::error("❌ ERREUR MODIFICATION EC", [
                'examen_id' => $this->editingExamenId,
                'ec_id' => $this->editingEC->id,
                'error' => $e->getMessage()
            ]);

            toastr()->error('Une erreur est survenue lors de la modification.');
        }
    }

    public function closeEditECModal()
    {
        $this->showEditECModal = false;
        $this->editingExamenId = null;
        $this->editingEC = null;
        $this->editingECData = [
            'date_specifique' => '',
            'heure_specifique' => '',
            'salle_id' => '',
            'code_base' => ''
        ];
        $this->resetValidation();
    }

    // Méthode pour fermer tous les modals (utilisée par le raccourci clavier)
    public function closeAllModals()
    {
        $this->closeEditECModal();
        $this->closeDeleteECModal();
        $this->cancelDelete();
    }

    // EC Deletion methods avec amélioration
    public function confirmDeleteEC($examenId, $ecId)
    {
        $this->examenToDeleteFrom = Examen::find($examenId);
        $this->ecToDelete = EC::find($ecId);
        
        if (!$this->examenToDeleteFrom || !$this->ecToDelete) {
            toastr()->error('Données introuvables.');
            return;
        }

        // Vérifier les dépendances avant d'ouvrir le modal
        $copiesCount = Copie::whereHas('codeAnonymat', function($q) use ($examenId, $ecId) {
            $q->where('examen_id', $examenId)
              ->where('ec_id', $ecId);
        })->count();

        $manchettesCount = Manchette::whereHas('codeAnonymat', function($q) use ($examenId, $ecId) {
            $q->where('examen_id', $examenId)
              ->where('ec_id', $ecId);
        })->count();

        if ($copiesCount > 0 || $manchettesCount > 0) {
            toastr()->error("Cette matière possède {$copiesCount} copies et {$manchettesCount} manchettes. Suppression impossible.");
            return;
        }

        Log::info("⚠️ DEMANDE CONFIRMATION SUPPRESSION EC", [
            'examen_id' => $examenId,
            'ec_id' => $ecId,
            'ec_nom' => $this->ecToDelete->nom,
            'user_id' => Auth::id()
        ]);

        $this->showDeleteECModal = true;
    }

    public function deleteEC()
    {
        if (!$this->examenToDeleteFrom || !$this->ecToDelete) {
            toastr()->error('Données manquantes pour la suppression.');
            return;
        }

        try {
            // Double vérification des dépendances
            $copiesCount = Copie::whereHas('codeAnonymat', function($q) {
                $q->where('examen_id', $this->examenToDeleteFrom->id)
                  ->where('ec_id', $this->ecToDelete->id);
            })->count();

            $manchettesCount = Manchette::whereHas('codeAnonymat', function($q) {
                $q->where('examen_id', $this->examenToDeleteFrom->id)
                  ->where('ec_id', $this->ecToDelete->id);
            })->count();

            if ($copiesCount > 0 || $manchettesCount > 0) {
                toastr()->error('Cette matière possède des copies ou manchettes et ne peut pas être supprimée.');
                $this->closeDeleteECModal();
                return;
            }

            DB::transaction(function () {
                // Supprimer les codes d'anonymat pour cette EC
                $codesCount = $this->examenToDeleteFrom->codesAnonymat()
                    ->where('ec_id', $this->ecToDelete->id)->count();
                
                if ($codesCount > 0) {
                    $this->examenToDeleteFrom->codesAnonymat()
                        ->where('ec_id', $this->ecToDelete->id)->delete();
                }

                // Détacher l'EC de l'examen
                $this->examenToDeleteFrom->ecs()->detach($this->ecToDelete->id);

                Log::info("✅ EC SUPPRIMÉE DE L'EXAMEN", [
                    'examen_id' => $this->examenToDeleteFrom->id,
                    'ec_id' => $this->ecToDelete->id,
                    'ec_nom' => $this->ecToDelete->nom,
                    'codes_supprimes' => $codesCount,
                    'user_id' => Auth::id()
                ]);
            });

            toastr()->success("Matière \"{$this->ecToDelete->nom}\" supprimée de l'examen.");
            $this->closeDeleteECModal();

            // Rafraîchir les données
            $this->dispatch('examensUpdated');

        } catch (\Exception $e) {
            Log::error("❌ ERREUR SUPPRESSION EC", [
                'examen_id' => $this->examenToDeleteFrom->id,
                'ec_id' => $this->ecToDelete->id,
                'error' => $e->getMessage()
            ]);

            toastr()->error('Une erreur est survenue lors de la suppression.');
        }
    }

    public function closeDeleteECModal()
    {
        $this->showDeleteECModal = false;
        $this->examenToDeleteFrom = null;
        $this->ecToDelete = null;
    }

    // Exam deletion methods avec amélioration
    public function confirmDelete($examenId)
    {
        $this->examenToDelete = Examen::with(['copies', 'manchettes', 'codesAnonymat', 'niveau', 'parcours'])
            ->find($examenId);
        
        if (!$this->examenToDelete) {
            toastr()->error('Examen introuvable.');
            return;
        }

        // Vérifier immédiatement s'il y a des dépendances
        $copiesCount = $this->examenToDelete->copies()->count();
        $manchettesCount = $this->examenToDelete->manchettes()->count();

        if ($copiesCount > 0 || $manchettesCount > 0) {
            toastr()->error("Impossible de supprimer cet examen. Il contient {$copiesCount} copies et {$manchettesCount} manchettes.");
            $this->examenToDelete = null;
            return;
        }

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
            toastr()->error('Aucun examen sélectionné pour suppression.');
            return;
        }

        $examen = $this->examenToDelete;

        try {
            // Triple vérification des dépendances
            $copiesCount = $examen->copies()->count();
            $manchettesCount = $examen->manchettes()->count();
            $codesCount = $examen->codesAnonymat()->count();

            if ($copiesCount > 0 || $manchettesCount > 0) {
                toastr()->error('Cet examen possède des données liées (copies, manchettes ou résultats) et ne peut pas être supprimé.');
                $this->showDeleteModal = false;
                $this->examenToDelete = null;
                return;
            }

            DB::transaction(function () use ($examen, $codesCount) {
                // Supprimer les codes d'anonymat en premier
                if ($codesCount > 0) {
                    $examen->codesAnonymat()->delete();
                }

                // Détacher les ECs (supprime les relations dans examen_ec)
                $examen->ecs()->detach();

                // Supprimer l'examen
                $examen->delete();

                Log::info("✅ EXAMEN SUPPRIMÉ", [
                    'examen_id' => $examen->id,
                    'niveau_id' => $examen->niveau_id,
                    'parcours_id' => $examen->parcours_id,
                    'codes_supprimes' => $codesCount,
                    'user_id' => Auth::id()
                ]);
            });

            toastr()->success('Examen supprimé avec succès.');

            // Nettoyer le cache
            Cache::forget("examens_stats_{$this->niveauId}_{$this->parcoursId}");

            // Rafraîchir les données
            $this->dispatch('examensUpdated');

        } catch (\Exception $e) {
            Log::error("❌ ERREUR SUPPRESSION EXAMEN", [
                'examen_id' => $examen->id,
                'error' => $e->getMessage()
            ]);

            toastr()->error('Une erreur est survenue lors de la suppression : ' . $e->getMessage());
        } finally {
            $this->showDeleteModal = false;
            $this->examenToDelete = null;
        }
    }

    // Méthodes d'export et d'impression
    public function exportExamens($format = 'xlsx')
    {
        try {
            $examens = $this->getFilteredExamens();
            
            if ($examens->isEmpty()) {
                toastr()->warning('Aucun examen à exporter avec les filtres actuels.');
                return;
            }

            // Logique d'export selon le format
            switch ($format) {
                case 'xlsx':
                    return $this->exportToExcel($examens);
                case 'pdf':
                    return $this->exportToPdf($examens);
                case 'csv':
                    return $this->exportToCsv($examens);
                default:
                    toastr()->error('Format d\'export non supporté.');
            }

        } catch (\Exception $e) {
            Log::error("❌ ERREUR EXPORT EXAMENS", [
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            toastr()->error('Erreur lors de l\'export.');
        }
    }

    private function getFilteredExamens()
    {
        $baseQuery = Examen::with([
            'ecs.ue',
            'niveau',
            'parcours',
            'copies',
            'manchettes',
            'codesAnonymat'
        ])
        ->where('niveau_id', $this->niveauId)
        ->where('parcours_id', $this->parcoursId);

        // Appliquer les filtres
        if (!empty($this->search)) {
            $searchTerm = '%' . trim($this->search) . '%';
            $baseQuery->whereHas('ecs', function($q) use ($searchTerm) {
                $q->where('ecs.nom', 'like', $searchTerm)
                  ->orWhere('ecs.abr', 'like', $searchTerm)
                  ->orWhere('ecs.enseignant', 'like', $searchTerm);
            });
        }

        if (!empty($this->enseignant_filter)) {
            $baseQuery->whereHas('ecs', function($q) {
                $q->where('ecs.enseignant', $this->enseignant_filter);
            });
        }

        if (!empty($this->date_from)) {
            $baseQuery->whereHas('ecs', function($q) {
                $q->whereDate('examen_ec.date_specifique', '>=', $this->date_from);
            });
        }

        if (!empty($this->date_to)) {
            $baseQuery->whereHas('ecs', function($q) {
                $q->whereDate('examen_ec.date_specifique', '<=', $this->date_to);
            });
        }

        return $baseQuery->orderBy($this->sortField, $this->sortDirection)->get();
    }

    // Méthodes de statistiques
    public function getExamensStatistics()
    {
        if (!$this->niveauId || !$this->parcoursId) {
            return null;
        }

        return Cache::remember("examens_stats_{$this->niveauId}_{$this->parcoursId}", 3600, function() {
            $examens = Examen::where('niveau_id', $this->niveauId)
                ->where('parcours_id', $this->parcoursId)
                ->with(['ecs', 'copies', 'manchettes', 'codesAnonymat'])
                ->get();

            $totalExamens = $examens->count();
            $totalECs = $examens->sum(function($examen) {
                return $examen->ecs->count();
            });

            $totalCopies = $examens->sum(function($examen) {
                return $examen->copies->count();
            });

            $totalManchettes = $examens->sum(function($examen) {
                return $examen->manchettes->count();
            });

            $examensComplets = $examens->filter(function($examen) {
                $totalCodes = $examen->codesAnonymat->count();
                $totalCopies = $examen->copies->count();
                return $totalCodes > 0 && $totalCopies >= $totalCodes;
            })->count();

            $enseignantsUniques = $examens->flatMap(function($examen) {
                return $examen->ecs->pluck('enseignant');
            })->filter()->unique()->count();

            return [
                'total_examens' => $totalExamens,
                'total_ecs' => $totalECs,
                'total_copies' => $totalCopies,
                'total_manchettes' => $totalManchettes,
                'examens_complets' => $examensComplets,
                'enseignants_uniques' => $enseignantsUniques,
                'taux_completion' => $totalExamens > 0 ? round(($examensComplets / $totalExamens) * 100, 1) : 0
            ];
        });
    }

    // Helper methods améliorés
    private function loadDataFromQueryParams()
    {
        $this->niveauId = $this->cleanInputValue($this->niveauId);
        $this->parcoursId = $this->cleanInputValue($this->parcoursId);

        if (!empty($this->niveauId)) {
            if ($this->loadNiveauInfo()) {
                $this->step = 'parcours';
            } else {
                $this->reset(['niveauId', 'niveauInfo', 'parcoursId', 'parcoursInfo']);
                $this->step = 'niveau';
                toastr()->warning('Niveau introuvable, retour à la sélection.');
                return;
            }
        }

        if (!empty($this->parcoursId)) {
            if ($this->loadParcoursInfo()) {
                $this->step = 'examens';
            } else {
                $this->reset(['parcoursId', 'parcoursInfo']);
                $this->step = 'parcours';
                toastr()->warning('Parcours introuvable, retour à la sélection.');
            }
        }
    }

    private function cleanInputValue($value)
    {
        if (is_array($value) && isset($value['value'])) {
            return $value['value'];
        }
        return $value;
    }

    private function loadNiveauInfo()
    {
        try {
            $niveau = Niveau::where('is_active', true)->find($this->niveauId);
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

                // Vérifier la cohérence avec le niveau
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

    public function getEnseignantsDisponibles()
    {
        if (!$this->niveauId || !$this->parcoursId) {
            return collect();
        }

        try {
            return Cache::remember("enseignants_{$this->niveauId}_{$this->parcoursId}", 1800, function() {
                return DB::table('examens')
                    ->join('examen_ec', 'examens.id', '=', 'examen_ec.examen_id')
                    ->join('ecs', 'examen_ec.ec_id', '=', 'ecs.id')
                    ->where('examens.niveau_id', $this->niveauId)
                    ->where('examens.parcours_id', $this->parcoursId)
                    ->whereNotNull('ecs.enseignant')
                    ->where('ecs.enseignant', '!=', '')
                    ->whereNull('examens.deleted_at')
                    ->distinct()
                    ->pluck('ecs.enseignant')
                    ->filter()
                    ->sort()
                    ->values();
            });
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des enseignants: ' . $e->getMessage());
            return collect();
        }
    }

    private function attachStatisticsToExamen($examen)
    {
        $copiesStatusByEc = [];
        $manchettesStatusByEc = [];

        foreach ($examen->ecs as $ec) {
            $totalCodes = $examen->codesAnonymat()->where('ec_id', $ec->id)->count();
            
            $copiesSaisies = Copie::whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
            })->count();

            $manchettesSaisies = Manchette::whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
            })->count();

            $copiesStatusByEc[$ec->id] = [
                'saisies' => $copiesSaisies,
                'total' => $totalCodes ?: 0
            ];

            $manchettesStatusByEc[$ec->id] = [
                'saisies' => $manchettesSaisies,
                'total' => $totalCodes ?: 0
            ];
        }

        $examen->copiesStatusByEc = $copiesStatusByEc;
        $examen->manchettesStatusByEc = $manchettesStatusByEc;

        return $examen;
    }

    // Méthodes pour les raccourcis clavier
    public function handleKeyboardShortcut($key)
    {
        switch ($key) {
            case 'escape':
                $this->closeAllModals();
                break;
            case 'ctrl+f':
                $this->dispatch('focusSearch');
                break;
            case 'ctrl+r':
                $this->resetFilters();
                break;
            case 'ctrl+e':
                $this->toggleAdvancedFilters();
                break;
        }
    }

    // Méthode pour la pagination personnalisée
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $niveaux = Niveau::where('is_active', true)->orderBy('nom')->get();

        $parcours = collect();
        if ($this->step === 'parcours' && $this->niveauId) {
            $parcours = Parcour::where('niveau_id', $this->niveauId)->orderBy('nom')->get();
        }

        $salles = Salle::orderBy('nom')->get();
        $enseignants = $this->getEnseignantsDisponibles();
        $stats = $this->getExamensStatistics();

        $examens = collect();
        if ($this->step === 'examens' && $this->niveauId && $this->parcoursId) {
            
            $baseQuery = Examen::with([
                'ecs.ue',
                'niveau',
                'parcours',
                'copies',
                'manchettes',
                'codesAnonymat'
            ])
            ->where('niveau_id', $this->niveauId)
            ->where('parcours_id', $this->parcoursId);

            // Filtres optimisés
            if (!empty($this->search)) {
                $searchTerm = '%' . trim($this->search) . '%';
                $baseQuery->whereHas('ecs', function($q) use ($searchTerm) {
                    $q->where('ecs.nom', 'like', $searchTerm)
                      ->orWhere('ecs.abr', 'like', $searchTerm)
                      ->orWhere('ecs.enseignant', 'like', $searchTerm);
                });
            }

            if (!empty($this->enseignant_filter)) {
                $baseQuery->whereHas('ecs', function($q) {
                    $q->where('ecs.enseignant', $this->enseignant_filter);
                });
            }

            if (!empty($this->date_from)) {
                $baseQuery->whereHas('ecs', function($q) {
                    $q->whereDate('examen_ec.date_specifique', '>=', $this->date_from);
                });
            }

            if (!empty($this->date_to)) {
                $baseQuery->whereHas('ecs', function($q) {
                    $q->whereDate('examen_ec.date_specifique', '<=', $this->date_to);
                });
            }

            $examens = $baseQuery->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage);

            // Traitement optimisé des données pour l'affichage
            foreach ($examens as $examen) {
                // FILTRAGE DES ECs SELON LES CRITÈRES DE RECHERCHE ET ENSEIGNANT
                $ecsFiltered = $examen->ecs;
                
                // Si filtre enseignant actif, ne garder que les ECs de cet enseignant
                if (!empty($this->enseignant_filter)) {
                    $ecsFiltered = $ecsFiltered->filter(function($ec) {
                        return $ec->enseignant === $this->enseignant_filter;
                    });
                }
                
                // Si recherche active, filtrer aussi par nom/abréviation d'EC
                if (!empty($this->search)) {
                    $searchTerm = strtolower(trim($this->search));
                    $ecsFiltered = $ecsFiltered->filter(function($ec) use ($searchTerm) {
                        return str_contains(strtolower($ec->nom), $searchTerm) ||
                               str_contains(strtolower($ec->abr ?? ''), $searchTerm) ||
                               str_contains(strtolower($ec->enseignant ?? ''), $searchTerm);
                    });
                }
                
                // Grouper les ECs filtrées par UE avec optimisation
                $ecsGroupedByUE = $ecsFiltered->groupBy('ue_id')->map(function($ecs, $ue_id) {
                    $ue = $ecs->first()->ue; // Utiliser la relation déjà chargée
                    return [
                        'ue' => $ue,
                        'ue_nom' => $ue ? $ue->nom : 'UE inconnue',
                        'ue_abr' => $ue ? $ue->abr : 'UE',
                        'ecs' => $ecs
                    ];
                })->filter(function($group) {
                    return $group['ecs']->isNotEmpty();
                });
                
                $examen->ecsGroupedByUE = $ecsGroupedByUE;
                $examen = $this->attachStatisticsToExamen($examen);
            }
        }

        return view('livewire.examen.index-examen', [
            'niveaux' => $niveaux,
            'parcours' => $parcours,
            'examens' => $examens,
            'enseignants' => $enseignants,
            'salles' => $salles,
            'stats' => $stats,
        ]);
    }
}