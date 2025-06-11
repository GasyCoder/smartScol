<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
                    ->withPivot('salle_id', 'date_specifique', 'heure_specifique')
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

    public function codesAnonymat()
    {
        return $this->hasMany(CodeAnonymat::class, 'examen_id');
    }

    /**
     * =====================================
     * GESTION DES CONFLITS DE SALLES
     * =====================================
     */

    /**
     * Vérifie si une salle est disponible à une date et heure données
     *
     * @param int $salleId
     * @param string $date (format Y-m-d)
     * @param string $heure (format H:i:s)
     * @param int $dureeMinutes
     * @param int|null $examenIdExclude (pour exclure l'examen actuel lors de la modification)
     * @return bool
     */
    public static function isSalleDisponible($salleId, $date, $heure, $dureeMinutes, $examenIdExclude = null)
    {
        $dureeMinutes = (int) $dureeMinutes;

        $heureDebut = Carbon::parse($date . ' ' . $heure);
        $heureFin = $heureDebut->copy()->addMinutes($dureeMinutes);

        // Récupérer TOUS les examens dans cette salle à cette date
        $examensExistants = \DB::table('examen_ec')
            ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
            ->where('examen_ec.salle_id', $salleId)
            ->whereDate('examen_ec.date_specifique', $date)
            ->whereNull('examens.deleted_at')
            ->when($examenIdExclude, function($query) use ($examenIdExclude) {
                $query->where('examens.id', '!=', $examenIdExclude);
            })
            ->select('examens.duree', 'examen_ec.heure_specifique')
            ->get();

        // Vérifier chaque examen existant
        foreach ($examensExistants as $existant) {
            $heureDebutExistant = Carbon::parse($date . ' ' . $existant->heure_specifique);
            $heureFinExistant = $heureDebutExistant->copy()->addMinutes((int) $existant->duree);

            // CONFLIT si les créneaux se chevauchent
            if ($heureDebut->lt($heureFinExistant) && $heureFin->gt($heureDebutExistant)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifie tous les conflits pour un examen donné
     *
     * @param array $ecsData [['ec_id' => 1, 'salle_id' => 1, 'date' => '2024-01-01', 'heure' => '09:00'], ...]
     * @param int $dureeMinutes
     * @param int|null $examenIdExclude
     * @return array
     */
    public static function verifierConflitsSalles($ecsData, $dureeMinutes, $examenIdExclude = null)
    {
        $conflits = [];
        $dureeMinutes = (int) $dureeMinutes;

        // 1. Vérifier conflits avec examens existants
        foreach ($ecsData as $ecData) {
            if (empty($ecData['salle_id'])) continue;

            if (!self::isSalleDisponible($ecData['salle_id'], $ecData['date'], $ecData['heure'], $dureeMinutes, $examenIdExclude)) {
                $salle = Salle::find($ecData['salle_id']);
                $ec = EC::find($ecData['ec_id']);

                $conflits[] = [
                    'ec_id' => $ecData['ec_id'],
                    'ec_nom' => $ec->nom ?? 'EC inconnue',
                    'salle_id' => $ecData['salle_id'],
                    'salle_nom' => $salle->nom ?? 'Salle inconnue',
                    'date' => $ecData['date'],
                    'heure' => $ecData['heure'],
                    'type' => 'existant'
                ];
            }
        }

        // 2. Vérifier conflits INTERNES (entre ECs de cette session) - OPTIMISÉ
        $conflitsInternes = [];
        for ($i = 0; $i < count($ecsData); $i++) {
            for ($j = $i + 1; $j < count($ecsData); $j++) {
                $ec1 = $ecsData[$i];
                $ec2 = $ecsData[$j];

                // Même salle et même date ?
                if ($ec1['salle_id'] == $ec2['salle_id'] && $ec1['date'] == $ec2['date']) {
                    $debut1 = Carbon::parse($ec1['date'] . ' ' . $ec1['heure']);
                    $fin1 = $debut1->copy()->addMinutes($dureeMinutes);

                    $debut2 = Carbon::parse($ec2['date'] . ' ' . $ec2['heure']);
                    $fin2 = $debut2->copy()->addMinutes($dureeMinutes);

                    // CONFLIT INTERNE détecté
                    if ($debut1->lt($fin2) && $fin1->gt($debut2)) {
                        $salle = Salle::find($ec1['salle_id']);
                        $ecModel1 = EC::find($ec1['ec_id']);
                        $ecModel2 = EC::find($ec2['ec_id']);

                        // Ajouter SEULEMENT UN conflit pour éviter les doublons
                        $clefConflit = $ec1['salle_id'] . '_' . $ec1['date'] . '_' . min($ec1['ec_id'], $ec2['ec_id']) . '_' . max($ec1['ec_id'], $ec2['ec_id']);

                        if (!isset($conflitsInternes[$clefConflit])) {
                            $conflitsInternes[$clefConflit] = [
                                'ec_id' => $ec1['ec_id'],
                                'ec_nom' => $ecModel1->nom ?? 'EC inconnue',
                                'salle_id' => $ec1['salle_id'],
                                'salle_nom' => $salle->nom ?? 'Salle inconnue',
                                'date' => $ec1['date'],
                                'heure' => $ec1['heure'],
                                'type' => 'interne',
                                'conflit_avec' => $ecModel2->nom ?? 'EC inconnue'
                            ];
                        }
                    }
                }
            }
        }

        // Fusionner les conflits
        return array_merge($conflits, array_values($conflitsInternes));
    }

    /**
     * Récupère les examens en conflit pour une salle/date/heure donnée
     *
     * @param int $salleId
     * @param string $date
     * @param string $heure
     * @param int $dureeMinutes
     * @param int|null $examenIdExclude
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getExamensConflictuels($salleId, $date, $heure, $dureeMinutes, $examenIdExclude = null)
    {
        // S'assurer que $dureeMinutes est un entier
        $dureeMinutes = (int) $dureeMinutes;

        $heureDebut = Carbon::parse($date . ' ' . $heure);
        $heureFin = $heureDebut->copy()->addMinutes($dureeMinutes);

        return self::whereHas('ecs', function($query) use ($salleId, $date) {
                $query->where('examen_ec.salle_id', $salleId)
                      ->whereDate('examen_ec.date_specifique', $date);
            })
            ->when($examenIdExclude, function($query) use ($examenIdExclude) {
                $query->where('id', '!=', $examenIdExclude);
            })
            ->with(['ecs', 'niveau', 'parcours'])
            ->get()
            ->filter(function($examen) use ($salleId, $heureDebut, $heureFin) {
                foreach ($examen->ecs as $ec) {
                    if ($ec->pivot->salle_id == $salleId) {
                        $heureDebutExistant = Carbon::parse($ec->pivot->date_specifique . ' ' . $ec->pivot->heure_specifique);
                        // S'assurer que la durée est un entier
                        $dureeExistante = (int) $examen->duree;
                        $heureFinExistant = $heureDebutExistant->copy()->addMinutes($dureeExistante);

                        if ($heureDebut->lt($heureFinExistant) && $heureFin->gt($heureDebutExistant)) {
                            return true;
                        }
                    }
                }
                return false;
            });
    }

    /**
     * Récupère les créneaux occupés pour une salle à une date donnée
     *
     * @param int $salleId
     * @param string $date
     * @return array
     */
    public static function getCreneauxOccupes($salleId, $date)
    {
        return \DB::table('examen_ec')
            ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
            ->join('ecs', 'examen_ec.ec_id', '=', 'ecs.id')
            ->where('examen_ec.salle_id', $salleId)
            ->whereDate('examen_ec.date_specifique', $date)
            ->whereNull('examens.deleted_at')
            ->select(
                'ecs.nom as ec_nom',
                'examen_ec.heure_specifique as heure_debut',
                'examens.duree'
            )
            ->get()
            ->map(function($item) {
                $debut = Carbon::parse($item->heure_debut);
                $fin = $debut->copy()->addMinutes($item->duree);

                return [
                    'ec_nom' => $item->ec_nom,
                    'heure_debut' => $debut->format('H:i'),
                    'heure_fin' => $fin->format('H:i'),
                    'duree' => $item->duree
                ];
            })
            ->sortBy('heure_debut')
            ->values()
            ->toArray();
    }

    /**
     * Suggère des créneaux libres pour une salle à une date donnée
     *
     * @param int $salleId
     * @param string $date
     * @param int $dureeMinutes
     * @param string $heureOuverture (défaut: 08:00)
     * @param string $heureFermeture (défaut: 18:00)
     * @return array
     */
    public static function suggererCreneauxLibres($salleId, $date, $dureeMinutes, $heureOuverture = '08:00', $heureFermeture = '18:00')
    {
        $dureeMinutes = (int) $dureeMinutes;
        $creneauxLibres = [];

        $heureActuelle = Carbon::parse($date . ' ' . $heureOuverture);
        $heureLimite = Carbon::parse($date . ' ' . $heureFermeture);

        while ($heureActuelle->copy()->addMinutes($dureeMinutes)->lte($heureLimite)) {
            $heureTest = $heureActuelle->format('H:i');

            if (self::isSalleDisponible($salleId, $date, $heureTest, $dureeMinutes)) {
                $creneauxLibres[] = [
                    'heure_debut' => $heureTest,
                    'heure_fin' => $heureActuelle->copy()->addMinutes($dureeMinutes)->format('H:i')
                ];
            }

            $heureActuelle->addMinutes(30);
        }

        return $creneauxLibres;
    }

    /**
     * Valide la planification d'un examen avant sauvegarde
     *
     * @param array $ecsData
     * @param int $dureeMinutes
     * @param int|null $examenIdExclude
     * @return array ['valid' => bool, 'conflits' => array, 'message' => string]
     */
    public function validerPlanification($ecsData, $dureeMinutes, $examenIdExclude = null)
    {
        // S'assurer que $dureeMinutes est un entier
        $dureeMinutes = (int) $dureeMinutes;

        $conflits = self::verifierConflitsSalles($ecsData, $dureeMinutes, $examenIdExclude);

        if (empty($conflits)) {
            return [
                'valid' => true,
                'conflits' => [],
                'message' => 'Aucun conflit détecté. La planification est valide.'
            ];
        }

        $messages = [];
        foreach ($conflits as $conflit) {
            $messages[] = "Conflit détecté pour {$conflit['ec_nom']} dans la salle {$conflit['salle_nom']} le {$conflit['date']} à {$conflit['heure']}.";
        }

        return [
            'valid' => false,
            'conflits' => $conflits,
            'message' => 'Conflits de salles détectés : ' . implode(' ', $messages)
        ];
    }

    /**
     * =====================================
     * MÉTHODES EXISTANTES (inchangées)
     * =====================================
     */

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

    /**
     * Vérifie si des examens existent pour une combinaison niveau/parcours.
     *
     * @param int $niveauId
     * @param int|null $parcoursId
     * @return bool
     */
    public static function hasExamsForNiveauAndParcours($niveauId, $parcoursId = null)
    {
        $query = self::where('niveau_id', $niveauId);
        if ($parcoursId) {
            $query->where('parcours_id', $parcoursId);
        }
        return $query->exists();
    }
}
