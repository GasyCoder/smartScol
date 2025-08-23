<?php

namespace App\Livewire\Copie;

use Livewire\Component;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\Examen;
use App\Models\EC;
use App\Models\Copie;
use App\Models\Manchette;
use App\Models\CodeAnonymat;
use App\Models\SessionExam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CopieSaisie extends Component
{
    // Propriétés de sélection
    public $niveauId = null;
    public $parcoursId = null;
    public $examenId = null;
    public $ecId = null;
    
    // Collections pour les dropdowns
    public $niveaux = [];
    public $parcours = [];
    public $examens = [];
    public $ecs = [];
    
    // Interface de saisie
    public $codeAnonymat = '';
    public $note = '';
    public $commentaire = '';
    public $codeAnonymatTrouve = null;
    public $manchetteCorrespondante = null;
    public $showSaisieInterface = false;
    
    // Statistiques et progression
    public $copiesSaisies = [];
    public $progressCount = 0;
    public $totalCopies = 0;
    public $sessionType = 'normale';
    
    // Messages
    public $message = '';
    public $messageType = 'info';

    protected $rules = [
        'codeAnonymat' => 'required|string|max:20',
        'note' => 'required|numeric|min:0|max:20',
        'commentaire' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'note.required' => 'La note est obligatoire.',
        'note.numeric' => 'La note doit être un nombre.',
        'note.min' => 'La note ne peut pas être négative.',
        'note.max' => 'La note ne peut pas dépasser 20.',
        'codeAnonymat.required' => 'Le code d\'anonymat est obligatoire.',
    ];

    public function mount()
    {
        $this->loadNiveaux();
        $this->sessionType = Manchette::getCurrentSessionType();
    }

    public function loadNiveaux()
    {
        $this->niveaux = Niveau::where('is_active', true)
            ->orderBy('abr')
            ->get();
    }

    public function updatedNiveauId($value)
    {
        $this->resetSelections(['parcours', 'examens', 'ecs']);
        
        if ($value) {
            $this->loadParcours();
        }
    }

    public function loadParcours()
    {
        if (!$this->niveauId) return;
        
        $this->parcours = Parcour::where('niveau_id', $this->niveauId)
            ->where('is_active', true)
            ->get();
    }

    public function updatedParcoursId($value)
    {
        $this->resetSelections(['examens', 'ecs']);
        
        if ($value || $this->niveauId) {
            $this->loadExamens();
        }
    }

    public function loadExamens()
    {
        if (!$this->niveauId) return;
        
        $query = Examen::where('niveau_id', $this->niveauId);
        
        if ($this->parcoursId) {
            $query->where('parcours_id', $this->parcoursId);
        }
        
        $this->examens = $query->with(['ecs', 'niveau', 'parcours'])->get();
    }

    public function updatedExamenId($value)
    {
        $this->resetSelections(['ecs']);
        
        if ($value) {
            $this->loadECs();
        }
    }

    public function loadECs()
    {
        if (!$this->examenId) return;
        
        $examen = Examen::find($this->examenId);
        $this->ecs = $examen ? $examen->ecs : collect([]);
    }

    public function updatedEcId($value)
    {
        if ($value && $this->examenId) {
            $this->initializeSaisieInterface();
        }
    }

    public function initializeSaisieInterface()
    {
        $this->showSaisieInterface = true;
        $this->loadStatistiques();
        $this->resetSaisieForm();
    }

    public function loadStatistiques()
    {
        if (!$this->examenId || !$this->ecId) return;
        
        $sessionId = Manchette::getCurrentSessionId();
        
        // Charger les copies existantes
        $this->copiesSaisies = Copie::where('examen_id', $this->examenId)
            ->where('ec_id', $this->ecId)
            ->where('session_exam_id', $sessionId)
            ->with(['codeAnonymat'])
            ->latest()
            ->get()
            ->map(function($copie) {
                $manchette = $copie->findCorrespondingManchette();
                return [
                    'id' => $copie->id,
                    'code_complet' => $copie->codeAnonymat->code_complet ?? '',
                    'note' => $copie->note,
                    'commentaire' => $copie->commentaire,
                    'etudiant' => $manchette ? $manchette->etudiant : null,
                    'date_saisie' => $copie->date_saisie ?? $copie->created_at,
                ];
            })
            ->toArray();
            
        $this->progressCount = count($this->copiesSaisies);
        
        // Estimer le total basé sur les manchettes existantes
        $this->totalCopies = Manchette::where('examen_id', $this->examenId)
            ->where('session_exam_id', $sessionId)
            ->whereHas('codeAnonymat', function($q) {
                $q->where('ec_id', $this->ecId);
            })
            ->count();
    }

    public function updatedCodeAnonymat($value)
    {
        $this->codeAnonymatTrouve = null;
        $this->manchetteCorrespondante = null;
        
        if (strlen($value) >= 2) {
            $this->rechercherCodeAnonymat();
        }
    }

    public function rechercherCodeAnonymat()
    {
        if (!$this->codeAnonymat || !$this->ecId || !$this->examenId) return;
        
        $sessionId = Manchette::getCurrentSessionId();
        
        // Chercher le code d'anonymat
        $codeAnonymyatObj = CodeAnonymat::where('code_complet', $this->codeAnonymat)
            ->where('examen_id', $this->examenId)
            ->where('ec_id', $this->ecId)
            ->where('session_exam_id', $sessionId)
            ->first();
            
        if ($codeAnonymyatObj) {
            $this->codeAnonymatTrouve = $codeAnonymyatObj;
            
            // Chercher la manchette correspondante
            $this->manchetteCorrespondante = Manchette::where('code_anonymat_id', $codeAnonymyatObj->id)
                ->where('examen_id', $this->examenId)
                ->where('session_exam_id', $sessionId)
                ->with('etudiant')
                ->first();
                
            if ($this->manchetteCorrespondante) {
                $this->clearMessage();
                
                // Vérifier si la copie existe déjà
                $copieExistante = Copie::where('code_anonymat_id', $codeAnonymyatObj->id)
                    ->where('examen_id', $this->examenId)
                    ->where('ec_id', $this->ecId)
                    ->where('session_exam_id', $sessionId)
                    ->first();
                    
                if ($copieExistante) {
                    $this->note = $copieExistante->note;
                    $this->commentaire = $copieExistante->commentaire;
                    $this->showMessage('Une copie existe déjà pour ce code. Vous pouvez la modifier.', 'info');
                }
            } else {
                $this->showMessage('Code d\'anonymat trouvé mais aucune manchette correspondante.', 'error');
            }
        } else {
            $this->codeAnonymatTrouve = null;
            $this->manchetteCorrespondante = null;
            if (strlen($this->codeAnonymat) >= 3) {
                $this->showMessage('Code d\'anonymat non trouvé pour cet examen/matière.', 'error');
            }
        }
    }

    public function sauvegarderCopie()
    {
        $this->validate();
        
        if (!$this->codeAnonymatTrouve || !$this->manchetteCorrespondante) {
            $this->showMessage('Veuillez d\'abord valider le code d\'anonymat.', 'error');
            return;
        }
        
        DB::beginTransaction();
        
        try {
            $sessionId = Manchette::getCurrentSessionId();
            
            // Créer ou mettre à jour la copie
            $copie = Copie::updateOrCreate(
                [
                    'examen_id' => $this->examenId,
                    'ec_id' => $this->ecId,
                    'code_anonymat_id' => $this->codeAnonymatTrouve->id,
                    'session_exam_id' => $sessionId,
                ],
                [
                    'note' => $this->note,
                    'commentaire' => $this->commentaire,
                    'saisie_par' => Auth::id(),
                    'date_saisie' => now(),
                ]
            );
            
            DB::commit();
            
            $action = $copie->wasRecentlyCreated ? 'créée' : 'mise à jour';
            $this->showMessage("Copie {$action} avec succès.", 'success');
            $this->resetSaisieForm();
            $this->loadStatistiques();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showMessage('Erreur lors de l\'enregistrement: ' . $e->getMessage(), 'error');
        }
    }

    public function supprimerCopie($copieId)
    {
        try {
            $copie = Copie::find($copieId);
            
            if ($copie && $copie->examen_id == $this->examenId && $copie->ec_id == $this->ecId) {
                $copie->delete();
                $this->showMessage('Copie supprimée avec succès.', 'success');
                $this->loadStatistiques();
            }
            
        } catch (\Exception $e) {
            $this->showMessage('Erreur lors de la suppression: ' . $e->getMessage(), 'error');
        }
    }

    public function marquerToutesVerifiees()
    {
        try {
            if (!$this->examenId || !$this->ecId) return;
            
            $nbMarquees = Copie::marquerToutesVerifiees($this->examenId, $this->ecId);
            $this->showMessage("{$nbMarquees} copies marquées comme vérifiées.", 'success');
            $this->loadStatistiques();
            
        } catch (\Exception $e) {
            $this->showMessage('Erreur lors du marquage: ' . $e->getMessage(), 'error');
        }
    }

    private function resetSelections($types)
    {
        foreach ($types as $type) {
            switch ($type) {
                case 'parcours':
                    $this->parcoursId = null;
                    $this->parcours = [];
                    break;
                case 'examens':
                    $this->examenId = null;
                    $this->examens = [];
                    break;
                case 'ecs':
                    $this->ecId = null;
                    $this->ecs = [];
                    $this->showSaisieInterface = false;
                    break;
            }
        }
    }

    private function resetSaisieForm()
    {
        $this->codeAnonymat = '';
        $this->note = '';
        $this->commentaire = '';
        $this->codeAnonymatTrouve = null;
        $this->manchetteCorrespondante = null;
        $this->clearMessage();
    }

    private function showMessage($message, $type = 'info')
    {
        $this->message = $message;
        $this->messageType = $type;
        
        if ($type === 'success') {
            $this->dispatch('clearMessage', ['delay' => 3000]);
        }
    }

    private function clearMessage()
    {
        $this->message = '';
        $this->messageType = 'info';
    }

    public function render()
    {
        return view('livewire.copie.copie-saisie');
    }
}