<?php

namespace App\Livewire\Manchette;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Examen;
use App\Models\EC;
use App\Models\Etudiant;
use App\Models\Manchette;
use App\Models\CodeAnonymat;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 * @property \Illuminate\Support\Collection $examens
 */

class ManchetteSaisie extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // URL/state
    public $step = 'niveau';
    public $niveau_id = null;
    public $parcours_id = null;
    public $examen_id = null;
    public $ec_id = null;

    // Sélections
    public $niveauSelected = null;
    public $parcoursSelected = null;
    public $examenSelected = null;
    public $ecSelected = null;

    // Collections pour l’affichage (attention: pas de paginator ici)
    public $niveaux = [];
    public $parcours = [];
    public $examens = [];

    // RECHERCHE / PAGINATION (valeurs par défaut alignées)
    public $search = '';
    public $perPage = 12;

    // Config manchettes
    public $totalManchettesPresentes = 0;
    public $totalEtudiantsTheorique = 0;
    public $codeSalle = '';

    // Interface saisie
    public $matricule = '';
    public $etudiantTrouve = null;
    public $prochaineSequence = 1;
    public $prochainCodeAnonymat = '';

    // Stats
    public $manchettesSaisies = [];
    public $progressCount = 0;
    public $sessionType = 'normale';

    // Messages
    public $message = '';
    public $messageType = 'info';

    protected $queryString = [
        'step' => ['except' => 'niveau'],
        'niveau_id' => ['except' => null],
        'parcours_id' => ['except' => null, 'as' => 'parcours'],
        'examen_id' => ['except' => null, 'as' => 'examen'],
        'ec_id' => ['except' => null, 'as' => 'ec'],
        'search' => ['except' => null, 'as' => 'q'],
        'perPage' => ['except' => 12],
    ];

    protected $rules = [
        'matricule' => 'required|string',
        'totalManchettesPresentes' => 'required|integer|min:1',
    ];

    /** Hooks de pagination / recherche */
    public function updatedSearch()  { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }

    /** Hook examen changé (via sélecteur par ex.) */
    public function updatedExamenId()
    {
        $this->examenSelected = $this->examen_id ? Examen::find($this->examen_id) : null;
        $this->resetPage();
    }

    public function mount()
    {
        $this->sessionType = Manchette::getCurrentSessionType();
        $this->loadNiveaux();
        $this->loadDataFromUrl();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['step', 'niveau_id', 'parcours_id', 'examen_id', 'ec_id'])) {
            $this->loadDataFromUrl();
        }
    }

    /** ======== DATA LOADING ======== */
    public function loadDataFromUrl()
    {
        try {
            if ($this->niveau_id) {
                $this->niveauSelected = Niveau::find($this->niveau_id);

                if ($this->niveauSelected) {
                    $this->loadParcours();

                    if ($this->parcours_id) {
                        $this->parcoursSelected = Parcour::find($this->parcours_id);
                    }

                    if ($this->niveauSelected && ($this->parcoursSelected || !$this->niveauSelected->has_parcours)) {
                        $this->loadExamens(); // garantit examen_id

                        if ($this->examen_id) {
                            $this->examenSelected = Examen::find($this->examen_id);

                            if ($this->examenSelected && $this->ec_id) {
                                $this->ecSelected = EC::find($this->ec_id);
                                if ($this->ecSelected) {
                                    $this->loadCodeSalleFromExamen();
                                    $this->calculateTotalEtudiants();

                                    if ($this->step === 'saisie') {
                                        $this->loadStatistiques();
                                        $this->calculateNextSequence();
                                    } elseif ($this->step === 'setup') {
                                        $this->loadStatistiquesInitiales();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            logger('Erreur loadDataFromUrl: ' . $e->getMessage());
        }
    }

    public function loadNiveaux()
    {
        $this->niveaux = Niveau::where('is_active', true)->orderBy('id')->get();
    }

    public function selectNiveau($niveauId)
    {
        $this->niveau_id = $niveauId;
        $this->parcours_id = null;
        $this->examen_id = null;
        $this->ec_id = null;

        $this->niveauSelected = Niveau::find($niveauId);
        $this->resetFromStep('parcours');
        $this->loadParcours();

        if ($this->parcours->isEmpty()) {
            $this->loadExamens();
            $this->step = 'ec';
        } else {
            $this->step = 'parcours';
        }
    }

    public function loadParcours()
    {
        if (!$this->niveauSelected) return;

        $this->parcours = Parcour::where('niveau_id', $this->niveauSelected->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    public function selectParcours($parcoursId = null)
    {
        $this->parcours_id = $parcoursId;
        $this->examen_id = null;
        $this->ec_id = null;

        $this->parcoursSelected = $parcoursId ? Parcour::find($parcoursId) : null;
        $this->resetFromStep('ec');

        $this->loadExamens(); // choisit un examen par défaut si possible
        $this->step = 'ec';
        $this->resetPage();
    }

    public function loadExamens()
    {
        if (!$this->niveauSelected) return;

        $query = Examen::where('niveau_id', $this->niveauSelected->id);

        if ($this->parcoursSelected) {
            $query->where('parcours_id', $this->parcoursSelected->id);
        }

        $this->examens = $query->with('ecs')->orderByDesc('id')->get();

        // Garantir un examen sélectionné
        if ($this->examens->count() === 1) {
            $this->examen_id = $this->examens->first()->id;
        } elseif (!$this->examen_id && $this->examens->count() > 1) {
            $this->examen_id = $this->examens->first()->id; // le plus récent
        }

        $this->examenSelected = $this->examen_id ? Examen::find($this->examen_id) : null;
        $this->resetPage();
    }

    /** ======== QUERY EC (pivot + tolérance) ======== */
    private function ecsQuery()
    {
        if (!$this->examen_id) {
            return EC::query()->whereRaw('1=0');
        }

        $q = EC::query()
            ->select(['ecs.id','ecs.nom','ecs.abr','ecs.niveau_id','ecs.parcours_id','ecs.ue_id'])
            ->join('examen_ec', 'examen_ec.ec_id', '=', 'ecs.id')
            ->where('examen_ec.examen_id', $this->examen_id)
            ->with(['ue:id,nom']);

        if (Schema::hasColumn('ecs', 'niveau_id') && $this->niveauSelected) {
            $q->where(function($qq) {
                $qq->where('ecs.niveau_id', $this->niveauSelected->id)
                   ->orWhereNull('ecs.niveau_id');
            });
        }

        if (Schema::hasColumn('ecs', 'parcours_id') && $this->parcoursSelected) {
            $q->where(function($qq) {
                $qq->where('ecs.parcours_id', $this->parcoursSelected->id)
                   ->orWhereNull('ecs.parcours_id');
            });
        }

        if ($this->search) {
            $s = trim($this->search);
            $q->where(function($qq) use ($s) {
                $qq->where('ecs.nom', 'like', "%{$s}%")
                   ->orWhere('ecs.abr', 'like', "%{$s}%")
                   ->orWhereHas('ue', fn($uq) => $uq->where('nom', 'like', "%{$s}%"));
            });
        }

        return $q->orderBy('ecs.abr');
    }

    /** ======== Sélection EC / Code / Stats ======== */
    public function selectEC($ecId)
    {
        try {
            $this->ec_id = $ecId;
            $this->ecSelected = EC::find($ecId);

            if (!$this->ecSelected) {
                $this->showMessage('Matière non trouvée', 'error');
                return;
            }

            $this->resetFromStep('setup');
            $this->loadCodeSalleFromExamen();
            $this->calculateTotalEtudiants();
            $this->loadStatistiquesInitiales();
            $this->step = 'setup';

            $this->showMessage('Matière sélectionnée: ' . $this->ecSelected->nom, 'success');
        } catch (\Throwable $e) {
            $this->showMessage('Erreur lors de la sélection: ' . $e->getMessage(), 'error');
            logger('Erreur selectEC: ' . $e->getMessage());
        }
    }

    public function loadCodeSalleFromExamen()
    {
        if (!$this->examenSelected || !$this->ecSelected) return;

        $examenEc = DB::table('examen_ec')
            ->where('examen_id', $this->examenSelected->id)
            ->where('ec_id', $this->ecSelected->id)
            ->first();

        $this->codeSalle = $examenEc && $examenEc->code_base ? $examenEc->code_base : 'TA';
    }

    public function calculateTotalEtudiants()
    {
        if (!$this->niveauSelected) return;

        $query = Etudiant::where('niveau_id', $this->niveauSelected->id)
            ->where('is_active', true);

        if ($this->parcoursSelected) {
            $query->where('parcours_id', $this->parcoursSelected->id);
        }

        $this->totalEtudiantsTheorique = $query->count();
    }

    public function startSaisie()
    {
        $this->validate(['totalManchettesPresentes' => 'required|integer|min:1']);

        if ($this->totalManchettesPresentes > $this->totalEtudiantsTheorique) {
            $this->showMessage("Le nombre de présents ({$this->totalManchettesPresentes}) ne peut pas dépasser le total d'étudiants ({$this->totalEtudiantsTheorique})", 'error');
            return;
        }

        $this->step = 'saisie';
        $this->loadStatistiques();
        $this->calculateNextSequence();
    }

    public function loadStatistiquesInitiales()
    {
        if (!$this->examenSelected || !$this->ecSelected) return;

        $sessionId = Manchette::getCurrentSessionId();

        $existantes = Manchette::where('examen_id', $this->examenSelected->id)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecSelected->id))
            ->count();

        $this->progressCount = $existantes;
    }

    public function loadStatistiques()
    {
        if (!$this->examenSelected || !$this->ecSelected) return;

        $sessionId = Manchette::getCurrentSessionId();

        $this->manchettesSaisies = Manchette::where('examen_id', $this->examenSelected->id)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecSelected->id))
            ->with(['etudiant', 'codeAnonymat'])
            ->latest()
            ->get()
            ->toArray();

        $this->progressCount = count($this->manchettesSaisies);
    }

    public function calculateNextSequence()
    {
        if (!$this->examenSelected || !$this->ecSelected || !$this->codeSalle) return;

        $sessionId = Manchette::getCurrentSessionId();

        $lastSequence = CodeAnonymat::where('examen_id', $this->examenSelected->id)
            ->where('session_exam_id', $sessionId)
            ->where('ec_id', $this->ecSelected->id)
            ->where('code_complet', 'LIKE', $this->codeSalle . '%')
            ->max('sequence');

        $this->prochaineSequence = ($lastSequence ?? 0) + 1;
        $this->prochainCodeAnonymat = $this->codeSalle . $this->prochaineSequence;
    }

    /** ======== Utils ======== */
    public function backToStep($stepName)
    {
        $this->step = $stepName;
        $this->clearMessage();

        switch ($stepName) {
            case 'niveau':
                $this->niveau_id = null;
                $this->parcours_id = null;
                $this->examen_id = null;
                $this->ec_id = null;
                break;
            case 'parcours':
                $this->parcours_id = null;
                $this->examen_id = null;
                $this->ec_id = null;
                break;
            case 'ec':
                $this->examen_id = null;
                $this->ec_id = null;
                break;
            case 'setup':
                // rien
                break;
        }
    }

    public function getTotalAbsentsProperty()
    {
        return max(0, $this->totalEtudiantsTheorique - $this->totalManchettesPresentes);
    }

    public function getPourcentagePresenceProperty()
    {
        return $this->totalEtudiantsTheorique > 0
            ? round(($this->totalManchettesPresentes / $this->totalEtudiantsTheorique) * 100, 1)
            : 0;
    }

    private function resetFromStep($step)
    {
        switch ($step) {
            case 'parcours':
                $this->parcoursSelected = null;
                $this->resetFromStep('ec');
                break;
            case 'ec':
                $this->examenSelected = null;
                $this->ecSelected = null;
                $this->resetFromStep('setup');
                break;
            case 'setup':
                $this->totalManchettesPresentes = 0;
                $this->totalEtudiantsTheorique = 0;
                $this->codeSalle = '';
                $this->resetFromStep('saisie');
                break;
            case 'saisie':
                $this->resetSaisieForm();
                break;
        }
    }

    private function resetSaisieForm()
    {
        $this->matricule = '';
        $this->etudiantTrouve = null;
        $this->clearMessage();
    }

    private function showMessage($message, $type = 'info')
    {
        $this->message = $message;
        $this->messageType = $type;

        if ($type === 'success') {
            $this->dispatch('clearMessage', ['delay' => 2000]);
        }
    }

    public function clearMessage()
    {
        $this->message = '';
        $this->messageType = 'info';
    }

    public function render()
    {
        // NE PAS STOCKER le paginator dans une propriété publique
        $ecs = $this->ecsQuery()->paginate($this->perPage);

        return view('livewire.manchette.manchette-saisie', [
            'ecs' => $ecs,
            'examensList' => $this->examens, // pour un select dans la vue
        ]);
    }
}
