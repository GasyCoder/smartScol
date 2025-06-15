<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Services\CalculAcademiqueService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResultatFinal extends Model
{
    use HasFactory;

    protected $table = 'resultats_finaux';

    // Statuts optimisés pour résultats déjà validés
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_PUBLIE = 'publie';
    const STATUT_ANNULE = 'annule';

    // Décisions possibles
    const DECISION_ADMIS = 'admis';
    const DECISION_RATTRAPAGE = 'rattrapage';
    const DECISION_REDOUBLANT = 'redoublant';
    const DECISION_EXCLUS = 'exclus';

    protected $fillable = [
        'etudiant_id',
        'examen_id',
        'session_exam_id',
        'code_anonymat_id',
        'ec_id',
        'note',
        'genere_par',
        'modifie_par',
        'statut',
        'status_history',
        'motif_annulation',
        'date_annulation',
        'annule_par',
        'date_reactivation',
        'reactive_par',
        'decision',
        'date_publication',
        'hash_verification',
        'jury_validated',
        'fusion_id',
        'date_fusion',
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'status_history' => 'array',
        'date_publication' => 'datetime',
        'date_fusion' => 'datetime',
        'date_annulation' => 'datetime',
        'date_reactivation' => 'datetime',
        'jury_validated' => 'boolean',
    ];

    /**
     * CORRECTION : Remplissage automatique du session_exam_id lors de la création
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($resultat) {
            if (empty($resultat->session_exam_id)) {
                $resultat->session_exam_id = Manchette::getCurrentSessionId();
            }
        });
    }

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

    public function sessionExam()
    {
        return $this->belongsTo(SessionExam::class, 'session_exam_id');
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

    public function utilisateurAnnulation()
    {
        return $this->belongsTo(User::class, 'annule_par');
    }

    public function utilisateurReactivation()
    {
        return $this->belongsTo(User::class, 'reactive_par');
    }

    public function deliberation()
    {
        return $this->belongsTo(Deliberation::class);
    }

    public function resultatFusion()
    {
        return $this->belongsTo(ResultatFusion::class, 'fusion_id');
    }

    public function historique()
    {
        return $this->hasMany(ResultatFinalHistorique::class);
    }

    // Scopes pour filtrer par session
    public function scopeForCurrentSession($query)
    {
        $sessionId = Manchette::getCurrentSessionId();
        return $query->where('session_exam_id', $sessionId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_exam_id', $sessionId);
    }

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

    // Méthodes utilitaires
    public function getSessionTypeAttribute()
    {
        return $this->sessionExam ? strtolower($this->sessionExam->type) : 'normale';
    }

    public function getSessionLibelleAttribute()
    {
        return $this->sessionExam ? $this->sessionExam->type : 'Inconnue';
    }

    /**
     * Relation avec l'historique utilisant status_history JSON
     */
    public function getHistoriqueAttribute()
    {
        return $this->status_history ?? [];
    }

    /**
     * Obtenir la dernière action d'annulation depuis status_history
     */
    public function getDerniereAnnulationAttribute()
    {
        $historique = $this->status_history ?? [];
        $annulations = array_filter($historique, function($entry) {
            return isset($entry['type_action']) && $entry['type_action'] === 'annulation';
        });

        return !empty($annulations) ? end($annulations) : null;
    }

    /**
     * Obtenir la dernière action de réactivation depuis status_history
     */
    public function getDerniereReactivationAttribute()
    {
        $historique = $this->status_history ?? [];
        $reactivations = array_filter($historique, function($entry) {
            return isset($entry['type_action']) && $entry['type_action'] === 'reactivation';
        });

        return !empty($reactivations) ? end($reactivations) : null;
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
            self::DECISION_RATTRAPAGE => 'Autorisé au rattrapage',
            self::DECISION_REDOUBLANT => 'Redoublant',
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
     * Vérifie si une transition est autorisée entre deux statuts
     */
    private static function transitionAutorisee($statutActuel, $nouveauStatut)
    {
        $transitions = self::getTransitionsAutorisees();
        return in_array($nouveauStatut, $transitions[$statutActuel] ?? []);
    }

    /**
     * Change le statut du résultat final avec historique dans status_history JSON
     */
    public function changerStatut($nouveauStatut, $userId, $avecDeliberation = false, $decision = null)
    {
        if (!self::transitionAutorisee($this->statut, $nouveauStatut)) {
            throw new \Exception("Transition de statut non autorisée: {$this->statut} → {$nouveauStatut}");
        }

        try {
            DB::beginTransaction();

            $ancienStatut = $this->statut;

            // Gestion de la décision si fournie
            if ($decision) {
                $validDecisions = [
                    self::DECISION_ADMIS,
                    self::DECISION_RATTRAPAGE,
                    self::DECISION_REDOUBLANT,
                    self::DECISION_EXCLUS
                ];
                if (!in_array($decision, $validDecisions)) {
                    throw new \Exception("Décision non valide: {$decision}");
                }
                $this->decision = $decision;
            }

            // Gestion de la publication
            if ($nouveauStatut === self::STATUT_PUBLIE) {
                $this->date_publication = now();
                if (!$this->hash_verification) {
                    $this->hash_verification = hash('sha256',
                        $this->id . $this->etudiant_id . $this->note . now()->timestamp
                    );
                }
            }

            // Mettre à jour le statut
            $this->statut = $nouveauStatut;
            $this->modifie_par = $userId;

            // Ajouter à l'historique JSON
            $historique = $this->status_history ?? [];
            $historique[] = [
                'type_action' => 'changement_statut',
                'statut_precedent' => $ancienStatut,
                'statut_nouveau' => $nouveauStatut,
                'user_id' => $userId,
                'date_action' => now()->toDateTimeString(),
                'donnees_supplementaires' => [
                    'avec_deliberation' => $avecDeliberation,
                    'decision' => $decision
                ]
            ];
            $this->status_history = $historique;

            $this->save();

            DB::commit();

            Log::info('Changement de statut résultat final', [
                'resultat_id' => $this->id,
                'de' => $ancienStatut,
                'vers' => $nouveauStatut,
                'user_id' => $userId,
                'avec_deliberation' => $avecDeliberation,
                'decision' => $decision
            ]);

            return $this;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de statut', [
                'resultat_id' => $this->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * Annule le résultat avec motif en utilisant les colonnes de la table
     */
    public function annuler($userId, $motif = null)
    {
        if ($this->statut !== self::STATUT_PUBLIE) {
            throw new \Exception("Seuls les résultats publiés peuvent être annulés");
        }

        try {
            DB::beginTransaction();

            $ancienStatut = $this->statut;

            // Mettre à jour le statut et les colonnes d'annulation
            $this->statut = self::STATUT_ANNULE;
            $this->modifie_par = $userId;
            $this->motif_annulation = $motif;
            $this->date_annulation = now();
            $this->annule_par = $userId;

            // Ajouter à l'historique JSON
            $historique = $this->status_history ?? [];
            $historique[] = [
                'type_action' => 'annulation',
                'statut_precedent' => $ancienStatut,
                'statut_nouveau' => self::STATUT_ANNULE,
                'user_id' => $userId,
                'date_action' => now()->toDateTimeString(),
                'motif' => $motif
            ];
            $this->status_history = $historique;

            $this->save();

            DB::commit();

            Log::info('Résultat final annulé', [
                'resultat_id' => $this->id,
                'user_id' => $userId,
                'motif' => $motif
            ]);

            return $this;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation', [
                'resultat_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Réactive le résultat annulé en utilisant les colonnes de la table
     */
    public function reactiver($userId)
    {
        if ($this->statut !== self::STATUT_ANNULE) {
            throw new \Exception("Seuls les résultats annulés peuvent être réactivés");
        }

        try {
            DB::beginTransaction();

            $ancienStatut = $this->statut;

            // Mettre à jour le statut et les colonnes de réactivation
            $this->statut = self::STATUT_EN_ATTENTE;
            $this->modifie_par = $userId;
            $this->date_reactivation = now();
            $this->reactive_par = $userId;

            // Ajouter à l'historique JSON
            $historique = $this->status_history ?? [];
            $historique[] = [
                'type_action' => 'reactivation',
                'statut_precedent' => $ancienStatut,
                'statut_nouveau' => self::STATUT_EN_ATTENTE,
                'user_id' => $userId,
                'date_action' => now()->toDateTimeString()
            ];
            $this->status_history = $historique;

            $this->save();

            DB::commit();

            Log::info('Résultat final réactivé', [
                'resultat_id' => $this->id,
                'user_id' => $userId
            ]);

            return $this;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la réactivation', [
                'resultat_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir l'historique complet formaté depuis status_history JSON
     */
    public function getStatusHistoryFormattedAttribute()
    {
        $historique = $this->status_history ?? [];

        return array_map(function ($entry) {
            $donnees = $entry['donnees_supplementaires'] ?? [];
            return [
                'de' => $entry['statut_precedent'] ?? null,
                'vers' => $entry['statut_nouveau'] ?? null,
                'user_id' => $entry['user_id'] ?? null,
                'date' => $entry['date_action'] ?? null,
                'type_action' => $entry['type_action'] ?? 'changement_statut',
                'avec_deliberation' => $donnees['avec_deliberation'] ?? false,
                'decision' => $donnees['decision'] ?? null,
                'motif' => $entry['motif'] ?? null,
            ];
        }, $historique);
    }

    /**
     * Vérifie si une délibération est requise
     */
    public function requiresDeliberation()
    {
        if (!$this->sessionExam) {
            return false;
        }

        $niveau = $this->examen->niveau ?? null;
        if ($niveau && $niveau->is_concours) {
            return false;
        }

        return $this->sessionExam->type === 'Rattrapage';
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
     * CORRECTION CRITIQUE : Calcule la moyenne d'une UE pour un étudiant dans une session
     * PLUS DE RÉFÉRENCE À examen.session_id car cette relation n'existe plus
     */
    public static function calculerMoyenneUE($etudiantId, $ueId, $sessionId)
    {
        try {
            // CORRECTION : Utiliser directement session_exam_id
            $resultats = self::with('ec')
                ->where('session_exam_id', $sessionId)
                ->whereHas('ec', function($q) use ($ueId) {
                    $q->where('ue_id', $ueId);
                })
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                return null;
            }

            // Vérifier s'il y a une note éliminatoire (0) dans cette UE
            $hasNoteZero = $resultats->contains('note', 0);

            if ($hasNoteZero) {
                // UE éliminée : moyenne = 0
                return 0;
            }

            // Calculer la moyenne UE = somme notes / nombre EC
            return round($resultats->avg('note'), 2);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul de la moyenne UE', [
                'etudiant_id' => $etudiantId,
                'ue_id' => $ueId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * CORRECTION CRITIQUE : Vérifie si un étudiant valide une UE selon les règles métier
     */
    public static function etudiantValideUE($etudiantId, $ueId, $sessionId)
    {
        try {
            $moyenneUE = self::calculerMoyenneUE($etudiantId, $ueId, $sessionId);

            if ($moyenneUE === null) {
                return false;
            }

            return $moyenneUE >= 10;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de validation UE', [
                'etudiant_id' => $etudiantId,
                'ue_id' => $ueId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * CORRECTION CRITIQUE : Détermine automatiquement la décision pour première session
     * PLUS DE RÉFÉRENCE À examen.session_id
     */
    public static function determinerDecisionPremiereSession($etudiantId, $sessionId)
    {
        try {
            // CORRECTION : Utiliser directement session_exam_id
            $resultats = self::with('ec.ue')
                ->where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                return self::DECISION_RATTRAPAGE;
            }

            // Grouper par UE
            $resultatsParUE = $resultats->groupBy('ec.ue_id');
            $totalCredits = 0;
            $creditsValides = 0;
            $hasNoteEliminatoire = false;

            foreach ($resultatsParUE as $ueId => $notesUE) {
                $ue = $notesUE->first()->ec->ue;
                $totalCredits += $ue->credits ?? 0;

                // Vérifier s'il y a une note éliminatoire (0) dans cette UE
                $hasNoteZeroInUE = $notesUE->contains('note', 0);

                if ($hasNoteZeroInUE) {
                    $hasNoteEliminatoire = true;
                    continue;
                }

                // Calculer la moyenne UE = somme notes / nombre EC
                $moyenneUE = $notesUE->avg('note');

                // UE validée si moyenne >= 10 ET aucune note = 0
                if ($moyenneUE >= 10) {
                    $creditsValides += $ue->credits ?? 0;
                }
            }

            // Décision selon votre logique
            $decision = $creditsValides >= $totalCredits ?
                self::DECISION_ADMIS :
                self::DECISION_RATTRAPAGE;

            Log::info('Décision première session calculée', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'credits_valides' => $creditsValides,
                'total_credits' => $totalCredits,
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'decision' => $decision
            ]);

            return $decision;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la détermination de la décision première session', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return self::DECISION_RATTRAPAGE;
        }
    }

    /**
     * CORRECTION CRITIQUE : Détermine automatiquement la décision pour session rattrapage
     */
    public static function determinerDecisionRattrapage($etudiantId, $sessionId)
    {
        try {
            // CORRECTION : Utiliser directement session_exam_id
            $resultats = self::with('ec.ue')
                ->where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                return self::DECISION_REDOUBLANT;
            }

            // Grouper par UE
            $resultatsParUE = $resultats->groupBy('ec.ue_id');
            $creditsValides = 0;
            $hasNoteEliminatoire = false;

            foreach ($resultatsParUE as $ueId => $notesUE) {
                $ue = $notesUE->first()->ec->ue;

                // Vérifier s'il y a une note éliminatoire (0) dans cette UE
                $hasNoteZeroInUE = $notesUE->contains('note', 0);

                if ($hasNoteZeroInUE) {
                    $hasNoteEliminatoire = true;
                    continue;
                }

                // Calculer la moyenne UE = somme notes / nombre EC
                $moyenneUE = $notesUE->avg('note');

                // UE validée si moyenne >= 10 ET aucune note = 0
                if ($moyenneUE >= 10) {
                    $creditsValides += $ue->credits ?? 0;
                }
            }

            // Décision selon votre logique pour le rattrapage
            if ($hasNoteEliminatoire) {
                $decision = self::DECISION_EXCLUS;
            } else {
                $decision = $creditsValides >= 40 ?
                    self::DECISION_ADMIS :
                    self::DECISION_REDOUBLANT;
            }

            Log::info('Décision rattrapage calculée', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'credits_valides' => $creditsValides,
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'decision' => $decision
            ]);

            return $decision;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la détermination de la décision rattrapage', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return self::DECISION_REDOUBLANT;
        }
    }

    /**
     * CORRECTION CRITIQUE : Calcule la moyenne générale d'un étudiant pour une session
     */
    public static function calculerMoyenneGenerale($etudiantId, $sessionId)
    {
        try {
            // CORRECTION : Utiliser directement session_exam_id
            $resultats = self::with('ec.ue')
                ->where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                return 0;
            }

            // Grouper par UE
            $resultatsParUE = $resultats->groupBy('ec.ue_id');
            $moyennesUE = [];

            foreach ($resultatsParUE as $ueId => $notesUE) {
                // Vérifier s'il y a une note éliminatoire (0) dans cette UE
                $hasNoteZeroInUE = $notesUE->contains('note', 0);

                if ($hasNoteZeroInUE) {
                    // UE éliminée : moyenne = 0
                    $moyennesUE[] = 0;
                } else {
                    // Calculer la moyenne UE = somme notes / nombre EC
                    $moyenneUE = $notesUE->avg('note');
                    $moyennesUE[] = $moyenneUE;
                }
            }

            // Moyenne générale = moyenne des moyennes UE
            $moyenneGenerale = count($moyennesUE) > 0 ?
                array_sum($moyennesUE) / count($moyennesUE) : 0;

            return round($moyenneGenerale, 2);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul de la moyenne générale', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * CORRECTION : Récupère les résultats académiques complets pour cet étudiant
     * NE PAS UTILISER CalculAcademiqueService qui peut avoir des références à examen.session_id
     */
    public function getResultatsAcademiquesComplets()
    {
        if (!$this->etudiant_id || !$this->session_exam_id) {
            return null;
        }

        try {
            // SOLUTION DIRECTE sans passer par le service qui peut avoir des erreurs
            return [
                'etudiant_id' => $this->etudiant_id,
                'session_id' => $this->session_exam_id,
                'moyenne_generale' => self::calculerMoyenneGenerale($this->etudiant_id, $this->session_exam_id),
                'decision' => $this->decision ?? 'non_definie',
                'resultats_ue' => $this->getResultatsUEDetailles()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des résultats académiques', [
                'resultat_final_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * NOUVEAU : Récupère les résultats UE détaillés pour cet étudiant
     */
    private function getResultatsUEDetailles()
    {
        $resultats = self::with('ec.ue')
            ->where('session_exam_id', $this->session_exam_id)
            ->where('etudiant_id', $this->etudiant_id)
            ->where('statut', self::STATUT_PUBLIE)
            ->get();

        $resultatsParUE = $resultats->groupBy('ec.ue_id');
        $resultatsUE = [];

        foreach ($resultatsParUE as $ueId => $notesUE) {
            $ue = $notesUE->first()->ec->ue;
            $hasNoteZero = $notesUE->contains('note', 0);
            $moyenneUE = $hasNoteZero ? 0 : $notesUE->avg('note');

            $resultatsUE[] = [
                'ue_id' => $ueId,
                'ue_nom' => $ue->nom,
                'moyenne' => round($moyenneUE, 2),
                'validee' => $moyenneUE >= 10 && !$hasNoteZero,
                'credits' => $ue->credits ?? 0,
                'notes_ec' => $notesUE->map(function($resultat) {
                    return [
                        'ec_nom' => $resultat->ec->nom,
                        'note' => $resultat->note,
                        'eliminatoire' => $resultat->note == 0
                    ];
                })->toArray()
            ];
        }

        return $resultatsUE;
    }

    /**
     * Calcule et met à jour la décision académique
     */
    public function calculerEtAppliquerDecision()
    {
        if (!$this->etudiant_id || !$this->session_exam_id) {
            return false;
        }

        try {
            $session = $this->sessionExam;
            $decision = $session && $session->type === 'Rattrapage'
                ? self::determinerDecisionRattrapage($this->etudiant_id, $this->session_exam_id)
                : self::determinerDecisionPremiereSession($this->etudiant_id, $this->session_exam_id);

            $this->decision = $decision;
            $this->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul et application de la décision', [
                'resultat_final_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * CORRECTION CRITIQUE : Applique les décisions académiques pour tous les étudiants d'une session
     * PLUS DE RÉFÉRENCE À examen.session_id
     */
    public static function appliquerDecisionsSession($sessionId)
    {
        try {
            $session = SessionExam::findOrFail($sessionId);
            $isRattrapage = $session->type === 'Rattrapage';

            // CORRECTION : Récupérer tous les étudiants de cette session directement
            $etudiantsIds = self::where('session_exam_id', $sessionId)
                ->where('statut', self::STATUT_PUBLIE)
                ->distinct('etudiant_id')
                ->pluck('etudiant_id');

            $count = 0;
            $statistiques = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0
            ];

            foreach ($etudiantsIds as $etudiantId) {
                // Calculer la décision selon votre logique
                $decision = $isRattrapage
                    ? self::determinerDecisionRattrapage($etudiantId, $sessionId)
                    : self::determinerDecisionPremiereSession($etudiantId, $sessionId);

                // Mettre à jour tous les résultats de cet étudiant pour cette session
                self::where('session_exam_id', $sessionId)
                    ->where('etudiant_id', $etudiantId)
                    ->where('statut', self::STATUT_PUBLIE)
                    ->update(['decision' => $decision]);

                $statistiques[$decision]++;
                $count++;
            }

            Log::info('Décisions académiques appliquées selon votre logique', [
                'session_id' => $sessionId,
                'type_session' => $session->type,
                'etudiants_traites' => $count,
                'statistiques' => $statistiques
            ]);

            return [
                'success' => true,
                'etudiants_traites' => $count,
                'statistiques' => $statistiques
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'application des décisions de session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'application des décisions : ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }

    /**
     * CORRECTION CRITIQUE : Génère un rapport académique pour un étudiant
     * PLUS DE RÉFÉRENCE À examen.session_id
     */
    public static function genererRapportAcademique($etudiantId, $sessionId)
    {
        try {
            $etudiant = Etudiant::find($etudiantId);
            $session = SessionExam::find($sessionId);

            // CORRECTION : Récupérer tous les résultats de l'étudiant directement
            $resultats = self::with('ec.ue')
                ->where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                throw new \Exception('Aucun résultat trouvé pour cet étudiant');
            }

            // Grouper par UE et calculer selon votre logique
            $resultatsParUE = $resultats->groupBy('ec.ue_id');
            $resultatsUE = [];
            $totalCredits = 0;
            $creditsValides = 0;
            $moyennesUE = [];
            $hasNoteEliminatoire = false;

            foreach ($resultatsParUE as $ueId => $notesUE) {
                $ue = $notesUE->first()->ec->ue;
                $totalCredits += $ue->credits ?? 0;

                // Vérifier s'il y a une note éliminatoire (0) dans cette UE
                $hasNoteZeroInUE = $notesUE->contains('note', 0);

                if ($hasNoteZeroInUE) {
                    $hasNoteEliminatoire = true;
                    $moyenneUE = 0;
                    $ueValidee = false;
                } else {
                    // Calculer la moyenne UE = somme notes / nombre EC
                    $moyenneUE = $notesUE->avg('note');
                    $ueValidee = $moyenneUE >= 10;

                    if ($ueValidee) {
                        $creditsValides += $ue->credits ?? 0;
                    }
                }

                $moyennesUE[] = $moyenneUE;

                $resultatsUE[] = [
                    'ue' => $ue,
                    'notes_ec' => $notesUE->map(function($note) {
                        return [
                            'ec' => $note->ec,
                            'note' => $note->note,
                            'est_eliminatoire' => $note->note == 0
                        ];
                    }),
                    'moyenne_ue' => round($moyenneUE, 2),
                    'validee' => $ueValidee,
                    'eliminee' => $hasNoteZeroInUE,
                    'credits' => $ue->credits ?? 0
                ];
            }

            // Moyenne générale = moyenne des moyennes UE
            $moyenneGenerale = count($moyennesUE) > 0 ?
                array_sum($moyennesUE) / count($moyennesUE) : 0;

            // Décision selon votre logique
            $decision = $session->type === 'Rattrapage' ?
                self::determinerDecisionRattrapage($etudiantId, $sessionId) :
                self::determinerDecisionPremiereSession($etudiantId, $sessionId);

            $rapport = [
                'informations_generales' => [
                    'etudiant' => $etudiant,
                    'session' => $session,
                    'date_generation' => now()->format('d/m/Y H:i:s')
                ],
                'resultats_detailles' => $resultatsUE,
                'synthese' => [
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'credits_valides' => $creditsValides,
                    'total_credits' => $totalCredits,
                    'pourcentage_credits' => $totalCredits > 0 ? round(($creditsValides / $totalCredits) * 100, 1) : 0,
                    'has_note_eliminatoire' => $hasNoteEliminatoire,
                    'decision' => $decision
                ],
                'observations' => self::genererObservationsSelonLogique($moyenneGenerale, $creditsValides, $totalCredits, $hasNoteEliminatoire, $decision)
            ];

            return $rapport;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du rapport académique', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Génère des observations selon votre logique académique
     */
    private static function genererObservationsSelonLogique($moyenneGenerale, $creditsValides, $totalCredits, $hasNoteEliminatoire, $decision)
    {
        $observations = [];

        // Observation sur la moyenne
        if ($moyenneGenerale >= 16) {
            $observations[] = "Excellente performance avec une moyenne générale de {$moyenneGenerale}/20.";
        } elseif ($moyenneGenerale >= 14) {
            $observations[] = "Très bonne performance avec une moyenne générale de {$moyenneGenerale}/20.";
        } elseif ($moyenneGenerale >= 12) {
            $observations[] = "Bonne performance avec une moyenne générale de {$moyenneGenerale}/20.";
        } elseif ($moyenneGenerale >= 10) {
            $observations[] = "Performance satisfaisante avec une moyenne générale de {$moyenneGenerale}/20.";
        } else {
            $observations[] = "Performance insuffisante avec une moyenne générale de {$moyenneGenerale}/20.";
        }

        // Observation sur les crédits
        $pourcentage = $totalCredits > 0 ? round(($creditsValides / $totalCredits) * 100, 1) : 0;
        $observations[] = "L'étudiant a validé {$creditsValides} crédits sur {$totalCredits} requis ({$pourcentage}%).";

        // Observation sur les notes éliminatoires
        if ($hasNoteEliminatoire) {
            $observations[] = "⚠️ ATTENTION : Une ou plusieurs notes éliminatoires (0) ont été détectées, rendant certaines UE non validées.";
        }

        // Observation sur la décision
        switch ($decision) {
            case self::DECISION_ADMIS:
                $observations[] = "✅ ADMIS : L'étudiant a validé toutes les UE requises.";
                break;
            case self::DECISION_RATTRAPAGE:
                $observations[] = "⚠️ RATTRAPAGE : L'étudiant doit repasser certaines UE non validées.";
                break;
            case self::DECISION_REDOUBLANT:
                $observations[] = "❌ REDOUBLANT : L'étudiant n'a pas atteint le minimum de crédits requis.";
                break;
            case self::DECISION_EXCLUS:
                $observations[] = "🚫 EXCLU : L'étudiant a des notes éliminatoires en session de rattrapage.";
                break;
        }

        return $observations;
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
     * Indique si cette note est éliminatoire
     */
    public function getEstEliminatoireAttribute()
    {
        return $this->note == 0;
    }

    /**
     * Obtient le motif de la dernière annulation
     */
    public function getMotifAnnulationActuelAttribute()
    {
        return $this->motif_annulation;
    }

    /**
     * Obtient la date de la dernière annulation
     */
    public function getDateAnnulationActuelleAttribute()
    {
        return $this->date_annulation;
    }

    /**
     * Obtient l'utilisateur qui a annulé
     */
    public function getAnnuleParActuelAttribute()
    {
        return $this->annule_par;
    }

    /**
     * Obtient la date de la dernière réactivation
     */
    public function getDateReactivationActuelleAttribute()
    {
        return $this->date_reactivation;
    }

    /**
     * Obtient l'utilisateur qui a réactivé
     */
    public function getReactiveParActuelAttribute()
    {
        return $this->reactive_par;
    }

    /**
     * Scopes pour les requêtes
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

    public function scopeEliminatoire($query)
    {
        return $query->where('note', '=', 0);
    }

    public function scopeAdmis($query)
    {
        return $query->where('decision', self::DECISION_ADMIS);
    }

    public function scopeRattrapage($query)
    {
        return $query->where('decision', self::DECISION_RATTRAPAGE);
    }

    public function scopeRedoublant($query)
    {
        return $query->where('decision', self::DECISION_REDOUBLANT);
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
        return $query->whereHas('sessionExam', function ($q) {
            $q->where('type', 'Normale');
        });
    }

    public function scopeRattrapageSession($query)
    {
        return $query->whereHas('sessionExam', function ($q) {
            $q->where('type', 'Rattrapage');
        });
    }

    public function scopeParNiveau($query, $niveauId)
    {
        return $query->whereHas('examen', function ($q) use ($niveauId) {
            $q->where('niveau_id', $niveauId);
        });
    }

    public function scopeParParcours($query, $parcoursId)
    {
        return $query->whereHas('examen', function ($q) use ($parcoursId) {
            $q->where('parcours_id', $parcoursId);
        });
    }

    public function scopeParAnneeUniversitaire($query, $anneeId)
    {
        return $query->whereHas('sessionExam', function ($q) use ($anneeId) {
            $q->where('annee_universitaire_id', $anneeId);
        });
    }

    public function scopePublieDans($query, $joursRecents)
    {
        return $query->where('statut', self::STATUT_PUBLIE)
            ->whereNotNull('date_publication')
            ->where('date_publication', '>=', now()->subDays($joursRecents));
    }

    /**
     * Statistiques globales pour une session
     */
    public static function getStatistiquesSession($sessionId)
    {
        try {
            $stats = self::where('session_exam_id', $sessionId)
                ->selectRaw('
                    COUNT(*) as total_resultats,
                    COUNT(CASE WHEN statut = ? THEN 1 END) as en_attente,
                    COUNT(CASE WHEN statut = ? THEN 1 END) as publies,
                    COUNT(CASE WHEN statut = ? THEN 1 END) as annules,
                    COUNT(CASE WHEN decision = ? THEN 1 END) as admis,
                    COUNT(CASE WHEN decision = ? THEN 1 END) as rattrapage,
                    COUNT(CASE WHEN decision = ? THEN 1 END) as redoublant,
                    COUNT(CASE WHEN decision = ? THEN 1 END) as exclus,
                    AVG(note) as moyenne_session,
                    COUNT(CASE WHEN note = 0 THEN 1 END) as notes_eliminatoires
                ', [
                    self::STATUT_EN_ATTENTE,
                    self::STATUT_PUBLIE,
                    self::STATUT_ANNULE,
                    self::DECISION_ADMIS,
                    self::DECISION_RATTRAPAGE,
                    self::DECISION_REDOUBLANT,
                    self::DECISION_EXCLUS
                ])
                ->first();

            return [
                'total_resultats' => $stats->total_resultats ?? 0,
                'statuts' => [
                    'en_attente' => $stats->en_attente ?? 0,
                    'publies' => $stats->publies ?? 0,
                    'annules' => $stats->annules ?? 0,
                ],
                'decisions' => [
                    'admis' => $stats->admis ?? 0,
                    'rattrapage' => $stats->rattrapage ?? 0,
                    'redoublant' => $stats->redoublant ?? 0,
                    'exclus' => $stats->exclus ?? 0,
                ],
                'notes' => [
                    'moyenne_session' => round($stats->moyenne_session ?? 0, 2),
                    'notes_eliminatoires' => $stats->notes_eliminatoires ?? 0,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques de session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Méthode pour publier en masse les résultats d'une session
     */
    public static function publierResultatsSession($sessionId, $userId, $avecDecisions = true)
    {
        try {
            DB::beginTransaction();

            // Récupérer tous les résultats en attente
            $resultats = self::where('session_exam_id', $sessionId)
                ->where('statut', self::STATUT_EN_ATTENTE)
                ->get();

            if ($resultats->isEmpty()) {
                throw new \Exception('Aucun résultat en attente de publication pour cette session');
            }

            $count = 0;
            foreach ($resultats as $resultat) {
                // Appliquer la décision si demandé
                if ($avecDecisions) {
                    $resultat->calculerEtAppliquerDecision();
                }

                // Publier le résultat
                $resultat->changerStatut(self::STATUT_PUBLIE, $userId);
                $count++;
            }

            DB::commit();

            Log::info('Publication en masse des résultats', [
                'session_id' => $sessionId,
                'resultats_publies' => $count,
                'avec_decisions' => $avecDecisions,
                'user_id' => $userId
            ]);

            return [
                'success' => true,
                'resultats_publies' => $count,
                'message' => "Succès : {$count} résultats publiés"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la publication en masse', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la publication : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Méthode pour annuler en masse les résultats d'une session
     */
    public static function annulerResultatsSession($sessionId, $userId, $motif = null)
    {
        try {
            DB::beginTransaction();

            // Récupérer tous les résultats publiés
            $resultats = self::where('session_exam_id', $sessionId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                throw new \Exception('Aucun résultat publié à annuler pour cette session');
            }

            $count = 0;
            foreach ($resultats as $resultat) {
                $resultat->annuler($userId, $motif);
                $count++;
            }

            DB::commit();

            Log::info('Annulation en masse des résultats', [
                'session_id' => $sessionId,
                'resultats_annules' => $count,
                'motif' => $motif,
                'user_id' => $userId
            ]);

            return [
                'success' => true,
                'resultats_annules' => $count,
                'message' => "Succès : {$count} résultats annulés"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation en masse', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'annulation : ' . $e->getMessage()
            ];
        }
    }

    /**
     * CORRECTION CRITIQUE : Méthode pour obtenir les résultats d'un étudiant sans référence à examen.session_id
     */
    public static function getResultatsEtudiant($etudiantId, $sessionId, $statuts = [self::STATUT_PUBLIE])
    {
        try {
            return self::with(['ec', 'ec.ue', 'examen'])
                ->where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->whereIn('statut', $statuts)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des résultats étudiant', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    public static function getResultatsEtudiantSession($etudiantId, $examenId, $sessionId)
    {
        // Déléguer la logique complexe vers Etudiant et garder seulement la requête
        return self::where('etudiant_id', $etudiantId)
            ->where('examen_id', $examenId)
            ->where('session_exam_id', $sessionId)
            ->where('statut', self::STATUT_PUBLIE)
            ->with(['ec', 'ec.ue'])
            ->get();
    }

    public static function comparerResultatsEntreSessions($etudiantId, $examenId, $sessionNormaleId, $sessionRattrapageId)
    {
        $etudiant = Etudiant::find($etudiantId);
        if (!$etudiant) {
            return null;
        }

        // Utiliser les méthodes d'Etudiant pour les décisions
        $decisionNormale = $etudiant->getDecisionPourSession($sessionNormaleId);
        $decisionRattrapage = $etudiant->getDecisionPourSession($sessionRattrapageId);

        // Garder seulement les calculs de moyennes (spécifiques à ResultatFinal)
        $moyenneNormale = self::calculerMoyenneGenerale($etudiantId, $sessionNormaleId);
        $moyenneRattrapage = self::calculerMoyenneGenerale($etudiantId, $sessionRattrapageId);

        return [
            'etudiant_id' => $etudiantId,
            'session_normale' => [
                'session_id' => $sessionNormaleId,
                'moyenne' => $moyenneNormale,
                'decision' => $decisionNormale
            ],
            'session_rattrapage' => [
                'session_id' => $sessionRattrapageId,
                'moyenne' => $moyenneRattrapage,
                'decision' => $decisionRattrapage
            ],
            'progression' => [
                'amelioration_moyenne' => $moyenneRattrapage - $moyenneNormale,
                'meilleure_session' => $moyenneRattrapage > $moyenneNormale ? 'rattrapage' : 'normale'
            ]
        ];
    }


    public static function creerStructuresRattrapage($examenId, $sessionNormaleId, $sessionRattrapageId, $userId)
    {
        try {
            DB::beginTransaction();

            // UTILISER la méthode d'Etudiant au lieu de dupliquer la logique
            $examen = Examen::findOrFail($examenId);

            $etudiantsEligibles = Etudiant::eligiblesRattrapage(
                $examen->niveau_id,
                $examen->parcours_id,
                $sessionNormaleId
            )->get();

            if ($etudiantsEligibles->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun étudiant éligible au rattrapage trouvé.',
                    'statistiques' => ['manchettes_creees' => 0, 'codes_crees' => 0, 'etudiants_traites' => 0]
                ];
            }

            // Récupérer les ECs de l'examen
            $ecs = EC::whereHas('examens', function($query) use ($examenId) {
                $query->where('examens.id', $examenId);
            })->get();

            $manchettesCreees = 0;
            $codesCreés = 0;

            foreach ($etudiantsEligibles as $etudiant) {
                foreach ($ecs as $ec) {
                    // Créer code d'anonymat si nécessaire
                    $codeExistant = CodeAnonymat::where('examen_id', $examenId)
                        ->where('ec_id', $ec->id)
                        ->where('code_complet', 'like', "RAT-{$ec->id}-{$etudiant->id}%")
                        ->first();

                    if (!$codeExistant) {
                        $codeAnonymat = CodeAnonymat::create([
                            'examen_id' => $examenId,
                            'ec_id' => $ec->id,
                            'code_complet' => "RAT-{$ec->id}-{$etudiant->id}-" . now()->format('His'),
                            'sequence' => $etudiant->id * 1000 + $ec->id,
                        ]);
                        $codesCreés++;
                    } else {
                        $codeAnonymat = $codeExistant;
                    }

                    // Créer manchette si nécessaire
                    $manchetteExiste = Manchette::where('examen_id', $examenId)
                        ->where('session_exam_id', $sessionRattrapageId)
                        ->where('etudiant_id', $etudiant->id)
                        ->where('code_anonymat_id', $codeAnonymat->id)
                        ->exists();

                    if (!$manchetteExiste) {
                        Manchette::create([
                            'examen_id' => $examenId,
                            'session_exam_id' => $sessionRattrapageId,
                            'etudiant_id' => $etudiant->id,
                            'code_anonymat_id' => $codeAnonymat->id,
                            'saisie_par' => $userId,
                            'date_saisie' => now()
                        ]);
                        $manchettesCreees++;
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Structures créées pour {$etudiantsEligibles->count()} étudiants éligibles.",
                'statistiques' => [
                    'manchettes_creees' => $manchettesCreees,
                    'codes_crees' => $codesCreés,
                    'etudiants_traites' => $etudiantsEligibles->count(),
                    'ecs_traitees' => $ecs->count()
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création structures rattrapage', [
                'examen_id' => $examenId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
                'statistiques' => ['manchettes_creees' => 0, 'codes_crees' => 0, 'etudiants_traites' => 0]
            ];
        }
    }


    /**
     * Applique la meilleure note entre les deux sessions pour un étudiant
     */
    public static function appliquerMeilleuresNotes($etudiantId, $examenId, $sessionNormaleId, $sessionRattrapageId)
    {
        try {
            DB::beginTransaction();

            $resultatsNormale = self::getResultatsEtudiantSession($etudiantId, $examenId, $sessionNormaleId);
            $resultatsRattrapage = self::getResultatsEtudiantSession($etudiantId, $examenId, $sessionRattrapageId);

            $meilleuresNotes = [];
            $notesModifiees = 0;

            // Grouper par EC pour comparer
            foreach ($resultatsNormale->groupBy('ec_id') as $ecId => $resultatsEc) {
                $noteNormale = $resultatsEc->first()->note ?? 0;
                $noteRattrapage = $resultatsRattrapage->where('ec_id', $ecId)->first()?->note ?? 0;

                // Garder la meilleure note
                $meilleureNote = max($noteNormale, $noteRattrapage);
                $meilleureSession = $noteRattrapage > $noteNormale ? 'rattrapage' : 'normale';

                $meilleuresNotes[$ecId] = [
                    'note_finale' => $meilleureNote,
                    'session_origine' => $meilleureSession,
                    'note_normale' => $noteNormale,
                    'note_rattrapage' => $noteRattrapage
                ];

                // Mettre à jour le résultat final avec la meilleure note
                // (On garde généralement le résultat de rattrapage comme référence finale)
                $resultatFinal = $resultatsRattrapage->where('ec_id', $ecId)->first();
                if ($resultatFinal && $resultatFinal->note != $meilleureNote) {
                    $resultatFinal->note = $meilleureNote;
                    $resultatFinal->modifie_par = Auth::id();

                    // Ajouter dans l'historique la fusion des notes
                    $historique = $resultatFinal->status_history ?? [];
                    $historique[] = [
                        'type_action' => 'fusion_meilleures_notes',
                        'note_normale' => $noteNormale,
                        'note_rattrapage' => $noteRattrapage,
                        'note_finale' => $meilleureNote,
                        'session_origine' => $meilleureSession,
                        'user_id' => Auth::id(),
                        'date_action' => now()->toDateTimeString()
                    ];
                    $resultatFinal->status_history = $historique;

                    $resultatFinal->save();
                    $notesModifiees++;
                }
            }

            // Recalculer la décision finale avec les meilleures notes
            $nouvelleMoyenne = self::calculerMoyenneGenerale($etudiantId, $sessionRattrapageId);
            $nouvelleDecision = self::determinerDecisionRattrapage($etudiantId, $sessionRattrapageId);

            // Mettre à jour tous les résultats de rattrapage avec la nouvelle décision
            self::where('etudiant_id', $etudiantId)
                ->where('examen_id', $examenId)
                ->where('session_exam_id', $sessionRattrapageId)
                ->update(['decision' => $nouvelleDecision]);

            DB::commit();

            Log::info('Meilleures notes appliquées', [
                'etudiant_id' => $etudiantId,
                'examen_id' => $examenId,
                'notes_modifiees' => $notesModifiees,
                'nouvelle_moyenne' => $nouvelleMoyenne,
                'nouvelle_decision' => $nouvelleDecision,
                'meilleures_notes' => $meilleuresNotes
            ]);

            return [
                'success' => true,
                'notes_modifiees' => $notesModifiees,
                'nouvelle_moyenne' => $nouvelleMoyenne,
                'nouvelle_decision' => $nouvelleDecision,
                'meilleures_notes' => $meilleuresNotes
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'application des meilleures notes', [
                'etudiant_id' => $etudiantId,
                'examen_id' => $examenId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    /**
     * Obtient les étudiants éligibles au rattrapage depuis la session normale
     */
    public static function getEtudiantsEligiblesRattrapage($examenId, $sessionNormaleId)
    {
        try {
            // Récupérer tous les étudiants ayant des résultats en session normale
            $etudiantsAvecResultats = self::where('examen_id', $examenId)
                ->where('session_exam_id', $sessionNormaleId)
                ->where('statut', self::STATUT_PUBLIE)
                ->with('etudiant')
                ->get()
                ->groupBy('etudiant_id');

            $etudiantsEligibles = [];

            foreach ($etudiantsAvecResultats as $etudiantId => $resultats) {
                $etudiant = $resultats->first()->etudiant;
                if (!$etudiant || !$etudiant->is_active) {
                    continue;
                }

                // Calculer la moyenne générale pour cet étudiant
                $moyenneGenerale = self::calculerMoyenneGenerale($etudiantId, $sessionNormaleId);

                // Éligible si moyenne < 10
                if ($moyenneGenerale < 10) {
                    $etudiantsEligibles[] = [
                        'etudiant_id' => $etudiantId,
                        'etudiant' => $etudiant,
                        'moyenne_normale' => $moyenneGenerale,
                        'decision_normale' => self::determinerDecisionPremiereSession($etudiantId, $sessionNormaleId),
                        'nb_resultats' => $resultats->count(),
                        'notes_eliminatoires' => $resultats->where('note', 0)->count()
                    ];
                }
            }

            // Trier par moyenne croissante (les plus en difficulté en premier)
            usort($etudiantsEligibles, function($a, $b) {
                return $a['moyenne_normale'] <=> $b['moyenne_normale'];
            });

            Log::info('Étudiants éligibles au rattrapage calculés', [
                'examen_id' => $examenId,
                'session_normale_id' => $sessionNormaleId,
                'total_eligibles' => count($etudiantsEligibles),
                'moyennes' => array_column($etudiantsEligibles, 'moyenne_normale')
            ]);

            return $etudiantsEligibles;

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des étudiants éligibles au rattrapage', [
                'examen_id' => $examenId,
                'session_normale_id' => $sessionNormaleId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Scope pour récupérer les résultats d'une session spécifique
     */
    public function scopePourSession($query, $sessionId)
    {
        return $query->where('session_exam_id', $sessionId);
    }

    /**
     * Scope pour récupérer les résultats entre deux sessions
     */
    public function scopeEntreSessions($query, $sessionIds)
    {
        return $query->whereIn('session_exam_id', $sessionIds);
    }

    /**
     * Scope pour les étudiants ayant des résultats dans plusieurs sessions
     */
    public function scopeEtudiantsMultiSessions($query, $examenId, $sessionIds)
    {
        return $query->where('examen_id', $examenId)
            ->whereIn('session_exam_id', $sessionIds)
            ->select('etudiant_id')
            ->groupBy('etudiant_id')
            ->havingRaw('COUNT(DISTINCT session_exam_id) > 1');
    }


    /**
     * NOUVELLES MÉTHODES à ajouter dans votre modèle ResultatFinal
     * Logique exacte faculté de médecine
     */

    /**
     * MÉTHODE MISE À JOUR : Calcule la moyenne d'une UE selon logique médecine
     *
     * @param int $etudiantId
     * @param int $ueId
     * @param int $sessionId
     * @return float|null
     */
    public static function calculerMoyenneUE_LogiqueMedecine($etudiantId, $ueId, $sessionId)
    {
        try {
            // Récupérer tous les résultats de l'UE pour cet étudiant
            $resultats = self::where('session_exam_id', $sessionId)
                ->whereHas('ec', function($q) use ($ueId) {
                    $q->where('ue_id', $ueId);
                })
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                return null;
            }

            // LOGIQUE MÉDECINE 1 : Vérifier s'il y a une note éliminatoire (0)
            $hasNoteZero = $resultats->contains('note', 0);

            if ($hasNoteZero) {
                // LOGIQUE MÉDECINE 2 : Si note 0, UE éliminée = moyenne 0
                return 0;
            }

            // LOGIQUE MÉDECINE 3 : Moyenne UE = somme notes / nombre EC
            return round($resultats->avg('note'), 2);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul de la moyenne UE médecine', [
                'etudiant_id' => $etudiantId,
                'ue_id' => $ueId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * MÉTHODE MISE À JOUR : Vérifie si un étudiant valide une UE selon logique médecine
     *
     * @param int $etudiantId
     * @param int $ueId
     * @param int $sessionId
     * @return bool
     */
    public static function etudiantValideUE_LogiqueMedecine($etudiantId, $ueId, $sessionId)
    {
        try {
            $moyenneUE = self::calculerMoyenneUE_LogiqueMedecine($etudiantId, $ueId, $sessionId);

            if ($moyenneUE === null) {
                return false;
            }

            // LOGIQUE MÉDECINE : UE validée si moyenne >= 10 ET pas de note 0
            return $moyenneUE >= 10 && $moyenneUE > 0;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de validation UE médecine', [
                'etudiant_id' => $etudiantId,
                'ue_id' => $ueId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * MÉTHODE MISE À JOUR : Calcule la moyenne générale selon logique médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @return float
     */
    public static function calculerMoyenneGenerale_LogiqueMedecine($etudiantId, $sessionId)
    {
        try {
            $resultats = self::with('ec.ue')
                ->where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                return 0;
            }

            // LOGIQUE MÉDECINE : Grouper par UE
            $resultatsParUE = $resultats->groupBy('ec.ue_id');
            $moyennesUE = [];
            $hasNoteEliminatoire = false;

            foreach ($resultatsParUE as $ueId => $notesUE) {
                // LOGIQUE MÉDECINE 1 : Vérifier s'il y a une note éliminatoire (0) dans cette UE
                $hasNoteZeroInUE = $notesUE->contains('note', 0);

                if ($hasNoteZeroInUE) {
                    $hasNoteEliminatoire = true;
                    $moyennesUE[] = 0; // UE éliminée
                } else {
                    // LOGIQUE MÉDECINE 2 : Moyenne UE = somme notes / nombre EC
                    $moyenneUE = $notesUE->avg('note');
                    $moyennesUE[] = $moyenneUE;
                }
            }

            // LOGIQUE MÉDECINE 3 : Moyenne générale = somme moyennes UE / nombre UE
            $moyenneGenerale = count($moyennesUE) > 0 ?
                array_sum($moyennesUE) / count($moyennesUE) : 0;

            // LOGIQUE MÉDECINE 4 : Si note éliminatoire, moyenne générale = 0
            if ($hasNoteEliminatoire) {
                $moyenneGenerale = 0;
            }

            return round($moyenneGenerale, 2);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul de moyenne générale médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * MÉTHODE MISE À JOUR : Détermine automatiquement la décision première session selon logique médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @return string
     */
    public static function determinerDecisionPremiereSession_LogiqueMedecine($etudiantId, $sessionId)
    {
        try {
            // Calculer les crédits validés selon logique médecine
            $creditsValides = self::calculerCreditsValides_LogiqueMedecine($etudiantId, $sessionId);

            // LOGIQUE MÉDECINE SESSION 1 : Si 60 crédits → Admis, sinon → Rattrapage
            return $creditsValides >= 60 ?
                self::DECISION_ADMIS :
                self::DECISION_RATTRAPAGE;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la détermination de la décision première session médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return self::DECISION_RATTRAPAGE;
        }
    }

    /**
     * MÉTHODE MISE À JOUR : Détermine automatiquement la décision rattrapage selon logique médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @return string
     */
    public static function determinerDecisionRattrapage_LogiqueMedecine($etudiantId, $sessionId)
    {
        try {
            // Calculer les crédits validés selon logique médecine
            $creditsValides = self::calculerCreditsValides_LogiqueMedecine($etudiantId, $sessionId);

            // Vérifier s'il y a des notes éliminatoires
            $hasNoteEliminatoire = self::hasNoteEliminatoire_LogiqueMedecine($etudiantId, $sessionId);

            // LOGIQUE MÉDECINE SESSION 2 :
            // 1. Si note éliminatoire → Exclu
            // 2. Si >= 40 crédits → Admis
            // 3. Sinon → Redoublant
            if ($hasNoteEliminatoire) {
                return self::DECISION_EXCLUS;
            }

            return $creditsValides >= 40 ?
                self::DECISION_ADMIS :
                self::DECISION_REDOUBLANT;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la détermination de la décision rattrapage médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return self::DECISION_REDOUBLANT;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Calcule les crédits validés selon logique médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @return int
     */
    public static function calculerCreditsValides_LogiqueMedecine($etudiantId, $sessionId)
    {
        try {
            $resultats = self::with('ec.ue')
                ->where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                return 0;
            }

            // Grouper par UE pour calculer les crédits selon logique médecine
            $resultatsParUE = $resultats->groupBy('ec.ue_id');
            $creditsValides = 0;

            foreach ($resultatsParUE as $ueId => $notesUE) {
                $ue = $notesUE->first()->ec->ue;

                // LOGIQUE MÉDECINE : Vérifier si UE validée
                if (self::etudiantValideUE_LogiqueMedecine($etudiantId, $ueId, $sessionId)) {
                    $creditsValides += $ue->credits ?? 0;
                }
            }

            return $creditsValides;

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des crédits validés médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Vérifie s'il y a des notes éliminatoires selon logique médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @return bool
     */
    public static function hasNoteEliminatoire_LogiqueMedecine($etudiantId, $sessionId)
    {
        try {
            return self::where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->where('note', 0)
                ->exists();

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification des notes éliminatoires médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Applique les décisions automatiquement selon logique médecine
     *
     * @param int $sessionId
     * @return array
     */
    public static function appliquerDecisionsSession_LogiqueMedecine($sessionId)
    {
        try {
            DB::beginTransaction();

            $session = SessionExam::findOrFail($sessionId);

            // Récupérer tous les étudiants distincts de cette session
            $etudiantsIds = self::where('session_exam_id', $sessionId)
                ->where('statut', self::STATUT_PUBLIE)
                ->distinct('etudiant_id')
                ->pluck('etudiant_id');

            $stats = [
                'total_etudiants' => $etudiantsIds->count(),
                'decisions' => [
                    'admis' => 0,
                    'rattrapage' => 0,
                    'redoublant' => 0,
                    'exclus' => 0
                ],
                'erreurs' => []
            ];

            foreach ($etudiantsIds as $etudiantId) {
                try {
                    // Déterminer la décision selon logique médecine
                    if ($session->type === 'Rattrapage') {
                        $decision = self::determinerDecisionRattrapage_LogiqueMedecine($etudiantId, $sessionId);
                    } else {
                        $decision = self::determinerDecisionPremiereSession_LogiqueMedecine($etudiantId, $sessionId);
                    }

                    // Appliquer la décision à tous les résultats de l'étudiant
                    $nbUpdated = self::where('session_exam_id', $sessionId)
                        ->where('etudiant_id', $etudiantId)
                        ->where('statut', self::STATUT_PUBLIE)
                        ->update(['decision' => $decision]);

                    if ($nbUpdated > 0) {
                        $stats['decisions'][$decision]++;

                        Log::info('Décision médecine appliquée', [
                            'etudiant_id' => $etudiantId,
                            'session_id' => $sessionId,
                            'session_type' => $session->type,
                            'decision' => $decision,
                            'resultats_mis_a_jour' => $nbUpdated
                        ]);
                    }

                } catch (\Exception $e) {
                    $stats['erreurs'][] = "Erreur étudiant {$etudiantId}: " . $e->getMessage();
                    Log::error('Erreur application décision médecine', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            Log::info('Application des décisions médecine terminée', [
                'session_id' => $sessionId,
                'session_type' => $session->type,
                'stats' => $stats
            ]);

            return [
                'success' => empty($stats['erreurs']),
                'message' => empty($stats['erreurs'])
                    ? "Décisions appliquées selon logique médecine pour {$stats['total_etudiants']} étudiants."
                    : "Décisions appliquées avec " . count($stats['erreurs']) . " erreurs.",
                'statistiques' => $stats
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'application des décisions médecine', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'application des décisions : ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }

    /**
     * NOUVELLE MÉTHODE : Obtient un rapport détaillé pour un étudiant selon logique médecine
     *
     * @param int $etudiantId
     * @param int $sessionId
     * @return array
     */
    public static function genererRapportEtudiant_LogiqueMedecine($etudiantId, $sessionId)
    {
        try {
            $etudiant = Etudiant::findOrFail($etudiantId);
            $session = SessionExam::findOrFail($sessionId);

            $resultats = self::with('ec.ue')
                ->where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', self::STATUT_PUBLIE)
                ->get();

            if ($resultats->isEmpty()) {
                throw new \Exception('Aucun résultat trouvé pour cet étudiant');
            }

            // Analyser selon logique médecine
            $resultatsParUE = $resultats->groupBy('ec.ue_id');
            $resultatsUE = [];
            $creditsValides = 0;
            $totalCredits = 0;
            $moyennesUE = [];
            $hasNoteEliminatoire = false;
            $uesEliminees = [];

            foreach ($resultatsParUE as $ueId => $notesUE) {
                $ue = $notesUE->first()->ec->ue;
                $totalCredits += $ue->credits ?? 0;

                // LOGIQUE MÉDECINE : Analyser l'UE
                $notes = $notesUE->pluck('note')->toArray();
                $hasNoteZeroInUE = in_array(0, $notes);

                if ($hasNoteZeroInUE) {
                    $hasNoteEliminatoire = true;
                    $uesEliminees[] = $ue->nom;
                    $moyenneUE = 0;
                    $ueValidee = false;
                } else {
                    $moyenneUE = round(array_sum($notes) / count($notes), 2);
                    $ueValidee = $moyenneUE >= 10;

                    if ($ueValidee) {
                        $creditsValides += $ue->credits ?? 0;
                    }

                    $moyennesUE[] = $moyenneUE;
                }

                $resultatsUE[] = [
                    'ue' => $ue,
                    'notes_ec' => $notesUE->map(function($resultat) {
                        return [
                            'ec' => $resultat->ec,
                            'note' => $resultat->note,
                            'est_eliminatoire' => $resultat->note == 0
                        ];
                    }),
                    'moyenne_ue' => $moyenneUE,
                    'validee' => $ueValidee,
                    'eliminee' => $hasNoteZeroInUE,
                    'credits' => $ue->credits ?? 0
                ];
            }

            // LOGIQUE MÉDECINE : Moyenne générale
            $moyenneGenerale = count($moyennesUE) > 0 ?
                round(array_sum($moyennesUE) / count($moyennesUE), 2) : 0;

            if ($hasNoteEliminatoire) {
                $moyenneGenerale = 0;
            }

            // LOGIQUE MÉDECINE : Décision
            if ($session->type === 'Rattrapage') {
                $decision = self::determinerDecisionRattrapage_LogiqueMedecine($etudiantId, $sessionId);
            } else {
                $decision = self::determinerDecisionPremiereSession_LogiqueMedecine($etudiantId, $sessionId);
            }

            $rapport = [
                'informations_generales' => [
                    'etudiant' => $etudiant,
                    'session' => $session,
                    'date_generation' => now()->format('d/m/Y H:i:s')
                ],
                'resultats_detailles' => $resultatsUE,
                'synthese' => [
                    'moyenne_generale' => $moyenneGenerale,
                    'credits_valides' => $creditsValides,
                    'total_credits' => $totalCredits,
                    'pourcentage_credits' => $totalCredits > 0 ?
                        round(($creditsValides / 60) * 100, 1) : 0,
                    'has_note_eliminatoire' => $hasNoteEliminatoire,
                    'ues_eliminees' => $uesEliminees,
                    'decision' => $decision
                ],
                'observations_medecine' => self::genererObservationsLogiqueMedecine(
                    $moyenneGenerale,
                    $creditsValides,
                    $totalCredits,
                    $hasNoteEliminatoire,
                    $decision,
                    $session->type
                ),
                'methode_calcul' => [
                    'moyenne_ue' => 'Somme des notes EC / Nombre d\'EC',
                    'validation_ue' => 'Moyenne UE >= 10 ET aucune note = 0',
                    'moyenne_generale' => 'Somme des moyennes UE / Nombre d\'UE',
                    'note_eliminatoire' => 'Une note de 0 élimine toute l\'UE',
                    'decision_session1' => 'Admis si 60 crédits, sinon Rattrapage',
                    'decision_session2' => 'Exclu si note 0, Admis si >= 40 crédits, sinon Redoublant'
                ]
            ];

            return $rapport;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du rapport étudiant médecine', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Génère des observations selon logique médecine
     *
     * @param float $moyenneGenerale
     * @param int $creditsValides
     * @param int $totalCredits
     * @param bool $hasNoteEliminatoire
     * @param string $decision
     * @param string $typeSession
     * @return array
     */
    private static function genererObservationsLogiqueMedecine($moyenneGenerale, $creditsValides, $totalCredits, $hasNoteEliminatoire, $decision, $typeSession)
    {
        $observations = [];

        // Observation sur la moyenne selon logique médecine
        if ($hasNoteEliminatoire) {
            $observations[] = "⚠️ ATTENTION : Note éliminatoire (0) détectée - Moyenne générale automatiquement fixée à 0 selon la règle médecine.";
        } else {
            if ($moyenneGenerale >= 16) {
                $observations[] = "✅ Excellente performance académique avec une moyenne générale de {$moyenneGenerale}/20.";
            } elseif ($moyenneGenerale >= 14) {
                $observations[] = "✅ Très bonne performance académique avec une moyenne générale de {$moyenneGenerale}/20.";
            } elseif ($moyenneGenerale >= 12) {
                $observations[] = "✅ Bonne performance académique avec une moyenne générale de {$moyenneGenerale}/20.";
            } elseif ($moyenneGenerale >= 10) {
                $observations[] = "✅ Performance satisfaisante avec une moyenne générale de {$moyenneGenerale}/20.";
            } else {
                $observations[] = "❌ Performance insuffisante avec une moyenne générale de {$moyenneGenerale}/20.";
            }
        }

        // Observation sur les crédits selon logique médecine
        $pourcentage = $totalCredits > 0 ? round(($creditsValides / 60) * 100, 1) : 0;
        $observations[] = "L'étudiant a validé {$creditsValides} crédits sur les 60 requis ({$pourcentage}%).";

        // Observation sur la décision selon logique médecine
        switch ($decision) {
            case self::DECISION_ADMIS:
                if ($typeSession === 'Normale') {
                    $observations[] = "🎉 ADMIS EN 1ÈRE SESSION : L'étudiant a validé tous les 60 crédits requis selon la logique médecine.";
                } else {
                    $observations[] = "🎉 ADMIS EN 2ÈME SESSION : L'étudiant a atteint le minimum de 40 crédits requis en rattrapage selon la logique médecine.";
                }
                break;

            case self::DECISION_RATTRAPAGE:
                $observations[] = "⚠️ AUTORISÉ AU RATTRAPAGE : L'étudiant n'a pas atteint les 60 crédits requis et doit passer en session de rattrapage selon la logique médecine.";
                break;

            case self::DECISION_REDOUBLANT:
                $observations[] = "❌ REDOUBLANT : L'étudiant n'a pas atteint le minimum de 40 crédits requis en session de rattrapage selon la logique médecine.";
                break;

            case self::DECISION_EXCLUS:
                $observations[] = "🚫 EXCLU : L'étudiant a une note éliminatoire (0) en session de rattrapage selon la logique médecine.";
                break;
        }

        // Observation spécifique au type de session
        if ($typeSession === 'Rattrapage') {
            if ($hasNoteEliminatoire) {
                $observations[] = "📋 RÈGLE MÉDECINE : Une note de 0 en session de rattrapage entraîne automatiquement l'exclusion.";
            } else {
                $observations[] = "📋 RÈGLE MÉDECINE : En session de rattrapage, il faut minimum 40 crédits pour être admis.";
            }
        } else {
            $observations[] = "📋 RÈGLE MÉDECINE : En première session, il faut 60 crédits pour être admis, sinon passage en rattrapage.";
        }

        return $observations;
    }

    /**
     * NOUVELLE MÉTHODE : Valide la cohérence des calculs médecine pour une session
     *
     * @param int $sessionId
     * @return array
     */
    public static function validerCoherenceSession_LogiqueMedecine($sessionId)
    {
        $erreurs = [];
        $warnings = [];

        try {
            $session = SessionExam::findOrFail($sessionId);

            // Récupérer tous les étudiants de la session
            $etudiantsIds = self::where('session_exam_id', $sessionId)
                ->where('statut', self::STATUT_PUBLIE)
                ->distinct('etudiant_id')
                ->pluck('etudiant_id');

            foreach ($etudiantsIds as $etudiantId) {
                try {
                    // Vérifier la cohérence de la moyenne générale
                    $moyenneCalculee = self::calculerMoyenneGenerale_LogiqueMedecine($etudiantId, $sessionId);
                    $moyennesDB = self::where('session_exam_id', $sessionId)
                        ->where('etudiant_id', $etudiantId)
                        ->where('statut', self::STATUT_PUBLIE)
                        ->with('ec.ue')
                        ->get()
                        ->groupBy('ec.ue_id')
                        ->map(function($notesUE) {
                            $hasZero = $notesUE->contains('note', 0);
                            return $hasZero ? 0 : round($notesUE->avg('note'), 2);
                        });

                    $moyenneDBCalculee = $moyennesDB->count() > 0 ?
                        round($moyennesDB->sum() / $moyennesDB->count(), 2) : 0;

                    if (abs($moyenneCalculee - $moyenneDBCalculee) > 0.01) {
                        $erreurs[] = "Étudiant {$etudiantId}: Moyenne générale incohérente (calculée: {$moyenneCalculee}, DB: {$moyenneDBCalculee})";
                    }

                    // Vérifier la cohérence de la décision
                    $decisionCalculee = $session->type === 'Rattrapage' ?
                        self::determinerDecisionRattrapage_LogiqueMedecine($etudiantId, $sessionId) :
                        self::determinerDecisionPremiereSession_LogiqueMedecine($etudiantId, $sessionId);

                    $decisionsDB = self::where('session_exam_id', $sessionId)
                        ->where('etudiant_id', $etudiantId)
                        ->where('statut', self::STATUT_PUBLIE)
                        ->pluck('decision')
                        ->unique();

                    if ($decisionsDB->count() > 1) {
                        $erreurs[] = "Étudiant {$etudiantId}: Décisions incohérentes en base " . $decisionsDB->implode(', ');
                    } elseif ($decisionsDB->first() !== $decisionCalculee) {
                        $erreurs[] = "Étudiant {$etudiantId}: Décision incorrecte (DB: {$decisionsDB->first()}, attendue: {$decisionCalculee})";
                    }

                    // Vérifier la cohérence des crédits
                    $creditsCalcules = self::calculerCreditsValides_LogiqueMedecine($etudiantId, $sessionId);
                    // Note: On ne peut pas vérifier directement les crédits en DB car ils ne sont pas stockés individuellement

                } catch (\Exception $e) {
                    $erreurs[] = "Erreur validation étudiant {$etudiantId}: " . $e->getMessage();
                }
            }

        } catch (\Exception $e) {
            $erreurs[] = "Erreur globale validation: " . $e->getMessage();
        }

        return [
            'session_id' => $sessionId,
            'etudiants_verifies' => $etudiantsIds->count(),
            'erreurs' => $erreurs,
            'warnings' => $warnings,
            'est_coherent' => empty($erreurs),
            'date_verification' => now()->format('Y-m-d H:i:s')
        ];
    }

}
