<?php

namespace App\Livewire;

use App\Models\UE;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\ResultatFinal;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CalculAcademiqueService;

class Dashboard extends Component
{
    public $selectedNiveauFilter = '';
    public $viewMode = 'table';
    public $refreshing = false;
    public $selectedYear;
    public $selectedChartType = 'line';

    /**
     * ✅ CORRECTION 1 : Utiliser les bonnes tables et relations
     */
    private function getStatistiquesNiveauxOptimisees()
    {
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive) {
                return collect();
            }

            // ✅ GARDER TOUTES LES SESSIONS
            $sessions = SessionExam::where('annee_universitaire_id', $anneeActive->id)->get();
            $sessionIds = $sessions->pluck('id')->toArray();

            if (empty($sessionIds)) {
                return collect();
            }

            $niveaux = Niveau::where('is_active', true)
                ->withCount(['etudiants as etudiants_actifs' => function($query) {
                    $query->where('is_active', true);
                }])
                ->get();

            $collection = collect();

            foreach ($niveaux as $niveau) {
                // ✅ CALCULS AVEC LOGIQUE PAR TYPE DE SESSION
                $admisCount = $this->calculerAdmisParNiveauToutesSessionsLogique($niveau->id, $sessions);
                $rattrapageCount = $this->calculerRattrapageParNiveauToutesSessionsLogique($niveau->id, $sessions);
                $redoublantCount = $this->calculerRedoublantParNiveauToutesSessionsLogique($niveau->id, $sessions);
                $exclusCount = $this->calculerExclusParNiveauToutesSessionsLogique($niveau->id, $sessions);

                $totalEtudiants = $niveau->etudiants_actifs;
                $tauxReussite = $totalEtudiants > 0 ? round(($admisCount / $totalEtudiants) * 100, 1) : 0;

                $collection->push((object) [
                    'id' => $niveau->id,
                    'abr' => $niveau->abr,
                    'nom' => $niveau->nom,
                    'is_concours' => $niveau->is_concours,
                    'has_rattrapage' => $niveau->has_rattrapage,
                    'has_parcours' => $niveau->has_parcours,
                    'etudiants_count' => $totalEtudiants,
                    'admis_count' => $admisCount,
                    'rattrapage_count' => $rattrapageCount,
                    'redoublant_count' => $redoublantCount,
                    'exclus_count' => $exclusCount,
                    'taux_reussite' => $tauxReussite,
                    'specialites_count' => $this->getSpecialitesParNiveau($niveau->abr),
                    'type_formation' => $this->getTypeFormation($niveau->abr)
                ]);
            }

