<?php

namespace App\Notifications;

use App\Models\Program;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProgramAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public Program $program;
    public User $trainer;

    public function __construct(Program $program, User $trainer)
    {
        $this->program = $program;
        $this->trainer = $trainer;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('programs.show', $this->program);

        return (new MailMessage)
                    ->subject('New Program Assigned: ' . $this->program->name)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line($this->trainer->name . ' has assigned you a new training program: ' . $this->program->name . '.')
                    ->action('View Program', $url)
                    ->line('You can now start this program and track your progress.')
                    ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'program_id' => $this->program->id,
            'program_name' => $this->program->name,
            'trainer_id' => $this->trainer->id,
            'trainer_name' => $this->trainer->name,
            'message' => $this->trainer->name . ' has assigned you the program: ' . $this->program->name,
            'link' => route('programs.show', $this->program),
        ];
    }
}
