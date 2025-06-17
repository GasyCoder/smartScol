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
use App\Models\SessionExam;
use App\Models\AnneeUniversitaire;
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
 * @property \Illuminate\Support\Collection $etudiantsSansManchette
 * @property \Illuminate\Support\Collection $searchResults
 */

class ManchettesIndex extends Component
{
    use WithPagination;

    // Propriétés de filtrage
    public $quickFilter = '';
    public $niveau_id;
    public $parcours_id;
    public $salle_id;
    public $examen_id;
    public $ec_id;
    public $currentSessionType = '';
    public $session_exam_id;

    // Collections pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $salles = [];
    public $ecs = [];

    // Gestion des sessions
    public $sessionActive = null;
    public $sessionActiveId = null;
    public $sessionType = null;
    public $sessionInfo = '';
    public $canAddManchettes = true;

    // Propriétés d'affichage et tri
    public $statusFilter = 'all';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;
    public $search = '';

    // Gestion des étudiants
    public $etudiantsAvecManchettes = [];
    public $etudiantsSansManchette = [];
    public $totalEtudiantsCount = 0;
    public $totalEtudiantsExpected = 0;

    // Modal de saisie
    public $showManchetteModal = false;
    public $code_anonymat = '';
    public $etudiant_id = null;
    public $matricule = '';
    public $editingManchetteId = null;

    // Recherche d'étudiants
    public $searchMode = 'matricule';
    public $searchQuery = '';
    public $searchResults = [];

    // Informations contextuelles
    public $selectedSalleCode = '';
    public $currentEcName = '';
    public $currentSalleName = '';
    public $currentEcDate = '';
    public $currentEcHeure = '';

    // Modal de suppression
    public $showDeleteModal = false;
    public $manchetteToDelete = null;

    // Messages et compteurs
    public $message = '';
    public $messageType = '';
    public $userManchettesCount = 0;
    public $totalManchettesCount = 0;

    protected $rules = [
        'code_anonymat' => 'required|string|max:20',
        'etudiant_id' => 'required|exists:etudiants,id',
    ];

    // Écouter les changements de session
    protected $listeners = ['session-changed' => 'handleSessionChanged'];

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
            $this->canAddManchettes = true;
            $this->sessionInfo = "Session {$sessionActive->type} active - Année {$anneeActive->libelle}";

