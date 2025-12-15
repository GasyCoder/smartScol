<?php

namespace App\Models;

use App\Models\SessionExam;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnneeUniversitaire extends Model
{
    use HasFactory;

    protected $table = 'annees_universitaires';
    
    protected $fillable = [
        'date_start',
        'date_end',
        'is_active'
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'is_active' => 'boolean',
    ];

    // ✅ ACCESSEUR : Génère automatiquement le libellé
    public function getLibelleAttribute()
    {
        if (!$this->date_start || !$this->date_end) {
            return 'N/A';
        }
        
        $anneeDebut = Carbon::parse($this->date_start)->year;
        $anneeFin = Carbon::parse($this->date_end)->year;
        
        return "{$anneeDebut}-{$anneeFin}";
    }



    /**
     * Relations
     */
    public function sessionExams()
    {
        return $this->hasMany(SessionExam::class);
    }


    /**
     * Retourne l'année universitaire active actuelle
     */
    public static function active()
    {
        return self::where('is_active', true)->first();
    }
}