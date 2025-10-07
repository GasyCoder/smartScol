<?php
// app/Jobs/SimulerDeliberationJob.php

namespace App\Jobs;

use App\Models\UE;
use App\Models\DeliberPaces;
use App\Models\ResultatFinal;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SimulerDeliberationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 1;

    protected $simulationId;

    public function __construct($simulationId)
    {
        $this->simulationId = $simulationId;
    }

    public function handle()
    {
        $startTime = microtime(true);
        
        try {
            // Charger la simulation
            $simulation = DeliberPaces::findOrFail($this->simulationId);
            
            $this->updateStatus($simulation, 'processing', 10);

            // Charger les UEs
            $uesStructure = $this->chargerUEStructure($simulation);
            $this->updateStatus($simulation, 'processing', 20);

            // Charger les résultats
            $resultats = $this->chargerResultatsOptimises($simulation);
            $this->updateStatus($simulation, 'processing', 50);

            if (empty($resultats)) {
                $this->updateStatus($simulation, 'failed', 100, 'Aucun résultat trouvé');
                return;
            }

            // Appliquer la simulation
            $resultats = $this->appliquerSimulationAvecQuota($simulation, $resultats);
            $this->updateStatus($simulation, 'processing', 80);

            // Grouper et calculer stats
            $groupes = $this->grouperParDecision($resultats);
            $stats = $this->calculerStatistiques($groupes);
            $this->updateStatus($simulation, 'processing', 90);

            // Sauvegarder
            $duree = round((microtime(true) - $startTime), 2);
            $this->sauvegarderSimulation($simulation, $resultats, $groupes, $stats, $duree);
            
            $this->updateStatus($simulation, 'completed', 100, null, $duree);

        } catch (\Exception $e) {
            Log::error('Erreur Job Simulation PACES: ' . $e->getMessage(), [
                'simulation_id' => $this->simulationId,
                'trace' => $e->getTraceAsString()
            ]);
            
            $simulation = DeliberPaces::find($this->simulationId);
            if ($simulation) {
                $this->updateStatus($simulation, 'failed', 100, $e->getMessage());
            }
            
            throw $e;
        }
    }

    private function updateStatus($simulation, $status, $progress, $error = null, $duree = null)
    {
        $simulation->update([
            'status' => $status,
            'progress' => $progress,
            'error_message' => $error,
            'duree_secondes' => $duree,
        ]);
    }

    private function chargerUEStructure($simulation)
    {
        return UE::where('niveau_id', $simulation->niveau_id)
            ->where('is_active', true)
            ->where(function($q) use ($simulation) {
                $q->where('parcours_id', $simulation->parcours_id)
                  ->orWhereNull('parcours_id');
            })
            ->with(['ecs' => function($q) {
                $q->where('is_active', true)->orderBy('id');
            }])
            ->orderBy('id')
            ->get();
    }

    private function chargerResultatsOptimises($simulation)
    {
        $resultats = DB::table('resultats_finaux as rf')
            ->join('examens as e', 'rf.examen_id', '=', 'e.id')
            ->join('etudiants as et', 'rf.etudiant_id', '=', 'et.id')
            ->join('ecs', 'rf.ec_id', '=', 'ecs.id')
            ->join('ues', 'ecs.ue_id', '=', 'ues.id')
            ->where('e.niveau_id', $simulation->niveau_id)
            ->where('e.parcours_id', $simulation->parcours_id)
            ->where('rf.session_exam_id', $simulation->session_exam_id)
            ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
            ->select([
                'rf.etudiant_id',
                'et.matricule',
                'et.nom',
                'et.prenom',
                'rf.ec_id',
                'rf.note',
                'rf.decision',
                'rf.is_deliber',
                'ecs.ue_id',
                'ues.credits as ue_credits',
                DB::raw('CAST(et.matricule AS UNSIGNED) as matricule_num')
            ])
            ->orderBy('matricule_num')
            ->get()
            ->groupBy('etudiant_id');

        return $this->traiterResultats($resultats);
    }

    private function traiterResultats($groupedResults)
    {
        $final = [];
        
        foreach ($groupedResults as $etudiantId => $notes) {
            $premier = $notes->first();
            
            $parUE = $notes->groupBy('ue_id');
            $resultatsUE = [];
            $creditsValides = 0;
            $totalCredits = 0;
            $sommesMoyennes = 0;
            $nbUE = 0;
            $hasElim = false;
            
            foreach ($parUE as $ueId => $notesUE) {
                $ueCredits = $notesUE->first()->ue_credits;
                $totalCredits += $ueCredits;
                
                $moyenneUE = $notesUE->avg('note');
                $hasZero = $notesUE->contains('note', 0);
                
                if ($hasZero) $hasElim = true;
                
                $validee = ($moyenneUE >= 10) && !$hasZero;
                if ($validee) $creditsValides += $ueCredits;
                
                $sommesMoyennes += $moyenneUE;
                $nbUE++;
                
                $resultatsUE[] = [
                    'ue_id' => $ueId,
                    'moyenne_ue' => round($moyenneUE, 2),
                    'ue_validee' => $validee,
                    'has_note_eliminatoire' => $hasZero
                ];
            }
            
            $moyenneGenerale = $nbUE > 0 ? round($sommesMoyennes / $nbUE, 2) : 0;
            
            $final[] = [
                'etudiant_id' => $etudiantId,
                'matricule' => $premier->matricule,
                'nom' => $premier->nom,
                'prenom' => $premier->prenom,
                'resultats_ue' => $resultatsUE,
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => $creditsValides,
                'total_credits' => $totalCredits,
                'has_note_eliminatoire' => $hasElim,
                'decision' => $premier->decision ?? 'non_definie',
                'is_deliber' => $premier->is_deliber ?? false,
                'est_redoublant' => intval($premier->matricule) <= 38999
            ];
        }
        
        return $final;
    }

    private function appliquerSimulationAvecQuota($simulation, $resultats)
    {
        usort($resultats, function($a, $b) {
            $diff = $b['credits_valides'] - $a['credits_valides'];
            if ($diff !== 0) return $diff <=> 0;
            
            $diff = $b['moyenne_generale'] - $a['moyenne_generale'];
            if ($diff !== 0) return $diff <=> 0;
            
            return ($a['has_note_eliminatoire'] ? 1 : 0) <=> ($b['has_note_eliminatoire'] ? 1 : 0);
        });

        $admisCount = 0;
        $checkQuota = $simulation->quota_admission !== null && $simulation->quota_admission !== '';

        foreach ($resultats as &$resultat) {
            $credits = $resultat['credits_valides'];
            $moyenne = $resultat['moyenne_generale'];
            $hasElim = $resultat['has_note_eliminatoire'];

            if ($simulation->note_eliminatoire && $hasElim) {
                $resultat['decision'] = 'exclus';
                $resultat['decision_simulee'] = true;
                continue;
            }

            if ($credits < 30 || $moyenne < 8) {
                $resultat['decision'] = 'exclus';
                $resultat['decision_simulee'] = true;
                continue;
            }

            if ($credits >= $simulation->credits_requis && $moyenne >= $simulation->moyenne_requise) {
                if (!$checkQuota || $admisCount < $simulation->quota_admission) {
                    $resultat['decision'] = 'admis';
                    $admisCount++;
                } else {
                    $resultat['decision'] = 'redoublant';
                }
            } else {
                $resultat['decision'] = 'redoublant';
            }

            $resultat['decision_simulee'] = true;
        }

        return $resultats;
    }

    private function grouperParDecision($resultats)
    {
        $groupes = ['admis' => [], 'redoublant' => [], 'exclus' => []];

        foreach ($resultats as $resultat) {
            $decision = $resultat['decision'];
            if (isset($groupes[$decision])) {
                $groupes[$decision][] = $resultat;
            }
        }

        return $groupes;
    }

    private function calculerStatistiques($groupes)
    {
        return [
            'admis' => count($groupes['admis']),
            'redoublant' => count($groupes['redoublant']),
            'exclus' => count($groupes['exclus'])
        ];
    }

    private function sauvegarderSimulation($simulation, $resultats, $groupes, $stats, $duree)
    {
        $simulation->update([
            'resultats' => $resultats,
            'groupes' => $groupes,
            'statistiques' => $stats,
            'nb_admis' => $stats['admis'],
            'nb_redoublants' => $stats['redoublant'],
            'nb_exclus' => $stats['exclus'],
            'duree_secondes' => $duree,
        ]);
    }
}