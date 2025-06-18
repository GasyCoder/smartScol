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
use App\Models\SessionExam;
use App\Models\AnneeUniversitaire;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    public $ecSearch = '';
    public $ec_id = null;
    public $niveau_id;
    public $parcours_id;
    public $salle_id;
    public $examen_id;
    public $session_exam_id;
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
    public $currentSessionType = '';
    public $totalCopiesCount = 0;
    public $userCopiesCount = 0;
    public $totalEtudiantsCount = 0;
    public $etudiantsSansNote = [];

    // Gestion des sessions
    public $sessionActive = null;
    public $sessionActiveId = null;
    public $sessionType = null;
    public $sessionInfo = '';
    public $canAddCopies = true;

    // Messages de statut
    public $message = '';
    public $messageType = '';

    public $search = '';
    public $showDeleteModal = false;
    public $copieToDelete = null;
    public $autoOpenModal = true;

    // NOUVELLES PROPRIÉTÉS pour la double vérification
    public $enableDoubleVerification = false;
    public $code_anonymat_confirmation = '';
    public $note_confirmation = null;
    

    // Mise à jour des règles de validation pour inclure session_exam_id
    protected function rules()
    {
        $rules = [
            'code_anonymat' => 'required|string|max:20|regex:/^[A-Za-z]+\d+$/',
            'note' => 'required|numeric|min:0|max:20',
            'ec_id' => 'required|exists:ecs,id',
            'session_exam_id' => 'required|exists:session_exams,id',
        ];

        // Règles additionnelles si double vérification activée
        if ($this->enableDoubleVerification) {
            $rules['code_anonymat_confirmation'] = 'required|same:code_anonymat';
            $rules['note_confirmation'] = 'required|numeric|same:note';
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'code_anonymat.required' => 'Le code d\'anonymat est obligatoire.',
            'code_anonymat.regex' => 'Le code d\'anonymat doit contenir des lettres suivies de chiffres (ex: TA1).',
            'code_anonymat_confirmation.required' => 'Veuillez confirmer le code d\'anonymat.',
            'code_anonymat_confirmation.same' => 'Les codes d\'anonymat ne correspondent pas.',
            'note.required' => 'La note est obligatoire.',
            'note.numeric' => 'La note doit être un nombre.',
            'note.min' => 'La note ne peut pas être inférieure à 0.',
            'note.max' => 'La note ne peut pas être supérieure à 20.',
            'note_confirmation.required' => 'Veuillez confirmer la note.',
            'note_confirmation.numeric' => 'La confirmation de note doit être un nombre.',
            'note_confirmation.same' => 'Les notes ne correspondent pas.',
        ];
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

    // Pour stocker les filtres en session
    protected function storeFiltres()
    {
        session()->put('copies.filtres', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'salle_id' => $this->salle_id,
            'ec_id' => $this->ec_id,
            'examen_id' => $this->examen_id,
            'session_exam_id' => $this->session_exam_id,
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
                        if (isset($filtres['session_exam_id'])) {
                            $this->session_exam_id = $filtres['session_exam_id'];
                        }
                        $this->updatedEcId();
                    }
                }
            }
        }
    }

    /**
     * NOUVELLE MÉTHODE : Charger TOUS les ECs depuis tous les examens du niveau/parcours
     */
    private function loadAllEcsFromExamens()
    {
        if (!$this->niveau_id || !$this->salle_id) {
            $this->ecs = collect();
            return;
        }

        $sessionId = $this->getCurrentSessionId();
        $sessionType = $this->getCurrentSessionType();

        // Récupérer TOUS les examens pour ce niveau/parcours
        $examens = DB::table('examens')
            ->where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        \Log::info('Examens trouvés pour niveau/parcours (CopiesIndex)', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'examens_ids' => $examens
        ]);

        if (empty($examens)) {
            $this->ecs = collect();
            return;
        }

        // Récupérer TOUS les ECs associés à ces examens pour cette salle
        $ecsData = DB::table('ecs')
            ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
            ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
            ->join('ues', 'ecs.ue_id', '=', 'ues.id')
            ->whereIn('examen_ec.examen_id', $examens)
            ->where('examen_ec.salle_id', $this->salle_id)
            ->whereNull('ecs.deleted_at')
            ->whereNull('examens.deleted_at')
            ->select(
                'ecs.*',
                'ues.nom as ue_nom',
                'ues.abr as ue_abr',
                'examen_ec.examen_id',
                'examen_ec.date_specifique',
                'examen_ec.heure_specifique'
            )
            ->distinct()
            ->orderBy('ues.nom')
            ->orderBy('ecs.nom')
            ->get();

        \Log::info('ECs trouvés depuis tous les examens (CopiesIndex)', [
            'count' => $ecsData->count(),
            'salle_id' => $this->salle_id,
            'examens_checked' => $examens,
            'ecs_found' => $ecsData->pluck('nom')->toArray()
        ]);

        if ($ecsData->isEmpty()) {
            $this->ecs = collect();
            return;
        }

        // Grouper par EC (car un EC peut être dans plusieurs examens)
        $ecsGrouped = $ecsData->groupBy('id')->map(function($group) use ($sessionType) {
            $firstEc = $group->first();

            // Prendre le premier examen comme référence si pas encore défini
            if (!$this->examen_id) {
                $this->examen_id = $firstEc->examen_id;
            }

            return (object) [
                'id' => $firstEc->id,
                'nom' => $firstEc->nom,
                'abr' => $firstEc->abr,
                'coefficient' => $firstEc->coefficient,
                'ue_id' => $firstEc->ue_id,
                'ue_nom' => $firstEc->ue_nom,
                'ue_abr' => $firstEc->ue_abr,
                'enseignant' => $firstEc->enseignant,
                'examen_id' => $firstEc->examen_id,
                'date_specifique' => $firstEc->date_specifique,
                'heure_specifique' => $firstEc->heure_specifique,
                'date_formatted' => $firstEc->date_specifique ?
                    \Carbon\Carbon::parse($firstEc->date_specifique)->format('d/m/Y') : null,
                'heure_formatted' => $firstEc->heure_specifique ?
                    \Carbon\Carbon::parse($firstEc->heure_specifique)->format('H:i') : null,
                'has_copies' => false, // Sera calculé après
                'copies_count' => 0,  // Sera calculé après
                'user_copies_count' => 0,
                'pourcentage' => 0,
                'session_libelle' => ucfirst($sessionType)
            ];
        })->values();

        $this->ecs = $ecsGrouped;

        // Calculer les compteurs de copies pour tous les ECs
        $this->calculateCopiesCountsForAllEcs();

        \Log::info('ECs finaux chargés (CopiesIndex)', [
            'count' => $this->ecs->count(),
            'examen_id_used' => $this->examen_id,
            'ecs_names' => $this->ecs->pluck('nom')->toArray()
        ]);

        // Sélectionner automatiquement si une seule EC
        if ($this->ecs->count() == 1) {
            $this->ec_id = $this->ecs->first()->id;
            $this->updatedEcId();
        }
    }

    /**
     * Calculer les compteurs de copies pour tous les ECs chargés
     */
    private function calculateCopiesCountsForAllEcs()
    {
        if ($this->ecs->isEmpty()) {
            return;
        }

        $sessionId = $this->getCurrentSessionId();
        if (!$sessionId) {
            return;
        }

        $ecIds = $this->ecs->pluck('id')->toArray();

        // Compter les copies par EC pour la session active
        $copiesCounts = DB::table('copies')
            ->where('session_exam_id', $sessionId)
            ->whereIn('ec_id', $ecIds)
            ->whereNull('deleted_at')
            ->select('ec_id', DB::raw('count(*) as total'))
            ->groupBy('ec_id')
            ->pluck('total', 'ec_id')
            ->toArray();

        // Compter les copies de l'utilisateur
        $userCopiesCounts = DB::table('copies')
            ->where('session_exam_id', $sessionId)
            ->where('saisie_par', Auth::id())
            ->whereIn('ec_id', $ecIds)
            ->whereNull('deleted_at')
            ->select('ec_id', DB::raw('count(*) as total'))
            ->groupBy('ec_id')
            ->pluck('total', 'ec_id')
            ->toArray();

        // Mettre à jour les compteurs
        $this->ecs = $this->ecs->map(function($ec) use ($copiesCounts, $userCopiesCounts) {
            $copiesCount = $copiesCounts[$ec->id] ?? 0;
            $userCount = $userCopiesCounts[$ec->id] ?? 0;

            $ec->copies_count = $copiesCount;
            $ec->user_copies_count = $userCount;
            $ec->has_copies = $copiesCount > 0;
            $ec->pourcentage = $this->totalEtudiantsCount > 0 ?
                round(($copiesCount / $this->totalEtudiantsCount) * 100, 1) : 0;

            return $ec;
        });

        \Log::info('Compteurs mis à jour pour tous les ECs (CopiesIndex)', [
            'copies_counts' => $copiesCounts,
            'user_counts' => $userCopiesCounts
        ]);
    }

    /**
     * Met à jour les informations de session
     */
    private function updateSessionInfo()
    {
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive) {
                throw new \Exception('Aucune année universitaire active trouvée.');
            }

            $sessionActive = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                ->where('is_active', true)
                ->where('is_current', true)
                ->first();

            if (!$sessionActive) {
                throw new \Exception('Aucune session active et courante trouvée.');
            }

            $this->sessionActive = $sessionActive;
            $this->sessionActiveId = $sessionActive->id;
            $this->session_exam_id = $sessionActive->id;
            $this->sessionType = strtolower($sessionActive->type);
            $this->currentSessionType = $sessionActive->type;
            $this->canAddCopies = true;
            $this->sessionInfo = "Session {$sessionActive->type} active - Année {$anneeActive->libelle}";

            \Log::info('Session active mise à jour (CopiesIndex)', [
                'session_id' => $this->session_exam_id,
                'type' => $this->sessionType,
                'annee_universitaire' => $anneeActive->libelle,
            ]);
        } catch (\Exception $e) {
            $this->sessionInfo = 'Erreur : ' . $e->getMessage();
            $this->sessionActive = null;
            $this->sessionActiveId = null;
            $this->session_exam_id = null;
            $this->currentSessionType = '';
            $this->canAddCopies = false;
            \Log::error('Erreur lors de la mise à jour de la session (CopiesIndex)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Récupère l'ID de la session actuelle
     */
    private function getCurrentSessionId()
    {
        if (!$this->sessionActiveId) {
            $this->updateSessionInfo();
        }
        return $this->sessionActiveId;
    }

    /**
     * Récupère le type de session actuel
     */
    private function getCurrentSessionType()
    {
        return $this->sessionActive ? strtolower($this->sessionActive->type) : 'normale';
    }

    // CORRIGÉ : Méthode pour charger les étudiants sans copie pour la session sélectionnée
    public function chargerEtatEtudiants()
    {
        if (!$this->examen_id || !$this->ec_id || $this->ec_id === 'all' || !$this->session_exam_id) {
            $this->etudiantsSansCopies = collect();
            return;
        }

        // NOUVELLE LOGIQUE : Récupérer la session actuelle
        $session = SessionExam::find($this->session_exam_id);
        if (!$session) {
            $this->etudiantsSansCopies = collect();
            return;
        }

        // LOGIQUE DIFFÉRENTE SELON LE TYPE DE SESSION
        if ($session->type === 'Normale') {
            // Session normale : TOUS les étudiants du niveau/parcours
            $etudiants = Etudiant::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->get();

        } else {
            // Session rattrapage : SEULS les étudiants éligibles
            $sessionNormale = SessionExam::where('annee_universitaire_id', $session->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                $this->etudiantsSansCopies = collect();
                return;
            }

            // Utiliser la nouvelle méthode du modèle Etudiant
            $etudiants = Etudiant::eligiblesRattrapage(
                $this->niveau_id,
                $this->parcours_id,
                $sessionNormale->id
            )->get();
        }

        // Récupérer les IDs des étudiants qui ont déjà une copie pour cette EC dans cette session
        $etudiantsAvecCopiesIds = Copie::where('ec_id', $this->ec_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->whereHas('codeAnonymat.manchette', function($query) {
                $query->where('examen_id', $this->examen_id)
                    ->where('session_exam_id', $this->session_exam_id);
            })
            ->with('codeAnonymat.manchette')
            ->get()
            ->pluck('codeAnonymat.manchette.etudiant_id')
            ->filter()
            ->unique()
            ->toArray();

        // Étudiants SANS copie pour cette EC dans cette session
        $this->etudiantsSansCopies = $etudiants->whereNotIn('id', $etudiantsAvecCopiesIds)->values();

        // Log pour debug
        \Log::info('État étudiants chargé avec nouvelle logique (Copies)', [
            'session_type' => $session->type,
            'total_etudiants_disponibles' => $etudiants->count(),
            'avec_copies' => count($etudiantsAvecCopiesIds),
            'sans_copies' => $this->etudiantsSansCopies->count(),
            'ec_id' => $this->ec_id,
            'session_id' => $this->session_exam_id
        ]);
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
            $this->session_exam_id = null;
        } elseif ($filterName === 'parcours_id') {
            $this->salle_id = null;
            $this->ec_id = null;
            $this->examen_id = null;
            $this->session_exam_id = null;
        } elseif ($filterName === 'salle_id') {
            $this->ec_id = null;
            $this->examen_id = null;
            $this->session_exam_id = null;
        }

        // Réinitialiser les informations associées
        if (in_array($filterName, ['niveau_id', 'parcours_id', 'salle_id', 'ec_id'])) {
            $this->selectedSalleCode = '';
            $this->currentEcName = '';
            $this->currentSalleName = '';
            $this->currentEcDate = '';
            $this->currentEcHeure = '';
            $this->currentSessionType = '';
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
            'niveau_id', 'parcours_id', 'salle_id', 'ec_id', 'examen_id', 'session_exam_id',
            'selectedSalleCode', 'currentEcName', 'currentSalleName',
            'currentEcDate', 'currentEcHeure', 'currentSessionType'
        ]);
        session()->forget('copies.filtres');

        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();

        $this->resetPage();
    }

    public function mount()
    {
        $this->niveaux = Niveau::where('is_active', true)->orderBy('id', 'asc')->get();
        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();

        try {
            // Tenter de récupérer la session active
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive) {
                throw new \Exception('Aucune année universitaire active trouvée.');
            }

            $sessionActive = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                ->where('is_active', true)
                ->where('is_current', true)
                ->first();

            if (!$sessionActive) {
                throw new \Exception('Aucune session active et courante trouvée.');
            }

            $this->sessionActive = $sessionActive;
            $this->sessionActiveId = $sessionActive->id;
            $this->session_exam_id = $sessionActive->id;
            $this->currentSessionType = $sessionActive->type;
            $this->sessionType = strtolower($sessionActive->type);
            $this->canAddCopies = true;
            $this->sessionInfo = "Session {$sessionActive->type} active - Année {$anneeActive->libelle}";

            \Log::info('Session active initialisée dans CopiesIndex', [
                'session_id' => $this->session_exam_id,
                'type' => $this->currentSessionType,
                'is_active' => $sessionActive->is_active,
                'is_current' => $sessionActive->is_current,
            ]);
        } catch (\Exception $e) {
            $this->sessionInfo = 'Erreur : ' . $e->getMessage();
            $this->sessionActive = null;
            $this->sessionActiveId = null;
            $this->session_exam_id = null;
            $this->currentSessionType = '';
            $this->canAddCopies = false;
            \Log::error('Erreur lors de l\'initialisation de la session dans CopiesIndex', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            toastr()->error($this->sessionInfo);
        }

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

        $this->updateSessionInfo();
        $this->storeFiltres();
        $this->resetPage();
    }

    public function exportNotes()
    {
        toastr()->info('Fonctionnalité d\'export en cours de développement');
    }

    public function printNotes()
    {
        toastr()->info('Fonctionnalité d\'impression en cours de développement');
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
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base ?? '';
                $this->currentSalleName = $salle->nom ?? '';
            }

            // APPROCHE SIMPLE : Charger les ECs directement sans doublons
            $ecsUniques = DB::table('ecs')
                ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
                ->join('ues', 'ecs.ue_id', '=', 'ues.id')
                ->where('examens.niveau_id', $this->niveau_id)
                ->where('examens.parcours_id', $this->parcours_id)
                ->where('examen_ec.salle_id', $this->salle_id)
                ->whereNull('ecs.deleted_at')
                ->whereNull('examens.deleted_at')
                ->select(
                    'ecs.id',
                    'ecs.nom',
                    'ecs.abr',
                    'ecs.coefficient',
                    'ecs.ue_id',
                    'ecs.enseignant',
                    'ues.nom as ue_nom',
                    'ues.abr as ue_abr',
                    // IMPORTANT : Prendre le premier examen trouvé
                    DB::raw('MIN(examen_ec.examen_id) as examen_id'),
                    DB::raw('MIN(examen_ec.date_specifique) as date_specifique'),
                    DB::raw('MIN(examen_ec.heure_specifique) as heure_specifique')
                )
                ->groupBy(
                    'ecs.id',
                    'ecs.nom',
                    'ecs.abr',
                    'ecs.coefficient',
                    'ecs.ue_id',
                    'ecs.enseignant',
                    'ues.nom',
                    'ues.abr'
                )
                ->orderBy('ues.nom')
                ->orderBy('ecs.nom')
                ->get();

            if ($ecsUniques->isNotEmpty()) {
                // Définir l'examen de référence (le premier trouvé)
                $this->examen_id = $ecsUniques->first()->examen_id;

                // Formater les ECs
                $this->ecs = $ecsUniques->map(function($ec) {
                    return (object) [
                        'id' => $ec->id,
                        'nom' => $ec->nom,
                        'abr' => $ec->abr,
                        'coefficient' => $ec->coefficient,
                        'ue_id' => $ec->ue_id,
                        'ue_nom' => $ec->ue_nom,
                        'ue_abr' => $ec->ue_abr,
                        'enseignant' => $ec->enseignant,
                        'examen_id' => $this->examen_id,
                        'original_examen_id' => $ec->examen_id,
                        'date_specifique' => $ec->date_specifique,
                        'heure_specifique' => $ec->heure_specifique,
                        'date_formatted' => $ec->date_specifique ?
                            \Carbon\Carbon::parse($ec->date_specifique)->format('d/m/Y') : null,
                        'heure_formatted' => $ec->heure_specifique ?
                            \Carbon\Carbon::parse($ec->heure_specifique)->format('H:i') : null,
                        'has_copies' => false,
                        'copies_count' => 0,
                        'user_copies_count' => 0,
                        'pourcentage' => 0,
                        'session_libelle' => ucfirst($this->getCurrentSessionType())
                    ];
                });

                // Calculer les compteurs
                $this->calculateCopiesCountsForAllEcs();

                \Log::info('ECs chargés sans doublons (méthode simple)', [
                    'count' => $this->ecs->count(),
                    'examen_id' => $this->examen_id,
                    'ecs_names' => $this->ecs->pluck('nom')->toArray()
                ]);
            }

            $this->storeFiltres();
            $this->resetPage();
        }
    }


    /**
     * NOUVELLE MÉTHODE : S'assurer que les codes d'anonymat existent pour l'examen de référence
     */
    private function ensureCodesAnonymatForReferenceExam()
    {
        if (!$this->examen_id || $this->ecs->isEmpty()) {
            return;
        }

        foreach ($this->ecs as $ec) {
            // Vérifier si des codes d'anonymat existent déjà pour cette EC dans l'examen de référence
            $existingCodesCount = CodeAnonymat::where('examen_id', $this->examen_id)
                ->where('ec_id', $ec->id)
                ->count();

            if ($existingCodesCount == 0) {
                // Si aucun code n'existe, en créer quelques-uns de base
                $baseCode = $this->selectedSalleCode;
                for ($i = 1; $i <= 4; $i++) {
                    CodeAnonymat::firstOrCreate([
                        'examen_id' => $this->examen_id,
                        'ec_id' => $ec->id,
                        'code_complet' => $baseCode . $i,
                    ], [
                        'sequence' => $i,
                    ]);
                }

                \Log::info('Codes d\'anonymat créés pour EC', [
                    'ec_id' => $ec->id,
                    'examen_id' => $this->examen_id,
                    'codes_crees' => 4
                ]);
            }
        }
    }

    public function updatedEcId()
    {
        // Réinitialiser les valeurs
        $this->currentEcName = '';
        $this->currentEcDate = '';
        $this->currentEcHeure = '';

        // Charger l'état des étudiants
        $this->chargerEtatEtudiants();

        // NOUVELLE LOGIQUE : Calculer le nombre d'étudiants selon la session
        $session = SessionExam::find($this->session_exam_id);
        $baseEtudiantsCount = 0;

        if ($session) {
            if ($session->type === 'Normale') {
                // Session normale : TOUS les étudiants du niveau/parcours
                $baseEtudiantsCount = Etudiant::where('niveau_id', $this->niveau_id)
                    ->where('parcours_id', $this->parcours_id)
                    ->count();
            } else {
                // Session rattrapage : SEULS les étudiants éligibles
                $sessionNormale = SessionExam::where('annee_universitaire_id', $session->annee_universitaire_id)
                    ->where('type', 'Normale')
                    ->first();

                if ($sessionNormale) {
                    $baseEtudiantsCount = Etudiant::eligiblesRattrapage(
                        $this->niveau_id,
                        $this->parcours_id,
                        $sessionNormale->id
                    )->count();
                }
            }
        }

        // Cas spécial: "Toutes les matières"
        if ($this->ec_id === 'all') {
            if ($this->examen_id && $this->salle_id && $this->session_exam_id) {
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

                // Calculer le nombre total de copies pour toutes les matières DANS LA SESSION ACTIVE
                $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->count();

                // Copies saisies par l'utilisateur actuel dans la session active
                $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->where('saisie_par', Auth::id())
                    ->count();

                // Calculer le nombre total d'étudiants × matières
                $nombreMatieres = count($ecIds);
                if ($nombreMatieres > 0) {
                    $this->totalEtudiantsCount = $baseEtudiantsCount * $nombreMatieres;
                } else {
                    $this->totalEtudiantsCount = $baseEtudiantsCount;
                }
            }
        }
        // Cas normal: une matière spécifique
        else if ($this->ec_id && $this->salle_id && $this->session_exam_id) {
            // Utiliser le nombre d'étudiants calculé selon la session
            $this->totalEtudiantsCount = $baseEtudiantsCount;

            // Essayer de trouver l'EC dans la collection chargée
            $ec = $this->ecs->firstWhere('id', $this->ec_id);
            if ($ec) {
                $this->currentEcName = $ec->nom;
                $this->currentEcDate = $ec->date_formatted ?? '';
                $this->currentEcHeure = $ec->heure_formatted ?? '';
                $this->examen_id = $ec->examen_id;
            } else {
                // Fallback: rechercher dans la base de données
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
                }
            }

            // Calculer le nombre de copies pour cette EC DANS LA SESSION ACTIVE
            $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('session_exam_id', $this->session_exam_id)
                ->count();

            // Copies saisies par l'utilisateur actuel dans la session active
            $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('session_exam_id', $this->session_exam_id)
                ->where('saisie_par', Auth::id())
                ->count();
        }

        // Log pour debug
        \Log::info('updatedEcId - Compteurs mis à jour', [
            'session_type' => $session ? $session->type : 'inconnue',
            'base_etudiants_count' => $baseEtudiantsCount,
            'total_etudiants_count' => $this->totalEtudiantsCount,
            'total_copies_count' => $this->totalCopiesCount,
            'user_copies_count' => $this->userCopiesCount,
            'ec_id' => $this->ec_id
        ]);

        // Effacer tout message précédent lors du changement d'EC
        $this->message = '';

        // Sauvegarder les filtres et réinitialiser la pagination
        $this->storeFiltres();
        $this->resetPage();
        $this->checkAndAutoOpenModal();
    }


    /**
     * ✅ NOUVELLE MÉTHODE À AJOUTER DANS VOTRE CLASSE
     */
    private function checkAndAutoOpenModal()
    {
        // Vérifications préalables
        if (!$this->autoOpenModal || !$this->canAddCopies || !$this->ec_id || $this->ec_id === 'all') {
            return;
        }

        // Ne pas ouvrir si on est en mode édition
        if (isset($this->editingCopieId)) {
            return;
        }

        // Ne pas ouvrir si la modal est déjà ouverte
        if ($this->showCopieModal) {
            return;
        }

        // ✅ CONDITION STRICTE : Vérifier s'il n'y a AUCUNE copie pour cette matière dans cette session
        $totalCopiesCount = Copie::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->whereNull('deleted_at')
            ->count();

        // ✅ CONDITION SUPPLÉMENTAIRE : Vérifier qu'il y a des étudiants disponibles
        $etudiantsDisponibles = count($this->etudiantsSansCopies ?? []);

        // ✅ AUTO-OUVERTURE SEULEMENT SI :
        // 1. AUCUNE copie n'existe (0 copies)
        // 2. ET il y a des étudiants sans copie
        if ($totalCopiesCount === 0 && $etudiantsDisponibles > 0) {
            
            // ✅ VÉRIFICATION SUPPLÉMENTAIRE : S'assurer qu'on peut ouvrir la modal
            $session = SessionExam::find($this->session_exam_id);
            if (!$session) {
                \Log::warning('Auto-ouverture annulée : session introuvable', [
                    'session_id' => $this->session_exam_id
                ]);
                return;
            }

            // ✅ NOUVELLE VÉRIFICATION : Compter les étudiants éligibles selon le type de session
            if ($session->type === 'Normale') {
                $etudiantsEligibles = Etudiant::where('niveau_id', $this->niveau_id)
                    ->where('parcours_id', $this->parcours_id)
                    ->count();
            } else {
                $sessionNormale = SessionExam::where('annee_universitaire_id', $session->annee_universitaire_id)
                    ->where('type', 'Normale')
                    ->first();
                
                if (!$sessionNormale) {
                    \Log::warning('Auto-ouverture annulée : session normale introuvable pour rattrapage');
                    return;
                }

                $etudiantsEligibles = Etudiant::eligiblesRattrapage(
                    $this->niveau_id,
                    $this->parcours_id,
                    $sessionNormale->id
                )->count();
            }

            // ✅ DERNIÈRE VÉRIFICATION : S'assurer qu'il y a des étudiants éligibles
            if ($etudiantsEligibles === 0) {
                \Log::warning('Auto-ouverture annulée : aucun étudiant éligible', [
                    'session_type' => $session->type,
                    'niveau_id' => $this->niveau_id,
                    'parcours_id' => $this->parcours_id
                ]);
                return;
            }

            // ✅ TOUT EST OK : Ouvrir la modal directement sans passer par openCopieModal()
            $this->prepareDirectModalOpening();
            
            // Message informatif spécifique à l'auto-ouverture
            $sessionType = $session->type;
            toastr()->info("✨ Première saisie pour cette matière en session {$sessionType}. Modal ouverte automatiquement pour {$etudiantsEligibles} étudiant(s) éligible(s).");
            
            // Log pour debug
            \Log::info('Modal auto-ouverte pour première saisie', [
                'ec_id' => $this->ec_id,
                'ec_name' => $this->currentEcName,
                'etudiants_eligibles' => $etudiantsEligibles,
                'etudiants_disponibles' => $etudiantsDisponibles,
                'session_id' => $this->session_exam_id,
                'session_type' => $sessionType
            ]);
        } else {
            // ✅ LOG pour debug quand l'auto-ouverture ne se fait pas
            \Log::debug('Auto-ouverture non déclenchée', [
                'total_copies' => $totalCopiesCount,
                'etudiants_disponibles' => $etudiantsDisponibles,
                'ec_id' => $this->ec_id,
                'raison' => $totalCopiesCount > 0 ? 'Des copies existent déjà' : 'Aucun étudiant disponible'
            ]);
        }
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Préparer l'ouverture directe de la modal (sans les vérifications de openCopieModal)
     */
    private function prepareDirectModalOpening()
    {
        // Charger la liste des étudiants sans copie
        $this->etudiantsSansNote = $this->etudiantsSansCopies;

        // S'assurer que le code de salle est défini
        if (empty($this->selectedSalleCode)) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base;
                $this->currentSalleName = $salle->nom;
            }
        }

        // Générer le premier code d'anonymat pour cette session
        $this->generateNextCodeAnonymatForSession();

        // Réinitialiser les champs
        $this->note = null;
        $this->note_confirmation = null;
        $this->code_anonymat_confirmation = '';
        $this->editingCopieId = null;

        // Ouvrir la modale directement
        $this->showCopieModal = true;
    }


    /**
     * ✅ MÉTHODE UTILITAIRE : Vérifier si l'auto-ouverture est possible
     */
    public function canAutoOpen()
    {
        if (!$this->autoOpenModal || !$this->ec_id || $this->ec_id === 'all') {
            return false;
        }

        $totalCopiesCount = Copie::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->whereNull('deleted_at')
            ->count();

        return $totalCopiesCount === 0 && count($this->etudiantsSansCopies ?? []) > 0;
    }

    public function openCopieModal()
    {
        // Vérifications préalables (code existant)...
        if (!$this->examen_id || !$this->ec_id || !$this->salle_id) {
            $this->message = 'Veuillez sélectionner une salle et une matière';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        if (!$this->ec_id || $this->ec_id === 'all') {
            $this->message = 'Veuillez sélectionner une matière spécifique pour ajouter une note';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Vérifications de session (code existant)...
        $session = SessionExam::find($this->session_exam_id);
        if (!$session) {
            $this->message = 'Session introuvable';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Logique de comptage des étudiants (code existant)...
        if ($session->type === 'Normale') {
            $etudiantsCount = Etudiant::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->count();
        } else {
            $sessionNormale = SessionExam::where('annee_universitaire_id', $session->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                $this->message = 'Aucune session normale trouvée pour calculer les étudiants éligibles au rattrapage';
                $this->messageType = 'error';
                toastr()->error($this->message);
                return;
            }

            $etudiantsCount = Etudiant::eligiblesRattrapage(
                $this->niveau_id,
                $this->parcours_id,
                $sessionNormale->id
            )->count();
        }

        if ($etudiantsCount === 0) {
            $sessionType = $session->type === 'Normale' ? 'normale' : 'rattrapage';
            $this->message = "Aucun étudiant éligible trouvé pour la session {$sessionType}. Veuillez vérifier vos filtres.";
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Vérifier la limite pour cette session
        $copiesCount = Copie::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->whereNull('deleted_at')
            ->count();

        if ($copiesCount >= $etudiantsCount) {
            $sessionType = $session->type === 'Normale' ? 'normale' : 'rattrapage';
            $this->message = "Limite atteinte pour la session {$sessionType} : Vous avez déjà saisi {$copiesCount} copies pour {$etudiantsCount} étudiants éligibles.";
            $this->messageType = 'warning';
            toastr()->warning($this->message);
            return;
        }

        // Charger la liste des étudiants sans copie
        $this->etudiantsSansNote = $this->etudiantsSansCopies;

        // S'assurer que le code de salle est défini
        if (empty($this->selectedSalleCode)) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base;
                $this->currentSalleName = $salle->nom;
            }
        }

        // Générer le code d'anonymat pour cette session (CORRIGÉ)
        $this->generateNextCodeAnonymatForSession();

        // Réinitialiser les champs
        $this->note = null;
        $this->note_confirmation = null;
        $this->code_anonymat_confirmation = '';
        $this->editingCopieId = null;

        // Message informatif adapté au type de session
        $remainingNotes = $etudiantsCount - $copiesCount;
        $sessionType = $session->type === 'Normale' ? 'normale' : 'rattrapage';
        $doubleVerifStatus = $this->enableDoubleVerification ? ' (Double vérification activée)' : '';
        
        $this->message = "Session {$sessionType} : {$copiesCount} notes saisies sur {$etudiantsCount} étudiants éligibles. Il reste {$remainingNotes} notes à saisir.{$doubleVerifStatus}";
        $this->messageType = 'info';
        toastr()->info($this->message);

        // Ouvrir la modale
        $this->showCopieModal = true;
    }

    // méthode savecopie
    public function saveCopie(): void
    {
        $this->validate();

        try {
            // Vérifier que session_exam_id est défini, actif et courant
            if (!$this->session_exam_id) {
                throw new \Exception('Aucune session d\'examen sélectionnée.');
            }

            $sessionExam = SessionExam::where('id', $this->session_exam_id)
                ->where('is_active', true)
                ->where('is_current', true)
                ->first();

            if (!$sessionExam) {
                \Log::error('Tentative de sauvegarde avec une session non active ou non courante', [
                    'session_id' => $this->session_exam_id,
                ]);
                throw new \Exception('La session d\'examen sélectionnée n\'est pas active ou courante.');
            }

            // S'assurer que l'examen_id est cohérent avec les manchettes
            if (!$this->examen_id) {
                throw new \Exception('Aucun examen sélectionné.');
            }

            // Vérifier l'examen
            $examen = Examen::find($this->examen_id);
            if (!$examen) {
                throw new \Exception("L'examen sélectionné n'existe pas.");
            }

            // NOUVELLE VÉRIFICATION : Si l'EC vient d'un autre examen, gérer la correspondance
            $ecInfo = $this->ecs->firstWhere('id', $this->ec_id);
            if ($ecInfo && isset($ecInfo->original_examen_id) && $ecInfo->original_examen_id != $this->examen_id) {
                \Log::info('EC provient d\'un autre examen, vérification de correspondance', [
                    'ec_id' => $this->ec_id,
                    'examen_reference' => $this->examen_id,
                    'examen_original' => $ecInfo->original_examen_id
                ]);

                // Vérifier que l'association examen_ec existe dans la table pour l'examen de référence
                $associationExists = DB::table('examen_ec')
                    ->where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->where('salle_id', $this->salle_id)
                    ->exists();

                if (!$associationExists) {
                    // Créer l'association manquante
                    DB::table('examen_ec')->insert([
                        'examen_id' => $this->examen_id,
                        'ec_id' => $this->ec_id,
                        'salle_id' => $this->salle_id,
                        'date_specifique' => $ecInfo->date_specifique,
                        'heure_specifique' => $ecInfo->heure_specifique,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    \Log::info('Association examen_ec créée', [
                        'examen_id' => $this->examen_id,
                        'ec_id' => $this->ec_id,
                        'salle_id' => $this->salle_id
                    ]);
                }
            }

            // VÉRIFICATION CRITIQUE : L'EC appartient-elle bien à cet examen dans cette salle ?
            $ecBelongsToExamen = DB::table('examen_ec')
                ->where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('salle_id', $this->salle_id)
                ->exists();

            if (!$ecBelongsToExamen) {
                throw new \Exception("Incohérence détectée : La matière (EC {$this->ec_id}) n'est pas associée à l'examen {$this->examen_id} dans la salle {$this->salle_id}.");
            }

            // ✅ VÉRIFICATION DE LIMITE CORRIGÉE : Compter les copies existantes
            $copiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('session_exam_id', $this->session_exam_id)
                ->whereNull('deleted_at')
                ->count();

            // ✅ CALCUL DU NOMBRE D'ÉTUDIANTS SELON LA LOGIQUE DE SESSION
            if ($sessionExam->type === 'Normale') {
                // Session normale : TOUS les étudiants du niveau/parcours
                $etudiantsCount = Etudiant::where('niveau_id', $this->niveau_id)
                    ->where('parcours_id', $this->parcours_id)
                    ->count();
            } else {
                // Session rattrapage : SEULS les étudiants éligibles
                $sessionNormale = SessionExam::where('annee_universitaire_id', $sessionExam->annee_universitaire_id)
                    ->where('type', 'Normale')
                    ->first();

                if (!$sessionNormale) {
                    throw new \Exception('Aucune session normale trouvée pour calculer les étudiants éligibles au rattrapage');
                }

                $etudiantsCount = Etudiant::eligiblesRattrapage(
                    $this->niveau_id,
                    $this->parcours_id,
                    $sessionNormale->id
                )->count();
            }

            // ✅ LOG POUR DEBUG
            \Log::info('Calcul du nombre d\'étudiants pour saveCopie', [
                'session_type' => $sessionExam->type,
                'etudiants_count' => $etudiantsCount,
                'copies_count' => $copiesCount,
                'niveau_id' => $this->niveau_id,
                'parcours_id' => $this->parcours_id,
                'is_editing' => isset($this->editingCopieId) ? 'oui' : 'non'
            ]);

            // ✅ VÉRIFICATION DE LIMITE CORRIGÉE : En mode AJOUT seulement
            if (!isset($this->editingCopieId)) {
                // ✅ LOGIQUE CORRIGÉE : Prévoir le nombre de copies après ajout
                $copiesApresAjout = $copiesCount + 1;
                
                if ($copiesApresAjout > $etudiantsCount) {
                    $sessionType = $sessionExam->type === 'Normale' ? 'normale' : 'rattrapage';
                    $this->message = "❌ Impossible d'ajouter cette note ! Limite atteinte : Vous avez déjà {$copiesCount} copies pour {$etudiantsCount} étudiants éligibles en session {$sessionType}.";
                    $this->messageType = 'error';
                    toastr()->error($this->message);
                    
                    \Log::warning('Tentative de dépassement de limite', [
                        'copies_actuelles' => $copiesCount,
                        'copies_apres_ajout' => $copiesApresAjout,
                        'etudiants_eligibles' => $etudiantsCount,
                        'session_type' => $sessionExam->type,
                        'ec_id' => $this->ec_id,
                        'user_id' => Auth::id()
                    ]);
                    
                    // ✅ NE PAS FERMER LA MODAL pour que l'utilisateur voie le message
                    return;
                }
            }

            // Créer ou récupérer le code d'anonymat AVEC LE BON EXAMEN_ID (cohérent avec manchettes)
            $codeAnonymat = CodeAnonymat::firstOrCreate(
                [
                    'examen_id' => $this->examen_id, // COHÉRENT avec les manchettes
                    'ec_id' => $this->ec_id,
                    'code_complet' => $this->code_anonymat,
                ],
                [
                    'sequence' => null,
                ]
            );

            \Log::info('Code d\'anonymat traité', [
                'code_id' => $codeAnonymat->id,
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
                'code_complet' => $this->code_anonymat,
                'was_created' => $codeAnonymat->wasRecentlyCreated
            ]);

            // Vérifier si une copie supprimée existe pour cette session
            $existingDeletedCopie = Copie::withTrashed()
                ->where('examen_id', $this->examen_id)
                ->where('code_anonymat_id', $codeAnonymat->id)
                ->where('ec_id', $this->ec_id)
                ->where('session_exam_id', $this->session_exam_id)
                ->whereNotNull('deleted_at')
                ->first();

            if ($existingDeletedCopie) {
                // Restaurer une copie supprimée
                $existingDeletedCopie->restore();
                $existingDeletedCopie->update([
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                    'session_exam_id' => $this->session_exam_id,
                    'updated_at' => now(),
                ]);
                $this->message = 'Note restaurée et mise à jour avec succès';
                \Log::info('Copie restaurée', [
                    'copie_id' => $existingDeletedCopie->id,
                    'examen_id' => $this->examen_id
                ]);

            } else if (isset($this->editingCopieId)) {
                // Modifier une copie existante
                $copie = Copie::find($this->editingCopieId);
                if (!$copie) {
                    throw new \Exception('La copie à modifier est introuvable.');
                }

                // Vérifier que la copie appartient bien au bon contexte
                if ($copie->ec_id != $this->ec_id || $copie->session_exam_id != $this->session_exam_id || $copie->examen_id != $this->examen_id) {
                    throw new \Exception('Cette copie appartient à une autre matière, session ou examen.');
                }

                $copie->update([
                    'code_anonymat_id' => $codeAnonymat->id,
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                    'session_exam_id' => $this->session_exam_id,
                    'updated_at' => now(),
                ]);
                $this->message = 'Copie modifiée avec succès';
                \Log::info('Copie modifiée', [
                    'copie_id' => $copie->id,
                    'examen_id' => $this->examen_id
                ]);

            } else {
                // ✅ DOUBLE VÉRIFICATION DE SÉCURITÉ avant création
                // Recompter les copies en temps réel (au cas où une autre personne aurait ajouté une copie)
                $copiesCountRealTime = Copie::where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->whereNull('deleted_at')
                    ->count();
                
                if ($copiesCountRealTime >= $etudiantsCount) {
                    $sessionType = $sessionExam->type === 'Normale' ? 'normale' : 'rattrapage';
                    throw new \Exception("❌ Limite atteinte en temps réel ! {$copiesCountRealTime} copies pour {$etudiantsCount} étudiants en session {$sessionType}. Une autre personne a peut-être ajouté une copie.");
                }

                // Vérifier qu'une copie n'existe pas déjà pour cette session
                $existingCopie = Copie::where('examen_id', $this->examen_id)
                    ->where('code_anonymat_id', $codeAnonymat->id)
                    ->where('ec_id', $this->ec_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->whereNull('deleted_at') // ✅ AJOUT DE CETTE LIGNE
                    ->first();

                if ($existingCopie) {
                    throw new \Exception("Ce code d'anonymat est déjà utilisé pour cette matière dans cette session.");
                }

                // ✅ VÉRIFICATION FINALE : S'assurer qu'on ne dépasse pas la limite
                $totalCopiesAfterCreation = $copiesCountRealTime + 1;
                if ($totalCopiesAfterCreation > $etudiantsCount) {
                    throw new \Exception("❌ Création impossible : {$totalCopiesAfterCreation} copies dépasserait la limite de {$etudiantsCount} étudiants.");
                }

                // Créer une nouvelle copie
                $nouvelleCopie = Copie::create([
                    'examen_id' => $this->examen_id, // COHÉRENT avec manchettes et codes
                    'session_exam_id' => $this->session_exam_id,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'ec_id' => $this->ec_id,
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);

                $this->message = 'Note enregistrée avec succès';
                \Log::info('Nouvelle copie créée avec vérifications de sécurité', [
                    'copie_id' => $nouvelleCopie->id,
                    'examen_id' => $this->examen_id,
                    'ec_id' => $this->ec_id,
                    'session_id' => $this->session_exam_id,
                    'copies_avant' => $copiesCountRealTime,
                    'copies_apres' => $copiesCountRealTime + 1,
                    'limite_etudiants' => $etudiantsCount
                ]);
            }

            // Mettre à jour les compteurs pour cette session
            $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('session_exam_id', $this->session_exam_id)
                ->count();

            $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('session_exam_id', $this->session_exam_id)
                ->where('saisie_par', Auth::id())
                ->count();

            // Gestion de la modale après sauvegarde
            if (!isset($this->editingCopieId)) {
                // Mode ajout : préparer pour la prochaine saisie
                $this->note = '';

                // ✅ VÉRIFIER S'IL RESTE DE LA PLACE POUR UNE AUTRE COPIE
                $copiesCountAfterSave = Copie::where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->whereNull('deleted_at')
                    ->count();

                if ($copiesCountAfterSave >= $etudiantsCount) {
                    // ✅ LIMITE ATTEINTE : Fermer la modal et informer
                    $sessionType = $sessionExam->type === 'Normale' ? 'normale' : 'rattrapage';
                    $this->message = "🎯 Saisie terminée ! Toutes les notes ont été saisies pour cette matière en session {$sessionType} ({$copiesCountAfterSave}/{$etudiantsCount}).";
                    $this->messageType = 'success';
                    $this->showCopieModal = false;
                    toastr()->success($this->message);
                    return;
                }

                // Générer le prochain code d'anonymat pour cette session
                $baseCode = $this->selectedSalleCode;
                $existingCodes = Copie::join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
                    ->where('copies.examen_id', $this->examen_id)
                    ->where('copies.ec_id', $this->ec_id)
                    ->where('copies.session_exam_id', $this->session_exam_id)
                    ->where('codes_anonymat.code_complet', 'like', $baseCode . '%')
                    ->whereNull('copies.deleted_at')
                    ->pluck('codes_anonymat.code_complet')
                    ->toArray();

                // Extraire les numéros pour trouver le suivant
                $numbers = [];
                foreach ($existingCodes as $code) {
                    if (preg_match('/^([A-Za-z]+)(\d+)$/', $code, $matches)) {
                        $numbers[] = (int)$matches[2];
                    }
                }
                $lastNumber = !empty($numbers) ? max($numbers) : 0;
                $nextNumber = $lastNumber + 1;
                $proposedCode = $baseCode . $nextNumber;

                // Vérifier que le nouveau code n'existe pas déjà
                while (CodeAnonymat::where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->where('code_complet', $proposedCode)
                    ->exists()) {
                    $codeUsedInCopie = Copie::join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
                        ->where('copies.examen_id', $this->examen_id)
                        ->where('copies.ec_id', $this->ec_id)
                        ->where('copies.session_exam_id', $this->session_exam_id)
                        ->where('codes_anonymat.code_complet', $proposedCode)
                        ->whereNull('copies.deleted_at')
                        ->exists();

                    if ($codeUsedInCopie) {
                        $nextNumber++;
                        $proposedCode = $baseCode . $nextNumber;
                    } else {
                        break;
                    }
                }

                $this->code_anonymat = $proposedCode;
                $this->showCopieModal = true;
                $this->dispatch('focus-note-field');
            } else {
                // Mode édition : fermer la modale
                $this->reset(['code_anonymat', 'note', 'editingCopieId']);
                $this->showCopieModal = false;
            }

            $this->messageType = 'success';
            toastr()->success($this->message);

            // Rafraîchir la liste des matières
            $this->calculateCopiesCountsForAllEcs();

            \Log::info('Copie sauvée avec examen_id cohérent', [
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
                'code_anonymat' => $this->code_anonymat,
                'session_exam_id' => $this->session_exam_id,
                'total_copies' => $this->totalCopiesCount,
                'user_copies' => $this->userCopiesCount
            ]);

        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);

            \Log::error('Erreur dans saveCopie', [
                'error' => $e->getMessage(),
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
                'session_id' => $this->session_exam_id,
                'trace' => $e->getTraceAsString()
            ]);
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

        // Vérifier que la copie correspond à l'EC et session actuellement sélectionnés
        if ($copie->ec_id != $this->ec_id || $copie->session_exam_id != $this->session_exam_id) {
            $this->message = 'Cette copie appartient à une autre matière ou session. Veuillez sélectionner la bonne matière et session avant de modifier.';
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

        // Vérifier que la copie correspond à l'EC et session actuellement sélectionnés
        if ($this->copieToDelete && ($this->copieToDelete->ec_id != $this->ec_id || $this->copieToDelete->session_exam_id != $this->session_exam_id)) {
            $this->message = 'Cette copie appartient à une autre matière ou session. Veuillez sélectionner la bonne matière et session avant de supprimer.';
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

            // Mettre à jour les compteurs globaux pour la session active
            $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('session_exam_id', $this->session_exam_id)
                ->count();

            $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('session_exam_id', $this->session_exam_id)
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

    /**
     * Méthode pour changer de session d'examen
     */
    public function changeSession($sessionId)
    {
        try {
            $session = SessionExam::find($sessionId);
            if (!$session) {
                throw new \Exception('Session d\'examen introuvable.');
            }

            // Mettre à jour la session active
            $this->session_exam_id = $sessionId;
            $this->currentSessionType = $session->type;

            // Sauvegarder dans les filtres
            $this->storeFiltres();

            // Recharger les données pour la nouvelle session
            if ($this->ec_id) {
                $this->updatedEcId();
            }

            // Message de confirmation
            $this->message = "Session changée vers : {$session->type}";
            $this->messageType = 'success';
            toastr()->success($this->message);

            // Émettre un événement pour le JavaScript
            $this->dispatch('session-changed', ['sessionType' => $session->type]);

        } catch (\Exception $e) {
            $this->message = 'Erreur lors du changement de session : ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }


    /**
     * NOUVELLE MÉTHODE : Vérifie si le formulaire peut être soumis
     */
    public function canSubmit()
    {
        // Vérifications de base
        if (empty($this->code_anonymat) || $this->note === null || $this->note === '') {
            return false;
        }

        // Vérifier le format du code d'anonymat
        if (!preg_match('/^[A-Za-z]+\d+$/', $this->code_anonymat)) {
            return false;
        }

        // Vérifier la plage de la note
        if ($this->note < 0 || $this->note > 20) {
            return false;
        }

        // Si double vérification activée, vérifier les confirmations
        if ($this->enableDoubleVerification) {
            if (empty($this->code_anonymat_confirmation) || $this->note_confirmation === null) {
                return false;
            }

            if ($this->code_anonymat !== $this->code_anonymat_confirmation) {
                return false;
            }

            if ((float)$this->note !== (float)$this->note_confirmation) {
                return false;
            }
        }

        return true;
    }

    /**
     * NOUVELLE MÉTHODE : Validation en temps réel du code d'anonymat
     */
    public function updatedCodeAnonymat($value)
    {
        // Nettoyer et formater le code
        $this->code_anonymat = strtoupper(trim($value));
        
        // Vérifier le format en temps réel
        if (!empty($this->code_anonymat)) {
            if (!preg_match('/^[A-Za-z]+\d+$/', $this->code_anonymat)) {
                $this->addError('code_anonymat', 'Format invalide. Utilisez des lettres suivies de chiffres (ex: TA1).');
            } else {
                $this->resetErrorBag('code_anonymat');
                
                // Vérifier la cohérence avec la salle
                if (!str_starts_with($this->code_anonymat, strtoupper($this->selectedSalleCode))) {
                    $this->addError('code_anonymat', "Le code devrait commencer par {$this->selectedSalleCode}");
                }
            }
        }
    }

    /**
     * NOUVELLE MÉTHODE : Validation en temps réel de la note
     */
    public function updatedNote($value)
    {
        if ($value !== null && $value !== '') {
            $note = (float)$value;
            if ($note < 0 || $note > 20) {
                $this->addError('note', 'La note doit être entre 0 et 20.');
            } else {
                $this->resetErrorBag('note');
            }
        }
    }

    /**
     * NOUVELLE MÉTHODE : Validation de la confirmation du code
     */
    public function updatedCodeAnonymatConfirmation($value)
    {
        if ($this->enableDoubleVerification && !empty($value)) {
            if ($value !== $this->code_anonymat) {
                $this->addError('code_anonymat_confirmation', 'Les codes d\'anonymat ne correspondent pas.');
            } else {
                $this->resetErrorBag('code_anonymat_confirmation');
            }
        }
    }

    /**
     * NOUVELLE MÉTHODE : Validation de la confirmation de la note
     */
    public function updatedNoteConfirmation($value)
    {
        if ($this->enableDoubleVerification && $value !== null) {
            if ((float)$value !== (float)$this->note) {
                $this->addError('note_confirmation', 'Les notes ne correspondent pas.');
            } else {
                $this->resetErrorBag('note_confirmation');
            }
        }
    }

    /**
     * NOUVELLE MÉTHODE : Gestion du toggle de double vérification
     */
    public function updatedEnableDoubleVerification($value)
    {
        // Réinitialiser les champs de confirmation
        if (!$value) {
            $this->code_anonymat_confirmation = '';
            $this->note_confirmation = null;
            $this->resetErrorBag(['code_anonymat_confirmation', 'note_confirmation']);
        }

        // Émettre un événement pour le JavaScript
        $this->dispatch('double-verification-changed', $value);
        
        // Message informatif
        if ($value) {
            $this->message = 'Double vérification activée - Saisie sécurisée';
            $this->messageType = 'info';
        } else {
            $this->message = 'Double vérification désactivée - Saisie rapide';
            $this->messageType = 'info';
        }
    }

    /**
     * NOUVELLE MÉTHODE : Réinitialiser pour la prochaine saisie
     */
    private function resetForNextEntry()
    {
        // Sauvegarder l'état de la double vérification
        $doubleVerificationState = $this->enableDoubleVerification;
        
        // Réinitialiser les champs de saisie
        $this->note = null;
        $this->note_confirmation = null;
        $this->code_anonymat_confirmation = '';
        
        // Générer le prochain code d'anonymat pour cette session
        $this->generateNextCodeAnonymatForSession(); // CORRECTION ICI
        
        // Restaurer l'état de la double vérification
        $this->enableDoubleVerification = $doubleVerificationState;
        
        // Recharger la liste des étudiants
        $this->chargerEtatEtudiants();
        
        // Garder la modal ouverte
        $this->showCopieModal = true;
        $this->dispatch('focus-note-field');
        
        // Message avec compteur
        $etudiantsSansCount = count($this->etudiantsSansCopies ?? []);
        if ($etudiantsSansCount > 0) {
            if ($etudiantsSansCount <= 5) {
                toastr()->success("Note enregistrée ! Plus que {$etudiantsSansCount} copie(s) à saisir ! 🎯");
            } else {
                toastr()->success("Note enregistrée ! {$etudiantsSansCount} copie(s) restante(s)");
            }
        } else {
            toastr()->success("Note enregistrée ! Toutes les copies ont été saisies ! 🎉");
        }
    }
    /**
     * NOUVELLE MÉTHODE : Réinitialiser complètement le formulaire
     */
    private function resetFormFields()
    {
        $this->reset([
            'code_anonymat', 
            'note', 
            'editingCopieId',
            'code_anonymat_confirmation',
            'note_confirmation'
        ]);
        $this->resetErrorBag();
    }


        /**
     * NOUVELLE MÉTHODE : Générer le prochain code d'anonymat pour la session courante
     */
    private function generateNextCodeAnonymatForSession()
    {
        $baseCode = $this->selectedSalleCode;
        $nextNumber = 1;
        
        // Récupérer tous les codes utilisés dans cette session pour cette EC
        $codesUtilises = Copie::join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('copies.examen_id', $this->examen_id)
            ->where('copies.ec_id', $this->ec_id)
            ->where('copies.session_exam_id', $this->session_exam_id)
            ->where('codes_anonymat.code_complet', 'like', $baseCode . '%')
            ->whereNull('copies.deleted_at')
            ->pluck('codes_anonymat.code_complet')
            ->toArray();

        // Extraire les numéros utilisés
        $numerosUtilises = [];
        foreach ($codesUtilises as $code) {
            if (preg_match('/^' . preg_quote($baseCode) . '(\d+)$/', $code, $matches)) {
                $numerosUtilises[] = (int)$matches[1];
            }
        }

        // Trouver le premier numéro disponible
        while (in_array($nextNumber, $numerosUtilises)) {
            $nextNumber++;
        }

        $proposedCode = $baseCode . $nextNumber;

        // Vérification finale que le code n'existe pas déjà
        $maxAttempts = 50;
        $attempts = 0;

        while ($this->codeExistsInCurrentSession($proposedCode) && $attempts < $maxAttempts) {
            $nextNumber++;
            $proposedCode = $baseCode . $nextNumber;
            $attempts++;
        }

        if ($attempts >= $maxAttempts) {
            throw new \Exception("Impossible de générer un code d'anonymat unique après {$maxAttempts} tentatives.");
        }

        $this->code_anonymat = $proposedCode;

        \Log::info('Code d\'anonymat généré pour session spécifique', [
            'session_id' => $this->session_exam_id,
            'ec_id' => $this->ec_id,
            'base_code' => $baseCode,
            'numero_choisi' => $nextNumber,
            'code_final' => $proposedCode,
            'codes_existants' => $codesUtilises
        ]);
    }

    /**
     * NOUVELLE MÉTHODE : Vérifier si un code existe dans la session courante
     */
    private function codeExistsInCurrentSession($code)
    {
        return Copie::join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('copies.examen_id', $this->examen_id)
            ->where('copies.ec_id', $this->ec_id)
            ->where('copies.session_exam_id', $this->session_exam_id)
            ->where('codes_anonymat.code_complet', $code)
            ->whereNull('copies.deleted_at')
            ->exists();
    }


        /**
     * MÉTHODE MODIFIÉE : Mise à jour des compteurs pour la session courante
     */
    private function updateCountersForCurrentSession()
    {
        $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->count();

        $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->where('saisie_par', Auth::id())
            ->count();

        \Log::info('Compteurs mis à jour après sauvegarde', [
            'session_id' => $this->session_exam_id,
            'ec_id' => $this->ec_id,
            'total_copies' => $this->totalCopiesCount,
            'user_copies' => $this->userCopiesCount
        ]);
    }

    /**
     * NOUVELLE MÉTHODE : Validation côté serveur pour AJAX
     */
    public function validateField($field, $value)
    {
        $this->$field = $value;
        
        switch ($field) {
            case 'code_anonymat':
                $this->updatedCodeAnonymat($value);
                break;
            case 'note':
                $this->updatedNote($value);
                break;
            case 'code_anonymat_confirmation':
                $this->updatedCodeAnonymatConfirmation($value);
                break;
            case 'note_confirmation':
                $this->updatedNoteConfirmation($value);
                break;
        }

        // Retourner l'état de validation
        return [
            'valid' => !$this->getErrorBag()->has($field),
            'errors' => $this->getErrorBag()->get($field),
            'canSubmit' => $this->canSubmit()
        ];
    }

    /**
     * NOUVELLE MÉTHODE : Obtenir les statistiques de saisie
     */
    public function getSaisieStats()
    {
        if (!$this->session_exam_id || !$this->ec_id) {
            return [
                'total_etudiants' => 0,
                'copies_saisies' => 0,
                'pourcentage' => 0,
                'restantes' => 0
            ];
        }

        $session = SessionExam::find($this->session_exam_id);
        if (!$session) {
            return ['error' => 'Session introuvable'];
        }

        // Calculer selon le type de session
        if ($session->type === 'Normale') {
            $totalEtudiants = Etudiant::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->count();
        } else {
            $sessionNormale = SessionExam::where('annee_universitaire_id', $session->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                return ['error' => 'Session normale introuvable'];
            }

            $totalEtudiants = Etudiant::eligiblesRattrapage(
                $this->niveau_id,
                $this->parcours_id,
                $sessionNormale->id
            )->count();
        }

        $copiesSaisies = Copie::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->whereNull('deleted_at')
            ->count();

        return [
            'total_etudiants' => $totalEtudiants,
            'copies_saisies' => $copiesSaisies,
            'pourcentage' => $totalEtudiants > 0 ? round(($copiesSaisies / $totalEtudiants) * 100, 1) : 0,
            'restantes' => max(0, $totalEtudiants - $copiesSaisies),
            'session_type' => $session->type,
            'double_verification' => $this->enableDoubleVerification
        ];
    }

    // LOGIQUE CÔTÉ LIVEWIRE UNIQUEMENT - Plus de JavaScript

    public function closeCopieModal()
    {
        // Recharger l'état des étudiants pour avoir les données les plus récentes
        $this->chargerEtatEtudiants();
        
        // Compter SEULEMENT les étudiants restants sans copie
        $etudiantsRestants = count($this->etudiantsSansCopies ?? []);

        // SI il reste des étudiants sans copie, afficher message et ne PAS fermer
        if ($etudiantsRestants > 0) {
            $this->message = "⚠️ Attention ! Il reste encore {$etudiantsRestants} étudiant(s) sans note. Cliquez sur 'Forcer la fermeture' si vous voulez vraiment arrêter.";
            $this->messageType = 'warning';
            
            // Activer le mode "demande de confirmation"
            $this->showForceCloseButton = true;
            
            toastr()->warning("Il reste {$etudiantsRestants} étudiant(s) sans note !");
            return; // Ne pas fermer la modal
        }

        // SINON fermeture directe (aucun étudiant restant = pas d'alerte)
        $this->forceCloseModal();
    }

    // Nouvelle propriété à ajouter en haut de votre classe
    public $showForceCloseButton = false;

    // Méthode pour forcer la fermeture après confirmation
    public function forceCloseModal()
    {
        $this->showCopieModal = false;
        $this->showForceCloseButton = false; // Réinitialiser
        $this->resetFormFields();
        toastr()->info('Saisie fermée');
    }

    public function render()
    {
        // Mise à jour des informations de session
        $this->updateSessionInfo();

        Log::debug('Rendering CopiesIndex', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'salle_id' => $this->salle_id,
            'examen_id' => $this->examen_id,
            'ec_id' => $this->ec_id,
            'search' => $this->search,
            'session_id' => $this->getCurrentSessionId(),
            'session_type' => $this->getCurrentSessionType(),
        ]);

        if ($this->niveau_id && $this->parcours_id && $this->salle_id && $this->examen_id && $this->session_exam_id) {
            $query = Copie::where('examen_id', $this->examen_id)
                          ->where('session_exam_id', $this->session_exam_id); // Filtrer par session

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

            $copies = $query->with(['codeAnonymat', 'ec', 'utilisateurSaisie', 'sessionExam'])
                ->paginate($this->perPage);

            Log::debug('Copies retrieved', [
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
                'session_id' => $this->session_exam_id,
                'total' => $copies->total(),
            ]);
        } else {
            // Toujours retourner un objet de pagination (vide)
            $copies = Copie::where('id', 0)->paginate($this->perPage);
            Log::debug('No copies retrieved due to missing filters', [
                'niveau_id' => $this->niveau_id,
                'parcours_id' => $this->parcours_id,
                'salle_id' => $this->salle_id,
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
                'session_id' => $this->session_exam_id,
            ]);
        }

        if ($this->ec_id && $this->ec_id !== 'all' && $this->examen_id) {
            $this->chargerEtatEtudiants();
        }

        // Créer un tableau complet pour sessionInfo
        $sessionInfo = [
            'message' => $this->sessionInfo,
            'active' => $this->sessionActive,
            'active_id' => $this->sessionActiveId,
            'type' => $this->sessionType,
            'can_add' => $this->canAddCopies,
            'session_libelle' => $this->sessionActive ? $this->sessionActive->type : null
        ];

        return view('livewire.copie.copies-index', [
            'copies' => $copies,
            'sessionInfo' => $sessionInfo
        ]);
    }
}
