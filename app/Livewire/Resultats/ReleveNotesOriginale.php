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
                
                // Filtre par décision
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


    private function setDefaults()
    {
        // ✅ Récupérer l'année universitaire active
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        
        if ($anneeActive) {
            $this->selectedAnneeUniv = $anneeActive->id;
            $this->anneeUnivLibelle = $anneeActive->libelle;
            
            // ✅ Récupérer la session normale
            $sessionNormale = SessionExam::where('annee_universitaire_id', $anneeActive->id)
                ->where('type', 'Normale')
                ->first();
            
            if ($sessionNormale) {
                $this->selectedSession = $sessionNormale->id;
                $this->sessionType = $sessionNormale->type;
            }
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

    public function updatedSelectedSession()
    {
        $this->calculerStatistiques();
        $this->resetPage();
    }

    public function updatedSelectedAnneeUniv()
    {
        $this->loadSessions();
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
                
                // ✅ Filtre par décision
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
        $etudiant = Etudiant::with(['niveau', 'parcours'])->findOrFail($etudiantId);
        $session = SessionExam::with('anneeUniversitaire')->findOrFail($sessionId);

        $sessionDeliberee = $session->estDeliberee();
        $parametresDeliberation = $sessionDeliberee ? $session->getParametresDeliberation() : null;

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

        $resultatsParUE = $resultats->groupBy('ec.ue_id');
        $uesData = [];
        $moyennesUE = [];
        $totalCredits = 0;
        $creditsValides = 0;
        $hasNoteEliminatoire = false;

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

            // Moyenne UE = somme des notes / nombre d'ECs
            $moyenneUE = count($notesValues) > 0 ? 
                round(array_sum($notesValues) / count($notesValues), 2) : 0;

            // ✅ UE validée si moyenne >= 10 ET pas de note 0
            $ueValidee = ($moyenneUE >= 10) && !$hasZeroInUE;
            
            // ✅ LOGIQUE TOUT OU RIEN : Si UE validée → tous les crédits, sinon 0
            $creditsValidesUE = $ueValidee ? $creditsUE : 0;
            
            // Ajouter au total des crédits validés
            $creditsValides += $creditsValidesUE;

            $moyennesUE[] = $moyenneUE;

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

        // Trier les UE par numéro
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

        // Moyenne générale = moyenne des moyennes UE
        $moyenneGenerale = count($moyennesUE) > 0 ? 
            round(array_sum($moyennesUE) / count($moyennesUE), 2) : 0;

        // ✅ Utiliser la décision depuis la BDD
        $decision = $decisionDB;

        // ✅ QR Code avec texte formaté
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

        // ✅✅✅ AJOUT CRUCIAL : Récupérer les données de délibération PACES ✅✅✅
        $deliberation = null;
        
        try {
            // Chercher dans deliber_paces pour ce niveau/parcours/session
            $deliberation = DeliberPaces::where('niveau_id', $etudiant->niveau_id)
                ->where('parcours_id', $etudiant->parcours_id)
                ->where('session_exam_id', $sessionId)
                ->where('type', 'deliberation')
                ->latest('applique_at')
                ->first();
            
            // ✅ Debug : Logger les résultats
            if ($deliberation) {
                Log::info('✅ Délibération PACES trouvée', [
                    'etudiant_id' => $etudiantId,
                    'etudiant_nom' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'niveau' => $etudiant->niveau?->nom,
                    'parcours' => $etudiant->parcours?->nom,
                    'credit_min_r' => $deliberation->credit_min_r,
                    'credit_max_r' => $deliberation->credit_max_r,
                    'moyenne_requise' => $deliberation->moyenne_requise,
                    'type' => $deliberation->type,
                    'applique_at' => $deliberation->applique_at
                ]);
            } else {
                Log::warning('⚠️ Aucune délibération PACES trouvée', [
                    'etudiant_id' => $etudiantId,
                    'niveau_id' => $etudiant->niveau_id,
                    'niveau_nom' => $etudiant->niveau?->nom,
                    'parcours_id' => $etudiant->parcours_id,
                    'parcours_nom' => $etudiant->parcours?->nom,
                    'session_exam_id' => $sessionId
                ]);
                
                // ✅ Debug supplémentaire : Compter les délibérations disponibles
                $countDeliberations = DeliberPaces::where('session_exam_id', $sessionId)
                    ->where('type', 'deliberation')
                    ->count();
                
                Log::info('📊 Total délibérations pour cette session', [
                    'session_id' => $sessionId,
                    'count' => $countDeliberations
                ]);
            }
        } catch (\Exception $e) {
            Log::error('❌ Erreur récupération délibération PACES', [
                'error' => $e->getMessage(),
                'etudiant_id' => $etudiantId,
                'trace' => $e->getTraceAsString()
            ]);
        }

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
                'session_deliberee' => $sessionDeliberee,
                'parametres_deliberation' => $parametresDeliberation
            ],
            'deliberation' => $deliberation, // ✅✅✅ CRUCIAL : Passer la variable à la vue ✅✅✅
            'date_generation' => now()->format('d/m/Y'),
            'header_image_base64' => $this->getHeaderImageBase64(),
            'qrcodeImage' => $qrcodeImage
        ];
    }


    /**
     * ✅ MÉTHODE OPTIMISÉE : Utiliser has_rattrapage au lieu de détecter PACES
     */
    private function determinerDecisionAvecDeliberation(
        $moyenneGenerale, 
        $creditsValides, 
        $totalCredits, 
        $hasNoteEliminatoire, 
        $session, 
        $sessionDeliberee, 
        $parametresDeliberation,
        $etudiant = null
    ) {
        $hasRattrapage = true;
        if ($etudiant && $etudiant->niveau) {
            $hasRattrapage = $etudiant->niveau->has_rattrapage;
        }

        // Si pas de délibération, utiliser la logique normale
        if (!$sessionDeliberee || !$parametresDeliberation) {
            return $this->determinerDecision($moyenneGenerale, $creditsValides, $totalCredits, $hasNoteEliminatoire, $session->type, $hasRattrapage);
        }

        $pourcentageCredits = $totalCredits > 0 ? ($creditsValides / $totalCredits) * 100 : 0;

        if ($session->type === 'Normale') {
            // ===== SESSION 1 (NORMALE) =====
            
            // ❌ Moyenne catastrophique → Exclus direct (ou redoublant si niveau strict)
            if ($moyenneGenerale < 8) {
                return $hasRattrapage ? 'redoublant' : 'exclus';
            }
            
            // ❌ Note éliminatoire → Rattrapage obligatoire
            if ($hasNoteEliminatoire) {
                return $hasRattrapage ? 'rattrapage' : 'exclus';
            }

            // ✅ Admis si 75%+ crédits ET moyenne >= 10
            if ($pourcentageCredits >= 75 && $moyenneGenerale >= 10) {
                return 'admis';
            }

            // 🔄 Rattrapage si moyenne acceptable (8-10) ET au moins 50% crédits
            if ($moyenneGenerale >= 8 && $moyenneGenerale < 10 && $pourcentageCredits >= 50) {
                return $hasRattrapage ? 'rattrapage' : 'redoublant';
            }

            // ❌ Sinon redoublant ou exclus
            return $hasRattrapage ? 'redoublant' : 'exclus';

        } else {
            // ===== SESSION 2 (RATTRAPAGE) =====
            
            // ❌ Moyenne catastrophique → Exclus
            if ($moyenneGenerale < 8) {
                return 'exclus';
            }
            
            // ❌ Note éliminatoire → Exclus
            if ($hasNoteEliminatoire) {
                return 'exclus';
            }

            // ✅ Admis si 67%+ crédits ET moyenne >= 10
            if ($pourcentageCredits >= 67 && $moyenneGenerale >= 10) {
                return 'admis';
            }

            // 🔄 Redoublant si au moins 40% crédits ET moyenne >= 8
            if ($pourcentageCredits >= 40 && $moyenneGenerale >= 8) {
                return 'redoublant';
            }

            // ❌ Sinon exclus
            return 'exclus';
        }
    }


    /**
     * ✅ MÉTHODE OPTIMISÉE : Utiliser has_rattrapage
     */
    private function determinerDecision($moyenne, $creditsValides, $totalCredits, $hasNoteEliminatoire, $typeSession, $hasRattrapage = true)
    {
        $pourcentageCredits = $totalCredits > 0 ? ($creditsValides / $totalCredits) * 100 : 0;

        if ($typeSession === 'Normale') {
            // ===== SESSION 1 (NORMALE) =====
            
            // ❌ Moyenne catastrophique
            if ($moyenne < 8) {
                return $hasRattrapage ? 'redoublant' : 'exclus';
            }
            
            // ❌ Note éliminatoire
            if ($hasNoteEliminatoire) {
                return $hasRattrapage ? 'rattrapage' : 'exclus';
            }
            
            // ✅ Admis si moyenne >= 10 ET tous les crédits validés
            if ($moyenne >= 10 && $creditsValides >= $totalCredits) {
                return 'admis';
            }
            
            // 🔄 Rattrapage si moyenne acceptable
            if ($moyenne >= 8 && $pourcentageCredits >= 50) {
                return $hasRattrapage ? 'rattrapage' : 'redoublant';
            }
            
            return $hasRattrapage ? 'redoublant' : 'exclus';

        } else {
            // ===== SESSION 2 (RATTRAPAGE) =====
            
            // ❌ Moyenne catastrophique
            if ($moyenne < 8) {
                return 'exclus';
            }
            
            // ❌ Note éliminatoire
            if ($hasNoteEliminatoire) {
                return 'exclus';
            }
            
            // ✅ Admis si moyenne >= 10 ET au moins 67% crédits
            if ($moyenne >= 10 && $pourcentageCredits >= 67) {
                return 'admis';
            }
            
            // 🔄 Redoublant si au moins 40% crédits ET moyenne >= 8
            if ($pourcentageCredits >= 40 && $moyenne >= 8) {
                return 'redoublant';
            }
            
            return 'exclus';
        }
    }

    public function render()
    {
        $this->calculerStatistiques();
        
        return view('livewire.resultats.releve-notes', [
            'etudiants' => $this->getEtudiants()
        ]);
    }
}