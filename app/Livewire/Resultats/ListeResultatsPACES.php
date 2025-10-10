<?php

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
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResultatsPacesExport;
use Illuminate\Support\Facades\Cache;
use App\Services\ResultatsPacesPdfService;
use Illuminate\Pagination\LengthAwarePaginator;

class ListeResultatsPACES extends Component
{
    use WithPagination;
    
    // Navigation avec URL
    public $etape = 'selection';
    public $filtreDecision = 'tous';
    public int $snapshotVersion = 1;          // bump si besoin d'invalider le snapshot
    public $lastSimHash = null;        // hash des paramètres pour éviter les re-simulations inutiles
    public $decisionMap = [];          // [etudiant_id => 'admis'|'redoublant'|'exclus']
    public $simStats = [               // mini tableau de stats à afficher
        'admis' => 0,
        'redoublant' => 0,
        'exclus' => 0,
    ];


    public string $modeSimulation = 'table'; // 'stats' = n'affiche que stats, 'table' = affiche tableau
    public array $simulationDecisionMap = []; // [etudiant_id => 'admis'|'redoublant'|'exclus']
    public array $simulationStats = [];       // ['admis'=>int, 'redoublant'=>int, 'exclus'=>int]

    
    // Sélection
    public $parcoursSelectionne;
    public $parcoursData;
    public $parcoursSlug;
    
    // Configuration
    public $anneeActive;
    public $sessionActive;
    public $niveauPACES;
    public $parcoursPACES;
    public int $resultatsVersion = 0;
    
    // Paramètres simulation
    public $quota_admission = null;
    public $credits_requis = 60;
    public $moyenne_requise = 10.00;
    public $appliquer_note_eliminatoire = true;
    
    // Simulation
    public $simulationEnCours = false; 
    public $simulationResultats = [];
    
    // Résultats
    public $resultatsGroupes = [];
    public $statistiquesDetailes = [];
    public $uesStructure;

    public $derniereDeliberation = null;
    public $valeursModifiees = false;

    public $perPage = 20;
    public $perPageOptions = [10, 20, 50, 100, 150, 200, 300, 500, 'Tous'];
    public $recherche = '';
    public bool $afficherTableau = false;
    
    // ✅ Flag pour éviter rechargements inutiles
    private $skipNextReload = false;
    
    protected $queryString = [
        'parcoursSlug' => ['except' => ''],
        'filtreDecision' => ['except' => 'tous'],
        'perPage' => ['except' => 50],
        'recherche' => ['except' => ''],
    ];


    public function mount()
    {
        $this->initialiserCollections();
        $this->chargerConfigurationActive();
        $this->chargerParcours();

        if ($this->parcoursSlug) {
            $this->restaurerDepuisUrl($this->parcoursSlug);
            $this->etape = 'resultats';
        } else {
            $this->etape = 'selection';
        }
    }



    public function updatedQuotaAdmission()
    {
        $this->verifierModifications();
    }

    public function updatedCreditsRequis()
    {
        $this->verifierModifications();
    }

    public function updatedMoyenneRequise()
    {
        $this->verifierModifications();
    }

    public function updatedAppliquerNoteEliminatoire()
    {
        $this->verifierModifications();
    }

    private function verifierModifications()
    {
        if (!$this->derniereDeliberation) {
            $this->valeursModifiees = false;
            return;
        }

        $this->valeursModifiees = 
            $this->quota_admission != $this->derniereDeliberation->quota_admission ||
            $this->credits_requis != $this->derniereDeliberation->credits_requis ||
            $this->moyenne_requise != $this->derniereDeliberation->moyenne_requise ||
            $this->appliquer_note_eliminatoire != $this->derniereDeliberation->note_eliminatoire;
    }

    public function restaurerDernieresValeurs()
    {
        if ($this->derniereDeliberation) {
            $this->quota_admission = $this->derniereDeliberation->quota_admission;
            $this->credits_requis = $this->derniereDeliberation->credits_requis;
            $this->moyenne_requise = $this->derniereDeliberation->moyenne_requise;
            $this->appliquer_note_eliminatoire = $this->derniereDeliberation->note_eliminatoire;
            $this->valeursModifiees = false;
            toastr()->info('Valeurs restaurées');
        }
    }

