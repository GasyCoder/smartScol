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

class ExamensEnseignantExport implements FromView, WithColumnWidths, WithStyles, WithTitle
{
    protected $examens;
    protected $enseignant;
    protected $niveau;
    protected $parcours;

    public function __construct($examens, $enseignant, $niveau, $parcours)
    {
        $this->examens = $examens;
        $this->enseignant = $enseignant;
        $this->niveau = $niveau;
        $this->parcours = $parcours;
    }

    public function view(): View
    {
        // Filtrer les ECs de cet enseignant uniquement
        $data = [];
        $planning = [];
        $totalHeures = 0;
        $totalCredits = 0; // Ajouter le calcul des crédits
        
        foreach ($this->examens as $examen) {
            foreach ($examen->ecs as $ec) {
                // Ne garder que les ECs de cet enseignant
                if ($ec->enseignant !== $this->enseignant) {
                    continue;
                }

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

                // CORRECTION: Convertir les dates Carbon en string pour éviter l'erreur
                $dateSort = $ec->pivot->date_specifique ? 
                    \Carbon\Carbon::parse($ec->pivot->date_specifique)->format('Y-m-d') : '9999-12-31';
                $heureSort = $ec->pivot->heure_specifique ? 
                    \Carbon\Carbon::parse($ec->pivot->heure_specifique)->format('H:i') : '23:59';

                // Récupérer les crédits de l'UE
                $ueCredits = $ec->ue->credits ?? 0;
                $totalCredits += $ueCredits;

                $item = [
                    'examen_id' => $examen->id,
                    'ue_abr' => $ec->ue->abr ?? '',
                    'ue_nom' => $ec->ue->nom ?? '',
                    'ue_credits' => $ueCredits, // Ajouter les crédits
                    'ec_abr' => $ec->abr ?? '',
                    'ec_nom' => $ec->nom,
                    'date' => $ec->pivot->date_specifique ? 
                        \Carbon\Carbon::parse($ec->pivot->date_specifique)->format('d/m/Y') : '',
                    'heure' => $ec->pivot->heure_specifique ? 
                        \Carbon\Carbon::parse($ec->pivot->heure_specifique)->format('H:i') : '',
                    'heure_fin' => $ec->pivot->heure_specifique ? 
                        \Carbon\Carbon::parse($ec->pivot->heure_specifique)->addMinutes($examen->duree)->format('H:i') : '',
                    'duree' => $examen->duree,
                    'salle' => $salle ? $salle->nom : '',
                    'code_base' => $ec->pivot->code_base ?? '',
                    'copies_saisies' => $copiesCount,
                    'manchettes_saisies' => $manchettesCount,
                    'total_codes' => $totalCodes,
                    'statut' => $this->getStatutExamen($copiesCount, $manchettesCount, $totalCodes),
                    'note_eliminatoire' => $examen->note_eliminatoire,
                    'date_sort' => $dateSort,  // Maintenant c'est une string
                    'heure_sort' => $heureSort // Maintenant c'est une string
                ];

                $data[] = $item;

                // Ajouter au planning (groupé par date) - CORRECTION: utiliser string comme clé
                $dateKey = $dateSort; // Utiliser la string au lieu de l'objet Carbon
                if (!isset($planning[$dateKey])) {
                    $planning[$dateKey] = [
                        'date' => $item['date'],
                        'examens' => []
                    ];
                }
                $planning[$dateKey]['examens'][] = $item;

                // Calculer le total d'heures
                $totalHeures += $examen->duree;
            }
        }

        // Trier les données par date puis par heure
        usort($data, function($a, $b) {
            if ($a['date_sort'] === $b['date_sort']) {
                return strcmp($a['heure_sort'], $b['heure_sort']);
            }
            return strcmp($a['date_sort'], $b['date_sort']);
        });

        // Trier le planning
        ksort($planning);
        foreach ($planning as &$jour) {
            usort($jour['examens'], function($a, $b) {
                return strcmp($a['heure_sort'], $b['heure_sort']);
            });
        }

        return view('exports.examens-enseignant-excel', [
            'data' => collect($data),
            'planning' => $planning,
            'enseignant' => $this->enseignant,
            'niveau' => $this->niveau,
            'parcours' => $this->parcours,
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'total_examens' => count($data),
            'total_heures' => $totalHeures,
            'total_credits' => $totalCredits, // Ajouter le total des crédits
            'moyenne_duree' => count($data) > 0 ? round($totalHeures / count($data)) : 0,
            'dates_examens' => collect($data)->pluck('date')->filter()->unique()->sort()->values()
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, // Date
            'B' => 10, // Heure début
            'C' => 10, // Heure fin
            'D' => 8,  // Durée
            'E' => 15, // UE Abréviation
            'F' => 30, // UE Nom
            'G' => 15, // EC Abréviation
            'H' => 30, // EC Nom
            'I' => 15, // Salle
            'J' => 10, // Code
            'K' => 10, // Copies
            'L' => 10, // Manchettes
            'M' => 15, // Statut
            'N' => 12, // Note élim.
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
                    'color' => ['rgb' => '059669']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            
            // Style pour les informations
            '2:5' => [
                'font' => [
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ]
            ],

            // Style pour les en-têtes de colonnes
            7 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '059669']
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
            'A8:N1000' => [
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
            'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Date
            'B:D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Heures et durée
            'I:I' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Salle
            'J:L' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Code et stats
            'M:N' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Statut et note
        ];
    }

    public function title(): string
    {
        $enseignant_clean = preg_replace('/[^a-zA-Z0-9]/', '_', $this->enseignant);
        return "Planning {$enseignant_clean}";
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