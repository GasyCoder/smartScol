<?php

namespace App\Livewire\Copie;

use App\Models\EC;
use App\Models\Copie;
use App\Models\Salle;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\Manchette;
use App\Models\CodeAnonymat;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 */
class CopiesIndex extends Component
{
    use WithPagination;

    // Variables de filtrage et contexte
    public $niveau_id;
    public $parcours_id;
    public $salle_id;
    public $ec_id;
    public $examen_id;
    public $noteFilter = 'all';
    public $sortField = 'created_at';
    public $sortDirection = 'asc';
    public $perPage = 25;
    public $totalEtudiantsPerEc = [];


    // Liste des données pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $salles = [];
    public $ecs = [];
    public $etudiantsAvecCopies = [];
    public $etudiantsSansCopies = [];

    // Variables pour la modale de saisie
    public $showCopieModal = false;
    public $code_anonymat = '';
    public $note = '';
    public $editingCopieId = null;
    public $selectedSalleCode = '';

    // Informations contextuelles pour l'affichage
    public $currentEcName = '';
    public $currentSalleName = '';
    public $currentEcDate = '';
    public $currentEcHeure = '';
    public $totalCopiesCount = 0;
    public $userCopiesCount = 0;
    public $totalEtudiantsCount = 0;
    public $etudiantsSansNote = [];

    // Messages de statut
    public $message = '';
    public $messageType = '';

    public $search = '';
    public $showDeleteModal = false;
    public $copieToDelete = null;

    // Mise à jour des règles de validation pour inclure ec_id
    protected $rules = [
        'code_anonymat' => 'required|string|max:20',
        'note' => 'required|numeric|min:0|max:20',
        'ec_id' => 'required|exists:ecs,id',
    ];

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

