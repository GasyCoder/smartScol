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
        'session_id',
        'niveau_id',
        'parcours_id',
        'duree',
        'note_eliminatoire'
    ];

    protected $casts = [
        'duree' => 'integer',
        'note_eliminatoire' => 'decimal:2'
    ];

    /**
     * Relations
     */
    public function ecs()
    {
        return $this->belongsToMany(EC::class, 'examen_ec', 'examen_id', 'ec_id')
                    ->using(ExamenEc::class)
                    ->withPivot('salle_id', 'date_specifique', 'heure_specifique')
                    ->withTimestamps();
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

    public function salles()
    {
        return $this->hasManyThrough(Salle::class, 'examen_ec', 'examen_id', 'id', 'id', 'salle_id');
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

    public function codesAnonymat()
    {
        return $this->hasMany(CodeAnonymat::class, 'examen_id');
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
        $total = $this->etudiantsConcernes->count();
        $copies = $this->copies->count();

        return $total > 0 && $total === $copies;
    }

    /**
     * Vérifie si toutes les manchettes ont été saisies
     */
    public function areAllManchettesSaisies()
    {
        $total = $this->etudiantsConcernes->count();
        $manchettes = $this->manchettes->count();

        return $total > 0 && $total === $manchettes;
    }

    /**
     * Vérifie si la correspondance copies-manchettes est complète pour fusion
     */
    public function isCorrespondanceComplete()
    {
        // Si le nombre de copies et manchettes n'est pas égal, il manque des données
        $nbCopies = $this->copies->count();
        $nbManchettes = $this->manchettes->count();

        if ($nbCopies !== $nbManchettes) {
            return false;
        }

        // Vérifier que tous les codes d'anonymat dans les copies ont une manchette correspondante
        $codesCopies = $this->copies->pluck('code_anonymat_id')->toArray();
        $codesManchettes = $this->manchettes->pluck('code_anonymat_id')->toArray();

        return count(array_diff($codesCopies, $codesManchettes)) === 0;
    }

    /**
     * Vérifie si un code d'anonymat existe déjà pour cet examen
     */
    public function codeAnonymatExists($code)
    {
        return $this->codesAnonymat()->where('code_complet', $code)->exists();
    }

    /**
     * Récupère le code d'anonymat d'un étudiant pour cet examen
     */
    public function getCodeAnonymatEtudiant($etudiantId)
    {
        return CodeAnonymat::where('examen_id', $this->id)
            ->where('etudiant_id', $etudiantId)
            ->first();
    }

    /**
     * Retourne les EC groupés par UE pour cet examen
     */
    public function getEcsGroupedByUEAttribute()
    {
        return $this->ecs->groupBy(function($ec) {
            return $ec->ue_id;
        })->map(function($ecs, $ue_id) {
            $ue = UE::find($ue_id);
            return [
                'ue' => $ue,
                'ue_nom' => $ue ? $ue->nom : 'UE inconnue',
                'ue_abr' => $ue ? $ue->abr : 'UE',
                'ecs' => $ecs
            ];
        });
    }

    /**
     * Calcule le statut général des copies et manchettes
     */
    public function getStatusGeneralAttribute()
    {
        $totalEtudiants = $this->etudiantsConcernes->count();
        $totalCopies = $this->copies->count();
        $totalManchettes = $this->manchettes->count();

        return [
            'total_etudiants' => $totalEtudiants,
            'copies' => [
                'total' => $totalCopies,
                'pourcentage' => $totalEtudiants > 0 ? round(($totalCopies / $totalEtudiants) * 100) : 0,
                'complete' => $totalEtudiants > 0 && $totalCopies >= $totalEtudiants
            ],
            'manchettes' => [
                'total' => $totalManchettes,
                'pourcentage' => $totalEtudiants > 0 ? round(($totalManchettes / $totalEtudiants) * 100) : 0,
                'complete' => $totalEtudiants > 0 && $totalManchettes >= $totalEtudiants
            ],
            'fusion_possible' => $this->isCorrespondanceComplete()
        ];
    }

    /**
     * Retourne la première date d'examen disponible dans les ECs (pour compatibilité)
     */
    public function getFirstDateAttribute()
    {
        $relation = $this->ecs;
        $firstEC = $relation->first();

        return $firstEC && $firstEC->pivot && $firstEC->pivot->date_specifique
            ? \Carbon\Carbon::parse($firstEC->pivot->date_specifique)
            : null;
    }

    /**
     * Retourne la première heure de début disponible dans les ECs (pour compatibilité)
     */
    public function getFirstHeureDebutAttribute()
    {
        $relation = $this->ecs;
        $firstEC = $relation->first();

        return $firstEC && $firstEC->pivot && $firstEC->pivot->heure_specifique
            ? \Carbon\Carbon::parse($firstEC->pivot->heure_specifique)
            : null;
    }

    /**
     * Retourne la première salle disponible dans les ECs (pour compatibilité)
     */
    public function getFirstSalleAttribute()
    {
        $relation = $this->ecs;
        $firstEC = $relation->first();

        return $firstEC && $firstEC->pivot && $firstEC->pivot->salle_id
            ? Salle::find($firstEC->pivot->salle_id)
            : null;
    }
}
