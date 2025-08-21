<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EtudiantTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            ['1234-A', 'RAKOTO', 'Jean', '15/05/1995', 'M', true],
            ['1235-B', 'RABE', 'Marie', '20/10/1998', 'F', true],
            ['1236-C', 'RASOA', 'Pierre', '03/09/1997', 'M', false],
        ];
    }

    public function headings(): array
    {
        return ['matricule', 'nom', 'prenom', 'date_naissance', 'sexe', 'is_active'];
    }

    public function styles(Worksheet $sheet)
    {
        // Ajouter des styles avancés
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDDDDD');

        // Ajouter une bordure aux cellules de données
        $lastRow = count($this->array()) + 1;
        $sheet->getStyle('A1:F'.$lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Instructions avec titre en rouge
        $sheet->setCellValue('H1', 'Instructions:');
        $sheet->setCellValue('H2', '1. Le matricule doit être unique pour chaque étudiant');
        $sheet->setCellValue('H3', '2. Le format de date est JJ/MM/AAAA');
        $sheet->setCellValue('H4', '3. Le sexe doit être M ou F');
        $sheet->setCellValue('H5', '4. is_active: true ou false');
        $sheet->mergeCells('H1:J1');

        // Ajouter un encadré pour les noms des colonnes
        $sheet->setCellValue('H7', 'Noms des colonnes pour l\'importation:');
        $sheet->setCellValue('H8', 'matricule, nom, prenom, date_naissance, sexe, is_active');
        $sheet->mergeCells('H7:J7');
        $sheet->mergeCells('H8:J8');

        // Mettre en évidence
        $sheet->getStyle('H7')->getFont()->setBold(true);
        $sheet->getStyle('H7')->getFont()->getColor()->setRGB('0000FF'); // Bleu
        $sheet->getStyle('H8')->getFont()->setItalic(true);

        // Mettre "Instructions:" en gras et en rouge
        $sheet->getStyle('H1')->getFont()->setBold(true);
        $sheet->getStyle('H1')->getFont()->getColor()->setRGB('FF0000'); // Rouge

        // Ajustement des colonnes d'instructions
        $sheet->getColumnDimension('H')->setWidth(60);

        return [];
    }
}
