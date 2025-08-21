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

    

    public $statistiquesPresence = [];
    public $afficherInfosPresence = true; 

    public function __construct()
    {
        $this->fusionService = app(FusionService::class);
        $this->calculService = app(CalculAcademiqueService::class);
    }

    public function mount($examenId)
    {
        $this->examenId = $examenId;
        $this->afficherMoyennesUE = session('afficher_moyennes_ue', false);

        // Code existant inchangé jusqu'à loadEcs()
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
        
        // ✅ NOUVEAU : Charger les données de présence
        $this->chargerDonneesPresence();
        
        $this->loadResultats();
    }


    public function updatedAfficherMoyennesUE($value)
    {
        // Sauvegarder l'état dans la session
        session(['afficher_moyennes_ue' => $value]);
        
        // Recharger les résultats pour appliquer le changement
        $this->loadResultats();
        
        // Émettre un événement pour le JavaScript (optionnel)
        $this->dispatch('moyennesUEToggled', $value);
        
        // Message de feedback
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
        } elseif ($this->niveau_id) {
            $this->ecs = EC::whereHas('ue', function($query) {
                $query->where('niveau_id', $this->niveau_id);
            })
            ->orderBy('id', 'asc')
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
        // CORRECTION : Filtrer par session active pour éviter les conflits entre sessions
        $this->etapeFusion = ResultatFusion::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
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

        // CORRECTION : Filtrer par session active
        $resultatsEtudiant = ResultatFusion::where('examen_id', $this->examenId)
            ->where('etudiant_id', $etudiantId)
            ->where('etape_fusion', $this->etapeFusion)
            ->where('session_exam_id', $this->sessionActive->id)
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
            // CORRECTION : Suppression de la condition sur session_id
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

        // CORRECTION : Ajout du filtre par session active
        $query = ResultatFusion::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
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

                // CORRECTION : Filtrer les copies par session active
                $copie = Copie::where('examen_id', $resultat->examen_id)
                    ->where('ec_id', $resultat->ec_id)
                    ->where('code_anonymat_id', $resultat->code_anonymat_id)
                    ->where('session_exam_id', $this->sessionActive->id)
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
                            ->where('session_exam_id', $this->sessionActive->id)
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
                    'ue_abr' => $ue->abr ?? 'UE',
                    'ue_credits' => $ue->credits ?? 0,
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

        // Code existant pour le calcul de base...
        $totalQuery = ResultatFusion::where('examen_id', $this->examenId)
            ->where('session_exam_id', $this->sessionActive->id)
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

        // ✅ NOUVEAU : Recharger les données de présence si nécessaire
        if (empty($this->statistiquesPresence)) {
            $this->chargerDonneesPresence();
        }
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

                // CORRECTION : Ajouter le filtre session_exam_id
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
                'observation' => $this->observation,
                'session_exam_id' => $this->sessionActive->id
            ]);

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
                    'is_checked' => true,
                    'commentaire' => $this->observation
                ]);

                Log::info('Création nouvelle copie lors de la modification', [
                    'resultat_fusion_id' => $resultatFusion->id,
                    'nouvelle_note' => $this->newNote,
                    'session_exam_id' => $this->sessionActive->id,
                    'raison' => 'Copie inexistante pour ce triplet (examen_id, ec_id, code_anonymat_id, session_exam_id)'
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
                    'session_exam_id' => $this->sessionActive->id,
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
            // Préparer les données avec présence
            $resultatsEnrichis = $this->prepareDataForExport();

            // ✅ NOUVEAU : Ajouter les métadonnées de présence
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
                ]
            ];

            $filename = $this->generateFilename('xlsx');

            return Excel::download(
                new ResultatsVerificationExport(
                    $resultatsEnrichis, 
                    $this->examen, 
                    $this->afficherMoyennesUE,
                    $metadonneesPresence // ✅ NOUVEAU paramètre
                ),
                $filename
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export Excel avec présence', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'examen_id' => $this->examenId,
                'session_exam_id' => $this->sessionActive->id,
                'user_id' => Auth::id(),
                'statistiques_presence' => $this->statistiquesPresence
            ]);
            toastr()->error('Erreur lors de l\'export Excel : ' . $e->getMessage());
        }
    }

    public function exportPdf($orientation = 'landscape')
    {
        try {
            // Préparer les données avec présence
            $resultatsEnrichis = $this->prepareDataForExport();

            $data = [
                'resultats' => $resultatsEnrichis,
                'examen' => $this->examen,
                'sessionActive' => $this->sessionActive,
                'afficherMoyennesUE' => $this->afficherMoyennesUE,
                'statistiques' => [
                    'total' => $this->totalResultats,
                    'verifiees' => $this->resultatsVerifies,
                    'non_verifiees' => $this->resultatsNonVerifies,
                    'pourcentage_verification' => $this->pourcentageVerification,
                    'avec_moyennes_ue' => $this->afficherMoyennesUE,
                    'etape_fusion' => $this->etapeFusion
                ],
                // ✅ NOUVEAU : Données de présence
                'statistiques_presence' => $this->statistiquesPresence,
                'coherence_presence' => $this->calculerCoherencePresence(),
                'dateExport' => now()->format('d/m/Y H:i:s')
            ];

            $pdf = Pdf::loadView('exports.resultats-verification-pdf', $data)
                    ->setPaper('a4', $orientation);

            $filename = $this->generateFilename('pdf');

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename, ['Content-Type' => 'application/pdf']);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export PDF avec présence', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'examen_id' => $this->examenId,
                'session_exam_id' => $this->sessionActive->id,
                'user_id' => Auth::id(),
                'statistiques_presence' => $this->statistiquesPresence
            ]);
            toastr()->error('Erreur lors de l\'export PDF : ' . $e->getMessage());
        }
    }

    private function generateFilename($extension)
    {
        $niveau = $this->examen->niveau->abr ?? 'NIV';
        $parcours = $this->examen->parcours->abr ?? 'PARC';
        $sessionType = $this->sessionActive->type ?? 'SESSION';
        $date = now()->format('Y-m-d_Hi');

        $suffixeMoyennes = $this->afficherMoyennesUE ? '-avec-moyennes-UE' : '-sans-moyennes';
        
        // ✅ NOUVEAU : Ajouter info présence
        $suffixePresence = '';
        if ($this->statistiquesPresence) {
            $tauxPresence = $this->statistiquesPresence['taux_presence'];
            $suffixePresence = "-presence-{$tauxPresence}pct";
        }

        return "resultats-verification-{$niveau}-{$parcours}-{$sessionType}{$suffixeMoyennes}{$suffixePresence}-{$date}.{$extension}";
    }

    private function prepareDataForExport()
    {
        $resultatsBase = $this->afficherMoyennesUE ? $this->prepareDataWithMoyennesUE() : $this->resultats;
        
        // ✅ NOUVEAU : Ajouter les infos de présence à chaque résultat
        if ($this->statistiquesPresence) {
            $resultatsBase = collect($resultatsBase)->map(function($resultat) {
                $resultat['statistiques_presence'] = $this->statistiquesPresence;
                $resultat['etudiant_est_present'] = true; // Puisqu'il a des résultats, il était présent
                return $resultat;
            })->toArray();
        }
        
        return $resultatsBase;
    }

    /**
     * CORRECTION : Méthode pour obtenir les statistiques détaillées par session
     */
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

    /**
     * CORRECTION : Méthode pour vérifier la cohérence des données par session
     */
    public function verifierCoherenceSession()
    {
        if (!$this->sessionActive || !$this->examen) {
            return false;
        }

        try {
            // Vérifier que les ResultatFusion correspondent à la session active
            $resultatsIncorrects = ResultatFusion::where('examen_id', $this->examenId)
                ->where('session_exam_id', '!=', $this->sessionActive->id)
                ->count();

            // Vérifier que les Copies correspondent à la session active
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

    /**
     * CORRECTION : Méthode pour nettoyer les données orphelines
     */
    public function nettoyerDonneesOrphelines()
    {
        if (!Auth::user()->hasPermissionTo('resultats.admin')) {
            toastr()->error('Permission insuffisante pour cette action');
            return;
        }

        try {
            DB::transaction(function () {
                // Supprimer les ResultatFusion sans session valide
                $orphelinesResultats = ResultatFusion::where('examen_id', $this->examenId)
                    ->whereDoesntHave('sessionExam')
                    ->delete();

                // Supprimer les Copies sans session valide
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

    // ✅ Calculer la cohérence entre présence et résultats
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


    // Export spécial "Rapport de présence"
    public function exportRapportPresence()
    {
        if (!$this->statistiquesPresence) {
            toastr()->error('Aucune donnée de présence disponible pour le rapport.');
            return;
        }

        try {
            // Préparer un rapport détaillé de présence
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



    // Obtenir les détails des étudiants avec présence
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

    // Récupérer les statistiques de présence (similaire à FusionIndex)
    public function getStatistiquesAvecPresence()
    {
        if (!$this->examenId || !$this->sessionActive) {
            return null;
        }

        $sessionId = $this->sessionActive->id;

        // Récupérer les données de présence
        $presenceGlobale = PresenceExamen::where('examen_id', $this->examenId)
            ->where('session_exam_id', $sessionId)
            ->whereNull('ec_id') // Présence globale
            ->first();

        if (!$presenceGlobale) {
            // Fallback : calculer depuis les ResultatFusion
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


    // Obtenir le total d'étudiants inscrits
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


    // Charger les données de présence
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

    //  Obtenir les statistiques détaillées avec présence
    public function getStatistiquesDetailleesAvecPresence()
    {
        $statsBase = $this->getStatistiquesDetaillees();
        
        if ($this->statistiquesPresence) {
            $statsBase['presence'] = $this->statistiquesPresence;
            
            // Calculer des ratios utiles
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


    //  Préparation des données avec moyennes UE (séparée pour clarté)
    private function prepareDataWithMoyennesUE()
    {
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

    //  Toggle affichage des infos de présence
    public function toggleInfosPresence()
    {
        $this->afficherInfosPresence = !$this->afficherInfosPresence;
    }

    //  Diagnostiquer les écarts de présence
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

    public function render()
    {
        // ✅ NOUVEAU : Calculer les statistiques détaillées avec présence
        $statistiquesDetailleesAvecPresence = $this->getStatistiquesDetailleesAvecPresence();
        
        return view('livewire.resultats.resultats-verification', [
            // Données existantes
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
            
            // ✅ NOUVELLES DONNÉES DE PRÉSENCE
            'statistiquesPresence' => $this->statistiquesPresence,
            'afficherInfosPresence' => $this->afficherInfosPresence,
            'statistiquesDetailleesAvecPresence' => $statistiquesDetailleesAvecPresence,
            
            // ✅ NOUVEAU : Indicateurs de qualité des données
            'qualitesDonnees' => [
                'coherence_presence' => $this->calculerCoherencePresence(),
                'source_donnees_fiable' => $this->statistiquesPresence && $this->statistiquesPresence['source'] === 'presence_enregistree',
                'couverture_complete' => $this->totalResultats > 0 && $this->statistiquesPresence && 
                                    ($this->totalResultats >= $this->statistiquesPresence['etudiants_presents'] * count($this->ecs) * 0.8) // 80% de couverture minimum
            ],
            
            // ✅ NOUVEAU : Actions disponibles selon les données de présence
            'actionsDisponibles' => [
                'peut_exporter_rapport_presence' => !empty($this->statistiquesPresence),
                'peut_diagnostiquer_ecarts' => !empty($this->statistiquesPresence) && $this->totalResultats > 0,
                'verification_coherence_possible' => $this->showVerification && !empty($this->statistiquesPresence)
            ]
        ]);
    }
}
