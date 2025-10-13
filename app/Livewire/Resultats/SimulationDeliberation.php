<?php
// app/Livewire/Resultats/SimulationDeliberation.php

namespace App\Livewire\Resultats;

use App\Models\UE;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Etudiant;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SessionExam;
use App\Models\DeliberPaces;
use App\Models\ResultatFinal;
use App\Models\PresenceExamen;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\DeliberationServicePaces;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResultatsPacesExport;
use App\Services\ResultatsPacesPdfService;

class SimulationDeliberation extends Component
{
    use WithPagination;
    
    // PARAMÈTRES URL
    public $parcoursSlug;
    
    // CONFIGURATION
    public $anneeActive;
    public $sessionActive;
    public $niveauPACES;
    public $parcours;
    public $parcoursId;

    // PARAMÈTRES SIMULATION
    public $quota_admission = null;
    public $quota_redoublant = null;
    public $quota_exclus = null;
    public $credits_requis = 60;
    public $moyenne_requise = 10;
    public $appliquer_note_eliminatoire = true;
    
    // NOUVEAUX PARAMÈTRES POUR REDOUBLANTS
    public $moyenne_min_redoublement = null;
    public $credits_min_redoublement = null;

    // ÉTAT
    public $simulationCalculee = false;
    public $resultatsSimulation = [];
    public $compteurs = ['admis' => 0, 'redoublant' => 0, 'exclus' => 0];
    public $statistiquesDetailes = [];
    public $uesStructure;
    
    // FILTRES & RECHERCHE
    public $filtreDecision = 'tous';
    public $recherche = '';
    public $perPage = 50;
    public $perPageOptions = [10, 20, 50, 100, 'Tous'];
    
    // SERVICE
    protected DeliberationServicePaces $deliberationService;

    protected $queryString = [
        'filtreDecision' => ['except' => 'tous'],
        'recherche' => ['except' => ''],
    ];

    public function boot(DeliberationServicePaces $service)
    {
        $this->deliberationService = $service;
    }

    public function mount($parcoursSlug)
    {
        Log::info('🚀 SIMULATION - Mount', ['slug' => $parcoursSlug]);
        
        $this->parcoursSlug = $parcoursSlug;
        
        // Charger config
        $this->chargerConfiguration();
        
        if (!$this->parcours) {
            toastr()->error('Parcours introuvable');
            return redirect()->route('resultats.paces-concours');
        }

        $this->parcoursId = $this->parcours->id;
        
        // Charger dernière délibération si existe
        $derniere = DeliberPaces::where('niveau_id', $this->niveauPACES->id)
            ->where('parcours_id', $this->parcoursId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->latest('applique_at')
            ->first();

        if ($derniere) {
            $this->quota_admission = $derniere->quota_admission;
            $this->quota_redoublant = $derniere->quota_redoublant;
            $this->credits_requis = $derniere->credits_requis;
            $this->moyenne_requise = $derniere->moyenne_requise;
            $this->moyenne_min_redoublement = $derniere->moyenne_min_redoublement;
            $this->credits_min_redoublement = $derniere->credits_min_redoublement;
            $this->appliquer_note_eliminatoire = $derniere->note_eliminatoire;
        } else {
            $this->quota_admission = $this->parcours->quota_admission;
        }

        // Charger UEs
        $this->chargerUEStructure();
        
        // 🎯 LANCER SIMULATION AUTOMATIQUE au chargement
        $this->simuler();
    }

    private function chargerConfiguration()
    {
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

        $this->parcours = Parcour::where('niveau_id', $this->niveauPACES->id)
            ->where('abr', $this->parcoursSlug)
            ->where('is_active', true)
            ->first();
    }

    private function chargerUEStructure()
    {
        // ✅ CHARGER ET FORMATER COMME DANS ListeResultatsPACES
        $ues = UE::where('niveau_id', $this->niveauPACES->id)
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('parcours_id', $this->parcoursId)
                ->orWhereNull('parcours_id');
            })
            ->with(['ecs' => function($q) {
                $q->where('is_active', true)->orderBy('id');
            }])
            ->orderBy('id')
            ->get();

