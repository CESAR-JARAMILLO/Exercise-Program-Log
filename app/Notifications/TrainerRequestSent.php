<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TrainerRequestSent extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $trainer
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'trainer_id' => $this->trainer->id,
            'trainer_name' => $this->trainer->name,
            'trainer_email' => $this->trainer->email,
            'message' => $this->trainer->name . ' has sent you a trainer connection request.',
            'action_url' => route('clients.requests'),
            'action_text' => 'View Requests',
        ];
    }
}
