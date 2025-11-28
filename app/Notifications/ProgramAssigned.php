<?php

namespace App\Notifications;

use App\Models\Program;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProgramAssigned extends Notification
{
    use Queueable;

    public Program $program;
    public User $trainer;

    public function __construct(Program $program, User $trainer)
    {
        $this->program = $program;
        $this->trainer = $trainer;
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
            'program_id' => $this->program->id,
            'program_name' => $this->program->name,
            'trainer_id' => $this->trainer->id,
            'trainer_name' => $this->trainer->name,
            'trainer_email' => $this->trainer->email,
            'message' => $this->trainer->name . ' has assigned you the program: ' . $this->program->name,
            'action_url' => route('programs.show', $this->program),
            'action_text' => 'View Program',
        ];
    }
}
