<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Copie extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'examen_id',
        'session_exam_id', // AJOUTÉ
        'ec_id',
        'code_anonymat_id',
        'note',
        'saisie_par',
        'note_old',
        'is_checked',
        'commentaire',
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'note_old' => 'decimal:2',
        'is_checked' => 'boolean',
    ];

    /**
     * AJOUTÉ : Remplissage automatique du session_exam_id lors de la création
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($copie) {
            if (empty($copie->session_exam_id)) {
                $copie->session_exam_id = Manchette::getCurrentSessionId();
            }
        });
    }

    // Relations
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function utilisateurSaisie()
    {
        return $this->belongsTo(User::class, 'saisie_par');
    }

    public function codeAnonymat()
    {
        return $this->belongsTo(CodeAnonymat::class, 'code_anonymat_id');
    }

    public function ec()
    {
        return $this->belongsTo(EC::class);
    }

    /**
     * AJOUTÉ : Relation avec la session d'examen
     */
    public function sessionExam()
    {
        return $this->belongsTo(SessionExam::class, 'session_exam_id');
    }

    public function resultatFusion()
    {
        return $this->hasOne(ResultatFusion::class, 'code_anonymat_id', 'code_anonymat_id')
            ->where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id);
    }

    public function resultatFinal()
    {
        return $this->hasOne(ResultatFinal::class, 'code_anonymat_id', 'code_anonymat_id')
            ->where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id);
    }

    // Attributs existants (inchangés)
    public function getCodeCompletAttribute()
    {
        return optional($this->codeAnonymat)->code_complet;
    }

    public function getCodeSalleAttribute()
    {
        if (!$this->codeAnonymat) {
            return null;
        }

        preg_match('/([A-Za-z]+)/', $this->codeAnonymat->code_complet, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    public function getNumeroAttribute()
    {
        if (!$this->codeAnonymat) {
            return null;
        }

        preg_match('/(\d+)$/', $this->codeAnonymat->code_complet, $matches);
        return isset($matches[1]) ? (int)$matches[1] : null;
    }

    /**
     * CORRIGÉ : Trouve l'étudiant via la manchette correspondante à la même session
     */
    public function getEtudiantAttribute()
    {
        $manchette = $this->findCorrespondingManchette();
        return $manchette ? $manchette->etudiant : null;
    }

    public function isAssociated()
    {
        return ResultatFusion::where('examen_id', $this->examen_id)
            ->where('code_anonymat_id', $this->code_anonymat_id)
            ->where('ec_id', $this->ec_id)
            ->exists();
    }

    /**
     * CORRIGÉ : Trouve la manchette correspondante dans la même session
     */
    public function findCorrespondingManchette()
    {
        if (!$this->code_anonymat_id || !$this->session_exam_id) {
            return null;
        }

        return Manchette::where('code_anonymat_id', $this->code_anonymat_id)
                       ->where('session_exam_id', $this->session_exam_id) // CORRIGÉ
                       ->whereHas('codeAnonymat', function($q) {
                           $q->where('ec_id', $this->ec_id);
                       })
                       ->first();
    }

    /**
     * AJOUTÉ : Récupère le type de session via la relation
     */
    public function getSessionTypeAttribute()
    {
        return $this->sessionExam ? strtolower($this->sessionExam->type) : 'normale';
    }

    /**
     * AJOUTÉ : Vérifie si c'est une copie de session normale
     */
    public function isSessionNormale()
    {
        return $this->sessionExam && $this->sessionExam->type === 'Normale';
    }

    /**
     * AJOUTÉ : Vérifie si c'est une copie de session rattrapage
     */
    public function isSessionRattrapage()
    {
        return $this->sessionExam && $this->sessionExam->type === 'Rattrapage';
    }

    /**
     * AJOUTÉ : Scopes pour filtrer par session
     */
    public function scopeSessionNormale($query)
    {
        return $query->whereHas('sessionExam', function($q) {
            $q->where('type', 'Normale');
        });
    }

    public function scopeSessionRattrapage($query)
    {
        return $query->whereHas('sessionExam', function($q) {
            $q->where('type', 'Rattrapage');
        });
    }

    public function scopeCurrentSession($query)
    {
        $sessionId = Manchette::getCurrentSessionId();
        return $query->where('session_exam_id', $sessionId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_exam_id', $sessionId);
    }

    // Méthodes existantes (inchangées)
    public function marquerCommeModifiee($nouvelleNote, $commentaire = null)
    {
        if ($this->note != $nouvelleNote) {
            $this->note_old = $this->note;
        }

        $this->note = $nouvelleNote;
        $this->commentaire = $commentaire;
        $this->is_checked = true;
        $this->save();

        Log::info('Copie marquée comme modifiée', [
            'copie_id' => $this->id,
            'ancienne_note' => $this->note_old,
            'nouvelle_note' => $this->note,
            'user_id' => Auth::id()
        ]);

        return $this;
    }

    public function marquerCommeVerifiee()
    {
        $this->is_checked = true;
        $this->save();

        return $this;
    }

    public function aEteModifiee()
    {
        return $this->note_old !== null && $this->note_old !== $this->note;
    }

    public function aEteVerifiee()
    {
        return $this->is_checked;
    }

    // Scopes existants (inchangés)
    public function scopeVerifiees($query)
    {
        return $query->where('is_checked', true);
    }

    public function scopeNonVerifiees($query)
    {
        return $query->where('is_checked', false);
    }

    public function scopeModifiees($query)
    {
        return $query->whereNotNull('note_old');
    }

    public function scopeNonModifiees($query)
    {
        return $query->whereNull('note_old');
    }

    // Méthodes existantes (inchangées)
    public function getStatutTexte()
    {
        if (!$this->is_checked) {
            return 'Non vérifiée';
        }

        if ($this->aEteModifiee()) {
            return 'Vérifiée et modifiée';
        }

        return 'Vérifiée sans modification';
    }

    public function synchroniserAvecResultatFusion()
    {
        $resultatFusion = ResultatFusion::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_anonymat_id', $this->code_anonymat_id)
            ->first();

        if ($resultatFusion && $this->is_checked) {
            $resultatFusion->note = $this->note;
            $resultatFusion->modifie_par = Auth::id();
            $resultatFusion->verified_at = now();
            $resultatFusion->verified_by = Auth::id();
            $resultatFusion->save();

            Log::info('Synchronisation copie -> ResultatFusion', [
                'copie_id' => $this->id,
                'resultat_fusion_id' => $resultatFusion->id,
                'note_synchronisee' => $this->note
            ]);
        }

        return $resultatFusion;
    }

    public static function marquerToutesVerifiees($examenId, $ecId = null, $filtres = [])
    {
        $query = self::where('examen_id', $examenId)
            ->where('is_checked', false);

        if ($ecId) {
            $query->where('ec_id', $ecId);
        }

        if (isset($filtres['search']) && $filtres['search']) {
            $query->whereHas('codeAnonymat', function($q) use ($filtres) {
                $q->whereHas('manchette.etudiant', function($subQ) use ($filtres) {
                    $subQ->where('matricule', 'like', '%' . $filtres['search'] . '%')
                         ->orWhere('nom', 'like', '%' . $filtres['search'] . '%')
                         ->orWhere('prenom', 'like', '%' . $filtres['search'] . '%');
                });
            });
        }

        $copies = $query->get();
        $nbMarquees = 0;

        foreach ($copies as $copie) {
            $copie->marquerCommeVerifiee();
            $nbMarquees++;
        }

        Log::info('Marquage en lot de copies comme vérifiées', [
            'examen_id' => $examenId,
            'ec_id' => $ecId,
            'nb_copies_marquees' => $nbMarquees,
            'user_id' => Auth::id()
        ]);

        return $nbMarquees;
    }

    public static function getStatistiquesVerification($examenId, $ecId = null, $filtres = [])
    {
        $query = self::where('examen_id', $examenId);

        if ($ecId) {
            $query->where('ec_id', $ecId);
        }

        if (isset($filtres['search']) && $filtres['search']) {
            $query->whereHas('codeAnonymat', function($q) use ($filtres) {
                $q->whereHas('manchette.etudiant', function($subQ) use ($filtres) {
                    $subQ->where('matricule', 'like', '%' . $filtres['search'] . '%')
                         ->orWhere('nom', 'like', '%' . $filtres['search'] . '%')
                         ->orWhere('prenom', 'like', '%' . $filtres['search'] . '%');
                });
            });
        }

        $total = $query->count();
        $verifiees = $query->where('is_checked', true)->count();
        $modifiees = $query->whereNotNull('note_old')->count();

        return [
            'total' => $total,
            'verifiees' => $verifiees,
            'non_verifiees' => $total - $verifiees,
            'modifiees' => $modifiees,
            'pourcentage_verification' => $total > 0 ? round(($verifiees / $total) * 100, 1) : 0
        ];
    }

    public function transfererVersResultatFinal()
    {
        if (!$this->is_checked) {
            throw new \Exception('La copie doit être vérifiée avant transfert vers ResultatFinal');
        }

        $resultatFusion = ResultatFusion::where('examen_id', $this->examen_id)
            ->where('ec_id', $this->ec_id)
            ->where('code_anonymat_id', $this->code_anonymat_id)
            ->where('statut', ResultatFusion::STATUT_VERIFY_3)
            ->first();

        if (!$resultatFusion) {
            throw new \Exception('Aucun ResultatFusion avec statut VERIFY_3 trouvé pour ce transfert');
        }

        $resultatFinal = ResultatFinal::updateOrCreate(
            [
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
                'code_anonymat_id' => $this->code_anonymat_id,
            ],
            [
                'etudiant_id' => $resultatFusion->etudiant_id,
                'note' => $this->note,
                'genere_par' => $resultatFusion->genere_par,
                'modifie_par' => Auth::id(),
                'fusion_id' => $resultatFusion->id,
                'date_fusion' => now(),
                'statut' => ResultatFinal::STATUT_EN_ATTENTE,
                'hash_verification' => hash('sha256',
                    $this->id . $this->code_anonymat_id . $this->note . now()->timestamp
                )
            ]
        );

        Log::info('Transfert copie -> ResultatFinal', [
            'copie_id' => $this->id,
            'resultat_fusion_id' => $resultatFusion->id,
            'resultat_final_id' => $resultatFinal->id,
            'note_transferee' => $this->note
        ]);

        return $resultatFinal;
    }

    public function scopeParEtudiant($query, $etudiantId)
    {
        return $query->whereHas('codeAnonymat.manchette', function($q) use ($etudiantId) {
            $q->where('etudiant_id', $etudiantId);
        });
    }

    /**
     * CORRIGÉ : Scope par session utilisant session_exam_id
     */
    public function scopeParSession($query, $sessionId)
    {
        return $query->where('session_exam_id', $sessionId);
    }

    public function scopeParNiveau($query, $niveauId)
    {
        return $query->whereHas('examen', function($q) use ($niveauId) {
            $q->where('niveau_id', $niveauId);
        });
    }

    public function scopeParParcours($query, $parcoursId)
    {
        return $query->whereHas('examen', function($q) use ($parcoursId) {
            $q->where('parcours_id', $parcoursId);
        });
    }
}