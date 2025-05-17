<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ImportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ue_count;
    protected $ec_count;

    /**
     * Create a new notification instance.
     */
    public function __construct($ue_count, $ec_count)
    {
        $this->ue_count = $ue_count;
        $this->ec_count = $ec_count;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Vous pouvez choisir les canaux en fonction de vos besoins
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Importation terminée avec succès!',
            'ue_count' => $this->ue_count,
            'ec_count' => $this->ec_count,
            'type' => 'import_completed',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => 'Importation terminée avec succès!',
            'ue_count' => $this->ue_count,
            'ec_count' => $this->ec_count,
            'type' => 'import_completed',
        ]);
    }
}