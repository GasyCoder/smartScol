<?php

namespace App\Livewire\Manchette;

use App\Models\Manchette;
use App\Models\Niveau;
use App\Models\Parcour;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManchettesCorbeille extends Component
{
    use WithPagination;

    // Variables de filtrage
    public $niveau_id;
    public $parcours_id;
    public $date_debut;
    public $date_fin;
    public $saisie_par; // Pour filtrer par utilisateur

    // Variables pour la sélection multiple
    public $selectedItems = [];
    public $selectAll = false;

    // Variable pour la recherche
    public $search = '';

    // Variable pour la confirmation de suppression
    public $manchetteToDelete = null;
    public $showDeleteModal = false;
    public $deleteMode = 'single'; // 'single' ou 'multiple'

    // Messages
    public $message = '';
    public $messageType = '';

    public function mount()
    {
        // Initialiser les dates par défaut (30 derniers jours)
        $this->date_fin = date('Y-m-d');
        $this->date_debut = date('Y-m-d', strtotime('-30 days'));
    }

    // CORRECTION : Ajout des méthodes manquantes pour les filtres
    
    /**
     * Mise à jour du niveau - charge les parcours
     */
    public function updatedNiveauId()
    {
        $this->parcours_id = null; // Réinitialiser le parcours
        $this->resetPage();
    }

    /**
     * Mise à jour du parcours
     */
    public function updatedParcoursId()
    {
        $this->resetPage();
    }

    /**
     * Mise à jour du filtre utilisateur
     */
    public function updatedSaisiePar()
    {
        $this->resetPage();
    }

    /**
     * Mise à jour de la recherche
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Mise à jour des dates
     */
    public function updatedDateDebut()
    {
        $this->resetPage();
    }

    public function updatedDateFin()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedItems = $this->getManchettes()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function resetFilters()
    {
        $this->reset(['niveau_id', 'parcours_id', 'search', 'saisie_par']);
        $this->date_fin = date('Y-m-d');
        $this->date_debut = date('Y-m-d', strtotime('-30 days'));
        $this->selectedItems = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function confirmDelete($id = null)
    {
        if ($id) {
            $this->manchetteToDelete = Manchette::onlyTrashed()->find($id);
            $this->deleteMode = 'single';
        } else {
            // Vérifier si des éléments sont sélectionnés
            if (empty($this->selectedItems)) {
                toastr()->error('Veuillez sélectionner au moins une manchette à supprimer.');
                return;
            }
            $this->deleteMode = 'multiple';
        }

        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->manchetteToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteDefinitely()
    {
        try {
            if ($this->deleteMode === 'single' && $this->manchetteToDelete) {
                // Supprimer définitivement une seule manchette
                $this->manchetteToDelete->forceDelete();
                $this->message = 'Manchette définitivement supprimée avec succès.';
            } else {
                // Supprimer définitivement plusieurs manchettes
                Manchette::onlyTrashed()->whereIn('id', $this->selectedItems)->forceDelete();
                $this->selectedItems = [];
                $this->selectAll = false;
                $this->message = 'Manchettes sélectionnées définitivement supprimées avec succès.';
            }

            $this->messageType = 'success';
            toastr()->success($this->message);
            $this->showDeleteModal = false;

        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    public function restoreManchette($id = null)
    {
        try {
            if ($id) {
                // Restaurer une seule manchette
                $manchette = Manchette::onlyTrashed()->find($id);
                if (!$manchette) {
                    throw new \Exception('Manchette introuvable.');
                }

                $manchette->restore();
                $this->message = 'Manchette restaurée avec succès.';
            } else {
                // Restaurer plusieurs manchettes
                if (empty($this->selectedItems)) {
                    toastr()->error('Veuillez sélectionner au moins une manchette à restaurer.');
                    return;
                }

                Manchette::onlyTrashed()->whereIn('id', $this->selectedItems)->restore();
                $this->selectedItems = [];
                $this->selectAll = false;
                $this->message = 'Manchettes sélectionnées restaurées avec succès.';
            }

            $this->messageType = 'success';
            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    // CORRECTION : Méthode getManchettes améliorée
    private function getManchettes()
    {
        $query = Manchette::onlyTrashed()
            ->with(['codeAnonymat', 'etudiant', 'examen.niveau', 'examen.parcours', 'utilisateurSaisie']);

        // FILTRE PAR NIVEAU ET PARCOURS - Logique corrigée
        if ($this->niveau_id && $this->parcours_id) {
            // Si les deux sont sélectionnés
            $query->whereHas('examen', function($q) {
                $q->where('niveau_id', $this->niveau_id)
                  ->where('parcours_id', $this->parcours_id);
            });
        } elseif ($this->niveau_id) {
            // Si seul le niveau est sélectionné
            $query->whereHas('examen', function($q) {
                $q->where('niveau_id', $this->niveau_id);
            });
        }

        // FILTRE PAR UTILISATEUR (SAISI PAR)
        if ($this->saisie_par) {
            $query->where('saisie_par', $this->saisie_par);
        }

        // FILTRE PAR DATES DE SUPPRESSION
        if ($this->date_debut) {
            $query->whereDate('deleted_at', '>=', $this->date_debut);
        }

        if ($this->date_fin) {
            $query->whereDate('deleted_at', '<=', $this->date_fin);
        }

        // FILTRE DE RECHERCHE
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('codeAnonymat', function($sq) use ($searchTerm) {
                    $sq->where('code_complet', 'like', $searchTerm);
                })
                ->orWhereHas('etudiant', function($sq) use ($searchTerm) {
                    $sq->where('matricule', 'like', $searchTerm)
                      ->orWhere('nom', 'like', $searchTerm)
                      ->orWhere('prenom', 'like', $searchTerm);
                });
            });
        }

        return $query;
    }

    public function render()
    {
        // Récupérer les données pour les filtres
        $niveaux = Niveau::where('is_active', true)->orderBy('nom')->get();
        
        // CORRECTION : Parcours en fonction du niveau sélectionné
        $parcours = collect();
        if ($this->niveau_id) {
            $parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('nom')
                ->get();
        }
        
        // CORRECTION : Utilisateurs qui ont vraiment saisi des manchettes supprimées
        $utilisateurs = DB::table('users')
            ->join('manchettes', 'users.id', '=', 'manchettes.saisie_par')
            ->whereNotNull('manchettes.deleted_at')
            ->select('users.id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get();

        // Récupérer les manchettes supprimées avec pagination
        $manchettes = $this->getManchettes()
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        // CORRECTION : Mise à jour de la sélection si nécessaire
        if ($this->selectAll && $manchettes->count() > 0) {
            $this->selectedItems = $manchettes->pluck('id')->map(fn($id) => (string) $id)->toArray();
        }

        return view('livewire.manchette.corbeille-manchette', [
            'manchettes' => $manchettes,
            'niveaux' => $niveaux,
            'parcours' => $parcours,
            'utilisateurs' => $utilisateurs
        ]);
    }
}