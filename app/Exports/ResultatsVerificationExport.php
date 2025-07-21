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

class ResultatsVerificationExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $resultats;
    protected $examen;
    protected $afficherMoyennesUE;

    public function __construct($resultats, $examen, $afficherMoyennesUE = false)
    {
        $this->resultats = collect($resultats);
        $this->examen = $examen;
        $this->afficherMoyennesUE = $afficherMoyennesUE;
    }

    public function headings(): array
    {
        $headings = [
            'N°',
            'IM',
            'Nom',
            'Prénom',
            'Unité d\'enseignement(UE)',
            'Enseignant',
            'Note/20'
        ];

        if ($this->afficherMoyennesUE) {
            $headings[] = 'Moyenne UE';
        }

        $headings[] = 'Commentaire';

        return $headings;
    }

    public function array(): array
    {
        // Grouper les résultats par étudiant
        $resultatsGroupes = $this->resultats->groupBy('matricule');
        $donnees = [];
        $numeroOrdre = 1;

        foreach ($resultatsGroupes as $matricule => $resultatsEtudiant) {
            $premierResultat = $resultatsEtudiant->first();

            // Grouper par UE pour cet étudiant
            $resultatsParUE = $resultatsEtudiant->groupBy('ue_nom');

            $premiereLigneEtudiant = true;

            foreach ($resultatsParUE as $ueNom => $resultatsUE) {
                // Ajouter une ligne pour l'UE avec la moyenne
                $ligneUE = [];

                if ($premiereLigneEtudiant) {
                    $ligneUE[] = $numeroOrdre;
                    $ligneUE[] = $premierResultat['matricule'];
                    $ligneUE[] = $premierResultat['nom'];
                    $ligneUE[] = $premierResultat['prenom'];
                    $premiereLigneEtudiant = false;
                } else {
                    $ligneUE[] = '';
                    $ligneUE[] = '';
                    $ligneUE[] = '';
                    $ligneUE[] = '';
                }

                $ueAbr = $resultatsUE->first()['ue_abr'] ?? 'UE';
                $ueCredits = $resultatsUE->first()['ue_credits'] ?? 0;
                $ueDisplay = $ueAbr . '.' . $ueNom . ($ueCredits ? " ({$ueCredits})" : '');
                $ligneUE[] = $ueDisplay; // Ligne dédiée à l'UE
                $ligneUE[] = ''; // Enseignant
                $ligneUE[] = ''; // Note

                // Ajouter la moyenne UE sur la ligne de l'UE
                if ($this->afficherMoyennesUE) {
                    $moyenneUE = $resultatsUE->first()['moyenne_ue'] ?? null;
                    $moyenneFormatee = $moyenneUE !== null ? number_format((float)$moyenneUE, 2, '.', '') : '';
                    $ligneUE[] = $moyenneFormatee;
                }

                $ligneUE[] = ''; // Commentaire
                $donnees[] = $ligneUE;

                // Ajouter les lignes pour chaque EC
                foreach ($resultatsUE as $index => $resultat) {
                    $ligne = [];

                    // Informations étudiant (vides car déjà affichées dans la ligne UE)
                    $ligne[] = '';
                    $ligne[] = '';
                    $ligne[] = '';
                    $ligne[] = '';

                    // EC
                    $ecIndex = $index + 1;
                    $ligne[] = "- EC{$ecIndex}. " . $resultat['matiere'];

                    // Enseignant
                    $ligne[] = $resultat['enseignant'] ?? 'N/A';

                    // Note avec format forcé XX.00
                    $noteFormatee = number_format((float)$resultat['note'], 2, '.', '');
                    $ligne[] = $noteFormatee;

                    // Moyenne UE (vide car déjà affichée dans la ligne UE)
                    if ($this->afficherMoyennesUE) {
                        $ligne[] = '';
                    }

                    // Commentaire
                    $ligne[] = $resultat['commentaire'] ?? '';

                    $donnees[] = $ligne;
                }
            }

            // Ajouter ligne vide entre les étudiants (sauf pour le dernier)
            if ($numeroOrdre < $resultatsGroupes->count()) {
                $nombreColonnes = $this->afficherMoyennesUE ? 9 : 8;
                $donnees[] = array_fill(0, $nombreColonnes, '');
            }

            $numeroOrdre++;
        }

        return $donnees;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $this->afficherMoyennesUE ? 'I' : 'H';
        $highestRow = $sheet->getHighestRow();

        // Style des en-têtes
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Bordures pour toutes les cellules
        $sheet->getStyle('A1:' . $lastColumn . $highestRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Alignement vertical pour toutes les cellules
        $sheet->getStyle('A1:' . $lastColumn . $highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        // Formater les colonnes de notes (G) et moyenne UE (H, si activée) avec un point décimal
        $sheet->getStyle('G2:G' . $highestRow)->getNumberFormat()->setFormatCode('0.00');
        if ($this->afficherMoyennesUE) {
            $sheet->getStyle('H2:H' . $highestRow)->getNumberFormat()->setFormatCode('0.00');
        }

        // Style pour les cellules avec UE et EC
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(-1); // Auto height

            // Vérifier si c'est une ligne de séparation (ligne vide entre étudiants)
            $cellValueA = $sheet->getCell('A' . $row)->getValue();
            $cellValueE = $sheet->getCell('E' . $row)->getValue();

            if (empty($cellValueA) && empty($cellValueE)) {
                // Ligne de séparation - couleur grise
                $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1D5DB']] // Gris
                ]);
            } else {
                // Vérifier si la cellule contient un nom d'UE (sans "- EC")
                if (!empty($cellValueE) && strpos($cellValueE, '- EC') !== 0) {
                    // C'est une ligne d'UE, on met en gras
                    $sheet->getStyle('E' . $row)->applyFromArray([
                        'font' => ['bold' => true]
                    ]);
                } else {
                    // C'est une ligne d'EC, on ne met pas en gras
                    $sheet->getStyle('E' . $row)->applyFromArray([
                        'font' => ['bold' => false]
                    ]);
                }

                // Mettre en gras les notes (colonne G)
                $sheet->getStyle('G' . $row)->applyFromArray([
                    'font' => ['bold' => true]
                ]);

                // Mettre en gras les moyennes UE si activées (colonne H)
                if ($this->afficherMoyennesUE) {
                    $moyenneValue = $sheet->getCell('H' . $row)->getValue();
                    if (!empty($moyenneValue) && is_numeric($moyenneValue)) {
                        $sheet->getStyle('H' . $row)->applyFromArray([
                            'font' => ['bold' => true]
                        ]);
                    }
                }
            }
        }

        // Fusionner les cellules "Moyenne UE" pour chaque UE
        if ($this->afficherMoyennesUE) {
            $currentRow = 2;
            while ($currentRow <= $highestRow) {
                $cellValueE = $sheet->getCell('E' . $currentRow)->getValue();
                if (!empty($cellValueE) && strpos($cellValueE, '- EC') !== 0) {
                    // C'est une ligne d'UE, compter le nombre d'EC qui suivent
                    $startRow = $currentRow;
                    $currentRow++;
                    $ecCount = 0;

                    while ($currentRow <= $highestRow) {
                        $nextCellValueE = $sheet->getCell('E' . $currentRow)->getValue();
                        if (empty($nextCellValueE) || strpos($nextCellValueE, '- EC') !== 0) {
                            break; // Fin de la liste des EC pour cette UE
                        }
                        $ecCount++;
                        $currentRow++;
                    }

                    // Fusionner la colonne "Moyenne UE" (H) sur toutes les lignes de cette UE
                    if ($ecCount > 0) {
                        $sheet->mergeCells("H{$startRow}:H" . ($startRow + $ecCount));
                        $sheet->getStyle("H{$startRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    }
                } else {
                    $currentRow++;
                }
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        if ($this->afficherMoyennesUE) {
            return [
                'A' => 5,   // N°
                'B' => 12,  // IM
                'C' => 18,  // Nom
                'D' => 18,  // Prénom
                'E' => 35,  // UE
                'F' => 20,  // Enseignant
                'G' => 10,  // Note/20
                'H' => 12,  // Moyenne UE
                'I' => 25   // Commentaire
            ];
        } else {
            return [
                'A' => 5,   // N°
                'B' => 12,  // IM
                'C' => 18,  // Nom
                'D' => 18,  // Prénom
                'E' => 35,  // UE
                'F' => 20,  // Enseignant
                'G' => 10,  // Note/20
                'H' => 25   // Commentaire
            ];
        }
    }
}