<?php

namespace App\Livewire\Copie;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Examen;
use App\Models\EC;
use App\Models\Copie;
use App\Models\Manchette;
use App\Models\CodeAnonymat;

class CopieSaisie extends Component
{
    use WithPagination;

    // ÉTAPES
    public string $step = 'niveau';

    // SÉLECTIONS
    public ?int $niveauId = null;
    public ?int $parcoursId = null;
    public ?int $examenId = null;
    public ?int $ecId = null;

    // OBJETS SÉLECTIONNÉS
    public ?Niveau $niveauSelected = null;
    public ?Parcour $parcoursSelected = null;
    public ?EC $ecSelected = null;
    public ?Examen $examenSelected = null;

    // COLLECTIONS
    public Collection $niveaux;
    public Collection $parcours;
    public Collection $examens;
    public ?Collection $examensList = null;

    // RECHERCHE EC
    public string $search = '';
    public int $perPage = 12;

    // SAISIE
    public string $matricule = '';
    public string $note = '';
    public $etudiantTrouve = null;
    public $manchetteCorrespondante = null;
    public $codeAnonymatCourant = null;

    // ÉTAT UI
    public bool $afficherChampNote = false;
    public bool $peutEnregistrer = false;
    public bool $noteDejaExiste = false;
    public $noteExistante = null;

    // STATS
    public int $progressCount = 0;
    public int $totalCopies = 0;
    public float $moyenneGenerale = 0.0;

    // SESSION
    public string $sessionType = 'normale';

    // MESSAGES
    public string $message = '';
    public string $messageType = 'info';

    // QUERYSTRING
    protected $queryString = [
        'step' => ['except' => 'niveau'],
        'niveauId' => ['except' => null, 'as' => 'niveau'],
        'parcoursId' => ['except' => null, 'as' => 'parcours'],
        'examenId' => ['except' => null, 'as' => 'examen'],
        'ecId' => ['except' => null, 'as' => 'ec'],
        'search' => ['except' => ''],
        'perPage' => ['except' => 12],
    ];

    // VALIDATION
    protected $rules = [
        'matricule' => 'required|string|min:3|max:50',
        'note' => 'required|numeric|min:0|max:20',
    ];

    protected $messages = [
        'matricule.required' => 'Le matricule est obligatoire.',
        'matricule.min' => 'Le matricule doit comporter au moins 3 caractères.',
        'note.required' => 'La note est obligatoire.',
        'note.numeric' => 'La note doit être un nombre.',
        'note.min' => 'La note ne peut pas être négative.',
        'note.max' => 'La note ne peut pas dépasser 20.',
    ];

    public function mount(): void
    {
        $this->niveaux = collect();
        $this->parcours = collect();
        $this->examens = collect();
        
        $this->loadNiveaux();
        $this->sessionType = Manchette::getCurrentSessionType();
        $this->perPage = 12;

        try {
            $this->loadDataFromUrl();
        } catch (\Throwable $e) {
            logger('Erreur mount CopieSaisie: ' . $e->getMessage());
            $this->step = 'niveau';
        }
    }

    public function render()
    {
        $ecs = collect();
        if ($this->step === 'ec') {
            $ecs = $this->getEcsWithStats();
        }

        return view('livewire.copie.copie-saisie', compact('ecs'));
    }

