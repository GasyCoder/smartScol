<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class ResultatsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize, WithEvents
{
    protected $resultats;
    protected $ecs;
    protected $session;
    protected $headings;

    public function __construct($resultats, $ecs, $session)
    {
        $this->resultats = collect($resultats);
        $this->ecs = $ecs;
        $this->session = $session;
        $this->prepareHeadings();
    }

    public function collection()
    {
        $data = collect();

        foreach ($this->resultats as $resultat) {
            $row = [
                $resultat['etudiant']->nom . ' ' . $resultat['etudiant']->prenom,
                $resultat['etudiant']->matricule,
            ];

            // Ajouter les notes pour chaque EC
            foreach ($this->ecs as $ueNom => $ecsUE) {
                foreach ($ecsUE as $ec) {
                    if (isset($resultat['notes'][$ec->id])) {
                        $note = $resultat['notes'][$ec->id]->note;
                        $row[] = number_format($note, 2);
                    } else {
                        $row[] = '-';
                    }
                }
            }

            // Ajouter moyenne, crédits et décision
            $row[] = number_format($resultat['moyenne_generale'], 2);
            $row[] = $resultat['credits_valides'] . '/60';
            $row[] = $resultat['decision_libelle'];

            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    private function prepareHeadings()
    {
        $this->headings = ['Nom Complet', 'Matricule'];

        // Ajouter les en-têtes des ECs
        foreach ($this->ecs as $ueNom => $ecsUE) {
            foreach ($ecsUE as $ec) {
                $this->headings[] = $ec->abr . ' (' . $ueNom . ')';
            }
        }

        $this->headings[] = 'Moyenne Générale';
        $this->headings[] = 'Crédits Validés';
        $this->headings[] = 'Décision';
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Style pour l'en-tête
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Bordures pour tout le tableau
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Centrer les données (sauf nom et matricule)
        if (count($this->headings) > 2) {
            $sheet->getStyle('C2:' . $lastColumn . $lastRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }

        return [];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 25, // Nom
            'B' => 15, // Matricule
        ];

        // Largeur pour les colonnes de notes
        $columnIndex = 'C';
        foreach ($this->ecs as $ueNom => $ecsUE) {
            foreach ($ecsUE as $ec) {
                $widths[$columnIndex] = 12;
                $columnIndex++;
            }
        }

        // Largeur pour moyenne, crédits et décision
        $widths[$columnIndex++] = 15; // Moyenne
        $widths[$columnIndex++] = 12; // Crédits
        $widths[$columnIndex++] = 18; // Décision

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

                // Ajouter des informations contextuelles
                $this->addContextInfo($sheet);

                // Colorier les notes selon leur valeur
                $this->colorizeNotes($sheet);

                // Colorier les décisions
                $this->colorizeDecisions($sheet);
            },
        ];
    }

    private function addContextInfo(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $infoRow = $lastRow + 2;

        // Informations sur l'export
        $sheet->setCellValue('A' . $infoRow, 'Informations sur l\'export:');
        $sheet->getStyle('A' . $infoRow)->getFont()->setBold(true);

        $sheet->setCellValue('A' . ($infoRow + 1), 'Session: ' . $this->session->type);
        $sheet->setCellValue('A' . ($infoRow + 2), 'Année: ' . ($this->session->anneeUniversitaire->libelle ?? 'N/A'));
        $sheet->setCellValue('A' . ($infoRow + 3), 'Date d\'export: ' . now()->format('d/m/Y H:i:s'));
        $sheet->setCellValue('A' . ($infoRow + 4), 'Nombre d\'étudiants: ' . $this->resultats->count());

        // Statistiques
        $admis = $this->resultats->where('decision', 'admis')->count();
        $rattrapage = $this->resultats->where('decision', 'rattrapage')->count();
        $redoublant = $this->resultats->where('decision', 'redoublant')->count();
        $exclus = $this->resultats->where('decision', 'exclus')->count();

        $sheet->setCellValue('C' . ($infoRow + 1), 'Admis: ' . $admis);
        $sheet->setCellValue('C' . ($infoRow + 2), 'Rattrapage: ' . $rattrapage);
        $sheet->setCellValue('C' . ($infoRow + 3), 'Redoublant: ' . $redoublant);
        $sheet->setCellValue('C' . ($infoRow + 4), 'Exclus: ' . $exclus);
    }

    private function colorizeNotes(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Colonnes des notes (de C jusqu'avant les 3 dernières colonnes)
        $startColumn = 'C';
        $endColumnIndex = count($this->headings) - 3; // -3 pour moyenne, crédits, décision
        $endColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endColumnIndex);

        for ($row = 2; $row <= $lastRow; $row++) {
            for ($col = 3; $col <= $endColumnIndex; $col++) {
                $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $cellValue = $sheet->getCell($cellCoordinate)->getValue();

                if (is_numeric($cellValue)) {
                    $note = floatval($cellValue);

                    if ($note == 0) {
                        // Note éliminatoire - rouge
                        $sheet->getStyle($cellCoordinate)->applyFromArray([
                            'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']]
                        ]);
                    } elseif ($note < 10) {
                        // Note insuffisante - orange
                        $sheet->getStyle($cellCoordinate)->applyFromArray([
                            'font' => ['color' => ['rgb' => 'EA580C']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FED7AA']]
                        ]);
                    } else {
                        // Note suffisante - vert
                        $sheet->getStyle($cellCoordinate)->applyFromArray([
                            'font' => ['color' => ['rgb' => '16A34A']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']]
                        ]);
                    }
                }
            }
        }
    }

    private function colorizeDecisions(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $decisionColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings));

        for ($row = 2; $row <= $lastRow; $row++) {
            $cellCoordinate = $decisionColumn . $row;
            $decision = strtolower($sheet->getCell($cellCoordinate)->getValue());

            switch (true) {
                case str_contains($decision, 'admis'):
                    $sheet->getStyle($cellCoordinate)->applyFromArray([
                        'font' => ['color' => ['rgb' => '16A34A'], 'bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']]
                    ]);
                    break;
                case str_contains($decision, 'rattrapage'):
                    $sheet->getStyle($cellCoordinate)->applyFromArray([
                        'font' => ['color' => ['rgb' => 'EA580C'], 'bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FED7AA']]
                    ]);
                    break;
                case str_contains($decision, 'redoublant'):
                    $sheet->getStyle($cellCoordinate)->applyFromArray([
                        'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']]
                    ]);
                    break;
                case str_contains($decision, 'exclus'):
                    $sheet->getStyle($cellCoordinate)->applyFromArray([
                        'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '991B1B']]
                    ]);
                    break;
            }
        }
    }
}
