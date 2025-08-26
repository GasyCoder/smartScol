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
    public $selectedNiveauFilter = '';
    public $viewMode = 'table';
    public $refreshing = false;
    public $selectedYear;
    public $selectedChartType = 'line';

    /**
     * Détecte automatiquement la colonne parcours
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
                    $detectedColumn = 'parcours_id';
                }
            } catch (\Exception $e) {
                $detectedColumn = 'parcours_id';
            }
        }
        
        return $detectedColumn;
    }

    /**
     * Statistiques par niveau médical (PACES, L2, L3, M1, M2, D1)
     */
    private function getStatistiquesNiveauxMedicaux()
    {
        try {
            $sql = "
                SELECT 
                    n.id,
                    n.abr,
                    n.nom,
                    n.is_concours,
                    n.has_rattrapage,
                    n.has_parcours,
                    COUNT(e.id) as etudiants_count
                FROM niveaux n
                LEFT JOIN etudiants e ON n.id = e.niveau_id 
                    AND e.is_active = 1 
                    AND e.deleted_at IS NULL
                WHERE n.is_active = 1
                GROUP BY n.id, n.abr, n.nom, n.is_concours, n.has_rattrapage, n.has_parcours
                ORDER BY 
                    CASE n.abr 
                        WHEN 'PACES' THEN 1 
                        WHEN 'L2' THEN 2 
                        WHEN 'L3' THEN 3 
                        WHEN 'M1' THEN 4 
                        WHEN 'M2' THEN 5 
                        WHEN 'D1' THEN 6 
                        ELSE 99 
                    END
            ";
            
            $results = DB::select($sql);
            
            $collection = collect();
            foreach ($results as $result) {
                // Calcul réel des admis par niveau
                $admisCount = $this->calculerAdmisParNiveauMedical($result->id);
                $tauxReussite = $result->etudiants_count > 0 ? round(($admisCount / $result->etudiants_count) * 100, 1) : 0;
                
                // Spécificités par niveau médical
                $specialites = $this->getSpecialitesParNiveau($result->abr);
                
                $obj = (object) [
                    'id' => $result->id,
                    'abr' => $result->abr,
                    'nom' => $result->nom,
                    'is_concours' => $result->is_concours,
                    'has_rattrapage' => $result->has_rattrapage,
                    'has_parcours' => $result->has_parcours,
                    'etudiants_count' => $result->etudiants_count,
                    'admis_count' => $admisCount,
                    'taux_reussite' => $tauxReussite,
                    'specialites_count' => $specialites,
                    'type_formation' => $this->getTypeFormation($result->abr)
                ];
                $collection->push($obj);
            }
            
            return $collection;
            
        } catch (\Exception $e) {
            Log::error('Erreur statistiques niveaux médicaux', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Nombre de spécialités par niveau
     */
    private function getSpecialitesParNiveau($niveauAbr)
    {
        $specialites = [
            'PACES' => 7, // MG, DENT, INF-G, INF-A, MAI, VET, DIET
            'L2' => 5,    // MG, INF-G, INF-A, MAI, DIET
            'L3' => 5,    // MG, INF-G, INF-A, MAI, DIET
            'M1' => 1,    // MG uniquement
            'M2' => 1,    // MG uniquement
            'D1' => 1     // MG uniquement
        ];
        
        return $specialites[$niveauAbr] ?? 0;
    }

    /**
     * Type de formation par niveau
     */
    private function getTypeFormation($niveauAbr)
    {
        $types = [
            'PACES' => 'Première Année Commune',
            'L2' => 'Licence 2ème année', 
            'L3' => 'Licence 3ème année',
            'M1' => 'Master 1ère année',
            'M2' => 'Master 2ème année',
            'D1' => 'Doctorat 1ère année'
        ];
        
        return $types[$niveauAbr] ?? 'Formation';
    }

    /**
     * Calcule les admis par niveau médical
     */
    private function calculerAdmisParNiveauMedical($niveauId)
    {
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive) return 0;

            $sessionCourante = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                                        ->where('is_current', true)
                                        ->first();
            if (!$sessionCourante) return 0;

            // Compte les étudiants admis pour ce niveau
            return ResultatFinal::join('etudiants', 'resultat_finals.etudiant_id', '=', 'etudiants.id')
                ->where('resultat_finals.session_exam_id', $sessionCourante->id)
                ->where('resultat_finals.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('resultat_finals.jury_validated', 1)
                ->where('resultat_finals.decision', ResultatFinal::DECISION_ADMIS)
                ->where('etudiants.niveau_id', $niveauId)
                ->where('etudiants.is_active', 1)
                ->count();

        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Statistiques par parcours médical 
     */
    private function getStatistiquesParcoursMediaux()
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
                ORDER BY 
                    CASE n.abr 
                        WHEN 'PACES' THEN 1 
                        WHEN 'L2' THEN 2 
                        WHEN 'L3' THEN 3 
                        WHEN 'M1' THEN 4 
                        WHEN 'M2' THEN 5 
                        WHEN 'D1' THEN 6 
                        ELSE 99 
                    END,
                    CASE p.abr
                        WHEN 'MG' THEN 1
                        WHEN 'DENT' THEN 2
                        WHEN 'INF-G' THEN 3
                        WHEN 'INF-A' THEN 4
                        WHEN 'MAI' THEN 5
                        WHEN 'VET' THEN 6
                        WHEN 'DIET' THEN 7
                        ELSE 99
                    END
            ";
            
            $results = DB::select($sql);
            
            $collection = collect();
            foreach ($results as $result) {
                // Calcul réel des admis par parcours
                $admisCount = $this->calculerAdmisParParcoursMedical($result->id, $parcoursColumn);
                $tauxReussite = $result->etudiants_count > 0 ? round(($admisCount / $result->etudiants_count) * 100, 1) : 0;
                
                $obj = (object) [
                    'id' => $result->id,
                    'nom' => $result->nom,
                    'abr' => $result->abr,
                    'etudiants_count' => $result->etudiants_count,
                    'admis_count' => $admisCount,
                    'taux_reussite' => $tauxReussite,
                    'niveau' => (object) [
                        'id' => $result->niveau_id,
                        'nom' => $result->niveau_nom,
                        'abr' => $result->niveau_abr,
                        'is_concours' => $result->is_concours,
                        'has_rattrapage' => $result->has_rattrapage
                    ],
                    'nom_complet' => $this->getNomCompletParcours($result->abr),
                    'couleur_badge' => $this->getCouleurBadgeParcours($result->abr)
                ];
                $collection->push($obj);
            }
            
            return $collection;
            
        } catch (\Exception $e) {
            Log::error('Erreur statistiques parcours médicaux', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Noms complets des parcours
     */
    private function getNomCompletParcours($abr)
    {
        $noms = [
            'MG' => 'Médecine Générale',
            'DENT' => 'Chirurgie Dentaire',
            'INF-G' => 'Infirmier Général',
            'INF-A' => 'Infirmier Anesthésiste',
            'MAI' => 'Maïeutique',
            'VET' => 'Vétérinaire',
            'DIET' => 'Diététique'
        ];
        
        return $noms[$abr] ?? $abr;
    }

    /**
     * Couleurs des badges par parcours
     */
    private function getCouleurBadgeParcours($abr)
    {
        $couleurs = [
            'MG' => 'primary',
            'DENT' => 'cyan',
            'INF-G' => 'green',
            'INF-A' => 'yellow',
            'MAI' => 'red',
            'VET' => 'slate',
            'DIET' => 'gray'
        ];
        
        return $couleurs[$abr] ?? 'gray';
    }

    /**
     * Calcule les admis par parcours médical
     */
    private function calculerAdmisParParcoursMedical($parcoursId, $parcoursColumn)
    {
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive) return 0;

            $sessionCourante = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                                        ->where('is_current', true)
                                        ->first();
            if (!$sessionCourante) return 0;

            return ResultatFinal::join('etudiants', 'resultat_finals.etudiant_id', '=', 'etudiants.id')
                ->where('resultat_finals.session_exam_id', $sessionCourante->id)
                ->where('resultat_finals.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('resultat_finals.jury_validated', 1)
                ->where('resultat_finals.decision', ResultatFinal::DECISION_ADMIS)
                ->where("etudiants.{$parcoursColumn}", $parcoursId)
                ->where('etudiants.is_active', 1)
                ->count();

        } catch (\Exception $e) {
            return 0;
        }
    }

    public function render()
    {
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        
        if (!$this->selectedYear && $anneeActive) {
            $this->selectedYear = $anneeActive->id;
        }

        // Données réelles faculté de médecine
        $totalEtudiants = Etudiant::where('is_active', true)->count();
        $etudiantsAdmis = 0;
        $redoublants = 0;
        $exclus = 0;
        $rattrapage = 0;
        $progressionEtudiants = 0;
        $progressionAdmis = 0;
        $progressionRedoublants = 0;
        $progressionExclus = 0;
        $progressionRattrapage = 0;
        $sessionDeliberee = false;
        
        // Graphiques vides par défaut
        $chartDataEtudiants = array_fill(0, 12, 0);
        $chartDataAdmis = array_fill(0, 12, 0);
        $chartDataRedoublants = array_fill(0, 12, 0);
        $chartDataExclus = array_fill(0, 12, 0);
        $chartDataRattrapage = array_fill(0, 12, 0);

        // Statistiques spéciales pour PACES (concours)
        $statsPACES = null;
        $statsGenerales = null;

        if ($anneeActive) {
            $sessionCourante = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                                        ->where('is_current', true)
                                        ->first();

            if ($sessionCourante) {
                $hasDeliberatedResults = ResultatFinal::where('session_exam_id', $sessionCourante->id)
                    ->where('statut', ResultatFinal::STATUT_PUBLIE)
                    ->where('jury_validated', 1)
                    ->exists();

                $sessionDeliberee = $hasDeliberatedResults;

                // Seulement si résultats délibérés
                if ($hasDeliberatedResults) {
                    $statistiquesSession = $this->calculerStatistiquesLogiqueMedecine($sessionCourante->id);
                    $etudiantsAdmis = $statistiquesSession['decisions']['admis'] ?? 0;
                    $redoublants = $statistiquesSession['decisions']['redoublants'] ?? 0;
                    $exclus = $statistiquesSession['decisions']['exclus'] ?? 0;
                    $rattrapage = $statistiquesSession['decisions']['rattrapage'] ?? 0;

                    // Statistiques spécifiques PACES
                    $statsPACES = $this->calculerStatistiquesPACES();
                }

                // Graphiques évolution médecine
                $chartData = $this->genererDonneesGraphiquesMedicales($anneeActive->id);
                $chartDataEtudiants = $chartData['etudiants'];
                $chartDataAdmis = $chartData['admis'];
                $chartDataRedoublants = $chartData['redoublants'];
                $chartDataExclus = $chartData['excluss'];
                $chartDataRattrapage = $chartData['rattrapage'];
            }

            // Progression année précédente
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

        // Récupération données médicales
        $statistiquesNiveaux = $this->getStatistiquesNiveauxMedicaux();
        $statistiquesParcours = $this->getStatistiquesParcoursMediaux();
        $anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();

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
            'statistiquesNiveaux',
            'statistiquesParcours',
            'anneesUniversitaires',
            'statsPACES'
        ))->with([
            'selectedYear' => $this->selectedYear,
            'selectedChartType' => $this->selectedChartType
        ]);
    }

    /**
     * Calcule statistiques spécifiques PACES (concours)
     */
    private function calculerStatistiquesPACES()
    {
        try {
            $niveauPACES = Niveau::where('abr', 'PACES')->first();
            if (!$niveauPACES) return null;

            $etudiantsPACES = Etudiant::where('niveau_id', $niveauPACES->id)
                                   ->where('is_active', true)
                                   ->count();

            $admisPACES = $this->calculerAdmisParNiveauMedical($niveauPACES->id);

            return [
                'total_candidats' => $etudiantsPACES,
                'admis_concours' => $admisPACES,
                'taux_selectivite' => $etudiantsPACES > 0 ? round(($admisPACES / $etudiantsPACES) * 100, 1) : 0,
                'places_disponibles' => $admisPACES // Basé sur les admis réels
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Génère graphiques spécifiques médecine
     */
    private function genererDonneesGraphiquesMedicales($anneeUniversitaireId)
    {
        $chartData = [
            'etudiants' => array_fill(0, 12, 0),
            'admis' => array_fill(0, 12, 0),
            'redoublants' => array_fill(0, 12, 0),
            'excluss' => array_fill(0, 12, 0),
            'rattrapage' => array_fill(0, 12, 0)
        ];

        try {
            $moisActuel = now();

            for ($i = 11; $i >= 0; $i--) {
                $mois = $moisActuel->copy()->subMonths($i);
                $indexMois = 11 - $i;

                // Inscriptions par niveau (focus PACES)
                $etudiantsMois = Etudiant::whereYear('created_at', $mois->year)
                                       ->whereMonth('created_at', $mois->month)
                                       ->where('is_active', true)
                                       ->count();

                $chartData['etudiants'][$indexMois] = $etudiantsMois;

                // Résultats délibérés par mois
                $sessions = SessionExam::where('annee_universitaire_id', $anneeUniversitaireId)
                    ->whereYear('created_at', $mois->year)
                    ->whereMonth('created_at', $mois->month)
                    ->get();

                $admis = $redoublants = $excluss = $rattrapage = 0;

                foreach ($sessions as $session) {
                    $stats = $this->calculerStatistiquesLogiqueMedecine($session->id);
                    $admis += $stats['decisions']['admis'];
                    $redoublants += $stats['decisions']['redoublants'];
                    $excluss += $stats['decisions']['excluss'];
                    $rattrapage += $stats['decisions']['rattrapage'];
                }

                $chartData['admis'][$indexMois] = $admis;
                $chartData['redoublants'][$indexMois] = $redoublants;
                $chartData['excluss'][$indexMois] = $excluss;
                $chartData['rattrapage'][$indexMois] = $rattrapage;
            }

        } catch (\Exception $e) {
            Log::error('Erreur génération graphiques médicaux', ['error' => $e->getMessage()]);
        }

        return $chartData;
    }

    /**
     * Calcule statistiques logique médecine
     */
    private function calculerStatistiquesLogiqueMedecine($sessionId)
    {
        try {
            $etudiantsAvecResultats = ResultatFinal::where('session_exam_id', $sessionId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->with('etudiant')
                ->get()
                ->groupBy('etudiant_id');

            $decisions = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublants' => 0,
                'excluss' => 0
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
                } else {
                    if ($isRattrapage) {
                        $decision = ResultatFinal::determinerDecisionRattrapage_LogiqueMedecine($etudiantId, $sessionId);
                    } else {
                        $decision = ResultatFinal::determinerDecisionPremiereSession_LogiqueMedecine($etudiantId, $sessionId);
                    }
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
                        $decisions['excluss']++;
                        break;
                }
            }

            return [
                'total_etudiants' => $etudiantsAvecResultats->count(),
                'decisions' => $decisions,
                'session_type' => $session ? $session->type : 'Normale'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques médecine', ['error' => $e->getMessage()]);

            return [
                'total_etudiants' => 0,
                'decisions' => [
                    'admis' => 0,
                    'rattrapage' => 0,
                    'redoublants' => 0,
                    'excluss' => 0
                ]
            ];
        }
    }

    private function calculerPourcentage($ancienneValeur, $nouvelleValeur)
    {
        if ($ancienneValeur == 0) {
            return $nouvelleValeur > 0 ? 100 : 0;
        }

        return round((($nouvelleValeur - $ancienneValeur) / $ancienneValeur) * 100, 1);
    }

    // Méthodes Livewire
    public function updatedSelectedNiveauFilter()
    {
        // Re-render automatique
    }

    public function changeYear($yearId)
    {
        $this->selectedYear = $yearId;
        session()->flash('info', "Année universitaire changée");
    }

    public function changeChartType($type)
    {
        $this->selectedChartType = $type;
    }

    public function refresh()
    {
        $this->refreshing = true;
        sleep(1);
        $this->refreshing = false;
        session()->flash('success', 'Dashboard actualisé !');
        $this->dispatch('dashboard-refreshed');
    }
}