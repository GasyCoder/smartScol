<?php

namespace App\Livewire\Resultats;

use App\Models\EC;
use App\Models\UE;
use App\Models\Copie;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\SessionExam;
use App\Models\PresenceExamen;
use App\Models\ResultatFusion;
use App\Services\FusionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\CalculAcademiqueService;
use App\Exports\ResultatsVerificationExport;
use Livewire\WithPagination;

/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 */

class ResultatVerification extends Component
{
    use WithPagination;

    public $examenId;
    public $etapeFusion = 0;
    public $resultats = [];
    public $showVerification = false;
    public $editingRow = null;
    public $newNote = null;
    public $observation = '';
    public $niveau_id;
    public $parcours_id;
    public $ec_id;
    public $search = '';
    public $niveaux = [];
    public $parcours = [];
    public $ecs = [];
    public $sessionActive = null;
    public $orderBy = 'matricule';
    public $orderAsc = true;
    public $examen;
    public $totalResultats = 0;
    public $resultatsVerifies = 0;
    public $resultatsNonVerifies = 0;
    public $pourcentageVerification = 0;
    public $noExamenFound = false;
    public $afficherMoyennesUE = false;
    protected $fusionService;
    protected $calculService;
    public $enseignant_id = '';
    public $enseignants = [];
    public $statistiquesPresence = [];
    public $afficherInfosPresence = true;

    // Cache pour optimiser les performances
    private $cacheEtudiants = [];
    private $cacheMoyennesUE = [];
    private $cacheMoyennesGenerales = [];
    
    // Pagination
    public $perPage = 150;
    public $page = 1;

    public function __construct()
    {
        $this->fusionService = app(FusionService::class);
        $this->calculService = app(CalculAcademiqueService::class);
    }

    public function mount($examenId)
    {
        $this->examenId = $examenId;
        $this->afficherMoyennesUE = session('afficher_moyennes_ue', false);

        $this->examen = Examen::with(['niveau', 'parcours'])->find($this->examenId);

        if (!$this->examen) {
            $this->noExamenFound = true;
            toastr()->error('Examen non trouvé.');
            return;
        }

        $this->sessionActive = SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->with('anneeUniversitaire')
            ->first();

        if (!$this->sessionActive) {
            toastr()->error('Aucune session active trouvée.');
            return;
        }

        $this->niveaux = Niveau::where('is_active', true)->orderBy('id', 'asc')->get();
        $this->niveau_id = $this->examen->niveau_id;
        $this->parcours_id = $this->examen->parcours_id;

        $this->loadParcours();
        $this->loadEcs();
        $this->checkEtapeFusion();
        $this->chargerDonneesPresence();
        $this->loadResultats();
    }

    public function updatedAfficherMoyennesUE($value)
    {
        session(['afficher_moyennes_ue' => $value]);
        $this->loadResultats();
        $this->dispatch('moyennesUEToggled', $value);
        
        if ($value) {
            toastr()->info('Mode moyennes UE activé - Les exports incluront les calculs UE');
        } else {
            toastr()->info('Mode moyennes UE désactivé - Exports simples sans calculs');
        }
    }

    public function toggleMoyennesUE()
    {
        $this->afficherMoyennesUE = !$this->afficherMoyennesUE;
        $this->updatedAfficherMoyennesUE($this->afficherMoyennesUE);
    }

    public function loadParcours()
    {
        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();
        } else {
            $this->parcours = collect();
        }

