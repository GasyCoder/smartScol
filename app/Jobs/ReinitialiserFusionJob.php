<?php

namespace App\Jobs;

use App\Services\FusionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReinitialiserFusionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $examen_id;
    protected $user_id;
    protected $operation_id;

    /**
     * Create a new job instance.
     *
     * @param int $examen_id ID de l'examen
     * @param int $user_id ID de l'utilisateur
     * @param string|null $operation_id ID de l'opération
     * @return void
     */
    public function __construct($examen_id, $user_id, $operation_id = null)
    {
        $this->examen_id = $examen_id;
        $this->user_id = $user_id;
        $this->operation_id = $operation_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Début job réinitialisation fusion', [
            'examen_id' => $this->examen_id,
            'user_id' => $this->user_id,
            'operation_id' => $this->operation_id
        ]);

        try {
            $fusionService = new FusionService();
            $result = $fusionService->performResetFusion(
                $this->examen_id,
                $this->user_id,
                $this->operation_id
            );

            if ($result['success']) {
                Log::info('Job réinitialisation fusion terminé', [
                    'examen_id' => $this->examen_id,
                    'user_id' => $this->user_id,
                    'operation_id' => $this->operation_id,
                    'count' => $result['count']
                ]);

                // Dispatch un événement pour notifier le frontend
                event(new \App\Events\FusionOperationCompleted(
                    \App\Models\FusionOperation::find($this->operation_id)
                ));
            } else {
                Log::error('Job réinitialisation fusion échoué', [
                    'examen_id' => $this->examen_id,
                    'user_id' => $this->user_id,
                    'operation_id' => $this->operation_id,
                    'error' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du job réinitialisation fusion', [
                'examen_id' => $this->examen_id,
                'user_id' => $this->user_id,
                'operation_id' => $this->operation_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mettre à jour le statut de l'opération en cas d'erreur
            if ($this->operation_id) {
                $operation = \App\Models\FusionOperation::find($this->operation_id);
                if ($operation) {
                    $operation->updateStatus('failed', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }
}
