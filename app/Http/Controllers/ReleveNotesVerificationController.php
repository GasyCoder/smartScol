<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\ResultatFinal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReleveNotesVerificationController extends Controller
{
    /**
     * Afficher le relevé de notes
     */
    public function show($etudiantId, $sessionId)
    {
        try {
            $donneesReleve = $this->getDonneesReleve($etudiantId, $sessionId);
            return view('livewire.resultats.partials.releve-notes-show', $donneesReleve);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'affichage du relevé : ' . $e->getMessage());
        }
    }

    /**
     * Générer le PDF du relevé de notes
     */
    public function genererPDF($etudiantId, $sessionId)
    {
        try {
            $donneesReleve = $this->getDonneesReleve($etudiantId, $sessionId);
            
            $pdf = Pdf::loadView('exports.releve-notes-verification', $donneesReleve)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'debugCss' => false,
                    'debugLayout' => false,
                ]);

            $nomFichier = sprintf(
                "Releve_Notes_%s_%s_%s.pdf",
                $donneesReleve['etudiant']->matricule,
                $donneesReleve['session']->type,
                now()->format('Ymd_His')
            );

            return $pdf->download($nomFichier);

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF relevé', [
                'etudiant_id' => $etudiantId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }

    /**
     * Récupérer les données du relevé
     */
    private function getDonneesReleve($etudiantId, $sessionId)
    {
        $etudiant = Etudiant::with(['niveau', 'parcours'])->findOrFail($etudiantId);
        $session = SessionExam::with('anneeUniversitaire')->findOrFail($sessionId);

        // Vérifier si la session a été délibérée
        $sessionDeliberee = $session->estDeliberee();
        $parametresDeliberation = $sessionDeliberee ? $session->getParametresDeliberation() : null;

        // Récupérer tous les résultats de l'étudiant pour cette session
        $resultats = ResultatFinal::with(['ec.ue', 'examen'])
            ->where('session_exam_id', $sessionId)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->get();

        if ($resultats->isEmpty()) {
            throw new \Exception('Aucun résultat trouvé pour cet étudiant dans cette session.');
        }

        // Grouper par UE et calculer
        $resultatsParUE = $resultats->groupBy('ec.ue_id');
        $uesData = [];
        $moyennesUE = [];
        $totalCredits = 0;
        $creditsValides = 0;
        $hasNoteEliminatoire = false;

        foreach ($resultatsParUE as $ueId => $resultatsUE) {
            $ue = $resultatsUE->first()->ec->ue;
            $totalCredits += $ue->credits ?? 0;

            $notesEC = [];
            $notesValues = [];
            $hasZeroInUE = false;

            foreach ($resultatsUE as $resultat) {
                $notesEC[] = [
                    'ec' => $resultat->ec,
                    'note' => $resultat->note,
                    'est_eliminatoire' => $resultat->note == 0
                ];
                
                $notesValues[] = $resultat->note;
                
                if ($resultat->note == 0) {
                    $hasZeroInUE = true;
                    $hasNoteEliminatoire = true;
                }
            }

            $moyenneUE = count($notesValues) > 0 ? 
                round(array_sum($notesValues) / count($notesValues), 2) : 0;

            $ueValidee = ($moyenneUE >= 10) && !$hasZeroInUE;
            
            if ($ueValidee) {
                $creditsValides += $ue->credits ?? 0;
            }

            $moyennesUE[] = $moyenneUE;

            $uesData[] = [
                'ue' => $ue,
                'notes_ec' => $notesEC,
                'moyenne_ue' => $moyenneUE,
                'validee' => $ueValidee,
                'eliminees' => $hasZeroInUE,
                'credits' => $ue->credits ?? 0
            ];
        }

        // Trier les UE
        usort($uesData, function($a, $b) {
            $nomA = $a['ue']->abr ?? $a['ue']->nom;
            $nomB = $b['ue']->abr ?? $b['ue']->nom;
            
            $extraireNumero = function($nom) {
                if (preg_match('/UE\s*(\d+)/i', $nom, $matches)) {
                    return (int) $matches[1];
                }
                return 999;
            };
            
            $numeroA = $extraireNumero($nomA);
            $numeroB = $extraireNumero($nomB);
            
            if ($numeroA !== 999 && $numeroB !== 999) {
                return $numeroA - $numeroB;
            }
            
            if ($numeroA !== 999 && $numeroB === 999) return -1;
            if ($numeroA === 999 && $numeroB !== 999) return 1;
            
            return strcasecmp($nomA, $nomB);
        });

        // Moyenne générale
        $moyenneGenerale = count($moyennesUE) > 0 ? 
            round(array_sum($moyennesUE) / count($moyennesUE), 2) : 0;

        // Déterminer la décision avec délibération
        $decision = $this->determinerDecisionAvecDeliberation(
            $moyenneGenerale, 
            $creditsValides, 
            $totalCredits, 
            $hasNoteEliminatoire, 
            $session, 
            $sessionDeliberee, 
            $parametresDeliberation
        );

        return [
            'etudiant' => $etudiant,
            'session' => $session,
            'ues_data' => $uesData,
            'synthese' => [
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => $creditsValides,
                'total_credits' => $totalCredits,
                'pourcentage_credits' => $totalCredits > 0 ? round(($creditsValides / $totalCredits) * 100, 1) : 0,
                'decision' => $decision,
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'session_deliberee' => $sessionDeliberee,
                'parametres_deliberation' => $parametresDeliberation
            ],
            'date_generation' => now()->format('d/m/Y à H:i:s'),
            'header_image_base64' => $this->getHeaderImageBase64()
        ];
    }

    /**
     * Obtenir l'image d'en-tête encodée en base64
     */
    private function getHeaderImageBase64()
    {
        try {
            $imagePath = public_path('assets/images/header.png');
            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);
                return 'data:image/png;base64,' . base64_encode($imageData);
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Erreur encodage image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Déterminer la décision avec délibération
     */
    private function determinerDecisionAvecDeliberation(
        $moyenneGenerale, 
        $creditsValides, 
        $totalCredits, 
        $hasNoteEliminatoire, 
        $session, 
        $sessionDeliberee, 
        $parametresDeliberation
    ) {
        // Si pas de délibération, utiliser la logique normale
        if (!$sessionDeliberee || !$parametresDeliberation) {
            return $this->determinerDecision($moyenneGenerale, $creditsValides, $totalCredits, $hasNoteEliminatoire, $session->type);
        }

        $pourcentageCredits = $totalCredits > 0 ? ($creditsValides / $totalCredits) * 100 : 0;

        if ($session->type === 'Normale') {
            // SESSION 1 avec délibération
            if ($hasNoteEliminatoire) {
                return 'rattrapage';
            }

            if ($pourcentageCredits >= 75 && $moyenneGenerale >= 10) {
                return 'admis';
            }

            return 'rattrapage';

        } else {
            // SESSION 2 (rattrapage) avec délibération
            if ($hasNoteEliminatoire) {
                return 'excluss';
            }

            if ($pourcentageCredits >= 67 && $moyenneGenerale >= 10) {
                return 'admis';
            }

            if ($pourcentageCredits >= 33) {
                return 'redoublant';
            }

            return 'excluss';
        }
    }

    /**
     * Déterminer la décision sans délibération
     */
    private function determinerDecision($moyenne, $creditsValides, $totalCredits, $hasNoteEliminatoire, $typeSession)
    {
        if ($typeSession === 'Normale') {
            if ($hasNoteEliminatoire) {
                return 'rattrapage';
            }
            return ($moyenne >= 10 && $creditsValides >= $totalCredits) ? 'admis' : 'rattrapage';
        } else {
            if ($hasNoteEliminatoire) {
                return 'excluss';
            }
            if ($moyenne >= 10 && $creditsValides >= 40) {
                return 'admis';
            }
            return $creditsValides >= 20 ? 'redoublant' : 'excluss';
        }
    }
}