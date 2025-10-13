<?php
// app/Services/DeliberationServicePaces.php

namespace App\Services;

use App\Models\ResultatFinal;
use Illuminate\Support\Facades\DB;

class DeliberationServicePaces
{
    // Seuils matricule
    private const MATRICULE_ANCIEN_MAX  = 38999;
    private const MATRICULE_NOUVEAU_MIN = 39001;

    /**
     * Normalise un quota :
     * - null, '', 0, <0  => illimité (retourne null)
     * - >0                => valeur conservée
     */
    private function normalizeQuota(mixed $q): ?int
    {
        if ($q === null || $q === '') return null;
        if (!is_numeric($q)) return null;
        $qi = (int)$q;
        return $qi > 0 ? $qi : null;
    }

    /**
     * Calcule les décisions (admis/redoublant/exclus) sans relèvement arbitraire du seuil.
     * - Admission : top-N par mérite parmi les éligibles (crédits OK, moyenne ≥ seuil, pas de note 0 si activé)
     * - Redoublement : nouveaux uniquement, si moyenne & crédits min OK, respect quota redoublement
     * - Anciens non admis : exclus
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
                'compteurs' => ['admis' => 0, 'redoublant' => 0, 'exclus' => 0],
            ];
        }

        // 1) Tri global par mérite pour une base cohérente
        usort($resultats, [$this, 'comparerMerite']);

        // 2) Paramètres
        $creditsReq              = (int)($params['credits_requis'] ?? 60);
        $seuilAdmission          = max(0.0, (float)($params['moyenne_requise'] ?? 10.0)); // ex: 10.0
        $quota                   = $this->normalizeQuota($params['quota_admission'] ?? null);
        $appliquerElim           = (bool)($params['appliquer_note_eliminatoire'] ?? true);

        $quotaRedoublant         = $this->normalizeQuota($params['quota_redoublant'] ?? null);
        $moyenneMinRedoublement  = (float)($params['moyenne_min_redoublement'] ?? 9.5);
        $creditsMinRedoublement  = (int)($params['credits_min_redoublement'] ?? 50);

        // 3) Map des anciens redoublants pour O(1)
        $etudiantIds = array_map(fn($r) => (int)$r['etudiant']->id, $resultats);
        $anciensMap  = $this->getAnciensRedoublantsMap($etudiantIds, $niveauId, $parcoursId, $sessionId);

        // ============================
        // 4) Construire la short-list des éligibles à l’ADMISSION (SEUIL BASE)
        //    - PAS de relèvement arbitraire à 14
        // ============================
        $eligibles = [];
        foreach ($resultats as $idx => $r) {
            $hasElim = (bool)($r['has_note_eliminatoire'] ?? false);
            $moy     = (float)($r['moyenne_generale'] ?? 0.0);
            $cred    = (int)  ($r['credits_valides'] ?? 0);

            if ($appliquerElim && $hasElim) continue; // note éliminatoire => non admissible
            if ($cred < $creditsReq) continue;        // crédits insuffisants
            if ($moy  < $seuilAdmission) continue;    // sous le seuil min (ex: 10)

            $eligibles[] = $r;
        }

        // Trier la short-list par mérite et couper à N si quota
        usort($eligibles, [$this, 'comparerMerite']);

        $admisIds = [];
        if (!is_null($quota)) {
            $eligibles = array_slice($eligibles, 0, max(0, (int)$quota));
        }
        foreach ($eligibles as $r) {
            if (!empty($r['etudiant']?->id)) {
                $admisIds[(int)$r['etudiant']->id] = true;
            }
        }

        // ============================
        // 5) Parcours de décision
        // ============================
        $admisCount       = 0;
        $redoublantCount  = 0;
        $exclusCount      = 0;

        foreach ($resultats as &$r) {
            $etudiant = $r['etudiant'] ?? null;
            if (!$etudiant?->id) {
                $r['decision'] = 'exclus';
                $r['debug']    = 'etudiant_invalide';
                $exclusCount++;
                continue;
            }

            $id        = (int)$etudiant->id;
            $matricule = (int)$etudiant->matricule;
            $moy       = (float)($r['moyenne_generale'] ?? 0.0);
            $cred      = (int)  ($r['credits_valides'] ?? 0);
            $hasElim   = (bool)($r['has_note_eliminatoire'] ?? false);
            $estAncien = $this->isAncien($matricule, $id, $anciensMap);

            // Éliminatoire => exclus
            if ($appliquerElim && $hasElim) {
                $r['decision'] = 'exclus';
                $r['debug']    = 'note_eliminatoire';
                $exclusCount++;
                continue;
            }

            // ✅ Admis si dans la short-list OU si quota illimité et critères admission OK
            if (isset($admisIds[$id]) || (is_null($quota) && $cred >= $creditsReq && $moy >= $seuilAdmission)) {
                $r['decision'] = 'admis';
                $r['debug']    = 'admis_selection_topN';
                $admisCount++;
                continue;
            }

            // Anciens non admis => exclus
            if ($estAncien) {
                $r['decision'] = 'exclus';
                $r['debug']    = 'ancien_non_admis';
                $exclusCount++;
                continue;
            }

            // 🟡 Redoublement (NOUVEAUX) si moyenne & crédits min OK + quota redoublement
            if ($this->estEligibleRedoublement($r, $moyenneMinRedoublement, $creditsMinRedoublement)) {
                if (is_null($quotaRedoublant) || $redoublantCount < $quotaRedoublant) {
                    $r['decision'] = 'redoublant';
                    $r['debug']    = 'redoublement_ok';
                    $redoublantCount++;
                    continue;
                } else {
                    $r['debug']    = 'redoublement_bloque_par_quota';
                }
            }

            // ❌ Par défaut
            $r['decision'] = 'exclus';
            $r['debug']    = $r['debug'] ?? 'exclus_defaut';
            $exclusCount++;
        }
        unset($r);

        return [
            'resultats' => $resultats,
            'compteurs' => [
                'admis'      => $admisCount,
                'redoublant' => $redoublantCount,
                'exclus'     => $exclusCount,
            ],
        ];
    }

    /**
     * Éligibilité redoublement : moyenne ET crédits (logique ET)
     */
    private function estEligibleRedoublement(array $resultat, float $moyenneMin, int $creditsMin): bool
    {
        $moy = (float)($resultat['moyenne_generale'] ?? 0.0);
        $cr  = (int)  ($resultat['credits_valides'] ?? 0);
        return ($moy >= $moyenneMin) && ($cr >= $creditsMin);
    }

    /**
     * Tri par mérite : Crédits DESC → Moyenne DESC → Matricule ASC
     */
    private function comparerMerite($a, $b): int
    {
        $ca = (int)($a['credits_valides'] ?? 0);
        $cb = (int)($b['credits_valides'] ?? 0);
        if ($cb !== $ca) return $cb <=> $ca;

        $ma = (float)($a['moyenne_generale'] ?? 0.0);
        $mb = (float)($b['moyenne_generale'] ?? 0.0);
        if ($mb !== $ma) return $mb <=> $ma;

        return (int)$a['etudiant']->matricule <=> (int)$b['etudiant']->matricule;
    }

    /**
     * Ancien = matricule ≤ 38999 OU déjà redoublant sur une session antérieure
     */
    private function isAncien(int $matricule, int $etudiantId, array $anciensMap): bool
    {
        return ($matricule <= self::MATRICULE_ANCIEN_MAX) || isset($anciensMap[$etudiantId]);
    }

    /**
     * Map rapide des étudiants ayant déjà la décision REDOUBLANT dans une session antérieure
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
                ->where('se.id', '!=', $sessionActiveId) // sessions antérieures uniquement
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
