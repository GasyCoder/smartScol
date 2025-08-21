<?php

namespace App\Livewire\Resultats;

use App\Models\UE;
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
use App\Exports\AdmisDeliberationExport;
use App\Services\CalculAcademiqueService;

/**
 * Component Livewire pour la gestion des résultats finaux avec délibération
 *
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $anneesUniversitaires
 */
class ResultatsFinale extends Component
{
    /**
     * ✅ PROPRIÉTÉS POUR L'EXPORT AVEC CONFIGURATION
     */
    public $showExportModal = false;
    public $exportType = 'pdf'; // 'pdf' ou 'excel'
    public $exportData = 'simulation'; // 'simulation' ou 'deliberation'
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
            'decision_filter' => 'tous', // 'tous', 'admis', 'rattrapage', 'redoublant', 'exclus'
            'moyenne_min' => null,
            'moyenne_max' => null,
        ],
        'tri' => [
            'champ' => 'moyenne_generale', // 'rang', 'nom', 'moyenne_generale', 'credits_valides'
            'ordre' => 'desc' // 'asc' ou 'desc'
        ]
    ];

    // ✅ PROPRIÉTÉS FILTRES
    public $selectedNiveau;
    public $selectedParcours;
    public $selectedAnneeUniversitaire;
    // ✅ PROPRIÉTÉS OPTIONS DISPONIBLES
    public $niveaux = [];
    public $parcours = [];
    public $anneesUniversitaires = [];

    // ✅ PROPRIÉTÉS DÉLIBÉRATION
    public $deliberationParams = [
        'credits_admission_s1' => 60,
        'credits_admission_s2' => 40,
        'credits_redoublement_s2' => 20,
        'note_eliminatoire_bloque_s1' => true,
        'note_eliminatoire_exclusion_s2' => true
    ];

    public $showDeliberationModal = false;
    public $deliberationStatus = [];
    public $simulationDeliberation = [];

    // ✅ PROPRIÉTÉS ONGLETS
    public $activeTab = 'session1';

    // ✅ PROPRIÉTÉS SESSIONS
    public $sessionNormale;
    public $sessionRattrapage;
    public $showSession2 = false;

    // ✅ PROPRIÉTÉS SIMULATION
    public $simulationParams = [];
    public $simulationResults = [];

    // ✅ PROPRIÉTÉS RÉSULTATS
    public $resultatsSession1 = [];
    public $resultatsSession2 = [];
    public $statistiquesSession1 = [];
    public $statistiquesSession2 = [];
    public $uesStructure = [];

    // ✅ VALIDATION
    protected $rules = [
        'selectedNiveau' => 'required|exists:niveaux,id',
        'selectedAnneeUniversitaire' => 'required|exists:annees_universitaires,id',
    ];

    protected $messages = [
        'selectedNiveau.required' => 'Veuillez sélectionner un niveau.',
        'selectedAnneeUniversitaire.required' => 'Veuillez sélectionner une année universitaire.',
    ];
    protected $calculAcademiqueService;


    // ✅ INITIALISATION
    public function mount()
    {
        // ✅ Initialiser le service de calcul académique
        $this->calculAcademiqueService = new CalculAcademiqueService();
        // ✅ Initialiser avec les dernières valeurs de délibération
        $this->initialiserParametresDeliberation();
        $this->initializeData();
        $this->setDefaultValues();
        $this->loadResultats();
    }


    /**
     * ✅ MÉTHODE : Ouvrir le modal d'export avec configuration
     */
    public function ouvrirModalExport($type = 'pdf', $source = 'simulation')
    {
        // Vérifier qu'on a des données à exporter
        $donnees = $this->getDonneesExport($source);

        if (empty($donnees)) {
            toastr()->error("Aucune donnée disponible pour l'export. Veuillez d'abord effectuer une simulation ou délibération.");
            return;
        }

        $this->exportType = $type;
        $this->exportData = $source;
        $this->showExportModal = true;

        // Reset des filtres
        $this->exportConfig['filtres'] = [
            'decision_filter' => 'tous',
            'moyenne_min' => null,
            'moyenne_max' => null,
        ];

        toastr()->info("Configuration de l'export {$type} - " . ucfirst($source));
    }


    /**
     * ✅ MÉTHODE : Obtenir les données pour l'export selon la source
     */
    private function getDonneesExport($source)
    {
        switch ($source) {
            case 'simulation':
                return $this->simulationDeliberation['resultats_detailles'] ?? [];

            case 'deliberation':
                // Prendre les résultats de la session active
                $resultats = $this->activeTab === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;
                return $this->formatResultatsForExport($resultats);

            default:
                return [];
        }
    }


    /**
     * ✅ MÉTHODE : Formater les résultats pour l'export
     */
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

    /**
     * ✅ MÉTHODE : Fermer le modal d'export
     */
    public function fermerModalExport()
    {
        $this->showExportModal = false;
        $this->resetErrorBag(['export']);
    }


    /**
     * ✅ MÉTHODE : Appliquer les filtres et tri aux données d'export
     */
    private function appliquerFiltresExport($donnees)
    {
        $donneesCollection = collect($donnees);

        // Appliquer le filtre par décision
        if ($this->exportConfig['filtres']['decision_filter'] !== 'tous') {
            $decisionFiltre = $this->exportConfig['filtres']['decision_filter'];
            $champ = $this->exportData === 'simulation' ? 'decision_simulee' : 'decision_actuelle';
            $donneesCollection = $donneesCollection->where($champ, $decisionFiltre);
        }

        // Appliquer le filtre par moyenne
        if (!empty($this->exportConfig['filtres']['moyenne_min'])) {
            $donneesCollection = $donneesCollection->where('moyenne_generale', '>=', $this->exportConfig['filtres']['moyenne_min']);
        }

        if (!empty($this->exportConfig['filtres']['moyenne_max'])) {
            $donneesCollection = $donneesCollection->where('moyenne_generale', '<=', $this->exportConfig['filtres']['moyenne_max']);
        }

        // Appliquer le tri
        $champ = $this->exportConfig['tri']['champ'];
        $ordre = $this->exportConfig['tri']['ordre'];

        if ($ordre === 'asc') {
            $donneesCollection = $donneesCollection->sortBy($champ);
        } else {
            $donneesCollection = $donneesCollection->sortByDesc($champ);
        }

        // Recalculer les rangs après tri/filtrage
        $donneesFinales = [];
        $rang = 1;
        foreach ($donneesCollection->values() as $item) {
            $item['rang'] = $rang;
            $donneesFinales[] = $item;
            $rang++;
        }

        return $donneesFinales;
    }



    /**
     * ✅ MÉTHODE : Générer l'Excel avec configuration
     */
    private function genererExcelAvecConfig($donnees, $session, $niveau, $parcours, $anneeUniv)
    {
        try {
            $filename = $this->genererNomFichier('xlsx', $session, $niveau, $parcours, $anneeUniv);

            Log::info('Export Excel avec config généré', [
                'filename' => $filename,
                'nb_resultats' => count($donnees),
                'source' => $this->exportData,
                'colonnes' => array_keys(array_filter($this->exportConfig['colonnes']))
            ]);

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


    /**
     * ✅ MÉTHODE : Générer le nom de fichier pour l'export
     */
    private function genererNomFichier($extension, $session, $niveau, $parcours, $anneeUniv)
    {
        $sessionType = $session->type === 'Normale' ? 'Session1' : 'Session2';
        $niveauNom = str_replace(' ', '_', $niveau->nom);
        $parcoursNom = $parcours ? '_' . str_replace(' ', '_', $parcours->nom) : '';
        $anneeNom = str_replace(['/', ' '], ['_', '_'], $anneeUniv->libelle);
        $source = ucfirst($this->exportData);
        $date = now()->format('Ymd_His');

        // Ajouter info sur les filtres si appliqués
        $filtreSuffix = '';
        if ($this->exportConfig['filtres']['decision_filter'] !== 'tous') {
            $filtreSuffix .= '_' . ucfirst($this->exportConfig['filtres']['decision_filter']);
        }

        return "{$source}_{$sessionType}_{$niveauNom}{$parcoursNom}_{$anneeNom}{$filtreSuffix}_{$date}.{$extension}";
    }

    public function initializeData()
    {
        try {
            $this->anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();
            $this->niveaux = Niveau::where('is_active', true)->orderBy('id', 'asc')->get();

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'initialisation des données: ' . $e->getMessage());
            $this->anneesUniversitaires = collect();
            $this->niveaux = collect();
        }
    }

    public function setDefaultValues()
    {
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            $this->selectedAnneeUniversitaire = $anneeActive?->id;

            if ($this->niveaux->isNotEmpty()) {
                $this->selectedNiveau = $this->niveaux->first()->id;
                $this->updatedSelectedNiveau();
            }

            $this->loadSessions();
            $this->initializeSimulationParams();

        } catch (\Exception $e) {
            Log::error('Erreur lors de la définition des valeurs par défaut: ' . $e->getMessage());
        }
    }





    /**
     * ✅ MÉTHODE : Exporter rapidement les admis (raccourci)
     */
    public function exporterAdmisRapide($type = 'pdf')
    {
        try {
            // Configuration par défaut pour les admis
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


    /**
     * ✅ MÉTHODE : Reset configuration export
     */
    public function resetConfigExport()
    {
        $this->exportConfig = [
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

        toastr()->info('Configuration d\'export réinitialisée');
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

    // ✅ MÉTHODES DE CHARGEMENT DES DONNÉES
    private function loadSessions()
    {
        if (!$this->selectedAnneeUniversitaire) {
            $this->sessionNormale = null;
            $this->sessionRattrapage = null;
            $this->showSession2 = false;
            return;
        }

        try {
            $this->sessionNormale = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
                ->where('type', 'Normale')
                ->first();

            $this->sessionRattrapage = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniversitaire)
                ->where('type', 'Rattrapage')
                ->first();

            $this->checkSession2Availability();

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des sessions: ' . $e->getMessage());
            $this->sessionNormale = null;
            $this->sessionRattrapage = null;
            $this->showSession2 = false;
        }
    }

    private function loadUEStructure()
    {
        if (!$this->selectedNiveau) {
            $this->uesStructure = [];
            return;
        }

        try {
            $this->uesStructure = UE::where('niveau_id', $this->selectedNiveau)
                ->with(['ecs' => function($query) {
                    $query->orderBy('id', 'asc');
                }])
                ->orderBy('id', 'asc')
                ->get()
                ->map(function($ue) {
                    return [
                        'ue' => $ue,
                        'ecs' => $ue->ecs->map(function($ec, $index) {
                            return [
                                'ec' => $ec,
                                'display_name' => 'EC' . ($index + 1) . '. ' . $ec->nom
                            ];
                        })
                    ];
                });

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de la structure UE: ' . $e->getMessage());
            $this->uesStructure = collect();
        }
    }

    // ✅ MÉTHODES DE MISE À JOUR DES FILTRES
    public function updatedSelectedNiveau()
    {
        if ($this->selectedNiveau) {
            try {
                $niveau = Niveau::find($this->selectedNiveau);
                if ($niveau?->has_parcours) {
                    $this->parcours = Parcour::where('niveau_id', $this->selectedNiveau)
                        ->where('is_active', true)
                        ->orderBy('id', 'asc')
                        ->get();
                } else {
                    $this->parcours = collect();
                    $this->selectedParcours = null;
                }

                $this->loadUEStructure();
                $this->initialiserParametresDeliberation();

                // ✅ NOUVEAU : Recalculer les pourcentages avec le nouveau niveau
                $this->initialiserParametresAvecPourcentages();

            } catch (\Exception $e) {
                Log::error('Erreur lors de la mise à jour du niveau: ' . $e->getMessage());
                $this->parcours = collect();
                $this->selectedParcours = null;
            }
        }

        $this->checkSession2Availability();
        $this->loadResultats();
    }

    public function updatedSelectedParcours()
    {
        $this->checkSession2Availability();
        $this->loadResultats();
        $this->initialiserParametresDeliberation();
        
        // ✅ NOUVEAU : Recalculer les pourcentages avec le nouveau parcours
        $this->initialiserParametresAvecPourcentages();
        
        $this->viderCacheDeliberation();
    }


    /**
     * ✅ MÉTHODE : Vider le cache des configurations de délibération
     */
    public function viderCacheDeliberation()
    {
        try {
            // Construire la clé de cache actuelle
            $cacheKey = sprintf(
                'deliberation_config_%s_%s_%s_%s',
                $this->selectedNiveau ?? 'null',
                $this->selectedParcours ?? 'null',
                $this->sessionNormale?->id ?? 'null',
                $this->sessionRattrapage?->id ?? 'null'
            );

            // Vider le cache
            cache()->forget($cacheKey);

            // ✅ Vider aussi les caches pour toutes les combinaisons possibles si nécessaire
            $patterns = [
                "deliberation_config_{$this->selectedNiveau}_*",
                "deliberation_config_*_{$this->selectedParcours}_*",
            ];

            foreach ($patterns as $pattern) {
                // Note: Cette méthode dépend du driver de cache utilisé
                // Pour Redis: cache()->getRedis()->del(cache()->getRedis()->keys($pattern))
                // Pour file: plus complexe, nécessite d'itérer sur les fichiers
            }

            Log::info('Cache délibération vidé', [
                'cache_key' => $cacheKey,
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur vidage cache délibération: ' . $e->getMessage());
        }
    }

    public function updatedSelectedAnneeUniversitaire()
    {
        $this->loadSessions();
        $this->checkSession2Availability();
        $this->loadResultats();
    }

    // ✅ MÉTHODES DE VÉRIFICATION
    private function checkSession2Availability()
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire || !$this->sessionRattrapage) {
            $this->showSession2 = false;
            return;
        }

        try {
            $hasResultsRattrapage = ResultatFinal::where('session_exam_id', $this->sessionRattrapage->id)
                ->whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->selectedNiveau);
                    if ($this->selectedParcours) {
                        $q->where('parcours_id', $this->selectedParcours);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->exists();

            $this->showSession2 = $hasResultsRattrapage;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de la session 2: ' . $e->getMessage());
            $this->showSession2 = false;
        }
    }

    // ✅ MÉTHODES DE CHARGEMENT DES RÉSULTATS
    public function loadResultats()
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->resultatsSession1 = [];
            $this->resultatsSession2 = [];
            $this->resetValidation();
            return;
        }

        try {
            // ✅ AJOUT : Log pour debugging
            Log::info('Rechargement des résultats', [
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'annee_id' => $this->selectedAnneeUniversitaire
            ]);

            $this->resultatsSession1 = $this->loadResultatsForSession($this->sessionNormale);

            if ($this->showSession2 && $this->sessionRattrapage) {
                $this->resultatsSession2 = $this->loadResultatsForSession($this->sessionRattrapage);
            } else {
                $this->resultatsSession2 = [];
            }

            // ✅ AJOUT : Log après chargement
            Log::info('Résultats rechargés', [
                'session1_count' => count($this->resultatsSession1),
                'session2_count' => count($this->resultatsSession2)
            ]);

            if (empty($this->resultatsSession1) && !empty($this->resultatsSession2)) {
                $this->simulationParams['session_type'] = 'session2';
            } elseif (!empty($this->resultatsSession1)) {
                $this->simulationParams['session_type'] = 'session1';
            }

            $this->calculateStatistics();

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des résultats: ' . $e->getMessage());
            $this->resultatsSession1 = [];
            $this->resultatsSession2 = [];
        }
    }


    /**
     * ✅ MÉTHODE COMPLÈTE ET CORRIGÉE : loadResultatsForSession
     * Charge les résultats d'une session avec gestion du cache et enrichissement
     */
    private function loadResultatsForSession($session)
    {
        if (!$session) {
            Log::info('Session non fournie pour loadResultatsForSession');
            return [];
        }

        try {
            Log::info('🔄 Chargement résultats session', [
                'session_id' => $session->id,
                'session_type' => $session->type,
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours
            ]);

            $calculService = new CalculAcademiqueService();

            // ✅ ÉTAPE 1 : Requête avec relations optimisées et cache busting
            $query = ResultatFinal::with([
                'etudiant:id,nom,prenom,matricule', // ✅ Sélection spécifique des champs
                'ec.ue:id,nom,abr,credits',
                'examen:id,niveau_id,parcours_id'
            ])
            ->where('session_exam_id', $session->id)
            ->whereHas('examen', function($q) {
                $q->where('niveau_id', $this->selectedNiveau);
                if ($this->selectedParcours) {
                    $q->where('parcours_id', $this->selectedParcours);
                }
            })
            ->where('statut', ResultatFinal::STATUT_PUBLIE);

            // ✅ ÉTAPE 2 : Exécuter la requête avec fresh() pour éviter le cache
            $resultats = $query->get()->fresh();

            Log::info('📊 Requête résultats exécutée', [
                'session_id' => $session->id,
                'resultats_count' => $resultats->count(),
                'sample_decisions' => $resultats->take(3)->pluck('decision', 'etudiant_id')->toArray(),
                'sample_jury_validated' => $resultats->take(3)->pluck('jury_validated', 'etudiant_id')->toArray()
            ]);

            if ($resultats->isEmpty()) {
                Log::warning('⚠️ Aucun résultat trouvé pour la session', [
                    'session_id' => $session->id,
                    'niveau_id' => $this->selectedNiveau,
                    'parcours_id' => $this->selectedParcours
                ]);
                return [];
            }

            // ✅ ÉTAPE 3 : Grouper par étudiant
            $resultatsGroupes = $resultats->groupBy('etudiant_id');

            Log::info('👥 Résultats groupés', [
                'nb_etudiants' => $resultatsGroupes->count(),
                'etudiants_ids' => $resultatsGroupes->keys()->toArray()
            ]);

            // ✅ ÉTAPE 4 : Traiter chaque étudiant
            $resultatsFinaux = $resultatsGroupes->map(function($resultatsEtudiant, $etudiantId) use ($session, $calculService) {
                $etudiant = $resultatsEtudiant->first()->etudiant;

                if (!$etudiant) {
                    Log::warning('Étudiant non trouvé', ['etudiant_id' => $etudiantId]);
                    return null;
                }

                try {
                    // ✅ CALCUL COMPLET avec service académique
                    $calculComplet = $calculService->calculerResultatsComplets($etudiantId, $session->id, true);

                    // ✅ RÉCUPÉRER LES VRAIES DONNÉES DE LA DB (pas calculées)
                    $premierResultat = $resultatsEtudiant->first();
                    $decisionDB = $premierResultat->decision;
                    $juryValidatedDB = $premierResultat->jury_validated ?? false;
                    $createdAt = $premierResultat->created_at;
                    $updatedAt = $premierResultat->updated_at;

                    Log::info('👤 Étudiant traité', [
                        'etudiant_id' => $etudiantId,
                        'nom' => $etudiant->nom,
                        'decision_db' => $decisionDB,
                        'jury_validated' => $juryValidatedDB,
                        'moyenne' => $calculComplet['synthese']['moyenne_generale'],
                        'credits' => $calculComplet['synthese']['credits_valides'],
                        'updated_at' => $updatedAt
                    ]);

                    // ✅ CONSTRUCTION DU RÉSULTAT ENRICHI
                    return [
                        'etudiant' => $etudiant,
                        'notes' => $resultatsEtudiant->keyBy('ec_id'),
                        'moyennes_ue' => collect($calculComplet['resultats_ue'])->pluck('moyenne_ue', 'ue_id')->toArray(),
                        'moyenne_generale' => $calculComplet['synthese']['moyenne_generale'],
                        'credits_valides' => $calculComplet['synthese']['credits_valides'],
                        'total_credits' => $calculComplet['synthese']['total_credits'],
                        'has_note_eliminatoire' => $calculComplet['synthese']['a_note_eliminatoire'],
                        'decision' => $decisionDB, // ✅ DECISION RÉELLE DE LA DB
                        'details_ue' => $calculComplet['resultats_ue'],
                        'jury_validated' => $juryValidatedDB, // ✅ FLAG DÉLIBÉRATION
                        'decision_details' => $calculComplet['decision'],
                        // ✅ METADATA UTILES
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                        'nb_resultats' => $resultatsEtudiant->count()
                    ];

                } catch (\Exception $e) {
                    Log::error('❌ Erreur calcul résultats étudiant', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $session->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // ✅ FALLBACK vers calcul manuel en cas d'erreur
                    return $this->calculerResultatsEtudiantFallback($resultatsEtudiant, $session);
                }
            })
            ->filter() // Retirer les résultats null
            ->values(); // Reset des indices

            // ✅ ÉTAPE 5 : Enrichir avec les informations de changement
            $resultatsEnrichis = $this->enrichirResultatsAvecChangements($resultatsFinaux->toArray(), $session->id);

            // ✅ ÉTAPE 6 : Tri final par performance académique
            $resultatsTriés = collect($resultatsEnrichis)
                ->sortBy([
                    ['jury_validated', 'desc'], // Délibérés en premier
                    ['credits_valides', 'desc'], // Puis par crédits
                    ['moyenne_generale', 'desc'], // Puis par moyenne
                    ['etudiant.nom', 'asc'] // Enfin par nom
                ])
                ->values()
                ->toArray();

            Log::info('✅ Résultats session traités avec succès', [
                'session_id' => $session->id,
                'session_type' => $session->type,
                'nb_etudiants' => count($resultatsTriés),
                'decisions_repartition' => collect($resultatsTriés)->pluck('decision')->countBy()->toArray(),
                'nb_deliberes' => collect($resultatsTriés)->where('jury_validated', true)->count(),
                'nb_changements' => collect($resultatsTriés)->where('a_change', true)->count() ?? 0
            ]);

            return $resultatsTriés;

        } catch (\Exception $e) {
            Log::error('❌ Erreur critique loadResultatsForSession', [
                'session_id' => $session->id ?? 'unknown',
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [];
        }
    }


    private function calculerResultatsEtudiantFallback($resultatsEtudiant, $session)
    {
        $etudiant = $resultatsEtudiant->first()->etudiant;
        $notes = $resultatsEtudiant->keyBy('ec_id');

        $calculAcademique = $this->calculerResultatsAcademiques($resultatsEtudiant);

        return [
            'etudiant' => $etudiant,
            'notes' => $notes,
            'moyennes_ue' => $calculAcademique['moyennes_ue'],
            'moyenne_generale' => $calculAcademique['moyenne_generale'],
            'credits_valides' => $calculAcademique['credits_valides'],
            'total_credits' => $calculAcademique['total_credits'],
            'has_note_eliminatoire' => $calculAcademique['has_note_eliminatoire'],
            'decision' => $this->determinerDecision($calculAcademique, $session),
            'details_ue' => $calculAcademique['details_ue'],
            'jury_validated' => $resultatsEtudiant->first()->jury_validated ?? false
        ];
    }

    private function calculerResultatsAcademiques($resultatsEtudiant)
    {
        $moyennesUE = [];
        $creditsValides = 0;
        $totalCredits = 0;
        $hasNoteEliminatoire = false;
        $detailsUE = [];

        try {
            $resultatsParUE = $resultatsEtudiant->groupBy('ec.ue_id');

            foreach ($resultatsParUE as $ueId => $notesUE) {
                $ue = $notesUE->first()->ec->ue;
                $totalCredits += $ue->credits ?? 0;

                $notes = $notesUE->pluck('note')->toArray();
                $hasNoteZeroInUE = in_array(0, $notes);

                if ($hasNoteZeroInUE) {
                    $hasNoteEliminatoire = true;
                    $moyenneUE = 0;
                    $ueValidee = false;
                } else {
                    $moyenneUE = count($notes) > 0 ? array_sum($notes) / count($notes) : 0;
                    $moyenneUE = round($moyenneUE, 2);
                    $ueValidee = $moyenneUE >= 10;

                    if ($ueValidee) {
                        $creditsValides += $ue->credits ?? 0;
                    }
                }

                $moyennesUE[$ueId] = $moyenneUE;
                $detailsUE[] = [
                    'ue_id' => $ueId,
                    'ue_nom' => $ue->nom,
                    'ue_abr' => $ue->abr,
                    'moyenne_ue' => $moyenneUE,
                    'credits' => $ue->credits ?? 0,
                    'validee' => $ueValidee,
                    'eliminee' => $hasNoteZeroInUE,
                    'notes_ec' => $notesUE->map(function($resultat) {
                        return [
                            'ec_nom' => $resultat->ec->nom,
                            'note' => $resultat->note,
                            'est_eliminatoire' => $resultat->note == 0
                        ];
                    })->toArray()
                ];
            }

            $moyenneGenerale = count($moyennesUE) > 0 ?
                array_sum($moyennesUE) / count($moyennesUE) : 0;

            if ($hasNoteEliminatoire) {
                $moyenneGenerale = 0;
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul académique médecine: ' . $e->getMessage());
            $moyenneGenerale = 0;
        }

        return [
            'moyennes_ue' => $moyennesUE,
            'moyenne_generale' => round($moyenneGenerale, 2),
            'credits_valides' => $creditsValides,
            'total_credits' => $totalCredits,
            'has_note_eliminatoire' => $hasNoteEliminatoire,
            'details_ue' => $detailsUE
        ];
    }

    private function determinerDecision($calculAcademique, $session)
    {
        $creditsValides = $calculAcademique['credits_valides'];
        $hasNoteEliminatoire = $calculAcademique['has_note_eliminatoire'];

        if ($session->type === 'Normale') {
            // Session 1 - Logique médecine
            if ($hasNoteEliminatoire) {
                return ResultatFinal::DECISION_RATTRAPAGE;
            }
            if ($creditsValides >= 60) {
                return ResultatFinal::DECISION_ADMIS;
            }
            return ResultatFinal::DECISION_RATTRAPAGE;
        } else {
            // Session 2 - Logique médecine
            if ($hasNoteEliminatoire) {
                return ResultatFinal::DECISION_EXCLUS;
            }
            return $creditsValides >= 40 ? ResultatFinal::DECISION_ADMIS : ResultatFinal::DECISION_REDOUBLANT;
        }
    }

    private function calculateStatistics()
    {
        $this->statistiquesSession1 = $this->calculateSessionStatistics($this->resultatsSession1);
        $this->statistiquesSession2 = $this->calculateSessionStatistics($this->resultatsSession2);
    }

    private function calculateSessionStatistics($resultats)
    {
        if (empty($resultats)) {
            return [
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
        }

        try {
            $total = count($resultats);
            $decisions = array_count_values(array_column($resultats, 'decision'));
            $moyennes = array_column($resultats, 'moyenne_generale');
            $credits = array_column($resultats, 'credits_valides');
            $juryValidated = array_sum(array_column($resultats, 'jury_validated'));

            $admis = $decisions[ResultatFinal::DECISION_ADMIS] ?? 0;
            $rattrapage = $decisions[ResultatFinal::DECISION_RATTRAPAGE] ?? 0;
            $redoublant = $decisions[ResultatFinal::DECISION_REDOUBLANT] ?? 0;
            $exclus = $decisions[ResultatFinal::DECISION_EXCLUS] ?? 0;

            return [
                'total_etudiants' => $total,
                'admis' => $admis,
                'rattrapage' => $rattrapage,
                'redoublant' => $redoublant,
                'exclus' => $exclus,
                'moyenne_promo' => $total > 0 ? round(array_sum($moyennes) / $total, 2) : 0,
                'credits_moyen' => $total > 0 ? round(array_sum($credits) / $total, 2) : 0,
                'taux_reussite' => $total > 0 ? round(($admis / $total) * 100, 2) : 0,
                'jury_validated' => $juryValidated
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques: ' . $e->getMessage());
            return [
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
        }
    }

    // ✅ MÉTHODES DE DÉLIBÉRATION
    public function ouvrirDeliberation($sessionType)
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            $this->addError('deliberation', 'Veuillez sélectionner un niveau et une année.');
            return;
        }

        $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

        if (!$session) {
            $this->addError('deliberation', 'Session non trouvée.');
            return;
        }

        try {
            $calculService = new CalculAcademiqueService();

            // Charger configuration existante ou créer par défaut
            $config = $calculService->getConfigurationDeliberation(
                $this->selectedNiveau,
                $this->selectedParcours,
                $session->id
            );

            if ($config) {
                $this->deliberationParams = [
                    'session_type' => $sessionType,
                    'session_id' => $session->id,
                    'credits_admission_s1' => $config->credits_admission_s1,
                    'credits_admission_s2' => $config->credits_admission_s2,
                    'credits_redoublement_s2' => $config->credits_redoublement_s2,
                    'note_eliminatoire_bloque_s1' => $config->note_eliminatoire_bloque_s1,
                    'note_eliminatoire_exclusion_s2' => $config->note_eliminatoire_exclusion_s2
                ];

                $this->deliberationStatus = [
                    'delibere' => $config->delibere,
                    'date_deliberation' => $config->date_deliberation,
                    'delibere_par' => $config->deliberePar?->name
                ];
            } else {
                // Valeurs par défaut logique médecine
                $this->deliberationParams = [
                    'session_type' => $sessionType,
                    'session_id' => $session->id,
                    'credits_admission_s1' => 60,
                    'credits_admission_s2' => 40,
                    'credits_redoublement_s2' => 20,
                    'note_eliminatoire_bloque_s1' => true,
                    'note_eliminatoire_exclusion_s2' => true
                ];

                $this->deliberationStatus = [
                    'delibere' => false,
                    'date_deliberation' => null,
                    'delibere_par' => null
                ];
            }

            $this->showDeliberationModal = true;

        } catch (\Exception $e) {
            Log::error('Erreur ouverture modal délibération: ' . $e->getMessage());
            $this->addError('deliberation', 'Erreur lors de l\'ouverture de la délibération.');
        }
    }


    public function simulerDeliberation()
    {
        try {
            // ✅ VÉRIFICATIONS PRÉALABLES (votre code existant...)
            if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
                $this->addError('deliberation', 'Veuillez sélectionner un niveau et une année.');
                return;
            }

            // ✅ CORRECTION : S'assurer que session_id est défini
            if (!isset($this->deliberationParams['session_id']) || !$this->deliberationParams['session_id']) {
                $sessionType = $this->deliberationParams['session_type'] ?? 'session1';
                $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

                if (!$session) {
                    $this->addError('deliberation', 'Session non trouvée.');
                    return;
                }

                $this->deliberationParams['session_id'] = $session->id;
            }

            // ✅ VALIDATION (votre code existant...)
            $erreurs = $this->validerParametresDeliberation();
            if (!empty($erreurs)) {
                foreach ($erreurs as $erreur) {
                    $this->addError('deliberation', $erreur);
                }
                return;
            }

            // ✅ CORRECTION PRINCIPALE : Récupérer et calculer les résultats avec informations étudiant
            $resultatsActuels = $this->deliberationParams['session_type'] === 'session1'
                ? $this->resultatsSession1
                : $this->resultatsSession2;

            if (empty($resultatsActuels)) {
                $this->addError('deliberation', 'Aucun résultat disponible pour la simulation');
                return;
            }

            $resultatsDetailles = [];
            $statistiques = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'changements' => 0
            ];

            foreach ($resultatsActuels as $index => $resultat) {
                // ✅ CORRECTION : Assurer que les informations étudiant sont complètes
                $etudiant = $resultat['etudiant'] ?? null;

                if (!$etudiant) {
                    continue; // Skip si pas d'étudiant
                }

                // Calculer la décision simulée selon les paramètres
                $decisionSimulee = $this->calculerDecisionSelonParametres($resultat);
                $decisionActuelle = $resultat['decision'] ?? 'rattrapage';

                $changement = $decisionActuelle !== $decisionSimulee;
                if ($changement) {
                    $statistiques['changements']++;
                }

                $statistiques[$decisionSimulee]++;

                // ✅ STRUCTURER CORRECTEMENT LES DONNÉES POUR LA VUE
                $resultatsDetailles[] = [
                    'etudiant_id' => $etudiant->id,
                    'etudiant' => $etudiant, // ✅ Objet Eloquent complet
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
                    'changement' => $changement
                ];
            }

            // ✅ STRUCTURE FINALE POUR LA VUE
            $this->simulationDeliberation = [
                'success' => true,
                'total_etudiants' => count($resultatsDetailles),
                'statistiques' => $statistiques,
                'resultats_detailles' => $resultatsDetailles,
                'parametres_utilises' => $this->deliberationParams
            ];

            // Message de succès
            $sessionName = $this->deliberationParams['session_type'] === 'session1' ? 'Session 1' : 'Session 2';
            toastr()->info(
                "🔍 Simulation {$sessionName} : {$statistiques['changements']} changements détectés. " .
                "Nouveaux résultats : {$statistiques['admis']} admis, {$statistiques['rattrapage']} rattrapage, " .
                "{$statistiques['redoublant']} redoublant, {$statistiques['exclus']} exclus"
            );

            Log::info('Simulation délibération réussie', [
                'session_id' => $this->deliberationParams['session_id'],
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'statistiques' => $statistiques
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur simulation délibération Livewire: ' . $e->getMessage());
            $this->addError('deliberation', 'Erreur lors de la simulation: ' . $e->getMessage());
        }
    }


    // ✅ NOUVELLE MÉTHODE : Calculer la décision selon les paramètres
    private function calculerDecisionSelonParametres($resultat)
    {
        $sessionType = $this->deliberationParams['session_type'] ?? 'session1';
        $creditsValides = $resultat['credits_valides'] ?? 0;
        $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;

        if ($sessionType === 'session1') {
            // ✅ LOGIQUE SESSION 1 (NORMALE)
            $creditsRequis = $this->deliberationParams['credits_admission_s1'] ?? 60;
            $bloquerSiNote0 = $this->deliberationParams['note_eliminatoire_bloque_s1'] ?? true;

            // Si note éliminatoire et option activée
            if ($hasNoteEliminatoire && $bloquerSiNote0) {
                return 'rattrapage';
            }

            // Sinon, selon les crédits
            return $creditsValides >= $creditsRequis ? 'admis' : 'rattrapage';

        } else {
            // ✅ LOGIQUE SESSION 2 (RATTRAPAGE)
            $creditsAdmission = $this->deliberationParams['credits_admission_s2'] ?? 40;
            $creditsRedoublement = $this->deliberationParams['credits_redoublement_s2'] ?? 20;
            $exclusionSiNote0 = $this->deliberationParams['note_eliminatoire_exclusion_s2'] ?? true;

            // Si note éliminatoire et option activée
            if ($hasNoteEliminatoire && $exclusionSiNote0) {
                return 'exclus';
            }

            // Sinon, selon les crédits
            if ($creditsValides >= $creditsAdmission) {
                return 'admis';
            } elseif ($creditsValides >= $creditsRedoublement) {
                return 'redoublant';
            } else {
                return 'exclus';
            }
        }
    }




    /**
     * Appliquer la délibération pour une session donnée
     */
    public function appliquerDeliberation(): void
    {
        try {
            $this->validateDeliberationPrerequisites();
            $session = $this->getTargetSession();
            $this->validateStudentsAvailability($session);

            $result = $this->executeDeliberation($session);

            if ($result['success']) {
                $this->handleDeliberationSuccess($result, $session);
            } else {
                $this->handleDeliberationFailure($result, $session);
            }
        } catch (\Exception $e) {
            $this->handleDeliberationError($e);
        }
    }

    /**
     * Valider les prérequis pour la délibération
     */
    private function validateDeliberationPrerequisites(): void
    {
        if (!$this->calculAcademiqueService) {
            $this->calculAcademiqueService = new CalculAcademiqueService();
        }

        if (empty($this->deliberationParams)) {
            throw new \Exception('Paramètres de délibération manquants.');
        }

        if (!Auth::user()->can('resultats.validation')) {
            throw new \Exception('Vous n\'avez pas l\'autorisation d\'appliquer une délibération.');
        }
    }

    /**
     * Récupérer la session cible pour la délibération
     */
    private function getTargetSession(): SessionExam
    {
        $sessionType = $this->deliberationParams['session_type'] ?? 'session1';
        $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

        if (!$session) {
            throw new \Exception("Session {$sessionType} non trouvée.");
        }

        return $session;
    }

    /**
     * Valider la disponibilité des étudiants pour la session
     */
    private function validateStudentsAvailability(SessionExam $session): void
    {
        $countEtudiants = ResultatFinal::where('session_exam_id', $session->id)
            ->whereHas('examen', function($q) {
                $q->where('niveau_id', $this->selectedNiveau);
                if ($this->selectedParcours) {
                    $q->where('parcours_id', $this->selectedParcours);
                }
            })
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->distinct('etudiant_id')
            ->count();

        if ($countEtudiants === 0) {
            $sessionType = $this->deliberationParams['session_type'] ?? 'session1';
            throw new \Exception("Aucun étudiant trouvé pour la session {$sessionType} (ID: {$session->id}). Vérifiez que les résultats sont publiés.");
        }

        Log::info('🎯 Délibération - Vérifications OK', [
            'session_type' => $this->deliberationParams['session_type'] ?? 'session1',
            'session_id' => $session->id,
            'session_nom' => $session->nom ?? 'N/A',
            'nb_etudiants' => $countEtudiants,
            'niveau_id' => $this->selectedNiveau,
            'parcours_id' => $this->selectedParcours
        ]);
    }

    /**
     * Exécuter la délibération
     */
    private function executeDeliberation(SessionExam $session): array
    {
        return $this->calculAcademiqueService->appliquerDeliberationAvecConfig(
            $this->selectedNiveau,
            $this->selectedParcours,
            $session->id,
            $this->deliberationParams
        );
    }

    /**
     * Gérer le succès de la délibération
     */
    private function handleDeliberationSuccess(array $result, SessionExam $session): void
    {
        $sessionType = $this->deliberationParams['session_type'] ?? 'session1';

        // Message de succès
        $statsMessage = collect($result['statistiques'])
            ->map(fn($count, $decision) => ucfirst($decision) . ': ' . $count)
            ->implode(', ');

        toastr()->success('Délibération appliquée avec succès. Statistiques: ' . $statsMessage);

        // Log de succès
        Log::info('Délibération appliquée', [
            'session_id' => $session->id,
            'session_type' => $sessionType,
            'niveau_id' => $this->selectedNiveau,
            'parcours_id' => $this->selectedParcours,
            'statistiques' => $result['statistiques'],
            'user_id' => Auth::id()
        ]);

        // Mise à jour du statut
        $this->updateDeliberationStatus();

        // Réinitialisation et rechargement
        $this->resetAfterDeliberation($sessionType);
    }

    /**
     * Gérer l'échec de la délibération
     */
    private function handleDeliberationFailure(array $result, SessionExam $session): void
    {
        $sessionType = $this->deliberationParams['session_type'] ?? 'session1';

        $this->addError('deliberation', $result['message']);
        toastr()->error($result['message']);

        Log::error('Échec application délibération', [
            'message' => $result['message'],
            'session_id' => $session->id,
            'session_type' => $sessionType,
            'params' => $this->deliberationParams
        ]);
    }

    /**
     * Gérer les erreurs de délibération
     */
    private function handleDeliberationError(\Exception $e): void
    {
        Log::error('Erreur application délibération Livewire: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'params' => $this->deliberationParams,
            'session_type' => $this->deliberationParams['session_type'] ?? 'non_defini',
            'niveau_id' => $this->selectedNiveau,
            'parcours_id' => $this->selectedParcours
        ]);

        $this->addError('deliberation', 'Erreur lors de l\'application: ' . $e->getMessage());
        toastr()->error('Erreur lors de l\'application de la délibération: ' . $e->getMessage());
    }

    /**
     * Mettre à jour le statut de délibération
     */
    private function updateDeliberationStatus(): void
    {
        $this->deliberationStatus = [
            'delibere' => true,
            'date_deliberation' => now(),
            'delibere_par' => Auth::user()->name
        ];
    }




    /**
     * ✅ MÉTHODE AMÉLIORÉE : Force le rechargement complet
     */
    public function forceReloadData()
    {
        try {
            Log::info('🔄 FORCE RELOAD DATA - Début');

            // ✅ ÉTAPE 1 : Vider complètement le cache
            $this->resultatsSession1 = [];
            $this->resultatsSession2 = [];
            $this->statistiquesSession1 = [];
            $this->statistiquesSession2 = [];

            // ✅ ÉTAPE 2 : Vider le cache Eloquent
            \Illuminate\Database\Eloquent\Model::clearBootedModels();

            // ✅ ÉTAPE 3 : Recharger les sessions
            $this->loadSessions();

            // ✅ ÉTAPE 4 : Recharger avec requête SQL directe
            if ($this->sessionNormale) {
                $this->resultatsSession1 = $this->loadResultatsForSessionWithFreshQuery($this->sessionNormale);
            }

            if ($this->showSession2 && $this->sessionRattrapage) {
                $this->resultatsSession2 = $this->loadResultatsForSessionWithFreshQuery($this->sessionRattrapage);
            }

            // ✅ ÉTAPE 5 : Recalculer les statistiques
            $this->calculateStatistics();

            // ✅ ÉTAPE 6 : Message de succès
            toastr()->success('✅ Données rechargées avec succès');

            Log::info('✅ FORCE RELOAD DATA - Terminé', [
                'session1_count' => count($this->resultatsSession1),
                'session2_count' => count($this->resultatsSession2),
                'stats_s1_admis' => $this->statistiquesSession1['admis'] ?? 0,
                'stats_s1_rattrapage' => $this->statistiquesSession1['rattrapage'] ?? 0
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erreur force reload: ' . $e->getMessage());
            toastr()->error('Erreur lors du rechargement des données');
        }
    }
    /**
     * ✅ SOLUTION 2 : Réinitialiser après délibération avec rechargement forcé
     */
    private function resetAfterDeliberation(string $sessionType): void
    {
        try {
            Log::info('🔄 Reset après délibération - Début', ['session_type' => $sessionType]);

            // ✅ ÉTAPE 1 : Fermer les modals
            $this->showDeliberationModal = false;
            $this->simulationDeliberation = [];

            // ✅ ÉTAPE 2 : Vider COMPLÈTEMENT les données
            $this->reset([
                'resultatsSession1',
                'resultatsSession2',
                'statistiquesSession1',
                'statistiquesSession2'
            ]);

            // ✅ ÉTAPE 3 : Attendre que la transaction soit commitée
            usleep(200000); // 200ms

            // ✅ ÉTAPE 4 : Vider le cache Eloquent
            \Illuminate\Database\Eloquent\Model::clearBootedModels();

            // ✅ ÉTAPE 5 : Force refresh avec méthode publique
            Log::info('🔄 Avant refreshResultats');
            $this->refreshResultats(); // Utiliser la méthode publique au lieu de forceReloadData
            Log::info('✅ Après refreshResultats');

            // ✅ ÉTAPE 6 : Vérifier que les données ont bien changé
            $this->verifierChangementsApresDeliberation($sessionType);

            // ✅ ÉTAPE 7 : Dispatch des événements
            $this->dispatch('force-page-refresh');
            $this->dispatch('resultatsActualises', [
                'session' => $sessionType,
                'timestamp' => now()->timestamp,
                'nouvelles_stats' => $sessionType === 'session1' ? $this->statistiquesSession1 : $this->statistiquesSession2
            ]);

            Log::info('✅ Reset après délibération - Terminé', [
                'session_type' => $sessionType,
                'nouvelles_stats' => $sessionType === 'session1' ? $this->statistiquesSession1 : $this->statistiquesSession2
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erreur reset après délibération: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Charge les résultats avec requête complètement fraîche
     */
    private function loadResultatsForSessionWithFreshQuery($session)
    {
        if (!$session) return [];

        try {
            $calculService = new CalculAcademiqueService();

            // ✅ REQUÊTE COMPLÈTEMENT FRAÎCHE sans cache
            $resultats = DB::table('resultats_finaux as rf')
                ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                ->join('ecs as ec', 'rf.ec_id', '=', 'ec.id')
                ->join('ues as ue', 'ec.ue_id', '=', 'ue.id')
                ->join('examens as ex', 'rf.examen_id', '=', 'ex.id')
                ->where('rf.session_exam_id', $session->id)
                ->where('ex.niveau_id', $this->selectedNiveau)
                ->when($this->selectedParcours, function($q) {
                    $q->where('ex.parcours_id', $this->selectedParcours);
                })
                ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                ->select([
                    'rf.*',
                    'e.id as etudiant_id',
                    'e.nom',
                    'e.prenom',
                    'e.matricule',
                    'ec.nom as ec_nom',
                    'ue.id as ue_id',
                    'ue.nom as ue_nom'
                ])
                ->get();

            Log::info('📊 Requête SQL FRESH exécutée', [
                'session_id' => $session->id,
                'session_type' => $session->type,
                'resultats_count' => $resultats->count(),
                'sample_decisions' => $resultats->take(3)->pluck('decision', 'etudiant_id')->toArray()
            ]);

            if ($resultats->isEmpty()) {
                Log::warning('⚠️ Aucun résultat FRESH trouvé');
                return [];
            }

            // ✅ Reconstituer les objets Eloquent
            $etudiantsData = $resultats->groupBy('etudiant_id');

            $resultatsFinaux = $etudiantsData->map(function($resultatsEtudiant, $etudiantId) use ($session, $calculService) {

                // Récupérer l'étudiant complet
                $etudiant = Etudiant::find($etudiantId);

                if (!$etudiant) {
                    Log::warning('Étudiant non trouvé', ['etudiant_id' => $etudiantId]);
                    return null;
                }

                try {
                    // ✅ RECALCUL COMPLET avec données fraîches
                    $calculComplet = $calculService->calculerResultatsComplets($etudiantId, $session->id, true);

                    // ✅ VÉRIFIER LA DÉCISION DEPUIS LA DB (pas le calcul)
                    $decisionDB = $resultatsEtudiant->first()->decision;
                    $juryValidatedDB = $resultatsEtudiant->first()->jury_validated;

                    Log::info('👤 Étudiant traité FRESH', [
                        'etudiant_id' => $etudiantId,
                        'nom' => $etudiant->nom,
                        'decision_db' => $decisionDB,
                        'jury_validated' => $juryValidatedDB,
                        'moyenne' => $calculComplet['synthese']['moyenne_generale']
                    ]);

                    return [
                        'etudiant' => $etudiant,
                        'notes' => $resultatsEtudiant->keyBy('ec_id'),
                        'moyennes_ue' => collect($calculComplet['resultats_ue'])->pluck('moyenne_ue', 'ue_id')->toArray(),
                        'moyenne_generale' => $calculComplet['synthese']['moyenne_generale'],
                        'credits_valides' => $calculComplet['synthese']['credits_valides'],
                        'total_credits' => $calculComplet['synthese']['total_credits'],
                        'has_note_eliminatoire' => $calculComplet['synthese']['a_note_eliminatoire'],
                        'decision' => $decisionDB, // ✅ UTILISER LA DÉCISION DE LA DB
                        'details_ue' => $calculComplet['resultats_ue'],
                        'jury_validated' => $juryValidatedDB,
                        'decision_details' => $calculComplet['decision']
                    ];

                } catch (\Exception $e) {
                    Log::error('❌ Erreur calcul résultats étudiant FRESH', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $session->id,
                        'error' => $e->getMessage()
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

            Log::info('✅ Résultats FRESH traités', [
                'session_type' => $session->type,
                'nb_etudiants' => count($resultatsFinaux),
                'decisions_repartition' => collect($resultatsFinaux)->pluck('decision')->countBy()->toArray()
            ]);

            return $resultatsFinaux;

        } catch (\Exception $e) {
            Log::error('❌ Erreur lors du chargement FRESH: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Vérifie que les changements sont bien appliqués
     */
    private function verifierChangementsApresDeliberation(string $sessionType)
    {
        try {
            $resultats = $sessionType === 'session1' ? $this->resultatsSession1 : $this->resultatsSession2;
            $stats = $sessionType === 'session1' ? $this->statistiquesSession1 : $this->statistiquesSession2;

            Log::info('🔍 Vérification changements après délibération', [
                'session_type' => $sessionType,
                'nb_resultats_charges' => count($resultats),
                'stats_admis' => $stats['admis'] ?? 0,
                'stats_rattrapage' => $stats['rattrapage'] ?? 0,
                'sample_decisions' => collect($resultats)->take(3)->pluck('decision', 'etudiant.nom')->toArray()
            ]);

            // Vérifier en base directement
            $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

            if ($session) {
                $statsDB = DB::table('resultats_finaux as rf')
                    ->join('examens as ex', 'rf.examen_id', '=', 'ex.id')
                    ->where('rf.session_exam_id', $session->id)
                    ->where('ex.niveau_id', $this->selectedNiveau)
                    ->when($this->selectedParcours, function($q) {
                        $q->where('ex.parcours_id', $this->selectedParcours);
                    })
                    ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                    ->selectRaw('
                        rf.decision,
                        COUNT(DISTINCT rf.etudiant_id) as nb,
                        COUNT(CASE WHEN rf.jury_validated = 1 THEN 1 END) as nb_jury
                    ')
                    ->groupBy('rf.decision')
                    ->get()
                    ->keyBy('decision');

                Log::info('📊 Vérification base de données', [
                    'session_id' => $session->id,
                    'stats_db' => $statsDB->toArray()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('❌ Erreur vérification changements: ' . $e->getMessage());
        }
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Actualise toutes les données après une délibération
     */
    private function actualiserDonneesApresDeliberation()
    {
        try {
            // 1. Recharger les résultats avec les nouvelles décisions
            $this->loadResultats();

            // 2. Recalculer les statistiques avec les nouvelles données
            $this->calculateStatistics();

            // 3. Vérifier le statut de délibération pour les deux sessions
            $this->rafraichirStatutsDeliberation();

            // 4. Reset les simulations car les données ont changé
            $this->simulationResults = [];
            $this->simulationDeliberation = [];

            // 5. Forcer la mise à jour de l'onglet actif si nécessaire
            $this->dispatch('donneesDeliberationMisesAJour', [
                'session' => $this->deliberationParams['session_type'],
                'statistiques' => $this->activeTab === 'session1' ? $this->statistiquesSession1 : $this->statistiquesSession2
            ]);

            Log::info('Données actualisées après délibération', [
                'session_type' => $this->deliberationParams['session_type'],
                'resultats_session1' => count($this->resultatsSession1),
                'resultats_session2' => count($this->resultatsSession2)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur actualisation après délibération: ' . $e->getMessage());
            toastr()->warning('Données partiellement actualisées. Veuillez rafraîchir la page si nécessaire.');
        }
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Rafraîchit les statuts de délibération
     */
    private function rafraichirStatutsDeliberation()
    {
        try {
            $calculService = new CalculAcademiqueService();

            // Vérifier session 1
            if ($this->sessionNormale) {
                $configS1 = $calculService->getConfigurationDeliberation(
                    $this->selectedNiveau,
                    $this->selectedParcours,
                    $this->sessionNormale->id
                );

                if ($configS1) {
                    $this->deliberationStatus['session1'] = [
                        'delibere' => $configS1->delibere,
                        'date_deliberation' => $configS1->date_deliberation,
                        'delibere_par' => $configS1->deliberePar?->name
                    ];
                }
            }

            // Vérifier session 2
            if ($this->sessionRattrapage) {
                $configS2 = $calculService->getConfigurationDeliberation(
                    $this->selectedNiveau,
                    $this->selectedParcours,
                    $this->sessionRattrapage->id
                );

                if ($configS2) {
                    $this->deliberationStatus['session2'] = [
                        'delibere' => $configS2->delibere,
                        'date_deliberation' => $configS2->date_deliberation,
                        'delibere_par' => $configS2->deliberePar?->name
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Erreur rafraîchissement statuts délibération: ' . $e->getMessage());
        }
    }

    public function annulerDeliberation($sessionType)
    {
        try {
            $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

            if (!$session) {
                $this->addError('deliberation', 'Session non trouvée.');
                return;
            }

            $calculService = new CalculAcademiqueService();

            $result = $calculService->annulerDeliberationAvecConfig(
                $this->selectedNiveau,
                $this->selectedParcours,
                $session->id
            );

            if ($result['success']) {
                toastr()->success('✅ Délibération annulée avec succès');

                $this->deliberationStatus = [
                    'delibere' => false,
                    'date_deliberation' => null,
                    'delibere_par' => null
                ];

                // ✅ AJOUT : Actualiser les données après annulation
                $this->actualiserDonneesApresDeliberation();

                Log::info('Délibération annulée', [
                    'session_id' => $session->id,
                    'niveau_id' => $this->selectedNiveau,
                    'parcours_id' => $this->selectedParcours
                ]);

            } else {
                $this->addError('deliberation', $result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Erreur annulation délibération Livewire: ' . $e->getMessage());
            $this->addError('deliberation', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }
    }

    public function checkDeliberationStatus($sessionType)
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            return false;
        }

        $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

        if (!$session) {
            return false;
        }

        try {
            $calculService = new CalculAcademiqueService();
            return $calculService->estDelibere(
                $this->selectedNiveau,
                $this->selectedParcours,
                $session->id
            );
        } catch (\Exception $e) {
            Log::error('Erreur vérification statut délibération: ' . $e->getMessage());
            return false;
        }
    }

    // ✅ VALIDATION DES PARAMÈTRES
    private function validerParametresDeliberation()
    {
        $erreurs = [];

        // Validation crédits session 1
        if ($this->deliberationParams['credits_admission_s1'] < 40 ||
            $this->deliberationParams['credits_admission_s1'] > 60) {
            $erreurs[] = 'Les crédits session 1 doivent être entre 40 et 60.';
        }

        // Validation crédits session 2
        if ($this->deliberationParams['credits_admission_s2'] < 30 ||
            $this->deliberationParams['credits_admission_s2'] > 50) {
            $erreurs[] = 'Les crédits session 2 doivent être entre 30 et 50.';
        }

        // Validation cohérence
        if ($this->deliberationParams['credits_redoublement_s2'] >=
            $this->deliberationParams['credits_admission_s2']) {
            $erreurs[] = 'Les crédits de redoublement doivent être inférieurs aux crédits d\'admission.';
        }

        return $erreurs;
    }

    // ✅ FERMER MODAL
    public function fermerDeliberationModal()
    {
        $this->showDeliberationModal = false;
        $this->simulationDeliberation = [];
        $this->resetErrorBag(['deliberation']);
    }

    // ✅ RESET SIMULATION
    public function resetSimulationDeliberation()
    {
        $this->simulationDeliberation = [];
    }

    // ✅ OBTENIR STATISTIQUES DÉLIBÉRATION
    public function getStatistiquesDeliberation($sessionType)
    {
        if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
            return null;
        }

        $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

        if (!$session) {
            return null;
        }

        try {
            $calculService = new CalculAcademiqueService();
            return $calculService->getStatistiquesDeliberation(
                $this->selectedNiveau,
                $this->selectedParcours,
                $session->id
            );
        } catch (\Exception $e) {
            Log::error('Erreur récupération stats délibération: ' . $e->getMessage());
            return null;
        }
    }

    // ✅ MÉTHODES D'EXPORT
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

            $data = [
                'resultats' => $resultats,
                'niveau' => $niveau,
                'parcours' => $parcours,
                'session' => $session,
                'annee_universitaire' => $anneeUniv,
                'statistiques' => $this->activeTab === 'session1' ? $this->statistiquesSession1 : $this->statistiquesSession2,
                'date_export' => now()->format('d/m/Y H:i'),
                'export_par' => Auth::user()->name,
                'ues_structure' => $this->uesStructure
            ];

            $pdf = Pdf::loadView('livewire.resultats.export-pdf', $data)
                ->setPaper('a4', 'landscape');

            $filename = sprintf(
                'resultats_%s_%s_%s_%s.pdf',
                $niveau->nom,
                $parcours ? $parcours->nom : 'Tous',
                $session->type,
                $anneeUniv->libelle
            );

            Log::info('Export PDF généré', [
                'filename' => $filename,
                'nb_resultats' => count($resultats),
                'session_type' => $session->type
            ]);

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export PDF: ' . $e->getMessage());
            toastr()->error('Erreur lors de l\'export PDF: ' . $e->getMessage());
        }
    }

    public function exportExcel()
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

            $filename = sprintf(
                'resultats_%s_%s_%s_%s.xlsx',
                $niveau->nom,
                $parcours ? $parcours->nom : 'Tous',
                $session->type,
                $anneeUniv->libelle
            );

            Log::info('Export Excel généré', [
                'filename' => $filename,
                'nb_resultats' => count($resultats),
                'session_type' => $session->type
            ]);

            return Excel::download(
                new ResultatsExport($resultats, $this->uesStructure, $session, $niveau, $parcours, $anneeUniv),
                $filename
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export Excel: ' . $e->getMessage());
            toastr()->error('Erreur lors de l\'export Excel: ' . $e->getMessage());
        }
    }

    // ✅ MÉTHODES D'ONGLETS
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->simulationParams['session_type'] = $tab;

        // ✅ CORRECTION : Mettre à jour aussi deliberationParams
        $session = $tab === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;
        if ($session) {
            $this->deliberationParams['session_type'] = $tab;
            $this->deliberationParams['session_id'] = $session->id;
        }
    }

    // ✅ MÉTHODES UTILITAIRES
    public function getResultatNote($notes, $ecId)
    {
        return $notes->get($ecId)?->note ?? '-';
    }

    public function getClasseNote($note)
    {
        if ($note === '-' || $note === null) {
            return 'text-gray-400';
        }

        $noteNum = (float) $note;

        if ($noteNum == 0) {
            return 'text-red-600 font-bold bg-red-50';
        } elseif ($noteNum < 10) {
            return 'text-red-500';
        } elseif ($noteNum < 12) {
            return 'text-orange-500';
        } elseif ($noteNum < 14) {
            return 'text-blue-500';
        } else {
            return 'text-green-600 font-semibold';
        }
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
            case ResultatFinal::DECISION_ADMIS:
                return 'Admis';
            case ResultatFinal::DECISION_RATTRAPAGE:
                return 'Rattrapage';
            case ResultatFinal::DECISION_REDOUBLANT:
                return 'Redoublant';
            case ResultatFinal::DECISION_EXCLUS:
                return 'Exclu';
            default:
                return 'Indéterminé';
        }
    }

    // ✅ MÉTHODES DE SIMULATION (CONSERVATION DE L'EXISTANT)
    public function simulerDecisions()
    {
        try {
            if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
                toastr()->error('Veuillez sélectionner un niveau et une année universitaire.');
                return;
            }

            $resultats = $this->simulationParams['session_type'] === 'session1' ?
                $this->resultatsSession1 : $this->resultatsSession2;

            if (empty($resultats)) {
                toastr()->error('Aucun résultat à simuler.');
                return;
            }

            $stats = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'changements' => 0
            ];

            $this->simulationResults = [];

            foreach ($resultats as $index => $resultat) {
                $nouvelleDécision = $this->simulerDecisionEtudiant($resultat);
                $changement = $resultat['decision'] !== $nouvelleDécision;

                if ($changement) {
                    $stats['changements']++;
                }

                $stats[$nouvelleDécision]++;

                $this->simulationResults[] = [
                    'etudiant' => $resultat['etudiant'],
                    'decision_actuelle' => $resultat['decision'],
                    'nouvelle_decision' => $nouvelleDécision,
                    'changement' => $changement,
                    'moyenne_generale' => $resultat['moyenne_generale'],
                    'credits_valides' => $resultat['credits_valides']
                ];
            }

            $sessionName = $this->simulationParams['session_type'] === 'session1' ? 'Session 1' : 'Session 2';

            toastr()->info(
                "🔍 Simulation {$sessionName}: {$stats['changements']} changements détectés. " .
                "Nouveaux résultats: {$stats['admis']} admis, {$stats['rattrapage']} rattrapage, " .
                "{$stats['redoublant']} redoublant, {$stats['exclus']} exclus"
            );

            Log::info('Simulation de décisions terminée', [
                'session_type' => $this->simulationParams['session_type'],
                'niveau_id' => $this->selectedNiveau,
                'statistiques' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la simulation: ' . $e->getMessage());
            toastr()->error('Erreur lors de la simulation: ' . $e->getMessage());
        }
    }

    private function simulerDecisionEtudiant($resultat)
    {
        $creditsValides = $resultat['credits_valides'];
        $hasNoteEliminatoire = $resultat['has_note_eliminatoire'];
        $sessionType = $this->simulationParams['session_type'];

        if ($sessionType === 'session1') {
            $creditsRequis = $this->simulationParams['credits_admission_session1'];
            $appliquerEliminatoire = $this->simulationParams['appliquer_note_eliminatoire_s1'];

            if ($appliquerEliminatoire && $hasNoteEliminatoire) {
                return ResultatFinal::DECISION_RATTRAPAGE;
            }

            return $creditsValides >= $creditsRequis ?
                ResultatFinal::DECISION_ADMIS : ResultatFinal::DECISION_RATTRAPAGE;
        } else {
            $creditsAdmission = $this->simulationParams['credits_admission_session2'];
            $creditsRedoublement = $this->simulationParams['credits_redoublement_session2'];
            $appliquerEliminatoire = $this->simulationParams['appliquer_note_eliminatoire_s2'];

            if ($appliquerEliminatoire && $hasNoteEliminatoire) {
                return ResultatFinal::DECISION_EXCLUS;
            }

            if ($creditsValides >= $creditsAdmission) {
                return ResultatFinal::DECISION_ADMIS;
            } elseif ($creditsValides >= $creditsRedoublement) {
                return ResultatFinal::DECISION_REDOUBLANT;
            } else {
                return ResultatFinal::DECISION_EXCLUS;
            }
        }
    }

    public function resetSimulation()
    {
        $this->simulationResults = [];
        $this->initializeSimulationParams();
        toastr()->info('Simulation réinitialisée.');
    }

    public function appliquerSimulation()
    {
        try {
            if (empty($this->simulationResults)) {
                toastr()->error('Aucune simulation à appliquer. Veuillez d\'abord simuler les décisions.');
                return;
            }

            $sessionId = $this->simulationParams['session_type'] === 'session1' ?
                $this->sessionNormale->id : $this->sessionRattrapage->id;

            $changementsAppliques = 0;

            DB::beginTransaction();

            foreach ($this->simulationResults as $simulation) {
                if ($simulation['changement']) {
                    $this->appliquerDecisionEtudiant(
                        $simulation['etudiant']->id,
                        $sessionId,
                        $simulation['nouvelle_decision']
                    );
                    $changementsAppliques++;
                }
            }

            DB::commit();

            $sessionName = $this->simulationParams['session_type'] === 'session1' ? 'Session 1' : 'Session 2';

            toastr()->success("✅ Simulation appliquée! {$changementsAppliques} décisions mises à jour en {$sessionName}.");

            $this->simulationResults = [];
            $this->loadResultats();

            Log::info('Simulation appliquée avec succès', [
                'session_id' => $sessionId,
                'changements_appliques' => $changementsAppliques
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'application de la simulation: ' . $e->getMessage());
            toastr()->error('Erreur lors de l\'application: ' . $e->getMessage());
        }
    }

    private function appliquerDecisionEtudiant($etudiantId, $sessionId, $nouvelleDecision)
    {
        $resultats = ResultatFinal::where('session_exam_id', $sessionId)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->get();

        foreach ($resultats as $resultat) {
            $ancienneDecision = $resultat->decision;

            $resultat->update([
                'decision' => $nouvelleDecision,
                'modifie_par' => Auth::id()
            ]);

            // ✅ CORRECTION : Utiliser la nouvelle méthode
            if (class_exists('App\Models\ResultatFinalHistorique')) {
                ResultatFinalHistorique::creerEntreeSimulationAppliquee(
                    $resultat->id,
                    $ancienneDecision,
                    $nouvelleDecision,
                    Auth::id(),
                    $this->simulationParams
                );
            }
        }
    }

    // ✅ MÉTHODES POUR APPLIQUER LA LOGIQUE MÉDECINE STANDARD
    public function appliquerLogiqueStandard($sessionType)
    {
        try {
            if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
                toastr()->error('Veuillez sélectionner un niveau et une année universitaire.');
                return;
            }

            $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

            if (!$session) {
                toastr()->error('Session non trouvée.');
                return;
            }

            $calculService = new CalculAcademiqueService();

            // Appliquer avec paramètres par défaut logique médecine
            $result = $calculService->appliquerDecisionsSession($session->id, true, false);

            if ($result['success']) {
                $stats = $result['statistiques'];
                $sessionName = $sessionType === 'session1' ? 'Session 1' : 'Session 2';

                toastr()->success(
                    "✅ Logique médecine standard appliquée en {$sessionName}! " .
                    "Résultats : {$stats['decisions']['admis']} admis, {$stats['decisions']['rattrapage']} rattrapage, " .
                    "{$stats['decisions']['redoublant']} redoublant, {$stats['decisions']['exclus']} exclus"
                );

                $this->loadResultats(); // Recharger les résultats

                Log::info('Logique médecine standard appliquée', [
                    'session_id' => $session->id,
                    'session_type' => $sessionType,
                    'niveau_id' => $this->selectedNiveau,
                    'parcours_id' => $this->selectedParcours,
                    'statistiques' => $stats
                ]);

            } else {
                toastr()->error('Erreur lors de l\'application: ' . $result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Erreur application logique médecine standard: ' . $e->getMessage());
            toastr()->error('Erreur lors de l\'application de la logique standard: ' . $e->getMessage());
        }
    }

    // ✅ MÉTHODES DE RECALCUL
    public function recalculerTout()
    {
        try {
            if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
                toastr()->error('Veuillez sélectionner un niveau et une année universitaire.');
                return;
            }

            // Recalculer Session 1
            if ($this->sessionNormale) {
                $this->appliquerLogiqueStandard('session1');
            }

            // Recalculer Session 2 si disponible
            if ($this->showSession2 && $this->sessionRattrapage) {
                $this->appliquerLogiqueStandard('session2');
            }

            toastr()->success('✅ Recalcul terminé pour toutes les sessions disponibles!');

        } catch (\Exception $e) {
            Log::error('Erreur recalcul tout: ' . $e->getMessage());
            toastr()->error('Erreur lors du recalcul: ' . $e->getMessage());
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

            Log::info('Données rafraîchies avec succès', [
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'annee_id' => $this->selectedAnneeUniversitaire,
                'session1_count' => count($this->resultatsSession1),
                'session2_count' => count($this->resultatsSession2)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du rafraîchissement des données: ' . $e->getMessage());
            toastr()->error('Erreur lors du rafraîchissement: ' . $e->getMessage());
        }
    }


    // ✅ SOLUTION 4 : Méthode de refresh manuel améliorée
    public function refreshResultats()
    {
        try {
            if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
                toastr()->warning('Veuillez sélectionner un niveau et une année universitaire.');
                return;
            }

            Log::info('🔄 Refresh manuel demandé');

            // ✅ VIDER COMPLÈTEMENT LE CACHE
            $this->resultatsSession1 = [];
            $this->resultatsSession2 = [];
            $this->statistiquesSession1 = [];
            $this->statistiquesSession2 = [];

            // ✅ FORCER LE RECHARGEMENT
            $this->forceReloadData();

            toastr()->success('✅ Résultats actualisés avec succès');

            Log::info('✅ Refresh manuel terminé', [
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'nouveaux_resultats_s1' => count($this->resultatsSession1),
                'nouveaux_resultats_s2' => count($this->resultatsSession2)
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erreur rafraîchissement résultats: ' . $e->getMessage());
            toastr()->error('Erreur lors du rafraîchissement des résultats.');
        }
    }


    /**
     * ✅ MÉTHODE BONUS : Recharge les sessions
     */
    public function refreshSessions()
    {
        try {
            $this->loadSessions();
            $this->checkSession2Availability();
            toastr()->info('Sessions actualisées');

        } catch (\Exception $e) {
            Log::error('Erreur rafraîchissement sessions: ' . $e->getMessage());
            toastr()->error('Erreur lors du rafraîchissement des sessions.');
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

            Log::info('Composant reset complet effectué');

        } catch (\Exception $e) {
            Log::error('Erreur reset composant: ' . $e->getMessage());
            toastr()->error('Erreur lors de la réinitialisation.');
        }
    }


    /**
     * ✅ MÉTHODE : Sélectionner toutes les colonnes
     */
    public function selectionnerToutesColonnes()
    {
        $this->exportConfig['colonnes'] = [
            'rang' => true,
            'nom_complet' => true,
            'matricule' => true,
            'moyenne' => true,
            'credits' => true,
            'decision' => true,
            'niveau' => true,
        ];

        toastr()->info('Toutes les colonnes sélectionnées');
    }

    /**
     * ✅ MÉTHODE : Désélectionner toutes les colonnes
     */
    public function deselectionnerToutesColonnes()
    {
        $this->exportConfig['colonnes'] = [
            'rang' => false,
            'nom_complet' => false,
            'matricule' => false,
            'moyenne' => false,
            'credits' => false,
            'decision' => false,
            'niveau' => false,
        ];

        toastr()->warning('Toutes les colonnes désélectionnées');
    }


    /**
     * ✅ MÉTHODE : Validation des colonnes sélectionnées
     */
    private function validerColonnesExport()
    {
        $colonnesSelectionnees = array_filter($this->exportConfig['colonnes']);

        if (empty($colonnesSelectionnees)) {
            $this->addError('export', 'Veuillez sélectionner au moins une colonne à exporter.');
            return false;
        }

        // Vérifier qu'on a au moins nom ou matricule pour identifier les étudiants
        if (!($this->exportConfig['colonnes']['nom_complet'] || $this->exportConfig['colonnes']['matricule'])) {
            $this->addError('export', 'Veuillez sélectionner au moins le nom ou le matricule pour identifier les étudiants.');
            return false;
        }

        return true;
    }


    /**
     * ✅ MÉTHODE : Générer le PDF avec configuration
     */
    private function genererPDFAvecConfig($donnees, $session, $niveau, $parcours, $anneeUniv)
    {
        try {
            $filename = $this->genererNomFichier('pdf', $session, $niveau, $parcours, $anneeUniv);

            Log::info('Export PDF avec config généré', [
                'filename' => $filename,
                'nb_resultats' => count($donnees),
                'source' => $this->exportData,
                'colonnes' => array_keys(array_filter($this->exportConfig['colonnes']))
            ]);

            $this->showExportModal = false;
            toastr()->success("Export PDF généré avec succès ! (" . count($donnees) . " résultats)");

            $pdfExporter = new AdmisDeliberationPDF(
                $donnees,
                $session,
                $niveau,
                $parcours,
                $this->exportConfig['colonnes']
            );

            $pdf = $pdfExporter->generate();

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF avec config', ['error' => $e->getMessage()]);
            throw $e;
        }
    }


    /**
     * ✅ MÉTHODE : Exporter tous les résultats de simulation (raccourci)
     */
    public function exporterTousSimulation($type = 'pdf')
    {
        try {
            // Configuration par défaut pour tous les résultats
            $this->exportConfig['filtres']['decision_filter'] = 'tous';
            $this->exportConfig['tri']['champ'] = 'moyenne_generale';
            $this->exportConfig['tri']['ordre'] = 'desc';

            $this->exportType = $type;
            $this->exportData = 'simulation';

            return $this->genererExportAvecConfig();

        } catch (\Exception $e) {
            Log::error('Erreur export tous simulation', ['error' => $e->getMessage()]);
            toastr()->error('Erreur lors de l\'export : ' . $e->getMessage());
        }
    }


    /**
     * ✅ MÉTHODE : Exporter par décision spécifique (depuis simulation)
     */
    public function exporterParDecisionSimulation($decision, $type = 'pdf')
    {
        try {
            // Configuration pour une décision spécifique
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


        /**
     * ✅ MÉTHODE : Générer l'export avec configuration (VERSION CORRIGÉE)
     */
    public function genererExportAvecConfig()
    {
        try {
            // Validation des colonnes
            if (!$this->validerColonnesExport()) {
                return;
            }

            // Validation de base
            if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
                $this->addError('export', 'Veuillez sélectionner un niveau et une année universitaire.');
                return;
            }

            // Récupérer les données brutes
            $donneesRaw = $this->getDonneesExport($this->exportData);

            if (empty($donneesRaw)) {
                $this->addError('export', "Aucune donnée disponible pour l'export.");
                return;
            }

            // Appliquer filtres et tri
            $donneesFiltrees = $this->appliquerFiltresExport($donneesRaw);

            if (empty($donneesFiltrees)) {
                $this->addError('export', "Aucune donnée ne correspond aux filtres appliqués.");
                return;
            }

            // Récupérer les métadonnées
            $session = $this->activeTab === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

            // ✅ CORRECTION : Si on est en simulation, utiliser la session du paramètre délibération
            if ($this->exportData === 'simulation' && !empty($this->deliberationParams['session_type'])) {
                $session = $this->deliberationParams['session_type'] === 'session1' ?
                    $this->sessionNormale : $this->sessionRattrapage;
            }

            $niveau = Niveau::find($this->selectedNiveau);
            $parcours = $this->selectedParcours ? Parcour::find($this->selectedParcours) : null;
            $anneeUniv = AnneeUniversitaire::find($this->selectedAnneeUniversitaire);

            // Générer selon le type
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


    /**
     * ✅ MÉTHODE : Actions rapides d'export depuis les boutons de simulation
     */
    public function exportRapideDepuisSimulation($type, $filtre = 'tous')
    {
        if (empty($this->simulationDeliberation)) {
            toastr()->error('Aucune simulation disponible pour l\'export.');
            return;
        }

        try {
            // Configuration rapide
            $this->exportType = $type;
            $this->exportData = 'simulation';
            $this->exportConfig['filtres']['decision_filter'] = $filtre;
            $this->exportConfig['tri']['champ'] = 'moyenne_generale';
            $this->exportConfig['tri']['ordre'] = 'desc';

            // Colonnes par défaut
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


    /**
     * ✅ MÉTHODE : Obtenir les statistiques des données filtrées pour affichage (CORRIGÉE)
     */
    public function getStatistiquesExportPreview()
    {
        if (!$this->showExportModal) {
            return null;
        }

        try {
            $donneesRaw = $this->getDonneesExport($this->exportData);
            if (empty($donneesRaw)) {
                return null;
            }

            $donneesFiltrees = $this->appliquerFiltresExport($donneesRaw);

            $stats = [
                'total_initial' => count($donneesRaw),
                'total_filtre' => count($donneesFiltrees),
                'decisions' => []
            ];

            if (!empty($donneesFiltrees)) {
                $champDecision = $this->exportData === 'simulation' ? 'decision_simulee' : 'decision_actuelle';
                $decisions = collect($donneesFiltrees)->pluck($champDecision);

                $stats['decisions'] = [
                    'admis' => $decisions->filter(function($d) { return $d === 'admis'; })->count(),
                    'rattrapage' => $decisions->filter(function($d) { return $d === 'rattrapage'; })->count(),
                    'redoublant' => $decisions->filter(function($d) { return $d === 'redoublant'; })->count(),
                    'exclus' => $decisions->filter(function($d) { return $d === 'exclus'; })->count(),
                ];

                $moyennes = collect($donneesFiltrees)->pluck('moyenne_generale');
                $stats['moyenne_min'] = $moyennes->min();
                $stats['moyenne_max'] = $moyennes->max();
                $stats['moyenne_moyenne'] = round($moyennes->avg(), 2);
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('Erreur calcul stats export preview', ['error' => $e->getMessage()]);
            return null;
        }
    }


    /**
     * ✅ MÉTHODE : Preview des données avant export (CORRIGÉE)
     */
    public function previewDonneesExport()
    {
        try {
            $donneesRaw = $this->getDonneesExport($this->exportData);
            if (empty($donneesRaw)) {
                return [];
            }

            $donneesFiltrees = $this->appliquerFiltresExport($donneesRaw);

            // Retourner seulement les 10 premiers pour le preview
            return array_slice($donneesFiltrees, 0, 10);

        } catch (\Exception $e) {
            Log::error('Erreur preview données export', ['error' => $e->getMessage()]);
            return [];
        }
    }


    /**
     * ✅ MÉTHODE : Toggle colonne export
     */
    public function toggleColonneExport($colonne)
    {
        $this->exportConfig['colonnes'][$colonne] = !$this->exportConfig['colonnes'][$colonne];
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Vérifie si les données doivent être rafraîchies
     */
    private function shouldRefreshData()
    {
        // Rafraîchir si on a des filtres mais pas de résultats
        return ($this->selectedNiveau && $this->selectedAnneeUniversitaire) &&
            (empty($this->resultatsSession1) && empty($this->resultatsSession2));
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Enrichit les résultats avec l'info de changement
     */
    private function enrichirResultatsAvecChangements($resultats, $sessionId)
    {
        if (empty($resultats)) return $resultats;

        try {
            // Récupérer l'historique des changements depuis status_history
            $etudiantsIds = collect($resultats)->pluck('etudiant.id')->unique();

            $historiqueChangements = DB::table('resultats_finaux')
                ->whereIn('etudiant_id', $etudiantsIds)
                ->where('session_exam_id', $sessionId)
                ->where('jury_validated', true)
                ->whereNotNull('status_history')
                ->select('etudiant_id', 'status_history', 'decision', 'created_at', 'updated_at')
                ->get()
                ->groupBy('etudiant_id');

            // Enrichir chaque résultat
            foreach ($resultats as &$resultat) {
                $etudiantId = $resultat['etudiant']->id;
                $decisionActuelle = $resultat['decision'];
                $juryValidated = $resultat['jury_validated'] ?? false;

                // Initialiser les informations de changement
                $resultat['decision_originale'] = null;
                $resultat['a_change'] = false;
                $resultat['date_changement'] = null;
                $resultat['type_changement'] = 'aucun';

                if ($juryValidated && isset($historiqueChangements[$etudiantId])) {
                    $historique = $historiqueChangements[$etudiantId]->first();

                    if ($historique->status_history) {
                        $statusHistory = json_decode($historique->status_history, true);

                        // Chercher la dernière délibération
                        $derniereDeliberation = collect($statusHistory)
                            ->filter(function($entry) {
                                return in_array($entry['type_action'] ?? '', [
                                    'deliberation_appliquee',
                                    'decision_deliberation'
                                ]);
                            })
                            ->sortByDesc('date_action')
                            ->first();

                        if ($derniereDeliberation) {
                            $decisionPrecedente = $derniereDeliberation['decision_precedente'] ?? null;
                            $decisionNouvelle = $derniereDeliberation['decision_nouvelle'] ?? $decisionActuelle;

                            $resultat['decision_originale'] = $decisionPrecedente;
                            $resultat['a_change'] = $decisionPrecedente !== $decisionNouvelle;
                            $resultat['date_changement'] = $derniereDeliberation['date_action'] ?? null;

                            // Analyser le type de changement
                            if ($resultat['a_change']) {
                                $resultat['type_changement'] = $this->determinerTypeChangement(
                                    $decisionPrecedente,
                                    $decisionNouvelle
                                );
                            } else {
                                $resultat['type_changement'] = 'confirme';
                            }
                        }
                    }
                }

                // Marquer les promotions exceptionnelles
                $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;
                if ($decisionActuelle === 'admis' && $hasNoteEliminatoire && $juryValidated) {
                    $resultat['promotion_exceptionnelle'] = true;
                    if ($resultat['type_changement'] === 'aucun') {
                        $resultat['type_changement'] = 'promotion_exceptionnelle';
                    }
                } else {
                    $resultat['promotion_exceptionnelle'] = false;
                }
            }

            Log::info('Résultats enrichis avec changements', [
                'session_id' => $sessionId,
                'nb_etudiants' => count($resultats),
                'nb_avec_changements' => collect($resultats)->where('a_change', true)->count(),
                'nb_promotions_exceptionnelles' => collect($resultats)->where('promotion_exceptionnelle', true)->count()
            ]);

            return $resultats;

        } catch (\Exception $e) {
            Log::error('Erreur enrichissement changements', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            // En cas d'erreur, retourner les résultats sans enrichissement
            return $resultats;
        }
    }


    /**
     * ✅ HELPER : Détermine le type de changement
     */
    private function determinerTypeChangement($ancienne, $nouvelle)
    {
        if ($ancienne === $nouvelle) {
            return 'confirme';
        }

        // Promotions
        if ($ancienne === 'rattrapage' && $nouvelle === 'admis') {
            return 'promotion';
        }
        if ($ancienne === 'redoublant' && $nouvelle === 'admis') {
            return 'promotion_majeure';
        }
        if ($ancienne === 'exclus' && in_array($nouvelle, ['admis', 'redoublant'])) {
            return 'grace';
        }

        // Rétrogradations
        if ($ancienne === 'admis' && $nouvelle === 'rattrapage') {
            return 'retrogradation';
        }
        if ($ancienne === 'admis' && in_array($nouvelle, ['redoublant', 'exclus'])) {
            return 'retrogradation_majeure';
        }

        // Changements latéraux
        if ($ancienne === 'rattrapage' && $nouvelle === 'redoublant') {
            return 'changement_lateral';
        }

        return 'autre';
    }


    // ✅ MÉTHODE RENDER FINALE
    public function render()
    {
        // Fetch results, potentially restricted by role
        $results = ResultatFinal::query();

        if (!Auth::user()->hasRole('superadmin')) {
            // Example restriction for enseignant/secretaire
            $results->where('visible_to_enseignant', true); // Adjust based on your model
        }

        // ✅ AMÉLIORATION : Vérifier le statut de délibération pour chaque session
        $deliberationStatus = [
            'session1' => $this->checkDeliberationStatus('session1'),
            'session2' => $this->checkDeliberationStatus('session2')
        ];

        // ✅ AMÉLIORATION : Récupérer les statistiques de délibération
        $statistiquesDeliberation = [
            'session1' => $this->getStatistiquesDeliberation('session1'),
            'session2' => $this->getStatistiquesDeliberation('session2')
        ];

        // ✅ AJOUT : Forcer le rafraîchissement si nécessaire
        if ($this->shouldRefreshData()) {
            $this->loadResultats();
        }

        return view('livewire.resultats.resultats-finale', [
            'deliberationStatus' => $deliberationStatus,
            'statistiquesDeliberation' => $statistiquesDeliberation
        ]);
    }

    /**
     * ✅ MÉTHODE : Initialiser les paramètres avec les dernières valeurs
     */
    private function initialiserParametresDeliberation()
    {
        $dernieresValeurs = $this->getDernieresValeursDeliberation();

        // ✅ Session 1 (Normale)
        if ($dernieresValeurs['session1']) {
            $config = $dernieresValeurs['session1'];
            $this->deliberationParams['credits_admission_s1'] = $config['credits_admission_s1'];
            $this->deliberationParams['note_eliminatoire_bloque_s1'] = $config['note_eliminatoire_bloque_s1'];
        } else {
            // Valeurs par défaut logique médecine
            $this->deliberationParams['credits_admission_s1'] = 60;
            $this->deliberationParams['note_eliminatoire_bloque_s1'] = true;
        }

        // ✅ Session 2 (Rattrapage)
        if ($dernieresValeurs['session2']) {
            $config = $dernieresValeurs['session2'];
            $this->deliberationParams['credits_admission_s2'] = $config['credits_admission_s2'];
            $this->deliberationParams['credits_redoublement_s2'] = $config['credits_redoublement_s2'];
            $this->deliberationParams['note_eliminatoire_exclusion_s2'] = $config['note_eliminatoire_exclusion_s2'];
        } else {
            // Valeurs par défaut logique médecine
            $this->deliberationParams['credits_admission_s2'] = 40;
            $this->deliberationParams['credits_redoublement_s2'] = 20;
            $this->deliberationParams['note_eliminatoire_exclusion_s2'] = true;
        }

        // ✅ Session type par défaut
        if (!isset($this->deliberationParams['session_type'])) {
            $this->deliberationParams['session_type'] = 'session1';
        }
    }



    /**
     * ✅ MÉTHODE CORRIGÉE : Récupérer les dernières valeurs de délibération
     */
    private function getDernieresValeursDeliberation()
    {
        try {
            $sessionNormaleConfig = null;
            $sessionRattrapageConfig = null;

            // Récupérer config Session Normale
            if ($this->sessionNormale) {
                $sessionNormaleConfig = DeliberationConfig::where('niveau_id', $this->selectedNiveau)
                    ->where('session_id', $this->sessionNormale->id)
                    ->when($this->selectedParcours, function($q) {
                        $q->where('parcours_id', $this->selectedParcours);
                    }, function($q) {
                        $q->whereNull('parcours_id');
                    })
                    ->first();
            }

            // Récupérer config Session Rattrapage
            if ($this->sessionRattrapage) {
                $sessionRattrapageConfig = DeliberationConfig::where('niveau_id', $this->selectedNiveau)
                    ->where('session_id', $this->sessionRattrapage->id)
                    ->when($this->selectedParcours, function($q) {
                        $q->where('parcours_id', $this->selectedParcours);
                    }, function($q) {
                        $q->whereNull('parcours_id');
                    })
                    ->first();
            }

            // ✅ CORRECTION : Structure standardisée avec valeurs par défaut
            return [
                'session1' => $sessionNormaleConfig ? [
                    'delibere' => $sessionNormaleConfig->delibere,
                    'date_deliberation' => $sessionNormaleConfig->date_deliberation,
                    'delibere_par' => $sessionNormaleConfig->delibere_par,
                    'credits_admission_s1' => $sessionNormaleConfig->credits_admission_s1 ?? 60,
                    'note_eliminatoire_bloque_s1' => $sessionNormaleConfig->note_eliminatoire_bloque_s1 ?? true,
                    'config_id' => $sessionNormaleConfig->id
                ] : [
                    // ✅ Valeurs par défaut si aucune config
                    'delibere' => false,
                    'date_deliberation' => null,
                    'delibere_par' => null,
                    'credits_admission_s1' => 60,
                    'note_eliminatoire_bloque_s1' => true,
                    'config_id' => null
                ],

                'session2' => $sessionRattrapageConfig ? [
                    'delibere' => $sessionRattrapageConfig->delibere,
                    'date_deliberation' => $sessionRattrapageConfig->date_deliberation,
                    'delibere_par' => $sessionRattrapageConfig->delibere_par,
                    'credits_admission_s2' => $sessionRattrapageConfig->credits_admission_s2 ?? 40,
                    'credits_redoublement_s2' => $sessionRattrapageConfig->credits_redoublement_s2 ?? 20,
                    'note_eliminatoire_exclusion_s2' => $sessionRattrapageConfig->note_eliminatoire_exclusion_s2 ?? true,
                    'config_id' => $sessionRattrapageConfig->id
                ] : [
                    // ✅ Valeurs par défaut si aucune config
                    'delibere' => false,
                    'date_deliberation' => null,
                    'delibere_par' => null,
                    'credits_admission_s2' => 40,
                    'credits_redoublement_s2' => 20,
                    'note_eliminatoire_exclusion_s2' => true,
                    'config_id' => null
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erreur récupération config délibération: ' . $e->getMessage());

            // ✅ Retourner des valeurs par défaut en cas d'erreur
            return [
                'session1' => [
                    'delibere' => false,
                    'date_deliberation' => null,
                    'delibere_par' => null,
                    'credits_admission_s1' => 60,
                    'note_eliminatoire_bloque_s1' => true,
                    'config_id' => null
                ],
                'session2' => [
                    'delibere' => false,
                    'date_deliberation' => null,
                    'delibere_par' => null,
                    'credits_admission_s2' => 40,
                    'credits_redoublement_s2' => 20,
                    'note_eliminatoire_exclusion_s2' => true,
                    'config_id' => null
                ]
            ];
        }
    }

    /**
     * ✅ PROPRIÉTÉ COMPUTED : Dernières valeurs délibération
     */
    public function getDernieresValeursDeliberationProperty()
    {
           // ✅ Cache le résultat pour éviter les requêtes répétées
        return once(function () {
            return $this->getDernieresValeursDeliberation();
        });
    }

    /**
     * ✅ MÉTHODE : Restaurer les dernières valeurs de délibération
     */
    public function restaurerDernieresValeurs()
    {
        try {
            $dernieresValeurs = $this->getDernieresValeursDeliberation();

            // ✅ Restaurer les valeurs selon le type de session sélectionné
            $sessionType = $this->deliberationParams['session_type'] ?? 'session1';

            if ($sessionType === 'session1' && $dernieresValeurs['session1']) {
                $config = $dernieresValeurs['session1'];
                $this->deliberationParams['credits_admission_s1'] = $config['credits_admission_s1'];
                $this->deliberationParams['note_eliminatoire_bloque_s1'] = $config['note_eliminatoire_bloque_s1'];

                toastr()->success('Dernières valeurs de Session 1 restaurées');

            } elseif ($sessionType === 'session2' && $dernieresValeurs['session2']) {
                $config = $dernieresValeurs['session2'];
                $this->deliberationParams['credits_admission_s2'] = $config['credits_admission_s2'];
                $this->deliberationParams['credits_redoublement_s2'] = $config['credits_redoublement_s2'];
                $this->deliberationParams['note_eliminatoire_exclusion_s2'] = $config['note_eliminatoire_exclusion_s2'];

                toastr()->success('Dernières valeurs de Session 2 restaurées');

            } else {
                toastr()->warning('Aucune configuration précédente trouvée pour cette session');
            }

            Log::info('Dernières valeurs restaurées', [
                'session_type' => $sessionType,
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'user_id' => Auth::id()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur restauration dernières valeurs: ' . $e->getMessage());
            toastr()->error('Erreur lors de la restauration des dernières valeurs');
        }
    }



    // ✅ NOUVELLE MÉTHODE : Watchers pour les pourcentages
    public function updatedDeliberationParamsPourcentageAdmissionS1()
    {
        $this->deliberationParams['credits_admission_s1'] = 
            $this->convertirPourcentageEnCredits($this->deliberationParams['pourcentage_admission_s1']);
        
        Log::info('Pourcentage S1 mis à jour', [
            'pourcentage' => $this->deliberationParams['pourcentage_admission_s1'],
            'credits' => $this->deliberationParams['credits_admission_s1']
        ]);
    }

    public function updatedDeliberationParamsPourcentageAdmissionS2()
    {
        $this->deliberationParams['credits_admission_s2'] = 
            $this->convertirPourcentageEnCredits($this->deliberationParams['pourcentage_admission_s2']);
    }

    public function updatedDeliberationParamsPourcentageRedoublementS2()
    {
        $this->deliberationParams['credits_redoublement_s2'] = 
            $this->convertirPourcentageEnCredits($this->deliberationParams['pourcentage_redoublement_s2']);
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
                
            Log::info('✅ Crédits totaux calculés', [
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'total_credits' => $totalCredits
            ]);
            
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

    // ✅ MÉTHODE 2 : Convertir pourcentage en crédits
    private function convertirPourcentageEnCredits($pourcentage)
    {
        $creditsTotaux = $this->calculerCreditsTotauxDisponibles();
        return round(($pourcentage / 100) * $creditsTotaux);
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

    // ✅ MÉTHODE 4 : Mise à jour des paramètres délibération avec pourcentages
    public function mettreAJourParametresAvecPourcentages()
    {
        try {
            $creditsTotaux = $this->calculerCreditsTotauxDisponibles();
            
            // Mettre à jour les paramètres avec les crédits réels
            if (isset($this->deliberationParams['pourcentage_admission_s1'])) {
                $this->deliberationParams['credits_admission_s1'] = 
                    $this->convertirPourcentageEnCredits($this->deliberationParams['pourcentage_admission_s1']);
            }
            
            if (isset($this->deliberationParams['pourcentage_admission_s2'])) {
                $this->deliberationParams['credits_admission_s2'] = 
                    $this->convertirPourcentageEnCredits($this->deliberationParams['pourcentage_admission_s2']);
            }
            
            if (isset($this->deliberationParams['pourcentage_redoublement_s2'])) {
                $this->deliberationParams['credits_redoublement_s2'] = 
                    $this->convertirPourcentageEnCredits($this->deliberationParams['pourcentage_redoublement_s2']);
            }
            
            Log::info('🔄 Paramètres mis à jour avec pourcentages', [
                'credits_totaux' => $creditsTotaux,
                'parametres_finaux' => [
                    'credits_admission_s1' => $this->deliberationParams['credits_admission_s1'] ?? null,
                    'credits_admission_s2' => $this->deliberationParams['credits_admission_s2'] ?? null,
                    'credits_redoublement_s2' => $this->deliberationParams['credits_redoublement_s2'] ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur mise à jour paramètres pourcentages', [
                'error' => $e->getMessage()
            ]);
        }
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
            
        Log::info('🎯 Paramètres initialisés avec pourcentages', [
            'credits_totaux' => $creditsTotaux,
            'pourcentages' => [
                'admission_s1' => $this->deliberationParams['pourcentage_admission_s1'],
                'admission_s2' => $this->deliberationParams['pourcentage_admission_s2'],
                'redoublement_s2' => $this->deliberationParams['pourcentage_redoublement_s2']
            ]
        ]);
    }

    // ✅ MÉTHODE 6 : Simulation délibération mise à jour avec logique dynamique
    public function simulerDeliberationAvecPourcentages()
    {
        try {
            // Vérifications préalables existantes...
            if (!$this->selectedNiveau || !$this->selectedAnneeUniversitaire) {
                $this->addError('deliberation', 'Veuillez sélectionner un niveau et une année.');
                return;
            }

            // Mettre à jour les paramètres avec les pourcentages
            $this->mettreAJourParametresAvecPourcentages();
            
            // S'assurer que session_id est défini
            if (!isset($this->deliberationParams['session_id']) || !$this->deliberationParams['session_id']) {
                $sessionType = $this->deliberationParams['session_type'] ?? 'session1';
                $session = $sessionType === 'session1' ? $this->sessionNormale : $this->sessionRattrapage;

                if (!$session) {
                    $this->addError('deliberation', 'Session non trouvée.');
                    return;
                }

                $this->deliberationParams['session_id'] = $session->id;
            }

            // Récupérer les résultats actuels
            $resultatsActuels = $this->deliberationParams['session_type'] === 'session1'
                ? $this->resultatsSession1
                : $this->resultatsSession2;

            if (empty($resultatsActuels)) {
                $this->addError('deliberation', 'Aucun résultat disponible pour la simulation');
                return;
            }

            $resultatsDetailles = [];
            $statistiques = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'changements' => 0
            ];

            $creditsTotaux = $this->calculerCreditsTotauxDisponibles();

            foreach ($resultatsActuels as $index => $resultat) {
                $etudiant = $resultat['etudiant'] ?? null;

                if (!$etudiant) {
                    continue;
                }

                // ✅ UTILISER LA LOGIQUE DYNAMIQUE avec crédits réels
                $decisionSimulee = $this->calculerDecisionSelonParametresDynamiques($resultat, $creditsTotaux);
                $decisionActuelle = $resultat['decision'] ?? 'rattrapage';

                $changement = $decisionActuelle !== $decisionSimulee;
                if ($changement) {
                    $statistiques['changements']++;
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
                    'total_credits' => $creditsTotaux,
                    'pourcentage_credits' => $creditsTotaux > 0 ? 
                        round(($resultat['credits_valides'] ?? 0) / $creditsTotaux * 100, 1) : 0,
                    'has_note_eliminatoire' => $resultat['has_note_eliminatoire'] ?? false,
                    'decision_actuelle' => $decisionActuelle,
                    'decision_simulee' => $decisionSimulee,
                    'changement' => $changement
                ];
            }

            // Structure finale pour la vue
            $this->simulationDeliberation = [
                'success' => true,
                'total_etudiants' => count($resultatsDetailles),
                'credits_totaux_disponibles' => $creditsTotaux,
                'statistiques' => $statistiques,
                'resultats_detailles' => $resultatsDetailles,
                'parametres_utilises' => $this->deliberationParams
            ];

            // Message de succès avec informations dynamiques
            $sessionName = $this->deliberationParams['session_type'] === 'session1' ? 'Session 1' : 'Session 2';
            $totalCreditsMessage = "Crédits disponibles: {$creditsTotaux}";
            
            toastr()->info(
                "🔍 Simulation {$sessionName} ({$totalCreditsMessage}) : {$statistiques['changements']} changements détectés. " .
                "Nouveaux résultats : {$statistiques['admis']} admis, {$statistiques['rattrapage']} rattrapage, " .
                "{$statistiques['redoublant']} redoublant, {$statistiques['exclus']} exclus"
            );

            Log::info('Simulation délibération réussie avec pourcentages', [
                'session_id' => $this->deliberationParams['session_id'],
                'niveau_id' => $this->selectedNiveau,
                'parcours_id' => $this->selectedParcours,
                'credits_totaux' => $creditsTotaux,
                'statistiques' => $statistiques
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur simulation délibération avec pourcentages: ' . $e->getMessage());
            $this->addError('deliberation', 'Erreur lors de la simulation: ' . $e->getMessage());
        }
    }

    // ✅ MÉTHODE 7 : Calcul décision avec logique dynamique
    private function calculerDecisionSelonParametresDynamiques($resultat, $creditsTotaux)
    {
        $sessionType = $this->deliberationParams['session_type'] ?? 'session1';
        $creditsValides = $resultat['credits_valides'] ?? 0;
        $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;

        if ($sessionType === 'session1') {
            // SESSION 1 - Logique dynamique
            $bloquerSiNote0 = $this->deliberationParams['note_eliminatoire_bloque_s1'] ?? true;

            // Si note éliminatoire et option activée
            if ($hasNoteEliminatoire && $bloquerSiNote0) {
                return 'rattrapage';
            }

            // ✅ LOGIQUE DYNAMIQUE : 100% des crédits disponibles requis
            return $creditsValides >= $creditsTotaux ? 'admis' : 'rattrapage';

        } else {
            // SESSION 2 - Logique dynamique
            $pourcentageAdmission = $this->deliberationParams['pourcentage_admission_s2'] ?? 67;
            $pourcentageRedoublement = $this->deliberationParams['pourcentage_redoublement_s2'] ?? 33;
            $exclusionSiNote0 = $this->deliberationParams['note_eliminatoire_exclusion_s2'] ?? true;

            $creditsAdmission = $this->convertirPourcentageEnCredits($pourcentageAdmission);
            $creditsRedoublement = $this->convertirPourcentageEnCredits($pourcentageRedoublement);

            // Si note éliminatoire et option activée
            if ($hasNoteEliminatoire && $exclusionSiNote0) {
                return 'exclus';
            }

            // ✅ LOGIQUE DYNAMIQUE basée sur les pourcentages
            if ($creditsValides >= $creditsAdmission) {
                return 'admis';
            } elseif ($creditsValides >= $creditsRedoublement) {
                return 'redoublant';
            } else {
                return 'exclus';
            }
        }
    }


        /**
     * Export PDF simple
     */
    public function exporterPDF()
    {
        try {
            $exportService = new ExportService();
            
            $pdf = $exportService->exporterPDF(
                $this->resultatsSession1,
                $this->selectedNiveau,
                $this->selectedAnneeUniversitaire,
                $this->selectedParcours,
                $this->uesStructure
            );
            
            session()->flash('export_success', 'Export PDF généré avec succès (' . count($this->resultatsSession1) . ' étudiants)');
            return $pdf;
            
        } catch (\Exception $e) {
            session()->flash('export_error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }


        /**
     * Export Excel simple
     */
    public function exporterExcel()
    {
        try {
            $exportService = new ExportService();
            
            $excel = $exportService->exporterExcel(
                $this->resultatsSession1,
                $this->uesStructure
            );
            
            session()->flash('export_success', 'Export Excel généré avec succès (' . count($this->resultatsSession1) . ' étudiants)');
            return $excel;
            
        } catch (\Exception $e) {
            session()->flash('export_error', 'Erreur lors de la génération d\'Excel: ' . $e->getMessage());
        }
    }


        /**
     * Export PDF admis seulement
     */
    public function exporterAdmisPDF()
    {
        try {
            $exportService = new ExportService();
            
            $pdf = $exportService->exporterAdmisPDF(
                $this->resultatsSession1,
                $this->selectedNiveau,
                $this->selectedAnneeUniversitaire,
                $this->selectedParcours,
                $this->uesStructure
            );
            
            $nbAdmis = collect($this->resultatsSession1)->where('decision', 'admis')->count();
            session()->flash('export_success', "Export PDF des admis généré avec succès ({$nbAdmis} étudiants)");
            return $pdf;
            
        } catch (\Exception $e) {
            session()->flash('export_error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

}
