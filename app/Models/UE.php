<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class UE extends Model
{
    use HasFactory;

    protected $table = 'ues';

    protected $fillable = [
        'abr',
        'nom',
        'niveau_id',
        'parcours_id'
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

    public function ecs()
    {
        return $this->hasMany(EC::class, 'ue_id');
    }
}

