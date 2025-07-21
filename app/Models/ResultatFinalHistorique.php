<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResultatFinalHistorique extends Model
{
    use HasFactory;

    protected $table = 'resultat_final_historiques';

    // ✅ MISE À JOUR : Ajouter les nouveaux types d'actions
    const TYPE_CREATION = 'creation';
    const TYPE_CHANGEMENT_STATUT = 'changement_statut';
    const TYPE_ANNULATION = 'annulation';
    const TYPE_REACTIVATION = 'reactivation';
    const TYPE_MODIFICATION = 'modification';

    // ✅ NOUVEAUX TYPES POUR DÉLIBÉRATION
    const TYPE_DELIBERATION = 'deliberation';
    const TYPE_SIMULATION_APPLIQUEE = 'simulation_appliquee';
    const TYPE_DECISION_DELIBERATION = 'decision_deliberation';
    const TYPE_DECISION_APPLIQUEE = 'decision_appliquee';
    const TYPE_ANNULATION_DELIBERATION = 'annulation_deliberation';

    protected $fillable = [
        'resultat_final_id',
        'type_action',
        'statut_precedent',
        'statut_nouveau',
        'decision_precedente',     // ✅ NOUVEAU
        'decision_nouvelle',       // ✅ NOUVEAU
        'user_id',
        'motif',
        'donnees_supplementaires',
        'date_action',
    ];

    protected $casts = [
        'donnees_supplementaires' => 'array',
        'date_action' => 'datetime',
    ];

    /**
     * Relations
     */
    public function resultatFinal()
    {
        return $this->belongsTo(ResultatFinal::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ✅ MISE À JOUR : Libellés des types d'actions
     */
    public static function getLibellesTypesActions()
    {
        return [
            self::TYPE_CREATION => 'Création',
            self::TYPE_CHANGEMENT_STATUT => 'Changement de statut',
            self::TYPE_ANNULATION => 'Annulation',
            self::TYPE_REACTIVATION => 'Réactivation',
            self::TYPE_MODIFICATION => 'Modification',
            self::TYPE_DELIBERATION => 'Délibération appliquée',
            self::TYPE_SIMULATION_APPLIQUEE => 'Simulation appliquée',
            self::TYPE_DECISION_DELIBERATION => 'Décision de délibération',
            self::TYPE_DECISION_APPLIQUEE => 'Décision appliquée',
            self::TYPE_ANNULATION_DELIBERATION => 'Annulation de délibération',
        ];
    }

    /**
     * Obtenir le libellé du type d'action
     */
    public function getLibelleTypeActionAttribute()
    {
        $libelles = self::getLibellesTypesActions();
        return $libelles[$this->type_action] ?? 'Action inconnue';
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Créer une entrée d'historique pour délibération
     */
    public static function creerEntreeDeliberation($resultatFinalId, $decisionPrecedente, $decisionNouvelle, $userId, $configId = null)
    {
        return self::create([
            'resultat_final_id' => $resultatFinalId,
            'type_action' => self::TYPE_DELIBERATION,
            'decision_precedente' => $decisionPrecedente,
            'decision_nouvelle' => $decisionNouvelle,
            'user_id' => $userId,
            'motif' => 'Délibération appliquée avec configuration personnalisée',
            'donnees_supplementaires' => [
                'config_deliberation_id' => $configId,
                'jury_validated' => true,
                'source' => 'deliberation_avec_configuration'
            ],
            'date_action' => now(),
        ]);
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Créer une entrée d'historique pour simulation appliquée
     */
    public static function creerEntreeSimulationAppliquee($resultatFinalId, $decisionPrecedente, $decisionNouvelle, $userId, $parametres = [])
    {
        return self::create([
            'resultat_final_id' => $resultatFinalId,
            'type_action' => self::TYPE_SIMULATION_APPLIQUEE,
            'decision_precedente' => $decisionPrecedente,
            'decision_nouvelle' => $decisionNouvelle,
            'user_id' => $userId,
            'motif' => 'Application de simulation avec paramètres personnalisés',
            'donnees_supplementaires' => [
                'parametres_simulation' => $parametres,
                'source' => 'simulation_parametree'
            ],
            'date_action' => now(),
        ]);
    }

    /**
     * Créer une entrée d'historique pour la création d'un résultat
     */
    public static function creerEntreeCreation($resultatFinalId, $userId)
    {
        return self::create([
            'resultat_final_id' => $resultatFinalId,
            'type_action' => self::TYPE_CREATION,
            'statut_nouveau' => ResultatFinal::STATUT_EN_ATTENTE,
            'user_id' => $userId,
            'date_action' => now(),
        ]);
    }

    /**
     * Créer une entrée d'historique pour un changement de statut
     */
    public static function creerEntreeChangementStatut($resultatFinalId, $statutPrecedent, $nouveauStatut, $userId, $donnees = [])
    {
        return self::create([
            'resultat_final_id' => $resultatFinalId,
            'type_action' => self::TYPE_CHANGEMENT_STATUT,
            'statut_precedent' => $statutPrecedent,
            'statut_nouveau' => $nouveauStatut,
            'user_id' => $userId,
            'donnees_supplementaires' => $donnees,
            'date_action' => now(),
        ]);
    }

    /**
     * Créer une entrée d'historique pour une annulation
     */
    public static function creerEntreeAnnulation($resultatFinalId, $userId, $motif = null)
    {
        return self::create([
            'resultat_final_id' => $resultatFinalId,
            'type_action' => self::TYPE_ANNULATION,
            'statut_precedent' => ResultatFinal::STATUT_PUBLIE,
            'statut_nouveau' => ResultatFinal::STATUT_ANNULE,
            'user_id' => $userId,
            'motif' => $motif,
            'date_action' => now(),
        ]);
    }

    /**
     * Créer une entrée d'historique pour une réactivation
     */
    public static function creerEntreeReactivation($resultatFinalId, $userId)
    {
        return self::create([
            'resultat_final_id' => $resultatFinalId,
            'type_action' => self::TYPE_REACTIVATION,
            'statut_precedent' => ResultatFinal::STATUT_ANNULE,
            'statut_nouveau' => ResultatFinal::STATUT_EN_ATTENTE,
            'user_id' => $userId,
            'date_action' => now(),
        ]);
    }

    /**
     * Créer une entrée d'historique pour une modification
     */
    public static function creerEntreeModification($resultatFinalId, $userId, $donnees = [])
    {
        return self::create([
            'resultat_final_id' => $resultatFinalId,
            'type_action' => self::TYPE_MODIFICATION,
            'user_id' => $userId,
            'donnees_supplementaires' => $donnees,
            'date_action' => now(),
        ]);
    }

    /**
     * Scopes pour les requêtes
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type_action', $type);
    }

    public function scopeParUtilisateur($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeParPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_action', [$dateDebut, $dateFin]);
    }

    public function scopeRecent($query, $jours = 30)
    {
        return $query->where('date_action', '>=', now()->subDays($jours));
    }

    public function scopeOrdreChronologique($query)
    {
        return $query->orderBy('date_action', 'asc');
    }

    public function scopeOrdreAntichronologique($query)
    {
        return $query->orderBy('date_action', 'desc');
    }

    /**
     * ✅ NOUVEAUX SCOPES POUR DÉLIBÉRATION
     */
    public function scopeDeliberations($query)
    {
        return $query->whereIn('type_action', [
            self::TYPE_DELIBERATION,
            self::TYPE_SIMULATION_APPLIQUEE,
            self::TYPE_DECISION_DELIBERATION,
            self::TYPE_ANNULATION_DELIBERATION
        ]);
    }

    public function scopeChangementsDecision($query)
    {
        return $query->whereNotNull('decision_precedente')
                    ->whereNotNull('decision_nouvelle');
    }
}
