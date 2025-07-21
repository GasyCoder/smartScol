<?php

namespace App\Exports;

use App\Models\UE;
use App\Models\EC;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class UEECExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $niveau_id;
    protected $parcours_id;

    public function __construct($niveau_id, $parcours_id)
    {
        $this->niveau_id = $niveau_id;
        $this->parcours_id = $parcours_id;
    }

    public function collection()
    {
        return UE::where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id)
            ->with('ecs')
            ->get();
    }

    /**
     * @param UE $ue
     */
    public function map($ue): array
    {
        $rows = [];

        if ($ue->ecs->count() > 0) {
            // Si l'UE a des EC, créer une ligne pour chaque EC
            foreach ($ue->ecs as $index => $ec) {
                $rows[] = [
                    'ue_abr' => $index === 0 ? $ue->abr : '', // Afficher l'abréviation UE seulement pour le premier EC
                    'ue_nom' => $index === 0 ? $ue->nom : '', // Afficher le nom UE seulement pour le premier EC
                    'ue_credits' => $index === 0 ? ($ue->credits ?? 0) : '', // Afficher les crédits UE seulement pour le premier EC
                    'ec_abr' => $ec->abr,
                    'ec_nom' => $ec->nom,
                    'enseignant' => $ec->enseignant
                ];
            }
        } else {
            // Si l'UE n'a pas d'EC, créer une ligne avec juste l'UE
            $rows[] = [
                'ue_abr' => $ue->abr,
                'ue_nom' => $ue->nom,
                'ue_credits' => $ue->credits ?? 0, // Ajouter les crédits UE
                'ec_abr' => '',
                'ec_nom' => '',
                'enseignant' => ''
            ];
        }

        return $rows;
    }


    public function headings(): array
    {
        return [
            'Code UE',      // ue_abr
            'Nom UE',       // ue_nom
            'Crédits UE',  // ue_credits
            'Code EC',      // ec_abr
            'Nom EC',       // ec_nom
            'Enseignant'    // enseignant
        ];
    }

    public function title(): string
    {
        return 'Unités d\'Enseignement';
    }

    public function styles(Worksheet $sheet)
    {
        // Style pour l'en-tête
        $sheet->getStyle('A1:G1')->getFont()->setBold(true); // Mise à jour pour inclure la colonne crédits
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDDDDD');



        // Format numérique pour la colonne des crédits
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('C3:C' . $lastRow)->getNumberFormat()->setFormatCode('0.00');

        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],  // En-têtes conviviaux
        ];
    }
}
