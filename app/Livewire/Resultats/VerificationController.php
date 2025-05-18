<?php

namespace App\Livewire\Resultats;

use App\Models\EC;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Resultat;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class VerificationController extends Component
{
    use WithPagination;

    // Variables de filtrage
    public $niveau_id;
    public $parcours_id;
    public $examen_id;
    public $ec_id;
    public $search = '';
    public $statut = 'provisoire';

    // Listes des données pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $examens = [];
    public $ecs = [];

    // Variables pour la modification
    public $showEditModal = false;
    public $resultat_id;
    public $etudiant_nom;
    public $code_anonymat;
    public $ec_nom;
    public $note;
    public $note_originale;

    // Variables de contrôle
    public $confirmingValidation = false;
    public $message = '';
    public $messageType = '';

    public function mount()
    {
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();
    }

    public function updatedNiveauId()
    {
        $this->parcours = collect();
        $this->examens = collect();
        $this->ecs = collect();
        $this->parcours_id = null;
        $this->examen_id = null;
        $this->ec_id = null;
        $this->resetPage();

        if ($this->niveau_id) {
            $this->parcours = Parcour::where('niveau_id', $this->niveau_id)
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();

            if ($this->parcours->count() == 1) {
                $this->parcours_id = $this->parcours->first()->id;
                $this->updatedParcoursId();
            }
        }
    }

    public function updatedParcoursId()
    {
        $this->examens = collect();
        $this->ecs = collect();
        $this->examen_id = null;
        $this->ec_id = null;
        $this->resetPage();

        if ($this->niveau_id && $this->parcours_id) {
            $this->examens = Examen::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->orderBy('id', 'desc')
                ->get();

            if ($this->examens->count() == 1) {
                $this->examen_id = $this->examens->first()->id;
                $this->updatedExamenId();
            }
        }
    }

    public function updatedExamenId()
    {
        $this->ecs = collect();
        $this->ec_id = null;
        $this->resetPage();

        if ($this->examen_id) {
            // Récupérer toutes les ECs liées à cet examen
            $ecIds = Resultat::where('examen_id', $this->examen_id)
                ->select('ec_id')
                ->distinct()
                ->pluck('ec_id');

            $this->ecs = EC::whereIn('id', $ecIds)->get();

            // Ajouter l'option "Toutes les matières"
            $this->ecs->prepend((object)[
                'id' => 'all',
                'nom' => 'Toutes les matières'
            ]);
        }
    }

    public function updatedStatut()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function editResultat($id)
    {
        $resultat = Resultat::with(['etudiant', 'codeAnonymat', 'ec'])
            ->findOrFail($id);

        $this->resultat_id = $resultat->id;
        $this->etudiant_nom = $resultat->etudiant->nom . ' ' . $resultat->etudiant->prenom;
        $this->code_anonymat = $resultat->codeAnonymat->code_complet;
        $this->ec_nom = $resultat->ec->nom;
        $this->note = $resultat->note;
        $this->note_originale = $resultat->note;

        $this->showEditModal = true;
    }

    public function saveResultat()
    {
        $this->validate([
            'note' => 'required|numeric|min:0|max:20',
        ]);

        $resultat = Resultat::findOrFail($this->resultat_id);

        if ($this->note != $this->note_originale) {
            $resultat->update([
                'note' => $this->note,
                'modifie_par' => Auth::id(),
                'date_modification' => now()
            ]);

            $this->message = 'Note modifiée avec succès';
            $this->messageType = 'success';
            toastr()->success($this->message);
        } else {
            $this->message = 'Aucune modification apportée';
            $this->messageType = 'info';
            toastr()->info($this->message);
        }

        $this->showEditModal = false;
    }

    public function confirmerValidation()
    {
        if ($this->statut != 'provisoire') {
            $this->message = 'Seuls les résultats provisoires peuvent être validés';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        $count = Resultat::where('examen_id', $this->examen_id)
            ->where('statut', 'provisoire')
            ->count();

        if ($count == 0) {
            $this->message = 'Aucun résultat provisoire à valider';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        $this->confirmingValidation = true;
    }

    public function validerResultats()
    {
        try {
            $count = Resultat::where('examen_id', $this->examen_id)
                ->where('statut', 'provisoire')
                ->update([
                    'statut' => 'valide',
                    'modifie_par' => Auth::id(),
                    'date_modification' => now()
                ]);

            $this->message = $count . ' résultats validés avec succès';
            $this->messageType = 'success';
            toastr()->success($this->message);

            // Mettre à jour le statut actif
            $this->statut = 'valide';
            $this->confirmingValidation = false;
        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    public function imprimer()
    {
        // Rediriger vers une route d'impression avec les paramètres de filtrage
        $params = [
            'examen' => $this->examen_id,
            'statut' => $this->statut
        ];

        if ($this->ec_id && $this->ec_id != 'all') {
            $params['ec'] = $this->ec_id;
        }

        return redirect()->route('resultats.imprimer', $params);
    }

    public function render()
    {
        $query = Resultat::query()
            ->with(['etudiant', 'codeAnonymat', 'ec', 'utilisateurGeneration', 'utilisateurModification']);

        if ($this->examen_id) {
            $query->where('examen_id', $this->examen_id);

            if ($this->ec_id && $this->ec_id != 'all') {
                $query->where('ec_id', $this->ec_id);
            }

            if ($this->statut) {
                $query->where('statut', $this->statut);
            }

            if ($this->search) {
                $query->whereHas('etudiant', function($q) {
                    $q->where('nom', 'like', '%' . $this->search . '%')
                      ->orWhere('prenom', 'like', '%' . $this->search . '%')
                      ->orWhere('matricule', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('codeAnonymat', function($q) {
                    $q->where('code_complet', 'like', '%' . $this->search . '%');
                });
            }
        } else {
            // Si aucun examen n'est sélectionné, renvoyer un résultat vide
            $query->where('id', 0);
        }

        // Tri par ordre alphabétique du nom de l'étudiant puis par matière
        $query->join('etudiants', 'resultats.etudiant_id', '=', 'etudiants.id')
              ->join('ecs', 'resultats.ec_id', '=', 'ecs.id')
              ->orderBy('etudiants.nom')
              ->orderBy('etudiants.prenom')
              ->orderBy('ecs.nom')
              ->select('resultats.*');

        $resultats = $query->paginate(25);

        return view('livewire.resultats.verification-controller', [
            'resultats' => $resultats,
            'niveaux' => $this->niveaux,
            'parcours' => $this->parcours,
            'examens' => $this->examens,
            'ecs' => $this->ecs
        ]);
    }
}
