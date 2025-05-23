<?php

namespace App\Livewire\Manchette;

use App\Models\CodeAnonymat;
use App\Models\Manchette;
use App\Models\Etudiant;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Salle;
use App\Models\EC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 */
class ManchettesIndex extends Component
{
    use WithPagination;

    public $niveau_id;
    public $parcours_id;
    public $salle_id;
    public $examen_id;
    public $ec_id;
    public $niveaux = [];
    public $parcours = [];
    public $salles = [];
    public $ecs = [];
    public $statusFilter = 'all';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;
    public $etudiantsAvecManchettes = [];
    public $etudiantsSansManchette = [];
    public $showManchetteModal = false;
    public $code_anonymat = '';
    public $etudiant_id = null;
    public $matricule = '';
    public $editingManchetteId = null;
    public $selectedSalleCode = '';
    public $searchMode = 'matricule';
    public $searchQuery = '';
    public $searchResults = [];
    public $currentEcName = '';
    public $currentSalleName = '';
    public $currentEcDate = '';
    public $currentEcHeure = '';
    public $showDeleteModal = false;
    public $manchetteToDelete = null;
    public $message = '';
    public $messageType = '';
    public $userManchettesCount = 0;
    public $totalManchettesCount = 0;
    public $totalEtudiantsCount = 0;
    public $totalEtudiantsExpected = 0;
    public $search = '';

    protected $rules = [
        'code_anonymat' => 'required|string|max:20',
        'etudiant_id' => 'required|exists:etudiants,id',
    ];

    public function resetEtudiantSelection()
    {
        $this->etudiant_id = null;
        $this->matricule = '';
        $this->searchQuery = '';
        $this->searchResults = [];
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

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function exportManchettes()
    {
        toastr()->info('Fonctionnalité d\'export en cours de développement');
    }

    public function printManchettes()
    {
        toastr()->info('Fonctionnalité d\'impression en cours de développement');
    }

    public function openManchetteModalForEtudiant($etudiantId)
    {
        if (!$this->examen_id || !$this->salle_id || !$this->ec_id || $this->ec_id === 'all') {
            toastr()->error('Veuillez d\'abord sélectionner une matière spécifique');
            return;
        }

        $etudiant = Etudiant::find($etudiantId);
        if (!$etudiant) {
            toastr()->error('Étudiant introuvable');
            return;
        }

        $hasExistingManchette = Manchette::whereHas('codeAnonymat', function ($query) {
                $query->where('ec_id', $this->ec_id);
            })
            ->where('examen_id', $this->examen_id)
            ->where('etudiant_id', $etudiantId)
            ->exists();

        if ($hasExistingManchette) {
            toastr()->error('Cet étudiant a déjà une manchette pour cette matière');
            return;
        }

        if (empty($this->selectedSalleCode)) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base;
                $this->currentSalleName = $salle->nom;
            }
        }

        $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_complet', 'like', $this->selectedSalleCode . '%')
            ->pluck('id')
            ->toArray();

        $manchettesCount = empty($codesIds) ? 0 : Manchette::whereIn('code_anonymat_id', $codesIds)->count();
        $nextNumber = $manchettesCount + 1;
        $proposedCode = $this->selectedSalleCode . $nextNumber;

        while (CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_complet', $proposedCode)
            ->exists()) {
            $nextNumber++;
            $proposedCode = $this->selectedSalleCode . $nextNumber;
        }