    public function loadDataFromUrl(): void
    {
        try {
            if ($this->niveauId) {
                $this->niveauSelected = Niveau::find($this->niveauId);
                
                if ($this->niveauSelected) {
                    $this->loadParcours();
                    
                    if ($this->parcoursId) {
                        $this->parcoursSelected = Parcour::find($this->parcoursId);
                    }
                    
                    if ($this->niveauSelected && ($this->parcoursSelected || !$this->niveauSelected->has_parcours)) {
                        $this->loadExamens();
                        
                        if ($this->examenId) {
                            $this->examenSelected = $this->examens->firstWhere('id', $this->examenId);
                            
                            if ($this->ecId) {
                                $this->ecSelected = EC::with(['ue'])->find($this->ecId);
                                if ($this->ecSelected && $this->step === 'saisie') {
                                    $this->initializeSaisie();
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            logger('Erreur loadDataFromUrl CopieSaisie: ' . $e->getMessage());
            $this->step = 'niveau';
            $this->niveauSelected = null;
            $this->parcoursSelected = null;
            $this->examenSelected = null;
            $this->ecSelected = null;
        }
    }

    public function loadNiveaux(): void
    {
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('id', 'asc')->get();
    }

    public function loadParcours(): void
    {
        if (!$this->niveauId || !$this->niveauSelected) { 
            $this->parcours = collect(); 
            return; 
        }

        try {
            $this->parcours = Parcour::where('niveau_id', $this->niveauId)
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();
        } catch (\Throwable $e) {
            logger('Erreur loadParcours: ' . $e->getMessage());
            $this->parcours = collect();
        }
    }

    public function loadExamens(): void
    {
        if (!$this->niveauId) { 
            $this->examens = collect(); 
            $this->examensList = collect(); 
            return; 
        }

        $q = Examen::where('niveau_id', $this->niveauId);
        if ($this->parcoursId) {
            $q->where('parcours_id', $this->parcoursId);
        }

        $this->examens = $q->with(['ecs', 'niveau', 'parcours'])
            ->orderByDesc('id')
            ->get();

        $this->examensList = collect($this->examens);

        if ($this->examensList->count() === 1) {
            $this->examenId = $this->examensList->first()->id;
            $this->examenSelected = $this->examensList->first();
        } elseif (!$this->examenId && $this->examensList->count() > 1) {
            $this->examenId = $this->examensList->first()->id;
            $this->examenSelected = $this->examensList->first();
        }
    }

    protected function getEcsWithStats()
    {
        if (!$this->examenId) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), 0, $this->perPage, 1, 
                ['path' => request()->url()]
            );
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();

            $manchettes = Manchette::where('examen_id', $this->examenId)
                ->where('session_exam_id', $sessionId)
                ->with('codeAnonymat')
                ->get();

            $ecIds = $manchettes->pluck('codeAnonymat.ec_id')
                ->filter()
                ->unique()
                ->values();

            if ($ecIds->isEmpty()) {
                return new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(), 0, $this->perPage, 1,
                    ['path' => request()->url()]
                );
            }

            $query = EC::whereIn('id', $ecIds->toArray())->with('ue')->orderBy('nom');

            if ($this->search) {
                $like = '%' . $this->search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('nom', 'like', $like)
                      ->orWhere('abr', 'like', $like)
                      ->orWhereHas('ue', fn($u) => $u->where('nom', 'like', $like));
                });
            }

            $allEcs = $query->get();

            $ecsWithStats = $allEcs->map(function($ec) use ($sessionId) {
                $totalManchettes = Manchette::where('examen_id', $this->examenId)
                    ->where('session_exam_id', $sessionId)
                    ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $ec->id))
                    ->count();

                $copiesSaisies = Copie::where('examen_id', $this->examenId)
                    ->where('session_exam_id', $sessionId)
                    ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $ec->id))
                    ->count();

                $ec->total_manchettes = $totalManchettes;
                $ec->copies_saisies = $copiesSaisies;
                $ec->restantes = max(0, $totalManchettes - $copiesSaisies);
                $ec->pourcentage = $totalManchettes > 0 ? round(($copiesSaisies / $totalManchettes) * 100, 1) : 0;
                $ec->est_terminee = $copiesSaisies >= $totalManchettes && $totalManchettes > 0;

