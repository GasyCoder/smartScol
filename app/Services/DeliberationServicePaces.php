<?php
// app/Services/DeliberationServicePaces.php

namespace App\Services;

use App\Models\ResultatFinal;
use Illuminate\Support\Facades\DB;

class DeliberationServicePaces
{
    // Constantes
    private const MATRICULE_ANCIEN_MAX  = 38999;
    private const MATRICULE_NOUVEAU_MIN = 39001;
    
    /**
     * Calcule les décisions de délibération selon les règles PACES
     * 
     * @param array $resultats Résultats consolidés
     * @param array $params Paramètres [quota_admission, credits_requis, moyenne_requise, appliquer_note_eliminatoire]
     * @param int $niveauId
     * @param int $parcoursId
     * @param int $sessionId
     * @return array [resultats avec décisions, compteurs]
     */
    public function calculerDeliberation(
        array $resultats, 
        array $params,
        int $niveauId,
        int $parcoursId,
        int $sessionId
    ): array {
        if (empty($resultats)) {
            return [
                'resultats' => [],
                'compteurs' => ['admis' => 0, 'redoublant' => 0, 'exclus' => 0]
            ];
        }

        // 1️⃣ Tri par mérite (crédits DESC, moyenne DESC, matricule ASC)
        usort($resultats, [$this, 'comparerMerite']);

        // 2️⃣ Extraction des paramètres
        $creditsReq = (int)($params['credits_requis'] ?? 60);
        $seuilAdmission = max(10.0, (float)($params['moyenne_requise'] ?? 10.0));
        $quota = isset($params['quota_admission']) && is_numeric($params['quota_admission']) 
            ? (int)$params['quota_admission'] 
            : null;
        $appliquerElim = (bool)($params['appliquer_note_eliminatoire'] ?? true);

        // 3️⃣ Map des anciens redoublants (une seule requête)
        $etudiantIds = array_map(fn($r) => (int)$r['etudiant']->id, $resultats);
        $anciensMap = $this->getAnciensRedoublantsMap($etudiantIds, $niveauId, $parcoursId, $sessionId);

        // 4️⃣ Relèvement automatique du seuil si quota dépassé
        if (!is_null($quota)) {
            $eligibles = $this->compterEligibles($resultats, $creditsReq, $seuilAdmission, $appliquerElim);
            if ($eligibles > $quota) {
                $seuilAdmission = 14.0; // Relève à 14/20
            }
        }

        // Seuil redoublement : 9.5 si admission à 10, sinon 10 si admission à 14
        $seuilRedoublement = ($seuilAdmission >= 14.0) ? 10.0 : 9.5;

        // 5️⃣ Attribution des décisions + comptage
        $admisCount = 0;
        $redoublantCount = 0;
        $exclusCount = 0;

        foreach ($resultats as &$r) {
            $etudiant = $r['etudiant'];
            if (empty($etudiant) || !isset($etudiant->id)) {
                $r['decision'] = 'exclus';
                $exclusCount++;
                continue;
            }

            $etudiantId = (int)$etudiant->id;
            $matricule = (int)$etudiant->matricule;
            $moyenne = (float)($r['moyenne_generale'] ?? 0.0);
            $credits = (int)($r['credits_valides'] ?? 0);
            $hasElim = (bool)($r['has_note_eliminatoire'] ?? false);
            $creditsPleins = ($credits >= $creditsReq);

            // ❌ Note éliminatoire = EXCLUS immédiat
            if ($appliquerElim && $hasElim) {
                $r['decision'] = 'exclus';
                $exclusCount++;
                continue;
            }

            // ✅ ADMISSION : crédits pleins + moyenne OK + quota respecté
            if ($creditsPleins && $moyenne >= $seuilAdmission) {
                if (is_null($quota) || $admisCount < $quota) {
                    $r['decision'] = 'admis';
                    $admisCount++;
                    continue;
                }
            }

            // 🔍 Vérifier si ANCIEN (matricule ≤ 38999 OU déjà redoublé avant)
            $estAncien = $this->isAncien($matricule, $etudiantId, $anciensMap);

            // 🚫 ANCIEN non admis = EXCLUS (pas de 2e chance)
            if ($estAncien) {
                $r['decision'] = 'exclus';
                $exclusCount++;
                continue;
            }

            // 🟡 REDOUBLEMENT (uniquement pour NOUVEAUX)
            // Conditions : crédits non pleins + moyenne ≥ seuil redoublement + pas de note 0
            if (!$creditsPleins && !$hasElim && $moyenne >= $seuilRedoublement) {
                $r['decision'] = 'redoublant';
                $redoublantCount++;
                continue;
            }

            // ❌ EXCLUSION par défaut
            $r['decision'] = 'exclus';
            $exclusCount++;
        }
        unset($r);

        return [
            'resultats' => $resultats,
            'compteurs' => [
                'admis' => $admisCount,
                'redoublant' => $redoublantCount,
                'exclus' => $exclusCount
            ]
        ];
    }

    /**
     * Tri par mérite : Crédits DESC → Moyenne DESC → Matricule ASC
     */
    private function comparerMerite($a, $b): int
    {
        $creditsA = (int)($a['credits_valides'] ?? 0);
        $creditsB = (int)($b['credits_valides'] ?? 0);
        if ($creditsB !== $creditsA) return $creditsB <=> $creditsA;

        $moyA = (float)($a['moyenne_generale'] ?? 0.0);
        $moyB = (float)($b['moyenne_generale'] ?? 0.0);
        if ($moyB !== $moyA) return $moyB <=> $moyA;

        return (int)$a['etudiant']->matricule <=> (int)$b['etudiant']->matricule;
    }

    /**
     * Compte les éligibles à l'admission avec seuil donné
     */
    private function compterEligibles(array $resultats, int $credits, float $seuil, bool $elimActive): int
    {
        $count = 0;
        foreach ($resultats as $r) {
            if ($elimActive && !empty($r['has_note_eliminatoire'])) continue;
            if (($r['credits_valides'] ?? 0) >= $credits && ($r['moyenne_generale'] ?? 0.0) >= $seuil) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Ancien = matricule ≤ 38999 OU déjà redoublé une année antérieure
     */
    private function isAncien(int $matricule, int $etudiantId, array $anciensMap): bool
    {
        return ($matricule <= self::MATRICULE_ANCIEN_MAX) || isset($anciensMap[$etudiantId]);
    }

    /**
     * Map des étudiants ayant été redoublants sur une année précédente
     * Retourne [etudiant_id => true] pour accès O(1)
     */
    private function getAnciensRedoublantsMap(
        array $etudiantIds, 
        int $niveauId, 
        int $parcoursId, 
        int $sessionActiveId
    ): array {
        if (empty($etudiantIds)) return [];

        try {
            $anciens = DB::table('resultats_finaux as rf')
                ->join('session_exams as se', 'rf.session_exam_id', '=', 'se.id')
                ->join('examens as e', 'rf.examen_id', '=', 'e.id')
                ->whereIn('rf.etudiant_id', $etudiantIds)
                ->where('rf.decision', ResultatFinal::DECISION_REDOUBLANT)
                ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('e.niveau_id', $niveauId)
                ->where('e.parcours_id', $parcoursId)
                ->where('se.id', '!=', $sessionActiveId) // Années antérieures uniquement
                ->distinct()
                ->pluck('rf.etudiant_id')
                ->toArray();

            return array_fill_keys(array_map('intval', $anciens), true);
        } catch (\Throwable $e) {
            \Log::warning('getAnciensRedoublantsMap error', ['error' => $e->getMessage()]);
            return [];
        }
    }
}