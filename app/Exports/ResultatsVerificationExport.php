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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ResultatsVerificationExport implements FromArray, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $resultats;
    protected $examen;
    protected $afficherMoyennesUE;
    protected $metadonnees;
    protected $data;
    protected $ueColumns;
    protected $totalColumns;
    protected $uesStructure;

    public function __construct($resultats, $examen, $afficherMoyennesUE = false, $metadonnees = [])
    {
        $this->examen = $examen;
        $this->afficherMoyennesUE = $afficherMoyennesUE;
        $this->metadonnees = $metadonnees;

        // On part sur la collection brute envoyée par le composant Livewire
        $collection = collect($resultats);

        // Détection du type de session à partir des métadonnées
        $sessionType = strtolower($metadonnees['session_info']['type'] ?? '');
        $isRattrapage = str_contains($sessionType, 'rattrap')
            || str_contains($sessionType, 'session2')
            || str_contains($sessionType, 'compens');

        if ($isRattrapage) {
            // 1) On récupère tous les EC qui ont AU MOINS une copie dans cette session
            $ecIdsAvecCopies = $collection
                ->filter(function ($row) {
                    // copie_id non nul => il y a une copie réelle en session active
                    return !empty($row['copie_id']);
                })
                ->pluck('ec_id')
                ->unique()
                ->values();

            // 2) On ne garde que les lignes correspondant à ces EC
            //    => les UE/EC sans copie (uniquement issues de la fusion) sont exclus
            $collection = $collection->filter(function ($row) use ($ecIdsAvecCopies) {
                return $ecIdsAvecCopies->contains($row['ec_id']);
            });
        }

        // Données filtrées (ou non, si session normale)
        $this->resultats = $collection;

        // Reste de l'initialisation inchangé
        $this->ueColumns = [];
        $this->prepareUEStructure();
        $this->prepareData();
    }


    public function title(): string
    {
        $niveau = $this->examen->niveau->abr ?? 'NIV';
        $parcours = $this->examen->parcours->abr ?? 'PARC';
        return "Resultats-{$niveau}-{$parcours}";
    }

    private function prepareUEStructure()
    {
        // Créer la structure UE à partir des résultats
        $this->uesStructure = $this->resultats
            ->groupBy('ue_id')
            ->map(function($resultatsUE) {
                $premierUE = $resultatsUE->first();
                $ecsUE = $resultatsUE->groupBy('ec_id')->map(function($resultatsEC) {
                    $premierEC = $resultatsEC->first();
                    return [
                        'ec' => (object)[
                            'id' => $premierEC['ec_id'],
                            'nom' => $premierEC['matiere'],
                            'abr' => 'EC', // Sera généré dynamiquement
                            'enseignant' => $premierEC['enseignant'] ?? 'N/A'
                        ]
                    ];
                })->values();

                return [
                    'ue' => (object)[
                        'id' => $premierUE['ue_id'],
                        'nom' => $premierUE['ue_nom'],
                        'abr' => $premierUE['ue_abr'] ?? 'UE',
                        'credits' => $premierUE['ue_credits'] ?? 0,
                        'ordre' => $premierUE['ue_id'] // Utiliser l'ID comme ordre par défaut
                    ],
                    'ecs' => $ecsUE->toArray()
                ];
            })
            ->sortBy(function($ueStructure) {
                // Trier par l'abr de l'UE pour avoir UE1, UE2, UE3, etc.
                $abr = $ueStructure['ue']->abr ?? 'UE';
                // Extraire le numéro de l'UE pour un tri numérique correct
                preg_match('/UE(\d+)/', $abr, $matches);
                return isset($matches[1]) ? intval($matches[1]) : 999;
            })
            ->values()
            ->toArray();
    }

    public function array(): array
    {
        return $this->data;
    }

    private function prepareData()
    {
        $data = [];

        // ✅ LIGNE 1: En-têtes UE
        $headerRow1 = ['', '', '', ''];
        $columnIndex = 5;

        foreach ($this->uesStructure as $index => $ueStructure) {
            $ue = $ueStructure['ue'];
            $nbEcs = count($ueStructure['ecs']);
            
            // ✅ Conditionner l'ajout de la moyenne UE selon le paramètre
            $nbColonnesUE = $this->afficherMoyennesUE ? $nbEcs + 1 : $nbEcs; // +1 pour la moyenne seulement si activé

            // Utilisation de l'abr de l'UE
            $ueAbr = $ue->abr ?? 'UE' . ($index + 1);
            
            // Nettoyage du nom UE
            $nomUE = $this->cleanUEName($ue->nom);
            
            $this->ueColumns[$ue->id] = [
                'start' => $columnIndex,
                'end' => $columnIndex + $nbColonnesUE - 1,
                'nb_columns' => $nbColonnesUE,
                'ue' => $ue,
                'index' => $index,
                'abr' => $ueAbr
            ];

            // Construction header UE
            $ueHeader = strtoupper($ueAbr) . '. ' . strtoupper($nomUE) . ' (' . number_format($ue->credits ?? 0, 2) . ' CRÉDITS)';
            $headerRow1[] = $ueHeader;

            // Colonnes vides pour fusionner
            for ($i = 1; $i < $nbColonnesUE; $i++) {
                $headerRow1[] = '';
            }

            $columnIndex += $nbColonnesUE;
        }

        $data[0] = $headerRow1;

        // ✅ LIGNE 2: Sous-en-têtes EC
        $headerRow2 = ['ORDRE', 'MATRICULE', 'NOM', 'PRÉNOM'];

        foreach ($this->uesStructure as $index => $ueStructure) {
            $ue = $ueStructure['ue'];

            foreach ($ueStructure['ecs'] as $ecIndex => $ecData) {
                $ec = $ecData['ec'];
                
                // Génération automatique de l'abr EC
                $ecAbr = 'EC' . ($ecIndex + 1);
                
                // Nettoyage du nom EC
                $nomEC = $this->cleanECName($ec->nom);
                
                // Construction header EC
                $ecHeader = strtoupper($ecAbr) . '. ' . strtoupper($nomEC);
                
                // Ajout de l'enseignant si disponible
                if (!empty($ec->enseignant) && $ec->enseignant !== 'N/A') {
                    $enseignant = $this->cleanEnseignantName($ec->enseignant);
                    $ecHeader .= ' [' . $enseignant . ']';
                }
                
                $headerRow2[] = $ecHeader;
            }

            // ✅ Ajouter colonne moyenne UE seulement si les moyennes sont activées
            if ($this->afficherMoyennesUE) {
                $headerRow2[] = 'MOYENNE';
            }
        }

        $data[1] = $headerRow2;

        // ✅ DONNÉES ÉTUDIANTS
        $etudiants = $this->resultats->groupBy('matricule');
        $ordre = 1;

        foreach ($etudiants as $matricule => $resultatsEtudiant) {
            $premierResultat = $resultatsEtudiant->first();
            
            $row = [
                $ordre++,
                $matricule,
                strtoupper($premierResultat['nom'] ?? ''),
                ucfirst(strtolower($premierResultat['prenom'] ?? '')),
            ];

            $toutesLesNotesEtudiant = collect();

            foreach ($this->uesStructure as $ueStructure) {
                $ue = $ueStructure['ue'];
                $notesUE = [];
                $hasNoteZero = false;

                foreach ($ueStructure['ecs'] as $ecData) {
                    $ec = $ecData['ec'];
                    $resultatEC = $resultatsEtudiant->where('ec_id', $ec->id)->first();
                    
                    if ($resultatEC && $resultatEC['note'] !== null && $resultatEC['note'] !== '') {
                        $note = (float)$resultatEC['note'];
                        $row[] = number_format($note, 2);
                        $notesUE[] = $note;
                        $toutesLesNotesEtudiant->push($note);
                        if ($note == 0) $hasNoteZero = true;
                    } else {
                        $row[] = '';
                    }
                }

                // ✅ Calcul et ajout de la moyenne UE seulement si les moyennes sont activées
                if ($this->afficherMoyennesUE) {
                    if ($hasNoteZero) {
                        $row[] = '0.00';
                    } elseif (!empty($notesUE)) {
                        $moyenneUE = array_sum($notesUE) / count($notesUE);
                        $row[] = number_format($moyenneUE, 2);
                    } else {
                        $row[] = '';
                    }
                }
            }

            $data[] = $row;
        }

        $this->totalColumns = count($headerRow2);
        $this->data = $data;
    }

    /**
     * Nettoie le nom de l'UE
     */
    private function cleanUEName($nom)
    {
        $nom = trim($nom);
        $nom = preg_replace('/^UE\d+\.\s*/', '', $nom);
        $nom = preg_replace('/\s+/', ' ', $nom);
        return trim($nom);
    }

    /**
     * Nettoie le nom de l'EC
     */
    private function cleanECName($nom)
    {
        $nom = trim($nom);
        $nom = preg_replace('/^EC\d+\.\s*/', '', $nom);
        $nom = preg_replace('/\s+/', ' ', $nom);
        return empty(trim($nom)) ? 'EC sans nom' : trim($nom);
    }

    /**
     * Nettoie le nom de l'enseignant en conservant les grades
     */
    private function cleanEnseignantName($enseignant)
    {
        $enseignant = trim($enseignant);
        // ✅ Conserver les grades (Dr., Prof, Pr.) - juste nettoyer les espaces
        $enseignant = preg_replace('/\s+/', ' ', $enseignant);
        return trim($enseignant);
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Style général - couleurs normales
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

        // Style en-têtes UE (ligne 1) - sans couleur de fond
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

        // Style en-têtes EC (ligne 2) - italique et sans couleur de fond
        $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
            'font' => [
                'bold' => true,
                'italic' => true, // ✅ Italique pour les EC
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

        // Style colonnes d'identification (A, B, C, D) - sans couleur de fond
        $sheet->getStyle('A3:D' . $lastRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // ✅ Alignement spécifique pour NOM et PRÉNOM à gauche
        $sheet->getStyle('C3:D' . $lastRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT, // ✅ Aligné à gauche
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Style données étudiants (commencent à la ligne 3)
        if ($lastRow > 2) {
            // Alignements pour les colonnes de données avec taille de police augmentée
            $sheet->getStyle('E3:' . $lastColumn . $lastRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'font' => [
                    'color' => ['rgb' => '000000'],
                    'size' => 11 // ✅ Taille augmentée de 9 à 11 pour les notes
                ]
            ]);

            // ✅ Style spécial pour les colonnes de moyennes si activées
            if ($this->afficherMoyennesUE) {
                // Parcourir les colonnes pour identifier celles contenant "MOYENNE"
                $totalCols = Coordinate::columnIndexFromString($lastColumn);
                for ($col = 5; $col <= $totalCols; $col++) {
                    $cellValue = $sheet->getCellByColumnAndRow($col, 2)->getValue();
                    
                    if ($cellValue && (strpos($cellValue, 'MOYENNE') !== false)) {
                        $colLetter = Coordinate::stringFromColumnIndex($col);
                        $sheet->getStyle($colLetter . '3:' . $colLetter . $lastRow)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '000000'],
                                'size' => 11
                            ],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                    }
                }
            }
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
            'A' => 6,   // Ordre
            'B' => 10,  // Matricule
            'C' => 20,  // Nom
            'D' => 15,  // Prénom
        ];

        $columnLetter = 'E';
        foreach ($this->uesStructure as $ueStructure) {
            // Colonnes EC
            foreach ($ueStructure['ecs'] as $ecData) {
                $widths[$columnLetter] = 12;
                $columnLetter++;
            }
            // ✅ Colonne moyenne UE seulement si les moyennes sont activées
            if ($this->afficherMoyennesUE) {
                $widths[$columnLetter] = 8;
                $columnLetter++;
            }
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->mergeUEHeaders($sheet);
                $this->freezePanes($sheet);
            },
        ];
    }

    private function mergeUEHeaders(Worksheet $sheet)
    {
        // Fusion des en-têtes UE
        foreach ($this->ueColumns as $ueData) {
            $startCol = Coordinate::stringFromColumnIndex($ueData['start']);
            $endCol = Coordinate::stringFromColumnIndex($ueData['end']);
            $sheet->mergeCells($startCol . '1:' . $endCol . '1');
        }
    }

    private function freezePanes(Worksheet $sheet)
    {
        // Figer les 4 premières colonnes et les 2 premières lignes
        $sheet->freezePane('E3');
    }
}