        // ✅ FORMATER POUR L'EXPORT (même structure que ListeResultatsPACES)
        $this->uesStructure = $ues
            ->map(function($ue) {
                return [
                    'ue' => $ue,
                    'ecs' => $ue->ecs
                ];
            })
            ->filter(function($ueStructure) {
                return $ueStructure['ecs']->isNotEmpty();
            });
    }

    /**
     * 🎯 SIMULATION (ultra-rapide, sans tableau)
     */
    public function simuler()
    {
        Log::info('⚡ SIMULATION - Début');

        if (!$this->parcoursId || !$this->sessionActive) {
            toastr()->error('Configuration incomplète');
            return;
        }

        try {
            // 1️⃣ Charger résultats bruts depuis DB
            $resultats = $this->chargerResultatsDepuisDB();
            
            if (empty($resultats)) {
                toastr()->warning('Aucun résultat disponible pour ce parcours');
                $this->simulationCalculee = false;
                return;
            }

            // 2️⃣ Appeler le service de délibération AVEC NOUVEAUX PARAMÈTRES
            $params = [
                'quota_admission' => $this->quota_admission,
                'quota_redoublant' => $this->quota_redoublant,
                'credits_requis' => $this->credits_requis,
                'moyenne_requise' => $this->moyenne_requise,
                'moyenne_min_redoublement' => $this->moyenne_min_redoublement,
                'credits_min_redoublement' => $this->credits_min_redoublement,
                'appliquer_note_eliminatoire' => $this->appliquer_note_eliminatoire,
            ];

            $simulation = $this->deliberationService->calculerDeliberation(
                $resultats,
                $params,
                $this->niveauPACES->id,
                $this->parcoursId,
                $this->sessionActive->id
            );

            // 3️⃣ Stocker résultats
            $this->resultatsSimulation = $simulation['resultats'];
            $this->compteurs = $simulation['compteurs'];
            
            // 4️⃣ Calculer stats détaillées
            $this->calculerStatistiquesDetailees();
            
            $this->simulationCalculee = true;

            Log::info('✅ SIMULATION - Terminée', [
                'compteurs' => $this->compteurs,
                'nb_resultats' => count($this->resultatsSimulation)
            ]);

            toastr()->success(sprintf(
                "⚡ Simulation : %d admis • %d redoublants • %d exclus",
                $this->compteurs['admis'],
                $this->compteurs['redoublant'],
                $this->compteurs['exclus']
            ));

        } catch (\Throwable $e) {
            Log::error('❌ SIMULATION - Erreur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de la simulation');
            $this->simulationCalculee = false;
        }
    }

    /**
     * Charger résultats depuis DB (même logique que ListeResultatsPACES)
     */
    private function chargerResultatsDepuisDB(): array
    {
        $resultatsIds = DB::table('resultats_finaux')
            ->join('examens', 'resultats_finaux.examen_id', '=', 'examens.id')
            ->where('examens.niveau_id', $this->niveauPACES->id)
            ->where('examens.parcours_id', $this->parcoursId)
            ->where('resultats_finaux.session_exam_id', $this->sessionActive->id)
            ->where('resultats_finaux.statut', ResultatFinal::STATUT_PUBLIE)
            ->pluck('resultats_finaux.id')
            ->toArray();

        if (empty($resultatsIds)) return [];

        $resultatsFinaux = ResultatFinal::whereIn('id', $resultatsIds)
            ->with([
                'etudiant:id,nom,prenom,matricule',
                'ec:id,nom,abr,ue_id,coefficient',
                'ec.ue:id,nom,abr,credits'
            ])
            ->get();

        return $this->traiterResultatsEnMemoire($resultatsFinaux);
    }

    /**
     * Traitement mémoire des résultats (calcul moyennes UE, crédits, etc.)
     */
    private function traiterResultatsEnMemoire($resultats): array
    {
        $groupes = $resultats->groupBy('etudiant_id');
        $final = [];

        foreach ($groupes as $etudiantId => $notes) {
            $etudiant = $notes->first()->etudiant;
            if (!$etudiant) continue;

            // Calcul par UE
            $byUE = [];
            $totalCredits = 0;
            
            foreach ($notes as $n) {
                $ueId = $n->ec->ue_id;
                $ue = $n->ec->ue;
                
                if (!isset($byUE[$ueId])) {
                    $byUE[$ueId] = [
                        'sum' => 0.0,
                        'cnt' => 0,
                        'has0' => false,
                        'credits' => $ue->credits,
                        'nom' => $ue->nom
                    ];
                    $totalCredits += (int)$ue->credits;
                }
                
                $byUE[$ueId]['sum'] += (float)$n->note;
                $byUE[$ueId]['cnt'] += 1;
                
                if ((float)$n->note == 0.0) {
                    $byUE[$ueId]['has0'] = true;
                }
            }

            // Calcul moyennes et crédits validés
            $creditsValides = 0;
            $moysUE = [];
            $resUE = [];
            $hasZeroGlobal = false;

            foreach ($byUE as $ueId => $info) {
                $moyUE = $info['cnt'] ? round($info['sum'] / $info['cnt'], 2) : 0.0;
                $validee = ($moyUE >= 10.0) && !$info['has0'];
                
                if ($validee) {
                    $creditsValides += (int)$info['credits'];
                }

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

            $final[] = [
                'etudiant' => $etudiant,
                'notes' => $notes->keyBy('ec_id'),
                'resultats_ue' => $resUE,
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => $creditsValides,
                'total_credits' => $totalCredits,
                'has_note_eliminatoire' => $hasZeroGlobal,
                'decision' => 'non_definie', // Sera calculé par le service
            ];
        }

        return $final;
    }

    /**
     * Calcul stats détaillées (présence, nouveaux, anciens, etc.)
     */
    private function calculerStatistiquesDetailees()
    {
        $statsPresence = $this->obtenirStatistiquesPresence();
        
        $totalInscrits = $statsPresence['total_inscrits'];
        $presents = $statsPresence['presents'];
        $absents = $statsPresence['absents'];
        
        // Compter anciens/nouveaux dans les résultats
        $anciensCount = 0;
        foreach ($this->resultatsSimulation as $r) {
            $mat = (int)$r['etudiant']->matricule;
            if ($mat <= 38999) {
                $anciensCount++;
            }
        }
        $nouveauxCount = max(0, $presents - $anciensCount);

        $this->statistiquesDetailes = [
            'total_inscrits' => $totalInscrits,
            'total_presents' => $presents,
            'total_absents' => $absents,
            'admis' => $this->compteurs['admis'],
            'redoublant_autorises' => $this->compteurs['redoublant'],
            'exclus' => $this->compteurs['exclus'],
            'etudiants_redoublants' => $anciensCount,
            'etudiants_nouveaux' => $nouveauxCount,
            'taux_reussite' => $presents > 0 ? round(($this->compteurs['admis'] / $presents) * 100, 1) : 0,
            'taux_presence' => $totalInscrits > 0 ? round(($presents / $totalInscrits) * 100, 1) : 0,
            'est_delibere' => false,
            'en_simulation' => true,
        ];
    }

    private function obtenirStatistiquesPresence(): array
    {
        $totalInscrits = Etudiant::where('niveau_id', $this->niveauPACES->id)
            ->where('parcours_id', $this->parcoursId)
            ->where('is_active', true)
            ->count();

        $examen = Examen::where('niveau_id', $this->niveauPACES->id)
            ->where('parcours_id', $this->parcoursId)
            ->first();

        if (!$examen) {
            return [
                'total_inscrits' => $totalInscrits,
                'presents' => 0,
                'absents' => $totalInscrits
            ];
        }

        $statsPresence = PresenceExamen::getStatistiquesExamen(
            $examen->id,
            $this->sessionActive->id
        );

        return [
            'total_inscrits' => $totalInscrits,
            'presents' => (int)$statsPresence['presents'],
            'absents' => (int)$statsPresence['absents'],
        ];
    }

    // ========================================
    // 🔍 FILTRES & RECHERCHE
    // ========================================

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

    private function getResultatsFiltres(): array
    {
        $resultats = $this->resultatsSimulation;

        // Filtre par décision
        if ($this->filtreDecision !== 'tous') {
            $resultats = array_values(array_filter($resultats, function($r) {
                return ($r['decision'] ?? 'exclus') === $this->filtreDecision;
            }));
        }

        // Filtre par recherche
        if (!empty($this->recherche)) {
            $terme = mb_strtolower(trim($this->recherche));
            $resultats = array_values(array_filter($resultats, function($r) use ($terme) {
                if (!isset($r['etudiant'])) return false;
                
                $etudiant = $r['etudiant'];
                $matricule = mb_strtolower((string)$etudiant->matricule);
                $nom = mb_strtolower($etudiant->nom);
                $prenom = mb_strtolower($etudiant->prenom);
                
                return str_contains($matricule, $terme) 
                    || str_contains($nom, $terme) 
                    || str_contains($prenom, $terme)
                    || str_contains($nom . ' ' . $prenom, $terme)
                    || str_contains($prenom . ' ' . $nom, $terme);
            }));
        }

        return $resultats;
    }

    // ========================================
    // 📤 EXPORTS
    // ========================================

    public function exporterExcelPaces()
    {
        try {
            $resultats = $this->getResultatsFiltres();

            if (empty($resultats)) {
                toastr()->warning('Aucun résultat à exporter');
                return;
            }

            // ✅ RECHARGER TOUTES LES RELATIONS PROPREMENT
            $resultatsAvecRelations = [];
            
            foreach ($resultats as $resultat) {
                // Vérifier que la structure existe
                if (!isset($resultat['notes']) || !isset($resultat['etudiant'])) {
                    \Log::warning('Export: Résultat incomplet', [
                        'has_notes' => isset($resultat['notes']),
                        'has_etudiant' => isset($resultat['etudiant'])
                    ]);
                    continue;
                }
                
                // Récupérer les IDs des notes
                $noteIds = [];
                foreach ($resultat['notes'] as $note) {
                    if (isset($note->id)) {
                        $noteIds[] = $note->id;
                    }
                }
                
                if (empty($noteIds)) {
                    \Log::warning('Export: Aucune note valide pour étudiant', [
                        'etudiant_id' => $resultat['etudiant']->id ?? 'N/A'
                    ]);
                    continue;
                }
                
                // ✅ Recharger avec TOUTES les relations
                $notesReload = \App\Models\ResultatFinal::whereIn('id', $noteIds)
                    ->with([
                        'etudiant:id,nom,prenom,matricule',
                        'ec:id,nom,abr,ue_id,coefficient,enseignant',
                        'ec.ue:id,nom,abr,credits'
                    ])
                    ->get();
                
                // Vérifier que les relations sont bien chargées
                $notesValides = collect();
                foreach ($notesReload as $note) {
                    if ($note->ec && $note->ec->ue) {
                        $notesValides->push($note);
                    } else {
                        \Log::warning('Export: Relation manquante', [
                            'note_id' => $note->id,
                            'ec_id' => $note->ec_id,
                            'has_ec' => !is_null($note->ec),
                            'has_ue' => !is_null($note->ec?->ue)
                        ]);
                    }
                }
                
                // Remplacer les notes avec la collection rechargée et indexée par ec_id
                $resultat['notes'] = $notesValides->keyBy('ec_id');
                
                // ✅ Ajouter le flag est_redoublant pour l'export
                $matricule = (int)$resultat['etudiant']->matricule;
                $resultat['est_redoublant'] = ($matricule <= 38999);
                
                $resultatsAvecRelations[] = $resultat;
            }
            
            if (empty($resultatsAvecRelations)) {
                toastr()->warning('Aucun résultat valide à exporter');
                return;
            }

            $filtreLabel = match($this->filtreDecision) {
                'admis' => 'Admis',
                'redoublant' => 'Redoublants',
                'exclus' => 'Exclus',
                default => 'Tous'
            };

            $filename = sprintf(
                'Simulation_PACES_%s_%s_%s.xlsx',
                $this->parcours->abr ?? 'Parcours',
                $filtreLabel,
                now()->format('Y-m-d_His')
            );

            \Log::info('Export Excel - Succès', [
                'nb_resultats' => count($resultatsAvecRelations),
                'filename' => $filename
            ]);

            return Excel::download(
                new ResultatsPacesExport(
                    $resultatsAvecRelations,
                    $this->uesStructure,
                    $this->filtreDecision,
                    $this->parcours->nom ?? ''
                ),
                $filename
            );

        } catch (\Throwable $e) {
            \Log::error('Erreur export Excel SIMULATION', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de l\'export Excel : ' . $e->getMessage());
        }
    }

    public function exporterPDF()
    {
        try {
            $resultats = $this->getResultatsFiltres();

            // ✅ NE PLUS BLOQUER si vide
            
            $service = new ResultatsPacesPdfService();
            
            $parcoursNom = $this->parcours->nom ?? 'PACES';
            
            $statistiques = [
                'inscrits' => $this->statistiquesDetailes['total_inscrits'] ?? 0,
                'presents' => $this->statistiquesDetailes['total_presents'] ?? 0,
                'absents' => $this->statistiquesDetailes['total_absents'] ?? 0,
                'total' => $this->statistiquesDetailes['total_presents'] ?? 0,
                'admis' => $this->compteurs['admis'] ?? 0,
                'redoublant' => $this->compteurs['redoublant'] ?? 0,
                'exclus' => $this->compteurs['exclus'] ?? 0,
            ];
            
            $pdf = $service->generer(
                $resultats,  // ✅ Peut être vide []
                $this->uesStructure,
                $this->filtreDecision,
                $parcoursNom,
                $statistiques,
                []
            );

            $filtreLabel = match($this->filtreDecision) {
                'admis' => 'Admis',
                'redoublant' => 'Redoublants',
                'exclus' => 'Exclus',
                default => 'Tous'
            };

            $filename = sprintf(
                'Simulation_PACES_%s_%s_%s.pdf',
                $this->parcours->abr ?? 'Parcours',
                $filtreLabel,
                now()->format('Y-m-d_His')
            );

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Throwable $e) {
            Log::error('Erreur export PDF SIMULATION', ['error' => $e->getMessage()]);
            toastr()->error('Erreur lors de l\'export PDF');
        }
    }

    /**
     * ✅ APPLIQUER LA DÉLIBÉRATION avec nouveaux paramètres
     */
    public function appliquer()
    {
        Log::info('🔒 APPLICATION - Début');

        if (!$this->simulationCalculee || empty($this->resultatsSimulation)) {
            toastr()->error('Aucune simulation à appliquer');
            return;
        }

        try {
            DB::beginTransaction();

            $savedCount = 0;
            
            // Construire map des décisions
            $decisionsMap = [];
            foreach ($this->resultatsSimulation as $r) {
                if (!empty($r['etudiant']) && isset($r['etudiant']->id)) {
                    $decisionsMap[(int)$r['etudiant']->id] = $r['decision'] ?? 'exclus';
                }
            }

            // Mise à jour en base
            foreach ($decisionsMap as $etudiantId => $decision) {
                $updated = ResultatFinal::whereHas('examen', function($q) {
                        $q->where('niveau_id', $this->niveauPACES->id)
                          ->where('parcours_id', $this->parcoursId);
                    })
                    ->where('etudiant_id', $etudiantId)
                    ->where('session_exam_id', $this->sessionActive->id)
                    ->update([
                        'decision' => $decision,
                        'jury_validated' => true,
                        'is_deliber' => true,
                        'deliber_at' => now(),
                        'deliber_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);

                if ($updated > 0) $savedCount++;
            }

            // Enregistrer métadonnées délibération AVEC NOUVEAUX PARAMÈTRES
            DeliberPaces::create([
                'niveau_id' => $this->niveauPACES->id,
                'parcours_id' => $this->parcoursId,
                'session_exam_id' => $this->sessionActive->id,
                'quota_admission' => $this->quota_admission,
                'quota_redoublant' => $this->quota_redoublant,
                'credits_requis' => $this->credits_requis,
                'moyenne_requise' => $this->moyenne_requise,
                'moyenne_min_redoublement' => $this->moyenne_min_redoublement,
                'credits_min_redoublement' => $this->credits_min_redoublement,
                'note_eliminatoire' => $this->appliquer_note_eliminatoire,
                'nb_admis' => $this->compteurs['admis'],
                'nb_redoublants' => $this->compteurs['redoublant'],
                'nb_exclus' => $this->compteurs['exclus'],
                'applique_par' => Auth::id(),
                'applique_at' => now(),
            ]);

            DB::commit();

            // Nettoyer les caches
            $this->nettoyerCaches();

            Log::info('🎉 APPLICATION - Succès', [
                'saved_count' => $savedCount,
                'compteurs' => $this->compteurs
            ]);

            toastr()->success(sprintf(
                "✅ Délibération appliquée : %d étudiant(s) • %d admis • %d redoublants • %d exclus",
                $savedCount,
                $this->compteurs['admis'],
                $this->compteurs['redoublant'],
                $this->compteurs['exclus']
            ));

            // ✅ REDIRECTION vers la liste des résultats
            return redirect()->route('resultats.paces-concours', ['parcoursSlug' => $this->parcoursSlug])
                ->with('success', 'Délibération appliquée avec succès');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ APPLICATION - Erreur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur : ' . $e->getMessage());
        }
    }

    private function nettoyerCaches()
    {
        $keys = [
            "resultats_paces_{$this->parcoursId}_{$this->sessionActive->id}_ids",
            "ues_paces_{$this->niveauPACES->id}_{$this->parcoursId}",
            "stats_parcours_{$this->parcoursId}_{$this->sessionActive->id}",
            "parcours_deliberes_{$this->niveauPACES->id}_{$this->sessionActive->id}",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * 🔙 ANNULER et retourner à la liste
     */
    public function annuler()
    {
        return redirect()->route('resultats.paces-concours', ['parcoursSlug' => $this->parcoursSlug]);
    }

    public function render()
    {
        return view('livewire.resultats.simulation-deliberation');
    }
}