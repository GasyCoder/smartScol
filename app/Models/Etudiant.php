<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date_naissance' => 'date', // Indique à Laravel de traiter ce champ comme une date
    ];

    // Définit les champs de type date qui seront gérés par Carbon
    protected $dates = [
        'date_naissance',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Accesseur/Mutateur pour la date de naissance
     * Convertit automatiquement le format
     */
    protected function dateNaissance(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? date('d/m/Y', strtotime($value)) : null,
            set: fn ($value) => $value ? date('Y-m-d', strtotime(str_replace('/', '-', $value))) : null,
        );
    }

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
