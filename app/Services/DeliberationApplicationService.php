<?php

namespace App\Services;

use App\Models\SessionExam;
use App\Models\ResultatFinal;
use App\Services\CalculAcademiqueService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeliberationApplicationService
{
    protected $calculAcademiqueService;

    public function __construct(CalculAcademiqueService $calculAcademiqueService)
    {
        $this->calculAcademiqueService = $calculAcademiqueService;
    }

    /**
     * Processus complet d'application de délibération
     */
    public function appliquerDeliberationComplete(
        int $niveauId,
        ?int $parcoursId,
        ?SessionExam $sessionNormale,
        ?SessionExam $sessionRattrapage,
        array $deliberationParams,
        array $simulationDeliberation
    ): array {
        try {
            // Validation complète
            $this->validateAll($deliberationParams, $simulationDeliberation);
            
            // Récupération session
            $session = $this->getTargetSession($deliberationParams['session_type'], $sessionNormale, $sessionRattrapage);
            
            // Validation étudiants
            $this->validateStudents($session, $niveauId, $parcoursId);
            
            // Exécution
            $result = $this->executeDeliberation($niveauId, $parcoursId, $session, $deliberationParams);
            
            return [
                'success' => true,
                'result' => $result,
                'session' => $session,
                'message' => $this->formatSuccessMessage($result['statistiques'] ?? [])
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur délibération service complet', [
                'niveau_id' => $niveauId,
                'parcours_id' => $parcoursId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de l\'application: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validation complète des prérequis
     */
    private function validateAll(array $deliberationParams, array $simulationDeliberation): void
    {
        if (empty($deliberationParams)) {
            throw new \Exception('Paramètres de délibération manquants.');
        }

        if (empty($deliberationParams['session_type'])) {
            throw new \Exception('Type de session non défini.');
        }

        if (!Auth::user()->can('resultats.validation')) {
            throw new \Exception('Autorisation insuffisante pour appliquer une délibération.');
        }

        if (empty($simulationDeliberation) || ($simulationDeliberation['statistiques']['changements'] ?? 0) === 0) {
            throw new \Exception('Aucun changement à appliquer. Simulez d\'abord la délibération.');
        }
    }

    /**
     * Récupérer la session cible
     */
    private function getTargetSession(string $sessionType, ?SessionExam $sessionNormale, ?SessionExam $sessionRattrapage): SessionExam
    {
        $session = $sessionType === 'session1' ? $sessionNormale : $sessionRattrapage;

        if (!$session) {
            throw new \Exception("Session {$sessionType} non trouvée.");
        }

        return $session;
    }

    /**
     * Valider la disponibilité des étudiants
     */
    private function validateStudents(SessionExam $session, int $niveauId, ?int $parcoursId): void
    {
        $count = ResultatFinal::where('session_exam_id', $session->id)
            ->whereHas('examen', function($q) use ($niveauId, $parcoursId) {
                $q->where('niveau_id', $niveauId);
                if ($parcoursId) {
                    $q->where('parcours_id', $parcoursId);
                }
            })
            ->where('statut', ResultatFinal::STATUT_PUBLIE)
            ->distinct('etudiant_id')
            ->count();

        if ($count === 0) {
            throw new \Exception("Aucun étudiant trouvé pour cette session. Vérifiez que les résultats sont publiés.");
        }
    }

    /**
     * Exécuter la délibération
     */
    private function executeDeliberation(int $niveauId, ?int $parcoursId, SessionExam $session, array $deliberationParams): array
    {
        $deliberationParams['session_id'] = $session->id;

        return $this->calculAcademiqueService->appliquerDeliberationAvecConfig(
            $niveauId,
            $parcoursId,
            $session->id,
            $deliberationParams
        );
    }

    /**
     * Formater le message de succès
     */
    private function formatSuccessMessage(array $statistics): string
    {
        if (empty($statistics)) {
            return 'Délibération appliquée avec succès.';
        }

        $statsMessage = collect($statistics)
            ->map(fn($count, $decision) => ucfirst($decision) . ': ' . $count)
            ->implode(', ');

        return 'Délibération appliquée avec succès. ' . $statsMessage;
    }
}