<?php

namespace App\Services;

use App\Models\Niveau;
use App\Models\Parcour;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ResultatsExport;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    /**
     * ✅ Export Excel avec le nouveau format de l'image
     */
    public function exporterExcel($resultats, $uesStructure = [], $niveau = null, $parcours = null, $anneeUniv = null, $session = null)
    {
        try {
            if (empty($resultats)) {
                throw new \Exception('Aucun résultat disponible pour l\'export.');
            }

            // ✅ Générer nom de fichier descriptif
            $nomFichier = $this->genererNomFichierExcel($niveau, $parcours, $session, $anneeUniv);
            
            // ✅ Utiliser la nouvelle classe d'export avec tous les paramètres
            return Excel::download(
                new ResultatsExport($resultats, $uesStructure, $session, $niveau, $parcours, $anneeUniv), 
                $nomFichier
            );
            
        } catch (\Exception $e) {
            Log::error('Erreur export Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Export Excel admis uniquement
     */
    public function exporterExcelAdmis($resultats, $uesStructure = [], $niveau = null, $parcours = null, $anneeUniv = null, $session = null)
    {
        try {
            // Filtrer pour ne garder que les admis
            $resultatsAdmis = collect($resultats)->filter(function($resultat) {
                return ($resultat['decision'] ?? '') === 'admis';
            })->values()->toArray();

            if (empty($resultatsAdmis)) {
                throw new \Exception('Aucun étudiant admis à exporter.');
            }

            $nomFichier = $this->genererNomFichierExcel($niveau, $parcours, $session, $anneeUniv, 'Admis');
            
            return Excel::download(
                new ResultatsExport($resultatsAdmis, $uesStructure, $session, $niveau, $parcours, $anneeUniv), 
                $nomFichier
            );
            
        } catch (\Exception $e) {
            Log::error('Erreur export Excel admis: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Export PDF simple (garder l'existant)
     */
    public function exporterPDF($resultats, $niveau = null, $anneeUniv = null, $parcours = null, $uesStructure = [])
    {
        try {
            if (empty($resultats)) {
                throw new \Exception('Aucun résultat disponible pour l\'export PDF.');
            }

            $donnees = [
                'resultats' => $resultats,
                'niveau' => $niveau,
                'parcours' => $parcours,
                'annee_universitaire' => $anneeUniv,
                'ues_structure' => $uesStructure,
                'statistiques' => $this->calculerStats($resultats),
                'date_export' => now()->format('d/m/Y H:i'),
                'export_par' => Auth::user()->name ?? 'Système'
            ];

            $nomFichier = $this->genererNomFichierPDF($niveau, $parcours, $anneeUniv);

            $pdf = Pdf::loadView('exports.resultats-pdf', $donnees)
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $nomFichier);
            
        } catch (\Exception $e) {
            Log::error('Erreur export PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Export PDF admis uniquement
     */
    public function exporterAdmisPDF($resultats, $niveau = null, $anneeUniv = null, $parcours = null, $uesStructure = [])
    {
        try {
            // Filtrer pour ne garder que les admis
            $resultatsAdmis = collect($resultats)->filter(function($resultat) {
                return ($resultat['decision'] ?? '') === 'admis';
            })->values()->toArray();

            if (empty($resultatsAdmis)) {
                throw new \Exception('Aucun étudiant admis à exporter.');
            }

            $donnees = [
                'resultats' => $resultatsAdmis,
                'niveau' => $niveau,
                'parcours' => $parcours,
                'annee_universitaire' => $anneeUniv,
                'ues_structure' => $uesStructure,
                'statistiques' => $this->calculerStats($resultatsAdmis),
                'date_export' => now()->format('d/m/Y H:i'),
                'export_par' => Auth::user()->name ?? 'Système',
                'titre_special' => 'LISTE DES ADMIS'
            ];

            $nomFichier = $this->genererNomFichierPDF($niveau, $parcours, $anneeUniv, 'Admis');

            $pdf = Pdf::loadView('exports.resultats-pdf', $donnees)
                ->setPaper('a4', 'landscape');

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $nomFichier);
            
        } catch (\Exception $e) {
            Log::error('Erreur export PDF admis: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Générer nom de fichier Excel descriptif
     */
    private function genererNomFichierExcel($niveau = null, $parcours = null, $session = null, $anneeUniv = null, $suffix = null)
    {
        $parts = ['Resultats'];
        
        if ($session) {
            $parts[] = $session->type === 'Normale' ? 'Session1' : 'Session2';
        }
        
        if ($niveau) {
            $parts[] = str_replace(' ', '_', $niveau->nom);
        }
        
        if ($parcours) {
            $parts[] = str_replace(' ', '_', $parcours->nom);
        }
        
        if ($anneeUniv) {
            $parts[] = str_replace(['/', ' '], ['_', '_'], $anneeUniv->libelle);
        }
        
        if ($suffix) {
            $parts[] = $suffix;
        }
        
        $parts[] = now()->format('Y-m-d_H-i');
        
        return implode('_', $parts) . '.xlsx';
    }

    /**
     * ✅ Générer nom de fichier PDF descriptif
     */
    private function genererNomFichierPDF($niveau = null, $parcours = null, $anneeUniv = null, $suffix = null)
    {
        $parts = ['Resultats'];
        
        if ($niveau) {
            $parts[] = str_replace(' ', '_', $niveau->nom);
        }
        
        if ($parcours) {
            $parts[] = str_replace(' ', '_', $parcours->nom);
        }
        
        if ($anneeUniv) {
            $parts[] = str_replace(['/', ' '], ['_', '_'], $anneeUniv->libelle);
        }
        
        if ($suffix) {
            $parts[] = $suffix;
        }
        
        $parts[] = now()->format('Y-m-d_H-i');
        
        return implode('_', $parts) . '.pdf';
    }

    /**
     * ✅ Calculer statistiques complètes
     */
    private function calculerStats($resultats)
    {
        $total = count($resultats);
        $decisions = collect($resultats)->pluck('decision');
        $moyennes = collect($resultats)->pluck('moyenne_generale')->filter();
        $creditsTotal = collect($resultats)->sum('credits_valides');
        
        $admis = $decisions->filter(fn($d) => $d === 'admis')->count();
        $rattrapage = $decisions->filter(fn($d) => $d === 'rattrapage')->count();
        $redoublant = $decisions->filter(fn($d) => $d === 'redoublant')->count();
        $exclus = $decisions->filter(fn($d) => $d === 'exclus')->count();
        
        return [
            'total_etudiants' => $total,
            'admis' => $admis,
            'rattrapage' => $rattrapage,
            'redoublant' => $redoublant,
            'exclus' => $exclus,
            'taux_reussite' => $total > 0 ? round(($admis / $total) * 100, 1) : 0,
            'moyenne_promo' => $moyennes->count() > 0 ? round($moyennes->avg(), 2) : 0,
            'credits_moyen' => $total > 0 ? round($creditsTotal / $total, 1) : 0,
            'etudiants_avec_note_eliminatoire' => collect($resultats)->where('has_note_eliminatoire', true)->count(),
            'etudiants_jury_validated' => collect($resultats)->where('jury_validated', true)->count(),
        ];
    }

    /**
     * ✅ Export rapide depuis le composant Livewire
     */
    public function exportRapideExcel($resultats, $uesStructure, $sessionType = 'Session1')
    {
        try {
            $nomFichier = "Export_Rapide_{$sessionType}_" . now()->format('Y-m-d_H-i') . '.xlsx';
            
            // Session mock pour l'export rapide
            $sessionMock = (object) [
                'type' => $sessionType === 'Session1' ? 'Normale' : 'Rattrapage',
                'id' => null
            ];
            
            return Excel::download(
                new ResultatsExport($resultats, $uesStructure, $sessionMock), 
                $nomFichier
            );
            
        } catch (\Exception $e) {
            Log::error('Erreur export rapide Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Valider les données avant export
     */
    public function validerDonneesExport($resultats, $uesStructure = [])
    {
        $erreurs = [];
        
        if (empty($resultats)) {
            $erreurs[] = 'Aucun résultat fourni pour l\'export';
        }
        
        if (empty($uesStructure)) {
            $erreurs[] = 'Structure UE manquante - l\'export pourrait être incomplet';
        }
        
        // Vérifier la cohérence des données
        foreach ($resultats as $index => $resultat) {
            if (!isset($resultat['etudiant'])) {
                $erreurs[] = "Ligne " . ($index + 1) . ": Informations étudiant manquantes";
            }
            
            if (!isset($resultat['moyenne_generale'])) {
                $erreurs[] = "Ligne " . ($index + 1) . ": Moyenne générale manquante";
            }
            
            if (!isset($resultat['decision'])) {
                $erreurs[] = "Ligne " . ($index + 1) . ": Décision manquante";
            }
        }
        
        return [
            'valid' => empty($erreurs),
            'erreurs' => $erreurs,
            'total_resultats' => count($resultats),
            'total_ues' => count($uesStructure)
        ];
    }
}