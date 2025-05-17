<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamenEc extends Pivot
{
    protected $table = 'examen_ec';
    
    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }
}