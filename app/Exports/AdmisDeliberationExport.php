<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;

class AdmisDeliberationExport implements FromArray, WithColumnWidths, WithTitle, WithEvents
{
    protected $donnees;
    protected $session;
    protected $niveau;
    protected $parcours;
    protected $colonnesConfig;
    protected $data;
    protected $totalColumns;
    protected $colonnesVisibles;

    public function __construct($donnees, $session, $niveau, $parcours = null, $colonnesConfig = [])
    {
        // ✅ TRI LOGIQUE : Plus de crédits d'abord, puis plus haute moyenne
        $donneesTriees = collect($donnees)->sortBy([
            ['credits_valides', 'desc'],
            ['moyenne_generale', 'desc']
        ])->values();

        // ✅ RECALCULER LES RANGS APRÈS TRI
        $this->donnees = $donneesTriees->map(function($item, $index) {
            $item['rang'] = $index + 1;
            return $item;
        });

        $this->session = $session;
        $this->niveau = $niveau;
        $this->parcours = $parcours;
        $this->colonnesConfig = array_merge([
            'rang' => true,
            'nom_complet' => true,
            'matricule' => true,
            'moyenne' => true,
            'credits' => true,
            'decision' => true,
            'niveau' => false,
        ], $colonnesConfig);

        $this->prepareData();
    }

    public function array(): array
    {
        return $this->data;
    }

    private function prepareData(): void
    {
        try {
            $this->data = [];
            $sessionType = $this->session->type === 'Normale'
                ? 'Session 1 (Normale)'
                : 'Session 2 (Rattrapage)';
            $parcoursTxt = $this->parcours ? ' - ' . $this->parcours->nom : '';

            // ✅ TITRE ET INFORMATIONS SIMPLES (lignes 1-5)
            $this->data[] = [$this->genererTitreDocument()];
            $this->data[] = ["{$sessionType} - {$this->niveau->nom}{$parcoursTxt}"];
            $this->data[] = ['Année Universitaire: ' . ($this->session->anneeUniversitaire->libelle ?? 'N/A')];
            $this->data[] = ['Date de génération: ' . now()->format('d/m/Y H:i:s')];
            $this->data[] = []; // Ligne vide

            // ✅ EN-TÊTES DU TABLEAU
            $this->colonnesVisibles = $this->getColonnesVisibles();
            $headers = array_column($this->colonnesVisibles, 'label');
            $this->data[] = $headers;
            $this->totalColumns = count($headers);

            // ✅ DONNÉES SEULEMENT
            $this->ajouterDonnees();

        } catch (\Exception $e) {
            Log::error('Erreur préparation données Excel', [
                'error' => $e->getMessage(),
                'nb_donnees' => $this->donnees->count()
            ]);

            // Fallback en cas d'erreur
            $this->data = [
                ['ERREUR LORS DE LA GÉNÉRATION'],
                ['Erreur: ' . $e->getMessage()],
                ['Date: ' . now()->format('d/m/Y H:i:s')]
            ];
            $this->totalColumns = 1;
        }
    }

