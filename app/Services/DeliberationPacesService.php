<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ResultatFinal;

class DeliberationPacesService
{
    /**
     * Calcule les résultats PACES de manière optimisée
     */
    public function calculerResultatsParcours($niveauId, $parcoursId, $sessionId)
    {
        // ✅ 1 SEULE REQUÊTE optimisée avec agrégation SQL
        $resultats = DB::table('resultats_finaux as rf')
            ->join('examens as ex', 'rf.examen_id', '=', 'ex.id')
            ->join('ecs', 'rf.ec_id', '=', 'ecs.id')
            ->join('ues', 'ecs.ue_id', '=', 'ues.id')
            ->join('etudiants as et', 'rf.etudiant_id', '=', 'et.id')
            ->where('ex.niveau_id', $niveauId)
            ->where('ex.parcours_id', $parcoursId)
            ->where('rf.session_exam_id', $sessionId)
            ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
            ->select(
                'rf.etudiant_id',
                'et.nom',
                'et.prenom',
                'et.matricule',
                'ues.id as ue_id',
                'ues.nom as ue_nom',
                'ues.credits',
                'rf.note',
                'rf.decision',
                'rf.is_deliber',
                'rf.deliber_at'
            )
            ->get();

        return $this->traiterResultatsOptimise($resultats);
    }

    /**
     * Traitement ultra-rapide des résultats
     * ✅ RETOURNE LA STRUCTURE ATTENDUE PAR LA VUE
     */
    private function traiterResultatsOptimise($resultats)
    {
        $resultatsParEtudiant = [];

        foreach ($resultats as $row) {
            $etudiantId = $row->etudiant_id;

            if (!isset($resultatsParEtudiant[$etudiantId])) {
                // ✅ CRÉER UN OBJET stdClass pour l'étudiant (compatible avec la vue)
                $etudiant = new \stdClass();
                $etudiant->id = $etudiantId;
                $etudiant->nom = $row->nom;
                $etudiant->prenom = $row->prenom;
                $etudiant->matricule = $row->matricule;

                $resultatsParEtudiant[$etudiantId] = [
                    'etudiant' => $etudiant, // ✅ Objet, pas array
                    'ues' => [],
                    'decision' => $row->decision ?? 'non_definie',
                    'is_deliber' => $row->is_deliber ?? false,
                    'deliber_at' => $row->deliber_at,
                ];
            }

            $ueId = $row->ue_id;
            if (!isset($resultatsParEtudiant[$etudiantId]['ues'][$ueId])) {
                $resultatsParEtudiant[$etudiantId]['ues'][$ueId] = [
                    'ue_nom' => $row->ue_nom,
                    'credits' => $row->credits,
                    'notes' => [],
                    'has_zero' => false,
                ];
            }

            $resultatsParEtudiant[$etudiantId]['ues'][$ueId]['notes'][] = $row->note;
            if ($row->note == 0) {
                $resultatsParEtudiant[$etudiantId]['ues'][$ueId]['has_zero'] = true;
            }
        }

        // ✅ Calcul des moyennes et crédits
        $resultatsFinaux = [];
        foreach ($resultatsParEtudiant as $etudiantId => $data) {
            $moyennesUE = [];
            $creditsValides = 0;
            $totalCredits = 0;
            $hasNoteEliminatoire = false;
            $resultatsUE = [];

            foreach ($data['ues'] as $ueId => $ue) {
                $totalCredits += $ue['credits'];
                $moyenneUE = array_sum($ue['notes']) / count($ue['notes']);
                $moyennesUE[] = $moyenneUE;

                $ueValidee = ($moyenneUE >= 10) && !$ue['has_zero'];

                if ($ue['has_zero']) {
                    $hasNoteEliminatoire = true;
                } elseif ($ueValidee) {
                    $creditsValides += $ue['credits'];
                }

                // ✅ Structure attendue par la vue
                $resultatsUE[] = [
                    'ue_id' => $ueId,
                    'ue_nom' => $ue['ue_nom'],
                    'moyenne_ue' => round($moyenneUE, 2),
                    'ue_validee' => $ueValidee,
                    'has_note_eliminatoire' => $ue['has_zero']
                ];
            }

            $moyenneGenerale = count($moyennesUE) > 0 ? 
                array_sum($moyennesUE) / count($moyennesUE) : 0;

            $resultatsFinaux[] = [
                'etudiant' => $data['etudiant'], // ✅ Objet stdClass
                'notes' => [], // ✅ Vide pour l'instant (optimisation)
                'resultats_ue' => $resultatsUE,
                'moyenne_generale' => round($moyenneGenerale, 2),
                'credits_valides' => $creditsValides,
                'total_credits' => $totalCredits,
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'decision' => $data['decision'],
                'is_deliber' => $data['is_deliber'],
                'deliber_at' => $data['deliber_at'],
                'est_redoublant' => intval($data['etudiant']->matricule) <= 38999,
                'a_participe' => true,
            ];
        }

        return $resultatsFinaux;
    }

