<?php

namespace App\Support;

class ReglesDeliberation
{
    // Crédits requis
    const CREDITS_TOTAL_ANNEE = 60;
    const CREDITS_PAR_UE = 5;
    const CREDITS_ADMISSION = 60; // Admission complète (session 1 ou 2)
    const CREDITS_MINIMUM_SESSION_2 = 40; // Seuil pour éviter le redoublement

    // Seuils de notes
    const NOTE_VALIDATION_UE = 10.00;
    const NOTE_ELIMINATOIRE = 0;

    // Décisions possibles selon les sessions (en minuscules pour la base de données)
    const DECISIONS_SESSION_1 = [
        'admis' => 'Admis (60 crédits validés)',
        'rattrapage' => 'Autorisé au rattrapage (<60 crédits ou note éliminatoire)',
        'exclus' => 'Exclu (cas spéciaux)'
    ];

    const DECISIONS_SESSION_2 = [
        'admis' => 'Admis (≥40 crédits validés)',
        'redoublant' => 'Redoublant (<40 crédits ou note éliminatoire)',
        'exclus' => 'Exclu (cas spéciaux)'
    ];

    /**
     * Déterminer la décision pour un étudiant selon ses résultats
     */
    public static function determinerDecision($creditsValides, $hasNoteEliminatoire, $isSessionRattrapage)
    {
        if (!$isSessionRattrapage) {
            // Session 1 (Normale)
            if ($creditsValides >= self::CREDITS_ADMISSION) {
                return 'admis';
            }
            return 'rattrapage';
        } else {
            // Session 2 (Rattrapage)
            if ($creditsValides >= self::CREDITS_ADMISSION) {
                return 'admis';
            } elseif ($creditsValides >= self::CREDITS_MINIMUM_SESSION_2) {
                return 'admis'; // Admission conditionnelle
            } elseif ($hasNoteEliminatoire) {
                return 'redoublant';
            }
            return 'redoublant';
        }
    }
    /**
     * Vérifier si une UE est validée
     */
    public static function isUEValidee($moyenneUE, $hasNoteEliminatoire)
    {
        if ($hasNoteEliminatoire) {
            return false;
        }
        return $moyenneUE >= self::NOTE_VALIDATION_UE;
    }

    /**
     * Obtenir le libellé d'une décision avec explication
     */
    public static function getLibelleDecision($decision, $creditsValides = null)
    {
        $libelles = [
            'admis' => $creditsValides >= self::CREDITS_ADMISSION
                ? "Admis - L'étudiant a validé tous les 60 crédits"
                : "Admis conditionnellement - L'étudiant a validé {$creditsValides}/60 crédits",
            'rattrapage' => "Autorisé au rattrapage - L'étudiant doit passer en session de rattrapage ({$creditsValides}/60 crédits)",
            'redoublant' => "Redoublant - L'étudiant doit redoubler ({$creditsValides}/60 crédits)",
            'exclus' => "Exclu - Cas exceptionnel nécessitant une décision administrative"
        ];

        return $libelles[$decision] ?? $decision;
    }

    /**
     * Obtenir les crédits nécessaires pour une décision
     */
    public static function getCreditsRequis($decision, $isSessionRattrapage)
    {
        if ($isSessionRattrapage) {
            switch ($decision) {
                case 'admis':
                    return self::CREDITS_MINIMUM_SESSION_2;
                case 'redoublant':
                    return 0;
                default:
                    return self::CREDITS_ADMISSION;
            }
        } else {
            switch ($decision) {
                case 'admis':
                    return self::CREDITS_ADMISSION;
                case 'rattrapage':
                    return 0;
                default:
                    return self::CREDITS_ADMISSION;
            }
        }
    }
}