    /**
     * ✅ AJOUTER LES DONNÉES DES ÉTUDIANTS (SIMPLIFIÉ)
     */
    private function ajouterDonnees(): void
    {
        foreach ($this->donnees as $item) {
            $row = [];

            foreach ($this->colonnesVisibles as $colonne) {
                switch ($colonne['key']) {
                    case 'rang':
                        $row[] = $item['rang'] ?? 1;
                        break;

                    case 'nom_complet':
                        if (is_array($item['etudiant'])) {
                            $nom = strtoupper($item['etudiant']['nom']) . ' ' . ucfirst(strtolower($item['etudiant']['prenom']));
                        } else {
                            $nom = strtoupper($item['etudiant']->nom) . ' ' . ucfirst(strtolower($item['etudiant']->prenom));
                        }
                        $row[] = $nom;
                        break;

                    case 'matricule':
                        $matricule = is_array($item['etudiant'])
                            ? $item['etudiant']['matricule']
                            : $item['etudiant']->matricule;
                        $row[] = $matricule;
                        break;

                    case 'moyenne':
                        $moyenne = $item['moyenne_generale'] ?? 0;
                        $moyenneText = number_format($moyenne, 2) . '/20';
                        if (isset($item['has_note_eliminatoire']) && $item['has_note_eliminatoire']) {
                            $moyenneText .= ' (Note élim.)';
                        }
                        $row[] = $moyenneText;
                        break;

                    case 'credits':
                        $credits = ($item['credits_valides'] ?? 0) . '/' . ($item['total_credits'] ?? 60);
                        $row[] = $credits;
                        break;

                    case 'decision':
                        $decision = $item['decision_simulee'] ?? $item['decision_actuelle'] ?? 'non_definie';
                        $decisionText = strtoupper($decision);
                        if (isset($item['changement']) && $item['changement']) {
                            $decisionText .= ' (Déliberé)';
                        }
                        $row[] = $decisionText;
                        break;

                    case 'niveau':
                        $row[] = $this->niveau->nom;
                        break;

                    default:
                        $row[] = '-';
                }
            }

            $this->data[] = $row;
        }
    }

    /**
     * ✅ GÉNÉRER LE TITRE SELON LE CONTENU
     */
    private function genererTitreDocument()
    {
        if ($this->donnees->isEmpty()) {
            return 'LISTE DES RÉSULTATS';
        }

        // Détecter le type de données
        $decisions = $this->donnees->pluck('decision_simulee')->filter()->unique();
        if ($decisions->isEmpty()) {
            $decisions = $this->donnees->pluck('decision_actuelle')->filter()->unique();
        }

        if ($decisions->count() === 1) {
            switch ($decisions->first()) {
                case 'admis':
                    return 'LISTE DES CANDIDATS ADMIS';
                case 'rattrapage':
                    return 'LISTE DES CANDIDATS AUTORISÉS AU RATTRAPAGE';
                case 'redoublant':
                    return 'LISTE DES CANDIDATS REDOUBLANTS';
                case 'exclus':
                    return 'LISTE DES CANDIDATS EXCLUS';
            }
        }

        return 'LISTE DES RÉSULTATS';
    }

    /**
     * ✅ OBTENIR LES COLONNES VISIBLES
     */
    private function getColonnesVisibles()
    {
        $colonnes = [];

        if ($this->colonnesConfig['rang']) $colonnes[] = ['key' => 'rang', 'label' => 'Rang', 'width' => 8];
        if ($this->colonnesConfig['nom_complet']) $colonnes[] = ['key' => 'nom_complet', 'label' => 'Nom et Prénom', 'width' => 30];
        if ($this->colonnesConfig['matricule']) $colonnes[] = ['key' => 'matricule', 'label' => 'Matricule', 'width' => 16];
        if ($this->colonnesConfig['moyenne']) $colonnes[] = ['key' => 'moyenne', 'label' => 'Moyenne', 'width' => 15];
        if ($this->colonnesConfig['credits']) $colonnes[] = ['key' => 'credits', 'label' => 'Crédits', 'width' => 12];
        if ($this->colonnesConfig['decision']) $colonnes[] = ['key' => 'decision', 'label' => 'Décision', 'width' => 15];
        if ($this->colonnesConfig['niveau']) $colonnes[] = ['key' => 'niveau', 'label' => 'Niveau', 'width' => 15];

        return $colonnes;
    }

    public function columnWidths(): array
    {
        $widths = [];
        $colIdx = 1;

        foreach ($this->colonnesVisibles as $colonne) {
            $widths[chr(64 + $colIdx)] = $colonne['width'];
            $colIdx++;
        }

        return $widths;
    }

