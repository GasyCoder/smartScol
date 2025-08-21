<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExamensExport implements FromView, WithColumnWidths, WithStyles, WithTitle
{
    protected $examens;
    protected $niveau;
    protected $parcours;
    protected $filters;

    public function __construct($examens, $niveau, $parcours, $filters = [])
    {
        $this->examens = $examens;
        $this->niveau = $niveau;
        $this->parcours = $parcours;
        $this->filters = $filters;
    }

 public function view(): View
    {
        // Préparer les données pour la vue
        $data = [];
        $totalCredits = 0; // Ajouter le calcul des crédits
        
        foreach ($this->examens as $examen) {
            foreach ($examen->ecs as $ec) {
                $salle = $ec->pivot->salle_id ? 
                    \App\Models\Salle::find($ec->pivot->salle_id) : null;
                
                // Calculer les statistiques
                $copiesCount = $examen->copies()->whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                    $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
                })->count();
                
                $manchettesCount = $examen->manchettes()->whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                    $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
                })->count();

                $totalCodes = $examen->codesAnonymat()->where('ec_id', $ec->id)->count();

                // Récupérer les crédits de l'UE
                $ueCredits = $ec->ue->credits ?? 0;
                $totalCredits += $ueCredits;

                $data[] = [
                    'examen_id' => $examen->id,
                    'ue_abr' => $ec->ue->abr ?? '',
                    'ue_nom' => $ec->ue->nom ?? '',
                    'ue_credits' => $ueCredits, // Ajouter les crédits
                    'ec_abr' => $ec->abr ?? '',
                    'ec_nom' => $ec->nom,
                    'enseignant' => $ec->enseignant ?? 'Non assigné',
                    'date' => $ec->pivot->date_specifique ? 
                        \Carbon\Carbon::parse($ec->pivot->date_specifique)->format('d/m/Y') : '',
                    'heure' => $ec->pivot->heure_specifique ? 
                        \Carbon\Carbon::parse($ec->pivot->heure_specifique)->format('H:i') : '',
                    'duree' => $examen->duree,
                    'salle' => $salle ? $salle->nom : '',
                    'code_base' => $ec->pivot->code_base ?? '',
                    'copies_saisies' => $copiesCount,
                    'manchettes_saisies' => $manchettesCount,
                    'total_codes' => $totalCodes,
                    'pourcentage_copies' => $totalCodes > 0 ? round(($copiesCount / $totalCodes) * 100) : 0,
                    'pourcentage_manchettes' => $totalCodes > 0 ? round(($manchettesCount / $totalCodes) * 100) : 0,
                    'statut' => $this->getStatutExamen($copiesCount, $manchettesCount, $totalCodes),
                    'note_eliminatoire' => $examen->note_eliminatoire
                ];
            }
        }

        return view('exports.examens-excel', [
            'data' => collect($data),
            'niveau' => $this->niveau,
            'parcours' => $this->parcours,
            'filters' => $this->filters,
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'total_examens' => $this->examens->count(),
            'total_ecs' => collect($data)->count(),
            'total_credits' => $totalCredits, // Ajouter le total des crédits
            'enseignants_uniques' => collect($data)->pluck('enseignant')->filter()->unique()->count()
        ]);
    }

    
    public function columnWidths(): array
    {
        return [
            'A' => 12, // Examen ID
            'B' => 15, // UE Abréviation  
            'C' => 35, // UE Nom
            'D' => 8,  // Crédits
            'E' => 15, // EC Abréviation
            'F' => 35, // EC Nom
            'G' => 25, // Enseignant
            'H' => 12, // Date
            'I' => 8,  // Heure
            'J' => 8,  // Durée
            'K' => 15, // Salle
            'L' => 10, // Code
            'M' => 10, // Copies
            'N' => 10, // Manchettes
            'O' => 10, // Total codes
            'P' => 12, // % Copies
            'Q' => 12, // % Manchettes
            'R' => 15, // Statut
            'S' => 12, // Note élim.
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style pour l'en-tête principal
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => '2563EB']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            
            // Style pour les informations
            '2:4' => [
                'font' => [
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ]
            ],

            // Style pour les en-têtes de colonnes
            6 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '1F2937']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '9CA3AF']
                    ]
                ]
            ],

            // Style pour les données
            'A7:S1000' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],

            // Alignement pour certaines colonnes
            'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // ID
            'H:H' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Date
            'I:I' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Heure
            'J:J' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Durée
            'L:L' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Code
            'M:Q' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Statistiques
            'R:R' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Statut
        ];
    }

    public function title(): string
    {
        $niveau_abr = $this->niveau['abr'] ?? 'N';
        $parcours_abr = $this->parcours['abr'] ?? 'P';
        return "Examens {$niveau_abr} {$parcours_abr}";
    }

    private function getStatutExamen($copies, $manchettes, $total)
    {
        if ($total == 0) {
            return 'Aucun code';
        }
        
        if ($copies >= $total && $manchettes >= $total) {
            return 'Complet';
        } elseif ($copies > 0 || $manchettes > 0) {
            return 'En cours';
        } else {
            return 'Non commencé';
        }
    }
}