            \Log::info('Session active initialisée dans ManchettesIndex', [
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
            $this->canAddManchettes = false;
            \Log::error('Erreur lors de l\'initialisation de la session dans ManchettesIndex', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            toastr()->error($this->sessionInfo);
        }

        $this->loadFiltres();
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
     * Met à jour les informations de session avec vraie relation
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
            $this->canAddManchettes = true;
            $this->sessionInfo = "Session {$sessionActive->type} active - Année {$anneeActive->libelle}";

            \Log::info('Session active mise à jour', [
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
            $this->canAddManchettes = false;
            \Log::error('Erreur lors de la mise à jour de la session', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * NOUVELLE MÉTHODE : Charger TOUS les ECs depuis tous les examens du niveau/parcours
     */
    public function loadAllEcsFromExamens()
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

        \Log::info('Examens trouvés pour niveau/parcours', [
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

        \Log::info('ECs trouvés depuis tous les examens', [
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
                'has_manchette' => false, // Sera calculé après
                'manchettes_count' => 0,  // Sera calculé après
                'user_manchettes_count' => 0,
                'pourcentage' => 0,
                'session_libelle' => ucfirst($sessionType)
            ];
        })->values();

        $this->ecs = $ecsGrouped;

        // Calculer les compteurs de manchettes pour tous les ECs
        $this->calculateManchettesCountsForAllEcs();

        \Log::info('ECs finaux chargés', [
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
     * Calculer les compteurs de manchettes pour tous les ECs chargés
     */
    public function calculateManchettesCountsForAllEcs():void
    {
        if ($this->ecs->isEmpty()) {
            return;
        }

        $sessionId = $this->getCurrentSessionId();
        if (!$sessionId) {
            return;
        }

        $ecIds = $this->ecs->pluck('id')->toArray();

        // Compter les manchettes par EC pour la session active
        $manchettesCounts = DB::table('manchettes')
            ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('manchettes.session_exam_id', $sessionId)
            ->whereIn('codes_anonymat.ec_id', $ecIds)
            ->whereNull('manchettes.deleted_at')
            ->select('codes_anonymat.ec_id', DB::raw('count(*) as total'))
            ->groupBy('codes_anonymat.ec_id')
            ->pluck('total', 'ec_id')
            ->toArray();

        // Compter les manchettes de l'utilisateur
        $userManchettesCounts = DB::table('manchettes')
            ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('manchettes.session_exam_id', $sessionId)
            ->where('manchettes.saisie_par', Auth::id())
            ->whereIn('codes_anonymat.ec_id', $ecIds)
            ->whereNull('manchettes.deleted_at')
            ->select('codes_anonymat.ec_id', DB::raw('count(*) as total'))
            ->groupBy('codes_anonymat.ec_id')
            ->pluck('total', 'ec_id')
            ->toArray();

        // Mettre à jour les compteurs
        $this->ecs = $this->ecs->map(function($ec) use ($manchettesCounts, $userManchettesCounts) {
            $manchettesCount = $manchettesCounts[$ec->id] ?? 0;
            $userCount = $userManchettesCounts[$ec->id] ?? 0;

            $ec->manchettes_count = $manchettesCount;
            $ec->user_manchettes_count = $userCount;
            $ec->has_manchette = $manchettesCount > 0;
            $ec->pourcentage = $this->totalEtudiantsCount > 0 ?
                round(($manchettesCount / $this->totalEtudiantsCount) * 100, 1) : 0;

            return $ec;
        });

        \Log::info('Compteurs mis à jour pour tous les ECs', [
            'manchettes_counts' => $manchettesCounts,
            'user_counts' => $userManchettesCounts
        ]);
    }

    /**
     * Gère les changements de session
     */
    public function handleSessionChanged($data)
    {
        $this->updateSessionInfo();
        $this->updateCountersForCurrentSession();
        toastr()->info('Session changée - Les données ont été mises à jour');
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
        // Vérification des autorisations de session
        if (!$this->canAddManchettes) {
            toastr()->error($this->sessionInfo);
            return;
        }

        if (!$this->examen_id || !$this->salle_id || !$this->ec_id || $this->ec_id === 'all') {
            toastr()->error('Veuillez d\'abord sélectionner une matière spécifique');
            return;
        }

        $etudiant = Etudiant::find($etudiantId);
        if (!$etudiant) {
            toastr()->error('Étudiant introuvable');
            return;
        }

        // Vérifier l'existence pour la session ACTIVE
        $hasExistingManchette = $this->checkExistingManchetteForCurrentSession($etudiantId);

        if ($hasExistingManchette) {
            $sessionLibelle = ucfirst($this->getCurrentSessionType());
            toastr()->error("Cet étudiant a déjà une manchette pour cette matière en session {$sessionLibelle}");
            return;
        }

        // Générer le code d'anonymat pour la session courante
        $this->generateCodeAnonymat();

        $this->etudiant_id = $etudiant->id;
        $this->matricule = $etudiant->matricule;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->editingManchetteId = null;
        $this->showManchetteModal = true;

        $sessionLibelle = ucfirst($this->getCurrentSessionType());
        toastr()->info("Prêt à enregistrer une manchette pour {$etudiant->nom} {$etudiant->prenom} (Session {$sessionLibelle})");
        $this->dispatch('manchette-etudiant-selected');
    }

    /**
     * Vérifie si une manchette existe pour la session courante
     */
    private function checkExistingManchetteForCurrentSession($etudiantId)
    {
        $sessionId = $this->getCurrentSessionId();
        if (!$sessionId) {
            return false;
        }

        $exists = Manchette::where('etudiant_id', $etudiantId)
            ->where('examen_id', $this->examen_id)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function ($query) {
                $query->where('ec_id', $this->ec_id);
            })
            ->exists();

        \Log::info('Vérification manchette existante', [
            'etudiant_id' => $etudiantId,
            'session_id' => $sessionId,
            'ec_id' => $this->ec_id,
            'exists' => $exists
        ]);

        return $exists;
    }

    /**
     * Génère le code d'anonymat pour la session courante
     */
    private function generateCodeAnonymat()
    {
        if (empty($this->selectedSalleCode)) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->selectedSalleCode = $salle->code_base;
                $this->currentSalleName = $salle->nom;
            }
        }

        $sessionId = $this->getCurrentSessionId();
        $baseCode = $this->selectedSalleCode;

        // ✅ CORRECTION PRINCIPALE : Compter les codes d'anonymat UTILISÉS dans les manchettes pour cette session
        $codesUtilises = DB::table('manchettes')
            ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('manchettes.examen_id', $this->examen_id)
            ->where('manchettes.session_exam_id', $sessionId)
            ->where('codes_anonymat.ec_id', $this->ec_id)
            ->where('codes_anonymat.code_complet', 'like', $baseCode . '%')
            ->whereNull('manchettes.deleted_at')
            ->pluck('codes_anonymat.code_complet')
            ->toArray();

        \Log::info('Codes d\'anonymat utilisés dans les manchettes', [
            'session_id' => $sessionId,
            'ec_id' => $this->ec_id,
            'base_code' => $baseCode,
            'codes_utilises' => $codesUtilises
        ]);

        // Extraire les numéros utilisés
        $numerosUtilises = [];
        foreach ($codesUtilises as $code) {
            if (preg_match('/^' . preg_quote($baseCode) . '(\d+)$/', $code, $matches)) {
                $numerosUtilises[] = (int)$matches[1];
            }
        }

        // Trouver le premier numéro disponible
        $nextNumber = 1;
        while (in_array($nextNumber, $numerosUtilises)) {
            $nextNumber++;
        }

        $proposedCode = $baseCode . $nextNumber;

        // ✅ DOUBLE VÉRIFICATION : S'assurer que le code n'est pas utilisé
        $maxAttempts = 50; // Éviter les boucles infinies
        $attempts = 0;

        while ($this->codeExistsForCurrentSession($proposedCode) && $attempts < $maxAttempts) {
            $nextNumber++;
            $proposedCode = $baseCode . $nextNumber;
            $attempts++;
        }

        if ($attempts >= $maxAttempts) {
            throw new \Exception("Impossible de générer un code d'anonymat unique après {$maxAttempts} tentatives.");
        }

        $this->code_anonymat = $proposedCode;

        \Log::info('Code d\'anonymat généré', [
            'session_id' => $sessionId,
            'ec_id' => $this->ec_id,
            'base_code' => $baseCode,
            'numero_choisi' => $nextNumber,
            'code_final' => $proposedCode,
            'tentatives' => $attempts
        ]);
    }

    /**
     * Vérifie si un code existe pour la session courante
     */
    private function codeExistsForCurrentSession($code)
    {
        $sessionId = $this->getCurrentSessionId();
        if (!$sessionId) {
            return false;
        }

        // Vérifier dans les manchettes actives
        $existsInManchettes = DB::table('manchettes')
            ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('manchettes.examen_id', $this->examen_id)
            ->where('manchettes.session_exam_id', $sessionId)
            ->where('codes_anonymat.ec_id', $this->ec_id)
            ->where('codes_anonymat.code_complet', $code)
            ->whereNull('manchettes.deleted_at')
            ->exists();

        \Log::debug('Vérification existence code', [
            'code' => $code,
            'session_id' => $sessionId,
            'ec_id' => $this->ec_id,
            'exists_in_manchettes' => $existsInManchettes
        ]);

        return $existsInManchettes;
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
            $this->currentSessionType = '';
        }
        $this->storeFiltres();
        $this->resetPage();
    }

    public function resetFiltres()
    {
        $this->reset([
            'niveau_id', 'parcours_id', 'salle_id', 'examen_id', 'ec_id',
            'selectedSalleCode', 'currentEcName', 'currentSalleName',
            'currentEcDate', 'currentEcHeure', 'currentSessionType'
        ]);
        session()->forget('manchettes.filtres');
        $this->parcours = collect();
        $this->salles = collect();
        $this->ecs = collect();
        $this->resetPage();
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

        $this->updateSessionInfo();
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

            // CORRIGÉ : Charger TOUS les ECs de tous les examens
            $this->loadAllEcsFromExamens();
        }

        $this->storeFiltres();
        $this->resetPage();
    }

    /**
     * Charge les étudiants avec et sans manchettes pour la session active
     */
    public function chargerEtudiants()
    {
        if (!$this->examen_id || !$this->ec_id || $this->ec_id === 'all' || !$this->session_exam_id) {
            $this->etudiantsSansManchette = collect();
            $this->etudiantsAvecManchettes = collect();
            $this->totalEtudiantsCount = 0;
            return;
        }

        // NOUVELLE LOGIQUE : Récupérer la session actuelle
        $session = SessionExam::find($this->session_exam_id);
        if (!$session) {
            $this->etudiantsSansManchette = collect();
            $this->etudiantsAvecManchettes = collect();
            $this->totalEtudiantsCount = 0;
            $this->sessionInfo = "Erreur : Session introuvable";
            return;
        }

        // LOGIQUE DIFFÉRENTE SELON LE TYPE DE SESSION
        if ($session->type === 'Normale') {
            // Session normale : TOUS les étudiants du niveau/parcours
            $etudiants = Etudiant::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->get();

            $this->sessionInfo = "Session Normale - {$etudiants->count()} étudiant(s) disponible(s)";

        } else {
            // Session rattrapage : SEULS les étudiants éligibles
            $sessionNormale = SessionExam::where('annee_universitaire_id', $session->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                $this->etudiantsSansManchette = collect();
                $this->etudiantsAvecManchettes = collect();
                $this->totalEtudiantsCount = 0;
                $this->sessionInfo = "Erreur : Aucune session normale trouvée pour cette année";
                return;
            }

            // Utiliser la nouvelle méthode du modèle Etudiant
            $etudiants = Etudiant::eligiblesRattrapage(
                $this->niveau_id,
                $this->parcours_id,
                $sessionNormale->id
            )->get();

            // Message informatif pour session rattrapage
            if ($etudiants->isEmpty()) {
                $this->sessionInfo = "Session Rattrapage - Aucun étudiant éligible (tous sont déjà admis ou n'ont pas de décision rattrapage)";
            } else {
                $this->sessionInfo = "Session Rattrapage - {$etudiants->count()} étudiant(s) éligible(s)";
            }
        }

        // Récupérer les IDs des étudiants qui ont déjà une manchette pour cette EC dans cette session
        $etudiantsAvecManchettesIds = Manchette::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->whereHas('codeAnonymat', function ($query) {
                $query->where('ec_id', $this->ec_id);
            })
            ->pluck('etudiant_id')
            ->filter()
            ->unique()
            ->toArray();

        // Séparer les étudiants
        $this->etudiantsAvecManchettes = $etudiants->whereIn('id', $etudiantsAvecManchettesIds)->values();
        $this->etudiantsSansManchette = $etudiants->whereNotIn('id', $etudiantsAvecManchettesIds)->values();
        $this->totalEtudiantsCount = $etudiants->count();
        $this->totalEtudiantsExpected = $this->totalEtudiantsCount;

        // Log pour debug
        \Log::info('Étudiants chargés avec nouvelle logique (Manchettes)', [
            'session_type' => $session->type,
            'session_id' => $this->session_exam_id,
            'total_etudiants_disponibles' => $etudiants->count(),
            'avec_manchettes' => count($etudiantsAvecManchettesIds),
            'sans_manchettes' => $this->etudiantsSansManchette->count(),
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
        ]);
    }

    public function updatedEcId()
    {
        $this->currentEcName = '';
        $this->currentEcDate = '';
        $this->currentEcHeure = '';

        if ($this->ec_id === 'all') {
            $this->handleAllEcsSelection();
        } else if ($this->ec_id && $this->salle_id && $this->examen_id) {
            $this->handleSpecificEcSelection();
        }

        $this->message = '';
        $this->storeFiltres();
        $this->resetPage();
    }

    /**
     * Gère la sélection "Toutes les matières"
     */
    private function handleAllEcsSelection()
    {
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

            $this->updateCountersForAllEcs($ecIds);
        }
    }

    /**
     * Gère la sélection d'une matière spécifique
     */
    private function handleSpecificEcSelection()
    {
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
            // Essayer de trouver l'EC dans la collection chargée
            $ec = $this->ecs->firstWhere('id', $this->ec_id);
            if ($ec) {
                $this->currentEcName = $ec->nom;
                $this->currentEcDate = $ec->date_formatted ?? '';
                $this->currentEcHeure = $ec->heure_formatted ?? '';
            } else {
                Log::warning('EC info not found', [
                    'ec_id' => $this->ec_id,
                    'examen_id' => $this->examen_id,
                    'salle_id' => $this->salle_id,
                ]);
                $this->ec_id = null;
                return;
            }
        }

        $this->updateCountersForSpecificEc();
    }

    /**
     * Met à jour les compteurs pour toutes les matières
     */
    private function updateCountersForAllEcs($ecIds)
    {
        $sessionId = $this->getCurrentSessionId();

        $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
            ->whereIn('ec_id', $ecIds)
            ->pluck('id')
            ->toArray();

        if (!empty($codesIds) && $sessionId) {
            $this->totalManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)
                ->where('session_exam_id', $sessionId)
                ->count();
            $this->userManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)
                ->where('session_exam_id', $sessionId)
                ->where('saisie_par', Auth::id())
                ->count();
        } else {
            $this->totalManchettesCount = 0;
            $this->userManchettesCount = 0;
        }

