<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class EC extends Model
{
    use HasFactory;

    protected $table = 'ecs';

    protected $fillable = [
        'abr',
        'nom',
        'coefficient',
        'ue_id',
        'enseignant_id'
    ];

    protected $casts = [
        'coefficient' => 'decimal:2'
    ];

    /**
     * Relations
     */
    public function ue()
    {
        return $this->belongsTo(UE::class);
    }

    public function enseignant()
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    public function examens()
    {
        return $this->hasMany(Examen::class);
    }

    /**
     * Obtient le niveau à travers l'UE
     */
    public function getNiveauAttribute()
    {
        return $this->ue->niveau;
    }

    /**
     * Obtient le parcours à travers l'UE (si applicable)
     */
    public function getParcoursAttribute()
    {
        return $this->ue->parcours;
    }
}
