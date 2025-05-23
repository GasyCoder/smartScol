<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ResultatFinal extends Model
{
    use HasFactory;

    protected $table = 'resultats_finaux';

    // Statuts optimisés pour résultats déjà validés
    const STATUT_EN_ATTENTE = 'en_attente';  // En attente de publication/délibération
    const STATUT_PUBLIE = 'publie';          // Résultats publiés officiellement
    const STATUT_ANNULE = 'annule';          // Résultats annulés

    // Décisions possibles
    const DECISION_ADMIS = 'admis';
    const DECISION_AJOURNE = 'ajourne';
    const DECISION_RATTRAPAGE = 'rattrapage';
    const DECISION_EXCLUS = 'exclus';

    protected $fillable = [
        'etudiant_id',
        'examen_id',
        'code_anonymat_id',
        'ec_id',
        'note',
        'genere_par',
        'modifie_par',
        'statut',
        'date_publication',
        'status_history',
        'decision',
        'deliberation_id',
        'fusion_id',
        'date_fusion',
        'hash_verification'
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'status_history' => 'array',
        'date_publication' => 'datetime',
        'date_fusion' => 'datetime'
    ];

    /**
     * Relations
     */
    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function codeAnonymat()
    {
        return $this->belongsTo(CodeAnonymat::class);
    }

    public function ec()
    {
        return $this->belongsTo(EC::class);
    }

    public function utilisateurGeneration()
    {
        return $this->belongsTo(User::class, 'genere_par');
    }

    public function utilisateurModification()
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }

    public function deliberation()
    {
        return $this->belongsTo(Deliberation::class);
    }

    public function resultatFusion()
    {
        return $this->belongsTo(ResultatFusion::class, 'fusion_id');
    }

    /**
     * Libellés des statuts
     */
    public static function getLibellesStatuts()
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente de publication',
            self::STATUT_PUBLIE => 'Publié',
            self::STATUT_ANNULE => 'Annulé'
        ];
    }

    /**
     * Libellés des décisions
     */
    public static function getLibellesDecisions()
    {
        return [
            self::DECISION_ADMIS => 'Admis',
            self::DECISION_AJOURNE => 'Ajourné',
            self::DECISION_RATTRAPAGE => 'Rattrapage',
            self::DECISION_EXCLUS => 'Exclu'
        ];
    }

    /**
     * Obtenir le libellé lisible du statut actuel
     */
    public function getLibelleStatutAttribute()
    {
        $libelles = self::getLibellesStatuts();
        return $libelles[$this->statut] ?? 'Statut inconnu';
    }

    /**
     * Obtenir le libellé lisible de la décision
     */
    public function getLibelleDecisionAttribute()
    {
        if (!$this->decision) return null;
        $libelles = self::getLibellesDecisions();
        return $libelles[$this->decision] ?? 'Décision inconnue';
    }

    /**
     * Transitions autorisées entre statuts
     */
    public static function getTransitionsAutorisees()
    {
        return [
            self::STATUT_EN_ATTENTE => [self::STATUT_PUBLIE, self::STATUT_ANNULE],
            self::STATUT_PUBLIE => [self::STATUT_ANNULE],
            self::STATUT_ANNULE => [self::STATUT_EN_ATTENTE, self::STATUT_PUBLIE]
        ];
    }

    /**
     * Vérifie si une transition est autorisée
     */
    public static function transitionAutorisee($statutActuel, $nouveauStatut)
    {
        $transitions = self::getTransitionsAutorisees();
        return in_array($nouveauStatut, $transitions[$statutActuel] ?? []);
    }

    /**
     * Vérifie si une délibération est requise
     */
    public function requiresDeliberation()
    {
        // Obtenir la session de l'examen
        $session = $this->examen->session;

        // Obtenir le niveau
        $niveau = $this->examen->niveau;

        // Si c'est un niveau de concours, pas de délibération
        if ($niveau->is_concours) {
            return false;
        }

        // Délibération uniquement pour les sessions de rattrapage
        return $session && $session->isRattrapage();
    }

    /**
     * Change le statut du résultat final avec gestion de la délibération
     */
    public function changerStatut($nouveauStatut, $userId, $avecDeliberation = false, $decision = null)
    {
        // Vérifier si la transition est autorisée
        if (!self::transitionAutorisee($this->statut, $nouveauStatut)) {
            throw new \Exception("Transition de statut non autorisée: {$this->statut} → {$nouveauStatut}");
        }

        // Préparer les données historiques
        $historique = $this->status_history ?? [];
        $historique[] = [
            'de' => $this->statut,
            'vers' => $nouveauStatut,
            'user_id' => $userId,
            'date' => now()->toDateTimeString(),
            'avec_deliberation' => $avecDeliberation,
            'decision' => $decision
        ];

        // Mettre à jour les informations
        $ancienStatut = $this->statut;
        $this->statut = $nouveauStatut;
        $this->status_history = $historique;
        $this->modifie_par = $userId;

        // Appliquer la décision si fournie
        if ($decision) {
            $this->decision = $decision;
        }

        // Gestion des dates avec délibération
        if ($nouveauStatut === self::STATUT_PUBLIE) {
            $this->date_publication = now();

            // Générer un hash de vérification pour les résultats publiés
            if (!$this->hash_verification) {
                $this->hash_verification = hash('sha256', $this->id . $this->etudiant_id . $this->note . now()->timestamp);
            }
        }

        $this->save();

        // Journaliser le changement
        Log::info('Changement de statut résultat final', [
            'resultat_id' => $this->id,
            'de' => $ancienStatut,
            'vers' => $nouveauStatut,
            'user_id' => $userId,
            'avec_deliberation' => $avecDeliberation,
            'decision' => $decision
        ]);

        return $this;
    }

    /**
     * Détermine l'action effectuée lors d'un changement de statut
     */
    private function determinerActionEffectuee($ancienStatut, $nouveauStatut, $avecDeliberation = false)
    {
        if ($ancienStatut === self::STATUT_EN_ATTENTE && $nouveauStatut === self::STATUT_PUBLIE) {
            return $avecDeliberation ? 'publication_apres_deliberation' : 'publication_directe';
        } elseif ($ancienStatut === self::STATUT_ANNULE && $nouveauStatut === self::STATUT_PUBLIE) {
            return 'republication';
        } elseif ($nouveauStatut === self::STATUT_ANNULE) {
            return 'annulation';
        } elseif ($ancienStatut === self::STATUT_ANNULE && $nouveauStatut === self::STATUT_EN_ATTENTE) {
            return 'reactivation';
        }

        return 'changement_statut';
    }

    /**
     * Assigne une délibération à ce résultat
     */
    public function assignerDeliberation($deliberationId)
    {
        if (!$this->requiresDeliberation()) {
            return false;
        }

        $this->deliberation_id = $deliberationId;
        $this->save();

        Log::info('Assignation délibération', [
            'resultat_id' => $this->id,
            'deliberation_id' => $deliberationId
        ]);

        return true;
    }

    /**
     * Calcule la moyenne d'une UE pour un étudiant dans une session
     */
    public static function calculerMoyenneUE($etudiantId, $ueId, $sessionId)
    {
        $resultats = self::whereHas('examen', function($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
            ->whereHas('ec', function($query) use ($ueId) {
                $query->where('ue_id', $ueId);
            })
            ->where('etudiant_id', $etudiantId)
            ->where('statut', '!=', self::STATUT_ANNULE)
            ->with('ec')
            ->get();

        if ($resultats->isEmpty()) {
            return null;
        }

        $totalPoints = 0;
        $totalCoefficients = 0;

        foreach ($resultats as $resultat) {
            $coefficient = $resultat->ec->coefficient ?? 1;
            $totalPoints += $resultat->note * $coefficient;
            $totalCoefficients += $coefficient;
        }

        return $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;
    }

    /**
     * Vérifie si un étudiant valide une UE
     */
    public static function etudiantValideUE($etudiantId, $ueId, $sessionId, $seuilReussite = 10)
    {
        $ue = UE::find($ueId);
        if (!$ue) return false;

        $ecs = $ue->ecs;
        $totalECs = $ecs->count();

        if ($totalECs === 0) return false;

        $resultats = self::whereHas('examen', function($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
            ->whereHas('ec', function($query) use ($ueId) {
                $query->where('ue_id', $ueId);
            })
            ->where('etudiant_id', $etudiantId)
            ->where('statut', '!=', self::STATUT_ANNULE)
            ->get();

        $ecsReussis = $resultats->where('note', '>=', $seuilReussite)->count();
        $pourcentageReussite = 0.5; // 50%
        $seuilEcsRequis = ceil($totalECs * $pourcentageReussite);

        return $ecsReussis >= $seuilEcsRequis;
    }

    /**
     * Détermine automatiquement la décision pour première session
     */
    public static function determinerDecisionPremiereSession($etudiantId, $sessionId)
    {
        $examen = Examen::where('session_id', $sessionId)->first();
        if (!$examen) return self::DECISION_AJOURNE;

        $niveau = $examen->niveau;
        $ues = $niveau->ues;

        $uesValidees = 0;
        $totalUes = $ues->count();

        foreach ($ues as $ue) {
            if (self::etudiantValideUE($etudiantId, $ue->id, $sessionId)) {
                $uesValidees++;
            }
        }

        if ($uesValidees === $totalUes) {
            return self::DECISION_ADMIS;
        }

        return self::DECISION_RATTRAPAGE;
    }

    /**
     * Détermine automatiquement la décision pour session rattrapage
     */
    public static function determinerDecisionRattrapage($etudiantId, $sessionId, $pourcentageRequis = 0.8)
    {
        $examen = Examen::where('session_id', $sessionId)->first();
        if (!$examen) return self::DECISION_AJOURNE;

        $niveau = $examen->niveau;
        $ues = $niveau->ues;

        $uesValidees = 0;
        $totalUes = $ues->count();

        foreach ($ues as $ue) {
            if (self::etudiantValideUE($etudiantId, $ue->id, $sessionId)) {
                $uesValidees++;
            }
        }

        // Calculer moyenne générale
        $moyenneGenerale = self::calculerMoyenneGenerale($etudiantId, $sessionId);

        // Vérifier seuil UE et moyenne
        $pourcentageValidees = $totalUes > 0 ? $uesValidees / $totalUes : 0;

        if ($pourcentageValidees >= $pourcentageRequis && $moyenneGenerale >= 10) {
            return self::DECISION_ADMIS;
        } elseif ($moyenneGenerale >= 8 && $pourcentageValidees >= 0.7) {
            // Critères de rachat possible (à adapter selon vos règles)
            return self::DECISION_AJOURNE; // Ajournement avec possibilité de rachat en délibération
        } else {
            return self::DECISION_AJOURNE;
        }
    }

    /**
     * Calcule la moyenne générale d'un étudiant pour une session
     */
    public static function calculerMoyenneGenerale($etudiantId, $sessionId)
    {
        $moyennesUE = [];
        $creditsUE = [];

        $examen = Examen::where('session_id', $sessionId)->first();
        if (!$examen) return 0;

        $niveau = $examen->niveau;
        $ues = $niveau->ues;

        foreach ($ues as $ue) {
            $moyenne = self::calculerMoyenneUE($etudiantId, $ue->id, $sessionId);
            if ($moyenne !== null) {
                $moyennesUE[$ue->id] = $moyenne;
                $creditsUE[$ue->id] = $ue->credits > 0 ? $ue->credits : 1;
            }
        }

        if (empty($moyennesUE)) return 0;

        // Calcul pondéré par crédits
        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($moyennesUE as $ueId => $moyenne) {
            $credits = $creditsUE[$ueId];
            $totalPoints += $moyenne * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
    }

    /**
     * Indique si ce résultat a été modifié depuis sa création
     */
    public function getEstModifieAttribute()
    {
        return !is_null($this->modifie_par);
    }

    /**
     * Indique si l'étudiant a réussi cette matière
     */
    public function getEstReussieAttribute()
    {
        return $this->note >= 10;
    }

    /**
     * Tous les scopes pour les requêtes
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    public function scopePublie($query)
    {
        return $query->where('statut', self::STATUT_PUBLIE);
    }

    public function scopeAnnule($query)
    {
        return $query->where('statut', self::STATUT_ANNULE);
    }

    public function scopeReussi($query)
    {
        return $query->where('note', '>=', 10);
    }

    public function scopeEchoue($query)
    {
        return $query->where('note', '<', 10);
    }

    public function scopeAdmis($query)
    {
        return $query->where('decision', self::DECISION_ADMIS);
    }

    public function scopeAjourne($query)
    {
        return $query->where('decision', self::DECISION_AJOURNE);
    }

    public function scopeRattrapage($query)
    {
        return $query->where('decision', self::DECISION_RATTRAPAGE);
    }

    public function scopeExclus($query)
    {
        return $query->where('decision', self::DECISION_EXCLUS);
    }

    public function scopeAvecDeliberation($query)
    {
        return $query->whereNotNull('deliberation_id');
    }

    public function scopeSansDeliberation($query)
    {
        return $query->whereNull('deliberation_id');
    }

    public function scopePremiereSession($query)
    {
        return $query->whereHas('examen.session', function($q) {
            $q->where('type', 'Normale');
        });
    }

    public function scopeRattrapageSession($query)
    {
        return $query->whereHas('examen.session', function($q) {
            $q->where('type', 'Rattrapage');
        });
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

    public function scopeParAnneeUniversitaire($query, $anneeId)
    {
        return $query->whereHas('examen.session', function($q) use ($anneeId) {
            $q->where('annee_universitaire_id', $anneeId);
        });
    }

    public function scopePublieDans($query, $joursRecents)
    {
        return $query->where('statut', self::STATUT_PUBLIE)
            ->whereNotNull('date_publication')
            ->where('date_publication', '>=', now()->subDays($joursRecents));
    }
}
