<?php

namespace App\Models;

use App\Services\RattrapageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Etudiant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'etudiants';

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'date_naissance',
        'niveau_id',
        'parcours_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date_naissance' => 'date',
    ];

    protected $dates = [
        'date_naissance',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Récupère les ECs non validés pour cet étudiant
     */
    public function getEcsNonValidesSession($sessionNormaleId)
    {
        return app(RattrapageService::class)->getEcsNonValidesEtudiant($this->id, $sessionNormaleId);
    }

    /**
     * Vérifie si l'étudiant a des ECs à rattraper
     */
    public function hasEcsARattraper($sessionNormaleId)
    {
        $ecsNonValides = $this->getEcsNonValidesSession($sessionNormaleId);
        return $ecsNonValides['total_ecs_rattrapage'] > 0;
    }

    /**
     * Accesseur/Mutateur pour la date de naissance
     */
    protected function dateNaissance(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? date('d/m/Y', strtotime($value)) : null,
            set: fn ($value) => $value ? date('Y-m-d', strtotime(str_replace('/', '-', $value))) : null,
        );
    }

    // ======================================================
    // RELATIONS EXISTANTES (corrigées)
    // ======================================================

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function parcours()
    {
        return $this->belongsTo(Parcour::class);
    }

    
    /**
     * CORRIGÉ : Relation avec ResultatFinal (pas Resultat)
     */
    public function resultatsFinaux()
    {
        return $this->hasMany(ResultatFinal::class);
    }

    /**
     * CORRIGÉ : Relation avec ResultatFusion
     */
    public function resultatsFusion()
    {
        return $this->hasMany(ResultatFusion::class);
    }

    /**
     * NOUVEAU : Relation avec les manchettes
     */
    public function manchettes()
    {
        return $this->hasMany(Manchette::class);
    }

    // ======================================================
    // NOUVELLES MÉTHODES POUR LA LOGIQUE DES SESSIONS
    // ======================================================

    /**
     * NOUVELLE MÉTHODE : Vérifie si l'étudiant peut passer en session rattrapage
     * RÈGLE MÉTIER : Seuls les étudiants avec décision "RATTRAPAGE" en session normale
     */
    public function peutPasserRattrapage($niveauId, $parcoursId, $sessionNormaleId)
    {
        // Récupérer toutes les décisions de l'étudiant en session normale
        $decisions = $this->resultatsFinaux()
            ->where('session_exam_id', $sessionNormaleId)
            ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                $q->where('niveau_id', $niveauId)
                  ->where('parcours_id', $parcoursId);
            })
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->pluck('decision')
            ->unique()
            ->toArray();

        // Si l'étudiant a une décision ADMIS, il ne peut PAS passer en rattrapage
        if (in_array(ResultatFinal::DECISION_ADMIS, $decisions)) {
            return false;
        }

        // Si l'étudiant a une décision RATTRAPAGE, il PEUT passer en rattrapage
        if (in_array(ResultatFinal::DECISION_RATTRAPAGE, $decisions)) {
            return true;
        }

        // Autres cas : ne peut pas passer
        return false;
    }

    /**
     * NOUVELLE MÉTHODE : Récupère la décision finale de l'étudiant pour une session
     */
    public function getDecisionPourSession($sessionId, $niveauId = null, $parcoursId = null)
    {
        $query = $this->resultatsFinaux()
            ->where('session_exam_id', $sessionId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE);

        if ($niveauId) {
            $query->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                $q->where('niveau_id', $niveauId);
                if ($parcoursId) {
                    $q->where('parcours_id', $parcoursId);
                }
            });
        }

        $decisions = $query->pluck('decision')->unique()->toArray();

        // Ordre de priorité pour la décision finale
        if (in_array(ResultatFinal::DECISION_ADMIS, $decisions)) {
            return ResultatFinal::DECISION_ADMIS;
        } elseif (in_array(ResultatFinal::DECISION_RATTRAPAGE, $decisions)) {
            return ResultatFinal::DECISION_RATTRAPAGE;
        } elseif (in_array(ResultatFinal::DECISION_EXCLUS, $decisions)) {
            return ResultatFinal::DECISION_EXCLUS;
        } elseif (in_array(ResultatFinal::DECISION_REDOUBLANT, $decisions)) {
            return ResultatFinal::DECISION_REDOUBLANT;
        }

        return null;
    }

    /**
     * NOUVEAU SCOPE : Récupère les étudiants éligibles au rattrapage
     */
    public function scopeEligiblesRattrapage($query, $niveauId, $parcoursId, $sessionNormaleId)
    {
        return $query->where('niveau_id', $niveauId)
            ->where('parcours_id', $parcoursId)
            ->whereHas('resultatsFinaux', function($q) use ($sessionNormaleId) {
                $q->where('session_exam_id', $sessionNormaleId)
                  ->where('decision', ResultatFinal::DECISION_RATTRAPAGE)
                  ->where('statut', ResultatFinal::STATUT_PUBLIE);
            })
            // IMPORTANT : Exclure ceux qui ont aussi une décision ADMIS
            ->whereDoesntHave('resultatsFinaux', function($q) use ($sessionNormaleId) {
                $q->where('session_exam_id', $sessionNormaleId)
                  ->where('decision', ResultatFinal::DECISION_ADMIS)
                  ->where('statut', ResultatFinal::STATUT_PUBLIE);
            });
    }

    /**
     * NOUVELLE MÉTHODE STATIQUE : Récupère les étudiants selon le type de session
     */
    public static function getEtudiantsSelonSession($niveauId, $parcoursId, $typeSession, $anneeUniversitaireId)
    {
        if ($typeSession === 'Normale') {
            // Session normale : TOUS les étudiants du niveau/parcours
            return self::where('niveau_id', $niveauId)
                ->where('parcours_id', $parcoursId)
                ->get();
        } else {
            // Session rattrapage : chercher la session normale correspondante
            $sessionNormale = SessionExam::where('annee_universitaire_id', $anneeUniversitaireId)
                ->where('type', 'Normale')
                ->first();

            if (!$sessionNormale) {
                return collect(); // Collection vide si pas de session normale
            }

            // Seuls les étudiants éligibles au rattrapage
            return self::eligiblesRattrapage($niveauId, $parcoursId, $sessionNormale->id)->get();
        }
    }

    /**
     * NOUVELLE MÉTHODE : Vérifie si l'étudiant a des résultats en session normale
     */
    public function hasResultatsSessionNormale($niveauId, $parcoursId, $anneeUniversitaireId)
    {
        $sessionNormale = SessionExam::where('annee_universitaire_id', $anneeUniversitaireId)
            ->where('type', 'Normale')
            ->first();

        if (!$sessionNormale) {
            return false;
        }

        return $this->resultatsFinaux()
            ->where('session_exam_id', $sessionNormale->id)
            ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                $q->where('niveau_id', $niveauId)
                  ->where('parcours_id', $parcoursId);
            })
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->exists();
    }

    /**
     * NOUVELLE MÉTHODE : Récupère toutes les décisions de l'étudiant pour une session
     */
    public function getDecisionsSessionNormale($niveauId, $parcoursId, $anneeUniversitaireId)
    {
        $sessionNormale = SessionExam::where('annee_universitaire_id', $anneeUniversitaireId)
            ->where('type', 'Normale')
            ->first();

        if (!$sessionNormale) {
            return [];
        }

        return $this->resultatsFinaux()
            ->where('session_exam_id', $sessionNormale->id)
            ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                $q->where('niveau_id', $niveauId)
                  ->where('parcours_id', $parcoursId);
            })
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->pluck('decision')
            ->unique()
            ->toArray();
    }

    // ======================================================
    // MÉTHODES EXISTANTES (inchangées)
    // ======================================================

    /**
     * Nom complet de l'étudiant
     */
    public function getFullNameAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Scope pour les étudiants PACES/L1
     */
    public function scopePaces($query)
    {
        return $query->whereHas('niveau', function ($q) {
            $q->concours();
        });
    }

    /**
     * Scope pour les étudiants L2-L6
     */
    public function scopeSuperieurs($query)
    {
        return $query->whereHas('niveau', function ($q) {
            $q->avecRattrapage();
        });
    }
}
