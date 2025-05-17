<?php

namespace App\Livewire\UEEC;

use App\Models\UE;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Exports\UEECExport;
use App\Imports\UEECImport;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Exports\UEECTemplateExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class UniteElement extends Component
{
    use WithFileUploads, WithPagination;

    // Utiliser queryString pour persister ces variables dans l'URL
    // avec des alias plus courts et propres
    protected $queryString = [
        'step' => ['except' => 'niveau'],
        'niveauId' => ['except' => '', 'as' => 'niveau'],
        'parcoursId' => ['except' => '', 'as' => 'parcours'],
    ];

    // Variables pour la navigation
    public $step = 'niveau'; // 'niveau', 'parcours', ou 'ue'

    public $niveauId = '';

    public $parcoursId = '';

    // Variables d'état pour l'affichage
    public $niveauInfo = null;

    public $parcoursInfo = null;

    public $parcours = [];

    // Variable pour l'importation
    public $importFile = null;

    // Variables pour la recherche et le tri
    public $search = '';

    public $sortField = 'abr';

    public $sortDirection = 'asc';
    public $showDeleteModal = false;
    public $ueId = null;

    public function mount()
    {
        // Au chargement initial, restaurer l'état en fonction des paramètres d'URL
        $this->loadDataFromQueryParams();
    }

    /**
     * Nettoie une valeur d'entrée, gérant les valeurs complexes comme les tableaux
     *
     * @param  mixed  $value  La valeur à nettoyer
     * @return mixed La valeur nettoyée
     */
    private function cleanInputValue($value)
    {
        // Si la valeur est un tableau (comme dans niveauId[value]=1)
        if (is_array($value) && isset($value['value'])) {
            return $value['value'];
        }

        return $value;
    }

    /**
     * Charge les données initiales à partir des paramètres d'URL
     */
    private function loadDataFromQueryParams()
    {
        // Nettoyer les valeurs des paramètres
        $this->niveauId = $this->cleanInputValue($this->niveauId);
        $this->parcoursId = $this->cleanInputValue($this->parcoursId);

        // Charger les données du niveau si nécessaire
        if ($this->niveauId) {
            $this->loadNiveauData($this->niveauId);
        }

        // Charger les données du parcours si nécessaire
        if ($this->parcoursId) {
            $this->loadParcoursData($this->parcoursId);
        }
    }

    /**
     * Charge les données d'un niveau de manière sécurisée
     *
     * @param  int  $niveauId  L'ID du niveau à charger
     * @return bool True si le chargement a réussi, false sinon
     */
    private function loadNiveauData($niveauId)
    {
        try {
            $niveau = Niveau::where('id', $niveauId)->first();

            if ($niveau) {
                $this->niveauInfo = [
                    'id' => $niveau->id,
                    'nom' => $niveau->nom,
                    'abr' => $niveau->abr,
                ];

                $this->parcours = Parcour::where('niveau_id', $niveauId)
                    ->where('is_active', true)
                    ->get();

                return true;
            }
        } catch (\Exception $e) {
            // Log l'erreur si nécessaire
            Log::error('Erreur lors du chargement des données du niveau: '.$e->getMessage());
        }

        return false;
    }

    /**
     * Charge les données d'un parcours de manière sécurisée
     *
     * @param  int  $parcoursId  L'ID du parcours à charger
     * @return bool True si le chargement a réussi, false sinon
     */
    private function loadParcoursData($parcoursId)
    {
        try {
            $parcours = Parcour::where('id', $parcoursId)->first();

            if ($parcours) {
                $this->parcoursInfo = [
                    'id' => $parcours->id,
                    'nom' => $parcours->nom,
                    'abr' => $parcours->abr,
                ];

                // Si le niveau n'est pas déjà chargé, chargez-le maintenant
                if (! $this->niveauInfo && $parcours->niveau_id) {
                    $this->niveauId = $parcours->niveau_id;
                    $this->loadNiveauData($parcours->niveau_id);
                }

                return true;
            }
        } catch (\Exception $e) {
            // Log l'erreur si nécessaire
            Log::error('Erreur lors du chargement des données du parcours: '.$e->getMessage());
        }

        return false;
    }

    /**
     * Méthode pour le tri des colonnes
     *
     * @param  string  $field  Le champ sur lequel trier
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Retourne à l'étape de sélection du niveau
     */
    public function retourANiveau()
    {
        $this->reset(['step', 'niveauId', 'parcoursId', 'niveauInfo', 'parcoursInfo']);
        $this->step = 'niveau';
    }

    /**
     * Retourne à l'étape de sélection du parcours
     */
    public function retourAParcours()
    {
        $this->reset(['parcoursId', 'parcoursInfo']);
        $this->step = 'parcours';
    }

    /**
     * Gère la mise à jour de la sélection du niveau
     *
     * @param  mixed  $value  La valeur sélectionnée
     */
    public function updatedNiveauId($value)
    {
        // Nettoyer la valeur
        $value = $this->cleanInputValue($value);
        $this->niveauId = $value;

        if ($value) {
            $this->parcoursId = '';
            $this->parcoursInfo = null;

            // Utiliser la méthode sécurisée pour charger les données du niveau
            if ($this->loadNiveauData($value)) {
                $this->step = 'parcours';
            }
        } else {
            $this->reset(['niveauInfo', 'parcours', 'parcoursId', 'parcoursInfo']);
        }
    }

    /**
     * Gère la mise à jour de la sélection du parcours
     *
     * @param  mixed  $value  La valeur sélectionnée
     */
    public function updatedParcoursId($value)
    {
        // Nettoyer la valeur
        $value = $this->cleanInputValue($value);
        $this->parcoursId = $value;

        if ($value) {
            // Utiliser la méthode sécurisée pour charger les données du parcours
            if ($this->loadParcoursData($value)) {
                $this->step = 'ue';
            }
        } else {
            $this->parcoursInfo = null;
        }
    }

    /**
     * Importe des UEs et ECs depuis un fichier Excel
     */
    public function importUEs()
    {
        // Validation du fichier
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            // Créer une instance de l'importation
            $import = new UEECImport($this->niveauId, $this->parcoursId);

            // Importer directement
            Excel::import($import, $this->importFile);

            // Récupérer les compteurs (si disponibles)
            $counts = $import->getImportCounts();

            // Réinitialiser le fichier et afficher un message de succès
            $this->importFile = null;
            toastr()->success('Les UEs et ECs ont été importés avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation des UEs et ECs: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de l\'importation: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new UEECTemplateExport, 'ue_ec_template.xlsx');
    }

    /**
     * Exporte les UEs et ECs au format Excel
     */
    public function exportUEs()
    {
        try {
            if (!$this->niveauId || !$this->parcoursId) {
                toastr()->error('Veuillez sélectionner un niveau et un parcours avant d\'exporter.');
                return;
            }

            $niveau = Niveau::find($this->niveauId);
            $parcours = Parcour::find($this->parcoursId);

            if (!$niveau || !$parcours) {
                toastr()->error('Niveau ou parcours non trouvé.');
                return;
            }

            // Vérifier qu'il y a des UEs à exporter
            $count = UE::where('niveau_id', $this->niveauId)
                ->where('parcours_id', $this->parcoursId)
                ->count();

            if ($count === 0) {
                toastr()->info('Aucune UE à exporter pour ce niveau et parcours.');
                return;
            }

            $filename = 'ue_ec_' . $niveau->abr . '_' . $parcours->abr . '_' . date('YmdHis') . '.xlsx';

            return Excel::download(new UEECExport($this->niveauId, $this->parcoursId), $filename);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'exportation des UEs et ECs: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de l\'exportation: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ouvre la modale de confirmation de suppression
     */
    public function confirmDelete($ue_id)
    {
        $this->ueId = $ue_id;
        $this->showDeleteModal = true;
    }


    /**
     * Ferme la modale de confirmation de suppression
     */
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    /**
     * Supprime une UE
     *
     * @param  int  $ueId  L'ID de l'UE à supprimer
     */
    #[On('deleteUE')]
    public function deleteUE()
    {
        try {
            $ue = UE::findOrFail($this->ueId);
            // Supprimez les EC associés
            $ue->ecs()->delete();
            // Vérifier si la salle est utilisée
            if ($ue->ecs()->count() > 0) {
                toastr()->error('Cette UE ne peut pas être supprimée car elle est utilisée dans des ECs.');
                $this->showDeleteModal = false;
                return;
            }

            $ue->delete();
            toastr()->success('UE supprimée avec succès.');
            $this->showDeleteModal = false;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la UE: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de la suppression.');
            $this->showDeleteModal = false;
        }
    }

    /**
     * Rend la vue avec les données nécessaires
     */
    public function render()
    {
        $niveaux = Niveau::where('is_active', true)->get();
        $ues = collect([]);

        if ($this->step === 'ue' && $this->niveauId && $this->parcoursId) {
            $uesQuery = UE::where('niveau_id', $this->niveauId)
                ->where('parcours_id', $this->parcoursId);

            // Ajouter la recherche si nécessaire
            if (! empty($this->search)) {
                $uesQuery->where(function ($query) {
                    $query->where('abr', 'like', '%'.$this->search.'%')
                        ->orWhere('nom', 'like', '%'.$this->search.'%');
                });
            }

            // Ajouter le tri
            $uesQuery->orderBy($this->sortField, $this->sortDirection);

            $ues = $uesQuery->paginate(10);
        }

        return view('livewire.admin.ue.unite_element', [
            'niveaux' => $niveaux,
            'ues' => $ues,
        ]);
    }
}
