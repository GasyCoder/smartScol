<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Etudiant;
use App\Models\ResultatFinal;
use App\Models\SessionExam;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Dashboard extends Component
{
    public function render()
    {
        // Récupérer l'année universitaire active
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();

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
        $chartDataEtudiants = array_fill(0, 12, 0);
        $chartDataAdmis = array_fill(0, 12, 0);
        $chartDataRedoublants = array_fill(0, 12, 0);
        $chartDataExclus = array_fill(0, 12, 0);
        $chartDataRattrapage = array_fill(0, 12, 0);

        if ($anneeActive) {
            // Total des étudiants inscrits (actifs)
            $totalEtudiants = Etudiant::where('is_active', true)->count();

            // Récupérer la session courante
            $sessionCourante = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                                        ->where('is_current', true)
                                        ->first();

            // ✅ NOUVEAU : Vérifier si la session courante est délibérée
            $sessionDeliberee = false;
            if ($sessionCourante) {
                // Vérifier si la session a été délibérée
                $hasDeliberatedResults = ResultatFinal::where('session_exam_id', $sessionCourante->id)
                    ->where('statut', ResultatFinal::STATUT_PUBLIE)
                    ->where('jury_validated', 1)
                    ->exists();

                $sessionDeliberee = $hasDeliberatedResults;
            }

            if ($sessionCourante) {
                // STATISTIQUES RÉELLES basées sur la logique médecine
                $statistiquesSession = $this->calculerStatistiquesLogiqueMedecine($sessionCourante->id);

                $etudiantsAdmis = $statistiquesSession['decisions']['admis'];
                $redoublants = $statistiquesSession['decisions']['redoublants'];
                $exclus = $statistiquesSession['decisions']['exclus'];
                $rattrapage = $statistiquesSession['decisions']['rattrapage'];

                // Calculer les progressions par rapport à la session précédente
                $sessionPrecedente = SessionExam::where('type', $sessionCourante->type)
                                              ->where('annee_universitaire_id', $anneeActive->id)
                                              ->where('id', '<', $sessionCourante->id)
                                              ->orderBy('id', 'desc')
                                              ->first();

                if ($sessionPrecedente) {
                    $anciennesStats = $this->calculerStatistiquesLogiqueMedecine($sessionPrecedente->id);

                    // Calcul des progressions en pourcentage
                    $progressionAdmis = $this->calculerPourcentage(
                        $anciennesStats['decisions']['admis'],
                        $etudiantsAdmis
                    );
                    $progressionRedoublants = $this->calculerPourcentage(
                        $anciennesStats['decisions']['redoublants'],
                        $redoublants
                    );
                    $progressionExclus = $this->calculerPourcentage(
                        $anciennesStats['decisions']['exclus'],
                        $exclus
                    );
                    $progressionRattrapage = $this->calculerPourcentage(
                        $anciennesStats['decisions']['rattrapage'],
                        $rattrapage
                    );
                }

                // Générer des données pour les graphiques (12 derniers mois) - DONNÉES RÉELLES
                $chartData = $this->genererDonneesGraphiquesReelles($anneeActive->id);
                $chartDataEtudiants = $chartData['etudiants'];
                $chartDataAdmis = $chartData['admis'];
                $chartDataRedoublants = $chartData['redoublants'];
                $chartDataExclus = $chartData['exclus'];
                $chartDataRattrapage = $chartData['rattrapage'];
            }

            // Progression des étudiants par rapport à l'année précédente
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
            'sessionDeliberee',        // ✅ NOUVEAU : Indicateur délibération
            'chartDataEtudiants',
            'chartDataAdmis',
            'chartDataRedoublants',
            'chartDataExclus',
            'chartDataRattrapage'
        ));
    }

    /**
     * Calcule les statistiques réelles selon la logique médecine ET l'état de délibération
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
     * Génère des données réelles pour les graphiques sur 12 mois
     */
    private function genererDonneesGraphiquesReelles($anneeUniversitaireId)
    {
        $chartData = [
            'etudiants' => array_fill(0, 12, 0),
            'admis' => array_fill(0, 12, 0),
            'redoublants' => array_fill(0, 12, 0),
            'exclus' => array_fill(0, 12, 0),
            'rattrapage' => array_fill(0, 12, 0)
        ];

        try {
            $moisActuel = now();

            for ($i = 11; $i >= 0; $i--) {
                $mois = $moisActuel->copy()->subMonths($i);
                $indexMois = 11 - $i;

                // Étudiants inscrits ce mois-là (données réelles)
                $etudiantsMois = Etudiant::whereYear('created_at', $mois->year)
                                       ->whereMonth('created_at', $mois->month)
                                       ->where('is_active', true)
                                       ->count();

                $chartData['etudiants'][$indexMois] = $etudiantsMois;

                // Récupérer les sessions de ce mois pour l'année universitaire
                $sessionsFromMois = SessionExam::where('annee_universitaire_id', $anneeUniversitaireId)
                    ->whereYear('created_at', $mois->year)
                    ->whereMonth('created_at', $mois->month)
                    ->get();

                $decisionsFromMois = [
                    'admis' => 0,
                    'rattrapage' => 0,
                    'redoublants' => 0,
                    'exclus' => 0
                ];

                foreach ($sessionsFromMois as $session) {
                    $statsSession = $this->calculerStatistiquesLogiqueMedecine($session->id);

                    $decisionsFromMois['admis'] += $statsSession['decisions']['admis'];
                    $decisionsFromMois['rattrapage'] += $statsSession['decisions']['rattrapage'];
                    $decisionsFromMois['redoublants'] += $statsSession['decisions']['redoublants'];
                    $decisionsFromMois['exclus'] += $statsSession['decisions']['exclus'];
                }

                $chartData['admis'][$indexMois] = $decisionsFromMois['admis'];
                $chartData['rattrapage'][$indexMois] = $decisionsFromMois['rattrapage'];
                $chartData['redoublants'][$indexMois] = $decisionsFromMois['redoublants'];
                $chartData['exclus'][$indexMois] = $decisionsFromMois['exclus'];
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
     * Calcule le pourcentage de progression
     */
    private function calculerPourcentage($ancienneValeur, $nouvelleValeur)
    {
        if ($ancienneValeur == 0) {
            return $nouvelleValeur > 0 ? 100 : 0;
        }

        return round((($nouvelleValeur - $ancienneValeur) / $ancienneValeur) * 100, 1);
    }

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
}