<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Examen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'examens';

    protected $fillable = [
        'code',
        'ec_id',
        'session_id',
        'niveau_id',
        'parcours_id',
        'date',
        'heure_debut',
        'duree',
        'note_eliminatoire'
    ];

    protected $casts = [
        'date' => 'date',
        'heure_debut' => 'datetime:H:i',
        'duree' => 'integer',
        'note_eliminatoire' => 'decimal:2'
    ];

    /**
     * Relations
     */
    public function ec()
    {
        return $this->belongsTo(EC::class);
    }

    public function session()
    {
        return $this->belongsTo(SessionExam::class, 'session_id');
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function parcours()
    {
        return $this->belongsTo(Parcour::class);
    }

    public function placements()
    {
        return $this->hasMany(Placement::class);
    }

    public function copies()
    {
        return $this->hasMany(Copie::class);
    }

    public function manchettes()
    {
        return $this->hasMany(Manchette::class);
    }

    public function resultats()
    {
        return $this->hasMany(Resultat::class);
    }

    /**
     * Génère un code unique pour l'examen
     */
    public static function genererCode()
    {
        $code = strtoupper(Str::random(8));
        while (self::where('code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }
        return $code;
    }

    /**
     * Récupère les étudiants concernés par cet examen en fonction du niveau et parcours
     */
    public function getEtudiantsConcernesAttribute()
    {
        $query = Etudiant::where('niveau_id', $this->niveau_id);

        // Si examen avec parcours spécifique
        if ($this->parcours_id) {
            $query->where('parcours_id', $this->parcours_id);
        }

        return $query->get();
    }

    /**
     * Vérifie si toutes les copies ont été saisies
     */
    public function areAllNotesSaisies()
    {
        $placements = $this->placements->where('is_present', true)->count();
        $copies = $this->copies->count();

        return $placements > 0 && $placements === $copies;
    }

    /**
     * Vérifie si toutes les manchettes ont été saisies
     */
    public function areAllManchettesSaisies()
    {
        $placements = $this->placements->where('is_present', true)->count();
        $manchettes = $this->manchettes->count();

        return $placements > 0 && $placements === $manchettes;
    }

    /**
     * Vérifie si la correspondance copies-manchettes est complète
     */
    public function isCorrespondanceComplete()
    {
        return $this->areAllNotesSaisies() && $this->areAllManchettesSaisies() &&
               $this->resultats->count() === $this->placements->where('is_present', true)->count();
    }
}
