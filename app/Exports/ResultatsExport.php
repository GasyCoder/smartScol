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

class ResultatsExport implements FromArray, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $resultats;
    protected $uesStructure;
    protected $session;
    protected $data;
    protected $ueStartColumns;
    protected $totalColumns;

    public function __construct($resultats, $uesStructure, $session)
    {
        $this->resultats = collect($resultats);
        $this->uesStructure = $uesStructure;
        $this->session = $session;
        $this->ueStartColumns = [];
        $this->prepareData();
    }

    public function array(): array
    {
        return $this->data;
    }

    private function prepareData()
    {
        $data = [];

        // Ligne 1: En-têtes UE avec regroupement
        $ueHeaderRow = ['', '', '', '']; // Ordre, Matricule, Nom, Prénom
        $columnIndex = 5;

        foreach ($this->uesStructure as $ueStructure) {
            $ue = $ueStructure['ue'];
            $this->ueStartColumns[$ue->id] = [
                'start' => $columnIndex,
                'nb_ecs' => count($ueStructure['ecs']),
                'ue' => $ue
            ];

            // Ajouter le nom UE sur plusieurs colonnes (EC + Moyenne)
            $nbColumns = count($ueStructure['ecs']) + 1; // +1 pour moyenne
            $ueHeaderRow[] = $ue->abr . ' - ' . $ue->nom . ' (' . $ue->credits . ')';

            // Colonnes vides pour les autres EC et moyenne de cette UE
            for ($i = 1; $i < $nbColumns; $i++) {
                $ueHeaderRow[] = '';
            }

            $columnIndex += $nbColumns;
        }

        // Colonnes finales
        $ueHeaderRow[] = 'Résultats';
        $ueHeaderRow[] = '';
        $ueHeaderRow[] = '';

        $data[0] = $ueHeaderRow;

        // Ligne 2: Sous-en-têtes (EC et Moyennes)
        $subHeaderRow = ['Ordre', 'Matricule', 'Nom', 'Prénom'];

        foreach ($this->uesStructure as $ueStructure) {
            // En-têtes des EC
            foreach ($ueStructure['ecs'] as $ecData) {
                $subHeaderRow[] = $ecData['display_name'];
            }
            // En-tête moyenne UE
            $subHeaderRow[] = 'Moyenne';
        }

        // Colonnes finales
        $subHeaderRow[] = 'Moy. Générale';
        $subHeaderRow[] = 'Crédits';
        $subHeaderRow[] = 'Décision';

        $data[1] = $subHeaderRow;

        // Données des étudiants
        $rowIndex = 2;
        foreach ($this->resultats as $index => $resultat) {
            $row = [
                $index + 1,
                $resultat['etudiant']->matricule,
                $resultat['etudiant']->nom,
                $resultat['etudiant']->prenom,
            ];

            // CORRECTION : Données par UE selon votre logique académique
            foreach ($this->uesStructure as $ueStructure) {
                $ue = $ueStructure['ue'];
                $notesUE = [];
                $hasNoteZero = false;

                // Notes des EC
                foreach ($ueStructure['ecs'] as $ecData) {
                    $ec = $ecData['ec'];
                    if (isset($resultat['notes'][$ec->id])) {
                        $note = $resultat['notes'][$ec->id]->note;
                        $row[] = number_format($note, 2);
                        $notesUE[] = $note;
                        if ($note == 0) $hasNoteZero = true;
                    } else {
                        $row[] = '-';
                    }
                }

                // CORRECTION : Moyenne UE selon votre logique
                if ($hasNoteZero) {
                    // UE éliminée à cause d'une note de 0
                    $row[] = '0.00 (Éliminé)';
                } elseif (!empty($notesUE)) {
                    // Moyenne UE = somme notes / nombre EC
                    $moyenneUE = array_sum($notesUE) / count($notesUE);
                    $row[] = number_format($moyenneUE, 2);
                } else {
                    $row[] = '-';
                }
            }

            // Données finales
            $row[] = number_format($resultat['moyenne_generale'], 2);
            $row[] = $resultat['credits_valides'] . '/' . ($resultat['total_credits'] ?? 60);

            // CORRECTION : Mapping des décisions
            $decisionLibelle = match($resultat['decision']) {
                'admis' => 'Admis',
                'rattrapage' => 'Rattrapage',
                'redoublant' => 'Redoublant',
                'exclus' => 'Exclus',
                default => 'Non définie'
            };
            $row[] = $decisionLibelle;

            $data[$rowIndex] = $row;
            $rowIndex++;
        }

        $this->totalColumns = count($subHeaderRow);
        $this->data = $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Style ligne 1 (UE Headers) - Simplicité selon vos demandes
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '000000']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style ligne 2 (Sous-headers)
        $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '000000']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6']
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

        // Style données étudiants - Simple et propre
        if ($lastRow > 2) {
            $sheet->getStyle('A3:' . $lastColumn . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'color' => ['rgb' => '000000']
                ]
            ]);

            // Centrage des données numériques (à partir de la colonne E)
            $sheet->getStyle('E3:' . $lastColumn . $lastRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // Hauteurs des lignes
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(35);

        return [];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,  // Ordre
            'B' => 12, // Matricule
            'C' => 18, // Nom
            'D' => 18, // Prénom
        ];

        $columnLetter = 'E';
        foreach ($this->uesStructure as $ueStructure) {
            // Colonnes EC
            foreach ($ueStructure['ecs'] as $ecData) {
                $widths[$columnLetter] = 10;
                $columnLetter++;
            }
            // Colonne moyenne UE
            $widths[$columnLetter] = 12;
            $columnLetter++;
        }

        // Colonnes finales
        $widths[$columnLetter++] = 12; // Moyenne générale
        $widths[$columnLetter++] = 10; // Crédits
        $widths[$columnLetter++] = 12; // Décision

        return $widths;
    }

    public function title(): string
    {
        return 'Résultats ' . $this->session->type;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->mergeUEHeaders($sheet);
                $this->addUEGroupBorders($sheet);
                $this->addContextInfo($sheet);
            },
        ];
    }

    private function mergeUEHeaders(Worksheet $sheet)
    {
        foreach ($this->ueStartColumns as $ueData) {
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['start']);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                $ueData['start'] + $ueData['nb_ecs'] // nb_ecs + moyenne
            );

            // Fusionner les cellules UE dans la ligne 1
            $sheet->mergeCells($startCol . '1:' . $endCol . '1');
        }

        // Fusionner "Résultats" pour les 3 dernières colonnes
        $lastCol = $sheet->getHighestColumn();
        $beforeLastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol) - 2
        );
        $sheet->mergeCells($beforeLastCol . '1:' . $lastCol . '1');
    }

    private function addUEGroupBorders(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Ajouter des bordures épaisses entre les groupes UE
        foreach ($this->ueStartColumns as $ueData) {
            if ($ueData['start'] > 5) { // Pas pour la première UE
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['start']);
                $sheet->getStyle($colLetter . '1:' . $colLetter . $lastRow)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            }
        }

        // Bordure avant les résultats finaux
        $resultsStartCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
            $this->totalColumns - 2
        );
        $sheet->getStyle($resultsStartCol . '1:' . $resultsStartCol . $lastRow)->applyFromArray([
            'borders' => [
                'left' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    private function addContextInfo(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $infoRow = $lastRow + 2;

        // Informations de session
        $sheet->setCellValue('A' . $infoRow, 'Session: ' . $this->session->type);
        $sheet->setCellValue('A' . ($infoRow + 1), 'Année: ' . ($this->session->anneeUniversitaire->libelle ?? 'N/A'));
        $sheet->setCellValue('A' . ($infoRow + 2), 'Date export: ' . now()->format('d/m/Y H:i:s'));

        // CORRECTION : Statistiques selon vos décisions corrigées
        $admis = $this->resultats->where('decision', 'admis')->count();
        $rattrapage = $this->resultats->where('decision', 'rattrapage')->count();
        $redoublant = $this->resultats->where('decision', 'redoublant')->count();
        $exclus = $this->resultats->where('decision', 'exclus')->count();
        $total = $this->resultats->count();

        $sheet->setCellValue('A' . ($infoRow + 4), 'STATISTIQUES');
        $sheet->getStyle('A' . ($infoRow + 4))->getFont()->setBold(true);

        if ($total > 0) {
            $sheet->setCellValue('A' . ($infoRow + 5), 'Total étudiants: ' . $total);
            $sheet->setCellValue('B' . ($infoRow + 5), 'Admis: ' . $admis . ' (' . round(($admis/$total)*100, 1) . '%)');

            if ($this->session->type === 'Normale') {
                // Première session : Admis vs Rattrapage
                $sheet->setCellValue('C' . ($infoRow + 5), 'Rattrapage: ' . $rattrapage . ' (' . round(($rattrapage/$total)*100, 1) . '%)');
            } else {
                // Session rattrapage : Admis vs Redoublant vs Exclus
                $sheet->setCellValue('C' . ($infoRow + 5), 'Redoublant: ' . $redoublant . ' (' . round(($redoublant/$total)*100, 1) . '%)');
                $sheet->setCellValue('D' . ($infoRow + 5), 'Exclus: ' . $exclus . ' (' . round(($exclus/$total)*100, 1) . '%)');
            }
        }

    }
}
