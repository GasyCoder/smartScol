<?php

namespace App\Services;

use App\Models\EC;
use App\Models\UE;
use App\Models\Niveau;
use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\Deliberation;
use App\Models\ResultatFinal;
use App\Models\ResultatFusion;
use App\Models\DeliberationConfig;
use App\Config\ReglesDeliberation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CalculAcademiqueService
{
    // ✅ Vos constantes existantes
    const CREDIT_TOTAL_REQUIS = 60;
    const CREDIT_MINIMUM_SESSION2 = 40;
    const SEUIL_VALIDATION_UE = 10.0;
    const NOTE_ELIMINATOIRE = 0;

    // ✅ MÉTHODE PRINCIPALE MANQUANTE : calculerResultatsComplets
    public function calculerResultatsComplets($etudiantId, $sessionId, $useResultatFinal = true)
    {
        try {
            // 1. Récupérer la session
            $session = SessionExam::findOrFail($sessionId);

            // 2. Récupérer les résultats de l'étudiant
            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            $resultats = $modelClass::where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->where('statut', $useResultatFinal ? ResultatFinal::STATUT_PUBLIE : 'valide')
                ->with(['ec.ue', 'etudiant'])
                ->get();

            if ($resultats->isEmpty()) {
                throw new \Exception("Aucun résultat trouvé pour l'étudiant {$etudiantId} en session {$sessionId}");
            }

            $etudiant = $resultats->first()->etudiant;

            // 3. Calculer les résultats par UE selon logique médecine
            $resultatsUE = $this->calculerResultatsUE_LogiqueMedecine($resultats);

            // 4. Calculer la synthèse générale
            $synthese = $this->calculerSyntheseGenerale($resultatsUE);

            // 5. Déterminer la décision selon logique médecine
            $decision = $this->determinerDecision_LogiqueMedecine($synthese, $session);

            // 6. Structurer la réponse complète
            return [
                'etudiant' => [
                    'id' => $etudiant->id,
                    'matricule' => $etudiant->matricule,
                    'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'niveau' => $etudiant->niveau->nom ?? 'N/A',
                    'parcours' => $etudiant->parcours ? $etudiant->parcours->nom : null
                ],
                'session' => [
                    'id' => $session->id,
                    'type' => $session->type,
                    'nom' => $session->nom,
                    'annee' => $session->anneeUniversitaire->libelle ?? 'N/A'
                ],
                'resultats_ue' => $resultatsUE,
                'synthese' => $synthese,
                'decision' => $decision,
                'metadonnees' => [
                    'date_calcul' => now()->format('Y-m-d H:i:s'),
                    'methode' => 'logique_medecine',
                    'nb_ue' => count($resultatsUE),
                    'nb_ec' => $resultats->count()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erreur calcul résultats complets', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors du calcul des résultats: ' . $e->getMessage());
        }
    }

    // ✅ MÉTHODE : Calcule les résultats par UE selon logique médecine
    private function calculerResultatsUE_LogiqueMedecine($resultats)
    {
        $resultatsUE = [];

        // Grouper par UE
        $resultatsParUE = $resultats->groupBy('ec.ue_id');

        foreach ($resultatsParUE as $ueId => $notesUE) {
            $ue = $notesUE->first()->ec->ue;

            // Récupérer toutes les notes de l'UE
            $notes = $notesUE->pluck('note')->toArray();
            $notesEC = $notesUE->map(function($resultat) {
                return [
                    'ec_id' => $resultat->ec_id,
                    'ec_nom' => $resultat->ec->nom,
                    'ec_abr' => $resultat->ec->abr ?? substr($resultat->ec->nom, 0, 10),
                    'note' => $resultat->note,
                    'est_eliminatoire' => $resultat->note == self::NOTE_ELIMINATOIRE
                ];
            })->toArray();

            // Vérifier s'il y a une note éliminatoire (0)
            $hasNoteEliminatoire = in_array(self::NOTE_ELIMINATOIRE, $notes);

            // Calcul de la moyenne UE selon logique médecine
            if ($hasNoteEliminatoire) {
                // En médecine : note 0 = UE éliminée
                $moyenneUE = 0;
                $ueValidee = false;
                $statutUE = 'eliminee';
            } else {
                // Moyenne arithmétique des EC
                $moyenneUE = count($notes) > 0 ? array_sum($notes) / count($notes) : 0;
                $moyenneUE = round($moyenneUE, 2);

                // Validation UE selon seuil
                $ueValidee = $moyenneUE >= self::SEUIL_VALIDATION_UE;
                $statutUE = $ueValidee ? 'validee' : 'non_validee';
            }

            // Calcul crédits
            $creditsUE = $ue->credits ?? 0;
            $creditsValides = $ueValidee ? $creditsUE : 0;

            $resultatsUE[] = [
                'ue_id' => $ueId,
                'ue_nom' => $ue->nom,
                'ue_abr' => $ue->abr ?? substr($ue->nom, 0, 10),
                'ue_credits' => $creditsUE,
                'moyenne_ue' => $moyenneUE,
                'validee' => $ueValidee,
                'credits_valides' => $creditsValides,
                'statut' => $statutUE,
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'notes_ec' => $notesEC,
                'nb_ec' => count($notesEC)
            ];
        }

        return $resultatsUE;
    }

    // ✅ MÉTHODE : Calcule la synthèse générale
    private function calculerSyntheseGenerale($resultatsUE)
    {
        $totalCredits = array_sum(array_column($resultatsUE, 'ue_credits'));
        $creditsValides = array_sum(array_column($resultatsUE, 'credits_valides'));

        // Vérifier s'il y a des notes éliminatoires
        $hasNoteEliminatoire = collect($resultatsUE)->contains('has_note_eliminatoire', true);

        // Calcul moyenne générale selon logique médecine
        if ($hasNoteEliminatoire) {
            // En médecine : note éliminatoire = moyenne générale à 0
            $moyenneGenerale = 0;
        } else {
            $moyennesUE = array_column($resultatsUE, 'moyenne_ue');
            $moyenneGenerale = count($moyennesUE) > 0 ?
                array_sum($moyennesUE) / count($moyennesUE) : 0;
            $moyenneGenerale = round($moyenneGenerale, 2);
        }

        // Statistiques UE
        $nbUE = count($resultatsUE);
        $nbUEValidees = count(array_filter($resultatsUE, fn($ue) => $ue['validee']));
        $nbUEEliminees = count(array_filter($resultatsUE, fn($ue) => $ue['has_note_eliminatoire']));

        return [
            'moyenne_generale' => $moyenneGenerale,
            'credits_valides' => $creditsValides,
            'total_credits' => $totalCredits,
            'pourcentage_credits' => $totalCredits > 0 ?
                round(($creditsValides / $totalCredits) * 100, 2) : 0,
            'a_note_eliminatoire' => $hasNoteEliminatoire,
            'nb_ue_totales' => $nbUE,
            'nb_ue_validees' => $nbUEValidees,
            'nb_ue_eliminees' => $nbUEEliminees,
            'nb_ue_non_validees' => $nbUE - $nbUEValidees - $nbUEEliminees
        ];
    }

    // ✅ MÉTHODE : Détermine la décision selon logique médecine
    private function determinerDecision_LogiqueMedecine($synthese, $session)
    {
        $creditsValides = $synthese['credits_valides'];
        $hasNoteEliminatoire = $synthese['a_note_eliminatoire'];

        if ($session->type === 'Normale') {
            // Session 1 - Logique médecine stricte
            if ($hasNoteEliminatoire) {
                return [
                    'code' => 'rattrapage',
                    'libelle' => 'Autorisé(e) au rattrapage',
                    'motif' => 'Présence de note(s) éliminatoire(s)',
                    'credits_requis' => self::CREDIT_TOTAL_REQUIS,
                    'credits_obtenus' => $creditsValides
                ];
            }

            if ($creditsValides >= self::CREDIT_TOTAL_REQUIS) {
                return [
                    'code' => 'admis',
                    'libelle' => 'Admis(e)',
                    'motif' => 'Validation de tous les crédits requis',
                    'credits_requis' => self::CREDIT_TOTAL_REQUIS,
                    'credits_obtenus' => $creditsValides
                ];
            } else {
                return [
                    'code' => 'rattrapage',
                    'libelle' => 'Autorisé(e) au rattrapage',
                    'motif' => 'Crédits insuffisants',
                    'credits_requis' => self::CREDIT_TOTAL_REQUIS,
                    'credits_obtenus' => $creditsValides
                ];
            }

        } else {
            // Session 2 - Logique médecine rattrapage
            if ($hasNoteEliminatoire) {
                return [
                    'code' => 'exclus',
                    'libelle' => 'Exclu(e)',
                    'motif' => 'Note éliminatoire en session de rattrapage',
                    'credits_requis' => self::CREDIT_MINIMUM_SESSION2,
                    'credits_obtenus' => $creditsValides
                ];
            }

            if ($creditsValides >= self::CREDIT_MINIMUM_SESSION2) {
                return [
                    'code' => 'admis',
                    'libelle' => 'Admis(e)',
                    'motif' => 'Validation des crédits minimum en rattrapage',
                    'credits_requis' => self::CREDIT_MINIMUM_SESSION2,
                    'credits_obtenus' => $creditsValides
                ];
            } else {
                return [
                    'code' => 'redoublant',
                    'libelle' => 'Autorisé(e) à redoubler',
                    'motif' => 'Crédits insuffisants en rattrapage',
                    'credits_requis' => self::CREDIT_MINIMUM_SESSION2,
                    'credits_obtenus' => $creditsValides
                ];
            }
        }
    }

    // ✅ NOUVELLE MÉTHODE : Applique la délibération selon la configuration
    public function appliquerDeliberationAvecConfig($niveauId, $parcoursId, $sessionId, $parametres = [])
    {
        try {
            DB::beginTransaction();

            // 1. Récupérer ou créer la configuration de délibération
            $config = DeliberationConfig::getOrCreateConfig($niveauId, $parcoursId, $sessionId);

            // 2. Mettre à jour les paramètres si fournis
            if (!empty($parametres)) {
                $config->update($parametres);
            }

            // 3. Récupérer la session pour déterminer le type
            $session = SessionExam::findOrFail($sessionId);

            // 4. Récupérer tous les étudiants de cette session avec des résultats
            $etudiantsIds = ResultatFinal::where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->distinct('etudiant_id')
                ->pluck('etudiant_id');

            $statistiques = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'total' => $etudiantsIds->count()
            ];

            // 5. Appliquer les décisions selon la configuration
            foreach ($etudiantsIds as $etudiantId) {
                $nouvelleDecision = $this->calculerDecisionAvecConfig($etudiantId, $sessionId, $config);

                $this->mettreAJourResultatsEtudiantDeliberation($etudiantId, $sessionId, $nouvelleDecision, $config->id);

                $statistiques[$nouvelleDecision]++;
            }


            // 6. Marquer la configuration comme délibérée
            $config->marquerDelibere(Auth::id());

            // ✅ IMPORTANT : S'assurer que la transaction est bien commitée
            DB::commit();

            // ✅ NOUVEAU : Attendre que la transaction soit réellement persistée
            usleep(50000); // 50ms pour que les writes soient flushés

            // ✅ NOUVEAU : Vérifier que les changements sont bien en base
            $verificationCount = ResultatFinal::where('session_exam_id', $sessionId)
                ->where('jury_validated', true)
                ->count();

            Log::info('✅ Délibération appliquée avec configuration - Vérification', [
                'config_id' => $config->id,
                'session_id' => $sessionId,
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'statistiques' => $statistiques,
                'verification_jury_validated_count' => $verificationCount
            ]);

            return [
                'success' => true,
                'message' => 'Délibération appliquée avec succès',
                'statistiques' => $statistiques,
                'config' => $config,
                'verification' => $verificationCount
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Erreur lors de la délibération avec config: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erreur lors de la délibération: ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }

    // ✅ NOUVELLE MÉTHODE : Calcule la décision selon la configuration
    private function calculerDecisionAvecConfig($etudiantId, $sessionId, DeliberationConfig $config)
    {
        // Utiliser la logique existante calculerResultatsComplets
        $resultat = $this->calculerResultatsComplets($etudiantId, $sessionId, true);

        $creditsValides = $resultat['synthese']['credits_valides'];
        $hasNoteEliminatoire = $resultat['synthese']['a_note_eliminatoire'];

        // Déterminer le type de session
        $session = $resultat['session'];

        if ($session['type'] === 'Normale') {
            // Session 1 - Utiliser config
            if ($config->note_eliminatoire_bloque_s1 && $hasNoteEliminatoire) {
                return 'rattrapage';
            }

            return $creditsValides >= $config->credits_admission_s1
                ? 'admis'
                : 'rattrapage';

        } else {
            // Session 2 - Utiliser config
            if ($config->note_eliminatoire_exclusion_s2 && $hasNoteEliminatoire) {
                return 'exclus';
            }

            if ($creditsValides >= $config->credits_admission_s2) {
                return 'admis';
            } elseif ($creditsValides >= $config->credits_redoublement_s2) {
                return 'redoublant';
            } else {
                return 'exclus';
            }
        }
    }

    // ✅ NOUVELLE MÉTHODE : Met à jour les résultats avec traçabilité délibération
    private function mettreAJourResultatsEtudiantDeliberation($etudiantId, $sessionId, $nouvelleDecision, $configId)
    {
        $resultats = ResultatFinal::where('session_exam_id', $sessionId)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->get();

        foreach ($resultats as $resultat) {
            $ancienneDecision = $resultat->decision;

            // ✅ MISE À JOUR avec force
            $updateData = [
                'decision' => $nouvelleDecision,
                'jury_validated' => true, // ✅ Marquer comme validé par le jury
                'modifie_par' => Auth::id(),
                'updated_at' => now() // ✅ Forcer la mise à jour du timestamp
            ];

            $resultat->update($updateData);

            // ✅ VÉRIFICATION : S'assurer que la mise à jour a bien eu lieu
            $resultat->fresh();

            Log::info('📝 Résultat mis à jour pour délibération', [
                'resultat_id' => $resultat->id,
                'etudiant_id' => $etudiantId,
                'ancienne_decision' => $ancienneDecision,
                'nouvelle_decision' => $nouvelleDecision,
                'jury_validated' => $resultat->jury_validated,
                'updated_at' => $resultat->updated_at
            ]);

            // Ajouter à l'historique JSON
            $statusHistory = $resultat->status_history ?? [];
            $statusHistory[] = [
                'type_action' => 'deliberation_appliquee',
                'decision_precedente' => $ancienneDecision,
                'decision_nouvelle' => $nouvelleDecision,
                'user_id' => Auth::id(),
                'date_action' => now()->toDateTimeString(),
                'config_deliberation_id' => $configId,
                'source' => 'deliberation_avec_configuration'
            ];

            $resultat->update(['status_history' => $statusHistory]);

            // ✅ Historique dans table dédiée si elle existe
            if (class_exists('App\Models\ResultatFinalHistorique')) {
                \App\Models\ResultatFinalHistorique::creerEntreeDeliberation(
                    $resultat->id,
                    $ancienneDecision,
                    $nouvelleDecision,
                    Auth::id(),
                    $configId
                );
            }
        }

        // ✅ DOUBLE VÉRIFICATION : Compter les résultats mis à jour pour cet étudiant
        $countMisAJour = ResultatFinal::where('session_exam_id', $sessionId)
            ->where('etudiant_id', $etudiantId)
            ->where('decision', $nouvelleDecision)
            ->where('jury_validated', true)
            ->count();

        Log::info('✅ Vérification mise à jour délibération étudiant', [
            'etudiant_id' => $etudiantId,
            'session_id' => $sessionId,
            'nouvelle_decision' => $nouvelleDecision,
            'count_mis_a_jour' => $countMisAJour
        ]);
    }


    // ✅ NOUVELLE MÉTHODE : Annule une délibération
    public function annulerDeliberationAvecConfig($niveauId, $parcoursId, $sessionId)
    {
        try {
            DB::beginTransaction();

            $config = DeliberationConfig::where('niveau_id', $niveauId)
                ->where('parcours_id', $parcoursId)
                ->where('session_id', $sessionId)
                ->first();

            if (!$config || !$config->delibere) {
                throw new \Exception('Aucune délibération à annuler');
            }

            // Remettre jury_validated à false pour tous les résultats concernés
            ResultatFinal::where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                })
                ->update([
                    'jury_validated' => false,
                    'modifie_par' => Auth::id()
                ]);

            // Annuler la configuration
            $config->annulerDeliberation();

            DB::commit();

            Log::info('Délibération annulée', [
                'config_id' => $config->id,
                'session_id' => $sessionId,
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId
            ]);

            return [
                'success' => true,
                'message' => 'Délibération annulée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur annulation délibération: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ];
        }
    }

    // ✅ NOUVELLE MÉTHODE : Vérifie si une session est délibérée
    public function estDelibere($niveauId, $parcoursId, $sessionId)
    {
        $config = DeliberationConfig::where('niveau_id', $niveauId)
            ->where('parcours_id', $parcoursId)
            ->where('session_id', $sessionId)
            ->first();

        return $config && $config->delibere;
    }

    // ✅ MISE À JOUR DE VOTRE MÉTHODE EXISTANTE : appliquerDecisionsSession
    public function appliquerDecisionsSession($sessionId, $useResultatFinal = false, $avecConfiguration = false, $niveauId = null, $parcoursId = null)
    {
        // Si avec configuration, utiliser la nouvelle méthode
        if ($avecConfiguration && $niveauId) {
            return $this->appliquerDeliberationAvecConfig($niveauId, $parcoursId, $sessionId);
        }

        // Sinon, garder votre logique existante
        try {
            DB::beginTransaction();

            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            // Récupérer les étudiants distincts via session_exam_id
            $etudiantsIds = $modelClass::where('session_exam_id', $sessionId)
                ->distinct('etudiant_id')
                ->pluck('etudiant_id');

            // Initialiser les statistiques
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

            // Traiter chaque étudiant selon logique médecine
            foreach ($etudiantsIds as $etudiantId) {
                try {
                    // Calculer les résultats complets selon logique médecine
                    $resultat = $this->calculerResultatsComplets($etudiantId, $sessionId, $useResultatFinal);
                    $decision = $resultat['decision']['code'];

                    // Appliquer la décision
                    $success = $this->appliquerDecision($etudiantId, $sessionId, $decision, $useResultatFinal);

                    if ($success) {
                        $stats['decisions'][$decision]++;

                        Log::info('Décision médecine appliquée', [
                            'etudiant_id' => $etudiantId,
                            'session_id' => $sessionId,
                            'decision' => $decision,
                            'credits_valides' => $resultat['synthese']['credits_valides'],
                            'moyenne_generale' => $resultat['synthese']['moyenne_generale'],
                            'has_eliminatoire' => $resultat['synthese']['a_note_eliminatoire']
                        ]);
                    } else {
                        $stats['erreurs'][] = "Échec application décision pour étudiant $etudiantId";
                    }
                } catch (\Exception $e) {
                    $stats['erreurs'][] = "Erreur étudiant $etudiantId: " . $e->getMessage();
                    Log::error('Erreur application décision médecine', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Logger les résultats
            Log::info('Application des décisions médecine terminée', [
                'session_id' => $sessionId,
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'application des décisions : ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }

    // ✅ MISE À JOUR DE VOTRE MÉTHODE EXISTANTE : appliquerDecision
    public function appliquerDecision($etudiantId, $sessionId, $decision, $useResultatFinal = false, $avecDeliberation = false)
    {
        try {
            DB::beginTransaction();

            // Vérifier la validité de la décision
            if (!in_array($decision, ['admis', 'rattrapage', 'redoublant', 'exclus'])) {
                throw new \Exception("Décision invalide : $decision");
            }

            $modelClass = $useResultatFinal ? ResultatFinal::class : ResultatFusion::class;

            // Récupérer les résultats de l'étudiant pour cette session via session_exam_id
            $resultats = $modelClass::where('session_exam_id', $sessionId)
                ->where('etudiant_id', $etudiantId)
                ->get();

            if ($resultats->isEmpty()) {
                Log::warning("Aucun résultat trouvé pour l'étudiant", [
                    'etudiant_id' => $etudiantId,
                    'session_id' => $sessionId
                ]);
                return false;
            }

            // Mettre à jour chaque résultat
            foreach ($resultats as $resultat) {
                $ancienneDecision = $resultat->decision;

                $updateData = [
                    'decision' => $decision,
                    'modifie_par' => Auth::id()
                ];

                // ✅ NOUVEAU : Si c'est une délibération, marquer jury_validated = true
                if ($avecDeliberation) {
                    $updateData['jury_validated'] = true;
                }

                $resultat->update($updateData);

                // Si c'est un résultat final, mettre à jour l'historique
                if ($useResultatFinal) {
                    $historique = $resultat->status_history ?? [];
                    if (!is_array($historique)) {
                        $historique = json_decode($historique, true) ?? [];
                    }

                    // Ajouter l'entrée de décision dans l'historique
                    $historique[] = [
                        'type_action' => $avecDeliberation ? 'decision_deliberation' : 'decision_appliquee',
                        'decision_precedente' => $ancienneDecision,
                        'decision_nouvelle' => $decision,
                        'user_id' => Auth::id() ?? 1,
                        'date_action' => now()->toDateTimeString(),
                        'methode' => $avecDeliberation ? 'deliberation_avec_config' : 'logique_medecine_automatique',
                        'jury_validated' => $avecDeliberation
                    ];

                    $resultat->status_history = $historique;
                    $resultat->save();
                }
            }

            DB::commit();

            Log::info('Décision appliquée avec succès', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'decision' => $decision,
                'resultats_maj' => $resultats->count(),
                'table' => $useResultatFinal ? 'resultats_finaux' : 'resultats_fusion',
                'avec_deliberation' => $avecDeliberation,
                'jury_validated' => $avecDeliberation
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'application de la décision', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'decision' => $decision,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    // ✅ NOUVELLE MÉTHODE : Récupère la configuration de délibération
    public function getConfigurationDeliberation($niveauId, $parcoursId, $sessionId)
    {
        return DeliberationConfig::where('niveau_id', $niveauId)
            ->where('parcours_id', $parcoursId)
            ->where('session_id', $sessionId)
            ->with(['niveau', 'parcours', 'session', 'deliberePar'])
            ->first();
    }

    // ✅ NOUVELLE MÉTHODE : Récupère toutes les configurations de délibération actives
    public function getConfigurationsDeliberationActives($sessionId = null)
    {
        $query = DeliberationConfig::with(['niveau', 'parcours', 'session', 'deliberePar']);

        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        return $query->orderBy('delibere', 'desc')
            ->orderBy('date_deliberation', 'desc')
            ->get();
    }

    // ✅ NOUVELLE MÉTHODE : Obtient les statistiques de délibération
    public function getStatistiquesDeliberation($niveauId, $parcoursId, $sessionId)
    {
        try {
            $config = $this->getConfigurationDeliberation($niveauId, $parcoursId, $sessionId);

            if (!$config) {
                return [
                    'configuration_existante' => false,
                    'delibere' => false,
                    'statistiques' => []
                ];
            }

            // Compter les résultats par décision
            $stats = ResultatFinal::where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->selectRaw('
                    decision,
                    COUNT(DISTINCT etudiant_id) as nb_etudiants,
                    COUNT(CASE WHEN jury_validated = 1 THEN 1 END) as nb_valides_jury
                ')
                ->groupBy('decision')
                ->get()
                ->keyBy('decision');

            $totalEtudiants = $stats->sum('nb_etudiants');
            $totalValidesJury = $stats->sum('nb_valides_jury');

            return [
                'configuration_existante' => true,
                'delibere' => $config->delibere,
                'date_deliberation' => $config->date_deliberation,
                'delibere_par' => $config->deliberePar?->name,
                'parametres' => [
                    'credits_admission_s1' => $config->credits_admission_s1,
                    'credits_admission_s2' => $config->credits_admission_s2,
                    'credits_redoublement_s2' => $config->credits_redoublement_s2,
                    'note_eliminatoire_bloque_s1' => $config->note_eliminatoire_bloque_s1,
                    'note_eliminatoire_exclusion_s2' => $config->note_eliminatoire_exclusion_s2,
                ],
                'statistiques' => [
                    'total_etudiants' => $totalEtudiants,
                    'total_valides_jury' => $totalValidesJury,
                    'pourcentage_valides_jury' => $totalEtudiants > 0 ?
                        round(($totalValidesJury / $totalEtudiants) * 100, 2) : 0,
                    'decisions' => [
                        'admis' => $stats->get('admis')?->nb_etudiants ?? 0,
                        'rattrapage' => $stats->get('rattrapage')?->nb_etudiants ?? 0,
                        'redoublant' => $stats->get('redoublant')?->nb_etudiants ?? 0,
                        'exclus' => $stats->get('exclus')?->nb_etudiants ?? 0,
                    ],
                    'decisions_validees_jury' => [
                        'admis' => $stats->get('admis')?->nb_valides_jury ?? 0,
                        'rattrapage' => $stats->get('rattrapage')?->nb_valides_jury ?? 0,
                        'redoublant' => $stats->get('redoublant')?->nb_valides_jury ?? 0,
                        'exclus' => $stats->get('exclus')?->nb_valides_jury ?? 0,
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erreur récupération statistiques délibération', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'configuration_existante' => false,
                'delibere' => false,
                'statistiques' => [],
                'erreur' => $e->getMessage()
            ];
        }
    }

    // ✅ NOUVELLE MÉTHODE : Simule une délibération sans l'appliquer
    public function simulerDeliberationAvecConfig($niveauId, $parcoursId, $sessionId, $parametresSimulation)
    {
        try {
            // Créer une config temporaire pour la simulation
            $configTemp = new DeliberationConfig($parametresSimulation);
            $configTemp->niveau_id = $niveauId;
            $configTemp->parcours_id = $parcoursId;
            $configTemp->session_id = $sessionId;

            // Récupérer tous les étudiants concernés
            $etudiantsIds = ResultatFinal::where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->distinct('etudiant_id')
                ->pluck('etudiant_id');

            $resultatsSimulation = [];
            $statistiques = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'changements' => 0
            ];

            foreach ($etudiantsIds as $etudiantId) {
                // Calculer la décision actuelle et simulée
                $decisionActuelle = $this->getDecisionActuelleEtudiant($etudiantId, $sessionId);
                $decisionSimulee = $this->calculerDecisionAvecConfig($etudiantId, $sessionId, $configTemp);

                $changement = $decisionActuelle !== $decisionSimulee;

                if ($changement) {
                    $statistiques['changements']++;
                }

                $statistiques[$decisionSimulee]++;

                $resultatsSimulation[] = [
                    'etudiant_id' => $etudiantId,
                    'decision_actuelle' => $decisionActuelle,
                    'decision_simulee' => $decisionSimulee,
                    'changement' => $changement
                ];
            }

            Log::info('Simulation délibération terminée', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'total_etudiants' => $etudiantsIds->count(),
                'changements' => $statistiques['changements'],
                'parametres' => $parametresSimulation
            ]);

            return [
                'success' => true,
                'total_etudiants' => $etudiantsIds->count(),
                'statistiques' => $statistiques,
                'resultats_detailles' => $resultatsSimulation,
                'parametres_simulation' => $parametresSimulation
            ];

        } catch (\Exception $e) {
            Log::error('Erreur simulation délibération', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la simulation: ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }

    // ✅ MÉTHODE UTILITAIRE : Récupère la décision actuelle d'un étudiant
    private function getDecisionActuelleEtudiant($etudiantId, $sessionId)
    {
        $resultat = ResultatFinal::where('session_exam_id', $sessionId)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->first();

        return $resultat ? $resultat->decision : 'rattrapage';
    }

    // ✅ NOUVELLE MÉTHODE : Valide la cohérence d'une configuration
    public function validerCoherenceConfiguration($parametres)
    {
        $erreurs = [];

        // Validation crédits session 1
        if ($parametres['credits_admission_s1'] < 40 || $parametres['credits_admission_s1'] > 60) {
            $erreurs[] = 'Les crédits d\'admission session 1 doivent être entre 40 et 60';
        }

        // Validation crédits session 2
        if ($parametres['credits_admission_s2'] < 30 || $parametres['credits_admission_s2'] > 50) {
            $erreurs[] = 'Les crédits d\'admission session 2 doivent être entre 30 et 50';
        }

        // Validation cohérence session 2
        if ($parametres['credits_redoublement_s2'] >= $parametres['credits_admission_s2']) {
            $erreurs[] = 'Les crédits de redoublement doivent être inférieurs aux crédits d\'admission session 2';
        }

        // Validation logique médecine
        if ($parametres['credits_admission_s1'] != 60) {
            $erreurs[] = 'ATTENTION: La logique médecine standard requiert 60 crédits en session 1';
        }

        if ($parametres['credits_admission_s2'] != 40) {
            $erreurs[] = 'ATTENTION: La logique médecine standard requiert 40 crédits en session 2';
        }

        return $erreurs;
    }

    // ✅ NOUVELLE MÉTHODE : Exporte les résultats de délibération
    public function exporterResultatsDeliberation($niveauId, $parcoursId, $sessionId, $format = 'array')
    {
        try {
            $config = $this->getConfigurationDeliberation($niveauId, $parcoursId, $sessionId);
            $stats = $this->getStatistiquesDeliberation($niveauId, $parcoursId, $sessionId);

            // Récupérer les résultats détaillés
            $resultats = ResultatFinal::where('session_exam_id', $sessionId)
                ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                })
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->with(['etudiant', 'ec.ue'])
                ->get()
                ->groupBy('etudiant_id');

            $exportData = [];

            foreach ($resultats as $etudiantId => $resultatsEtudiant) {
                $etudiant = $resultatsEtudiant->first()->etudiant;
                $resultatComplet = $this->calculerResultatsComplets($etudiantId, $sessionId, true);

                $exportData[] = [
                    'etudiant' => [
                        'id' => $etudiant->id,
                        'matricule' => $etudiant->matricule,
                        'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                        'niveau' => $etudiant->niveau->nom ?? 'N/A',
                        'parcours' => $etudiant->parcours ? $etudiant->parcours->nom : null
                    ],
                    'resultats_academiques' => $resultatComplet['synthese'],
                    'decision' => $resultatComplet['decision']['code'],
                    'jury_validated' => $resultatsEtudiant->first()->jury_validated ?? false,
                    'date_derniere_modification' => $resultatsEtudiant->max('updated_at')
                ];
            }

            $rapport = [
                'configuration' => $config ? $config->toArray() : null,
                'statistiques' => $stats,
                'resultats_etudiants' => $exportData,
                'metadonnees' => [
                    'date_export' => now()->format('Y-m-d H:i:s'),
                    'export_par' => Auth::user()->name ?? 'Système',
                    'niveau_id' => $niveauId,
                    'parcours_id' => $parcoursId,
                    'session_id' => $sessionId
                ]
            ];

            Log::info('Export délibération généré', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'nb_etudiants' => count($exportData)
            ]);

            return $rapport;

        } catch (\Exception $e) {
            Log::error('Erreur export délibération', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    // ✅ MÉTHODES UTILITAIRES SUPPLÉMENTAIRES

    /**
     * Calcule les statistiques globales d'une session
     */
    public function calculerStatistiquesGlobales($sessionId, $niveauId = null, $parcoursId = null)
    {
        try {
            $query = ResultatFinal::where('session_exam_id', $sessionId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE);

            if ($niveauId) {
                $query->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                });
            }

            $resultats = $query->get();

            if ($resultats->isEmpty()) {
                return [
                    'total_etudiants' => 0,
                    'decisions' => [],
                    'moyennes' => [],
                    'credits' => []
                ];
            }

            // Grouper par étudiant
            $etudiantsStats = $resultats->groupBy('etudiant_id')->map(function($resultatsEtudiant) use ($sessionId) {
                $etudiantId = $resultatsEtudiant->first()->etudiant_id;

                try {
                    $calcul = $this->calculerResultatsComplets($etudiantId, $sessionId, true);
                    return [
                        'decision' => $calcul['decision']['code'],
                        'moyenne' => $calcul['synthese']['moyenne_generale'],
                        'credits' => $calcul['synthese']['credits_valides'],
                        'has_eliminatoire' => $calcul['synthese']['a_note_eliminatoire']
                    ];
                } catch (\Exception $e) {
                    Log::warning('Erreur calcul stats pour étudiant', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionId,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter();

            // Calculer les statistiques finales
            $decisions = $etudiantsStats->pluck('decision')->countBy();
            $moyennes = $etudiantsStats->pluck('moyenne')->filter();
            $credits = $etudiantsStats->pluck('credits')->filter();

            return [
                'total_etudiants' => $etudiantsStats->count(),
                'decisions' => [
                    'admis' => $decisions->get('admis', 0),
                    'rattrapage' => $decisions->get('rattrapage', 0),
                    'redoublant' => $decisions->get('redoublant', 0),
                    'exclus' => $decisions->get('exclus', 0)
                ],
                'moyennes' => [
                    'moyenne_generale' => $moyennes->isNotEmpty() ? round($moyennes->avg(), 2) : 0,
                    'moyenne_min' => $moyennes->isNotEmpty() ? round($moyennes->min(), 2) : 0,
                    'moyenne_max' => $moyennes->isNotEmpty() ? round($moyennes->max(), 2) : 0
                ],
                'credits' => [
                    'credits_moyen' => $credits->isNotEmpty() ? round($credits->avg(), 2) : 0,
                    'credits_min' => $credits->isNotEmpty() ? $credits->min() : 0,
                    'credits_max' => $credits->isNotEmpty() ? $credits->max() : 0
                ],
                'taux_reussite' => $etudiantsStats->count() > 0 ?
                    round(($decisions->get('admis', 0) / $etudiantsStats->count()) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques globales', [
                'session_id' => $sessionId,
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors du calcul des statistiques: ' . $e->getMessage());
        }
    }

    /**
     * Vérifie la cohérence des calculs selon logique médecine
     */
    public function validerCoherenceCalculsMedecine($etudiantId, $sessionId)
    {
        try {
            $resultats = $this->calculerResultatsComplets($etudiantId, $sessionId, true);

            $erreurs = [];
            $avertissements = [];

            // Vérifier cohérence credits/moyennes
            $synthese = $resultats['synthese'];

            if ($synthese['a_note_eliminatoire'] && $synthese['moyenne_generale'] > 0) {
                $erreurs[] = 'Incohérence: note éliminatoire présente mais moyenne > 0';
            }

            if ($synthese['credits_valides'] > $synthese['total_credits']) {
                $erreurs[] = 'Erreur: crédits validés > crédits totaux';
            }

            // Vérifier cohérence décision
            $decision = $resultats['decision']['code'];
            $session = $resultats['session'];

            if ($session['type'] === 'Normale') {
                if ($decision === 'admis' && $synthese['credits_valides'] < self::CREDIT_TOTAL_REQUIS) {
                    $erreurs[] = 'Incohérence S1: admis avec moins de 60 crédits';
                }
                if ($decision === 'admis' && $synthese['a_note_eliminatoire']) {
                    $avertissements[] = 'Attention S1: admis malgré note éliminatoire';
                }
            } else {
                if ($decision === 'admis' && $synthese['credits_valides'] < self::CREDIT_MINIMUM_SESSION2) {
                    $erreurs[] = 'Incohérence S2: admis avec moins de 40 crédits';
                }
                if ($decision === 'exclus' && !$synthese['a_note_eliminatoire'] && $synthese['credits_valides'] >= 20) {
                    $avertissements[] = 'Attention S2: exclusion sans note éliminatoire avec crédits suffisants';
                }
            }

            return [
                'valide' => empty($erreurs),
                'erreurs' => $erreurs,
                'avertissements' => $avertissements,
                'resultats' => $resultats
            ];

        } catch (\Exception $e) {
            return [
                'valide' => false,
                'erreurs' => ['Erreur lors de la validation: ' . $e->getMessage()],
                'avertissements' => [],
                'resultats' => null
            ];
        }
    }

    /**
     * Récupère les étudiants éligibles au rattrapage
     */
    public function getEtudiantsEligiblesRattrapage($sessionNormaleId, $niveauId = null, $parcoursId = null)
    {
        try {
            $query = ResultatFinal::where('session_exam_id', $sessionNormaleId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->where('decision', 'rattrapage');

            if ($niveauId) {
                $query->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                    $q->where('niveau_id', $niveauId);
                    if ($parcoursId) {
                        $q->where('parcours_id', $parcoursId);
                    }
                });
            }

            $etudiantsRattrapage = $query->distinct('etudiant_id')
                ->with(['etudiant'])
                ->get()
                ->pluck('etudiant_id')
                ->unique();

            return $etudiantsRattrapage->map(function($etudiantId) use ($sessionNormaleId) {
                try {
                    $resultats = $this->calculerResultatsComplets($etudiantId, $sessionNormaleId, true);
                    return [
                        'etudiant' => $resultats['etudiant'],
                        'synthese' => $resultats['synthese'],
                        'eligible' => true,
                        'motif_rattrapage' => $resultats['decision']['motif']
                    ];
                } catch (\Exception $e) {
                    Log::warning('Erreur calcul éligibilité rattrapage', [
                        'etudiant_id' => $etudiantId,
                        'session_id' => $sessionNormaleId,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter()->values();

        } catch (\Exception $e) {
            Log::error('Erreur récupération étudiants rattrapage', [
                'session_normale_id' => $sessionNormaleId,
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Erreur lors de la récupération des étudiants éligibles: ' . $e->getMessage());
        }
    }
}
