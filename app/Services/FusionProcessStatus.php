<?php

namespace App\Services;

/**
 * Classe pour gérer les statuts du processus de fusion
 */
class FusionProcessStatus
{
    // Définition des statuts du processus de fusion
    const INITIAL = 'initial';             // État initial
    const COHERENCE_VERIFIEE = 'coherence'; // Cohérence vérifiée
    const FUSION_PROVISOIRE = 'fusion';    // Résultats provisoires créés
    const VALIDATION = 'validation';       // Résultats validés
    const PUBLICATION = 'publie';          // Résultats publiés
    const DELIBERATION = 'deliberation';   // Après délibération (pour sessions de rattrapage)
    const ANNULATION = 'annule';           // Résultats annulés

    /**
     * Renvoie les transitions autorisées entre les statuts
     *
     * @return array
     */
    public static function getTransitionsAutorisees()
    {
        return [
            self::INITIAL => [self::COHERENCE_VERIFIEE],
            self::COHERENCE_VERIFIEE => [self::FUSION_PROVISOIRE, self::INITIAL],
            self::FUSION_PROVISOIRE => [self::VALIDATION, self::COHERENCE_VERIFIEE, self::ANNULATION],
            self::VALIDATION => [self::PUBLICATION, self::FUSION_PROVISOIRE, self::ANNULATION],
            self::PUBLICATION => [self::DELIBERATION, self::ANNULATION],
            self::DELIBERATION => [self::ANNULATION],
            self::ANNULATION => [self::INITIAL]
        ];
    }

    /**
     * Vérifie si une transition est autorisée
     *
     * @param string $statutActuel
     * @param string $nouveauStatut
     * @return bool
     */
    public static function transitionAutorisee($statutActuel, $nouveauStatut)
    {
        $transitions = self::getTransitionsAutorisees();
        return in_array($nouveauStatut, $transitions[$statutActuel] ?? []);
    }

    /**
     * Renvoie le pourcentage de progression pour l'interface utilisateur
     *
     * @param string $statut
     * @return int
     */
    public static function getProgressPercentage($statut)
    {
        switch ($statut) {
            case self::INITIAL:
                return 0;
            case self::COHERENCE_VERIFIEE:
                return 25;
            case self::FUSION_PROVISOIRE:
                return 50;
            case self::VALIDATION:
                return 75;
            case self::PUBLICATION:
            case self::DELIBERATION:
                return 100;
            case self::ANNULATION:
                return 0;
            default:
                return 0;
        }
    }
}
