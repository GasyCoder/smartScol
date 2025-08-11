<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UEECTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            // UE1 avec 2 EC
            ['UE1', 'Médecine humaine', 6.0, 'EC1', 'Anatomie', '2.0', 'Dr. Martin'],
            ['', '', '', 'EC2', 'Histologie', '1.5', 'Dr. Durant'],

            // UE2 avec 2 EC
            ['UE2', 'Physiologie', 4.0, 'EC3', 'Physiologie cardiaque', '1.0', 'Pr. Bernard'],
            ['', '', '', 'EC4', 'Physiologie respiratoire', '1.0', 'Dr. Petit'],
        ];
    }

    public function headings(): array
    {
        return ['ue_abr', 'ue_nom', 'ue_credits', 'ec_abr', 'ec_nom', 'coefficient', 'enseignant'];
    }

    public function styles(Worksheet $sheet)
    {
        // Ajouter des styles avancés
        $sheet->getStyle('A1:G1')->getFont()->setBold(true); // Mis à jour de F à G
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDDDDD');

        // Ajouter une bordure aux cellules de données
        $lastRow = count($this->array()) + 1;
        $sheet->getStyle('A1:G'.$lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN); // Mis à jour de F à G

        // Mettre en surbrillance les groupes UE
        $sheet->getStyle('A2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F0F8FF'); // Bleu très clair
        $sheet->getStyle('A4')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F0F8FF');

        // Format numérique pour la colonne des crédits
        $sheet->getStyle('C2:C'.$lastRow)->getNumberFormat()->setFormatCode('0.00');

        // Instructions
        $sheet->setCellValue('I1', 'Instructions:'); // Décalé d'une colonne à cause de la nouvelle colonne
        $sheet->setCellValue('I2', '1. Ne remplissez que la première ligne pour chaque UE');
        $sheet->setCellValue('I3', '2. Laissez vide les champs ue_abr, ue_nom et ue_credits pour les EC supplémentaires');
        $sheet->setCellValue('I4', '3. Le champ ue_credits représente le nombre de crédits associés à l\'UE (ex: 6.0)');
        $sheet->setCellValue('I5', '4. Le coefficient est optionnel (valeur par défaut: 1.0)');
        $sheet->setCellValue('I6', '5. Le champ enseignant contient le nom de l\'enseignant responsable');
        $sheet->mergeCells('I1:K1'); // Mis à jour en fonction du décalage

        // Mettre "Instructions:" en gras et en rouge
        $sheet->getStyle('I1')->getFont()->setBold(true); // Décalé
        $sheet->getStyle('I1')->getFont()->getColor()->setRGB('FF0000'); // Rouge

        // Ajustement des colonnes d'instructions
        $sheet->getColumnDimension('I')->setWidth(70); // Décalé et élargi pour les instructions plus longues

        return [];
    }
}