    // Pour stocker les filtres en session
    protected function storeFiltres()
    {
        session()->put('copies.filtres', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'salle_id' => $this->salle_id,
            'ec_id' => $this->ec_id,
            'examen_id' => $this->examen_id,
        ]);
    }

    // Pour récupérer les filtres stockés
    protected function loadFiltres()
    {
        $filtres = session()->get('copies.filtres', []);

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

    // Méthode pour charger la liste des étudiants traités/non traités
    public function chargerEtatEtudiants()
    {
        if (!$this->examen_id || !$this->ec_id || $this->ec_id === 'all') {
            return;
        }

        // Récupérer tous les étudiants pour ce niveau/parcours
        $etudiants = Etudiant::where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->get();

        // Récupérer les IDs des étudiants qui ont déjà une copie pour cette matière
        $etudiantsAvecCopiesIds = Manchette::join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
            ->join('copies', 'manchettes.code_anonymat_id', '=', 'copies.code_anonymat_id')
            ->where('manchettes.examen_id', $this->examen_id)
            ->where('codes_anonymat.ec_id', $this->ec_id)
            ->pluck('manchettes.etudiant_id')
            ->toArray();

        // Diviser les étudiants en deux groupes
        $this->etudiantsAvecCopies = $etudiants->whereIn('id', $etudiantsAvecCopiesIds)->values();
        $this->etudiantsSansCopies = $etudiants->whereNotIn('id', $etudiantsAvecCopiesIds)->values();
    }

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

        $this->storeFiltres();
        $this->resetPage();
    }

    public function updatedNoteFilter()
    {
        $this->resetPage();
    }


    // Pour réinitialiser les filtres
    public function resetFiltres()
    {
        $this->reset([
            'niveau_id', 'parcours_id', 'salle_id', 'ec_id', 'examen_id',
            'selectedSalleCode', 'currentEcName', 'currentSalleName',
            'currentEcDate', 'currentEcHeure'
        ]);
        session()->forget('copies.filtres');

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


    public function exportNotes()
    {
        // Implémentez l'export selon vos besoins
        toastr()->info('Fonctionnalité d\'export en cours de développement');
    }

    public function printNotes()
    {
        // Implémentez l'impression selon vos besoins
        toastr()->info('Fonctionnalité d\'impression en cours de développement');
    }

    public function openCopieModalForEtudiant($etudiantId)
    {
        // Trouvez l'étudiant
        $etudiant = Etudiant::find($etudiantId);
        if (!$etudiant) {
            toastr()->error('Étudiant introuvable');
            return;
        }

        // Ouvrez la modal normale d'abord
        $this->openCopieModal();

        if ($this->showCopieModal) {
            // Logique spécifique à implémenter selon votre contexte
            toastr()->info('Saisie pour l\'étudiant : ' . $etudiant->nom . ' ' . $etudiant->prenom);
        }
    }


    public function openCopieModalForAll()
    {
        if (count($this->etudiantsSansCopies) === 0) {
            toastr()->info('Tous les étudiants ont déjà une note pour cette matière');
            return;
        }

        // Ouvrez la modal normale d'abord
        $this->openCopieModal();

        if ($this->showCopieModal) {
            // Logique spécifique à implémenter selon votre contexte
            toastr()->info('Prêt à saisir les ' . count($this->etudiantsSansCopies) . ' notes manquantes');
        }
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
                ->orderBy('id', 'desc')
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

                // Compter les copies pour chaque matière
                $ecIds = $ecData->pluck('id')->toArray();

                // Compter le total des copies par EC
                $copiesCounts = DB::table('copies')
                    ->where('examen_id', $this->examen_id)
                    ->whereIn('ec_id', $ecIds)
                    ->select('ec_id', DB::raw('count(*) as total'))
                    ->groupBy('ec_id')
                    ->pluck('total', 'ec_id')
                    ->toArray();

                // Compter les copies saisies par l'utilisateur actuel pour chaque EC
                $currentUserCopiesCounts = DB::table('copies')
                    ->where('examen_id', $this->examen_id)
                    ->whereIn('ec_id', $ecIds)
                    ->where('saisie_par', Auth::id())
                    ->select('ec_id', DB::raw('count(*) as total'))
                    ->groupBy('ec_id')
                    ->pluck('total', 'ec_id')
                    ->toArray();

                // Transformer en collection d'objets avec dates formatées
                $this->ecs = $ecData->map(function($item) use ($copiesCounts, $currentUserCopiesCounts) {
                    $ec = new \stdClass();
                    foreach ((array)$item as $key => $value) {
                        $ec->$key = $value;
                    }

                    // Formatage des dates
                    $ec->date_formatted = $ec->date_specifique ? \Carbon\Carbon::parse($ec->date_specifique)->format('d/m/Y') : null;
                    $ec->heure_formatted = $ec->heure_specifique ? \Carbon\Carbon::parse($ec->heure_specifique)->format('H:i') : null;

                    // Statistiques
                    $ec->has_copies = isset($copiesCounts[$ec->id]) && $copiesCounts[$ec->id] > 0;
                    $ec->copies_count = $copiesCounts[$ec->id] ?? 0;
                    $ec->user_copies_count = $currentUserCopiesCounts[$ec->id] ?? 0;
                    $ec->pourcentage = $this->totalEtudiantsCount > 0
                        ? round(($ec->copies_count / $this->totalEtudiantsCount) * 100, 1)
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

    public function updatedEcId()
    {
        // Réinitialiser les valeurs
        $this->currentEcName = '';
        $this->currentEcDate = '';
        $this->currentEcHeure = '';

        // Charger l'état des étudiants
        $this->chargerEtatEtudiants();

        // Recalculer le nombre total d'étudiants de base (toujours commencer par la vraie valeur)
        $baseEtudiantsCount = Etudiant::where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->count();

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

                // Calculer le nombre total de copies pour toutes les matières
                $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
                    ->count();

                // Copies saisies par l'utilisateur actuel
                $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
                    ->where('saisie_par', Auth::id())
                    ->count();

                // Important : ajuster le nombre total d'étudiants × matières pour le calcul du pourcentage
                // CORRIGÉ : Calcul direct sans accumulation
                $nombreMatieres = count($ecIds);
                if ($nombreMatieres > 0) {
                    // Calculer directement le nombre total d'étudiants × matières
                    $this->totalEtudiantsCount = $baseEtudiantsCount * $nombreMatieres;
                } else {
                    $this->totalEtudiantsCount = $baseEtudiantsCount;
                }
            }
        }
        // Cas normal: une matière spécifique
        else if ($this->ec_id && $this->salle_id) {
            // Réinitialiser le nombre total d'étudiants à sa valeur de base
            $this->totalEtudiantsCount = $baseEtudiantsCount;

            // Rechercher les informations sur l'examen
            $examenEc = DB::table('examen_ec')
                ->where('ec_id', $this->ec_id)
                ->where('salle_id', $this->salle_id)
                ->first();

            if ($examenEc) {
                $this->examen_id = $examenEc->examen_id;

                // Récupérer les informations de l'EC sélectionnée
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

                // Calculer le nombre de copies pour cette EC
                $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->count();

                // Copies saisies par l'utilisateur actuel
                $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->where('saisie_par', Auth::id())
                    ->count();
            }
        }

        // Effacer tout message précédent lors du changement d'EC
        $this->message = '';

        // Sauvegarder les filtres et réinitialiser la pagination
        $this->storeFiltres();
        $this->resetPage();
    }


    public function openCopieModal()
    {
        // Vérifier que le contexte est complet
        if (!$this->examen_id || !$this->ec_id || !$this->salle_id) {
            $this->message = 'Veuillez sélectionner une salle et une matière';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Vérifier qu'une matière spécifique est sélectionnée (pas "all")
        if (!$this->ec_id || $this->ec_id === 'all') {
            $this->message = 'Veuillez sélectionner une matière spécifique pour ajouter une note';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Vérifier le nombre de copies déjà saisies
        $copiesCount = Copie::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->whereNull('deleted_at')
            ->count();

        // Compter le nombre d'étudiants pour ce niveau et parcours
        $etudiantsCount = Etudiant::where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->count();

        // Vérifier que le nombre total d'étudiants est défini
        if ($etudiantsCount === 0) {
            $this->message = 'Aucun étudiant trouvé pour ce niveau et parcours. Veuillez vérifier vos filtres.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Empêcher la saisie si le maximum est atteint
        if ($copiesCount >= $etudiantsCount) {
            $this->message = "Limite atteinte : Vous avez déjà saisi {$copiesCount} copies pour {$etudiantsCount} étudiants.";
            $this->messageType = 'warning';
            toastr()->warning($this->message);
            return;
        }

        // Charger la liste des étudiants sans copie pour cette matière
        $this->etudiantsSansNote = $this->etudiantsSansCopies;

        // S'assurer que le code de salle est défini
        if (empty($this->selectedSalleCode)) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base;
                $this->currentSalleName = $salle->nom;
            }
        }

        // Définir le code de base
        $baseCode = $this->selectedSalleCode;

        // Commencer toujours à 1 et chercher le prochain code disponible
        $nextNumber = 1;
        $proposedCode = $baseCode . $nextNumber;

        // Vérifier uniquement les codes déjà utilisés dans les COPIES (pas les manchettes)
        // pour cette matière spécifique
        while (Copie::join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('copies.examen_id', $this->examen_id)
            ->where('copies.ec_id', $this->ec_id)
            ->where('codes_anonymat.code_complet', $proposedCode)
            ->whereNull('copies.deleted_at')
            ->exists()) {
            $nextNumber++;
            $proposedCode = $baseCode . $nextNumber;
        }

        // Préinitialiser le code d'anonymat avec le code unique
        $this->code_anonymat = $proposedCode;
        $this->note = '';
        $this->editingCopieId = null;

        // Afficher une alerte informative sur le nombre de notes restantes
        $remainingNotes = $etudiantsCount - $copiesCount;
        $this->message = "Vous avez saisi {$copiesCount} notes sur {$etudiantsCount} étudiants. Il reste {$remainingNotes} notes à saisir pour cette matière.";
        $this->messageType = 'info';
        toastr()->info($this->message);

        // Ouvrir la modale
        $this->showCopieModal = true;
    }


    public function saveCopie()
    {
        $this->validate();

        try {
            // Vérifier si l'examen existe
            $examen = Examen::find($this->examen_id);
            if (!$examen) {
                throw new \Exception("L'examen sélectionné n'existe pas.");
            }

            // Vérifier si l'EC est bien associé à l'examen
            $ecBelongsToExamen = DB::table('examen_ec')
                ->where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('salle_id', $this->salle_id)
                ->exists();

            if (!$ecBelongsToExamen) {
                throw new \Exception("La matière sélectionnée n'est pas associée à cet examen et cette salle.");
            }

            // Vérifier le nombre de copies déjà saisies pour cette matière
            $copiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->whereNull('deleted_at')
                ->count();

            // Compter le nombre d'étudiants pour ce niveau et parcours
            $etudiantsCount = Etudiant::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->count();

            // Vérifier la limite avant de sauvegarder
            if ($copiesCount >= $etudiantsCount) {
                $this->message = "Limite atteinte : Vous avez déjà saisi {$copiesCount} notes pour {$etudiantsCount} étudiants dans cette matière.";
                $this->messageType = 'warning';
                toastr()->warning($this->message);
                $this->showCopieModal = false;
                return;
            }

            // Rechercher ou créer le code d'anonymat avec l'EC
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

            // Vérifier si une copie supprimée existe avec ce code
            $existingDeletedCopie = Copie::withTrashed()
                ->where('examen_id', $this->examen_id)
                ->where('code_anonymat_id', $codeAnonymat->id)
                ->where('ec_id', $this->ec_id)
                ->whereNotNull('deleted_at')
                ->first();

            if ($existingDeletedCopie) {
                // Restaurer et mettre à jour la copie supprimée
                $existingDeletedCopie->restore();
                $existingDeletedCopie->update([
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                ]);
                $this->message = 'Note restaurée et mise à jour avec succès';
            } else if (isset($this->editingCopieId)) {
                // Mode édition
                $copie = Copie::find($this->editingCopieId);

                if (!$copie) {
                    throw new \Exception('La copie à modifier est introuvable.');
                }

                // Vérifier que la copie correspond à l'EC actuellement sélectionné
                if ($copie->ec_id != $this->ec_id) {
                    throw new \Exception('Cette copie appartient à une autre matière.');
                }

                // Mise à jour de la copie existante
                $copie->update([
                    'code_anonymat_id' => $codeAnonymat->id,
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                ]);

                $this->message = 'Copie modifiée avec succès';
            } else {
                // Vérifier si une copie existe déjà pour ce code et cette matière (non supprimée)
                $existingCopie = Copie::where('examen_id', $this->examen_id)
                    ->where('code_anonymat_id', $codeAnonymat->id)
                    ->where('ec_id', $this->ec_id)
                    ->first();

                if ($existingCopie) {
                    throw new \Exception("Ce code d'anonymat est déjà utilisé pour cette matière. Veuillez utiliser un autre code.");
                }

                // Mode création - Créer une nouvelle copie
                Copie::create([
                    'examen_id' => $this->examen_id,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'ec_id' => $this->ec_id,
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                ]);

                $this->message = 'Note enregistrée avec succès';
            }

            // Mettre à jour les compteurs globaux
            $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->count();

            $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('saisie_par', Auth::id())
                ->count();

            // Maintenir la modale ouverte si on est en mode création
            if (!isset($this->editingCopieId)) {
                // Réinitialiser seulement la note
                $this->note = '';

                // Définir le code de base à partir de la salle
                $baseCode = $this->selectedSalleCode;

                // Trouver tous les codes existants pour cet examen et EC dans les copies
                $existingCodes = Copie::join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
                    ->where('copies.examen_id', $this->examen_id)
                    ->where('copies.ec_id', $this->ec_id)
                    ->where('codes_anonymat.code_complet', 'like', $baseCode . '%')
                    ->whereNull('copies.deleted_at')
                    ->pluck('codes_anonymat.code_complet')
                    ->toArray();

                // Extraire les numéros des codes existants
                $numbers = [];
                foreach ($existingCodes as $code) {
                    if (preg_match('/^([A-Za-z]+)(\d+)$/', $code, $matches)) {
                        $numbers[] = (int)$matches[2];
                    }
                }

                // Trouver le dernier numéro utilisé
                $lastNumber = !empty($numbers) ? max($numbers) : 0;

                // Déterminer le prochain numéro en commençant par lastNumber + 1
                $prefix = $baseCode;
                $nextNumber = $lastNumber + 1;

                // Proposer le prochain code
                $proposedCode = $prefix . $nextNumber;

                // Vérifier si ce code existe déjà dans codes_anonymat mais n'est pas utilisé dans copies
                // On ne veut incrémenter que si le code est réellement utilisé dans copies
                while (CodeAnonymat::where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->where('code_complet', $proposedCode)
                    ->exists()) {
                    // Vérifier si ce code est utilisé dans une copie active
                    $codeUsedInCopie = Copie::join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
                        ->where('copies.examen_id', $this->examen_id)
                        ->where('copies.ec_id', $this->ec_id)
                        ->where('codes_anonymat.code_complet', $proposedCode)
                        ->whereNull('copies.deleted_at')
                        ->exists();

                    if ($codeUsedInCopie) {
                        // Si le code est utilisé dans une copie active, incrémenter
                        $nextNumber++;
                        $proposedCode = $prefix . $nextNumber;
                    } else {
                        // Si le code existe dans codes_anonymat mais n'est pas utilisé dans copies, on peut l'utiliser
                        break;
                    }
                }

                // Mettre à jour le code d'anonymat pour la prochaine saisie
                $this->code_anonymat = $proposedCode;

                // Garder la modale ouverte et mettre le focus sur le champ note
                $this->showCopieModal = true;
                $this->dispatch('focus-note-field');
            } else {
                // Si on était en mode édition, on ferme la modale
                $this->reset(['code_anonymat', 'note', 'editingCopieId']);
                $this->showCopieModal = false;
            }

            $this->messageType = 'success';

            // Notification
            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }
    public function editCopie($id)
    {
        $copie = Copie::with('codeAnonymat')->find($id);

        if (! $copie) {
            $this->message = 'Copie introuvable.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Vérifier que la copie correspond à l'EC actuellement sélectionné
        if ($copie->ec_id != $this->ec_id) {
            $this->message = 'Cette copie appartient à une autre matière. Veuillez sélectionner la bonne matière avant de modifier.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Remplir les champs du formulaire avec les données existantes
        $this->code_anonymat = $copie->codeAnonymat->code_complet;
        $this->note = $copie->note;

        // Stocker l'ID de la copie à éditer pour le traitement par saveCopie
        $this->editingCopieId = $id;

        // Ouvrir la modale
        $this->showCopieModal = true;
    }

    public function confirmDelete($id)
    {
        $this->copieToDelete = Copie::with('codeAnonymat')->find($id);

        // Vérifier que la copie correspond à l'EC actuellement sélectionné
        if ($this->copieToDelete && $this->copieToDelete->ec_id != $this->ec_id) {
            $this->message = 'Cette copie appartient à une autre matière. Veuillez sélectionner la bonne matière avant de supprimer.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->copieToDelete = null;
        $this->showDeleteModal = false;
    }

    public function confirmDeleteCopie()
    {
        if (!$this->copieToDelete) {
            $this->message = 'Copie introuvable.';
            $this->messageType = 'error';
            toastr()->error($this->message);
            $this->showDeleteModal = false;
            return;
        }

        $copieId = $this->copieToDelete->id;
        $this->showDeleteModal = false;
        $this->deleteCopie($copieId);
    }

    public function deleteCopie($id)
    {
        try {
            $copie = Copie::find($id);
            if (! $copie) {
                throw new \Exception('Copie introuvable.');
            }

            // Vérifier que la copie n'est pas associée à un résultat
            if ($copie->isAssociated()) {
                throw new \Exception('Cette copie est déjà associée à un résultat et ne peut pas être supprimée.');
            }

            // Récupérer l'identifiant EC avant suppression
            $ec_id_deleted = $copie->ec_id;

            $copie->delete();
            $this->message = 'Copie supprimée avec succès';
            $this->messageType = 'success';

            // Mettre à jour les compteurs globaux
            $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->count();

            $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('saisie_par', Auth::id())
                ->count();

            // Réinitialiser les variables de suivi
            $this->copieToDelete = null;

            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: '.$e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if ($this->niveau_id && $this->parcours_id && $this->salle_id && $this->examen_id) {
            $query = Copie::where('examen_id', $this->examen_id);

            // Si une EC spécifique est sélectionnée (et ce n'est pas "all")
            if ($this->ec_id && $this->ec_id !== 'all') {
                $query->where('ec_id', $this->ec_id);
            }

            // Filtre par type de note (réussie/échouée)
            if ($this->noteFilter === 'success') {
                $query->where('note', '>=', 10);
            } elseif ($this->noteFilter === 'failed') {
                $query->where('note', '<', 10);
            }

            // Filtrer par recherche sur le code d'anonymat ou la note
            if ($this->search) {
                $query->where(function($q) {
                    $q->whereHas('codeAnonymat', function ($sq) {
                        $sq->where('code_complet', 'like', '%'.$this->search.'%');
                    })
                    ->orWhere('note', 'like', '%'.$this->search.'%');
                });
            }

            // Ajout du tri sur les colonnes
            if ($this->sortField === 'code_anonymat') {
                $query->join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
                    ->orderBy('codes_anonymat.code_complet', $this->sortDirection)
                    ->select('copies.*'); // Important pour éviter les conflits de colonnes
            } else {
                $query->orderBy($this->sortField, $this->sortDirection);
            }

            $copies = $query->with(['codeAnonymat', 'ec', 'utilisateurSaisie'])
                ->paginate($this->perPage);
        } else {
            // Toujours retourner un objet de pagination (vide)
            $copies = Copie::where('id', 0)->paginate($this->perPage);
        }

        return view('livewire.copie.copies-index', [
            'copies' => $copies,
        ]);
    }
}
