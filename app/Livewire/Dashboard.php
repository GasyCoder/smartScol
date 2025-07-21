<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Etudiant;
use App\Models\ResultatFinal;
use App\Models\SessionExam;
use App\Models\AnneeUniversitaire;
use App\Models\Niveau;
use App\Models\Parcour;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Dashboard extends Component
{
    // Nouvelles propriétés pour les statistiques par parcours
    public $selectedNiveauFilter = '';
    public $viewMode = 'table';
    public $refreshing = false;
    
    // 🔧 AJOUT : Variables pour les graphiques
    public $selectedYear;
    public $selectedChartType = 'line';

    /**
     * 🔧 SOLUTION UNIVERSELLE : Détecte automatiquement la colonne parcours
     */
    private function detectParcoursColumn()
    {
        static $detectedColumn = null;
        
        if ($detectedColumn === null) {
            try {
                $columns = Schema::getColumnListing('etudiants');
                
                if (in_array('parcours_id', $columns)) {
                    $detectedColumn = 'parcours_id';
                } elseif (in_array('parcour_id', $columns)) {
                    $detectedColumn = 'parcour_id';
                } else {
                    $detectedColumn = 'parcours_id'; // Valeur par défaut
                }
            } catch (\Exception $e) {
                $detectedColumn = 'parcours_id';
            }
        }
        
        return $detectedColumn;
    }

    /**
     * 🔧 NOUVELLES MÉTHODES : Statistiques par niveau - SQL PUR
     */
    private function getStatistiquesNiveauxSQL()
    {
        try {
            $sql = "
                SELECT 
                    n.id,
                    n.abr,
                    n.nom,
                    n.is_concours,
                    n.has_rattrapage,
                    COUNT(e.id) as etudiants_count
                FROM niveaux n
                LEFT JOIN etudiants e ON n.id = e.niveau_id 
                    AND e.is_active = 1 
                    AND e.deleted_at IS NULL
                WHERE n.is_active = 1
                GROUP BY n.id, n.abr, n.nom, n.is_concours, n.has_rattrapage
                ORDER BY n.abr
            ";
            
            $results = DB::select($sql);
            
            // Convertir en collection d'objets
            $collection = collect();
            foreach ($results as $result) {
                $obj = (object) [
                    'id' => $result->id,
                    'abr' => $result->abr,
                    'nom' => $result->nom,
                    'is_concours' => $result->is_concours,
                    'has_rattrapage' => $result->has_rattrapage,
                    'etudiants_count' => $result->etudiants_count
                ];
                $collection->push($obj);
            }
            
            return $collection;
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur calcul statistiques niveaux SQL', [
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * 🔧 NOUVELLES MÉTHODES : Statistiques par parcours - SQL PUR
     */
    private function getStatistiquesParcoursSQL()
    {
        try {
            $parcoursColumn = $this->detectParcoursColumn();
            
            $sql = "
                SELECT 
                    p.id,
                    p.nom,
                    p.abr,
                    p.niveau_id,
                    n.nom as niveau_nom,
                    n.abr as niveau_abr,
                    n.is_concours,
                    n.has_rattrapage,
                    COUNT(e.id) as etudiants_count
                FROM parcours p
                INNER JOIN niveaux n ON p.niveau_id = n.id
                LEFT JOIN etudiants e ON p.id = e.{$parcoursColumn} 
                    AND e.is_active = 1 
                    AND e.deleted_at IS NULL
                WHERE p.is_active = 1
                GROUP BY p.id, p.nom, p.abr, p.niveau_id, n.nom, n.abr, n.is_concours, n.has_rattrapage
                ORDER BY n.abr, p.nom
            ";
            
            $results = DB::select($sql);
            
            // Convertir en collection d'objets pour la compatibilité avec la vue
            $collection = collect();
            foreach ($results as $result) {
                $obj = (object) [
                    'id' => $result->id,
                    'nom' => $result->nom,
                    'abr' => $result->abr,
                    'etudiants_count' => $result->etudiants_count,
                    'niveau' => (object) [
                        'id' => $result->niveau_id,
                        'nom' => $result->niveau_nom,
                        'abr' => $result->niveau_abr,
                        'is_concours' => $result->is_concours,
                        'has_rattrapage' => $result->has_rattrapage
                    ]
                ];
                $collection->push($obj);
            }
            
            Log::info('✅ Statistiques parcours récupérées avec SQL', [
                'count' => $collection->count(),
                'colonne_utilisee' => $parcoursColumn
            ]);
            
            return $collection;
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur calcul statistiques parcours SQL', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect();
        }
    }

    public function render()
    {
        // Récupérer l'année universitaire active
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        
        // 🔧 INITIALISER selectedYear si pas défini
        if (!$this->selectedYear && $anneeActive) {
            $this->selectedYear = $anneeActive->id;
        }

        // Initialisation des variables ORIGINALES
        $totalEtudiants = 0;
        $etudiantsAdmis = 0;
        $redoublants = 0;
        $exclus = 0;
        $rattrapage = 0;
        $progressionEtudiants = 0;
        $progressionAdmis = 0;
        $progressionRedoublants = 0;
        $progressionExclus = 0;
        $progressionRattrapage = 0;
        $chartDataEtudiants = array_fill(0, 12, 0);
        $chartDataAdmis = array_fill(0, 12, 0);
        $chartDataRedoublants = array_fill(0, 12, 0);
        $chartDataExclus = array_fill(0, 12, 0);
        $chartDataRattrapage = array_fill(0, 12, 0);
        $sessionDeliberee = false;

        // ✅ NOUVELLES VARIABLES pour les statistiques par parcours
        $statistiquesNiveaux = $this->getStatistiquesNiveauxSQL();
        $statistiquesParcours = $this->getStatistiquesParcoursSQL();
        $anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();
        $topEtudiants = collect();

        if ($anneeActive) {
            // Code ORIGINAL pour les statistiques générales
            $totalEtudiants = Etudiant::where('is_active', true)->count();

            $sessionCourante = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                                        ->where('is_current', true)
                                        ->first();

            if ($sessionCourante) {
                $hasDeliberatedResults = ResultatFinal::where('session_exam_id', $sessionCourante->id)
                    ->where('statut', ResultatFinal::STATUT_PUBLIE)
                    ->where('jury_validated', 1)
                    ->exists();

                $sessionDeliberee = $hasDeliberatedResults;

                $statistiquesSession = $this->calculerStatistiquesLogiqueMedecine($sessionCourante->id);

                // 🔧 CORRECTION : Utiliser les bonnes clés
                $etudiantsAdmis = $statistiquesSession['decisions']['admis'] ?? 0;
                $redoublants = $statistiquesSession['decisions']['redoublants'] ?? 0;
                $exclus = $statistiquesSession['decisions']['exclus'] ?? 0; // 🔧 Correction ici
                $rattrapage = $statistiquesSession['decisions']['rattrapage'] ?? 0;

                $sessionPrecedente = SessionExam::where('type', $sessionCourante->type)
                                              ->where('annee_universitaire_id', $anneeActive->id)
                                              ->where('id', '<', $sessionCourante->id)
                                              ->orderBy('id', 'desc')
                                              ->first();

                if ($sessionPrecedente) {
                    $anciennesStats = $this->calculerStatistiquesLogiqueMedecine($sessionPrecedente->id);

                    $progressionAdmis = $this->calculerPourcentage(
                        $anciennesStats['decisions']['admis'] ?? 0,
                        $etudiantsAdmis
                    );
                    $progressionRedoublants = $this->calculerPourcentage(
                        $anciennesStats['decisions']['redoublants'] ?? 0,
                        $redoublants
                    );
                    $progressionExclus = $this->calculerPourcentage(
                        $anciennesStats['decisions']['exclus'] ?? 0, // 🔧 Correction ici
                        $exclus
                    );
                    $progressionRattrapage = $this->calculerPourcentage(
                        $anciennesStats['decisions']['rattrapage'] ?? 0,
                        $rattrapage
                    );
                }

                $chartData = $this->genererDonneesGraphiquesReelles($anneeActive->id);
                $chartDataEtudiants = $chartData['etudiants'];
                $chartDataAdmis = $chartData['admis'];
                $chartDataRedoublants = $chartData['redoublants'];
                $chartDataExclus = $chartData['exclus']; // 🔧 Correction ici
                $chartDataRattrapage = $chartData['rattrapage'];
            }

            $anneePrecedente = AnneeUniversitaire::where('date_start', '<', $anneeActive->date_start)
                                               ->orderBy('date_start', 'desc')
                                               ->first();

            if ($anneePrecedente) {
                $anciensEtudiants = Etudiant::whereBetween('created_at', [
                    $anneePrecedente->date_start,
                    $anneePrecedente->date_end
                ])->where('is_active', true)->count();

                $progressionEtudiants = $this->calculerPourcentage($anciensEtudiants, $totalEtudiants);
            }
        }

        // ✅ RETOURNER LA VUE ORIGINALE avec les nouvelles variables
        return view('livewire.dashboard', compact(
            'totalEtudiants',
            'etudiantsAdmis',
            'redoublants',
            'exclus',
            'rattrapage',
            'progressionEtudiants',
            'progressionAdmis',
            'progressionRedoublants',
            'progressionExclus',
            'progressionRattrapage',
            'sessionDeliberee',
            'chartDataEtudiants',
            'chartDataAdmis',
            'chartDataRedoublants',
            'chartDataExclus',
            'chartDataRattrapage',
            // ✅ NOUVELLES VARIABLES
            'statistiquesNiveaux',
            'statistiquesParcours',
            'anneesUniversitaires',
            'topEtudiants'
        ))->with([
            // 🔧 VARIABLES POUR LES GRAPHIQUES
            'selectedYear' => $this->selectedYear,
            'selectedChartType' => $this->selectedChartType
        ]);
    }

    /**
     * MÉTHODES ORIGINALES CONSERVÉES - 🔧 CORRECTION DES CLÉS
     */
    private function calculerStatistiquesLogiqueMedecine($sessionId)
    {
        try {
            $etudiantsAvecResultats = ResultatFinal::where('session_exam_id', $sessionId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->with('etudiant')
                ->get()
                ->groupBy('etudiant_id');

            // 🔧 CORRECTION : Standardiser les clés (sans 's' pour exclus)
            $decisions = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublants' => 0,
                'exclus' => 0  // 🔧 Changé de 'excluss' vers 'exclus'
            ];

            $session = SessionExam::find($sessionId);
            $isRattrapage = $session && $session->type === 'Rattrapage';

            foreach ($etudiantsAvecResultats as $etudiantId => $resultats) {
                $etudiant = $resultats->first()->etudiant;

                if (!$etudiant || !$etudiant->is_active) {
                    continue;
                }

                $premierResultat = $resultats->first();
                $estDelibere = $premierResultat->jury_validated ?? false;

                if ($estDelibere) {
                    $decision = $premierResultat->decision;

                    Log::info('📊 Dashboard - Décision délibérée utilisée', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionId,
                        'decision_deliberee' => $decision,
                        'jury_validated' => true
                    ]);
                } else {
                    if ($isRattrapage) {
                        $decision = ResultatFinal::determinerDecisionRattrapage_LogiqueMedecine($etudiantId, $sessionId);
                    } else {
                        $decision = ResultatFinal::determinerDecisionPremiereSession_LogiqueMedecine($etudiantId, $sessionId);
                    }

                    Log::info('📊 Dashboard - Décision calculée utilisée', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionId,
                        'decision_calculee' => $decision,
                        'jury_validated' => false
                    ]);
                }

                switch ($decision) {
                    case ResultatFinal::DECISION_ADMIS:
                        $decisions['admis']++;
                        break;
                    case ResultatFinal::DECISION_RATTRAPAGE:
                        $decisions['rattrapage']++;
                        break;
                    case ResultatFinal::DECISION_REDOUBLANT:
                        $decisions['redoublants']++;
                        break;
                    case ResultatFinal::DECISION_EXCLUS:
                        $decisions['exclus']++; // 🔧 Correction ici
                        break;
                }
            }

            Log::info('📊 Dashboard - Statistiques finales', [
                'session_id' => $sessionId,
                'session_type' => $session ? $session->type : 'Normale',
                'decisions_finales' => $decisions,
                'total_etudiants' => $etudiantsAvecResultats->count()
            ]);

            return [
                'total_etudiants' => $etudiantsAvecResultats->count(),
                'decisions' => $decisions,
                'session_type' => $session ? $session->type : 'Normale'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques logique médecine', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'total_etudiants' => 0,
                'decisions' => [
                    'admis' => 0,
                    'rattrapage' => 0,
                    'redoublants' => 0,
                    'exclus' => 0 // 🔧 Correction ici
                ],
                'session_type' => 'Normale'
            ];
        }
    }

    private function genererDonneesGraphiquesReelles($anneeUniversitaireId)
    {
        $chartData = [
            'etudiants' => array_fill(0, 12, 0),
            'admis' => array_fill(0, 12, 0),
            'redoublants' => array_fill(0, 12, 0),
            'exclus' => array_fill(0, 12, 0), // 🔧 Correction ici
            'rattrapage' => array_fill(0, 12, 0)
        ];

        try {
            $moisActuel = now();

            for ($i = 11; $i >= 0; $i--) {
                $mois = $moisActuel->copy()->subMonths($i);
                $indexMois = 11 - $i;

                $etudiantsMois = Etudiant::whereYear('created_at', $mois->year)
                                       ->whereMonth('created_at', $mois->month)
                                       ->where('is_active', true)
                                       ->count();

                $chartData['etudiants'][$indexMois] = $etudiantsMois;

                $sessionsFromMois = SessionExam::where('annee_universitaire_id', $anneeUniversitaireId)
                    ->whereYear('created_at', $mois->year)
                    ->whereMonth('created_at', $mois->month)
                    ->get();

                $decisionsFromMois = [
                    'admis' => 0,
                    'rattrapage' => 0,
                    'redoublants' => 0,
                    'exclus' => 0 // 🔧 Correction ici
                ];

                foreach ($sessionsFromMois as $session) {
                    $statsSession = $this->calculerStatistiquesLogiqueMedecine($session->id);

                    $decisionsFromMois['admis'] += $statsSession['decisions']['admis'] ?? 0;
                    $decisionsFromMois['rattrapage'] += $statsSession['decisions']['rattrapage'] ?? 0;
                    $decisionsFromMois['redoublants'] += $statsSession['decisions']['redoublants'] ?? 0;
                    $decisionsFromMois['exclus'] += $statsSession['decisions']['exclus'] ?? 0; // 🔧 Correction ici
                }

                $chartData['admis'][$indexMois] = $decisionsFromMois['admis'];
                $chartData['rattrapage'][$indexMois] = $decisionsFromMois['rattrapage'];
                $chartData['redoublants'][$indexMois] = $decisionsFromMois['redoublants'];
                $chartData['exclus'][$indexMois] = $decisionsFromMois['exclus']; // 🔧 Correction ici
            }

        } catch (\Exception $e) {
            Log::error('Erreur génération données graphiques', [
                'annee_universitaire_id' => $anneeUniversitaireId,
                'error' => $e->getMessage()
            ]);
        }

        return $chartData;
    }

    private function calculerPourcentage($ancienneValeur, $nouvelleValeur)
    {
        if ($ancienneValeur == 0) {
            return $nouvelleValeur > 0 ? 100 : 0;
        }

        return round((($nouvelleValeur - $ancienneValeur) / $ancienneValeur) * 100, 1);
    }

    // ✅ NOUVELLES MÉTHODES LIVEWIRE pour l'interactivité des parcours
    public function updatedSelectedNiveauFilter()
    {
        // Rien à faire, juste pour déclencher le re-render
    }

    // 🔧 MÉTHODES POUR LES GRAPHIQUES
    public function changeYear($yearId)
    {
        $this->selectedYear = $yearId;
        session()->flash('info', "Année universitaire changée");
    }

    public function changeChartType($type)
    {
        $this->selectedChartType = $type;
    }

    /**
     * 🔍 Méthodes de test ajoutées
     */
    public function testParcours()
    {
        $parcoursColumn = $this->detectParcoursColumn();
        
        $niveaux = DB::table('niveaux')->count();
        $parcours = DB::table('parcours')->count();
        $etudiants = DB::table('etudiants')->where('is_active', 1)->count();
        
        session()->flash('info', "Test: {$niveaux} niveaux, {$parcours} parcours, {$etudiants} étudiants. Colonne: {$parcoursColumn}");
        
        Log::info('🔍 Test données dashboard', [
            'niveaux' => $niveaux,
            'parcours' => $parcours,
            'etudiants' => $etudiants,
            'colonne_parcours_detectee' => $parcoursColumn
        ]);
    }

    public function testData()
    {
        try {
            $statistiquesTest = $this->getStatistiquesParcoursSQL();
            session()->flash('success', "✅ Test réussi ! {$statistiquesTest->count()} parcours trouvés.");
        } catch (\Exception $e) {
            session()->flash('error', "❌ Erreur test : " . $e->getMessage());
        }
    }

    public function testSQL()
    {
        try {
            $result = DB::select("SELECT COUNT(*) as total FROM parcours WHERE is_active = 1");
            $total = $result[0]->total ?? 0;
            session()->flash('info', "🔧 SQL Direct : {$total} parcours actifs trouvés");
        } catch (\Exception $e) {
            session()->flash('error', "❌ Erreur SQL : " . $e->getMessage());
        }
    }

    public function refresh()
    {
        $this->refreshing = true;
        
        // Simule un délai de rafraîchissement
        sleep(1);
        
        $this->refreshing = false;
        session()->flash('success', 'Dashboard actualisé avec succès !');
        
        $this->dispatch('dashboard-refreshed');
    }

    public function exportTableData()
    {
        session()->flash('info', 'Export en cours de développement...');
    }

    // CONSERVER TOUTES VOS AUTRES MÉTHODES ORIGINALES
    public function getStatistiquesDetailleesSession($sessionId)
    {
        $statsBase = $this->calculerStatistiquesLogiqueMedecine($sessionId);

        $resultats = ResultatFinal::where('session_exam_id', $sessionId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->get();

        $moyenneGeneraleSession = 0;
        $notesEliminatoires = 0;
        $moyennesUE = [];

        $etudiantsTraites = $resultats->groupBy('etudiant_id');

        foreach ($etudiantsTraites as $etudiantId => $resultatsEtudiant) {
            $moyenneEtudiant = ResultatFinal::calculerMoyenneGenerale_LogiqueMedecine($etudiantId, $sessionId);
            $moyennesUE[] = $moyenneEtudiant;

            $notesEliminatoires += $resultatsEtudiant->where('note', 0)->count();
        }

        if (count($moyennesUE) > 0) {
            $moyenneGeneraleSession = round(array_sum($moyennesUE) / count($moyennesUE), 2);
        }

        return array_merge($statsBase, [
            'moyenne_generale_session' => $moyenneGeneraleSession,
            'notes_eliminatoires' => $notesEliminatoires,
            'taux_reussite' => $statsBase['total_etudiants'] > 0 ?
                round(($statsBase['decisions']['admis'] / $statsBase['total_etudiants']) * 100, 1) : 0,
            'taux_rattrapage' => $statsBase['total_etudiants'] > 0 ?
                round(($statsBase['decisions']['rattrapage'] / $statsBase['total_etudiants']) * 100, 1) : 0
        ]);
    }

    public function getRepartitionMoyennes($sessionId)
    {
        $etudiantsAvecResultats = ResultatFinal::where('session_exam_id', $sessionId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->distinct('etudiant_id')
            ->pluck('etudiant_id');

        $tranches = [
            '0-5' => 0,
            '5-10' => 0,
            '10-12' => 0,
            '12-14' => 0,
            '14-16' => 0,
            '16-20' => 0
        ];

        foreach ($etudiantsAvecResultats as $etudiantId) {
            $moyenne = ResultatFinal::calculerMoyenneGenerale_LogiqueMedecine($etudiantId, $sessionId);

            if ($moyenne < 5) {
                $tranches['0-5']++;
            } elseif ($moyenne < 10) {
                $tranches['5-10']++;
            } elseif ($moyenne < 12) {
                $tranches['10-12']++;
            } elseif ($moyenne < 14) {
                $tranches['12-14']++;
            } elseif ($moyenne < 16) {
                $tranches['14-16']++;
            } else {
                $tranches['16-20']++;
            }
        }

        return $tranches;
    }

    public function getEvolutionResultats($anneeUniversitaireId, $nombreSessions = 5)
    {
        $sessions = SessionExam::where('annee_universitaire_id', $anneeUniversitaireId)
            ->orderBy('created_at', 'desc')
            ->limit($nombreSessions)
            ->get();

        $evolution = [];

        foreach ($sessions as $session) {
            $stats = $this->calculerStatistiquesLogiqueMedecine($session->id);

            $evolution[] = [
                'session' => $session,
                'stats' => $stats,
                'date' => $session->created_at->format('M Y')
            ];
        }

        return array_reverse($evolution);
    }
}