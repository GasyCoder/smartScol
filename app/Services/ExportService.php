<?php

namespace App\Services;

use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportService
{
    /**
     * Export PDF simple
     */
    public function exporterPDF($resultats, $selectedNiveau, $selectedAnneeUniversitaire, $selectedParcours = null, $uesStructure = [])
    {
        try {
            if (empty($resultats)) {
                throw new \Exception('Aucun résultat disponible pour l\'export.');
            }

            $niveau = is_object($selectedNiveau) ? $selectedNiveau : Niveau::find($selectedNiveau);
            $anneeUniv = is_object($selectedAnneeUniversitaire) ? $selectedAnneeUniversitaire : AnneeUniversitaire::find($selectedAnneeUniversitaire);
            $parcours = $selectedParcours ? (is_object($selectedParcours) ? $selectedParcours : Parcour::find($selectedParcours)) : null;

            // Protection si pas trouvé
            if (!$niveau) {
                $niveau = (object) ['nom' => 'Niveau non spécifié'];
            }
            if (!$anneeUniv) {
                $anneeUniv = (object) ['libelle' => date('Y') . '-' . (date('Y') + 1)];
            }

            $donneesVue = [
                'session' => (object) ['type' => 'Normale'],
                'niveau' => $niveau,
                'parcours' => $parcours,
                'anneeUniversitaire' => $anneeUniv,
                'dateGeneration' => now(),
                'resultats' => $resultats,
                'statistics' => $this->calculerStats($resultats),
                'uesStructure' => $uesStructure
            ];

            $nomFichier = 'resultats_session1_' . now()->format('Y-m-d_H-i') . '.pdf';
            
            $pdf = Pdf::loadView('exports.resultats-pdf', $donneesVue)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);
            
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $nomFichier, [
                'Content-Type' => 'application/pdf',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur export PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export Excel simple
     */
    public function exporterExcel($resultats, $uesStructure = [])
    {
        try {
            if (empty($resultats)) {
                throw new \Exception('Aucun résultat disponible pour l\'export.');
            }

            $donnees = $this->preparerDonneesExcel($resultats, $uesStructure);
            $nomFichier = 'resultats_session1_' . now()->format('Y-m-d_H-i') . '.xlsx';
            
            return Excel::download(new class($donnees) implements 
                \Maatwebsite\Excel\Concerns\FromArray, 
                \Maatwebsite\Excel\Concerns\WithHeadings, 
                \Maatwebsite\Excel\Concerns\WithTitle 
            {
                private $donnees;
                
                public function __construct($donnees) {
                    $this->donnees = $donnees;
                }
                
                public function array(): array {
                    return $this->donnees;
                }
                
                public function headings(): array {
                    return !empty($this->donnees) ? array_keys($this->donnees[0]) : [];
                }
                
                public function title(): string {
                    return 'Résultats Session 1';
                }
                
            }, $nomFichier);
            
        } catch (\Exception $e) {
            Log::error('Erreur export Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export PDF admis seulement
     */
    public function exporterAdmisPDF($resultats, $selectedNiveau, $selectedAnneeUniversitaire, $selectedParcours = null, $uesStructure = [])
    {
        try {
            // Filtrer seulement les admis
            $admis = collect($resultats)->filter(function($resultat) {
                return ($resultat['decision'] ?? '') === 'admis';
            })->values()->all();

            if (empty($admis)) {
                throw new \Exception('Aucun étudiant admis trouvé.');
            }

            return $this->exporterPDF($admis, $selectedNiveau, $selectedAnneeUniversitaire, $selectedParcours, $uesStructure);
            
        } catch (\Exception $e) {
            Log::error('Erreur export PDF admis: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Préparer données Excel
     */
    private function preparerDonneesExcel($resultats, $uesStructure)
    {
        $donnees = [];
        
        foreach ($resultats as $index => $resultat) {
            $etudiant = $resultat['etudiant'] ?? null;
            if (!$etudiant) continue;

            $ligne = [
                'N°' => $index + 1,
                'Matricule' => $etudiant->matricule ?? '',
                'Nom' => $etudiant->nom ?? '',
                'Prénom' => $etudiant->prenom ?? '',
            ];
            
            // Ajouter UE si disponibles
            if (!empty($uesStructure)) {
                foreach ($uesStructure as $ueStructure) {
                    $moyenneUE = $this->calculerMoyenneUE($resultat, $ueStructure);
                    $ligne[$ueStructure['ue']->abr ?? 'UE'] = $moyenneUE;
                }
            }
            
            $ligne = array_merge($ligne, [
                'Moyenne Générale' => number_format($resultat['moyenne_generale'] ?? 0, 2),
                'Crédits Validés' => $resultat['credits_valides'] ?? 0,
                'Total Crédits' => $resultat['total_credits'] ?? 60,
                'Décision' => ucfirst($resultat['decision'] ?? 'Non définie'),
                'Note Éliminatoire' => ($resultat['has_note_eliminatoire'] ?? false) ? 'Oui' : 'Non',
                'Validé par Jury' => ($resultat['jury_validated'] ?? false) ? 'Oui' : 'Non',
            ]);
            
            $donnees[] = $ligne;
        }
        
        return $donnees;
    }

    /**
     * Calculer moyenne UE
     */
    private function calculerMoyenneUE($resultat, $ueStructure)
    {
        $notesUE = [];
        $hasNoteZero = false;
        
        foreach ($ueStructure['ecs'] as $ecData) {
            if (isset($resultat['notes'][$ecData['ec']->id])) {
                $note = $resultat['notes'][$ecData['ec']->id]->note;
                $notesUE[] = $note;
                if ($note == 0) $hasNoteZero = true;
            }
        }
        
        if ($hasNoteZero) {
            return '0.00 (Élim)';
        } elseif (!empty($notesUE)) {
            return number_format(array_sum($notesUE) / count($notesUE), 2);
        }
        
        return '-';
    }

    /**
     * Calculer statistiques
     */
    private function calculerStats($resultats)
    {
        $total = count($resultats);
        $decisions = collect($resultats)->pluck('decision');
        $moyennes = collect($resultats)->pluck('moyenne_generale');
        $creditsTotal = collect($resultats)->sum('credits_valides');
        
        $admis = $decisions->filter(fn($d) => $d === 'admis')->count();
        
        return [
            'total_etudiants' => $total,
            'admis' => $admis,
            'rattrapage' => $decisions->filter(fn($d) => $d === 'rattrapage')->count(),
            'redoublant' => $decisions->filter(fn($d) => $d === 'redoublant')->count(),
            'exclus' => $decisions->filter(fn($d) => $d === 'exclus')->count(),
            'taux_reussite' => $total > 0 ? round(($admis / $total) * 100, 1) : 0,
            'moyenne_promo' => $moyennes->count() > 0 ? number_format($moyennes->avg(), 2) : 0,
            'credits_moyen' => $total > 0 ? round($creditsTotal / $total, 1) : 0
        ];
    }
}