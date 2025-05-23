<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Deliberation extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse.
     */
    protected $fillable = [
        'niveau_id',
        'session_id',
        'annee_universitaire_id',
        'date_deliberation',
        'statut',
        'seuil_admission',
        'seuil_rachat',
        'pourcentage_ue_requises',
        'appliquer_regles_auto',
        'observations',
        'decisions_speciales',
        'nombre_admis',
        'nombre_ajournes',
        'nombre_exclus',
        'nombre_rachats',
        'date_finalisation',
        'date_publication',
        'finalise_par'
    ];

    /**
     * Les attributs à convertir.
     */
    protected $casts = [
        'date_deliberation' => 'datetime',
        'date_finalisation' => 'datetime',
        'date_publication' => 'datetime',
        'seuil_admission' => 'decimal:2',
        'seuil_rachat' => 'decimal:2',
        'pourcentage_ue_requises' => 'integer',
        'appliquer_regles_auto' => 'boolean',
        'decisions_speciales' => 'json',
        'nombre_admis' => 'integer',
        'nombre_ajournes' => 'integer',
        'nombre_exclus' => 'integer',
        'nombre_rachats' => 'integer'
    ];

    // Constantes pour les statuts de délibération
    const STATUT_PROGRAMMEE = 'programmee';    // Délibération programmée mais pas encore tenue
    const STATUT_EN_COURS = 'en_cours';        // Délibération en cours de déroulement
    const STATUT_TERMINEE = 'terminee';        // Délibération terminée, décisions prises
    const STATUT_VALIDEE = 'validee';          // Délibération validée et résultats publiés
    const STATUT_ANNULEE = 'annulee';          // Délibération annulée

    // Constantes pour les décisions
    const DECISION_ADMIS = 'admis';
    const DECISION_AJOURNE = 'ajourne';
    const DECISION_ADMIS_CONDITIONNELLEMENT = 'admis_conditionnellement';
    const DECISION_EXCLUS = 'exclus';

    /**
     * Relations avec d'autres modèles.
     */
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function session()
    {
        return $this->belongsTo(SessionExam::class, 'session_id');
    }

    public function anneeUniversitaire()
    {
        return $this->belongsTo(AnneeUniversitaire::class);
    }

    public function presidentJury()
    {
        return $this->belongsTo(User::class, 'president_jury');
    }

    public function finalisePar()
    {
        return $this->belongsTo(User::class, 'finalise_par');
    }

    /**
     * Relation avec les résultats concernés par cette délibération.
     */
    public function resultats()
    {
        return $this->hasMany(Resultat::class);
    }

    /**
     * Relation avec les décisions prises lors de cette délibération.
     */
    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    /**
     * Retourne les libellés des statuts.
     */
    public static function getLibellesStatuts()
    {
        return [
            self::STATUT_PROGRAMMEE => 'Programmée',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_TERMINEE => 'Terminée',
            self::STATUT_VALIDEE => 'Validée',
            self::STATUT_ANNULEE => 'Annulée'
        ];
    }

    /**
     * Retourne les libellés des décisions.
     */
    public static function getLibellesDecisions()
    {
        return [
            self::DECISION_ADMIS => 'Admis',
            self::DECISION_AJOURNE => 'Ajourné',
            self::DECISION_ADMIS_CONDITIONNELLEMENT => 'Admis conditionnellement',
            self::DECISION_EXCLUS => 'Exclu'
        ];
    }

    /**
     * Accesseur pour le libellé du statut.
     */
    public function getLibelleStatutAttribute()
    {
        $libelles = self::getLibellesStatuts();
        return $libelles[$this->statut] ?? 'Statut inconnu';
    }

    /**
     * Vérifie si la délibération peut être démarrée.
     */
    public function verifierPrerequisDeliberation()
    {
        $errors = [];

        // 1. Vérifier que c'est bien une session de rattrapage
        if (!$this->session || !$this->session->isRattrapage()) {
            $errors[] = "La délibération ne peut avoir lieu que pour une session de rattrapage";
        }

        // 2. Vérifier que ce n'est pas PACES (niveau concours)
        if ($this->niveau && $this->niveau->is_concours) {
            $errors[] = "Aucune délibération n'est prévue pour les niveaux de concours";
        }


        // 4. Vérifier que tous les résultats sont saisis
        if (!$this->tousResultatsSaisis()) {
            $errors[] = "Tous les résultats doivent être saisis avant la délibération";
        }

        return [
            'valide' => empty($errors),
            'erreurs' => $errors
        ];
    }

    /**
     * Vérifie si tous les résultats nécessaires sont saisis.
     */
    public function tousResultatsSaisis()
    {
        // Récupérer tous les examens concernés par cette délibération
        $examens = Examen::where('niveau_id', $this->niveau_id)
            ->where('session_id', $this->session_id)
            ->get();

        if ($examens->isEmpty()) {
            return false;
        }

        // Récupérer les étudiants concernés
        $etudiants = $this->getEtudiantsConcernes();

        // Pour chaque examen et chaque étudiant, vérifier que les résultats existent
        foreach ($examens as $examen) {
            foreach ($etudiants as $etudiantId) {
                $resultatsCount = Resultat::where('examen_id', $examen->id)
                    ->where('etudiant_id', $etudiantId)
                    ->where('statut', Resultat::STATUT_PROVISOIRE)
                    ->count();

                if ($resultatsCount === 0) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Retourne les IDs des étudiants concernés par cette délibération.
     */
    public function getEtudiantsConcernes()
    {
        // Récupérer les étudiants du niveau concerné
        $etudiantsQuery = Etudiant::where('niveau_id', $this->niveau_id)
            ->where('is_active', true);

        // Si le niveau a des parcours, filtrer par parcours
        if ($this->niveau->has_parcours && isset($this->parcours_id)) {
            $etudiantsQuery->where('parcours_id', $this->parcours_id);
        }

        // Pour les délibérations de rattrapage, filtrer les étudiants qui ont eu
        // la décision "rattrapage" en première session
        if ($this->session && $this->session->isRattrapage()) {
            $etudiantsEnRattrapage = $this->getEtudiantsEnRattrapage();
            $etudiantsQuery->whereIn('id', $etudiantsEnRattrapage);
        }

        return $etudiantsQuery->pluck('id')->toArray();
    }

    /**
     * Récupère les IDs des étudiants qui doivent passer le rattrapage.
     */
    private function getEtudiantsEnRattrapage()
    {
        // Trouver la session normale correspondante
        $sessionNormale = SessionExam::where('annee_universitaire_id', $this->annee_universitaire_id)
            ->where('type', 'Normale')
            ->first();

        if (!$sessionNormale) {
            return [];
        }

        // Récupérer les résultats de la session normale avec décision "rattrapage"
        return Resultat::whereHas('examen', function($query) use ($sessionNormale) {
                $query->where('niveau_id', $this->niveau_id)
                      ->where('session_id', $sessionNormale->id);
            })
            ->where('decision', Resultat::DECISION_RATTRAPAGE)
            ->distinct()
            ->pluck('etudiant_id')
            ->toArray();
    }

    /**
     * Démarre la délibération.
     */
    public function demarrer($userId)
    {
        // Vérifier les prérequis
        $verification = $this->verifierPrerequisDeliberation();
        if (!$verification['valide']) {
            throw new \Exception('Impossible de démarrer la délibération : ' . implode(', ', $verification['erreurs']));
        }

        DB::beginTransaction();

        try {
            // Changer le statut
            $this->statut = self::STATUT_EN_COURS;
            $this->save();

            // Associer les résultats provisoires
            $this->associerResultatsProvisoires();

            // Si règles automatiques activées, les appliquer
            if ($this->appliquer_regles_auto) {
                $this->appliquerReglesAutomatiques();
            }

            DB::commit();

            Log::info('Délibération démarrée', [
                'deliberation_id' => $this->id,
                'niveau' => $this->niveau->nom,
                'session' => $this->session->type,
                'demarre_par' => $userId
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du démarrage de la délibération', [
                'deliberation_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Associe les résultats provisoires à cette délibération.
     */
    public function associerResultatsProvisoires()
    {
        $examens = Examen::where('niveau_id', $this->niveau_id)
            ->where('session_id', $this->session_id)
            ->get();

        $count = 0;

        foreach ($examens as $examen) {
            $updated = Resultat::where('examen_id', $examen->id)
                ->where('statut', Resultat::STATUT_PROVISOIRE)
                ->whereNull('deliberation_id')
                ->update(['deliberation_id' => $this->id]);

            $count += $updated;
        }

        return $count;
    }

    /**
     * Applique les règles automatiques de délibération.
     */
    public function appliquerReglesAutomatiques()
    {
        if ($this->statut !== self::STATUT_EN_COURS) {
            throw new \Exception('La délibération doit être en cours pour appliquer les règles automatiques');
        }

        $etudiants = $this->getEtudiantsConcernes();

        $admis = 0;
        $ajournes = 0;
        $rachats = 0;
        $exclus = 0;

        foreach ($etudiants as $etudiantId) {
            // Calculer la moyenne générale
            $moyenne = $this->calculerMoyenneEtudiant($etudiantId);

            // Calculer le pourcentage d'UE validées
            $pourcentageUE = $this->calculerPourcentageUEValidees($etudiantId);

            // Déterminer la décision automatique
            $decision = $this->determinerDecisionAutomatique($moyenne, $pourcentageUE);

            // Créer ou mettre à jour la décision
            $this->enregistrerDecision($etudiantId, $decision, $moyenne);

            // Comptabiliser
            switch ($decision) {
                case self::DECISION_ADMIS:
                    $admis++;
                    break;
                case self::DECISION_ADMIS_CONDITIONNELLEMENT:
                    $rachats++;
                    break;
                case self::DECISION_AJOURNE:
                    $ajournes++;
                    break;
                case self::DECISION_EXCLUS:
                    $exclus++;
                    break;
            }
        }

        // Mettre à jour les statistiques
        $this->nombre_admis = $admis;
        $this->nombre_ajournes = $ajournes;
        $this->nombre_exclus = $exclus;
        $this->nombre_rachats = $rachats;
        $this->save();

        return true;
    }

    /**
     * Calcule la moyenne générale d'un étudiant pour cette délibération.
     */
    public function calculerMoyenneEtudiant($etudiantId)
    {
        // Récupérer tous les résultats de l'étudiant pour cette délibération
        $resultats = Resultat::whereHas('examen', function($query) {
                $query->where('niveau_id', $this->niveau_id)
                      ->where('session_id', $this->session_id);
            })
            ->where('etudiant_id', $etudiantId)
            ->with('ec')
            ->get();

        if ($resultats->isEmpty()) {
            return 0;
        }

        // Grouper par UE et calculer les moyennes d'UE
        $resultatsParUE = $resultats->groupBy('ec.ue_id');
        $moyennesUE = [];

        foreach ($resultatsParUE as $ueId => $resultatsUE) {
            $ue = $resultatsUE->first()->ec->ue;

            // Calculer la moyenne pondérée par les coefficients
            $totalPoints = 0;
            $totalCoefficients = 0;

            foreach ($resultatsUE as $resultat) {
                $coef = $resultat->ec->coefficient ?? 1;
                $totalPoints += $resultat->note * $coef;
                $totalCoefficients += $coef;
            }

            $moyenneUE = $totalCoefficients > 0 ? $totalPoints / $totalCoefficients : 0;
            $moyennesUE[$ueId] = [
                'moyenne' => $moyenneUE,
                'credits' => $ue->credits ?? 1
            ];
        }

        // Calculer la moyenne générale pondérée par les crédits
        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($moyennesUE as $ueData) {
            $totalPoints += $ueData['moyenne'] * $ueData['credits'];
            $totalCredits += $ueData['credits'];
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
    }

    /**
     * Calcule le pourcentage d'UE validées par un étudiant.
     */
    public function calculerPourcentageUEValidees($etudiantId)
    {
        // Récupérer toutes les UE du niveau
        $ues = UE::where('niveau_id', $this->niveau_id)->get();

        if ($ues->isEmpty()) {
            return 0;
        }

        $totalUE = $ues->count();
        $ueValidees = 0;

        foreach ($ues as $ue) {
            if ($this->etudiantValideUE($etudiantId, $ue->id)) {
                $ueValidees++;
            }
        }

        return $totalUE > 0 ? round(($ueValidees / $totalUE) * 100, 2) : 0;
    }

    /**
     * Vérifie si un étudiant valide une UE.
     */
    public function etudiantValideUE($etudiantId, $ueId)
    {
        // Calculer la moyenne de l'UE
        $resultats = Resultat::whereHas('examen', function($query) {
                $query->where('session_id', $this->session_id);
            })
            ->whereHas('ec', function($query) use ($ueId) {
                $query->where('ue_id', $ueId);
            })
            ->where('etudiant_id', $etudiantId)
            ->with('ec')
            ->get();

        if ($resultats->isEmpty()) {
            return false;
        }

        // Calculer la moyenne pondérée
        $totalPoints = 0;
        $totalCoefficients = 0;

        foreach ($resultats as $resultat) {
            $coef = $resultat->ec->coefficient ?? 1;
            $totalPoints += $resultat->note * $coef;
            $totalCoefficients += $coef;
        }

        $moyenne = $totalCoefficients > 0 ? $totalPoints / $totalCoefficients : 0;

        // UE validée si moyenne >= 10
        return $moyenne >= 10;
    }

    /**
     * Détermine la décision automatique en fonction des performances.
     */
    public function determinerDecisionAutomatique($moyenne, $pourcentageUE)
    {
        // Règles de décision
        if ($moyenne >= $this->seuil_admission && $pourcentageUE >= $this->pourcentage_ue_requises) {
            return self::DECISION_ADMIS;
        } elseif ($moyenne >= $this->seuil_rachat) {
            return self::DECISION_ADMIS_CONDITIONNELLEMENT;
        } elseif ($moyenne < 5) {
            return self::DECISION_EXCLUS;
        } else {
            return self::DECISION_AJOURNE;
        }
    }

    /**
     * Enregistre une décision pour un étudiant.
     */
    public function enregistrerDecision($etudiantId, $decision, $moyenne, $pointsJury = 0, $observations = null)
    {
        // Vérifier si une décision existe déjà
        $decisionExistante = Decision::where('deliberation_id', $this->id)
            ->where('etudiant_id', $etudiantId)
            ->first();

        if ($decisionExistante) {
            // Mettre à jour la décision existante
            $decisionExistante->update([
                'moyenne' => $moyenne,
                'decision' => $decision,
                'points_jury' => $pointsJury,
                'observations' => $observations
            ]);
            return $decisionExistante;
        } else {
            // Créer une nouvelle décision
            return Decision::create([
                'deliberation_id' => $this->id,
                'etudiant_id' => $etudiantId,
                'moyenne' => $moyenne,
                'decision' => $decision,
                'points_jury' => $pointsJury,
                'observations' => $observations
            ]);
        }
    }

    /**
     * Finalise la délibération.
     */
    public function finaliser($userId)
    {
        if ($this->statut !== self::STATUT_EN_COURS) {
            throw new \Exception('La délibération doit être en cours pour être finalisée');
        }

        DB::beginTransaction();

        try {
            // Mettre à jour le statut
            $this->statut = self::STATUT_TERMINEE;
            $this->date_finalisation = now();
            $this->finalise_par = $userId;
            $this->save();

            // Calculer les statistiques finales
            $this->mettreAJourStatistiques();

            DB::commit();

            Log::info('Délibération finalisée', [
                'deliberation_id' => $this->id,
                'finalise_par' => $userId,
                'admis' => $this->nombre_admis,
                'ajournes' => $this->nombre_ajournes,
                'exclus' => $this->nombre_exclus,
                'rachats' => $this->nombre_rachats
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la finalisation de la délibération', [
                'deliberation_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Met à jour les statistiques de la délibération.
     */
    public function mettreAJourStatistiques()
    {
        $stats = $this->decisions()
            ->selectRaw('decision, COUNT(*) as total')
            ->groupBy('decision')
            ->pluck('total', 'decision')
            ->toArray();

        $this->nombre_admis = $stats[self::DECISION_ADMIS] ?? 0;
        $this->nombre_ajournes = $stats[self::DECISION_AJOURNE] ?? 0;
        $this->nombre_exclus = $stats[self::DECISION_EXCLUS] ?? 0;
        $this->nombre_rachats = $stats[self::DECISION_ADMIS_CONDITIONNELLEMENT] ?? 0;
        $this->save();
    }

    /**
     * Valide et publie les résultats de la délibération.
     */
    public function publier($userId)
    {
        if ($this->statut !== self::STATUT_TERMINEE) {
            throw new \Exception('La délibération doit être terminée pour être publiée');
        }

        DB::beginTransaction();

        try {
            // Mettre à jour les résultats associés
            $this->publierResultats($userId);

            // Mettre à jour le statut de la délibération
            $this->statut = self::STATUT_VALIDEE;
            $this->date_publication = now();
            $this->save();

            DB::commit();

            Log::info('Délibération publiée', [
                'deliberation_id' => $this->id,
                'publie_par' => $userId
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la publication de la délibération', [
                'deliberation_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Publie les résultats associés à cette délibération.
     */
    private function publierResultats($userId)
    {
        // Récupérer toutes les décisions
        $decisions = $this->decisions()->with('etudiant')->get();

        foreach ($decisions as $decision) {
            // Mettre à jour tous les résultats de cet étudiant
            $resultats = Resultat::where('etudiant_id', $decision->etudiant_id)
                ->where('deliberation_id', $this->id)
                ->get();

            foreach ($resultats as $resultat) {
                $resultat->decision = $decision->decision;
                $resultat->statut = Resultat::STATUT_PUBLIE;
                $resultat->date_validation = $this->date_finalisation;
                $resultat->date_publication = now();
                $resultat->modifie_par = $userId;
                $resultat->save();
            }
        }
    }

    /**
     * Annule la délibération.
     */
    public function annuler($userId, $raison = null)
    {
        if ($this->statut === self::STATUT_VALIDEE) {
            throw new \Exception('Impossible d\'annuler une délibération déjà validée et publiée');
        }

        DB::beginTransaction();

        try {
            // Dissocier les résultats
            Resultat::where('deliberation_id', $this->id)
                ->update(['deliberation_id' => null]);

            // Supprimer les décisions
            $this->decisions()->delete();

            // Mettre à jour le statut
            $this->statut = self::STATUT_ANNULEE;
            $this->observations = $this->observations . "\n\nAnnulée le " . now()->format('d/m/Y H:i') .
                                " par " . User::find($userId)->name .
                                ($raison ? ". Raison : $raison" : "");
            $this->save();

            DB::commit();

            Log::info('Délibération annulée', [
                'deliberation_id' => $this->id,
                'annule_par' => $userId,
                'raison' => $raison
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation de la délibération', [
                'deliberation_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Méthodes d'état pour vérifier le statut actuel.
     */
    public function isProgrammee()
    {
        return $this->statut === self::STATUT_PROGRAMMEE;
    }

    public function isEnCours()
    {
        return $this->statut === self::STATUT_EN_COURS;
    }

    public function isTerminee()
    {
        return $this->statut === self::STATUT_TERMINEE;
    }

    public function isValidee()
    {
        return $this->statut === self::STATUT_VALIDEE;
    }

    public function isAnnulee()
    {
        return $this->statut === self::STATUT_ANNULEE;
    }

    /**
     * Scopes pour les requêtes.
     */
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeActiveAnnee($query)
    {
        return $query->whereHas('anneeUniversitaire', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeRattrapage($query)
    {
        return $query->whereHas('session', function ($q) {
            $q->where('type', 'Rattrapage');
        });
    }

    public function scopeNiveauxReguliers($query)
    {
        return $query->whereHas('niveau', function ($q) {
            $q->where('is_concours', false);
        });
    }

    public function scopeEnAttente($query)
    {
        return $query->whereIn('statut', [
            self::STATUT_PROGRAMMEE,
            self::STATUT_EN_COURS
        ]);
    }

    public function scopeTerminees($query)
    {
        return $query->whereIn('statut', [
            self::STATUT_TERMINEE,
            self::STATUT_VALIDEE
        ]);
    }

    /**
     * Récupère les paramètres par défaut pour un niveau
     *
     * @param Niveau|int|string $niveau Objet Niveau, ID du niveau ou code du niveau
     * @return array
     */
    public static function getDefaultParamsForNiveau($niveau)
    {
        // Valeurs par défaut de base pour tous les niveaux
        $defaultParams = [
            'seuil_admission' => 10.00,
            'seuil_rachat' => 9.75,
            'pourcentage_ue_requises' => 80,
            'appliquer_regles_auto' => true
        ];

        // Récupérer le code du niveau selon le type de paramètre reçu
        $niveauCode = null;

        if ($niveau instanceof Niveau) {
            // Si on a reçu directement un objet Niveau
            $niveauCode = $niveau->abr;
        } elseif (is_numeric($niveau)) {
            // Si on a reçu un ID de niveau, on récupère l'objet
            $niveauObj = Niveau::find($niveau);
            if ($niveauObj) {
                $niveauCode = $niveauObj->abr;
            }
        } else {
            // Si on a reçu directement un code de niveau (L1, L2, etc.)
            $niveauCode = (string) $niveau;
        }

        // Si aucun code de niveau valide n'a été trouvé, retourner les valeurs par défaut
        if (!$niveauCode) {
            Log::warning('Impossible de déterminer le code du niveau pour les paramètres de délibération', [
                'niveau_input' => $niveau
            ]);
            return $defaultParams;
        }

        // Ajuster selon le niveau
        $niveauCode = strtoupper($niveauCode);
        switch ($niveauCode) {
            case 'L1':
                // Paramètres standards pour L1
                break;
            case 'L2':
                $defaultParams['seuil_rachat'] = 9.50;
                break;
            case 'L3':
                // Paramètres standards pour L3
                break;
            case 'M1':
                $defaultParams['pourcentage_ue_requises'] = 85;
                break;
            case 'M2':
                $defaultParams['seuil_admission'] = 10.50;
                $defaultParams['pourcentage_ue_requises'] = 90;
                break;
            case 'D':
                $defaultParams['seuil_admission'] = 12.00;
                $defaultParams['seuil_rachat'] = 11.50;
                $defaultParams['pourcentage_ue_requises'] = 95;
                break;
            default:
                // Utiliser les valeurs par défaut pour les autres niveaux
                Log::info('Utilisation des paramètres par défaut pour le niveau', [
                    'niveau_code' => $niveauCode
                ]);
                break;
        }

        // Remarque : Dans une évolution future, ces valeurs pourraient être stockées
        // dans une table dédiée pour permettre la configuration via l'interface admin

        return $defaultParams;
    }

    /**
     * Applique les paramètres par défaut à cette délibération
     * basés sur le niveau associé
     */
    public function applyDefaultParams()
    {
        if (!$this->niveau) {
            return false;
        }

        $params = self::getDefaultParamsForNiveau($this->niveau->abr);

        $this->seuil_admission = $params['seuil_admission'];
        $this->seuil_rachat = $params['seuil_rachat'];
        $this->pourcentage_ue_requises = $params['pourcentage_ue_requises'];
        $this->appliquer_regles_auto = $params['appliquer_regles_auto'];

        return true;
    }
}
