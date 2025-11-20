<?php

namespace App\Livewire\Manchette;

use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\Manchette;
use App\Models\SessionExam;
use App\Models\CodeAnonymat;
use Livewire\WithPagination;
use App\Models\ResultatFinal;
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

    // Sessions - LOGIQUE CORRECTE
    public $sessionActive = null;
    public $sessionNormaleId = null;
    public $currentSessionType = '';
    public $showBothSessions = false;
    public $sessionFilter = 'all'; // 'all', 'normale', 'rattrapage'

    // Modal de modification
    public $showEditModal = false;
    public $editingManchetteId = null;
    public $code_anonymat = '';
    public $etudiant_id = null;

    // Modal de suppression
    public $showDeleteModal = false;
    public $manchetteToDelete = null;

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

        // Récupérer les sessions
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
                    
                    // Si on est en session rattrapage, récupérer aussi la session normale
                    if ($this->sessionActive->type === 'Rattrapage') {
                        $sessionNormale = $this->sessionActive->getSessionNormaleCorrespondante();
                        if ($sessionNormale) {
                            $this->sessionNormaleId = $sessionNormale->id;
                            $this->showBothSessions = true;
                            $this->sessionFilter = 'all';
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur session active', ['error' => $e->getMessage()]);
        }

        $this->loadFilters();
    }

    /**
     * Mise à jour du filtre de session
     */
    public function updatedSessionFilter()
    {
        $this->resetPage();
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
        
        $this->sessionFilter = 'all';
        
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
            'session_filter' => $this->sessionFilter,
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
            
            if (!empty($filters['session_filter'])) {
                $this->sessionFilter = $filters['session_filter'];
            }
        }
    }

    /**
     * ✅ LOGIQUE CORRECTE : Récupérer les IDs des étudiants éligibles au rattrapage
     */
    private function getEtudiantsEligiblesRattrapage()
    {
        if (!$this->sessionNormaleId) {
            return collect();
        }

        try {
            // Récupérer les étudiants avec décision RATTRAPAGE en session normale
            $etudiantsRattrapage = DB::table('resultats_finaux')
                ->where('session_exam_id', $this->sessionNormaleId)
                ->where('decision', ResultatFinal::DECISION_RATTRAPAGE)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->pluck('etudiant_id')
                ->unique();

            // Exclure ceux qui ont une décision ADMIS en session normale
            $etudiantsAdmis = DB::table('resultats_finaux')
                ->where('session_exam_id', $this->sessionNormaleId)
                ->where('decision', ResultatFinal::DECISION_ADMIS)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->pluck('etudiant_id')
                ->unique();

            return $etudiantsRattrapage->diff($etudiantsAdmis);
            
        } catch (\Exception $e) {
            Log::error('Erreur récupération étudiants éligibles rattrapage', [
                'error' => $e->getMessage(),
                'session_normale_id' => $this->sessionNormaleId
            ]);
            return collect();
        }
    }

    /**
     * ✅ VRAIE LOGIQUE CORRECTE : Récupérer les ECs validées (qui restent en session normale)
     */
    private function getEcsValidesSessionNormale($etudiantId)
    {
        if (!$this->sessionNormaleId) {
            return collect();
        }

        try {
            // Utiliser le RattrapageService pour obtenir les ECs non validées
            $rattrapageService = app(\App\Services\RattrapageService::class);
            $ecsNonValides = $rattrapageService->getEcsNonValidesEtudiant($etudiantId, $this->sessionNormaleId);
            
            // Récupérer toutes les ECs de l'étudiant en session normale
            $toutesLesEcs = DB::table('resultats_finaux')
                ->join('codes_anonymat', 'resultats_finaux.code_anonymat_id', '=', 'codes_anonymat.id')
                ->where('resultats_finaux.etudiant_id', $etudiantId)
                ->where('resultats_finaux.session_exam_id', $this->sessionNormaleId)
                ->where('resultats_finaux.statut', ResultatFinal::STATUT_PUBLIE)
                ->pluck('codes_anonymat.ec_id')
                ->unique();
            
            // LOGIQUE CORRECTE : ECs validées = Toutes les ECs - ECs non validées
            $ecsValidees = $toutesLesEcs->diff(collect($ecsNonValides['ecs_non_valides'] ?? []));
            
            return $ecsValidees;
            
        } catch (\Exception $e) {
            Log::error('Erreur récupération ECs validées avec RattrapageService', [
                'error' => $e->getMessage(),
                'etudiant_id' => $etudiantId,
                'session_normale_id' => $this->sessionNormaleId
            ]);
            return collect();
        }
    }

    /**
     * Ouvrir la modal de modification
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

        // Vérifier que la manchette appartient à une des sessions autorisées
        $sessionsAutorisees = $this->getSessionsAutorisees();
        if (!in_array($manchette->session_exam_id, $sessionsAutorisees)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Cette manchette appartient à une session non autorisée.'
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
     * Récupérer les IDs des sessions autorisées
     */
    private function getSessionsAutorisees()
    {
        $sessions = [];
        
        if ($this->session_exam_id) {
            $sessions[] = $this->session_exam_id;
        }
        
        if ($this->sessionNormaleId) {
            $sessions[] = $this->sessionNormaleId;
        }
        
        return $sessions;
    }

    /**
     * Sauvegarder les modifications
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
                $sessionsAutorisees = $this->getSessionsAutorisees();
                $existingManchette = Manchette::where('code_anonymat_id', $existingCode->id)
                    ->whereIn('session_exam_id', $sessionsAutorisees)
                    ->first();
                
                if ($existingManchette) {
                    throw new \Exception('Ce code d\'anonymat est déjà utilisé par un autre étudiant.');
                }
            }

            // Vérifier si l'étudiant a déjà une autre manchette pour cette EC
            $sessionsAutorisees = $this->getSessionsAutorisees();
            $existingStudentManchette = Manchette::where('etudiant_id', $this->etudiant_id)
                ->where('examen_id', $manchette->examen_id)
                ->whereIn('session_exam_id', $sessionsAutorisees)
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
     * Annuler la modification
     */
    public function cancelEdit()
    {
        $this->showEditModal = false;
        $this->resetEditForm();
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
     * Confirmer la suppression
     */
    public function confirmDelete($id)
    {
        $manchette = Manchette::with(['codeAnonymat.ec', 'etudiant'])->find($id);
        
        if (!$manchette) {
            toastr()->error('Veuillez sélectionner au moins une manchette à supprimer.');
            return;
        }

        // Vérifier que la manchette appartient à une session autorisée
        $sessionsAutorisees = $this->getSessionsAutorisees();
        if (!in_array($manchette->session_exam_id, $sessionsAutorisees)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Cette manchette appartient à une session non autorisée.'
            ]);
            return;
        }

        $this->manchetteToDelete = $manchette;
        $this->showDeleteModal = true;
    }

    /**
     * Supprimer la manchette
     */
    public function deleteManchette()
    {
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

            // Supprimer la manchette
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
            
            Log::error('Erreur suppression manchette', [
                'error' => $e->getMessage(),
                'manchette_id' => $manchetteId
            ]);
        }
    }

    /**
     * Annuler la suppression
     */
    public function cancelDelete()
    {
        $this->manchetteToDelete = null;
        $this->showDeleteModal = false;
        $this->dispatch('modal-closed');
    }

    /**
     * ✅ LOGIQUE CORRECTE : Obtenir les statistiques avec la vraie logique
     */
    public function getSessionStatistics()
    {
        if (!$this->showBothSessions) {
            return null;
        }

        $stats = [
            'normale' => 0,
            'rattrapage' => 0,
            'total' => 0,
            'etudiants_eligibles' => 0
        ];

        try {
            // Récupérer les étudiants éligibles au rattrapage
            $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
            $stats['etudiants_eligibles'] = $etudiantsEligibles->count();
            
            if ($etudiantsEligibles->isEmpty()) {
                return $stats;
            }

            // Compter les manchettes selon la VRAIE logique
            $manchettesNormale = collect();
            $manchettesRattrapage = collect();
            
            foreach ($etudiantsEligibles as $etudiantId) {
                // 1. Compter les manchettes des ECs VALIDÉES en session normale
                $ecsValidees = $this->getEcsValidesSessionNormale($etudiantId);
                if ($ecsValidees->isNotEmpty()) {
                    $ids = DB::table('manchettes')
                        ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
                        ->where('manchettes.etudiant_id', $etudiantId)
                        ->where('manchettes.session_exam_id', $this->sessionNormaleId)
                        ->whereIn('codes_anonymat.ec_id', $ecsValidees->toArray())
                        ->pluck('manchettes.id');
                    
                    $manchettesNormale = $manchettesNormale->concat($ids);
                }
                
                // 2. Compter toutes les manchettes de session rattrapage
                $ids = DB::table('manchettes')
                    ->where('etudiant_id', $etudiantId)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->pluck('id');
                
                $manchettesRattrapage = $manchettesRattrapage->concat($ids);
            }

            // Appliquer les filtres si nécessaire
            if ($this->niveau_id || $this->ec_id || $this->saisie_par || $this->search) {
                $baseQuery = Manchette::query();
                
                // Filtres niveau/parcours
                if ($this->niveau_id && $this->parcours_id) {
                    $examensIds = DB::table('examens')
                        ->where('niveau_id', $this->niveau_id)
                        ->where('parcours_id', $this->parcours_id)
                        ->whereNull('deleted_at')
                        ->pluck('id');
                    $baseQuery->whereIn('examen_id', $examensIds);
                } elseif ($this->niveau_id) {
                    $examensIds = DB::table('examens')
                        ->where('niveau_id', $this->niveau_id)
                        ->whereNull('deleted_at')
                        ->pluck('id');
                    $baseQuery->whereIn('examen_id', $examensIds);
                }

                if ($this->ec_id) {
                    $baseQuery->whereHas('codeAnonymat', function ($q) {
                        $q->where('ec_id', $this->ec_id);
                    });
                }

                if ($this->saisie_par) {
                    $baseQuery->where('saisie_par', $this->saisie_par);
                }

                if ($this->search) {
                    $baseQuery->where(function ($q) {
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

                $filteredIds = $baseQuery->pluck('id');
                $manchettesNormale = $manchettesNormale->intersect($filteredIds);
                $manchettesRattrapage = $manchettesRattrapage->intersect($filteredIds);
            }

            $stats['normale'] = $manchettesNormale->unique()->count();
            $stats['rattrapage'] = $manchettesRattrapage->unique()->count();
            $stats['total'] = $stats['normale'] + $stats['rattrapage'];

        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques sessions', ['error' => $e->getMessage()]);
        }

        return $stats;
    }

    /**
     * Rendu du composant
     */
    public function render()
    {
        $manchettes = $this->getManchettes();
        $sessionStats = $this->getSessionStatistics();

        return view('livewire.manchette.manchettes-index', [
            'manchettes' => $manchettes,
            'sessionInfo' => [
                'active' => $this->sessionActive,
                'type' => $this->currentSessionType,
                'session_libelle' => $this->sessionActive ? $this->sessionActive->type : null,
                'show_both' => $this->showBothSessions,
                'normale_id' => $this->sessionNormaleId,
            ],
            'sessionStats' => $sessionStats
        ]);
    }

    /**
     * ✅ VRAIE LOGIQUE CORRECTE : Récupérer les manchettes selon la logique précise
     */
    private function getManchettes()
    {
        // Base query
        $query = Manchette::with(['codeAnonymat.ec.ue', 'etudiant', 'utilisateurSaisie', 'sessionExam']);

        // LOGIQUE CORRECTE APPLIQUÉE
        if ($this->showBothSessions) {
            // En session rattrapage : logique CORRECTE
            $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
            
            if ($etudiantsEligibles->isEmpty()) {
                return $query->where('id', 0)->paginate($this->perPage);
            }
            
            // Construction des IDs des manchettes à afficher
            $manchetteIds = collect();
            
            // Filtrer selon le choix utilisateur
            switch ($this->sessionFilter) {
                case 'normale':
                    // Afficher seulement les manchettes des ECs validées en session normale
                    foreach ($etudiantsEligibles as $etudiantId) {
                        $ecsValidees = $this->getEcsValidesSessionNormale($etudiantId);
                        if ($ecsValidees->isNotEmpty()) {
                            $ids = DB::table('manchettes')
                                ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
                                ->where('manchettes.etudiant_id', $etudiantId)
                                ->where('manchettes.session_exam_id', $this->sessionNormaleId)
                                ->whereIn('codes_anonymat.ec_id', $ecsValidees->toArray())
                                ->pluck('manchettes.id');
                            $manchetteIds = $manchetteIds->concat($ids);
                        }
                    }
                    break;
                    
                case 'rattrapage':
                    // Afficher seulement les manchettes de rattrapage
                    foreach ($etudiantsEligibles as $etudiantId) {
                        $ids = DB::table('manchettes')
                            ->where('etudiant_id', $etudiantId)
                            ->where('session_exam_id', $this->session_exam_id)
                            ->pluck('id');
                        $manchetteIds = $manchetteIds->concat($ids);
                    }
                    break;
                    
                case 'all':
                default:
                    // LOGIQUE CORRECTE : Afficher les ECs validées + rattrapage
                    foreach ($etudiantsEligibles as $etudiantId) {
                        // 1. Ajouter les manchettes des ECs VALIDÉES en session normale
                        $ecsValidees = $this->getEcsValidesSessionNormale($etudiantId);
                        if ($ecsValidees->isNotEmpty()) {
                            $ids = DB::table('manchettes')
                                ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
                                ->where('manchettes.etudiant_id', $etudiantId)
                                ->where('manchettes.session_exam_id', $this->sessionNormaleId)
                                ->whereIn('codes_anonymat.ec_id', $ecsValidees->toArray())
                                ->pluck('manchettes.id');
                            $manchetteIds = $manchetteIds->concat($ids);
                        }
                        
                        // 2. Ajouter toutes les manchettes de rattrapage
                        $ids = DB::table('manchettes')
                            ->where('etudiant_id', $etudiantId)
                            ->where('session_exam_id', $this->session_exam_id)
                            ->pluck('id');
                        $manchetteIds = $manchetteIds->concat($ids);
                    }
                    break;
            }
            
            if ($manchetteIds->isEmpty()) {
                return $query->where('id', 0)->paginate($this->perPage);
            }
            
            $query->whereIn('id', $manchetteIds->unique()->toArray());
        } else {
            // Session normale : afficher seulement la session active (tous les étudiants)
            if ($this->session_exam_id) {
                $query->where('session_exam_id', $this->session_exam_id);
            }
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
                case 'session_exam_id':
                    $query->join('session_exams', 'session_exams.id', '=', 'manchettes.session_exam_id')
                          ->orderBy('session_exams.type', $this->sortDirection)
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
                ->where('id', 0) // Pas de résultats en cas d'erreur
                ->paginate($this->perPage);
        }
    }
}