<?php

namespace App\Livewire\Resultats;

use App\Models\User;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Resultat;
use App\Models\SessionExam;
use App\Models\Deliberation;
use App\Services\FusionService;
use Illuminate\Support\Facades\Auth;
/**
 * @property \Illuminate\Support\Collection $niveaux
 * @property \Illuminate\Support\Collection $parcours
 * @property \Illuminate\Support\Collection $salles
 * @property \Illuminate\Support\Collection $ecs
 * @property \Illuminate\Support\Collection $examens
 * @property \Illuminate\Support\Collection $resultats
 * @property \Illuminate\Support\Collection $moyennes
 * @property \Illuminate\Support\Collection $sessions
 */
class PublicationController extends Component
{
    // Variables de filtrage
    public $niveau_id;
    public $parcours_id;
    public $session_id;
    public $examen_id;

    // Listes des données pour les sélecteurs
    public $niveaux = [];
    public $parcours = [];
    public $sessions = [];
    public $examens = [];

    // Variables de contrôle
    public $confirmingPublication = false;
    public $showDeliberationModal = false;
    public $needsDeliberation = false;
    public $president_jury_id;
    public $date_deliberation;
    public $message = '';
    public $messageType = '';

    // Statistiques
    public $statistiques = null;

    public function mount()
    {
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr', 'desc')
            ->get();

        $this->date_deliberation = now()->format('Y-m-d\TH:i');
    }

    public function updatedNiveauId()
    {
        $this->parcours = collect();
        $this->sessions = collect();
        $this->examens = collect();
        $this->parcours_id = null;
        $this->session_id = null;
        $this->examen_id = null;
        $this->statistiques = null;

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
        $this->sessions = collect();
        $this->examens = collect();
        $this->session_id = null;
        $this->examen_id = null;
        $this->statistiques = null;

        if ($this->niveau_id && $this->parcours_id) {
            // Récupérer les sessions qui ont des examens pour ce niveau/parcours
            $sessionIds = Examen::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->pluck('session_id')
                ->unique();

            $this->sessions = SessionExam::whereIn('id', $sessionIds)
                ->orderBy('date_start', 'desc')
                ->get();

            if ($this->sessions->count() == 1) {
                $this->session_id = $this->sessions->first()->id;
                $this->updatedSessionId();
            }
        }
    }

    public function updatedSessionId()
    {
        $this->examens = collect();
        $this->examen_id = null;
        $this->statistiques = null;

        if ($this->niveau_id && $this->parcours_id && $this->session_id) {
            $this->examens = Examen::where('niveau_id', $this->niveau_id)
                ->where('parcours_id', $this->parcours_id)
                ->where('session_id', $this->session_id)
                ->orderBy('id', 'desc')
                ->get();

            // Vérifier si cette session nécessite une délibération
            $session = SessionExam::find($this->session_id);
            $this->needsDeliberation = $session && $session->type === 'Rattrapage';

            if ($this->examens->count() == 1) {
                $this->examen_id = $this->examens->first()->id;
                $this->chargerStatistiques();
            }
        }
    }

    public function updatedExamenId()
    {
        $this->statistiques = null;
        if ($this->examen_id) {
            $this->chargerStatistiques();
        }
    }

    public function chargerStatistiques()
    {
        if (!$this->examen_id) {
            return;
        }

        $fusionService = new FusionService();
        $this->statistiques = $fusionService->calculerStatistiques($this->examen_id);
    }

    public function confirmerPublication()
    {
        if (!$this->examen_id) {
            $this->message = 'Veuillez sélectionner un examen';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Vérifier qu'il y a des résultats validés
        $count = Resultat::where('examen_id', $this->examen_id)
            ->where('statut', 'valide')
            ->count();

        if ($count == 0) {
            $this->message = 'Aucun résultat validé à publier';
            $this->messageType = 'error';
            toastr()->error($this->message);
            return;
        }

        // Si c'est une session de rattrapage, afficher le modal de délibération
        if ($this->needsDeliberation) {
            $this->showDeliberationModal = true;
        } else {
            // Sinon, confirmer la publication directe
            $this->confirmingPublication = true;
        }
    }

    public function creerDeliberation()
    {
        $this->validate([
            'president_jury_id' => 'required|exists:users,id',
            'date_deliberation' => 'required|date_format:Y-m-d\TH:i'
        ]);

        try {
            // Vérifier si une délibération existe déjà
            $existante = Deliberation::where('niveau_id', $this->niveau_id)
                ->where('session_id', $this->session_id)
                ->whereHas('session.anneeUniversitaire', function($q) {
                    $q->where('is_active', true);
                })
                ->first();

            if ($existante) {
                // Utiliser l'existante
                $deliberation = $existante;
                $message = 'Délibération existante utilisée';
            } else {
                // Créer une nouvelle délibération
                $session = SessionExam::find($this->session_id);
                $deliberation = Deliberation::create([
                    'niveau_id' => $this->niveau_id,
                    'session_id' => $this->session_id,
                    'annee_universitaire_id' => $session->annee_universitaire_id,
                    'date_deliberation' => $this->date_deliberation,
                    'president_jury' => $this->president_jury_id
                ]);
                $message = 'Délibération créée avec succès';
            }

            // Associer les résultats à cette délibération
            $updated = Resultat::where('examen_id', $this->examen_id)
                ->where('statut', 'valide')
                ->update([
                    'deliberation_id' => $deliberation->id
                ]);

            $this->message = $message . '. ' . $updated . ' résultats associés à la délibération';
            $this->messageType = 'success';
            toastr()->success($this->message);

            $this->showDeliberationModal = false;
            $this->confirmingPublication = true;
        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    public function publierResultats()
    {
        try {
            $count = Resultat::where('examen_id', $this->examen_id)
                ->where('statut', 'valide')
                ->update([
                    'statut' => 'publie',
                    'modifie_par' => Auth::id(),
                    'date_modification' => now()
                ]);

            $this->message = $count . ' résultats publiés avec succès';
            $this->messageType = 'success';
            toastr()->success($this->message);

            $this->confirmingPublication = false;
            $this->statistiques = null;
            $this->chargerStatistiques();
        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
            $this->messageType = 'error';
            toastr()->error($this->message);
        }
    }

    public function render()
    {
        return view('livewire.resultats.publication-controller', [
            'niveaux' => $this->niveaux,
            'parcours' => $this->parcours,
            'sessions' => $this->sessions,
            'examens' => $this->examens,
            'presidents_jury' => User::all() // Liste des utilisateurs pour le choix du président
        ]);
    }
}
