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
        'session_exam_id', // ✅ AJOUTÉ
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

    // ✅ NOUVELLE RELATION : Session d'examen
    public function sessionExam()
    {
        return $this->belongsTo(SessionExam::class, 'session_exam_id');
    }

    // ✅ MODIFIÉ : Relations avec manchettes pour une session spécifique
    public function manchettes()
    {
        return $this->hasMany(Manchette::class, 'code_anonymat_id')
                   ->where('session_exam_id', $this->session_exam_id);
    }

    // ✅ MODIFIÉ : Relations avec copies pour une session spécifique
    public function copies()
    {
        return $this->hasMany(Copie::class, 'code_anonymat_id')
                   ->where('session_exam_id', $this->session_exam_id);
    }

    /**
     * ✅ NOUVELLE : Relation avec toutes les manchettes (toutes sessions)
     */
    public function allManchettes()
    {
        return $this->hasMany(Manchette::class, 'code_anonymat_id');
    }

    /**
     * ✅ NOUVELLE : Relation avec toutes les copies (toutes sessions)
     */
    public function allCopies()
    {
        return $this->hasMany(Copie::class, 'code_anonymat_id');
    }

    /**
     * ✅ MODIFIÉ : Relation avec une manchette pour cette session
     */
    public function manchette()
    {
        return $this->hasOne(Manchette::class, 'code_anonymat_id')
                   ->where('session_exam_id', $this->session_exam_id)
                   ->latest();
    }

    /**
     * ✅ MODIFIÉ : Relation avec une copie pour cette session
     */
    public function copie()
    {
        return $this->hasOne(Copie::class, 'code_anonymat_id')
                   ->where('session_exam_id', $this->session_exam_id)
                   ->latest();
    }

    /**
     * ✅ NOUVELLE : Relation avec les manchettes pour une session spécifique
     */
    public function manchetteForSession($sessionId)
    {
        return $this->hasOne(Manchette::class, 'code_anonymat_id')
                   ->where('session_exam_id', $sessionId);
    }

    /**
     * ✅ NOUVELLE : Relation avec les copies pour une session spécifique
     */
    public function copieForSession($sessionId)
    {
        return $this->hasOne(Copie::class, 'code_anonymat_id')
                   ->where('session_exam_id', $sessionId);
    }

    /**
     * ✅ MODIFIÉ : Récupère l'étudiant via la manchette de cette session
     */
    public function getEtudiantAttribute()
    {
        $manchette = $this->manchette;
        return $manchette ? $manchette->etudiant : null;
    }

    /**
     * ✅ NOUVELLE : Récupère l'étudiant pour une session spécifique
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
     * ✅ MODIFIÉ : Vérifie si ce code d'anonymat est utilisé dans cette session
     */
    public function isUsedInSession($sessionId = null)
    {
        $sessionId = $sessionId ?? $this->session_exam_id;
        if (!$sessionId) {
            return false;
        }

        return $this->allManchettes()
                   ->where('session_exam_id', $sessionId)
                   ->exists();
    }

    /**
     * ✅ MODIFIÉ : Vérifie si ce code d'anonymat a une copie associée dans cette session
     */
    public function hasCopieInSession($sessionId = null)
    {
        $sessionId = $sessionId ?? $this->session_exam_id;
        if (!$sessionId) {
            return false;
        }

        return $this->allCopies()
                   ->where('session_exam_id', $sessionId)
                   ->exists();
    }

    /**
     * ✅ NOUVELLE : Vérifie si ce code d'anonymat est complètement traité (manchette + copie) pour une session
     */
    public function isCompleteForSession($sessionId = null)
    {
        $sessionId = $sessionId ?? $this->session_exam_id;
        return $this->isUsedInSession($sessionId) && $this->hasCopieInSession($sessionId);
    }

    /**
     * ✅ MODIFIÉ : Récupère toutes les sessions où ce code d'anonymat est utilisé
     */
    public function getUsedSessions()
    {
        return SessionExam::whereIn('id',
            $this->allManchettes()->pluck('session_exam_id')->unique()
        )->get();
    }

    /**
     * ✅ MODIFIÉ : Récupère les statistiques d'utilisation de ce code par session
     */
    public function getUsageStats()
    {
        $manchettes = $this->allManchettes()
            ->join('session_exams', 'manchettes.session_exam_id', '=', 'session_exams.id')
            ->selectRaw('session_exams.type, COUNT(*) as count')
            ->groupBy('session_exams.type')
            ->pluck('count', 'type');

        $copies = $this->allCopies()
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
     * ✅ MODIFIÉ : Scope pour filtrer par session
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_exam_id', $sessionId);
    }

    /**
     * ✅ NOUVELLE : Scope pour les codes avec manchettes seulement dans une session
     */
    public function scopeWithManchetteOnly($query, $sessionId)
    {
        return $query->where('session_exam_id', $sessionId)
                    ->whereHas('allManchettes', function($q) use ($sessionId) {
                        $q->where('session_exam_id', $sessionId);
                    })
                    ->whereDoesntHave('allCopies', function($q) use ($sessionId) {
                        $q->where('session_exam_id', $sessionId);
                    });
    }

    /**
     * ✅ NOUVELLE : Scope pour les codes complets (manchette + copie) dans une session
     */
    public function scopeComplete($query, $sessionId)
    {
        return $query->where('session_exam_id', $sessionId)
                    ->whereHas('allManchettes', function($q) use ($sessionId) {
                        $q->where('session_exam_id', $sessionId);
                    })
                    ->whereHas('allCopies', function($q) use ($sessionId) {
                        $q->where('session_exam_id', $sessionId);
                    });
    }

    /**
     * ✅ NOUVELLE : Scope pour les codes non utilisés dans une session
     */
    public function scopeUnused($query, $sessionId)
    {
        return $query->where('session_exam_id', $sessionId)
                    ->whereDoesntHave('allManchettes', function($q) use ($sessionId) {
                        $q->where('session_exam_id', $sessionId);
                    });
    }

    /**
     * ✅ MODIFIÉ : Méthode statique pour obtenir les statistiques d'utilisation globales par session
     */
    public static function getGlobalUsageStats($examenId, $sessionId, $ecId = null)
    {
        $query = self::where('examen_id', $examenId)
                    ->where('session_exam_id', $sessionId);

        if ($ecId) {
            $query->where('ec_id', $ecId);
        }

        $total = $query->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'utilises' => 0,
                'avec_copie' => 0,
                'complets' => 0,
                'pourcentage_utilisation' => 0,
                'pourcentage_completion' => 0
            ];
        }

        $utilises = $query->whereHas('allManchettes', function($q) use ($sessionId) {
            $q->where('session_exam_id', $sessionId);
        })->count();

        $avecCopie = $query->whereHas('allCopies', function($q) use ($sessionId) {
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
     * ✅ MODIFIÉ : Trouve le code d'anonymat suivant dans la séquence pour la même salle/EC/session
     */
    public function getNextInSequence()
    {
        return self::where('examen_id', $this->examen_id)
                  ->where('ec_id', $this->ec_id)
                  ->where('session_exam_id', $this->session_exam_id)
                  ->where('sequence', '>', $this->sequence)
                  ->where('code_complet', 'LIKE', $this->code_salle . '%')
                  ->orderBy('sequence')
                  ->first();
    }

    /**
     * ✅ MODIFIÉ : Trouve le code d'anonymat précédent dans la séquence pour la même salle/EC/session
     */
    public function getPreviousInSequence()
    {
        return self::where('examen_id', $this->examen_id)
                  ->where('ec_id', $this->ec_id)
                  ->where('session_exam_id', $this->session_exam_id)
                  ->where('sequence', '<', $this->sequence)
                  ->where('code_complet', 'LIKE', $this->code_salle . '%')
                  ->orderBy('sequence', 'desc')
                  ->first();
    }

    /**
     * ✅ MODIFIÉ : Génère un nouveau code d'anonymat dans la même séquence pour une session
     */
    public static function generateNext($examenId, $sessionId, $ecId, $codeSalle)
    {
        $lastSequence = self::where('examen_id', $examenId)
                           ->where('session_exam_id', $sessionId)
                           ->where('ec_id', $ecId)
                           ->where('code_complet', 'LIKE', $codeSalle . '%')
                           ->max('sequence');

        $nextSequence = ($lastSequence ?? 0) + 1;
        $codeComplet = $codeSalle . $nextSequence;

        return self::create([
            'examen_id' => $examenId,
            'session_exam_id' => $sessionId,
            'ec_id' => $ecId,
            'code_complet' => $codeComplet,
            'sequence' => $nextSequence
        ]);
    }

    /**
     * Valide le format du code complet
     */
    public function validateCodeFormat()
    {
        // Format attendu: lettres suivies de chiffres (ex: TA1, SB23, etc.)
        return preg_match('/^[A-Za-z]+\d+$/', $this->code_complet);
    }

    /**
     * ✅ MODIFIÉ : Trouve les doublons de codes pour le même examen/EC/session
     */
    public static function findDuplicates($examenId, $sessionId, $ecId = null)
    {
        $query = self::where('examen_id', $examenId)
                    ->where('session_exam_id', $sessionId);

        if ($ecId) {
            $query->where('ec_id', $ecId);
        }

        return $query->selectRaw('code_complet, COUNT(*) as count')
                    ->groupBy('code_complet')
                    ->having('count', '>', 1)
                    ->get();
    }

    /**
     * ✅ NOUVELLE : Vérifie si un code existe déjà pour un examen/session/EC
     */
    public static function codeExists($examenId, $sessionId, $ecId, $codeComplet)
    {
        return self::where('examen_id', $examenId)
                  ->where('session_exam_id', $sessionId)
                  ->where('ec_id', $ecId)
                  ->where('code_complet', $codeComplet)
                  ->exists();
    }

    /**
     * ✅ NOUVELLE : Crée ou récupère un code d'anonymat pour une session spécifique
     */
    public static function firstOrCreateForSession($examenId, $sessionId, $ecId, $codeComplet)
    {
        return self::firstOrCreate(
            [
                'examen_id' => $examenId,
                'session_exam_id' => $sessionId,
                'ec_id' => $ecId,
                'code_complet' => $codeComplet,
            ],
            [
                'sequence' => null,
            ]
        );
    }
}
