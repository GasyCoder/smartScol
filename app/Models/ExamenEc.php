<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamenEc extends Pivot
{
    protected $table = 'examen_ec';
    
    protected $fillable = [
        'examen_id',
        'ec_id', 
        'salle_id',
        'date_specifique',
        'heure_specifique',
        'code_base' // Code saisi manuellement
    ];

    protected $casts = [
        'date_specifique' => 'date',
        'heure_specifique' => 'datetime:H:i'
    ];

    /**
     * Relations
     */
    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function ec()
    {
        return $this->belongsTo(EC::class);
    }

    /**
     * Vérifier si le code est unique dans l'examen
     */
    public static function isCodeUniqueInExamen($code_base, $examenId, $excludeEcId = null)
    {
        $query = self::where('examen_id', $examenId)
                     ->where('code_base', $code_base);
        
        if ($excludeEcId) {
            $query->where('ec_id', '!=', $excludeEcId);
        }
        
        return !$query->exists();
    }

    /**
     * Obtenir tous les codes utilisés dans un examen
     */
    public static function getUsedCodesInExamen($examenId)
    {
        return self::where('examen_id', $examenId)
                   ->whereNotNull('code_base')
                   ->pluck('code_base')
                   ->toArray();
    }
}