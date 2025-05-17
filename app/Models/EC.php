<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class EC extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ecs';

    protected $fillable = [
        'abr',
        'nom',
        'coefficient', //NULL
        'ue_id',
        'enseignant'
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

    public function examens()
    {
        return $this->belongsToMany(Examen::class, 'examen_ec', 'ec_id', 'examen_id')
                    ->using(ExamenEc::class)
                    ->withPivot('salle_id', 'date_specifique', 'heure_specifique')
                    ->withTimestamps();
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