    public function updatedPerPage()
    {
        $this->resetPage();
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

    private function restaurerDepuisUrl($slug)
    {
        $parcours = $this->parcoursPACES->firstWhere('abr', $slug);
        
        if ($parcours) {
            Log::info('Restauration depuis URL', ['slug' => $slug, 'parcours' => $parcours->id]);
            
            $this->parcoursSelectionne = $parcours->id;
            $this->parcoursData = $parcours;
            $this->parcoursSlug = $slug;
            $this->quota_admission = $parcours->quota_admission;
            
            $this->etape = 'resultats';
            $this->chargerResultatsParcours();
        } else {
            $this->etape = 'selection';
            $this->parcoursSlug = null;
        }
    }

    private function initialiserCollections()
    {
        $this->parcoursPACES = collect();
        $this->uesStructure = collect();
        $this->resultatsGroupes = ['admis' => [], 'redoublant' => [], 'exclus' => []];
        $this->statistiquesDetailes = [];
        $this->simulationResultats = [];
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


    public function selectionnerParcours($parcoursId)
    {
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
            $this->quota_admission = $this->derniereDeliberation->quota_admission;
            $this->credits_requis = $this->derniereDeliberation->credits_requis;
            $this->moyenne_requise = $this->derniereDeliberation->moyenne_requise;
            $this->appliquer_note_eliminatoire = $this->derniereDeliberation->note_eliminatoire;
            $this->valeursModifiees = false;
            
            // ✅ NOUVEAU : Tableau visible UNIQUEMENT si délibération APPLIQUÉE
            $this->afficherTableau = ($this->derniereDeliberation->applique_at !== null);
        } else {
            $this->quota_admission = $this->parcoursData->quota_admission;
            $this->credits_requis = 60;
            $this->moyenne_requise = 10.00;
            $this->appliquer_note_eliminatoire = true;
            
            // ✅ NOUVEAU : Pas de tableau si aucune délibération
            $this->afficherTableau = false;
        }
        
        $this->etape = 'resultats';
        $this->chargerResultatsParcours();
    }


    public function retourSelection()
    {
        $this->etape = 'selection';
        $this->parcoursSelectionne = null;
        $this->parcoursSlug = null;
        $this->filtreDecision = 'tous';
        $this->simulationEnCours = false;
        $this->afficherTableau = false; // 👈 AJOUTER CETTE LIGNE
        $this->resetResultats();
    }

    // ✅ OPTIMISÉ : Annuler simulation sans rechargement lourd
    public function annulerSimulation()
    {
        if (!$this->simulationEnCours) return;

        // ✅ ULTRA-RAPIDE : Reset sans rechargement
        $this->simulationEnCours = false;
        $this->simulationDecisionMap = [];
        $this->simulationStats = [];
        
        // ✅ RESTAURER les stats originales (déjà en mémoire)
        $this->grouperParDecision(array_merge(
            $this->resultatsGroupes['admis'] ?? [],
            $this->resultatsGroupes['redoublant'] ?? [],
            $this->resultatsGroupes['exclus'] ?? []
        ));
        
        $this->resultatsVersion++; // Force refresh
        
        toastr()->info("Simulation annulée");
    }


    /**
     * Dashboard MEGA : toujours visible après sélection parcours
     */
    public function getAfficherDashboardMegaProperty()
    {
        return $this->etape === 'resultats' 
            && $this->parcoursSelectionne 
            && !empty($this->statistiquesDetailes);
    }


    public function getEstPretProperty()
    {
        return $this->etape === 'resultats' && 
            $this->parcoursSelectionne && 
            !empty($this->resultatsGroupes);
    }
    
    // ✅ OPTIMISÉ : Éviter rechargements inutiles
    private function chargerResultatsParcours()
    {
        if ($this->skipNextReload) {
            $this->skipNextReload = false;
            Log::info('⚡ Rechargement évité (optimisation performance)');
            return;
        }
        
        if ($this->simulationEnCours && !empty($this->simulationResultats)) {
            Log::info('⚡ Rechargement évité (simulation en cours)');
            return;
        }

        $tempsDebut = microtime(true);
        Log::info('🕐 DÉBUT CHARGEMENT RÉSULTATS');

        if (!$this->parcoursSelectionne || !$this->sessionActive) {
            $this->etape = 'selection';
            return;
        }

        try {
            $this->chargerUEStructure();
            $resultats = $this->chargerResultatsOptimises();

            if (empty($resultats)) {
                toastr()->warning('Aucun résultat publié pour ce parcours');
                $this->resetResultats();
                return;
            }

            // ✅ CORRECTION : TOUJOURS recalculer les décisions avec les règles actuelles
            // (même si délibérations existent en DB)
            $resultats = $this->calculerDecisionsInitiales($resultats);

            $this->grouperParDecision($resultats);
            $this->calculerStatistiquesDetailees($resultats);

            $tempsFin = microtime(true);
            $tempsTotal = round(($tempsFin - $tempsDebut) * 1000, 2);
            
            Log::info("✅ CHARGEMENT TERMINÉ en {$tempsTotal}ms");

            if (!$this->simulationEnCours) {
                toastr()->success(count($resultats) . ' résultat(s) chargé(s)');
            }

        } catch (\Exception $e) {
            Log::error("❌ ERREUR: " . $e->getMessage());
            toastr()->error('Erreur: ' . $e->getMessage());
        }
    }


    private function calculerDecisionsInitiales($resultats)
    {
        // Tri par mérite
        usort($resultats, function ($a, $b) {
            $ma = (float)($a['moyenne_generale'] ?? 0);
            $mb = (float)($b['moyenne_generale'] ?? 0);
            if ($mb !== $ma) return $mb <=> $ma;

            $ca = (float)($a['credits_valides'] ?? 0);
            $cb = (float)($b['credits_valides'] ?? 0);
            if ($cb !== $ca) return $cb <=> $ca;

            return (int)$a['etudiant']->matricule <=> (int)$b['etudiant']->matricule;
        });

        $quota    = is_numeric($this->quota_admission) ? (int)$this->quota_admission : null;
        $creditsR = (int)$this->credits_requis;
        $seuilAdm = max(10.0, (float)$this->moyenne_requise);

        // Estimation dépassement quota
        $eligiblesBase = 0;
        foreach ($resultats as $r) {
            if ($this->appliquer_note_eliminatoire && !empty($r['has_note_eliminatoire'])) continue;
            if (($r['credits_valides'] ?? 0) >= $creditsR && ($r['moyenne_generale'] ?? 0) >= $seuilAdm) {
                $eligiblesBase++;
            }
        }
        if (!is_null($quota) && $eligiblesBase > $quota) {
            $seuilAdm = 14.0;
        }

        $seuilRed = ($seuilAdm >= 14.0) ? 10.0 : 9.5;

        // ✅ NOUVEAU : Construire la map des anciens redoublants EN AVANCE
        $anciensRedoublants = $this->getAnciensRedoublantsMap(
            array_column(array_map(fn($r) => $r['etudiant'], $resultats), 'id')
        );

        $admisCount = 0;
        foreach ($resultats as &$r) {
            $etudiant   = $r['etudiant'];
            $matricule  = (int)$etudiant->matricule;
            $etudiantId = (int)$etudiant->id;
            $credits    = (float)($r['credits_valides'] ?? 0);
            $moyenne    = (float)($r['moyenne_generale'] ?? 0);
            $hasZero    = (bool)($r['has_note_eliminatoire'] ?? false);
            $pleinCreds = $credits >= $creditsR;

            // Note éliminatoire => EXCLUS
            if ($this->appliquer_note_eliminatoire && $hasZero) {
                $r['decision'] = 'exclus';
                continue;
            }

            // ✅ CORRECTION : Détection stricte des anciens
            // Matricule ≤ 38999 OU a déjà été redoublant = ancien
            $estAncien = ($matricule <= 38999) || isset($anciensRedoublants[$etudiantId]);

            // ADMIS si seuil atteint et quota disponible
            if ($pleinCreds && $moyenne >= $seuilAdm) {
                if (is_null($quota) || $admisCount < $quota) {
                    $r['decision'] = 'admis';
                    $admisCount++;
                    continue;
                }
            }

            // ✅ RÈGLE STRICTE : Ancien non admis = EXCLUS (jamais redoublant)
            if ($estAncien) {
                $r['decision'] = 'exclus';
                continue;
            }

            // ✅ RÈGLE : Seulement les NOUVEAUX (≥ 39000 ET jamais redoublé) peuvent redoubler
            $peutRedoubler = (!$pleinCreds && !$hasZero && $moyenne >= $seuilRed);
            $r['decision'] = $peutRedoubler ? 'redoublant' : 'exclus';
        }
        unset($r);

        return $resultats;
    }




    // ✅ SIMPLIFIÉ : Simulation ultra-rapide sans loading
    public function simulerDeliberation()
    {
        if (!$this->parcoursSelectionne || !$this->sessionActive || !$this->niveauPACES) {
            toastr()->error("Configuration incomplète");
            return;
        }
        if ($this->quota_admission !== null && $this->quota_admission < 0) {
            toastr()->error('Le quota doit être positif');
            return;
        }

        try {
            $resultats = $this->chargerResultatsOptimises();
            if (empty($resultats)) {
                toastr()->warning('Aucun résultat disponible');
                return;
            }

            // Seuils
            $seuils = $this->calculerSeuilsEffectifs($resultats);
            $seuilAdm = $seuils['admission'];
            $seuilRed = $seuils['redoublement'];

            // Tri
            usort($resultats, function($a,$b){
                if ($b['credits_valides'] !== $a['credits_valides']) 
                    return $b['credits_valides'] <=> $a['credits_valides'];
                if ($b['moyenne_generale'] !== $a['moyenne_generale']) 
                    return $b['moyenne_generale'] <=> $a['moyenne_generale'];
                return ($a['has_note_eliminatoire'] ? 1 : 0) <=> ($b['has_note_eliminatoire'] ? 1 : 0);
            });

            // ✅ NOUVEAU : Construire la map des anciens redoublants EN AVANCE (optimisation)
            $anciensRedoublants = $this->getAnciensRedoublantsMap(
                array_column(array_map(fn($r) => $r['etudiant'], $resultats), 'id')
            );

            $quota = is_numeric($this->quota_admission) ? (int)$this->quota_admission : null;
            $admisCount = 0;
            $map = [];
            $ad = $re = $ex = 0;

            foreach ($resultats as $r) {
                if (empty($r['etudiant']) || empty($r['etudiant']->id)) continue;

                $id = (int)$r['etudiant']->id;
                $matricule = (int)$r['etudiant']->matricule;
                $credits = (int)($r['credits_valides'] ?? 0);
                $moy = (float)($r['moyenne_generale'] ?? 0);
                $elim = (bool)($r['has_note_eliminatoire'] ?? false);
                $plein = $credits >= (int)$this->credits_requis;

                // Élimination
                if ($this->appliquer_note_eliminatoire && $elim) {
                    $map[$id] = 'exclus';
                    $ex++;
                    continue;
                }

                // ✅ CORRECTION : Détection stricte des anciens
                $estAncien = ($matricule <= 38999) || isset($anciensRedoublants[$id]);

                // Admission
                if ($plein && $moy >= $seuilAdm && (is_null($quota) || $admisCount < $quota)) {
                    $map[$id] = 'admis';
                    $ad++;
                    $admisCount++;
                    continue;
                }

                // ✅ RÈGLE STRICTE : Ancien non admis = EXCLUS (jamais redoublant)
                if ($estAncien) {
                    $map[$id] = 'exclus';
                    $ex++;
                    continue;
                }

                // ✅ RÈGLE : Seulement les NOUVEAUX peuvent redoubler
                $peutRedoubler = (!$plein && !$elim && $moy >= $seuilRed);
                if ($peutRedoubler) {
                    $map[$id] = 'redoublant';
                    $re++;
                } else {
                    $map[$id] = 'exclus';
                    $ex++;
                }
            }

            // Enregistrer
            $this->simulationDecisionMap = $map;
            $this->simulationStats = ['admis'=>$ad, 'redoublant'=>$re, 'exclus'=>$ex];
            $this->simulationEnCours = true;

            // MAJ stats affichées
            $this->statistiquesDetailes['admis'] = $ad;
            $this->statistiquesDetailes['redoublant_autorises'] = $re;
            $this->statistiquesDetailes['exclus'] = $ex;
            $this->resultatsVersion++;

            toastr()->success("⚡ Simulé : {$ad} admis • {$re} redoublants • {$ex} exclus");

        } catch (\Throwable $e) {
            $this->simulationEnCours = false;
            $this->simulationDecisionMap = [];
            $this->simulationStats = [];
            toastr()->error('Erreur : '.$e->getMessage());
            \Log::error('Simulation PACES', ['err'=>$e->getMessage()]);
        }
    }



    public function appliquerDeliberation()
    {
        if (!$this->simulationEnCours) {
            toastr()->warning('Veuillez d\'abord simuler la délibération');
            return;
        }

        // ✅ on accepte soit la nouvelle carte (recommandé), soit l'ancien tableau pour compat
        $hasMap = !empty($this->simulationDecisionMap);
        $hasLegacy = !empty($this->simulationResultats);

        if (!$hasMap && !$hasLegacy) {
            toastr()->error('Aucune donnée de simulation à appliquer');
            return;
        }

        try {
            DB::beginTransaction();

            $savedCount = 0;

            if ($hasMap) {
                // --- chemin rapide & fiable : on parcourt la carte ---
                foreach ($this->simulationDecisionMap as $etudiantId => $decision) {
                    $updated = ResultatFinal::whereHas('examen', function($q) {
                            $q->where('niveau_id', $this->niveauPACES->id)
                            ->where('parcours_id', $this->parcoursSelectionne);
                        })
                        ->where('etudiant_id', (int)$etudiantId)
                        ->where('session_exam_id', $this->sessionActive->id)
                        ->update([
                            'decision'       => $decision,
                            'jury_validated' => true,
                            'is_deliber'     => true,
                            'deliber_at'     => now(),
                            'deliber_by'     => Auth::id(),
                            'updated_at'     => now(),
                        ]);

                    if ($updated > 0) $savedCount++;
                }
            } else {
                // --- compat: ancien chemin (moins sûr avec Livewire) ---
                foreach ($this->simulationResultats as $resultat) {
                    if (!isset($resultat['etudiant']->id)) continue;

                    $updated = ResultatFinal::whereHas('examen', function($q) {
                            $q->where('niveau_id', $this->niveauPACES->id)
                            ->where('parcours_id', $this->parcoursSelectionne);
                        })
                        ->where('etudiant_id', $resultat['etudiant']->id)
                        ->where('session_exam_id', $this->sessionActive->id)
                        ->update([
                            'decision'       => $resultat['decision'],
                            'jury_validated' => true,
                            'is_deliber'     => true,
                            'deliber_at'     => now(),
                            'deliber_by'     => Auth::id(),
                            'updated_at'     => now(),
                        ]);

                    if ($updated > 0) $savedCount++;
                }
            }

            // Stats pour trace (privilégier celles de la map)
            $nbAdmis = $hasMap ? ($this->simulationStats['admis'] ?? 0) : count($this->resultatsGroupes['admis'] ?? []);
            $nbRedo  = $hasMap ? ($this->simulationStats['redoublant'] ?? 0) : count($this->resultatsGroupes['redoublant'] ?? []);
            $nbExcl  = $hasMap ? ($this->simulationStats['exclus'] ?? 0) : count($this->resultatsGroupes['exclus'] ?? []);

            $deliberPaces = DeliberPaces::create([
                'niveau_id'         => $this->niveauPACES->id,
                'parcours_id'       => $this->parcoursSelectionne,
                'session_exam_id'   => $this->sessionActive->id,
                'quota_admission'   => $this->quota_admission,
                'credits_requis'    => $this->credits_requis,
                'moyenne_requise'   => $this->moyenne_requise,
                'note_eliminatoire' => $this->appliquer_note_eliminatoire,
                'nb_admis'          => $nbAdmis,
                'nb_redoublants'    => $nbRedo,
                'nb_exclus'         => $nbExcl,
                'applique_par'      => Auth::id(),
                'applique_at'       => now(),
            ]);

            DB::commit();

            \Log::info('✅ Délibération PACES appliquée', [
                'deliber_id'       => $deliberPaces->id,
                'etudiants_updated'=> $savedCount
            ]);

            // Reset & rechargement propre
            $this->simulationEnCours = false;
            $this->modeSimulation = 'table'; // réafficher tableau
            $this->simulationDecisionMap = [];
            $this->simulationStats = [];
            $this->simulationResultats = []; // legacy
            $this->skipNextReload = false;

            // recharger l'état DB réel
            $this->chargerResultatsParcours();
            $this->resultatsVersion++;
            $this->afficherTableau = true;
            toastr()->success("Délibération appliquée : {$savedCount} étudiant(s) mis à jour");

        } catch (\Throwable $e) {
            DB::rollBack();
            toastr()->error('Erreur : '.$e->getMessage());
            \Log::error('Erreur application délibération', ['error' => $e->getMessage()]);
        }
    }



    /**
     * Récupère en UNE SEULE requête la map des étudiants ayant déjà été "redoublant"
     * @param array $etudiantIds Liste des IDs étudiants à vérifier
     * @return array Map [etudiant_id => true] des anciens redoublants
     */
    private function getAnciensRedoublantsMap(array $etudiantIds): array
    {
        if (empty($etudiantIds)) {
            return [];
        }

        $anneeCouranteId = optional($this->anneeActive)->id;

        try {
            // ✅ UNE SEULE requête pour TOUS les étudiants
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

            // Retourner une map pour accès O(1)
            return array_fill_keys($anciens, true);

        } catch (\Throwable $e) {
            \Log::warning('getAnciensRedoublantsMap(): fallback empty', ['err' => $e->getMessage()]);
            return [];
        }
    }

    public function getStatistiquesDeliberationProperty()
    {
        if (!$this->sessionActive || !$this->parcoursSelectionne) {
            return null;
        }

        $stats = ResultatFinal::whereHas('examen', function($q) {
                $q->where('niveau_id', $this->niveauPACES->id)
                ->where('parcours_id', $this->parcoursSelectionne);
            })
            ->where('session_exam_id', $this->sessionActive->id)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->selectRaw('
                COUNT(DISTINCT etudiant_id) as total_etudiants,
                COUNT(DISTINCT CASE WHEN is_deliber = 1 THEN etudiant_id END) as etudiants_deliberes,
                MAX(deliber_at) as derniere_deliberation
            ')
            ->first();

        return $stats;
    }

    public function getHasDeliberationsProperty()
    {
        $stats = $this->statistiques_deliberation;
        return $stats && $stats->etudiants_deliberes > 0;
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

    private function chargerResultatsOptimises()
    {
        $cacheKey = "resultats_paces_{$this->parcoursSelectionne}_{$this->sessionActive->id}";
        
        $resultatsIds = Cache::remember($cacheKey . '_ids', 300, function() {
            return DB::table('resultats_finaux')
                ->join('examens', 'resultats_finaux.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $this->niveauPACES->id)
                ->where('examens.parcours_id', $this->parcoursSelectionne)
                ->where('resultats_finaux.session_exam_id', $this->sessionActive->id)
                ->where('resultats_finaux.statut', ResultatFinal::STATUT_PUBLIE)
                ->pluck('resultats_finaux.id')
                ->toArray();
        });

        if (empty($resultatsIds)) return [];

        $resultats = ResultatFinal::whereIn('id', $resultatsIds)
            ->select([
                'id',
                'etudiant_id',
                'ec_id',
                'examen_id',
                'note',
                'decision',
                'is_deliber',
                'deliber_at'
            ])
            ->with([
                'etudiant:id,nom,prenom,matricule',
                'ec:id,nom,abr,ue_id,coefficient',
                'ec.ue:id,nom,abr,credits'
            ])
            ->get();

        if ($resultats->isEmpty()) return [];

        return $this->traiterResultatsEnMemoire($resultats);
    }



    private function traiterResultatsEnMemoire($resultats)
    {
        $resultatsGroupes = $resultats->groupBy('etudiant_id');
        $resultatsFinaux = [];

        foreach ($resultatsGroupes as $etudiantId => $notesEtudiant) {
            $etudiant = $notesEtudiant->first()->etudiant;
            if (!$etudiant) continue;

            $calculs = $this->calculerResultatsEtudiantRapide($notesEtudiant);
            $matricule = (int) $etudiant->matricule;
            $aDejaRedouble = $this->aDejaRedoubleUneFois($etudiant->id);

            $resultatsFinaux[] = [
                'etudiant' => $etudiant,
                'notes' => $notesEtudiant->keyBy('ec_id'),
                'resultats_ue' => $calculs['resultats_ue'],
                'moyenne_generale' => $calculs['moyenne_generale'],
                'credits_valides' => $calculs['credits_valides'],
                'total_credits' => $calculs['total_credits'],
                'has_note_eliminatoire' => $calculs['has_note_eliminatoire'],

                'decision' => $notesEtudiant->first()->decision ?? 'non_definie',
                'is_deliber' => $notesEtudiant->first()->is_deliber ?? false,
                'deliber_at' => $notesEtudiant->first()->deliber_at,

                'est_redoublant' => (intval($etudiant->matricule) <= 38999) || $aDejaRedouble,
                'est_passant'    => (intval($etudiant->matricule) > 38999) && !$aDejaRedouble,


                'a_participe' => true,
            ];
        }

        usort($resultatsFinaux, [$this, 'comparerParMerite']);

        return $resultatsFinaux;
    }



    private function calculerResultatsEtudiantRapide($notesEtudiant)
    {
        $resultatsParUE = $notesEtudiant->groupBy('ec.ue_id');
        $resultatsUE = [];
        $creditsValides = 0;
        $totalCredits = 0;
        $moyennesUE = [];
        $hasNoteEliminatoire = false;

        foreach ($resultatsParUE as $ueId => $notesUE) {
            $ue = $notesUE->first()->ec->ue;
            $totalCredits += $ue->credits;

            $somme = 0;
            $count = 0;
            $hasZeroInUE = false;

            foreach ($notesUE as $note) {
                $somme += $note->note;
                $count++;
                if ($note->note == 0) {
                    $hasZeroInUE = true;
                    $hasNoteEliminatoire = true;
                }
            }

            $moyenneUE = $count > 0 ? round($somme / $count, 2) : 0;
            $moyennesUE[] = $moyenneUE;
            $ueValidee = ($moyenneUE >= 10) && !$hasZeroInUE;

            if ($ueValidee) $creditsValides += $ue->credits;

            $resultatsUE[] = [
                'ue_id' => $ueId,
                'ue_nom' => $ue->nom,
                'moyenne_ue' => $moyenneUE,
                'ue_validee' => $ueValidee,
                'has_note_eliminatoire' => $hasZeroInUE
            ];
        }

        $moyenneGenerale = !empty($moyennesUE) 
            ? round(array_sum($moyennesUE) / count($moyennesUE), 2) 
            : 0;

        return [
            'resultats_ue' => $resultatsUE,
            'moyenne_generale' => $moyenneGenerale,
            'credits_valides' => $creditsValides,
            'total_credits' => $totalCredits,
            'has_note_eliminatoire' => $hasNoteEliminatoire
        ];
    }

    private function comparerParMerite($a, $b)
    {
        $ordreDecisions = [
            'admis' => 1,
            'redoublant' => 2,
            'exclus' => 3,
            'non_definie' => 4
        ];
        
        $decisionA = $ordreDecisions[$a['decision']] ?? 4;
        $decisionB = $ordreDecisions[$b['decision']] ?? 4;
        
        if ($decisionA !== $decisionB) {
            return $decisionA <=> $decisionB;
        }
        
        $elimA = $a['has_note_eliminatoire'] ? 1 : 0;
        $elimB = $b['has_note_eliminatoire'] ? 1 : 0;
        
        if ($elimA !== $elimB) {
            return $elimA <=> $elimB;
        }
        
        if ($b['credits_valides'] !== $a['credits_valides']) {
            return $b['credits_valides'] <=> $a['credits_valides'];
        }
        
        if ($b['moyenne_generale'] !== $a['moyenne_generale']) {
            return $b['moyenne_generale'] <=> $a['moyenne_generale'];
        }
        
        return $a['etudiant']->matricule <=> $b['etudiant']->matricule;
    }


    private function appliquerSimulationAvecQuota($resultats)
    {
        // Tri par mérite
        usort($resultats, function($a, $b) {
            if ($b['credits_valides'] !== $a['credits_valides']) {
                return $b['credits_valides'] <=> $a['credits_valides'];
            }
            if ($b['moyenne_generale'] !== $a['moyenne_generale']) {
                return $b['moyenne_generale'] <=> $a['moyenne_generale'];
            }
            return ($a['has_note_eliminatoire'] ? 1 : 0) <=> ($b['has_note_eliminatoire'] ? 1 : 0);
        });

        $quota = is_numeric($this->quota_admission) ? (int)$this->quota_admission : null;
        $seuilAdmissionEffectif = max(10.0, (float)$this->moyenne_requise);

        // Vérifier dépassement quota
        $admisEligiblesIdx = [];
        foreach ($resultats as $i => $r) {
            if ($this->appliquer_note_eliminatoire && $r['has_note_eliminatoire']) continue;
            if ($r['credits_valides'] >= $this->credits_requis && $r['moyenne_generale'] >= $seuilAdmissionEffectif) {
                $admisEligiblesIdx[] = $i;
            }
        }
        if (!is_null($quota) && count($admisEligiblesIdx) > $quota) {
            $seuilAdmissionEffectif = 14.0;
        }
        $seuilRedoublement = ($seuilAdmissionEffectif >= 14.0) ? 10.0 : 9.5;

        // ✅ NOUVEAU : Construire la map des anciens redoublants
        $anciensRedoublants = $this->getAnciensRedoublantsMap(
            array_column(array_map(fn($r) => $r['etudiant'], $resultats), 'id')
        );

        $admisCount = 0;
        foreach ($resultats as &$r) {
            $etudiant = $r['etudiant'] ?? null;
            if (!$etudiant) {
                $r['decision'] = 'exclus';
                $r['decision_simulee'] = true;
                continue;
            }

            $matricule = (int)$etudiant->matricule;
            $etudiantId = (int)$etudiant->id;
            $credits   = (float)$r['credits_valides'];
            $moyenne   = (float)$r['moyenne_generale'];
            $has0      = (bool)$r['has_note_eliminatoire'];
            $plein     = $credits >= (int)$this->credits_requis;

            // Note éliminatoire => EXCLUS
            if ($this->appliquer_note_eliminatoire && $has0) {
                $r['decision'] = 'exclus';
                $r['decision_simulee'] = true;
                continue;
            }

            // ✅ CORRECTION : Détection stricte des anciens
            $estAncien = ($matricule <= 38999) || isset($anciensRedoublants[$etudiantId]);

            // ADMIS si conditions + quota
            if ($plein && $moyenne >= $seuilAdmissionEffectif) {
                if (is_null($quota) || $admisCount < $quota) {
                    $r['decision'] = 'admis';
                    $r['decision_simulee'] = true;
                    $admisCount++;
                    continue;
                }
            }

            // ✅ RÈGLE STRICTE : Ancien non admis = EXCLUS (jamais redoublant)
            if ($estAncien) {
                $r['decision'] = 'exclus';
                $r['decision_simulee'] = true;
                continue;
            }

            // ✅ RÈGLE : Seulement les NOUVEAUX peuvent redoubler
            $peutRedoubler = (!$plein && !$has0 && $moyenne >= $seuilRedoublement);
            $r['decision'] = $peutRedoubler ? 'redoublant' : 'exclus';
            $r['decision_simulee'] = true;
        }
        unset($r);

        // Tri final
        usort($resultats, [$this, 'comparerParMerite']);

        return $resultats;
    }



    /**
     * Construit une carte légère des décisions et les stats à partir d'un tableau $resultats.
     * @return array{0: array<int,string>, 1: array{admis:int, redoublant:int, exclus:int}}
     */
    private function buildDecisionMapAndStats(array $resultats): array
    {
        $map = [];
        $ad = 0;
        $re = 0;
        $ex = 0;

        foreach ($resultats as $r) {
            // sécurité: vérifier l'objet étudiant et son id
            if (empty($r['etudiant']) || !isset($r['etudiant']->id)) {
                continue;
            }

            // normaliser la décision
            $d = $r['decision'] ?? 'exclus';
            if (!in_array($d, ['admis', 'redoublant', 'exclus'], true)) {
                $d = 'exclus';
            }

            $map[(int) $r['etudiant']->id] = $d;

            if ($d === 'admis') {
                $ad++;
            } elseif ($d === 'redoublant') {
                $re++;
            } else {
                $ex++;
            }
        }

        // ✅ retourner la carte et les stats
        return [
            $map,
            [
                'admis'      => $ad,
                'redoublant' => $re,
                'exclus'     => $ex,
            ],
        ];
    }

    


    private function grouperParDecision($resultats)
    {
        $this->resultatsGroupes = ['admis' => [], 'redoublant' => [], 'exclus' => []];

        foreach ($resultats as $resultat) {
            $decision = $resultat['decision'];
            if (isset($this->resultatsGroupes[$decision])) {
                $this->resultatsGroupes[$decision][] = $resultat;
            }
        }

        // 🔁 BUMP : force Livewire à considérer un nouvel état
        $this->resultatsVersion++;
    }


    private function calculerStatistiquesDetailees($resultats)
    {
        $statsPresence = $this->obtenirStatistiquesPresence();
        
        $totalInscrits = $statsPresence['total_inscrits'];
        $totalPresents = $statsPresence['presents'];
        $totalAbsents = $statsPresence['absents'];

        // ✅ Calcul direct sans collect()
        $redoublants = 0;
        foreach ($resultats as $r) {
            if ($r['est_redoublant']) $redoublants++;
        }
        $nouveaux = $totalPresents - $redoublants;

        // ✅ Comptage direct depuis les groupes déjà créés
        $nbAdmis = count($this->resultatsGroupes['admis']);
        $nbRedoublant = count($this->resultatsGroupes['redoublant']);
        $nbExclus = count($this->resultatsGroupes['exclus']);

        $this->statistiquesDetailes = [
            'total_inscrits' => $totalInscrits,
            'total_presents' => $totalPresents,
            'total_absents' => $totalAbsents,
            'admis' => $nbAdmis,
            'redoublant_autorises' => $nbRedoublant,
            'exclus' => $nbExclus,
            'etudiants_redoublants' => $redoublants,
            'etudiants_nouveaux' => $nouveaux,
            'taux_reussite' => $totalPresents > 0 ? round(($nbAdmis / $totalPresents) * 100, 1) : 0,
            'taux_presence' => $totalInscrits > 0 ? round(($totalPresents / $totalInscrits) * 100, 1) : 0
        ];
    }


    private function obtenirStatistiquesPresence()
    {
        // ✅ Cache pour éviter de recalculer à chaque simulation
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

        $examen = Examen::where('niveau_id', $this->niveauPACES->id)
            ->where('parcours_id', $this->parcoursSelectionne)
            ->first();

        if (!$examen) {
            $totalInscrits = Etudiant::where('niveau_id', $this->niveauPACES->id)
                ->where('parcours_id', $this->parcoursSelectionne)
                ->where('is_active', true)
                ->count();

            return $statsCache = [
                'total_inscrits' => $totalInscrits,
                'presents' => 0,
                'absents' => $totalInscrits
            ];
        }

        $presences = PresenceExamen::where('examen_id', $examen->id)
            ->where('session_exam_id', $this->sessionActive->id)
            ->get();

        if ($presences->isEmpty()) {
            $totalInscrits = Etudiant::where('niveau_id', $this->niveauPACES->id)
                ->where('parcours_id', $this->parcoursSelectionne)
                ->where('is_active', true)
                ->count();

            return $statsCache = [
                'total_inscrits' => $totalInscrits,
                'presents' => 0,
                'absents' => $totalInscrits
            ];
        }

        $presenceReference = $presences->sortByDesc('total_attendu')->first();
        $totalInscrits = $presenceReference->total_attendu ?? 0;
        $presents = $presences->max('etudiants_presents');
        $absents = max(0, $totalInscrits - $presents);

        return $statsCache = [
            'total_inscrits' => $totalInscrits,
            'presents' => $presents,
            'absents' => $absents
        ];
    }
    
    private function resetResultats()
    {
        $this->resultatsGroupes = ['admis' => [], 'redoublant' => [], 'exclus' => []];
        $this->statistiquesDetailes = [];
        $this->simulationResultats = [];
        $this->resultatsVersion++; // 🔁
    }

    // ✅ OPTIMISÉ : Source depuis simulation si active
    public function getResultatsPaginesProperty()
    {
        // ✅ CRITICAL : Éviter les calculs si simulation en cours sans changement
        static $lastHash = null;
        static $lastResult = null;
        
        $currentHash = md5(serialize([
            $this->resultatsVersion,
            $this->simulationEnCours,
            $this->filtreDecision,
            $this->recherche,
            $this->perPage,
            $this->getPage(),
            count($this->resultatsGroupes['admis']),
            count($this->resultatsGroupes['redoublant']),
            count($this->resultatsGroupes['exclus'])
        ]));
        
        if ($lastHash === $currentHash && $lastResult !== null) {
            return $lastResult;
        }

        // ✅ Source optimisée
        if ($this->filtreDecision === 'tous') {
            $resultatsAffiches = array_merge(
                $this->resultatsGroupes['admis'] ?? [],
                $this->resultatsGroupes['redoublant'] ?? [],
                $this->resultatsGroupes['exclus'] ?? []
            );
            usort($resultatsAffiches, [$this, 'comparerParMerite']);
        } else {
            $resultatsAffiches = $this->resultatsGroupes[$this->filtreDecision] ?? [];
        }

        // Recherche
        if (!empty($this->recherche)) {
            $resultatsAffiches = $this->filtrerParRecherche($resultatsAffiches);
        }

        $collection = collect($resultatsAffiches);

        if ($this->perPage === 'Tous') {
            $result = new LengthAwarePaginator(
                $collection,
                $collection->count(),
                $collection->count(),
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        } else {
            $page = $this->getPage();
            $perPage = (int) $this->perPage;
            
            $result = new LengthAwarePaginator(
                $collection->forPage($page, $perPage),
                $collection->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }

        $lastHash = $currentHash;
        $lastResult = $result;
        
        return $result;
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

            $nomComplet = $nom . ' ' . $prenom;
            if (str_contains($nomComplet, $terme)) {
                return true;
            }

            $prenomNom = $prenom . ' ' . $nom;
            if (str_contains($prenomNom, $terme)) {
                return true;
            }

            return false;
        });
    }

    public function reinitialiserRecherche()
    {
        $this->recherche = '';
        $this->resetPage();
    }

    public function surlignerTexte($texte, $recherche)
    {
        if (empty($recherche)) {
            return e($texte);
        }

        $pattern = '/' . preg_quote($recherche, '/') . '/iu';
        return preg_replace(
            $pattern,
            '<mark class="bg-yellow-200 dark:bg-yellow-600 text-gray-900 dark:text-gray-100 px-1 rounded">$0</mark>',
            e($texte)
        );
    }



    // --- AJOUTS CONSERVÉS (pas indispensables pour la règle “no triple”) ---
    private function calculerSeuilsEffectifs(array $resultats): array
    {
        $candidatsBase = array_filter($resultats, function ($r) {
            return !$r['has_note_eliminatoire']
                && $r['credits_valides'] >= $this->credits_requis
                && $r['moyenne_generale'] >= max(10, (float)$this->moyenne_requise);
        });

        $seuilAdmission = max(10, (float)$this->moyenne_requise);

        if (!empty($this->quota_admission) && count($candidatsBase) > (int)$this->quota_admission) {
            $seuilAdmission = max($seuilAdmission, 14);
        }

        $seuilRedoublement = ($seuilAdmission >= 14) ? 10.0 : 9.5;

        return [
            'admission'    => $seuilAdmission,
            'redoublement' => $seuilRedoublement,
        ];
    }

    private function estDoubleRedoublant(int $etudiantId): bool
    {
        try {
            return \App\Models\ResultatFinal::where('etudiant_id', $etudiantId)
                ->where('decision', \App\Models\ResultatFinal::DECISION_REDOUBLANT)
                ->whereHas('sessionExam', function ($q) {
                    $q->where('annee_universitaire_id', '!=', optional($this->anneeActive)->id);
                })
                ->distinct('session_exam_id')
                ->count() >= 2;
        } catch (\Throwable $e) {
            \Log::warning('estDoubleRedoublant() fallback false', ['err' => $e->getMessage()]);
            return false;
        }
    }

    private function appliquerQuotaParMerite(array $admis): array
    {
        if (empty($this->quota_admission)) return $admis;

        usort($admis, function ($a, $b) {
            if ($b['moyenne_generale'] !== $a['moyenne_generale']) {
                return $b['moyenne_generale'] <=> $a['moyenne_generale'];
            }
            if ($b['credits_valides'] !== $a['credits_valides']) {
                return $b['credits_valides'] <=> $a['credits_valides'];
            }
            return $a['etudiant']->matricule <=> $b['etudiant']->matricule;
        });

        return array_slice($admis, 0, (int)$this->quota_admission);
    }

    private function aDejaRedoubleUneFois(int $etudiantId): bool
    {
        try {
            $anneeCouranteId = optional($this->anneeActive)->id;

            $nbAnneesRedoublant = \App\Models\ResultatFinal::where('etudiant_id', $etudiantId)
                ->where('decision', \App\Models\ResultatFinal::DECISION_REDOUBLANT)
                ->where('statut', \App\Models\ResultatFinal::STATUT_PUBLIE)
                ->whereHas('examen', function ($q) {
                    $q->where('niveau_id', $this->niveauPACES->id)
                      ->where('parcours_id', $this->parcoursSelectionne);
                })
                ->whereHas('sessionExam', function ($q) use ($anneeCouranteId) {
                    $q->where('annee_universitaire_id', '!=', $anneeCouranteId);
                })
                ->join('session_exams as se', 'resultats_finaux.session_exam_id', '=', 'se.id')
                ->distinct('se.annee_universitaire_id')
                ->count('se.annee_universitaire_id');

            return $nbAnneesRedoublant >= 1;
        } catch (\Throwable $e) {
            \Log::warning('aDejaRedoubleUneFois(): fallback false', ['err' => $e->getMessage()]);
            return false;
        }
    }


    /**
     * Construit (ou lit) un snapshot compact des étudiants pour simulation rapide.
     * Source: mémoire actuelle si dispo, sinon DB -> compactage, puis cache.
     */
    private function getSnapshotEtudiants(): array
    {
        if (!$this->parcoursSelectionne || !$this->sessionActive || !$this->niveauPACES) {
            return [];
        }

        $cacheKey = "paces_snapshot_{$this->niveauPACES->id}_{$this->parcoursSelectionne}_{$this->sessionActive->id}_v{$this->snapshotVersion}";

        return Cache::remember($cacheKey, 900, function () {
            // 1) Si on a déjà des résultats en mémoire (chargés une fois), on compacte directement (plus rapide)
            $tous = array_merge(
                $this->resultatsGroupes['admis'] ?? [],
                $this->resultatsGroupes['redoublant'] ?? [],
                $this->resultatsGroupes['exclus'] ?? []
            );

            if (!empty($tous)) {
                return array_map(function ($r) {
                    return [
                        'eid'        => $r['etudiant']->id,
                        'credits'    => (float)($r['credits_valides'] ?? 0),
                        'moy'        => (float)($r['moyenne_generale'] ?? 0),
                        'has0'       => (bool)($r['has_note_eliminatoire'] ?? false),
                        // “ancien redoublant” = pas de triple : true si aDejaRedoubleUneFois
                        'ancien_red' => (bool)$this->aDejaRedoubleUneFois($r['etudiant']->id),
                        // Optim : on ne retient pas est_passant, on déduit: !$ancien_red
                    ];
                }, $tous);
            }

            // 2) Sinon: on charge compact depuis DB (sélection minimale), puis on recalcule rapidement en PHP
            $resultatsIds = DB::table('resultats_finaux')
                ->join('examens', 'resultats_finaux.examen_id', '=', 'examens.id')
                ->where('examens.niveau_id', $this->niveauPACES->id)
                ->where('examens.parcours_id', $this->parcoursSelectionne)
                ->where('resultats_finaux.session_exam_id', $this->sessionActive->id)
                ->where('resultats_finaux.statut', ResultatFinal::STATUT_PUBLIE)
                ->pluck('resultats_finaux.id')
                ->toArray();

            if (empty($resultatsIds)) return [];

            // On charge le strict minimum (sans with())
            $resultats = ResultatFinal::whereIn('id', $resultatsIds)
                ->select(['etudiant_id','ec_id','note'])
                ->with(['ec:id,ue_id', 'ec.ue:id,credits']) // petite jointure — raisonnable
                ->get()
                ->groupBy('etudiant_id');

            $snapshot = [];

            foreach ($resultats as $etudiantId => $notesEtudiant) {
                // Reprend la logique du calcul rapide UE -> crédits, moyenne générale, has0
                $calc = $this->calculerResultatsEtudiantRapide($notesEtudiant);

                $snapshot[] = [
                    'eid'        => (int)$etudiantId,
                    'credits'    => (float)$calc['credits_valides'],
                    'moy'        => (float)$calc['moyenne_generale'],
                    'has0'       => (bool)$calc['has_note_eliminatoire'],
                    'ancien_red' => (bool)$this->aDejaRedoubleUneFois((int)$etudiantId),
                ];
            }

            return $snapshot;
        });
    }

    /**
     * Simulation ULTRA-LÉGÈRE : ne rend pas le tableau, calcule juste les compteurs.
     * Ne trie pas, ne compose pas les groupes; seulement du counting.
     */
    private function simulerCountsDepuisSnapshot(array $snap): array
    {
        $quota = is_numeric($this->quota_admission) ? (int)$this->quota_admission : null;
        $creditsRequis = (int)$this->credits_requis;
        $seuilBase = max(10.0, (float)$this->moyenne_requise);

        // 1) Estimer le relèvement du seuil (10 -> 14) si quota dépassé
        $eligibles10 = 0;
        foreach ($snap as $s) {
            if ($this->appliquer_note_eliminatoire && $s['has0']) continue;
            if ($s['credits'] >= $creditsRequis && $s['moy'] >= $seuilBase) {
                $eligibles10++;
            }
        }

        $seuilAdmission = $seuilBase;
        if (!is_null($quota) && $eligibles10 > $quota) {
            $seuilAdmission = max($seuilAdmission, 14.0);
        }

        // Redoublement : 9.5 si seuilAdmission=10 ; sinon 10.0
        $seuilRedoublement = ($seuilAdmission >= 14.0) ? 10.0 : 9.5;

        // 2) Comptage
        $admis = 0; $redoublant = 0; $exclus = 0;

        // si seuil=14 et quota fixé, il est possible que les admissibles dépassent quand même le quota.
        // Pour compter, on borne à quota si dépasse (pas besoin de trier).
        $admissiblesSeuil = 0;

        foreach ($snap as $s) {
            $has0 = $s['has0'];
            $plein = $s['credits'] >= $creditsRequis;
            $moy   = $s['moy'];
            $ancien = $s['ancien_red']; // pas de triple

            if ($this->appliquer_note_eliminatoire && $has0) {
                $exclus++; 
                continue;
            }

            // Admissible (avant quota)
            if ($plein && $moy >= $seuilAdmission) {
                $admissiblesSeuil++;
                continue; // on finalisera après quota
            }

            // Sinon, redoublement possible UNIQUEMENT si pas ancien redoublant
            if (!$plein && !$has0 && $moy >= $seuilRedoublement && !$ancien) {
                $redoublant++;
            } else {
                // ancien redoublant non admis => exclus, ou niveau sous le seuil => exclus
                $exclus++;
            }
        }

        // Application du quota sur les admissibles
        if (is_null($quota)) {
            $admis = $admissiblesSeuil;
        } else {
            $admis = min($admissiblesSeuil, $quota);

        }

        return [
            'admis'       => $admis,
            'redoublant'  => $redoublant,
            'exclus'      => $exclus,
            'seuil'       => $seuilAdmission,
            'seuil_red'   => $seuilRedoublement,
            'eligibles10' => $eligibles10,
            'quota'       => $quota,
        ];
    }



    /**
     * ✅ Récupère les résultats filtrés (simulation ou délibéré)
     */
    private function getResultatsFiltres(): array
    {
        // Source : simulation active OU données délibérées
        if ($this->filtreDecision === 'tous') {
            $resultats = array_merge(
                $this->resultatsGroupes['admis'] ?? [],
                $this->resultatsGroupes['redoublant'] ?? [],
                $this->resultatsGroupes['exclus'] ?? []
            );
            usort($resultats, [$this, 'comparerParMerite']);
        } else {
            $resultats = $this->resultatsGroupes[$this->filtreDecision] ?? [];
        }

        // Appliquer la recherche si active
        if (!empty($this->recherche)) {
            $resultats = $this->filtrerParRecherche($resultats);
        }

        return $resultats;
    }

    /**
     * 📥 Export Excel
     */
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
            \Log::error('Erreur export Excel PACES', ['error' => $e->getMessage()]);
            toastr()->error('Erreur lors de l\'export Excel');
        }
    }




    /**
     * 📄 Export PDF
     */
    public function exporterPDF()
    {
        try {
            $resultats = $this->getResultatsFiltres();

            if (empty($resultats)) {
                toastr()->warning('Aucun résultat à exporter');
                return;
            }

            $service = new ResultatsPacesPdfService();
            
            // ✅ CORRECTION : Nettoyer le nom du parcours (enlever "PACES" du début)
            $parcoursNom = $this->parcoursData->nom ?? 'PACES';
            $parcoursNom = preg_replace('/^PACES\s*/i', '', $parcoursNom); // Enlève "PACES " du début
            
            $pdf = $service->generer(
                $resultats,
                $this->uesStructure,
                $parcoursNom, // ✅ Maintenant sans "PACES"
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
            \Log::error('Erreur export PDF PACES', ['error' => $e->getMessage()]);
            toastr()->error('Erreur lors de l\'export PDF');
        }
    }


    public function render()
    {
        return view('livewire.resultats.liste-resultats-paces');
    }
}