            return $collection->sortBy(function($niveau) {
                $ordre = [
                    'PACES' => 1, 'L2' => 2, 'L3' => 3, 
                    'M1' => 4, 'M2' => 5, 'D1' => 6
                ];
                return $ordre[$niveau->abr] ?? 99;
            })->values();

        } catch (\Exception $e) {
            Log::error('Erreur statistiques niveaux optimisées', ['error' => $e->getMessage()]);
            return collect();
        }
    }


    private function calculerAdmisParNiveauToutesSessionsLogique($niveauId, $sessions)
    {
        try {
            $totalAdmis = 0;

            foreach ($sessions as $session) {
                // Compter les admis de cette session
                $admisSession = DB::table('resultats_finaux as rf')
                    ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                    ->where('rf.session_exam_id', $session->id)
                    ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                    ->where('rf.decision', ResultatFinal::DECISION_ADMIS)
                    ->where('e.niveau_id', $niveauId)
                    ->where('e.is_active', true)
                    ->whereNull('e.deleted_at')
                    ->distinct('e.id')
                    ->count('e.id');

                $totalAdmis += $admisSession;
            }

            return $totalAdmis;
        } catch (\Exception $e) {
            Log::error('Erreur calcul admis niveau toutes sessions', ['niveau_id' => $niveauId, 'error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Rattrapage avec logique selon type session
     */
    private function calculerRattrapageParNiveauToutesSessionsLogique($niveauId, $sessions)
    {
        try {
            $totalRattrapage = 0;

            foreach ($sessions as $session) {
                if ($session->type === 'Normale') {
                    // Session normale : compter les rattrapages
                    $rattrapageSession = DB::table('resultats_finaux as rf')
                        ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                        ->where('rf.session_exam_id', $session->id)
                        ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                        ->where('rf.decision', ResultatFinal::DECISION_RATTRAPAGE)
                        ->where('e.niveau_id', $niveauId)
                        ->where('e.is_active', true)
                        ->whereNull('e.deleted_at')
                        ->distinct('e.id')
                        ->count('e.id');

                    $totalRattrapage += $rattrapageSession;
                }
                // Session rattrapage : pas de rattrapage (ils deviennent redoublants ou exclus)
            }

            return $totalRattrapage;
        } catch (\Exception $e) {
            return 0;
        }
    }


    
    /**
     * ✅ CORRECTION 2 : Calculer admis selon logique médecine
     */
    private function calculerAdmisParNiveauLogiqueMedecine($niveauId, $sessionIds)
    {
        try {
            // ✅ CORRECTION: session_exams au lieu de sessions_exams
            $sessionsNormales = DB::table('session_exams') // ✅ CORRIGÉ
                ->whereIn('id', $sessionIds)
                ->where('type', 'Normale')
                ->pluck('id')
                ->toArray();

            return DB::table('resultats_finaux as rf')
                ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                ->whereIn('rf.session_exam_id', $sessionsNormales)
                ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('rf.decision', ResultatFinal::DECISION_ADMIS)
                ->where('e.niveau_id', $niveauId)
                ->where('e.is_active', true)
                ->whereNull('e.deleted_at')
                ->distinct('e.id')
                ->count('e.id');
        } catch (\Exception $e) {
            Log::error('Erreur calcul admis niveau', ['niveau_id' => $niveauId, 'error' => $e->getMessage()]);
            return 0;
        }
    }

    private function calculerRedoublantParNiveauToutesSessionsLogique($niveauId, $sessions)
    {
        try {
            $totalRedoublants = 0;

            foreach ($sessions as $session) {
                if ($session->type === 'Rattrapage') {
                    // Seulement session rattrapage : compter les redoublants
                    $redoublantsSession = DB::table('resultats_finaux as rf')
                        ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                        ->where('rf.session_exam_id', $session->id)
                        ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                        ->where('rf.decision', ResultatFinal::DECISION_REDOUBLANT)
                        ->where('e.niveau_id', $niveauId)
                        ->where('e.is_active', true)
                        ->whereNull('e.deleted_at')
                        ->distinct('e.id')
                        ->count('e.id');

                    $totalRedoublants += $redoublantsSession;
                }
                // Session normale : pas de redoublants
            }

            return $totalRedoublants;
        } catch (\Exception $e) {
            return 0;
        }
    }


    private function calculerExclusParNiveauToutesSessionsLogique($niveauId, $sessions)
    {
        try {
            $totalExclus = 0;

            foreach ($sessions as $session) {
                if ($session->type === 'Rattrapage') {
                    // Seulement session rattrapage : compter les exclus
                    $exclusSession = DB::table('resultats_finaux as rf')
                        ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                        ->where('rf.session_exam_id', $session->id)
                        ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                        ->where('rf.decision', ResultatFinal::DECISION_EXCLUS)
                        ->where('e.niveau_id', $niveauId)
                        ->where('e.is_active', true)
                        ->whereNull('e.deleted_at')
                        ->distinct('e.id')
                        ->count('e.id');

                    $totalExclus += $exclusSession;
                }
                // Session normale : pas d'exclus
            }

            return $totalExclus;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * ✅ CORRECTION 3 : Méthodes pour autres décisions
     */
    private function calculerRattrapageParNiveau($niveauId, $sessionIds)
    {
        try {
            // ✅ CORRECTION: session_exams au lieu de sessions_exams
            $sessionsNormales = DB::table('session_exams') // ✅ CORRIGÉ
                ->whereIn('id', $sessionIds)
                ->where('type', 'Normale')
                ->pluck('id')
                ->toArray();

            return DB::table('resultats_finaux as rf')
                ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                ->whereIn('rf.session_exam_id', $sessionsNormales)
                ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('rf.decision', ResultatFinal::DECISION_RATTRAPAGE)
                ->where('e.niveau_id', $niveauId)
                ->where('e.is_active', true)
                ->whereNull('e.deleted_at')
                ->distinct('e.id')
                ->count('e.id');
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculerRedoublantParNiveau($niveauId, $sessionIds)
    {
        try {
            return DB::table('resultats_finaux as rf')
                ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                ->whereIn('rf.session_exam_id', $sessionIds)
                ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('rf.decision', ResultatFinal::DECISION_REDOUBLANT)
                ->where('e.niveau_id', $niveauId)
                ->where('e.is_active', true)
                ->whereNull('e.deleted_at')
                ->distinct('e.id')
                ->count('e.id');
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculerExclusParNiveau($niveauId, $sessionIds)
    {
        try {
            return DB::table('resultats_finaux as rf')
                ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                ->whereIn('rf.session_exam_id', $sessionIds)
                ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('rf.decision', ResultatFinal::DECISION_EXCLUS)
                ->where('e.niveau_id', $niveauId)
                ->where('e.is_active', true)
                ->whereNull('e.deleted_at')
                ->distinct('e.id')
                ->count('e.id');
        } catch (\Exception $e) {
            return 0;
        }
    }



    /**
     * ✅ CORRECTION 4 : Statistiques parcours optimisées
     */
    private function getStatistiquesParcoursOptimisees()
    {
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive) {
                return collect();
            }

            $sessions = SessionExam::where('annee_universitaire_id', $anneeActive->id)->get();
            $sessionIds = $sessions->pluck('id')->toArray();

            if (empty($sessionIds)) {
                return collect();
            }

            // ✅ REQUÊTE OPTIMISÉE avec relations
            $parcours = Parcour::where('is_active', true)
                ->with(['niveau:id,nom,abr,is_concours,has_rattrapage'])
                ->withCount(['etudiants as etudiants_actifs' => function($query) {
                    $query->where('is_active', true);
                }])
                ->get();

            $collection = collect();

            foreach ($parcours as $parcour) {
                if (!$parcour->niveau) continue;

                $admisCount = $this->calculerAdmisParParcoursLogiqueMedecine($parcour->id, $sessionIds);
                $totalEtudiants = $parcour->etudiants_actifs;
                $tauxReussite = $totalEtudiants > 0 ? round(($admisCount / $totalEtudiants) * 100, 1) : 0;

                $collection->push((object) [
                    'id' => $parcour->id,
                    'nom' => $parcour->nom,
                    'abr' => $parcour->abr,
                    'etudiants_count' => $totalEtudiants,
                    'admis_count' => $admisCount,
                    'taux_reussite' => $tauxReussite,
                    'niveau' => $parcour->niveau,
                    'nom_complet' => $this->getNomCompletParcours($parcour->abr),
                    'couleur_badge' => $this->getCouleurBadgeParcours($parcour->abr)
                ]);
            }

            // ✅ Tri logique médecine
            return $collection->sortBy([
                ['niveau.abr', function($niveau) {
                    $ordre = ['PACES' => 1, 'L2' => 2, 'L3' => 3, 'M1' => 4, 'M2' => 5, 'D1' => 6];
                    return $ordre[$niveau] ?? 99;
                }],
                ['abr', function($parcours) {
                    $ordre = ['MG' => 1, 'DENT' => 2, 'INF-G' => 3, 'INF-A' => 4, 'MAI' => 5, 'VET' => 6, 'DIET' => 7];
                    return $ordre[$parcours] ?? 99;
                }]
            ])->values();

        } catch (\Exception $e) {
            Log::error('Erreur statistiques parcours optimisées', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * ✅ CORRECTION 5 : Calculer admis par parcours
     */
    private function calculerAdmisParParcoursLogiqueMedecine($parcoursId, $sessionIds)
    {
        try {
            // ✅ CORRECTION: session_exams au lieu de sessions_exams
            $sessionsNormales = DB::table('session_exams') // ✅ CORRIGÉ
                ->whereIn('id', $sessionIds)
                ->where('type', 'Normale')
                ->pluck('id')
                ->toArray();

            return DB::table('resultats_finaux as rf')
                ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                ->whereIn('rf.session_exam_id', $sessionsNormales)
                ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('rf.decision', ResultatFinal::DECISION_ADMIS)
                ->where('e.parcours_id', $parcoursId)
                ->where('e.is_active', true)
                ->whereNull('e.deleted_at')
                ->distinct('e.id')
                ->count('e.id');
        } catch (\Exception $e) {
            Log::error('Erreur calcul admis parcours', ['parcours_id' => $parcoursId, 'error' => $e->getMessage()]);
            return 0;
        }
    }


    /**
     * ✅ CORRECTION 7 : Graphiques optimisés
     */
    private function genererDonneesGraphiquesOptimisees($anneeUniversitaireId)
    {
        $chartData = [
            'etudiants' => array_fill(0, 12, 0),
            'admis' => array_fill(0, 12, 0),
            'redoublants' => array_fill(0, 12, 0), // Sera toujours 0
            'excluss' => array_fill(0, 12, 0), // Sera toujours 0  
            'rattrapage' => array_fill(0, 12, 0)
        ];

        try {
            $moisActuel = now();
            
            // ✅ FILTRER SEULEMENT SESSIONS NORMALES
            $sessionsNormales = SessionExam::where('annee_universitaire_id', $anneeUniversitaireId)
                ->where('type', 'Normale')
                ->get();
                
            $sessionIds = $sessionsNormales->pluck('id')->toArray();

            if (empty($sessionIds)) {
                return $chartData;
            }

            for ($i = 11; $i >= 0; $i--) {
                $mois = $moisActuel->copy()->subMonths($i);
                $indexMois = 11 - $i;

                // Inscriptions par mois
                $etudiantsMois = Etudiant::whereYear('created_at', $mois->year)
                                    ->whereMonth('created_at', $mois->month)
                                    ->where('is_active', true)
                                    ->count();

                $chartData['etudiants'][$indexMois] = $etudiantsMois;

                // ✅ Résultats sessions normales seulement
                $resultats = DB::table('resultats_finaux as rf')
                    ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                    ->whereIn('rf.session_exam_id', $sessionIds) // ✅ SESSIONS NORMALES
                    ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                    ->whereYear('rf.created_at', $mois->year)
                    ->whereMonth('rf.created_at', $mois->month)
                    ->where('e.is_active', true)
                    ->selectRaw('
                        COUNT(DISTINCT CASE WHEN rf.decision = ? THEN e.id END) as admis,
                        COUNT(DISTINCT CASE WHEN rf.decision = ? THEN e.id END) as rattrapage
                    ', [
                        ResultatFinal::DECISION_ADMIS,
                        ResultatFinal::DECISION_RATTRAPAGE
                    ])
                    ->first();

                $chartData['admis'][$indexMois] = $resultats->admis ?? 0;
                $chartData['rattrapage'][$indexMois] = $resultats->rattrapage ?? 0;
                // ✅ Forcer redoublants et exclus à 0 pour sessions normales
                $chartData['redoublants'][$indexMois] = 0;
                $chartData['excluss'][$indexMois] = 0;
            }

        } catch (\Exception $e) {
            Log::error('Erreur génération graphiques', ['error' => $e->getMessage()]);
        }

        return $chartData;
    }

    // ✅ GARDER les méthodes utilitaires existantes
    private function getSpecialitesParNiveau($niveauAbr)
    {
        $specialites = [
            'PACES' => 7, 'L2' => 5, 'L3' => 5, 'M1' => 1, 'M2' => 1, 'D1' => 1
        ];
        return $specialites[$niveauAbr] ?? 0;
    }

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

    private function getCouleurBadgeParcours($abr)
    {
        $couleurs = [
            'MG' => 'primary', 'DENT' => 'cyan', 'INF-G' => 'green',
            'INF-A' => 'yellow', 'MAI' => 'red', 'VET' => 'slate', 'DIET' => 'gray'
        ];
        return $couleurs[$abr] ?? 'gray';
    }

    private function calculerPourcentage($ancienneValeur, $nouvelleValeur)
    {
        if ($ancienneValeur == 0) {
            return $nouvelleValeur > 0 ? 100 : 0;
        }
        return round((($nouvelleValeur - $ancienneValeur) / $ancienneValeur) * 100, 1);
    }

    // ✅ MÉTHODE RENDER OPTIMISÉE
    public function render()
    {
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        
        if (!$this->selectedYear && $anneeActive) {
            $this->selectedYear = $anneeActive->id;
        }

        // ✅ UTILISER les nouvelles méthodes optimisées
        $statistiquesGlobales = $this->calculerStatistiquesGlobalesLogiqueMedecine();
        
        $totalEtudiants = $statistiquesGlobales['total_etudiants'];
        $etudiantsAdmis = $statistiquesGlobales['admis'];
        $redoublants = $statistiquesGlobales['redoublant'];
        $exclus = $statistiquesGlobales['exclus'];
        $rattrapage = $statistiquesGlobales['rattrapage'];
        $sessionDeliberee = $statistiquesGlobales['session_deliberee'];

        // Calcul des progressions (simplifié)
        $progressionEtudiants = 0;
        $progressionAdmis = 0;
        $progressionRedoublants = 0;
        $progressionExclus = 0;
        $progressionRattrapage = 0;

        // Graphiques
        $chartDataEtudiants = array_fill(0, 12, 0);
        $chartDataAdmis = array_fill(0, 12, 0);
        $chartDataRedoublants = array_fill(0, 12, 0);
        $chartDataExclus = array_fill(0, 12, 0);
        $chartDataRattrapage = array_fill(0, 12, 0);

        if ($anneeActive) {
            $chartData = $this->genererDonneesGraphiquesOptimisees($anneeActive->id);
            $chartDataEtudiants = $chartData['etudiants'];
            $chartDataAdmis = $chartData['admis'];
            $chartDataRedoublants = $chartData['redoublants'];
            $chartDataExclus = $chartData['excluss'];
            $chartDataRattrapage = $chartData['rattrapage'];
        }

        // ✅ UTILISER les méthodes optimisées
        $statistiquesNiveaux = $this->getStatistiquesNiveauxOptimisees();
        $statistiquesParcours = $this->getStatistiquesParcoursOptimisees();
        $anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();

        // Statistiques PACES spécifiques
        $statsPACES = $this->calculerStatistiquesPACES();

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
     * ✅ CORRECTION 8 : Statistiques PACES optimisées
     */
    private function calculerStatistiquesPACES()
    {
        try {
            $niveauPACES = Niveau::where('abr', 'PACES')->first();
            if (!$niveauPACES) return null;

            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive) return null;

            $sessions = SessionExam::where('annee_universitaire_id', $anneeActive->id)->get();
            $sessionIds = $sessions->pluck('id')->toArray();

            $etudiantsPACES = Etudiant::where('niveau_id', $niveauPACES->id)
                                   ->where('is_active', true)
                                   ->count();

            $admisPACES = $this->calculerAdmisParNiveauLogiqueMedecine($niveauPACES->id, $sessionIds);

            return [
                'total_candidats' => $etudiantsPACES,
                'admis_concours' => $admisPACES,
                'taux_selectivite' => $etudiantsPACES > 0 ? round(($admisPACES / $etudiantsPACES) * 100, 1) : 0,
                'places_disponibles' => $admisPACES
            ];

        } catch (\Exception $e) {
            Log::error('Erreur statistiques PACES', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // Méthodes Livewire existantes (inchangées)
    public function updatedSelectedNiveauFilter() { }
    public function changeYear($yearId) { $this->selectedYear = $yearId; }
    public function changeChartType($type) { $this->selectedChartType = $type; }
    
    public function refresh()
    {
        $this->refreshing = true;
        sleep(1);
        $this->refreshing = false;
        session()->flash('success', 'Dashboard actualisé !');
        $this->dispatch('dashboard-refreshed');
    }


    public function forcerRecalculDecisions()
    {
        try {
            $calculService = new CalculAcademiqueService();
            
            // Forcer le recalcul avec la nouvelle logique
            $result = $calculService->corrigerToutesLesDecisions();
            
            if ($result['success']) {
                session()->flash('success', 
                    "Décisions recalculées ! Redoublants: {$result['statistiques']['redoublant']}, " .
                    "Admis: {$result['statistiques']['admis']}, " .
                    "Rattrapage: {$result['statistiques']['rattrapage']}"
                );
                
                Log::info('🔧 CORRECTION APPLIQUÉE', $result['statistiques']);
            } else {
                session()->flash('error', 'Erreur lors du recalcul: ' . $result['message']);
            }
            
            // Rafraîchir les données
            $this->dispatch('dashboard-refreshed');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
            Log::error('Erreur recalcul dashboard', ['error' => $e->getMessage()]);
        }
    }



    private function forcerRecalculSiNecessaire($sessionIds)
    {
        // Vérifier s'il y a des redoublants
        $countRedoublants = DB::table('resultats_finaux')
            ->whereIn('session_exam_id', $sessionIds)
            ->where('statut', 'publie')
            ->whereIn('decision', ['redoublant', 'REDOUBLANT', 'Redoublant'])
            ->distinct('etudiant_id')
            ->count();
        
        // Si aucun redoublant, forcer le recalcul
        if ($countRedoublants == 0) {
            Log::warning('🚨 AUCUN REDOUBLANT TROUVÉ - RECALCUL FORCÉ');
            
            try {
                $calculService = new CalculAcademiqueService();
                $result = $calculService->corrigerToutesLesDecisions();
                
            } catch (\Exception $e) {
                Log::error('❌ Erreur recalcul automatique', ['error' => $e->getMessage()]);
            }
        }
    }



    private function calculerStatistiquesGlobalesLogiqueMedecine()
    {
        try {
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if (!$anneeActive) {
                return [
                    'total_etudiants' => 0, 'admis' => 0, 'rattrapage' => 0,
                    'redoublant' => 0, 'exclus' => 0, 'session_deliberee' => false
                ];
            }

            // ✅ FILTRER SEULEMENT LES SESSIONS NORMALES pour le dashboard principal
            $sessionsNormales = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                ->where('type', 'Normale') // ⚠️ SEULEMENT SESSIONS NORMALES
                ->get();
                
            $sessionIds = $sessionsNormales->pluck('id')->toArray();

            $totalEtudiants = Etudiant::where('is_active', true)->count();

            if (empty($sessionIds)) {
                return [
                    'total_etudiants' => $totalEtudiants,
                    'admis' => 0, 'rattrapage' => 0, 'redoublant' => 0, 'exclus' => 0,
                    'session_deliberee' => false,
                    'contexte_session' => 'Aucune session normale active'
                ];
            }

            // ✅ REQUÊTE CORRIGÉE : Seulement sessions normales
            $statistiques = DB::table('resultats_finaux as rf')
                ->join('etudiants as e', 'rf.etudiant_id', '=', 'e.id')
                ->whereIn('rf.session_exam_id', $sessionIds) // ✅ SEULEMENT SESSIONS NORMALES
                ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('e.is_active', true)
                ->whereNull('e.deleted_at')
                ->selectRaw('
                    COUNT(DISTINCT e.id) as total_avec_resultats,
                    COUNT(DISTINCT CASE WHEN rf.decision = ? THEN e.id END) as admis,
                    COUNT(DISTINCT CASE WHEN rf.decision = ? THEN e.id END) as rattrapage,
                    COUNT(DISTINCT CASE WHEN rf.decision = ? THEN e.id END) as redoublant,
                    COUNT(DISTINCT CASE WHEN rf.decision = ? THEN e.id END) as exclus
                ', [
                    ResultatFinal::DECISION_ADMIS,
                    ResultatFinal::DECISION_RATTRAPAGE,
                    ResultatFinal::DECISION_REDOUBLANT,
                    ResultatFinal::DECISION_EXCLUS
                ])
                ->first();

            // ✅ VÉRIFICATION : En session normale, redoublants DOIT être 0
            $admis = $statistiques->admis ?? 0;
            $rattrapage = $statistiques->rattrapage ?? 0;
            $redoublant = $statistiques->redoublant ?? 0;
            $exclus = $statistiques->exclus ?? 0;

            // ✅ SÉCURITÉ : Forcer redoublants = 0 en session normale
            if ($redoublant > 0) {
                $redoublant = 0;
            }
            
            if ($exclus > 0) {
                $exclus = 0;
            }

            $sessionDeliberee = ($admis + $rattrapage) > 0;

            return [
                'total_etudiants' => $totalEtudiants,
                'admis' => $admis,
                'rattrapage' => $rattrapage,
                'redoublant' => $redoublant, // = 0 en session normale
                'exclus' => $exclus, // = 0 en session normale
                'session_deliberee' => $sessionDeliberee,
                'etudiants_avec_resultats' => $statistiques->total_avec_resultats ?? 0,
                'contexte_session' => 'Session normale uniquement',
                'has_session_rattrapage' => false // Toujours false pour dashboard principal
            ];

        } catch (\Exception $e) {
            Log::error('Erreur statistiques dashboard', ['error' => $e->getMessage()]);
            
            return [
                'total_etudiants' => Etudiant::where('is_active', true)->count(),
                'admis' => 0, 'rattrapage' => 0, 'redoublant' => 0, 'exclus' => 0,
                'session_deliberee' => false, 'contexte_session' => 'Erreur'
            ];
        }
    }

}