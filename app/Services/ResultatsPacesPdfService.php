<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

        // Si le filtre courant veut absolument afficher/masquer, on peut verrouiller
        if (in_array($this->filtreDecision, ['admis', 'redoublant', 'exclus'], true)) {
            $this->colonnesConfig['decision'] = false; 
        }

        try {
            $data = $this->prepareDataForExport();

            // ✅ CORRECTION 1 : Passer isPhpEnabled directement dans setOptions()
            $pdf = Pdf::loadView('exports.resultats-paces-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'isPhpEnabled' => true,        // ✅ Ajouté ici directement
                    'margin_top' => 15,
                    'margin_bottom' => 25,
                    'margin_left' => 20,
                    'margin_right' => 20,
                ]);

            // ✅ CORRECTION 2 : Utiliser getDomPDF() et render() sans set_option()
            $dompdf = $pdf->getDomPDF();
            
            // Rendu du PDF
            $dompdf->render();
            
            // ✅ Ajout pagination APRÈS le rendu
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
        if (!empty($typeDocument['hide_decision'])) {
            $this->colonnesConfig['decision'] = false;
        }

        return [
            'resultats' => $donneesAvecRang,
            'ues_structure' => $this->uesStructure,
            'parcours_nom' => $this->parcoursNom,
            'statistiques' => $this->statistiques,
            'date_export' => now()->format('d/m/Y H:i'),
            'export_par' => Auth::user()->name ?? 'Système',
            'total_pages_estimate' => ceil($donneesAvecRang->count() / 25),
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
            
            // ✅ CORRECTION : Retourner l'image brute (comme l'exemple)
            'qrcodeImage' => $this->genererQrCodeStatistiques(),
            'stats_detaillees' => [
                'total' => $donneesAvecRang->count(),
                'admis' => $donneesAvecRang->where('decision', 'admis')->count(),
                'redoublant' => $donneesAvecRang->where('decision', 'redoublant')->count(),
                'exclus' => $donneesAvecRang->where('decision', 'exclus')->count(),
                'rattrapage' => $donneesAvecRang->where('decision', 'rattrapage')->count(),
            ],
        ];
    }



    private function determinerTypeDocument()
    {
        // ===== ÉTAPE 1 : Récupérer l'année universitaire =====
        $anneeActive = \App\Models\AnneeUniversitaire::where('is_active', true)->first();
        $anneeLib = $anneeActive?->libelle ?? '2024-2025';

        // ===== ÉTAPE 2 : LOG pour diagnostic =====
        Log::info('📊 determinerTypeDocument - VALEURS REÇUES', [
            'filtreDecision' => $this->filtreDecision,
            'parcoursNom' => $this->parcoursNom,
        ]);

        // ===== ÉTAPE 3 : Traiter le nom du parcours intelligemment =====
        $parcoursOriginal = trim((string) $this->parcoursNom);
        
        // Enlever le préfixe "PACES " s'il existe (ex: "PACES Médecine Générale" → "Médecine Générale")
        $parcoursClean = preg_replace('/^PACES\s+/i', '', $parcoursOriginal);
        $parcoursClean = trim($parcoursClean);
        
        // ✅ CORRECTION : Si après nettoyage il reste vide OU si c'est juste "PACES"
        // → Garder "PACES" comme nom d'affichage
        if (empty($parcoursClean) || strcasecmp($parcoursOriginal, 'PACES') === 0) {
            $parcoursClean = 'PACES';
        }
        
        $parcoursUpper = strtoupper($parcoursClean);

        Log::info('📊 determinerTypeDocument - PARCOURS TRAITÉ', [
            'parcoursOriginal' => $parcoursOriginal,
            'parcoursUpper' => $parcoursUpper,
        ]);

        // ===== ÉTAPE 4 : Cas spécial - Aucun résultat =====
        if ($this->resultats->isEmpty()) {
            return [
                'type'           => 'vide',
                'titre_document' => 'RÉSULTATS CONCOURS - PACES',
                'titre_special'  => 'AUCUNE DONNÉE',
                'annee_affiche'  => $anneeLib,
                'hide_decision'  => false,
            ];
        }

        // ===== ÉTAPE 5 : Normaliser le filtre de décision =====
        // Convertir en minuscules et retirer les espaces
        $filtreNormalise = strtolower(trim((string) $this->filtreDecision));
        
        Log::info('📊 determinerTypeDocument - FILTRE NORMALISÉ', [
            'filtreNormalise' => $filtreNormalise,
            'est_admis' => ($filtreNormalise === 'admis') ? 'OUI' : 'NON',
        ]);

        // ===== ÉTAPE 6 : Configuration de base =====
        $titre_document = 'RÉSULTATS CONCOURS - PACES';
        
        // ===== ÉTAPE 7 : Déterminer le type selon le filtre =====
        switch ($filtreNormalise) {
            case 'admis':
                Log::info('✅ CAS DÉTECTÉ : ADMIS');
                return [
                    'type'           => 'admis_seulement',
                    'titre_document' => $titre_document,
                    'titre_special'  => 'LISTE DES ÉTUDIANTS  <span style="font-size:18px"><u>ADMIS</u></span> - PARCOURS ' . $parcoursUpper,
                    'annee_affiche'  => $anneeLib,
                    'hide_decision'  => true,  // ✅ Masquer colonne décision pour admis
                ];

            case 'redoublant':
            case 'redoublants':
                Log::info('✅ CAS DÉTECTÉ : REDOUBLANT');
                return [
                    'type'           => 'redoublant_seulement',
                    'titre_document' => $titre_document,
                    'titre_special'  => 'LISTE DES ÉTUDIANTS <u>REDOUBLANTS</u> - PARCOURS ' . $parcoursUpper,
                    'annee_affiche'  => $anneeLib,
                    'hide_decision'  => false, // ✅ Afficher colonne décision
                ];

            case 'exclus':
                Log::info('✅ CAS DÉTECTÉ : EXCLUS');
                return [
                    'type'           => 'exclus_seulement',
                    'titre_document' => $titre_document,
                    'titre_special'  => 'LISTE DES ÉTUDIANTS <u>EXCLUS</u> - PARCOURS ' . $parcoursUpper,
                    'annee_affiche'  => $anneeLib,
                    'hide_decision'  => false, // ✅ Afficher colonne décision
                ];

            default:
                Log::warning('⚠️ CAS DEFAULT - Filtre non reconnu', [
                    'filtre_recu' => $filtreNormalise,
                ]);
                
                return [
                    'type'           => 'mixte',
                    'titre_document' => $titre_document,
                    'titre_special'  => 'LISTE DES ÉTUDIANTS - PARCOURS ' . $parcoursUpper,
                    'annee_affiche'  => $anneeLib,
                    'hide_decision'  => false,
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




    // ✅ VERSION CORRIGÉE : Utilise les VRAIES statistiques
    private function genererQrCodeStatistiques()
    {
        try {
            // ✅ UTILISER les statistiques passées en paramètre (si disponibles)
            if (!empty($this->statistiques)) {
                $totalResultats = $this->statistiques['total'] ?? 0;
                $admis = $this->statistiques['admis'] ?? 0;
                $redoublant = $this->statistiques['redoublant'] ?? 0;
                $exclus = $this->statistiques['exclus'] ?? 0;
                $presents = $this->statistiques['presents'] ?? $totalResultats;
                $inscrits = $this->statistiques['inscrits'] ?? $totalResultats;
            } else {
                // ❌ Fallback : compter depuis les résultats (peut être filtré)
                $totalResultats = $this->resultats->count();
                $admis = $this->resultats->where('decision', 'admis')->count();
                $redoublant = $this->resultats->where('decision', 'redoublant')->count();
                $exclus = $this->resultats->where('decision', 'exclus')->count();
                $presents = $totalResultats;
                $inscrits = $totalResultats;
            }

            // ✅ Construire le texte avec TOUTES les stats
            $qrCodeData = mb_convert_encoding(sprintf(
                "RÉSULTATS PACES\n\n" .
                "PARCOURS: %s\n" .
                "ANNÉE: %s\n\n" .
                "STATISTIQUES OFFICIELLES:\n" .
                "Inscrits: %d\n" .
                "Présents: %d\n" .
                "Absents: %d\n\n" .
                "DÉCISIONS:\n" .
                "✓ Admis: %d\n" .
                "⚠ Redoublants: %d\n" .
                "✗ Exclus: %d\n\n" .
                "Date: %s",
                $this->parcoursNom,
                AnneeUniversitaire::where('is_active', true)->first()?->libelle ?? '2024-2025',
                $inscrits,
                $presents,
                max(0, $inscrits - $presents),
                $admis,
                $redoublant,
                $exclus,
                now()->format('d/m/Y H:i')
            ), 'UTF-8', 'UTF-8');

            // ✅ Générer le QR Code
            $qrCode = new QrCode;
            $qrcodeImage = $qrCode::size(200)
                ->encoding('UTF-8')
                ->errorCorrection('M')
                ->generate($qrCodeData);

            Log::info('✅ QR Code généré', [
                'longueur' => strlen($qrcodeImage),
                'stats_utilisees' => !empty($this->statistiques) ? 'vraies' : 'calculees'
            ]);

            return $qrcodeImage;

        } catch (\Exception $e) {
            Log::error('❌ Erreur génération QR Code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}