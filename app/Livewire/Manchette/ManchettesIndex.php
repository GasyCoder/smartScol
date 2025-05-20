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

    // Variables de filtrage et contexte
    public $niveau_id;
    public $parcours_id;
    public $salle_id;
    public $examen_id;
    public $ec_id; // Filtre par EC/matière

    // Liste des données pour les sélecteurs
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


    // Variables pour la modale de saisie
    public $showManchetteModal = false;
    public $code_anonymat = '';
    public $etudiant_id = null;
    public $matricule = '';
    public $editingManchetteId = null;
    public $selectedSalleCode = '';
    public $searchMode = 'matricule'; // 'matricule' ou 'nom'
    public $searchQuery = '';
    public $searchResults = [];

    // Variables pour le contexte de la modale
    public $currentEcName = ''; // Nom de la matière sélectionnée
    public $currentSalleName = ''; // Nom de la salle sélectionnée
    public $currentEcDate = ''; // Date de l'examen pour la matière sélectionnée
    public $currentEcHeure = ''; // Heure de l'examen pour la matière sélectionnée

    // Variables pour la confirmation de suppression
    public $showDeleteModal = false;
    public $manchetteToDelete = null;

    // Messages de statut
    public $message = '';
    public $messageType = '';
    public $userManchettesCount = 0;
    public $totalManchettesCount = 0;
    public $totalEtudiantsCount = 0;
    public $totalEtudiantsExpected = 0;


    public $search = '';

    // Règles de validation pour la modale
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

    // Méthode pour le tri des colonnes
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


    // Pour le filtrage par statut
    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Pour l'export
    public function exportManchettes()
    {
        // Implémentez selon vos besoins
        toastr()->info('Fonctionnalité d\'export en cours de développement');
    }

    // Pour l'impression
    public function printManchettes()
    {
        // Implémentez selon vos besoins
        toastr()->info('Fonctionnalité d\'impression en cours de développement');
    }


    /**
     * Ouvre le modal de manchette avec les informations d'un étudiant préchargées
     * @param int $etudiantId ID de l'étudiant sélectionné
     */
    public function openManchetteModalForEtudiant($etudiantId)
    {
        // Vérification des prérequis (examen, salle et EC sélectionnés)
        if (!$this->examen_id || !$this->salle_id || !$this->ec_id || $this->ec_id === 'all') {
            toastr()->error('Veuillez d\'abord sélectionner une matière spécifique');
            return;
        }

        // Récupération de l'étudiant
        $etudiant = Etudiant::find($etudiantId);
        if (!$etudiant) {
            toastr()->error('Étudiant introuvable');
            return;
        }

        // Vérification que l'étudiant n'a pas déjà une manchette pour cette matière
        $hasExistingManchette = Manchette::whereHas('codeAnonymat', function($query) {
                $query->where('ec_id', $this->ec_id);
            })
            ->where('examen_id', $this->examen_id)
            ->where('etudiant_id', $etudiantId)
            ->exists();

        if ($hasExistingManchette) {
            toastr()->error('Cet étudiant a déjà une manchette pour cette matière');
            return;
        }

        // S'assurer que le code de salle est défini
        if (empty($this->selectedSalleCode)) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base;
                $this->currentSalleName = $salle->nom;
            }
        }

        // Génération du prochain code d'anonymat disponible
        $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_complet', 'like', $this->selectedSalleCode . '%')
            ->pluck('id')
            ->toArray();

        $manchettesCount = empty($codesIds) ? 0 : Manchette::whereIn('code_anonymat_id', $codesIds)->count();
        $nextNumber = $manchettesCount + 1;
        $proposedCode = $this->selectedSalleCode . $nextNumber;

        // Vérifier si ce code est déjà utilisé et incrémenter si nécessaire
        while (CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_complet', $proposedCode)
            ->exists()) {
            $nextNumber++;
            $proposedCode = $this->selectedSalleCode . $nextNumber;
        }

        // Précharger les informations dans le formulaire
        $this->code_anonymat = $proposedCode;
        $this->etudiant_id = $etudiant->id;
        $this->matricule = $etudiant->matricule;

        // Effacer les champs de recherche
        $this->searchQuery = '';
        $this->searchResults = [];

        // Réinitialiser toute édition en cours
        $this->editingManchetteId = null;

        // Ouvrir le modal
        $this->showManchetteModal = true;

        // Notification pour feedback utilisateur
        toastr()->info('Prêt à enregistrer une manchette pour ' . $etudiant->nom . ' ' . $etudiant->prenom);

        // Émettre un événement pour focus automatique sur le bouton d'enregistrement
        $this->dispatch('manchette-etudiant-selected');
    }

    // Pour stocker les filtres en session
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

    // Pour récupérer les filtres stockés
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

    // Méthode pour réinitialiser les filtres individuels
    public function clearFilter($filterName)
    {
        // Réinitialiser le filtre spécifié
        $this->$filterName = null;

        // Réinitialiser les filtres dépendants si nécessaire
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

        // Réinitialiser les informations associées
        if (in_array($filterName, ['niveau_id', 'parcours_id', 'salle_id', 'ec_id'])) {
            $this->selectedSalleCode = '';
            $this->currentEcName = '';
            $this->currentSalleName = '';
            $this->currentEcDate = '';
            $this->currentEcHeure = '';
        }

        // Si vous utilisez la méthode storeFiltres comme pour les copies
        if (method_exists($this, 'storeFiltres')) {
            $this->storeFiltres();
        }

        $this->resetPage();
    }

    // Pour réinitialiser les filtres
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
        // Charger les niveaux
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();

        // Initialiser avec des collections vides
        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();

        // Définir l'ordre de tri par défaut (ajout)
        $this->sortField = 'created_at';
        $this->sortDirection = 'asc';

        // Charger les filtres enregistrés
        $this->loadFiltres();
    }

    public function updatedNiveauId()
    {
        // Réinitialiser les dépendances
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

        // Charger les parcours pour ce niveau
        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();

            // S'il n'y a qu'un seul parcours, le sélectionner automatiquement
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
        // Réinitialiser les dépendances
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
            // Charger toutes les salles qui ont des examens pour ce niveau et parcours
            $this->salles = DB::table('salles')
                ->join('examen_ec', 'salles.id', '=', 'examen_ec.salle_id')
                ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->whereNull('examens.deleted_at')
                ->select('salles.*')
                ->distinct()
                ->get();

            // Calculer le nombre total d'étudiants pour ces filtres
            $this->totalEtudiantsCount = Etudiant::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->count();

            // S'il n'y a qu'une seule salle, la sélectionner automatiquement
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
            // Récupérer le code et le nom de la salle
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base ?? '';
                $this->currentSalleName = $salle->nom ?? '';
            }

            // Récupérer l'ID de l'examen
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

                // Récupérer les matières avec leurs dates et heures
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

                // Compter les manchettes par EC
                $ecIds = $ecData->pluck('id')->toArray();

                // Préparer une liste des IDs de codes d'anonymat par EC
                $codesParEC = [];
                foreach ($ecIds as $ec_id) {
                    $codesParEC[$ec_id] = CodeAnonymat::where('examen_id', $this->examen_id)
                        ->where('ec_id', $ec_id)
                        ->pluck('id')
                        ->toArray();
                }

                // Compter les manchettes par EC
                $manchettesCountsParEC = [];
                foreach ($codesParEC as $ec_id => $codes) {
                    if (!empty($codes)) {
                        $manchettesCountsParEC[$ec_id] = Manchette::whereIn('code_anonymat_id', $codes)->count();
                    } else {
                        $manchettesCountsParEC[$ec_id] = 0;
                    }
                }

                // Compter les manchettes saisies par l'utilisateur actuel
                $userManchettesCountsParEC = [];
                foreach ($codesParEC as $ec_id => $codes) {
                    if (!empty($codes)) {
                        $userManchettesCountsParEC[$ec_id] = Manchette::whereIn('code_anonymat_id', $codes)
                            ->where('saisie_par', Auth::id())
                            ->count();
                    } else {
                        $userManchettesCountsParEC[$ec_id] = 0;
                    }
                }

                // Transformer en collection d'objets
                $this->ecs = $ecData->map(function($item) use ($manchettesCountsParEC, $userManchettesCountsParEC) {
                    $ec = new \stdClass();
                    foreach ((array)$item as $key => $value) {
                        $ec->$key = $value;
                    }

                    // Formatage des dates
                    $ec->date_formatted = $ec->date_specifique ? \Carbon\Carbon::parse($ec->date_specifique)->format('d/m/Y') : null;
                    $ec->heure_formatted = $ec->heure_specifique ? \Carbon\Carbon::parse($ec->heure_specifique)->format('H:i') : null;

                    // Statistiques
                    $ec->manchettes_count = $manchettesCountsParEC[$ec->id] ?? 0;
                    $ec->user_manchettes_count = $userManchettesCountsParEC[$ec->id] ?? 0;
                    $ec->pourcentage = $this->totalEtudiantsCount > 0
                        ? round(($ec->manchettes_count / $this->totalEtudiantsCount) * 100, 1)
                        : 0;

                    return $ec;
                });

                // S'il n'y a qu'une seule EC, la sélectionner automatiquement
                if ($this->ecs->count() == 1) {
                    $this->ec_id = $this->ecs->first()->id;
                    $this->updatedEcId();
                }
            }
        }

        $this->storeFiltres();
        $this->resetPage();
    }

    /**
     * Charge la liste des étudiants avec et sans manchette
     */
    public function chargerEtudiants()
    {
        if (!$this->examen_id || !$this->ec_id || $this->ec_id === 'all') {
            $this->etudiantsSansManchette = collect();
            return;
        }

        // Récupérer tous les étudiants pour ce niveau/parcours
        $etudiants = Etudiant::where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->get();

        // Récupérer les IDs des étudiants qui ont déjà une manchette pour cette matière
        $etudiantsAvecManchettesIds = Manchette::join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('manchettes.examen_id', $this->examen_id)
            ->whereHas('codeAnonymat', function($q) {
                $q->where('ec_id', $this->ec_id);
            })
            ->pluck('manchettes.etudiant_id')
            ->toArray();

        // Diviser les étudiants en deux groupes
        $this->etudiantsAvecManchettes = $etudiants->whereIn('id', $etudiantsAvecManchettesIds)->values();
        $this->etudiantsSansManchette = $etudiants->whereNotIn('id', $etudiantsAvecManchettesIds)->values();

        // Mettre à jour le nombre total d'étudiants attendus pour les statistiques
        $this->totalEtudiantsCount = $etudiants->count();
        $this->totalEtudiantsExpected = $this->totalEtudiantsCount; // Pour les calculs de pourcentage
    }

    public function updatedEcId()
    {
        $this->currentEcName = '';
        $this->currentEcDate = '';
        $this->currentEcHeure = '';

        // Cas spécial: "Toutes les matières"
        if ($this->ec_id === 'all') {
            if ($this->examen_id && $this->salle_id) {
                // Récupérer les informations sur les matières
                $ecInfo = DB::table('ecs')
                    ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                    ->where('examen_ec.examen_id', '=', $this->examen_id)
                    ->where('examen_ec.salle_id', '=', $this->salle_id)
                    ->select('ecs.id', 'ecs.nom')
                    ->get();

                $ecNames = $ecInfo->pluck('nom')->toArray();
                $ecIds = $ecInfo->pluck('id')->toArray();
                $this->currentEcName = 'Toutes les matières (' . implode(', ', $ecNames) . ')';

                // Calculer toutes les statistiques pour l'ensemble des matières
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

                // Important : ajuster le nombre total de manchettes attendues
                $nombreMatieres = count($ecIds);
                if ($nombreMatieres > 0) {
                    // Multiplier le nombre d'étudiants par le nombre de matières
                    $this->totalEtudiantsExpected = $this->totalEtudiantsCount * $nombreMatieres;
                } else {
                    $this->totalEtudiantsExpected = 0;
                }
            }
        }
        // Code existant pour une matière spécifique
        else if ($this->ec_id && $this->salle_id && $this->examen_id) {
            $ecInfo = DB::table('ecs')
                ->join('examen_ec', function($join) {
                    $join->on('ecs.id', '=', 'examen_ec.ec_id')
                        ->where('examen_ec.examen_id', '=', $this->examen_id)
                        ->where('examen_ec.salle_id', '=', $this->salle_id);
                })
                ->where('ecs.id', $this->ec_id)
                ->select('ecs.nom', 'examen_ec.date_specifique', 'examen_ec.heure_specifique')
                ->first();

            if ($ecInfo) {
                $this->currentEcName = $ecInfo->nom;
                $this->currentEcDate = $ecInfo->date_specifique ? \Carbon\Carbon::parse($ecInfo->date_specifique)->format('d/m/Y') : '';
                $this->currentEcHeure = $ecInfo->heure_specifique ? \Carbon\Carbon::parse($ecInfo->heure_specifique)->format('H:i') : '';
            }

            // Calculer le nombre de manchettes pour cette EC
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

            // Pour une matière spécifique, le nombre attendu est égal au nombre d'étudiants
            $this->totalEtudiantsExpected = $this->totalEtudiantsCount;
        }

        // Effacer tout message précédent lors du changement d'EC
        $this->message = '';

        $this->storeFiltres();
        $this->resetPage();
    }

    public function openManchetteModal()
    {
        // Vérifier que le contexte est complet
        if (!$this->examen_id || !$this->salle_id || !$this->ec_id) {
            $this->message = 'Veuillez sélectionner une matière';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Vérifier qu'une matière spécifique est sélectionnée (pas "all")
        if (!$this->ec_id || $this->ec_id === 'all') {
            $this->message = 'Veuillez sélectionner une matière spécifique pour ajouter une manchette';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // S'assurer que le code de salle est défini
        if (empty($this->selectedSalleCode)) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base;
                $this->currentSalleName = $salle->nom;
            }
        }

        // Compter les manchettes existantes pour cette matière spécifique
        $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_complet', 'like', $this->selectedSalleCode . '%')
            ->pluck('id')
            ->toArray();

        $manchettesCount = empty($codesIds) ? 0 : Manchette::whereIn('code_anonymat_id', $codesIds)->count();

        // Suggérer un numéro pour le code d'anonymat
        $nextNumber = $manchettesCount + 1;

        // Trouver un code d'anonymat non utilisé
        $baseCode = $this->selectedSalleCode;
        $proposedCode = $baseCode . $nextNumber;

        // Vérifier si ce code est déjà utilisé pour cette matière
        while (CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_complet', $proposedCode)
            ->exists()) {
            $nextNumber++;
            $proposedCode = $baseCode . $nextNumber;
        }

        // Préinitialiser le code d'anonymat avec le code unique
        $this->code_anonymat = $proposedCode;
        $this->etudiant_id = null;
        $this->matricule = '';
        $this->searchQuery = '';
        $this->searchResults = [];

        // Ouvrir la modale
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
        // Vider la recherche lorsqu'on change de mode
        $this->searchQuery = '';
        $this->searchResults = [];
    }

    public function searchEtudiant()
    {
        if (empty($this->searchQuery) || strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            return;
        }

        // Rechercher des étudiants selon le mode (matricule ou nom)
        $query = Etudiant::query();

        if ($this->searchMode === 'matricule') {
            $query->where('matricule', 'like', '%' . $this->searchQuery . '%');
        } else {
            // Recherche par nom ou prénom
            $searchTerm = '%' . $this->searchQuery . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('nom', 'like', $searchTerm)
                ->orWhere('prenom', 'like', $searchTerm);
            });
        }

        // Filtrer par niveau et parcours si définis
        if ($this->niveau_id) {
            $query->where('niveau_id', $this->niveau_id);

            if ($this->parcours_id) {
                $query->where('parcours_id', $this->parcours_id);
            }
        }

        // Exclure les étudiants qui ont déjà une manchette pour cette matière
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
            // Vider les résultats de recherche
            $this->searchResults = [];
            $this->searchQuery = '';
        }
    }

    public function saveManchette()
    {
        $this->validate();

        try {
            // Vérifier si l'examen existe
            $examen = Examen::find($this->examen_id);
            if (!$examen) {
                throw new \Exception("L'examen sélectionné n'existe pas.");
            }

            // Vérifier si l'EC existe
            $ec = EC::find($this->ec_id);
            if (!$ec) {
                throw new \Exception("La matière sélectionnée n'existe pas.");
            }

            // IMPORTANT: Vérification au niveau de l'application qu'un étudiant n'a qu'une manchette
            // par matière pour cet examen
            if (!isset($this->editingManchetteId)) {
                // Chercher les manchettes existantes pour cet étudiant, cet examen et cette matière
                $existingManchette = Manchette::where('etudiant_id', $this->etudiant_id)
                    ->where('examen_id', $this->examen_id)
                    ->whereHas('codeAnonymat', function($query) {
                        $query->where('ec_id', $this->ec_id);
                    })
                    ->first();

                if ($existingManchette) {
                    throw new \Exception("Cet étudiant a déjà une manchette pour cette matière (Code: {$existingManchette->codeAnonymat->code_complet}).");
                }
            }

            // Rechercher ou créer le code d'anonymat
            $codeAnonymat = CodeAnonymat::firstOrCreate(
                [
                    'examen_id' => $this->examen_id,
                    'ec_id' => $this->ec_id,
                    'code_complet' => $this->code_anonymat,
                ],
                [
                    'sequence' => null, // Sera extrait automatiquement dans le modèle
                ]
            );

            // Vérifier si ce code d'anonymat est déjà utilisé par une autre manchette
            $existingManchetteWithCode = Manchette::where('code_anonymat_id', $codeAnonymat->id)
                ->when(isset($this->editingManchetteId), function($query) {
                    return $query->where('id', '!=', $this->editingManchetteId);
                })
                ->first();

            if ($existingManchetteWithCode) {
                throw new \Exception("Ce code d'anonymat est déjà utilisé par l'étudiant {$existingManchetteWithCode->etudiant->nom} {$existingManchetteWithCode->etudiant->prenom}.");
            }

            // Vérifier si une manchette supprimée existe avec ce code
            $deletedManchette = Manchette::withTrashed()
                ->where('examen_id', $this->examen_id)
                ->where('code_anonymat_id', $codeAnonymat->id)
                ->whereNotNull('deleted_at')
                ->first();

            if ($deletedManchette) {
                // Restaurer et mettre à jour la manchette supprimée
                $deletedManchette->restore();
                $deletedManchette->update([
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);

                $this->message = 'Manchette restaurée et mise à jour avec succès';
                $restoredManchette = true;
            }
            elseif (isset($this->editingManchetteId)) {
                // Mode édition
                $manchette = Manchette::find($this->editingManchetteId);

                if (!$manchette) {
                    throw new \Exception('La manchette à modifier est introuvable.');
                }

                // Vérifier si l'étudiant a changé et s'il a déjà une manchette pour cette matière
                if ($manchette->etudiant_id != $this->etudiant_id) {
                    $etudiantHasEC = Manchette::where('etudiant_id', $this->etudiant_id)
                        ->where('examen_id', $this->examen_id)
                        ->where('id', '!=', $this->editingManchetteId)
                        ->whereHas('codeAnonymat', function($query) {
                            $query->where('ec_id', $this->ec_id);
                        })
                        ->exists();

                    if ($etudiantHasEC) {
                        throw new \Exception("Cet étudiant a déjà une manchette pour cette matière.");
                    }
                }

                // Mise à jour de la manchette
                $manchette->update([
                    'code_anonymat_id' => $codeAnonymat->id,
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);

                $this->message = 'Manchette modifiée avec succès';
                $restoredManchette = false;
            }
            else {
                // Mode création - création d'une nouvelle manchette
                Manchette::create([
                    'examen_id' => $this->examen_id,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);

                $this->message = 'Manchette enregistrée avec succès';
                $restoredManchette = false;
            }

            // NOUVELLE PARTIE: Maintenir la modale ouverte si on est en mode création
            if (!isset($this->editingManchetteId)) {
                // Réinitialiser les informations de l'étudiant mais garder le code prêt pour la prochaine saisie
                $this->etudiant_id = null;
                $this->matricule = '';
                $this->searchQuery = '';
                $this->searchResults = [];

                // Incrémenter automatiquement le code d'anonymat pour la prochaine saisie
                if (preg_match('/^([A-Za-z]+)(\d+)$/', $this->code_anonymat, $matches)) {
                    $prefix = $matches[1];
                    $number = (int)$matches[2] + 1;

                    // Vérifier si ce code existe déjà
                    $newCode = $prefix . $number;

                    // Continuer d'incrémenter si le code existe déjà
                    while (CodeAnonymat::where('examen_id', $this->examen_id)
                        ->where('ec_id', $this->ec_id)
                        ->where('code_complet', $newCode)
                        ->exists()) {
                        $number++;
                        $newCode = $prefix . $number;
                    }

                    $this->code_anonymat = $newCode;
                }

                // Garder la modale ouverte et mettre le focus sur le champ de recherche d'étudiant
                $this->showManchetteModal = true;
                $this->dispatch('focus-search-field');
            } else {
                // Si on était en mode édition, on ferme la modale
                $this->reset(['code_anonymat', 'etudiant_id', 'matricule', 'editingManchetteId', 'searchResults', 'searchQuery']);
                $this->showManchetteModal = false;
            }

            // Mettre à jour les compteurs
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

            // Notification
            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: '.$e->getMessage();
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

        // Vérifier que la manchette correspond à l'EC actuellement sélectionné
        if ($manchette->codeAnonymat->ec_id != $this->ec_id) {
            $this->message = 'Cette manchette appartient à une autre matière. Veuillez sélectionner la bonne matière avant de modifier.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Remplir les champs du formulaire avec les données existantes
        $this->code_anonymat = $manchette->codeAnonymat->code_complet;
        $this->etudiant_id = $manchette->etudiant_id;
        $this->matricule = $manchette->etudiant->matricule;

        // Stocker l'ID de la manchette à éditer
        $this->editingManchetteId = $id;

        // Ouvrir la modale
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

        // Vérifier que la manchette correspond à l'EC actuellement sélectionné
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

            // Vérifier que la manchette n'est pas associée à un résultat
            if ($this->manchetteToDelete->isAssociated()) {
                throw new \Exception('Cette manchette est déjà associée à un résultat et ne peut pas être supprimée.');
            }

            $this->manchetteToDelete->delete();
            $this->message = 'Manchette supprimée avec succès';
            $this->messageType = 'success';
            $this->showDeleteModal = false;
            $this->manchetteToDelete = null;

            // Mettre à jour les compteurs
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
            $this->message = 'Erreur: '.$e->getMessage();
            $this->messageType = 'error';
            $this->showDeleteModal = false;
            toastr()->error($this->message);
        }
    }


    public function render()
    {
        if ($this->niveau_id && $this->parcours_id && $this->salle_id && $this->examen_id) {
            $query = Manchette::where('examen_id', $this->examen_id);

            // Filtrer par EC si sélectionné (et ce n'est pas "all")
            if ($this->ec_id && $this->ec_id !== 'all') {
                $query->whereHas('codeAnonymat', function($q) {
                    $q->where('ec_id', $this->ec_id);
                });
            } else if ($this->ec_id === 'all' && $this->salle_id) {
                // Si "Toutes les matières" est sélectionné, récupérer les manchettes
                // pour cette salle
                $salle = Salle::find($this->salle_id);
                if ($salle && $salle->code_base) {
                    $query->whereHas('codeAnonymat', function($q) use ($salle) {
                        $q->where('code_complet', 'like', $salle->code_base . '%');
                    });
                }
            }

            // Recherche textuelle sur le code d'anonymat ou l'étudiant
            if ($this->search) {
                $query->where(function($q) {
                    $q->whereHas('codeAnonymat', function($sq) {
                        $sq->where('code_complet', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('etudiant', function($sq) {
                        $sq->where('matricule', 'like', '%' . $this->search . '%')
                        ->orWhere('nom', 'like', '%' . $this->search . '%')
                        ->orWhere('prenom', 'like', '%' . $this->search . '%');
                    });
                });
            }

            // Tri des colonnes
            if (isset($this->sortField)) {
                if ($this->sortField === 'code_anonymat_id') {
                    $query->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
                        ->orderBy('codes_anonymat.code_complet', $this->sortDirection ?? 'asc')
                        ->select('manchettes.*'); // Important pour éviter les conflits de colonnes
                }
                elseif ($this->sortField === 'etudiant_id') {
                    $query->join('etudiants', 'manchettes.etudiant_id', '=', 'etudiants.id')
                        ->orderBy('etudiants.nom', $this->sortDirection ?? 'asc')
                        ->orderBy('etudiants.prenom', $this->sortDirection ?? 'asc')
                        ->select('manchettes.*');
                }
                elseif ($this->sortField === 'ec_id') {
                    $query->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
                        ->join('ecs', 'codes_anonymat.ec_id', '=', 'ecs.id')
                        ->orderBy('ecs.nom', $this->sortDirection ?? 'asc')
                        ->select('manchettes.*');
                }
                else {
                    // Tri standard sur les colonnes de la table manchettes
                    $query->orderBy($this->sortField, $this->sortDirection ?? 'asc');
                }
            } else {
                // Tri par défaut
               $query->orderBy('created_at', 'asc');
            }

            // Pagination avec nombre personnalisable d'éléments par page
            $manchettes = $query->with(['codeAnonymat.ec', 'etudiant', 'utilisateurSaisie'])
                ->paginate($this->perPage ?? 25);

            // Mettre à jour les compteurs
            if ($this->ec_id && $this->ec_id !== 'all') {
                // Compteur total de manchettes pour cette matière
                $this->totalManchettesCount = $manchettes->total();

                // Manchettes saisies par l'utilisateur actuel
                $this->userManchettesCount = Manchette::where('examen_id', $this->examen_id)
                    ->where('saisie_par', Auth::id())
                    ->whereHas('codeAnonymat', function($q) {
                        $q->where('ec_id', $this->ec_id);
                    })
                    ->count();
            } else {
                // Pour "Toutes les matières"
                $this->totalManchettesCount = $manchettes->total();
                $this->userManchettesCount = Manchette::where('examen_id', $this->examen_id)
                    ->where('saisie_par', Auth::id())
                    ->count();
            }
        } else {
            // Pagination vide si les critères ne sont pas remplis
            $manchettes = Manchette::where('id', 0)->paginate($this->perPage ?? 25);
        }

        // Charger les étudiants sans manchette pour la section en bas du tableau
        if ($this->ec_id && $this->ec_id !== 'all' && $this->examen_id) {
            $this->chargerEtudiants();
        }

        return view('livewire.manchette.manchettes-index', [
            'manchettes' => $manchettes,
        ]);
    }
}
