<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',  // Code utilisé pour l'anonymat (ex: TA, SA)
        'capacite'
    ];

    protected $casts = [
        'capacite' => 'integer'
    ];

    /**
     * Relations
     */
    public function placements()
    {
        return $this->hasMany(Placement::class);
    }

    /**
     * Copies utilisant ce code de salle pour l'anonymat
     */
    public function copies()
    {
        return Copie::where('code_salle', $this->code);
    }

    /**
     * Manchettes utilisant ce code de salle pour l'anonymat
     */
    public function manchettes()
    {
        return Manchette::where('code_salle', $this->code);
    }

    /**
     * Scope pour rechercher par code
     */
    public function scopeParCode($query, $code)
    {
        return $query->where('code', $code);
    }
}
