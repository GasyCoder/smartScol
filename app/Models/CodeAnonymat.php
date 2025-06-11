<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodeAnonymat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'codes_anonymat';

    protected $fillable = [
        'examen_id',
        'ec_id',
        'code_complet',
        'sequence'
    ];

    protected $casts = [
        'sequence' => 'integer',
    ];

    /**
     * Avant la sauvegarde, extrait la séquence si non définie
     */
    protected static function booted()
    {
        static::creating(function ($codeAnonymat) {
            if (empty($codeAnonymat->sequence)) {
                $codeAnonymat->sequence = $codeAnonymat->getSequenceFromCode();
            }
        });
    }

    // Relations de base
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function ec()
    {
        return $this->belongsTo(EC::class, 'ec_id');
    }

    public function manchettes()
    {
        return $this->hasMany(Manchette::class, 'code_anonymat_id');
    }

    public function copies()
    {
        return $this->hasMany(Copie::class, 'code_anonymat_id');
    }

    /**
     * MODIFIÉ : Relation avec une manchette (la plus récente par défaut)
     */
    public function manchette()
    {
        return $this->hasOne(Manchette::class, 'code_anonymat_id')->latest();
    }

    /**
     * MODIFIÉ : Relation avec une copie (la plus récente par défaut)
     */
    public function copie()
    {
        return $this->hasOne(Copie::class, 'code_anonymat_id')->latest();
    }

    /**
     * NOUVEAU : Relation avec les manchettes pour la session active
     */
    public function manchetteCurrentSession()
    {
        $sessionId = Manchette::getCurrentSessionId();
        return $this->hasOne(Manchette::class, 'code_anonymat_id')
                   ->where('session_exam_id', $sessionId);
    }

    /**
     * NOUVEAU : Relation avec les copies pour la session active
     */
    public function copieCurrentSession()
    {
        $sessionId = Manchette::getCurrentSessionId();
        return $this->hasOne(Copie::class, 'code_anonymat_id')
                   ->where('session_exam_id', $sessionId);
    }

    /**
     * NOUVEAU : Relation avec les manchettes pour une session spécifique
     */
    public function manchetteForSession($sessionId)
    {
        return $this->hasOne(Manchette::class, 'code_anonymat_id')
                   ->where('session_exam_id', $sessionId);
    }

    /**
     * NOUVEAU : Relation avec les copies pour une session spécifique
     */
    public function copieForSession($sessionId)
    {
        return $this->hasOne(Copie::class, 'code_anonymat_id')
                   ->where('session_exam_id', $sessionId);
    }

    /**
     * MODIFIÉ : Récupère l'étudiant via la manchette de la session active
     */
    public function getEtudiantAttribute()
    {
        $manchette = $this->manchetteCurrentSession;
        return $manchette ? $manchette->etudiant : null;
    }

    /**
     * NOUVEAU : Récupère l'étudiant pour une session spécifique
     */
    public function getEtudiantForSession($sessionId)
    {
        $manchette = $this->manchetteForSession($sessionId);
        return $manchette ? $manchette->etudiant : null;
    }

    /**
     * Extracte la séquence numérique du code complet (ex: 'TA1' => 1)
     */
    public function getSequenceFromCode()
    {
        if (preg_match('/(\d+)$/', $this->code_complet, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Extrait le code salle (lettres) du code complet
     */
    public function getCodeSalleAttribute()
    {
        if (preg_match('/^([A-Za-z]+)/', $this->code_complet, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extrait le numéro (chiffres) du code complet
     */
    public function getNumeroAttribute()
    {
        if (preg_match('/(\d+)$/', $this->code_complet, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Relation avec la salle basée sur le code salle
     */
    public function getSalleAttribute()
    {
        $codeSalle = $this->getCodeSalleAttribute();
        if ($codeSalle) {
            return Salle::where('code_base', $codeSalle)->first();
        }
        return null;
    }

    /**
     * NOUVEAU : Vérifie si ce code d'anonymat est utilisé dans la session active
     */
    public function isUsedInCurrentSession()
    {
        $sessionId = Manchette::getCurrentSessionId();
        if (!$sessionId) {
            return false;
        }

        return $this->manchettes()
                   ->where('session_exam_id', $sessionId)
                   ->exists();
    }

    /**
     * NOUVEAU : Vérifie si ce code d'anonymat est utilisé dans une session spécifique
     */
    public function isUsedInSession($sessionId)
    {
        return $this->manchettes()
                   ->where('session_exam_id', $sessionId)
                   ->exists();
    }

    /**
     * NOUVEAU : Vérifie si ce code d'anonymat a une copie associée dans la session active
     */
    public function hasCopieInCurrentSession()
    {
        $sessionId = Manchette::getCurrentSessionId();
        if (!$sessionId) {
            return false;
        }

        return $this->copies()
                   ->where('session_exam_id', $sessionId)
                   ->exists();
    }

    /**
     * NOUVEAU : Vérifie si ce code d'anonymat a une copie associée dans une session spécifique
     */
    public function hasCopieInSession($sessionId)
    {
        return $this->copies()
                   ->where('session_exam_id', $sessionId)
                   ->exists();
    }

    /**
     * NOUVEAU : Récupère toutes les sessions où ce code d'anonymat est utilisé
     */
    public function getUsedSessions()
    {
        return SessionExam::whereIn('id',
            $this->manchettes()->pluck('session_exam_id')->unique()
        )->get();
    }

    /**
     * NOUVEAU : Vérifie si ce code d'anonymat est complètement traité (manchette + copie) pour une session
     */
    public function isCompleteForSession($sessionId)
    {
        return $this->isUsedInSession($sessionId) && $this->hasCopieInSession($sessionId);
    }

    /**
     * NOUVEAU : Vérifie si ce code d'anonymat est complètement traité pour la session active
     */
    public function isCompleteForCurrentSession()
    {
        $sessionId = Manchette::getCurrentSessionId();
        return $sessionId ? $this->isCompleteForSession($sessionId) : false;
    }

    /**
     * NOUVEAU : Récupère les statistiques d'utilisation de ce code par session
     */
    public function getUsageStats()
    {
        $manchettes = $this->manchettes()
            ->join('session_exams', 'manchettes.session_exam_id', '=', 'session_exams.id')
            ->selectRaw('session_exams.type, COUNT(*) as count')
            ->groupBy('session_exams.type')
            ->pluck('count', 'type');

        $copies = $this->copies()
            ->join('session_exams', 'copies.session_exam_id', '=', 'session_exams.id')
            ->selectRaw('session_exams.type, COUNT(*) as count')
            ->groupBy('session_exams.type')
            ->pluck('count', 'type');

        return [
            'manchettes' => $manchettes->toArray(),
            'copies' => $copies->toArray(),
        ];
    }

    /**
     * NOUVEAU : Scope pour filtrer par session
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->whereHas('manchettes', function($q) use ($sessionId) {
            $q->where('session_exam_id', $sessionId);
        });
    }

    /**
     * NOUVEAU : Scope pour filtrer par session active
     */
    public function scopeForCurrentSession($query)
    {
        $sessionId = Manchette::getCurrentSessionId();
        return $sessionId ? $query->forSession($sessionId) : $query->whereRaw('1=0');
    }

    /**
     * NOUVEAU : Scope pour les codes avec manchettes seulement
     */
    public function scopeWithManchetteOnly($query, $sessionId = null)
    {
        $sessionId = $sessionId ?? Manchette::getCurrentSessionId();

        return $query->whereHas('manchettes', function($q) use ($sessionId) {
            $q->where('session_exam_id', $sessionId);
        })->whereDoesntHave('copies', function($q) use ($sessionId) {
            $q->where('session_exam_id', $sessionId);
        });
    }

    /**
     * NOUVEAU : Scope pour les codes complets (manchette + copie)
     */
    public function scopeComplete($query, $sessionId = null)
    {
        $sessionId = $sessionId ?? Manchette::getCurrentSessionId();

        return $query->whereHas('manchettes', function($q) use ($sessionId) {
            $q->where('session_exam_id', $sessionId);
        })->whereHas('copies', function($q) use ($sessionId) {
            $q->where('session_exam_id', $sessionId);
        });
    }

    /**
     * NOUVEAU : Scope pour les codes non utilisés dans une session
     */
    public function scopeUnused($query, $sessionId = null)
    {
        $sessionId = $sessionId ?? Manchette::getCurrentSessionId();

        return $query->whereDoesntHave('manchettes', function($q) use ($sessionId) {
            $q->where('session_exam_id', $sessionId);
        });
    }

    /**
     * NOUVEAU : Méthode statique pour obtenir les statistiques d'utilisation globales
     */
    public static function getGlobalUsageStats($examenId, $ecId = null)
    {
        $query = self::where('examen_id', $examenId);

        if ($ecId) {
            $query->where('ec_id', $ecId);
        }

        $total = $query->count();
        $sessionId = Manchette::getCurrentSessionId();

        if (!$sessionId) {
            return [
                'total' => $total,
                'utilises' => 0,
                'avec_copie' => 0,
                'complets' => 0,
                'pourcentage_utilisation' => 0,
                'pourcentage_completion' => 0
            ];
        }

        $utilises = $query->forSession($sessionId)->count();
        $avecCopie = $query->whereHas('copies', function($q) use ($sessionId) {
            $q->where('session_exam_id', $sessionId);
        })->count();
        $complets = $query->complete($sessionId)->count();

        return [
            'total' => $total,
            'utilises' => $utilises,
            'avec_copie' => $avecCopie,
            'complets' => $complets,
            'pourcentage_utilisation' => $total > 0 ? round(($utilises / $total) * 100, 1) : 0,
            'pourcentage_completion' => $utilises > 0 ? round(($complets / $utilises) * 100, 1) : 0
        ];
    }

    /**
     * NOUVEAU : Trouve le code d'anonymat suivant dans la séquence pour la même salle/EC
     */
    public function getNextInSequence()
    {
        return self::where('examen_id', $this->examen_id)
                  ->where('ec_id', $this->ec_id)
                  ->where('sequence', '>', $this->sequence)
                  ->where('code_complet', 'LIKE', $this->code_salle . '%')
                  ->orderBy('sequence')
                  ->first();
    }

    /**
     * NOUVEAU : Trouve le code d'anonymat précédent dans la séquence pour la même salle/EC
     */
    public function getPreviousInSequence()
    {
        return self::where('examen_id', $this->examen_id)
                  ->where('ec_id', $this->ec_id)
                  ->where('sequence', '<', $this->sequence)
                  ->where('code_complet', 'LIKE', $this->code_salle . '%')
                  ->orderBy('sequence', 'desc')
                  ->first();
    }

    /**
     * NOUVEAU : Génère un nouveau code d'anonymat dans la même séquence
     */
    public static function generateNext($examenId, $ecId, $codeSalle)
    {
        $lastSequence = self::where('examen_id', $examenId)
                           ->where('ec_id', $ecId)
                           ->where('code_complet', 'LIKE', $codeSalle . '%')
                           ->max('sequence');

        $nextSequence = ($lastSequence ?? 0) + 1;
        $codeComplet = $codeSalle . $nextSequence;

        return self::create([
            'examen_id' => $examenId,
            'ec_id' => $ecId,
            'code_complet' => $codeComplet,
            'sequence' => $nextSequence
        ]);
    }

    /**
     * NOUVEAU : Valide le format du code complet
     */
    public function validateCodeFormat()
    {
        // Format attendu: lettres suivies de chiffres (ex: TA1, SB23, etc.)
        return preg_match('/^[A-Za-z]+\d+$/', $this->code_complet);
    }

    /**
     * NOUVEAU : Trouve les doublons de codes pour le même examen/EC
     */
    public static function findDuplicates($examenId, $ecId = null)
    {
        $query = self::where('examen_id', $examenId);

        if ($ecId) {
            $query->where('ec_id', $ecId);
        }

        return $query->selectRaw('code_complet, COUNT(*) as count')
                    ->groupBy('code_complet')
                    ->having('count', '>', 1)
                    ->get();
    }
}
