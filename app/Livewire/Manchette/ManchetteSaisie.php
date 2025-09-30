<?php

namespace App\Livewire\Manchette;

use App\Models\EC;
use App\Models\UE;
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
use App\Models\PresenceExamen;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ManchetteSaisie extends Component
{
    use WithPagination;

    public $ecsDisponibles = []; // ECs filtrés selon la session
    public $ecsNonValides = []; // Pour session rattrapage
    public $statistiquesRattrapage = [];
    public $sessionNormaleId = null;

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

    // SAISIE - NOUVELLES PROPRIÉTÉS POUR CODE ANONYMAT
    public string $matricule = '';
    public string $codeAnonymatSaisi = ''; // Code saisi manuellement
    public string $codeAnonymatSuggere = ''; // Code suggéré automatiquement
    public $etudiantTrouve = null;
    public bool $matriculeExisteDeja = false;
    public $prochaineSequence = 1;
    public $prochainCodeAnonymat = '';
    public $manchettesSaisies = [];
    
    // ÉTATS DE CONFIRMATION
    public bool $showConfirmation = false;
    public bool $isValidatingCode = false;
    public array $codeValidationErrors = [];

    // UI
    public string $search = '';
    public int $perPage = 30;
    public string $message = '';
    public string $messageType = 'info';
    public string $sessionType = 'normale';

    public $enveloppe1 = '';  // Pas une chaîne vide, mais 0
    public $enveloppe2 = '';
    public $enveloppe3 = ''; 
    public $enveloppe4 = '';
    public $show_envelope_calculator = false;

    // LISTENERS
    protected $listeners = [
        'save-presence-shortcut' => 'savePresence',
        'confirm-code-anonymat' => 'confirmerCodeAnonymat',
        'cancel-confirmation' => 'annulerConfirmation'
    ];

    // QUERY STRING
    protected $queryString = [
        'step' => ['except' => 'niveau'],
        'niveau_id' => ['except' => null],
        'parcours_id' => ['except' => null, 'as' => 'parcours'],
        'examen_id' => ['except' => null, 'as' => 'examen'],
        'ec_id' => ['except' => null, 'as' => 'ec'],
        'search' => ['except' => null, 'as' => 'q'],
        'perPage' => ['except' => 30],
        // 'matricule' => ['except' => ''],
    ];

    // VALIDATION
    protected $rules = [
        'matricule' => 'required|string',
        'totalManchettesPresentes' => 'required|integer|min:1',
        'codeAnonymatSaisi' => 'required|string|regex:/^[A-Z]{2}[0-9]+$/',
    ];

    protected $messages = [
        'codeAnonymatSaisi.required' => 'Le code anonymat est obligatoire',
        'codeAnonymatSaisi.regex' => 'Le code anonymat doit être composé de 2 lettres majuscules suivies de chiffres (ex: AB1, XY25)',
    ];

    // HOOKS
    public function updatedSearch() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }
    
    public function updatedExamenId()
    {
        $this->examenSelected = $this->examen_id ? Examen::find($this->examen_id) : null;
        
        // Recalculer les données de rattrapage
        if ($this->examenSelected && $this->sessionType === 'rattrapage') {
            $this->filterECsSelonSession();
        }
        
        $this->resetPage();
    }

    // NOUVEAU : Validation automatique du code anonymat lors de la saisie
    public function updatedCodeAnonymatSaisi()
    {
        $this->validateCodeAnonymats();
        
        // Réinitialiser la confirmation si le code change
        $this->showConfirmation = false;
    }

    // MODIFIÉ : Génération automatique du matricule avec code anonymat
    public function updatedMatricule()
    {
        // Réinitialiser toutes les propriétés liées au code
        $this->etudiantTrouve = null;
        $this->matriculeExisteDeja = false;
        $this->codeAnonymatSaisi = '';
        $this->codeAnonymatSuggere = '';
        $this->showConfirmation = false;
        $this->codeValidationErrors = [];
        
        if (strlen($this->matricule) >= 3) {
            $query = Etudiant::where('matricule', $this->matricule)
                ->where('niveau_id', $this->niveauSelected->id);
                
            if ($this->parcoursSelected) {
                $query->where('parcours_id', $this->parcoursSelected->id);
            }
            
            $etudiantCandidat = $query->first();
            
            if ($etudiantCandidat) {
                // Vérification selon le type de session
                if ($this->sessionType === 'rattrapage') {
                    $this->verifierEligibiliteRattrapage($etudiantCandidat);
                } else {
                    // Session normale : validation standard
                    $this->etudiantTrouve = $etudiantCandidat;
                    $this->verifierDoublonSessionNormale();
                }
                
                // NOUVEAU : Générer la suggestion de code anonymat
                if ($this->etudiantTrouve && !$this->matriculeExisteDeja) {
                    $this->genererSuggestionCodeAnonymat();
                }
            } else {
                // Étudiant non trouvé dans le niveau/parcours
                $this->etudiantTrouve = null;
            }
        }
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
        if (!Auth::user()->hasAnyRole(['secretaire'])) {
            abort(403, 'Accès non autorisé.');
        }
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
        
        // Calculer les données de rattrapage dès que l'examen est sélectionné
        if ($this->examenSelected && $this->sessionType === 'rattrapage') {
            $this->filterECsSelonSession();
        }
        
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
        // ⚠️ VÉRIFICATION OBLIGATOIRE : Bloquer si absents non synchronisés
        if ($this->step === 'saisie' && !$this->peutQuitterEtape) {
            $this->showMessage('⚠️ Vous devez synchroniser les absents avant de continuer.', 'warning');
            toastr()->warning('Synchronisez d\'abord les ' . $this->totalAbsents . ' absent(s).');
            return; // Bloquer l'action
        }
        
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
        
        // Filtrer les ECs selon la session (calcule les statistiques de rattrapage)
        $this->filterECsSelonSession();
        
        // Recalculer APRÈS l'analyse de rattrapage
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
        if ($this->sessionType === 'rattrapage' && empty($this->statistiquesRattrapage)) {
            $this->filterECsSelonSession();
        }
        
        $this->calculateTotalEtudiants();
        $this->isEditingPresence = true;
        $this->show_envelope_calculator = false;
        $this->clearEnvelopes();
        $this->dispatch('editing-started');
        
        if ($this->totalManchettesPresentes == 0) {
            $this->totalManchettesPresentes = max(1, min($this->progressCount, $this->totalEtudiantsTheorique));
        }
    }

    public function cancelEditingPresence()
    {
        $this->isEditingPresence = false;
        $this->show_envelope_calculator = false;
        $this->clearEnvelopes();
        $this->loadPresenceData();
        $this->showMessage('Modification annulée', 'info');
    }

    public function savePresence()
    {
        // Vérifications de sécurité AVANT la validation
        if (!$this->examenSelected || !$this->examenSelected->id) {
            $errorMessage = 'Examen non sélectionné. Veuillez recommencer la sélection.';
            $this->showMessage($errorMessage, 'error');
            toastr()->error($errorMessage);
            $this->step = 'ec';
            return;
        }

        if (!$this->ecSelected || !$this->ecSelected->id) {
            $errorMessage = 'Matière (EC) non sélectionnée. Veuillez recommencer la sélection.';
            $this->showMessage($errorMessage, 'error');
            toastr()->error($errorMessage);
            $this->step = 'ec';
            return;
        }

        if (!$this->salleId) {
            $errorMessage = 'Salle non trouvée. Veuillez recommencer la sélection.';
            $this->showMessage($errorMessage, 'error');
            toastr()->error($errorMessage);
            $this->step = 'ec';
            return;
        }
        
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
        // Vérification que la propriété existe
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

        if ($this->sessionType === 'rattrapage' && $this->ecSelected) {
            // Session rattrapage : seulement les étudiants éligibles pour cette EC spécifique
            $etudiantsEligibles = $this->getEtudiantsEligiblesPourEC($this->ecSelected->id);
            $this->totalEtudiantsTheorique = $etudiantsEligibles->count();
            
            \Log::info('Total étudiants calculé pour EC rattrapage', [
                'ec_id' => $this->ecSelected->id,
                'ec_nom' => $this->ecSelected->nom,
                'etudiants_eligibles' => $this->totalEtudiantsTheorique
            ]);
        } else {
            // Session normale : tous les étudiants du niveau/parcours
            $query = Etudiant::where('niveau_id', $this->niveauSelected->id)
                ->where('is_active', true);

            if ($this->parcoursSelected) {
                $query->where('parcours_id', $this->parcoursSelected->id);
            }

            $this->totalEtudiantsTheorique = $query->count();
        }
    }

    // NOUVELLES MÉTHODES POUR CODE ANONYMAT
    private function genererSuggestionCodeAnonymat()
    {
        if (!$this->examenSelected || !$this->ecSelected) {
            return;
        }

        try {
            // Générer les lettres à partir du code salle ou utiliser des lettres par défaut
            $lettres = $this->genererLettresCode();
            
            // Trouver le prochain numéro disponible
            $sessionId = Manchette::getCurrentSessionId();
            
            // Récupérer tous les codes existants pour cette EC/session avec ce préfixe
            $codesExistants = CodeAnonymat::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->where('ec_id', $this->ecSelected->id)
                ->where('code_complet', 'LIKE', $lettres . '%')
                ->whereHas('allManchettes', function($query) use ($sessionId) {
                    $query->where('session_exam_id', $sessionId);
                })
                ->pluck('code_complet')
                ->toArray();

            // Extraire les numéros existants
            $numerosExistants = [];
            foreach ($codesExistants as $code) {
                if (preg_match('/^' . $lettres . '([0-9]+)$/', $code, $matches)) {
                    $numerosExistants[] = (int) $matches[1];
                }
            }
            
            // Trouver le premier numéro disponible
            $prochainNumero = 1;
            while (in_array($prochainNumero, $numerosExistants)) {
                $prochainNumero++;
            }
            
            // Vérifier que le numéro ne dépasse pas le nombre de présences
            if ($prochainNumero > $this->totalManchettesPresentes && $this->totalManchettesPresentes > 0) {
                // Chercher un trou dans la séquence plutôt que de dépasser
                for ($i = 1; $i <= $this->totalManchettesPresentes; $i++) {
                    if (!in_array($i, $numerosExistants)) {
                        $prochainNumero = $i;
                        break;
                    }
                }
            }
            
            $this->codeAnonymatSuggere = $lettres . $prochainNumero;
            $this->codeAnonymatSaisi = $this->codeAnonymatSuggere;
            
        } catch (\Exception $e) {
            logger('Erreur génération suggestion code: ' . $e->getMessage());
            $this->codeAnonymatSuggere = 'AS1';
            $this->codeAnonymatSaisi = 'AS1';
        }
    }

    private function genererLettresCode()
    {
        // Utiliser toujours "AS" comme préfixe par défaut (cohérent avec votre exemple AS1)
        if (!$this->codeSalle || strlen($this->codeSalle) < 1) {
            return 'AS'; // Par défaut
        }

        // Convertir le code salle en lettres majuscules
        $codeSalle = strtoupper($this->codeSalle);
        
        if (strlen($codeSalle) >= 2) {
            return substr($codeSalle, 0, 2);
        }
        
        // Si une seule lettre, compléter avec 'S'
        return $codeSalle . 'S';
    }

    private function validateCodeAnonymats()
    {
        $this->codeValidationErrors = [];

        if (empty($this->codeAnonymatSaisi)) {
            return;
        }

        // 1. Validation du format
        if (!preg_match('/^[A-Z]{2}[0-9]+$/', $this->codeAnonymatSaisi)) {
            $this->codeValidationErrors[] = 'Format invalide : utilisez 2 lettres majuscules suivies de chiffres (ex: AS1, AS25)';
        }

        // 2. Extraire le numéro
        if (preg_match('/^[A-Z]{2}([0-9]+)$/', $this->codeAnonymatSaisi, $matches)) {
            $numero = (int) $matches[1];
            
            // 3. Vérifier que le numéro ne dépasse pas les présences
            if ($numero > $this->totalManchettesPresentes && $this->totalManchettesPresentes > 0) {
                $this->codeValidationErrors[] = "Numéro trop élevé : maximum autorisé {$this->totalManchettesPresentes} (selon le nombre de présences)";
            }
            
            if ($numero < 1) {
                $this->codeValidationErrors[] = "Le numéro doit être supérieur à 0";
            }
        }

        // 4. Vérifier les doublons seulement si les autres validations passent
        if ($this->examenSelected && $this->ecSelected && empty($this->codeValidationErrors)) {
            $sessionId = Manchette::getCurrentSessionId();
            
            // Vérifier s'il existe un code anonymat avec une manchette active
            $codeExistant = CodeAnonymat::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->where('ec_id', $this->ecSelected->id)
                ->where('code_complet', $this->codeAnonymatSaisi)
                ->whereHas('allManchettes', function($query) use ($sessionId) {
                    $query->where('session_exam_id', $sessionId);
                })
                ->first();

            if ($codeExistant) {
                // Vérifier si c'est utilisé par un autre étudiant
                $manchetteExistante = $codeExistant->allManchettes()
                    ->where('session_exam_id', $sessionId)
                    ->with('etudiant')
                    ->first();
                    
                if ($manchetteExistante) {
                    // Si c'est le même étudiant (cas d'une modification), ce n'est pas un doublon
                    if ($this->etudiantTrouve && $manchetteExistante->etudiant_id == $this->etudiantTrouve->id) {
                        // Même étudiant, pas de problème
                        return;
                    }
                    
                    $etudiantExistant = $manchetteExistante->etudiant;
                    $nomExistant = $etudiantExistant ? ($etudiantExistant->nom . ' ' . ($etudiantExistant->prenom ?? '')) : 'Étudiant inconnu';
                    $this->codeValidationErrors[] = "Ce code d'anonymat est déjà utilisé par {$nomExistant}";
                }
            }
        }
    }

    public function validerEtConfirmer()
    {
        // Vérifications de base
        if (!$this->etudiantTrouve || !is_object($this->etudiantTrouve)) {
            //$this->showMessage('Étudiant non trouvé. Vérifiez le matricule.', 'error');
            return;
        }

        if ($this->matriculeExisteDeja) {
            $this->showMessage('Cet étudiant a déjà une manchette pour cette matière.', 'warning');
            return;
        }

        // Validation du code anonymat
        $this->validate([
            'codeAnonymatSaisi' => 'required|string|regex:/^[A-Z]{2}[0-9]+$/'
        ]);

        $this->validateCodeAnonymats();

        if (!empty($this->codeValidationErrors)) {
            $errorMessage = 'Erreurs de validation du code : ' . implode(', ', $this->codeValidationErrors);
            $this->showMessage($errorMessage, 'error');
            return;
        }

        // Afficher la confirmation et émettre l'événement pour le focus
        $this->showConfirmation = true;
        $this->dispatch('modal-opened'); // Ajout de l'événement
    }

    public function confirmerCodeAnonymat()
    {
        $this->showConfirmation = false;
        $this->sauvegarderManchette();
    }

    public function annulerConfirmation()
    {
        $this->showConfirmation = false;
        $this->showMessage('Enregistrement annulé. Vous pouvez modifier le code anonymat.', 'info');
    }

    public function validerParEntree()
    {
        // Vérifier si le champ matricule est valide et un étudiant est trouvé
        if ($this->etudiantTrouve && !$this->matriculeExisteDeja && !isset($this->etudiantTrouve->message_erreur)) {
            // Vérifier si un code anonymat est saisi et valide
            if (!empty($this->codeAnonymatSaisi) && $this->codeEstValide) {
                $this->validerEtConfirmer();
            } else {
                // Si le code anonymat n'est pas encore saisi, déplacer le focus vers ce champ
                $this->dispatch('focus-code-anonymat');
            }
        }
    }

    // SAISIE MANCHETTES - MODIFIÉE
    /**
     * Vérification d'éligibilité au rattrapage
     */
    private function verifierEligibiliteRattrapage($etudiantCandidat)
    {
        if (!$this->ecSelected) {
            $this->showMessage('EC non sélectionnée pour la vérification.', 'error');
            $this->etudiantTrouve = null;
            return;
        }

        // Vérifier si cet étudiant est éligible pour cette EC spécifique
        $etudiantsEligibles = $this->getEtudiantsEligiblesPourEC($this->ecSelected->id);
        $estEligible = $etudiantsEligibles->contains('etudiant_id', $etudiantCandidat->id);

        if (!$estEligible) {
            $this->verifierRaisonNonEligibiliteSpecifique($etudiantCandidat);
            return;
        }

        // Étudiant éligible - vérifier les doublons
        $this->etudiantTrouve = $etudiantCandidat;
        $this->verifierDoublonSessionRattrapage();
    }

    /**
     * Raison spécifique par EC/UE
     */
    private function verifierRaisonNonEligibiliteSpecifique($etudiantCandidat)
    {
        try {
            if (!$this->ecSelected) {
                $this->showMessage('EC non sélectionnée.', 'error');
                $this->etudiantTrouve = null;
                return;
            }

            // Récupérer l'UE de cette EC
            $ue = UE::find($this->ecSelected->ue_id);
            if (!$ue) {
                $this->showMessage('UE introuvable pour cette EC.', 'error');
                $this->etudiantTrouve = null;
                return;
            }

            // Vérifier la moyenne de l'UE en session normale
            $notesUe = DB::table('resultats_finaux')
                ->join('ecs', 'resultats_finaux.ec_id', '=', 'ecs.id')
                ->where('ecs.ue_id', $ue->id)
                ->where('resultats_finaux.etudiant_id', $etudiantCandidat->id)
                ->where('resultats_finaux.session_exam_id', $this->sessionNormaleId)
                ->where('resultats_finaux.statut', 'publie')
                ->pluck('resultats_finaux.note');

            if ($notesUe->isEmpty()) {
                $this->showMessage("Cet étudiant n'a pas de résultats pour l'UE \"{$ue->nom}\" en session normale.", 'error');
                toastr()->error("Aucun résultat en session normale pour l'UE \"{$ue->nom}\".");
            } else {
                $moyenneUe = $notesUe->avg();
                if ($moyenneUe >= 10) {
                    $this->showMessage("Cet étudiant a déjà VALIDÉ en session normale.", 'error');
                    toastr()->warning("UE \"{$ue->nom}\" déjà validée, rattrapage non autorisé.");
                } else {
                    $this->showMessage("Problème technique : UE non validée mais étudiant non trouvé dans les éligibles.", 'error');
                    toastr()->error("Erreur de cohérence des données de rattrapage.");
                }
            }

            $this->etudiantTrouve = null;

        } catch (\Exception $e) {
            $this->showMessage('Erreur lors de la vérification spécifique: ' . $e->getMessage(), 'error');
            toastr()->error('Erreur lors de la vérification d\'éligibilité.');
            $this->etudiantTrouve = null;
        }
    }

    /**
     * Vérification des doublons pour session normale
     */
    private function verifierDoublonSessionNormale()
    {
        $sessionId = Manchette::getCurrentSessionId();
        
        $this->matriculeExisteDeja = Manchette::where('etudiant_id', $this->etudiantTrouve->id)
            ->where('examen_id', $this->examenSelected->id)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function($q) {
                $q->where('ec_id', $this->ecSelected->id);
            })
            ->exists();
    }

    /**
     * Vérification des doublons pour session rattrapage
     */
    private function verifierDoublonSessionRattrapage()
    {
        $sessionId = Manchette::getCurrentSessionId();
        
        $this->matriculeExisteDeja = Manchette::where('etudiant_id', $this->etudiantTrouve->id)
            ->where('examen_id', $this->examenSelected->id)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function($q) {
                $q->where('ec_id', $this->ecSelected->id);
            })
            ->exists();
    }

    private function resetMatriculeCompletement()
    {
        // Triple réinitialisation pour garantir le nettoyage
        $this->reset(['matricule', 'etudiantTrouve', 'matriculeExisteDeja']);
        $this->matricule = '';
        $this->etudiantTrouve = null;
        $this->matriculeExisteDeja = false;
        // Déclencher les événements pour le JavaScript
        $this->dispatch('matricule-cleared');
    }

    private function resetSaisieComplete()
    {
        $this->reset([
            'matricule', 
            'codeAnonymatSaisi', 
            'codeAnonymatSuggere', 
            'etudiantTrouve', 
            'matriculeExisteDeja',
            'showConfirmation',
            'codeValidationErrors'
        ]);
        
        // Forcer la valeur vide explicitement
        $this->matricule = '';
        $this->codeAnonymatSaisi = '';
        $this->codeAnonymatSuggere = '';
        
        session()->forget('manchette_saisie_matricule');
        $this->dispatch('matricule-cleared');
        $this->dispatch('focus-matricule-input');
    }

    public function sauvegarderManchette()
    {
        // Validations rapides
        if (!$this->etudiantTrouve || !is_object($this->etudiantTrouve)) {
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
            $etudiantId = (int) $this->etudiantTrouve->id;

            // ✅ CORRIGÉ : e.prenom au lieu de e.prenoms
            $verifications = DB::select("
                SELECT 
                    m.id as manchette_id,
                    m.deleted_at,
                    ca.code_complet,
                    ca.id as code_id,
                    e.nom,
                    e.prenom
                FROM manchettes m
                INNER JOIN codes_anonymat ca ON m.code_anonymat_id = ca.id
                LEFT JOIN etudiants e ON m.etudiant_id = e.id
                WHERE m.examen_id = ? 
                    AND m.session_exam_id = ?
                    AND ca.ec_id = ?
                    AND (
                        (m.etudiant_id = ? AND m.deleted_at IS NULL)
                        OR ca.code_complet = ?
                    )
                LIMIT 5
            ", [
                $this->examenSelected->id,
                $sessionId,
                $this->ecSelected->id,
                $etudiantId,
                $this->codeAnonymatSaisi
            ]);

            $manchetteSupprimeeTrouvee = null;
            
            // Analyser les résultats
            foreach ($verifications as $verif) {
                if ($verif->deleted_at && $verif->code_complet === $this->codeAnonymatSaisi) {
                    $manchetteSupprimeeTrouvee = $verif;
                    continue;
                }
                
                if (!$verif->deleted_at && $verif->code_complet !== $this->codeAnonymatSaisi) {
                    throw new \Exception("Cet étudiant a déjà une manchette (Code: {$verif->code_complet}).");
                }
                
                // ✅ CORRIGÉ : $verif->prenom au lieu de $verif->prenoms
                if (!$verif->deleted_at && $verif->code_complet === $this->codeAnonymatSaisi && $verif->nom) {
                    $nomExistant = trim($verif->nom . ' ' . ($verif->prenom ?? ''));
                    throw new \Exception("Ce code ({$this->codeAnonymatSaisi}) est déjà utilisé par {$nomExistant}.");
                }
            }

            // Transaction ultra-rapide
            DB::transaction(function() use ($sessionId, $etudiantId, $manchetteSupprimeeTrouvee) {
                if ($manchetteSupprimeeTrouvee) {
                    DB::table('manchettes')
                        ->where('id', $manchetteSupprimeeTrouvee->manchette_id)
                        ->update([
                            'etudiant_id' => $etudiantId,
                            'saisie_par' => Auth::id(),
                            'date_saisie' => now(),
                            'deleted_at' => null,
                            'updated_at' => now()
                        ]);
                } else {
                    $codeId = DB::table('codes_anonymat')->insertGetId([
                        'examen_id' => $this->examenSelected->id,
                        'session_exam_id' => $sessionId,
                        'ec_id' => $this->ecSelected->id,
                        'code_complet' => $this->codeAnonymatSaisi,
                        'sequence' => (int) substr($this->codeAnonymatSaisi, 2),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    DB::table('manchettes')->insert([
                        'etudiant_id' => $etudiantId,
                        'code_anonymat_id' => $codeId,
                        'examen_id' => $this->examenSelected->id,
                        'session_exam_id' => $sessionId,
                        'saisie_par' => Auth::id(),
                        'date_saisie' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            });

            $this->progressCount++;
            $manchettesRestantes = $this->totalManchettesPresentes - $this->progressCount;
            
            $this->resetSaisieComplete();
            
            if ($manchettesRestantes > 0) {
                $this->dispatch('focus-matricule-input');
            } else {
                $this->loadStatistiques();
            }

            $successMessage = "Manchette enregistrée : {$this->codeAnonymatSaisi}";
            
            if ($manchettesRestantes <= 0) {
                $finalMessage = "Toutes les manchettes ont été saisies !";
                $this->showMessage($finalMessage, 'success');
                toastr()->success($finalMessage, ['timeOut' => 8000]);
            } else {
                $message = "{$successMessage} (Reste: {$manchettesRestantes})";
                toastr()->success($message, ['timeOut' => 2000]);
            }

        } catch (\Exception $e) {
            $this->showMessage('Erreur: ' . $e->getMessage(), 'error');
            logger('Erreur sauvegarderManchette: ' . $e->getMessage());
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
            ->where('ecs.is_active', true)
            ->with(['ue:id,nom,niveau_id,parcours_id']);

        // Filtrage selon la session
        if ($this->sessionType === 'rattrapage' && !empty($this->ecsDisponibles)) {
            $q->whereIn('ecs.id', $this->ecsDisponibles);
        }

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
            try {
                // Recharger les examens depuis la base de données
                $this->loadExamens();
                
                // Recharger les statistiques de progression
                if ($this->examenSelected && $this->examenSelected->id) {
                    // Forcer le rechargement des statistiques sans cache
                    Cache::forget("ec_progress_{$this->examenSelected->id}_" . Manchette::getCurrentSessionId());
                }
                
                // Reset pagination et messages
                $this->resetPage();
                $this->clearMessage();
                
                // Forcer le re-render du composant
                $this->dispatch('$refresh');
                
                $message = 'Liste des ECs actualisée avec succès!';
                $this->showMessage($message, 'success');
                toastr()->success($message);
                
            } catch (\Exception $e) {
                $errorMessage = 'Erreur lors de l\'actualisation: ' . $e->getMessage();
                $this->showMessage($errorMessage, 'error');
                toastr()->error($errorMessage);
            }
        } else {
            toastr()->info('Actualisation disponible uniquement sur la page des ECs');
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

    public function getPourcentageProgressionProperty()
    {
        if ($this->totalManchettesPresentes <= 0) {
            return 0;
        }
        
        return round(($this->progressCount / $this->totalManchettesPresentes) * 100, 1);
    }

    public function getCodeEstValideProperty()
    {
        return empty($this->codeValidationErrors) && !empty($this->codeAnonymatSaisi);
    }

    public function hasValidSetup()
    {
        return $this->examenSelected 
            && $this->ecSelected 
            && $this->salleId 
            && $this->totalEtudiantsTheorique > 0 
            && $this->codeSalle;
    }

    public function toggleEnvelopeCalculator()
    {
        $this->show_envelope_calculator = !$this->show_envelope_calculator;
        if (!$this->show_envelope_calculator) {
            $this->clearEnvelopes();
        }
    }

   public function clearEnvelopes()
    {
        $this->enveloppe1 = '';  
        $this->enveloppe2 = '';
        $this->enveloppe3 = '';
        $this->enveloppe4 = '';
        $this->calculateFromEnvelopes(); // Recalculer après effacement
    }

    // Calcul automatique quand les enveloppes changent
    public function updatedEnveloppe1()
    {
        $this->calculateFromEnvelopes();
    }

    public function updatedEnveloppe2() 
    {
        $this->calculateFromEnvelopes();
    }

    public function updatedEnveloppe3()
    {
        $this->calculateFromEnvelopes();
    }

    public function updatedEnveloppe4()
    {
        $this->calculateFromEnvelopes();
    }

    public function calculateFromEnvelopes()
    {
        // Conversion explicite en entier pour éviter l'erreur string + string
        $env1 = (int) ($this->enveloppe1 ?? 0);
        $env2 = (int) ($this->enveloppe2 ?? 0); 
        $env3 = (int) ($this->enveloppe3 ?? 0);
        $env4 = (int) ($this->enveloppe4 ?? 0);
        
        $total = $env1 + $env2 + $env3 + $env4;
        
        $this->totalManchettesPresentes = $total;
    }

    /**
     * Filtre les ECs selon le type de session
     */
    public function filterECsSelonSession()
    {
        if (!$this->examenSelected) {
            return;
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();
            $sessionType = Manchette::getCurrentSessionType();
            
            if ($sessionType === 'rattrapage') {
                // Logique rattrapage : récupérer la session normale correspondante
                $sessionRattrapage = SessionExam::find($sessionId);
                $this->sessionNormaleId = SessionExam::where('annee_universitaire_id', $sessionRattrapage->annee_universitaire_id)
                    ->where('type', 'Normale')
                    ->value('id');

                if (!$this->sessionNormaleId) {
                    $this->showMessage('Session normale non trouvée pour ce rattrapage.', 'error');
                    return;
                }

                // Analyser tous les étudiants éligibles au rattrapage
                $this->analyserEtudiantsRattrapage();
            } else {
                // Session normale : tous les ECs sont disponibles
                $this->ecsDisponibles = $this->examenSelected->ecs->pluck('id')->toArray();
                $this->ecsNonValides = [];
            }

        } catch (\Exception $e) {
            \Log::error('Erreur filterECsSelonSession', [
                'examen_id' => $this->examenSelected->id,
                'error' => $e->getMessage()
            ]);
            $this->showMessage('Erreur lors du filtrage des ECs: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Analyse tous les étudiants éligibles pour déterminer les ECs à rattraper
     */
    private function analyserEtudiantsRattrapage()
    {
        if (!$this->sessionNormaleId || !$this->examenSelected) {
            return;
        }

        try {
            // Récupérer toutes les UE de l'examen avec leurs ECs
            $uesAvecEcs = DB::table('ues')
                ->join('ecs', 'ues.id', '=', 'ecs.ue_id')
                ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                ->where('examen_ec.examen_id', $this->examenSelected->id)
                ->where('ues.niveau_id', $this->examenSelected->niveau_id)
                ->when($this->examenSelected->parcours_id, function($q) {
                    $q->where('ues.parcours_id', $this->examenSelected->parcours_id);
                })
                ->select('ues.id as ue_id', 'ues.nom as ue_nom', 'ecs.id as ec_id', 'ecs.nom as ec_nom')
                ->get()
                ->groupBy('ue_id');

            if ($uesAvecEcs->isEmpty()) {
                $this->resetStatistiquesRattrapage();
                return;
            }

            // Analyser chaque étudiant du niveau/parcours
            $etudiants = Etudiant::where('niveau_id', $this->examenSelected->niveau_id)
                ->when($this->examenSelected->parcours_id, function($q) {
                    $q->where('parcours_id', $this->examenSelected->parcours_id);
                })
                ->where('is_active', true)
                ->get();

            $ecsDisponiblesGlobal = collect();
            $statistiquesDetaillees = [];

            foreach ($etudiants as $etudiant) {
                $analyse = $this->analyserEtudiantSpecifique($etudiant, $uesAvecEcs);
                
                if (!empty($analyse['ecs_a_rattraper'])) {
                    $ecsDisponiblesGlobal = $ecsDisponiblesGlobal->merge($analyse['ecs_a_rattraper']);
                    $statistiquesDetaillees[] = $analyse['statistiques'];
                }
            }

            // Résultats globaux
            $this->ecsDisponibles = $ecsDisponiblesGlobal->unique()->values()->toArray();
            $this->statistiquesRattrapage = [
                'etudiants_eligibles' => $etudiants->count(),
                'etudiants_avec_ecs_rattrapage' => count($statistiquesDetaillees),
                'ecs_concernees' => count($this->ecsDisponibles),
                'detail_etudiants' => $statistiquesDetaillees,
                'session_normale_id' => $this->sessionNormaleId,
                'ues_analysees' => $uesAvecEcs->keys()->toArray()
            ];

            \Log::info('Analyse rattrapage granulaire terminée', [
                'examen_id' => $this->examenSelected->id,
                'etudiants_eligibles' => count($statistiquesDetaillees),
                'ecs_disponibles' => count($this->ecsDisponibles),
                'ues_analysees' => $uesAvecEcs->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur analyse rattrapage granulaire', [
                'examen_id' => $this->examenSelected->id,
                'error' => $e->getMessage()
            ]);
            $this->resetStatistiquesRattrapage();
        }
    }

    /**
     * Reset des statistiques
     */
    private function resetStatistiquesRattrapage()
    {
        $this->ecsDisponibles = [];
        $this->statistiquesRattrapage = [
            'etudiants_eligibles' => 0,
            'etudiants_avec_ecs_rattrapage' => 0,
            'ecs_concernees' => 0,
            'detail_etudiants' => [],
            'message' => 'Aucune donnée de rattrapage disponible'
        ];
    }

    /**
     * Analyse spécifique d'un étudiant UE par UE
     */
    private function analyserEtudiantSpecifique($etudiant, $uesAvecEcs)
    {
        $ecsARattraper = collect();
        $uesNonValidees = [];

        foreach ($uesAvecEcs as $ueId => $ecsUe) {
            // Calculer la moyenne de l'UE en session normale
            $notesUe = DB::table('resultats_finaux')
                ->whereIn('ec_id', $ecsUe->pluck('ec_id'))
                ->where('etudiant_id', $etudiant->id)
                ->where('session_exam_id', $this->sessionNormaleId)
                ->where('statut', 'publie')
                ->pluck('note');

            if ($notesUe->isEmpty()) {
                continue; // Étudiant n'a pas de notes pour cette UE
            }

            $moyenneUe = $notesUe->avg();

            // Si UE < 10, toutes les ECs de cette UE doivent être rattrapées
            if ($moyenneUe < 10) {
                $uesNonValidees[] = [
                    'ue_id' => $ueId,
                    'ue_nom' => $ecsUe->first()->ue_nom,
                    'moyenne' => round($moyenneUe, 2),
                    'nb_ecs' => $ecsUe->count()
                ];
                
                $ecsARattraper = $ecsARattraper->merge($ecsUe->pluck('ec_id'));
            }
        }

        return [
            'ecs_a_rattraper' => $ecsARattraper->toArray(),
            'statistiques' => [
                'etudiant_id' => $etudiant->id,
                'matricule' => $etudiant->matricule,
                'nom' => $etudiant->nom . ' ' . ($etudiant->prenom ?? ''),
                'ues_non_validees' => $uesNonValidees,
                'nb_ecs_a_rattraper' => $ecsARattraper->count()
            ]
        ];
    }

    /**
     * Calcule les étudiants éligibles pour une EC spécifique
     */
    public function getEtudiantsEligiblesPourEC($ecId)
    {
        if ($this->sessionType !== 'rattrapage' || empty($this->statistiquesRattrapage)) {
            return collect();
        }

        // Récupérer l'UE de cette EC
        $ueId = EC::where('id', $ecId)->value('ue_id');
        if (!$ueId) {
            return collect();
        }

        $etudiantsEligibles = collect();

        foreach ($this->statistiquesRattrapage['detail_etudiants'] as $etudiantStats) {
            // Vérifier si cet étudiant a cette UE dans ses UE non validées
            $aUeNonValidee = collect($etudiantStats['ues_non_validees'])
                ->contains('ue_id', $ueId);

            if ($aUeNonValidee) {
                $etudiantsEligibles->push([
                    'etudiant_id' => $etudiantStats['etudiant_id'],
                    'matricule' => $etudiantStats['matricule'],
                    'nom' => $etudiantStats['nom']
                ]);
            }
        }

        return $etudiantsEligibles;
    }


    /**
     * Synchronise automatiquement les manchettes pour les étudiants absents
     * Les absents sont les étudiants inscrits qui n'ont PAS encore de manchette
     */
    public function synchroniserManchettesAbsents()
    {
        if (!$this->examenSelected || !$this->ecSelected || !$this->salleId) {
            $this->showMessage('Configuration incomplète pour la synchronisation.', 'error');
            toastr()->error('Configuration incomplète');
            return;
        }

        // Vérifier que toutes les présences ont été saisies
        if ($this->progressCount < $this->totalManchettesPresentes) {
            $restantes = $this->totalManchettesPresentes - $this->progressCount;
            $this->showMessage("Vous devez d'abord terminer la saisie des {$restantes} manchette(s) présente(s).", 'warning');
            toastr()->warning("Il reste {$restantes} manchette(s) à saisir");
            return;
        }

        try {
            DB::beginTransaction();

            $sessionId = Manchette::getCurrentSessionId();
            $sessionType = Manchette::getCurrentSessionType();
            $lettresCode = $this->genererLettresCode();
            
            // 1️⃣ RÉCUPÉRER TOUS LES ÉTUDIANTS INSCRITS
            $query = Etudiant::where('niveau_id', $this->niveauSelected->id)
                ->where('is_active', true);
                
            if ($this->parcoursSelected) {
                $query->where('parcours_id', $this->parcoursSelected->id);
            }
            
            // Pour le rattrapage, filtrer uniquement les étudiants éligibles
            if ($sessionType === 'rattrapage' && !empty($this->statistiquesRattrapage)) {
                $etudiantsEligiblesIds = collect($this->statistiquesRattrapage['detail_etudiants'] ?? [])
                    ->pluck('etudiant_id')
                    ->toArray();
                
                $query->whereIn('id', $etudiantsEligiblesIds);
            }
            
            $tousLesEtudiants = $query->get();
            
            // 2️⃣ RÉCUPÉRER LES ÉTUDIANTS QUI ONT DÉJÀ UNE MANCHETTE (= présents)
            $etudiantsPresentsIds = Manchette::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecSelected->id))
                ->whereNotNull('etudiant_id') // Seulement les présents identifiés
                ->pluck('etudiant_id')
                ->toArray();
            
            // 3️⃣ IDENTIFIER LES ABSENTS (inscrits - présents)
            $etudiantsAbsents = $tousLesEtudiants->whereNotIn('id', $etudiantsPresentsIds);
            
            if ($etudiantsAbsents->isEmpty()) {
                $this->showMessage('Aucun étudiant absent à synchroniser.', 'info');
                toastr()->info('Tous les étudiants ont déjà une manchette');
                return;
            }
            
            // 4️⃣ RÉCUPÉRER LES NUMÉROS DÉJÀ UTILISÉS
            $numerosExistants = CodeAnonymat::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->where('ec_id', $this->ecSelected->id)
                ->where('code_complet', 'LIKE', $lettresCode . '%')
                ->pluck('sequence')
                ->toArray();

            $manchettesCreees = 0;
            $prochainNumero = 1;

            // 5️⃣ CRÉER LES MANCHETTES POUR CHAQUE ABSENT
            foreach ($etudiantsAbsents as $etudiantAbsent) {
                // Trouver le prochain numéro disponible
                while (in_array($prochainNumero, $numerosExistants)) {
                    $prochainNumero++;
                }

                $codeComplet = $lettresCode . $prochainNumero;

                // Créer le code d'anonymat avec is_absent = true
                $codeAnonymat = CodeAnonymat::create([
                    'examen_id' => $this->examenSelected->id,
                    'session_exam_id' => $sessionId,
                    'ec_id' => $this->ecSelected->id,
                    'code_base' => $lettresCode,
                    'code_complet' => $codeComplet,
                    'sequence' => $prochainNumero,
                    'is_absent' => true, // ✅ Marquer comme absent
                    'saisie_par' => Auth::id(),
                ]);

                // Créer la manchette AVEC l'etudiant_id de l'absent
                Manchette::create([
                    'etudiant_id' => $etudiantAbsent->id, // ✅ Étudiant identifié mais absent
                    'code_anonymat_id' => $codeAnonymat->id,
                    'examen_id' => $this->examenSelected->id,
                    'session_exam_id' => $sessionId,
                    'matricule' => $etudiantAbsent->matricule,
                    'saisie_par' => Auth::id(),
                ]);

                // Ajouter à la liste des numéros utilisés
                $numerosExistants[] = $prochainNumero;
                $prochainNumero++;
                $manchettesCreees++;
            }

            DB::commit();

            // Recharger les statistiques
            $this->loadStatistiques();
            $this->progressCount = $this->getManchettesCount();

            $successMessage = "✅ Synchronisation réussie : {$manchettesCreees} manchette(s) créée(s) pour les étudiants absents";
            $this->showMessage($successMessage, 'success');
            
            toastr()->success($successMessage, [
                'timeOut' => 6000,
                'closeButton' => true
            ]);

            $this->dispatch('manchettes-absents-synchronisees', [
                'total_crees' => $manchettesCreees,
                'session_type' => ucfirst($sessionType),
                'matiere' => $this->ecSelected->nom ?? 'Matière inconnue',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'Erreur lors de la synchronisation : ' . $e->getMessage();
            $this->showMessage($errorMessage, 'error');
            toastr()->error($errorMessage, [
                'timeOut' => 8000
            ]);
            
            logger('Erreur synchroniserManchettesAbsents: ' . $e->getMessage(), [
                'examen_id' => $this->examenSelected->id,
                'ec_id' => $this->ecSelected->id,
                'session_id' => $sessionId
            ]);
        }
    }


    /**
     * Vérifie si la synchronisation des absents est possible
     */
    public function getCanSynchroniserAbsentsProperty()
    {
        // Toutes les présences doivent être saisies
        $toutesPresencesSaisies = $this->progressCount >= $this->totalManchettesPresentes;
        
        // Il doit y avoir des absents
        $nombreAbsents = $this->totalEtudiantsTheorique - $this->totalManchettesPresentes;
        
        // Vérifier qu'on n'a pas déjà synchronisé
        $sessionId = Manchette::getCurrentSessionId();
        $manchettesAbsentesExistantes = 0;
        
        if ($this->examenSelected && $this->ecSelected && $sessionId) {
            $manchettesAbsentesExistantes = CodeAnonymat::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->where('ec_id', $this->ecSelected->id)
                ->where('is_absent', true)
                ->count();
        }
        
        return $toutesPresencesSaisies 
            && $nombreAbsents > 0 
            && $manchettesAbsentesExistantes == 0;
    }

    /**
     * Compte le nombre de manchettes absentes déjà créées
     */
    public function getNombreManchettesAbsentesProperty()
    {
        if (!$this->examenSelected || !$this->ecSelected) {
            return 0;
        }
        
        $sessionId = Manchette::getCurrentSessionId();
        
        return CodeAnonymat::where('examen_id', $this->examenSelected->id)
            ->where('session_exam_id', $sessionId)
            ->where('ec_id', $this->ecSelected->id)
            ->where('is_absent', true)
            ->count();
    }

    /**
     * Vérifie si l'utilisateur peut quitter cette étape
     * (synchronisation des absents obligatoire)
     */
    public function getPeutQuitterEtapeProperty()
    {
        // Si toutes les présences sont saisies ET qu'il y a des absents non synchronisés
        // alors on ne peut PAS quitter
        if ($this->canSynchroniserAbsents) {
            return false; // BLOQUÉ - doit synchroniser d'abord
        }
        
        return true; // Peut quitter
    }

    /**
     * Récupère le nombre de manchettes de présents (excluant les absents)
     */
    public function getTotalManchettesPresentsProperty()
    {
        // Vérifications de sécurité
        if (!isset($this->examenSelected) || !$this->examenSelected) {
            return 0;
        }
        
        if (!isset($this->ecSelected) || !$this->ecSelected) {
            return 0;
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();
            
            // Compter UNIQUEMENT les présents (is_absent = false)
            return Manchette::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', function($q) {
                    $q->where('ec_id', $this->ecSelected->id)
                    ->where('is_absent', false); // SEULEMENT les présents
                })
                ->count();
        } catch (\Exception $e) {
            logger('Erreur getTotalManchettesPresentsProperty: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère le nombre de manchettes d'absents synchronisés
     */
    public function getTotalManchettesAbsentsProperty()
    {
        // Vérifications de sécurité
        if (!isset($this->examenSelected) || !$this->examenSelected) {
            return 0;
        }
        
        if (!isset($this->ecSelected) || !$this->ecSelected) {
            return 0;
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();
            
            return Manchette::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', function($q) {
                    $q->where('ec_id', $this->ecSelected->id)
                    ->where('is_absent', true); // SEULEMENT les absents
                })
                ->count();
        } catch (\Exception $e) {
            logger('Erreur getTotalManchettesAbsentsProperty: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Nombre de copies restantes à saisir (sur les présents uniquement)
     */
    public function getCopiesRestantesProperty(): int
    {
        return max(0, $this->totalManchettesPresents - $this->progressCount);
    }


    /**
     * Récupère les statistiques présents/absents pour une EC
     */
    public function getStatistiquesEC($ecId): array
    {
        if (!isset($this->examenSelected) || !$this->examenSelected || !$ecId) {
            return ['presents' => 0, 'absents' => 0];
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();
            
            $manchettesEC = Manchette::where('examen_id', $this->examenSelected->id)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $ecId))
                ->with('codeAnonymat')
                ->get();
            
            return [
                'presents' => $manchettesEC->filter(fn($m) => $m->codeAnonymat && !$m->codeAnonymat->is_absent)->count(),
                'absents' => $manchettesEC->filter(fn($m) => $m->codeAnonymat && $m->codeAnonymat->is_absent)->count(),
            ];
        } catch (\Exception $e) {
            logger('Erreur getStatistiquesEC: ' . $e->getMessage());
            return ['presents' => 0, 'absents' => 0];
        }
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