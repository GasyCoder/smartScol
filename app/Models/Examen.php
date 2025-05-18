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
     * Mise à jour pour vérifier par EC
     */
    public function isCorrespondanceComplete()
    {
        // Récupérer toutes les ECs pour cet examen
        $ecs = $this->ecs;

        foreach ($ecs as $ec) {
            // Compter les codes d'anonymat pour cette EC
            $codes = CodeAnonymat::where('examen_id', $this->id)
                ->where('ec_id', $ec->id)
                ->pluck('id')
                ->toArray();

            if (empty($codes)) {
                continue; // Passer si aucun code pour cette EC
            }

            // Vérifier que chaque code a une copie et une manchette
            $copiesCount = Copie::whereIn('code_anonymat_id', $codes)->count();
            $manchettesCount = Manchette::whereIn('code_anonymat_id', $codes)->count();

            // Si les nombres ne correspondent pas, la fusion n'est pas possible
            if ($copiesCount != count($codes) || $manchettesCount != count($codes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifie si un code d'anonymat existe déjà pour cet examen et cette matière
     * Mise à jour pour prendre en compte l'EC
     */
    public function codeAnonymatExists($code, $ec_id)
    {
        return $this->codesAnonymat()
            ->where('code_complet', $code)
            ->where('ec_id', $ec_id)
            ->exists();
    }



    /**
     * Récupère le code d'anonymat d'un étudiant pour une matière spécifique de cet examen
     */
    public function getCodeAnonymatEtudiant($etudiant_id, $ec_id)
    {
        // Trouver la manchette qui lie l'étudiant à un code pour cette matière
        $manchette = Manchette::where('examen_id', $this->id)
            ->where('etudiant_id', $etudiant_id)
            ->whereHas('codeAnonymat', function($q) use ($ec_id) {
                $q->where('ec_id', $ec_id);
            })
            ->first();

        return $manchette ? $manchette->codeAnonymat : null;
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
     * Récupère les codes d'anonymat groupés par EC
     */
    public function getCodesGroupedByECAttribute()
    {
        return $this->codesAnonymat()
            ->with(['ec', 'copie', 'manchette'])
            ->get()
            ->groupBy('ec_id')
            ->map(function($codes, $ec_id) {
                $ec = EC::find($ec_id);
                return [
                    'ec' => $ec,
                    'ec_nom' => $ec ? $ec->nom : 'EC inconnue',
                    'codes' => $codes,
                    'total' => $codes->count(),
                    'avec_copies' => $codes->filter(function($code) {
                        return $code->copie !== null;
                    })->count(),
                    'avec_manchettes' => $codes->filter(function($code) {
                        return $code->manchette !== null;
                    })->count()
                ];
            });
    }

    /**
     * Calcule le statut général des copies et manchettes, amélioré pour tenir compte des ECs
     */
    public function getStatusGeneralAttribute()
    {
        $totalEtudiants = $this->etudiantsConcernes->count();
        $totalECs = $this->ecs->count();

        // Total théorique: nombre d'étudiants × nombre de matières
        $totalTheorique = $totalEtudiants * $totalECs;

        // Nombre réel de codes d'anonymat, copies et manchettes
        $totalCodes = $this->codesAnonymat()->count();
        $totalCopies = $this->copies->count();
        $totalManchettes = $this->manchettes->count();

        // Statut par matière
        $statutParEC = [];
        foreach ($this->ecs as $ec) {
            $codesEC = $this->codesAnonymat()->where('ec_id', $ec->id)->pluck('id')->toArray();

            $copiesEC = Copie::whereIn('code_anonymat_id', $codesEC)->count();
            $manchettesEC = Manchette::whereIn('code_anonymat_id', $codesEC)->count();

            $statutParEC[$ec->id] = [
                'ec_nom' => $ec->nom,
                'codes' => count($codesEC),
                'copies' => $copiesEC,
                'manchettes' => $manchettesEC,
                'complet' => (count($codesEC) == $copiesEC && $copiesEC == $manchettesEC && $copiesEC > 0)
            ];
        }

        return [
            'total_etudiants' => $totalEtudiants,
            'total_ecs' => $totalECs,
            'total_theorique' => $totalTheorique,
            'copies' => [
                'total' => $totalCopies,
                'pourcentage' => $totalTheorique > 0 ? round(($totalCopies / $totalTheorique) * 100) : 0,
            ],
            'manchettes' => [
                'total' => $totalManchettes,
                'pourcentage' => $totalTheorique > 0 ? round(($totalManchettes / $totalTheorique) * 100) : 0,
            ],
            'par_ec' => $statutParEC,
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
