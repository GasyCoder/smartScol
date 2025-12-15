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
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\DeliberPaces;

class ReleveNotesOriginale extends Component
{
    use WithPagination;

    // Filtres
    public $selectedNiveau;
    public $selectedParcours;
    public $selectedDecision;  
    public $selectedSession;
    public $selectedAnneeUniv;
    public $search = '';
    public $statistiques = [];

    // Collections
    public $niveaux;
    public $parcours;
    public $sessions;
    public $anneesUniversitaires;
    public $anneeUnivLibelle;
    public $sessionType;

    public function mount()
    {
        $this->loadData();
        $this->setDefaults();
    }

    private function loadData()
    {
        $this->niveaux = Niveau::where('is_active', true)->orderBy('nom')->get();
        $this->parcours = collect();
        $this->sessions = collect();
        $this->anneesUniversitaires = AnneeUniversitaire::orderBy('date_start', 'desc')->get();
    }

    private function setDefaults()
    {
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        
        if ($anneeActive) {
            $this->selectedAnneeUniv = $anneeActive->id;
            $this->anneeUnivLibelle = $anneeActive->getLibelleAttribute();
            
            $this->loadSessions();
            
            $sessionNormale = $this->sessions->firstWhere('type', 'Normale');
            
            if ($sessionNormale) {
                $this->selectedSession = $sessionNormale->id;
                $this->sessionType = $sessionNormale->type;
            } elseif ($this->sessions->isNotEmpty()) {
                $premiereSession = $this->sessions->first();
                $this->selectedSession = $premiereSession->id;
                $this->sessionType = $premiereSession->type;
            }
        }
    }

    private function loadSessions()
    {
        if ($this->selectedAnneeUniv) {
            $this->sessions = SessionExam::where('annee_universitaire_id', $this->selectedAnneeUniv)
                ->where('is_active', true)
                ->orderByRaw("CASE WHEN type = 'Normale' THEN 1 WHEN type = 'Rattrapage' THEN 2 ELSE 3 END")
                ->get();
        } else {
            $this->sessions = collect();
        }
    }

    // ✅ NOUVELLE PROPRIÉTÉ COMPUTED : Décisions disponibles selon la session
    public function getDecisionsDisponiblesProperty()
    {
        if (!$this->sessionType) {
            return [
                'admis' => 'Admis',
                'rattrapage' => 'Rattrapage',
                'redoublant' => 'Redoublant',
                'exclus' => 'Exclus'
            ];
        }

        if ($this->sessionType === 'Rattrapage') {
            // ✅ En session de rattrapage : PAS de "Rattrapage" comme décision
            return [
                'admis' => 'Admis',
                'redoublant' => 'Redoublant',
                'exclus' => 'Exclus'
            ];
        }

        // Session Normale : toutes les décisions possibles
        return [
            'admis' => 'Admis',
            'rattrapage' => 'Rattrapage',
            'redoublant' => 'Redoublant',
            'exclus' => 'Exclus'
        ];
    }

