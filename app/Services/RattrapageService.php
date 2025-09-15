<?php

namespace App\Services;

use App\Models\EC;
use App\Models\UE;
use App\Models\Etudiant;
use App\Models\Manchette;
use App\Models\SessionExam;
use App\Models\CodeAnonymat;
use App\Models\ResultatFinal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RattrapageService
{
    /**
     * 🎯 MÉTHODE PRINCIPALE : Récupère les ECs non validés pour un étudiant
     * 
     * @param int $etudiantId
     * @param int $sessionNormaleId
     * @return array
     */
    public function getEcsNonValidesEtudiant($etudiantId, $sessionNormaleId)
    {
        try {
            // 1. Récupérer tous les résultats de l'étudiant en session normale
            $resultatsNormale = ResultatFinal::with('ec.ue')
                ->where('etudiant_id', $etudiantId)
                ->where('session_exam_id', $sessionNormaleId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->get();

            if ($resultatsNormale->isEmpty()) {
                return [
                    'ues_non_validees' => [],
                    'ecs_non_valides' => [],
                    'total_ecs_rattrapage' => 0,
                    'statistiques' => [
                        'ues_total' => 0,
                        'ues_validees' => 0,
                        'ues_non_validees' => 0
                    ]
                ];
            }

            // 2. Grouper par UE et analyser selon logique médecine
            $resultatsParUE = $resultatsNormale->groupBy('ec.ue_id');
            $uesNonValidees = [];
            $ecsNonValides = [];
            $statistiques = [
                'ues_total' => $resultatsParUE->count(),
                'ues_validees' => 0,
                'ues_non_validees' => 0
            ];

            foreach ($resultatsParUE as $ueId => $resultatsUE) {
                $ue = $resultatsUE->first()->ec->ue;
                
                // LOGIQUE MÉDECINE : UE validée si moyenne >= 10 ET aucune note = 0
                $hasNoteZero = $resultatsUE->contains('note', 0);
                $moyenneUE = $hasNoteZero ? 0 : round($resultatsUE->avg('note'), 2);
                $ueValidee = $moyenneUE >= 10 && !$hasNoteZero;

                if (!$ueValidee) {
                    // UE non validée -> tous ses ECs vont en rattrapage
                    $statistiques['ues_non_validees']++;
                    
                    $ecsUE = [];
                    foreach ($resultatsUE as $resultat) {
                        $ecsUE[] = [
                            'ec_id' => $resultat->ec_id,
                            'ec' => $resultat->ec,
                            'note_normale' => $resultat->note,
                            'est_eliminatoire' => $resultat->note == 0
                        ];
                        
                        $ecsNonValides[] = $resultat->ec_id;
                    }

                    $uesNonValidees[] = [
                        'ue_id' => $ueId,
                        'ue' => $ue,
                        'moyenne_normale' => $moyenneUE,
                        'a_note_zero' => $hasNoteZero,
                        'raison_non_validation' => $hasNoteZero ? 'Note éliminatoire (0)' : "Moyenne insuffisante ({$moyenneUE} < 10)",
                        'ecs' => $ecsUE,
                        'nb_ecs' => count($ecsUE)
                    ];
                } else {
                    $statistiques['ues_validees']++;
                }
            }

            return [
                'etudiant_id' => $etudiantId,
                'session_normale_id' => $sessionNormaleId,
                'ues_non_validees' => $uesNonValidees,
                'ecs_non_valides' => array_unique($ecsNonValides),
                'total_ecs_rattrapage' => count(array_unique($ecsNonValides)),
                'statistiques' => $statistiques
            ];

        } catch (\Exception $e) {
            Log::error('Erreur récupération ECs non validés', [
                'etudiant_id' => $etudiantId,
                'session_normale_id' => $sessionNormaleId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * 🏗️ MÉTHODE : Crée automatiquement les structures pour la session de rattrapage
     * 
     * @param int $examenId
     * @param int $sessionNormaleId
     * @param int $sessionRattrapageId
     * @param int $userId
     * @return array
     */
    public function creerStructuresRattrapage($examenId, $sessionNormaleId, $sessionRattrapageId, $userId)
    {
        try {
            DB::beginTransaction();

            $examen = \App\Models\Examen::findOrFail($examenId);
            
            // 1. Récupérer les étudiants éligibles au rattrapage
            $etudiantsEligibles = Etudiant::eligiblesRattrapage(
                $examen->niveau_id, 
                $examen->parcours_id, 
                $sessionNormaleId
            )->get();

            if ($etudiantsEligibles->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun étudiant éligible au rattrapage.',
                    'statistiques' => []
                ];
            }

            $manchettesCreees = 0;
            $codesCreés = 0;
            $erreurs = [];

            foreach ($etudiantsEligibles as $etudiant) {
                try {
                    // 2. Récupérer les ECs non validés pour cet étudiant
                    $ecsNonValides = $this->getEcsNonValidesEtudiant($etudiant->id, $sessionNormaleId);
                    
                    if (empty($ecsNonValides['ecs_non_valides'])) {
                        continue; // Cet étudiant n'a pas d'ECs à rattraper (ne devrait pas arriver)
                    }

                    // 3. Créer codes anonymat et manchettes SEULEMENT pour les ECs non validés
                    foreach ($ecsNonValides['ecs_non_valides'] as $ecId) {
                        // Créer ou récupérer le code anonymat pour cette session de rattrapage
                        $codeAnonymat = $this->creerCodeAnonymatRattrapage(
                            $examenId, 
                            $sessionRattrapageId, 
                            $ecId, 
                            $etudiant->id
                        );
                        
                        if ($codeAnonymat->wasRecentlyCreated) {
                            $codesCreés++;
                        }

                        // Créer la manchette si elle n'existe pas déjà
                        $manchetteExiste = Manchette::where([
                            'examen_id' => $examenId,
                            'session_exam_id' => $sessionRattrapageId,
                            'etudiant_id' => $etudiant->id,
                            'code_anonymat_id' => $codeAnonymat->id
                        ])->exists();

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

                } catch (\Exception $e) {
                    $erreurs[] = "Erreur étudiant {$etudiant->matricule}: " . $e->getMessage();
                    Log::error('Erreur création structures rattrapage étudiant', [
                        'etudiant_id' => $etudiant->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Structures de rattrapage créées pour {$etudiantsEligibles->count()} étudiants.",
                'statistiques' => [
                    'etudiants_traites' => $etudiantsEligibles->count(),
                    'codes_crees' => $codesCreés,
                    'manchettes_creees' => $manchettesCreees,
                    'erreurs' => count($erreurs)
                ],
                'erreurs' => $erreurs
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création structures rattrapage', [
                'examen_id' => $examenId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage(),
                'statistiques' => []
            ];
        }
    }

    /**
     * 🔗 MÉTHODE : Crée un code anonymat spécifique pour le rattrapage
     * 
     * @param int $examenId
     * @param int $sessionRattrapageId
     * @param int $ecId
     * @param int $etudiantId
     * @return CodeAnonymat
     */
    private function creerCodeAnonymatRattrapage($examenId, $sessionRattrapageId, $ecId, $etudiantId)
    {
        // Générer un code unique pour le rattrapage
        $baseCode = "R" . str_pad($ecId, 2, '0', STR_PAD_LEFT) . str_pad($etudiantId, 4, '0', STR_PAD_LEFT);
        
        // Vérifier unicité
        $counter = 1;
        $codeComplet = $baseCode;
        
        while (CodeAnonymat::where('examen_id', $examenId)
            ->where('session_exam_id', $sessionRattrapageId)
            ->where('code_complet', $codeComplet)
            ->exists()) {
            $codeComplet = $baseCode . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return CodeAnonymat::firstOrCreate([
            'examen_id' => $examenId,
            'session_exam_id' => $sessionRattrapageId,
            'ec_id' => $ecId,
            'code_complet' => $codeComplet
        ], [
            'sequence' => $etudiantId * 1000 + $ecId
        ]);
    }

    /**
     * 📊 MÉTHODE : Fusionne les meilleures notes entre session normale et rattrapage
     * 
     * @param int $etudiantId
     * @param int $examenId
     * @param int $sessionNormaleId
     * @param int $sessionRattrapageId
     * @return array
     */
    public function fusionnerMeilleuresNotes($etudiantId, $examenId, $sessionNormaleId, $sessionRattrapageId)
    {
        try {
            DB::beginTransaction();

            // Récupérer tous les résultats des deux sessions
            $resultatsNormale = ResultatFinal::where('etudiant_id', $etudiantId)
                ->where('examen_id', $examenId)
                ->where('session_exam_id', $sessionNormaleId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->get()
                ->keyBy('ec_id');

            $resultatsRattrapage = ResultatFinal::where('etudiant_id', $etudiantId)
                ->where('examen_id', $examenId)
                ->where('session_exam_id', $sessionRattrapageId)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->get()
                ->keyBy('ec_id');

            $fusionsEffectuees = [];
            $notesModifiees = 0;

            // Pour chaque EC ayant des résultats en rattrapage, comparer avec la normale
            foreach ($resultatsRattrapage as $ecId => $resultatRattrapage) {
                $resultatNormale = $resultatsNormale->get($ecId);
                
                if ($resultatNormale) {
                    $noteNormale = $resultatNormale->note;
                    $noteRattrapage = $resultatRattrapage->note;
                    
                    // Garder la meilleure note (sauf si rattrapage = 0 et normale > 0)
                    if ($noteRattrapage == 0 && $noteNormale > 0) {
                        // Note éliminatoire en rattrapage -> garder 0
                        $noteFusionnee = 0;
                        $sessionOrigine = 'rattrapage';
                    } else {
                        $noteFusionnee = max($noteNormale, $noteRattrapage);
                        $sessionOrigine = $noteRattrapage >= $noteNormale ? 'rattrapage' : 'normale';
                    }

                    // Mettre à jour le résultat de rattrapage avec la meilleure note
                    if ($resultatRattrapage->note != $noteFusionnee) {
                        $resultatRattrapage->update([
                            'note' => $noteFusionnee,
                            'modifie_par' => Auth::id(),
                            'status_history' => array_merge($resultatRattrapage->status_history ?? [], [[
                                'action' => 'fusion_meilleures_notes',
                                'note_normale' => $noteNormale,
                                'note_rattrapage' => $noteRattrapage,
                                'note_finale' => $noteFusionnee,
                                'session_origine' => $sessionOrigine,
                                'date' => now()->toDateTimeString(),
                                'user_id' => Auth::id()
                            ]])
                        ]);
                        $notesModifiees++;
                    }

                    $fusionsEffectuees[$ecId] = [
                        'ec_id' => $ecId,
                        'note_normale' => $noteNormale,
                        'note_rattrapage' => $noteRattrapage,
                        'note_finale' => $noteFusionnee,
                        'session_origine' => $sessionOrigine,
                        'modification_appliquee' => $resultatRattrapage->note != $noteFusionnee
                    ];
                }
            }

            // Recalculer la décision finale
            $nouvelleDecision = ResultatFinal::determinerDecisionRattrapage_LogiqueMedecine($etudiantId, $sessionRattrapageId);
            
            // Appliquer la nouvelle décision
            ResultatFinal::where('etudiant_id', $etudiantId)
                ->where('examen_id', $examenId)
                ->where('session_exam_id', $sessionRattrapageId)
                ->update(['decision' => $nouvelleDecision]);

            DB::commit();

            return [
                'success' => true,
                'notes_modifiees' => $notesModifiees,
                'nouvelle_decision' => $nouvelleDecision,
                'fusions_effectuees' => $fusionsEffectuees,
                'nouvelle_moyenne' => ResultatFinal::calculerMoyenneGenerale_LogiqueMedecine($etudiantId, $sessionRattrapageId)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur fusion meilleures notes', [
                'etudiant_id' => $etudiantId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la fusion : ' . $e->getMessage()
            ];
        }
    }

    /**
     * 📋 MÉTHODE : Génère un rapport de comparaison session normale vs rattrapage
     * 
     * @param int $etudiantId
     * @param int $examenId
     * @param int $sessionNormaleId
     * @param int $sessionRattrapageId
     * @return array
     */
    public function genererRapportComparatif($etudiantId, $examenId, $sessionNormaleId, $sessionRattrapageId)
    {
        try {
            $etudiant = Etudiant::findOrFail($etudiantId);
            
            // Analyser la session normale
            $analyseNormale = $this->analyserResultatsSession($etudiantId, $sessionNormaleId);
            
            // Analyser la session de rattrapage
            $analyseRattrapage = $this->analyserResultatsSession($etudiantId, $sessionRattrapageId);
            
            // Calculer l'amélioration
            $amelioration = [
                'moyenne' => $analyseRattrapage['moyenne_generale'] - $analyseNormale['moyenne_generale'],
                'credits' => $analyseRattrapage['credits_valides'] - $analyseNormale['credits_valides'],
                'ues_validees' => count($analyseRattrapage['ues_validees']) - count($analyseNormale['ues_validees'])
            ];

            return [
                'etudiant' => $etudiant,
                'session_normale' => $analyseNormale,
                'session_rattrapage' => $analyseRattrapage,
                'amelioration' => $amelioration,
                'decision_finale' => $analyseRattrapage['decision'] ?? 'non_definie',
                'recommandations' => $this->genererRecommandations($analyseNormale, $analyseRattrapage, $amelioration)
            ];

        } catch (\Exception $e) {
            Log::error('Erreur génération rapport comparatif', [
                'etudiant_id' => $etudiantId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Analyse les résultats d'une session
     */
    private function analyserResultatsSession($etudiantId, $sessionId)
    {
        $resultats = ResultatFinal::with('ec.ue')
            ->where('etudiant_id', $etudiantId)
            ->where('session_exam_id', $sessionId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->get();

        if ($resultats->isEmpty()) {
            return [
                'moyenne_generale' => 0,
                'credits_valides' => 0,
                'ues_validees' => [],
                'ues_non_validees' => [],
                'has_note_eliminatoire' => false,
                'decision' => null
            ];
        }

        $resultatsParUE = $resultats->groupBy('ec.ue_id');
        $uesValidees = [];
        $uesNonValidees = [];
        $creditsValides = 0;
        $moyennesUE = [];
        $hasNoteEliminatoire = false;

        foreach ($resultatsParUE as $ueId => $notesUE) {
            $ue = $notesUE->first()->ec->ue;
            $hasZero = $notesUE->contains('note', 0);
            
            if ($hasZero) {
                $hasNoteEliminatoire = true;
                $moyenneUE = 0;
                $uesNonValidees[] = $ue;
            } else {
                $moyenneUE = round($notesUE->avg('note'), 2);
                if ($moyenneUE >= 10) {
                    $uesValidees[] = $ue;
                    $creditsValides += $ue->credits ?? 0;
                } else {
                    $uesNonValidees[] = $ue;
                }
            }
            
            $moyennesUE[] = $moyenneUE;
        }

        return [
            'moyenne_generale' => count($moyennesUE) > 0 ? round(array_sum($moyennesUE) / count($moyennesUE), 2) : 0,
            'credits_valides' => $creditsValides,
            'ues_validees' => $uesValidees,
            'ues_non_validees' => $uesNonValidees,
            'has_note_eliminatoire' => $hasNoteEliminatoire,
            'decision' => $resultats->first()->decision ?? null
        ];
    }

    /**
     * Génère des recommandations basées sur l'analyse comparative
     */
    private function genererRecommandations($analyseNormale, $analyseRattrapage, $amelioration)
    {
        $recommandations = [];

        if ($amelioration['moyenne'] > 0) {
            $recommandations[] = "✅ Amélioration significative de la moyenne générale (+{$amelioration['moyenne']} points).";
        } elseif ($amelioration['moyenne'] < 0) {
            $recommandations[] = "⚠️ Baisse de la moyenne générale ({$amelioration['moyenne']} points).";
        }

        if ($amelioration['credits'] > 0) {
            $recommandations[] = "📈 Gain de {$amelioration['credits']} crédits supplémentaires.";
        }

        if ($analyseRattrapage['has_note_eliminatoire']) {
            $recommandations[] = "🚨 Note éliminatoire détectée - Impact critique sur la décision finale.";
        }

        $decision = $analyseRattrapage['decision'];
        switch ($decision) {
            case 'admis':
                $recommandations[] = "🎉 ADMIS - Objectif atteint avec succès.";
                break;
            case 'redoublant':
                $recommandations[] = "📚 REDOUBLANT - Nécessité de reprendre l'année.";
                break;
            case 'exclus':
                $recommandations[] = "❌ EXCLU - Situation critique nécessitant un recours.";
                break;
        }

        return $recommandations;
    }
}