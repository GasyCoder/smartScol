<?php

namespace App\Livewire\Copie;

use App\Models\EC;
use App\Models\Copie;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;

use Livewire\Component;
use App\Models\Manchette;
use App\Models\SettingNote;
use App\Models\CodeAnonymat;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CopieSaisie extends Component
{
    use WithPagination;

    // ÉTAPES
    public string $step = 'niveau';
    public $is_active;
    public string $codeAnonymat = ''; 

    public bool $afficherRemplissageAuto = false;
    public bool $enCoursRemplissage = false;
    public array $copiesManquantes = [];
    public int $nombreCopiesCreees = 0;
    public bool $modeSync = false;

    // SÉLECTIONS
    public $niveauId = null;
    public $parcoursId = null;
    public $examenId = null;
    public $ecId = null;

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

    public string $numeroCode = ''; // Nouveau : pour le numéro seul
    public string $prefixeCode = ''; // Nouveau : pour afficher le préfixe

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


    public function updatedNumeroCode($value): void
    {
        // Reset
        $this->etudiantTrouve = null;
        $this->codeAnonymatCourant = null;
        $this->afficherChampNote = false;
        $this->peutEnregistrer = false;
        $this->noteDejaExiste = false;
        $this->noteExistante = null;
        $this->note = '';
        $this->resetErrorBag();

        // Nettoyer et valider l'entrée
        $numero = trim((string)$value);
        
        // Si vide ou non numérique, ne rien faire
        if (empty($numero) || !is_numeric($numero)) {
            $this->codeAnonymat = '';
            return;
        }

        // Construire le code complet avec le préfixe
        $prefixe = $this->codeSalle; // Récupère "AS", "TA", etc.
        $this->codeAnonymat = strtoupper($prefixe . $numero);
        
        // Vérification automatique
        $this->rechercherCodeAnonymat();
    }


    public function updatedNiveauId($value)
    {
        $this->niveauId = $value ? (int) $value : null;
    }

    public function updatedParcoursId($value)
    {
        $this->parcoursId = $value ? (int) $value : null;
    }

    public function updatedExamenId($value)
    {
        $this->examenId = $value ? (int) $value : null;
    }

    public function updatedEcId($value)
    {
        $this->ecId = $value ? (int) $value : null;
    }

    // VALIDATION
    protected function rules()
    {
        $rules = [
            'note' => 'required|numeric|min:0|max:20',
        ];
        
        if ($this->is_active) {
            $rules['matricule'] = 'required|string|min:3|max:50';
        } else {
            $rules['codeAnonymat'] = 'required|string|min:2';
        }
        
        return $rules;
    }

    protected $messages = [
        'matricule.required' => 'Le matricule est obligatoire.',
        'matricule.min' => 'Le matricule doit comporter au moins 3 caractères.',
        'codeAnonymat.required' => 'Le code anonymat est obligatoire.',
        'codeAnonymat.min' => 'Le code anonymat doit comporter au moins 2 caractères.',
        'note.required' => 'La note est obligatoire.',
        'note.numeric' => 'La note doit être un nombre.',
        'note.min' => 'La note ne peut pas être négative.',
        'note.max' => 'La note ne peut pas dépasser 20.',
    ];

    protected $casts = [
        'niveauId' => 'integer',
        'parcoursId' => 'integer',
        'examenId' => 'integer',
        'ecId' => 'integer',
    ];

    public function mount(): void
    {
        
        if (!Auth::user()->hasAnyRole(['secretaire'])) {
            abort(403, 'Accès non autorisé.');
        }

        $this->niveaux = collect();
        $this->parcours = collect();
        $this->examens = collect();
        $this->is_active = SettingNote::isActive();
        
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


    public function updatedCodeAnonymat($value): void
    {
        // Reset
        $this->etudiantTrouve = null;
        $this->codeAnonymatCourant = null;
        $this->afficherChampNote = false;
        $this->peutEnregistrer = false;
        $this->noteDejaExiste = false;
        $this->noteExistante = null;
        $this->note = '';
        $this->resetErrorBag();

        // Vérification automatique si au moins 2 caractères
        if (is_string($value) && mb_strlen(trim($value)) >= 2) {
            $this->rechercherCodeAnonymat();
        }
    }


    // MÉTHODE SIMPLIFIÉE pour recherche par code anonymat
    public function rechercherCodeAnonymat(): void
    {
        $this->etudiantTrouve = null;
        $this->codeAnonymatCourant = null;
        $this->afficherChampNote = false;
        $this->peutEnregistrer = false;
        $this->noteDejaExiste = false;

        if (!$this->codeAnonymat || !$this->examenId || !$this->ecId) {
            return;
        }

        $sessionId = Manchette::getCurrentSessionId();
        $code = strtoupper(trim($this->codeAnonymat));

        // 1. Chercher le code anonymat
        $codeAnonymatObj = CodeAnonymat::where('code_complet', $code)
            ->where('examen_id', $this->examenId)
            ->where('ec_id', $this->ecId)
            ->where('session_exam_id', $sessionId)
            ->first();

        if (!$codeAnonymatObj) {
            // Code non trouvé
            return;
        }

        // 2. Vérifier qu'il a une manchette
        $manchette = Manchette::where('code_anonymat_id', $codeAnonymatObj->id)
            ->where('examen_id', $this->examenId)
            ->where('session_exam_id', $sessionId)
            ->with('etudiant')
            ->first();

        if (!$manchette) {
            toastr('❌ Aucune manchette pour ce code.', 'error');
            return;
        }

        // 3. Vérifier si copie existe déjà
        $copieExistante = Copie::where('examen_id', $this->examenId)
            ->where('code_anonymat_id', $codeAnonymatObj->id)
            ->where('session_exam_id', $sessionId)
            ->first();

        if ($copieExistante) {
            // Note déjà saisie
            $this->codeAnonymatCourant = $codeAnonymatObj;
            $this->etudiantTrouve = $manchette->etudiant; // Pour affichage optionnel
            $this->noteDejaExiste = true;
            $this->noteExistante = $copieExistante->note;
            return;
        }

        // 4. Tout est OK → Afficher le champ note
        $this->codeAnonymatCourant = $codeAnonymatObj;
        $this->etudiantTrouve = $manchette->etudiant; // Pour affichage optionnel
        $this->manchetteCorrespondante = $manchette;
        $this->afficherChampNote = true;

        toastr('✅ Code trouvé - Saisissez la note', 'success');
        $this->dispatch('etudiantTrouve');
        $this->dispatch('focusNote');
        
        $this->verifierPeutEnregistrer();
    }

    // Nouvelle méthode de recherche par code anonymat
    public function rechercherParCodeAnonymat(): void
    {
        // Reset des états
        $this->etudiantTrouve = null;
        $this->manchetteCorrespondante = null;
        $this->codeAnonymatCourant = null;
        $this->afficherChampNote = false;
        $this->peutEnregistrer = false;
        $this->noteDejaExiste = false;
        $this->noteExistante = null;

        if (!$this->codeAnonymat || !$this->examenId || !$this->ecId) {
            logger('❌ Données manquantes', [
                'codeAnonymat' => $this->codeAnonymat,
                'examenId' => $this->examenId,
                'ecId' => $this->ecId
            ]);
            return;
        }

        $sessionId = Manchette::getCurrentSessionId();
        $code = trim($this->codeAnonymat); // RETIREZ strtoupper() pour le moment
        $codesDisponibles = CodeAnonymat::where('examen_id', $this->examenId)
            ->where('ec_id', $this->ecId)
            ->where('session_exam_id', $sessionId)
            ->pluck('code_complet')
            ->toArray();
        logger('🔍 DEBUG Recherche code', [
            'code_saisi' => $code,
            'examen_id' => $this->examenId,
            'ec_id' => $this->ecId,
            'session_id' => $sessionId,
            'codes_disponibles' => $codesDisponibles, // VOIR TOUS LES CODES
            'nb_codes' => count($codesDisponibles)
        ]);

        // Rechercher le code anonymat (SANS strtoupper pour le moment)
        $codeAnonymatObj = CodeAnonymat::where('code_complet', $code)
            ->where('examen_id', $this->examenId)
            ->where('ec_id', $this->ecId)
            ->where('session_exam_id', $sessionId)
            ->first();

        logger('📋 Résultat recherche code', [
            'trouve' => $codeAnonymatObj ? 'OUI' : 'NON',
            'code_obj' => $codeAnonymatObj ? $codeAnonymatObj->toArray() : null
        ]);

        if (!$codeAnonymatObj) {
            toastr('❌ Code anonymat non trouvé pour cette matière.', 'error');
            return;
        }

        // Rechercher la manchette associée
        $manchette = Manchette::query()
            ->where('code_anonymat_id', $codeAnonymatObj->id)
            ->where('examen_id', $this->examenId)
            ->where('session_exam_id', $sessionId)
            ->with(['etudiant', 'codeAnonymat'])
            ->first();

        logger('👤 Résultat recherche manchette', [
            'trouve' => $manchette ? 'OUI' : 'NON',
            'manchette' => $manchette ? $manchette->toArray() : null
        ]);

        if (!$manchette) {
            toastr('❌ Aucune manchette trouvée pour ce code.', 'error');
            return;
        }

        // Vérifier si copie existe déjà
        $copieExistante = Copie::query()
            ->where('examen_id', $this->examenId)
            ->where('code_anonymat_id', $codeAnonymatObj->id)
            ->where('session_exam_id', $sessionId)
            ->first();

        logger('📝 Vérification copie existante', [
            'existe' => $copieExistante ? 'OUI' : 'NON'
        ]);

        if ($copieExistante) {
            // Note déjà saisie
            $this->etudiantTrouve = $manchette->etudiant;
            $this->codeAnonymatCourant = $codeAnonymatObj;
            $this->noteDejaExiste = true;
            $this->noteExistante = $copieExistante->note;
            
            logger('⚠️ Note déjà saisie');
            $this->clearMessage();
            return;
        }

        // Code trouvé et pas encore noté
        $this->manchetteCorrespondante = $manchette;
        $this->etudiantTrouve = $manchette->etudiant;
        $this->codeAnonymatCourant = $codeAnonymatObj;
        $this->afficherChampNote = true;
        
        logger('✅ Prêt pour saisie', [
            'etudiant' => $this->etudiantTrouve->nom,
            'afficherChampNote' => $this->afficherChampNote
        ]);
        
        toastr('✅ Code trouvé - Étudiant: ' . $this->etudiantTrouve->nom . ' ' . $this->etudiantTrouve->prenoms, 'success');
        $this->dispatch('etudiantTrouve');
        $this->dispatch('focusNote');

        $this->verifierPeutEnregistrer();
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
        
        // Initialiser le préfixe de code
        $this->prefixeCode = $this->codeSalle;
        
        // Focus sur le bon champ selon le mode
        if ($this->is_active) {
            $this->dispatch('focusMatricule');
        } else {
            $this->dispatch('focusNumeroCode'); // Nouveau dispatch
        }
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
        
        // Mettre à jour peutEnregistrer
        $this->verifierPeutEnregistrer();
    }


    public function getBoutonActiveProperty(): bool
    {
        // Vérifier la note
        $noteValide = !empty($this->note) 
            && is_numeric($this->note)
            && floatval($this->note) >= 0 
            && floatval($this->note) <= 20;
        
        // Vérifier l'identifiant selon le mode
        if ($this->is_active) {
            // Mode matricule
            $identifiantValide = !empty($this->matricule);
        } else {
            // Mode code anonymat
            $identifiantValide = !empty($this->codeAnonymat);
        }
        
        // Toutes les conditions
        return $identifiantValide
            && $noteValide
            && $this->codeAnonymatCourant
            && !$this->noteDejaExiste;
    }

    private function verifierPeutEnregistrer(): void
    {
        $noteValide = !empty($this->note) 
            && is_numeric($this->note) 
            && floatval($this->note) >= 0 
            && floatval($this->note) <= 20;
        
        // Pour mode matricule
        if ($this->is_active) {
            $identifiantValide = !empty($this->matricule);
        } 
        // Pour mode code anonymat
        else {
            $identifiantValide = !empty($this->codeAnonymat);
        }
        
        $this->peutEnregistrer = $identifiantValide
            && $noteValide
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

            // Dispatch événement de sauvegarde
            $this->dispatch('copieSauvegardee');
            
            // Reset complet du formulaire (CECI VA VIDER numeroCode automatiquement)
            $this->resetSaisieForm();
            
            // Recharger les statistiques
            $this->loadStatistiques();
            
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
        $this->codeAnonymat = '';
        $this->numeroCode = ''; // Reset du numéro
        $this->note = '';
        $this->etudiantTrouve = null;
        $this->manchetteCorrespondante = null;
        $this->codeAnonymatCourant = null;
        $this->afficherChampNote = false;
        $this->peutEnregistrer = false;
        $this->noteDejaExiste = false;
        $this->noteExistante = null;
        $this->clearMessage();
        
        $this->dispatch('resetForm');
        
        // Refocus automatiquement sur le bon champ
        if (!$this->is_active) {
            $this->dispatch('focusNumeroCode');
        } else {
            $this->dispatch('focusMatricule');
        }
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


    /**
     * Récupère l'intervalle des codes anonymat disponibles
     */
    public function getIntervalleCodesProperty(): array
    {
        if (!$this->examenId || !$this->ecId) {
            return [
                'min' => null,
                'max' => null,
                'total' => 0
            ];
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();
            
            // Récupérer tous les codes pour cette EC
            $codes = CodeAnonymat::where('examen_id', $this->examenId)
                ->where('session_exam_id', $sessionId)
                ->where('ec_id', $this->ecId)
                ->whereHas('allManchettes', function($q) use ($sessionId) {
                    $q->where('session_exam_id', $sessionId);
                })
                ->orderBy('sequence', 'asc')
                ->get();

            if ($codes->isEmpty()) {
                return [
                    'min' => null,
                    'max' => null,
                    'total' => 0
                ];
            }

            return [
                'min' => $codes->first()->code_complet,
                'max' => $codes->last()->code_complet,
                'total' => $codes->count()
            ];
        } catch (\Exception $e) {
            logger('Erreur getIntervalleCodesProperty: ' . $e->getMessage());
            return [
                'min' => null,
                'max' => null,
                'total' => 0
            ];
        }
    }


    public function toggleModeSync(): void
    {
        $this->modeSync = !$this->modeSync;
        
        if ($this->modeSync) {
            // Analyser automatiquement quand on active
            $this->analyserCopiesManquantes();
        }
    }

    public function toggleRemplissageAuto(): void
    {
        $this->afficherRemplissageAuto = !$this->afficherRemplissageAuto;
        
        if ($this->afficherRemplissageAuto) {
            $this->analyserCopiesManquantes();
        }
    }

    // Méthode pour analyser les copies manquantes
    public function analyserCopiesManquantes(): void
    {
        if (!$this->examenId || !$this->ecId) {
            $this->showMessage('❌ Veuillez sélectionner un examen et une EC.', 'error');
            return;
        }

        try {
            $sessionId = Manchette::getCurrentSessionId();
            
            // Récupérer toutes les manchettes pour cette EC
            $manchettesAvecCodes = Manchette::where('examen_id', $this->examenId)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecId))
                ->with(['codeAnonymat', 'etudiant'])
                ->get();

            if ($manchettesAvecCodes->isEmpty()) {
                $this->copiesManquantes = [];
                $this->showMessage('ℹ️ Aucune manchette trouvée pour cette EC.', 'info');
                return;
            }

            // Récupérer les codes anonymat qui ont déjà une copie
            $codesAvecCopie = Copie::where('examen_id', $this->examenId)
                ->where('session_exam_id', $sessionId)
                ->whereHas('codeAnonymat', fn($q) => $q->where('ec_id', $this->ecId))
                ->pluck('code_anonymat_id')
                ->toArray();

            // Filtrer les manchettes sans copie
            $manchettesSansCopie = $manchettesAvecCodes->filter(function($manchette) use ($codesAvecCopie) {
                return !in_array($manchette->code_anonymat_id, $codesAvecCopie);
            });

            // Préparer les données pour l'affichage
            $this->copiesManquantes = $manchettesSansCopie->map(function($manchette) {
                return [
                    'code_anonymat_id' => $manchette->code_anonymat_id,
                    'code_complet' => $manchette->codeAnonymat->code_complet,
                    'matricule' => $manchette->etudiant->matricule ?? 'N/A',
                    'nom_complet' => ($manchette->etudiant->nom ?? '') . ' ' . ($manchette->etudiant->prenoms ?? ''),
                ];
            })->sortBy('code_complet')->values()->toArray();

            if (empty($this->copiesManquantes)) {
                $this->showMessage('✅ Toutes les copies ont déjà été saisies !', 'success');
            } else {
                $this->showMessage(
                    '📋 ' . count($this->copiesManquantes) . ' copie(s) manquante(s) détectée(s).', 
                    'info'
                );
            }

        } catch (\Exception $e) {
            logger('Erreur analyserCopiesManquantes: ' . $e->getMessage());
            $this->showMessage('❌ Erreur lors de l\'analyse : ' . $e->getMessage(), 'error');
            $this->copiesManquantes = [];
        }
    }

    // Méthode pour créer automatiquement les copies manquantes avec note 0
    public function creerCopiesManquantes(): void
    {
        if (empty($this->copiesManquantes)) {
            $this->showMessage('❌ Aucune copie à créer.', 'error');
            return;
        }

        if (!$this->examenId || !$this->ecId) {
            $this->showMessage('❌ Informations manquantes.', 'error');
            return;
        }

        $this->enCoursRemplissage = true;
        $this->nombreCopiesCreees = 0;
        $erreurs = [];

        DB::beginTransaction();

        try {
            $sessionId = Manchette::getCurrentSessionId();
            $userId = Auth::id();

            foreach ($this->copiesManquantes as $copieManquante) {
                try {
                    // Vérifier une dernière fois que la copie n'existe pas (au cas où)
                    $existe = Copie::where('examen_id', $this->examenId)
                        ->where('code_anonymat_id', $copieManquante['code_anonymat_id'])
                        ->where('session_exam_id', $sessionId)
                        ->exists();

                    if (!$existe) {
                        Copie::create([
                            'examen_id' => $this->examenId,
                            'ec_id' => $this->ecId,
                            'code_anonymat_id' => $copieManquante['code_anonymat_id'],
                            'session_exam_id' => $sessionId,
                            'note' => 0.00,
                            'saisie_par' => $userId,
                            'date_saisie' => now(),
                            'commentaire' => 'Copie non remise - Note automatique',
                        ]);

                        $this->nombreCopiesCreees++;
                    }
                } catch (\Exception $e) {
                    $erreurs[] = "Erreur pour {$copieManquante['code_complet']}: " . $e->getMessage();
                    logger('Erreur création copie auto: ' . $e->getMessage(), [
                        'code' => $copieManquante['code_complet']
                    ]);
                }
            }

            DB::commit();

            // Recharger les statistiques
            $this->loadStatistiques();
            
            // Réinitialiser l'analyse
            $this->copiesManquantes = [];
            $this->afficherRemplissageAuto = false;

            // Message de succès
            if ($this->nombreCopiesCreees > 0) {
                $message = "✅ {$this->nombreCopiesCreees} copie(s) créée(s) avec succès avec la note 0/20.";
                if (!empty($erreurs)) {
                    $message .= " (" . count($erreurs) . " erreur(s))";
                }
                $this->showMessage($message, 'success');
                toastr()->success($message);
                
                // Dispatcher événement
                $this->dispatch('copiesAutomatiquesCreees', [
                    'nombre' => $this->nombreCopiesCreees
                ]);
            } else {
                $this->showMessage('⚠️ Aucune copie n\'a pu être créée.', 'warning');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            logger('Erreur transaction creerCopiesManquantes: ' . $e->getMessage());
            $this->showMessage('❌ Erreur lors de la création : ' . $e->getMessage(), 'error');
        } finally {
            $this->enCoursRemplissage = false;
        }
    }

    // Méthode pour annuler l'opération
    public function annulerRemplissageAuto(): void
    {
        $this->afficherRemplissageAuto = false;
        $this->copiesManquantes = [];
        $this->nombreCopiesCreees = 0;
    }
}