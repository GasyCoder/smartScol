<?php

namespace App\Livewire\Examen;

use App\Models\EC;
use App\Models\UE;
use App\Models\Salle;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AddExamen extends Component
{
    // Propriétés de base pour l'examen
    public $niveau_id;
    public $parcours_id;
    public $duree = 120;
    public $note_eliminatoire;
    public $ecCodes = [];

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

    // Propriétés pour la gestion des conflits
    public $conflitsSalles = [];      // Liste des conflits détectés
    public $showConflitsModal = false; // Affichage du modal des conflits
    public $creneauxLibres = [];      // Suggestions de créneaux libres

    protected $rules = [
        'selectedEcs' => 'required|array|min:1', // Au moins un EC doit être sélectionné
        'selectedEcs.*' => 'exists:ecs,id',
        'niveau_id' => 'required|exists:niveaux,id',
        'parcours_id' => 'nullable|exists:parcours,id',
        'date_defaut' => 'required|date',
        'heure_defaut' => 'required',
        'salle_defaut' => 'required|exists:salles,id',
        'duree' => 'required|integer|min:15',
        'note_eliminatoire' => 'nullable|numeric|min:0|max:20',
        'ecCodes.*' => 'nullable|string|max:20',
    ];

    protected $messages = [
        'ecCodes.*.max' => 'Le code ne peut pas dépasser 20 caractères.',
    ];

    /**
     * Initialisation du composant
     */
    public function mount($niveau = null, $parcour = null)
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Accès non autorisé.');
        }
        // Initialisation des valeurs par défaut
        $this->date_defaut = now()->format('Y-m-d');
        $this->heure_defaut = '08:00';
        $this->duree = 120; // S'assurer que c'est un entier

        // Récupération de la première salle disponible
        $firstSalle = Salle::first();
        $this->salle_defaut = $firstSalle ? $firstSalle->id : null;

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
            $ues = $ueQuery->orderBy('id', 'asc')->get();

            // 3. Construire les groupes d'ECs par UE
            $this->groupedEcs = [];
            foreach ($ues as $ue) {
                $ecs = EC::where('ue_id', $ue->id)
                    ->orderBy('id', 'asc')
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
     * LOGIQUE INTELLIGENTE : Si même date/salle, répartir automatiquement les heures
     */
    public function updateAllEcDatesHoursSalles()
    {
        if (empty($this->selectedEcs)) {
            return;
        }

        // Si on n'utilise pas les dates/salles spécifiques et qu'on a plusieurs ECs
        if (!$this->useSpecificDates && !$this->useSpecificSalles && count($this->selectedEcs) > 1) {
            $this->repartirAutomatiquementCreneaux();
        } else {
            // Logique classique pour un seul EC ou si spécifique activé
            foreach ($this->selectedEcs as $ecId) {
                if (!in_array($ecId, $this->usedEcIds)) {
                    $this->ecDates[$ecId] = $this->date_defaut;
                    $this->ecHours[$ecId] = $this->heure_defaut;
                    $this->ecSalles[$ecId] = $this->salle_defaut;
                }
            }
        }

        // Vérifier les conflits après mise à jour
        $this->verifierConflitsEnTempsReel();
    }

    /**
     * Répartit automatiquement les créneaux pour éviter les conflits
     */
    private function repartirAutomatiquementCreneaux()
    {
        $heureActuelle = \Carbon\Carbon::parse($this->heure_defaut);
        $dureeMinutes = (int) $this->duree;
        $pauseMinutes = 30; // Pause entre les examens

        foreach ($this->selectedEcs as $index => $ecId) {
            if (!in_array($ecId, $this->usedEcIds)) {
                $this->ecDates[$ecId] = $this->date_defaut;
                $this->ecSalles[$ecId] = $this->salle_defaut;

                // Calculer l'heure pour cet EC
                if ($index === 0) {
                    // Premier EC : utiliser l'heure par défaut
                    $this->ecHours[$ecId] = $this->heure_defaut;
                } else {
                    // ECs suivants : calculer l'heure en évitant les conflits
                    $heureProposee = $this->calculerProchainCreneauLibre(
                        $this->salle_defaut,
                        $this->date_defaut,
                        $heureActuelle->format('H:i'),
                        $dureeMinutes
                    );
                    $this->ecHours[$ecId] = $heureProposee;
                    $heureActuelle = \Carbon\Carbon::parse($this->date_defaut . ' ' . $heureProposee)->addMinutes($dureeMinutes + $pauseMinutes);
                }
            }
        }
    }

    /**
     * Calcule le prochain créneau libre pour une salle donnée
     */
    private function calculerProchainCreneauLibre($salleId, $date, $heureDebutSouhaitee, $dureeMinutes)
    {
        $heureActuelle = \Carbon\Carbon::parse($date . ' ' . $heureDebutSouhaitee);
        $heureLimite = \Carbon\Carbon::parse($date . ' 18:00'); // Limite à 18h

        while ($heureActuelle->copy()->addMinutes($dureeMinutes)->lte($heureLimite)) {
            $heureTest = $heureActuelle->format('H:i');

            // Vérifier si ce créneau est libre (en excluant les ECs déjà planifiés dans cette session)
            if ($this->estCreneauLibreLocalement($salleId, $date, $heureTest, $dureeMinutes)) {
                return $heureTest;
            }

            // Passer au créneau suivant (par tranches de 30 minutes)
            $heureActuelle->addMinutes(30);
        }

        // Si aucun créneau libre trouvé, retourner l'heure souhaitée (il y aura un conflit détecté)
        return $heureDebutSouhaitee;
    }

    /**
     * Vérifie si un créneau est libre en tenant compte des planifications en cours
     */
    private function estCreneauLibreLocalement($salleId, $date, $heure, $dureeMinutes)
    {
        // 1. Vérifier contre les examens existants en base
        if (!Examen::isSalleDisponible($salleId, $date, $heure, $dureeMinutes)) {
            return false;
        }

        // 2. Vérifier contre les ECs déjà planifiés dans cette session
        $heureDebut = \Carbon\Carbon::parse($date . ' ' . $heure);
        $heureFin = $heureDebut->copy()->addMinutes($dureeMinutes);

        foreach ($this->ecHours as $ecId => $ecHeure) {
            if (in_array($ecId, $this->selectedEcs) &&
                $this->ecSalles[$ecId] == $salleId &&
                $this->ecDates[$ecId] == $date) {

                $heureDebutExistant = \Carbon\Carbon::parse($date . ' ' . $ecHeure);
                $heureFinExistant = $heureDebutExistant->copy()->addMinutes($dureeMinutes);

                // Vérifier le chevauchement
                if ($heureDebut->lt($heureFinExistant) && $heureFin->gt($heureDebutExistant)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Mettre à jour les dates spécifiques quand la date par défaut change
     */
    public function updatedDateDefaut()
    {
        if (!$this->useSpecificDates) {
            $this->updateAllEcDatesHoursSalles();
        }
        $this->verifierConflitsEnTempsReel();
    }

    /**
     * Mettre à jour les heures spécifiques quand l'heure par défaut change
     */
    public function updatedHeureDefaut()
    {
        if (!$this->useSpecificDates) {
            $this->updateAllEcDatesHoursSalles();
        }
        $this->verifierConflitsEnTempsReel();
    }

    /**
     * Mettre à jour les salles spécifiques quand la salle par défaut change
     */
    public function updatedSalleDefaut()
    {
        if (!$this->useSpecificSalles) {
            $this->updateAllEcDatesHoursSalles();
        }
        $this->verifierConflitsEnTempsReel();
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
        $this->verifierConflitsEnTempsReel();
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
        $this->verifierConflitsEnTempsReel();
    }

    /**
     * Sélectionner tous les ECs disponibles (non utilisés)
     * LOGIQUE INTELLIGENTE : Répartition automatique des créneaux
     */
    public function selectAllAvailableEcs()
    {
        $this->selectedEcs = [];

        foreach ($this->groupedEcs as $group) {
            foreach ($group['ecs'] as $ec) {
                if (!in_array($ec->id, $this->usedEcIds)) {
                    $this->selectedEcs[] = $ec->id;
                }
            }
        }

        // Répartition intelligente des créneaux
        $this->repartirAutomatiquementTousLesEcs();
        $this->verifierConflitsEnTempsReel();
    }

    /**
     * Répartit intelligemment tous les ECs sélectionnés
     */
    private function repartirAutomatiquementTousLesEcs()
    {
        if (empty($this->selectedEcs)) {
            return;
        }

        $dureeMinutes = (int) $this->duree;
        $pauseMinutes = 30;
        $heureActuelle = \Carbon\Carbon::parse($this->heure_defaut);

        // Si on n'utilise pas les paramètres spécifiques
        if (!$this->useSpecificDates && !$this->useSpecificSalles) {
            foreach ($this->selectedEcs as $index => $ecId) {
                $this->ecDates[$ecId] = $this->date_defaut;
                $this->ecSalles[$ecId] = $this->salle_defaut;

                if ($index === 0) {
                    // Premier EC : heure par défaut
                    $this->ecHours[$ecId] = $this->heure_defaut;
                } else {
                    // ECs suivants : calcul automatique
                    $heureProposee = $this->calculerProchainCreneauLibre(
                        $this->salle_defaut,
                        $this->date_defaut,
                        $heureActuelle->format('H:i'),
                        $dureeMinutes
                    );
                    $this->ecHours[$ecId] = $heureProposee;
                    $heureActuelle = \Carbon\Carbon::parse($this->date_defaut . ' ' . $heureProposee)
                                          ->addMinutes($dureeMinutes + $pauseMinutes);
                }
            }
        } else {
            // Si paramètres spécifiques activés, initialisation classique
            foreach ($this->selectedEcs as $ecId) {
                if (!isset($this->ecDates[$ecId])) {
                    $this->ecDates[$ecId] = $this->date_defaut;
                    $this->ecHours[$ecId] = $this->heure_defaut;
                    $this->ecSalles[$ecId] = $this->salle_defaut;
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
        $this->conflitsSalles = [];
    }

    /**
     * Copier la date, l'heure et la salle à tous les ECs sélectionnés
     * LOGIQUE INTELLIGENTE : Si même date/salle, répartir les heures automatiquement
     */
    public function copyDateTimeSalleToAllEcs()
    {
        if (empty($this->selectedEcs)) {
            return;
        }

        // Si plusieurs ECs et même date/salle, répartition intelligente
        if (count($this->selectedEcs) > 1 && !$this->useSpecificDates && !$this->useSpecificSalles) {
            $this->repartirAutomatiquementCreneaux();
        } else {
            // Logique classique : copie directe
            foreach ($this->selectedEcs as $ecId) {
                if (!in_array($ecId, $this->usedEcIds)) {
                    $this->ecDates[$ecId] = $this->date_defaut;
                    $this->ecHours[$ecId] = $this->heure_defaut;
                    $this->ecSalles[$ecId] = $this->salle_defaut;
                }
            }
        }

        $this->verifierConflitsEnTempsReel();
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
        $this->conflitsSalles = [];
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
        $this->conflitsSalles = [];
        $this->loadAvailableEcs();
    }

    /**
     * Méthode appelée quand les ECs sélectionnés changent
     * LOGIQUE INTELLIGENTE : Répartition automatique pour éviter les conflits
     */
    public function updatedSelectedEcs()
    {
        // Nettoyer les données des ECs désélectionnés
        $this->ecDates = array_intersect_key($this->ecDates, array_flip($this->selectedEcs));
        $this->ecHours = array_intersect_key($this->ecHours, array_flip($this->selectedEcs));
        $this->ecSalles = array_intersect_key($this->ecSalles, array_flip($this->selectedEcs));
        $this->ecCodes = array_intersect_key($this->ecCodes, array_flip($this->selectedEcs)); // NOUVEAU

        // Initialiser les nouveaux ECs sélectionnés
        $nouveauxEcs = [];
        foreach ($this->selectedEcs as $ecId) {
            if (!isset($this->ecDates[$ecId])) {
                $nouveauxEcs[] = $ecId;
            }
        }

        if (!empty($nouveauxEcs)) {
            if (count($this->selectedEcs) > 1 && !$this->useSpecificDates && !$this->useSpecificSalles) {
                $this->repartirAutomatiquementTousLesEcs();
            } else {
                foreach ($nouveauxEcs as $ecId) {
                    $this->ecDates[$ecId] = $this->date_defaut;
                    $this->ecHours[$ecId] = $this->heure_defaut;
                    $this->ecSalles[$ecId] = $this->salle_defaut;
                    $this->ecCodes[$ecId] = ''; // NOUVEAU: Initialiser à vide pour saisie manuelle
                }
            }
        }

        $this->verifierConflitsEnTempsReel();
    }

     /**
     * NOUVEAU: Validation des codes pour éviter les doublons
     */
    public function validateCodes()
    {
        $codes = array_filter($this->ecCodes, function($code_base) {
            return !empty(trim($code_base));
        });
        
        // Vérifier les doublons dans la saisie actuelle
        $duplicates = array_diff_assoc($codes, array_unique($codes));
        
        if (!empty($duplicates)) {
            return [
                'valid' => false,
                'message' => 'Codes en doublon détectés : ' . implode(', ', array_unique($duplicates))
            ];
        }

        return ['valid' => true, 'message' => ''];
    }
    

    /**
     * NOUVEAU: Vérifier si un code est déjà utilisé dans d'autres examens
     */
    public function checkCodeExists($code_base, $ecId)
    {
        if (empty($code_base)) return false;
        
        // Vérifier dans tous les examens existants
        return DB::table('examen_ec')
            ->where('code_base', $code_base)
            ->where('ec_id', '!=', $ecId)
            ->exists();
    }

    /**
     * NOUVEAU: Méthode pour vérifier les codes en temps réel
     */
    public function updatedEcCodes($value, $ecId)
    {
        if (!empty($value) && $this->checkCodeExists($value, $ecId)) {
            // Réinitialiser le code s'il existe déjà
            $this->ecCodes[$ecId] = '';
            toastr()->warning("Le code '{$value}' est déjà utilisé. Veuillez choisir un autre code.");
        }
    }


    /**
     * =====================================
     * GESTION DES CONFLITS DE SALLES
     * =====================================
     */

    /**
     * Vérification des conflits en temps réel
     */
    public function verifierConflitsEnTempsReel()
    {
        if (empty($this->selectedEcs)) {
            $this->conflitsSalles = [];
            return;
        }

        // Préparer les données des ECs
        $ecsData = [];
        foreach ($this->selectedEcs as $ecId) {
            $salleId = $this->useSpecificSalles ? ($this->ecSalles[$ecId] ?? null) : $this->salle_defaut;
            $date = $this->useSpecificDates ? ($this->ecDates[$ecId] ?? $this->date_defaut) : $this->date_defaut;
            $heure = $this->useSpecificDates ? ($this->ecHours[$ecId] ?? $this->heure_defaut) : $this->heure_defaut;

            if ($salleId && $date && $heure) {
                $ecsData[] = [
                    'ec_id' => $ecId,
                    'salle_id' => $salleId,
                    'date' => $date,
                    'heure' => $heure,
                ];
            }
        }

        // Vérifier les conflits - Convertir la durée en entier
        $dureeMinutes = (int) $this->duree;
        $this->conflitsSalles = Examen::verifierConflitsSalles($ecsData, $dureeMinutes);
    }

    /**
     * Méthodes appelées lors des changements de dates/heures/salles spécifiques
     */
    public function updatedEcDates()
    {
        $this->verifierConflitsEnTempsReel();
    }

    public function updatedEcHours()
    {
        $this->verifierConflitsEnTempsReel();
    }

    public function updatedEcSalles()
    {
        $this->verifierConflitsEnTempsReel();
    }

    /**
     * Méthode appelée quand la durée change
     */
    public function updatedDuree()
    {
        // S'assurer que la durée est un entier
        $this->duree = (int) $this->duree;
        $this->verifierConflitsEnTempsReel();
    }

    /**
     * Afficher le modal des conflits avec suggestions
     */
    public function afficherModalConflits()
    {
        $this->showConflitsModal = true;
        $this->genererSuggestionsCreneaux();
    }

    /**
     * Fermer le modal des conflits
     */
    public function fermerModalConflits()
    {
        $this->showConflitsModal = false;
        $this->creneauxLibres = [];
    }

    /**
     * Générer des suggestions de créneaux libres pour les conflits
     */
    public function genererSuggestionsCreneaux()
    {
        $this->creneauxLibres = [];

        foreach ($this->conflitsSalles as $conflit) {
            $dureeMinutes = (int) $this->duree; // Conversion en entier
            $suggestions = Examen::suggererCreneauxLibres(
                $conflit['salle_id'],
                $conflit['date'],
                $dureeMinutes
            );

            $this->creneauxLibres[$conflit['ec_id']] = [
                'ec_nom' => $conflit['ec_nom'],
                'salle_nom' => $conflit['salle_nom'],
                'date' => $conflit['date'],
                'suggestions' => array_slice($suggestions, 0, 5) // Limite à 5 suggestions
            ];
        }
    }

    /**
     * Appliquer une suggestion de créneau
     */
    public function appliquerSuggestion($ecId, $nouvelleHeure)
    {
        if ($this->useSpecificDates) {
            $this->ecHours[$ecId] = $nouvelleHeure;
        } else {
            $this->heure_defaut = $nouvelleHeure;
            $this->updateAllEcDatesHoursSalles();
        }

        $this->verifierConflitsEnTempsReel();

        // Si plus de conflits, fermer le modal
        if (empty($this->conflitsSalles)) {
            $this->fermerModalConflits();
            toastr()->success('Conflit résolu ! Nouveau créneau appliqué.');
        }
    }

    /**
     * Nouvelle méthode : Réorganiser automatiquement tous les créneaux
     */
    public function reorganiserCreneauxAutomatiquement()
    {
        if (empty($this->selectedEcs)) {
            toastr()->warning('Aucun EC sélectionné pour la réorganisation.');
            return;
        }

        $this->repartirAutomatiquementTousLesEcs();
        $this->verifierConflitsEnTempsReel();

        if (empty($this->conflitsSalles)) {
            toastr()->success('Créneaux réorganisés automatiquement sans conflit !');
        } else {
            toastr()->info('Créneaux réorganisés, mais des conflits persistent. Veuillez les résoudre manuellement.');
        }
    }

    /**
     * Nouvelle méthode : Suggérer des salles alternatives pour résoudre les conflits
     */
    public function suggererSallesAlternatives()
    {
        $suggestions = [];

        foreach ($this->conflitsSalles as $conflit) {
            $sallesAlternatives = Salle::where('id', '!=', $conflit['salle_id'])->get();

            foreach ($sallesAlternatives as $salle) {
                if (Examen::isSalleDisponible($salle->id, $conflit['date'], $conflit['heure'], $this->duree)) {
                    $suggestions[$conflit['ec_id']][] = [
                        'salle_id' => $salle->id,
                        'salle_nom' => $salle->nom,
                        'capacite' => $salle->capacite ?? 'N/A'
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Appliquer une salle alternative
     */
    public function appliquerSalleAlternative($ecId, $nouvelleSalleId)
    {
        if ($this->useSpecificSalles) {
            $this->ecSalles[$ecId] = $nouvelleSalleId;
        } else {
            $this->salle_defaut = $nouvelleSalleId;
            $this->updateAllEcDatesHoursSalles();
        }

        $this->verifierConflitsEnTempsReel();

        if (empty($this->conflitsSalles)) {
            $salle = Salle::find($nouvelleSalleId);
            toastr()->success("Conflit résolu ! Salle changée pour : {$salle->nom}");
        }
    }

    /**
     * Sauvegarder l'examen - MESSAGES D'ERREUR OPTIMISÉS
     */
    public function save()
    {
        $this->validate();

        try {
            // NOUVEAU: Validation des codes
            $codeValidation = $this->validateCodes();
            if (!$codeValidation['valid']) {
                toastr()->error($codeValidation['message']);
                return;
            }

            // Vérification finale des conflits
            $this->verifierConflitsEnTempsReel();

            if (!empty($this->conflitsSalles)) {
                $conflitsInternes = count(array_filter($this->conflitsSalles, fn($c) => $c['type'] === 'interne'));
                $conflitsExistants = count($this->conflitsSalles) - $conflitsInternes;

                if ($conflitsInternes > 0 && $conflitsExistants === 0) {
                    toastr()->error("❌ {$conflitsInternes} conflit(s) détecté(s) : Plusieurs matières ont la même heure dans la même salle. Utilisez 'Réorganiser automatiquement' ou modifiez les heures manuellement.");
                } elseif ($conflitsExistants > 0 && $conflitsInternes === 0) {
                    toastr()->error("❌ {$conflitsExistants} conflit(s) avec des examens existants. Changez les heures ou salles.");
                } else {
                    toastr()->error("❌ {$conflitsInternes} conflit(s) interne(s) + {$conflitsExistants} conflit(s) avec examens existants. Réorganisez la planification.");
                }

                $this->afficherModalConflits();
                return;
            }

            // Nettoyer les valeurs
            $niveau_id = $this->cleanInputValue($this->niveau_id);
            $parcours_id = $this->cleanInputValue($this->parcours_id);
            $note_eliminatoire = !empty($this->note_eliminatoire) ? $this->cleanInputValue($this->note_eliminatoire) : null;

            // Vérifier ECs déjà utilisés
            $alreadyUsedEcs = array_intersect($this->selectedEcs, $this->usedEcIds);
            if (!empty($alreadyUsedEcs)) {
                $ecNames = EC::whereIn('id', $alreadyUsedEcs)->pluck('nom')->toArray();
                throw new \Exception('Certains EC sont déjà utilisés : ' . implode(', ', $ecNames));
            }

            if (count($this->selectedEcs) === 0) {
                throw new \Exception('Aucun EC sélectionné.');
            }

            // Créer l'examen
            $examen = Examen::create([
                'niveau_id' => $niveau_id,
                'parcours_id' => $parcours_id,
                'duree' => (int)$this->duree,
                'note_eliminatoire' => $note_eliminatoire,
            ]);

            // Attacher les ECs avec codes
            foreach ($this->selectedEcs as $ecId) {
                $date = $this->useSpecificDates ? $this->ecDates[$ecId] : $this->date_defaut;
                $heure = $this->useSpecificDates ? $this->ecHours[$ecId] : $this->heure_defaut;
                $salle = $this->useSpecificSalles ? $this->ecSalles[$ecId] : $this->salle_defaut;
                $code_base = !empty($this->ecCodes[$ecId]) ? trim($this->ecCodes[$ecId]) : null; // NOUVEAU

                $examen->ecs()->attach($ecId, [
                    'date_specifique' => $date,
                    'heure_specifique' => $heure,
                    'salle_id' => $salle,
                    'code_base' => $code_base, // NOUVEAU
                ]);
            }

            toastr()->success('✅ Examen créé avec succès !');

            return redirect()->route('examens.index', [
                'niveau' => $niveau_id,
                'parcours' => $parcours_id,
                'step' => 'examens'
            ]);
        } catch (\Exception $e) {
            toastr()->error('Erreur : ' . $e->getMessage());
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

        return view('livewire.examen.add-examen', [
            'niveaux' => $niveaux,
            'parcours' => $parcours,
            'salles' => $salles,
        ]);
    }
}
