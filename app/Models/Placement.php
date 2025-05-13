<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    use HasFactory;

    protected $fillable = [
        'examen_id',
        'etudiant_id',
        'salle_id',
        'place',
        'is_present'
    ];

    protected $casts = [
        'is_present' => 'boolean'
    ];

    /**
     * Relations
     */
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    /**
     * Scope pour les étudiants présents à l'examen
     */
    public function scopePresents($query)
    {
        return $query->where('is_present', true);
    }

    /**
     * Scope pour les étudiants absents à l'examen
     */
    public function scopeAbsents($query)
    {
        return $query->where('is_present', false);
    }

    /**
     * Scope pour les placements dont la présence n'est pas encore marquée
     */
    public function scopePresenceNonMarquee($query)
    {
        return $query->whereNull('is_present');
    }
}
