<?php
// app/Livewire/Resultats/ListeResultatsPACES.php

namespace App\Livewire\Resultats;

use App\Models\UE;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\SessionExam;
use App\Models\DeliberPaces;
use Livewire\WithPagination;
use App\Models\ResultatFinal;
use App\Models\PresenceExamen;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResultatsPacesExport;
use Illuminate\Support\Facades\Cache;
use App\Services\ResultatsPacesPdfService;
use Illuminate\Pagination\LengthAwarePaginator;

class ListeResultatsPACES extends Component
{
    use WithPagination;
    
    // ========================================
    // 🎯 PROPRIÉTÉS (NETTOYÉES - SIMULATION RETIRÉE)
    // ========================================
    
    // NAVIGATION
    public $etape = 'selection';
    public $filtreDecision = 'tous';
    
    // SÉLECTION
    public $parcoursSelectionne;
    public $parcoursData;
    public $parcoursSlug;
    
    // CONFIGURATION
    public $anneeActive;
    public $sessionActive;
    public $niveauPACES;
    public $parcoursPACES;

    // FLAGS
    public bool $afficherTableau = false;
    
    // COMPTEURS
    public array $compteurs = ['admis' => 0, 'redoublant' => 0, 'exclus' => 0];
    
    // STATISTIQUES
    public $statistiquesDetailes = [];
    
    // UES
    public $uesStructure;
    
    // DÉLIBÉRATION
    public $derniereDeliberation = null;
    
    // PAGINATION
    public $perPage = 20;
    public $perPageOptions = [10, 20, 50, 100, 150, 200, 300, 500, 'Tous'];
    public $recherche = '';
    
    // VERSION (pour forcer re-render)
    public int $resultatsVersion = 0;
    
    protected $queryString = [
        'parcoursSlug' => ['except' => ''],
        'filtreDecision' => ['except' => 'tous'],
        'perPage' => ['except' => 50],
        'recherche' => ['except' => ''],
    ];

    // CACHE STATIC
    private static $cacheResultats = [];
    private const MATRICULE_ANCIEN_MAX = 38999;

    // ========================================
    // 🚀 LIFECYCLE
    // ========================================

    public function mount()
    {
        Log::info('🚀 MOUNT - Démarrage ListeResultatsPACES');
        
        $this->initialiserCollections();
        $this->chargerConfigurationActive();
        $this->chargerParcours();

        if ($this->parcoursSlug) {
            $this->restaurerDepuisUrl($this->parcoursSlug);
            $this->etape = 'resultats';
        }

        if ($this->niveauPACES && $this->sessionActive) {
            $this->getStatistiquesParcoursProperty();
        }
        
        Log::info('✅ MOUNT - ListeResultatsPACES initialisé');
    }

    // ========================================
    // 🔑 GESTION DES CLÉS DE CACHE
    // ========================================
    
    private function getCacheKey(): string
    {
        return sprintf(
            'paces_resultats_%d_%d_%d',
            $this->niveauPACES->id ?? 0,
            $this->parcoursSelectionne ?? 0,
            $this->sessionActive->id ?? 0
        );
    }

    // ========================================
    // 📊 CHARGEMENT DES RÉSULTATS
    // ========================================
    
