<?php

namespace App\Livewire\Resultats;

use Livewire\Component;
use App\Models\Resultat;
use App\Models\Examen;
use App\Models\Copie;
use App\Models\EC;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\SessionExam;
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

    // Filter properties - STATUT FIXÉ SUR "À VÉRIFIER"
    public $niveau_id;
    public $parcours_id;
    public $ec_id;
    // SUPPRIMÉ: public $statut = 'provisoire'; - Plus de filtre statut nécessaire
    public $search = '';
    public $niveaux = [];
    public $parcours = [];
    public $ecs = [];
    public $sessionActive = null;

    // Sorting properties
    public $orderBy = 'matricule';
    public $orderAsc = true;
    public $examen;

    // Print mode
    public $printMode = false;

    // Statistiques pour l'affichage
    public $totalResultats = 0;
    public $resultatsVerifies = 0;
    public $resultatsNonVerifies = 0;

    /**
     * Initialisation du composant - LOGIQUE SIMPLIFIÉE POUR VÉRIFICATION UNIQUEMENT
     */
    public function mount($examenId)
    {
        $this->examenId = $examenId;

        // Charger l'examen avec ses relations
        $this->examen = Examen::with(['niveau', 'parcours'])->find($this->examenId);

        if (!$this->examen) {
            toastr()->error('Examen non trouvé.');
            return;
        }

        // Charger la session active
        $this->sessionActive = SessionExam::where('is_active', true)
            ->where('is_current', true)
            ->with('anneeUniversitaire')
            ->first();

        if (!$this->sessionActive) {
            toastr()->error('Aucune session active trouvée. Veuillez configurer une session active dans les paramètres.');
            return;
        }

        // Charger les niveaux pour les filtres
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();

        // Pré-remplir les filtres avec les valeurs de l'examen
        $this->niveau_id = $this->examen->niveau_id;
        $this->parcours_id = $this->examen->parcours_id;
        $this->loadParcours();
        $this->loadEcs();

        // Vérifier l'état de fusion et charger les résultats
        $this->checkEtapeFusion();
        $this->loadResultats();
    }

    /**
     * Charger les parcours disponibles selon le niveau sélectionné
     */
    public function loadParcours()
    {
        $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Charger les éléments constitutifs (EC) liés à l'examen
     */
    public function loadEcs()
    {
        if (!$this->examenId) {
            $this->ecs = collect();
            return;
        }

        $this->ecs = EC::whereHas('examens', function ($query) {
            $query->where('examens.id', $this->examenId);
        })->get();
    }

    /**
     * Réactions aux changements de filtres - SUPPRESSION DU STATUT
     */
    public function updatedNiveauId()
    {
        // Vérification si le niveau sélectionné correspond à l'examen
        if ($this->niveau_id && $this->niveau_id != $this->examen->niveau_id) {
            toastr()->warning(
                'Le niveau sélectionné ne correspond pas à l\'examen en cours. ' .
                'Niveau de l\'examen : ' . ($this->examen->niveau->nom ?? 'Non défini')
            );

            Log::warning('Niveau non correspondant sélectionné', [
                'examen_id' => $this->examenId,
                'niveau_examen' => $this->examen->niveau_id,
                'niveau_selectionne' => $this->niveau_id,
                'nom_niveau_examen' => $this->examen->niveau->nom ?? 'Non défini'
            ]);
        }

        $this->parcours_id = null;
        $this->ec_id = null;
        $this->loadParcours();
        $this->loadResultats();
    }


    public function updatedParcoursId()
    {
        // Vérification si le parcours sélectionné correspond à l'examen
        if ($this->parcours_id && $this->parcours_id != $this->examen->parcours_id) {
            toastr()->warning(
                'Le parcours sélectionné ne correspond pas à l\'examen en cours. ' .
                'Parcours de l\'examen : ' . ($this->examen->parcours->nom ?? 'Non défini')
            );

            Log::warning('Parcours non correspondant sélectionné', [
                'examen_id' => $this->examenId,
                'parcours_examen' => $this->examen->parcours_id,
                'parcours_selectionne' => $this->parcours_id,
                'nom_parcours_examen' => $this->examen->parcours->nom ?? 'Non défini'
            ]);
        }

        $this->ec_id = null;
        $this->loadEcs();
        $this->loadResultats();
    }

    public function resetToExamenValues()
    {
        $this->niveau_id = $this->examen->niveau_id;
        $this->parcours_id = $this->examen->parcours_id;
        $this->ec_id = null;

        $this->loadParcours();
        $this->loadEcs();
        $this->loadResultats();

        toastr()->success('Filtres remis aux valeurs de l\'examen');
    }


    public function updatedEcId()
    {
        $this->loadResultats();
        $this->calculerStatistiques();
    }

    public function updatedSearch()
    {
        $this->loadResultats();
        $this->calculerStatistiques();
    }

    /**
     * Gestion du tri des colonnes
     */
    public function toggleOrder($column)
    {
        if ($this->orderBy === $column) {
            $this->orderAsc = !$this->orderAsc;
        } else {
            $this->orderBy = $column;
            $this->orderAsc = true;
        }
        $this->loadResultats();
    }

    /**
     * Basculer entre mode normal et mode impression
     */
    public function togglePrintMode()
    {
        $this->printMode = !$this->printMode;
    }

    /**
     * Vérifier l'étape de fusion - UNIQUEMENT POUR RÉSULTATS À VÉRIFIER
     */
    public function checkEtapeFusion()
    {
        $this->etapeFusion = Resultat::where('examen_id', $this->examenId)
            ->max('etape_fusion') ?? 0;

        $this->showVerification = $this->etapeFusion >= 1;

        if (!$this->showVerification) {
            toastr()->info('La fusion doit être effectuée avant de pouvoir vérifier les résultats.');
        }
    }

    /**
     * Charger UNIQUEMENT les résultats "À vérifier" (statut provisoire)
     */
    public function loadResultats()
    {
        if (!$this->showVerification) {
            $this->resultats = [];
            return;
        }

        // Vérification de cohérence avant chargement
        $incohernceDetectee = false;
        $messageIncoherence = [];

        if ($this->niveau_id && $this->niveau_id != $this->examen->niveau_id) {
            $incohernceDetectee = true;
            $messageIncoherence[] = 'Niveau différent de l\'examen';
        }

        if ($this->parcours_id && $this->parcours_id != $this->examen->parcours_id) {
            $incohernceDetectee = true;
            $messageIncoherence[] = 'Parcours différent de l\'examen';
        }

        if ($incohernceDetectee) {
            Log::info('Chargement avec filtres incohérents', [
                'examen_id' => $this->examenId,
                'incoherences' => $messageIncoherence,
                'niveau_examen' => $this->examen->niveau_id,
                'parcours_examen' => $this->examen->parcours_id,
                'niveau_filtre' => $this->niveau_id,
                'parcours_filtre' => $this->parcours_id
            ]);
        }

        // Continuer avec le chargement normal...
        Log::info('Chargement des résultats à vérifier', [
            'examen_id' => $this->examenId,
            'ec_id' => $this->ec_id,
            'search' => $this->search,
            'coherence_filtres' => !$incohernceDetectee
        ]);

        // REQUÊTE FIXÉE SUR STATUT PROVISOIRE UNIQUEMENT
        $query = Resultat::where('examen_id', $this->examenId)
            ->where('statut', Resultat::STATUT_PROVISOIRE)
            ->where('etape_fusion', 1)
            ->with(['etudiant', 'ec']);

        // Application des filtres utilisateur
        if ($this->ec_id) {
            $query->where('ec_id', $this->ec_id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('etudiant', function ($q) {
                    $q->where('matricule', 'like', '%' . $this->search . '%')
                        ->orWhere('nom', 'like', '%' . $this->search . '%')
                        ->orWhere('prenom', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Récupération et transformation des données
        $resultatsTransformes = $query->get()
            ->map(function ($resultat, $index) {
                $etudiant = $resultat->etudiant;
                $ec = $resultat->ec;

                // Recherche de la copie correspondante
                $copie = Copie::where('examen_id', $resultat->examen_id)
                    ->where('ec_id', $resultat->ec_id)
                    ->where('code_anonymat_id', $resultat->code_anonymat_id)
                    ->first();

                // Logique de source de note pour la vérification
                $noteAffichee = $resultat->note;
                $sourceNote = 'resultats';

                if ($copie && $copie->is_checked) {
                    $noteAffichee = $copie->note;
                    $sourceNote = 'copies';
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
                    'created_at' => $copie->created_at ?? null,
                    'updated_at' => $copie->updated_at ?? null,
                ];
            });

        // Application du tri
        if ($this->orderBy && !$resultatsTransformes->isEmpty()) {
            $champTri = $this->orderBy;
            $ordreAscendant = $this->orderAsc;

            $resultatsTransformes = $resultatsTransformes->sort(function ($a, $b) use ($champTri, $ordreAscendant) {
                $valeurA = $a[$champTri] ?? '';
                $valeurB = $b[$champTri] ?? '';

                if (is_numeric($valeurA) && is_numeric($valeurB)) {
                    $comparaison = $valeurA <=> $valeurB;
                } else {
                    $comparaison = strcasecmp($valeurA, $valeurB);
                }

                return $ordreAscendant ? $comparaison : -$comparaison;
            });
        }

        // Recalcul des numéros d'ordre après tri
        $this->resultats = $resultatsTransformes->values()->map(function ($resultat, $index) {
            $resultat['numero_ordre'] = $index + 1;
            return $resultat;
        })->toArray();

        Log::info('Résultats à vérifier chargés', [
            'total_resultats' => count($this->resultats),
            'coherence_filtres' => !$incohernceDetectee
        ]);
    }

    /**
     * NOUVELLE MÉTHODE : Calculer les statistiques de vérification
     */
    public function calculerStatistiques()
    {
        if (!$this->showVerification) {
            $this->totalResultats = 0;
            $this->resultatsVerifies = 0;
            $this->resultatsNonVerifies = 0;
            return;
        }

        // Statistiques globales (tous les résultats à vérifier)
        $totalQuery = Resultat::where('examen_id', $this->examenId)
            ->where('statut', Resultat::STATUT_PROVISOIRE)
            ->where('etape_fusion', 1);

        // Appliquer les mêmes filtres que pour l'affichage
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

        // Compter les résultats vérifiés (copies avec is_checked = true)
        $resultatsIds = $totalQuery->pluck('id');
        $this->resultatsVerifies = collect($this->resultats)->where('is_checked', true)->count();
        $this->resultatsNonVerifies = $this->totalResultats - $this->resultatsVerifies;
    }

    /**
     * NOUVELLE MÉTHODE : Marquer tous les résultats filtrés comme vérifiés
     */
    public function marquerTousVerifies()
    {
        if (!Auth::user()->hasPermissionTo('resultats.verify')) {
            toastr()->error('Permission insuffisante pour cette action');
            return;
        }

        try {
            DB::transaction(function () {
                // Récupérer tous les IDs des copies correspondant aux résultats affichés
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

                    Log::info('Marquage en lot comme vérifiés', [
                        'copies_ids' => $copiesIds->toArray(),
                        'count' => $copiesIds->count()
                    ]);
                }
            });

            $this->loadResultats();
            $this->calculerStatistiques();

            toastr()->success('Tous les résultats affichés ont été marqués comme vérifiés');

        } catch (\Exception $e) {
            Log::error('Erreur marquage en lot', [
                'error' => $e->getMessage()
            ]);
            toastr()->error('Erreur lors du marquage: ' . $e->getMessage());
        }
    }

    /**
     * Démarrer l'édition d'une ligne spécifique
     */
    public function startEditing($index)
    {
        $this->editingRow = $index;
        $resultat = $this->resultats[$index];

        $this->newNote = $resultat['note'];
        $this->observation = $resultat['commentaire'] ?? '';
    }

    /**
     * Annuler l'édition
     */
    public function cancelEditing()
    {
        $this->editingRow = null;
        $this->newNote = null;
        $this->observation = '';
    }

    /**
     * Sauvegarder les modifications - LOGIQUE INCHANGÉE
     */
    public function saveChanges($index)
    {
        Log::info('Début du processus de vérification', [
            'index' => $index,
            'newNote' => $this->newNote
        ]);

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

            DB::transaction(function () use ($copie) {
                if ($copie->note != $this->newNote) {
                    $copie->note_old = $copie->note;
                }

                $copie->note = $this->newNote;
                $copie->commentaire = $this->observation;
                $copie->is_checked = true;
                $copie->save();
            });

            $this->loadResultats();
            $this->calculerStatistiques();
            $this->cancelEditing();

            toastr()->success('Note vérifiée et mise à jour avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur dans le processus de vérification', [
                'message' => $e->getMessage()
            ]);

            toastr()->error('Erreur lors de la vérification: ' . $e->getMessage());
            $this->cancelEditing();
        }
    }

    /**
     * NOUVELLE MÉTHODE : Obtenir le pourcentage de vérification
     */
    public function getPourcentageVerificationProperty()
    {
        if ($this->totalResultats === 0) {
            return 0;
        }

        return round(($this->resultatsVerifies / $this->totalResultats) * 100, 1);
    }

    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.resultats.resultats-verification');
    }
}
