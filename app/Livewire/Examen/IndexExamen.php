<?php

namespace App\Livewire\Examen;

use App\Models\EC;
use App\Models\UE;
use App\Models\Copie;
use App\Models\Salle;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Manchette;
use Livewire\WithPagination;
use App\Exports\ExamensExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\Exports\ExamensEnseignantExport;

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
        if (!Auth::user()->hasAnyRole(['superadmin', 'enseignant'])) {
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

    private function emptyStats(): array
    {
        return [
            'total_examens'        => 0,
            'total_ues'            => 0,
            'total_ecs'            => 0,
            'enseignants_uniques'  => 0,
            'ecs_planifiees'       => 0,
            'taux_planification'   => 0.0,
            'total_credits_ues'    => 0,   // 👈 NOUVEAU
        ];
    }

    public function getExamensStatistics(): array
    {
        if (!$this->niveauId || !$this->parcoursId) {
            return $this->emptyStats();
        }

        return \Cache::remember("examens_stats_v3_{$this->niveauId}_{$this->parcoursId}", 3600, function () {
            $stats = $this->emptyStats();

            $examens = Examen::where('niveau_id', $this->niveauId)
                ->where('parcours_id', $this->parcoursId)
                ->with(['ecs.ue']) // on a besoin de ue_id et des libellés
                ->get();

            $stats['total_examens'] = $examens->count();
            $stats['total_ecs']     = $examens->sum(fn ($ex) => $ex->ecs->count());

            // UEs distinctes impliquées (via les EC)
            $ueIds = $examens->flatMap(fn ($ex) => $ex->ecs->pluck('ue_id'))
                ->filter()
                ->unique()
                ->values();

            $stats['total_ues'] = $ueIds->count();

            // Total des crédits des UEs (colonne UE.credits)
            $stats['total_credits_ues'] = $ueIds->isEmpty()
                ? 0
                : (float) \App\Models\UE::whereIn('id', $ueIds)->sum('credits');

            // Enseignants uniques
            $stats['enseignants_uniques'] = $examens->flatMap(fn ($ex) => $ex->ecs->pluck('enseignant'))
                ->filter()
                ->unique()
                ->count();

            // EC planifiées = date+heure sur le pivot
            $ecsPlanifiees = 0;
            foreach ($examens as $ex) {
                foreach ($ex->ecs as $ec) {
                    if (!empty($ec->pivot->date_specifique) && !empty($ec->pivot->heure_specifique)) {
                        $ecsPlanifiees++;
                    }
                }
            }
            $stats['ecs_planifiees'] = $ecsPlanifiees;

            $stats['taux_planification'] = $stats['total_ecs'] > 0
                ? round(($ecsPlanifiees / $stats['total_ecs']) * 100, 1)
                : 0.0;

            return $stats;
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


    /**
     * Export des examens selon le format et le type
     */
    public function exportExamens($format = 'excel', $type = 'all')
    {
        try {
            if (!$this->niveauId || !$this->parcoursId) {
                toastr()->error('Niveau et parcours requis pour l\'export.');
                return;
            }

            // Récupérer les données selon le type
            $examens = $this->getExamensForExport($type);
            
            if ($examens->isEmpty()) {
                toastr()->warning('Aucun examen à exporter avec les critères sélectionnés.');
                return;
            }

            // Générer le nom de fichier
            $filename = $this->generateExportFilename($format, $type);

            // Log de l'export
            Log::info("📊 EXPORT EXAMENS", [
                'format' => $format,
                'type' => $type,
                'niveau_id' => $this->niveauId,
                'parcours_id' => $this->parcoursId,
                'nb_examens' => $examens->count(),
                'enseignant_filter' => $this->enseignant_filter,
                'user_id' => Auth::id()
            ]);

            // Exporter selon le format
            switch ($format) {
                case 'excel':
                    return $this->exportToExcel($examens, $type, $filename);
                case 'pdf':
                    return $this->exportToPdf($examens, $type, $filename);
                default:
                    toastr()->error('Format d\'export non supporté.');
                    return;
            }

        } catch (\Exception $e) {
            Log::error("❌ ERREUR EXPORT EXAMENS", [
                'format' => $format,
                'type' => $type,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            toastr()->error('Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    /**
     * Récupère les examens selon le type d'export
     */
    private function getExamensForExport($type)
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

        // Appliquer les filtres selon le type
        switch ($type) {
            case 'all':
                // Appliquer tous les filtres actifs
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
                break;

            case 'enseignant':
                // Filtrer uniquement par enseignant
                if (!empty($this->enseignant_filter)) {
                    $baseQuery->whereHas('ecs', function($q) {
                        $q->where('ecs.enseignant', $this->enseignant_filter);
                    });
                } else {
                    // Si pas d'enseignant sélectionné, retourner collection vide
                    return collect();
                }
                break;
        }

        return $baseQuery->orderBy('created_at', 'asc')->get();
    }

    /**
     * Export Excel
     */
    private function exportToExcel($examens, $type, $filename)
    {
        try {
            // Utiliser les classes d'export existantes
            if ($type === 'enseignant' && !empty($this->enseignant_filter)) {
                return Excel::download(
                    new ExamensEnseignantExport($examens, $this->enseignant_filter, $this->niveauInfo, $this->parcoursInfo),
                    $filename
                );
            } else {
                return Excel::download(
                    new ExamensExport($examens, $this->niveauInfo, $this->parcoursInfo, $this->getActiveFilters()),
                    $filename
                );
            }
            
        } catch (\Exception $e) {
            Log::error("❌ ERREUR EXPORT EXCEL", [
                'error' => $e->getMessage(),
                'type' => $type,
                'user_id' => Auth::id()
            ]);
            
            toastr()->error('Erreur lors de l\'export Excel : ' . $e->getMessage());
            return;
        }
    }

    /**
     * Export PDF
     */
    private function exportToPdf($examens, $type, $filename)
    {
        try {
            // Si c'est un export par enseignant, préparer les données comme dans ExamensEnseignantExport
            if ($type === 'enseignant') {
                $data = [];
                $totalHeures = 0;
                $totalCredits = 0;
                
                // Filtrer et préparer les données pour cet enseignant
                foreach ($examens as $examen) {
                    foreach ($examen->ecs as $ec) {
                        // Ne garder que les ECs de cet enseignant
                        if ($ec->enseignant !== $this->enseignant_filter) {
                            continue;
                        }

                        $salle = $ec->pivot->salle_id ? 
                            \App\Models\Salle::find($ec->pivot->salle_id) : null;
                        
                        // Calculer les statistiques
                        $copiesCount = $examen->copies()->whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                            $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
                        })->count();
                        
                        $manchettesCount = $examen->manchettes()->whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                            $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
                        })->count();

                        $totalCodes = $examen->codesAnonymat()->where('ec_id', $ec->id)->count();

                        // Récupérer les crédits de l'UE
                        $ueCredits = $ec->ue->credits ?? 0;
                        $totalCredits += $ueCredits;

                        // Déterminer le statut
                        if ($totalCodes == 0) {
                            $statut = 'Aucun code';
                        } elseif ($copiesCount >= $totalCodes && $manchettesCount >= $totalCodes) {
                            $statut = 'Complet';
                        } elseif ($copiesCount > 0 || $manchettesCount > 0) {
                            $statut = 'En cours';
                        } else {
                            $statut = 'Non commencé';
                        }

                        $data[] = [
                            'examen_id' => $examen->id,
                            'ue_abr' => $ec->ue->abr ?? '',
                            'ue_nom' => $ec->ue->nom ?? '',
                            'ue_credits' => $ueCredits,
                            'ec_abr' => $ec->abr ?? '',
                            'ec_nom' => $ec->nom,
                            'date' => $ec->pivot->date_specifique ? 
                                \Carbon\Carbon::parse($ec->pivot->date_specifique)->format('d/m/Y') : '',
                            'heure' => $ec->pivot->heure_specifique ? 
                                \Carbon\Carbon::parse($ec->pivot->heure_specifique)->format('H:i') : '',
                            'heure_fin' => $ec->pivot->heure_specifique ? 
                                \Carbon\Carbon::parse($ec->pivot->heure_specifique)->addMinutes($examen->duree)->format('H:i') : '',
                            'duree' => $examen->duree,
                            'salle' => $salle ? $salle->nom : '',
                            'code_base' => $ec->pivot->code_base ?? '',
                            'copies_saisies' => $copiesCount,
                            'manchettes_saisies' => $manchettesCount,
                            'total_codes' => $totalCodes,
                            'statut' => $statut,
                            'note_eliminatoire' => $examen->note_eliminatoire
                        ];

                        $totalHeures += $examen->duree;
                    }
                }

                // Trier les données par date puis par heure
                usort($data, function($a, $b) {
                    if ($a['date'] === $b['date']) {
                        return strcmp($a['heure'], $b['heure']);
                    }
                    return strcmp($a['date'], $b['date']);
                });

                // Préparer les données pour la vue PDF enseignant
                $pdfData = [
                    'data' => collect($data),
                    'enseignant' => $this->enseignant_filter,
                    'niveau' => $this->niveauInfo,
                    'parcours' => $this->parcoursInfo,
                    'generated_at' => now()->format('d/m/Y H:i'),
                    'generated_by' => Auth::user()->name ?? 'Système',
                    'total_examens' => count($data),
                    'total_heures' => $totalHeures,
                    'total_credits' => $totalCredits,
                    'moyenne_duree' => count($data) > 0 ? round($totalHeures / count($data)) : 0,
                    'dates_examens' => collect($data)->pluck('date')->filter()->unique()->sort()->values()
                ];

            } else {
                // Export général - calculer les statistiques normalement
                $totalEcs = $examens->sum(function($examen) {
                    return $examen->ecs->count();
                });

                $totalMinutes = $examens->sum('duree') * $totalEcs;

                $enseignantsUniques = $examens->flatMap(function($examen) {
                    return $examen->ecs->pluck('enseignant');
                })->filter()->unique()->count();

                $pdfData = [
                    'examens' => $examens,
                    'niveau' => $this->niveauInfo,
                    'parcours' => $this->parcoursInfo,
                    'type' => $type,
                    'enseignant' => null,
                    'filters' => $this->getActiveFilters(),
                    'generated_at' => now()->format('d/m/Y H:i'),
                    'generated_by' => Auth::user()->name ?? 'Système',
                    'total_examens' => $examens->count(),
                    'total_ecs' => $totalEcs,
                    'total_minutes' => $totalMinutes,
                    'enseignants_uniques_count' => $enseignantsUniques
                ];
            }

            // Choisir la vue selon le type
            $view = $type === 'enseignant' ? 'exports.examens-enseignant-pdf' : 'exports.examens-pdf';

            // Générer le PDF avec configuration SIMPLE
            $pdf = Pdf::loadView($view, $pdfData)
                ->setPaper('a4', 'landscape');

            // Utiliser response()->streamDownload
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (\Exception $e) {
            Log::error("❌ ERREUR EXPORT PDF", [
                'error' => $e->getMessage(),
                'type' => $type,
                'user_id' => Auth::id()
            ]);
            
            toastr()->error('Erreur lors de l\'export PDF : ' . $e->getMessage());
            return;
        }
    }

    /**
     * Génère le nom de fichier pour l'export
     */
    private function generateExportFilename($format, $type)
    {
        $extension = $format === 'excel' ? 'xlsx' : 'pdf';
        $niveau_abr = $this->niveauInfo['abr'] ?? 'N';
        $parcours_abr = $this->parcoursInfo['abr'] ?? 'P';
        
        $base = "examens_{$niveau_abr}_{$parcours_abr}";
        
        if ($type === 'enseignant' && !empty($this->enseignant_filter)) {
            $enseignant_clean = preg_replace('/[^a-zA-Z0-9]/', '_', $this->enseignant_filter);
            $base .= "_{$enseignant_clean}";
        }
        
        if ($this->hasFilters && $type === 'all') {
            $base .= "_filtre";
        }
        
        $timestamp = now()->format('Y-m-d_H-i');
        
        return "{$base}_{$timestamp}.{$extension}";
    }

    /**
     * Récupère les filtres actifs
     */
    private function getActiveFilters()
    {
        $filters = [];
        
        if (!empty($this->search)) {
            $filters['Recherche'] = $this->search;
        }
        
        if (!empty($this->enseignant_filter)) {
            $filters['Enseignant'] = $this->enseignant_filter;
        }
        
        if (!empty($this->date_from)) {
            $filters['Date debut'] = \Carbon\Carbon::parse($this->date_from)->format('d/m/Y');
        }
        
        if (!empty($this->date_to)) {
            $filters['Date fin'] = \Carbon\Carbon::parse($this->date_to)->format('d/m/Y');
        }
        
        return $filters;
    }

    /**
     * Export rapide au format CSV (bonus)
     */
    public function exportToCsv($type = 'all')
    {
        try {
            $examens = $this->getExamensForExport($type);
            
            if ($examens->isEmpty()) {
                toastr()->warning('Aucun examen à exporter.');
                return;
            }

            $filename = $this->generateExportFilename('csv', $type);
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($examens) {
                $file = fopen('php://output', 'w');
                
                // En-têtes CSV
                fputcsv($file, [
                    'Examen ID',
                    'UE Abréviation',
                    'UE Nom',
                    'EC Abréviation', 
                    'EC Nom',
                    'Enseignant',
                    'Date',
                    'Heure',
                    'Salle',
                    'Code',
                    'Durée (min)',
                    'Copies',
                    'Manchettes'
                ], ';');

                // Données
                foreach ($examens as $examen) {
                    foreach ($examen->ecs as $ec) {
                        $salle = $ec->pivot->salle_id ? 
                            \App\Models\Salle::find($ec->pivot->salle_id)?->nom : '';
                        
                        $copiesCount = $examen->copies()->whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                            $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
                        })->count();
                        
                        $manchettesCount = $examen->manchettes()->whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                            $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
                        })->count();

                        fputcsv($file, [
                            $examen->id,
                            $ec->ue->abr ?? '',
                            $ec->ue->nom ?? '',
                            $ec->abr ?? '',
                            $ec->nom,
                            $ec->enseignant ?? '',
                            $ec->pivot->date_specifique ? 
                                \Carbon\Carbon::parse($ec->pivot->date_specifique)->format('d/m/Y') : '',
                            $ec->pivot->heure_specifique ? 
                                \Carbon\Carbon::parse($ec->pivot->heure_specifique)->format('H:i') : '',
                            $salle,
                            $ec->pivot->code_base ?? '',
                            $examen->duree,
                            $copiesCount,
                            $manchettesCount
                        ], ';');
                    }
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error("❌ ERREUR EXPORT CSV", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            toastr()->error('Erreur lors de l\'export CSV.');
        }
    }

    /**
     * Nettoie les données des examens
     */
    private function cleanExamensData($examens)
    {
        foreach($examens as $examen) {
            foreach($examen->ecs as $ec) {
                // Nettoyer les chaînes de caractères
                $ec->nom = $this->cleanString($ec->nom ?? '');
                $ec->abr = $this->cleanString($ec->abr ?? '');
                $ec->enseignant = $this->cleanString($ec->enseignant ?? '');
                
                // Nettoyer les données de l'UE
                if($ec->ue) {
                    $ec->ue->nom = $this->cleanString($ec->ue->nom ?? '');
                    $ec->ue->abr = $this->cleanString($ec->ue->abr ?? '');
                }
                
                // Nettoyer les données pivot
                if($ec->pivot) {
                    $ec->pivot->code_base = $this->cleanString($ec->pivot->code_base ?? '');
                }
            }
        }
        
        return $examens;
    }
    
    /**
     * Nettoie une chaîne de caractères - VERSION CORRIGÉE UTF-8
     */
    private function cleanString($string)
    {
        if (empty($string)) {
            return '';
        }
        
        // Convertir en string si ce n'est pas déjà le cas
        $string = (string) $string;
        
        // Forcer la conversion UTF-8 sécurisée
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        
        // Supprimer les caractères de contrôle et invisibles
        $string = preg_replace('/[\x00-\x1F\x7F-\x9F]/', '', $string);
        
        // Remplacer les caractères problématiques
        $replacements = [
            '"' => '',
            "'" => '',
            '`' => '',
            '€' => 'EUR',
            '©' => '(c)',
            '®' => '(r)',
            // Remplacer les accents
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ã' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Õ' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N'
        ];
        
        $string = str_replace(array_keys($replacements), array_values($replacements), $string);
        
        return trim($string);
    }

    /**
     * Nettoie un tableau de données
     */
    private function cleanArrayData($array)
    {
        if (!is_array($array)) {
            return $array;
        }
        
        $cleaned = [];
        foreach($array as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = $this->cleanString($value);
            } else if (is_array($value)) {
                $cleaned[$key] = $this->cleanArrayData($value);
            } else {
                $cleaned[$key] = $value;
            }
        }
        
        return $cleaned;
    }
}