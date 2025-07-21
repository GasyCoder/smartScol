<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Niveau extends Model
{
    use HasFactory;

    protected $table = 'niveaux';

    protected $fillable = [
        'abr',
        'nom',
        'has_parcours',
        'has_rattrapage',
        'is_concours',
        'is_active'
    ];

    protected $casts = [
        'has_parcours' => 'boolean',
        'has_rattrapage' => 'boolean',
        'is_concours' => 'boolean',
        'is_active' => 'boolean',
    ];



    /**
     * Relations
     */
    public function parcours()
    {
        return $this->hasMany(Parcour::class);
    }

    public function ues()
    {
        return $this->hasMany(UE::class);
    }

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class);
    }

    public function examens()
    {
        return $this->hasMany(Examen::class);
    }

    public function deliberations()
    {
        return $this->hasMany(Deliberation::class);
    }

    /**
     * Niveau de type concours (PACES/L1)
     */
    public function scopeConcours($query)
    {
        return $query->where('is_concours', true);
    }

    /**
     * Niveaux supérieurs avec rattrapage (L2-L6)
     */
    public function scopeAvecRattrapage($query)
    {
        return $query->where('has_rattrapage', true);
    }
}
