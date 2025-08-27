<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Examen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'examens';

    protected $fillable = [
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
                    ->withPivot('salle_id', 'code_base')
                    ->withTimestamps();
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function parcours()
    {
        return $this->belongsTo(Parcour::class);
    }

    public function copies()
    {
        return $this->hasMany(Copie::class);
    }

    public function manchettes()
    {
        return $this->hasMany(Manchette::class);
    }

    public function codesAnonymat()
    {
        return $this->hasMany(CodeAnonymat::class, 'examen_id');
    }

    /**
     * Vérifier si un examen existe déjà avec les mêmes critères
     */
    public static function examExists($niveauId, $parcoursId, $ecIds = [])
    {
        if (empty($ecIds)) {
            Log::info('examExists: Aucun EC fourni');
            return false;
        }

        Log::info('examExists: Recherche examens', [
            'niveauId' => $niveauId,
            'parcoursId' => $parcoursId,
            'ecIds' => $ecIds
        ]);

        // Rechercher tous les examens avec le même niveau et parcours
        $examensExistants = self::where('niveau_id', $niveauId)
            ->where('parcours_id', $parcoursId)
            ->with(['ecs' => function($query) {
                $query->orderBy('id');
            }])
            ->get();

        Log::info('examExists: Examens trouvés', [
            'count' => $examensExistants->count(),
            'examens' => $examensExistants->pluck('id')->toArray()
        ]);

        // Vérifier si un examen contient exactement les mêmes ECs
        foreach ($examensExistants as $examen) {
            $existingEcIds = $examen->ecs->pluck('id')->sort()->values()->toArray();
            $newEcIds = collect($ecIds)->sort()->values()->toArray();
            
            Log::info('examExists: Comparaison', [
                'examen_id' => $examen->id,
                'existingEcIds' => $existingEcIds,
                'newEcIds' => $newEcIds,
                'identique' => $existingEcIds === $newEcIds
            ]);
            
            if ($existingEcIds === $newEcIds) {
                Log::info('examExists: Doublon trouvé', ['examen_id' => $examen->id]);
                return $examen;
            }
        }

        Log::info('examExists: Aucun doublon trouvé');
        return false;
    }


    public function hasEc($ecId)
    {
        return $this->ecs()->where('ec_id', $ecId)->exists();
    }

    public function getAttachedEcIdsAttribute()
    {
        return $this->ecs()->pluck('ec_id')->toArray();
    }

    /**
     * Récupérer les étudiants concernés par cet examen
     */
    public function getEtudiantsConcernesAttribute()
    {
        $query = Etudiant::where('niveau_id', $this->niveau_id);

        if ($this->parcours_id) {
            $query->where('parcours_id', $this->parcours_id);
        }

        return $query->get();
    }

    /**
     * Vérifier si toutes les copies ont été saisies
     */
    public function areAllNotesSaisies()
    {
        $total = $this->etudiantsConcernes->count();
        $copies = $this->copies->count();

        return $total > 0 && $total === $copies;
    }

    /**
     * Vérifier si toutes les manchettes ont été saisies
     */
    public function areAllManchettesSaisies()
    {
        $total = $this->etudiantsConcernes->count();
        $manchettes = $this->manchettes->count();

        return $total > 0 && $total === $manchettes;
    }

    /**
     * Vérifier si la correspondance copies-manchettes est complète pour fusion
     */
    public function isCorrespondanceComplete()
    {
        foreach ($this->ecs as $ec) {
            $codes = CodeAnonymat::where('examen_id', $this->id)
                ->where('ec_id', $ec->id)
                ->pluck('id')
                ->toArray();

            if (empty($codes)) {
                continue;
            }

            $copiesCount = Copie::whereIn('code_anonymat_id', $codes)->count();
            $manchettesCount = Manchette::whereIn('code_anonymat_id', $codes)->count();

            if ($copiesCount != count($codes) || $manchettesCount != count($codes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifier si un code d'anonymat existe déjà pour cet examen et cette matière
     */
    public function codeAnonymatExists($code, $ec_id)
    {
        return $this->codesAnonymat()
            ->where('code_complet', $code)
            ->where('ec_id', $ec_id)
            ->exists();
    }

    /**
     * Récupérer le code d'anonymat d'un étudiant pour une matière spécifique
     */
    public function getCodeAnonymatEtudiant($etudiant_id, $ec_id)
    {
        $manchette = Manchette::where('examen_id', $this->id)
            ->where('etudiant_id', $etudiant_id)
            ->whereHas('codeAnonymat', function($q) use ($ec_id) {
                $q->where('ec_id', $ec_id);
            })
            ->first();

        return $manchette ? $manchette->codeAnonymat : null;
    }

    /**
     * Retourner les EC groupés par UE pour cet examen
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
     * Récupérer les codes d'anonymat groupés par EC
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
     * Calculer le statut général des copies et manchettes
     */
    public function getStatusGeneralAttribute()
    {
        $totalEtudiants = $this->etudiantsConcernes->count();
        $totalECs = $this->ecs->count();
        $totalTheorique = $totalEtudiants * $totalECs;

        $totalCodes = $this->codesAnonymat()->count();
        $totalCopies = $this->copies->count();
        $totalManchettes = $this->manchettes->count();

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
     * Vérifier si des examens existent pour une combinaison niveau/parcours
     */
    public static function hasExamsForNiveauAndParcours($niveauId, $parcoursId = null)
    {
        $query = self::where('niveau_id', $niveauId);
        if ($parcoursId) {
            $query->where('parcours_id', $parcoursId);
        }
        return $query->exists();
    }

    /**
     * Obtenir les statistiques des salles utilisées
     */
    public function getSalleStats()
    {
        $sallesUsed = [];
        $sallesSansAffectation = 0;

        foreach ($this->ecs as $ec) {
            if ($ec->pivot->salle_id) {
                $salle = Salle::find($ec->pivot->salle_id);
                if ($salle) {
                    $sallesUsed[$salle->id] = $salle->nom;
                }
            } else {
                $sallesSansAffectation++;
            }
        }

        return [
            'salles_utilisees' => count($sallesUsed),
            'salles_list' => $sallesUsed,
            'ecs_sans_salle' => $sallesSansAffectation
        ];
    }

    /**
     * Obtenir les statistiques des codes
     */
    public function getCodeStats()
    {
        $codesDefinis = 0;
        $codesSansAffectation = 0;

        foreach ($this->ecs as $ec) {
            if (!empty($ec->pivot->code_base)) {
                $codesDefinis++;
            } else {
                $codesSansAffectation++;
            }
        }

        return [
            'codes_definis' => $codesDefinis,
            'codes_manquants' => $codesSansAffectation,
            'pourcentage_completion' => $this->ecs->count() > 0 
                ? round(($codesDefinis / $this->ecs->count()) * 100) 
                : 0
        ];
    }
}