<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ResultatsPacesExport implements FromArray, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $resultats;
    protected $uesStructure;
    protected $filtreDecision;
    protected $parcoursNom;
    protected $data;
    protected $ueColumns;
    protected $totalColumns;
    protected $lignesRedoublants = []; // ✅ NOUVEAU : Tracer les redoublants

    public function __construct($resultats, $uesStructure, $filtreDecision = 'tous', $parcoursNom = '')
    {
        $this->resultats = collect($resultats);
        $this->uesStructure = $uesStructure;
        $this->filtreDecision = $filtreDecision;
        $this->parcoursNom = $parcoursNom;
        $this->ueColumns = [];
        $this->prepareData();
    }

    public function array(): array
    {
        return $this->data;
    }

    private function prepareData()
    {
        $data = [];

        // LIGNE 1: En-têtes UE
        $headerRow1 = ['', '', '', ''];
        $columnIndex = 5;

        foreach ($this->uesStructure as $index => $ueStructure) {
            $ue = $ueStructure['ue'];
            $nbEcs = count($ueStructure['ecs']);
            $nbColonnesUE = $nbEcs + 2;

            $ueAbr = $ue->abr ?? 'UE' . ($index + 1);
            $nomUE = $this->cleanUEName($ue->nom);
            
            $this->ueColumns[$ue->id] = [
                'start' => $columnIndex,
                'end' => $columnIndex + $nbColonnesUE - 1,
                'nb_columns' => $nbColonnesUE,
                'ue' => $ue,
                'index' => $index,
                'abr' => $ueAbr
            ];

            $ueHeader = strtoupper($ueAbr) . '. ' . strtoupper($nomUE) . ' (' . ($ue->credits ?? 0) . ' CRÉDITS)';
            $headerRow1[] = $ueHeader;

            for ($i = 1; $i < $nbColonnesUE; $i++) {
                $headerRow1[] = '';
            }
            $columnIndex += $nbColonnesUE;
        }

        $headerRow1[] = '';
        $headerRow1[] = '';
        $headerRow1[] = '';
        $data[0] = $headerRow1;

        // LIGNE 2: Sous-en-têtes EC
        $headerRow2 = ['RANG', 'MATRICULE', 'NOM', 'PRÉNOM'];

        foreach ($this->uesStructure as $index => $ueStructure) {
            foreach ($ueStructure['ecs'] as $ec) {
                $ecAbr = $ec->abr ?? 'EC';
                $nomEC = $this->cleanECName($ec->nom);
                
                $creditsEC = $this->getCreditsECDirectement($ec->id);
                
                $ecHeader = strtoupper($ecAbr) . '. ' . strtoupper($nomEC);
                if (!empty($ec->enseignant)) {
                    $ecHeader .= ' [' . trim($ec->enseignant) . ']';
                }
                $headerRow2[] = $ecHeader;
            }

            $headerRow2[] = 'MOY. UE';
            $headerRow2[] = 'CRÉDITS EC VALIDÉS';
        }

        $headerRow2[] = 'CRÉDITS TOTAUX';
        $headerRow2[] = 'MOYENNE GÉNÉRALE';
        $headerRow2[] = 'DÉCISION';
        $data[1] = $headerRow2;

        // TRI PAR ORDRE DE MÉRITE ACADÉMIQUE COHÉRENT
        $this->resultats = $this->resultats->sort(function($a, $b) {
            $prioriteDecisions = [
                'admis' => 1,
                'redoublant' => 2,
                'exclus' => 3
            ];
            
            $decisionA = $prioriteDecisions[$a['decision'] ?? 'exclus'] ?? 4;
            $decisionB = $prioriteDecisions[$b['decision'] ?? 'exclus'] ?? 4;
            
            if ($decisionA !== $decisionB) {
                return $decisionA <=> $decisionB;
            }
            
            $moyenneA = $a['moyenne_generale'] ?? 0;
            $moyenneB = $b['moyenne_generale'] ?? 0;
            
            if (abs($moyenneA - $moyenneB) >= 0.01) {
                return $moyenneB <=> $moyenneA;
            }
            
            $creditsA = $a['credits_valides'] ?? 0;
            $creditsB = $b['credits_valides'] ?? 0;
            
            return $creditsB <=> $creditsA;
            
        })->values();

        // DONNÉES ÉTUDIANTS
        foreach ($this->resultats as $index => $resultat) {
            $etudiant = $resultat['etudiant'];
            
            // ✅ ENREGISTRER si redoublant (ligne 3 = index 0)
            $estRedoublant = $resultat['est_redoublant'] ?? false;
            $this->lignesRedoublants[$index + 3] = $estRedoublant; // +3 car ligne 1 et 2 = en-têtes
            
            $row = [
                $index + 1, // ✅ RANG SANS BADGE
                $etudiant->matricule ?? '',
                (empty($etudiant->nom) || $etudiant->nom === '0') ? '' : strtoupper($etudiant->nom),
                (empty($etudiant->prenom) || $etudiant->prenom === '0') ? '' : ucfirst(strtolower($etudiant->prenom)),
            ];

            foreach ($this->uesStructure as $ueStructure) {
                $ue = $ueStructure['ue'];
                $notesUE = [];
                $hasNoteZero = false;
                $detailsCreditsEC = [];
                $creditsECValides = 0;
                $creditsECTotauxUE = 0;

                foreach ($ueStructure['ecs'] as $ec) {
                    $creditsEC = $this->getCreditsECDirectement($ec->id);
                    $creditsECTotauxUE += $creditsEC;

                    if (isset($resultat['notes'][$ec->id])) {
                        $note = $resultat['notes'][$ec->id]->note;
                        $row[] = number_format($note, 2);
                        $notesUE[] = $note;
                        
                        $ecValidee = ($note >= 10) && ($note != 0);
                        if ($ecValidee) {
                            $creditsECValides += $creditsEC;
                        }
                        
                        $statut = ($note == 0) ? '✗' : ($ecValidee ? '✓' : '✗');
                        $detailsCreditsEC[] = ($ec->abr ?? 'EC') . ':' . $statut . '(' . $creditsEC . ')';
                        
                        if ($note == 0) $hasNoteZero = true;
                    } else {
                        $row[] = '-';
                        $detailsCreditsEC[] = ($ec->abr ?? 'EC') . ':-(' . $creditsEC . ')';
                    }
                }

                // Moyenne UE
                if ($hasNoteZero) {
                    $row[] = '0.00';
                } elseif (!empty($notesUE)) {
                    $moyenneUE = array_sum($notesUE) / count($notesUE);
                    $row[] = number_format($moyenneUE, 2);
                } else {
                    $row[] = '-';
                }
                
                // Crédits EC validés
                $creditsUETotaux = $ue->credits ?? 0;
                $creditsECValidesTxt = $creditsECValides == (int)$creditsECValides ? (int)$creditsECValides : $creditsECValides;
                $creditsUETotauxTxt = $creditsUETotaux == (int)$creditsUETotaux ? (int)$creditsUETotaux : $creditsUETotaux;
                $resumeCredits = "{$creditsECValidesTxt}/{$creditsUETotauxTxt}";
                $row[] = $resumeCredits;
            }

            // Colonnes finales
            $row[] = ($resultat['credits_valides'] ?? 0) . '/' . ($resultat['total_credits'] ?? 60);
            $row[] = number_format($resultat['moyenne_generale'] ?? 0, 2);

            $decision = $resultat['decision'] ?? 'non_definie';
            $decisionLibelle = match($decision) {
                'admis' => 'ADMIS',
                'redoublant' => 'REDOUBLANT',
                'exclus' => 'EXCLUS',
                default => 'NON DÉFINIE'
            };
            
            if (isset($resultat['is_deliber']) && $resultat['is_deliber']) {
                $decisionLibelle .= ' ✓';
            } elseif (isset($resultat['decision_simulee']) && $resultat['decision_simulee']) {
                $decisionLibelle .= ' (Simulé)';
            }
            
            $row[] = $decisionLibelle;

            $data[] = $row;
        }

        $this->totalColumns = count($headerRow2);
        $this->data = $data;
    }

    private function getCreditsECDirectement($ecId)
    {
        try {
            $ec = \DB::table('ecs')->where('id', $ecId)->first();
            
            if ($ec) {
                if (isset($ec->credits) && $ec->credits > 0) {
                    return (int) $ec->credits;
                }
                if (isset($ec->credit) && $ec->credit > 0) {
                    return (int) $ec->credit;
                }
                if (isset($ec->nb_credits) && $ec->nb_credits > 0) {
                    return (int) $ec->nb_credits;
                }
            }
            
            return $this->calculerCreditsECProportionnel($ecId);
            
        } catch (\Exception $e) {
            \Log::error('Erreur récupération crédits EC: ' . $e->getMessage());
            return 1;
        }
    }

    private function calculerCreditsECProportionnel($ecId)
    {
        try {
            $ec = \DB::table('ecs')->where('id', $ecId)->first();
            if (!$ec || !$ec->ue_id) {
                return 1;
            }
            
            $ue = \DB::table('ues')->where('id', $ec->ue_id)->first();
            $nbECsUE = \DB::table('ecs')->where('ue_id', $ec->ue_id)->where('is_active', true)->count();
            
            if ($ue && $nbECsUE > 0) {
                $creditsUE = $ue->credits ?? 0;
                $creditsParEC = round($creditsUE / $nbECsUE, 1);
                return $creditsParEC > 0 ? $creditsParEC : 1;
            }
            
            return 1;
            
        } catch (\Exception $e) {
            \Log::error('Erreur calcul crédits proportionnel: ' . $e->getMessage());
            return 1;
        }
    }

    private function cleanUEName($nom)
    {
        $nom = trim($nom);
        $nom = preg_replace('/^UE\d+\.\s*/', '', $nom);
        $nom = preg_replace('/\s+/', ' ', $nom);
        return trim($nom);
    }

    private function cleanECName($nom)
    {
        $nom = trim($nom);
        $nom = ltrim($nom);
        $nom = preg_replace('/^EC\d+\.\s*/', '', $nom);
        $nom = preg_replace('/\s+/', ' ', $nom);
        
        if (empty(trim($nom))) {
            $nom = 'EC sans nom';
        }
        
        return trim($nom);
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // ✅ Style général (NOIR, TAILLE AUGMENTÉE)
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'font' => [
                'color' => ['rgb' => '000000'], // NOIR
                'size' => 11, // ✅ AUGMENTÉ (était 9)
                'name' => 'Arial'
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // ✅ Style en-têtes UE (ligne 1) - NOIR, TAILLE AUGMENTÉE
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12, // ✅ AUGMENTÉ (était 10)
                'color' => ['rgb' => '000000'] // NOIR
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA'] // Gris très clair (garde pour lisibilité)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // ✅ Style en-têtes EC (ligne 2) - NOIR, TAILLE AUGMENTÉE
        $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10, // ✅ AUGMENTÉ (était 8)
                'color' => ['rgb' => '000000'] // NOIR
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'] // Gris clair (garde pour lisibilité)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // ✅ Style données étudiants
        if ($lastRow > 2) {
            $sheet->getStyle('A3:' . $lastColumn . $lastRow)->applyFromArray([
                'font' => [
                    'size' => 11, // ✅ AUGMENTÉ (était 9)
                    'color' => ['rgb' => '000000'] // NOIR
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            // Alignements
            $sheet->getStyle('E3:' . $lastColumn . $lastRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getStyle('C3:D' . $lastRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
            $sheet->getStyle('A3:B' . $lastRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // ✅ Hauteurs des lignes (AUGMENTÉES)
        $sheet->getRowDimension(1)->setRowHeight(40); // ✅ AUGMENTÉ (était 35)
        $sheet->getRowDimension(2)->setRowHeight(50); // ✅ AUGMENTÉ (était 45)
        
        for ($i = 3; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(25); // ✅ AUGMENTÉ (était 20)
        }

        return [];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 10,
            'B' => 12,
            'C' => 20,
            'D' => 18,
        ];

        $columnLetter = 'E';
        foreach ($this->uesStructure as $ueStructure) {
            foreach ($ueStructure['ecs'] as $ec) {
                $widths[$columnLetter] = 15;
                $columnLetter++;
            }
            
            $widths[$columnLetter] = 12;
            $columnLetter++;
            
            $widths[$columnLetter] = 18;
            $columnLetter++;
        }

        $widths[$columnLetter++] = 16;
        $widths[$columnLetter++] = 15;
        $widths[$columnLetter++] = 16;

        return $widths;
    }

    public function title(): string
    {
        return 'Résultats PACES';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->mergeUEHeaders($sheet);
                $this->addUEColors($sheet);
                $this->styleEnseignantNames($sheet);
                $this->colorerLignesParStatut($sheet); // ✅ CHANGÉ
                $this->addContextInfo($sheet);
            },
        ];
    }

    private function mergeUEHeaders(Worksheet $sheet)
    {
        foreach ($this->ueColumns as $ueData) {
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['start']);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['end']);
            $sheet->mergeCells($startCol . '1:' . $endCol . '1');
        }

        $totalCols = $this->totalColumns;
        $beforeLastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols - 2);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
        
        $sheet->mergeCells($beforeLastCol . '1:' . $lastCol . '1');
    }

    private function styleEnseignantNames(Worksheet $sheet)
    {
        $columnIndex = 5;
        
        foreach ($this->uesStructure as $ueStructure) {
            foreach ($ueStructure['ecs'] as $ec) {
                if (!empty($ec->enseignant)) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                    
                    $cellValue = $sheet->getCell($columnLetter . '2')->getValue();
                    if (strpos($cellValue, '[') !== false) {
                        $sheet->getStyle($columnLetter . '2')->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'italic' => true,
                                'size' => 10, // ✅ AUGMENTÉ (était 8)
                                'color' => ['rgb' => '000000'] // NOIR
                            ]
                        ]);
                    }
                }
                
                $columnIndex++;
            }
            $columnIndex += 2;
        }
    }

    private function addUEColors(Worksheet $sheet)
    {
        foreach ($this->ueColumns as $ueData) {
            $ueIndex = $ueData['index'];
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['start']);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['end']);

            // Bordure gauche épaisse pour séparer les UE
            if ($ueIndex > 0) {
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle($startCol . '1:' . $startCol . $lastRow)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            }
        }
    }

    // ✅ NOUVELLE MÉTHODE : Colorer selon Redoublant/Nouveau
    private function colorerLignesParStatut(Worksheet $sheet)
    {
        $lastCol = $sheet->getHighestColumn();

        foreach ($this->lignesRedoublants as $row => $estRedoublant) {
            // ✅ VERT pour Nouveaux, JAUNE pour Redoublants
            $color = $estRedoublant ? 'FFF9C4' : 'C8E6C9'; // Jaune : Vert

            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $color]
                ]
            ]);
        }
    }

    private function addContextInfo(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $infoRow = $lastRow + 3;

        $sheet->setCellValue('A' . ($infoRow + 1), 'Parcours: ' . $this->parcoursNom);
        $sheet->setCellValue('A' . ($infoRow + 2), 'Filtre: ' . ucfirst($this->filtreDecision));
        $sheet->setCellValue('A' . ($infoRow + 3), 'Date export: ' . now()->format('d/m/Y H:i:s'));

        $statsRow = $infoRow + 5;
        $sheet->setCellValue('A' . $statsRow, 'STATISTIQUES');
        $sheet->getStyle('A' . $statsRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E8']]
        ]);

        $total = $this->resultats->count();
        if ($total > 0) {
            $admis = $this->resultats->where('decision', 'admis')->count();
            $redoublant = $this->resultats->where('decision', 'redoublant')->count();
            $exclus = $this->resultats->where('decision', 'exclus')->count();

            $sheet->setCellValue('A' . ($statsRow + 1), 'Total étudiants: ' . $total);
            $sheet->setCellValue('A' . ($statsRow + 2), 'Admis: ' . $admis . ' (' . round(($admis/$total)*100, 1) . '%)');
            $sheet->setCellValue('A' . ($statsRow + 3), 'Redoublants: ' . $redoublant . ' (' . round(($redoublant/$total)*100, 1) . '%)');
            $sheet->setCellValue('A' . ($statsRow + 4), 'Exclus: ' . $exclus . ' (' . round(($exclus/$total)*100, 1) . '%)');
        }
    }
}