    private function chargerResultatsDepuisDB(): array
    {
        Log::info('📥 CHARGEMENT DB - Début');
        
        $cacheKeyIds = "resultats_paces_{$this->parcoursSelectionne}_{$this->sessionActive->id}_ids";
        
        $resultatsIds = Cache::remember($cacheKeyIds, 300, function() {
            return DB::table('resultats_finaux')
                ->join('examens', 'resultats_finaux.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $this->niveauPACES->id)
                ->where('examens.parcours_id', $this->parcoursSelectionne)
                ->where('resultats_finaux.session_exam_id', $this->sessionActive->id)
                ->where('resultats_finaux.statut', ResultatFinal::STATUT_PUBLIE)
                ->pluck('resultats_finaux.id')
                ->toArray();
        });

        if (empty($resultatsIds)) {
            Log::warning('⚠️ CHARGEMENT DB - Aucun résultat trouvé');
            return [];
        }

        Log::info('📊 CHARGEMENT DB - IDs trouvés', ['count' => count($resultatsIds)]);

        $resultatsFinaux = ResultatFinal::whereIn('id', $resultatsIds)
            ->select([
                'id',
                'etudiant_id',
                'ec_id',
                'examen_id',
                'note',
                'decision',
                'is_deliber',
                'deliber_at',
                'deliber_by',
                'jury_validated'
            ])
            ->with([
                'etudiant:id,nom,prenom,matricule',
                'ec:id,nom,abr,ue_id,coefficient',
                'ec.ue:id,nom,abr,credits'
            ])
            ->get();

        if ($resultatsFinaux->isEmpty()) {
            Log::warning('⚠️ CHARGEMENT DB - Résultats vides après chargement');
            return [];
        }

        Log::info('✅ CHARGEMENT DB - Résultats chargés', [
            'count' => $resultatsFinaux->count()
        ]);

        return $this->traiterResultatsEnMemoire($resultatsFinaux);
    }

    private function traiterResultatsEnMemoire($resultats)
    {
        // Groupement par étudiant
        $groupes = $resultats->groupBy('etudiant_id');

        // Préparer la map "anciens redoublants" en UNE requête
        $etudiantIds = $groupes->keys()->map(fn($id) => (int)$id)->all();
        $anciensMap = $this->getAnciensRedoublantsMap($etudiantIds);

        $final = [];
        foreach ($groupes as $etudiantId => $notes) {
            $etudiant = $notes->first()->etudiant;
            if (!$etudiant) continue;

            // === Calcul rapide par UE (une seule passe) ===
            $byUE = [];
            $totalCredits = 0;
            foreach ($notes as $n) {
                $ueId = $n->ec->ue_id;
                $ue = $n->ec->ue;
                if (!isset($byUE[$ueId])) {
                    $byUE[$ueId] = ['sum'=>0.0,'cnt'=>0,'has0'=>false,'credits'=>$ue->credits,'nom'=>$ue->nom];
                    $totalCredits += (int)$ue->credits;
                }
                $byUE[$ueId]['sum'] += (float)$n->note;
                $byUE[$ueId]['cnt'] += 1;
                if ((float)$n->note == 0.0) $byUE[$ueId]['has0'] = true;
            }

            $creditsValides = 0;
            $moysUE = [];
            $resUE = [];
            $hasZeroGlobal = false;

            foreach ($byUE as $ueId => $info) {
                $moyUE = $info['cnt'] ? round($info['sum'] / $info['cnt'], 2) : 0.0;
                $validee = ($moyUE >= 10.0) && !$info['has0'];
                if ($validee) $creditsValides += (int)$info['credits'];

                $hasZeroGlobal = $hasZeroGlobal || $info['has0'];
                $moysUE[] = $moyUE;
                $resUE[] = [
                    'ue_id' => $ueId,
                    'ue_nom' => $info['nom'],
                    'moyenne_ue' => $moyUE,
                    'ue_validee' => $validee,
                    'has_note_eliminatoire' => $info['has0'],
                ];
            }

            $moyenneGenerale = count($moysUE) ? round(array_sum($moysUE) / count($moysUE), 2) : 0.0;

            // Ancien / Nouveau
            $mat = (int)$etudiant->matricule;
            $eid = (int)$etudiantId;
            $isAncien = ($mat <= self::MATRICULE_ANCIEN_MAX) || isset($anciensMap[$eid]);

            $premier = $notes->first();

            $item = [
                'etudiant' => $etudiant,
                'notes' => $notes->keyBy('ec_id'),
                'resultats_ue' => $resUE,
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => $creditsValides,
                'total_credits' => $totalCredits,
                'has_note_eliminatoire' => $hasZeroGlobal,
                'decision' => $premier->decision ?? 'non_definie',
                'is_deliber' => (bool)($premier->is_deliber ?? false),
                'deliber_at' => $premier->deliber_at,
                'est_ancien' => (bool)$isAncien,
                'est_redoublant' => (bool)$isAncien,
                'est_passant' => !$isAncien,
            ];

            $final[] = $item;
        }

        // Tri visuel d'affichage selon le mérite
        usort($final, function ($a, $b) {
            // 1) Décision (groupe)
            $ra = $this->decisionRank($a['decision'] ?? null);
            $rb = $this->decisionRank($b['decision'] ?? null);
            if ($ra !== $rb) return $ra <=> $rb;

            // 2) Crédits validés (desc)
            $ca = (int)($a['credits_valides'] ?? 0);
            $cb = (int)($b['credits_valides'] ?? 0);
            if ($cb !== $ca) return $cb <=> $ca;

            // 3) Moyenne générale (desc)
            $ma = (float)($a['moyenne_generale'] ?? 0);
            $mb = (float)($b['moyenne_generale'] ?? 0);
            if ($mb !== $ma) return $mb <=> $ma;

            // 4) Sans note éliminatoire d'abord (false < true)
            $za = (bool)($a['has_note_eliminatoire'] ?? false);
            $zb = (bool)($b['has_note_eliminatoire'] ?? false);
            if ($za !== $zb) return $za <=> $zb;

            // 5) Matricule (asc) pour stabiliser
            return (int)$a['etudiant']->matricule <=> (int)$b['etudiant']->matricule;
        });

        return $final;
    }

    /**
     * Map des étudiants ayant été redoublants sur une année antérieure
     */
    private function getAnciensRedoublantsMap(array $etudiantIds): array
    {
        if (empty($etudiantIds)) return [];

        static $cacheByKey = [];
        $key = $this->parcoursSelectionne.'|'.md5(json_encode(array_values($etudiantIds)));

        if (isset($cacheByKey[$key])) {
            return $cacheByKey[$key];
        }

        $anneeCouranteId = optional($this->anneeActive)->id;

        try {
            $anciens = DB::table('resultats_finaux as rf')
                ->join('session_exams as se', 'rf.session_exam_id', '=', 'se.id')
                ->join('examens as e', 'rf.examen_id', '=', 'e.id')
                ->whereIn('rf.etudiant_id', $etudiantIds)
                ->where('rf.decision', ResultatFinal::DECISION_REDOUBLANT)
                ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                ->where('e.niveau_id', $this->niveauPACES->id)
                ->where('e.parcours_id', $this->parcoursSelectionne)
                ->where('se.annee_universitaire_id', '!=', $anneeCouranteId)
                ->distinct()
                ->pluck('rf.etudiant_id')
                ->toArray();

            return $cacheByKey[$key] = array_fill_keys(array_map('intval', $anciens), true);
        } catch (\Throwable $e) {
            \Log::warning('getAnciensRedoublantsMap(): fallback empty', ['err' => $e->getMessage()]);
            return $cacheByKey[$key] = [];
        }
    }

    // ========================================
    // 📁 CHARGEMENT DES PARCOURS
    // ========================================
    
    private function chargerResultatsParcours()
    {
        Log::info('📂 CHARGEMENT PARCOURS - Début');

        if (!$this->parcoursSelectionne || !$this->sessionActive) {
            $this->etape = 'selection';
            Log::warning('⚠️ CHARGEMENT PARCOURS - Pas de parcours ou session');
            return;
        }

        try {
            $this->chargerUEStructure();
            $resultats = $this->chargerResultatsDepuisDB();

            if (empty($resultats)) {
                toastr()->warning('Aucun résultat publié pour ce parcours');
                Log::warning('⚠️ CHARGEMENT PARCOURS - Aucun résultat');
                return;
            }

            // Calculer stats
            $this->calculerStatistiquesDetailees($resultats);
            
            // Vérifier si délibéré
            $premierResultat = $resultats[0] ?? null;
            $estDelibere = $premierResultat && ($premierResultat['is_deliber'] ?? false);
            
            if ($estDelibere) {
                $this->afficherTableau = true;
                Log::info('✅ Résultats délibérés - Tableau activé');
            }

            Log::info("✅ CHARGEMENT PARCOURS - Terminé", [
                'nb_resultats' => count($resultats),
                'est_delibere' => $estDelibere,
                'afficher_tableau' => $this->afficherTableau
            ]);
            
            toastr()->success(count($resultats) . ' résultat(s) chargé(s)');
        } catch (\Throwable $e) {
            Log::error("❌ CHARGEMENT PARCOURS - Erreur: " . $e->getMessage());
            toastr()->error('Erreur: ' . $e->getMessage());
        }
    }

    // ========================================
    // 📊 CALCUL DES STATISTIQUES
    // ========================================
    
    private function calculerStatistiquesDetailees($resultats)
    {
        Log::info('📊 STATS DÉTAILLÉES - Début du calcul');

        // Vérifier si délibéré
        $premierResultat = is_array($resultats) && !empty($resultats) ? reset($resultats) : null;
        $estDelibere = !empty($premierResultat) && !empty($premierResultat['is_deliber']);

        // Compteurs
        $admis = 0;
        $redoublant = 0;
        $exclus = 0;
        $redoublantsEtudiants = 0;

        if ($estDelibere) {
            foreach ($resultats as $r) {
                $decision = $r['decision'] ?? 'exclus';
                if ($decision === 'admis') {
                    $admis++;
                } elseif ($decision === 'redoublant') {
                    $redoublant++;
                } else {
                    $exclus++;
                }

                $isAncienCompat = (bool)($r['est_redoublant'] ?? ($r['est_ancien'] ?? false));
                if ($isAncienCompat) {
                    $redoublantsEtudiants++;
                }
            }
        } else {
            foreach ($resultats as $r) {
                $isAncienCompat = (bool)($r['est_redoublant'] ?? ($r['est_ancien'] ?? false));
                if ($isAncienCompat) {
                    $redoublantsEtudiants++;
                }
            }
            Log::info('⚠️ STATS - Résultats non délibérés, décisions à 0');
        }

        // Présence
        $statsPresence = $this->obtenirStatistiquesPresence();

        $totalInscrits = $statsPresence['total_inscrits'];
        $totalPresents = $statsPresence['presents'];
        $totalAbsents = $statsPresence['absents'];
        $nouveaux = max(0, $totalPresents - $redoublantsEtudiants);

        // Assemble
        $this->statistiquesDetailes = [
            'total_inscrits' => $totalInscrits,
            'total_presents' => $totalPresents,
            'total_absents' => $totalAbsents,
            'admis' => $admis,
            'redoublant_autorises' => $redoublant,
            'exclus' => $exclus,
            'etudiants_redoublants' => $redoublantsEtudiants,
            'etudiants_nouveaux' => $nouveaux,
            'taux_reussite' => ($totalPresents > 0 && $admis > 0) ? round(($admis / $totalPresents) * 100, 1) : 0,
            'taux_presence' => $totalInscrits > 0 ? round(($totalPresents / $totalInscrits) * 100, 1) : 0,
            'est_delibere' => $estDelibere,
            'en_simulation' => false,
        ];

        // Compteurs pour les toasts
        $this->compteurs = [
            'admis' => $admis,
            'redoublant' => $redoublant,
            'exclus' => $exclus
        ];

        Log::info('✅ STATS DÉTAILLÉES - Calculées', [
            'est_delibere' => $estDelibere,
            'stats' => $this->statistiquesDetailes,
            'compteurs' => $this->compteurs
        ]);
    }

    private function obtenirStatistiquesPresence()
    {
        static $statsCache = null;
        
        if ($statsCache !== null) {
            return $statsCache;
        }

        if (!$this->sessionActive || !$this->parcoursSelectionne) {
            return $statsCache = [
                'total_inscrits' => 0,
                'presents' => 0,
                'absents' => 0
            ];
        }

        $totalInscrits = Etudiant::where('niveau_id', $this->niveauPACES->id)
            ->where('parcours_id', $this->parcoursSelectionne)
            ->where('is_active', true)
            ->count();

        $examen = Examen::where('niveau_id', $this->niveauPACES->id)
            ->where('parcours_id', $this->parcoursSelectionne)
            ->first();

        if (!$examen) {
            return $statsCache = [
                'total_inscrits' => $totalInscrits,
                'presents' => 0,
                'absents' => $totalInscrits
            ];
        }

        $statsPresence = PresenceExamen::getStatistiquesExamen(
            $examen->id,
            $this->sessionActive->id
        );

        return $statsCache = [
            'total_inscrits' => $totalInscrits,
            'presents' => (int)$statsPresence['presents'],
            'absents' => (int)$statsPresence['absents'],
        ];
    }

    // ========================================
    // 🔍 FILTRES & PAGINATION
    // ========================================

    public function getResultatsPaginesProperty()
    {
        $resultats = $this->chargerResultatsDepuisDB();
        $resultatsAffiches = $this->filtrerParDecision($resultats);

        if (!empty($this->recherche)) {
            $resultatsAffiches = $this->filtrerParRecherche($resultatsAffiches);
        }

        $collection = collect($resultatsAffiches);

        if ($this->perPage === 'Tous') {
            return new LengthAwarePaginator(
                $collection,
                $collection->count(),
                $collection->count(),
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        } else {
            $page = max(1, (int) Paginator::resolveCurrentPage('page'));
            $perPage = (int) $this->perPage;
            
            return new LengthAwarePaginator(
                $collection->forPage($page, $perPage),
                $collection->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }
    }

    private function filtrerParDecision(array $resultats): array
    {
        if ($this->filtreDecision === 'tous') {
            return $resultats;
        }

        return array_values(array_filter($resultats, function ($r) {
            return ($r['decision'] ?? 'exclus') === $this->filtreDecision;
        }));
    }

    private function filtrerParRecherche($resultats)
    {
        $terme = mb_strtolower(trim($this->recherche));
        
        if (empty($terme)) {
            return $resultats;
        }

        return array_filter($resultats, function($resultat) use ($terme) {
            if (!isset($resultat['etudiant'])) {
                return false;
            }

            $etudiant = $resultat['etudiant'];
            
            $matricule = mb_strtolower((string) $etudiant->matricule);
            if (str_contains($matricule, $terme)) {
                return true;
            }

            $nom = mb_strtolower($etudiant->nom);
            if (str_contains($nom, $terme)) {
                return true;
            }

            $prenom = mb_strtolower($etudiant->prenom);
            if (str_contains($prenom, $terme)) {
                return true;
            }

            return str_contains($nom . ' ' . $prenom, $terme) || str_contains($prenom . ' ' . $nom, $terme);
        });
    }

    public function getInfosPaginationProperty()
    {
        $paginator = $this->resultats_pagines;
        
        return [
            'de' => $paginator->firstItem() ?? 0,
            'a' => $paginator->lastItem() ?? 0,
            'total' => $paginator->total(),
            'page_actuelle' => $paginator->currentPage(),
            'derniere_page' => $paginator->lastPage()
        ];
    }

    public function changerFiltre($decision)
    {
        $this->filtreDecision = $decision;
        $this->resetPage();
    }

    public function updatedRecherche()
    {
        $this->resetPage();
    }

    public function reinitialiserRecherche()
    {
        $this->recherche = '';
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // ========================================
    // 🔄 NAVIGATION
    // ========================================

    private function restaurerDepuisUrl($slug)
    {
        Log::info('🔗 RESTAURATION URL - Début', ['slug' => $slug]);
        
        $parcours = $this->parcoursPACES->firstWhere('abr', $slug);
        
        if ($parcours) {
            $this->parcoursSelectionne = $parcours->id;
            $this->parcoursData = $parcours;
            $this->parcoursSlug = $slug;
            
            $this->derniereDeliberation = DeliberPaces::where('niveau_id', $this->niveauPACES->id)
                ->where('parcours_id', $parcours->id)
                ->where('session_exam_id', $this->sessionActive->id)
                ->latest('applique_at')
                ->first();

            if ($this->derniereDeliberation) {
                $this->afficherTableau = $this->verifierResultatsDeliberes();
                Log::info('✅ RESTAURATION URL - Délibération trouvée');
            } else {
                $this->afficherTableau = false;
                Log::info('ℹ️ RESTAURATION URL - Aucune délibération');
            }
            
            $this->etape = 'resultats';
            $this->chargerResultatsParcours();
        } else {
            $this->etape = 'selection';
            $this->parcoursSlug = null;
            Log::warning('⚠️ RESTAURATION URL - Parcours non trouvé');
        }
    }

    public function selectionnerParcours($parcoursId)
    {
        Log::info('🎯 SÉLECTION PARCOURS', ['parcours_id' => $parcoursId]);
        
        $this->parcoursSelectionne = $parcoursId;
        $this->parcoursData = $this->parcoursPACES->find($parcoursId);
        $this->parcoursSlug = $this->parcoursData->abr;

        $this->dispatch('replaceUrl', [
            'url' => url()->current() . '?parcoursSlug=' . $this->parcoursSlug
        ]);

        $this->derniereDeliberation = DeliberPaces::where('niveau_id', $this->niveauPACES->id)
            ->where('parcours_id', $parcoursId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->latest('applique_at')
            ->first();

        if ($this->derniereDeliberation) {
            $this->afficherTableau = $this->verifierResultatsDeliberes();
        } else {
            $this->afficherTableau = false;
        }

        $this->etape = 'resultats';
        $this->chargerResultatsParcours();
    }

    public function retourSelection()
    {
        Log::info('⬅️ RETOUR SÉLECTION');
        
        $this->etape = 'selection';
        $this->parcoursSelectionne = null;
        $this->parcoursSlug = null;
        $this->filtreDecision = 'tous';
        $this->afficherTableau = false;
        
        $key = $this->getCacheKey();
        Cache::forget($key);
        unset(self::$cacheResultats[$key]);
    }

    // ========================================
    // 🏗️ INITIALISATION
    // ========================================

    private function initialiserCollections()
    {
        $this->parcoursPACES = collect();
        $this->uesStructure = collect();
        $this->statistiquesDetailes = [];
    }

    private function chargerConfigurationActive()
    {
        try {
            $this->niveauPACES = Niveau::where('is_concours', true)
                ->where('abr', 'PACES')
                ->first();

            if (!$this->niveauPACES) {
                toastr()->error('Niveau PACES non configuré');
                return;
            }

            $this->anneeActive = AnneeUniversitaire::where('is_active', true)->first();
            $this->sessionActive = SessionExam::where('annee_universitaire_id', $this->anneeActive->id)
                ->where('type', 'Normale')
                ->where('is_active', true)
                ->first();

        } catch (\Exception $e) {
            Log::error('Erreur config PACES: ' . $e->getMessage());
        }
    }

    private function chargerParcours()
    {
        if (!$this->niveauPACES) return;

        $this->parcoursPACES = Parcour::where('niveau_id', $this->niveauPACES->id)
            ->where('is_active', true)
            ->withCount(['etudiants' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('id', 'asc')
            ->get();
    }

    private function chargerUEStructure()
    {
        $cacheKey = "ues_paces_{$this->niveauPACES->id}_{$this->parcoursSelectionne}";

        $this->uesStructure = Cache::remember($cacheKey, 3600, function() {
            return UE::where('niveau_id', $this->niveauPACES->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('parcours_id', $this->parcoursSelectionne)
                      ->orWhereNull('parcours_id');
                })
                ->with(['ecs' => function($q) {
                    $q->where('is_active', true)->orderBy('id');
                }])
                ->orderBy('id')
                ->get()
                ->map(function($ue) {
                    return ['ue' => $ue, 'ecs' => $ue->ecs];
                })
                ->filter(function($ueStructure) {
                    return $ueStructure['ecs']->isNotEmpty();
                });
        });
    }

    private function verifierResultatsDeliberes(): bool
    {
        if (!$this->niveauPACES || !$this->parcoursSelectionne || !$this->sessionActive) {
            return false;
        }

        try {
            return ResultatFinal::whereHas('examen', function($q) {
                    $q->where('niveau_id', $this->niveauPACES->id)
                    ->where('parcours_id', $this->parcoursSelectionne);
                })
                ->where('session_exam_id', $this->sessionActive->id)
                ->where('statut', ResultatFinal::STATUT_PUBLIE)
                ->where('is_deliber', true)
                ->whereNotNull('deliber_at')
                ->exists();

        } catch (\Exception $e) {
            Log::error('Erreur vérification délibération', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ========================================
    // 📈 PROPRIÉTÉS COMPUTED
    // ========================================

    public function getAfficherDashboardMegaProperty()
    {
        return $this->etape === 'resultats' 
            && $this->parcoursSelectionne 
            && !empty($this->statistiquesDetailes);
    }

    public function getStatistiquesParcours($parcoursId)
    {
        $cacheKey = "stats_parcours_{$parcoursId}_{$this->sessionActive->id}";
        
        return Cache::remember($cacheKey, 600, function() use ($parcoursId) {
            $totalInscrits = Etudiant::where('niveau_id', $this->niveauPACES->id)
                ->where('parcours_id', $parcoursId)
                ->where('is_active', true)
                ->count();

            $redoublants = Etudiant::where('niveau_id', $this->niveauPACES->id)
                ->where('parcours_id', $parcoursId)
                ->where('is_active', true)
                ->where('matricule', '<=', 38999)
                ->count();

            $examen = Examen::where('niveau_id', $this->niveauPACES->id)
                ->where('parcours_id', $parcoursId)
                ->first();

            $presents = 0;
            $absents = 0;

            if ($examen && $this->sessionActive) {
                $statsPresence = PresenceExamen::getStatistiquesExamen(
                    $examen->id, 
                    $this->sessionActive->id
                );

                $presents = (int)$statsPresence['presents'];
                $absents = (int)$statsPresence['absents'];
            } else {
                $absents = $totalInscrits;
            }

            return [
                'total_inscrits' => $totalInscrits,
                'redoublants' => $redoublants,
                'nouveaux' => max(0, $totalInscrits - $redoublants),
                'presents' => $presents,
                'absents' => $absents,
                'taux_presence' => $totalInscrits > 0 ? round(($presents / $totalInscrits) * 100, 1) : 0
            ];
        });
    }

    public function getStatistiquesParcoursProperty()
    {
        if (!$this->niveauPACES || !$this->sessionActive) {
            return collect();
        }

        return $this->parcoursPACES->mapWithKeys(function($parcours) {
            return [$parcours->id => $this->getStatistiquesParcours($parcours->id)];
        });
    }

    public function getParcoursDeliberesProperty()
    {
        if (!$this->niveauPACES || !$this->sessionActive) {
            return [];
        }

        return Cache::remember(
            "parcours_deliberes_{$this->niveauPACES->id}_{$this->sessionActive->id}",
            300,
            function() {
                $parcoursDeliberes = DB::table('resultats_finaux as rf')
                    ->join('examens as e', 'rf.examen_id', '=', 'e.id')
                    ->where('e.niveau_id', $this->niveauPACES->id)
                    ->where('rf.session_exam_id', $this->sessionActive->id)
                    ->where('rf.statut', ResultatFinal::STATUT_PUBLIE)
                    ->where('rf.is_deliber', true)
                    ->whereNotNull('rf.deliber_at')
                    ->distinct('e.parcours_id')
                    ->pluck('e.parcours_id')
                    ->toArray();

                return array_fill_keys($parcoursDeliberes, true);
            }
        );
    }

    // ========================================
    // 📤 EXPORTS
    // ========================================

    private function getResultatsFiltres(): array
    {
        $resultats = $this->chargerResultatsDepuisDB();
        $resultats = $this->filtrerParDecision($resultats);

        if (!empty($this->recherche)) {
            $resultats = $this->filtrerParRecherche($resultats);
        }

        return $resultats;
    }

    public function exporterExcelPaces()
    {
        try {
            $resultats = $this->getResultatsFiltres();

            if (empty($resultats)) {
                toastr()->warning('Aucun résultat à exporter');
                return;
            }

            $filtreLabel = match($this->filtreDecision) {
                'admis' => 'Admis',
                'redoublant' => 'Redoublants',
                'exclus' => 'Exclus',
                default => 'Tous'
            };

            $filename = sprintf(
                'Resultats_PACES_%s_%s_%s.xlsx',
                $this->parcoursData->abr ?? 'Parcours',
                $filtreLabel,
                now()->format('Y-m-d_His')
            );

            return Excel::download(
                new ResultatsPacesExport(
                    $resultats,
                    $this->uesStructure,
                    $this->filtreDecision,
                    $this->parcoursData->nom ?? ''
                ),
                $filename
            );

        } catch (\Throwable $e) {
            Log::error('Erreur export Excel PACES', ['error' => $e->getMessage()]);
            toastr()->error('Erreur lors de l\'export Excel');
        }
    }


    public function exporterPDF()
    {
        try {
            $resultats = $this->getResultatsFiltres();

            if (empty($resultats)) {
                toastr()->warning('Aucun résultat à exporter');
                return;
            }

            $service = new ResultatsPacesPdfService();
            
            $parcoursNom = $this->parcoursData->nom ?? 'PACES';
            
            // ✅ CORRECTION : Préparer les VRAIES statistiques
            $statistiques = [
                'inscrits' => $this->statistiquesDetailes['total_inscrits'] ?? 0,
                'presents' => $this->statistiquesDetailes['total_presents'] ?? 0,
                'absents' => $this->statistiquesDetailes['total_absents'] ?? 0,
                'total' => $this->statistiquesDetailes['total_presents'] ?? 0,
                'admis' => $this->statistiquesDetailes['admis'] ?? 0,
                'redoublant' => $this->statistiquesDetailes['redoublant_autorises'] ?? 0,
                'exclus' => $this->statistiquesDetailes['exclus'] ?? 0,
            ];
            
            // ✅ CORRECTION : Passer les stats
            $pdf = $service->generer(
                $resultats,
                $this->uesStructure,
                $this->filtreDecision,
                $parcoursNom,
                $statistiques,  // ✅ Vraies stats au lieu de []
                []
            );

            $filtreLabel = match($this->filtreDecision) {
                'admis' => 'Admis',
                'redoublant' => 'Redoublants',
                'exclus' => 'Exclus',
                default => 'Tous'
            };

            $filename = sprintf(
                'Resultats_PACES_%s_%s_%s.pdf',
                $this->parcoursData->abr ?? 'Parcours',
                $filtreLabel,
                now()->format('Y-m-d_His')
            );

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Throwable $e) {
            Log::error('Erreur export PDF PACES', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de l\'export PDF : ' . $e->getMessage());
        }
    }

    /**
     * Surligne le texte recherché
     */
    public function surlignerTexte($texte, $recherche = null)
    {
        if (empty($recherche) || empty($texte)) {
            return e($texte);
        }

        $recherche = trim($recherche);
        if (strlen($recherche) === 0) {
            return e($texte);
        }

        // Échapper le texte et la recherche
        $texteEscape = e($texte);
        $rechercheEscape = preg_quote($recherche, '/');

        // Surligner en préservant la casse
        $resultat = preg_replace(
            '/(' . $rechercheEscape . ')/iu',
            '<mark class="bg-yellow-300 dark:bg-yellow-600 text-gray-900 dark:text-white font-bold px-1 rounded">$1</mark>',
            $texteEscape
        );

        return $resultat;
    }


    private function decisionRank(?string $decision): int
    {
        $d = strtolower((string) $decision);
        return match ($d) {
            'admis'       => 0,
            'redoublant'  => 1,
            'exclus'      => 2,
            default       => 3, // non_definie / autres -> en fin
        };
    }

    public function render()
    {
        return view('livewire.resultats.liste-resultats-paces');
    }
}