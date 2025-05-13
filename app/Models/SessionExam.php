<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionExam extends Model
{
    use HasFactory;

    protected $table = 'session_exams';

    protected $fillable = [
        'code',
        'nom',
        'annee_universitaire_id',
        'type',
        'date_start',
        'date_end'
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date'
    ];

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

    public function deliberations()
    {
        return $this->hasMany(Deliberation::class, 'session_id');
    }

    /**
     * Scope pour les sessions de type "normale"
     */
    public function scopeNormale($query)
    {
        return $query->where('type', 'normale');
    }

    /**
     * Scope pour les sessions de type "rattrapage"
     */
    public function scopeRattrapage($query)
    {
        return $query->where('type', 'rattrapage');
    }

    /**
     * Scope pour les sessions de type "concours"
     */
    public function scopeConcours($query)
    {
        return $query->where('type', 'concours');
    }
}
