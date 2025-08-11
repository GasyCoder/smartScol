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

    private function getManchettes()
    {
        $query = Manchette::onlyTrashed()
            ->with(['codeAnonymat', 'etudiant', 'examen', 'utilisateurSaisie']);

        // Appliquer les filtres
        if ($this->niveau_id) {
            $query->whereHas('examen', function($q) {
                $q->where('niveau_id', $this->niveau_id);
            });
        }

        if ($this->parcours_id) {
            $query->whereHas('examen', function($q) {
                $q->where('parcours_id', $this->parcours_id);
            });
        }

        if ($this->saisie_par) {
            $query->where('saisie_par', $this->saisie_par);
        }

        // Filtre par dates de suppression
        if ($this->date_debut) {
            $query->whereDate('deleted_at', '>=', $this->date_debut);
        }

        if ($this->date_fin) {
            $query->whereDate('deleted_at', '<=', $this->date_fin);
        }

        // Recherche
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('codeAnonymat', function($sq) {
                    $sq->where('code_complet', 'like', '%' . $this->search . '%');
                })->orWhereHas('etudiant', function($sq) {
                    $sq->where('matricule', 'like', '%' . $this->search . '%')
                      ->orWhere('nom', 'like', '%' . $this->search . '%');
                });
            });
        }

        return $query;
    }

    public function render()
    {
        // Récupérer les données pour les filtres
        $niveaux = Niveau::where('is_active', true)->orderBy('abr')->get();
        $parcours = collect();
        $utilisateurs = DB::table('users')
            ->join('manchettes', 'users.id', '=', 'manchettes.saisie_par')
            ->whereNotNull('manchettes.deleted_at')
            ->select('users.id', 'users.name')
            ->distinct()
            ->get();

        if ($this->niveau_id) {
            $parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'desc')
                ->get();
        }

        // Récupérer les manchettes supprimées
        $manchettes = $this->getManchettes()->orderBy('deleted_at', 'desc')->paginate(15);

        // Mettre à jour la sélection si nécessaire
        if ($this->selectAll) {
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