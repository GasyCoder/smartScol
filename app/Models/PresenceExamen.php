<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresenceExamen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'presences_examens';

    protected $fillable = [
        'examen_id',
        'session_exam_id',
        'salle_id',
        'ec_id',
        'etudiants_presents',
        'etudiants_absents',
        'total_attendu',
        'observations',
        'saisie_par',
        'date_saisie',
    ];

    protected $casts = [
        'date_saisie' => 'datetime',
        'etudiants_presents' => 'integer',
        'etudiants_absents' => 'array',
        'total_attendu' => 'integer',
    ];

    /**
     * Boot method - Auto-remplissage du session_exam_id comme Manchette
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($presence) {
            if (empty($presence->session_exam_id)) {
                $presence->session_exam_id = Manchette::getCurrentSessionId();
            }
        });
    }


    /**
     * Récupère les statistiques de présence pour un examen
     */
    public static function getStatistiquesExamen($examenId, $sessionId)
    {
        // Présence globale (ec_id = null)
        $presenceGlobale = self::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->whereNull('ec_id')
            ->first();

        if ($presenceGlobale) {
            return [
                'presents' => $presenceGlobale->etudiants_presents,
                'absents' => is_array($presenceGlobale->etudiants_absents) 
                    ? count($presenceGlobale->etudiants_absents) 
                    : 0,
                'total_attendu' => $presenceGlobale->total_attendu,
                'source' => 'presence_globale'
            ];
        }

        // Fallback : calculer depuis les manchettes
        return self::calculerDepuisManchettes($examenId, $sessionId);
    }

    /**
     * Calcule la présence depuis les manchettes si pas de données PresenceExamen
     */
    private static function calculerDepuisManchettes($examenId, $sessionId)
    {
        // Compter les manchettes présentes (is_absent = false)
        $presents = Manchette::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function($q) {
                $q->where('is_absent', false)
                ->orWhereNull('is_absent');
            })
            ->distinct('etudiant_id')
            ->count('etudiant_id');

        // Compter les manchettes absentes (is_absent = true)
        $absents = Manchette::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function($q) {
                $q->where('is_absent', true);
            })
            ->distinct('etudiant_id')
            ->count('etudiant_id');

        // Total théorique
        $examen = Examen::find($examenId);
        $totalAttendu = 0;
        if ($examen) {
            $totalAttendu = Etudiant::where('niveau_id', $examen->niveau_id)
                ->where('parcours_id', $examen->parcours_id)
                ->where('is_active', true)
                ->count();
        }

        return [
            'presents' => $presents,
            'absents' => $absents,
            'total_attendu' => $totalAttendu,
            'source' => 'calcule_manchettes'
        ];
    }

    /**
     * Récupère les stats de présence pour une EC spécifique
     */
    public static function getStatistiquesEC($examenId, $sessionId, $ecId)
    {
        // Présence spécifique à l'EC
        $presenceEC = self::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->where('ec_id', $ecId)
            ->first();

        if ($presenceEC) {
            return [
                'presents' => $presenceEC->etudiants_presents,
                'absents' => is_array($presenceEC->etudiants_absents) 
                    ? count($presenceEC->etudiants_absents) 
                    : 0,
                'total_attendu' => $presenceEC->total_attendu,
                'source' => 'presence_ec_specifique'
            ];
        }

        // Fallback : utiliser la présence globale
        return self::getStatistiquesExamen($examenId, $sessionId);
    }


    /**
     * Relations identiques au pattern de Manchette
     */
    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class);
    }

    public function sessionExam(): BelongsTo
    {
        return $this->belongsTo(SessionExam::class);
    }

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class);
    }

    public function ec(): BelongsTo
    {
        return $this->belongsTo(EC::class);
    }

    public function utilisateurSaisie(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saisie_par');
    }

    /**
     * Accesseurs calculés
     */
    public function getTotalEtudiantsAttribute(): int
    {
        return $this->etudiants_presents + $this->etudiants_absents;
    }

    public function getTauxPresenceAttribute(): float
    {
        $total = $this->total_etudiants;
        return $total > 0 ? round(($this->etudiants_presents / $total) * 100, 2) : 0;
    }

    public function getEcartAttenduAttribute(): int
    {
        return $this->total_attendu ? ($this->total_etudiants - $this->total_attendu) : 0;
    }

    /**
     * Attributs de session identiques à Manchette
     */
    public function getSessionLibelleAttribute()
    {
        return $this->sessionExam ? $this->sessionExam->type : 'Inconnue';
    }

    public function getSessionTypeAttribute()
    {
        return $this->sessionExam ? strtolower($this->sessionExam->type) : 'normale';
    }

    public function isSessionNormale()
    {
        return $this->sessionExam && $this->sessionExam->type === 'Normale';
    }

    public function isSessionRattrapage()
    {
        return $this->sessionExam && $this->sessionExam->type === 'Rattrapage';
    }

    /**
     * Scopes identiques au pattern de Manchette
     */
    public function scopeForExamen($query, $examenId, $sessionId, $salleId)
    {
        return $query->where('examen_id', $examenId)
                    ->where('session_exam_id', $sessionId)
                    ->where('salle_id', $salleId);
    }

    public function scopeForEc($query, $ecId)
    {
        return $query->where('ec_id', $ecId);
    }

    public function scopeCurrentSession($query)
    {
        $sessionId = Manchette::getCurrentSessionId();
        return $query->where('session_exam_id', $sessionId);
    }

    public function scopeSessionNormale($query)
    {
        return $query->whereHas('sessionExam', function($q) {
            $q->where('type', 'Normale');
        });
    }

    public function scopeSessionRattrapage($query)
    {
        return $query->whereHas('sessionExam', function($q) {
            $q->where('type', 'Rattrapage');
        });
    }

    /**
     * Méthodes statiques dans le style de Manchette
     */
    public static function forCurrentSession()
    {
        $sessionId = Manchette::getCurrentSessionId();
        if (!$sessionId) {
            return self::whereNull('session_exam_id');
        }

        return self::where('session_exam_id', $sessionId);
    }

    public static function forSession($sessionId)
    {
        return self::where('session_exam_id', $sessionId);
    }

    /**
     * Vérifie si présence existe pour session courante
     */
    public static function existsForCurrentSession($examenId, $salleId, $ecId = null)
    {
        $sessionId = Manchette::getCurrentSessionId();
        if (!$sessionId) {
            return false;
        }

        $query = self::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->where('salle_id', $salleId);

        if ($ecId) {
            $query->where('ec_id', $ecId);
        }

        return $query->exists();
    }

    /**
     * Trouve la présence pour la session courante
     */
    public static function findForCurrentSession($examenId, $salleId, $ecId = null)
    {
        $sessionId = Manchette::getCurrentSessionId();
        if (!$sessionId) {
            return null;
        }

        $query = self::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->where('salle_id', $salleId);

        if ($ecId) {
            $query->where('ec_id', $ecId);
        }

        return $query->first();
    }

    /**
     * Transfert vers une autre session (comme Manchette)
     */
    public function transferToSession($targetSessionId, $userId)
    {
        $newPresence = $this->replicate();
        $newPresence->session_exam_id = $targetSessionId;
        $newPresence->saisie_par = $userId;
        $newPresence->date_saisie = now();
        $newPresence->save();

        return $newPresence;
    }
}