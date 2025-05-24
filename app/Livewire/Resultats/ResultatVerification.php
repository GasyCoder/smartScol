<?php

namespace App\Livewire\Resultats;

use Livewire\Component;
use App\Models\ResultatFusion;
use App\Models\Examen;
use App\Models\Copie;
use App\Models\EC;
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

                $copie = Copie::where('examen_id', $resultat->examen_id)
                    ->where('ec_id', $resultat->ec_id)
                    ->where('code_anonymat_id', $resultat->code_anonymat_id)
                    ->first();

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

                return [
                    'id' => $resultat->id,
                    'numero_ordre' => $index + 1,
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
                    'ue_id' => $ue->id ?? null,
                    'ue_nom' => $ue->nom ?? 'N/A',
                    'moyenne_ue' => $moyenneUE,
                    'created_at' => $copie->created_at ?? null,
                    'updated_at' => $copie->updated_at ?? null,
                ];
            });

        if ($this->orderBy && !$resultatsTransformes->isEmpty()) {
            $champTri = $this->orderBy;
            $ordreAscendant = $this->orderAsc;

            $resultatsTransformes = $resultatsTransformes->sort(function ($a, $b) use ($champTri, $ordreAscendant) {
                $valeurA = $a[$champTri] ?? '';
                $valeurB = $b[$champTri] ?? '';

                if ($champTri === 'moyenne_ue' && ($valeurA === null || $valeurB === null)) {
                    // Gérer les valeurs null pour moyenne_ue (placer à la fin)
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
                $copiesIds = collect($this->resultats)
                    ->where('is_checked', false)
                    ->pluck('copie_id')
                    ->filter();

                if ($copiesIds->isNotEmpty()) {
                    Copie::whereIn('id', $copiesIds)
                        ->update([
                            'is_checked' => true,
                            'updated_at' => now()
                        ]);

                    ResultatFusion::whereIn('copie_id', $copiesIds)
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

    public function startEditing($index)
    {
        $this->editingRow = $index;
        $resultat = $this->resultats[$index];
        $this->newNote = $resultat['note'];
        $this->observation = $resultat['commentaire'] ?? '';
    }

    public function cancelEditing()
    {
        $this->editingRow = null;
        $this->newNote = null;
        $this->observation = '';
    }

    public function saveChanges($index)
    {
        try {
            if (!isset($this->resultats[$index])) {
                throw new \Exception("Index de résultat invalide: {$index}");
            }

            $resultatData = $this->resultats[$index];

            if (!isset($resultatData['copie_id']) || !$resultatData['copie_id']) {
                throw new \Exception("Aucune copie source trouvée");
            }

            if (!is_numeric($this->newNote) || $this->newNote < 0 || $this->newNote > 20) {
                throw new \Exception("La note doit être un nombre entre 0 et 20");
            }

            $copie = Copie::findOrFail($resultatData['copie_id']);
            $resultatFusion = ResultatFusion::findOrFail($resultatData['id']);

            DB::transaction(function () use ($copie, $resultatFusion) {
                if ($copie->note != $this->newNote) {
                    $copie->note_old = $copie->note;
                }

                $copie->note = $this->newNote;
                $copie->commentaire = $this->observation;
                $copie->is_checked = true;
                $copie->save();

                $resultatFusion->note = $this->newNote;
                $resultatFusion->statut = $this->etapeFusion == 1 ? ResultatFusion::STATUT_VERIFY_1 : ResultatFusion::STATUT_VERIFY_2;
                $resultatFusion->save();
            });

            $this->loadResultats();
            $this->cancelEditing();
            toastr()->success('Note vérifiée et mise à jour avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur dans le processus de vérification', ['message' => $e->getMessage()]);
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
