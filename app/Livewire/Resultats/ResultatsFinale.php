<?php

namespace App\Livewire\Resultats;

use App\Models\UE;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\ResultatFinal;
use App\Services\ExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ResultatsExport;
use App\Models\AnneeUniversitaire;
use App\Models\DeliberationConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdmisDeliberationPDF;
use App\Models\ResultatFinalHistorique;
use Illuminate\Support\Facades\Storage;
use App\Exports\AdmisDeliberationExport;
use App\Services\CalculAcademiqueService;
use App\Services\DeliberationApplicationService;

class ResultatsFinale extends Component
{
    // PROPRIÉTÉS D'ÉTAT
    public $etape_actuelle = 'niveau';
    public $dois_afficher_resultats = false;
    public $peut_charger_resultats = false;
    public $nom_niveau_selectionne;
    public $nom_parcours_selectionne = 'Tous les parcours';
    public $nom_annee_selectionnee;
    public $showConfirmationModal = false; 

    // PROPRIÉTÉS FILTRES
    public $selectedNiveau;
    public $selectedParcours;
    public $selectedAnneeUniversitaire;
    public $niveaux;
    public $parcours;
    public $anneesUniversitaires;
    public $simulationResults = [];

    // PROPRIÉTÉS SESSIONS
    public $sessionNormale;
    public $sessionRattrapage;
    public $showSession2 = false;
    public $activeTab = 'session1';

    // PROPRIÉTÉS RÉSULTATS
    public $resultatsSession1 = [];
    public $resultatsSession2 = [];
    public $statistiquesSession1 = [];
    public $statistiquesSession2 = [];
    /** @var \Illuminate\Support\Collection */
    public $uesStructure; // ✅ Documentation du type

    // PROPRIÉTÉS DÉLIBÉRATION
    public $deliberationParams = [
        'session_type' => 'session1',
        'session_id' => null,
        'credits_admission_s1' => 60,
        'credits_admission_s2' => 40,
        'credits_redoublement_s2' => 20,
        'note_eliminatoire_bloque_s1' => true,
        'note_eliminatoire_exclusion_s2' => true
    ];
    public $showDeliberationModal = false;
    public $deliberationStatus = [];
    public $simulationDeliberation = [];
    public $simulationParams = [];

    // PROPRIÉTÉS EXPORT
    public $showExportModal = false;
    public $exportType = 'pdf';
    public $exportData = 'simulation';
    public $exportConfig = [
        'colonnes' => [
            'rang' => true,
            'nom_complet' => true,
            'matricule' => true,
            'moyenne' => true,
            'credits' => true,
            'decision' => true,
            'niveau' => false,
        ],
        'filtres' => [
            'decision_filter' => 'tous',
            'moyenne_min' => null,
            'moyenne_max' => null,
        ],
        'tri' => [
            'champ' => 'moyenne_generale',
            'ordre' => 'desc'
        ]
    ];

    protected $calculAcademiqueService;
    protected $queryString = ['selectedNiveau', 'selectedParcours', 'selectedAnneeUniversitaire'];
    protected $deliberationApplicationService;

    protected $rules = [
        'selectedNiveau' => 'required|exists:niveaux,id',
        'selectedAnneeUniversitaire' => 'required|exists:annees_universitaires,id',
    ];

    public function mount()
    {
        $this->calculAcademiqueService = new CalculAcademiqueService();
        $this->deliberationApplicationService = new DeliberationApplicationService($this->calculAcademiqueService);
        
        // Initialiser les collections
        $this->niveaux = collect();
        $this->parcours = collect();
        $this->anneesUniversitaires = collect();
        $this->uesStructure = collect(); // ✅ CHANGÉ de [] à collect()
        
        // ✅ AJOUT : Sauvegarder les valeurs avant setDefaultValues
        $savedNiveau = $this->selectedNiveau;
        $savedParcours = $this->selectedParcours;
        
        // Initialiser les noms affichés
        $this->nom_niveau_selectionne = null;
        $this->nom_parcours_selectionne = null;
        $this->nom_annee_selectionnee = null;
        
        $this->initializeData();
        $this->setDefaultValues();
        
        // ✅ AJOUT : Restaurer les valeurs après setDefaultValues
        if ($savedNiveau) {
            $this->selectedNiveau = $savedNiveau;
            
            // Charger les parcours pour ce niveau
            if ($savedNiveau) {
                try {
                    $niveau = Niveau::find($savedNiveau);
                    if ($niveau?->has_parcours) {
                        $this->parcours = Parcour::where('niveau_id', $savedNiveau)
                            ->where('is_active', true)
                            ->orderBy('id', 'asc')
                            ->get();
                            
                        // Restaurer le parcours s'il existe
                        if ($savedParcours && $this->parcours->where('id', $savedParcours)->isNotEmpty()) {
                            $this->selectedParcours = $savedParcours;
                        }
                    }
                    $this->loadUEStructure();
                } catch (\Exception $e) {
                    Log::error('Erreur restauration niveau/parcours: ' . $e->getMessage());
                }
            }
        }
        
        $this->updateNomsAffiches();
        $this->updateEtapeActuelle();
        $this->updatePeutChargerResultats();
    }


    
    public function initializeData()
    {
        try {
            $this->anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();
            $this->niveaux = Niveau::where('is_active', true)
                                    ->where('abr', '!=', 'PACES')
                                    ->orderBy('id', 'asc')->get();
        } catch (\Exception $e) {
            Log::error('Erreur initialisation données: ' . $e->getMessage());
            $this->anneesUniversitaires = collect();
            $this->niveaux = collect();
        }
    }

