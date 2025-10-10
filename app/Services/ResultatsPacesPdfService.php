<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ResultatsPacesPdfService
{
    protected $resultats;
    protected $uesStructure;
    protected $filtreDecision;
    protected $parcoursNom;
    protected $statistiques;
    protected $colonnesConfig;

    public function generer(
        array $resultats,
        $uesStructure,
        string $filtreDecision = 'tous',
        string $parcoursNom = 'PACES',
        array $statistiques = [],
        array $colonnesConfig = []
    ) {
        $this->resultats = collect($resultats);
        $this->uesStructure = $uesStructure;
        $this->filtreDecision = $filtreDecision;
        $this->parcoursNom = $parcoursNom;
        $this->statistiques = $statistiques;
        
        $this->colonnesConfig = array_merge([
            'rang' => true,
            'nom_complet' => true,
            'matricule' => true,
            'moyenne' => false,
            'credits' => false,
            'decision' => true,
            'ues_details' => false,
        ], $colonnesConfig);

        try {
            $data = $this->prepareDataForExport();

            $pdf = Pdf::loadView('exports.resultats-paces-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'margin_top' => 15,
                    'margin_bottom' => 25,
                    'margin_left' => 20,
                    'margin_right' => 20,
                ]);

            // ✅ SOLUTION DÉFINITIVE : Callback qui s'exécute APRÈS le rendu
            $dompdf = $pdf->getDomPDF();
            $dompdf->set_option('isPhpEnabled', true);
            
            // Rendu du PDF
            $dompdf->render();
            
            // ✅ AJOUT pagination APRÈS le rendu (c'est la clé!)
            $canvas = $dompdf->getCanvas();
            $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                $text = "Page $pageNumber sur $pageCount";
                $font = $fontMetrics->getFont('helvetica', 'bold');
                $size = 10;
                
                // Calcul largeur pour centrage
                $width = $fontMetrics->getTextWidth($text, $font, $size);
                $x = ($canvas->get_width() - $width) / 2;
                $y = $canvas->get_height() - 40;
                
                // Dessiner sur CHAQUE page
                $canvas->text($x, $y, $text, $font, $size, [0, 0, 0]);
            });

            return $pdf;

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF PACES', [
                'error' => $e->getMessage(),
                'nb_resultats' => $this->resultats->count(),
                'filtre' => $this->filtreDecision,
                'parcours' => $this->parcoursNom
            ]);
            throw $e;
        }
    }

    private function getHeaderImageBase64()
    {
        try {
            $imagePath = public_path('assets/images/header.png');
            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);
                return 'data:image/png;base64,' . base64_encode($imageData);
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Erreur encodage image header: ' . $e->getMessage());
            return null;
        }
    }

    private function prepareDataForExport()
    {
        // ✅ IMPORTANT : Ne PAS refiltrer, les résultats sont déjà filtrés !
        // On utilise directement $this->resultats
        
        // Tri par rang/mérite
        $donneesTriees = $this->resultats
            ->sortBy([
                ['credits_valides', 'desc'],
                ['moyenne_generale', 'desc'],
                ['etudiant.matricule', 'asc']
            ])
            ->values();

        // Recalculer les rangs
        $donneesAvecRang = $donneesTriees->map(function($item, $index) {
            if (is_array($item)) {
                $item['rang'] = $index + 1;
            } else {
                $item['rang'] = $index + 1;
            }
            return $item;
        });

        // Type de document selon le filtre
        $typeDocument = $this->determinerTypeDocument();

        return [
            'resultats' => $donneesAvecRang,
            'ues_structure' => $this->uesStructure,
            'parcours_nom' => $this->parcoursNom,
            'statistiques' => $this->statistiques,
            'date_export' => now()->format('d/m/Y H:i'),
            'export_par' => Auth::user()->name ?? 'Système',
            'total_pages_estimate' => ceil($donneesAvecRang->count() / 25), // ✅ Estimation (25 lignes par page)
            'show_pagination' => true,
            
            // Infos document
            'titre_document' => $typeDocument['titre_document'],
            'titre_special' => $typeDocument['titre_special'],
            'type_document' => $typeDocument['type'],
            
            // Infos session
            'annee_universitaire' => AnneeUniversitaire::where('is_active', true)->first(),
            'session_type' => 'Session Normale',
            
            // Config
            'colonnes_config' => $this->colonnesConfig,
            'header_image_base64' => $this->getHeaderImageBase64(),
            'doyen_nom' => config('app.doyen_nom', 'RAKOTOMALALA Rivo'),
            'conditions' => $this->getConditions(),
        ];
    }

    private function determinerTypeDocument()
    {
        if ($this->resultats->isEmpty()) {
            return [
                'type' => 'vide',
                'titre_special' => null,
                'titre_document' => 'RÉSULTATS CONCOURS - PACES MÉDECINE GÉNÉRALE'
            ];
        }

        // ✅ CORRECTION : Nettoyer "PACES" du début du nom
        $parcoursClean = preg_replace('/^PACES\s*/i', '', $this->parcoursNom);
        $parcoursUpper = strtoupper($parcoursClean);

        switch ($this->filtreDecision) {
            case 'admis':
                return [
                    'type' => 'admis_seulement',
                    'titre_special' => 'LISTE DES CANDIDATS ADMIS',
                    'titre_document' => "LISTE DES CANDIDATS ADMIS EN PACES {$parcoursUpper}"
                ];
            
            case 'redoublant':
                return [
                    'type' => 'redoublant_seulement',
                    'titre_special' => 'LISTE DES CANDIDATS AUTORISÉS AU REDOUBLEMENT',
                    'titre_document' => "LISTE DES CANDIDATS AUTORISÉS AU REDOUBLEMENT - PACES {$parcoursUpper}"
                ];
            
            case 'exclus':
                return [
                    'type' => 'exclus_seulement',
                    'titre_special' => 'LISTE DES CANDIDATS EXCLUS',
                    'titre_document' => "LISTE DES CANDIDATS EXCLUS - PACES {$parcoursUpper}"
                ];
            
            default: // 'tous'
                return [
                    'type' => 'mixte',
                    'titre_special' => null,
                    'titre_document' => "RÉSULTATS CONCOURS - PACES {$parcoursUpper}"
                ];
        }
    }

    private function getConditions()
    {
        switch ($this->filtreDecision) {
            case 'admis':
                return 'Sous réserve de validation de Stage Hospitalier et des modules pratiques';
            
            case 'redoublant':
                return 'Sous réserve de rattrapage des modules défaillants et validation de Stage Hospitalier';
            
            default:
                return 'Sous réserve de validation de Stage Hospitalier et des modules pratiques';
        }
    }

    public function getFileName()
    {
        $filtreLabel = match($this->filtreDecision) {
            'admis' => 'Admis',
            'redoublant' => 'Redoublants',
            'exclus' => 'Exclus',
            default => 'Tous'
        };

        $parcours = str_replace(' ', '_', $this->parcoursNom);
        $date = now()->format('Ymd_His');

        return "Liste_PACES_{$parcours}_{$filtreLabel}_{$date}.pdf";
    }

    public static function createFromData(
        array $resultats,
        $uesStructure,
        string $filtreDecision,
        string $parcoursNom,
        array $statistiques = []
    ) {
        $instance = new self();
        return $instance->generer($resultats, $uesStructure, $filtreDecision, $parcoursNom, $statistiques);
    }
}