<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'is_active' => 'boolean'
    ];



    /**
     * Relations
     */
    public function sessionExams()
    {
        return $this->hasMany(SessionExam::class);
    }

    public function deliberations()
    {
        return $this->hasMany(Deliberation::class);
    }

    /**
     * Retourne l'année universitaire active actuelle
     */
    public static function active()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Retourne l'année universitaire sous forme d'une chaîne
     */
    public function getLibelleAttribute()
    {
        return $this->date_start->format('Y') . '-' . $this->date_end->format('Y');
    }
}
