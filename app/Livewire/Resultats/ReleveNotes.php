<?php

namespace App\Livewire\Resultats;

use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\Etudiant;
use App\Models\SessionExam;
use Livewire\WithPagination;
use App\Models\ResultatFinal;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\Log;

class ReleveNotes extends Component
{
    use WithPagination;

    // Filtres
    public $selectedNiveau;
    public $selectedParcours;
    public $selectedSession;
    public $selectedAnneeUniv;
    public $search = '';
    public $statistiques = [];

    // Collections
    public $niveaux;
    public $parcours;
    public $sessions;
    public $anneesUniv;

    public function mount()
    {
        $this->loadData();
        $this->setDefaults();
    }

    private function loadData()
    {
        $this->niveaux = Niveau::where('is_active', true)->orderBy('nom')->get();
        $this->anneesUniv = AnneeUniversitaire::orderBy('date_start', 'desc')->get();
        $this->parcours = collect();
        $this->sessions = collect();
    }


    private function calculerStatistiques()
    {
        if (!$this->selectedSession) {
            $this->statistiques = [];
            return;
        }

        try {
            // ✅ CORRECTION : Partir des étudiants et appliquer les filtres
            $etudiantsQuery = Etudiant::query()
                ->where('is_active', true);

            // ✅ FILTRER directement sur la table étudiants
            if ($this->selectedNiveau) {
                $etudiantsQuery->where('niveau_id', $this->selectedNiveau);
            }

            if ($this->selectedParcours) {
                $etudiantsQuery->where('parcours_id', $this->selectedParcours);
            }

            if ($this->search) {
                $etudiantsQuery->where(function($q) {
                    $q->where('nom', 'like', '%' . $this->search . '%')
                    ->orWhere('prenom', 'like', '%' . $this->search . '%')
                    ->orWhere('matricule', 'like', '%' . $this->search . '%');
                });
            }

            // ✅ SEULEMENT les étudiants qui ont des résultats dans la session sélectionnée
            $etudiantsQuery->whereHas('resultatsFinaux', function($q) {
                $q->where('session_exam_id', $this->selectedSession)
                ->where('statut', ResultatFinal::STATUT_PUBLIE);
            });

            $etudiants = $etudiantsQuery->get();

            // ✅ COMPTER les décisions par étudiant unique
            $decisionsCount = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'excluss' => 0
            ];

            foreach ($etudiants as $etudiant) {
                // ✅ Récupérer LA décision de cet étudiant pour cette session
                $decision = ResultatFinal::where('session_exam_id', $this->selectedSession)
                    ->where('etudiant_id', $etudiant->id)
                    ->where('statut', ResultatFinal::STATUT_PUBLIE)
                    ->value('decision'); // Prend la première décision trouvée

                if ($decision && isset($decisionsCount[$decision])) {
                    $decisionsCount[$decision]++;
                }
            }

            $total = $etudiants->count();
            $admis = $decisionsCount['admis'];
            $rattrapage = $decisionsCount['rattrapage'];
            $redoublant = $decisionsCount['redoublant'];
            $excluss = $decisionsCount['excluss'];
            $autres = $redoublant + $excluss;

            $this->statistiques = [
                'total' => $total,
                'admis' => $admis,
                'rattrapage' => $rattrapage,
                'redoublant' => $redoublant,
                'excluss' => $excluss,
                'autres' => $autres,
                'pourcentage_admis' => $total > 0 ? round(($admis / $total) * 100, 1) : 0,
                'pourcentage_rattrapage' => $total > 0 ? round(($rattrapage / $total) * 100, 1) : 0,
                'pourcentage_autres' => $total > 0 ? round(($autres / $total) * 100, 1) : 0,
            ];

            // ✅ DEBUGGING - Log les statistiques pour vérifier
            \Log::info('Statistiques calculées', [
                'niveau' => $this->selectedNiveau,
                'parcours' => $this->selectedParcours,
                'session' => $this->selectedSession,
                'search' => $this->search,
                'etudiants_total' => $total,
                'decisions' => $decisionsCount
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques filtrées', [
                'error' => $e->getMessage(),
                'niveau' => $this->selectedNiveau,
                'parcours' => $this->selectedParcours,
                'session' => $this->selectedSession
            ]);
            $this->statistiques = [];
        }
    }

    private function setDefaults()
    {
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        $this->selectedAnneeUniv = $anneeActive?->id;
        $this->loadSessions();
    }

    public function updatedSelectedNiveau()
    {
        if ($this->selectedNiveau) {
            $niveau = Niveau::find($this->selectedNiveau);
            if ($niveau?->has_parcours) {
                $this->parcours = Parcour::where('niveau_id', $this->selectedNiveau)
                    ->where('is_active', true)
                    ->orderBy('nom')
                    ->get();
            } else {
                $this->parcours = collect();
                $this->selectedParcours = null;
            }
        } else {
            $this->parcours = collect();
            $this->selectedParcours = null;
        }
        $this->calculerStatistiques();
        $this->resetPage();
    }

    public function updatedSelectedParcours()
    {
        $this->calculerStatistiques(); // ✅ RECALCULER lors du changement de parcours
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->calculerStatistiques(); // ✅ RECALCULER lors de la recherche
        $this->resetPage();
    }

    public function updatedSelectedSession()
    {
        $this->calculerStatistiques(); // ✅ RECALCULER lors du changement de session
        $this->resetPage();
    }

    public function updatedSelectedAnneeUniv()
    {
        $this->loadSessions();
        $this->calculerStatistiques();
        $this->resetPage();
    }

    private function loadSessions()
    {
        if ($this->selectedAnneeUniv) {
            $this->sessions = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniv)
                ->orderBy('type')
                ->get();
                
            // Sélectionner la session normale par défaut
            if ($this->sessions->isNotEmpty()) {
                $sessionNormale = $this->sessions->where('type', 'Normale')->first();
                $this->selectedSession = $sessionNormale?->id;
            }
        } else {
            $this->sessions = collect();
            $this->selectedSession = null;
        }
    }

    public function getEtudiants()
    {
        $query = Etudiant::query()
            ->with(['niveau', 'parcours'])
            ->where('is_active', true);

        // Filtres
        if ($this->selectedNiveau) {
            $query->where('niveau_id', $this->selectedNiveau);
        }

        if ($this->selectedParcours) {
            $query->where('parcours_id', $this->selectedParcours);
        }

        // Recherche
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nom', 'like', '%' . $this->search . '%')
                  ->orWhere('prenom', 'like', '%' . $this->search . '%')
                  ->orWhere('matricule', 'like', '%' . $this->search . '%');
            });
        }

        // Filtrer seulement les étudiants qui ont des résultats dans la session sélectionnée
        if ($this->selectedSession) {
            $query->whereHas('resultatsFinaux', function($q) {
                $q->where('session_exam_id', $this->selectedSession)
                  ->where('statut', ResultatFinal::STATUT_PUBLIE);
            });
        }

        return $query->orderBy('nom')->orderBy('prenom')->paginate(20);
    }

    public function voirReleve($etudiantId)
    {
        if (!$this->selectedSession) {
            session()->flash('error', 'Veuillez sélectionner une session.');
            return;
        }

        return redirect()->route('resultats.releve-notes.show', [
            'etudiant' => $etudiantId,
            'session' => $this->selectedSession
        ]);
    }

    public function genererPDF($etudiantId)
    {
        if (!$this->selectedSession) {
            session()->flash('error', 'Veuillez sélectionner une session.');
            return;
        }

        try {
            $donneesReleve = $this->getDonneesReleve($etudiantId, $this->selectedSession);
            
            $pdf = Pdf::loadView('exports.releve-notes', $donneesReleve)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);

            $nomFichier = "Releve_Notes_{$donneesReleve['etudiant']->matricule}_{$donneesReleve['session']->type}.pdf";

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $nomFichier);

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    private function getHeaderImageBase64()
    {
        try {
            $imagePath = public_path('assets/images/header.png'); // Notez le chemin correct
            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);
                return 'data:image/png;base64,' . base64_encode($imageData);
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Erreur encodage image: ' . $e->getMessage());
            return null;
        }
    }


    public function getDonneesReleve($etudiantId, $sessionId)
    {
        $etudiant = Etudiant::with(['niveau', 'parcours'])->findOrFail($etudiantId);
        $session = SessionExam::with('anneeUniversitaire')->findOrFail($sessionId);

        // ✅ NOUVEAU : Vérifier si la session a été délibérée
        $sessionDeliberee = $session->estDeliberee();
        $parametresDeliberation = $sessionDeliberee ? $session->getParametresDeliberation() : null;

        // Récupérer tous les résultats de l'étudiant pour cette session
        $resultats = ResultatFinal::with(['ec.ue', 'examen'])
            ->where('session_exam_id', $sessionId)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->get();

        if ($resultats->isEmpty()) {
            throw new \Exception('Aucun résultat trouvé pour cet étudiant dans cette session.');
        }

        // Grouper par UE et calculer (logique existante...)
        $resultatsParUE = $resultats->groupBy('ec.ue_id');
        $uesData = [];
        $moyennesUE = [];
        $totalCredits = 0;
        $creditsValides = 0;
        $hasNoteEliminatoire = false;

        foreach ($resultatsParUE as $ueId => $resultatsUE) {
            $ue = $resultatsUE->first()->ec->ue;
            $totalCredits += $ue->credits ?? 0;

            $notesEC = [];
            $notesValues = [];
            $hasZeroInUE = false;

            foreach ($resultatsUE as $resultat) {
                $notesEC[] = [
                    'ec' => $resultat->ec,
                    'note' => $resultat->note,
                    'est_eliminatoire' => $resultat->note == 0
                ];
                
                $notesValues[] = $resultat->note;
                
                if ($resultat->note == 0) {
                    $hasZeroInUE = true;
                    $hasNoteEliminatoire = true;
                }
            }

            $moyenneUE = count($notesValues) > 0 ? 
                round(array_sum($notesValues) / count($notesValues), 2) : 0;

            $ueValidee = ($moyenneUE >= 10) && !$hasZeroInUE;
            
            if ($ueValidee) {
                $creditsValides += $ue->credits ?? 0;
            }

            $moyennesUE[] = $moyenneUE;

            $uesData[] = [
                'ue' => $ue,
                'notes_ec' => $notesEC,
                'moyenne_ue' => $moyenneUE,
                'validee' => $ueValidee,
                'eliminees' => $hasZeroInUE,
                'credits' => $ue->credits ?? 0
            ];
        }

        // Trier les UE (logique existante...)
        usort($uesData, function($a, $b) {
            $nomA = $a['ue']->abr ?? $a['ue']->nom;
            $nomB = $b['ue']->abr ?? $b['ue']->nom;
            
            $extraireNumero = function($nom) {
                if (preg_match('/UE\s*(\d+)/i', $nom, $matches)) {
                    return (int) $matches[1];
                }
                return 999;
            };
            
            $numeroA = $extraireNumero($nomA);
            $numeroB = $extraireNumero($nomB);
            
            if ($numeroA !== 999 && $numeroB !== 999) {
                return $numeroA - $numeroB;
            }
            
            if ($numeroA !== 999 && $numeroB === 999) return -1;
            if ($numeroA === 999 && $numeroB !== 999) return 1;
            
            return strcasecmp($nomA, $nomB);
        });

        // Moyenne générale
        $moyenneGenerale = count($moyennesUE) > 0 ? 
            round(array_sum($moyennesUE) / count($moyennesUE), 2) : 0;

        // ✅ NOUVEAU : Déterminer la décision avec délibération
        $decision = $this->determinerDecisionAvecDeliberation(
            $moyenneGenerale, 
            $creditsValides, 
            $totalCredits, 
            $hasNoteEliminatoire, 
            $session, 
            $sessionDeliberee, 
            $parametresDeliberation
        );

        return [
            'etudiant' => $etudiant,
            'session' => $session,
            'ues_data' => $uesData,
            'synthese' => [
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => $creditsValides,
                'total_credits' => $totalCredits,
                'pourcentage_credits' => $totalCredits > 0 ? round(($creditsValides / $totalCredits) * 100, 1) : 0,
                'decision' => $decision,
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'session_deliberee' => $sessionDeliberee, // ✅ NOUVEAU
                'parametres_deliberation' => $parametresDeliberation // ✅ NOUVEAU
            ],
            'date_generation' => now()->format('d/m/Y à H:i:s'),
            'header_image_base64' => $this->getHeaderImageBase64()
        ];
    }


    /**
     * MÉTHODE CORRIGÉE : Déterminer la décision avec délibération (75% de crédits)
     */
    private function determinerDecisionAvecDeliberation(
        $moyenneGenerale, 
        $creditsValides, 
        $totalCredits, 
        $hasNoteEliminatoire, 
        $session, 
        $sessionDeliberee, 
        $parametresDeliberation
    ) {
        // Si pas de délibération, utiliser la logique normale
        if (!$sessionDeliberee || !$parametresDeliberation) {
            return $this->determinerDecision($moyenneGenerale, $creditsValides, $totalCredits, $hasNoteEliminatoire, $session->type);
        }

        // LOGIQUE DE DÉLIBÉRATION CORRIGÉE
        $pourcentageCredits = $totalCredits > 0 ? ($creditsValides / $totalCredits) * 100 : 0;

        if ($session->type === 'Normale') {
            // SESSION 1 avec délibération
            
            // 1. PRIORITÉ ABSOLUE : Note éliminatoire = rattrapage (même avec 75% de crédits)
            if ($hasNoteEliminatoire) {
                return 'rattrapage';
            }

            // 2. RÈGLE DÉLIBÉRATION : >= 75% crédits + moyenne >= 10 + aucune note 0 = ADMIS
            if ($pourcentageCredits >= 75 && $moyenneGenerale >= 10) {
                return 'admis';
            }

            // 3. Sinon rattrapage
            return 'rattrapage';

        } else {
            // SESSION 2 (rattrapage) avec délibération
            
            // 1. PRIORITÉ ABSOLUE : Note éliminatoire = exclusion
            if ($hasNoteEliminatoire) {
                return 'excluss';
            }

            // 2. RÈGLE DÉLIBÉRATION S2 : >= 67% crédits (40/60) + moyenne >= 10 = ADMIS
            if ($pourcentageCredits >= 67 && $moyenneGenerale >= 10) { // 67% ≈ 40 crédits sur 60
                return 'admis';
            }

            // 3. Si >= 33% crédits (20/60) = redoublant
            if ($pourcentageCredits >= 33) {
                return 'redoublant';
            }

            // 4. Sinon exclusion
            return 'excluss';
        }
    }

    private function determinerDecision($moyenne, $creditsValides, $totalCredits, $hasNoteEliminatoire, $typeSession)
    {
        if ($typeSession === 'Normale') {
            if ($hasNoteEliminatoire) {
                return 'rattrapage';
            }
            return ($moyenne >= 10 && $creditsValides >= $totalCredits) ? 'admis' : 'rattrapage';
        } else {
            if ($hasNoteEliminatoire) {
                return 'exclus';
            }
            if ($moyenne >= 10 && $creditsValides >= 40) {
                return 'admis';
            }
            return $creditsValides >= 20 ? 'redoublant' : 'excluss';
        }
    }


    public function render()
    {
        $this->calculerStatistiques(); // S'assurer que les stats sont à jour
        
        return view('livewire.resultats.releve-notes', [
            'etudiants' => $this->getEtudiants()
        ]);
    }
}