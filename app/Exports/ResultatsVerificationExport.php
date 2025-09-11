<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ResultatsVerificationExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $resultats;
    protected $examen;
    protected $afficherMoyennesUE;
    protected $metadonnees;

    public function __construct($resultats, $examen, $afficherMoyennesUE = false, $metadonnees = [])
    {
        $this->resultats = collect($resultats);
        $this->examen = $examen;
        $this->afficherMoyennesUE = $afficherMoyennesUE;
        $this->metadonnees = $metadonnees;
    }

    public function headings(): array
    {
        $headings = ['Matricule', 'Étudiant', 'UE / EC', 'Note', 'Enseignant'];
        
        if ($this->afficherMoyennesUE) {
            $headings[] = 'Moy.UE';
        }

        return $headings;
    }

    public function array(): array
    {
        $donnees = [];
        
        // Grouper les résultats par étudiant
        $resultatsParEtudiant = $this->resultats->groupBy('matricule');
        
        // Créer un index des résultats pour accès rapide
        $indexResultats = $this->resultats->keyBy(function($resultat) {
            return $resultat['matricule'] . '_' . $resultat['ec_id'];
        });

        foreach ($resultatsParEtudiant as $matricule => $resultatsEtudiant) {
            $premierResultat = $resultatsEtudiant->first();
            $nomCompletEtudiant = $premierResultat['nom'] . ' ' . $premierResultat['prenom'];
            
            // Grouper par UE
            $resultatsParUE = $resultatsEtudiant->groupBy('ue_id');
            $premiereLigneEtudiant = true;

            foreach ($resultatsParUE as $ueId => $resultatsUE) {
                // Calculer la moyenne UE pour cet étudiant
                $notesUE = $resultatsUE->where('note', '!=', null)
                                     ->where('note', '!=', '')
                                     ->pluck('note')
                                     ->filter(function($note) {
                                         return is_numeric($note);
                                     })
                                     ->map(function($note) {
                                         return (float)$note;
                                     });

                $moyenneUE = '';
                if ($notesUE->isNotEmpty()) {
                    if ($notesUE->contains(0)) {
                        $moyenneUE = '0.00';
                    } else {
                        $moyenneUE = number_format($notesUE->avg(), 2, '.', '');
                    }
                }

                $premiereLigneUE = true;
                $premierResultatUE = $resultatsUE->first();
                $ueDisplay = ($premierResultatUE['ue_abr'] ?? 'UE') . '. ' . $premierResultatUE['ue_nom'] . ' (' . ($premierResultatUE['ue_credits'] ?? 0) . ')';

                // Ligne d'en-tête UE
                $ligneUE = [];
                if ($premiereLigneEtudiant) {
                    $ligneUE[] = $matricule;
                    $ligneUE[] = $nomCompletEtudiant;
                    $premiereLigneEtudiant = false;
                } else {
                    $ligneUE[] = '';
                    $ligneUE[] = '';
                }
                
                $ligneUE[] = $ueDisplay;
                $ligneUE[] = '';
                $ligneUE[] = '';
                
                if ($this->afficherMoyennesUE) {
                    $ligneUE[] = $moyenneUE;
                }
                
                $donnees[] = $ligneUE;

                // Lignes des EC
                foreach ($resultatsUE as $indexEC => $resultat) {
                    $ligneEC = [];
                    $ligneEC[] = ''; // Matricule vide
                    $ligneEC[] = ''; // Étudiant vide
                    
                    // Nom de l'EC avec indentation
                    $ecDisplay = '    EC' . ($indexEC + 1) . '. ' . $resultat['matiere'];
                    $ligneEC[] = $ecDisplay;
                    
                    // Note
                    if ($resultat['note'] !== null && $resultat['note'] !== '') {
                        $ligneEC[] = number_format((float)$resultat['note'], 2, '.', '');
                    } else {
                        $ligneEC[] = '';
                    }
                    
                    // Enseignant
                    $ligneEC[] = $resultat['enseignant'] ?? 'N/A';
                    
                    if ($this->afficherMoyennesUE) {
                        $ligneEC[] = ''; // Pas de moyenne pour les EC individuels
                    }
                    
                    $donnees[] = $ligneEC;
                }
            }
            
            // Ligne vide entre les étudiants
            $nombreColonnes = $this->afficherMoyennesUE ? 6 : 5;
            $donnees[] = array_fill(0, $nombreColonnes, '');
        }

        return $donnees;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $nombreColonnes = $this->afficherMoyennesUE ? 6 : 5;
        $lastColumnLetter = Coordinate::stringFromColumnIndex($nombreColonnes);

        // Style des en-têtes
        $sheet->getStyle('A1:' . $lastColumnLetter . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Bordures pour toutes les cellules
        $sheet->getStyle('A1:' . $lastColumnLetter . $highestRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Traitement ligne par ligne pour les styles
        for ($row = 2; $row <= $highestRow; $row++) {
            $cellValueC = $sheet->getCell('C' . $row)->getValue();
            $cellValueA = $sheet->getCell('A' . $row)->getValue();
            
            // Ligne vide de séparation entre étudiants
            if (empty($cellValueC) && empty($cellValueA)) {
                $sheet->getStyle('A' . $row . ':' . $lastColumnLetter . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1D5DB']]
                ]);
            }
            // Ligne UE (ne commence pas par des espaces)
            elseif (!empty($cellValueC) && !str_starts_with($cellValueC, '    ')) {
                $sheet->getStyle('A' . $row . ':' . $lastColumnLetter . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']]
                ]);
            }
            // Ligne avec matricule/nom étudiant
            elseif (!empty($cellValueA)) {
                $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F9FF']]
                ]);
            }
        }

        // Format numérique pour la colonne des notes (D)
        $sheet->getStyle('D2:D' . $highestRow)->getNumberFormat()->setFormatCode('0.00');
        
        // Format numérique pour la colonne moyenne UE si activée
        if ($this->afficherMoyennesUE) {
            $sheet->getStyle('F2:F' . $highestRow)->getNumberFormat()->setFormatCode('0.00');
        }

        // Ajustement automatique de la hauteur des lignes
        for ($row = 1; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(-1);
        }

        // Figer les 2 premières colonnes et la première ligne
        $sheet->freezePane('C2');

        return [];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 12,  // Matricule
            'B' => 25,  // Étudiant
            'C' => 35,  // UE/EC
            'D' => 10,  // Note
            'E' => 20,  // Enseignant
        ];

        if ($this->afficherMoyennesUE) {
            $widths['F'] = 12; // Moy.UE
        }

        return $widths;
    }
}