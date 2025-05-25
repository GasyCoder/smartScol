<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UE extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ues';

    protected $fillable = [
        'abr',
        'nom',
        'niveau_id',
        'parcours_id',
        'credits'
    ];

    protected $casts = [
        'credits' => 'decimal:2'  // Cast pour assurer que c'est bien traité comme un décimal
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

    /**
     * Accesseur pour calculer le total des crédits des ECs associés
     * Si cette UE a des ECs avec leurs propres crédits
     */
    public function getCalculatedCreditsAttribute()
    {
        // Si cette UE a des ECs avec leurs propres crédits, on peut les additionner
        if ($this->ecs->isNotEmpty() && $this->ecs->first()->credits) {
            return $this->ecs->sum('credits');
        }

        // Sinon on retourne les crédits de l'UE
        return $this->credits;
    }

}
