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
use App\Models\SessionExam;
use App\Models\CodeAnonymat;
use Livewire\WithPagination;
use App\Models\PresenceExamen;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs  
 * @property \Illuminate\Support\Collection $etudiantsSansCopies
 */
class CopiesIndex extends Component
{
    use WithPagination;

    public $check_matricule = '';
    public $etudiant_trouve = null;
    public $manchette_trouvee = null;
    public $copie_existante = null;

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
    public $selectedCodeBase = '';

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
    
    public $presenceData = null;
    public $presenceEnregistree = false;

    public function updatedCheckMatricule()
    {
        $this->etudiant_trouve = null;
        $this->manchette_trouvee = null;
        $this->code_anonymat = '';
        $this->copie_existante = null; // NOUVEAU
        
        if (!empty($this->check_matricule)) {
            // Chercher l'étudiant
            $etudiant = Etudiant::where('matricule', $this->check_matricule)->first();
            
            if ($etudiant) {
                // Chercher sa manchette pour cette session/EC
                $manchette = Manchette::where('etudiant_id', $etudiant->id)
                    ->where('examen_id', $this->examen_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->whereHas('codeAnonymat', function($q) {
                        $q->where('ec_id', $this->ec_id);
                    })
                    ->first();
                    
                if ($manchette) {
                    $this->etudiant_trouve = $etudiant;
                    $this->manchette_trouvee = $manchette;
                    $this->code_anonymat = $manchette->codeAnonymat->code_complet;
                    
                    // ✅ NOUVEAU : Vérifier si une copie existe déjà
                    $copieExistante = Copie::where('examen_id', $this->examen_id)
                        ->where('ec_id', $this->ec_id)
                        ->where('session_exam_id', $this->session_exam_id)
                        ->where('code_anonymat_id', $manchette->code_anonymat_id)
                        ->whereNull('deleted_at')
                        ->first();
                    
                    if ($copieExistante) {
                        $this->copie_existante = $copieExistante;
                        toastr()->warning("⚠️ Une note ({$copieExistante->note}/20) existe déjà pour cet étudiant dans cette matière !");
                    }
                } else {
                    toastr()->error("❌ Aucune manchette trouvée pour ce matricule dans cette matière/session");
                }
            } 
            
            // else {
            //     toastr()->error("❌ Matricule introuvable");
            // }
        }
    }

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

        // ✅ NOUVEAU : Récupérer le nombre d'étudiants présents depuis les données de présence
        $this->checkPresenceEnregistree();
        $etudiantsPresents = $this->presenceData ? $this->presenceData->etudiants_presents : $this->totalEtudiantsCount;

        // Mettre à jour les compteurs
        $this->ecs = $this->ecs->map(function($ec) use ($copiesCounts, $userCopiesCounts, $etudiantsPresents) {
            $copiesCount = $copiesCounts[$ec->id] ?? 0;
            $userCount = $userCopiesCounts[$ec->id] ?? 0;

            $ec->copies_count = $copiesCount;
            $ec->user_copies_count = $userCount;
            $ec->has_copies = $copiesCount > 0;
            
            // ✅ CORRIGÉ : Utiliser le nombre d'étudiants présents
            $ec->pourcentage = $etudiantsPresents > 0 ?
                round(($copiesCount / $etudiantsPresents) * 100, 1) : 0;

            return $ec;
        });

        \Log::info('Compteurs mis à jour avec présence existante', [
            'copies_counts' => $copiesCounts,
            'user_counts' => $userCopiesCounts,
            'etudiants_presents' => $etudiantsPresents,
            'presence_data_exists' => $this->presenceData !== null
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

        // CORRIGÉ : Log pour debug - utiliser count() au lieu de count($array)
        \Log::info('État étudiants chargé avec nouvelle logique (Copies)', [
            'session_type' => $session->type,
            'total_etudiants_disponibles' => $etudiants->count(),
            'avec_copies' => count($etudiantsAvecCopiesIds), // CORRIGÉ : count() pour array
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
            $this->selectedCodeBase = '';
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
            'selectedCodeBase', 'currentEcName', 'currentSalleName',
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
        $this->selectedCodeBase = '';
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
        $this->selectedCodeBase = '';
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
        $this->selectedCodeBase = ''; // MODIFIÉ
        $this->currentEcName = '';
        $this->currentSalleName = '';
        $this->currentEcDate = '';
        $this->currentEcHeure = '';

        if ($this->salle_id) {
            $salle = Salle::find($this->salle_id);
            if ($salle) {
                $this->currentSalleName = $salle->nom ?? '';
            }

            // MODIFIÉ : Inclure code_base dans la requête
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
                    'examen_ec.code_base', // AJOUTÉ
                    DB::raw('MIN(examen_ec.examen_id) as examen_id'),
                    DB::raw('MIN(examen_ec.date_specifique) as date_specifique'),
                    DB::raw('MIN(examen_ec.heure_specifique) as heure_specifique')
                )
                ->groupBy(
                    'ecs.id', 'ecs.nom', 'ecs.abr', 'ecs.coefficient', 'ecs.ue_id', 'ecs.enseignant',
                    'ues.nom', 'ues.abr', 'examen_ec.code_base' // AJOUTÉ
                )
                ->orderBy('ues.nom')
                ->orderBy('ecs.nom')
                ->get();

            if ($ecsUniques->isNotEmpty()) {
                $this->examen_id = $ecsUniques->first()->examen_id;

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
                        'code_base' => $ec->code_base, // AJOUTÉ
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

                $this->calculateCopiesCountsForAllEcs();
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
                $baseCode = $this->selectedCodeBase;
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
        $this->selectedCodeBase = '';

        // Charger l'état des étudiants
        $this->chargerEtatEtudiants();

        // ✅ NOUVEAU : Vérifier la présence après sélection EC
        if ($this->ec_id && $this->ec_id !== 'all') {
            $this->checkPresenceEnregistree();
        }

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

                // ✅ NOUVEAU : Utiliser les étudiants présents pour "toutes les matières"
                $etudiantsPresents = $this->presenceData ? $this->presenceData->etudiants_presents : $baseEtudiantsCount;

                // Calculer le nombre total de copies pour toutes les matières DANS LA SESSION ACTIVE
                $this->totalCopiesCount = Copie::where('examen_id', $this->examen_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->count();

                // Copies saisies par l'utilisateur actuel dans la session active
                $this->userCopiesCount = Copie::where('examen_id', $this->examen_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->where('saisie_par', Auth::id())
                    ->count();

                // ✅ NOUVEAU : Calculer le nombre total basé sur les présents × matières
                $nombreMatieres = count($ecIds);
                if ($nombreMatieres > 0) {
                    $this->totalEtudiantsCount = $etudiantsPresents * $nombreMatieres;
                } else {
                    $this->totalEtudiantsCount = $etudiantsPresents;
                }
            }
        }
        // Cas normal: une matière spécifique
        else if ($this->ec_id && $this->salle_id && $this->session_exam_id) {
            // ✅ NOUVEAU : Utiliser le nombre d'étudiants présents au lieu du total théorique
            $etudiantsPresents = $this->presenceData ? $this->presenceData->etudiants_presents : $baseEtudiantsCount;
            $this->totalEtudiantsCount = $etudiantsPresents;

            // Essayer de trouver l'EC dans la collection chargée
            $ec = $this->ecs->firstWhere('id', $this->ec_id);
            if ($ec) {
                $this->currentEcName = $ec->nom;
                $this->currentEcDate = $ec->date_formatted ?? '';
                $this->currentEcHeure = $ec->heure_formatted ?? '';
                $this->selectedCodeBase = $ec->code_base ?? ''; 
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
                        ->select('ecs.nom', 'examen_ec.date_specifique', 'examen_ec.heure_specifique', 'examen_ec.code_base')
                        ->first();

                    if ($ecInfo) {
                        $this->currentEcName = $ecInfo->nom;
                        $this->currentEcDate = $ecInfo->date_specifique ? \Carbon\Carbon::parse($ecInfo->date_specifique)->format('d/m/Y') : '';
                        $this->currentEcHeure = $ecInfo->heure_specifique ? \Carbon\Carbon::parse($ecInfo->heure_specifique)->format('H:i') : '';
                        $this->selectedCodeBase = $ecInfo->code_base ?? '';
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

        // ✅ NOUVEAU : Log pour debug avec données de présence
        \Log::info('updatedEcId - Compteurs mis à jour avec présence', [
            'session_type' => $session ? $session->type : 'inconnue',
            'base_etudiants_count' => $baseEtudiantsCount,
            'etudiants_presents' => $this->presenceData ? $this->presenceData->etudiants_presents : 0,
            'total_etudiants_count' => $this->totalEtudiantsCount,
            'total_copies_count' => $this->totalCopiesCount,
            'user_copies_count' => $this->userCopiesCount,
            'ec_id' => $this->ec_id,
            'presence_enregistree' => $this->presenceEnregistree
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
        // Vérifications préalables (garde le code existant)
        if (!$this->autoOpenModal || !$this->canAddCopies || !$this->ec_id || $this->ec_id === 'all') {
            return;
        }

        if (isset($this->editingCopieId) || $this->showCopieModal) {
            return;
        }

        // ✅ CORRECTION : Vérifier la présence D'ABORD
        $this->checkPresenceEnregistree();
        
        if (!$this->presenceEnregistree || !$this->presenceData) {
            return; // Pas d'auto-ouverture sans données de présence
        }

        // ✅ UTILISER LES ÉTUDIANTS PRÉSENTS au lieu du total
        $etudiantsPresents = $this->presenceData->etudiants_presents;
        
        if ($etudiantsPresents <= 0) {
            return; // Pas d'auto-ouverture si aucun étudiant présent
        }

        $totalCopiesCount = Copie::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->whereNull('deleted_at')
            ->count();

        // ✅ CONDITION CORRIGÉE : 0 copies ET étudiants présents > 0
        if ($totalCopiesCount === 0 && $etudiantsPresents > 0) {
            $this->prepareDirectModalOpening();
            
            toastr()->info("✨ Première saisie pour {$etudiantsPresents} étudiant(s) présent(s).");
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
        if (empty($this->selectedCodeBase)) {
            $codeBase = DB::table('examen_ec')
                ->where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('salle_id', $this->salle_id)
                ->value('code_base');

            if ($codeBase) {
                $this->selectedCodeBase = $codeBase;
            } else {
                \Log::warning('Code base non défini dans examen_ec pour copie', [
                    'examen_id' => $this->examen_id,
                    'ec_id' => $this->ec_id,
                    'salle_id' => $this->salle_id
                ]);
                // Ne pas faire de fallback, laisser vide pour forcer la configuration
                $this->selectedCodeBase = '';
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
        // Vérifications préalables (code existant)
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

        // Vérifications de session (code existant)
        $session = SessionExam::find($this->session_exam_id);
        if (!$session) {
            $this->message = 'Session introuvable';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // ✅ NOUVELLE VÉRIFICATION : Présence obligatoire
        $this->checkPresenceEnregistree();
        
        if (!$this->presenceEnregistree) {
            toastr()->warning('❌ Aucune données de présence trouvées ! Veuillez d\'abord saisir les manchettes pour enregistrer la présence.');
            return;
        }

        // ✅ NOUVEAU : Logique de comptage basée sur les étudiants présents
        $etudiantsPresents = $this->presenceData->etudiants_presents;
        
        // Compter les copies existantes pour cette session
        $copiesCount = Copie::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('session_exam_id', $this->session_exam_id)
            ->whereNull('deleted_at')
            ->count();

        // ✅ NOUVEAU : Vérifier la limite basée sur les étudiants présents
        if ($copiesCount >= $etudiantsPresents) {
            $sessionType = ucfirst($this->getCurrentSessionType());
            $taux = round($this->presenceData->taux_presence);
            toastr()->warning("✅ Toutes les notes ont déjà été saisies ! ({$copiesCount}/{$etudiantsPresents} étudiants présents, {$taux}% de présence en session {$sessionType})");
            return;
        }

        // Charger la liste des étudiants sans copie
        $this->etudiantsSansNote = $this->etudiantsSansCopies;

        // S'assurer que le code de salle est défini
        if (empty($this->selectedCodeBase)) {
            // Essayer de récupérer le code_base depuis examen_ec
            $codeBase = DB::table('examen_ec')
                ->where('examen_id', $this->examen_id)
                ->where('ec_id', $this->ec_id)
                ->where('salle_id', $this->salle_id)
                ->value('code_base');

            if ($codeBase) {
                $this->selectedCodeBase = $codeBase;
            } else {
                $this->message = 'Aucun code de base défini pour cette matière dans cet examen. Veuillez contacter l\'administrateur.';
                $this->messageType = 'error';
                toastr()->error($this->message);
                return;
            }
        }

        // Générer le code d'anonymat pour cette session
        $this->generateNextCodeAnonymatForSession();

        // Réinitialiser les champs
        $this->note = null;
        $this->note_confirmation = null;
        $this->code_anonymat_confirmation = '';
        $this->editingCopieId = null;

        // ✅ NOUVEAU : Message informatif avec données de présence
        $restantes = $etudiantsPresents - $copiesCount;
        $sessionType = ucfirst($this->getCurrentSessionType());
        $taux = round($this->presenceData->taux_presence);
        $doubleVerifStatus = $this->enableDoubleVerification ? ' (Double vérification activée)' : '';
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
                throw new \Exception('La session d\'examen sélectionnée n\'est pas active ou courante.');
            }

            // S'assurer que l'examen_id est cohérent
            if (!$this->examen_id) {
                throw new \Exception('Aucun examen sélectionné.');
            }

            // Vérifier l'examen
            $examen = Examen::find($this->examen_id);
            if (!$examen) {
                throw new \Exception("L'examen sélectionné n'existe pas.");
            }

            // ✅ CORRECTION CRITIQUE : Vérifier la présence et utiliser comme limite
            $this->checkPresenceEnregistree();
            
            if (!$this->presenceEnregistree || !$this->presenceData) {
                throw new \Exception('❌ Aucune données de présence trouvées ! Impossible de saisir des notes sans données de présence.');
            }

            // ✅ NOUVELLE LOGIQUE : Utiliser le nombre d'étudiants présents comme limite absolue
            $etudiantsPresents = $this->presenceData->etudiants_presents;
            
            if ($etudiantsPresents <= 0) {
                throw new \Exception('❌ Aucun étudiant présent enregistré ! Vérifiez les données de présence.');
            }

            // Vérifier la cohérence examen/EC/salle
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

            // ✅ VÉRIFICATION DE LIMITE CORRIGÉE : En mode AJOUT seulement
            if (!isset($this->editingCopieId)) {
                // ✅ LOGIQUE CORRIGÉE : Vérifier contre le nombre d'étudiants présents
                if ($copiesCount >= $etudiantsPresents) {
                    $sessionType = $sessionExam->type === 'Normale' ? 'normale' : 'rattrapage';
                    $taux = round($this->presenceData->taux_presence);
                    $this->message = "❌ Limite atteinte ! Vous avez déjà saisi {$copiesCount} notes pour {$etudiantsPresents} étudiants présents ({$taux}% de présence en session {$sessionType}).";
                    $this->messageType = 'error';
                    toastr()->error($this->message);
                    
                    // ✅ NE PAS FERMER LA MODAL pour que l'utilisateur voie le message
                    return;
                }
            }

            // Créer ou récupérer le code d'anonymat
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

            } else if (isset($this->editingCopieId)) {
                // Modifier une copie existante
                $copie = Copie::find($this->editingCopieId);
                if (!$copie) {
                    throw new \Exception('La copie à modifier est introuvable.');
                }

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

            } else {
                // ✅ DOUBLE VÉRIFICATION DE SÉCURITÉ avant création
                $copiesCountRealTime = Copie::where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->whereNull('deleted_at')
                    ->count();
                
                if ($copiesCountRealTime >= $etudiantsPresents) {
                    $sessionType = $sessionExam->type === 'Normale' ? 'normale' : 'rattrapage';
                    throw new \Exception("❌ Limite atteinte en temps réel ! {$copiesCountRealTime} notes pour {$etudiantsPresents} étudiants présents en session {$sessionType}. Une autre personne a peut-être ajouté une note.");
                }

                // Vérifier qu'une copie n'existe pas déjà pour cette session
                $existingCopie = Copie::where('examen_id', $this->examen_id)
                    ->where('code_anonymat_id', $codeAnonymat->id)
                    ->where('ec_id', $this->ec_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existingCopie) {
                    throw new \Exception("Ce code d'anonymat est déjà utilisé pour cette matière dans cette session.");
                }

                // Créer une nouvelle copie
                $nouvelleCopie = Copie::create([
                    'examen_id' => $this->examen_id,
                    'session_exam_id' => $this->session_exam_id,
                    'code_anonymat_id' => $codeAnonymat->id,
                    'ec_id' => $this->ec_id,
                    'note' => $this->note,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
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

            // ✅ GESTION DE LA MODALE APRÈS SAUVEGARDE CORRIGÉE
            if (!isset($this->editingCopieId)) {
                // Mode ajout : préparer pour la prochaine saisie
                $this->note = '';
                // Réinitialiser le champ matricule après sauvegarde
                $this->check_matricule = '';
                $this->etudiant_trouve = null;
                $this->manchette_trouvee = null;

                // ✅ VÉRIFIER S'IL RESTE DE LA PLACE POUR UNE AUTRE COPIE (basé sur présence)
                $copiesCountAfterSave = Copie::where('examen_id', $this->examen_id)
                    ->where('ec_id', $this->ec_id)
                    ->where('session_exam_id', $this->session_exam_id)
                    ->whereNull('deleted_at')
                    ->count();

                if ($copiesCountAfterSave >= $etudiantsPresents) {
                    // ✅ LIMITE ATTEINTE : Fermer la modal et informer
                    $sessionType = $sessionExam->type === 'Normale' ? 'normale' : 'rattrapage';
                    $taux = round($this->presenceData->taux_presence);
                    $this->message = "🎯 Saisie terminée ! Toutes les notes ont été saisies pour les étudiants présents en session {$sessionType} ({$copiesCountAfterSave}/{$etudiantsPresents}, {$taux}% de présence).";
                    $this->showCopieModal = false;
                    
                    // ✅ NOUVEAU : Émettre un événement de fin de saisie
                    $this->dispatch('saisie-notes-terminee', [
                        'total_notes' => $copiesCountAfterSave,
                        'etudiants_presents' => $etudiantsPresents,
                        'taux_presence' => $taux,
                        'session_type' => $sessionType,
                        'matiere' => $this->currentEcName
                    ]);
                    
                    return;
                }

                // Générer le prochain code d'anonymat pour cette session
                $baseCode = $this->selectedCodeBase;
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

                // ✅ NOUVEAU : Messages avec compteur de présence
                $restantes = $etudiantsPresents - $copiesCountAfterSave;
                toastr()->success("Note enregistrée ! {$restantes} notes restantes pour les étudiants présents"); 
            } 
             
            $this->reset(['code_anonymat', 'note', 'editingCopieId']);
            $this->showCopieModal = false;
            toastr()->success($this->message);

            // Rafraîchir la liste des matières
            $this->calculateCopiesCountsForAllEcs();

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
        $this->code_anonymat = strtoupper(trim($value));
        
        if (!empty($this->code_anonymat)) {
            if (!preg_match('/^[A-Za-z]+\d+$/', $this->code_anonymat)) {
                $this->addError('code_anonymat', 'Format invalide. Utilisez des lettres suivies de chiffres (ex: TA1).');
            } else {
                $this->resetErrorBag('code_anonymat');
                
                // MODIFIÉ : Vérifier la cohérence avec le code_base
                if (!str_starts_with($this->code_anonymat, strtoupper($this->selectedCodeBase))) {
                    $this->addError('code_anonymat', "Le code devrait commencer par {$this->selectedCodeBase}");
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
            'note_confirmation',
            'check_matricule', // AJOUTER
            'etudiant_trouve', // AJOUTER
            'manchette_trouvee' // AJOUTER
        ]);
        $this->resetErrorBag();
    }

    /**
     * NOUVELLE MÉTHODE : Générer le prochain code d'anonymat pour la session courante
     */
    private function generateNextCodeAnonymatForSession()
    {
        // MODIFIÉ : Récupérer le code_base pour cette matière (comme dans ManchettesIndex)
        if (!$this->ec_id || !$this->salle_id || !$this->examen_id) {
            throw new \Exception("Paramètres manquants pour générer le code d'anonymat");
        }

        // Récupérer le code_base depuis examen_ec (EXACTEMENT comme dans ManchettesIndex)
        $codeBase = DB::table('examen_ec')
            ->where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('salle_id', $this->salle_id)
            ->value('code_base');

        if (empty($codeBase)) {
            throw new \Exception("Aucun code_base trouvé pour cette matière. Veuillez définir un code lors de la création de l'examen.");
        }

        $this->selectedCodeBase = $codeBase;
        \Log::info('Code_base utilisé pour génération (Copies)', [
            'ec_id' => $this->ec_id,
            'code_base' => $codeBase
        ]);

        $sessionId = $this->getCurrentSessionId();

        // Récupérer tous les codes utilisés dans cette session pour cette EC
        $codesUtilises = Copie::join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('copies.examen_id', $this->examen_id)
            ->where('copies.ec_id', $this->ec_id)
            ->where('copies.session_exam_id', $sessionId)
            ->where('codes_anonymat.code_complet', 'like', $codeBase . '%')
            ->whereNull('copies.deleted_at')
            ->pluck('codes_anonymat.code_complet')
            ->toArray();

        // Extraire les numéros utilisés
        $numerosUtilises = [];
        foreach ($codesUtilises as $code) {
            if (preg_match('/^' . preg_quote($codeBase) . '(\d+)$/', $code, $matches)) {
                $numerosUtilises[] = (int)$matches[1];
            }
        }

        // Trouver le premier numéro disponible
        $nextNumber = 1;
        while (in_array($nextNumber, $numerosUtilises)) {
            $nextNumber++;
        }

        $proposedCode = $codeBase . $nextNumber;

        // Vérification finale
        $maxAttempts = 50;
        $attempts = 0;

        while ($this->codeExistsInCurrentSession($proposedCode) && $attempts < $maxAttempts) {
            $nextNumber++;
            $proposedCode = $codeBase . $nextNumber;
            $attempts++;
        }

        if ($attempts >= $maxAttempts) {
            throw new \Exception("Impossible de générer un code d'anonymat unique après {$maxAttempts} tentatives.");
        }

        $this->code_anonymat = $proposedCode;

        \Log::info('Code d\'anonymat généré avec code_base (Copies)', [
            'session_id' => $sessionId,
            'ec_id' => $this->ec_id,
            'base_code' => $codeBase,
            'numero_choisi' => $nextNumber,
            'code_final' => $proposedCode
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
        $this->chargerEtatEtudiants();
        $etudiantsRestants = count($this->etudiantsSansCopies ?? []);
        if ($etudiantsRestants > 0) {
            $this->messageType = 'warning';
            $this->showForceCloseButton = true;
            toastr()->warning("Il reste {$etudiantsRestants} étudiant(s) sans note !");
            return; 
        }
        $this->forceCloseModal();
    }

    // Nouvelle propriété à ajouter en haut de votre classe
    public $showForceCloseButton = false;

    // Méthode pour forcer la fermeture après confirmation
    public function forceCloseModal()
    {
        $this->showCopieModal = false;
        $this->showForceCloseButton = false;
        $this->autoOpenModal = false; // AJOUTER CETTE LIGNE
        $this->resetFormFields();
        
        // Réinitialiser complètement les champs de recherche
        $this->reset(['check_matricule', 'etudiant_trouve', 'manchette_trouvee', 'code_anonymat', 'note']);
        
        toastr()->info('Saisie fermée');
    }

    public function render()
    {
        // Mise à jour des informations de session
        $this->updateSessionInfo();

        // ✅ NOUVEAU : Vérifier la présence si examen et salle sont sélectionnés
        if ($this->examen_id && $this->salle_id) {
            $this->checkPresenceEnregistree();
        }

        Log::debug('Rendering CopiesIndex with Presence', [
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id,
            'salle_id' => $this->salle_id,
            'examen_id' => $this->examen_id,
            'ec_id' => $this->ec_id,
            'search' => $this->search,
            'session_id' => $this->getCurrentSessionId(),
            'session_type' => $this->getCurrentSessionType(),
            'presence_enregistree' => $this->presenceEnregistree,
        ]);

        // Validation des IDs (code existant)
        if ($this->examen_id && !Examen::find($this->examen_id)) {
            Log::warning('Invalid examen_id', ['examen_id' => $this->examen_id]);
            $this->examen_id = null;
        }
        if ($this->ec_id && $this->ec_id !== 'all' && !EC::find($this->ec_id)) {
            Log::warning('Invalid ec_id', ['ec_id' => $this->ec_id]);
            $this->ec_id = null;
        }

        // Requête des copies (code existant)
        if ($this->niveau_id && $this->parcours_id && $this->salle_id && $this->examen_id && $this->session_exam_id) {
            $query = Copie::where('examen_id', $this->examen_id)
                        ->where('session_exam_id', $this->session_exam_id);

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
                    ->select('copies.*');
            } else {
                $query->orderBy($this->sortField, $this->sortDirection);
            }

            $copies = $query->with(['codeAnonymat', 'ec', 'utilisateurSaisie', 'sessionExam'])
                ->paginate($this->perPage);

            Log::debug('Copies retrieved with presence check', [
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
                'session_id' => $this->session_exam_id,
                'total' => $copies->total(),
                'presence_enregistree' => $this->presenceEnregistree,
            ]);
        } else {
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

        // Charger l'état des étudiants (code existant)
        if ($this->ec_id && $this->ec_id !== 'all' && $this->examen_id) {
            $this->chargerEtatEtudiants();
        }

        // Créer le tableau sessionInfo (code existant)
        $sessionInfo = [
            'message' => $this->sessionInfo,
            'active' => $this->sessionActive,
            'active_id' => $this->sessionActiveId,
            'type' => $this->sessionType,
            'can_add' => $this->canAddCopies,
            'session_libelle' => $this->sessionActive ? $this->sessionActive->type : null
        ];

        // ✅ NOUVEAU : Retour avec les nouvelles données de présence
        return view('livewire.copie.copies-index', [
            'copies' => $copies,
            'sessionInfo' => $sessionInfo,
            // NOUVELLES DONNÉES DE PRÉSENCE
            'presenceStats' => $this->getPresenceStats(),
            'canStartSaisie' => $this->canStartCopiesSaisie(),
            'presenceStatusMessage' => $this->getPresenceStatusMessage(),
        ]);
    }

    public function checkPresenceEnregistree()
    {
        if (!$this->examen_id || !$this->salle_id) {
            $this->presenceEnregistree = false;
            $this->presenceData = null;
            return;
        }

        $sessionId = $this->getCurrentSessionId();
        if (!$sessionId) {
            $this->presenceEnregistree = false;
            $this->presenceData = null;
            return;
        }

        // ✅ UTILISER LA MÊME LOGIQUE QUE DANS ManchettesIndex
        $this->presenceData = PresenceExamen::forExamen($this->examen_id, $sessionId, $this->salle_id)
            ->when($this->ec_id && $this->ec_id !== 'all', function ($query) {
                return $query->forEc($this->ec_id);
            })
            ->first();

        $this->presenceEnregistree = $this->presenceData !== null;

        \Log::info('Vérification présence pour copies', [
            'examen_id' => $this->examen_id,
            'salle_id' => $this->salle_id,
            'ec_id' => $this->ec_id,
            'session_id' => $sessionId,
            'presence_trouvee' => $this->presenceEnregistree
        ]);
    }

    /**
     * ✅ MÉTHODE SIMPLE : Récupérer les stats de présence (lecture seule)
     */
    public function getPresenceStats()
    {
        if (!$this->presenceData) {
            return null;
        }

        return [
            'presents' => $this->presenceData->etudiants_presents,
            'absents' => $this->presenceData->etudiants_absents,
            'total' => $this->presenceData->total_etudiants,
            'taux_presence' => $this->presenceData->taux_presence,
            'ecart_attendu' => $this->presenceData->ecart_attendu,
            'total_attendu' => $this->presenceData->total_attendu,
        ];
    }

    /**
     * ✅ MÉTHODE SIMPLE : Vérifier si la saisie peut commencer
     */
    public function canStartCopiesSaisie()
    {
        if (!$this->examen_id || !$this->salle_id || !$this->ec_id || $this->ec_id === 'all') {
            return false;
        }

        if (!$this->canAddCopies) {
            return false;
        }

        $this->checkPresenceEnregistree();
        return $this->presenceEnregistree;
    }



        /**
     * ✅ MÉTHODE SIMPLE : Message d'état de présence
     */
    public function getPresenceStatusMessage()
    {
        if (!$this->examen_id || !$this->salle_id || !$this->ec_id || $this->ec_id === 'all') {
            return null;
        }

        if (!$this->presenceEnregistree) {
            return [
                'type' => 'warning',
                'icon' => 'ni-info',
                'message' => 'Aucune données de présence trouvées. Veuillez d\'abord saisir les manchettes pour enregistrer la présence.'
            ];
        }

        if ($this->presenceData) {
            $taux = $this->presenceData->taux_presence;
            if ($taux >= 75) {
                return [
                    'type' => 'success',
                    'icon' => 'ni-check-circle',
                    'message' => "Excellente présence ({$taux}%) - Vous pouvez saisir les notes des étudiants présents."
                ];
            } elseif ($taux >= 50) {
                return [
                    'type' => 'info',
                    'icon' => 'ni-users',
                    'message' => "Présence correcte ({$taux}%) - Vous pouvez saisir les notes."
                ];
            } else {
                return [
                    'type' => 'warning',
                    'icon' => 'ni-alert-fill',
                    'message' => "Faible présence ({$taux}%) - Vérifiez que tous les étudiants présents ont bien une manchette."
                ];
            }
        }

        return null;
    }



    /**
     * NOUVELLE MÉTHODE : Récupérer les stats de présence pour une matière spécifique
     * À ajouter dans votre classe CopiesIndex
     */
    public function getPresenceStatsParMatiere($ecId)
    {
        if (!$this->examen_id || !$this->salle_id) {
            return null;
        }

        $sessionId = $this->getCurrentSessionId();
        if (!$sessionId) {
            return null;
        }

        // Chercher d'abord une présence spécifique à cette matière
        $presenceSpecifique = PresenceExamen::forExamen($this->examen_id, $sessionId, $this->salle_id)
            ->forEc($ecId)
            ->first();

        if ($presenceSpecifique) {
            \Log::info('Présence spécifique trouvée pour EC (Copies)', [
                'ec_id' => $ecId,
                'presents' => $presenceSpecifique->etudiants_presents
            ]);
            
            return [
                'presents' => $presenceSpecifique->etudiants_presents,
                'absents' => $presenceSpecifique->etudiants_absents,
                'total' => $presenceSpecifique->total_etudiants,
                'taux_presence' => $presenceSpecifique->taux_presence,
                'ecart_attendu' => $presenceSpecifique->ecart_attendu,
                'total_attendu' => $presenceSpecifique->total_attendu,
                'type' => 'specifique'
            ];
        }

        // Si pas de présence spécifique, utiliser la présence globale
        $presenceGlobale = PresenceExamen::forExamen($this->examen_id, $sessionId, $this->salle_id)
            ->whereNull('ec_id') // ec_id = NULL pour présence globale
            ->first();

        if ($presenceGlobale) {
            \Log::info('Présence globale utilisée pour EC (Copies)', [
                'ec_id' => $ecId,
                'presents' => $presenceGlobale->etudiants_presents,
                'type' => 'globale'
            ]);
            
            return [
                'presents' => $presenceGlobale->etudiants_presents,
                'absents' => $presenceGlobale->etudiants_absents,
                'total' => $presenceGlobale->total_etudiants,
                'taux_presence' => $presenceGlobale->taux_presence,
                'ecart_attendu' => $presenceGlobale->ecart_attendu,
                'total_attendu' => $presenceGlobale->total_attendu,
                'type' => 'globale_reutilisee'
            ];
        }

        // Aucune présence trouvée
        \Log::info('Aucune présence trouvée pour EC (Copies)', [
            'ec_id' => $ecId,
            'examen_id' => $this->examen_id,
            'salle_id' => $this->salle_id,
            'session_id' => $sessionId
        ]);

        return null;
    }


}
