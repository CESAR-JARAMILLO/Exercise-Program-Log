<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Trainer Connection Request'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__(':trainer has sent you a trainer connection request.', ['trainer' => $this->trainer->name]))
            ->line(__('You can accept or decline this request in your account.'))
            ->action(__('View Requests'), route('clients.requests'))
            ->line(__('Thank you for using our application!'));
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
        ];
    }
}
