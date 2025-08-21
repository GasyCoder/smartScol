<?php

namespace App\Exports;

use App\Models\Etudiant;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EtudiantsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $niveau_id;
    protected $parcours_id;

    public function __construct($niveau_id, $parcours_id)
    {
        $this->niveau_id = $niveau_id;
        $this->parcours_id = $parcours_id;
    }

    public function query()
    {
        return Etudiant::query()
            ->where('niveau_id', $this->niveau_id)
            ->where('parcours_id', $this->parcours_id);
    }

    public function headings(): array
    {
        return [
            'Matricule',
            'Nom',
            'Prénom',
            'Date de naissance',
            'Sexe',
            'Statut'
        ];
    }

    public function map($etudiant): array
    {
        return [
            $etudiant->matricule,
            $etudiant->nom,
            $etudiant->prenom,
            $etudiant->date_naissance,
            $etudiant->sexe,
            $etudiant->is_active ? 'Actif' : 'Inactif'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DDDDDD']]]
        ];
    }
}
