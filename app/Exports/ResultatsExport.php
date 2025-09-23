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

class ResultatsExport implements FromArray, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $resultats;
    protected $uesStructure;
    protected $session;
    protected $niveau;
    protected $parcours;
    protected $anneeUniv;
    protected $data;
    protected $ueColumns;
    protected $totalColumns;

    public function __construct($resultats, $uesStructure, $session = null, $niveau = null, $parcours = null, $anneeUniv = null)
    {
        $this->resultats = collect($resultats);
        $this->uesStructure = $uesStructure;
        $this->session = $session ?: $this->createDefaultSession();
        $this->niveau = $niveau;
        $this->parcours = $parcours;
        $this->anneeUniv = $anneeUniv;
        $this->ueColumns = [];
        $this->prepareData();
    }

    private function createDefaultSession()
    {
        return (object) [
            'type' => 'Normale',
            'id' => null,
            'libelle' => 'Session par défaut'
        ];
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
        $headerRow2 = ['ORDRE', 'MATRICULE', 'NOM', 'PRÉNOM'];

        foreach ($this->uesStructure as $index => $ueStructure) {
            foreach ($ueStructure['ecs'] as $ecData) {
                $ec = $ecData['ec'];
                $ecAbr = $ec->abr ?? 'EC';
                $nomEC = $this->cleanECName($ec->nom);
                
                // ✅ RÉCUPÉRATION DIRECTE des crédits EC depuis la base
                $creditsEC = $this->getCreditsECDirectement($ec->id);
                
                $ecHeader = strtoupper($ecAbr) . '. ' . strtoupper($nomEC);
                if (!empty($ec->enseignant)) {
                    $ecHeader .= ' [' . trim($ec->enseignant) . ']';
                }
                $headerRow2[] = $ecHeader;
            }

            $headerRow2[] = 'MOYENNE UE';
            $headerRow2[] = 'CRÉDITS EC VALIDÉS';
        }

        $headerRow2[] = 'MOYENNE GÉNÉRALE';
        $headerRow2[] = 'CRÉDITS TOTAUX';
        $headerRow2[] = 'DÉCISION';
        $data[1] = $headerRow2;

        // TRI PAR ORDRE DE MÉRITE ACADÉMIQUE COHÉRENT
        $this->resultats = $this->resultats->sort(function($a, $b) {
            // 1. PRIORITÉ ABSOLUE : Décision académique
            $prioriteDecisions = [
                'admis' => 1,        // Admis en premier
                'rattrapage' => 2,   
                'redoublant' => 3,
                'exclus' => 4
            ];
            
            $decisionA = $prioriteDecisions[$a['decision'] ?? 'rattrapage'] ?? 5;
            $decisionB = $prioriteDecisions[$b['decision'] ?? 'rattrapage'] ?? 5;
            
            if ($decisionA !== $decisionB) {
                return $decisionA <=> $decisionB;
            }
            
            // 2. DANS CHAQUE GROUPE : Moyenne générale décroissante
            $moyenneA = $a['moyenne_generale'] ?? 0;
            $moyenneB = $b['moyenne_generale'] ?? 0;
            
            if (abs($moyenneA - $moyenneB) >= 0.01) {
                return $moyenneB <=> $moyenneA; // Meilleure moyenne en premier
            }
            
            // 3. Puis crédits décroissants
            $creditsA = $a['credits_valides'] ?? 0;
            $creditsB = $b['credits_valides'] ?? 0;
            
            return $creditsB <=> $creditsA;
            
        })->values();


        // DONNÉES ÉTUDIANTS avec calculs corrects
        foreach ($this->resultats as $index => $resultat) {
            $etudiant = $resultat['etudiant'];
            
            $row = [
                $index + 1,
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

                // ✅ TRAITEMENT CORRECT par EC avec récupération directe des crédits
                foreach ($ueStructure['ecs'] as $ecData) {
                    $ec = $ecData['ec'];
                    
                    // ✅ RÉCUPÉRATION DIRECTE des crédits
                    $creditsEC = $this->getCreditsECDirectement($ec->id);
                    $creditsECTotauxUE += $creditsEC;

                    if (isset($resultat['notes'][$ec->id])) {
                        $note = $resultat['notes'][$ec->id]->note;
                        $row[] = number_format($note, 2);
                        $notesUE[] = $note;
                        
                        // ✅ LOGIQUE VALIDATION EC
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
                
                // ✅ COLONNE CRÉDITS EC VALIDÉS avec logique correcte
                $resumeCredits = implode(' | ', $detailsCreditsEC);
                
                // ✅ TOTAL = crédits EC validés / crédits totaux de l'UE (pas seulement EC)
                $creditsUETotaux = $ue->credits ?? 0;

                // Formatage sans décimales inutiles
                $creditsECValidesTxt = $creditsECValides == (int)$creditsECValides ? (int)$creditsECValides : $creditsECValides;
                $creditsUETotauxTxt = $creditsUETotaux == (int)$creditsUETotaux ? (int)$creditsUETotaux : $creditsUETotaux;

                $resumeCredits = "{$creditsECValidesTxt}/{$creditsUETotauxTxt}";

                if ($creditsUETotaux > 0) {
                    $pourcentage = round(($creditsECValides / $creditsUETotaux) * 100, 1);
                    // $resumeCredits .= " ({$pourcentage}%)";
                }

                $row[] = $resumeCredits;
            }

            // Colonnes finales
            $row[] = number_format($resultat['moyenne_generale'] ?? 0, 2);
            $row[] = ($resultat['credits_valides'] ?? 0) . '/' . ($resultat['total_credits'] ?? 60);

            $decision = $resultat['decision'] ?? 'non_definie';
            $decisionLibelle = match($decision) {
                'admis' => 'Admis',
                'rattrapage' => 'Rattrapage', 
                'redoublant' => 'Redoublant',
                'exclus' => 'Exclus',
                default => 'Non définie'
            };
            $row[] = $decisionLibelle;

            $data[] = $row;
        }

        $this->totalColumns = count($headerRow2);
        $this->data = $data;
    }


    private function getCreditsECDirectement($ecId)
    {
        try {
            // Requête directe vers la table EC
            $ec = \DB::table('ecs')->where('id', $ecId)->first();
            
            if ($ec) {
                // Essayer différents noms de colonnes possibles
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
            
            // ✅ FALLBACK: Calculer proportionnellement par rapport à l'UE
            return $this->calculerCreditsECProportionnel($ecId);
            
        } catch (\Exception $e) {
            \Log::error('Erreur récupération crédits EC: ' . $e->getMessage());
            return 1; // Valeur par défaut
        }
    }

    // ✅ MÉTHODE pour calculer crédits EC proportionnellement
    private function calculerCreditsECProportionnel($ecId)
    {
        try {
            // Récupérer l'UE de cet EC
            $ec = \DB::table('ecs')->where('id', $ecId)->first();
            if (!$ec || !$ec->ue_id) {
                return 1;
            }
            
            // Récupérer l'UE et compter ses EC
            $ue = \DB::table('ues')->where('id', $ec->ue_id)->first();
            $nbECsUE = \DB::table('ecs')->where('ue_id', $ec->ue_id)->where('is_active', true)->count();
            
            if ($ue && $nbECsUE > 0) {
                $creditsUE = $ue->credits ?? 0;
                
                // ✅ RÉPARTITION PROPORTIONNELLE
                $creditsParEC = round($creditsUE / $nbECsUE, 1);
                return $creditsParEC > 0 ? $creditsParEC : 1;
            }
            
            return 1;
            
        } catch (\Exception $e) {
            \Log::error('Erreur calcul crédits proportionnel: ' . $e->getMessage());
            return 1;
        }
    }


    /**
     * Nettoie le nom de l'UE en supprimant les préfixes répétitifs
     */
    private function cleanUEName($nom)
    {
        $nom = trim($nom);
        
        // Supprime les préfixes UE1., UE2., etc. au début
        $nom = preg_replace('/^UE\d+\.\s*/', '', $nom);
        
        // Supprime les espaces en excès
        $nom = preg_replace('/\s+/', ' ', $nom);
        
        return trim($nom);
    }

    /**
     * Nettoie le nom de l'EC en supprimant les préfixes répétitifs
     */
    private function cleanECName($nom)
    {
        $nom = trim($nom);
        
        // Supprime les espaces au début
        $nom = ltrim($nom);
        
        // Supprime les préfixes EC1., EC2., etc. au début
        $nom = preg_replace('/^EC\d+\.\s*/', '', $nom);
        
        // Supprime les espaces en excès
        $nom = preg_replace('/\s+/', ' ', $nom);
        
        // Si le nom devient vide après nettoyage
        if (empty(trim($nom))) {
            $nom = 'EC sans nom';
        }
        
        return trim($nom);
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Style général
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'font' => [
                'color' => ['rgb' => '000000'],
                'size' => 9,
                'name' => 'Arial'
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style en-têtes UE (ligne 1)
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '000000']
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

        // Style en-têtes EC (ligne 2)
        $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 8,
                'color' => ['rgb' => '000000']
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

        // Style données étudiants (commencent à la ligne 3)
        if ($lastRow > 2) {
            $sheet->getStyle('A3:' . $lastColumn . $lastRow)->applyFromArray([
                'font' => [
                    'size' => 9,
                    'color' => ['rgb' => '000000']
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

        // Hauteurs des lignes
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->getRowDimension(2)->setRowHeight(45);
        
        for ($i = 3; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(20);
        }

        return [];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,   // Ordre
            'B' => 12,  // Matricule
            'C' => 20,  // Nom
            'D' => 18,  // Prénom
        ];

        $columnLetter = 'E';
        foreach ($this->uesStructure as $ueStructure) {
            // Colonnes EC
            foreach ($ueStructure['ecs'] as $ecData) {
                $widths[$columnLetter] = 15;
                $columnLetter++;
            }
            
            // Colonne moyenne UE
            $widths[$columnLetter] = 12;
            $columnLetter++;
            
            // ✅ NOUVELLE COLONNE: Crédits EC validés (plus large)
            $widths[$columnLetter] = 25;
            $columnLetter++;
        }

        // Colonnes finales
        $widths[$columnLetter++] = 12; // Moyenne générale
        $widths[$columnLetter++] = 30; // Crédits totaux (plus large pour détails)
        $widths[$columnLetter++] = 14; // Décision

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
                $this->addUEColors($sheet);
                $this->styleEnseignantNames($sheet);
                $this->addContextInfo($sheet);
            },
        ];
    }

    private function mergeUEHeaders(Worksheet $sheet)
    {
        // Fusion des en-têtes UE
        foreach ($this->ueColumns as $ueData) {
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['start']);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['end']);
            $sheet->mergeCells($startCol . '1:' . $endCol . '1');
        }

        // Fusion des colonnes finales (3 colonnes au lieu de 2)
        $totalCols = $this->totalColumns;
        $beforeLastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols - 2);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
        
        $sheet->mergeCells($beforeLastCol . '1:' . $lastCol . '1');
    }

    private function styleEnseignantNames(Worksheet $sheet)
    {
        $columnIndex = 5; // Commence à la colonne E
        
        foreach ($this->uesStructure as $ueStructure) {
            foreach ($ueStructure['ecs'] as $ecData) {
                $ec = $ecData['ec'];
                
                // Si l'EC a un enseignant, on applique le style italique aux noms entre crochets
                if (!empty($ec->enseignant)) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                    
                    // Appliquer un style simple à toute la cellule qui contient l'enseignant
                    $sheet->getStyle($columnLetter . '2')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 8,
                            'color' => ['rgb' => '000000']
                        ]
                    ]);
                    
                    // Pour les noms d'enseignants, on peut ajouter une couleur différente
                    $cellValue = $sheet->getCell($columnLetter . '2')->getValue();
                    if (strpos($cellValue, '[') !== false) {
                        // Appliquer une couleur grise pour différencier la partie enseignant
                        $sheet->getStyle($columnLetter . '2')->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'italic' => true, // Toute la cellule en italique si elle contient un enseignant
                                'size' => 8,
                                'color' => ['rgb' => '333333']
                            ]
                        ]);
                    }
                }
                
                $columnIndex++;
            }
            $columnIndex++; // Colonne moyenne UE
        }
    }

    private function addUEColors(Worksheet $sheet)
    {
        foreach ($this->ueColumns as $ueData) {
            $ueIndex = $ueData['index'];
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['start']);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ueData['end']);
            
            // Couleur de fond pour les en-têtes UE
            $sheet->getStyle($startCol . '1:' . $endCol . '1')->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA']
                ],
                'font' => [
                    'color' => ['rgb' => '000000'],
                    'bold' => true
                ]
            ]);

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

    private function addContextInfo(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $infoRow = $lastRow + 3;

        $sheet->setCellValue('A' . ($infoRow + 1), 'Session: ' . $this->session->type);
        if ($this->niveau) {
            $sheet->setCellValue('A' . ($infoRow + 2), 'Niveau: ' . $this->niveau->nom);
        }
        if ($this->parcours) {
            $sheet->setCellValue('A' . ($infoRow + 3), 'Parcours: ' . $this->parcours->nom);
        }
        if ($this->anneeUniv) {
            $sheet->setCellValue('A' . ($infoRow + 4), 'Année: ' . $this->anneeUniv->libelle);
        }
        $sheet->setCellValue('A' . ($infoRow + 5), 'Date export: ' . now()->format('d/m/Y H:i:s'));

        // Statistiques
        $statsRow = $infoRow + 7;
        $sheet->setCellValue('A' . $statsRow, 'STATISTIQUES');
        $sheet->getStyle('A' . $statsRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E8']]
        ]);

        $total = $this->resultats->count();
        if ($total > 0) {
            $admis = $this->resultats->where('decision', 'admis')->count();
            $rattrapage = $this->resultats->where('decision', 'rattrapage')->count();
            $redoublant = $this->resultats->where('decision', 'redoublant')->count();
            $exclus = $this->resultats->where('decision', 'exclus')->count();

            $sheet->setCellValue('A' . ($statsRow + 1), 'Total étudiants: ' . $total);
            $sheet->setCellValue('A' . ($statsRow + 2), 'Admis: ' . $admis . ' (' . round(($admis/$total)*100, 1) . '%)');
            $sheet->setCellValue('A' . ($statsRow + 3), 'Rattrapage: ' . $rattrapage . ' (' . round(($rattrapage/$total)*100, 1) . '%)');
            $sheet->setCellValue('A' . ($statsRow + 4), 'Redoublant: ' . $redoublant . ' (' . round(($redoublant/$total)*100, 1) . '%)');
            $sheet->setCellValue('A' . ($statsRow + 5), 'Exclus: ' . $exclus . ' (' . round(($exclus/$total)*100, 1) . '%)');
        }
    }
}