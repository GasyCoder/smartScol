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
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class ListeResultatsPACES extends Component
{
    use WithPagination;
    
    // Navigation avec URL
    public $etape = 'selection';
    public $filtreDecision = 'tous';
    
    // Sélection
    public $parcoursSelectionne;
    public $parcoursData;
    public $parcoursSlug;
    
    // Configuration
    public $anneeActive;
    public $sessionActive;
    public $niveauPACES;
    public $parcoursPACES;
    
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
    
    // ✅ Flag pour éviter rechargements inutiles
    private $skipNextReload = false;
    
    protected $queryString = [
        'parcoursSlug' => ['except' => ''],
        'filtreDecision' => ['except' => 'tous'],
        'perPage' => ['except' => 50], 
        'recherche' => ['except' => ''],
    ];

    public function mount($parcoursSlug = null)
    {
        $this->initialiserCollections();
        $this->chargerConfigurationActive();
        $this->chargerParcours();
        
        if ($parcoursSlug) {
            $this->restaurerDepuisUrl($parcoursSlug);
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
        } else {
            $this->quota_admission = $this->parcoursData->quota_admission;
            $this->credits_requis = 60;
            $this->moyenne_requise = 10.00;
            $this->appliquer_note_eliminatoire = true;
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
        $this->resetResultats();
    }

    // ✅ OPTIMISÉ : Annuler simulation sans rechargement lourd
    public function annulerSimulation()
    {
        if (!$this->simulationEnCours) {
            return;
        }

        Log::info('🔙 Annulation simulation');

        $this->simulationEnCours = false;
        $this->simulationResultats = [];
        $this->skipNextReload = false;
        
        // Recharger l'état original depuis DB
        $this->chargerResultatsParcours();
        
        toastr()->info('Simulation annulée - Retour à l\'état original');
    }

    public function getEstPretProperty()
    {
        return $this->etape === 'resultats' && 
            $this->parcoursSelectionne && 
            !empty($this->resultatsGroupes);
    }
    
    // ✅ OPTIMISÉ : Éviter rechargements inutiles
    public function chargerResultatsParcours()
    {
        // ✅ CRITICAL : Ne PAS recharger si on vient de simuler
        if ($this->skipNextReload) {
            $this->skipNextReload = false;
            Log::info('⚡ Rechargement évité (optimisation performance)');
            return;
        }
        
        // ✅ Si simulation active, ne pas recharger
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

            $hasDeliberations = collect($resultats)->contains('is_deliber', true);

            if (!$hasDeliberations) {
                $resultats = $this->calculerDecisionsInitiales($resultats);
            }

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
        usort($resultats, function($a, $b) {
            if ($b['moyenne_generale'] === $a['moyenne_generale']) {
                return $b['credits_valides'] <=> $a['credits_valides'];
            }
            return $b['moyenne_generale'] <=> $a['moyenne_generale'];
        });

        $admisCount = 0;

        foreach ($resultats as &$resultat) {
            $credits = $resultat['credits_valides'];
            $moyenne = $resultat['moyenne_generale'];
            $hasNoteElim = $resultat['has_note_eliminatoire'];

            if ($this->appliquer_note_eliminatoire && $hasNoteElim) {
                $resultat['decision'] = 'exclus';
                continue;
            }

            if ($credits < 30 || $moyenne < 8) {
                $resultat['decision'] = 'exclus';
                continue;
            }

            $eligibleAdmission = $credits >= $this->credits_requis && 
                                $moyenne >= $this->moyenne_requise;

            if ($eligibleAdmission) {
                if ($this->quota_admission === null || 
                    $this->quota_admission === '' || 
                    $admisCount < $this->quota_admission) {
                    $resultat['decision'] = 'admis';
                    $admisCount++;
                } else {
                    $resultat['decision'] = 'redoublant';
                }
            } else {
                $resultat['decision'] = 'redoublant';
            }
        }

        return $resultats;
    }

    // ✅ SIMPLIFIÉ : Simulation ultra-rapide sans loading
    public function simulerDeliberation()
    {   
        // ✅ CRITICAL : Timeout de sécurité
        set_time_limit(30); // Maximum 30 secondes
        ini_set('memory_limit', '512M'); // Augmenter la mémoire si nécessaire
        
        if ($this->quota_admission < 0) {
            toastr()->error('Le quota doit être positif');
            return;
        }

        try {
            $tempsDebut = microtime(true);

            // ✅ CRITICAL : Vérifier si déjà en simulation
            if ($this->simulationEnCours && !empty($this->simulationResultats)) {
                toastr()->info('Simulation déjà en cours');
                return;
            }

            // Récupération depuis MÉMOIRE
            $resultats = array_merge(
                $this->resultatsGroupes['admis'] ?? [],
                $this->resultatsGroupes['redoublant'] ?? [],
                $this->resultatsGroupes['exclus'] ?? []
            );

            if (empty($resultats)) {
                $this->chargerUEStructure();
                $resultats = $this->chargerResultatsOptimises();
                
                if (empty($resultats)) {
                    toastr()->warning('Aucun résultat disponible');
                    return;
                }
            }

            // Application simulation
            $this->simulationEnCours = true;
            $resultats = $this->appliquerSimulationAvecQuota($resultats);
            $this->simulationResultats = $resultats;

            // Regroupement
            $this->grouperParDecision($resultats);
            
            // Statistiques
            $this->calculerStatistiquesDetailees($resultats);

            // Filtre auto sur ADMIS (moins de données)
            $this->filtreDecision = 'admis';
            $this->resetPage();

            // CRITICAL : Empêcher rechargement
            $this->skipNextReload = true;

            $tempsFin = microtime(true);
            $tempsTotal = round(($tempsFin - $tempsDebut) * 1000, 2);

            toastr()->success("⚡ {$this->statistiquesDetailes['admis']} Admis | {$this->statistiquesDetailes['redoublant_autorises']} Redoublants | {$this->statistiquesDetailes['exclus']} Exclus ({$tempsTotal}ms)");

        } catch (\Exception $e) {
            $this->simulationEnCours = false;
            toastr()->error('Erreur : ' . $e->getMessage());
        }
    }


    public function appliquerDeliberation()
    {
        if (!$this->simulationEnCours) {
            toastr()->warning('Veuillez d\'abord simuler la délibération');
            return;
        }

        if (empty($this->simulationResultats)) {
            toastr()->error('Aucune donnée de simulation à appliquer');
            return;
        }

        try {
            DB::beginTransaction();

            $savedCount = 0;
            
            foreach ($this->simulationResultats as $resultat) {
                if (!isset($resultat['etudiant'])) continue;

                $updated = ResultatFinal::whereHas('examen', function($q) {
                        $q->where('niveau_id', $this->niveauPACES->id)
                          ->where('parcours_id', $this->parcoursSelectionne);
                    })
                    ->where('etudiant_id', $resultat['etudiant']->id)
                    ->where('session_exam_id', $this->sessionActive->id)
                    ->update([
                        'decision' => $resultat['decision'],
                        'jury_validated' => true,
                        'is_deliber' => true,
                        'deliber_at' => now(),
                        'deliber_by' => Auth::id(),
                        'updated_at' => now()
                    ]);

                if ($updated > 0) $savedCount++;
            }

            $deliberPaces = DeliberPaces::create([
                'niveau_id' => $this->niveauPACES->id,
                'parcours_id' => $this->parcoursSelectionne,
                'session_exam_id' => $this->sessionActive->id,
                'quota_admission' => $this->quota_admission,
                'credits_requis' => $this->credits_requis,
                'moyenne_requise' => $this->moyenne_requise,
                'note_eliminatoire' => $this->appliquer_note_eliminatoire,
                'nb_admis' => count($this->resultatsGroupes['admis'] ?? []),
                'nb_redoublants' => count($this->resultatsGroupes['redoublant'] ?? []),
                'nb_exclus' => count($this->resultatsGroupes['exclus'] ?? []),
                'applique_par' => Auth::id(),
                'applique_at' => now(),
            ]);

            DB::commit();

            Log::info('✅ Délibération PACES appliquée', [
                'deliber_id' => $deliberPaces->id,
                'etudiants_updated' => $savedCount
            ]);

            $this->simulationEnCours = false;
            $this->simulationResultats = [];
            $this->skipNextReload = false;
            $this->chargerResultatsParcours();

            toastr()->success("Délibération appliquée : {$savedCount} étudiant(s) mis à jour");

        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Erreur : ' . $e->getMessage());
            Log::error('Erreur application délibération', ['error' => $e->getMessage()]);
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
                'est_redoublant' => intval($etudiant->matricule) <= 38999,
                'a_participe' => true
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
        // ✅ Tri optimisé (une seule fois)
        usort($resultats, function($a, $b) {
            if ($b['credits_valides'] !== $a['credits_valides']) {
                return $b['credits_valides'] <=> $a['credits_valides'];
            }
            if ($b['moyenne_generale'] !== $a['moyenne_generale']) {
                return $b['moyenne_generale'] <=> $a['moyenne_generale'];
            }
            return ($a['has_note_eliminatoire'] ? 1 : 0) <=> ($b['has_note_eliminatoire'] ? 1 : 0);
        });

        $admisCount = 0;
        $checkQuota = $this->quota_admission !== null && $this->quota_admission !== '';

        // ✅ Boucle optimisée
        foreach ($resultats as &$resultat) {
            $credits = $resultat['credits_valides'];
            $moyenne = $resultat['moyenne_generale'];
            $hasNoteElim = $resultat['has_note_eliminatoire'];

            // Règles de décision (sans changement)
            if ($this->appliquer_note_eliminatoire && $hasNoteElim) {
                $resultat['decision'] = 'exclus';
                $resultat['decision_simulee'] = true;
                continue;
            }

            if ($credits < 30 || $moyenne < 8) {
                $resultat['decision'] = 'exclus';
                $resultat['decision_simulee'] = true;
                continue;
            }

            if ($credits >= $this->credits_requis && $moyenne >= $this->moyenne_requise) {
                if (!$checkQuota || $admisCount < $this->quota_admission) {
                    $resultat['decision'] = 'admis';
                    $admisCount++;
                } else {
                    $resultat['decision'] = 'redoublant';
                }
            } else {
                $resultat['decision'] = 'redoublant';
            }

            $resultat['decision_simulee'] = true;
        }

        // ✅ Tri final par mérite
        usort($resultats, [$this, 'comparerParMerite']);

        return $resultats;
    }


    private function grouperParDecision($resultats)
    {
        // ✅ Réinitialisation rapide
        $this->resultatsGroupes = ['admis' => [], 'redoublant' => [], 'exclus' => []];

        // ✅ Groupement optimisé
        foreach ($resultats as $resultat) {
            $decision = $resultat['decision'];
            if ($decision === 'admis') {
                $this->resultatsGroupes['admis'][] = $resultat;
            } elseif ($decision === 'redoublant') {
                $this->resultatsGroupes['redoublant'][] = $resultat;
            } elseif ($decision === 'exclus') {
                $this->resultatsGroupes['exclus'][] = $resultat;
            }
        }
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
    }

    // ✅ OPTIMISÉ : Source depuis simulation si active
    public function getResultatsPaginesProperty()
    {
        // ✅ CRITICAL : Éviter les calculs si simulation en cours sans changement
        static $lastHash = null;
        static $lastResult = null;
        
        $currentHash = md5(serialize([
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

    public function render()
    {
        return view('livewire.resultats.liste-resultats-paces');
    }
}