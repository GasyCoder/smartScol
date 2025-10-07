<?php

namespace App\Livewire\Resultats;

use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\SessionExam;
use App\Models\AnneeUniversitaire;
use App\Services\StatutPACESService;
use Livewire\Component;
use Illuminate\Support\Collection;

class ResultatsPACES extends Component
{
    public $sessionId;
    public $anneeUniversitaireId;
    public $sessions; // Enlever le typage ou typer comme Collection
    public $anneesUniversitaires;
    public $parcours;
    public $statistiques = [];
    public $processing = false;
    public $message = '';
    public $messageType = '';

    public function mount()
    {
        $this->chargerAnneesUniversitaires();
        $this->chargerParcours();
        
        // Sélectionner l'année active par défaut
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        if ($anneeActive) {
            $this->anneeUniversitaireId = $anneeActive->id;
            $this->chargerSessions();
        }
    }

    public function chargerAnneesUniversitaires()
    {
        $this->anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();
    }

    public function chargerSessions()
    {
        if (!$this->anneeUniversitaireId) {
            $this->sessions = collect(); // Collection vide au lieu de []
            return;
        }

        $this->sessions = SessionExam::where('annee_universitaire_id', $this->anneeUniversitaireId)
            ->orderBy('type', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Sélectionner la session Normale par défaut si elle existe
        if ($this->sessions->isNotEmpty() && !$this->sessionId) {
            $sessionNormale = $this->sessions->where('type', 'Normale')->first();
            $this->sessionId = $sessionNormale ? $sessionNormale->id : $this->sessions->first()->id;
        }
    }

    public function chargerParcours()
    {
        $this->parcours = Parcour::whereHas('niveau', function($q) {
            $q->where('is_concours', true);
        })
        ->where('is_active', true)
        ->with('niveau')
        ->get();
    }

    public function attribuerStatuts()
    {
        if (!$this->sessionId) {
            $this->message = 'Veuillez sélectionner une session.';
            $this->messageType = 'error';
            return;
        }

        $this->processing = true;
        $this->message = '';

        try {
            $service = new StatutPACESService();
            $resultat = $service->attribuerStatutsPACES($this->sessionId);

            if ($resultat['success']) {
                $this->statistiques = $resultat['statistiques'];
                $this->message = $resultat['message'];
                $this->messageType = 'success';
                
                $this->chargerStatistiques();
            } else {
                $this->message = $resultat['message'];
                $this->messageType = 'error';
            }

        } catch (\Exception $e) {
            $this->message = 'Erreur : ' . $e->getMessage();
            $this->messageType = 'error';
            \Log::error('Erreur attribution statuts PACES', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->processing = false;
        }
    }

    public function chargerStatistiques()
    {
        if (!$this->sessionId || !$this->parcours) return;

        $service = new StatutPACESService();
        $this->statistiques = [];

        foreach ($this->parcours as $parcours) {
            $this->statistiques[$parcours->id] = $service->getStatistiquesParcours(
                $parcours->id, 
                $this->sessionId
            );
        }
    }

    public function updatedAnneeUniversitaireId()
    {
        $this->sessionId = null;
        $this->statistiques = [];
        $this->chargerSessions();
    }

    public function updatedSessionId()
    {
        $this->chargerStatistiques();
    }

    public function render()
    {
        return view('livewire.resultats.resultats-paces');
    }
}