    public function title(): string
    {
        $type = $this->session->type === 'Normale' ? 'Session1' : 'Session2';

        // Détecter le contenu pour le nom de l'onglet
        if (!$this->donnees->isEmpty()) {
            $decisions = $this->donnees->pluck('decision_simulee')->filter()->unique();
            if ($decisions->isEmpty()) {
                $decisions = $this->donnees->pluck('decision_actuelle')->filter()->unique();
            }

            if ($decisions->count() === 1) {
                return ucfirst($decisions->first()) . '_' . $type;
            }
        }

        return 'Resultats_' . $type;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                try {
                    /** @var Worksheet $sheet */
                    $sheet = $event->sheet->getDelegate();
                    $this->appliquerStylesSimples($sheet);
                } catch (\Exception $e) {
                    Log::error('Erreur application styles Excel', ['error' => $e->getMessage()]);
                }
            },
        ];
    }

    /**
     * ✅ APPLIQUER LES STYLES SIMPLES AU FICHIER EXCEL (SANS COULEURS)
     */
    private function appliquerStylesSimples(Worksheet $sheet): void
    {
        $lastColumn = chr(64 + $this->totalColumns);
        $totalRows = count($this->data);

        // Trouver la ligne d'en-tête du tableau
        $headerRow = 6; // En-têtes à la ligne 6 (après titre + infos + ligne vide)
        $dataStartRow = $headerRow + 1;
        $dataEndRow = $totalRows;

        // ✅ BORDURES FINES POUR TOUT LE DOCUMENT
        $sheet->getStyle("A1:{$lastColumn}{$totalRows}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // ✅ STYLES DES TITRES (premières lignes) - NORMAL (PAS GRAS)
        for ($i = 1; $i <= 4; $i++) {
            $sheet->mergeCells("A{$i}:{$lastColumn}{$i}");
            $sheet->getStyle("A{$i}")->applyFromArray([
                'font' => [
                    'bold' => true,  // ✅ TITRES PAS GRAS
                    'size' => $i === 1 ? 14 : 12
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ]);
        }

        // ✅ STYLE DE L'EN-TÊTE DU TABLEAU - SEULEMENT LES EN-TÊTES EN GRAS
        if ($headerRow <= $totalRows) {
            $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
                'font' => [
                    'bold' => false,   // ✅ SEULEMENT LES EN-TÊTES COLONNES EN GRAS
                    'size' => 11
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ]);
        }

        // ✅ STYLES DES DONNÉES - EXPLICITEMENT PAS GRAS
        if ($dataStartRow <= $dataEndRow) {
            // Forcer TOUTES les données à ne pas être en gras
            $sheet->getStyle("A{$dataStartRow}:{$lastColumn}{$dataEndRow}")->applyFromArray([
                'font' => [
                    'size' => 11
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ]);

            // ✅ S'assurer spécifiquement que le rang n'est pas en gras
            if ($this->colonnesConfig['rang']) {
                $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")->applyFromArray([
                    'font' => [
                        'size' => 11
                    ]
                ]);
            }

            // ✅ ALIGNEMENTS SPÉCIFIQUES PAR COLONNE
            $colIdx = 1;
            foreach ($this->colonnesVisibles as $colonne) {
                $col = chr(64 + $colIdx);

                switch ($colonne['key']) {
                    case 'rang':
                    case 'matricule':
                    case 'moyenne':
                    case 'credits':
                    case 'decision':
                        $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$dataEndRow}")
                              ->getAlignment()
                              ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        break;

                    case 'nom_complet':
                        $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$dataEndRow}")
                              ->getAlignment()
                              ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        break;
                }

                $colIdx++;
            }
        }

        // ✅ AJUSTER LA HAUTEUR DES LIGNES
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        if ($headerRow <= $totalRows) {
            $sheet->getRowDimension($headerRow)->setRowHeight(25);
        }

        // ✅ FIGER LES VOLETS (en-tête)
        if ($headerRow <= $totalRows) {
            $sheet->freezePane("A" . ($headerRow + 1));
        }
    }
}