        $nombreMatieres = count($ecIds);
        $this->totalEtudiantsExpected = $nombreMatieres > 0 ? $this->totalEtudiantsCount * $nombreMatieres : 0;
    }

    /**
     * Met à jour les compteurs pour une matière spécifique
     */
    private function updateCountersForSpecificEc()
    {
        $sessionId = $this->getCurrentSessionId();

        $codesIds = CodeAnonymat::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->pluck('id')
            ->toArray();

        if (!empty($codesIds) && $sessionId) {
            $this->totalManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)
                ->where('session_exam_id', $sessionId)
                ->count();
            $this->userManchettesCount = Manchette::whereIn('code_anonymat_id', $codesIds)
                ->where('session_exam_id', $sessionId)
                ->where('saisie_par', Auth::id())
                ->count();
        } else {
            $this->totalManchettesCount = 0;
            $this->userManchettesCount = 0;
        }

        $this->totalEtudiantsExpected = $this->totalEtudiantsCount;
    }

    /**
     * Met à jour les compteurs pour la session courante
     */
    private function updateCountersForCurrentSession()
    {
        if ($this->examen_id && $this->ec_id) {
            if ($this->ec_id === 'all') {
                $ecIds = DB::table('ecs')
                    ->join('examen_ec', 'ecs.id', '=', 'examen_ec.ec_id')
                    ->where('examen_ec.examen_id', $this->examen_id)
                    ->where('examen_ec.salle_id', $this->salle_id)
                    ->pluck('ecs.id')
                    ->toArray();
                $this->updateCountersForAllEcs($ecIds);
            } else {
                $this->updateCountersForSpecificEc();
            }
        }
    }

    public function openManchetteModal()
    {
        // Vérification des autorisations de session
        if (!$this->canAddManchettes) {
            $this->message = $this->sessionInfo;
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        if (!$this->examen_id || !$this->salle_id || !$this->ec_id || $this->ec_id === 'all') {
            $this->message = 'Veuillez sélectionner une matière spécifique';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        $this->generateCodeAnonymat();
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

        // Récupérer la session actuelle
        $session = SessionExam::find($this->session_exam_id);
        if (!$session) {
            $this->searchResults = [];
            return;
        }

        // Base de la requête selon le type de session
        if ($session->type === 'Normale') {
            // Session normale : tous les étudiants du niveau/parcours
            $query = Etudiant::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id);

        } else {
            // Session rattrapage : seuls les étudiants éligibles
            $sessionNormale = SessionExam::where('annee_universitaire_id', $session->annee_universitaire_id)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                $this->searchResults = [];
                return;
            }

            $query = Etudiant::eligiblesRattrapage(
                $this->niveau_id,
                $this->parcours_id,
                $sessionNormale->id
            );
        }

        // Appliquer le filtre de recherche
        if ($this->searchMode === 'matricule') {
            $query->where('matricule', 'like', '%' . $this->searchQuery . '%');
        } else {
            $searchTerm = '%' . $this->searchQuery . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nom', 'like', $searchTerm)
                ->orWhere('prenom', 'like', $searchTerm);
            });
        }

        // Exclure les étudiants ayant déjà une manchette pour cette session
        $etudiantsAvecManchettes = Manchette::where('examen_id', $this->examen_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->whereHas('codeAnonymat', function($q) {
                $q->where('ec_id', $this->ec_id);
            })
            ->pluck('etudiant_id')
            ->toArray();

        if (!empty($etudiantsAvecManchettes) && !isset($this->editingManchetteId)) {
            $query->whereNotIn('id', $etudiantsAvecManchettes);
        }

        $this->searchResults = $query->limit(10)->get();

        // Log pour debug
        \Log::info('Recherche étudiants avec nouvelle logique', [
            'session_type' => $session->type,
            'search_query' => $this->searchQuery,
            'results_count' => $this->searchResults->count()
        ]);
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

    /**
     * NOUVELLE MÉTHODE : Sélection rapide d'un étudiant depuis la liste
     */
    public function selectEtudiantQuick($etudiantId)
    {
        $etudiant = Etudiant::find($etudiantId);
        if (!$etudiant) {
            toastr()->error('Étudiant introuvable');
            return;
        }

        // Vérifier que l'étudiant n'a pas déjà une manchette
        $hasExistingManchette = $this->checkExistingManchetteForCurrentSession($etudiantId);
        if ($hasExistingManchette) {
            $sessionLibelle = ucfirst($this->getCurrentSessionType());
            toastr()->error("Cet étudiant a déjà une manchette pour cette matière en session {$sessionLibelle}");
            return;
        }

        // Sélectionner l'étudiant
        $this->etudiant_id = $etudiant->id;
        $this->matricule = $etudiant->matricule;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->quickFilter = ''; // Réinitialiser le filtre

        // Focus automatique sur le bouton enregistrer après sélection
        $this->dispatch('etudiant-selected-quick');

        toastr()->success("Étudiant {$etudiant->nom} {$etudiant->prenom} sélectionné");
    }

    /**
     * NOUVELLE MÉTHODE : Sélection d'un étudiant aléatoire
     */
    public function selectRandomStudent()
    {
        if (empty($this->etudiantsSansManchette) || count($this->etudiantsSansManchette) == 0) {
            toastr()->warning('Aucun étudiant disponible');
            return;
        }

        $randomIndex = array_rand($this->etudiantsSansManchette->toArray());
        $randomEtudiant = $this->etudiantsSansManchette[$randomIndex];

        $this->selectEtudiantQuick($randomEtudiant->id);
        toastr()->info('Étudiant sélectionné aléatoirement');
    }

    /**
     * NOUVELLE MÉTHODE : Sélection du premier étudiant
     */
    public function selectFirstStudent()
    {
        if (empty($this->etudiantsSansManchette) || count($this->etudiantsSansManchette) == 0) {
            toastr()->warning('Aucun étudiant disponible');
            return;
        }

        $firstEtudiant = $this->etudiantsSansManchette->first();
        $this->selectEtudiantQuick($firstEtudiant->id);
        toastr()->info('Premier étudiant sélectionné');
    }

    /**
     * MÉTHODE CORRIGÉE : Mise à jour du saveManchette pour garder la modal ouverte
    */
    public function saveManchette()
    {
        // Vérification des autorisations de session
        if (!$this->canAddManchettes) {
            $this->message = $this->sessionInfo;
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

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

            $sessionId = $this->getCurrentSessionId();
            $sessionType = $this->getCurrentSessionType();

            if (!$sessionId) {
                throw new \Exception("Aucune session active trouvée.");
            }

            if (!isset($this->editingManchetteId)) {
                // ✅ VÉRIFICATION RENFORCÉE : Manchette existante pour cet étudiant
                $existingManchette = Manchette::where('etudiant_id', $this->etudiant_id)
                    ->where('examen_id', $this->examen_id)
                    ->where('session_exam_id', $sessionId)
                    ->whereHas('codeAnonymat', function ($query) {
                        $query->where('ec_id', $this->ec_id);
                    })
                    ->first();

                if ($existingManchette) {
                    $sessionLibelle = ucfirst($sessionType);
                    throw new \Exception("Cet étudiant a déjà une manchette pour cette matière en session {$sessionLibelle} (Code: {$existingManchette->codeAnonymat->code_complet}).");
                }
            }

            // ✅ NOUVELLE LOGIQUE : Créer d'abord le code d'anonymat avec session_exam_id
            $codeAnonymat = CodeAnonymat::where('examen_id', $this->examen_id)
                ->where('session_exam_id', $sessionId)
                ->where('ec_id', $this->ec_id)
                ->where('code_complet', $this->code_anonymat)
                ->first();

            if (!$codeAnonymat) {
                // Créer un nouveau code d'anonymat
                $codeAnonymat = CodeAnonymat::create([
                    'examen_id' => $this->examen_id,
                    'session_exam_id' => $sessionId,
                    'ec_id' => $this->ec_id,
                    'code_complet' => $this->code_anonymat,
                    'sequence' => null,
                ]);

                \Log::info('Nouveau code d\'anonymat créé', [
                    'code_id' => $codeAnonymat->id,
                    'examen_id' => $this->examen_id,
                    'session_id' => $sessionId,
                    'ec_id' => $this->ec_id,
                    'code_complet' => $this->code_anonymat
                ]);
            }

            // ✅ VÉRIFICATION STRICTE : Code utilisé par un autre étudiant dans cette session
            $existingManchetteWithCode = Manchette::where('code_anonymat_id', $codeAnonymat->id)
                ->where('session_exam_id', $sessionId)
                ->when(isset($this->editingManchetteId), function ($query) {
                    return $query->where('id', '!=', $this->editingManchetteId);
                })
                ->with('etudiant')
                ->first();

            if ($existingManchetteWithCode) {
                $sessionLibelle = ucfirst($sessionType);
                $etudiantExistant = $existingManchetteWithCode->etudiant;
                throw new \Exception("Ce code d'anonymat ({$this->code_anonymat}) est déjà utilisé en session {$sessionLibelle} par l'étudiant {$etudiantExistant->nom} {$etudiantExistant->prenom}.");
            }

            // Rechercher une manchette supprimée pour cette session
            $deletedManchette = Manchette::withTrashed()
                ->where('examen_id', $this->examen_id)
                ->where('code_anonymat_id', $codeAnonymat->id)
                ->where('session_exam_id', $sessionId)
                ->whereNotNull('deleted_at')
                ->first();

            if ($deletedManchette) {
                // Restaurer une manchette supprimée
                $deletedManchette->restore();
                $deletedManchette->update([
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);
                $sessionLibelle = ucfirst($sessionType);
                $this->message = "Manchette restaurée et mise à jour avec succès pour la session {$sessionLibelle}";
            } elseif (isset($this->editingManchetteId)) {
                // Mode modification
                $manchette = Manchette::find($this->editingManchetteId);
                if (!$manchette) {
                    throw new \Exception('La manchette à modifier est introuvable.');
                }

                // Vérifier si on change d'étudiant
                if ($manchette->etudiant_id != $this->etudiant_id) {
                    $etudiantHasEC = Manchette::where('etudiant_id', $this->etudiant_id)
                        ->where('examen_id', $this->examen_id)
                        ->where('session_exam_id', $sessionId)
                        ->where('id', '!=', $this->editingManchetteId)
                        ->whereHas('codeAnonymat', function ($query) {
                            $query->where('ec_id', $this->ec_id);
                        })
                        ->exists();

                    if ($etudiantHasEC) {
                        $sessionLibelle = ucfirst($sessionType);
                        throw new \Exception("Cet étudiant a déjà une manchette pour cette matière en session {$sessionLibelle}.");
                    }
                }

                $manchette->update([
                    'code_anonymat_id' => $codeAnonymat->id,
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]);
                $sessionLibelle = ucfirst($sessionType);
                $this->message = "Manchette modifiée avec succès pour la session {$sessionLibelle}";
            } else {
                // ✅ NOUVELLE CRÉATION
                Manchette::create([
                    'examen_id' => $this->examen_id,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'etudiant_id' => $this->etudiant_id,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                    'session_exam_id' => $sessionId,
                ]);
                $sessionLibelle = ucfirst($sessionType);
                $this->message = "Manchette enregistrée avec succès pour la session {$sessionLibelle}";

                \Log::info('Nouvelle manchette créée', [
                    'etudiant_id' => $this->etudiant_id,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'session_id' => $sessionId,
                    'code_complet' => $this->code_anonymat
                ]);
            }

            // ✅ GESTION POST-SAUVEGARDE AMÉLIORÉE
            if (!isset($this->editingManchetteId)) {
                // Réinitialiser seulement les champs étudiant
                $this->etudiant_id = null;
                $this->matricule = '';
                $this->searchQuery = '';
                $this->searchResults = [];
                $this->quickFilter = '';

                // Générer le prochain code pour la session courante
                $this->generateNextCodeForCurrentSession();

                // Recharger la liste des étudiants
                $this->chargerEtudiants();

                // Garder la modal ouverte
                $this->showManchetteModal = true;
                $this->dispatch('focus-search-field');

                // Message avec compteur
                $etudiantsSansCount = count($this->etudiantsSansManchette ?? []);
                if ($etudiantsSansCount > 0) {
                    if ($etudiantsSansCount <= 10) {
                        toastr()->success($this->message . " - Plus que {$etudiantsSansCount} étudiant(s) !");
                    } else {
                        toastr()->success($this->message . " - {$etudiantsSansCount} étudiant(s) restant(s)");
                    }
                } else {
                    toastr()->success($this->message . " - Tous les étudiants ont maintenant une manchette ! 🎉");
                }
            } else {
                // Mode modification : fermer la modal
                $this->reset(['code_anonymat', 'etudiant_id', 'matricule', 'editingManchetteId', 'searchResults', 'searchQuery', 'quickFilter']);
                $this->showManchetteModal = false;
                toastr()->success($this->message);
            }

            // Mettre à jour les compteurs
            $this->updateCountersForCurrentSession();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);

            \Log::error('Erreur dans saveManchette', [
                'error' => $e->getMessage(),
                'etudiant_id' => $this->etudiant_id,
                'code_anonymat' => $this->code_anonymat,
                'session_id' => $this->getCurrentSessionId(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }


    /**
     * Génère le prochain code pour la session courante
     */
    private function generateNextCodeForCurrentSession()
    {
        if (preg_match('/^([A-Za-z]+)(\d+)$/', $this->code_anonymat, $matches)) {
            $prefix = $matches[1];
            $currentNumber = (int)$matches[2];
            $sessionId = $this->getCurrentSessionId();

            // Récupérer tous les codes utilisés dans cette session pour cette EC
            $codesUtilises = DB::table('manchettes')
                ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
                ->where('manchettes.examen_id', $this->examen_id)
                ->where('manchettes.session_exam_id', $sessionId)
                ->where('codes_anonymat.ec_id', $this->ec_id)
                ->where('codes_anonymat.code_complet', 'like', $prefix . '%')
                ->whereNull('manchettes.deleted_at')
                ->pluck('codes_anonymat.code_complet')
                ->toArray();

            // Extraire les numéros utilisés
            $numerosUtilises = [];
            foreach ($codesUtilises as $code) {
                if (preg_match('/^' . preg_quote($prefix) . '(\d+)$/', $code, $matches)) {
                    $numerosUtilises[] = (int)$matches[1];
                }
            }

            // Trouver le premier numéro disponible après le numéro actuel
            $nextNumber = $currentNumber + 1;
            while (in_array($nextNumber, $numerosUtilises)) {
                $nextNumber++;
            }

            $newCode = $prefix . $nextNumber;

            // Vérification finale
            if (!$this->codeExistsForCurrentSession($newCode)) {
                $this->code_anonymat = $newCode;
            } else {
                // Fallback : utiliser la méthode complète de génération
                $this->generateCodeAnonymat();
            }

            \Log::info('Prochain code généré', [
                'code_precedent' => $matches[0],
                'nouveau_code' => $this->code_anonymat,
                'numeros_utilises' => $numerosUtilises
            ]);
        }
    }


    /**
     * NOUVELLE MÉTHODE : Fermer la modal manuellement avec confirmation si des étudiants restent
     */
    public function closeModalWithConfirmation()
    {
        $etudiantsSansCount = count($this->etudiantsSansManchette ?? []);

        if ($etudiantsSansCount > 0 && !isset($this->editingManchetteId)) {
            // Demander confirmation si des étudiants n'ont pas encore de manchette
            $this->dispatch('confirm-close-modal', [
                'message' => "Il reste encore {$etudiantsSansCount} étudiant(s) sans manchette. Voulez-vous vraiment fermer la saisie ?"
            ]);
        } else {
            // Fermer directement
            $this->forceCloseModal();
        }
    }

    /**
     * NOUVELLE MÉTHODE : Forcer la fermeture de la modal
     */
    public function forceCloseModal()
    {
        $this->showManchetteModal = false;
        $this->reset(['code_anonymat', 'etudiant_id', 'matricule', 'editingManchetteId', 'searchResults', 'searchQuery', 'quickFilter']);
        toastr()->info('Saisie des manchettes fermée');
    }

    public function editManchette($id)
    {
        $manchette = Manchette::with(['codeAnonymat', 'etudiant', 'sessionExam'])->find($id);
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

        // Vérifier que la manchette appartient à la session active
        $sessionId = $this->getCurrentSessionId();
        if ($manchette->session_exam_id !== $sessionId) {
            $sessionLibelle = $this->sessionActive ? $this->sessionActive->type : 'Inconnue';
            $manchetteSessionLibelle = $manchette->sessionExam ? $manchette->sessionExam->type : 'Inconnue';
            $this->message = "Cette manchette appartient à la session {$manchetteSessionLibelle}. Session active : {$sessionLibelle}.";
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
        $manchette = Manchette::with(['codeAnonymat.ec', 'sessionExam'])->find($id);
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

        // Vérifier que la manchette appartient à la session active
        $sessionId = $this->getCurrentSessionId();
        if ($manchette->session_exam_id !== $sessionId) {
            $sessionLibelle = $this->sessionActive ? $this->sessionActive->type : 'Inconnue';
            $manchetteSessionLibelle = $manchette->sessionExam ? $manchette->sessionExam->type : 'Inconnue';
            $this->message = "Cette manchette appartient à la session {$manchetteSessionLibelle}. Session active : {$sessionLibelle}.";
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
                throw new \Exception('Cette manchette est déjà associée à une copie et ne peut pas être supprimée.');
            }

            $sessionLibelle = $this->manchetteToDelete->sessionExam ? $this->manchetteToDelete->sessionExam->type : 'Inconnue';
            $this->manchetteToDelete->delete();
            $this->message = "Manchette supprimée avec succès de la session {$sessionLibelle}";
            $this->messageType = 'success';
            $this->showDeleteModal = false;
            $this->manchetteToDelete = null;

            // Mettre à jour les compteurs pour la session courante
            $this->updateCountersForCurrentSession();

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
        // Mise à jour des informations de session
        $this->updateSessionInfo();

        Log::debug('Rendering ManchettesIndex', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'salle_id' => $this->salle_id,
            'examen_id' => $this->examen_id,
            'ec_id' => $this->ec_id,
            'search' => $this->search,
            'session_id' => $this->getCurrentSessionId(),
            'session_type' => $this->getCurrentSessionType(),
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
            // Filtrer par session active (session_exam_id)
            $sessionId = $this->getCurrentSessionId();
            $query = Manchette::where('examen_id', $this->examen_id);

            if ($sessionId) {
                $query->where('session_exam_id', $sessionId);
            } else {
                // Si pas de session active, ne rien afficher
                $query->where('id', 0);
            }

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

            $manchettes = $query->with(['codeAnonymat.ec', 'etudiant', 'utilisateurSaisie', 'sessionExam'])
                ->paginate($this->perPage);

            Log::debug('Manchettes retrieved', [
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
                'session_id' => $sessionId,
                'total' => $manchettes->total(),
            ]);

            // Mettre à jour les compteurs pour la session courante
            $this->updateCountersForCurrentSession();
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

        // Créer un tableau complet pour sessionInfo
        $sessionInfo = [
            'message' => $this->sessionInfo,
            'active' => $this->sessionActive,
            'active_id' => $this->sessionActiveId,
            'type' => $this->sessionType,
            'can_add' => $this->canAddManchettes,
            'session_libelle' => $this->sessionActive ? $this->sessionActive->type : null
        ];

        return view('livewire.manchette.manchettes-index', [
            'manchettes' => $manchettes,
            'sessionInfo' => $sessionInfo
        ]);
    }
}
