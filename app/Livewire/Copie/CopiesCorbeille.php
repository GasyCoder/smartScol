<?php

namespace App\Livewire\Copie;

use App\Models\Copie;
use App\Models\Niveau;
use App\Models\Parcour;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CopiesCorbeille extends Component
{
    use WithPagination;

    // Variables de filtrage (suppression de $ec_id et $examen_id)
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
    public $copieToDelete = null;
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
            $this->selectedItems = $this->getCopies()->pluck('id')->map(fn($id) => (string) $id)->toArray();
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
            $this->copieToDelete = Copie::onlyTrashed()->find($id);
            $this->deleteMode = 'single';
        } else {
            // Vérifier si des éléments sont sélectionnés
            if (empty($this->selectedItems)) {
                toastr()->error('Veuillez sélectionner au moins une note à supprimer.');
                return;
            }
            $this->deleteMode = 'multiple';
        }

        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->copieToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteDefinitely()
    {
        try {
            if ($this->deleteMode === 'single' && $this->copieToDelete) {
                // Supprimer définitivement une seule copie
                $this->copieToDelete->forceDelete();
                $this->message = 'Note définitivement supprimée avec succès.';
            } else {
                // Supprimer définitivement plusieurs copies
                Copie::onlyTrashed()->whereIn('id', $this->selectedItems)->forceDelete();
                $this->selectedItems = [];
                $this->selectAll = false;
                $this->message = 'Notes sélectionnées définitivement supprimées avec succès.';
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

    public function restoreCopie($id = null)
    {
        try {
            if ($id) {
                // Restaurer une seule copie
                $copie = Copie::onlyTrashed()->find($id);
                if (!$copie) {
                    throw new \Exception('Note introuvable.');
                }

                $copie->restore();
                $this->message = 'Note restaurée avec succès.';
            } else {
                // Restaurer plusieurs copies
                if (empty($this->selectedItems)) {
                    toastr()->error('Veuillez sélectionner au moins une note à restaurer.');
                    return;
                }

                Copie::onlyTrashed()->whereIn('id', $this->selectedItems)->restore();
                $this->selectedItems = [];
                $this->selectAll = false;
                $this->message = 'Notes sélectionnées restaurées avec succès.';
            }

            $this->messageType = 'success';
            toastr()->success($this->message);

        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    private function getCopies()
    {
        $query = Copie::onlyTrashed()
            ->with(['codeAnonymat', 'ec', 'examen', 'utilisateurSaisie']);

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
            $query->whereHas('codeAnonymat', function($q) {
                $q->where('code_complet', 'like', '%' . $this->search . '%');
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
            ->join('copies', 'users.id', '=', 'copies.saisie_par')
            ->whereNotNull('copies.deleted_at')
            ->select('users.id', 'users.name')
            ->distinct()
            ->get();

        if ($this->niveau_id) {
            $parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'desc')
                ->get();
        }

        // Récupérer les copies supprimées
        $copies = $this->getCopies()->orderBy('deleted_at', 'desc')->paginate(15);

        // Mettre à jour la sélection si nécessaire
        if ($this->selectAll) {
            $this->selectedItems = $copies->pluck('id')->map(fn($id) => (string) $id)->toArray();
        }

        return view('livewire.copie.copies-corbeille', [
            'copies' => $copies,
            'niveaux' => $niveaux,
            'parcours' => $parcours,
            'utilisateurs' => $utilisateurs
        ]);
    }
}
