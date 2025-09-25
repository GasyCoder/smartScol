<?php

namespace App\Services;

use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\SessionExam;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ResultatsExport;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    /**
     * ✅ Export PDF format officiel (nouveau)
     */
    public function exporterPDFOfficiel($resultats, $niveau = null, $anneeUniv = null, $parcours = null, $session = null, $optionsExport = [])
    {
        try {
            if (empty($resultats)) {
                throw new \Exception('Aucun résultat disponible pour l\'export PDF.');
            }

            // Trier les résultats par ordre de mérite (moyenne décroissante)
            $resultatsOrdonnes = collect($resultats)->sortByDesc('moyenne_generale')->values()->toArray();

            $donnees = [
                'resultats' => $resultatsOrdonnes,
                'niveau' => $niveau,
                'parcours' => $parcours,
                'annee_universitaire' => $anneeUniv,
                'session' => $session,
                'statistiques' => $this->calculerStats($resultats),
                'date_export' => now()->format('d/m/Y H:i'),
                'export_par' => Auth::user()->name ?? 'Système',
                'titre_special' => $optionsExport['titre_special'] ?? null,
                'conditions' => $optionsExport['conditions'] ?? 'Sous réserve de validation de Stage Hospitalier',
                'doyen_nom' => $optionsExport['doyen_nom'] ?? 'RAKOTOMALALA Jules Robert'
            ];

            $nomFichier = $this->genererNomFichierPDF($niveau, $parcours, $anneeUniv, $optionsExport['suffix'] ?? null);

            $pdf = Pdf::loadView('exports.resultats-pdf-officiel', $donnees)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'margin_top' => 15,
                    'margin_bottom' => 20,
                    'margin_left' => 20,
                    'margin_right' => 20,
                ]);

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $nomFichier);
            
        } catch (\Exception $e) {
            Log::error('Erreur export PDF officiel: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Export PDF admis uniquement - Format officiel
     */
    public function exporterAdmisPDFOfficiel($resultats, $niveau = null, $anneeUniv = null, $parcours = null, $session = null, $optionsExport = [])
    {
        try {
            // Filtrer pour ne garder que les admis et ordonner par mérite
            $resultatsAdmis = collect($resultats)
                ->filter(function($resultat) {
                    return ($resultat['decision'] ?? '') === 'admis';
                })
                ->sortByDesc('moyenne_generale')
                ->values()
                ->toArray();

            if (empty($resultatsAdmis)) {
                throw new \Exception('Aucun étudiant admis à exporter.');
            }

            $optionsExport['titre_special'] = 'LISTE DES ETUDIANTS ADMIS EN ' . strtoupper($niveau->nom ?? '');
            $optionsExport['suffix'] = 'Admis';

            return $this->exporterPDFOfficiel($resultatsAdmis, $niveau, $anneeUniv, $parcours, $session, $optionsExport);
            
        } catch (\Exception $e) {
            Log::error('Erreur export PDF admis officiel: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Export Excel avec le nouveau format de l'image
     */
    public function exporterExcel($resultats, $uesStructure = [], $niveau = null, $parcours = null, $anneeUniv = null, $session = null, $deliberationParams = [])
    {
        try {
            if (empty($resultats)) {
                throw new \Exception('Aucun résultat disponible pour l\'export.');
            }

            // ✅ NOUVEAU : Vérifier si la délibération a été appliquée
            $deliberationAppliquee = $this->verifierDeliberationAppliquee($session);
            
            // ✅ LOGIQUE DIFFÉRENTE selon la délibération
            if ($deliberationAppliquee) {
                // 🔥 DÉLIBÉRATION APPLIQUÉE : Exporter avec les corrections académiques
                return $this->exporterAvecDeliberation($resultats, $uesStructure, $niveau, $parcours, $anneeUniv, $session, $deliberationParams);
            } else {
                // 📊 DÉLIBÉRATION NON APPLIQUÉE : Exporter les résultats bruts
                return $this->exporterSansDeliberation($resultats, $uesStructure, $niveau, $parcours, $anneeUniv, $session);
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur export Excel: ' . $e->getMessage());
            throw $e;
        }
    }



    /**
     * ✅ Export AVEC délibération appliquée (résultats corrigés)
     */
    private function exporterAvecDeliberation($resultats, $uesStructure, $niveau, $parcours, $anneeUniv, $session, $deliberationParams)
    {
        Log::info('🔔 Export AVEC délibération appliquée', [
            'session_id' => $session->id ?? null,
            'session_type' => $session->type ?? 'inconnue',
            'nb_resultats' => count($resultats)
        ]);

        // ✅ RÉCUPÉRER le seuil dynamiquement
        $seuilCredits = $this->determinerSeuilCredits($session, $deliberationParams);
        
        // ✅ VALIDATION et correction automatique des résultats
        $resultatsCorrigés = $this->validerEtCorrigerResultats($resultats, $seuilCredits);
        
        // ✅ ORDRE crédits avant moyenne
        $resultatsFinaux = $this->corrigerResultatsPourExport($resultatsCorrigés['resultats_corriges']);

        $nomFichier = $this->genererNomFichierExcel($niveau, $parcours, $session, $anneeUniv, 'Delibere');
        
        // ✅ LOGGER les corrections appliquées
        if (!$resultatsCorrigés['coherent']) {
            Log::info('📝 Corrections appliquées lors de l\'export délibéré', [
                'nb_corrections' => count($resultatsCorrigés['erreurs']),
                'seuil_credits' => $seuilCredits,
                'session_type' => $session->type ?? 'Normale'
            ]);
        }
        
        return Excel::download(
            new ResultatsExport($resultatsFinaux, $uesStructure, $session, $niveau, $parcours, $anneeUniv), 
            $nomFichier
        );
    }


    /**
     * ✅ Export SANS délibération (résultats bruts)
     */
    private function exporterSansDeliberation($resultats, $uesStructure, $niveau, $parcours, $anneeUniv, $session)
    {
        Log::info('📋 Export SANS délibération (résultats bruts)', [
            'session_id' => $session->id ?? null,
            'session_type' => $session->type ?? 'inconnue',
            'nb_resultats' => count($resultats)
        ]);

        // ❌ PAS de correction académique - résultats bruts
        $resultatsBruts = collect($resultats)->map(function($resultat) {
            return array_merge($resultat, [
                'decision_origine' => 'brute', // Marquer comme résultat brut
                'decision_corrigee' => false
            ]);
        })->toArray();

        $nomFichier = $this->genererNomFichierExcel($niveau, $parcours, $session, $anneeUniv, 'Brut');
        
        return Excel::download(
            new ResultatsExport($resultatsBruts, $uesStructure, $session, $niveau, $parcours, $anneeUniv), 
            $nomFichier
        );
    }


    private function verifierDeliberationAppliquee($session): bool
    {
        try {
            if (!$session || !$session->id) {
                return false;
            }

            // ✅ CORRECTION : Toujours recharger depuis la base pour avoir l'état le plus récent
            $sessionComplete = SessionExam::find($session->id);

            if (!$sessionComplete) {
                Log::warning('Session non trouvée lors de la vérification délibération', [
                    'session_id' => $session->id
                ]);
                return false;
            }

            Log::info('Vérification état délibération', [
                'session_id' => $sessionComplete->id,
                'type' => $sessionComplete->type,
                'deliberation_appliquee' => $sessionComplete->deliberation_appliquee,
                'date_deliberation' => $sessionComplete->date_deliberation
            ]);

            return (bool) $sessionComplete->deliberation_appliquee;
            
        } catch (\Exception $e) {
            Log::warning('Erreur vérification délibération: ' . $e->getMessage(), [
                'session_id' => $session->id ?? 'null'
            ]);
            return false;
        }
    }

    
    /**
     * ✅ Export Excel admis uniquement
     */
    public function exporterExcelAdmis($resultats, $uesStructure = [], $niveau = null, $parcours = null, $anneeUniv = null, $session = null, $deliberationParams = [])
    {
        try {
            // ✅ Vérifier si la délibération a été appliquée
            $deliberationAppliquee = $this->verifierDeliberationAppliquee($session);
            
            if (!$deliberationAppliquee) {
                throw new \Exception('❌ Impossible d\'exporter la liste des admis : la délibération n\'a pas encore été appliquée à cette session.');
            }

            // ✅ RÉCUPÉRER le seuil dynamiquement (seulement si délibération appliquée)
            $seuilCredits = $this->determinerSeuilCredits($session, $deliberationParams);
            
            // ✅ VALIDATION préalable
            $resultatsValidés = $this->validerEtCorrigerResultats($resultats, $seuilCredits);
            
            // ✅ FILTRAGE strict : seulement les vrais admis (après délibération)
            $resultatsAdmis = collect($resultatsValidés['resultats_corriges'])->filter(function($resultat) {
                $decision = $resultat['decision'] ?? '';
                $moyenneGenerale = $resultat['moyenne_generale'] ?? 0;
                $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;
                
                // ✅ CRITÈRES STRICTS pour être dans la liste des admis
                return $decision === 'admis' && 
                       $moyenneGenerale >= 10.00 && 
                       !$hasNoteEliminatoire;
            })->values()->toArray();

            if (empty($resultatsAdmis)) {
                throw new \Exception('Aucun étudiant véritablement admis selon les critères académiques (moyenne ≥ 10 ET crédits suffisants ET pas de note éliminatoire).');
            }

            $resultatsCorrigés = $this->corrigerResultatsPourExport($resultatsAdmis);
            $nomFichier = $this->genererNomFichierExcel($niveau, $parcours, $session, $anneeUniv, 'Admis');
            
            Log::info('✅ Export liste admis après délibération', [
                'nb_admis' => count($resultatsAdmis),
                'session' => $session->type ?? 'inconnue'
            ]);
            
            return Excel::download(
                new ResultatsExport($resultatsCorrigés, $uesStructure, $session, $niveau, $parcours, $anneeUniv), 
                $nomFichier
            );
            
        } catch (\Exception $e) {
            Log::error('Erreur export Excel admis: ' . $e->getMessage());
            throw $e;
        }
    }


    private function determinerSeuilCredits($session = null, $deliberationParams = [])
    {
        try {
            // ✅ 1. PRIORITÉ : Paramètres de délibération passés
            if (!empty($deliberationParams)) {
                $sessionType = $session->type ?? 'Normale';
                
                if ($sessionType === 'Normale') {
                    return $deliberationParams['credits_admission_s1'] ?? 60;
                } else {
                    return $deliberationParams['credits_admission_s2'] ?? 40;
                }
            }
            
            // ✅ 2. FALLBACK : Basé sur le type de session
            if ($session) {
                if ($session->type === 'Rattrapage') {
                    return 40; // Session 2 généralement plus permissive
                }
                return 60; // Session 1 normale
            }
            
            // ✅ 3. FALLBACK ultime : Valeur par défaut directe
            return 60;
            
        } catch (\Exception $e) {
            Log::warning('Erreur détermination seuil crédits, utilisation valeur par défaut', [
                'error' => $e->getMessage(),
                'session_type' => $session->type ?? 'inconnue'
            ]);
            
            // ✅ Valeur sécurisée par défaut
            return 60;
        }
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Validation et correction des résultats
     */
    private function validerEtCorrigerResultats($resultats, $seuilCredits = 45)
    {
        $erreurs = [];
        $corrections = [];

        foreach ($resultats as $index => $resultat) {
            $etudiant = $resultat['etudiant'];
            $moyenneGenerale = $resultat['moyenne_generale'] ?? 0;
            $creditsValides = $resultat['credits_valides'] ?? 0;
            $totalCredits = $resultat['total_credits'] ?? 60;
            $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;
            $decisionActuelle = $resultat['decision'] ?? null;

            // ✅ CALCUL de la décision correcte selon la logique académique
            $decisionCorrecte = $this->calculerDecisionCorrecte(
                $moyenneGenerale, 
                $creditsValides, 
                $totalCredits, 
                $hasNoteEliminatoire, 
                $seuilCredits
            );

            // ✅ DÉTECTION des incohérences
            if ($decisionActuelle !== $decisionCorrecte) {
                $erreurs[] = [
                    'ligne' => $index + 1,
                    'etudiant' => $etudiant->matricule ?? 'N/A',
                    'nom' => $etudiant->nom ?? 'N/A',
                    'decision_actuelle' => $decisionActuelle,
                    'decision_correcte' => $decisionCorrecte,
                    'moyenne' => $moyenneGenerale,
                    'credits' => "{$creditsValides}/{$totalCredits}",
                    'has_eliminatoire' => $hasNoteEliminatoire
                ];

                // ✅ APPLIQUER la correction
                $corrections[] = array_merge($resultat, [
                    'decision' => $decisionCorrecte,
                    'decision_corrigee' => true,
                    'decision_originale' => $decisionActuelle
                ]);
            } else {
                $corrections[] = $resultat;
            }
        }

        return [
            'coherent' => empty($erreurs),
            'erreurs' => $erreurs,
            'resultats_corriges' => $corrections,
            'nb_corrections' => count($erreurs)
        ];
    }

    

    /**
     * ✅ LOGIQUE DE DÉCISION ACADÉMIQUE (identique à celle du composant Livewire)
     */
    private function calculerDecisionCorrecte($moyenneGenerale, $creditsValides, $totalCredits, $hasNoteEliminatoire, $seuilCredits)
    {
        // ✅ PRIORITÉ 1 : Note éliminatoire = jamais admis
        if ($hasNoteEliminatoire) {
            return 'rattrapage';
        }
        
        // ✅ PRIORITÉ 2 : RÈGLE STRICTE pour être admis
        // Il faut TOUJOURS moyenne >= 10 ET crédits >= seuil
        if ($moyenneGenerale >= 10.0 && $creditsValides >= $seuilCredits) {
            return 'admis';
        }
        
        // ✅ PRIORITÉ 3 : Si moyenne < 10, jamais admis
        if ($moyenneGenerale < 10.0) {
            if ($moyenneGenerale < 8.0) {
                return 'redoublant';
            }
            return 'rattrapage';
        }
        
        // ✅ PRIORITÉ 4 : Si moyenne >= 10 mais crédits insuffisants
        if ($creditsValides < $seuilCredits) {
            return 'rattrapage';
        }
        
        // ✅ Par défaut
        return 'rattrapage';
    }
    /**
     * ✅ NOUVELLE MÉTHODE : Corriger l'ordre d'affichage crédits avant moyenne
     */
   private function corrigerResultatsPourExport($resultats)
    {
        return collect($resultats)->map(function($resultat) {
            $creditsValides = $resultat['credits_valides'] ?? 0;
            $totalCredits = $resultat['total_credits'] ?? 60;
            $moyenneGenerale = $resultat['moyenne_generale'] ?? 0;
            
            // ✅ NOUVEAU : Calculer détails crédits par UE
            $detailsCreditsUE = $this->calculerDetailsCreditsUE($resultat);
            
            return array_merge($resultat, [
                // ✅ ORDRE : Crédits avant moyenne pour l'export Excel
                'credits_valides' => $creditsValides,
                'total_credits' => $totalCredits,
                'moyenne_generale' => $moyenneGenerale,
                'tous_credits_valides' => $creditsValides >= $totalCredits,
                
                // ✅ NOUVEAU : Détails crédits par UE pour Excel
                'details_credits_ue' => $detailsCreditsUE['resume'],
                'credits_ue_detailles' => $detailsCreditsUE['details'],
                'nb_ue_validees' => $detailsCreditsUE['stats']['nb_ue_validees'],
                'nb_ue_totales' => $detailsCreditsUE['stats']['nb_ue_totales'],
                
                'rang' => null
            ]);
        })
        ->sortBy([
            ['tous_credits_valides', 'desc'], // Priorité : tous crédits validés
            ['moyenne_generale', 'desc']      // Puis par moyenne décroissante
        ])
        ->values()
        ->map(function($resultat, $index) {
            $resultat['rang'] = $index + 1;
            return $resultat;
        })
        ->toArray();
    }


    private function calculerDetailsCreditsUE($resultat)
    {
        $detailsUE = $resultat['details_ue'] ?? [];
        $resume = [];
        $details = [];
        $nbUEValidees = 0;
        $nbUETotales = 0;
        
        foreach ($detailsUE as $ueDetail) {
            $nbUETotales++;
            $ueNom = $ueDetail['ue_abr'] ?? $ueDetail['ue_nom'] ?? 'UE';
            $creditsUE = $ueDetail['ue_credits'] ?? 0;
            $ueValidee = $ueDetail['validee'] ?? false;
            $creditsUEValides = $ueValidee ? $creditsUE : 0;
            
            if ($ueValidee) {
                $nbUEValidees++;
            }
            
            // ✅ Calculer crédits EC pour cette UE
            $creditsECValides = 0;
            $creditsECTotaux = 0;
            $detailsEC = [];
            
            foreach ($ueDetail['notes_ec'] ?? [] as $noteEC) {
                $creditsEC = $noteEC['credits_ec'] ?? 0;
                $ecValidee = $noteEC['ec_validee'] ?? false;
                
                if ($ecValidee) {
                    $creditsECValides += $creditsEC;
                }
                $creditsECTotaux += $creditsEC;
                
                $detailsEC[] = [
                    'nom' => $noteEC['ec_abr'] ?? $noteEC['ec_nom'] ?? 'EC',
                    'note' => $noteEC['note'] ?? 0,
                    'credits' => $creditsEC,
                    'validee' => $ecValidee
                ];
            }
            
            // ✅ Résumé pour affichage simple
            $resume[] = "{$ueNom}:{$creditsUEValides}/{$creditsUE}";
            
            // ✅ Détails complets pour export Excel
            $details[] = [
                'ue_nom' => $ueNom,
                'ue_moyenne' => $ueDetail['moyenne_ue'] ?? 0,
                'ue_validee' => $ueValidee,
                'credits_ue_valides' => $creditsUEValides,
                'credits_ue_totaux' => $creditsUE,
                'credits_ec_valides' => $creditsECValides,
                'credits_ec_totaux' => $creditsECTotaux,
                'details_ec' => $detailsEC,
                'has_note_eliminatoire' => $ueDetail['has_note_eliminatoire'] ?? false
            ];
        }
        
        return [
            'resume' => implode(' | ', $resume), // Format: "UE1:6/6 | UE2:4/6 | UE3:0/6"
            'details' => $details,
            'stats' => [
                'nb_ue_validees' => $nbUEValidees,
                'nb_ue_totales' => $nbUETotales,
                'taux_validation_ue' => $nbUETotales > 0 ? round(($nbUEValidees / $nbUETotales) * 100, 1) : 0
            ]
        ];
    }

    /**
     * ✅ Export PDF simple (garder l'existant pour compatibilité)
     */
    public function exporterPDF($resultats, $niveau = null, $anneeUniv = null, $parcours = null, $uesStructure = [])
    {
        try {
            if (empty($resultats)) {
                throw new \Exception('Aucun résultat disponible pour l\'export PDF.');
            }

            $donnees = [
                'resultats' => $resultats,
                'niveau' => $niveau,
                'parcours' => $parcours,
                'annee_universitaire' => $anneeUniv,
                'ues_structure' => $uesStructure,
                'statistiques' => $this->calculerStats($resultats),
                'date_export' => now()->format('d/m/Y H:i'),
                'export_par' => Auth::user()->name ?? 'Système'
            ];

            $nomFichier = $this->genererNomFichierPDF($niveau, $parcours, $anneeUniv);

            $pdf = Pdf::loadView('exports.resultats-pdf', $donnees)
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $nomFichier);
            
        } catch (\Exception $e) {
            Log::error('Erreur export PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Export PDF admis uniquement (format détaillé ancien)
     */
    public function exporterAdmisPDF($resultats, $niveau = null, $anneeUniv = null, $parcours = null, $uesStructure = [])
    {
        try {
            // Filtrer pour ne garder que les admis
            $resultatsAdmis = collect($resultats)->filter(function($resultat) {
                return ($resultat['decision'] ?? '') === 'admis';
            })->values()->toArray();

            if (empty($resultatsAdmis)) {
                throw new \Exception('Aucun étudiant admis à exporter.');
            }

            $donnees = [
                'resultats' => $resultatsAdmis,
                'niveau' => $niveau,
                'parcours' => $parcours,
                'annee_universitaire' => $anneeUniv,
                'ues_structure' => $uesStructure,
                'statistiques' => $this->calculerStats($resultatsAdmis),
                'date_export' => now()->format('d/m/Y H:i'),
                'export_par' => Auth::user()->name ?? 'Système',
                'titre_special' => 'LISTE DES ADMIS'
            ];

            $nomFichier = $this->genererNomFichierPDF($niveau, $parcours, $anneeUniv, 'Admis');

            $pdf = Pdf::loadView('exports.resultats-pdf', $donnees)
                ->setPaper('a4', 'landscape');

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $nomFichier);
            
        } catch (\Exception $e) {
            Log::error('Erreur export PDF admis: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Générer nom de fichier Excel descriptif
     */
    private function genererNomFichierExcel($niveau = null, $parcours = null, $session = null, $anneeUniv = null, $suffix = null)
    {
        $parts = ['Resultats'];
        
        if ($session) {
            $parts[] = $session->type === 'Normale' ? 'Session1' : 'Session2';
        }
        
        if ($niveau) {
            $parts[] = str_replace(' ', '_', $niveau->nom);
        }
        
        if ($parcours) {
            $parts[] = str_replace(' ', '_', $parcours->nom);
        }
        
        if ($anneeUniv) {
            $parts[] = str_replace(['/', ' '], ['_', '_'], $anneeUniv->libelle);
        }
        
        if ($suffix) {
            $parts[] = $suffix;
        }
        
        $parts[] = now()->format('Y-m-d_H-i');
        
        return implode('_', $parts) . '.xlsx';
    }

    /**
     * ✅ Générer nom de fichier PDF descriptif
     */
    private function genererNomFichierPDF($niveau = null, $parcours = null, $anneeUniv = null, $suffix = null)
    {
        $parts = ['Liste'];
        
        if ($suffix === 'Admis') {
            $parts[] = 'Admis';
        } else {
            $parts[] = 'Resultats';
        }
        
        if ($niveau) {
            $parts[] = str_replace(' ', '_', $niveau->nom);
        }
        
        if ($parcours) {
            $parts[] = str_replace(' ', '_', $parcours->nom);
        }
        
        if ($anneeUniv) {
            $parts[] = str_replace(['/', ' '], ['_', '_'], $anneeUniv->libelle);
        }
        
        $parts[] = now()->format('Y-m-d_H-i');
        
        return implode('_', $parts) . '.pdf';
    }

    /**
     * ✅ Calculer statistiques complètes
     */
    private function calculerStats($resultats)
    {
        $total = count($resultats);
        $decisions = collect($resultats)->pluck('decision');
        $moyennes = collect($resultats)->pluck('moyenne_generale')->filter();
        $creditsTotal = collect($resultats)->sum('credits_valides');
        
        $admis = $decisions->filter(fn($d) => $d === 'admis')->count();
        $rattrapage = $decisions->filter(fn($d) => $d === 'rattrapage')->count();
        $redoublant = $decisions->filter(fn($d) => $d === 'redoublant')->count();
        $exclus = $decisions->filter(fn($d) => $d === 'exclus')->count();
        
        return [
            'total_etudiants' => $total,
            'admis' => $admis,
            'rattrapage' => $rattrapage,
            'redoublant' => $redoublant,
            'exclus' => $exclus,
            'taux_reussite' => $total > 0 ? round(($admis / $total) * 100, 1) : 0,
            'moyenne_promo' => $moyennes->count() > 0 ? round($moyennes->avg(), 2) : 0,
            'credits_moyen' => $total > 0 ? round($creditsTotal / $total, 1) : 0,
            'etudiants_avec_note_eliminatoire' => collect($resultats)->where('has_note_eliminatoire', true)->count(),
            'etudiants_jury_validated' => collect($resultats)->where('jury_validated', true)->count(),
        ];
    }

    /**
     * ✅ Convertir nombres en lettres (utilitaire)
     */
    public function nombreEnLettres($nombre)
    {
        $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
        $dixaines = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
        $dizaines = ['dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
        
        if ($nombre < 10) {
            return $unites[$nombre];
        } elseif ($nombre < 20) {
            return $dizaines[$nombre - 10];
        } elseif ($nombre < 100) {
            $dix = intval($nombre / 10);
            $unite = $nombre % 10;
            if ($unite == 0) {
                return $dixaines[$dix];
            } else {
                return $dixaines[$dix] . '-' . $unites[$unite];
            }
        } elseif ($nombre < 1000) {
            $cent = intval($nombre / 100);
            $reste = $nombre % 100;
            $result = '';
            if ($cent == 1) {
                $result = 'cent';
            } else {
                $result = $unites[$cent] . ' cent';
            }
            if ($reste > 0) {
                $result .= ' ' . $this->nombreEnLettres($reste);
            }
            return $result;
        }
        
        return (string) $nombre; // Fallback pour nombres > 999
    }

    /**
     * ✅ Convertir numéro de mois en nom français
     */
    public function moisEnFrancais($numeroMois)
    {
        $mois = [
            '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
            '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
            '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
        ];
        
        return $mois[$numeroMois] ?? 'Mois invalide';
    }

    /**
     * ✅ Export rapide depuis le composant Livewire
     */
    public function exportRapideExcel($resultats, $uesStructure, $sessionType = 'Session1')
    {
        try {
            $nomFichier = "Export_Rapide_{$sessionType}_" . now()->format('Y-m-d_H-i') . '.xlsx';
            
            // Session mock pour l'export rapide
            $sessionMock = (object) [
                'type' => $sessionType === 'Session1' ? 'Normale' : 'Rattrapage',
                'id' => null
            ];
            
            return Excel::download(
                new ResultatsExport($resultats, $uesStructure, $sessionMock), 
                $nomFichier
            );
            
        } catch (\Exception $e) {
            Log::error('Erreur export rapide Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Valider les données avant export
     */
    public function validerDonneesExport($resultats, $uesStructure = [])
    {
        $erreurs = [];
        
        if (empty($resultats)) {
            $erreurs[] = 'Aucun résultat fourni pour l\'export';
        }
        
        if (empty($uesStructure)) {
            $erreurs[] = 'Structure UE manquante - l\'export pourrait être incomplet';
        }
        
        // Vérifier la cohérence des données
        foreach ($resultats as $index => $resultat) {
            if (!isset($resultat['etudiant'])) {
                $erreurs[] = "Ligne " . ($index + 1) . ": Informations étudiant manquantes";
            }
            
            if (!isset($resultat['moyenne_generale'])) {
                $erreurs[] = "Ligne " . ($index + 1) . ": Moyenne générale manquante";
            }
            
            if (!isset($resultat['decision'])) {
                $erreurs[] = "Ligne " . ($index + 1) . ": Décision manquante";
            }
        }
        
        return [
            'valid' => empty($erreurs),
            'erreurs' => $erreurs,
            'total_resultats' => count($resultats),
            'total_ues' => count($uesStructure)
        ];
    }
}