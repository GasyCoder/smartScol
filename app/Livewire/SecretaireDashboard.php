<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Manchette;
use App\Models\Copie;
use App\Models\Niveau;

class SecretaireDashboard extends Component
{
    // DONNÉES
    public array $statsGlobales = [];
    public array $statsPersonnelles = [];
    public array $statsParNiveau = [];
    public array $activiteRecente = [];
    public string $sessionType = 'normale';
    
    // UI
    public bool $isLoading = false;

    public function mount()
    {
        $this->sessionType = Manchette::getCurrentSessionType();
        $this->loadAllStats();
    }

    public function render()
    {
        return view('livewire.secretaire-dashboard');
    }

    public function refreshStats()
    {
        $this->isLoading = true;
        $this->loadAllStats();
        $this->isLoading = false;
        
        toastr()->success('Statistiques actualisées !', ['timeOut' => 2000]);
    }

    // CHARGEMENT DONNÉES GLOBALES
    public function loadAllStats()
    {
        try {
            $sessionId = Manchette::getCurrentSessionId();
            $userId = Auth::id();
            
            if (!$sessionId) {
                $this->resetStats();
                return;
            }

            // STATS GLOBALES
            $this->statsGlobales = [
                'total_manchettes' => $this->getTotalManchettes($sessionId),
                'manchettes_aujourdhui' => $this->getManchettesAujourdhui($sessionId),
                'total_copies' => $this->getTotalCopies($sessionId),
                'copies_aujourdhui' => $this->getCopiesAujourdhui($sessionId),
                'moyenne_generale' => $this->getMoyenne($sessionId),
                'matieres_actives' => $this->getMatieresActives($sessionId)
            ];

            // Calculs dérivés
            $this->statsGlobales['pourcentage_global'] = $this->statsGlobales['total_manchettes'] > 0 
                ? round(($this->statsGlobales['total_copies'] / $this->statsGlobales['total_manchettes']) * 100, 1) 
                : 0;
            $this->statsGlobales['restantes_global'] = max(0, $this->statsGlobales['total_manchettes'] - $this->statsGlobales['total_copies']);

            // STATS PERSONNELLES
            $this->statsPersonnelles = [
                'mes_manchettes' => $this->getMesManchettes($sessionId, $userId),
                'mes_manchettes_aujourdhui' => $this->getMesManchettesAujourdhui($sessionId, $userId),
                'mes_copies' => $this->getMesCopies($sessionId, $userId),
                'mes_copies_aujourdhui' => $this->getMesCopiesAujourdhui($sessionId, $userId)
            ];
            
            // STATS PAR NIVEAU
            $this->statsParNiveau = $this->getStatsParNiveau($sessionId, $userId);
            
            // ACTIVITÉ RÉCENTE
            $this->activiteRecente = $this->getActiviteRecente($sessionId, $userId);
            
        } catch (\Exception $e) {
            logger('Erreur loadAllStats Dashboard: ' . $e->getMessage());
            $this->resetStats();
        }
    }

    // MÉTHODES SIMPLIFIÉES GLOBALES
    private function getTotalManchettes($sessionId)
    {
        return DB::table('manchettes')
            ->where('session_exam_id', $sessionId)
            ->count();
    }

    private function getManchettesAujourdhui($sessionId)
    {
        return DB::table('manchettes')
            ->where('session_exam_id', $sessionId)
            ->whereDate('created_at', today())
            ->count();
    }

    private function getTotalCopies($sessionId)
    {
        return DB::table('copies')
            ->where('session_exam_id', $sessionId)
            ->count();
    }

    private function getCopiesAujourdhui($sessionId)
    {
        return DB::table('copies')
            ->where('session_exam_id', $sessionId)
            ->whereDate('created_at', today())
            ->count();
    }

    private function getMoyenne($sessionId)
    {
        return round(
            DB::table('copies')
                ->where('session_exam_id', $sessionId)
                ->avg('note') ?? 0, 
            2
        );
    }

    private function getMatieresActives($sessionId)
    {
        return DB::table('manchettes')
            ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
            ->where('manchettes.session_exam_id', $sessionId)
            ->distinct('codes_anonymat.ec_id')
            ->count();
    }

    // MES STATS PERSONNELLES
    private function getMesManchettes($sessionId, $userId)
    {
        return DB::table('manchettes')
            ->where('session_exam_id', $sessionId)
            ->where('saisie_par', $userId)
            ->count();
    }

    private function getMesManchettesAujourdhui($sessionId, $userId)
    {
        return DB::table('manchettes')
            ->where('session_exam_id', $sessionId)
            ->where('saisie_par', $userId)
            ->whereDate('created_at', today())
            ->count();
    }

    private function getMesCopies($sessionId, $userId)
    {
        return DB::table('copies')
            ->where('session_exam_id', $sessionId)
            ->where('saisie_par', $userId)
            ->count();
    }

    private function getMesCopiesAujourdhui($sessionId, $userId)
    {
        return DB::table('copies')
            ->where('session_exam_id', $sessionId)
            ->where('saisie_par', $userId)
            ->whereDate('created_at', today())
            ->count();
    }

