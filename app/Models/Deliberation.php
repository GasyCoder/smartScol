<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliberation extends Model
{
    use HasFactory;

    protected $fillable = [
        'niveau_id',
        'session_id',
        'annee_universitaire_id',
        'date_deliberation',
        'president_jury'
    ];

    protected $casts = [
        'date_deliberation' => 'datetime'
    ];

    /**
     * Relations
     */
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function session()
    {
        return $this->belongsTo(SessionExam::class, 'session_id');
    }

    public function anneeUniversitaire()
    {
        return $this->belongsTo(AnneeUniversitaire::class);
    }

    public function presidentJury()
    {
        return $this->belongsTo(User::class, 'president_jury');
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    /**
     * Scope pour les délibérations de niveaux supérieurs (L2 à L6)
     */
    public function scopeNiveauxSuperieurs($query)
    {
        return $query->whereHas('niveau', function ($q) {
            $q->avecRattrapage();
        });
    }

    /**
     * Vérifie si tous les résultats sont saisis pour cette délibération
     */
    public function areAllResultatsSaisis()
    {
        // Récupérer les examens concernés
        $examens = Examen::where('niveau_id', $this->niveau_id)
            ->whereHas('session', function ($query) {
                $query->where('id', $this->session_id)
                    ->where('annee_universitaire_id', $this->annee_universitaire_id);
            })
            ->get();

        // Vérifier si tous les examens ont leurs résultats complets
        foreach ($examens as $examen) {
            if (!$examen->isCorrespondanceComplete()) {
                return false;
            }
        }

        return true;
    }
}

