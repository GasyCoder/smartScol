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
        'code_base'
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
        if (empty($code_base)) {
            return true; // Les codes vides sont autorisés
        }

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
                   ->where('code_base', '!=', '')
                   ->pluck('code_base')
                   ->toArray();
    }

    /**
     * Vérifier si une salle est utilisée dans l'examen
     */
    public static function isSalleUsedInExamen($salleId, $examenId, $excludeEcId = null)
    {
        if (empty($salleId)) {
            return false;
        }

        $query = self::where('examen_id', $examenId)
                     ->where('salle_id', $salleId);
        
        if ($excludeEcId) {
            $query->where('ec_id', '!=', $excludeEcId);
        }
        
        return $query->exists();
    }

    /**
     * Générer le prochain code disponible pour un examen
     */
    public static function generateNextCodeForExamen($examenId)
    {
        $usedCodes = self::getUsedCodesInExamen($examenId);
        
        // Pattern de génération : TA, TB, TC, SA, SB, SC, etc.
        $firstLetters = ['T', 'S', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $secondLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        
        foreach ($firstLetters as $first) {
            foreach ($secondLetters as $second) {
                $code = $first . $second;
                if (!in_array($code, $usedCodes)) {
                    return $code;
                }
            }
        }
        
        // Si tous les codes 2 lettres sont utilisés, passer aux codes 3 lettres
        foreach ($firstLetters as $first) {
            foreach ($secondLetters as $second) {
                foreach ($secondLetters as $third) {
                    $code = $first . $second . $third;
                    if (!in_array($code, $usedCodes)) {
                        return $code;
                    }
                }
            }
        }
        
        return null; // Aucun code disponible
    }
}