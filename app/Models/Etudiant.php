<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Etudiant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'etudiants';

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'date_naissance',
        'sexe',
        'niveau_id',
        'parcours_id',
    ];

    /**
     * Relations
     */
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function parcours()
    {
        return $this->belongsTo(Parcour::class);
    }

    public function placements()
    {
        return $this->hasMany(Placement::class);
    }

    public function resultats()
    {
        return $this->hasMany(Resultat::class);
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    /**
     * Nom complet de l'étudiant
     */
    public function getFullNameAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Scope pour les étudiants PACES/L1
     */
    public function scopePaces($query)
    {
        return $query->whereHas('niveau', function ($q) {
            $q->concours();
        });
    }

    /**
     * Scope pour les étudiants L2-L6
     */
    public function scopeSuperieurs($query)
    {
        return $query->whereHas('niveau', function ($q) {
            $q->avecRattrapage();
        });
    }
}
