<?php

namespace App\Jobs;

use App\Services\FusionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FusionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $examen_id;
    protected $user_id;
    protected $force;
    protected $operation_id;

    /**
     * Create a new job instance.
     *
     * @param int $examen_id ID de l'examen
     * @param int $user_id ID de l'utilisateur
     * @param bool $force Forcer la fusion
     * @param string|null $operation_id ID de l'opération
     * @return void
     */
    public function __construct($examen_id, $user_id, $force = false, $operation_id = null)
    {
        $this->examen_id = $examen_id;
        $this->user_id = $user_id;
        $this->force = $force;
        $this->operation_id = $operation_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Début job fusion', [
            'examen_id' => $this->examen_id,
            'user_id' => $this->user_id,
            'force' => $this->force,
            'operation_id' => $this->operation_id
        ]);

        try {
            $fusionService = new FusionService();
            $result = $fusionService->performFusion(
                $this->examen_id,
                $this->user_id,
                $this->force,
                $this->operation_id
            );

            if ($result['success']) {
                Log::info('Job fusion terminé', [
                    'examen_id' => $this->examen_id,
                    'user_id' => $this->user_id,
                    'operation_id' => $this->operation_id,
                    'resultats_generes' => $result['statistiques']['resultats_generes'] ?? 0,
                    'erreurs_count' => $result['statistiques']['erreurs_count'] ?? 0
                ]);
            } else {
                Log::error('Job fusion échoué', [
                    'examen_id' => $this->examen_id,
                    'user_id' => $this->user_id,
                    'operation_id' => $this->operation_id,
                    'error' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du job fusion', [
                'examen_id' => $this->examen_id,
                'user_id' => $this->user_id,
                'operation_id' => $this->operation_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
