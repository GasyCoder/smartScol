<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdmisDeliberationPDF
{
    protected $donnees;
    protected $session;
    protected $niveau;
    protected $parcours;
    protected $colonnesConfig;
    protected $anneeUniversitaire;

    public function __construct(
        $donnees, 
        \App\Models\SessionExam $session, 
        \App\Models\Niveau $niveau, 
        ?\App\Models\Parcour $parcours = null, 
        array $colonnesConfig = []
    ) {
        $this->donnees = collect($donnees);
        $this->session = $session;
        $this->niveau = $niveau;
        $this->parcours = $parcours;
        $this->anneeUniversitaire = $session->anneeUniversitaire ?? null;
        $this->colonnesConfig = array_merge([
            'rang' => true,
            'nom_complet' => true,
            'matricule' => true,
            'moyenne' => true,
            'credits' => true,
            'decision' => true,
            'niveau' => false,
        ], $colonnesConfig);
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
            Log::error('Erreur encodage image: ' . $e->getMessage());
            return null;
        }
    }

    public function generate()
    {
        try {
            $data = $this->prepareDataForExport();

            // Utiliser la vue exports.resultats-export-pdf
            $pdf = Pdf::loadView('exports.resultats-export-pdf', $data)
                     ->setPaper('a4', 'portrait')
                     ->setOptions([
                         'defaultFont' => 'DejaVu Sans',
                         'isHtml5ParserEnabled' => true,
                         'isRemoteEnabled' => true,
                         'margin_top' => 15,
                         'margin_bottom' => 20,
                         'margin_left' => 20,
                         'margin_right' => 20,
                     ]);

            return $pdf;

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF', [
                'error' => $e->getMessage(),
                'nb_donnees' => $this->donnees->count(),
                'session' => $this->session->type ?? 'N/A',
                'niveau' => $this->niveau->nom ?? 'N/A'
            ]);
            throw $e;
        }
    }

    // Méthode pour générer et sauvegarder dans public
    public function generateAndSaveToPublic()
    {
        try {
            $pdf = $this->generate();
            
            $filename = $this->getFileName();
            $publicPath = 'exports/deliberations/' . date('Y/m/') . $filename;
            $fullPath = public_path($publicPath);
            
            // Créer les dossiers si nécessaire
            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }
            
            // Sauvegarder le PDF
            file_put_contents($fullPath, $pdf->output());

            return [
                'pdf' => $pdf,
                'path' => $publicPath,
                'url' => asset($publicPath),
                'filename' => $filename,
                'full_path' => $fullPath
            ];

        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde PDF', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // Préparer les données pour la vue exports.resultats-export-pdf
    private function prepareDataForExport()
    {
        // Tri par ordre de mérite
        $donneesTriees = $this->donnees
            ->sortBy([
                ['credits_valides', 'desc'],
                ['moyenne_generale', 'desc'],
                ['etudiant.nom', 'asc']
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

        // Statistiques
        $statistiques = $this->calculerStatistiques();
        
        // Type de document
        $typeDocument = $this->determinerTypeDocument();

        // Structure de données compatible avec exports.resultats-export-pdf
        return [
            'resultats' => $donneesAvecRang,
            'niveau' => $this->niveau,
            'parcours' => $this->parcours,
            'session' => $this->session,
            'annee_universitaire' => $this->anneeUniversitaire,
            'statistiques' => $statistiques,
            'date_export' => now()->format('d/m/Y H:i'),
            'export_par' => Auth::user()->name ?? 'Système',
            'ues_structure' => [], // Vide pour ce type d'export
            
            // Données spécifiques pour le PDF
            'titre_document' => $typeDocument['titre_document'],
            'titre_special' => $typeDocument['titre_special'],
            'session_info' => [
                'type' => $this->session->type ?? 'Non définie',
                'type_complet' => $this->session->type === 'Normale' ? 'Session Normale' : 'Session de Rattrapage',
                'annee' => $this->anneeUniversitaire->libelle ?? 'Non définie',
                'niveau' => $this->niveau->nom ?? 'Non défini',
                'parcours' => $this->parcours->nom ?? null,
            ],
            'conditions' => $this->getConditions(),
            'doyen_nom' => config('app.doyen_nom', 'RAKOTOMALALA Rivo'),
            'colonnes_config' => $this->colonnesConfig,
            'header_image_base64' => $this->getHeaderImageBase64(),
        ];
    }

    private function determinerTypeDocument()
    {
        if ($this->donnees->isEmpty()) {
            return [
                'type' => 'vide',
                'titre_special' => null,
                'titre_document' => 'LISTE DES RÉSULTATS'
            ];
        }

        // Analyser les décisions
        $decisions = $this->donnees->pluck('decision_simulee')->filter();
        if ($decisions->isEmpty()) {
            $decisions = $this->donnees->pluck('decision_actuelle')->filter();
        }
        if ($decisions->isEmpty()) {
            $decisions = $this->donnees->pluck('decision')->filter();
        }

        $decisionsUniques = $decisions->unique();

        // Si une seule décision
        if ($decisionsUniques->count() === 1) {
            $decision = $decisionsUniques->first();
            switch ($decision) {
                case 'admis':
                    return [
                        'type' => 'admis_seulement',
                        'titre_special' => 'LISTE DES CANDIDATS ADMIS',
                        'titre_document' => 'LISTE DES CANDIDATS ADMIS EN ' . strtoupper($this->niveau->nom) . ' ' . strtoupper($this->parcours->nom)
                    ];
                case 'rattrapage':
                    return [
                        'type' => 'rattrapage_seulement',
                        'titre_special' => 'LISTE DES CANDIDATS AUTORISÉS AU RATTRAPAGE',
                        'titre_document' => 'LISTE DES CANDIDATS AUTORISÉS AU RATTRAPAGE EN ' . strtoupper($this->niveau->nom). ' ' . strtoupper($this->parcours->nom)
                    ];
                case 'redoublant':
                    return [
                        'type' => 'redoublant_seulement',
                        'titre_special' => 'LISTE DES CANDIDATS REDOUBLANTS',
                        'titre_document' => 'LISTE DES CANDIDATS REDOUBLANTS EN ' . strtoupper($this->niveau->nom). ' ' . strtoupper($this->parcours->nom)
                    ];
                case 'exclus':
                    return [
                        'type' => 'exclus_seulement',
                        'titre_special' => 'LISTE DES CANDIDATS EXCLUS',
                        'titre_document' => 'LISTE DES CANDIDATS EXCLUS EN ' . strtoupper($this->niveau->nom). ' ' . strtoupper($this->parcours->nom)
                    ];
            }
        }

        // Plusieurs décisions
        $sessionType = $this->session->type === 'Normale' ? 'SESSION NORMALE' : 'SESSION DE RATTRAPAGE';
        $niveauNom = strtoupper($this->niveau->nom ?? '');
        $parcoursNom = $this->parcours ? ' - ' . strtoupper($this->parcours->nom) : '';

        return [
            'type' => 'mixte',
            'titre_special' => null,
            'titre_document' => 'RÉSULTATS ' . $sessionType . ' - ' . $niveauNom . $parcoursNom
        ];
    }

    private function calculerStatistiques()
    {
        if ($this->donnees->isEmpty()) {
            return [
                'total_etudiants' => 0,
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0,
                'taux_reussite' => 0,
                'moyenne_promo' => 0,
                'credits_moyen' => 0,
                'etudiants_avec_note_eliminatoire' => 0,
            ];
        }

        $total = $this->donnees->count();
        
        // Récupérer les décisions
        $decisions = $this->donnees->pluck('decision_simulee')->filter();
        if ($decisions->isEmpty()) {
            $decisions = $this->donnees->pluck('decision_actuelle')->filter();
        }
        if ($decisions->isEmpty()) {
            $decisions = $this->donnees->pluck('decision')->filter();
        }

        // Compter par décision
        $admis = $decisions->filter(fn($d) => $d === 'admis')->count();
        $rattrapage = $decisions->filter(fn($d) => $d === 'rattrapage')->count();
        $redoublant = $decisions->filter(fn($d) => $d === 'redoublant')->count();
        $exclus = $decisions->filter(fn($d) => $d === 'exclus')->count();

        // Moyennes et crédits
        $moyennes = $this->donnees->pluck('moyenne_generale')->filter();
        $credits = $this->donnees->pluck('credits_valides')->filter();
        $notesEliminatoires = $this->donnees->where('has_note_eliminatoire', true)->count();

        return [
            'total_etudiants' => $total,
            'admis' => $admis,
            'rattrapage' => $rattrapage,
            'redoublant' => $redoublant,
            'exclus' => $exclus,
            'taux_reussite' => $total > 0 ? round(($admis / $total) * 100, 1) : 0,
            'moyenne_promo' => $moyennes->count() > 0 ? round($moyennes->avg(), 2) : 0,
            'credits_moyen' => $credits->count() > 0 ? round($credits->avg(), 1) : 0,
            'etudiants_avec_note_eliminatoire' => $notesEliminatoires,
        ];
    }

    private function getConditions()
    {
        $sessionType = $this->session->type ?? 'Normale';
        
        if ($sessionType === 'Normale') {
            return 'Sous réserve de validation de Stage Hospitalier et des modules pratiques';
        } else {
            return 'Sous réserve de validation de Stage Hospitalier et rattrapage des modules défaillants';
        }
    }

    public function getFileName()
    {
        $sessionType = $this->session->type === 'Normale' ? 'Session1' : 'Session2';
        $niveau = str_replace(' ', '_', $this->niveau->nom);
        $parcours = $this->parcours ? '_' . str_replace(' ', '_', $this->parcours->nom) : '';
        $annee = str_replace(['/', ' '], ['_', '_'], $this->anneeUniversitaire->libelle ?? '2024-2025');
        $date = now()->format('Ymd_His');

        // Ajouter le type de contenu
        $typeContenu = '';
        if (!$this->donnees->isEmpty()) {
            $typeDocument = $this->determinerTypeDocument();
            if ($typeDocument['type'] !== 'mixte' && $typeDocument['type'] !== 'vide') {
                $typeContenu = '_' . ucfirst(str_replace('_seulement', '', $typeDocument['type']));
            }
        }

        return "Liste_{$sessionType}_{$niveau}{$parcours}_{$annee}{$typeContenu}_{$date}.pdf";
    }

    public static function createFromData($donnees, $session, $niveau, $parcours = null, $colonnesConfig = [])
    {
        return new self($donnees, $session, $niveau, $parcours, $colonnesConfig);
    }
}