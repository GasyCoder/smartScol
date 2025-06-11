<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EC extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ecs';

    protected $fillable = [
        'abr',
        'nom',
        'coefficient', //NULL
        'ue_id',
        'enseignant'
    ];

    protected $casts = [
        'coefficient' => 'decimal:2'
    ];

    protected $attributes = [
        'is_active' => true,
        'coefficient' => 1.0
    ];

    /**
     * Relations
     */
    public function ue(): BelongsTo
    {
        return $this->belongsTo(UE::class);
    }

    public function examens(): BelongsToMany
    {
        return $this->belongsToMany(Examen::class, 'examen_ec', 'ec_id', 'examen_id')
                    ->using(ExamenEc::class)
                    ->withPivot('salle_id', 'date_specifique', 'heure_specifique')
                    ->withTimestamps();
    }

    /**
     * Relation avec la table pivot examen_ec
     */
    public function examenEc(): HasMany
    {
        return $this->hasMany(ExamenEc::class, 'ec_id');
    }

    /**
     * Relation avec les résultats de fusion
     */
    public function resultatsFusion(): HasMany
    {
        return $this->hasMany(ResultatFusion::class);
    }

    /**
     * Relation avec les résultats finaux
     */
    public function resultatsFinaux(): HasMany
    {
        return $this->hasMany(ResultatFinal::class);
    }

    /**
     * Relation avec les codes d'anonymat
     */
    public function codesAnonymat(): HasMany
    {
        return $this->hasMany(CodeAnonymat::class);
    }

    /**
     * Accesseurs pour propriétés dérivées
     */
    public function getNiveauAttribute()
    {
        return $this->ue?->niveau;
    }

    public function getParcoursAttribute()
    {
        return $this->ue?->parcours;
    }

    /**
     * Accesseur pour le nom complet (avec code/abréviation si disponible)
     */
    public function getNomCompletAttribute()
    {
        $nom = $this->nom;

        if ($this->code) {
            $nom = $this->code . ' - ' . $nom;
        } elseif ($this->abr) {
            $nom = $this->abr . ' - ' . $nom;
        }

        return $nom;
    }

    /**
     * Accesseur pour le libellé court
     */
    public function getLibelleCourtAttribute()
    {
        return $this->abr ?? $this->code ?? $this->nom;
    }

    /**
     * Méthodes utilitaires pour les résultats
     */

    /**
     * Obtient tous les résultats pour cet EC dans une session donnée
     */
    public function getResultatsSession($sessionId, $useResultatFinal = false)
    {
        if ($useResultatFinal) {
            return $this->resultatsFinaux()
                ->where('session_exam_id', $sessionId)
                ->where('statut', '!=', ResultatFinal::STATUT_ANNULE)
                ->get();
        } else {
            return $this->resultatsFusion()
                ->where('session_exam_id', $sessionId)
                ->where('statut', ResultatFusion::STATUT_VALIDE)
                ->get();
        }
    }

    /**
     * Vérifie si l'EC a des résultats dans une session
     */
    public function hasResultatsInSession($sessionId, $useResultatFinal = false)
    {
        if ($useResultatFinal) {
            return $this->resultatsFinaux()
                ->where('session_exam_id', $sessionId)
                ->where('statut', '!=', ResultatFinal::STATUT_ANNULE)
                ->exists();
        } else {
            return $this->resultatsFusion()
                ->where('session_exam_id', $sessionId)
                ->where('statut', ResultatFusion::STATUT_VALIDE)
                ->exists();
        }
    }

    /**
     * Obtient la moyenne de l'EC pour une session
     */
    public function getMoyenneSession($sessionId, $useResultatFinal = false)
    {
        $resultats = $this->getResultatsSession($sessionId, $useResultatFinal);

        if ($resultats->isEmpty()) {
            return null;
        }

        // Vérifier s'il y a des notes éliminatoires
        if ($resultats->contains('note', 0)) {
            return 0;
        }

        return round($resultats->avg('note'), 2);
    }

    /**
     * Obtient le nombre d'étudiants ayant cette EC dans une session
     */
    public function getNombreEtudiantsSession($sessionId, $useResultatFinal = false)
    {
        return $this->getResultatsSession($sessionId, $useResultatFinal)
                    ->unique('etudiant_id')
                    ->count();
    }

    /**
     * Obtient les statistiques de l'EC pour une session
     */
    public function getStatistiquesSession($sessionId, $useResultatFinal = false)
    {
        $resultats = $this->getResultatsSession($sessionId, $useResultatFinal);

        if ($resultats->isEmpty()) {
            return [
                'nombre_etudiants' => 0,
                'moyenne' => null,
                'notes_eliminatoires' => 0,
                'taux_reussite' => 0
            ];
        }

        $notesEliminatoires = $resultats->where('note', 0)->count();
        $notesReussies = $resultats->where('note', '>=', 10)->count();

        return [
            'nombre_etudiants' => $resultats->count(),
            'moyenne' => $this->getMoyenneSession($sessionId, $useResultatFinal),
            'notes_eliminatoires' => $notesEliminatoires,
            'taux_reussite' => $resultats->count() > 0 ?
                round(($notesReussies / $resultats->count()) * 100, 2) : 0
        ];
    }

    /**
     * Scopes
     */

    /**
     * Scope pour les ECs actifs
     */
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les ECs d'une UE
     */
    public function scopeParUE($query, $ueId)
    {
        return $query->where('ue_id', $ueId);
    }

    /**
     * Scope pour les ECs d'un niveau
     */
    public function scopeParNiveau($query, $niveauId)
    {
        return $query->whereHas('ue', function($q) use ($niveauId) {
            $q->where('niveau_id', $niveauId);
        });
    }

    /**
     * Scope pour les ECs d'un parcours
     */
    public function scopeParParcours($query, $parcoursId)
    {
        return $query->whereHas('ue', function($q) use ($parcoursId) {
            $q->where('parcours_id', $parcoursId);
        });
    }

    /**
     * Scope pour les ECs qui ont des résultats dans une session
     */
    public function scopeAvecResultatsSession($query, $sessionId, $useResultatFinal = false)
    {
        if ($useResultatFinal) {
            return $query->whereHas('resultatsFinaux', function($q) use ($sessionId) {
                $q->where('session_exam_id', $sessionId)
                  ->where('statut', '!=', ResultatFinal::STATUT_ANNULE);
            });
        } else {
            return $query->whereHas('resultatsFusion', function($q) use ($sessionId) {
                $q->where('session_exam_id', $sessionId)
                  ->where('statut', ResultatFusion::STATUT_VALIDE);
            });
        }
    }

    /**
     * Méthodes de validation
     */

    /**
     * Vérifie si l'EC est valide pour un calcul
     */
    public function estValideCalcul()
    {
        return $this->is_active &&
               $this->ue &&
               $this->coefficient > 0;
    }

    /**
     * Obtient le coefficient effectif (valeur par défaut si null)
     */
    public function getCoefficientEffectif()
    {
        return $this->coefficient ?? 1.0;
    }

    /**
     * Méthodes pour les examens
     */

    /**
     * Vérifie si l'EC est programmée dans une session
     */
    public function estProgrammeeSession($sessionId)
    {
        return $this->examens()
                    ->where('session_exam_id', $sessionId)
                    ->exists();
    }

    /**
     * Obtient les examens de l'EC pour une session
     */
    public function getExamensSession($sessionId)
    {
        return $this->examens()
                    ->where('session_exam_id', $sessionId)
                    ->get();
    }

    /**
     * Override pour une meilleure performance des requêtes
     */
    public function newEloquentBuilder($query)
    {
        return new \Illuminate\Database\Eloquent\Builder($query);
    }

    /**
     * Méthode toString pour le debugging
     */
    public function __toString()
    {
        return $this->nom_complet ?? $this->nom ?? "EC #{$this->id}";
    }
}