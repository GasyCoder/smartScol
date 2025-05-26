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
use App\Models\ResultatFusion;
use App\Services\FusionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\CalculAcademiqueService;
use App\Exports\ResultatsVerificationExport;
/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 */
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
    public $totalResultats = 0;
    public $resultatsVerifies = 0;
    public $resultatsNonVerifies = 0;
    public $pourcentageVerification = 0;
    public $noExamenFound = false;
    public $afficherMoyennesUE = false;
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

    public function checkEtapeFusion()
    {
        $this->etapeFusion = ResultatFusion::where('examen_id', $this->examenId)
            ->max('etape_fusion') ?? 0;

        $this->showVerification = $this->etapeFusion >= 1;

        if (!$this->showVerification) {
            toastr()->info('La fusion initiale doit être effectuée avant de pouvoir vérifier les résultats.');
        }
    }

    /**
     * Calcule les moyennes UE pour un étudiant
     */
    private function calculerMoyennesUEEtudiant($etudiantId)
    {
        $moyennesUE = [];

        // Récupérer tous les résultats de l'étudiant pour cet examen
        $resultatsEtudiant = ResultatFusion::where('examen_id', $this->examenId)
            ->where('etudiant_id', $etudiantId)
            ->where('etape_fusion', $this->etapeFusion)
            ->with(['ec', 'ec.ue'])
            ->get();

        // Grouper par UE
        $resultatsParUE = $resultatsEtudiant->groupBy(function($resultat) {
            return $resultat->ec->ue->id ?? null;
        });

        foreach ($resultatsParUE as $ueId => $resultatsUE) {
            if (!$ueId) continue;

            $ue = $resultatsUE->first()->ec->ue;

            // Vérifier s'il y a une note éliminatoire (0)
            $noteEliminatoire = $resultatsUE->where('note', 0)->isNotEmpty();

            // Calculer la moyenne UE : somme des notes EC / nombre d'EC
            $notesUE = $resultatsUE->pluck('note')->filter(function($note) {
                return $note !== null && is_numeric($note);
            });

            if ($notesUE->isNotEmpty()) {
                $moyenneUE = $notesUE->avg();

                // Appliquer les règles métier
                $ueValidee = $moyenneUE >= 10 && !$noteEliminatoire;
                $creditsObtenus = $ueValidee ? ($ue->credit ?? 0) : 0;

                $moyennesUE[$ueId] = [
                    'nom' => $ue->nom,
                    'moyenne' => $moyenneUE,
                    'coefficient' => $ue->coefficient ?? 1,
                    'credit' => $ue->credit ?? 0,
                    'credits_obtenus' => $creditsObtenus,
                    'validee' => $ueValidee,
                    'note_eliminatoire' => $noteEliminatoire,
                    'nb_ec' => $notesUE->count()
                ];
            }
        }

        return $moyennesUE;
    }


    /**
     * Calcule la moyenne générale d'un étudiant
     */
    private function calculerMoyenneGeneraleEtudiant($etudiantId)
    {
        // Récupérer les moyennes UE de l'étudiant
        $moyennesUE = $this->calculerMoyennesUEEtudiant($etudiantId);

        if (empty($moyennesUE)) {
            return null;
        }

        // Calculer selon votre logique : somme des moyennes UE / nombre d'UE
        $sommesMoyennesUE = 0;
        $nombreUE = 0;

        foreach ($moyennesUE as $donneesUE) {
            if ($donneesUE['moyenne'] !== null && is_numeric($donneesUE['moyenne'])) {
                $sommesMoyennesUE += $donneesUE['moyenne'];
                $nombreUE++;
            }
        }

        return $nombreUE > 0 ? $sommesMoyennesUE / $nombreUE : null;
    }

    public function loadResultats()
    {
        $this->noExamenFound = false;
        $this->resultats = [];

        if ($this->niveau_id && $this->parcours_id) {
            $examenExists = Examen::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->where('session_id', $this->sessionActive->id)
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

                // Calcul de la moyenne UE
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

                // Calcul de la moyenne générale pour cet étudiant
                $moyenneGenerale = $this->calculerMoyenneGeneraleEtudiant($etudiant->id);
                return [
                    'id' => $resultat->id,
                    'unique_key' => "rf_{$resultat->id}",
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
                    'ec_id' => $resultat->ec_id,
                    'code_anonymat_id' => $resultat->code_anonymat_id,
                    'ue_id' => $ue->id ?? null,
                    'ue_nom' => $ue->nom ?? 'N/A',
                    'ue_abr' => $ue->abr ?? 'UE',  // AJOUT
                    'ue_credits' => $ue->credits ?? 0,  // AJOUT
                    'moyenne_ue' => $moyenneUE,
                    'moyenne_generale' => $moyenneGenerale,
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

                if (in_array($champTri, ['moyenne_ue', 'moyenne_generale']) && ($valeurA === null || $valeurB === null)) {
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
            $this->pourcentageVerification = 0;
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
        $this->pourcentageVerification = $this->totalResultats === 0 ? 0 :
            round(($this->resultatsVerifies / $this->totalResultats) * 100, 1);
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
            'etape_fusion' => $this->etapeFusion
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

            $resultatFusion = ResultatFusion::with(['etudiant', 'ec'])->findOrFail($resultatData['id']);

            if (!$resultatFusion) {
                throw new \Exception("ResultatFusion non trouvé avec l'ID: {$resultatData['id']}");
            }

            Log::info('Tentative de sauvegarde modification individuelle', [
                'unique_key' => $uniqueKey,
                'resultat_fusion_id' => $resultatFusion->id,
                'etudiant_id' => $resultatFusion->etudiant_id,
                'etudiant_nom' => $resultatFusion->etudiant->nom ?? 'N/A',
                'etudiant_prenom' => $resultatFusion->etudiant->prenom ?? 'N/A',
                'ec_id' => $resultatFusion->ec_id,
                'ec_nom' => $resultatFusion->ec->nom ?? 'N/A',
                'code_anonymat_id' => $resultatFusion->code_anonymat_id,
                'nouvelle_note' => $this->newNote,
                'ancienne_note' => $resultatData['note'],
                'observation' => $this->observation
            ]);

            $copie = Copie::where('examen_id', $resultatFusion->examen_id)
                ->where('ec_id', $resultatFusion->ec_id)
                ->where('code_anonymat_id', $resultatFusion->code_anonymat_id)
                ->first();

            if (!$copie) {
                $copie = new Copie([
                    'examen_id' => $resultatFusion->examen_id,
                    'ec_id' => $resultatFusion->ec_id,
                    'code_anonymat_id' => $resultatFusion->code_anonymat_id,
                    'note' => $this->newNote,
                    'saisie_par' => Auth::id(),
                    'is_checked' => true,
                    'commentaire' => $this->observation
                ]);

                Log::info('Création nouvelle copie lors de la modification', [
                    'resultat_fusion_id' => $resultatFusion->id,
                    'nouvelle_note' => $this->newNote,
                    'raison' => 'Copie inexistante pour ce triplet (examen_id, ec_id, code_anonymat_id)'
                ]);
            }

            DB::transaction(function () use ($copie, $resultatFusion) {
                $copie->marquerCommeModifiee($this->newNote, $this->observation);
                $resultatFusion->mettreAJourNote($this->newNote, Auth::id(), $this->etapeFusion);
                $resultatFusion->marquerCommeVerifie($this->etapeFusion, Auth::id());

                Log::info('Sauvegarde modification individuelle réussie', [
                    'copie_id' => $copie->id,
                    'resultat_fusion_id' => $resultatFusion->id,
                    'note_appliquee' => $this->newNote,
                    'statut_applique' => $resultatFusion->statut,
                    'user_id' => Auth::id()
                ]);
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

    public function getPourcentageVerificationProperty()
    {
        return $this->pourcentageVerification;
    }

    public function exportExcel()
    {
        try {
            // Préparer les données avec moyennes UE SI le switch est activé
            $resultatsEnrichis = $this->prepareDataForExport();

            $filename = $this->generateFilename('xlsx');

            return Excel::download(
                new ResultatsVerificationExport($resultatsEnrichis, $this->examen, $this->afficherMoyennesUE),
                $filename
            );

        } catch (\Exception $e) {
            toastr()->error('Erreur lors de l\'export Excel : ' . $e->getMessage());
        }
    }

    public function exportPdf($orientation = 'landscape')
    {
        try {
            // Préparer les données avec moyennes UE SI le switch est activé
            $resultatsEnrichis = $this->prepareDataForExport();

            $data = [
                'resultats' => $resultatsEnrichis,
                'examen' => $this->examen,
                'afficherMoyennesUE' => $this->afficherMoyennesUE,
                'statistiques' => [
                    'total' => $this->totalResultats,
                    'verifiees' => $this->resultatsVerifies,
                    'non_verifiees' => $this->resultatsNonVerifies,
                    'pourcentage_verification' => $this->pourcentageVerification,
                    'avec_moyennes_ue' => $this->afficherMoyennesUE
                ],
                'dateExport' => now()->format('d/m/Y H:i:s')
            ];

            $pdf = Pdf::loadView('exports.resultats-verification-pdf', $data)
                    ->setPaper('a4', $orientation);

            $filename = $this->generateFilename('pdf');

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename, ['Content-Type' => 'application/pdf']);

        } catch (\Exception $e) {
            toastr()->error('Erreur lors de l\'export PDF : ' . $e->getMessage());
        }
    }


    private function generateFilename($extension)
    {
        $niveau = $this->examen->niveau->abr ?? 'NIV';
        $parcours = $this->examen->parcours->abr ?? 'PARC';
        $date = now()->format('Y-m-d_Hi');

        $suffixe = $this->afficherMoyennesUE ? '-avec-moyennes-UE' : '-sans-moyennes';

        return "resultats-verification-{$niveau}-{$parcours}{$suffixe}-{$date}.{$extension}";
    }


    private function prepareDataForExport()
    {
        if (!$this->afficherMoyennesUE) {
            // Si switch désactivé, retourner les données normales
            return $this->resultats;
        }

        // Si switch activé, enrichir avec moyennes UE
        $resultatsGroupes = collect($this->resultats)->groupBy('matricule');
        $resultatsEnrichis = [];

        foreach ($resultatsGroupes as $matricule => $resultatsEtudiant) {
            $premierResultat = $resultatsEtudiant->first();
            $etudiantId = $premierResultat['etudiant_id'];

            // Calculer les moyennes UE pour cet étudiant
            $moyennesUE = $this->calculerMoyennesUEEtudiant($etudiantId);
            $moyenneGenerale = $this->calculerMoyenneGeneraleEtudiant($etudiantId);

            foreach ($resultatsEtudiant as $resultat) {
                // Ajouter la moyenne UE pour ce résultat
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


    public function render()
    {
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
        ]);
    }
}
