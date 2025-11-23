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

    public function weeks()
    {
        return $this->hasMany(ProgramWeek::class);
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
        $activeProgram = ActiveProgram::create([
            'user_id' => $userId,
            'program_id' => $this->id,
            'started_at' => $startDate,
            'current_week' => 1,
            'current_day' => 1,
            'status' => 'active',
        ]);

        // Update program status and dates
        $this->update([
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addWeeks($this->length_weeks),
        ]);

        return $activeProgram;
    }
}
