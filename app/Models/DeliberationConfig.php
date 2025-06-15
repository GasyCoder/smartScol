<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliberationConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'niveau_id',
        'parcours_id',
        'session_id',
        'credits_admission_s1',
        'credits_admission_s2',
        'credits_redoublement_s2',
        'note_eliminatoire_bloque_s1',
        'note_eliminatoire_exclusion_s2',
        'delibere',
        'date_deliberation',
        'delibere_par'
    ];

    protected $casts = [
        'note_eliminatoire_bloque_s1' => 'boolean',
        'note_eliminatoire_exclusion_s2' => 'boolean',
        'delibere' => 'boolean',
        'date_deliberation' => 'datetime'
    ];

    // ✅ RELATIONS
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function parcours()
    {
        return $this->belongsTo(Parcour::class);
    }

    public function session()
    {
        return $this->belongsTo(SessionExam::class, 'session_id');
    }

    public function deliberePar()
    {
        return $this->belongsTo(User::class, 'delibere_par');
    }

    // ✅ MÉTHODES UTILITAIRES
    public static function getOrCreateConfig($niveauId, $parcoursId, $sessionId)
    {
        return self::firstOrCreate(
            [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId
            ],
            [
                'credits_admission_s1' => 60,
                'credits_admission_s2' => 40,
                'credits_redoublement_s2' => 20,
                'note_eliminatoire_bloque_s1' => true,
                'note_eliminatoire_exclusion_s2' => true,
                'delibere' => false
            ]
        );
    }

    public function marquerDelibere($userId)
    {
        $this->update([
            'delibere' => true,
            'date_deliberation' => now(),
            'delibere_par' => $userId
        ]);
    }

    public function annulerDeliberation()
    {
        $this->update([
            'delibere' => false,
            'date_deliberation' => null,
            'delibere_par' => null
        ]);
    }

    // ✅ SCOPES
    public function scopeDelibere($query)
    {
        return $query->where('delibere', true);
    }

    public function scopeNonDelibere($query)
    {
        return $query->where('delibere', false);
    }
}
