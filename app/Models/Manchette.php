<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manchette extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'manchettes';

    protected $fillable = [
        'examen_id',
        'code_anonymat_id',
        'etudiant_id',
        'saisie_par',
        'date_saisie',
        'session_exam_id',
    ];

    protected $casts = [
        'date_saisie' => 'datetime',
    ];

    /**
     * Remplissage automatique du session_exam_id lors de la création
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($manchette) {
            if (empty($manchette->session_exam_id)) {
                $manchette->session_exam_id = self::getCurrentSessionId();
            }
        });
    }

    // Relations
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function utilisateurSaisie()
    {
        return $this->belongsTo(User::class, 'saisie_par');
    }

    public function codeAnonymat()
    {
        return $this->belongsTo(CodeAnonymat::class, 'code_anonymat_id');
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    public function sessionExam()
    {
        return $this->belongsTo(SessionExam::class, 'session_exam_id');
    }

    /**
     * Récupère l'ID de la session actuellement active
     */
    public static function getCurrentSessionId()
    {
        try {
            // Première tentative: session active et courante
            $sessionActive = SessionExam::where('is_active', true)
                ->where('is_current', true)
                ->first();

            if ($sessionActive) {
                return $sessionActive->id;
            }

            // Deuxième tentative: n'importe quelle session active
            $sessionActive = SessionExam::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($sessionActive) {
                \Log::warning('Session trouvée mais pas courante', [
                    'session_id' => $sessionActive->id,
                    'is_current' => $sessionActive->is_current
                ]);
                return $sessionActive->id;
            }

            // Dernière tentative: créer une session par défaut
            $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            if ($anneeActive) {
                $sessionActive = SessionExam::create([
                    'annee_universitaire_id' => $anneeActive->id,
                    'type' => 'Normale',
                    'is_active' => true,
                    'is_current' => true,
                    'date_start' => now(),
                    'date_end' => now()->addMonths(6)
                ]);

                \Log::info('Session créée automatiquement', [
                    'session_id' => $sessionActive->id
                ]);

                return $sessionActive->id;
            }

            throw new \Exception('Impossible de créer une session automatiquement');

        } catch (\Exception $e) {
            \Log::error('Erreur getCurrentSessionId', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Aucune session d\'examen disponible. Contactez l\'administrateur.');
        }
    }
    /**
     * Récupère le type de session actuellement active
     */
    public static function getCurrentSessionType()
    {
        $sessionId = self::getCurrentSessionId();
        if (!$sessionId) {
            return 'normale';
        }

        $session = SessionExam::find($sessionId);
        return $session ? strtolower($session->type) : 'normale';
    }

    /**
     * Vérifie si une manchette existe déjà pour cette combinaison dans la session active
     */
    public static function existsForCurrentSession($examenId, $etudiantId, $ecId)
    {
        $sessionId = self::getCurrentSessionId();
        if (!$sessionId) {
            return false;
        }

        return self::where('examen_id', $examenId)
            ->where('etudiant_id', $etudiantId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function($query) use ($ecId) {
                $query->where('ec_id', $ecId);
            })
            ->exists();
    }

    /**
     * Vérifie si un code d'anonymat est déjà utilisé dans la session active
     */
    public static function codeExistsForCurrentSession($examenId, $ecId, $codeComplet)
    {
        $sessionId = self::getCurrentSessionId();
        if (!$sessionId) {
            return false;
        }

        return self::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function($query) use ($ecId, $codeComplet) {
                $query->where('ec_id', $ecId)
                      ->where('code_complet', $codeComplet);
            })
            ->exists();
    }

    /**
     * Récupère les manchettes pour la session active
     */
    public static function forCurrentSession()
    {
        $sessionId = self::getCurrentSessionId();
        if (!$sessionId) {
            return self::whereNull('session_exam_id');
        }

        return self::where('session_exam_id', $sessionId);
    }

    /**
     * Récupère les manchettes pour une session spécifique
     */
    public static function forSession($sessionId)
    {
        return self::where('session_exam_id', $sessionId);
    }

    /**
     * CORRIGÉ : Vérifie si cette manchette est déjà associée à une copie pour sa session
     */
    public function isAssociated()
    {
        if (!$this->code_anonymat_id || !$this->examen_id) {
            return false;
        }

        $ecId = $this->getEcAttribute()->id ?? null;
        if (!$ecId) {
            return false;
        }

        // Vérifie s'il existe une copie correspondante pour cette session
        return Copie::where('examen_id', $this->examen_id)
                   ->where('code_anonymat_id', $this->code_anonymat_id)
                   ->where('ec_id', $ecId)
                   ->where('session_exam_id', $this->session_exam_id) // CORRIGÉ
                   ->exists();
    }

    /**
     * CORRIGÉ : Trouve la copie correspondante pour la même session
     */
    public function findCorrespondingCopie()
    {
        if (!$this->code_anonymat_id) {
            return null;
        }

        $ecId = $this->getEcAttribute()->id ?? null;
        if (!$ecId) {
            return null;
        }

        return Copie::where('code_anonymat_id', $this->code_anonymat_id)
                   ->where('ec_id', $ecId)
                   ->where('session_exam_id', $this->session_exam_id) // CORRIGÉ
                   ->first();
    }

    /**
     * NOUVEAU : Scopes pour filtrer par session via session_exam_id
     */
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

    public function scopeCurrentSession($query)
    {
        $sessionId = self::getCurrentSessionId();
        return $query->where('session_exam_id', $sessionId);
    }

    /**
     * Récupère le libellé de session via la relation
     */
    public function getSessionLibelleAttribute()
    {
        return $this->sessionExam ? $this->sessionExam->type : 'Inconnue';
    }

    /**
     * Récupère le type de session en minuscules
     */
    public function getSessionTypeAttribute()
    {
        return $this->sessionExam ? strtolower($this->sessionExam->type) : 'normale';
    }

    /**
     * Vérifie si c'est une manchette de session normale
     */
    public function isSessionNormale()
    {
        return $this->sessionExam && $this->sessionExam->type === 'Normale';
    }

    /**
     * Vérifie si c'est une manchette de session rattrapage
     */
    public function isSessionRattrapage()
    {
        return $this->sessionExam && $this->sessionExam->type === 'Rattrapage';
    }

    // Attributs existants (inchangés)
    public function getCodeAnonymatCompletAttribute()
    {
        return $this->codeAnonymat ? $this->codeAnonymat->code_complet : null;
    }

    public function getCodeSalleAttribute()
    {
        $codeObj = $this->codeAnonymat;
        if ($codeObj && preg_match('/^([A-Za-z]+)/', $codeObj->code_complet, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getNumeroAttribute()
    {
        $codeObj = $this->codeAnonymat;
        if ($codeObj && preg_match('/(\d+)$/', $codeObj->code_complet, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    public function getEcAttribute()
    {
        return $this->codeAnonymat ? $this->codeAnonymat->ec : null;
    }

    public function getMatriculeEtudiantAttribute()
    {
        return $this->etudiant ? $this->etudiant->matricule : null;
    }

    /**
     * CORRIGÉ : Compte les manchettes par session pour un examen et EC donnés
     */
    public static function countBySession($examenId, $ecId = null)
    {
        $query = self::where('examen_id', $examenId);

        if ($ecId) {
            $query->whereHas('codeAnonymat', function($q) use ($ecId) {
                $q->where('ec_id', $ecId);
            });
        }

        return $query->join('session_exams', 'manchettes.session_exam_id', '=', 'session_exams.id')
                    ->selectRaw('session_exams.type, COUNT(*) as count')
                    ->groupBy('session_exams.type')
                    ->pluck('count', 'type')
                    ->toArray();
    }

    /**
     * NOUVEAU : Trouve une manchette de l'autre session pour le même étudiant/matière
     */
    public function findCorrespondingManchetteInOtherSession()
    {
        // Récupère l'autre type de session
        $currentSessionType = $this->sessionExam ? $this->sessionExam->type : 'Normale';
        $otherSessionType = $currentSessionType === 'Normale' ? 'Rattrapage' : 'Normale';

        $ecId = $this->getEcAttribute()->id ?? null;
        if (!$ecId) {
            return null;
        }

        // Trouve la session de l'autre type dans la même année universitaire
        $autreSession = SessionExam::where('type', $otherSessionType)
            ->where('annee_universitaire_id', $this->sessionExam->annee_universitaire_id ?? null)
            ->first();

        if (!$autreSession) {
            return null;
        }

        return self::where('examen_id', $this->examen_id)
                  ->where('etudiant_id', $this->etudiant_id)
                  ->where('session_exam_id', $autreSession->id)
                  ->whereHas('codeAnonymat', function($query) use ($ecId) {
                      $query->where('ec_id', $ecId);
                  })
                  ->first();
    }

    /**
     * NOUVEAU : Scope pour filtrer par examen et session
     */
    public function scopeForExamenAndSession($query, $examenId, $sessionId = null)
    {
        $query->where('examen_id', $examenId);

        if ($sessionId) {
            $query->where('session_exam_id', $sessionId);
        }

        return $query;
    }

    /**
     * NOUVEAU : Scope pour filtrer par EC et session
     */
    public function scopeForEcAndSession($query, $ecId, $sessionId = null)
    {
        $sessionId = $sessionId ?? self::getCurrentSessionId();

        return $query->where('session_exam_id', $sessionId)
                    ->whereHas('codeAnonymat', function($q) use ($ecId) {
                        $q->where('ec_id', $ecId);
                    });
    }

    /**
     * NOUVEAU : Scope pour filtrer par étudiant et session
     */
    public function scopeForEtudiantAndSession($query, $etudiantId, $sessionId = null)
    {
        $sessionId = $sessionId ?? self::getCurrentSessionId();

        return $query->where('etudiant_id', $etudiantId)
                    ->where('session_exam_id', $sessionId);
    }

    /**
     * MODIFIÉ : Obtient des statistiques par session
     */
    public static function getSessionStats($examenId, $ecId = null)
    {
        $query = self::where('examen_id', $examenId);

        if ($ecId) {
            $query->whereHas('codeAnonymat', function($q) use ($ecId) {
                $q->where('ec_id', $ecId);
            });
        }

        $stats = $query->join('session_exams', 'manchettes.session_exam_id', '=', 'session_exams.id')
            ->selectRaw('
                session_exams.type as session_type,
                COUNT(*) as total,
                COUNT(DISTINCT etudiant_id) as etudiants_uniques,
                COUNT(DISTINCT saisie_par) as saisie_par_users
            ')
            ->groupBy('session_exams.type')
            ->get()
            ->keyBy('session_type');

        return [
            'Normale' => $stats->get('Normale', (object)[
                'total' => 0,
                'etudiants_uniques' => 0,
                'saisie_par_users' => 0
            ]),
            'Rattrapage' => $stats->get('Rattrapage', (object)[
                'total' => 0,
                'etudiants_uniques' => 0,
                'saisie_par_users' => 0
            ])
        ];
    }

    /**
     * MODIFIÉ : Marque les manchettes comme transférées vers une autre session
     */
    public function transferToSession($targetSessionId, $userId)
    {
        // Créer une copie dans la nouvelle session
        $newManchette = $this->replicate();
        $newManchette->session_exam_id = $targetSessionId;
        $newManchette->saisie_par = $userId;
        $newManchette->date_saisie = now();
        $newManchette->save();

        return $newManchette;
    }
}
