<?php

namespace App\Livewire\Student;

use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Imports\EtudiantImport;
use App\Exports\EtudiantsExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EtudiantTemplateExport;

class Students extends Component
{
    use WithPagination, WithFileUploads;

    // Utiliser queryString pour persister ces variables dans l'URL
    protected $queryString = [
        'step' => ['except' => 'niveau'],
        'niveauId' => ['except' => '', 'as' => 'niveau'],
        'parcoursId' => ['except' => '', 'as' => 'parcours']
    ];

    // Variables pour la navigation
    public $step = 'niveau'; // 'niveau', 'parcours', ou 'etudiants'
    public $niveauId = '';
    public $parcoursId = '';
    public $student_id;
    public $showDeleteModal = false;

    // Variables d'état pour l'affichage
    public $niveauInfo = null;
    public $parcoursInfo = null;
    public $parcours = [];

    // Variable pour l'importation
    public $importFile = null;

    // Variables pour la recherche et le tri
    public $search = '';
    public $sortField = 'matricule';
    public $sortDirection = 'asc';

    public function mount()
    {
        // Au chargement initial, restaurer l'état en fonction des paramètres d'URL
        $this->loadDataFromQueryParams();
    }

    /**
     * Nettoie une valeur d'entrée, gérant les valeurs complexes comme les tableaux
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
     */
    private function loadNiveauData($niveauId)
    {
        try {
            $niveau = Niveau::where('id', $niveauId)->first();

            if ($niveau) {
                $this->niveauInfo = [
                    'id' => $niveau->id,
                    'nom' => $niveau->nom,
                    'abr' => $niveau->abr
                ];

                $this->parcours = Parcour::where('niveau_id', $niveauId)
                    ->where('is_active', true)
                    ->get();

                return true;
            }
        } catch (\Exception $e) {
            // Log l'erreur si nécessaire
            Log::error('Erreur lors du chargement des données du niveau: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Charge les données d'un parcours de manière sécurisée
     */
    private function loadParcoursData($parcoursId)
    {
        try {
            $parcours = Parcour::where('id', $parcoursId)->first();

            if ($parcours) {
                $this->parcoursInfo = [
                    'id' => $parcours->id,
                    'nom' => $parcours->nom,
                    'abr' => $parcours->abr
                ];

                // Si le niveau n'est pas déjà chargé, chargez-le maintenant
                if (!$this->niveauInfo && $parcours->niveau_id) {
                    $this->niveauId = $parcours->niveau_id;
                    $this->loadNiveauData($parcours->niveau_id);
                }

                return true;
            }
        } catch (\Exception $e) {
            // Log l'erreur si nécessaire
            Log::error('Erreur lors du chargement des données du parcours: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Méthode pour le tri des colonnes
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
     */
    public function updatedParcoursId($value)
    {
        // Nettoyer la valeur
        $value = $this->cleanInputValue($value);
        $this->parcoursId = $value;

        if ($value) {
            // Utiliser la méthode sécurisée pour charger les données du parcours
            if ($this->loadParcoursData($value)) {
                $this->step = 'etudiants';
            }
        } else {
            $this->parcoursInfo = null;
        }
    }

    /**
     * Télécharge le modèle d'import des étudiants
     */
    public function downloadTemplate()
    {
        return Excel::download(new EtudiantTemplateExport(), 'modele_etudiants.xlsx');
    }

    /**
     * Importe des étudiants depuis un fichier Excel
     */
    public function importEtudiants()
    {
        // Validation du fichier
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            // Créer une instance de l'importation
            $import = new EtudiantImport(
                $this->niveauId,
                $this->parcoursId
            );

            // Importer directement sans file d'attente
            Excel::import($import, $this->importFile, null, \Maatwebsite\Excel\Excel::XLSX);

            // Récupérer les compteurs
            $counts = $import->getImportCounts();

            // Réinitialiser le fichier et afficher un message de succès
            $this->importFile = null;
            toastr()->success(
                'Importation réussie : ' . $counts['created'] . ' étudiant(s) créé(s) et ' .
                $counts['updated'] . ' étudiant(s) mis à jour.'
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation des étudiants: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue: ' . $e->getMessage());
        }
    }

    /**
     * Exporte les étudiants au format Excel
     */
    public function exportEtudiants()
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

            $filename = 'etudiants_' . $niveau->abr . '_' . $parcours->abr . '_' . date('YmdHis') . '.xlsx';

            return Excel::download(new EtudiantsExport($this->niveauId, $this->parcoursId), $filename);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'exportation des étudiants: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de l\'exportation: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ouvre la modale de confirmation de suppression
     */
    public function confirmDelete($id)
    {
        $this->student_id = $id; // Uniformiser le nom de la variable
        $this->showDeleteModal = true;
    }

    /**
     * Ferme la modale de confirmation de suppression
     */
    public function cancelDelete()
    {
        $this->student_id = null; // Réinitialiser l'ID
        $this->showDeleteModal = false;
    }

    /**
     * Supprime un étudiant
     */
    public function deleteEtudiant()
    {
        try {
            // Utiliser student_id au lieu de etudiantId pour rester cohérent
            $etudiant = Etudiant::findOrFail($this->student_id);
            $etudiant->delete();

            toastr()->success('Étudiant supprimé avec succès.');
            $this->student_id = null; // Réinitialiser l'ID
            $this->showDeleteModal = false;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'étudiant: ' . $e->getMessage());
            toastr()->error('Une erreur est survenue lors de la suppression.');
            $this->showDeleteModal = false;
        }
    }

    /**
     * Obtient le nombre total d'étudiants pour un niveau et parcours donnés
     */
    public function getEtudiantsCount()
    {
        if ($this->niveauId && $this->parcoursId) {
            return Etudiant::where('niveau_id', $this->niveauId)
                ->where('parcours_id', $this->parcoursId)
                ->count();
        }

        return 0;
    }

    /**
     * Rend la vue avec les données nécessaires
     */
    public function render()
    {
        $niveaux = Niveau::where('is_active', true)->get();
        $etudiants = collect([]);

        if ($this->step === 'etudiants' && $this->niveauId && $this->parcoursId) {
            $etudiantsQuery = Etudiant::where('niveau_id', $this->niveauId)
                ->where('parcours_id', $this->parcoursId);

            // Ajouter la recherche si nécessaire
            if (!empty($this->search)) {
                $etudiantsQuery->where(function ($query) {
                    $query->where('matricule', 'like', '%' . $this->search . '%')
                        ->orWhere('nom', 'like', '%' . $this->search . '%')
                        ->orWhere('prenom', 'like', '%' . $this->search . '%');
                });
            }

            // Ajouter le tri
            $etudiantsQuery->orderBy($this->sortField, $this->sortDirection);

            $etudiants = $etudiantsQuery->paginate(5);
        }


        return view('livewire.admin.student.index', [
            'niveaux' => $niveaux,
            'etudiants' => $etudiants,
        ]);
    }
}