        $this->code_anonymat = $proposedCode;
        $this->etudiant_id = $etudiant->id;
        $this->matricule = $etudiant->matricule;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->editingManchetteId = null;
        $this->showManchetteModal = true;
        toastr()->info('Prêt à enregistrer une manchette pour ' . $etudiant->nom . ' ' . $etudiant->prenom);
        $this->dispatch('manchette-etudiant-selected');
    }

    protected function storeFiltres()
    {
        session()->put('manchettes.filtres', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'salle_id' => $this->salle_id,
            'examen_id' => $this->examen_id,
            'ec_id' => $this->ec_id,
        ]);
    }

    protected function loadFiltres()
    {
        $filtres = session()->get('manchettes.filtres', []);
        if (isset($filtres['niveau_id'])) {
            $this->niveau_id = $filtres['niveau_id'];
            $this->updatedNiveauId();
            if (isset($filtres['parcours_id'])) {
                $this->parcours_id = $filtres['parcours_id'];
                $this->updatedParcoursId();
                if (isset($filtres['salle_id'])) {
                    $this->salle_id = $filtres['salle_id'];
                    $this->updatedSalleId();
                    if (isset($filtres['ec_id'])) {
                        $this->ec_id = $filtres['ec_id'];
                        $this->updatedEcId();
                    }
                }
            }
        }
    }

    public function clearFilter($filterName)
    {
        $this->$filterName = null;
        if ($filterName === 'niveau_id') {
            $this->parcours_id = null;
            $this->salle_id = null;
            $this->ec_id = null;
            $this->examen_id = null;
        } elseif ($filterName === 'parcours_id') {
            $this->salle_id = null;
            $this->ec_id = null;
            $this->examen_id = null;
        } elseif ($filterName === 'salle_id') {
            $this->ec_id = null;
            $this->examen_id = null;
        }
        if (in_array($filterName, ['niveau_id', 'parcours_id', 'salle_id', 'ec_id'])) {
            $this->selectedSalleCode = '';
            $this->currentEcName = '';
            $this->currentSalleName = '';
            $this->currentEcDate = '';
            $this->currentEcHeure = '';
        }
        $this->storeFiltres();
        $this->resetPage();
    }

    public function resetFiltres()
    {
        $this->reset([
            'niveau_id', 'parcours_id', 'salle_id', 'examen_id', 'ec_id',
            'selectedSalleCode', 'currentEcName', 'currentSalleName',
            'currentEcDate', 'currentEcHeure'
        ]);
        session()->forget('manchettes.filtres');
        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();
        $this->resetPage();
    }

    public function mount()
    {
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();
        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();
        $this->sortField = 'created_at';
        $this->sortDirection = 'asc';
        $this->loadFiltres();
    }

    public function updatedNiveauId()
    {
        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();
        $this->parcours_id = null;
        $this->salle_id = null;
        $this->ec_id = null;
        $this->examen_id = null;
        $this->selectedSalleCode = '';
        $this->currentEcName = '';
        $this->currentSalleName = '';
        $this->currentEcDate = '';
        $this->currentEcHeure = '';

        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();
            if ($this->parcours->count() == 1) {
                $this->parcours_id = $this->parcours->first()->id;
                $this->updatedParcoursId();
            }
        }

        $this->storeFiltres();
        $this->resetPage();
    }

    public function updatedParcoursId()
    {
        $this->salles = collect();
        $this->ecs = collect();
        $this->salle_id = null;
        $this->ec_id = null;
        $this->examen_id = null;
        $this->selectedSalleCode = '';
        $this->currentEcName = '';
        $this->currentSalleName = '';
        $this->currentEcDate = '';
        $this->currentEcHeure = '';

        if ($this->niveau_id && $this->parcours_id) {
            $this->salles = DB::table('salles')
                ->join('examen_ec', 'salles.id', '=', 'examen_ec.salle_id')
                ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->whereNull('examens.deleted_at')
                ->select('salles.*')
                ->distinct()
                ->get();

            $this->totalEtudiantsCount = Etudiant::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->count();

            if ($this->salles->count() == 1) {
                $this->salle_id = $this->salles->first()->id;
                $this->updatedSalleId();
            }
        }

        $this->storeFiltres();
        $this->resetPage();
    }

    public function updatedSalleId()
    {
        $this->ecs = collect();
        $this->ec_id = null;
        $this->examen_id = null;
        $this->selectedSalleCode = '';
        $this->currentEcName = '';
        $this->currentSalleName = '';
        $this->currentEcDate = '';
        $this->currentEcHeure = '';

        if ($this->salle_id) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base ?? '';
                $this->currentSalleName = $salle->nom ?? '';
            }

            $examens = DB::table('examens')
                ->join('examen_ec', 'examens.id', '=', 'examen_ec.examen_id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->where('examen_ec.salle_id', $this->salle_id)
                ->select('examens.id')
                ->distinct()
                ->get()
                ->pluck('id');

            if ($examens->count() > 0) {
                $this->examen_id = $examens->first();
                $ecData = DB::table('ecs')
                    ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                    ->where('examen_ec.examen_id', $this->examen_id)
                    ->where('examen_ec.salle_id', $this->salle_id)
                    ->whereNull('ecs.deleted_at')
                    ->select(
                        'ecs.*',
                        'examen_ec.examen_id',
                        'examen_ec.date_specifique',
                        'examen_ec.heure_specifique'
                    )
                    ->distinct()
                    ->get();

                $ecIds = $ecData->pluck('id')->toArray();
                $codesParEC = [];
                foreach ($ecIds as $ec_id) {
                    $codesParEC[$ec_id] = CodeAnonymat::where('examen_id', $this->examen_id)
                        ->where('ec_id', $ec_id)
                        ->pluck('id')
                        ->toArray();
                }

                $manchettesCountsParEC = [];
                $userManchettesCountsParEC = [];
                foreach ($codesParEC as $ec_id => $codes) {
                    if (!empty($codes)) {
                        $manchettesCountsParEC[$ec_id] = Manchette::whereIn('code_anonymat_id', $codes)->count();
                        $userManchettesCountsParEC[$ec_id] = Manchette::whereIn('code_anonymat_id', $codes)
                            ->where('saisie_par', Auth::id())
                            ->count();
                    } else {
                        $manchettesCountsParEC[$ec_id] = 0;
                        $userManchettesCountsParEC[$ec_id] = 0;
                    }
                }

                $this->ecs = $ecData->map(function ($item) use ($manchettesCountsParEC, $userManchettesCountsParEC) {
                    $ec = new \stdClass();
                    foreach ((array)$item as $key => $value) {
                        $ec->$key = $value;
                    }
                    $ec->date_formatted = $ec->date_specifique ? \Carbon\Carbon::parse($ec->date_specifique)->format('d/m/Y') : null;
                    $ec->heure_formatted = $ec->heure_specifique ? \Carbon\Carbon::parse($ec->heure_specifique)->format('H:i') : null;
                    $ec->manchettes_count = $manchettesCountsParEC[$ec->id] ?? 0;
                    $ec->user_manchettes_count = $userManchettesCountsParEC[$ec->id] ?? 0;
                    $ec->pourcentage = $this->totalEtudiantsCount > 0
                        ? round(($ec->manchettes_count / $this->totalEtudiantsCount) * 100, 1)
                        : 0;
                    return $ec;
                });

                if ($this->ecs->count() == 1) {
                    $this->ec_id = $this->ecs->first()->id;
                    $this->updatedEcId();
                }
            }
        }

        $this->storeFiltres();
        $this->resetPage();
    }

    public function chargerEtudiants()
    {
        if (!$this->examen_id || !$this->ec_id || $this->ec_id === 'all') {
            $this->etudiantsSansManchette = collect();
            return;
        }

        $etudiants = Etudiant::where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->get();

        $etudiantsAvecManchettesIds = Manchette::join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('manchettes.examen_id', $this->examen_id)
            ->where('codes_anonymat.ec_id', $this->ec_id)
            ->pluck('manchettes.etudiant_id')
            ->toArray();

        $this->etudiantsAvecManchettes = $etudiants->whereIn('id', $etudiantsAvecManchettesIds)->values();
        $this->etudiantsSansManchette = $etudiants->whereNotIn('id', $etudiantsAvecManchettesIds)->values();
        $this->totalEtudiantsCount = $etudiants->count();
        $this->totalEtudiantsExpected = $this->totalEtudiantsCount;
    }

    public function updatedEcId()
    {
        $this->currentEcName = '';
        $this->currentEcDate = '';
        $this->currentEcHeure = '';

        if ($this->ec_id === 'all') {
            if ($this->examen_id && $this->salle_id) {
                $ecInfo = DB::table('ecs')
                    ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                    ->where('examen_ec.examen_id', $this->examen_id)
                    ->where('examen_ec.salle_id', $this->salle_id)
                    ->select('ecs.id', 'ecs.nom')
                    ->get();

                $ecNames = $ecInfo->pluck('nom')->toArray();
                $ecIds = $ecInfo->pluck('id')->toArray();
                $this->currentEcName = 'Toutes les matières (' . implode(', ', $ecNames) . ')';

                $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
                    ->whereIn('ec_id', $ecIds)
                    ->pluck('id')
                    ->toArray();

                if (!empty($codesIds)) {
                    $this->totalManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)->count();
                    $this->userManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)
                        ->where('saisie_par', Auth::id())
                        ->count();
                } else {
                    $this->totalManchettesCount = 0;
                    $this->userManchettesCount = 0;
                }

                $nombreMatieres = count($ecIds);
                $this->totalEtudiantsExpected = $nombreMatieres > 0 ? $this->totalEtudiantsCount * $nombreMatieres : 0;
            }
        } else if ($this->ec_id && $this->salle_id && $this->examen_id) {
            $ecInfo = DB::table('ecs')
                ->join('examen_ec', function ($join) {
                    $join->on('ecs.id', '=', 'examen_ec.ec_id')
                         ->where('examen_ec.examen_id', $this->examen_id)
                         ->where('examen_ec.salle_id', $this->salle_id);
                })
                ->where('ecs.id', $this->ec_id)
                ->select('ecs.nom', 'examen_ec.date_specifique', 'examen_ec.heure_specifique')
                ->first();

            if ($ecInfo) {
                $this->currentEcName = $ecInfo->nom;
                $this->currentEcDate = $ecInfo->date_specifique ? \Carbon\Carbon::parse($ecInfo->date_specifique)->format('d/m/Y') : '';
                $this->currentEcHeure = $ecInfo->heure_specifique ? \Carbon\Carbon::parse($ecInfo->heure_specifique)->format('H:i') : '';
            } else {
                Log::warning('EC info not found', [
                    'ec_id' => $this->ec_id,
                    'examen_id' => $this->examen_id,
                    'salle_id' => $this->salle_id,
                ]);
                $this->ec_id = null;
            }

            $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->pluck('id')
                ->toArray();

            if (!empty($codesIds)) {
                $this->totalManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)->count();
                $this->userManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)
                    ->where('saisie_par', Auth::id())
                    ->count();
            } else {
                $this->totalManchettesCount = 0;
                $this->userManchettesCount = 0;
            }

            $this->totalEtudiantsExpected = $this->totalEtudiantsCount;
        }

        $this->message = '';
        $this->storeFiltres();
        $this->resetPage();
    }

    public function openManchetteModal()
    {
        if (!$this->examen_id || !$this->salle_id || !$this->ec_id || $this->ec_id === 'all') {
            $this->message = 'Veuillez sélectionner une matière spécifique';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        if (empty($this->selectedSalleCode)) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base;
                $this->currentSalleName = $salle->nom;
            }
        }

        $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_complet', 'like', $this->selectedSalleCode . '%')
            ->pluck('id')
            ->toArray();

        $manchettesCount = empty($codesIds) ? 0 : Manchette::whereIn('code_anonymat_id', $codesIds)->count();
        $nextNumber = $manchettesCount + 1;
        $proposedCode = $this->selectedSalleCode . $nextNumber;

        while (CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_complet', $proposedCode)
            ->exists()) {
            $nextNumber++;
            $proposedCode = $this->selectedSalleCode . $nextNumber;
        }

        $this->code_anonymat = $proposedCode;
        $this->etudiant_id = null;
        $this->matricule = '';
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->showManchetteModal = true;
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) >= 2) {
            $this->searchEtudiant();
        } else {
            $this->searchResults = [];
        }
    }

    public function updatedSearchMode()
    {
        $this->searchQuery = '';
        $this->searchResults = [];
    }

    public function searchEtudiant()
    {
        if (empty($this->searchQuery) || strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            return;
        }

        $query = Etudiant::query();
        if ($this->searchMode === 'matricule') {
            $query->where('matricule', 'like', '%' . $this->searchQuery . '%');
        } else {
            $searchTerm = '%' . $this->searchQuery . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nom', 'like', $searchTerm)
                  ->orWhere('prenom', 'like', $searchTerm);
            });
        }

        if ($this->niveau_id) {
            $query->where('niveau_id', $this->niveau_id);
            if ($this->parcours_id) {
                $query->where('parcours_id', $this->parcours_id);
            }
        }

        $etudiantsAvecManchettes = DB::table('manchettes')
            ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('manchettes.examen_id', $this->examen_id)
            ->where('codes_anonymat.ec_id', $this->ec_id)
            ->pluck('manchettes.etudiant_id')
            ->toArray();

        if (!empty($etudiantsAvecManchettes) && !isset($this->editingManchetteId)) {
            $query->whereNotIn('id', $etudiantsAvecManchettes);
        }

        $this->searchResults = $query->limit(10)->get();
    }

    public function selectEtudiant($id)
    {
        $etudiant = Etudiant::find($id);
        if ($etudiant) {
            $this->etudiant_id = $etudiant->id;
            $this->matricule = $etudiant->matricule;
            $this->searchResults = [];
            $this->searchQuery = '';
        }
    }

    public function saveManchette()
    {
        $this->validate();

        try {
            $examen = Examen::find($this->examen_id);
            if (!$examen) {
                throw new \Exception("L'examen sélectionné n'existe pas.");
            }

            $ec = EC::find($this->ec_id);
            if (!$ec) {
                throw new \Exception("La matière sélectionnée n'existe pas.");
            }

            if (!isset($this->editingManchetteId)) {
                $existingManchette = Manchette::where('etudiant_id', $this->etudiant_id)
                    ->where('examen_id', $this->examen_id)
                    ->whereHas('codeAnonymat', function ($query) {
                        $query->where('ec_id', $this->ec_id);
                    })
                    ->first();

                if ($existingManchette) {
                    throw new \Exception("Cet étudiant a déjà une manchette pour cette matière (Code: {$existingManchette->codeAnonymat->code_complet}).");
                }
            }

            $codeAnonymat = CodeAnonymat::firstOrCreate(
                [
                    'examen_id' => $this->examen_id,
                    'ec_id' => $this->ec_id,
                    'code_complet' => $this->code_anonymat,
                ],
                [
                    'sequence' => null,
                ]
            );

            $existingManchetteWithCode = Manchette::where('code_anonymat_id', $codeAnonymat->id)
                ->when(isset($this->editingManchetteId), function ($query) {
                    return $query->where('id', '!=', $this->editingManchetteId);
                })
                ->first();

            if ($existingManchetteWithCode) {
                throw new \Exception("Ce code d'anonymat est déjà utilisé par l'étudiant {$existingManchetteWithCode->etudiant->nom} {$existingManchetteWithCode->etudiant->prenom}.");
            }

            $deletedManchette = Manchette::withTrashed()
                ->where('examen_id', $this->examen_id)
                ->where('code_anonymat_id', $codeAnonymat->id)
                ->whereNotNull('deleted_at')
                ->first();

            if ($deletedManchette) {
                $deletedManchette->restore();
                $deletedManchette->update([
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);
                $this->message = 'Manchette restaurée et mise à jour avec succès';
            } elseif (isset($this->editingManchetteId)) {
                $manchette = Manchette::find($this->editingManchetteId);
                if (!$manchette) {
                    throw new \Exception('La manchette à modifier est introuvable.');
                }

                if ($manchette->etudiant_id != $this->etudiant_id) {
                    $etudiantHasEC = Manchette::where('etudiant_id', $this->etudiant_id)
                        ->where('examen_id', $this->examen_id)
                        ->where('id', '!=', $this->editingManchetteId)
                        ->whereHas('codeAnonymat', function ($query) {
                            $query->where('ec_id', $this->ec_id);
                        })
                        ->exists();

                    if ($etudiantHasEC) {
                        throw new \Exception("Cet étudiant a déjà une manchette pour cette matière.");
                    }
                }

                $manchette->update([
                    'code_anonymat_id' => $codeAnonymat->id,
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);
                $this->message = 'Manchette modifiée avec succès';
            } else {
                Manchette::create([
                    'examen_id' => $this->examen_id,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);
                $this->message = 'Manchette enregistrée avec succès';
            }

            if (!isset($this->editingManchetteId)) {
                $this->etudiant_id = null;
                $this->matricule = '';
                $this->searchQuery = '';
                $this->searchResults = [];
                if (preg_match('/^([A-Za-z]+)(\d+)$/', $this->code_anonymat, $matches)) {
                    $prefix = $matches[1];
                    $number = (int)$matches[2] + 1;
                    $newCode = $prefix . $number;
                    while (CodeAnonymat::where('examen_id', $this->examen_id)
                        ->where('ec_id', $this->ec_id)
                        ->where('code_complet', $newCode)
                        ->exists()) {
                        $number++;
                        $newCode = $prefix . $number;
                    }
                    $this->code_anonymat = $newCode;
                }
                $this->showManchetteModal = true;
                $this->dispatch('focus-search-field');
            } else {
                $this->reset(['code_anonymat', 'etudiant_id', 'matricule', 'editingManchetteId', 'searchResults', 'searchQuery']);
                $this->showManchetteModal = false;
            }

            if ($this->examen_id && $this->ec_id) {
                $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->pluck('id')
                    ->toArray();

                if (!empty($codesIds)) {
                    $this->totalManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)->count();
                    $this->userManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)
                        ->where('saisie_par', Auth::id())
                        ->count();
                }
            }

            $this->messageType = 'success';
            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    public function editManchette($id)
    {
        $manchette = Manchette::with(['codeAnonymat', 'etudiant'])->find($id);
        if (!$manchette) {
            $this->message = 'Manchette introuvable.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        if ($manchette->codeAnonymat->ec_id != $this->ec_id) {
            $this->message = 'Cette manchette appartient à une autre matière. Veuillez sélectionner la bonne matière avant de modifier.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        $this->code_anonymat = $manchette->codeAnonymat->code_complet;
        $this->etudiant_id = $manchette->etudiant_id;
        $this->matricule = $manchette->etudiant->matricule;
        $this->editingManchetteId = $id;
        $this->showManchetteModal = true;
    }

    public function confirmDelete($id)
    {
        $manchette = Manchette::with('codeAnonymat.ec')->find($id);
        if (!$manchette) {
            $this->message = 'Manchette introuvable.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        if ($manchette->codeAnonymat->ec_id != $this->ec_id) {
            $this->message = 'Cette manchette appartient à une autre matière. Veuillez sélectionner la bonne matière avant de supprimer.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        $this->manchetteToDelete = $manchette;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->manchetteToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteManchette()
    {
        try {
            if (!$this->manchetteToDelete) {
                throw new \Exception('Manchette introuvable.');
            }

            if ($this->manchetteToDelete->isAssociated()) {
                throw new \Exception('Cette manchette est déjà associée à un résultat et ne peut pas être supprimée.');
            }

            $this->manchetteToDelete->delete();
            $this->message = 'Manchette supprimée avec succès';
            $this->messageType = 'success';
            $this->showDeleteModal = false;
            $this->manchetteToDelete = null;

            $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->pluck('id')
                ->toArray();

            if (!empty($codesIds)) {
                $this->totalManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)->count();
                $this->userManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)
                    ->where('saisie_par', Auth::id())
                    ->count();
            }

            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showDeleteModal = false;
            toastr()->error($this->message);
        }
    }

    public function render()
    {
        Log::debug('Rendering ManchettesIndex', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'salle_id' => $this->salle_id,
            'examen_id' => $this->examen_id,
            'ec_id' => $this->ec_id,
            'search' => $this->search,
        ]);

        if ($this->examen_id && !Examen::find($this->examen_id)) {
            Log::warning('Invalid examen_id', ['examen_id' => $this->examen_id]);
            $this->examen_id = null;
        }
        if ($this->ec_id && $this->ec_id !== 'all' && !EC::find($this->ec_id)) {
            Log::warning('Invalid ec_id', ['ec_id' => $this->ec_id]);
            $this->ec_id = null;
        }

        if ($this->niveau_id && $this->parcours_id && $this->salle_id && $this->examen_id) {
            $query = Manchette::where('examen_id', $this->examen_id);

            if ($this->ec_id && $this->ec_id !== 'all') {
                $query->whereHas('codeAnonymat', function ($q) {
                    $q->where('ec_id', $this->ec_id)
                      ->whereNotNull('code_complet')
                      ->where('code_complet', '!=', '');
                });
            } else if ($this->ec_id === 'all' && $this->salle_id) {
                $salle = Salle::find($this->salle_id);
                if ($salle && $salle->code_base) {
                    $query->whereHas('codeAnonymat', function ($q) use ($salle) {
                        $q->where('code_complet', 'like', $salle->code_base . '%');
                    });
                } else {
                    Log::warning('Salle or code_base missing', ['salle_id' => $this->salle_id]);
                    $query = Manchette::where('id', 0);
                }
            }

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

            if (isset($this->sortField)) {
                if ($this->sortField === 'code_anonymat_id') {
                    $query->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
                        ->orderBy('codes_anonymat.code_complet', $this->sortDirection)
                        ->select('manchettes.*');
                } elseif ($this->sortField === 'etudiant_id') {
                    $query->join('etudiants', 'manchettes.etudiant_id', '=', 'etudiants.id')
                        ->orderBy('etudiants.nom', $this->sortDirection)
                        ->orderBy('etudiants.prenom', $this->sortDirection)
                        ->select('manchettes.*');
                } elseif ($this->sortField === 'ec_id') {
                    $query->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
                        ->join('ecs', 'codes_anonymat.ec_id', '=', 'ecs.id')
                        ->orderBy('ecs.nom', $this->sortDirection)
                        ->select('manchettes.*');
                } else {
                    $query->orderBy($this->sortField, $this->sortDirection);
                }
            } else {
                $query->orderBy('created_at', 'asc');
            }

            $manchettes = $query->with(['codeAnonymat.ec', 'etudiant', 'utilisateurSaisie'])
                ->paginate($this->perPage);

            Log::debug('Manchettes retrieved', [
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
                'total' => $manchettes->total(),
                'manchettes' => $manchettes->items(),
            ]);

            if ($this->ec_id && $this->ec_id !== 'all') {
                $this->totalManchettesCount = $manchettes->total();
                $this->userManchettesCount = Manchette::where('examen_id', $this->examen_id)
                    ->where('saisie_par', Auth::id())
                    ->whereHas('codeAnonymat', function ($q) {
                        $q->where('ec_id', $this->ec_id);
                    })
                    ->count();
            } else {
                $this->totalManchettesCount = $manchettes->total();
                $this->userManchettesCount = Manchette::where('examen_id', $this->examen_id)
                    ->where('saisie_par', Auth::id())
                    ->count();
            }
        } else {
            $manchettes = Manchette::where('id', 0)->paginate($this->perPage);
            Log::debug('No manchettes retrieved due to missing filters', [
                'niveau_id' => $this->niveau_id,
                'parcours_id' => $this->parcours_id,
                'salle_id' => $this->salle_id,
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
            ]);
        }

        if ($this->ec_id && $this->ec_id !== 'all' && $this->examen_id) {
            $this->chargerEtudiants();
        }

        return view('livewire.manchette.manchettes-index', [
            'manchettes' => $manchettes,
        ]);
    }
}
