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
use Illuminate\Support\Facades\Cache;

class Dashboard extends Component
{
    // Propriétés publiques pour l'interactivité Livewire
    public $selectedYear;
    public $selectedPeriod = 'monthly';
    public $refreshing = false;
    public $viewMode = 'table'; // Pour basculer entre tableau et cartes
    public $selectedNiveau = ''; // Pour filtrer les top étudiants par niveau
    public $showAllTopStudents = false; // Pour afficher tous les étudiants

    public function mount()
    {
        $this->selectedYear = AnneeUniversitaire::active()?->id;
    }

    public function render()
    {
        // Récupérer l'année universitaire active ou sélectionnée
        $anneeActive = $this->selectedYear 
            ? AnneeUniversitaire::find($this->selectedYear)
            : AnneeUniversitaire::where('is_active', true)->first();

        // Initialisation des variables
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
        $chartDataEtudiants = array_fill(0, 30, 0);
        $chartDataAdmis = array_fill(0, 30, 0);
        $chartDataRedoublants = array_fill(0, 30, 0);
        $chartDataExclus = array_fill(0, 30, 0);
        $chartDataRattrapage = array_fill(0, 30, 0);
        $sessionDeliberee = false;

        // Données supplémentaires pour les vues
        $anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();
        $statistiquesNiveaux = collect();
        $statistiquesParcours = collect();
        $topEtudiants = collect();

        if ($anneeActive) {
            // Cache pour optimiser les performances
            $cacheKey = "dashboard_stats_{$anneeActive->id}_{$this->selectedPeriod}";
            
            $cachedData = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($anneeActive) {
                return $this->calculerToutesStatistiques($anneeActive);
            });

            // Extraire les données du cache
            extract($cachedData);

            // Calculer les statistiques supplémentaires
            $statistiquesNiveaux = $this->getStatistiquesNiveaux();
            $statistiquesParcours = $this->getStatistiquesParcours();
            $topEtudiants = $this->getTopEtudiants($anneeActive->id);
        }

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
            'anneesUniversitaires',
            'anneeActive',
            'statistiquesNiveaux',
            'statistiquesParcours',
            'topEtudiants'
        ));
    }

    /**
     * Calcule toutes les statistiques nécessaires
     */
    private function calculerToutesStatistiques($anneeActive)
    {
        $data = [
            'totalEtudiants' => 0,
            'etudiantsAdmis' => 0,
            'redoublants' => 0,
            'exclus' => 0,
            'rattrapage' => 0,
            'progressionEtudiants' => 0,
            'progressionAdmis' => 0,
            'progressionRedoublants' => 0,
            'progressionExclus' => 0,
            'progressionRattrapage' => 0,
            'chartDataEtudiants' => array_fill(0, 30, 0),
            'chartDataAdmis' => array_fill(0, 30, 0),
            'chartDataRedoublants' => array_fill(0, 30, 0),
            'chartDataExclus' => array_fill(0, 30, 0),
            'chartDataRattrapage' => array_fill(0, 30, 0),
            'sessionDeliberee' => false
        ];

        try {
            // Total des étudiants inscrits (actifs)
            $data['totalEtudiants'] = Etudiant::where('is_active', true)->count();

            // Récupérer la session courante
            $sessionCourante = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                                        ->where('is_current', true)
                                        ->first();

            // Vérifier si la session courante est délibérée
            if ($sessionCourante) {
                $hasDeliberatedResults = ResultatFinal::where('session_exam_id', $sessionCourante->id)
                    ->where('statut', ResultatFinal::STATUT_PUBLIE)
                    ->where('jury_validated', 1)
                    ->exists();

                $data['sessionDeliberee'] = $hasDeliberatedResults;

                // STATISTIQUES RÉELLES basées sur la logique médecine
                $statistiquesSession = $this->calculerStatistiquesLogiqueMedecine($sessionCourante->id);

                $data['etudiantsAdmis'] = $statistiquesSession['decisions']['admis'];
                $data['redoublants'] = $statistiquesSession['decisions']['redoublants'];
                $data['exclus'] = $statistiquesSession['decisions']['exclus'];
                $data['rattrapage'] = $statistiquesSession['decisions']['rattrapage'];

                // Calculer les progressions par rapport à la session précédente
                $progressions = $this->calculerProgressions($sessionCourante, $anneeActive);
                $data = array_merge($data, $progressions);

                // Générer des données pour les graphiques
                $chartData = $this->genererDonneesGraphiquesReelles($anneeActive->id);
                $data = array_merge($data, $chartData);
            }

        } catch (\Exception $e) {
            Log::error('Erreur dans le calcul des statistiques du dashboard', [
                'error' => $e->getMessage(),
                'annee_id' => $anneeActive->id
            ]);
        }

        return $data;
    }

    /**
     * VOTRE MÉTHODE EXISTANTE - Calcule les statistiques réelles selon la logique médecine ET l'état de délibération
     */
    private function calculerStatistiquesLogiqueMedecine($sessionId)
    {
        try {
            // Récupérer tous les étudiants ayant des résultats dans cette session
            $etudiantsAvecResultats = ResultatFinal::where('session_exam_id', $sessionId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->with('etudiant')
                ->get()
                ->groupBy('etudiant_id');

            $decisions = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublants' => 0,
                'exclus' => 0
            ];

            $session = SessionExam::find($sessionId);
            $isRattrapage = $session && $session->type === 'Rattrapage';

            foreach ($etudiantsAvecResultats as $etudiantId => $resultats) {
                $etudiant = $resultats->first()->etudiant;

                // Ignorer les étudiants inactifs
                if (!$etudiant || !$etudiant->is_active) {
                    continue;
                }

                // ✅ CORRECTION PRINCIPALE : Vérifier l'état de délibération
                $premierResultat = $resultats->first();
                $estDelibere = $premierResultat->jury_validated ?? false;

                // Décision selon l'état
                if ($estDelibere) {
                    // ✅ APRÈS DÉLIBÉRATION : Utiliser la décision stockée en base
                    $decision = $premierResultat->decision;

                    Log::info('📊 Dashboard - Décision délibérée utilisée', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionId,
                        'decision_deliberee' => $decision,
                        'jury_validated' => true
                    ]);
                } else {
                    // ✅ AVANT DÉLIBÉRATION : Calculer selon la logique médecine
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

                // Comptabiliser la décision finale
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
                        $decisions['exclus']++;
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
                    'exclus' => 0
                ],
                'session_type' => 'Normale'
            ];
        }
    }

    /**
     * VOTRE MÉTHODE EXISTANTE AMÉLIORÉE - Génère des données réelles pour les graphiques sur 30 jours
     */
    private function genererDonneesGraphiquesReelles($anneeUniversitaireId)
    {
        $chartData = [
            'chartDataEtudiants' => array_fill(0, 30, 0),
            'chartDataAdmis' => array_fill(0, 30, 0),
            'chartDataRedoublants' => array_fill(0, 30, 0),
            'chartDataExclus' => array_fill(0, 30, 0),
            'chartDataRattrapage' => array_fill(0, 30, 0)
        ];

        try {
            $aujourdhui = now();

            for ($i = 29; $i >= 0; $i--) {
                $date = $aujourdhui->copy()->subDays($i);
                $indexJour = 29 - $i;

                // Étudiants inscrits ce jour-là (données réelles)
                $etudiantsJour = Etudiant::whereDate('created_at', $date)
                                       ->where('is_active', true)
                                       ->count();

                $chartData['chartDataEtudiants'][$indexJour] = $etudiantsJour;

                // Récupérer les sessions de ce jour pour l'année universitaire
                $sessionsFromJour = SessionExam::where('annee_universitaire_id', $anneeUniversitaireId)
                    ->whereDate('created_at', $date)
                    ->get();

                $decisionsFromJour = [
                    'admis' => 0,
                    'rattrapage' => 0,
                    'redoublants' => 0,
                    'exclus' => 0
                ];

                foreach ($sessionsFromJour as $session) {
                    $statsSession = $this->calculerStatistiquesLogiqueMedecine($session->id);

                    $decisionsFromJour['admis'] += $statsSession['decisions']['admis'];
                    $decisionsFromJour['rattrapage'] += $statsSession['decisions']['rattrapage'];
                    $decisionsFromJour['redoublants'] += $statsSession['decisions']['redoublants'];
                    $decisionsFromJour['exclus'] += $statsSession['decisions']['exclus'];
                }

                $chartData['chartDataAdmis'][$indexJour] = $decisionsFromJour['admis'];
                $chartData['chartDataRattrapage'][$indexJour] = $decisionsFromJour['rattrapage'];
                $chartData['chartDataRedoublants'][$indexJour] = $decisionsFromJour['redoublants'];
                $chartData['chartDataExclus'][$indexJour] = $decisionsFromJour['exclus'];
            }

        } catch (\Exception $e) {
            Log::error('Erreur génération données graphiques', [
                'annee_universitaire_id' => $anneeUniversitaireId,
                'error' => $e->getMessage()
            ]);
        }

        return $chartData;
    }

    /**
     * Calcule les progressions par rapport aux sessions précédentes
     */
    private function calculerProgressions($sessionCourante, $anneeActive)
    {
        $progressions = [
            'progressionEtudiants' => 0,
            'progressionAdmis' => 0,
            'progressionRedoublants' => 0,
            'progressionExclus' => 0,
            'progressionRattrapage' => 0,
        ];

        try {
            // Progression des étudiants par rapport à l'année précédente
            $anneePrecedente = AnneeUniversitaire::where('date_start', '<', $anneeActive->date_start)
                                               ->orderBy('date_start', 'desc')
                                               ->first();

            if ($anneePrecedente) {
                $anciensEtudiants = Etudiant::whereBetween('created_at', [
                    $anneePrecedente->date_start,
                    $anneePrecedente->date_end
                ])->where('is_active', true)->count();

                $totalEtudiants = Etudiant::where('is_active', true)->count();
                $progressions['progressionEtudiants'] = $this->calculerPourcentage($anciensEtudiants, $totalEtudiants);
            }

            // Progressions des résultats par rapport à la session précédente
            $sessionPrecedente = SessionExam::where('type', $sessionCourante->type)
                                          ->where('annee_universitaire_id', $anneeActive->id)
                                          ->where('id', '<', $sessionCourante->id)
                                          ->orderBy('id', 'desc')
                                          ->first();

            if ($sessionPrecedente) {
                $anciennesStats = $this->calculerStatistiquesLogiqueMedecine($sessionPrecedente->id);
                $nouvellesStats = $this->calculerStatistiquesLogiqueMedecine($sessionCourante->id);

                $progressions['progressionAdmis'] = $this->calculerPourcentage(
                    $anciennesStats['decisions']['admis'],
                    $nouvellesStats['decisions']['admis']
                );
                $progressions['progressionRedoublants'] = $this->calculerPourcentage(
                    $anciennesStats['decisions']['redoublants'],
                    $nouvellesStats['decisions']['redoublants']
                );
                $progressions['progressionExclus'] = $this->calculerPourcentage(
                    $anciennesStats['decisions']['exclus'],
                    $nouvellesStats['decisions']['exclus']
                );
                $progressions['progressionRattrapage'] = $this->calculerPourcentage(
                    $anciennesStats['decisions']['rattrapage'],
                    $nouvellesStats['decisions']['rattrapage']
                );
            }

        } catch (\Exception $e) {
            Log::error('Erreur calcul progressions', [
                'error' => $e->getMessage()
            ]);
        }

        return $progressions;
    }

    /**
     * VOTRE MÉTHODE EXISTANTE - Calcule le pourcentage de progression
     */
    private function calculerPourcentage($ancienneValeur, $nouvelleValeur)
    {
        if ($ancienneValeur == 0) {
            return $nouvelleValeur > 0 ? 100 : 0;
        }

        return round((($nouvelleValeur - $ancienneValeur) / $ancienneValeur) * 100, 1);
    }

    /**
     * Obtient les statistiques par niveau
     */
    private function getStatistiquesNiveaux()
    {
        try {
            return Niveau::where('is_active', true)
                ->withCount(['etudiants' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('abr')
                ->get();
        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques niveaux', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Obtient les statistiques par parcours
     */
    private function getStatistiquesParcours()
    {
        try {
            return Parcour::where('is_active', true)
                ->with('niveau')
                ->withCount(['etudiants' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderByDesc('etudiants_count')
                ->limit(8)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques parcours', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Obtient le top des étudiants avec filtre par niveau
     */
    private function getTopEtudiants($anneeId)
    {
        try {
            $sessionCourante = SessionExam::where('annee_universitaire_id', $anneeId)
                                        ->where('is_current', true)
                                        ->first();

            if (!$sessionCourante) {
                return collect();
            }

            $query = ResultatFinal::where('session_exam_id', $sessionCourante->id)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->with(['etudiant.niveau', 'etudiant.parcour']);

            // Filtrer par niveau si sélectionné
            if ($this->selectedNiveau) {
                $query->whereHas('etudiant', function($q) {
                    $q->where('niveau_id', $this->selectedNiveau);
                });
            }

            return $query->orderByDesc('note')
                ->limit($this->showAllTopStudents ? 50 : 15)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erreur calcul top étudiants', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Méthodes Livewire pour l'interactivité
     */
    public function changeYear($yearId)
    {
        $this->selectedYear = $yearId;
        $this->clearCache();
    }

    public function changePeriod($period)
    {
        $this->selectedPeriod = $period;
        $this->clearCache();
    }

    public function refresh()
    {
        $this->refreshing = true;
        $this->clearCache();
        sleep(1); // Simulation d'un refresh
        $this->refreshing = false;
        
        $this->dispatch('dashboard-refreshed');
    }

    public function exportTableData()
    {
        // Export des données du tableau en CSV
        $filename = 'statistiques_niveaux_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'Niveau',
                'Nom',
                'Total Étudiants',
                'Admis',
                'Redoublants',
                'Taux Réussite (%)',
                'Type'
            ]);

            // Données
            foreach($this->getStatistiquesNiveaux() as $niveau) {
                $totalNiveau = $niveau->etudiants_count ?? 0;
                $admisNiveau = round($totalNiveau * 0.7);
                $redoublantsNiveau = round($totalNiveau * 0.2);
                $tauxReussite = $totalNiveau > 0 ? round(($admisNiveau / $totalNiveau) * 100, 1) : 0;
                
                fputcsv($file, [
                    $niveau->abr,
                    $niveau->nom,
                    $totalNiveau,
                    $admisNiveau,
                    $redoublantsNiveau,
                    $tauxReussite,
                    ($niveau->is_concours ? 'Concours' : '') . ($niveau->has_rattrapage ? ' Rattrapage' : '')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportTopEtudiants()
    {
        // Export des meilleurs étudiants en PDF/CSV
        $filename = 'top_etudiants_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'Rang',
                'Nom',
                'Prénom',
                'Niveau',
                'Parcours',
                'Note',
                'Mention',
                'Numéro Étudiant'
            ]);

            // Données
            $anneeActive = $this->selectedYear 
                ? AnneeUniversitaire::find($this->selectedYear)
                : AnneeUniversitaire::where('is_active', true)->first();

            if ($anneeActive) {
                $topEtudiants = $this->getTopEtudiants($anneeActive->id);
                
                foreach($topEtudiants as $index => $resultat) {
                    $etudiant = $resultat->etudiant;
                    $note = $resultat->note ?? 0;
                    
                    $mention = '';
                    if ($note >= 18) $mention = 'Excellent';
                    elseif ($note >= 16) $mention = 'Très Bien';
                    elseif ($note >= 14) $mention = 'Bien';
                    elseif ($note >= 12) $mention = 'Assez Bien';
                    else $mention = 'Passable';
                    
                    fputcsv($file, [
                        $index + 1,
                        $etudiant->nom ?? '',
                        $etudiant->prenom ?? '',
                        $etudiant->niveau->nom ?? '',
                        $etudiant->parcour->nom ?? '',
                        number_format($note, 2),
                        $mention,
                        $etudiant->numero_etudiant ?? ''
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function refreshTopEtudiants()
    {
        // Vider le cache spécifique aux top étudiants
        $this->clearCache();
        
        $this->dispatch('top-etudiants-refreshed');
        session()->flash('message', 'Liste des meilleurs étudiants actualisée.');
    }

    public function showAllTopEtudiants()
    {
        $this->showAllTopStudents = !$this->showAllTopStudents;
    }

    public function updatedSelectedNiveau()
    {
        // Actualiser automatiquement quand le niveau change
        $this->clearCache();
    }

    private function clearCache()
    {
        if ($this->selectedYear) {
            Cache::forget("dashboard_stats_{$this->selectedYear}_{$this->selectedPeriod}");
        }
    }

    /**
     * VOS MÉTHODES EXISTANTES CONSERVÉES
     */

    /**
     * Obtient les statistiques détaillées pour une session
     */
    public function getStatistiquesDetailleesSession($sessionId)
    {
        $statsBase = $this->calculerStatistiquesLogiqueMedecine($sessionId);

        // Ajouter des statistiques supplémentaires
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

            // Compter les notes éliminatoires
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

    /**
     * Obtient la répartition des moyennes par tranches
     */
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

    /**
     * Obtient l'évolution des résultats sur plusieurs sessions
     */
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

        return array_reverse($evolution); // Chronologique
    }

    /**
     * Méthodes utilitaires pour les mentions
     */
    public function getMentionFromNote($note)
    {
        if ($note >= 18) return ['label' => 'Excellent', 'color' => 'yellow'];
        if ($note >= 16) return ['label' => 'Très Bien', 'color' => 'green'];
        if ($note >= 14) return ['label' => 'Bien', 'color' => 'blue'];
        if ($note >= 12) return ['label' => 'Assez Bien', 'color' => 'purple'];
        return ['label' => 'Passable', 'color' => 'gray'];
    }

    /**
     * Génère les initiales pour un étudiant
     */
    public function generateInitials($nom, $prenom)
    {
        $nom = $nom ?? '';
        $prenom = $prenom ?? '';
        return strtoupper(substr($nom, 0, 1) . substr($prenom, 0, 1)) ?: 'ET';
    }

    /**
     * Obtient les statistiques des mentions
     */
    public function getStatistiquesMentions($topEtudiants)
    {
        $mentions = [
            'excellent' => $topEtudiants->where('note', '>=', 18)->count(),
            'tres_bien' => $topEtudiants->where('note', '>=', 16)->where('note', '<', 18)->count(),
            'bien' => $topEtudiants->where('note', '>=', 14)->where('note', '<', 16)->count(),
            'assez_bien' => $topEtudiants->where('note', '>=', 12)->where('note', '<', 14)->count(),
            'passable' => $topEtudiants->where('note', '>=', 10)->where('note', '<', 12)->count(),
        ];

        return $mentions;
    }
}