<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\FusionOperationCompleted;

class FusionOperation extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'examen_id',
        'user_id',
        'type',
        'status',
        'parameters',
        'result',
        'started_at',
        'completed_at',
        'error_message'
    ];

    protected $casts = [
        'parameters' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Relations
     */
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Met à jour le statut de l'opération
     *
     * @param string $status
     * @param array|null $result
     * @return $this
     */
    public function updateStatus($status, $result = null)
    {
        $this->status = $status;

        if ($status === 'processing') {
            $this->started_at = now();
        } elseif (in_array($status, ['completed', 'failed'])) {
            $this->completed_at = now();

            if ($result) {
                $this->result = $result;
            }

            if ($status === 'failed' && isset($result['message'])) {
                $this->error_message = $result['message'];
            }
        }

        $this->save();

        // Émettre un événement pour la mise à jour en temps réel
        if ($status === 'completed' || $status === 'failed') {
            event(new FusionOperationCompleted($this));
        }

        return $this;
    }
}