    public function setDefaultValues()
    {
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            $this->selectedAnneeUniversitaire = $anneeActive?->id;
            
            // Mettre à jour le nom de l'année sélectionnée
            if ($anneeActive) {
                $this->nom_annee_selectionnee = $anneeActive->libelle;
            }
            
            $this->loadSessions();
            $this->initializeSimulationParams();
        } catch (\Exception $e) {
            Log::error('Erreur valeurs par défaut: ' . $e->getMessage());
        }
    }
    
    private function updateNomsAffiches()
    {
        // Niveau
        $this->nom_niveau_selectionne = $this->selectedNiveau ? 
            $this->niveaux->where('id', $this->selectedNiveau)->first()?->nom : null;
        
        // Parcours - avec vérification que la collection parcours est chargée
        if ($this->selectedParcours) {
            // Si la collection parcours est vide, la recharger
            if ($this->parcours->isEmpty() && $this->selectedNiveau) {
                try {
                    $niveau = Niveau::find($this->selectedNiveau);
                    if ($niveau?->has_parcours) {
                        $this->parcours = Parcour::where('niveau_id', $this->selectedNiveau)
                            ->where('is_active', true)
                            ->orderBy('id', 'asc')
                            ->get();
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur rechargement parcours dans updateNomsAffiches: ' . $e->getMessage());
                }
            }
            
            $parcoursFound = $this->parcours->where('id', $this->selectedParcours)->first();
            $this->nom_parcours_selectionne = $parcoursFound?->nom ?: 'Parcours introuvable';
        } else {
            $this->nom_parcours_selectionne = 'Tous les parcours';
        }
        
        // Année
        $this->nom_annee_selectionnee = $this->selectedAnneeUniversitaire ? 
            $this->anneesUniversitaires->where('id', $this->selectedAnneeUniversitaire)->first()?->libelle : null;
    }

    public function selectNiveau($niveauId = null)
    {
        $ancienNiveau = $this->selectedNiveau;
        $this->selectedNiveau = $niveauId;
        
        // ✅ MODIFICATION : Reset parcours seulement si changement de niveau
        if ($ancienNiveau !== $niveauId) {
            $this->selectedParcours = null;
            $this->resetResults();
        }
        
        if ($niveauId) {
            try {
                $niveau = Niveau::find($niveauId);
                if ($niveau?->has_parcours) {
                    $this->parcours = Parcour::where('niveau_id', $niveauId)
                        ->where('is_active', true)
                        ->orderBy('id', 'asc')
                        ->get();
                } else {
                    $this->parcours = collect();
                    $this->selectedParcours = null;
                }
                $this->loadUEStructure();
            } catch (\Exception $e) {
                Log::error('Erreur sélection niveau: ' . $e->getMessage());
                $this->parcours = collect();
            }
        } else {
            $this->parcours = collect();
        }
        
        $this->updateNomsAffiches();
        $this->updateEtapeActuelle();
        $this->updatePeutChargerResultats();
    }

    public function selectParcours($parcoursId = null)
    {
        
        $this->selectedParcours = $parcoursId;
        $this->resetResults();
        
        $this->updateNomsAffiches();
        $this->updateEtapeActuelle();
        $this->updatePeutChargerResultats();
    }


    public function chargerResultats()
    {
        if (!$this->peut_charger_resultats) {
            toastr()->error('Configuration incomplète pour charger les résultats.');
            return;
        }
        
        try {
            $this->loadUEStructure();
            
            // ✅ Maintenant fonctionne car $this->uesStructure est toujours une Collection
            if ($this->uesStructure->isEmpty()) {
                toastr()->warning('Aucune UE trouvée pour ce niveau/parcours.');
                return;
            }
            
            $this->loadSessions();
            $this->checkSession2Availability();
            $this->loadResultats();
            $this->updateEtapeActuelle();
            
            $totalSession1 = count($this->resultatsSession1);
            $totalSession2 = count($this->resultatsSession2);
            
            $message = "Résultats chargés ! Session 1: {$totalSession1} étudiant(s)";
            if ($totalSession2 > 0) {
                $message .= ", Session 2: {$totalSession2} étudiant(s)";
            }
            
            toastr()->success($message);
            
        } catch (\Exception $e) {
            Log::error('Erreur chargement résultats: ' . $e->getMessage());
            toastr()->error('Erreur lors du chargement des résultats: ' . $e->getMessage());
        }
    }


    private function updateEtapeActuelle()
    {
        if (!$this->selectedNiveau) {
            $this->etape_actuelle = 'niveau';
        } elseif ($this->parcours->isNotEmpty() && $this->selectedParcours === null) {
            $this->etape_actuelle = 'parcours';
        } elseif (!empty($this->resultatsSession1) || !empty($this->resultatsSession2)) {
            $this->etape_actuelle = 'resultats';
            $this->dois_afficher_resultats = true;
        } else {
            $this->etape_actuelle = 'pret_charger';
            $this->dois_afficher_resultats = false;
        }
    }

    private function updatePeutChargerResultats()
    {
        $this->peut_charger_resultats = $this->selectedNiveau && 
            $this->selectedAnneeUniversitaire && 
            ($this->parcours->isEmpty() || $this->selectedParcours !== null);
    }

    private function resetResults()
    {
        $this->resultatsSession1 = [];
        $this->resultatsSession2 = [];
        $this->statistiquesSession1 = [];
        $this->statistiquesSession2 = [];
        $this->simulationDeliberation = [];
        $this->sessionNormale = null;
        $this->sessionRattrapage = null;
        $this->showSession2 = false;
        $this->dois_afficher_resultats = false;
    }

    private function loadSessions()
    {
        if (!$this->selectedAnneeUniversitaire) {
            return;
        }

        try {
            $this->sessionNormale = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
                ->where('type', 'Normale')->first();

            $this->sessionRattrapage = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
                ->where('type', 'Rattrapage')->first();

            $this->checkSession2Availability();
        } catch (\Exception $e) {
            Log::error('Erreur chargement sessions: ' . $e->getMessage());
        }
    }

    private function checkSession2Availability()
    {
        if (!$this->selectedNiveau || !$this->sessionRattrapage) {
            $this->showSession2 = false;
            return;
        }

        try {
            $this->showSession2 = ResultatFinal::where('session_exam_id', $this->sessionRattrapage->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->selectedNiveau);
                    if ($this->selectedParcours) {
                        $q->where('parcours_id', $this->selectedParcours);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->exists();
        } catch (\Exception $e) {
            Log::error('Erreur vérification session 2: ' . $e->getMessage());
            $this->showSession2 = false;
        }
    }

    public function loadResultats()
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            return;
        }

        try {
            $this->resultatsSession1 = $this->loadResultatsForSession($this->sessionNormale);
            
            if ($this->showSession2 && $this->sessionRattrapage) {
                $this->resultatsSession2 = $this->loadResultatsForSession($this->sessionRattrapage);
            }

            $this->calculateStatistics();
            $this->updateEtapeActuelle();
        } catch (\Exception $e) {
            Log::error('Erreur chargement résultats: ' . $e->getMessage());
        }
    }



    private function loadResultatsForSession($session)
    {
        if (!$session) return [];

        try {
            if (!$this->calculAcademiqueService) {
                $this->calculAcademiqueService = new CalculAcademiqueService();
            }
            
            $calculService = $this->calculAcademiqueService;

            // ✅ CORRECTION : Trouver TOUS les examens pour ce niveau/parcours
            $examenQuery = Examen::where('niveau_id', $this->selectedNiveau);
            if ($this->selectedParcours) {
                $examenQuery->where('parcours_id', $this->selectedParcours);
            }
            
            $examens = $examenQuery->get(); // ✅ Tous les examens
            
            if ($examens->isEmpty()) {
                Log::warning('❌ Aucun examen trouvé', [
                    'niveau_id' => $this->selectedNiveau,
                    'parcours_id' => $this->selectedParcours,
                    'session_id' => $session->id
                ]);
                return [];
            }
            
            $examenIds = $examens->pluck('id')->toArray();

            // ✅ REQUÊTE CORRIGÉE avec tous les examens du niveau/parcours
            $query = ResultatFinal::with([
                'etudiant:id,nom,prenom,matricule,niveau_id,parcours_id',
                'ec.ue:id,nom,abr,credits,parcours_id,niveau_id',
                'examen:id,niveau_id,parcours_id'
            ])
            ->whereIn('examen_id', $examenIds) // ✅ TOUS les examens concernés
            ->where('session_exam_id', $session->id)
            ->whereHas('etudiant', function($q) {
                $q->where('niveau_id', $this->selectedNiveau);
                if ($this->selectedParcours) {
                    $q->where('parcours_id', $this->selectedParcours);
                }
            })
            ->where('statut', ResultatFinal::STATUT_PUBLIE);

            $resultats = $query->get();
            
            if ($resultats->isEmpty()) {
                Log::info('ℹ️ Aucun résultat publié trouvé', [
                    'examen_ids' => $examenIds,
                    'session_id' => $session->id,
                    'niveau_id' => $this->selectedNiveau,
                    'parcours_id' => $this->selectedParcours
                ]);
                return [];
            }

            // ✅ FILTRAGE STRICT : Valider chaque résultat
            $resultatsValides = $resultats->filter(function($resultat) {
                // Vérifier que l'étudiant correspond bien
                if (!$resultat->etudiant) return false;
                if ($resultat->etudiant->niveau_id != $this->selectedNiveau) return false;
                if ($this->selectedParcours && $resultat->etudiant->parcours_id != $this->selectedParcours) return false;
                
                // Vérifier que l'EC/UE correspondent
                if (!$resultat->ec || !$resultat->ec->ue) return false;
                if ($resultat->ec->ue->niveau_id != $this->selectedNiveau) return false;
                
                // ✅ NOUVEAU : Vérifier que l'EC appartient à ce parcours
                if ($this->selectedParcours) {
                    $ecParcoursId = $resultat->ec->parcours_id;
                    // Accepter si EC est du même parcours OU commune (null)
                    if ($ecParcoursId !== null && $ecParcoursId != $this->selectedParcours) {
                        return false;
                    }
                }
                
                // Vérifier que l'examen correspond
                if (!$resultat->examen) return false;
                if ($resultat->examen->niveau_id != $this->selectedNiveau) return false;
                if ($this->selectedParcours && $resultat->examen->parcours_id != $this->selectedParcours) return false;
                
                return true;
            });

            if ($resultatsValides->isEmpty()) {
                Log::warning('⚠️ Aucun résultat avec relations valides', [
                    'total_resultats' => $resultats->count(),
                    'resultats_valides' => 0,
                    'niveau_id' => $this->selectedNiveau,
                    'parcours_id' => $this->selectedParcours
                ]);
                return [];
            }

            Log::info('✅ Résultats filtrés', [
                'total_brut' => $resultats->count(),
                'total_valide' => $resultatsValides->count(),
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours
            ]);

            $resultatsGroupes = $resultatsValides->groupBy('etudiant_id');

            return $resultatsGroupes->map(function($resultatsEtudiant, $etudiantId) use ($session, $calculService) {
                $etudiant = $resultatsEtudiant->first()->etudiant;
                if (!$etudiant) return null;

                try {
                    $calculComplet = $calculService->calculerResultatsComplets(
                        $etudiantId, 
                        $session->id, 
                        true, 
                        $this->selectedNiveau, 
                        $this->selectedParcours
                    );
                    
                    $premierResultat = $resultatsEtudiant->first();

                    return [
                        'etudiant' => $etudiant,
                        'notes' => $resultatsEtudiant->keyBy('ec_id'),
                        'moyennes_ue' => collect($calculComplet['resultats_ue'])->pluck('moyenne_ue', 'ue_id')->toArray(),
                        'moyenne_generale' => $calculComplet['synthese']['moyenne_generale'],
                        'moyenne_generale_reelle' => $calculComplet['synthese']['moyenne_generale'],
                        'credits_valides' => $calculComplet['synthese']['credits_valides'],
                        'total_credits' => $calculComplet['synthese']['total_credits'],
                        'has_note_eliminatoire' => $calculComplet['synthese']['a_note_eliminatoire'],
                        'decision' => $premierResultat->decision,
                        'details_ue' => $calculComplet['resultats_ue'],
                        'jury_validated' => $premierResultat->jury_validated ?? false,
                    ];
                } catch (\Exception $e) {
                    Log::error('❌ Erreur calcul étudiant: ' . $e->getMessage(), [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $session->id
                    ]);
                    return null;
                }
            })
            ->filter()
            ->sortBy([
                ['credits_valides', 'desc'],
                ['moyenne_generale', 'desc']
            ])
            ->values()
            ->toArray();

        } catch (\Exception $e) {
            Log::error('❌ Erreur loadResultatsForSession: ' . $e->getMessage());
            return [];
        }
    }


    

    private function loadUEStructure()
    {
        if (!$this->selectedNiveau) {
            $this->uesStructure = collect(); // ✅ CHANGÉ de [] à collect()
            return;
        }

        try {
            $uesQuery = UE::where('niveau_id', $this->selectedNiveau)
                ->where('is_active', true);
            
            if ($this->selectedParcours) {
                $uesQuery->where(function($q) {
                    $q->where('parcours_id', $this->selectedParcours)
                    ->orWhereNull('parcours_id');
                });
            }
            
            $ues = $uesQuery->with(['ecs' => function($query) {
                    $query->where('is_active', true)->orderBy('id');
                }])
                ->orderBy('id')
                ->get();

            $this->uesStructure = $ues->map(function($ue) {
                $ecsFiltered = $ue->ecs;
                
                if ($this->selectedParcours) {
                    $ecsFiltered = $ecsFiltered->filter(function($ec) {
                        return $ec->parcours_id == $this->selectedParcours || 
                            $ec->parcours_id === null;
                    });
                }
                
                return [
                    'ue' => $ue,
                    'ecs' => $ecsFiltered->map(function($ec) {
                        return ['ec' => $ec];
                    })
                ];
            })->filter(function($ueStructure) {
                return $ueStructure['ecs']->isNotEmpty();
            }); // ✅ Résultat est déjà une Collection
            
        } catch (\Exception $e) {
            Log::error('Erreur loadUEStructure: ' . $e->getMessage());
            $this->uesStructure = collect(); // ✅ CHANGÉ de collect() déjà correct
        }
    }


    private function calculateStatistics()
    {
        $this->statistiquesSession1 = $this->calculateSessionStatistics($this->resultatsSession1);
        $this->statistiquesSession2 = $this->calculateSessionStatistics($this->resultatsSession2);
    }

    private function calculateSessionStatistics($resultats)
    {
        $defaultStats = [
            'total_etudiants' => 0,
            'admis' => 0,
            'rattrapage' => 0,
            'redoublant' => 0,
            'exclus' => 0,
            'moyenne_promo' => 0,
            'taux_reussite' => 0,
            'credits_moyen' => 0,
            'jury_validated' => 0
        ];

        if (empty($resultats)) {
            return $defaultStats;
        }

        try {
            $total = count($resultats);
            $decisions = array_count_values(array_column($resultats, 'decision'));
            
            $moyennes = array_column($resultats, 'moyenne_generale');
            $credits = array_column($resultats, 'credits_valides');
            $juryValidated = array_sum(array_column($resultats, 'jury_validated'));

            $admis = $decisions[ResultatFinal::DECISION_ADMIS] ?? 0;

            return [
                'total_etudiants' => $total,
                'admis' => $admis,
                'rattrapage' => $decisions[ResultatFinal::DECISION_RATTRAPAGE] ?? 0,
                'redoublant' => $decisions[ResultatFinal::DECISION_REDOUBLANT] ?? 0,
                'exclus' => $decisions[ResultatFinal::DECISION_EXCLUS] ?? 0,
                'moyenne_promo' => $total > 0 ? round(array_sum($moyennes) / $total, 2) : 0,
                'credits_moyen' => $total > 0 ? round(array_sum($credits) / $total, 2) : 0,
                'taux_reussite' => $total > 0 ? round(($admis / $total) * 100, 2) : 0,
                'jury_validated' => $juryValidated
            ];
        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques: ' . $e->getMessage());
            return $defaultStats; // ✅ jamais vide
        }
    }



        // ✅ MÉTHODE UTILITAIRE AJOUTÉE : Obtenir les vraies moyennes d'un étudiant
    public function getVraieMoyenneEtudiant($resultat)
    {
        try {
            // Si on a déjà la moyenne réelle dans les données
            if (isset($resultat['moyenne_generale_reelle'])) {
                return $resultat['moyenne_generale_reelle'];
            }

            // Sinon, utiliser la moyenne générale (qui devrait être la vraie avec nos corrections)
            return $resultat['moyenne_generale'] ?? 0;

        } catch (\Exception $e) {
            Log::error('Erreur getVraieMoyenneEtudiant: ' . $e->getMessage());
            return 0;
        }
    }


    // ✅ MÉTHODE UTILITAIRE AJOUTÉE : Vérifier si une UE est validée
    public function isUEValidee($moyenneUE, $hasNoteEliminatoire)
    {
        return ($moyenneUE >= 10) && (!$hasNoteEliminatoire);
    }

    // ✅ MÉTHODE UTILITAIRE AJOUTÉE : Obtenir la classe CSS pour une moyenne UE
    public function getClasseMoyenneUE($moyenneUE, $hasNoteEliminatoire)
    {
        if ($hasNoteEliminatoire) {
            // Rouge si note éliminatoire (mais affiche vraie moyenne)
            return 'text-red-600 dark:text-red-400 font-bold';
        } elseif ($moyenneUE >= 10) {
            // Vert si validée
            return 'text-green-600 dark:text-green-400 font-semibold';
        } else {
            // Orange si moyenne < 10 sans note éliminatoire
            return 'text-orange-600 dark:text-orange-400';
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        
        if (in_array($tab, ['session1', 'session2'])) {
            $session = $tab === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;
            if ($session) {
                $this->deliberationParams['session_type'] = $tab;
                $this->deliberationParams['session_id'] = $session->id;
            }
        }
        
    }


    public function simulerDeliberation()
    {
        try {
            if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
                $this->addError('deliberation', 'Configuration incomplète.');
                return;
            }

            $resultatsActuels = $this->deliberationParams['session_type'] === 'session1'
                ? $this->resultatsSession1 : $this->resultatsSession2;

            if (empty($resultatsActuels)) {
                $this->addError('deliberation', 'Aucun résultat disponible.');
                return;
            }

            $resultatsDetailles = [];
            // ✅ CORRECTION : Toutes les clés doivent être 'exclus' et non 'excluss'
            $statistiques = ['admis' => 0, 'rattrapage' => 0, 'redoublant' => 0, 'exclus' => 0, 'changements' => 0];
            $erreursCriteres = [];

            foreach ($resultatsActuels as $index => $resultat) {
                $etudiant = $resultat['etudiant'] ?? null;
                if (!$etudiant) continue;

                $validation = $this->validerCriteresAdmission($resultat, $this->deliberationParams['session_type']);
                
                $decisionSimulee = $this->calculerDecisionSelonParametres($resultat);
                $decisionActuelle = $resultat['decision'] ?? 'rattrapage';
                $changement = $decisionActuelle !== $decisionSimulee;

                if ($decisionSimulee === 'admis' && !$validation['peut_etre_admis']) {
                    $erreursCriteres[] = [
                        'etudiant' => $etudiant->nom . ' ' . $etudiant->prenom,
                        'matricule' => $etudiant->matricule,
                        'decision_calculee' => $decisionSimulee,
                        'erreurs' => $validation['erreurs'],
                        'details' => $validation['details']
                    ];
                    
                    $decisionSimulee = 'rattrapage';
                    $changement = $decisionActuelle !== $decisionSimulee;
                }

                if ($changement) $statistiques['changements']++;
                
                // ✅ VÉRIFICATION : S'assurer que la décision est valide
                if (!isset($statistiques[$decisionSimulee])) {
                    Log::error('❌ Décision invalide détectée', [
                        'decision' => $decisionSimulee,
                        'etudiant_id' => $etudiant->id,
                        'session_type' => $this->deliberationParams['session_type']
                    ]);
                    $decisionSimulee = 'exclus'; // Fallback sécurisé
                }
                
                $statistiques[$decisionSimulee]++;

                $resultatsDetailles[] = [
                    'etudiant_id' => $etudiant->id,
                    'etudiant' => $etudiant,
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'matricule' => $etudiant->matricule,
                    'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'rang' => $index + 1,
                    'moyenne_generale' => $resultat['moyenne_generale'] ?? 0,
                    'credits_valides' => $resultat['credits_valides'] ?? 0,
                    'total_credits' => $resultat['total_credits'] ?? 60,
                    'has_note_eliminatoire' => $resultat['has_note_eliminatoire'] ?? false,
                    'decision_actuelle' => $decisionActuelle,
                    'decision_simulee' => $decisionSimulee,
                    'changement' => $changement,
                    'validation_criteres' => $validation
                ];
            }

            $this->simulationDeliberation = [
                'success' => true,
                'total_etudiants' => count($resultatsDetailles),
                'statistiques' => $statistiques,
                'resultats_detailles' => $resultatsDetailles,
                'parametres_utilises' => $this->deliberationParams,
                'erreurs_criteres' => $erreursCriteres
            ];

            $sessionName = $this->deliberationParams['session_type'] === 'session1' ? 'Session 1' : 'Session 2';
            
            if (!empty($erreursCriteres)) {
                $nombreErreurs = count($erreursCriteres);
                toastr()->warning("Simulation {$sessionName} : {$statistiques['changements']} changements détectés. ATTENTION : {$nombreErreurs} incohérence(s) corrigée(s) automatiquement.");
                
                Log::warning('Incohérences détectées dans la simulation de délibération', [
                    'session_type' => $sessionName,
                    'nombre_erreurs' => $nombreErreurs,
                    'erreurs' => $erreursCriteres
                ]);
            } else {
                toastr()->info("Simulation {$sessionName} : {$statistiques['changements']} changements détectés. Tous les critères sont cohérents.");
            }

        } catch (\Exception $e) {
            Log::error('Erreur simulation délibération: ' . $e->getMessage());
            $this->addError('deliberation', 'Erreur simulation: ' . $e->getMessage());
        }
    }


    public function verifierCoherenceResultat($resultat)
    {
        $moyenne = $resultat['moyenne_generale'] ?? 0;
        $credits = $resultat['credits_valides'] ?? 0;
        $decision = $resultat['decision'] ?? 'non_definie';
        $hasEliminatoire = $resultat['has_note_eliminatoire'] ?? false;
        
        $problemes = [];
        
        // Vérifier cohérence admission
        if ($decision === 'admis') {
            if ($moyenne < 10.0) {
                $problemes[] = "Admis avec moyenne < 10 : {$moyenne}";
            }
            if ($hasEliminatoire) {
                $problemes[] = "Admis avec note éliminatoire";
            }
            if ($credits < 60) { // Supposer 60 crédits requis en session 1
                $problemes[] = "Admis avec crédits insuffisants : {$credits}/60";
            }
        }
        
        return [
            'coherent' => empty($problemes),
            'problemes' => $problemes,
            'details' => [
                'moyenne' => $moyenne,
                'credits' => $credits,
                'decision' => $decision,
                'has_eliminatoire' => $hasEliminatoire
            ]
        ];
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Vérifie si le niveau peut avoir des exclusions
     */
    private function niveauPeutExclure()
    {
        if (!$this->selectedNiveau) {
            return false;
        }
        
        try {
            $niveau = Niveau::find($this->selectedNiveau);
            
            if (!$niveau) {
                return false;
            }
            
            // Seul PACES peut exclure
            return in_array($niveau->abr, ['PACES', 'L1']);
            
        } catch (\Exception $e) {
            Log::error('Erreur vérification niveau exclusion', [
                'niveau_id' => $this->selectedNiveau,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ✅ MÉTHODE MODIFIÉE avec gestion niveau
     */
    private function calculerDecisionSelonParametres($resultat)
    {
        $sessionType = $this->deliberationParams['session_type'] ?? 'session1';
        $creditsValides = $resultat['credits_valides'] ?? 0;
        $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;
        $moyenneGenerale = $resultat['moyenne_generale'] ?? 0;
        
        // ✅ AJOUT : Vérifier si le niveau peut exclure
        $peutExclure = $this->niveauPeutExclure();

        if ($sessionType === 'session1') {
            $creditsRequis = $this->deliberationParams['credits_admission_s1'] ?? 60;
            $bloquerSiNote0 = $this->deliberationParams['note_eliminatoire_bloque_s1'] ?? true;

            if ($hasNoteEliminatoire && $bloquerSiNote0) {
                return 'rattrapage';
            }
            
            if ($moyenneGenerale >= 10.0 && $creditsValides >= $creditsRequis) {
                return 'admis';
            }
            
            if ($moyenneGenerale < 10.0) {
                return 'rattrapage';
            }
            
            if ($creditsValides < $creditsRequis) {
                return 'rattrapage';
            }
            
            return 'rattrapage';
            
        } else {
            // ✅ SESSION 2 - LOGIQUE ADAPTÉE SELON NIVEAU
            $creditsAdmission = $this->deliberationParams['credits_admission_s2'] ?? 40;
            $creditsRedoublement = $this->deliberationParams['credits_redoublement_s2'] ?? 20;
            $exclusionSiNote0 = $this->deliberationParams['note_eliminatoire_exclusion_s2'] ?? true;

            // ✅ MODIFICATION : Exclusion seulement pour PACES
            if ($hasNoteEliminatoire && $exclusionSiNote0) {
                return $peutExclure ? 'exclus' : 'redoublant';
            }

            if ($moyenneGenerale >= 10.0 && $creditsValides >= $creditsAdmission) {
                return 'admis';
            }
            
            if ($moyenneGenerale < 10.0) {
                if ($creditsValides >= $creditsRedoublement) {
                    return 'redoublant';
                } else {
                    return $peutExclure ? 'exclus' : 'redoublant';
                }
            }
            
            if ($creditsValides >= $creditsRedoublement) {
                return 'redoublant';
            }
            
            return $peutExclure ? 'exclus' : 'redoublant';
        }
    }


    private function validerCriteresAdmission($resultat, $sessionType = 'session1')
    {
        $moyenneGenerale = $resultat['moyenne_generale'] ?? 0;
        $creditsValides = $resultat['credits_valides'] ?? 0;
        $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;
        
        $erreurs = [];
        $avertissements = [];
        
        // Critères de base
        $moyenneRequise = 10.0;
        $creditsRequis = $sessionType === 'session1' 
            ? ($this->deliberationParams['credits_admission_s1'] ?? 60)
            : ($this->deliberationParams['credits_admission_s2'] ?? 40);
        
        // Validation moyenne
        if ($moyenneGenerale < $moyenneRequise) {
            $erreurs[] = "Moyenne insuffisante: {$moyenneGenerale} < {$moyenneRequise}";
        }
        
        // Validation crédits
        if ($creditsValides < $creditsRequis) {
            $erreurs[] = "Crédits insuffisants: {$creditsValides} < {$creditsRequis}";
        }
        
        // Validation note éliminatoire
        if ($hasNoteEliminatoire) {
            $erreurs[] = "Présence de note(s) éliminatoire(s)";
        }
        
        // Logique d'admission
        $peutEtreAdmis = empty($erreurs);
        
        return [
            'peut_etre_admis' => $peutEtreAdmis,
            'erreurs' => $erreurs,
            'avertissements' => $avertissements,
            'details' => [
                'moyenne' => $moyenneGenerale,
                'moyenne_requise' => $moyenneRequise,
                'credits' => $creditsValides,
                'credits_requis' => $creditsRequis,
                'has_eliminatoire' => $hasNoteEliminatoire
            ]
        ];
    }


    public function fermerDeliberationModal()
    {
        $this->showDeliberationModal = false;
        $this->simulationDeliberation = [];
        $this->resetErrorBag(['deliberation']);
    }

    
    public function exporterExcel()
    {
        try {
            $exportService = new ExportService();
            
            $resultats = $this->activeTab === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;
            
            if (empty($resultats)) {
                toastr()->error('Aucun résultat à exporter.');
                return;
            }
            
            $excel = $exportService->exporterExcel($resultats, $this->uesStructure);
            
            toastr()->success('Export Excel généré avec succès (' . count($resultats) . ' étudiants)');
            return $excel;
            
        } catch (\Exception $e) {
            Log::error('Erreur export Excel: ' . $e->getMessage());
            toastr()->error('Erreur lors de l\'export Excel: ' . $e->getMessage());
        }
    }


    public function exportExcel()
    {
        try {
            $this->validate();

            // ✅ CORRECTION : Recharger les données fraîches depuis la base avant export
            $this->loadResultats();

            $resultats = $this->activeTab === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;
            $session = $this->activeTab === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

            if (empty($resultats)) {
                toastr()->error('Aucun résultat à exporter.');
                return;
            }

            // ✅ CORRECTION : Recharger la session depuis la base pour avoir le bon statut de délibération
            $sessionFraiche = SessionExam::find($session->id);
            
            $niveau = Niveau::find($this->selectedNiveau);
            $parcours = $this->selectedParcours ? Parcour::find($this->selectedParcours) : null;
            $anneeUniv = AnneeUniversitaire::find($this->selectedAnneeUniversitaire);

            $exportService = new ExportService();

            // ✅ CORRECTION : Passer la session fraîche et forcer les paramètres de délibération
            return $exportService->exporterExcel(
                $resultats, 
                $this->uesStructure, 
                $niveau, 
                $parcours, 
                $anneeUniv, 
                $sessionFraiche, // Session fraîche avec statut délibération correct
                $this->deliberationParams
            );

        } catch (\Exception $e) {
            Log::error('Erreur export Excel: ' . $e->getMessage());
            toastr()->error('Erreur lors de l\'export Excel: ' . $e->getMessage());
        }
    }


    public function exportPDF()
    {
        try {
            $this->validate();

            $resultats = $this->activeTab === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;
            $session = $this->activeTab === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

            if (empty($resultats)) {
                toastr()->error('Aucun résultat à exporter.');
                return;
            }

            $niveau = Niveau::find($this->selectedNiveau);
            $parcours = $this->selectedParcours ? Parcour::find($this->selectedParcours) : null;
            $anneeUniv = AnneeUniversitaire::find($this->selectedAnneeUniversitaire);

            /** @var \App\Exports\AdmisDeliberationPDF $pdfExporter */
            $pdfExporter = new AdmisDeliberationPDF(
                $resultats,
                $session,
                $niveau,
                $parcours,
                []
            );

            // ✅ IntelliSense devrait maintenant reconnaître la méthode
            $result = $pdfExporter->generateAndSaveToPublic();
            
            toastr()->success("PDF généré et sauvegardé ! URL : " . $result['url']);

            return response()->streamDownload(function() use ($result) {
                echo $result['pdf']->output();
            }, $result['filename']);

        } catch (\Exception $e) {
            Log::error('Erreur export PDF: ' . $e->getMessage());
            toastr()->error('Erreur lors de l\'export PDF: ' . $e->getMessage());
        }
    }


    public function ouvrirModalExport($type = 'pdf', $source = 'simulation')
    {
        $donnees = $this->getDonneesExport($source);

        if (empty($donnees)) {
            toastr()->error("Aucune donnée disponible pour l'export.");
            return;
        }

        $this->exportType = $type;
        $this->exportData = $source;
        $this->showExportModal = true;

        $this->exportConfig['filtres'] = [
            'decision_filter' => 'tous',
            'moyenne_min' => null,
            'moyenne_max' => null,
        ];

        toastr()->info("Configuration de l'export {$type}");
    }

    public function fermerModalExport()
    {
        $this->showExportModal = false;
        $this->resetErrorBag(['export']);
    }

    private function getDonneesExport($source)
    {
        switch ($source) {
            case 'simulation':
                return $this->simulationDeliberation['resultats_detailles'] ?? [];
            case 'deliberation':
                $resultats = $this->activeTab === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;
                return $this->formatResultatsForExport($resultats);
            default:
                return [];
        }
    }

    private function formatResultatsForExport($resultats)
    {
        $formatted = [];
        $rang = 1;

        foreach ($resultats as $resultat) {
            $etudiant = $resultat['etudiant'];

            $formatted[] = [
                'rang' => $rang,
                'etudiant_id' => $etudiant->id,
                'etudiant' => $etudiant,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'matricule' => $etudiant->matricule,
                'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                'moyenne_generale' => $resultat['moyenne_generale'] ?? 0,
                'credits_valides' => $resultat['credits_valides'] ?? 0,
                'total_credits' => $resultat['total_credits'] ?? 60,
                'has_note_eliminatoire' => $resultat['has_note_eliminatoire'] ?? false,
                'decision_actuelle' => $resultat['decision'] ?? 'non_definie',
                'decision_simulee' => $resultat['decision'] ?? 'non_definie',
                'changement' => false
            ];
            $rang++;
        }

        return $formatted;
    }

    public function exporterParDecisionSimulation($decision, $type = 'pdf')
    {
        try {
            $this->exportConfig['filtres']['decision_filter'] = $decision;
            $this->exportConfig['tri']['champ'] = 'moyenne_generale';
            $this->exportConfig['tri']['ordre'] = 'desc';

            $this->exportType = $type;
            $this->exportData = 'simulation';

            return $this->genererExportAvecConfig();

        } catch (\Exception $e) {
            Log::error('Erreur export par décision simulation', [
                'decision' => $decision,
                'error' => $e->getMessage()
            ]);
            toastr()->error('Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    public function exporterAdmisRapide($type = 'pdf')
    {
        try {
            $this->exportConfig['filtres']['decision_filter'] = 'admis';
            $this->exportConfig['tri']['champ'] = 'moyenne_generale';
            $this->exportConfig['tri']['ordre'] = 'desc';

            $this->exportType = $type;
            $this->exportData = !empty($this->simulationDeliberation) ? 'simulation' : 'deliberation';

            return $this->genererExportAvecConfig();

        } catch (\Exception $e) {
            Log::error('Erreur export admis rapide', ['error' => $e->getMessage()]);
            toastr()->error('Erreur lors de l\'export rapide des admis : ' . $e->getMessage());
        }
    }

    public function exportRapideDepuisSimulation($type, $filtre = 'tous')
    {
        if (empty($this->simulationDeliberation)) {
            toastr()->error('Aucune simulation disponible pour l\'export.');
            return;
        }

        try {
            $this->exportType = $type;
            $this->exportData = 'simulation';
            $this->exportConfig['filtres']['decision_filter'] = $filtre;
            $this->exportConfig['tri']['champ'] = 'moyenne_generale';
            $this->exportConfig['tri']['ordre'] = 'desc';

            $this->exportConfig['colonnes'] = [
                'rang' => true,
                'nom_complet' => true,
                'matricule' => true,
                'moyenne' => true,
                'credits' => true,
                'decision' => true,
                'niveau' => false,
            ];

            return $this->genererExportAvecConfig();

        } catch (\Exception $e) {
            Log::error('Erreur export rapide simulation', [
                'type' => $type,
                'filtre' => $filtre,
                'error' => $e->getMessage()
            ]);
            toastr()->error('Erreur lors de l\'export rapide : ' . $e->getMessage());
        }
    }

    public function genererExportAvecConfig()
    {
        try {
            if (!$this->validerColonnesExport()) {
                return;
            }

            if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
                $this->addError('export', 'Veuillez sélectionner un niveau et une année universitaire.');
                return;
            }

            $donneesRaw = $this->getDonneesExport($this->exportData);

            if (empty($donneesRaw)) {
                $this->addError('export', "Aucune donnée disponible pour l'export.");
                return;
            }

            $donneesFiltrees = $this->appliquerFiltresExport($donneesRaw);

            if (empty($donneesFiltrees)) {
                $this->addError('export', "Aucune donnée ne correspond aux filtres appliqués.");
                return;
            }

            $session = $this->activeTab === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

            if ($this->exportData === 'simulation' && !empty($this->deliberationParams['session_type'])) {
                $session = $this->deliberationParams['session_type'] === 'session1' ?
                    $this->sessionNormale : $this->sessionRattrapage;
            }

            $niveau = Niveau::find($this->selectedNiveau);
            $parcours = $this->selectedParcours ? Parcour::find($this->selectedParcours) : null;
            $anneeUniv = AnneeUniversitaire::find($this->selectedAnneeUniversitaire);

            if ($this->exportType === 'pdf') {
                return $this->genererPDFAvecConfig($donneesFiltrees, $session, $niveau, $parcours, $anneeUniv);
            } else {
                return $this->genererExcelAvecConfig($donneesFiltrees, $session, $niveau, $parcours, $anneeUniv);
            }

        } catch (\Exception $e) {
            Log::error('Erreur génération export avec config', [
                'type' => $this->exportType,
                'source' => $this->exportData,
                'error' => $e->getMessage()
            ]);
            $this->addError('export', 'Erreur lors de la génération : ' . $e->getMessage());
        }
    }

    private function validerColonnesExport()
    {
        $colonnesSelectionnees = array_filter($this->exportConfig['colonnes']);

        if (empty($colonnesSelectionnees)) {
            $this->addError('export', 'Veuillez sélectionner au moins une colonne à exporter.');
            return false;
        }

        if (!($this->exportConfig['colonnes']['nom_complet'] || $this->exportConfig['colonnes']['matricule'])) {
            $this->addError('export', 'Veuillez sélectionner au moins le nom ou le matricule.');
            return false;
        }

        return true;
    }

    private function appliquerFiltresExport($donnees)
    {
        $donneesCollection = collect($donnees);

        if ($this->exportConfig['filtres']['decision_filter'] !== 'tous') {
            $decisionFiltre = $this->exportConfig['filtres']['decision_filter'];
            $champ = $this->exportData === 'simulation' ? 'decision_simulee' : 'decision_actuelle';
            $donneesCollection = $donneesCollection->where($champ, $decisionFiltre);
        }

        if (!empty($this->exportConfig['filtres']['moyenne_min'])) {
            $donneesCollection = $donneesCollection->where('moyenne_generale', '>=', $this->exportConfig['filtres']['moyenne_min']);
        }

        if (!empty($this->exportConfig['filtres']['moyenne_max'])) {
            $donneesCollection = $donneesCollection->where('moyenne_generale', '<=', $this->exportConfig['filtres']['moyenne_max']);
        }

        $champ = $this->exportConfig['tri']['champ'];
        $ordre = $this->exportConfig['tri']['ordre'];

        if ($ordre === 'asc') {
            $donneesCollection = $donneesCollection->sortBy($champ);
        } else {
            $donneesCollection = $donneesCollection->sortByDesc($champ);
        }

        $donneesFinales = [];
        $rang = 1;
        foreach ($donneesCollection->values() as $item) {
            $item['rang'] = $rang;
            $donneesFinales[] = $item;
            $rang++;
        }

        return $donneesFinales;
    }

    private function genererPDFAvecConfig($donnees, $session, $niveau, $parcours, $anneeUniv)
    {
        try {
            $pdfExporter = new AdmisDeliberationPDF(
                $donnees,
                $session,
                $niveau,
                $parcours,
                $this->exportConfig['colonnes'] ?? []
            );

            $result = $pdfExporter->generateAndSaveToPublic();
            
            toastr()->success("PDF généré et sauvegardé ! URL : " . $result['url']);

            return response()->streamDownload(function() use ($result) {
                echo $result['pdf']->output();
            }, $result['filename']);

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    private function genererExcelAvecConfig($donnees, $session, $niveau, $parcours, $anneeUniv)
    {
        try {
            $filename = $this->genererNomFichier('xlsx', $session, $niveau, $parcours, $anneeUniv);

            $this->showExportModal = false;
            toastr()->success("Export Excel généré avec succès ! (" . count($donnees) . " résultats)");

            return Excel::download(
                new AdmisDeliberationExport(
                    $donnees,
                    $session,
                    $niveau,
                    $parcours,
                    $this->exportConfig['colonnes']
                ),
                $filename
            );

        } catch (\Exception $e) {
            Log::error('Erreur génération Excel avec config', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function genererNomFichier($extension, $session, $niveau, $parcours, $anneeUniv)
    {
        $sessionType = $session->type === 'Normale' ? 'Session1' : 'Session2';
        $niveauNom = str_replace(' ', '_', $niveau->nom);
        $parcoursNom = $parcours ? '_' . str_replace(' ', '_', $parcours->nom) : '';
        $anneeNom = str_replace(['/', ' '], ['_', '_'], $anneeUniv->libelle);
        $source = ucfirst($this->exportData);
        $date = now()->format('Ymd_His');

        $filtreSuffix = '';
        if ($this->exportConfig['filtres']['decision_filter'] !== 'tous') {
            $filtreSuffix .= '_' . ucfirst($this->exportConfig['filtres']['decision_filter']);
        }

        return "{$source}_{$sessionType}_{$niveauNom}{$parcoursNom}_{$anneeNom}{$filtreSuffix}_{$date}.{$extension}";
    }


    // PROPRIÉTÉS COMPUTED
    public function getNomNiveauSelectionneProperty()
    {
        return $this->selectedNiveau ? 
            $this->niveaux->where('id', $this->selectedNiveau)->first()?->nom : null;
    }

    public function getNomParcoursSelectionneProperty()
    {
        return $this->selectedParcours ? 
            $this->parcours->where('id', $this->selectedParcours)->first()?->nom : 'Tous les parcours';
    }

    public function getNomAnneeSelectionneProperty()
    {
        return $this->selectedAnneeUniversitaire ? 
            $this->anneesUniversitaires->where('id', $this->selectedAnneeUniversitaire)->first()?->libelle : null;
    }

    public function getDoisAfficherBoutonChargerProperty()
    {
        return $this->etape_actuelle === 'pret_charger' && $this->peut_charger_resultats;
    }

    // MÉTHODES UTILITAIRES
    public function getResultatNote($notes, $ecId)
    {
        return $notes->get($ecId)?->note ?? '-';
    }

    public function getClasseNote($note)
    {
        if ($note === '-' || $note === null) return 'text-gray-400';
        $noteNum = (float) $note;
        if ($noteNum == 0) return 'text-red-600 font-bold bg-red-50';
        if ($noteNum < 10) return 'text-red-500';
        if ($noteNum < 12) return 'text-orange-500';
        if ($noteNum < 14) return 'text-blue-500';
        return 'text-green-600 font-semibold';
    }

    public function getClasseDecision($decision)
    {
        switch ($decision) {
            case ResultatFinal::DECISION_ADMIS:
                return 'bg-green-100 text-green-800 border-green-200';
            case ResultatFinal::DECISION_RATTRAPAGE:
                return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case ResultatFinal::DECISION_REDOUBLANT:
                return 'bg-orange-100 text-orange-800 border-orange-200';
            case ResultatFinal::DECISION_EXCLUS:
                return 'bg-red-100 text-red-800 border-red-200';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }

    public function getLibelleDecision($decision)
    {
        switch ($decision) {
            case ResultatFinal::DECISION_ADMIS: return 'Admis';
            case ResultatFinal::DECISION_RATTRAPAGE: return 'Rattrapage';
            case ResultatFinal::DECISION_REDOUBLANT: return 'Redoublant';
            case ResultatFinal::DECISION_EXCLUS: return 'Exclu';
            default: return 'Indéterminé';
        }
    }

    public function updatedSelectedAnneeUniversitaire()
    {
        if ($this->selectedAnneeUniversitaire) {
            $this->loadSessions();
            $this->checkSession2Availability();
            $this->resetResults(); // ✅ Reset seulement les résultats, pas les sélections
            $this->updateNomsAffiches();
            $this->updateEtapeActuelle();
            $this->updatePeutChargerResultats();
        }
    }

    public function refreshData()
    {
        try {
            // 1. Réinitialiser les données
            $this->resetValidation();
            $this->simulationDeliberation = [];
            $this->simulationResults = [];

            // 2. Recharger les sessions
            $this->loadSessions();

            // 3. Recharger les résultats si les filtres sont définis
            if ($this->selectedNiveau && $this->selectedAnneeUniversitaire) {
                $this->loadResultats();
            }

            // 4. Vérifier la disponibilité de la session 2
            $this->checkSession2Availability();

            // 5. Recharger la structure UE
            $this->loadUEStructure();

            // 6. Message de confirmation
            toastr()->info('✅ Données actualisées avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors du rafraîchissement des données: ' . $e->getMessage());
            toastr()->error('Erreur lors du rafraîchissement: ' . $e->getMessage());
        }
    }


    /**
     * ✅ MÉTHODE BONUS : Reset complet du composant
     */
    public function resetComponent()
    {
        try {
            // Reset toutes les propriétés
            $this->resultatsSession1 = [];
            $this->resultatsSession2 = [];
            $this->simulationDeliberation = [];
            $this->simulationResults = [];
            $this->statistiquesSession1 = [];
            $this->statistiquesSession2 = [];
            $this->uesStructure = [];
            $this->showDeliberationModal = false;
            $this->showSession2 = false;

            // Reset validation
            $this->resetValidation();
            $this->resetErrorBag();

            // Réinitialiser les paramètres
            $this->initializeSimulationParams();

            // Recharger les données de base
            $this->setDefaultValues();

            toastr()->success('✅ Composant réinitialisé');

        } catch (\Exception $e) {
            Log::error('Erreur reset composant: ' . $e->getMessage());
            toastr()->error('Erreur lors de la réinitialisation.');
        }
    }

    private function initializeSimulationParams()
    {
        $this->simulationParams = [
            'session_type' => 'session1',
            'credits_admission_session1' => 60,
            'appliquer_note_eliminatoire_s1' => true,
            'credits_admission_session2' => 40,
            'credits_redoublement_session2' => 20,
            'appliquer_note_eliminatoire_s2' => true,
        ];

        // ✅ NOUVEAU : Initialiser aussi deliberationParams avec pourcentages
        $this->deliberationParams = [
            'session_type' => 'session1',
            'session_id' => null,
            'credits_admission_s1' => 60,
            'credits_admission_s2' => 40,
            'credits_redoublement_s2' => 20,
            'note_eliminatoire_bloque_s1' => true,
            'note_eliminatoire_exclusion_s2' => true
        ];
        
        // ✅ NOUVEAU : Initialiser avec pourcentages
        $this->initialiserParametresAvecPourcentages();
    }

    // ✅ MÉTHODE 5 : Initialiser les paramètres avec pourcentages par défaut
    private function initialiserParametresAvecPourcentages()
    {
        $creditsTotaux = $this->calculerCreditsTotauxDisponibles();
        
        // Ajouter les champs pourcentages aux paramètres existants
        $this->deliberationParams['credits_totaux_disponibles'] = $creditsTotaux;
        
        // Calculer les pourcentages par défaut basés sur les crédits actuels
        $this->deliberationParams['pourcentage_admission_s1'] = 
            $this->convertirCreditsEnPourcentage($this->deliberationParams['credits_admission_s1'] ?? 60);
            
        $this->deliberationParams['pourcentage_admission_s2'] = 
            $this->convertirCreditsEnPourcentage($this->deliberationParams['credits_admission_s2'] ?? 40);
            
        $this->deliberationParams['pourcentage_redoublement_s2'] = 
            $this->convertirCreditsEnPourcentage($this->deliberationParams['credits_redoublement_s2'] ?? 20);
            
    }


    // ✅ MÉTHODE 1 : Calculer les crédits totaux disponibles pour un niveau/parcours
    private function calculerCreditsTotauxDisponibles()
    {
        try {
            $query = UE::where('niveau_id', $this->selectedNiveau);
            
            if ($this->selectedParcours) {
                $query->where('parcours_id', $this->selectedParcours);
            }
            
            $totalCredits = $query->where('is_active', true)
                ->sum('credits');
            
            return $totalCredits ?: 60; // Fallback to 60 if no UE found
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur calcul crédits totaux', [
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'error' => $e->getMessage()
            ]);
            
            return 60; // Fallback sécurisé
        }
    }

    // ✅ MÉTHODE 3 : Convertir crédits en pourcentage
    private function convertirCreditsEnPourcentage($credits)
    {
        $creditsTotaux = $this->calculerCreditsTotauxDisponibles();
        
        if ($creditsTotaux == 0) {
            return 0;
        }
        
        return round(($credits / $creditsTotaux) * 100, 1);
    }

    /**
     * Appliquer la délibération pour une session donnée
     */
    public function appliquerDeliberation(): void
    {
        $this->showConfirmationModal = false;
        
        // ✅ VÉRIFICATION DE SÉCURITÉ
        if (!$this->deliberationApplicationService) {
            $this->deliberationApplicationService = new DeliberationApplicationService(
                $this->calculAcademiqueService ?: new CalculAcademiqueService()
            );
        }
        
        // Le service gère TOUT
        $result = $this->deliberationApplicationService->appliquerDeliberationComplete(
            $this->selectedNiveau,
            $this->selectedParcours,
            $this->sessionNormale,
            $this->sessionRattrapage,
            $this->deliberationParams,
            $this->simulationDeliberation
        );
        
        if ($result['success']) {
            // Succès
            toastr()->success($result['message']);
            $this->handleSuccess($result['result'], $result['session']);
        } else {
            // Erreur
            toastr()->error($result['message']);
            $this->addError('deliberation', $result['error']);
        }
    }


    /**
     * Gérer le succès (version simplifiée)
     */
    private function handleSuccess(array $resultData, SessionExam $session): void
    {
        $sessionType = $this->deliberationParams['session_type'] ?? 'session1';
        
        // Mise à jour du statut
        $this->updateDeliberationStatus();
        
        // Reset et rechargement
        $this->resetAfterDeliberation($sessionType);
    }


    /**
     * Mettre à jour le statut de délibération
     */
    private function updateDeliberationStatus(): void
    {
        try {
            // Mettre à jour le statut pour les vues
            $this->deliberationStatus = [
                'session1' => $this->checkDeliberationStatus('session1'),
                'session2' => $this->checkDeliberationStatus('session2')
            ];
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour statut délibération: ' . $e->getMessage());
            
            // Fallback simple
            $this->deliberationStatus = [
                'delibere' => true,
                'date_deliberation' => now(),
                'delibere_par' => Auth::user()->name ?? 'Utilisateur'
            ];
        }
    }


    /**
     * Réinitialiser après délibération - Version simplifiée
     */
    private function resetAfterDeliberation(string $sessionType): void
    {
        try {
            // Fermer les modals
            $this->showDeliberationModal = false;
            $this->showConfirmationModal = false;
            $this->simulationDeliberation = [];

            // ✅ CORRECTION : Recharger la session depuis la base pour avoir le statut délibération
            $this->loadSessions();
            
            // ✅ CORRECTION : Vider le cache des résultats avant rechargement  
            $this->resultatsSession1 = [];
            $this->resultatsSession2 = [];
            
            // ✅ CORRECTION : Recharger complètement les résultats depuis la base
            $this->loadResultats();
            
            // Recalculer les statistiques
            $this->calculateStatistics();
            
            // Mettre à jour l'état de délibération
            $this->updateDeliberationStatus();

            // Message informatif
            $sessionName = $sessionType === 'session1' ? 'Session 1' : 'Session 2';
            toastr()->info("Données actualisées après délibération {$sessionName}");

            // ✅ AJOUT : Forcer le rafraîchissement de la vue
            $this->dispatch('resultats-updated');

        } catch (\Exception $e) {
            Log::error('Erreur reset après délibération: ' . $e->getMessage());
            
            // En cas d'erreur, forcer un rechargement complet
            $this->refreshData();
            
            toastr()->warning('Données rechargées après délibération. Veuillez vérifier les résultats.');
        }
    }

    /**
     * Ouvrir le modal de confirmation de délibération
     */
    public function ouvrirConfirmationDeliberation(): void
    {
        // Vérifier qu'il y a des changements à appliquer
        if (empty($this->simulationDeliberation) || ($this->simulationDeliberation['statistiques']['changements'] ?? 0) === 0) {
            toastr()->warning('Aucun changement à appliquer. Veuillez d\'abord simuler la délibération.');
            return;
        }

        // Vérifier les autorisations
        if (!Auth::user()->can('resultats.validation')) {
            toastr()->error('Vous n\'avez pas l\'autorisation d\'appliquer une délibération.');
            return;
        }

        $this->showConfirmationModal = true;
    }

    /**
     * Fermer le modal de confirmation
     */
    public function fermerConfirmationModal(): void
    {
        $this->showConfirmationModal = false;
    }


    public function checkDeliberationStatus($sessionType): bool
    {
        $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;
        return $session ? $session->estDeliberee() : false;
    }


    public function ouvrirDeliberation($sessionType)
    {
        $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;
        
        if (!$session) {
            toastr()->error('Session non trouvée.');
            return;
        }

        // ✅ MODIFICATION : Informer au lieu de bloquer
        if ($session->estDeliberee()) {
            $nbModifications = method_exists($session, 'getNombreModificationsDeliberation') ? 
                $session->getNombreModificationsDeliberation() : 0;
                
            $message = $nbModifications > 0 ? 
                "Session déjà délibérée le {$session->date_deliberation->format('d/m/Y à H:i')} ({$nbModifications} modification(s) précédente(s)). Vous pouvez la modifier." :
                "Session déjà délibérée le {$session->date_deliberation->format('d/m/Y à H:i')}. Vous pouvez la modifier.";
                
            toastr()->info($message);
        }

        $this->deliberationParams['session_type'] = $sessionType;
        $this->showDeliberationModal = true;
    }


    public function render()
    {
        $deliberationStatus = [
            'session1' => $this->checkDeliberationStatus('session1'),
            'session2' => $this->checkDeliberationStatus('session2')
        ];

        return view('livewire.resultats.resultats-finale', [
            'deliberationStatus' => $deliberationStatus,
            'statistiquesDeliberation' => []
        ]);
    }
}