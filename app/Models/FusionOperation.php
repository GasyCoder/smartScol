<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FusionOperation extends Model
{
    protected $table = 'fusion_operations';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'examen_id',
        'user_id',
        'type',
        'status',
        'parameters',
        'result',
        'error_message',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'parameters' => 'json',
        'result' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Met à jour le statut de l'opération
     *
     * @param string $status
     * @param array $result
     * @return bool
     */
    public function updateStatus($status, $result = [])
    {
        if ($status === 'processing' && !$this->started_at) {
            $this->started_at = now();
        }

        if (in_array($status, ['completed', 'failed'])) {
            $this->completed_at = now();
        }

        $this->status = $status;

        if ($status === 'failed' && isset($result['error'])) {
            $this->error_message = $result['error'];
        } elseif (!empty($result)) {
            $this->result = $result;
        }

        Log::info('FusionOperation statut mis à jour', [
            'id' => $this->id,
            'examen_id' => $this->examen_id,
            'type' => $this->type,
            'status' => $status
        ]);

        return $this->save();
    }

    /**
     * Relation avec l'examen
     */
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