                return $ec;
            });

            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $itemsForCurrentPage = $ecsWithStats->forPage($currentPage, $this->perPage);

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $itemsForCurrentPage,
                $ecsWithStats->count(),
                $this->perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );

        } catch (\Exception $e) {
            logger('Erreur dans getEcsWithStats: ' . $e->getMessage());
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), 0, $this->perPage, 1,
                ['path' => request()->url()]
            );
        }
    }

    // NAVIGATION
    public function backToStep(string $to): void
    {
        switch ($to) {
            case 'niveau':
                $this->resetSelections(['parcours', 'examens', 'ecs']);
                $this->resetURLParams(['parcours', 'examen', 'ec']);
                $this->step = 'niveau';
                $this->resetPage();
                break;

            case 'parcours':
                if (!$this->niveauSelected) return;
                $this->resetSelections(['examens', 'ecs']);
                $this->resetURLParams(['examen', 'ec']);
                $this->step = 'parcours';
                $this->resetPage();
                break;

            case 'ec':
                if (!$this->niveauSelected) return;
                $this->resetSelections(['ecs']);
                $this->resetURLParams(['ec']);
                $this->step = 'ec';
                $this->resetPage();
                break;
        }
    }

    public function selectNiveau(int $niveauId): void
    {
        $this->niveauId = $niveauId;
        $this->niveauSelected = Niveau::find($niveauId);

        if (!$this->niveauSelected) {
            $this->showMessage('Niveau non trouvé', 'error');
            return;
        }

        $this->loadParcours();
        $this->loadExamens();

        if (!$this->niveauSelected->has_parcours || $this->parcours->isEmpty()) {
            $this->step = 'ec';
        } else {
            $this->step = 'parcours';
        }
        $this->resetPage();
    }

    public function selectParcours(?int $parcoursId = null): void
    {
        $this->parcoursId = $parcoursId;
        $this->parcoursSelected = $parcoursId ? Parcour::find($parcoursId) : null;

        $this->loadExamens();
        $this->step = 'ec';
        $this->resetPage();
    }

    public function selectEC(int $ecId): void
    {
        $this->ecId = $ecId;
        $this->ecSelected = EC::with(['ue'])->find($ecId);

        if (!$this->ecSelected) {
            $this->showMessage('❌ Matière non trouvée.', 'error');
            return;
        }

        $this->initializeSaisie();
        $this->step = 'saisie';
        
        $this->showMessage('✅ Matière sélectionnée: ' . $this->ecSelected->nom, 'success');
    }

    public function changerExamen($examenId): void
    {
        $this->examenId = $examenId;
        $this->examenSelected = $this->examens->firstWhere('id', $examenId);
        $this->resetPage();
    }

    // SAISIE
    public function initializeSaisie(): void
    {
        $this->resetSaisieForm();
        $this->loadStatistiques();
        $this->dispatch('focusMatricule');
    }

    public function updatedMatricule($value): void
    {
        // Reset
        $this->etudiantTrouve = null;
        $this->manchetteCorrespondante = null;
        $this->codeAnonymatCourant = null;
        $this->afficherChampNote = false;
        $this->peutEnregistrer = false;
        $this->noteDejaExiste = false;
        $this->noteExistante = null;
        $this->note = '';
        $this->resetErrorBag();

        // Vérification automatique si au moins 3 caractères
        if (is_string($value) && mb_strlen(trim($value)) >= 3) {
            $this->rechercherParMatricule();
        }
    }




    public function updatedNote($value): void
    {
        // Valider en temps réel pour afficher le message d'erreur
        $this->validateOnly('note');
        
        // Mettre à jour directement $peutEnregistrer
        $this->peutEnregistrer = !empty($this->matricule) 
            && !empty($this->note)
            && is_numeric($this->note)
            && floatval($this->note) >= 0 
            && floatval($this->note) <= 20  // ← Si > 20, sera false
            && $this->etudiantTrouve 
            && $this->codeAnonymatCourant
            && !$this->noteDejaExiste;
    }


    public function getBoutonActiveProperty(): bool
    {
        return !empty($this->matricule) 
            && !empty($this->note)
            && is_numeric($this->note)
            && floatval($this->note) >= 0 
            && floatval($this->note) <= 20  // ← Condition clé
            && $this->etudiantTrouve 
            && $this->codeAnonymatCourant
            && !$this->noteDejaExiste;
    }

    private function verifierPeutEnregistrer(): void
    {
        // Vérification simple et claire
        $noteValide = !empty($this->note) 
            && is_numeric($this->note) 
            && floatval($this->note) >= 0 
            && floatval($this->note) <= 20;
        
        $this->peutEnregistrer = !empty($this->matricule) 
            && $noteValide  // Utiliser cette variable
            && $this->etudiantTrouve 
            && $this->codeAnonymatCourant
            && !$this->noteDejaExiste;
    }

    public function rechercherParMatricule(): void
    {
        // Reset des états
        $this->etudiantTrouve = null;
        $this->manchetteCorrespondante = null;
        $this->codeAnonymatCourant = null;
        $this->afficherChampNote = false;
        $this->peutEnregistrer = false;
        $this->noteDejaExiste = false;
        $this->noteExistante = null;

        if (!$this->matricule || !$this->examenId || !$this->ecId) return;

        $sessionId = Manchette::getCurrentSessionId();
        $mat = trim($this->matricule);

        // Rechercher la manchette
        $manchette = Manchette::query()
            ->where('examen_id', $this->examenId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('etudiant', fn($q) => $q->where('matricule', $mat))
            ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecId))
            ->with(['etudiant', 'codeAnonymat'])
            ->first();

        if (!$manchette) {
            toastr('❌ Matricule non trouvé pour cette matière.', 'error');
            return;
        }

        // Vérifier si copie existe déjà
        $copieExistante = Copie::query()
            ->where('examen_id', $this->examenId)
            ->where('code_anonymat_id', $manchette->codeAnonymat->id)
            ->where('session_exam_id', $sessionId)
            ->first();

        if ($copieExistante) {
            // Note déjà saisie - afficher info sous le champ matricule
            $this->etudiantTrouve = $manchette->etudiant;
            $this->codeAnonymatCourant = $manchette->codeAnonymat;
            $this->noteDejaExiste = true;
            $this->noteExistante = $copieExistante->note;
            
            $this->clearMessage(); // Pas de message global
            return;
        }

        // Étudiant trouvé et pas encore noté
        $this->manchetteCorrespondante = $manchette;
        $this->etudiantTrouve = $manchette->etudiant;
        $this->codeAnonymatCourant = $manchette->codeAnonymat;
        $this->afficherChampNote = true;
        
        toastr('✅ Étudiant trouvé - Code: ' . $this->codeAnonymatCourant->code_complet, 'success');
        $this->dispatch('etudiantTrouve');
        $this->dispatch('focusNote');

        $this->verifierPeutEnregistrer();
    }

    public function verifierSiCopieExiste(): array
    {
        if (!$this->codeAnonymatCourant || !$this->examenId) {
            return ['existe' => false];
        }

        $sessionId = Manchette::getCurrentSessionId();
        
        $existe = Copie::where('examen_id', $this->examenId)
            ->where('code_anonymat_id', $this->codeAnonymatCourant->id)
            ->where('session_exam_id', $sessionId)
            ->exists();

        return ['existe' => $existe];
    }

    public function sauvegarderCopie(): void
    {
        if (!$this->peutEnregistrer) {
            $this->showMessage('❌ Veuillez compléter toutes les informations.', 'error');
            return;
        }

        $this->validate();

        if (!$this->manchetteCorrespondante || !$this->codeAnonymatCourant) {
            $this->showMessage('❌ Veuillez d\'abord valider le matricule.', 'error');
            return;
        }

        DB::beginTransaction();

        try {
            $sessionId = Manchette::getCurrentSessionId();

            // Vérification finale anti-doublon
            $existe = Copie::where('examen_id', $this->examenId)
                ->where('code_anonymat_id', $this->codeAnonymatCourant->id)
                ->where('session_exam_id', $sessionId)
                ->lockForUpdate()
                ->exists();

            if ($existe) {
                DB::rollBack();
                $this->resetSaisieForm();
                $this->loadStatistiques();
                
                $this->showMessage('❌ Note déjà saisie par quelqu\'un d\'autre.', 'error');
                toastr()->error('Note déjà saisie par quelqu\'un d\'autre.');
                return;
            }

            // Créer la copie
            Copie::create([
                'examen_id' => $this->examenId,
                'ec_id' => $this->ecId,
                'code_anonymat_id' => $this->codeAnonymatCourant->id,
                'session_exam_id' => $sessionId,
                'note' => floatval($this->note),
                'saisie_par' => Auth::id(),
                'date_saisie' => now(),
            ]);

            DB::commit();

            // Messages de succès
            $copiesRestantes = $this->totalCopies - ($this->progressCount + 1);
            
            if ($copiesRestantes <= 0) {
                $this->showMessage("🎉 Saisie terminée ! Note: {$this->note}/20", 'success');
                toastr()->success("🎉 Félicitations ! Toutes les notes ont été saisies !");
            } else {
                toastr()->success("Note enregistrée avec succès!");
            }

            // Reset et focus
            $this->dispatch('copieSauvegardee');
            $this->resetSaisieForm();
            $this->loadStatistiques();
            $this->dispatch('focusMatricule');
            
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            if ($e->getCode() == '23000' && strpos($e->getMessage(), 'copies_examen_code_session_unique') !== false) {
                $this->resetSaisieForm();
                $this->loadStatistiques();
                $this->showMessage('❌ Note déjà saisie.', 'warning');
                toastr()->warning('Note déjà saisie.');
            } else {
                logger('Erreur SQL: ' . $e->getMessage());
                $this->showMessage('❌ Erreur de base de données.', 'error');
                toastr()->error('Erreur de base de données.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            logger('Erreur: ' . $e->getMessage());
            $this->showMessage('❌ Erreur: ' . $e->getMessage(), 'error');
            toastr()->error('Erreur lors de l\'enregistrement.');
        }
    }

    public function supprimerDerniereCopie(): void
    {
        try {
            $sessionId = Manchette::getCurrentSessionId();
            
            $derniereCopie = Copie::where('examen_id', $this->examenId)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecId))
                ->latest('created_at')
                ->first();

            if ($derniereCopie) {
                $codeAnonymat = $derniereCopie->codeAnonymat ? $derniereCopie->codeAnonymat->code_complet : '#' . $derniereCopie->id;
                $derniereCopie->delete();
                
                $this->showMessage("✅ Dernière copie {$codeAnonymat} supprimée.", 'success');
                toastr()->success("Copie {$codeAnonymat} supprimée.");
                $this->loadStatistiques();
            } else {
                $this->showMessage('❌ Aucune copie à supprimer.', 'error');
                toastr()->error('Aucune copie à supprimer.');
            }
        } catch (\Throwable $e) {
            $this->showMessage('❌ Erreur lors de la suppression : ' . $e->getMessage(), 'error');
            toastr()->error('Erreur lors de la suppression.');
        }
    }

    public function loadStatistiques(): void
    {
        if (!$this->examenId || !$this->ecId) return;

        $sessionId = Manchette::getCurrentSessionId();

        // Récupérer les copies selon la contrainte unique + filtre ec_id
        $copies = Copie::where('examen_id', $this->examenId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecId))
            ->get();

        $this->progressCount = $copies->count();

        $this->totalCopies = Manchette::where('examen_id', $this->examenId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecId))
            ->count();

        // Moyenne
        if ($copies->count() > 0) {
            $this->moyenneGenerale = round($copies->avg('note'), 2);
        } else {
            $this->moyenneGenerale = 0;
        }
    }

    // HELPERS
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }

    private function resetSelections(array $types): void
    {
        foreach ($types as $type) {
            switch ($type) {
                case 'parcours':
                    $this->parcoursId = null;
                    $this->parcours = collect();
                    $this->parcoursSelected = null;
                    break;

                case 'examens':
                    $this->examenId = null;
                    $this->examens = collect();
                    $this->examensList = collect();
                    $this->examenSelected = null;
                    break;

                case 'ecs':
                    $this->ecId = null;
                    $this->ecSelected = null;
                    $this->resetSaisieForm();
                    break;
            }
        }
    }

    protected function resetURLParams(array $params): void
    {
        foreach ($params as $param) {
            switch ($param) {
                case 'parcours': $this->parcoursId = null; break;
                case 'examen': $this->examenId = null; break;
                case 'ec': $this->ecId = null; break;
            }
        }
    }

    private function resetSaisieForm(): void
    {
        $this->matricule = '';
        $this->note = '';
        $this->etudiantTrouve = null;
        $this->manchetteCorrespondante = null;
        $this->codeAnonymatCourant = null;
        $this->afficherChampNote = false;
        $this->peutEnregistrer = false;
        $this->noteDejaExiste = false;
        $this->noteExistante = null;
        $this->clearMessage();
        
        // Déclencher l'événement pour arrêter la vérification
        $this->dispatch('resetForm');
    }

    protected function showMessage(string $message, string $type = 'info'): void
    {
        $this->message = $message;
        $this->messageType = $type;

        if ($type === 'success') {
            $this->dispatch('clearMessage', ['delay' => 3000]);
        }
    }

    public function clearMessage(): void
    {
        $this->message = '';
        $this->messageType = 'info';
    }

    // PROPRIÉTÉS CALCULÉES
    public function getPourcentageProgressionProperty(): float
    {
        return $this->totalCopies > 0 
            ? min(($this->progressCount / $this->totalCopies) * 100, 100) 
            : 0;
    }

    public function getCopiesRestantesProperty(): int
    {
        return max(0, $this->totalCopies - $this->progressCount);
    }

    public function getCodeSalleProperty(): string
    {
        if (!$this->ecSelected || !$this->examenId) return '';
        
        $examenEc = DB::table('examen_ec')
            ->where('examen_id', $this->examenId)
            ->where('ec_id', $this->ecId)
            ->first();

        return $examenEc && $examenEc->code_base ? $examenEc->code_base : 'TA';
    }

    public function getTotalAbsentsProperty(): int
    {
        if (!$this->niveauSelected) return 0;
        
        $query = \App\Models\Etudiant::where('niveau_id', $this->niveauSelected->id)
            ->where('is_active', true);

        if ($this->parcoursSelected) {
            $query->where('parcours_id', $this->parcoursSelected->id);
        }

        $totalInscrits = $query->count();
        
        return max(0, $totalInscrits - $this->totalCopies);
    }
}