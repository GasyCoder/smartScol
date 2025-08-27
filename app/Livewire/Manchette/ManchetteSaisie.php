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
use App\Models\CodeAnonymat;
use Livewire\WithPagination;
use App\Models\PresenceExamen;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ManchetteSaisie extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // NAVIGATION
    public $step = 'niveau';
    public $niveau_id = null;
    public $parcours_id = null;
    public $examen_id = null;
    public $ec_id = null;

    // OBJETS SÉLECTIONNÉS
    public $niveauSelected = null;
    public $parcoursSelected = null;
    public $examenSelected = null;
    public $ecSelected = null;

    // COLLECTIONS
    public $niveaux;
    public $parcours;
    public $examens;

    // CONFIGURATION PRÉSENCES
    public $salleId = null;
    public ?string $codeSalle = null;
    public int $totalEtudiantsTheorique = 0;
    public int $totalManchettesPresentes = 0;
    public int $progressCount = 0;
    public bool $hasExistingPresence = false;
    public bool $isEditingPresence = false;

    // SAISIE
    public string $matricule = '';
    public $etudiantTrouve = null;
    public bool $matriculeExisteDeja = false;
    public $prochaineSequence = 1;
    public $prochainCodeAnonymat = '';
    public $manchettesSaisies = [];

    // UI
    public string $search = '';
    public int $perPage = 30;
    public string $message = '';
    public string $messageType = 'info';
    public string $sessionType = 'normale';

    // LISTENERS
    protected $listeners = ['save-presence-shortcut' => 'savePresence'];

    // QUERY STRING
    protected $queryString = [
        'step' => ['except' => 'niveau'],
        'niveau_id' => ['except' => null],
        'parcours_id' => ['except' => null, 'as' => 'parcours'],
        'examen_id' => ['except' => null, 'as' => 'examen'],
        'ec_id' => ['except' => null, 'as' => 'ec'],
        'search' => ['except' => null, 'as' => 'q'],
        'perPage' => ['except' => 30],
        'matricule' => ['except' => ''],
    ];

    // VALIDATION
    protected $rules = [
        'matricule' => 'required|string',
        'totalManchettesPresentes' => 'required|integer|min:1',
    ];

    // HOOKS
    public function updatedSearch() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }
    
    public function updatedExamenId()
    {
        $this->examenSelected = $this->examen_id ? Examen::find($this->examen_id) : null;
        $this->resetPage();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['step', 'niveau_id', 'parcours_id', 'examen_id', 'ec_id'])) {
            $this->loadDataFromUrl();
            if (in_array($propertyName, ['niveau_id', 'parcours_id', 'examen_id'])) {
                $this->resetPage();
            }
        }
    }

    // INITIALISATION
    public function mount()
    {
        $this->niveaux = new Collection();
        $this->parcours = new Collection();
        $this->examens = new Collection();
        
        $this->sessionType = Manchette::getCurrentSessionType();
        $this->loadNiveaux();
        
        // Chargement URL
        $this->step = request()->query('step', 'niveau');
        $this->niveau_id = request()->query('niveau_id');
        $this->parcours_id = request()->query('parcours');
        $this->examen_id = request()->query('examen');
        $this->ec_id = request()->query('ec');
        $this->search = request()->query('q', '');
        $this->perPage = (int) request()->query('perPage', 30);
        
        $this->totalManchettesPresentes = 0;
        $this->progressCount = 0;
        $this->loadDataFromUrl();
    }

    // CHARGEMENT DONNÉES
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
                        $this->loadExamens();

                        if ($this->examen_id) {
                            $this->examenSelected = Examen::find($this->examen_id);

                            if ($this->examenSelected && $this->ec_id) {
                                $this->ecSelected = EC::find($this->ec_id);
                                if ($this->ecSelected) {
                                    $this->setupECConfiguration();
                                }
                            }
                        }
                    }
                    
                    if ($this->step === 'ec' && (!$this->examens || $this->examens->isEmpty())) {
                        $this->loadExamens();
                    }
                }
            }
        } catch (\Throwable $e) {
            logger('Erreur loadDataFromUrl: ' . $e->getMessage());
            if (!$this->examens instanceof Collection) {
                $this->examens = new Collection();
            }
        }
    }

    public function loadNiveaux()
    {
        $this->niveaux = Cache::remember('niveaux_active', now()->addMinutes(60), function () {
            return Niveau::where('is_active', true)->orderBy('id')->get();
        });
    }

    public function loadParcours()
    {
        if (!$this->niveauSelected) {
            $this->parcours = new Collection();
            return;
        }

        $this->parcours = Parcour::where('niveau_id', $this->niveauSelected->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    public function loadExamens()
    {
        if (!$this->niveauSelected) {
            $this->examens = new Collection();
            return;
        }

        $query = Examen::where('niveau_id', $this->niveauSelected->id);

        if ($this->parcoursSelected) {
            $query->where('parcours_id', $this->parcoursSelected->id);
        }

        $this->examens = $query->with('ecs')->orderByDesc('id')->get();

        if ($this->examens->count() === 1) {
            $this->examen_id = $this->examens->first()->id;
        } elseif (!$this->examen_id && $this->examens->count() > 1) {
            $this->examen_id = $this->examens->first()->id;
        }

        $this->examenSelected = $this->examen_id ? Examen::find($this->examen_id) : null;
        $this->resetPage();
    }

    // NAVIGATION
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
        
        $this->resetPage();
    }

    public function selectParcours($parcoursId = null)
    {
        $this->parcours_id = $parcoursId;
        $this->examen_id = null;
        $this->ec_id = null;

        $this->parcoursSelected = $parcoursId ? Parcour::find($parcoursId) : null;
        $this->resetFromStep('ec');

        $this->loadExamens();
        $this->step = 'ec';
        $this->resetPage();
    }

    public function selectEC($ecId)
    {
        try {
            $this->ec_id = $ecId;
            $this->ecSelected = EC::with('ue:id,nom,niveau_id,parcours_id')->find($ecId);

            if (!$this->ecSelected) {
                $errorMessage = 'Matière non trouvée';
                $this->showMessage($errorMessage, 'error');
                toastr()->error($errorMessage);
                return;
            }

            $this->setupECConfiguration();
            $this->step = 'setup';
            $this->updateUrl();

            $message = 'Matière sélectionnée: ' . $this->ecSelected->nom;
            if ($this->progressCount > 0) {
                $message .= " - {$this->progressCount} manchette(s) déjà saisie(s)";
            }
            
            $this->showMessage($message, 'success');
            toastr()->success($message, [
                'timeOut' => 3000
            ]);
            
        } catch (\Throwable $e) {
            $errorMessage = 'Erreur lors de la sélection: ' . $e->getMessage();
            $this->showMessage($errorMessage, 'error');
            toastr()->error($errorMessage);
        }
    }
    
    public function backToStep($stepName)
    {
        $this->step = $stepName;
        $this->clearMessage();

        switch ($stepName) {
            case 'niveau':
                $this->resetFromStep('niveau');
                break;
            case 'parcours':
                $this->resetFromStep('parcours');
                $this->loadParcours();
                break;
            case 'ec':
                $this->resetFromStep('setup');
                $this->loadExamens();
                $this->resetPage();
                break;
            case 'setup':
                if ($this->examenSelected && $this->ecSelected) {
                    $this->setupECConfiguration();
                }
                break;
        }
        
        $this->loadDataFromUrl();
    }

    // CONFIGURATION EC
    public function setupECConfiguration()
    {
        $this->resetPresenceData();
        $this->loadCodeSalleFromExamen();
        $this->calculateTotalEtudiants();
        $this->loadPresenceData();
        
        if ($this->step === 'saisie') {
            $this->loadStatistiques();
            $this->calculateNextSequence();
        }
    }

    // GESTION PRÉSENCES - LOGIQUE SIMPLIFIÉE
    public function loadPresenceData()
    {
        if (!$this->examenSelected || !$this->ecSelected || !$this->salleId) {
            $this->resetPresenceData();
            return;
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();
            $presence = PresenceExamen::findForCurrentSession($this->examenSelected->id, $this->salleId, $this->ecSelected->id);
            $this->progressCount = $this->getManchettesCount();
            
            if ($presence) {
                $this->totalManchettesPresentes = $presence->etudiants_presents;
                $this->hasExistingPresence = true;
            } elseif ($this->progressCount > 0) {
                $this->totalManchettesPresentes = $this->progressCount;
                $this->hasExistingPresence = false;
            } else {
                $this->totalManchettesPresentes = 0;
                $this->hasExistingPresence = false;
            }
            
        } catch (\Throwable $e) {
            logger('Erreur loadPresenceData: ' . $e->getMessage());
            $this->resetPresenceData();
        }
    }

    public function getManchettesCount()
    {
        if (!$this->examenSelected || !$this->ecSelected) {
            return 0;
        }
        
        $sessionId = Manchette::getCurrentSessionId();
        return Manchette::where('examen_id', $this->examenSelected->id)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecSelected->id))
            ->count();
    }

    public function resetPresenceData()
    {
        $this->totalManchettesPresentes = 0;
        $this->hasExistingPresence = false;
        $this->progressCount = 0;
        $this->isEditingPresence = false;
    }

    public function startEditingPresence()
    {
        $this->isEditingPresence = true;
        $this->dispatch('editing-started');
        
        if ($this->totalManchettesPresentes == 0) {
            $this->totalManchettesPresentes = max(1, min($this->progressCount, $this->totalEtudiantsTheorique));
        }
    }

    public function cancelEditingPresence()
    {
        $this->isEditingPresence = false;
        $this->loadPresenceData();
        $this->showMessage('Modification annulée', 'info');
    }

    public function savePresence()
    {
        if ($this->totalManchettesPresentes < 1) {
            $errorMessage = 'Le nombre de présents doit être au moins 1';
            $this->showMessage($errorMessage, 'error');
            toastr()->error($errorMessage);
            return;
        }
        
        if ($this->totalManchettesPresentes > $this->totalEtudiantsTheorique) {
            $errorMessage = 'Le nombre de présents ne peut pas dépasser le total des inscrits (' . $this->totalEtudiantsTheorique . ')';
            $this->showMessage($errorMessage, 'error');
            toastr()->error($errorMessage);
            return;
        }
        
        if ($this->progressCount > $this->totalManchettesPresentes) {
            $errorMessage = "Impossible : {$this->progressCount} manchettes déjà saisies, mais vous indiquez seulement {$this->totalManchettesPresentes} présents";
            $this->showMessage($errorMessage, 'error');
            toastr()->error($errorMessage, [
                'timeOut' => 6000
            ]);
            return;
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();
            
            PresenceExamen::updateOrCreate([
                'examen_id' => $this->examenSelected->id,
                'session_exam_id' => $sessionId,
                'salle_id' => $this->salleId,
                'ec_id' => $this->ecSelected->id,
            ], [
                'etudiants_presents' => $this->totalManchettesPresentes,
                'etudiants_absents' => $this->totalEtudiantsTheorique - $this->totalManchettesPresentes,
                'total_attendu' => $this->totalEtudiantsTheorique,
                'saisie_par' => Auth::id(),
                'date_saisie' => now(),
            ]);

            $this->hasExistingPresence = true;
            $this->isEditingPresence = false;
            
            $message = $this->progressCount > 0 
                ? "✅ Présence mise à jour : {$this->totalManchettesPresentes} présents" 
                : "✅ Présence configurée : {$this->totalManchettesPresentes} présents";
                
            $this->showMessage($message, 'success');
            toastr()->success($message, [
                'timeOut' => 4000
            ]);
            
        } catch (\Throwable $e) {
            $errorMessage = 'Erreur lors de la sauvegarde: ' . $e->getMessage();
            $this->showMessage($errorMessage, 'error');
            toastr()->error($errorMessage, [
                'timeOut' => 6000
            ]);
        }
    }

    public function canStartSaisie()
    {
        // Ajoutez une vérification que la propriété existe
        if (!property_exists($this, 'totalManchettesPresentes') || 
            !isset($this->totalManchettesPresentes)) {
            return false;
        }
        
        return $this->totalManchettesPresentes > 0 
            && $this->totalManchettesPresentes <= $this->totalEtudiantsTheorique
            && $this->hasExistingPresence
            && !$this->isEditingPresence
            && $this->salleId 
            && $this->codeSalle;
    }

    public function getRemainingManchettes()
    {
        // Vérification plus robuste
        if (!property_exists($this, 'totalManchettesPresentes') || 
            !isset($this->totalManchettesPresentes)) {
            return 0;
        }
        return max(0, $this->totalManchettesPresentes - $this->progressCount);
    }

    public function goToSaisie()
    {
        if (!$this->canStartSaisie()) {
            $warningMessage = 'Vous devez d\'abord configurer le nombre de présents';
            $this->showMessage($warningMessage, 'warning');
            toastr()->warning($warningMessage, [
                'timeOut' => 4000
            ]);
            return;
        }
        
        if ($this->getRemainingManchettes() <= 0) {
            $infoMessage = '✅ Toutes les manchettes ont déjà été saisies';
            $this->showMessage($infoMessage, 'info');
            toastr()->info($infoMessage, [
                'timeOut' => 3000
            ]);
            return;
        }
        
        $this->calculateNextSequence();
        $this->loadStatistiques();
        $this->step = 'saisie';
        
        $successMessage = "🏷️ Saisie prête : {$this->getRemainingManchettes()} manchette(s) restante(s)";
        $this->showMessage($successMessage, 'success');
        toastr()->success($successMessage, [
            'timeOut' => 3000
        ]);
    }

    public function getSetupStateProperty()
    {
        if (!$this->hasExistingPresence && !$this->isEditingPresence) {
            return 'not-configured';
        }
        
        if ($this->isEditingPresence) {
            return 'editing';
        }
        
        if ($this->hasExistingPresence && !$this->isEditingPresence) {
            if ($this->getRemainingManchettes() <= 0) {
                return 'completed';
            }
            return 'configured';
        }
        
        return 'unknown';
    }

    // CONFIGURATION TECHNIQUE
    public function loadCodeSalleFromExamen()
    {
        if (!$this->examenSelected || !$this->ecSelected) return;

        try {
            $examenEc = DB::table('examen_ec')
                ->where('examen_id', $this->examenSelected->id)
                ->where('ec_id', $this->ecSelected->id)
                ->first();

            if ($examenEc && $examenEc->salle_id) {
                $this->codeSalle = $examenEc->code_base ?? 'TA';
                $this->salleId = $examenEc->salle_id;
                
                if (!Salle::find($examenEc->salle_id)) {
                    $this->showMessage('Salle non trouvée pour l\'ID: ' . $examenEc->salle_id, 'error');
                    $this->salleId = null;
                }
            } else {
                $this->codeSalle = 'TA';
                $this->salleId = null;
                $this->showMessage('Aucune salle associée à cet examen et cette matière.', 'warning');
            }
        } catch (\Throwable $e) {
            $this->showMessage('Erreur lors du chargement du code salle: ' . $e->getMessage(), 'error');
        }
    }

    public function calculateTotalEtudiants()
    {
        if (!$this->niveauSelected) {
            $this->totalEtudiantsTheorique = 0;
            return;
        }

        $query = Etudiant::where('niveau_id', $this->niveauSelected->id)
            ->where('is_active', true);

        if ($this->parcoursSelected) {
            $query->where('parcours_id', $this->parcoursSelected->id);
        }

        $this->totalEtudiantsTheorique = $query->count();
    }

    // SAISIE MANCHETTES
    public function updatedMatricule()
    {
        session()->put('manchette_saisie_matricule', $this->matricule);
        $this->etudiantTrouve = null;
        $this->matriculeExisteDeja = false;
        
        if (strlen($this->matricule) >= 3) {
            $query = Etudiant::where('matricule', $this->matricule)
                ->where('niveau_id', $this->niveauSelected->id);
                
            if ($this->parcoursSelected) {
                $query->where('parcours_id', $this->parcoursSelected->id);
            }
            
            $this->etudiantTrouve = $query->first();
            
            if ($this->etudiantTrouve && $this->examenSelected && $this->ecSelected) {
                $sessionId = Manchette::getCurrentSessionId();
                
                $this->matriculeExisteDeja = Manchette::where('etudiant_id', $this->etudiantTrouve->id)
                    ->where('examen_id', $this->examenSelected->id)
                    ->where('session_exam_id', $sessionId)
                    ->whereHas('codeAnonymat', function($q) {
                        $q->where('ec_id', $this->ecSelected->id);
                    })
                    ->exists();
            }
        }
    }

    public function validerParEntree()
    {
        if ($this->etudiantTrouve && !$this->matriculeExisteDeja) {
            $this->sauvegarderManchette();
        }
    }

    public function sauvegarderManchette()
    {
        $this->validate(['matricule' => 'required|string']);

        if (!$this->etudiantTrouve || !is_object($this->etudiantTrouve)) {
            $this->showMessage('Étudiant non trouvé. Vérifiez le matricule.', 'error');
            return;
        }

        if (!isset($this->etudiantTrouve->id) || !$this->etudiantTrouve->id) {
            $this->showMessage('Données étudiant invalides. Veuillez réessayer.', 'error');
            return;
        }

        if ($this->progressCount >= $this->totalManchettesPresentes) {
            $this->showMessage('Le nombre maximum de manchettes a été atteint!', 'error');
            return;
        }

        if ($this->matriculeExisteDeja) {
            $this->showMessage('Cet étudiant a déjà une manchette pour cette matière.', 'warning');
            return;
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();
            $sessionType = Manchette::getCurrentSessionType();
            $sessionLibelle = ucfirst($sessionType);
            
            if (!$sessionId) {
                throw new \Exception("Aucune session active trouvée.");
            }

            if (!$this->salleId) {
                throw new \Exception('Salle non trouvée pour cet examen.');
            }

            $etudiantId = (int) $this->etudiantTrouve->id;

            // Vérifications anti-doublons
            $existingManchette = Manchette::where('etudiant_id', $etudiantId)
                ->where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', function ($query) {
                    $query->where('ec_id', $this->ecSelected->id);
                })
                ->with('codeAnonymat')
                ->first();

            if ($existingManchette) {
                $codeExistant = $existingManchette->codeAnonymat->code_complet ?? 'Code inconnu';
                throw new \Exception("Cet étudiant a déjà une manchette pour cette matière en session {$sessionLibelle} (Code: {$codeExistant}).");
            }

            // Vérifier code pas déjà utilisé
            $existingCode = CodeAnonymat::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->where('ec_id', $this->ecSelected->id)
                ->where('code_complet', $this->prochainCodeAnonymat)
                ->whereHas('manchettes', function($query) use ($sessionId) {
                    $query->where('session_exam_id', $sessionId);
                })
                ->with(['manchettes.etudiant'])
                ->first();

            if ($existingCode && $existingCode->manchettes->isNotEmpty()) {
                $manchetteExistante = $existingCode->manchettes->first();
                $etudiantExistant = $manchetteExistante->etudiant;
                $nomExistant = ($etudiantExistant->nom ?? 'Nom inconnu') . ' ' . ($etudiantExistant->prenoms ?? '');
                throw new \Exception("Ce code d'anonymat ({$this->prochainCodeAnonymat}) est déjà utilisé en session {$sessionLibelle} par l'étudiant {$nomExistant}.");
            }

            // Gestion manchettes supprimées
            $deletedManchette = Manchette::withTrashed()
                ->where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', function($query) {
                    $query->where('ec_id', $this->ecSelected->id)
                        ->where('code_complet', $this->prochainCodeAnonymat);
                })
                ->whereNotNull('deleted_at')
                ->first();

            if ($deletedManchette) {
                // Restaurer manchette supprimée
                $deletedManchette->restore();
                $deletedManchette->update([
                    'etudiant_id' => $etudiantId,
                    'matricule' => $this->matricule,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);
                
                $successMessage = "✅ Manchette restaurée : {$this->prochainCodeAnonymat}";
            } else {
                // Créer nouvelle manchette
                $codeAnonymat = CodeAnonymat::create([
                    'examen_id' => $this->examenSelected->id,
                    'session_exam_id' => $sessionId,
                    'ec_id' => $this->ecSelected->id,
                    'code_base' => $this->codeSalle,
                    'code_complet' => $this->prochainCodeAnonymat,
                    'sequence' => $this->prochaineSequence,
                    'saisie_par' => Auth::id(),
                ]);

                Manchette::create([
                    'etudiant_id' => $etudiantId,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'examen_id' => $this->examenSelected->id,
                    'session_exam_id' => $sessionId,
                    'matricule' => $this->matricule,
                    'saisie_par' => Auth::id(),
                ]);
                
                $successMessage = "✅ Manchette enregistrée : {$this->prochainCodeAnonymat}";
            }

            // Progression
            $this->progressCount++;
            $manchettesRestantes = $this->totalManchettesPresentes - $this->progressCount;
            
            // Préparation suivante
            $this->matricule = '';
            $this->etudiantTrouve = null;
            $this->matriculeExisteDeja = false;
            $this->prochaineSequence++;
            $this->prochainCodeAnonymat = $this->codeSalle . $this->prochaineSequence;
            
            $this->loadStatistiques();

            // Messages intelligents avec toast
            if ($manchettesRestantes <= 0) {
                $finalMessage = "🎉 Félicitations ! Toutes les manchettes ont été saisies avec succès pour la session {$sessionLibelle} !";
                 $this->showMessage($finalMessage, 'success');
                
                // Toast de célébration finale
                toastr()->success($finalMessage, [
                    'timeOut' => 8000,
                    'extendedTimeOut' => 3000,
                    'closeButton' => true
                ]);
                
                $this->dispatch('saisie-terminee', [
                    'total_manchettes' => $this->progressCount,
                    'etudiants_presents' => $this->totalManchettesPresentes,
                    'session_type' => $sessionLibelle,
                    'matiere' => $this->ecSelected->nom ?? 'Matière inconnue',
                    'code_salle' => $this->codeSalle ?? 'Salle inconnue'
                ]);
                
            } else {
                $baseMessage = "{$successMessage} (Reste: {$manchettesRestantes})";
                
                // Messages motivants avec toast
                if ($manchettesRestantes == 1) {
                    $motivationalMessage = $baseMessage . " - Plus qu'une seule manchette ! Vous y êtes presque ! 🎯";
                    $this->showMessage($motivationalMessage, 'success');
                    toastr()->success($motivationalMessage, [
                        'timeOut' => 5000,
                        'closeButton' => true
                    ]);
                } elseif ($manchettesRestantes <= 3) {
                    $motivationalMessage = $baseMessage . " - Plus que {$manchettesRestantes} manchettes ! Vous touchez au but ! 🚀";
                    $this->showMessage($motivationalMessage, 'success');
                    toastr()->success($motivationalMessage, [
                        'timeOut' => 5000,
                        'closeButton' => true
                    ]);
                } elseif ($manchettesRestantes <= 5) {
                    $motivationalMessage = $baseMessage . " - Plus que {$manchettesRestantes} manchettes ! Courage ! 💪";
                    $this->showMessage($motivationalMessage, 'success');
                    toastr()->success($motivationalMessage, [
                        'timeOut' => 4000
                    ]);
                } elseif ($manchettesRestantes <= 10) {
                    $motivationalMessage = $baseMessage . " - Plus que {$manchettesRestantes} manchettes ! Excellent travail ! 👏";
                    $this->showMessage($motivationalMessage, 'success');
                    toastr()->success($motivationalMessage, [
                        'timeOut' => 4000
                    ]);
                } else {
                    $this->showMessage($baseMessage, 'success');
                    toastr()->success($baseMessage, [
                        'timeOut' => 3000
                    ]);
                }
                
                $this->dispatch('manchette-saved');
            }

        } catch (\Exception $e) {
            $this->showMessage('Erreur lors de l\'enregistrement: ' . $e->getMessage(), 'error');
            logger('Erreur sauvegarderManchette: ' . $e->getMessage());
            return;
        }
    }

    public function calculateNextSequence()
    {
        if (!$this->examenSelected || !$this->ecSelected || !$this->codeSalle) {
            $this->prochaineSequence = 1;
            $this->prochainCodeAnonymat = ($this->codeSalle ?: 'TA') . '1';
            return;
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();

            $lastSequence = CodeAnonymat::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->where('ec_id', $this->ecSelected->id)
                ->where('code_complet', 'LIKE', $this->codeSalle . '%')
                ->max('sequence');

            $this->prochaineSequence = ($lastSequence ?? 0) + 1;
            $this->prochainCodeAnonymat = $this->codeSalle . $this->prochaineSequence;
            
        } catch (\Exception $e) {
            $this->prochaineSequence = 1;
            $this->prochainCodeAnonymat = ($this->codeSalle ?: 'TA') . '1';
        }
    }

    // ✅ MÉTHODE MANQUANTE loadStatistiques
    public function loadStatistiques()
    {
        if (!$this->examenSelected || !$this->ecSelected) {
            $this->manchettesSaisies = [];
            $this->progressCount = 0;
            return;
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();

            $this->manchettesSaisies = Manchette::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecSelected->id))
                ->with(['etudiant', 'codeAnonymat'])
                ->latest()
                ->get()
                ->toArray();

            $this->progressCount = count($this->manchettesSaisies);
            
        } catch (\Exception $e) {
            $this->manchettesSaisies = [];
            $this->progressCount = 0;
        }
    }

    // MÉTHODES UTILITAIRES
    private function ecsQuery()
    {
        if (!$this->examen_id) {
            return EC::query()->whereRaw('1=0');
        }

        $q = EC::query()
            ->select([
                'ecs.id',
                'ecs.nom', 
                'ecs.abr',
                'ecs.ue_id',
                'ecs.enseignant'
            ])
            ->join('examen_ec', 'examen_ec.ec_id', '=', 'ecs.id')
            ->where('examen_ec.examen_id', $this->examen_id)
            ->where('ecs.is_active', true)  // Add this line to filter active ECs
            ->with(['ue:id,nom,niveau_id,parcours_id']);

        // Filtrage par niveau via l'UE
        if ($this->niveauSelected) {
            $q->whereHas('ue', function($query) {
                $query->where('niveau_id', $this->niveauSelected->id);
            });
        }

        // Filtrage par parcours via l'UE  
        if ($this->parcoursSelected) {
            $q->whereHas('ue', function($query) {
                $query->where('parcours_id', $this->parcoursSelected->id);
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

    public function updateUrl()
    {
        $queryParams = array_filter([
            'step' => $this->step,
            'niveau_id' => $this->niveau_id,
            'parcours' => $this->parcours_id,
            'examen' => $this->examen_id,
            'ec' => $this->ec_id,
            'q' => $this->search,
            'perPage' => $this->perPage,
        ], function($value) {
            return !is_null($value) && $value !== '';
        });

        $url = url()->current() . '?' . http_build_query($queryParams);
        $this->dispatch('update-url', ['url' => $url]);
    }

    private function resetFromStep($step)
    {
        switch ($step) {
            case 'niveau':
                $this->niveau_id = null;
                $this->parcours_id = null;
                $this->examen_id = null;
                $this->ec_id = null;
                $this->salleId = null;
                $this->codeSalle = null;
                $this->niveauSelected = null;
                $this->resetFromStep('parcours');
                break;
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
                $this->resetPresenceData();
                $this->resetFromStep('saisie');
                break;
            case 'saisie':
                $this->resetSaisieForm();
                break;
        }
    }

    private function resetSaisieForm(): void
    {
        $this->matricule = '';
        $this->etudiantTrouve = null;
        $this->matriculeExisteDeja = false;
        session()->forget('manchette_saisie_matricule');
        $this->clearMessage();
    }

    private function showMessage($message, $type = 'info')
    {
        session()->flash('message', $message);
        session()->flash('messageType', $type);
    }

    public function clearMessage()
    {
        $this->message = '';
        $this->messageType = 'info';
    }

    public function forceReloadEcs()
    {
        if ($this->step === 'ec') {
            $this->resetPage();
            $this->clearMessage();
        }
    }

    public function getEcProgressData()
    {
        if (!$this->examenSelected) {
            return collect();
        }
        
        try {
            $sessionId = Manchette::getCurrentSessionId();
            
            return Manchette::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->with('codeAnonymat')
                ->get()
                ->groupBy('codeAnonymat.ec_id')
                ->map(function($manchettes, $ecId) use ($sessionId) {
                    // Récupérer le total prévu pour cette EC
                    $totalPrevu = PresenceExamen::where('examen_id', $this->examenSelected->id)
                        ->where('session_exam_id', $sessionId)
                        ->where('ec_id', $ecId)
                        ->value('etudiants_presents') ?? 0;
                    
                    return [
                        'ec_id' => $ecId,
                        'count' => $manchettes->count(),
                        'total_prevu' => $totalPrevu,
                        'est_termine' => $manchettes->count() >= $totalPrevu && $totalPrevu > 0,
                        'manchettes' => $manchettes
                    ];
                });
        } catch (\Exception $e) {
            return collect();
        }
    }

    // PROPRIÉTÉS CALCULÉES
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

    public function getManchettesRestantesProperty()
    {
        return max(0, $this->totalManchettesPresentes - $this->progressCount);
    }

    public function hasValidSetup()
    {
        return $this->examenSelected 
            && $this->ecSelected 
            && $this->salleId 
            && $this->totalEtudiantsTheorique > 0 
            && $this->codeSalle;
    }

    // RENDER
    public function render()
    {
        if ($this->step === 'ec' && (!$this->examens || $this->examens->isEmpty()) && $this->niveauSelected) {
            $this->loadExamens();
        }

        if (!$this->examen_id && $this->examens && $this->examens->isNotEmpty()) {
            $this->examen_id = $this->examens->first()->id;
            $this->examenSelected = $this->examens->first();
        }

        $ecs = $this->ecsQuery()->paginate($this->perPage);

        return view('livewire.manchette.manchette-saisie', [
            'ecs' => $ecs,
            'examensList' => $this->examens,
        ]);
    }
}