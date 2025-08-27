<?php

namespace App\Livewire\Examen;

use App\Models\EC;
use App\Models\UE;
use App\Models\Salle;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\Parcour;
use Livewire\Component;
use App\Models\ExamenEc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AddExamen extends Component
{
    // Propriétés de base pour l'examen
    public $niveau_id;
    public $parcours_id;
    public $duree = 120;
    public $note_eliminatoire;

    // Propriétés pour gérer les EC
    public $selectedEcs = [];
    public $usedEcIds = [];
    public $groupedEcs = [];

    // Propriétés pour les salles et codes par EC
    public $ecSalles = [];
    public $ecCodes = [];

    // Propriétés pour contexte et UI
    public $niveauInfo = null;
    public $parcoursInfo = null;

    protected $rules = [
        'selectedEcs' => 'required|array|min:1',
        'selectedEcs.*' => 'exists:ecs,id',
        'niveau_id' => 'required|exists:niveaux,id',
        'parcours_id' => 'nullable|exists:parcours,id',
        'duree' => 'required|integer|min:15',
        'note_eliminatoire' => 'nullable|numeric|min:0|max:20',
        'ecCodes.*' => 'nullable|string|max:20',
        'ecSalles.*' => 'nullable|exists:salles,id',
    ];

    protected $messages = [
        'selectedEcs.required' => 'Vous devez sélectionner au moins une matière.',
        'selectedEcs.min' => 'Vous devez sélectionner au moins une matière.',
        'duree.required' => 'La durée est obligatoire.',
        'duree.min' => 'La durée minimum est de 15 minutes.',
        'ecCodes.*.max' => 'Le code ne peut pas dépasser 20 caractères.',
        'ecSalles.*.exists' => 'La salle sélectionnée n\'existe pas.',
    ];

    public function mount($niveau = null, $parcour = null)
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Accès non autorisé.');
        }

        // Initialisation des valeurs par défaut
        $this->duree = 120;

        // Chargement du niveau
        if ($niveau) {
            $this->niveau_id = $niveau;
            $this->loadNiveauInfo();
        }

        // Chargement du parcours
        if ($parcour) {
            $this->parcours_id = $parcour;
            $this->loadParcoursInfo();
        }

        // Charger tous les ECs disponibles si un niveau est sélectionné
        if ($this->niveau_id && $this->parcours_id) {
            $this->loadAvailableEcs();
        }
    }

    private function loadNiveauInfo()
    {
        try {
            $niveau = Niveau::find($this->niveau_id);
            if ($niveau) {
                $this->niveauInfo = [
                    'id' => $niveau->id,
                    'nom' => $niveau->nom,
                    'abr' => $niveau->abr,
                    'has_parcours' => $niveau->has_parcours
                ];
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des données du niveau: ' . $e->getMessage());
        }
        return false;
    }

    private function loadParcoursInfo()
    {
        try {
            $parcours = Parcour::find($this->parcours_id);
            if ($parcours) {
                $this->parcoursInfo = [
                    'id' => $parcours->id,
                    'nom' => $parcours->nom,
                    'abr' => $parcours->abr
                ];
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des données du parcours: ' . $e->getMessage());
        }
        return false;
    }

    private function cleanInputValue($value)
    {
        if (is_array($value) && isset($value['value'])) {
            return $value['value'];
        }
        if (is_array($value) && count($value) > 0) {
            return $value[0];
        }
        return $value;
    }

    public function loadAvailableEcs()
    {
        try {
            $niveau_id = $this->cleanInputValue($this->niveau_id);
            $parcours_id = $this->cleanInputValue($this->parcours_id);

            // 1. Récupérer les ECs déjà utilisés dans TOUS les examens
            $this->usedEcIds = \DB::table('examen_ec')
                ->join('examens', 'examen_ec.examen_id', '=', 'examens.id')
                ->whereNull('examens.deleted_at')
                ->pluck('ec_id')
                ->unique()
                ->toArray();

            // 2. Récupérer toutes les UEs du niveau/parcours
            $ueQuery = UE::where('niveau_id', $niveau_id);
            if ($parcours_id) {
                $ueQuery->where(function($q) use ($parcours_id) {
                    $q->where('parcours_id', $parcours_id)
                      ->orWhereNull('parcours_id');
                });
            }
            $ues = $ueQuery->orderBy('id', 'asc')->get();

            // 3. Construire les groupes d'ECs par UE
            $this->groupedEcs = [];
            foreach ($ues as $ue) {
                $ecs = EC::where('ue_id', $ue->id)
                    ->orderBy('id', 'asc')
                    ->get();

                if ($ecs->isNotEmpty()) {
                    $this->groupedEcs[] = [
                        'ue' => $ue,
                        'ecs' => $ecs
                    ];

                    // Initialiser les salles et codes pour tous les ECs
                    foreach ($ecs as $ec) {
                        if (!in_array($ec->id, $this->usedEcIds)) {
                            if (!isset($this->ecSalles[$ec->id])) {
                                $this->ecSalles[$ec->id] = '';
                            }
                            if (!isset($this->ecCodes[$ec->id])) {
                                $this->ecCodes[$ec->id] = '';
                            }
                        }
                    }
                }
            }

            Log::info('ECs groupés chargés', [
                'nbGroupes' => count($this->groupedEcs),
                'ecUtilisés' => count($this->usedEcIds)
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des ECs disponibles: ' . $e->getMessage());
            $this->groupedEcs = [];
        }
    }

    public function updatedNiveauId()
    {
        $this->selectedEcs = [];
        $this->ecSalles = [];
        $this->ecCodes = [];
        $this->loadAvailableEcs();
    }

    public function updatedParcoursId()
    {
        $this->selectedEcs = [];
        $this->ecSalles = [];
        $this->ecCodes = [];
        $this->loadAvailableEcs();
    }

    public function selectAllAvailableEcs()
    {
        $this->selectedEcs = [];

        foreach ($this->groupedEcs as $group) {
            foreach ($group['ecs'] as $ec) {
                if (!in_array($ec->id, $this->usedEcIds)) {
                    $this->selectedEcs[] = $ec->id;
                }
            }
        }

        toastr()->success(count($this->selectedEcs) . ' matière(s) sélectionnée(s)');
    }

    public function deselectAllEcs()
    {
        $this->selectedEcs = [];
        toastr()->info('Toutes les matières ont été désélectionnées');
    }

    public function updatedSelectedEcs()
    {
        Log::info('Selected ECs updated', ['selectedEcs' => $this->selectedEcs]);
        
        // Nettoyer les données des ECs désélectionnés
        $this->ecSalles = array_intersect_key($this->ecSalles, array_flip($this->selectedEcs));
        $this->ecCodes = array_intersect_key($this->ecCodes, array_flip($this->selectedEcs));

        // Initialiser les nouveaux ECs sélectionnés
        foreach ($this->selectedEcs as $ecId) {
            if (!isset($this->ecSalles[$ecId])) {
                $this->ecSalles[$ecId] = '';
            }
            if (!isset($this->ecCodes[$ecId])) {
                $this->ecCodes[$ecId] = '';
            }
        }
    }

    public function validateCodes()
    {
        $codes = array_filter($this->ecCodes, function($code) {
            return !empty(trim($code));
        });
        
        // Vérifier le format (2-3 caractères alphabétiques ou alphanumériques)
        foreach ($codes as $ecId => $code) {
            if (!preg_match('/^[A-Z0-9]{2,3}$/i', $code)) {
                $ec = EC::find($ecId);
                $nomEC = $ec ? $ec->nom : "EC {$ecId}";
                return [
                    'valid' => false,
                    'message' => "Le code '{$code}' pour {$nomEC} doit contenir 2-3 caractères (lettres/chiffres uniquement)"
                ];
            }
        }
        
        // Vérifier les doublons dans la saisie actuelle
        $duplicates = array_diff_assoc($codes, array_unique($codes));
        
        if (!empty($duplicates)) {
            return [
                'valid' => false,
                'message' => 'Codes en doublon détectés : ' . implode(', ', array_unique($duplicates))
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    public function genererCodesAutomatiquement()
    {
        if (empty($this->selectedEcs)) {
            toastr()->warning('Aucun EC sélectionné pour la génération de codes.');
            return;
        }

        $codesGeneres = 0;
        $codesExistants = 0;
        $usedCodes = array_filter($this->ecCodes);

        foreach ($this->selectedEcs as $ecId) {
            if (!empty($this->ecCodes[$ecId])) {
                $codesExistants++;
                continue;
            }

            // Générer un code automatique
            $codeGenere = $this->genererCodePourEC($usedCodes);
            
            if ($codeGenere) {
                $this->ecCodes[$ecId] = $codeGenere;
                $usedCodes[] = $codeGenere;
                $codesGeneres++;
            }
        }

        if ($codesGeneres > 0) {
            $message = "✅ {$codesGeneres} code(s) généré(s) automatiquement";
            if ($codesExistants > 0) {
                $message .= " ({$codesExistants} code(s) déjà existant(s) conservé(s))";
            }
            toastr()->success($message);
        } else if ($codesExistants > 0) {
            toastr()->info("Tous les codes existent déjà ({$codesExistants} EC(s))");
        } else {
            toastr()->error('Impossible de générer des codes automatiquement');
        }
    }

    private function genererCodePourEC($usedCodes)
    {
        // Pattern de génération : TA, TB, TC, SA, SB, SC, etc.
        $firstLetters = ['T', 'S', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $secondLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        
        foreach ($firstLetters as $first) {
            foreach ($secondLetters as $second) {
                $code = $first . $second;
                if (!in_array($code, $usedCodes)) {
                    return $code;
                }
            }
        }
        
        return null;
    }

    public function genererCodePourECSpecifique($ecId)
    {
        if (!in_array($ecId, $this->selectedEcs)) {
            toastr()->error('EC non sélectionné');
            return;
        }

        $usedCodes = array_filter($this->ecCodes);
        $codeGenere = $this->genererCodePourEC($usedCodes);
        
        if ($codeGenere) {
            $this->ecCodes[$ecId] = $codeGenere;
            
            $ec = EC::find($ecId);
            $nomEC = $ec ? $ec->nom : 'EC';
            
            toastr()->success("Code généré pour {$nomEC}: {$codeGenere}");
        } else {
            toastr()->error('Impossible de générer un code pour cet EC');
        }
    }

    public function reinitialiserCodeSpecifique($ecId)
    {
        if (!in_array($ecId, $this->selectedEcs)) {
            toastr()->error('EC non sélectionné');
            return;
        }

        $ec = EC::find($ecId);
        $nomEC = $ec ? $ec->nom : 'EC';
        
        if (!empty($this->ecCodes[$ecId])) {
            $ancienCode = $this->ecCodes[$ecId];
            $this->ecCodes[$ecId] = '';
            toastr()->success("Code '{$ancienCode}' supprimé pour {$nomEC}");
        } else {
            toastr()->info("Aucun code à supprimer pour {$nomEC}");
        }
    }

    public function reinitialiserTousLesCodes()
    {
        if (empty($this->selectedEcs)) {
            toastr()->warning('Aucun EC sélectionné');
            return;
        }

        $nbCodesSupprimes = 0;
        
        foreach ($this->selectedEcs as $ecId) {
            if (!empty($this->ecCodes[$ecId])) {
                $this->ecCodes[$ecId] = '';
                $nbCodesSupprimes++;
            }
        }

        if ($nbCodesSupprimes > 0) {
            toastr()->success("✅ {$nbCodesSupprimes} code(s) réinitialisé(s)");
        } else {
            toastr()->info('Aucun code à réinitialiser');
        }
    }

    public function updatedEcCodes($value, $ecId)
    {
        if (!empty($value)) {
            // Convertir en majuscules automatiquement
            $value = strtoupper(trim($value));
            $this->ecCodes[$ecId] = $value;

            // Vérifier le format
            if (!preg_match('/^[A-Z0-9]{2,3}$/', $value)) {
                toastr()->warning("Le code doit contenir 2-3 caractères (lettres/chiffres uniquement)");
                return;
            }

            // Vérifier les doublons dans les codes actuels
            $autresCodes = array_filter($this->ecCodes, function($code, $id) use ($ecId) {
                return $id != $ecId && !empty(trim($code));
            }, ARRAY_FILTER_USE_BOTH);

            if (in_array($value, $autresCodes)) {
                $this->ecCodes[$ecId] = '';
                toastr()->warning("Le code '{$value}' est déjà utilisé dans cette session.");
                return;
            }
        }
    }

    public function save()
    {
        $this->validate();

        try {
            // Validation des codes
            $codeValidation = $this->validateCodes();
            if (!$codeValidation['valid']) {
                toastr()->error($codeValidation['message']);
                return;
            }

            // Nettoyer les valeurs
            $niveau_id = $this->cleanInputValue($this->niveau_id);
            $parcours_id = $this->cleanInputValue($this->parcours_id);
            $note_eliminatoire = !empty($this->note_eliminatoire) ? 
                $this->cleanInputValue($this->note_eliminatoire) : null;

            // VÉRIFIER SI UN EXAMEN EXISTE DÉJÀ POUR CE NIVEAU/PARCOURS
            $examenExistant = Examen::where('niveau_id', $niveau_id)
                ->where('parcours_id', $parcours_id)
                ->first();

            // SI L'EXAMEN EXISTE DÉJÀ, ON L'UTILISE
            if ($examenExistant) {
                $examen = $examenExistant;
                Log::info('Examen existant réutilisé', ['examen_id' => $examen->id]);
            } else {
                // SINON ON CRÉE UN NOUVEL EXAMEN
                $examen = Examen::create([
                    'niveau_id' => $niveau_id,
                    'parcours_id' => $parcours_id,
                    'duree' => (int)$this->duree,
                    'note_eliminatoire' => $note_eliminatoire,
                ]);
                Log::info('Nouvel examen créé', ['examen_id' => $examen->id]);
            }

            // VÉRIFIER SI LES ECS SONT DÉJÀ ATTACHÉS À CET EXAMEN
            $ecsDejaAttaches = $examen->ecs()->pluck('ec_id')->toArray();
            $nouveauxECs = array_diff($this->selectedEcs, $ecsDejaAttaches);

            if (empty($nouveauxECs)) {
                toastr()->warning('Tous les ECs sélectionnés sont déjà attachés à cet examen.');
                return;
            }

            // ATTACHER LES NOUVEAUX ECS À L'EXAMEN
            foreach ($nouveauxECs as $ecId) {
                // Vérifier si l'EC est déjà utilisé dans un autre examen
                if (in_array($ecId, $this->usedEcIds)) {
                    $ec = EC::find($ecId);
                    toastr()->error("L'EC '{$ec->nom}' est déjà utilisé dans un autre examen !");
                    continue;
                }

                $salle = !empty($this->ecSalles[$ecId]) ? $this->ecSalles[$ecId] : null;
                $code_base = !empty($this->ecCodes[$ecId]) ? trim($this->ecCodes[$ecId]) : null;

                // Vérifier l'unicité du code dans cet examen (SEULEMENT LE CODE DOIT ÊTRE UNIQUE)
                if ($code_base && !ExamenEc::isCodeUniqueInExamen($code_base, $examen->id)) {
                    $ec = EC::find($ecId);
                    toastr()->error("Le code '{$code_base}' est déjà utilisé dans cet examen pour l'EC '{$ec->nom}'");
                    continue;
                }

                // SUPPRIMER LA VÉRIFICATION DE SALLE - MEME SALLE AUTORISÉE POUR DIFFÉRENTS ECS !
                // if ($salle && ExamenEc::isSalleUsedInExamen($salle, $examen->id)) {
                //     toastr()->error("La salle est déjà utilisée dans cet examen pour un autre EC");
                //     continue;
                // }

                // TOUT EST OK - ATTACHER L'EC
                $examen->ecs()->attach($ecId, [
                    'salle_id' => $salle,
                    'code_base' => $code_base,
                ]);

                Log::info('EC attaché à l\'examen', [
                    'examen_id' => $examen->id,
                    'ec_id' => $ecId,
                    'salle_id' => $salle,
                    'code_base' => $code_base
                ]);
            }

            // Mettre à jour la liste des ECs utilisés
            $this->usedEcIds = array_merge($this->usedEcIds, $nouveauxECs);
            $this->loadAvailableEcs();

            toastr()->success('✅ ECs ajoutés à l\'examen avec succès !');

            // Réinitialiser le formulaire
            $this->selectedEcs = [];
            $this->ecSalles = [];
            $this->ecCodes = [];

            return redirect()->route('examens.index', [
                'niveau' => $niveau_id,
                'parcours' => $parcours_id,
                'step' => 'examens'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            toastr()->error('Erreur : ' . $e->getMessage());
            return null;
        }
    }
    
    public function render()
    {
        $niveaux = Niveau::where('is_active', true)->get();
        $parcours = $this->niveau_id ? 
            Parcour::where('niveau_id', $this->cleanInputValue($this->niveau_id))->get() : [];
        $salles = Salle::orderBy('nom')->get();

        return view('livewire.examen.add-examen', [
            'niveaux' => $niveaux,
            'parcours' => $parcours,
            'salles' => $salles,
        ]);
    }
}