    private function getStatsParNiveau($sessionId, $userId)
    {
        try {
            $niveaux = Niveau::where('is_active', true)
                ->orderBy('id')
                ->get();

            $stats = [];

            foreach ($niveaux as $niveau) {
                // Stats pour ce niveau
                $totalManchettes = DB::table('manchettes')
                    ->join('examens', 'manchettes.examen_id', '=', 'examens.id')
                    ->where('manchettes.session_exam_id', $sessionId)
                    ->where('examens.niveau_id', $niveau->id)
                    ->count();

                $totalCopies = DB::table('copies')
                    ->join('examens', 'copies.examen_id', '=', 'examens.id')
                    ->where('copies.session_exam_id', $sessionId)
                    ->where('examens.niveau_id', $niveau->id)
                    ->count();

                $mesManchettes = DB::table('manchettes')
                    ->join('examens', 'manchettes.examen_id', '=', 'examens.id')
                    ->where('manchettes.session_exam_id', $sessionId)
                    ->where('manchettes.saisie_par', $userId)
                    ->where('examens.niveau_id', $niveau->id)
                    ->count();

                $mesCopies = DB::table('copies')
                    ->join('examens', 'copies.examen_id', '=', 'examens.id')
                    ->where('copies.session_exam_id', $sessionId)
                    ->where('copies.saisie_par', $userId)
                    ->where('examens.niveau_id', $niveau->id)
                    ->count();

                // Ajouter seulement les niveaux qui ont des données
                if ($totalManchettes > 0 || $totalCopies > 0) {
                    $stats[] = [
                        'niveau_id' => $niveau->id,
                        'niveau_nom' => $niveau->nom,
                        'niveau_abr' => $niveau->abr,
                        'total_manchettes' => $totalManchettes,
                        'total_copies' => $totalCopies,
                        'mes_manchettes' => $mesManchettes,
                        'mes_copies' => $mesCopies,
                        'pourcentage' => $totalManchettes > 0 ? round(($totalCopies / $totalManchettes) * 100, 1) : 0
                    ];
                }
            }

            return $stats;
            
        } catch (\Exception $e) {
            logger('Erreur getStatsParNiveau: ' . $e->getMessage());
            return [];
        }
    }

    private function getActiviteRecente($sessionId, $userId)
    {
        try {
            $activites = [];

            // MES manchettes récentes
            $manchettes = DB::table('manchettes')
                ->join('etudiants', 'manchettes.etudiant_id', '=', 'etudiants.id')
                ->join('codes_anonymat', 'manchettes.code_anonymat_id', '=', 'codes_anonymat.id')
                ->join('ecs', 'codes_anonymat.ec_id', '=', 'ecs.id')
                ->join('examens', 'manchettes.examen_id', '=', 'examens.id')
                ->join('niveaux', 'examens.niveau_id', '=', 'niveaux.id')
                ->where('manchettes.session_exam_id', $sessionId)
                ->where('manchettes.saisie_par', $userId)
                ->select([
                    'manchettes.created_at',
                    'codes_anonymat.code_complet',
                    'etudiants.nom as etudiant_nom',
                    'etudiants.prenom as etudiant_prenom',
                    'ecs.abr as ec_abr',
                    'niveaux.abr as niveau_abr'
                ])
                ->orderBy('manchettes.created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($manchettes as $m) {
                $activites[] = [
                    'type' => 'ma_manchette',
                    'message' => 'J\'ai créé la manchette: ' . $m->code_complet,
                    'etudiant' => $m->etudiant_nom . ' ' . $m->etudiant_prenom,
                    'matiere' => $m->ec_abr,
                    'niveau' => $m->niveau_abr,
                    'date' => \Carbon\Carbon::parse($m->created_at),
                    'icon' => '🏷️',
                    'color' => 'blue'
                ];
            }

            // MES copies récentes
            $copies = DB::table('copies')
                ->join('codes_anonymat', 'copies.code_anonymat_id', '=', 'codes_anonymat.id')
                ->join('ecs', 'codes_anonymat.ec_id', '=', 'ecs.id')
                ->join('examens', 'copies.examen_id', '=', 'examens.id')
                ->join('niveaux', 'examens.niveau_id', '=', 'niveaux.id')
                ->where('copies.session_exam_id', $sessionId)
                ->where('copies.saisie_par', $userId)
                ->select([
                    'copies.created_at',
                    'copies.note',
                    'ecs.abr as ec_abr',
                    'niveaux.abr as niveau_abr'
                ])
                ->orderBy('copies.created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($copies as $c) {
                $activites[] = [
                    'type' => 'ma_copie',
                    'message' => 'J\'ai saisi la note: ' . $c->note . '/20',
                    'etudiant' => '',
                    'matiere' => $c->ec_abr,
                    'niveau' => $c->niveau_abr,
                    'date' => \Carbon\Carbon::parse($c->created_at),
                    'icon' => '📝',
                    'color' => 'green'
                ];
            }

            // Trier par date
            usort($activites, fn($a, $b) => $b['date'] <=> $a['date']);
            
            return array_slice($activites, 0, 10);
            
        } catch (\Exception $e) {
            logger('Erreur getActiviteRecente: ' . $e->getMessage());
            return [];
        }
    }

    private function resetStats()
    {
        $this->statsGlobales = [
            'total_manchettes' => 0,
            'manchettes_aujourdhui' => 0,
            'total_copies' => 0,
            'copies_aujourdhui' => 0,
            'moyenne_generale' => 0,
            'matieres_actives' => 0,
            'pourcentage_global' => 0,
            'restantes_global' => 0
        ];
        
        $this->statsPersonnelles = [
            'mes_manchettes' => 0,
            'mes_manchettes_aujourdhui' => 0,
            'mes_copies' => 0,
            'mes_copies_aujourdhui' => 0
        ];
        
        $this->statsParNiveau = [];
        $this->activiteRecente = [];
    }
}