<?php

namespace App\Livewire\Resultats;

use Livewire\Component;
use App\Models\ResultatFusion;
use App\Models\Examen;
use App\Models\Copie;
use App\Models\EC;
use App\Models\UE;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\SessionExam;
use App\Services\FusionService;
use App\Services\CalculAcademiqueService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ResultatVerification extends Component
{
    public $examenId;
    public $etapeFusion = 0;
    public $resultats = [];
    public $showVerification = false;
    public $editingRow = null; // Maintenant utilisé pour stocker l'ID unique du résultat
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
    public $printMode = false;
    public $totalResultats = 0;
    public $resultatsVerifies = 0;
    public $resultatsNonVerifies = 0;

    protected $fusionService;
    protected $calculService;

    public function __construct()
    {
        $this->fusionService = app(FusionService::class);
        $this->calculService = app(CalculAcademiqueService::class);
    }

    public function mount($examenId)
    {
        $this->examenId = $examenId;
        $this->examen = Examen::with(['niveau', 'parcours', 'session'])->find($this->examenId);

        if (!$this->examen) {
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

        $this->niveaux = Niveau::where('is_active', true)->orderBy('abr', 'desc')->get();
        $this->niveau_id = $this->examen->niveau_id;
        $this->parcours_id = $this->examen->parcours_id;
        $this->loadParcours();
        $this->loadEcs();
        $this->checkEtapeFusion();
        $this->loadResultats();
    }

    public function loadParcours()
    {
        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('abr')
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
            ->orderBy('nom')
            ->get();
        } elseif ($this->niveau_id) {
            $this->ecs = EC::whereHas('ue', function($query) {
                $query->where('niveau_id', $this->niveau_id);
            })
            ->orderBy('nom')
            ->get();
        } else {
            $this->ecs = collect();
        }

        if ($this->ec_id && !$this->ecs->pluck('id')->contains($this->ec_id)) {
            $this->ec_id = null;
        }
    }

    public function resetToExamenValues()
    {
        if ($this->examen) {
            $this->niveau_id = $this->examen->niveau_id;
            $this->parcours_id = $this->examen->parcours_id;
            $this->ec_id = null;
            $this->search = '';

            $this->loadParcours();
            $this->loadEcs();
            $this->loadResultats();

            toastr()->info('Filtres réinitialisés aux valeurs de l\'examen.');
        }
    }

    public function togglePrintMode()
    {
        $this->printMode = !$this->printMode;
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

    public function updatedNiveauId()
    {
        $this->parcours_id = null;
        $this->ec_id = null;
        $this->loadParcours();
        $this->loadEcs();
        $this->loadResultats();
    }

    public function updatedParcoursId()
    {
        $this->ec_id = null;
        $this->loadEcs();
        $this->loadResultats();
    }

    public function updatedEcId()
    {
        $this->loadResultats();
    }

    public function updatedSearch()
    {
        $this->loadResultats();
    }

    public function checkEtapeFusion()
    {
        $this->etapeFusion = ResultatFusion::where('examen_id', $this->examenId)
            ->max('etape_fusion') ?? 0;

        $this->showVerification = $this->etapeFusion >= 1;

        if (!$this->showVerification) {
            toastr()->info('La fusion doit être effectuée avant de vérifier les résultats.');
        }
    }

    public function loadResultats()
    {
        if (!$this->showVerification) {
            $this->resultats = [];
            $this->calculerStatistiques();
            return;
        }

        $query = ResultatFusion::where('examen_id', $this->examenId)
            ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2])
            ->where('etape_fusion', $this->etapeFusion)
            ->with(['etudiant', 'ec', 'ec.ue']);

        if ($this->ec_id) {
            $query->where('ec_id', $this->ec_id);
        }

        if ($this->search) {
            $query->whereHas('etudiant', function ($q) {
                $q->where('matricule', 'like', '%' . $this->search . '%')
                    ->orWhere('nom', 'like', '%' . $this->search . '%')
                    ->orWhere('prenom', 'like', '%' . $this->search . '%');
            });
        }

        $resultatsTransformes = $query->get()
            ->map(function ($resultat, $index) {
                $etudiant = $resultat->etudiant;
                $ec = $resultat->ec;
                $ue = $ec->ue;

                // ✅ CORRESPONDANCE FIABLE : Utiliser les 3 clés uniques comme référence
                $copie = Copie::where('examen_id', $resultat->examen_id)
                    ->where('ec_id', $resultat->ec_id)
                    ->where('code_anonymat_id', $resultat->code_anonymat_id)
                    ->first();

                // Les 3 clés (examen_id, ec_id, code_anonymat_id) garantissent déjà la correspondance exacte
                // Pas besoin de vérifications supplémentaires qui peuvent poser problème

                $noteAffichee = $resultat->note;
                $sourceNote = 'resultats_fusion';

                if ($copie && $copie->is_checked) {
                    $noteAffichee = $copie->note;
                    $sourceNote = 'copies';
                }

                $moyenneUE = null;
                if ($this->etapeFusion >= 2 && $ue) {
                    try {
                        $resultatsUE = ResultatFusion::where('examen_id', $this->examenId)
                            ->where('etudiant_id', $etudiant->id)
                            ->whereHas('ec', function ($q) use ($ue) {
                                $q->where('ue_id', $ue->id);
                            })
                            ->get();

                        $calculResultat = $this->calculService->calculerResultatUE($ue, $resultatsUE);
                        $moyenneUE = $calculResultat['moyenne'];
                    } catch (\Exception $e) {
                        Log::error('Erreur calcul moyenne UE', [
                            'etudiant_id' => $etudiant->id,
                            'ue_id' => $ue->id,
                            'error' => $e->getMessage(),
                        ]);
                        $moyenneUE = null;
                    }
                }

                // ✅ IDENTIFIANT UNIQUE : Utiliser l'ID du ResultatFusion comme clé unique
                return [
                    'id' => $resultat->id, // ID du ResultatFusion - CLÉ UNIQUE IMPORTANTE
                    'unique_key' => "rf_{$resultat->id}", // Clé unique pour l'affichage
                    'numero_ordre' => $index + 1, // Sera recalculé après tri
                    'matricule' => $etudiant->matricule ?? 'N/A',
                    'nom' => $etudiant->nom ?? 'N/A',
                    'prenom' => $etudiant->prenom ?? 'N/A',
                    'matiere' => $ec->nom ?? 'N/A',
                    'enseignant' => $ec->enseignant ?? 'Non défini',
                    'note' => $noteAffichee,
                    'note_source' => $sourceNote,
                    'note_old' => $copie->note_old ?? null,
                    'is_checked' => $copie->is_checked ?? false,
                    'commentaire' => $copie->commentaire ?? '',
                    'copie_id' => $copie->id ?? null,
                    'etudiant_id' => $resultat->etudiant_id,
                    'ec_id' => $resultat->ec_id,
                    'code_anonymat_id' => $resultat->code_anonymat_id,
                    'ue_id' => $ue->id ?? null,
                    'ue_nom' => $ue->nom ?? 'N/A',
                    'moyenne_ue' => $moyenneUE,
                    'created_at' => $copie->created_at ?? null,
                    'updated_at' => $copie->updated_at ?? null,
                ];
            });

        // ✅ TRI CORRECT : Préserver les IDs uniques
        if ($this->orderBy && !$resultatsTransformes->isEmpty()) {
            $champTri = $this->orderBy;
            $ordreAscendant = $this->orderAsc;

            $resultatsTransformes = $resultatsTransformes->sort(function ($a, $b) use ($champTri, $ordreAscendant) {
                $valeurA = $a[$champTri] ?? '';
                $valeurB = $b[$champTri] ?? '';

                if ($champTri === 'moyenne_ue' && ($valeurA === null || $valeurB === null)) {
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

        // ✅ RECALCUL DES NUMÉROS D'ORDRE après tri
        $this->resultats = $resultatsTransformes->values()->map(function ($resultat, $index) {
            $resultat['numero_ordre'] = $index + 1;
            return $resultat;
        })->toArray();

        $this->calculerStatistiques();
    }

    public function calculerStatistiques()
    {
        if (!$this->showVerification) {
            $this->totalResultats = 0;
            $this->resultatsVerifies = 0;
            $this->resultatsNonVerifies = 0;
            return;
        }

        $totalQuery = ResultatFusion::where('examen_id', $this->examenId)
            ->whereIn('statut', [ResultatFusion::STATUT_VERIFY_1, ResultatFusion::STATUT_VERIFY_2])
            ->where('etape_fusion', $this->etapeFusion);

        if ($this->ec_id) {
            $totalQuery->where('ec_id', $this->ec_id);
        }

        if ($this->search) {
            $totalQuery->whereHas('etudiant', function ($q) {
                $q->where('matricule', 'like', '%' . $this->search . '%')
                    ->orWhere('nom', 'like', '%' . $this->search . '%')
                    ->orWhere('prenom', 'like', '%' . $this->search . '%');
            });
        }

        $this->totalResultats = $totalQuery->count();
        $this->resultatsVerifies = collect($this->resultats)->where('is_checked', true)->count();
        $this->resultatsNonVerifies = $this->totalResultats - $this->resultatsVerifies;
    }

    public function marquerTousVerifies()
    {
        if (!Auth::user()->hasPermissionTo('resultats.verifier')) {
            toastr()->error('Permission insuffisante pour cette action');
            return;
        }

        try {
            DB::transaction(function () {
                $resultatsNonVerifies = collect($this->resultats)
                    ->where('is_checked', false)
                    ->filter(function($resultat) {
                        return isset($resultat['copie_id']) && $resultat['copie_id'];
                    });

                if ($resultatsNonVerifies->isNotEmpty()) {
                    $copiesIds = $resultatsNonVerifies->pluck('copie_id');
                    Copie::whereIn('id', $copiesIds)
                        ->update([
                            'is_checked' => true,
                            'updated_at' => now()
                        ]);

                    $resultatsIds = $resultatsNonVerifies->pluck('id');
                    ResultatFusion::whereIn('id', $resultatsIds)
                        ->update([
                            'statut' => $this->etapeFusion == 1 ? ResultatFusion::STATUT_VERIFY_1 : ResultatFusion::STATUT_VERIFY_2,
                            'updated_at' => now()
                        ]);
                }
            });

            $this->loadResultats();
            toastr()->success('Tous les résultats affichés ont été marqués comme vérifiés');
        } catch (\Exception $e) {
            Log::error('Erreur marquage en lot', ['error' => $e->getMessage()]);
            toastr()->error('Erreur lors du marquage: ' . $e->getMessage());
        }
    }

    // ✅ MODIFICATION MAJEURE : Utiliser l'ID unique plutôt que l'index
    public function startEditing($uniqueKey)
    {
        // Trouver le résultat par sa clé unique
        $resultat = collect($this->resultats)->firstWhere('unique_key', $uniqueKey);

        if (!$resultat) {
            toastr()->error('Résultat non trouvé');
            return;
        }

        $this->editingRow = $uniqueKey; // Stocker la clé unique
        $this->newNote = $resultat['note'];
        $this->observation = $resultat['commentaire'] ?? '';

        Log::info('Début édition', [
            'unique_key' => $uniqueKey,
            'resultat_id' => $resultat['id'],
            'etudiant' => $resultat['nom'] . ' ' . $resultat['prenom'],
            'matiere' => $resultat['matiere'],
            'note_actuelle' => $resultat['note']
        ]);
    }

    public function cancelEditing()
    {
        $this->editingRow = null;
        $this->newNote = null;
        $this->observation = '';
    }

    // ✅ CORRECTION MAJEURE : Méthode saveChanges entièrement refaite
    public function saveChanges($uniqueKey)
    {
        try {
            // Trouver le résultat par sa clé unique
            $resultatData = collect($this->resultats)->firstWhere('unique_key', $uniqueKey);

            if (!$resultatData) {
                throw new \Exception("Résultat non trouvé avec la clé: {$uniqueKey}");
            }

            // Validation de la note
            if (!is_numeric($this->newNote) || $this->newNote < 0 || $this->newNote > 20) {
                throw new \Exception("La note doit être un nombre entre 0 et 20");
            }

            // Vérifications de cohérence
            $resultatFusion = ResultatFusion::with(['etudiant', 'ec'])->findOrFail($resultatData['id']);

            if (!$resultatFusion) {
                throw new \Exception("ResultatFusion non trouvé avec l'ID: {$resultatData['id']}");
            }

            // Log détaillé pour debug
            Log::info('Tentative de sauvegarde', [
                'unique_key' => $uniqueKey,
                'resultat_fusion_id' => $resultatFusion->id,
                'etudiant_id' => $resultatFusion->etudiant_id,
                'etudiant_nom' => $resultatFusion->etudiant->nom ?? 'N/A',
                'etudiant_prenom' => $resultatFusion->etudiant->prenom ?? 'N/A',
                'ec_id' => $resultatFusion->ec_id,
                'ec_nom' => $resultatFusion->ec->nom ?? 'N/A',
                'code_anonymat_id' => $resultatFusion->code_anonymat_id,
                'nouvelle_note' => $this->newNote,
                'ancienne_note' => $resultatData['note']
            ]);

            // Recherche de la copie correspondante avec vérifications strictes
            $copie = Copie::where('examen_id', $resultatFusion->examen_id)
                ->where('ec_id', $resultatFusion->ec_id)
                ->where('code_anonymat_id', $resultatFusion->code_anonymat_id)
                ->first();

            if (!$copie) {
                // Créer une nouvelle copie si elle n'existe pas
                $copie = new Copie([
                    'examen_id' => $resultatFusion->examen_id,
                    'ec_id' => $resultatFusion->ec_id,
                    'code_anonymat_id' => $resultatFusion->code_anonymat_id,
                    'note' => $this->newNote,
                    'saisie_par' => Auth::id(),
                    'is_checked' => true,
                    'commentaire' => $this->observation
                ]);

                Log::info('Création nouvelle copie', [
                    'resultat_fusion_id' => $resultatFusion->id,
                    'nouvelle_note' => $this->newNote
                ]);
            } else {
                // ✅ CORRESPONDANCE FIABLE : Les 3 clés garantissent l'unicité
                // La combinaison (examen_id, ec_id, code_anonymat_id) est unique et suffit
                // pour identifier précisément la copie correspondant au résultat
                Log::info('Création nouvelle copie', [
                    'resultat_fusion_id' => $resultatFusion->id,
                    'examen_id' => $resultatFusion->examen_id,
                    'ec_id' => $resultatFusion->ec_id,
                    'code_anonymat_id' => $resultatFusion->code_anonymat_id,
                    'nouvelle_note' => $this->newNote
                ]);
            }

            // Transaction pour garantir la cohérence
            DB::transaction(function () use ($copie, $resultatFusion) {
                // Sauvegarder l'ancienne note si elle change
                if ($copie->exists && $copie->note != $this->newNote) {
                    $copie->note_old = $copie->note;
                }

                $copie->note = $this->newNote;
                $copie->commentaire = $this->observation;
                $copie->is_checked = true;
                $copie->save();

                // Mettre à jour le résultat fusion
                $resultatFusion->note = $this->newNote;
                $resultatFusion->statut = $this->etapeFusion == 1 ?
                    ResultatFusion::STATUT_VERIFY_1 :
                    ResultatFusion::STATUT_VERIFY_2;
                $resultatFusion->modifie_par = Auth::id();
                $resultatFusion->save();

                Log::info('Sauvegarde réussie', [
                    'copie_id' => $copie->id,
                    'resultat_fusion_id' => $resultatFusion->id,
                    'note_appliquee' => $this->newNote,
                    'user_id' => Auth::id()
                ]);
            });

            // Recharger les données et annuler l'édition
            $this->loadResultats();
            $this->cancelEditing();

            toastr()->success("Note vérifiée et mise à jour avec succès pour {$resultatFusion->etudiant->prenom} {$resultatFusion->etudiant->nom} en {$resultatFusion->ec->nom}");

        } catch (\Exception $e) {
            Log::error('Erreur dans le processus de vérification', [
                'unique_key' => $uniqueKey,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur lors de la vérification: ' . $e->getMessage());
            $this->cancelEditing();
        }
    }

    public function getPourcentageVerificationProperty()
    {
        return $this->totalResultats === 0 ? 0 : round(($this->resultatsVerifies / $this->totalResultats) * 100, 1);
    }

    public function render()
    {
        return view('livewire.resultats.resultats-verification', [
            'examen' => $this->examen,
            'sessionActive' => $this->sessionActive,
            'niveaux' => $this->niveaux,
            'parcours' => $this->parcours,
            'ecs' => $this->ecs,
            'resultats' => $this->resultats,
            'showVerification' => $this->showVerification,
            'printMode' => $this->printMode,
            'totalResultats' => $this->totalResultats,
            'resultatsVerifies' => $this->resultatsVerifies,
            'resultatsNonVerifies' => $this->resultatsNonVerifies,
            'pourcentageVerification' => $this->pourcentageVerification,
            'etapeFusion' => $this->etapeFusion
        ]);
    }
}
