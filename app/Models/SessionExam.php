<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SessionExam extends Model
{
    use HasFactory;

    protected $table = 'session_exams';

    protected $fillable = [
        'is_active',
        'is_current',
        'annee_universitaire_id',
        'type',
        'date_start',
        'date_end'
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'is_active' => 'boolean',
        'is_current' => 'boolean'
    ];

    /**
     * Récupère la session normale correspondante à cette session de rattrapage
     */
    public function getSessionNormaleCorrespondante()
    {
        if ($this->type !== 'Rattrapage') {
            return null;
        }
        
        return self::where('annee_universitaire_id', $this->annee_universitaire_id)
            ->where('type', 'Normale')
            ->first();
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($session) {
            if ($session->is_active && $session->is_current) {
                // Désactiver seulement les autres sessions du MÊME TYPE
                // SANS toucher à is_current !
                DB::table('session_exams')
                    ->where('annee_universitaire_id', $session->annee_universitaire_id)
                    ->where('type', $session->type) // Même type seulement
                    ->where('id', '!=', $session->id)
                    ->update([
                        'is_active' => false
                        // ✅ On ne touche PAS à is_current !
                    ]);
            }
        });
    }

    /**
     * Relations
     */
    public function anneeUniversitaire()
    {
        return $this->belongsTo(AnneeUniversitaire::class);
    }

    public function examens()
    {
        return $this->hasMany(Examen::class, 'session_id');
    }



    /**
     * Vérifie si c'est une session de première session (normale)
     */
    public function isPremiereSession()
    {
        return $this->type === 'Normale';
    }

    /**
     * Vérifie si c'est une session de rattrapage
     */
    public function isRattrapage()
    {
        return $this->type === 'Rattrapage';
    }

    /**
     * Vérifie si cette session nécessite une délibération
     * Dans votre cas, uniquement la 2ème session (rattrapage) requiert délibération
     */
    public function needsDeliberation()
    {
        return $this->isRattrapage();
    }

    /**
     * Publie directement les résultats pour les sessions normales (sans délibération)
     */
    public function publierResultatsDirectement()
    {
        if (!$this->isPremiereSession()) {
            return [
                'success' => false,
                'message' => 'Cette méthode ne peut être utilisée que pour la première session'
            ];
        }

        $count = $this->resultats()
            ->where('statut', 'valide')
            ->update([
                'statut' => 'publie',
                'date_modification' => now()
            ]);

        return [
            'success' => true,
            'count' => $count,
            'message' => "$count résultats publiés directement"
        ];
    }

    /**
     * Scope pour les sessions de type "normale"
     */
    public function scopeNormale($query)
    {
        return $query->where('type', 'Normale');
    }

    /**
     * Scope pour les sessions de type "rattrapage"
     */
    public function scopeRattrapage($query)
    {
        return $query->where('type', 'Rattrapage');
    }

    /**
     * Scope pour les sessions actives dans l'année universitaire active
     */
    public function scopeActiveInActiveYear($query)
    {
        return $query->where('is_active', true)
                    ->whereHas('anneeUniversitaire', function($q) {
                        $q->where('is_active', true);
                    });
    }

    /**
     * Scope pour la session courante
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
