<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parcour extends Model
{
    use HasFactory;

    protected $fillable = [
        'abr',
        'nom',
        'niveau_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relations
     */
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
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
}
