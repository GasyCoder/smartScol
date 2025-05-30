<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        'code_anonymat_id',
        'ec_id',
        'note',
        'genere_par',
        'modifie_par',
        'statut',
        'date_publication',
        'decision',
        'deliberation_id',
        'fusion_id',
        'date_fusion',
        'hash_verification',
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'date_publication' => 'datetime',
        'date_fusion' => 'datetime',
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
     * Relation avec l'historique
     */
    public function historique()
    {
        return $this->hasMany(ResultatFinalHistorique::class)->ordreChronologique();
    }

    /**
     * Relation pour obtenir la dernière action d'annulation
     */
    public function derniereAnnulation()
    {
        return $this->hasOne(ResultatFinalHistorique::class)
            ->where('type_action', ResultatFinalHistorique::TYPE_ANNULATION)
            ->latest('date_action');
    }

    /**
     * Relation pour obtenir la dernière action de réactivation
     */
    public function derniereReactivation()
    {
        return $this->hasOne(ResultatFinalHistorique::class)
            ->where('type_action', ResultatFinalHistorique::TYPE_REACTIVATION)
            ->latest('date_action');
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
     * Change le statut du résultat final avec historique
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
            $this->save();

            // Créer l'entrée d'historique
            ResultatFinalHistorique::creerEntreeChangementStatut(
                $this->id,
                $ancienStatut,
                $nouveauStatut,
                $userId,
                [
                    'avec_deliberation' => $avecDeliberation,
                    'decision' => $decision
                ]
            );

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
     * Annule le résultat avec motif
     */
    public function annuler($userId, $motif = null)
    {
        if ($this->statut !== self::STATUT_PUBLIE) {
            throw new \Exception("Seuls les résultats publiés peuvent être annulés");
        }

        try {
            DB::beginTransaction();

            // Mettre à jour le statut
            $this->statut = self::STATUT_ANNULE;
            $this->modifie_par = $userId;
            $this->save();

            // Créer l'entrée d'historique d'annulation
            ResultatFinalHistorique::creerEntreeAnnulation($this->id, $userId, $motif);

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
     * Réactive le résultat annulé
     */
    public function reactiver($userId)
    {
        if ($this->statut !== self::STATUT_ANNULE) {
            throw new \Exception("Seuls les résultats annulés peuvent être réactivés");
        }

        try {
            DB::beginTransaction();

            // Mettre à jour le statut
            $this->statut = self::STATUT_EN_ATTENTE;
            $this->modifie_par = $userId;
            $this->save();

            // Créer l'entrée d'historique de réactivation
            ResultatFinalHistorique::creerEntreeReactivation($this->id, $userId);

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
     * Obtient le motif de la dernière annulation
     */
    public function getMotifAnnulationAttribute()
    {
        return $this->derniereAnnulation?->motif;
    }

    /**
     * Obtient la date de la dernière annulation
     */
    public function getDateAnnulationAttribute()
    {
        return $this->derniereAnnulation?->date_action;
    }

    /**
     * Obtient l'utilisateur qui a annulé
     */
    public function getAnnuleParAttribute()
    {
        return $this->derniereAnnulation?->user_id;
    }

    /**
     * Obtient la date de la dernière réactivation
     */
    public function getDateReactivationAttribute()
    {
        return $this->derniereReactivation?->date_action;
    }

    /**
     * Obtient l'utilisateur qui a réactivé
     */
    public function getReactiveParAttribute()
    {
        return $this->derniereReactivation?->user_id;
    }

    /**
     * Obtient l'historique complet formaté
     */
    public function getStatusHistoryAttribute()
    {
        return $this->historique()
            ->where('type_action', ResultatFinalHistorique::TYPE_CHANGEMENT_STATUT)
            ->get()
            ->map(function ($entry) {
                $donnees = $entry->donnees_supplementaires ?? [];
                return [
                    'de' => $entry->statut_precedent,
                    'vers' => $entry->statut_nouveau,
                    'user_id' => $entry->user_id,
                    'date' => $entry->date_action->toDateTimeString(),
                    'avec_deliberation' => $donnees['avec_deliberation'] ?? false,
                    'decision' => $donnees['decision'] ?? null,
                ];
            })
            ->toArray();
    }

    /**
     * Vérifie si une délibération est requise
     */
    public function requiresDeliberation()
    {
        $session = $this->examen->session;
        $niveau = $this->examen->niveau;
        if ($niveau->is_concours) {
            return false;
        }
        return $session && $session->isRattrapage();
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
        try {
            $calculService = new CalculAcademiqueService();
            $resultats = $calculService->calculerResultatsComplets($etudiantId, $sessionId, true);
            foreach ($resultats['resultats_ue'] as $resultatUE) {
                if ($resultatUE['ue_id'] == $ueId) {
                    return $resultatUE['moyenne'];
                }
            }
            return null;
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
     * Vérifie si un étudiant valide une UE selon les règles métier
     */
    public static function etudiantValideUE($etudiantId, $ueId, $sessionId)
    {
        try {
            $calculService = new CalculAcademiqueService();
            $resultats = $calculService->calculerResultatsComplets($etudiantId, $sessionId, true);
            foreach ($resultats['resultats_ue'] as $resultatUE) {
                if ($resultatUE['ue_id'] == $ueId) {
                    return $resultatUE['validee'];
                }
            }
            return false;
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
     * Détermine automatiquement la décision pour première session
     */
    public static function determinerDecisionPremiereSession($etudiantId, $sessionId)
    {
        try {
            $calculService = new CalculAcademiqueService();
            $resultats = $calculService->calculerResultatsComplets($etudiantId, $sessionId, true);

            // Vérification des notes éliminatoires (0)
            if ($resultats['synthese']['a_note_eliminatoire']) {
                Log::info('Décision Rattrapage - Note éliminatoire détectée', [
                    'etudiant_id' => $etudiantId,
                    'session_id' => $sessionId
                ]);
                return self::DECISION_RATTRAPAGE;
            }

            // Calculer la moyenne générale de toutes les UEs
            $moyenneGenerale = 0;
            $totalUEs = 0;
            foreach ($resultats['resultats_ue'] as $ue) {
                $moyenneGenerale += $ue['moyenne'];
                $totalUEs++;
            }
            $moyenneGenerale = $totalUEs > 0 ? $moyenneGenerale / $totalUEs : 0;

            // Vérifier si toutes les UEs sont validées (moyenne >= 10)
            $toutesUEsValidees = true;
            foreach ($resultats['resultats_ue'] as $ue) {
                if ($ue['moyenne'] < 10) {
                    $toutesUEsValidees = false;
                    break;
                }
            }

            if ($toutesUEsValidees) {
                Log::info('Décision Admis - Toutes UEs validées', [
                    'etudiant_id' => $etudiantId,
                    'session_id' => $sessionId,
                    'moyenne_generale' => $moyenneGenerale
                ]);
                return self::DECISION_ADMIS;
            }

            // Si au moins une UE n'est pas validée, l'étudiant va en rattrapage
            Log::info('Décision Rattrapage - UEs non validées', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'moyenne_generale' => $moyenneGenerale
            ]);
            return self::DECISION_RATTRAPAGE;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la détermination de la décision', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return self::DECISION_RATTRAPAGE;
        }
    }

    /**
     * Détermine automatiquement la décision pour session rattrapage
     */
    public static function determinerDecisionRattrapage($etudiantId, $sessionId)
    {
        try {
            $calculService = new CalculAcademiqueService();
            $resultats = $calculService->calculerResultatsComplets($etudiantId, $sessionId, true);
            $creditsValides = $resultats['synthese']['credits_valides'];
            $creditsRequis = $resultats['synthese']['credits_requis'];

            if ($resultats['synthese']['a_note_eliminatoire']) {
                return self::DECISION_EXCLUS;
            }

            if ($creditsValides >= $creditsRequis) {
                return self::DECISION_ADMIS;
            }

            return self::DECISION_REDOUBLANT;
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
     * Calcule la moyenne générale d'un étudiant pour une session
     */
    public static function calculerMoyenneGenerale($etudiantId, $sessionId)
    {
        try {
            $calculService = new CalculAcademiqueService();
            $resultats = $calculService->calculerResultatsComplets($etudiantId, $sessionId, true);
            return $resultats['synthese']['moyenne_generale'];
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
     * Récupère les résultats académiques complets pour cet étudiant
     */
    public function getResultatsAcademiquesComplets()
    {
        if (!$this->etudiant_id || !$this->examen) {
            return null;
        }

        try {
            $calculService = new CalculAcademiqueService();
            return $calculService->calculerResultatsComplets($this->etudiant_id, $this->examen->session_id, true);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des résultats académiques', [
                'resultat_final_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Calcule et met à jour la décision académique
     */
    public function calculerEtAppliquerDecision()
    {
        if (!$this->etudiant_id || !$this->examen) {
            return false;
        }

        try {
            $session = $this->examen->session;
            $decision = $session->isRattrapage()
                ? self::determinerDecisionRattrapage($this->etudiant_id, $this->examen->session_id)
                : self::determinerDecisionPremiereSession($this->etudiant_id, $this->examen->session_id);

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
     * Applique les décisions académiques pour tous les étudiants d'une session
     */
    public static function appliquerDecisionsSession($sessionId)
    {
        try {
            $session = SessionExam::findOrFail($sessionId);
            $isRattrapage = $session->isRattrapage();

            $etudiantsIds = self::whereHas('examen', function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
                ->where('statut', self::STATUT_EN_ATTENTE)
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
                $decision = $isRattrapage
                    ? self::determinerDecisionRattrapage($etudiantId, $sessionId)
                    : self::determinerDecisionPremiereSession($etudiantId, $sessionId);

                self::whereHas('examen', function ($query) use ($sessionId) {
                    $query->where('session_id', $sessionId);
                })
                    ->where('etudiant_id', $etudiantId)
                    ->where('statut', self::STATUT_EN_ATTENTE)
                    ->update(['decision' => $decision]);

                $statistiques[$decision]++;
                $count++;
            }

            Log::info('Décisions académiques appliquées pour la session', [
                'session_id' => $sessionId,
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
     * Génère un rapport académique pour un étudiant
     */
    public static function genererRapportAcademique($etudiantId, $sessionId)
    {
        try {
            $calculService = new CalculAcademiqueService();
            $resultats = $calculService->calculerResultatsComplets($etudiantId, $sessionId, true);
            $etudiant = Etudiant::find($etudiantId);
            $session = SessionExam::find($sessionId);

            $rapport = [
                'informations_generales' => [
                    'etudiant' => $resultats['etudiant'],
                    'session' => $resultats['session'],
                    'date_generation' => now()->format('d/m/Y H:i:s')
                ],
                'resultats_detailles' => $resultats['resultats_ue'],
                'synthese' => $resultats['synthese'],
                'decision_finale' => $resultats['decision'],
                'observations' => self::genererObservations($resultats)
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
     * Génère des observations automatiques basées sur les résultats
     */
    private static function genererObservations($resultats)
    {
        $observations = [];
        $moyenne = $resultats['synthese']['moyenne_generale'];

        if ($moyenne >= 16) {
            $observations[] = "Excellent résultat avec une moyenne générale de {$moyenne}/20.";
        } elseif ($moyenne >= 14) {
            $observations[] = "Très bon résultat avec une moyenne générale de {$moyenne}/20.";
        } elseif ($moyenne >= 12) {
            $observations[] = "Bon résultat avec une moyenne générale de {$moyenne}/20.";
        } elseif ($moyenne >= 10) {
            $observations[] = "Résultat satisfaisant avec une moyenne générale de {$moyenne}/20.";
        } else {
            $observations[] = "Résultat insuffisant avec une moyenne générale de {$moyenne}/20.";
        }

        $creditsValides = $resultats['synthese']['credits_valides'];
        $creditsTotal = $resultats['synthese']['credits_requis'];
        $pourcentage = $resultats['synthese']['pourcentage_credits'];
        $observations[] = "L'étudiant a validé {$creditsValides} crédits sur {$creditsTotal} ({$pourcentage}%).";

        if ($resultats['synthese']['a_note_eliminatoire']) {
            $nbEliminatoires = count($resultats['synthese']['notes_eliminatoires']);
            $observations[] = "Attention : {$nbEliminatoires} UE(s) avec note(s) éliminatoire(s).";
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
        return $query->whereHas('examen.session', function ($q) {
            $q->where('type', 'Normale');
        });
    }

    public function scopeRattrapageSession($query)
    {
        return $query->whereHas('examen.session', function ($q) {
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
        return $query->whereHas('examen.session', function ($q) use ($anneeId) {
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