        if ($this->parcours_id && !$this->parcours->pluck('id')->contains($this->parcours_id)) {
            $this->parcours_id = null;
            $this->ec_id = null;
        }
    }

    public function loadEcs()
    {
        if ($this->niveau_id && $this->parcours_id) {
            $this->ecs = EC::whereHas('ue', function($query) {
                $query->where('niveau_id', $this->niveau_id)
                      ->where('parcours_id', $this->parcours_id);
            })
            ->orderBy('id', 'asc')
            ->get();

            $this->enseignants = $this->ecs->pluck('enseignant')->unique()->filter()->map(function ($enseignant) {
                return ['id' => $enseignant, 'nom' => $enseignant];
            })->values();

        } elseif ($this->niveau_id) {
            $this->ecs = EC::whereHas('ue', function($query) {
                $query->where('niveau_id', $this->niveau_id);
            })
            ->orderBy('id', 'asc')
            ->get();
        } else {
            $this->ecs = collect();
        }

        $this->enseignants = $this->ecs->pluck('enseignant')->unique()->filter()->map(function ($enseignant) {
            return ['id' => $enseignant, 'nom' => $enseignant];
        })->values();

        if ($this->ec_id && !$this->ecs->pluck('id')->contains($this->ec_id)) {
            $this->ec_id = null;
        }
    }

    public function checkEtapeFusion()
    {
        $this->etapeFusion = ResultatFusion::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->max('etape_fusion') ?? 0;

        $this->showVerification = $this->etapeFusion >= 1;

        if (!$this->showVerification) {
            toastr()->info('La fusion initiale doit être effectuée avant de pouvoir vérifier les résultats.');
        }
    }

    // Pré-charger toutes les données nécessaires en une seule fois
    private function preloadAllData()
    {
        if (!$this->showVerification) return [collect(), collect()];

        $resultatsQuery = ResultatFusion::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2])
            ->where('etape_fusion', $this->etapeFusion)
            ->with([
                'etudiant:id,matricule,nom,prenom',
                'ec:id,nom,enseignant,ue_id',
                'ec.ue:id,nom,abr,credits'
            ]);

        if ($this->enseignant_id) {
            $resultatsQuery->whereHas('ec', function ($q) {
                $q->where('enseignant', $this->enseignant_id);
            });
        }

        if ($this->ec_id) {
            $resultatsQuery->where('ec_id', $this->ec_id);
        }

        if ($this->search) {
            $resultatsQuery->whereHas('etudiant', function ($q) {
                $q->where('matricule', 'like', '%' . $this->search . '%')
                    ->orWhere('nom', 'like', '%' . $this->search . '%')
                    ->orWhere('prenom', 'like', '%' . $this->search . '%');
            });
        }

        $allResultats = $resultatsQuery->get();

        $codesAnonymat = $allResultats->pluck('code_anonymat_id')->unique();
        $ecIds = $allResultats->pluck('ec_id')->unique();
        
        $copies = Copie::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->whereIn('code_anonymat_id', $codesAnonymat)
            ->whereIn('ec_id', $ecIds)
            ->get()
            ->keyBy(function($copie) {
                return $copie->code_anonymat_id . '_' . $copie->ec_id;
            });

        return [$allResultats, $copies];
    }

    // Calcul des moyennes UE en lot
    private function calculerMoyennesUEBatch($etudiantIds)
    {
        if (empty($etudiantIds)) return [];

        $resultatsUE = ResultatFusion::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->whereIn('etudiant_id', $etudiantIds)
            ->where('etape_fusion', $this->etapeFusion)
            ->with(['ec:id,ue_id', 'ec.ue:id,nom,abr,credits,coefficient'])
            ->get()
            ->groupBy(['etudiant_id', 'ec.ue_id']);

        $moyennesUE = [];

        foreach ($resultatsUE as $etudiantId => $resultatsParUE) {
            $moyennesUE[$etudiantId] = [];
            
            foreach ($resultatsParUE as $ueId => $resultatsUEEtudiant) {
                if (!$ueId) continue;

                $ue = $resultatsUEEtudiant->first()->ec->ue;
                $noteEliminatoire = $resultatsUEEtudiant->where('note', 0)->isNotEmpty();
                
                $notesUE = $resultatsUEEtudiant->pluck('note')->filter(function($note) {
                    return $note !== null && is_numeric($note);
                });

                if ($notesUE->isNotEmpty()) {
                    $moyenneUE = $notesUE->avg();
                    $ueValidee = $moyenneUE >= 10 && !$noteEliminatoire;
                    $creditsObtenus = $ueValidee ? ($ue->credits ?? 0) : 0;

                    $moyennesUE[$etudiantId][$ueId] = [
                        'nom' => $ue->nom,
                        'moyenne' => $moyenneUE,
                        'coefficient' => $ue->coefficient ?? 1,
                        'credits' => $ue->credits ?? 0,
                        'credits_obtenus' => $creditsObtenus,
                        'validee' => $ueValidee,
                        'note_eliminatoire' => $noteEliminatoire,
                        'nb_ec' => $notesUE->count()
                    ];
                }
            }
        }

        return $moyennesUE;
    }

    // Calcul des moyennes générales en lot
    private function calculerMoyennesGeneralesBatch($etudiantIds, $moyennesUEBatch)
    {
        $moyennesGenerales = [];

        foreach ($etudiantIds as $etudiantId) {
            if (!isset($moyennesUEBatch[$etudiantId])) {
                $moyennesGenerales[$etudiantId] = null;
                continue;
            }

            $moyennesUE = $moyennesUEBatch[$etudiantId];
            if (empty($moyennesUE)) {
                $moyennesGenerales[$etudiantId] = null;
                continue;
            }

            $sommesMoyennesUE = 0;
            $nombreUE = 0;

            foreach ($moyennesUE as $donneesUE) {
                if ($donneesUE['moyenne'] !== null && is_numeric($donneesUE['moyenne'])) {
                    $sommesMoyennesUE += $donneesUE['moyenne'];
                    $nombreUE++;
                }
            }

            $moyennesGenerales[$etudiantId] = $nombreUE > 0 ? $sommesMoyennesUE / $nombreUE : null;
        }

        return $moyennesGenerales;
    }

    // Méthodes fallback pour calculs individuels
    private function calculerMoyennesUEEtudiant($etudiantId)
    {
        if (isset($this->cacheMoyennesUE[$etudiantId])) {
            return $this->cacheMoyennesUE[$etudiantId];
        }

        $moyennesUE = [];

        $resultatsEtudiant = ResultatFusion::where('examen_id', $this->examenId)
            ->where('etudiant_id', $etudiantId)
            ->where('etape_fusion', $this->etapeFusion)
            ->where('session_exam_id', $this->sessionActive->id)
            ->with(['ec', 'ec.ue'])
            ->get();

        $resultatsParUE = $resultatsEtudiant->groupBy(function($resultat) {
            return $resultat->ec->ue->id ?? null;
        });

        foreach ($resultatsParUE as $ueId => $resultatsUE) {
            if (!$ueId) continue;

            $ue = $resultatsUE->first()->ec->ue;
            $noteEliminatoire = $resultatsUE->where('note', 0)->isNotEmpty();

            $notesUE = $resultatsUE->pluck('note')->filter(function($note) {
                return $note !== null && is_numeric($note);
            });

            if ($notesUE->isNotEmpty()) {
                $moyenneUE = $notesUE->avg();
                $ueValidee = $moyenneUE >= 10 && !$noteEliminatoire;
                $creditsObtenus = $ueValidee ? ($ue->credits ?? 0) : 0;

                $moyennesUE[$ueId] = [
                    'nom' => $ue->nom,
                    'moyenne' => $moyenneUE,
                    'coefficient' => $ue->coefficient ?? 1,
                    'credits' => $ue->credits ?? 0,
                    'credits_obtenus' => $creditsObtenus,
                    'validee' => $ueValidee,
                    'note_eliminatoire' => $noteEliminatoire,
                    'nb_ec' => $notesUE->count()
                ];
            }
        }

        $this->cacheMoyennesUE[$etudiantId] = $moyennesUE;
        return $moyennesUE;
    }

    private function calculerMoyenneGeneraleEtudiant($etudiantId)
    {
        if (isset($this->cacheMoyennesGenerales[$etudiantId])) {
            return $this->cacheMoyennesGenerales[$etudiantId];
        }

        $moyennesUE = $this->calculerMoyennesUEEtudiant($etudiantId);

        if (empty($moyennesUE)) {
            return null;
        }

        $sommesMoyennesUE = 0;
        $nombreUE = 0;

        foreach ($moyennesUE as $donneesUE) {
            if ($donneesUE['moyenne'] !== null && is_numeric($donneesUE['moyenne'])) {
                $sommesMoyennesUE += $donneesUE['moyenne'];
                $nombreUE++;
            }
        }

        $moyenneGenerale = $nombreUE > 0 ? $sommesMoyennesUE / $nombreUE : null;
        $this->cacheMoyennesGenerales[$etudiantId] = $moyenneGenerale;
        
        return $moyenneGenerale;
    }

    public function loadResultats()
    {
        $this->viderCaches();
        $this->noExamenFound = false;
        $this->resultats = [];

        if ($this->niveau_id && $this->parcours_id) {
            $examenExists = Examen::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->exists();

            if (!$examenExists) {
                $this->noExamenFound = true;
                $this->showVerification = false;
                $this->calculerStatistiques();
                return;
            }
        }

        if (!$this->showVerification) {
            $this->calculerStatistiques();
            return;
        }

        try {
            // Récupérer TOUS les ECs du niveau/parcours
            $tousLesECs = $this->getAllECsForExamen();
            
            // Récupérer tous les étudiants qui ont au moins un résultat
            $etudiantsAvecResultats = $this->getEtudiantsAvecResultats();
            
            if ($etudiantsAvecResultats->isEmpty()) {
                $this->calculerStatistiques();
                return;
            }

            // Générer le dataset complet avec TOUS les ECs pour chaque étudiant
            $datasetComplet = $this->genererDatasetComplet($etudiantsAvecResultats, $tousLesECs);
            
            // Appliquer la pagination sur le dataset complet
            $resultatsLimites = $datasetComplet->forPage($this->page, $this->perPage);
            
            $etudiantIds = $resultatsLimites->pluck('etudiant_id')->filter()->unique();

            $moyennesUEBatch = [];
            if ($this->etapeFusion >= 2 && $etudiantIds->isNotEmpty()) {
                $moyennesUEBatch = $this->calculerMoyennesUEBatch($etudiantIds->toArray());
            }

            // Transformer les résultats pour l'affichage
            $resultatsTransformes = $resultatsLimites->map(function ($item, $index) use ($moyennesUEBatch) {
                // Si c'est un EC sans note
                if (!isset($item['resultat_id'])) {
                    return [
                        'id' => null,
                        'unique_key' => "empty_{$item['etudiant_id']}_{$item['ec_id']}",
                        'numero_ordre' => ($this->page - 1) * $this->perPage + $index + 1,
                        'matricule' => $item['matricule'],
                        'nom' => $item['nom'],
                        'prenom' => $item['prenom'],
                        'matiere' => $item['matiere'],
                        'enseignant' => $item['enseignant'],
                        'note' => null,
                        'note_source' => 'empty',
                        'note_old' => null,
                        'is_checked' => false,
                        'commentaire' => '',
                        'copie_id' => null,
                        'etudiant_id' => $item['etudiant_id'],
                        'ec_id' => $item['ec_id'],
                        'code_anonymat_id' => null,
                        'ue_id' => $item['ue_id'],
                        'ue_nom' => $item['ue_nom'],
                        'ue_abr' => $item['ue_abr'],
                        'ue_credits' => $item['ue_credits'],
                        'moyenne_ue' => isset($moyennesUEBatch[$item['etudiant_id']][$item['ue_id']]) 
                            ? $moyennesUEBatch[$item['etudiant_id']][$item['ue_id']]['moyenne'] 
                            : null,
                        'created_at' => null,
                        'updated_at' => null,
                        'saisie_par' => 'Inconnu',      // AJOUTÉ
                        'modifie_par' => 'Inconnu',     // AJOUTÉ
                    ];
                }

                // Si c'est un EC avec note
                $moyenneUE = null;
                if ($this->etapeFusion >= 2 && isset($moyennesUEBatch[$item['etudiant_id']][$item['ue_id']])) {
                    $moyenneUE = $moyennesUEBatch[$item['etudiant_id']][$item['ue_id']]['moyenne'];
                }

                return [
                    'id' => $item['resultat_id'],
                    'unique_key' => "rf_{$item['resultat_id']}",
                    'numero_ordre' => ($this->page - 1) * $this->perPage + $index + 1,
                    'matricule' => $item['matricule'],
                    'nom' => $item['nom'],
                    'prenom' => $item['prenom'],
                    'matiere' => $item['matiere'],
                    'enseignant' => $item['enseignant'],
                    'note' => $item['note_affichee'],
                    'note_source' => $item['note_source'],
                    'note_old' => $item['note_old'],
                    'is_checked' => $item['is_checked'],
                    'commentaire' => $item['commentaire'],
                    'copie_id' => $item['copie_id'],
                    'etudiant_id' => $item['etudiant_id'],
                    'ec_id' => $item['ec_id'],
                    'code_anonymat_id' => $item['code_anonymat_id'],
                    'ue_id' => $item['ue_id'],
                    'ue_nom' => $item['ue_nom'],
                    'ue_abr' => $item['ue_abr'],
                    'ue_credits' => $item['ue_credits'],
                    'moyenne_ue' => $moyenneUE,
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['updated_at'],
                    'saisie_par' => $item['saisie_par'],        // AJOUTÉ
                    'modifie_par' => $item['modifie_par'],      // AJOUTÉ
                ];
            });

            if ($this->orderBy && !$resultatsTransformes->isEmpty()) {
                $champTri = $this->orderBy;
                $ordreAscendant = $this->orderAsc;

                $resultatsTransformes = $resultatsTransformes->sort(function ($a, $b) use ($champTri, $ordreAscendant) {
                    $valeurA = $a[$champTri] ?? '';
                    $valeurB = $b[$champTri] ?? '';

                    if (in_array($champTri, ['moyenne_ue']) && ($valeurA === null || $valeurB === null)) {
                        if ($valeurA === null && $valeurB === null) {
                            return 0;
                        }
                        return $valeurA === null ? 1 : -1;
                    }

                    if (is_numeric($valeurA) && is_numeric($valeurB)) {
                        $comparaison = $valeurA <=> $valeurB;
                    } else {
                        $comparaison = strcasecmp($valeurA, $valeurB);
                    }

                    return $ordreAscendant ? $comparaison : -$comparaison;
                });
            }

            $this->resultats = $resultatsTransformes->values()->toArray();
            $this->totalResultats = $datasetComplet->count();
            
            // Calculer les statistiques sur le dataset complet
            $this->calculerStatistiquesAvecDatasetComplet($datasetComplet);

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des résultats complets', [
                'error' => $e->getMessage(),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'examen_id' => $this->examenId,
                'session_id' => $this->sessionActive->id
            ]);
            toastr()->error('Erreur lors du chargement des résultats : ' . $e->getMessage());
        }
    }

    // Nouvelle méthode pour récupérer tous les ECs
    private function getAllECsForExamen()
    {
        $query = EC::whereHas('ue', function($q) {
            $q->where('niveau_id', $this->examen->niveau_id)
              ->where('parcours_id', $this->examen->parcours_id);
        })->with(['ue:id,nom,abr,credits']);

        if ($this->ec_id) {
            $query->where('id', $this->ec_id);
        }

        if ($this->enseignant_id) {
            $query->where('enseignant', $this->enseignant_id);
        }

        return $query->orderBy('id', 'asc')->get();
    }

    // Nouvelle méthode pour récupérer les étudiants avec au moins un résultat
    private function getEtudiantsAvecResultats()
    {
        $query = \App\Models\Etudiant::whereHas('resultatsFusion', function($q) {
            $q->where('examen_id', $this->examenId)
              ->where('session_exam_id', $this->sessionActive->id)
              ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2])
              ->where('etape_fusion', $this->etapeFusion);
        });

        if ($this->search) {
            $query->where(function($q) {
                $q->where('matricule', 'like', '%' . $this->search . '%')
                  ->orWhere('nom', 'like', '%' . $this->search . '%')
                  ->orWhere('prenom', 'like', '%' . $this->search . '%');
            });
        }

        return $query->select('id', 'matricule', 'nom', 'prenom')
                    ->orderBy('matricule')
                    ->get();
    }

    // Nouvelle méthode pour générer le dataset complet
    private function genererDatasetComplet($etudiants, $ecs)
    {
        $datasetComplet = collect();

        // Charger les utilisateurs une seule fois pour optimiser les performances
        $userIds = [];
        $usersCache = [];

        // Récupérer tous les résultats existants avec les relations utilisateurs
        $resultatsExistants = ResultatFusion::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2])
            ->where('etape_fusion', $this->etapeFusion)
            ->with([
                'etudiant:id,matricule,nom,prenom', 
                'ec:id,nom,enseignant,ue_id', 
                'ec.ue:id,nom,abr,credits'
            ])
            ->get()
            ->keyBy(function($item) {
                return $item->etudiant_id . '_' . $item->ec_id;
            });

        // Récupérer toutes les copies avec les relations utilisateurs
        $copies = Copie::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->with(['utilisateurSaisie:id,name', 'utilisateurModification:id,name'])
            ->get()
            ->keyBy(function($copie) {
                return $copie->code_anonymat_id . '_' . $copie->ec_id;
            });

        foreach ($etudiants as $etudiant) {
            foreach ($ecs as $ec) {
                $key = $etudiant->id . '_' . $ec->id;
                $resultat = $resultatsExistants->get($key);

                if ($resultat) {
                    $copieKey = $resultat->code_anonymat_id . '_' . $resultat->ec_id;
                    $copie = $copies->get($copieKey);

                    $noteAffichee = $resultat->note;
                    $sourceNote = 'resultats_fusion';

                    if ($copie && $copie->is_checked) {
                        $noteAffichee = $copie->note;
                        $sourceNote = 'copies';
                    }

                    // Résoudre les noms des utilisateurs pour saisie_par et modifie_par
                    $saisieParName = 'Inconnu';
                    $modifieParName = 'Inconnu';

                    if ($copie) {
                        // Toujours résoudre saisie_par depuis la copie, car c'est requis dans la table
                        $saisieParName = $copie->utilisateurSaisie ? $copie->utilisateurSaisie->name : 'Système';
                        // Résoudre modifie_par si présent
                        $modifieParName = $copie->utilisateurModification ? $copie->utilisateurModification->name : 'Inconnu';
                    } elseif ($resultat->genere_par) {
                        // Si pas de copie mais resultat généré par le système
                        $saisieParName = 'Système';
                    }

                    $datasetComplet->push([
                        'resultat_id' => $resultat->id,
                        'etudiant_id' => $etudiant->id,
                        'matricule' => $etudiant->matricule,
                        'nom' => $etudiant->nom,
                        'prenom' => $etudiant->prenom,
                        'ec_id' => $ec->id,
                        'matiere' => $ec->nom,
                        'enseignant' => $ec->enseignant ?? 'Non défini',
                        'ue_id' => $ec->ue->id,
                        'ue_nom' => $ec->ue->nom,
                        'ue_abr' => $ec->ue->abr ?? 'UE',
                        'ue_credits' => $ec->ue->credits ?? 0,
                        'note_affichee' => $noteAffichee,
                        'note_source' => $sourceNote,
                        'note_old' => $copie->note_old ?? null,
                        'is_checked' => $copie->is_checked ?? false,
                        'commentaire' => $copie->commentaire ?? '',
                        'copie_id' => $copie->id ?? null,
                        'code_anonymat_id' => $resultat->code_anonymat_id,
                        'created_at' => $copie->created_at ?? $resultat->created_at,
                        'updated_at' => $copie->updated_at ?? $resultat->updated_at,
                        'saisie_par' => $saisieParName,        // AJOUTÉ
                        'modifie_par' => $modifieParName,      // AJOUTÉ
                    ]);
                } else {
                    // EC sans résultat - afficher vide
                    $datasetComplet->push([
                        'etudiant_id' => $etudiant->id,
                        'matricule' => $etudiant->matricule,
                        'nom' => $etudiant->nom,
                        'prenom' => $etudiant->prenom,
                        'ec_id' => $ec->id,
                        'matiere' => $ec->nom,
                        'enseignant' => $ec->enseignant ?? 'Non défini',
                        'ue_id' => $ec->ue->id,
                        'ue_nom' => $ec->ue->nom,
                        'ue_abr' => $ec->ue->abr ?? 'UE',
                        'ue_credits' => $ec->ue->credits ?? 0,
                        'created_at' => null,
                        'updated_at' => null,
                        'saisie_par' => 'Inconnu',
                        'modifie_par' => 'Inconnu',
                    ]);
                }
            }
        }

        return $datasetComplet;
    }

    // Nouvelle méthode pour calculer les statistiques avec dataset complet
    private function calculerStatistiquesAvecDatasetComplet($datasetComplet)
    {
        $resultatsAvecNotes = $datasetComplet->filter(function($item) {
            return isset($item['resultat_id']);
        });

        $this->resultatsVerifies = $resultatsAvecNotes->where('is_checked', true)->count();
        $this->resultatsNonVerifies = $resultatsAvecNotes->where('is_checked', false)->count();
        
        $totalAvecNotes = $resultatsAvecNotes->count();
        $this->pourcentageVerification = $totalAvecNotes === 0 ? 0 :
            round(($this->resultatsVerifies / $totalAvecNotes) * 100, 1);

        if (empty($this->statistiquesPresence)) {
            $this->chargerDonneesPresence();
        }
    }

    // Méthode spéciale pour les exports
    private function loadResultatsForExport()
    {
        $this->loadResultats();
    }

    private function calculerStatistiquesOptimisees($allResultats = null)
    {
        if (!$this->showVerification) {
            $this->totalResultats = 0;
            $this->resultatsVerifies = 0;
            $this->resultatsNonVerifies = 0;
            $this->pourcentageVerification = 0;
            return;
        }

        try {
            if ($allResultats) {
                $this->totalResultats = $allResultats->count();
                
                $copiesVerifiees = Copie::where('examen_id', $this->examenId)
                    ->where('session_exam_id', $this->sessionActive->id)
                    ->where('is_checked', true)
                    ->whereIn('code_anonymat_id', $allResultats->pluck('code_anonymat_id'))
                    ->whereIn('ec_id', $allResultats->pluck('ec_id'))
                    ->count();
                
                $this->resultatsVerifies = $copiesVerifiees;
            } else {
                $baseQuery = ResultatFusion::where('examen_id', $this->examenId)
                    ->where('session_exam_id', $this->sessionActive->id)
                    ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2])
                    ->where('etape_fusion', $this->etapeFusion);

                if ($this->ec_id) {
                    $baseQuery->where('ec_id', $this->ec_id);
                }

                if ($this->search) {
                    $baseQuery->whereHas('etudiant', function ($q) {
                        $q->where('matricule', 'like', '%' . $this->search . '%')
                            ->orWhere('nom', 'like', '%' . $this->search . '%')
                            ->orWhere('prenom', 'like', '%' . $this->search . '%');
                    });
                }

                $this->totalResultats = $baseQuery->count();
                
                $this->resultatsVerifies = $baseQuery->whereHas('copie', function($q) {
                    $q->where('is_checked', true);
                })->count();
            }

            $this->resultatsNonVerifies = $this->totalResultats - $this->resultatsVerifies;
            $this->pourcentageVerification = $this->totalResultats === 0 ? 0 :
                round(($this->resultatsVerifies / $this->totalResultats) * 100, 1);

            if (empty($this->statistiquesPresence)) {
                $this->chargerDonneesPresence();
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques', [
                'error' => $e->getMessage(),
                'examen_id' => $this->examenId,
                'session_id' => $this->sessionActive->id
            ]);
        }
    }

    public function calculerStatistiques()
    {
        $this->calculerStatistiquesOptimisees();
    }

    public function marquerTousVerifies()
    {
        if (!Auth::user()->hasPermissionTo('resultats.verifier')) {
            toastr()->error('Permission insuffisante pour cette action');
            return;
        }

        if ($this->etapeFusion > 2) {
            toastr()->info('Le processus est terminé. La fusion finale a été appliquée (statut VERIFY_3 atteint).');
            return;
        }

        try {
            DB::transaction(function () {
                $resultatsNonVerifies = collect($this->resultats)
                    ->where('is_checked', false)
                    ->filter(function($resultat) {
                        return isset($resultat['copie_id']) && $resultat['copie_id'];
                    });

                if ($resultatsNonVerifies->isEmpty()) {
                    toastr()->info('Aucun résultat à vérifier pour cette étape');
                    return;
                }

                $copiesIds = $resultatsNonVerifies->pluck('copie_id')->unique();

                Copie::whereIn('id', $copiesIds)
                    ->where('session_exam_id', $this->sessionActive->id)
                    ->update([
                        'is_checked' => true,
                        'updated_at' => now()
                    ]);

                $statutCible = match($this->etapeFusion) {
                    1 => ResultatFusion::STATUT_VERIFY_1,
                    2 => ResultatFusion::STATUT_VERIFY_2,
                    default => throw new \InvalidArgumentException("Vérification humaine non applicable pour l'étape {$this->etapeFusion}")
                };

                $resultatsIds = $resultatsNonVerifies->pluck('id')->unique();
                ResultatFusion::marquerPlusieursCommeVerifies($resultatsIds->toArray(), $this->etapeFusion, Auth::id());

                Log::info('Vérifications humaines effectuées en lot', [
                    'etape_fusion' => $this->etapeFusion,
                    'type_action' => $this->etapeFusion == 1 ? 'Première vérification humaine' : 'Seconde vérification humaine',
                    'statut_applique' => $statutCible,
                    'nb_resultats_verifies' => count($resultatsIds),
                    'nb_copies_verifiees' => count($copiesIds),
                    'examen_id' => $this->examenId,
                    'session_exam_id' => $this->sessionActive->id,
                    'utilisateur_id' => Auth::id(),
                    'prochaine_etape_possible' => $this->getProchineEtapeAction(),
                    'timestamp' => now()->toISOString()
                ]);
            });

            $this->loadResultats();
            $this->cancelEditing();

            $etapeNom = match($this->etapeFusion) {
                1 => 'première vérification humaine',
                2 => 'seconde vérification humaine',
            };

            $prochaineEtape = match($this->etapeFusion) {
                1 => 'Vous pouvez maintenant procéder à la fusion 2 (synchronisation des corrections)',
                2 => 'Vous pouvez maintenant procéder à la fusion 3 (finale, qui donnera le statut VERIFY_3)',
            };

            toastr()->success("Tous les résultats affichés ont été vérifiés pour la {$etapeNom}. {$prochaineEtape}");

        } catch (\InvalidArgumentException $e) {
            toastr()->error($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Erreur lors des vérifications humaines en lot', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'etape_fusion' => $this->etapeFusion,
                'examen_id' => $this->examenId,
                'session_exam_id' => $this->sessionActive->id,
                'utilisateur_id' => Auth::id()
            ]);
            toastr()->error('Erreur lors de la vérification: ' . $e->getMessage());
        }
    }

    private function isVerificationHumaineAutorisee(): bool
    {
        return in_array($this->etapeFusion, [1, 2]);
    }

    private function getEtapeLabel(): string
    {
        return match($this->etapeFusion) {
            1 => 'Première vérification humaine (post-fusion initiale)',
            2 => 'Seconde vérification humaine (post-synchronisation)',
            3 => 'Fusion finale terminée (statut VERIFY_3, aucune vérification humaine requise)',
            default => 'Étape indéterminée'
        };
    }

    private function getProchineEtape(): string
    {
        return match($this->etapeFusion) {
            1 => 'Fusion 2 (synchronisation des corrections)',
            2 => 'Fusion 3 (finale → statut VERIFY_3)',
            3 => 'Processus terminé (statut VERIFY_3 atteint)',
            default => 'Étape indéterminée'
        };
    }

    private function getProchineEtapeAction(): string
    {
        return match($this->etapeFusion) {
            1 => 'Fusion de synchronisation disponible',
            2 => 'Fusion finale disponible (résultera en statut VERIFY_3)',
            3 => 'Aucune action requise (processus complet)',
            default => 'Action indéterminée'
        };
    }

    public function startEditing($uniqueKey)
    {
        $resultat = collect($this->resultats)->firstWhere('unique_key', $uniqueKey);

        if (!$resultat) {
            toastr()->error('Résultat non trouvé');
            return;
        }

        $this->editingRow = $uniqueKey;
        $this->newNote = $resultat['note'];
        $this->observation = $resultat['commentaire'] ?? '';

        Log::info('Début édition individuelle', [
            'unique_key' => $uniqueKey,
            'resultat_id' => $resultat['id'],
            'etudiant' => $resultat['nom'] . ' ' . $resultat['prenom'],
            'matiere' => $resultat['matiere'],
            'note_actuelle' => $resultat['note'],
            'etape_fusion' => $this->etapeFusion,
            'session_exam_id' => $this->sessionActive->id
        ]);
    }

    public function cancelEditing()
    {
        $this->editingRow = null;
        $this->newNote = null;
        $this->observation = '';
    }

    public function saveChanges($uniqueKey)
    {
        try {
            $resultatData = collect($this->resultats)->firstWhere('unique_key', $uniqueKey);

            if (!$resultatData) {
                throw new \Exception("Résultat non trouvé avec la clé: {$uniqueKey}");
            }

            if (!is_numeric($this->newNote) || $this->newNote < 0 || $this->newNote > 20) {
                throw new \Exception("La note doit être un nombre entre 0 et 20");
            }

            $resultatFusion = ResultatFusion::with(['etudiant', 'ec'])
                ->where('session_exam_id', $this->sessionActive->id)
                ->findOrFail($resultatData['id']);

            if (!$resultatFusion) {
                throw new \Exception("ResultatFusion non trouvé avec l'ID: {$resultatData['id']}");
            }

            $copie = Copie::where('examen_id', $resultatFusion->examen_id)
                ->where('ec_id', $resultatFusion->ec_id)
                ->where('code_anonymat_id', $resultatFusion->code_anonymat_id)
                ->where('session_exam_id', $this->sessionActive->id)
                ->first();

            if (!$copie) {
                $copie = new Copie([
                    'examen_id' => $resultatFusion->examen_id,
                    'ec_id' => $resultatFusion->ec_id,
                    'code_anonymat_id' => $resultatFusion->code_anonymat_id,
                    'session_exam_id' => $this->sessionActive->id,
                    'note' => $this->newNote,
                    'saisie_par' => Auth::id(),
                    'modifie_par' => Auth::id(),
                    'is_checked' => true,
                    'commentaire' => 'Modifié lors de la vérification humaine',
                    'updated_at' => now(),
                ]);
                $copie->save(); // Explicitly save the new Copie record
            }

            DB::transaction(function () use ($copie, $resultatFusion) {
                $copie->marquerCommeModifiee($this->newNote, $this->observation);
                $resultatFusion->mettreAJourNote($this->newNote, Auth::id(), $this->etapeFusion);
                $resultatFusion->marquerCommeVerifie($this->etapeFusion, Auth::id());
            });

            $this->loadResultats();
            $this->cancelEditing();

            toastr()->success("Note vérifiée et mise à jour avec succès pour {$resultatFusion->etudiant->prenom} {$resultatFusion->etudiant->nom} en {$resultatFusion->ec->nom}");

        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification individuelle', [
                'unique_key' => $uniqueKey,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'etape_fusion' => $this->etapeFusion,
                'session_exam_id' => $this->sessionActive->id,
                'nouvelle_note' => $this->newNote
            ]);
            toastr()->error('Erreur lors de la modification: ' . $e->getMessage());
            $this->cancelEditing();
        }
    }

    public function resetToExamenValues()
    {
        if ($this->examen) {
            $this->niveau_id = $this->examen->niveau_id;
            $this->parcours_id = $this->examen->parcours_id;
            $this->ec_id = null;
            $this->enseignant_id = null;
            $this->search = '';

            $this->loadParcours();
            $this->loadEcs();
            $this->loadResultats();

            toastr()->info('Filtres réinitialisés aux valeurs de l\'examen.');
        }
    }

    public function toggleOrder($field)
    {
        if ($this->orderBy === $field) {
            $this->orderAsc = !$this->orderAsc;
        } else {
            $this->orderBy = $field;
            $this->orderAsc = true;
        }

        $this->loadResultats();
    }

    // Méthodes de pagination
    public function gotoPage($page)
    {
        $this->page = $page;
        $this->loadResultats();
    }

    public function nextPage()
    {
        $maxPages = ceil($this->totalResultats / $this->perPage);
        if ($this->page < $maxPages) {
            $this->page++;
            $this->loadResultats();
        }
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadResultats();
        }
    }

    public function updatedPerPage()
    {
        $this->page = 1;
        $this->loadResultats();
    }

    // Méthodes de mise à jour des filtres
    public function updatedNiveauId()
    {
        $this->viderCaches();
        $this->parcours_id = null;
        $this->ec_id = null;
        $this->enseignant_id = null;
        $this->page = 1;
        $this->loadParcours();
        $this->loadEcs();
        $this->loadResultats();
    }

    public function updatedParcoursId()
    {
        $this->viderCaches();
        $this->ec_id = null;
        $this->enseignant_id = null;
        $this->page = 1;
        $this->loadEcs();
        $this->loadResultats();
    }

    public function updatedEcId()
    {
        $this->viderCaches();
        $this->enseignant_id = null;
        $this->page = 1;
        $this->loadResultats();
    }

    public function updatedSearch()
    {
        $this->page = 1;
        $this->loadResultats();
    }

    public function updatedEnseignantId()
    {
        $this->page = 1;
        $this->loadResultats();
    }

    // Vider les caches
    private function viderCaches()
    {
        $this->cacheEtudiants = [];
        $this->cacheMoyennesUE = [];
        $this->cacheMoyennesGenerales = [];
        
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    public function getPourcentageVerificationProperty()
    {
        return $this->pourcentageVerification;
    }


    /**
     * Prépare les données complètes pour l'export (tous les EC même sans notes)
     */
    private function prepareDataForExportComplet()
    {
        // Récupérer TOUS les EC du niveau/parcours
        $tousLesECs = EC::whereHas('ue', function($query) {
            $query->where('niveau_id', $this->examen->niveau_id)
                ->where('parcours_id', $this->examen->parcours_id);
        })->with(['ue:id,nom,abr,credits'])->orderBy('id')->get();

        // Récupérer tous les étudiants qui ont au moins un résultat
        $etudiantsAvecResultats = $this->getEtudiantsAvecResultats();
        
        if ($etudiantsAvecResultats->isEmpty()) {
            return [];
        }

        // Récupérer tous les résultats existants
        $resultatsExistants = ResultatFusion::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2])
            ->where('etape_fusion', $this->etapeFusion)
            ->with(['etudiant:id,matricule,nom,prenom'])
            ->get()
            ->keyBy(function($item) {
                return $item->etudiant_id . '_' . $item->ec_id;
            });

        // Récupérer toutes les copies
        $copies = Copie::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
            ->with(['utilisateurSaisie:id,name', 'utilisateurModification:id,name']) // AJOUTÉ
            ->get()
            ->keyBy(function($copie) {
                return $copie->code_anonymat_id . '_' . $copie->ec_id;
            });

        $datasetComplet = collect();

        // Générer le dataset complet avec TOUS les EC pour chaque étudiant
        foreach ($etudiantsAvecResultats as $etudiant) {
            foreach ($tousLesECs as $ec) {
                $key = $etudiant->id . '_' . $ec->id;
                $resultat = $resultatsExistants->get($key);

                if ($resultat) {
                    // EC avec résultat existant
                    $copieKey = $resultat->code_anonymat_id . '_' . $resultat->ec_id;
                    $copie = $copies->get($copieKey);

                    $noteAffichee = $resultat->note;
                    $sourceNote = 'resultats_fusion';

                    if ($copie && $copie->is_checked) {
                        $noteAffichee = $copie->note;
                        $sourceNote = 'copies';
                    }

                    $datasetComplet->push([
                        'id' => $resultat->id,
                        'unique_key' => "rf_{$resultat->id}",
                        'matricule' => $etudiant->matricule,
                        'nom' => $etudiant->nom,
                        'prenom' => $etudiant->prenom,
                        'matiere' => $ec->nom,
                        'enseignant' => $ec->enseignant ?? 'Non défini',
                        'note' => $noteAffichee,
                        'note_source' => $sourceNote,
                        'note_old' => $copie->note_old ?? null,
                        'is_checked' => $copie->is_checked ?? false,
                        'commentaire' => $copie->commentaire ?? '',
                        'copie_id' => $copie->id ?? null,
                        'etudiant_id' => $etudiant->id,
                        'ec_id' => $ec->id,
                        'code_anonymat_id' => $resultat->code_anonymat_id,
                        'ue_id' => $ec->ue->id,
                        'ue_nom' => $ec->ue->nom,
                        'ue_abr' => $ec->ue->abr ?? 'UE',
                        'ue_credits' => $ec->ue->credits ?? 0,
                        'created_at' => $copie->created_at ?? null,
                        'updated_at' => $copie->updated_at ?? null,
                    ]);
                } else {
                    // EC sans résultat - créer une entrée vide
                    $datasetComplet->push([
                        'id' => null,
                        'unique_key' => "empty_{$etudiant->id}_{$ec->id}",
                        'matricule' => $etudiant->matricule,
                        'nom' => $etudiant->nom,
                        'prenom' => $etudiant->prenom,
                        'matiere' => $ec->nom,
                        'enseignant' => $ec->enseignant ?? 'Non défini',
                        'note' => null,
                        'note_source' => 'empty',
                        'note_old' => null,
                        'is_checked' => false,
                        'commentaire' => '',
                        'copie_id' => null,
                        'etudiant_id' => $etudiant->id,
                        'ec_id' => $ec->id,
                        'code_anonymat_id' => null,
                        'ue_id' => $ec->ue->id,
                        'ue_nom' => $ec->ue->nom,
                        'ue_abr' => $ec->ue->abr ?? 'UE',
                        'ue_credits' => $ec->ue->credits ?? 0,
                        'created_at' => null,
                        'updated_at' => null,
                    ]);
                }
            }
        }

        return $datasetComplet->toArray();
    }


    public function exportExcel()
    {
        try {
            // Calculer le nombre d'étudiants pour estimer la taille
            $nombreEtudiants = $this->resultats ? collect($this->resultats)->groupBy('matricule')->count() : 0;
            $nombreColonnes = 2 + ($this->afficherMoyennesUE ? 1 : 0) + ($nombreEtudiants * 2);
            
            // Protection contre les exports trop volumineux
            if ($nombreEtudiants > 200) {
                toastr()->error("Export trop volumineux : {$nombreEtudiants} étudiants généreraient {$nombreColonnes} colonnes. Veuillez filtrer les données.");
                return;
            }
            
            if ($this->totalResultats > 10000) {
                toastr()->warning('Export volumineux détecté. Cela peut prendre du temps...');
            }

            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', 300); // 5 minutes
            
            // Utiliser la nouvelle méthode pour récupérer TOUTES les données
            $resultatsEnrichis = $this->prepareDataForExportComplet();

            if (empty($resultatsEnrichis)) {
                toastr()->error('Aucune donnée à exporter.');
                return;
            }

            // Ajouter les moyennes UE si nécessaire
            if ($this->afficherMoyennesUE) {
                $resultatsEnrichis = $this->enrichirAvecMoyennesUE($resultatsEnrichis);
            }

            $metadonneesPresence = [
                'statistiques_presence' => $this->statistiquesPresence,
                'statistiques_verification' => [
                    'total_resultats' => $this->totalResultats,
                    'resultats_verifies' => $this->resultatsVerifies,
                    'resultats_non_verifies' => $this->resultatsNonVerifies,
                    'pourcentage_verification' => $this->pourcentageVerification,
                    'etape_fusion' => $this->etapeFusion
                ],
                'session_info' => [
                    'type' => $this->sessionActive->type,
                    'annee_universitaire' => $this->sessionActive->anneeUniversitaire->nom ?? 'N/A',
                    'date_export' => now()->format('Y-m-d H:i:s')
                ],
                'export_info' => [
                    'nombre_etudiants' => $nombreEtudiants,
                    'nombre_colonnes' => $nombreColonnes,
                    'format' => 'paysage'
                ]
            ];

            $filename = $this->generateFilename('xlsx');

            Log::info('Début export Excel format paysage', [
                'examen_id' => $this->examenId,
                'nombre_etudiants' => $nombreEtudiants,
                'nombre_colonnes' => $nombreColonnes,
                'total_resultats' => count($resultatsEnrichis),
                'avec_moyennes_ue' => $this->afficherMoyennesUE
            ]);

            return Excel::download(
                new ResultatsVerificationExport(
                    $resultatsEnrichis, 
                    $this->examen, 
                    $this->afficherMoyennesUE,
                    $metadonneesPresence
                ),
                $filename
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export Excel', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'examen_id' => $this->examenId,
                'session_exam_id' => $this->sessionActive->id,
                'user_id' => Auth::id(),
                'nombre_etudiants' => $nombreEtudiants ?? 'inconnu'
            ]);
            toastr()->error('Erreur lors de l\'export Excel : ' . $e->getMessage());
        } finally {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', 30);
        }
    }


    /**
     * Enrichit les données avec les moyennes UE pour l'export
     */
    private function enrichirAvecMoyennesUE($resultats)
    {
        $resultatsGroupes = collect($resultats)->groupBy('matricule');
        $resultatsEnrichis = [];

        foreach ($resultatsGroupes as $matricule => $resultatsEtudiant) {
            $premierResultat = $resultatsEtudiant->first();
            $etudiantId = $premierResultat['etudiant_id'];

            // Calculer les moyennes UE pour cet étudiant
            $moyennesUE = $this->calculerMoyennesUEEtudiant($etudiantId);

            foreach ($resultatsEtudiant as $resultat) {
                if (isset($resultat['ue_id']) && isset($moyennesUE[$resultat['ue_id']])) {
                    $resultat['moyenne_ue'] = $moyennesUE[$resultat['ue_id']]['moyenne'];
                } else {
                    $resultat['moyenne_ue'] = null;
                }

                $resultatsEnrichis[] = $resultat;
            }
        }

        return $resultatsEnrichis;
    }

    private function generateFilename($extension)
    {
        $niveau = $this->examen->niveau->abr ?? 'NIV';
        $parcours = $this->examen->parcours->abr ?? 'PARC';
        $sessionType = $this->sessionActive->type ?? 'SESSION';
        $date = now()->format('Y-m-d_Hi');

        $suffixeMoyennes = $this->afficherMoyennesUE ? '-avec-moyennes-UE' : '-sans-moyennes';
        
        $suffixePresence = '';
        if ($this->statistiquesPresence) {
            $tauxPresence = $this->statistiquesPresence['taux_presence'];
            $suffixePresence = "-presence-{$tauxPresence}pct";
        }

        return "resultats-verification-{$niveau}-{$parcours}-{$sessionType}{$suffixeMoyennes}{$suffixePresence}-{$date}.{$extension}";
    }

    private function prepareDataForExportOptimized()
    {
        if ($this->totalResultats <= $this->perPage) {
            return $this->prepareDataForExport();
        }

        $resultatsEnrichis = [];
        $chunkSize = 100;
        
        for ($page = 1; $page <= ceil($this->totalResultats / $chunkSize); $page++) {
            $oldPage = $this->page;
            $oldPerPage = $this->perPage;
            
            $this->page = $page;
            $this->perPage = $chunkSize;
            
            $this->loadResultatsForExport();
            
            if ($this->afficherMoyennesUE) {
                $chunk = $this->prepareDataWithMoyennesUE();
            } else {
                $chunk = $this->resultats;
            }
            
            $resultatsEnrichis = array_merge($resultatsEnrichis, $chunk);
            
            $this->page = $oldPage;
            $this->perPage = $oldPerPage;
            
            $this->viderCaches();
        }

        return $resultatsEnrichis;
    }

    private function prepareDataForExport()
    {
        $resultatsBase = $this->afficherMoyennesUE ? $this->prepareDataWithMoyennesUE() : $this->resultats;
        
        if ($this->statistiquesPresence) {
            $resultatsBase = collect($resultatsBase)->map(function($resultat) {
                $resultat['statistiques_presence'] = $this->statistiquesPresence;
                $resultat['etudiant_est_present'] = true;
                return $resultat;
            })->toArray();
        }
        
        return $resultatsBase;
    }

    private function prepareDataWithMoyennesUE()
    {
        $resultatsGroupes = collect($this->resultats)->groupBy('matricule');
        $resultatsEnrichis = [];

        foreach ($resultatsGroupes as $matricule => $resultatsEtudiant) {
            $premierResultat = $resultatsEtudiant->first();
            $etudiantId = $premierResultat['etudiant_id'];

            $moyennesUE = $this->calculerMoyennesUEEtudiant($etudiantId);
            $moyenneGenerale = $this->calculerMoyenneGeneraleEtudiant($etudiantId);

            foreach ($resultatsEtudiant as $resultat) {
                if (isset($resultat['ue_id']) && isset($moyennesUE[$resultat['ue_id']])) {
                    $resultat['moyenne_ue'] = $moyennesUE[$resultat['ue_id']]['moyenne'];
                }

                $resultat['moyennes_ue_etudiant'] = $moyennesUE;
                $resultat['moyenne_generale'] = $moyenneGenerale;
                $resultatsEnrichis[] = $resultat;
            }
        }

        return $resultatsEnrichis;
    }

    private function chargerDonneesPresence()
    {
        $this->statistiquesPresence = $this->getStatistiquesAvecPresence();
        
        if ($this->statistiquesPresence) {
            Log::info('Données de présence chargées pour verification', [
                'examen_id' => $this->examenId,
                'session_id' => $this->sessionActive->id,
                'session_type' => $this->sessionActive->type,
                'total_inscrits' => $this->statistiquesPresence['total_inscrits'],
                'etudiants_presents' => $this->statistiquesPresence['etudiants_presents'],
                'taux_presence' => $this->statistiquesPresence['taux_presence'],
                'source' => $this->statistiquesPresence['source']
            ]);
        }
    }

    public function getStatistiquesAvecPresence()
    {
        if (!$this->examenId || !$this->sessionActive) {
            return null;
        }

        $sessionId = $this->sessionActive->id;

        $presenceGlobale = PresenceExamen::where('examen_id', $this->examenId)
            ->where('session_exam_id', $sessionId)
            ->whereNull('ec_id')
            ->first();

        if (!$presenceGlobale) {
            $etudiantsPresents = ResultatFusion::where('examen_id', $this->examenId)
                ->where('session_exam_id', $sessionId)
                ->distinct('etudiant_id')
                ->count();
            
            $totalInscrits = $this->getTotalEtudiantsInscrits();
            
            return [
                'total_inscrits' => $totalInscrits,
                'etudiants_presents' => $etudiantsPresents,
                'etudiants_absents' => $totalInscrits - $etudiantsPresents,
                'taux_presence' => $totalInscrits > 0 ? round(($etudiantsPresents / $totalInscrits) * 100, 2) : 0,
                'source' => 'resultats_fusion'
            ];
        }

        return [
            'total_inscrits' => $presenceGlobale->total_attendu ?: $presenceGlobale->total_etudiants,
            'etudiants_presents' => $presenceGlobale->etudiants_presents,
            'etudiants_absents' => $presenceGlobale->etudiants_absents,
            'taux_presence' => $presenceGlobale->taux_presence,
            'source' => 'presence_enregistree'
        ];
    }

    private function getTotalEtudiantsInscrits()
    {
        if (!$this->examen) {
            return 0;
        }

        return \App\Models\Etudiant::where('niveau_id', $this->examen->niveau_id)
            ->where('parcours_id', $this->examen->parcours_id)
            ->where('is_active', true)
            ->count();
    }

    private function calculerCoherencePresence()
    {
        if (!$this->statistiquesPresence) {
            return null;
        }

        $etudiantsAvecResultats = collect($this->resultats)->pluck('matricule')->unique()->count();
        $etudiantsPresents = $this->statistiquesPresence['etudiants_presents'];
        
        return [
            'etudiants_presents_declares' => $etudiantsPresents,
            'etudiants_avec_resultats' => $etudiantsAvecResultats,
            'ecart' => abs($etudiantsAvecResultats - $etudiantsPresents),
            'coherence_parfaite' => $etudiantsAvecResultats === $etudiantsPresents,
            'taux_couverture' => $etudiantsPresents > 0 ? round(($etudiantsAvecResultats / $etudiantsPresents) * 100, 1) : 0,
            'source_presence' => $this->statistiquesPresence['source']
        ];
    }

    public function exportRapportPresence()
    {
        if (!$this->statistiquesPresence) {
            toastr()->error('Aucune donnée de présence disponible pour le rapport.');
            return;
        }

        try {
            $rapportPresence = [
                'examen' => [
                    'id' => $this->examen->id,
                    'nom' => $this->examen->nom,
                    'niveau' => $this->examen->niveau->nom ?? 'N/A',
                    'parcours' => $this->examen->parcours->nom ?? 'N/A'
                ],
                'session' => [
                    'type' => $this->sessionActive->type,
                    'annee' => $this->sessionActive->anneeUniversitaire->nom ?? 'N/A',
                    'date_export' => now()
                ],
                'presence' => $this->statistiquesPresence,
                'coherence' => $this->calculerCoherencePresence(),
                'verification' => [
                    'etape_fusion' => $this->etapeFusion,
                    'total_resultats' => $this->totalResultats,
                    'resultats_verifies' => $this->resultatsVerifies,
                    'pourcentage_verification' => $this->pourcentageVerification
                ],
                'details_etudiants' => $this->getDetailsEtudiantsPresence()
            ];

            $filename = "rapport-presence-{$this->examen->niveau->abr}-{$this->examen->parcours->abr}-{$this->sessionActive->type}-" . now()->format('Y-m-d_Hi') . ".json";

            return response()->json($rapportPresence)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export rapport présence', [
                'error' => $e->getMessage(),
                'examen_id' => $this->examenId,
                'session_id' => $this->sessionActive->id
            ]);
            toastr()->error('Erreur lors de l\'export du rapport : ' . $e->getMessage());
        }
    }

    private function getDetailsEtudiantsPresence()
    {
        $etudiantsAvecResultats = collect($this->resultats)
            ->groupBy('matricule')
            ->map(function($resultatsEtudiant) {
                $premier = $resultatsEtudiant->first();
                return [
                    'matricule' => $premier['matricule'],
                    'nom' => $premier['nom'],
                    'prenom' => $premier['prenom'],
                    'nb_resultats' => $resultatsEtudiant->count(),
                    'nb_resultats_verifies' => $resultatsEtudiant->where('is_checked', true)->count(),
                    'moyenne_generale' => $premier['moyenne_generale'] ?? null,
                    'present_selon_resultats' => true
                ];
            })
            ->values()
            ->toArray();

        return [
            'nb_etudiants_avec_resultats' => count($etudiantsAvecResultats),
            'liste_etudiants' => $etudiantsAvecResultats
        ];
    }

    public function toggleInfosPresence()
    {
        $this->afficherInfosPresence = !$this->afficherInfosPresence;
    }

    public function diagnostiquerEcartsPresence()
    {
        if (!$this->statistiquesPresence) {
            toastr()->warning('Aucune donnée de présence disponible pour le diagnostic.');
            return;
        }

        try {
            $etudiantsAvecResultats = ResultatFusion::where('examen_id', $this->examenId)
                ->where('session_exam_id', $this->sessionActive->id)
                ->distinct('etudiant_id')
                ->count();

            $etudiantsPresents = $this->statistiquesPresence['etudiants_presents'];
            $ecart = abs($etudiantsAvecResultats - $etudiantsPresents);

            if ($ecart > 0) {
                Log::warning('Écart détecté entre présence et résultats', [
                    'examen_id' => $this->examenId,
                    'session_id' => $this->sessionActive->id,
                    'etudiants_presents_declare' => $etudiantsPresents,
                    'etudiants_avec_resultats' => $etudiantsAvecResultats,
                    'ecart' => $ecart,
                    'source_presence' => $this->statistiquesPresence['source']
                ]);
                
                toastr()->warning("Écart détecté : {$etudiantsPresents} présents déclarés vs {$etudiantsAvecResultats} avec résultats (écart: {$ecart})");
            } else {
                toastr()->success("Cohérence parfaite : {$etudiantsPresents} présents = {$etudiantsAvecResultats} avec résultats");
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du diagnostic des écarts', [
                'error' => $e->getMessage(),
                'examen_id' => $this->examenId,
                'session_id' => $this->sessionActive->id
            ]);
            toastr()->error('Erreur lors du diagnostic : ' . $e->getMessage());
        }
    }

    public function getStatistiquesDetailleesAvecPresence()
    {
        $statsBase = $this->getStatistiquesDetaillees();
        
        if ($this->statistiquesPresence) {
            $statsBase['presence'] = $this->statistiquesPresence;
            
            if ($this->statistiquesPresence['etudiants_presents'] > 0) {
                $statsBase['ratios'] = [
                    'resultats_par_present' => round($this->totalResultats / $this->statistiquesPresence['etudiants_presents'], 2),
                    'verification_vs_presents' => round(($this->resultatsVerifies / $this->statistiquesPresence['etudiants_presents']) * 100, 1),
                    'couverture_fusion' => round(($this->totalResultats / ($this->statistiquesPresence['etudiants_presents'] * count($this->ecs))) * 100, 1)
                ];
            }
        }
        
        return $statsBase;
    }

    public function getStatistiquesDetaillees()
    {
        if (!$this->sessionActive || !$this->examen) {
            return [];
        }

        $stats = [
            'session' => [
                'type' => $this->sessionActive->type,
                'annee_universitaire' => $this->sessionActive->anneeUniversitaire->nom ?? 'N/A',
                'is_active' => $this->sessionActive->is_active,
                'is_current' => $this->sessionActive->is_current
            ],
            'examen' => [
                'nom' => $this->examen->nom,
                'niveau' => $this->examen->niveau->nom ?? 'N/A',
                'parcours' => $this->examen->parcours->nom ?? 'N/A'
            ],
            'fusion' => [
                'etape_actuelle' => $this->etapeFusion,
                'verification_possible' => $this->showVerification
            ],
            'resultats' => [
                'total' => $this->totalResultats,
                'verifies' => $this->resultatsVerifies,
                'non_verifies' => $this->resultatsNonVerifies,
                'pourcentage' => $this->pourcentageVerification
            ]
        ];

        return $stats;
    }

    public function verifierCoherenceSession()
    {
        if (!$this->sessionActive || !$this->examen) {
            return false;
        }

        try {
            $resultatsIncorrects = ResultatFusion::where('examen_id', $this->examenId)
                ->where('session_exam_id', '!=', $this->sessionActive->id)
                ->count();

            $copiesIncorrectes = Copie::where('examen_id', $this->examenId)
                ->where('session_exam_id', '!=', $this->sessionActive->id)
                ->count();

            if ($resultatsIncorrects > 0 || $copiesIncorrectes > 0) {
                Log::warning('Incohérence détectée dans les données de session', [
                    'examen_id' => $this->examenId,
                    'session_active_id' => $this->sessionActive->id,
                    'resultats_incorrects' => $resultatsIncorrects,
                    'copies_incorrectes' => $copiesIncorrectes
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de cohérence', [
                'error' => $e->getMessage(),
                'examen_id' => $this->examenId,
                'session_active_id' => $this->sessionActive->id
            ]);
            return false;
        }
    }

    public function nettoyerDonneesOrphelines()
    {
        if (!Auth::user()->hasPermissionTo('resultats.admin')) {
            toastr()->error('Permission insuffisante pour cette action');
            return;
        }

        try {
            DB::transaction(function () {
                $orphelinesResultats = ResultatFusion::where('examen_id', $this->examenId)
                    ->whereDoesntHave('sessionExam')
                    ->delete();

                $orphelinesCopies = Copie::where('examen_id', $this->examenId)
                    ->whereDoesntHave('sessionExam')
                    ->delete();

                Log::info('Nettoyage données orphelines effectué', [
                    'examen_id' => $this->examenId,
                    'resultats_supprimes' => $orphelinesResultats,
                    'copies_supprimees' => $orphelinesCopies,
                    'user_id' => Auth::id()
                ]);

                if ($orphelinesResultats > 0 || $orphelinesCopies > 0) {
                    toastr()->success("Nettoyage effectué : {$orphelinesResultats} résultats et {$orphelinesCopies} copies orphelines supprimés.");
                } else {
                    toastr()->info('Aucune donnée orpheline trouvée.');
                }
            });

            $this->loadResultats();
        } catch (\Exception $e) {
            Log::error('Erreur lors du nettoyage', [
                'error' => $e->getMessage(),
                'examen_id' => $this->examenId,
                'user_id' => Auth::id()
            ]);
            toastr()->error('Erreur lors du nettoyage : ' . $e->getMessage());
        }
    }

    public function render()
    {
        $maxPages = ceil($this->totalResultats / $this->perPage);
        $hasPagination = $this->totalResultats > $this->perPage;
        
        $paginationInfo = [
            'current_page' => $this->page,
            'per_page' => $this->perPage,
            'total' => $this->totalResultats,
            'max_pages' => $maxPages,
            'has_pagination' => $hasPagination,
            'from' => (($this->page - 1) * $this->perPage) + 1,
            'to' => min($this->page * $this->perPage, $this->totalResultats)
        ];

        $statistiquesDetailleesAvecPresence = $this->getStatistiquesDetailleesAvecPresence();
        
        return view('livewire.resultats.resultats-verification', [
            'examen' => $this->examen,
            'sessionActive' => $this->sessionActive,
            'resultats' => $this->resultats,
            'niveaux' => $this->niveaux,
            'parcours' => $this->parcours,
            'ecs' => $this->ecs,
            'showVerification' => $this->showVerification,
            'etapeFusion' => $this->etapeFusion,
            'totalResultats' => $this->totalResultats,
            'resultatsVerifies' => $this->resultatsVerifies,
            'resultatsNonVerifies' => $this->resultatsNonVerifies,
            'pourcentageVerification' => $this->pourcentageVerification,
            'noExamenFound' => $this->noExamenFound,
            'afficherMoyennesUE' => $this->afficherMoyennesUE,
            'statistiquesPresence' => $this->statistiquesPresence,
            'afficherInfosPresence' => $this->afficherInfosPresence,
            'statistiquesDetailleesAvecPresence' => $statistiquesDetailleesAvecPresence,
            'paginationInfo' => $paginationInfo,
            'qualitesDonnees' => [
                'coherence_presence' => $this->calculerCoherencePresence(),
                'source_donnees_fiable' => $this->statistiquesPresence && $this->statistiquesPresence['source'] === 'presence_enregistree',
                'couverture_complete' => $this->totalResultats > 0 && $this->statistiquesPresence && 
                                    ($this->totalResultats >= $this->statistiquesPresence['etudiants_presents'] * count($this->ecs) * 0.8)
            ],
            'actionsDisponibles' => [
                'peut_exporter_rapport_presence' => !empty($this->statistiquesPresence),
                'peut_diagnostiquer_ecarts' => !empty($this->statistiquesPresence) && $this->totalResultats > 0,
                'verification_coherence_possible' => $this->showVerification && !empty($this->statistiquesPresence)
            ]
        ]);
    }
}