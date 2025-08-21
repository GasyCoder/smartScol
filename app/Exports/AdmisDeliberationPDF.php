<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class AdmisDeliberationPDF
{
    protected $donnees;
    protected $session;
    protected $niveau;
    protected $parcours;
    protected $colonnesConfig;

    public function __construct($donnees, $session, $niveau, $parcours = null, $colonnesConfig = [])
    {
        // ✅ CORRECTION : Accepter toutes les données, pas seulement les admis
        $this->donnees = collect($donnees);
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
    }

    public function generate()
    {
        try {
            $data = $this->prepareData();

            $pdf = Pdf::loadView('exports.admis-deliberation-pdf', $data)
                     ->setPaper('a4', 'portrait')
                     ->setOptions([
                         'defaultFont' => 'Arial',
                         'isHtml5ParserEnabled' => true,
                         'isRemoteEnabled' => true,
                         'margin_top' => 10,
                         'margin_bottom' => 10,
                         'margin_left' => 10,
                         'margin_right' => 10,
                     ]);

            return $pdf;

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF', [
                'error' => $e->getMessage(),
                'nb_donnees' => $this->donnees->count()
            ]);
            throw $e;
        }
    }

    private function prepareData()
    {
        $sessionType = $this->session->type === 'Normale' ? 'Session 1 (Normale)' : 'Session 2 (Rattrapage)';
        $parcoursTxt = $this->parcours ? ' - ' . $this->parcours->nom : '';

        // ✅ CALCUL DES STATISTIQUES SUR TOUTES LES DONNÉES
        $stats = [];
        if ($this->donnees->count() > 0) {
            $stats = [
                'total' => $this->donnees->count(),
                'moyenne_max' => $this->donnees->max('moyenne_generale'),
                'moyenne_min' => $this->donnees->min('moyenne_generale'),
                'moyenne_generale' => round($this->donnees->avg('moyenne_generale'), 2),
            ];
        }

        // ✅ CALCUL DES MENTIONS SUR TOUTES LES DONNÉES
        $mentions = [];
        if ($this->donnees->count() > 0) {
            $mentions = [
                'excellent' => $this->donnees->where('moyenne_generale', '>=', 16)->count(),
                'tres_bien' => $this->donnees->whereBetween('moyenne_generale', [14, 15.99])->count(),
                'bien' => $this->donnees->whereBetween('moyenne_generale', [12, 13.99])->count(),
                'assez_bien' => $this->donnees->whereBetween('moyenne_generale', [10, 11.99])->count(),
            ];
        }

        // ✅ CALCUL DES RÉPARTITIONS PAR DÉCISION
        $repartitionDecisions = [];
        if ($this->donnees->count() > 0) {
            // Utiliser le bon champ selon le contexte
            $champDecision = $this->donnees->first()['decision_simulee'] ?? false ? 'decision_simulee' : 'decision_actuelle';
            if (!$champDecision || !isset($this->donnees->first()[$champDecision])) {
                $champDecision = 'decision_actuelle';
            }

            $decisions = $this->donnees->pluck($champDecision);
            $repartitionDecisions = [
                'admis' => $decisions->filter(function($d) { return $d === 'admis'; })->count(),
                'rattrapage' => $decisions->filter(function($d) { return $d === 'rattrapage'; })->count(),
                'redoublant' => $decisions->filter(function($d) { return $d === 'redoublant'; })->count(),
                'exclus' => $decisions->filter(function($d) { return $d === 'exclus'; })->count(),
            ];
        }

        // ✅ PRÉPARER LES DONNÉES AVEC RANG
        $donneesAvecRang = [];
        $rang = 1;
        foreach ($this->donnees as $item) {
            $itemArray = is_array($item) ? $item : $item->toArray();
            $itemArray['rang'] = $rang;
            $donneesAvecRang[] = $itemArray;
            $rang++;
        }

        return [
            'donnees' => $donneesAvecRang,
            'session' => $this->session,
            'session_type' => $sessionType,
            'niveau' => $this->niveau,
            'parcours' => $this->parcours,
            'parcours_text' => $parcoursTxt,
            'colonnes_config' => $this->colonnesConfig,
            'stats' => $stats,
            'mentions' => $mentions,
            'repartition_decisions' => $repartitionDecisions,
            'date_generation' => now()->format('d/m/Y H:i:s'),
            'annee_universitaire' => $this->session->anneeUniversitaire->libelle ?? 'N/A',

            // ✅ NOUVELLES DONNÉES POUR LE PDF
            'titre_document' => $this->genererTitreDocument(),
            'colonnes_visibles' => $this->getColonnesVisibles(),
            'has_filtres' => $this->hasFilters(),
            'source' => 'simulation', // ou 'deliberation' selon le contexte
        ];
    }

    /**
     * ✅ GÉNÈRE LE TITRE DU DOCUMENT SELON LE CONTENU
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
     * ✅ OBTIENT LA LISTE DES COLONNES VISIBLES
     */
    private function getColonnesVisibles()
    {
        $colonnes = [];

        if ($this->colonnesConfig['rang']) $colonnes[] = ['key' => 'rang', 'label' => 'Rang'];
        if ($this->colonnesConfig['nom_complet']) $colonnes[] = ['key' => 'nom_complet', 'label' => 'Nom et Prénom'];
        if ($this->colonnesConfig['matricule']) $colonnes[] = ['key' => 'matricule', 'label' => 'Matricule'];
        if ($this->colonnesConfig['moyenne']) $colonnes[] = ['key' => 'moyenne', 'label' => 'Moyenne'];
        if ($this->colonnesConfig['credits']) $colonnes[] = ['key' => 'credits', 'label' => 'Crédits'];
        if ($this->colonnesConfig['decision']) $colonnes[] = ['key' => 'decision', 'label' => 'Décision'];
        if ($this->colonnesConfig['niveau']) $colonnes[] = ['key' => 'niveau', 'label' => 'Niveau'];

        return $colonnes;
    }

    /**
     * ✅ VÉRIFIE S'IL Y A DES FILTRES APPLIQUÉS
     */
    private function hasFilters()
    {
        if ($this->donnees->isEmpty()) {
            return false;
        }

        // Vérifier si toutes les décisions sont identiques (filtre appliqué)
        $decisions = $this->donnees->pluck('decision_simulee')->filter();
        if ($decisions->isEmpty()) {
            $decisions = $this->donnees->pluck('decision_actuelle')->filter();
        }

        return $decisions->unique()->count() === 1;
    }

    public function getFileName()
    {
        $sessionType = $this->session->type === 'Normale' ? 'Session1' : 'Session2';
        $niveau = str_replace(' ', '_', $this->niveau->nom);
        $parcours = $this->parcours ? '_' . str_replace(' ', '_', $this->parcours->nom) : '';
        $annee = str_replace(['/', ' '], ['_', '_'], $this->session->anneeUniversitaire->libelle ?? '2024-2025');
        $date = now()->format('Ymd_His');

        // ✅ AJOUTER INFO SUR LE CONTENU
        $typeContenu = '';
        if (!$this->donnees->isEmpty()) {
            $decisions = $this->donnees->pluck('decision_simulee')->filter();
            if ($decisions->isEmpty()) {
                $decisions = $this->donnees->pluck('decision_actuelle')->filter();
            }

            if ($decisions->unique()->count() === 1) {
                $typeContenu = '_' . ucfirst($decisions->first());
            }
        }

        return "Resultats_{$sessionType}_{$niveau}{$parcours}_{$annee}{$typeContenu}_{$date}.pdf";
    }
}
