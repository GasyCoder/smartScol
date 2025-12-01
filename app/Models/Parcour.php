<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parcour extends Model
{
    use HasFactory;

    protected $table = 'parcours'; // Spécifier explicitement

    protected $fillable = [
        'abr',
        'nom',
        'niveau_id',
        'is_active',
        'is_ifirp',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_ifirp' => 'boolean',
    ];

    /**
     * Relations - TOUTES CORRIGÉES avec foreign key explicite
     */
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function ues()
    {
        return $this->hasMany(UE::class, 'parcours_id'); // EXPLICITE
    }

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class, 'parcours_id'); // EXPLICITE - C'EST ÇA LE PROBLÈME !
    }

    public function examens()
    {
        return $this->hasMany(Examen::class, 'parcours_id'); // EXPLICITE
    }

    /**
     * Scopes utiles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByNiveau($query, $niveauId)
    {
        return $query->where('niveau_id', $niveauId);
    }
}