    private function calculerStatistiques()
    {
        if (!$this->selectedSession) {
            $this->statistiques = [];
            return;
        }

        try {
            $etudiantsQuery = Etudiant::query()
                ->where('is_active', true);

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

            $etudiantsQuery->whereHas('resultatsFinaux', function($q) {
                $q->where('session_exam_id', $this->selectedSession)
                ->where('statut', ResultatFinal::STATUT_PUBLIE);
                
                if ($this->selectedDecision) {
                    $q->where('decision', $this->selectedDecision);
                }
            });

            $etudiants = $etudiantsQuery->get();

            $decisionsCount = [
                'admis' => 0,
                'rattrapage' => 0,
                'redoublant' => 0,
                'exclus' => 0
            ];

            foreach ($etudiants as $etudiant) {
                $decision = ResultatFinal::where('session_exam_id', $this->selectedSession)
                    ->where('etudiant_id', $etudiant->id)
                    ->where('statut', ResultatFinal::STATUT_PUBLIE)
                    ->value('decision');

                if ($decision && isset($decisionsCount[$decision])) {
                    $decisionsCount[$decision]++;
                }
            }

            $total = $etudiants->count();
            $admis = $decisionsCount['admis'];
            $rattrapage = $decisionsCount['rattrapage'];
            $redoublant = $decisionsCount['redoublant'];
            $exclus = $decisionsCount['exclus'];
            $autres = $redoublant + $exclus;

            $this->statistiques = [
                'total' => $total,
                'admis' => $admis,
                'rattrapage' => $rattrapage,
                'redoublant' => $redoublant,
                'exclus' => $exclus,
                'autres' => $autres,
                'pourcentage_admis' => $total > 0 ? round(($admis / $total) * 100, 1) : 0,
                'pourcentage_rattrapage' => $total > 0 ? round(($rattrapage / $total) * 100, 1) : 0,
                'pourcentage_autres' => $total > 0 ? round(($autres / $total) * 100, 1) : 0,
            ];

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
        $this->calculerStatistiques();
        $this->resetPage();
    }

    public function updatedSelectedDecision()
    {
        $this->calculerStatistiques();
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->calculerStatistiques();
        $this->resetPage();
    }

    // ✅ MODIFIÉ : Reset la décision si elle n'est plus valide
    public function updatedSelectedSession()
    {
        if ($this->selectedSession) {
            $session = SessionExam::find($this->selectedSession);
            if ($session) {
                $this->sessionType = $session->type;
                
                // ✅ Reset la décision si "rattrapage" est sélectionné en session Rattrapage
                if ($this->sessionType === 'Rattrapage' && $this->selectedDecision === 'rattrapage') {
                    $this->selectedDecision = '';
                }
            }
        }
        $this->calculerStatistiques();
        $this->resetPage();
    }

    public function updatedSelectedAnneeUniv()
    {
        $this->loadSessions();
        
        $sessionNormale = $this->sessions->firstWhere('type', 'Normale');
        if ($sessionNormale) {
            $this->selectedSession = $sessionNormale->id;
            $this->sessionType = $sessionNormale->type;
        } elseif ($this->sessions->isNotEmpty()) {
            $premiereSession = $this->sessions->first();
            $this->selectedSession = $premiereSession->id;
            $this->sessionType = $premiereSession->type;
        } else {
            $this->selectedSession = null;
            $this->sessionType = null;
        }
        
        if ($this->selectedAnneeUniv) {
            $annee = AnneeUniversitaire::find($this->selectedAnneeUniv);
            $this->anneeUnivLibelle = $annee?->getLibelleAttribute() ?? 'N/A';
        }
        
        $this->calculerStatistiques();
        $this->resetPage();
    }

    public function getEtudiants()
    {
        $query = Etudiant::query()
            ->with(['niveau', 'parcours'])
            ->where('is_active', true);

        if ($this->selectedNiveau) {
            $query->where('niveau_id', $this->selectedNiveau);
        }

        if ($this->selectedParcours) {
            $query->where('parcours_id', $this->selectedParcours);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nom', 'like', '%' . $this->search . '%')
                  ->orWhere('prenom', 'like', '%' . $this->search . '%')
                  ->orWhere('matricule', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedSession) {
            $query->whereHas('resultatsFinaux', function($q) {
                $q->where('session_exam_id', $this->selectedSession)
                  ->where('statut', ResultatFinal::STATUT_PUBLIE);
                
                if ($this->selectedDecision) {
                    $q->where('decision', $this->selectedDecision);
                }
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
            
            $pdf = Pdf::loadView('exports.releve-notes-originale', $donneesReleve)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);

            $nomFichier = "Releve_Notes_Originale_{$donneesReleve['etudiant']->matricule}_{$donneesReleve['session']->type}.pdf";

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
            $imagePath = public_path('assets/images/header.png');
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
        // ===== 1. RÉCUPÉRATION DES DONNÉES DE BASE =====
        $etudiant = Etudiant::with(['niveau', 'parcours'])->findOrFail($etudiantId);
        $session = SessionExam::with('anneeUniversitaire')->findOrFail($sessionId);

        // Vérifier si la session a été délibérée
        $sessionDeliberee = $session->estDeliberee();
        $parametresDeliberation = $sessionDeliberee ? $session->getParametresDeliberation() : null;

        // ===== 2. RÉCUPÉRATION DES RÉSULTATS =====
        $resultats = ResultatFinal::with(['ec.ue', 'examen'])
            ->where('session_exam_id', $sessionId)
            ->where('etudiant_id', $etudiantId)
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->get();

        if ($resultats->isEmpty()) {
            throw new \Exception('Aucun résultat trouvé pour cet étudiant dans cette session.');
        }

        // ✅ Lire la décision depuis la BDD (ne pas recalculer)
        $decisionDB = $resultats->first()->decision ?? 'non_definie';

        // ===== 3. CALCUL DES MOYENNES PAR UE =====
        $resultatsParUE = $resultats->groupBy('ec.ue_id');
        $uesData = [];
        $moyennesUE = [];
        $totalCredits = 0;
        $creditsValides = 0;
        $hasNoteEliminatoire = false;
        $hasNoteEliminatoirePACES = false; // ✅ NOUVEAU : Spécifique pour PACES

        foreach ($resultatsParUE as $ueId => $resultatsUE) {
            $ue = $resultatsUE->first()->ec->ue;
            $creditsUE = floatval($ue->credits ?? 0);
            $totalCredits += $creditsUE;

            $notesEC = [];
            $notesValues = [];
            $hasZeroInUE = false;

            foreach ($resultatsUE as $resultat) {
                $noteEC = floatval($resultat->note);
                
                $notesEC[] = [
                    'ec' => $resultat->ec,
                    'note' => $noteEC,
                    'est_eliminatoire' => $noteEC == 0
                ];
                
                $notesValues[] = $noteEC;
                
                if ($noteEC == 0) {
                    $hasZeroInUE = true;
                    $hasNoteEliminatoire = true;
                }
            }

            $moyenneUE = count($notesValues) > 0 ? 
                round(array_sum($notesValues) / count($notesValues), 2) : 0;

            $ueValidee = ($moyenneUE >= 10) && !$hasZeroInUE;
            $creditsValidesUE = $ueValidee ? $creditsUE : 0;
            $creditsValides += $creditsValidesUE;
            $moyennesUE[] = $moyenneUE;

            // ✅ NOUVEAU : Détecter si PACES avec moyenne >= 10 mais crédits = 0 à cause d'une note éliminatoire
            if ($etudiant->niveau && $etudiant->niveau->abr === 'PACES') {
                if ($moyenneUE >= 10 && $creditsValidesUE == 0 && $hasZeroInUE) {
                    $hasNoteEliminatoirePACES = true;
                }
            }

            $uesData[] = [
                'ue' => $ue,
                'notes_ec' => $notesEC,
                'moyenne_ue' => $moyenneUE,
                'validee' => $ueValidee,
                'eliminees' => $hasZeroInUE,
                'credits' => $creditsUE,
                'credits_valides' => $creditsValidesUE
            ];
        }

        // ===== 4. TRI DES UE PAR NUMÉRO =====
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

        // ===== 5. CALCUL MOYENNE GÉNÉRALE =====
        $moyenneGenerale = count($moyennesUE) > 0 ? 
            round(array_sum($moyennesUE) / count($moyennesUE), 2) : 0;

        $decision = $decisionDB;

        // ===== 6. CALCUL DU NIVEAU SUIVANT ET MESSAGES =====
        $niveauSuivant = null;
        $messageAdmission = 'ADMIS(E)';
        $messageRedoublement = 'AUTORISÉ(E) À REDOUBLER';
        $estSortantIfirp = false;

        if ($etudiant->niveau) {
            $niveauId = $etudiant->niveau->id;
            $niveauAbr = $etudiant->niveau->abr ?? '';
            
            $isL3 = ($niveauId == 3 || $niveauAbr === 'L3');
            $parcoursAbr = $etudiant->parcours->abr ?? '';
            $estSortantIfirp = $isL3 && in_array($parcoursAbr, ['INF-G', 'INF-A', 'MAI']);
            
            if ($estSortantIfirp) {
                $messageAdmission = 'SORTANT(E)';
                $messageRedoublement = "AUTORISÉ(E) À REDOUBLER EN L3 (3e année)";
                $niveauSuivant = null;
            }
            elseif ($niveauId >= 2) {
                $numeroActuel = null;
                
                if (preg_match('/^L(\d+)$/i', $niveauAbr, $matches)) {
                    $numeroActuel = (int)$matches[1];
                }
                elseif (preg_match('/(\d+)(?:e|ère|eme)/i', $etudiant->niveau->nom, $matches)) {
                    $numeroActuel = (int)$matches[1];
                }
                elseif (is_numeric($niveauAbr)) {
                    $numeroActuel = (int)$niveauAbr;
                }
                
                if ($numeroActuel) {
                    $numeroSuivant = $numeroActuel + 1;
                    
                    if ($numeroSuivant == 2) {
                        $niveauSuivant = "L2 (2e année)";
                    } elseif ($numeroSuivant == 3) {
                        $niveauSuivant = "L3 (3e année)";
                    } else {
                        $niveauSuivant = $numeroSuivant . "e année";
                    }
                    
                    if ($session->type === 'Rattrapage') {
                        $messageAdmission = "ADMIS(E) EN {$niveauSuivant} APRÈS REPÊCHAGE";
                    } else {
                        $messageAdmission = "ADMIS(E) EN {$niveauSuivant} À LA PREMIÈRE SESSION";
                    }
                    
                    if ($numeroActuel == 2) {
                        $messageRedoublement = "AUTORISÉ(E) À REDOUBLER EN L2 (2e année)";
                    } elseif ($numeroActuel == 3) {
                        $messageRedoublement = "AUTORISÉ(E) À REDOUBLER EN L3 (3e année)";
                    } else {
                        $messageRedoublement = "AUTORISÉ(E) À REDOUBLER EN {$numeroActuel}e année";
                    }
                }
            }
        }

        // ===== 7. GÉNÉRATION DU QR CODE =====
        $qrCodeData = "RELEVÉ DE NOTES\n\n" .
            "Année Universitaire: {$session->anneeUniversitaire->libelle}\n" .
            "Matricule: {$etudiant->matricule}\n" .
            "Nom: " . mb_strtoupper($etudiant->nom, 'UTF-8') . "\n" .
            "Prénom: " . ucfirst($etudiant->prenom ?? '') . "\n" .
            "Niveau: " . ($etudiant->niveau?->nom ?? 'N/A') . "\n" .
            "Parcours: " . ($etudiant->parcours?->nom ?? 'Tronc Commun') . "\n\n" .
            "Moyenne Générale: " . number_format($moyenneGenerale, 2) . "/20\n" .
            "Crédits Validés: " . number_format($creditsValides, 2) . "/" . number_format($totalCredits, 2) . "\n" .
            "Décision: " . mb_strtoupper($decision, 'UTF-8') . "\n\n" .
            "Document officiel - Faculté de Médecine Mahajanga";

        $qrcodeImage = QrCode::size(150)
            ->encoding('UTF-8')
            ->errorCorrection('M')
            ->margin(1)
            ->generate($qrCodeData);

        // ===== 8. RÉCUPÉRATION DÉLIBÉRATION PACES =====
        $deliberation = null;
        
        try {
            $deliberation = DeliberPaces::where('niveau_id', $etudiant->niveau_id)
                ->where('parcours_id', $etudiant->parcours_id)
                ->where('session_exam_id', $sessionId)
                ->where('type', 'deliberation')
                ->latest('applique_at')
                ->first();
        } catch (\Exception $e) {
            Log::error('❌ Erreur récupération délibération PACES', [
                'error' => $e->getMessage(),
                'etudiant_id' => $etudiantId
            ]);
        }

        // ===== 9. RETOUR DES DONNÉES =====
        return [
            'etudiant' => $etudiant,
            'session' => $session,
            'ues_data' => $uesData,
            'synthese' => [
                'moyenne_generale' => $moyenneGenerale,
                'credits_valides' => round($creditsValides, 2),
                'total_credits' => $totalCredits,
                'pourcentage_credits' => $totalCredits > 0 ? round(($creditsValides / $totalCredits) * 100, 1) : 0,
                'decision' => $decision,
                'has_note_eliminatoire' => $hasNoteEliminatoire,
                'has_note_eliminatoire_paces' => $hasNoteEliminatoirePACES, // ✅ NOUVEAU
                'session_deliberee' => $sessionDeliberee,
                'parametres_deliberation' => $parametresDeliberation,
                'niveau_suivant' => $niveauSuivant,
                'message_admission' => $messageAdmission,
                'message_redoublement' => $messageRedoublement,
                'est_sortant_ifirp' => $estSortantIfirp
            ],
            'deliberation' => $deliberation,
            'date_generation' => now()->format('d/m/Y'),
            'header_image_base64' => $this->getHeaderImageBase64(),
            'qrcodeImage' => $qrcodeImage
        ];
    }

    public function render()
    {
        $this->calculerStatistiques();
        
        return view('livewire.resultats.releve-notes', [
            'etudiants' => $this->getEtudiants()
        ]);
    }
}