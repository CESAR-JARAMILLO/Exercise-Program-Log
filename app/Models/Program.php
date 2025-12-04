<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trainer_id',
        'name',
        'length_weeks',
        'description',
        'start_date', // Keep for when program is started
        'end_date',   // Keep for when program is started
        'notes',
        'status',     // NEW: template, active, completed
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function weeks()
    {
        return $this->hasMany(ProgramWeek::class);
    }

    public function assignments()
    {
        return $this->hasMany(ProgramAssignment::class);
    }

    public function assignedClients()
    {
        return $this->belongsToMany(User::class, 'program_assignments', 'program_id', 'client_id')
            ->withPivot('status', 'assigned_at')
            ->withTimestamps();
    }

    // NEW: Relationship to active programs
    public function activePrograms()
    {
        return $this->hasMany(ActiveProgram::class);
    }

    // Relationship to stopped programs
    public function activeProgramsStopped()
    {
        return $this->hasMany(ActiveProgram::class);
    }

    // NEW: Check if program is a template
    public function isTemplate(): bool
    {
        return $this->status === 'template';
    }

    // NEW: Check if program is active
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // NEW: Start a program for a user
    public function startForUser($userId, $startDate)
    {
        // Ensure startDate is a Carbon instance
        if (! $startDate instanceof Carbon) {
            $startDate = Carbon::parse($startDate);
        }

        // Create active program instance
        // Note: Program status remains 'template' so multiple users can start the same program
        // Each user gets their own ActiveProgram instance to track individual progress
        $activeProgram = ActiveProgram::create([
            'user_id' => $userId,
            'program_id' => $this->id,
            'started_at' => $startDate,
            'current_week' => 1,
            'current_day' => 1,
            'status' => 'active',
        ]);

        // Don't update program status - keep it as 'template' so other users can start it too
        // The program dates are not updated either, as they're user-specific in ActiveProgram

        return $activeProgram;
    }

    // Program Assignment Methods
    public function assignToClient(int $clientId, int $trainerId): ProgramAssignment
    {
        return ProgramAssignment::create([
            'program_id' => $this->id,
            'trainer_id' => $trainerId,
            'client_id' => $clientId,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);
    }

    public function unassignFromClient(int $clientId): bool
    {
        return ProgramAssignment::where('program_id', $this->id)
            ->where('client_id', $clientId)
            ->delete();
    }

    public function isAssignedToClient(int $clientId): bool
    {
        return ProgramAssignment::where('program_id', $this->id)
            ->where('client_id', $clientId)
            ->exists();
    }

    public function isAssignedToMe(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->isAssignedToClient(auth()->id());
    }

    public function canBeViewedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        // Owner can always view
        if ($this->user_id === $user->id) {
            return true;
        }

        // Trainer who created it can view
        if ($this->trainer_id === $user->id) {
            return true;
        }

        // Assigned client can view
        if ($this->isAssignedToClient($user->id)) {
            return true;
        }

        return false;
    }

    public function canBeEditedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        // Only owner or trainer can edit
        return $this->user_id === $user->id || $this->trainer_id === $user->id;
    }

    public function canBeDeletedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        // Only owner or trainer can delete
        return $this->user_id === $user->id || $this->trainer_id === $user->id;
    }

    public function getAssignedClients()
    {
        return $this->assignedClients()->get();
    }
}