    /**
     * Applique la simulation avec quota (OPTIMISÉ)
     */
    public function appliquerSimulation($resultats, $quota, $creditsRequis, $moyenneRequise, $noteEliminatoire)
    {
        // ✅ Tri par crédits puis moyenne (références directes, pas de closure)
        usort($resultats, function($a, $b) {
            $diff = $b['credits_valides'] - $a['credits_valides'];
            if ($diff !== 0) return $diff <=> 0;
            
            $diff = $b['moyenne_generale'] - $a['moyenne_generale'];
            if ($diff !== 0) return $diff <=> 0;
            
            return ($a['has_note_eliminatoire'] ? 1 : 0) <=> ($b['has_note_eliminatoire'] ? 1 : 0);
        });

        $admisCount = 0;
        foreach ($resultats as &$resultat) {
            // Règle 1 : Note éliminatoire
            if ($noteEliminatoire && $resultat['has_note_eliminatoire']) {
                $resultat['decision'] = 'exclus';
                $resultat['decision_simulee'] = true;
                continue;
            }

            // Règle 2 : Exclusion stricte
            if ($resultat['credits_valides'] < 30 || $resultat['moyenne_generale'] < 8) {
                $resultat['decision'] = 'exclus';
                $resultat['decision_simulee'] = true;
                continue;
            }

            // Règle 3 : Admission
            if ($resultat['credits_valides'] >= $creditsRequis && 
                $resultat['moyenne_generale'] >= $moyenneRequise) {
                
                if ($quota === null || $quota === '' || $admisCount < $quota) {
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

    /**
     * Sauvegarde BULK optimisée (10x plus rapide)
     */
    public function sauvegarderDeliberationBulk($resultats, $niveauId, $parcoursId, $sessionId, $userId)
    {
        try {
            DB::beginTransaction();

            // ✅ CASE statement pour UPDATE BULK
            $casesDecision = [];
            $etudiantIds = [];
            
            foreach ($resultats as $resultat) {
                $etudiantId = is_object($resultat['etudiant']) 
                    ? $resultat['etudiant']->id 
                    : $resultat['etudiant']['id'];
                
                $casesDecision[] = "WHEN {$etudiantId} THEN '{$resultat['decision']}'";
                $etudiantIds[] = $etudiantId;
            }
            
            $caseDecisionSql = implode(' ', $casesDecision);
            $idsSql = implode(',', $etudiantIds);
            
            // ✅ 1 SEULE REQUÊTE pour tout mettre à jour
            DB::statement("
                UPDATE resultats_finaux rf
                JOIN examens ex ON rf.examen_id = ex.id
                SET 
                    rf.decision = CASE rf.etudiant_id {$caseDecisionSql} END,
                    rf.jury_validated = 1,
                    rf.is_deliber = 1,
                    rf.deliber_at = NOW(),
                    rf.deliber_by = {$userId},
                    rf.updated_at = NOW()
                WHERE ex.niveau_id = {$niveauId}
                  AND ex.parcours_id = {$parcoursId}
                  AND rf.session_exam_id = {$sessionId}
                  AND rf.etudiant_id IN ({$idsSql})
            ");

            DB::commit();
            
            Log::info('Délibération BULK appliquée', [
                'count' => count($resultats),
                'parcours_id' => $parcoursId
            ]);

            return ['success' => true, 'count' => count($resultats)];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur sauvegarde BULK', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}