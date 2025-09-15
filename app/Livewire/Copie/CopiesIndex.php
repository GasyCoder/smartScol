<?php

namespace App\Livewire\Copie;

use App\Models\EC;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Copie;
use App\Models\SessionExam;
use Livewire\WithPagination;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CopiesIndex extends Component
{
    use WithPagination;

    // Propriétés de filtrage essentielles
    public $niveau_id;
    public $parcours_id;
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

    // Session avec logique de rattrapage - TOUTES LES PROPRIÉTÉS DÉFINIES
    public $sessionActive = null;
    public $currentSessionType = '';
    public $sessionNormaleId = null;
    public $showBothSessions = false;
    public $sessionFilter = 'all';

    // Modal de modification
    public $showEditModal = false;
    public $editingCopieId = null;
    public $code_anonymat = '';
    public $note = null;

    // Modal de suppression
    public $showDeleteModal = false;
    public $copieToDelete = null;

    protected $rules = [
        'note' => 'nullable|numeric|min:0|max:20',
    ];

    protected $messages = [
        'note.numeric' => 'La note doit être un nombre.',
        'note.min' => 'La note ne peut pas être inférieure à 0.',
        'note.max' => 'La note ne peut pas être supérieure à 20.',
    ];

    public function mount()
    {
        // Charger les niveaux
        $this->niveaux = Niveau::where('is_active', true)->orderBy('nom')->get();
        
        // Charger les secrétaires
        $this->secretaires = DB::table('users')
            ->join('copies', 'users.id', '=', 'copies.saisie_par')
            ->select('users.id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get();

        // Session active ET logique de rattrapage
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        if ($anneeActive) {
            $this->sessionActive = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                ->where('is_active', true)
                ->where('is_current', true)
                ->first();

            if ($this->sessionActive) {
                $this->session_exam_id = $this->sessionActive->id;
                $this->currentSessionType = $this->sessionActive->type;
                
                // Si session de rattrapage, récupérer aussi la session normale
                if ($this->sessionActive->type === 'Rattrapage') {
                    $sessionNormale = $this->sessionActive->getSessionNormaleCorrespondante();
                    if ($sessionNormale) {
                        $this->sessionNormaleId = $sessionNormale->id;
                        $this->showBothSessions = true;
                    }
                }
            }
        }
    }

    public function updatedNiveauId()
    {
        $this->parcours_id = null;
        $this->ec_id = null;
        $this->parcours = collect();
        $this->ecs = collect();

        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('nom')
                ->get();
        }
        $this->resetPage();
    }

    public function updatedParcoursId()
    {
        $this->ec_id = null;
        $this->ecs = collect();

        if ($this->niveau_id && $this->parcours_id) {
            $this->ecs = DB::table('ecs')
                ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->whereNull('ecs.deleted_at')
                ->select('ecs.*')
                ->distinct()
                ->orderBy('ecs.nom')
                ->get();
        }
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSaisiePar()
    {
        $this->resetPage();
    }

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

    public function resetFilters()
    {
        $this->reset(['niveau_id', 'parcours_id', 'ec_id', 'search', 'saisie_par']);
        $this->parcours = collect();
        $this->ecs = collect();
        $this->resetPage();
    }

    public function editCopie($id)
    {
        $copie = Copie::with(['codeAnonymat'])->find($id);
        
        if (!$copie) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Copie introuvable.'
            ]);
            return;
        }

        $this->editingCopieId = $id;
        $this->code_anonymat = $copie->codeAnonymat->code_complet ?? 'N/A';
        $this->note = $copie->note;
        $this->showEditModal = true;
    }

    public function updateCopie()
    {
        $this->validate();

        try {
            $copie = Copie::find($this->editingCopieId);
            
            if (!$copie) {
                throw new \Exception('Copie introuvable.');
            }

            $copie->update([
                'note' => $this->note,
                'modifie_par' => Auth::id(),
                'updated_at' => now(),
            ]);

            $this->showEditModal = false;
            $this->reset(['editingCopieId', 'code_anonymat', 'note']);
            
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Note modifiée avec succès.'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelEdit()
    {
        $this->showEditModal = false;
        $this->reset(['editingCopieId', 'code_anonymat', 'note']);
    }

    public function confirmDelete($id)
    {
        $copie = Copie::with(['codeAnonymat'])->find($id);
        
        if (!$copie) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Copie introuvable.'
            ]);
            return;
        }

        $this->copieToDelete = $copie;
        $this->showDeleteModal = true;
    }

    public function deleteCopie()
    {
        try {
            if (!$this->copieToDelete) {
                throw new \Exception('Copie introuvable.');
            }

            $this->copieToDelete->delete();
            
            $this->showDeleteModal = false;
            $this->copieToDelete = null;
            
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Copie supprimée avec succès.'
            ]);
            
        } catch (\Exception $e) {
            $this->showDeleteModal = false;
            $this->copieToDelete = null;
            
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelDelete()
    {
        $this->copieToDelete = null;
        $this->showDeleteModal = false;
    }

    private function getCopies()
    {
        $query = Copie::with(['codeAnonymat.ec.ue', 'utilisateurSaisie', 'sessionExam']);

        // LOGIQUE CORRECTE : En session rattrapage, montrer copies rattrapage + copies validées session normale
        if ($this->showBothSessions && $this->sessionNormaleId) {
            // Récupérer les étudiants éligibles au rattrapage (ceux qui ont au moins une décision "rattrapage")
            $etudiantsRattrapage = DB::table('resultats_finaux')
                ->where('session_exam_id', $this->sessionNormaleId)
                ->where('decision', 'rattrapage')
                ->where('statut', 'publie')
                ->select('etudiant_id')
                ->distinct()
                ->pluck('etudiant_id');

            if ($etudiantsRattrapage->isEmpty()) {
                // Aucun étudiant en rattrapage, afficher seulement session active
                $query->where('session_exam_id', $this->session_exam_id);
            } else {
                $copieIds = collect();

                // Utiliser le RattrapageService pour chaque étudiant
                $rattrapageService = app(\App\Services\RattrapageService::class);

                foreach ($etudiantsRattrapage as $etudiantId) {
                    try {
                        // Utiliser le service pour obtenir les ECs non validés
                        $ecsAnalyse = $rattrapageService->getEcsNonValidesEtudiant($etudiantId, $this->sessionNormaleId);
                        
                        $ecsEnRattrapage = $ecsAnalyse['ecs_non_valides'] ?? [];
                        
                        // 1. Ajouter les copies de rattrapage pour les ECs en rattrapage
                        if (!empty($ecsEnRattrapage)) {
                            $copiesRattrapage = DB::table('copies')
                                ->join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
                                ->join('manchettes', function($join) use ($etudiantId) {
                                    $join->on('codes_anonymat.id', '=', 'manchettes.code_anonymat_id')
                                         ->where('manchettes.etudiant_id', '=', $etudiantId)
                                         ->where('manchettes.session_exam_id', '=', $this->session_exam_id);
                                })
                                ->where('copies.session_exam_id', $this->session_exam_id)
                                ->whereIn('codes_anonymat.ec_id', $ecsEnRattrapage)
                                ->pluck('copies.id');
                            
                            $copieIds = $copieIds->concat($copiesRattrapage);
                        }

                        // 2. Ajouter les copies validées de session normale pour les ECs VALIDÉS
                        $toutesLesEcs = DB::table('resultats_finaux')
                            ->where('etudiant_id', $etudiantId)
                            ->where('session_exam_id', $this->sessionNormaleId)
                            ->where('statut', 'publie')
                            ->pluck('ec_id')
                            ->toArray();
                        
                        $ecsValidees = array_diff($toutesLesEcs, $ecsEnRattrapage);
                        
                        if (!empty($ecsValidees)) {
                            $copiesValidees = DB::table('copies')
                                ->join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
                                ->join('manchettes', function($join) use ($etudiantId) {
                                    $join->on('codes_anonymat.id', '=', 'manchettes.code_anonymat_id')
                                         ->where('manchettes.etudiant_id', '=', $etudiantId)
                                         ->where('manchettes.session_exam_id', '=', $this->sessionNormaleId);
                                })
                                ->where('copies.session_exam_id', $this->sessionNormaleId)
                                ->whereIn('codes_anonymat.ec_id', $ecsValidees)
                                ->pluck('copies.id');
                            
                            $copieIds = $copieIds->concat($copiesValidees);
                        }
                        
                    } catch (\Exception $e) {
                        Log::error('Erreur récupération ECs pour étudiant', [
                            'etudiant_id' => $etudiantId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                if ($copieIds->isNotEmpty()) {
                    $query->whereIn('id', $copieIds->unique()->toArray());
                } else {
                    $query->where('id', 0); // Aucun résultat
                }
            }
        } else {
            // Session normale ou pas de logique rattrapage : afficher seulement session active
            if ($this->session_exam_id) {
                $query->where('session_exam_id', $this->session_exam_id);
            }
        }

        // Filtres simples
        if ($this->niveau_id && $this->parcours_id) {
            $examensIds = DB::table('examens')
                ->where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->whereNull('deleted_at')
                ->pluck('id');
            $query->whereIn('examen_id', $examensIds);
        } elseif ($this->niveau_id) {
            $examensIds = DB::table('examens')
                ->where('niveau_id', $this->niveau_id)
                ->whereNull('deleted_at')
                ->pluck('id');
            $query->whereIn('examen_id', $examensIds);
        }

        if ($this->ec_id) {
            $query->where('ec_id', $this->ec_id);
        }

        if ($this->saisie_par) {
            $query->where('saisie_par', $this->saisie_par);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('codeAnonymat', function ($sq) {
                    $sq->where('code_complet', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Tri simple
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        $copies = $this->getCopies();

        return view('livewire.copie.copies-index', [
            'copies' => $copies,
            'sessionInfo' => [
                'active' => $this->sessionActive,
                'type' => $this->currentSessionType,
                'session_libelle' => $this->sessionActive ? $this->sessionActive->type : null,
                'show_both' => $this->showBothSessions,
                'normale_id' => $this->sessionNormaleId,
            ],
        ]);
    }
}