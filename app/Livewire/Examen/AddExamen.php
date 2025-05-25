<?php

namespace App\Livewire\Examen;

use App\Models\EC;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Salle;
use App\Models\SessionExam;
use App\Models\UE;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AddExamen extends Component
{
    // Propriétés de base pour l'examen
    public $session_id;
    public $niveau_id;
    public $parcours_id;
    public $duree = 120;
    public $note_eliminatoire;

    // Date et heure par défaut
    public $date_defaut;
    public $heure_defaut;
    public $salle_defaut;

    // Propriétés pour gérer les EC
    public $selectedEcs = [];    // IDs des ECs sélectionnés
    public $usedEcIds = [];      // ECs déjà utilisés dans d'autres examens
    public $groupedEcs = [];     // ECs groupés par UE

    // Propriétés pour dates/heures/salles spécifiques par EC
    public $ecDates = [];
    public $ecHours = [];
    public $ecSalles = [];

    // Propriétés pour les options avancées
    public $useSpecificDates = false;  // Détermine si on utilise des dates spécifiques par EC
    public $useSpecificSalles = false; // Détermine si on utilise des salles spécifiques par EC

    // Propriétés pour contexte et UI
    public $showParcours = false;
    public $niveauInfo = null;
    public $parcoursInfo = null;
    public $sessionInfo = null;

    protected $rules = [
        'selectedEcs' => 'required|array|min:1', // Au moins un EC doit être sélectionné
        'selectedEcs.*' => 'exists:ecs,id',
        'session_id' => 'required|exists:session_exams,id',
        'niveau_id' => 'required|exists:niveaux,id',
        'parcours_id' => 'nullable|exists:parcours,id',
        'date_defaut' => 'required|date',
        'heure_defaut' => 'required',
        'salle_defaut' => 'required|exists:salles,id',
        'duree' => 'required|integer|min:15',
        'note_eliminatoire' => 'nullable|numeric|min:0|max:20',
    ];

    /**
     * Initialisation du composant
     */
    public function mount($niveau = null, $parcour = null)
    {
        // Initialisation des valeurs par défaut
        $this->date_defaut = now()->format('Y-m-d');
        $this->heure_defaut = '08:00';

        // Récupération de la première salle disponible
        $firstSalle = Salle::first();
        $this->salle_defaut = $firstSalle ? $firstSalle->id : null;

        // Récupération automatique de la session active courante
        $this->autoSelectSession();

        // Chargement du niveau
        if ($niveau) {
            $this->niveau_id = $niveau;
            $this->loadNiveauInfo();
            $this->updateShowParcours();
        }

        // Chargement du parcours
        if ($parcour) {
            $this->parcours_id = $parcour;
            $this->loadParcoursInfo();
        }

        // Charger tous les ECs disponibles si un niveau est sélectionné
        if ($this->niveau_id) {
            $this->loadAvailableEcs();
        }
    }

    /**
     * Sélectionne automatiquement la session active courante
     */
    private function autoSelectSession()
    {
        try {
            // Récupère la session active et courante de l'année universitaire active
            $session = SessionExam::where('is_active', true)
                            ->where('is_current', true)
                            ->whereHas('anneeUniversitaire', function($q) {
                                $q->where('is_active', true);
                            })
                            ->first();

            // Si une session est trouvée, l'utiliser
            if ($session) {
                $this->session_id = $session->id;
                $this->sessionInfo = [
                    'id' => $session->id,
                    'nom' => $session->nom ?? $session->type,
                    'code' => $session->code ?? '',
                    'date_start' => $session->date_start->format('d/m/Y'),
                    'date_end' => $session->date_end->format('d/m/Y')
                ];
                return;
            }

            // Sinon, prendre la première session active de l'année active
            $fallbackSession = SessionExam::where('is_active', true)
                                    ->whereHas('anneeUniversitaire', function($q) {
                                        $q->where('is_active', true);
                                    })
                                    ->orderBy('date_start', 'desc')
                                    ->first();

            if ($fallbackSession) {
                $this->session_id = $fallbackSession->id;
                $this->sessionInfo = [
                    'id' => $fallbackSession->id,
                    'nom' => $fallbackSession->nom ?? $fallbackSession->type,
                    'code' => $fallbackSession->code ?? '',
                    'date_start' => $fallbackSession->date_start->format('d/m/Y'),
                    'date_end' => $fallbackSession->date_end->format('d/m/Y')
                ];
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la sélection automatique de la session: ' . $e->getMessage());
        }
    }

    /**
     * Charge les informations du niveau sélectionné
     */
    private function loadNiveauInfo()
    {
        try {
            $niveau = Niveau::find($this->niveau_id);
            if ($niveau) {
                $this->niveauInfo = [
                    'id' => $niveau->id,
                    'nom' => $niveau->nom,
                    'abr' => $niveau->abr,
                    'has_parcours' => $niveau->has_parcours
                ];
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des données du niveau: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Charge les informations du parcours sélectionné
     */
    private function loadParcoursInfo()
    {
        try {
            $parcours = Parcour::find($this->parcours_id);
            if ($parcours) {
                $this->parcoursInfo = [
                    'id' => $parcours->id,
                    'nom' => $parcours->nom,
                    'abr' => $parcours->abr
                ];
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des données du parcours: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Nettoie une valeur d'entrée
     */
    private function cleanInputValue($value)
    {
        // Si la valeur est un tableau (comme dans niveau_id[value]=1)
        if (is_array($value) && isset($value['value'])) {
            return $value['value'];
        }
        // Si c'est un autre type de tableau, prendre la première valeur
        if (is_array($value) && count($value) > 0) {
            return $value[0];
        }
        return $value;
    }

    /**
     * Charge tous les ECs disponibles et utilisés, groupés par UE
     */
    public function loadAvailableEcs()
    {
        try {
            $niveau_id = $this->cleanInputValue($this->niveau_id);
            $parcours_id = $this->cleanInputValue($this->parcours_id);

            // 1. Récupérer les ECs déjà utilisés dans d'autres examens
            $this->usedEcIds = \DB::table('examen_ec')
                ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $niveau_id)
                ->when($parcours_id, function($query) use ($parcours_id) {
                    $query->where('examens.parcours_id', $parcours_id);
                })
                ->pluck('ec_id')
                ->toArray();

            // 2. Récupérer toutes les UEs du niveau/parcours
            $ueQuery = UE::where('niveau_id', $niveau_id);
            if ($parcours_id) {
                $ueQuery->where(function($q) use ($parcours_id) {
                    $q->where('parcours_id', $parcours_id)
                      ->orWhereNull('parcours_id');
                });
            }
            $ues = $ueQuery->orderBy('nom')->get();

            // 3. Construire les groupes d'ECs par UE
            $this->groupedEcs = [];
            foreach ($ues as $ue) {
                $ecs = EC::where('ue_id', $ue->id)
                    ->orderBy('nom')
                    ->get();

                // Ajouter ce groupe uniquement s'il y a des ECs
                if ($ecs->isNotEmpty()) {
                    $this->groupedEcs[] = [
                        'ue' => $ue,
                        'ecs' => $ecs
                    ];

                    // Initialiser les dates/heures/salles par défaut pour tous les ECs
                    foreach ($ecs as $ec) {
                        if (!in_array($ec->id, $this->usedEcIds) && !isset($this->ecDates[$ec->id])) {
                            $this->ecDates[$ec->id] = $this->date_defaut;
                            $this->ecHours[$ec->id] = $this->heure_defaut;
                            $this->ecSalles[$ec->id] = $this->salle_defaut;
                        }
                    }
                }
            }

            Log::info('ECs groupés chargés', [
                'nbGroupes' => count($this->groupedEcs),
                'ecUtilisés' => count($this->usedEcIds)
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des ECs disponibles: ' . $e->getMessage());
            $this->groupedEcs = [];
        }
    }

    /**
     * Mise à jour du flag showParcours en fonction du niveau
     */
    public function updateShowParcours()
    {
        if (!$this->niveau_id) {
            $this->showParcours = false;
            return;
        }

        try {
            $niveau_id = $this->cleanInputValue($this->niveau_id);
            $niveau = Niveau::find($niveau_id);
            $this->showParcours = $niveau ? $niveau->has_parcours : false;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de showParcours: ' . $e->getMessage());
            $this->showParcours = false;
        }
    }

    /**
     * Mettre à jour les dates/heures/salles pour tous les ECs sélectionnés
     */
    public function updateAllEcDatesHoursSalles()
    {
        foreach ($this->selectedEcs as $ecId) {
            if (!in_array($ecId, $this->usedEcIds)) {
                $this->ecDates[$ecId] = $this->date_defaut;
                $this->ecHours[$ecId] = $this->heure_defaut;
                $this->ecSalles[$ecId] = $this->salle_defaut;
            }
        }
    }

    /**
     * Mettre à jour les dates spécifiques quand la date par défaut change
     */
    public function updatedDateDefaut()
    {
        if (!$this->useSpecificDates) {
            $this->updateAllEcDatesHoursSalles();
        }
    }

    /**
     * Mettre à jour les heures spécifiques quand l'heure par défaut change
     */
    public function updatedHeureDefaut()
    {
        if (!$this->useSpecificDates) {
            $this->updateAllEcDatesHoursSalles();
        }
    }

    /**
     * Mettre à jour les salles spécifiques quand la salle par défaut change
     */
    public function updatedSalleDefaut()
    {
        if (!$this->useSpecificSalles) {
            $this->updateAllEcDatesHoursSalles();
        }
    }

    /**
     * Basculer l'option d'utilisation de dates spécifiques par EC
     */
    public function toggleUseSpecificDates()
    {
        $this->useSpecificDates = !$this->useSpecificDates;

        if (!$this->useSpecificDates) {
            // Si on désactive les dates spécifiques, on réinitialise toutes les dates
            $this->updateAllEcDatesHoursSalles();
        }
    }

    /**
     * Basculer l'option d'utilisation de salles spécifiques par EC
     */
    public function toggleUseSpecificSalles()
    {
        $this->useSpecificSalles = !$this->useSpecificSalles;

        if (!$this->useSpecificSalles) {
            // Si on désactive les salles spécifiques, on réinitialise toutes les salles
            $this->updateAllEcDatesHoursSalles();
        }
    }

    /**
     * Sélectionner tous les ECs disponibles (non utilisés)
     */
    public function selectAllAvailableEcs()
    {
        $this->selectedEcs = [];

        foreach ($this->groupedEcs as $group) {
            foreach ($group['ecs'] as $ec) {
                if (!in_array($ec->id, $this->usedEcIds)) {
                    $this->selectedEcs[] = $ec->id;

                    // Initialiser les dates/heures/salles
                    if (!isset($this->ecDates[$ec->id])) {
                        $this->ecDates[$ec->id] = $this->date_defaut;
                        $this->ecHours[$ec->id] = $this->heure_defaut;
                        $this->ecSalles[$ec->id] = $this->salle_defaut;
                    }
                }
            }
        }
    }

    /**
     * Désélectionner tous les ECs
     */
    public function deselectAllEcs()
    {
        $this->selectedEcs = [];
    }

    /**
     * Copier la date, l'heure et la salle à tous les ECs sélectionnés
     */
    public function copyDateTimeSalleToAllEcs()
    {
        foreach ($this->selectedEcs as $ecId) {
            if (!in_array($ecId, $this->usedEcIds)) {
                $this->ecDates[$ecId] = $this->date_defaut;
                $this->ecHours[$ecId] = $this->heure_defaut;
                $this->ecSalles[$ecId] = $this->salle_defaut;
            }
        }
    }

    /**
     * Méthode appelée quand le niveau change
     */
    public function updatedNiveauId()
    {
        $this->updateShowParcours();
        $this->selectedEcs = [];
        $this->ecDates = [];
        $this->ecHours = [];
        $this->ecSalles = [];
        $this->loadAvailableEcs();
    }

    /**
     * Méthode appelée quand le parcours change
     */
    public function updatedParcoursId()
    {
        $this->selectedEcs = [];
        $this->ecDates = [];
        $this->ecHours = [];
        $this->ecSalles = [];
        $this->loadAvailableEcs();
    }

    /**
     * Méthode appelée quand les ECs sélectionnés changent
     */
    public function updatedSelectedEcs()
    {
        // Initialiser les dates/heures/salles pour les nouveaux ECs sélectionnés
        foreach ($this->selectedEcs as $ecId) {
            if (!isset($this->ecDates[$ecId])) {
                $this->ecDates[$ecId] = $this->date_defaut;
                $this->ecHours[$ecId] = $this->heure_defaut;
                $this->ecSalles[$ecId] = $this->salle_defaut;
            }
        }
    }

    /**
     * Sauvegarder l'examen avec les ECs sélectionnés
     */
    public function save()
    {
        $this->validate();

        try {
            // Vérifier si la session est définie
            if (empty($this->session_id)) {
                $this->autoSelectSession();
                if (empty($this->session_id)) {
                    throw new \Exception('Aucune session d\'examen active n\'a été trouvée.');
                }
            }

            // Nettoyer les valeurs
            $session_id = $this->cleanInputValue($this->session_id);
            $niveau_id = $this->cleanInputValue($this->niveau_id);
            $parcours_id = $this->cleanInputValue($this->parcours_id);

            // Traiter note_eliminatoire
            $note_eliminatoire = null;
            if (!empty($this->note_eliminatoire)) {
                $note_eliminatoire = $this->cleanInputValue($this->note_eliminatoire);
            }

            // Vérifier que les ECs sélectionnés ne sont pas déjà utilisés
            $alreadyUsedEcs = array_intersect($this->selectedEcs, $this->usedEcIds);
            if (!empty($alreadyUsedEcs)) {
                $ecNames = EC::whereIn('id', $alreadyUsedEcs)->pluck('nom')->toArray();
                throw new \Exception('Certains EC sont déjà utilisés dans d\'autres examens: ' . implode(', ', $ecNames));
            }

            // Créer l'examen
            $examen = Examen::create([
                'session_id' => $session_id,
                'niveau_id' => $niveau_id,
                'parcours_id' => $parcours_id,
                'duree' => (int)$this->duree,
                'note_eliminatoire' => $note_eliminatoire,
            ]);

            // Vérifier qu'il y a au moins un EC à attacher
            if (count($this->selectedEcs) === 0) {
                throw new \Exception('Aucun EC n\'a été sélectionné.');
            }

            // Attacher les ECs à l'examen avec leurs dates, heures et salles spécifiques
            foreach ($this->selectedEcs as $ecId) {
                $date = $this->useSpecificDates ? $this->ecDates[$ecId] : $this->date_defaut;
                $heure = $this->useSpecificDates ? $this->ecHours[$ecId] : $this->heure_defaut;
                $salle = $this->useSpecificSalles ? $this->ecSalles[$ecId] : $this->salle_defaut;

                $examen->ecs()->attach($ecId, [
                    'date_specifique' => $date,
                    'heure_specifique' => $heure,
                    'salle_id' => $salle,
                ]);
            }

            toastr()->success('Examen créé avec succès.');

            // Redirection
            return redirect()->route('examens.index', [
                'niveau' => $niveau_id,
                'parcours' => $parcours_id,
                'step' => 'examens'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'examen: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Rendu de la vue
     */
    public function render()
    {
        $niveaux = Niveau::where('is_active', true)->get();
        $parcours = $this->niveau_id ? Parcour::where('niveau_id', $this->cleanInputValue($this->niveau_id))->get() : [];
        $salles = Salle::orderBy('nom')->get();

        // Requête pour les sessions d'examen
        $sessions = SessionExam::where('is_active', true)
                    ->whereHas('anneeUniversitaire', function($q) {
                        $q->where('is_active', true);
                    })
                    ->orderBy('date_start', 'desc')
                    ->get();

        // Si sessionInfo n'est pas encore défini
        if (empty($this->sessionInfo) && !empty($this->session_id)) {
            $currentSession = $sessions->firstWhere('id', $this->session_id);
            if ($currentSession) {
                $this->sessionInfo = [
                    'id' => $currentSession->id,
                    'nom' => $currentSession->nom ?? $currentSession->type,
                    'date_start' => $currentSession->date_start->format('d/m/Y'),
                    'date_end' => $currentSession->date_end->format('d/m/Y')
                ];
            }
        }

        // Session courante
        $currentSession = $sessions->where('is_current', true)->first();
        if (!$currentSession && $sessions->isNotEmpty()) {
            $currentSession = $sessions->first();
        }

        return view('livewire.examen.add-examen', [
            'niveaux' => $niveaux,
            'parcours' => $parcours,
            'sessions' => $sessions,
            'salles' => $salles,
            'currentSession' => $currentSession,
        ]);
    }
}
