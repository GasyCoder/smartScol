<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResultatFinalHistorique extends Model
{
    use HasFactory;

    protected $table = 'resultats_finaux_historique';

    // Types d'actions possibles
    const TYPE_CREATION = 'creation';
    const TYPE_CHANGEMENT_STATUT = 'changement_statut';
    const TYPE_ANNULATION = 'annulation';
    const TYPE_REACTIVATION = 'reactivation';
    const TYPE_MODIFICATION = 'modification';

    protected $fillable = [
        'resultat_final_id',
        'type_action',
        'statut_precedent',
        'statut_nouveau',
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
     * Libellés des types d'actions
     */
    public static function getLibellesTypesActions()
    {
        return [
            self::TYPE_CREATION => 'Création',
            self::TYPE_CHANGEMENT_STATUT => 'Changement de statut',
            self::TYPE_ANNULATION => 'Annulation',
            self::TYPE_REACTIVATION => 'Réactivation',
            self::TYPE_MODIFICATION => 'Modification',
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
}
