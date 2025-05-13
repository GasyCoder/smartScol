<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    use HasFactory;

    protected $fillable = [
        'deliberation_id',
        'etudiant_id',
        'moyenne',
        'decision',
        'points_jury',
        'observations'
    ];

    protected $casts = [
        'moyenne' => 'decimal:2',
        'points_jury' => 'decimal:2'
    ];

    /**
     * Décisions possibles
     */
    const DECISION_ADMIS = 'admis';
    const DECISION_AJOURNE = 'ajourne';
    const DECISION_ADMIS_CONDITIONNELLEMENT = 'admis_conditionnellement';
    const DECISION_REDOUBLE = 'redouble';

    /**
     * Relations
     */
    public function deliberation()
    {
        return $this->belongsTo(Deliberation::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    /**
     * Calcule la moyenne des résultats de l'étudiant pour cette délibération
     */
    public static function calculerMoyenne(Etudiant $etudiant, Deliberation $deliberation)
    {
        // Récupérer les examens concernés
        $resultats = Resultat::where('etudiant_id', $etudiant->id)
            ->whereHas('examen', function ($query) use ($deliberation) {
                $query->where('niveau_id', $deliberation->niveau_id)
                    ->where('session_id', $deliberation->session_id);
            })
            ->with('examen.ec') // Chargement des relations pour les coefficients
            ->get();

        if ($resultats->isEmpty()) {
            return 0;
        }

        $somme_ponderee = 0;
        $somme_coefficients = 0;

        foreach ($resultats as $resultat) {
            $coefficient = $resultat->examen->ec->coefficient;
            $somme_ponderee += $resultat->note * $coefficient;
            $somme_coefficients += $coefficient;
        }

        return $somme_coefficients > 0 ? $somme_ponderee / $somme_coefficients : 0;
    }

    /**
     * Détermine automatiquement la décision en fonction de la moyenne
     */
    public static function determinerDecision($moyenne, $session_type)
    {
        // Pour une session normale
        if ($session_type === 'normale') {
            if ($moyenne >= 10) {
                return self::DECISION_ADMIS;
            } else if ($moyenne >= 8) {
                return self::DECISION_ADMIS_CONDITIONNELLEMENT;
            } else {
                return self::DECISION_AJOURNE;
            }
        }
        // Pour une session de rattrapage
        else if ($session_type === 'rattrapage') {
            if ($moyenne >= 10) {
                return self::DECISION_ADMIS;
            } else {
                return self::DECISION_REDOUBLE;
            }
        }

        return self::DECISION_AJOURNE; // Par défaut
    }
}
