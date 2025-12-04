<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActiveProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_id',
        'started_at',
        'current_week',
        'current_day',
        'status',
        'stopped_at',
    ];

    protected $casts = [
        'started_at' => 'date',
        'stopped_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function program() {
        return $this->belongsTo(Program::class);
    }

    public function workoutLogs() {
        return $this->hasMany(WorkoutLog::class);
    }

    // Get scheduled date for a specific week and day
    // Note: Dates are sequential (including rest days), but rest days won't have workouts
    public function getScheduledDate($weekNumber, $dayNumber): Carbon {
        return $this->started_at->copy()
            ->addWeeks($weekNumber - 1)
            ->addDays($dayNumber - 1);
    }

    // Get all scheduled dates (including rest days for calendar display)
    public function getScheduledDates(): array {
        $dates = [];
        foreach ($this->program->weeks as $week) {
            foreach ($week->days as $day) {
                $dates[] = [
                    'week' => $week->week_number,
                    'day' => $day->day_number,
                    'date' => $this->getScheduledDate($week->week_number, $day->day_number),
                    'program_day_id' => $day->id,
                    'is_rest_day' => $day->is_rest_day ?? false,
                ];
            }
        }
        return $dates;
    }

    // Get only workout dates (excluding rest days)
    public function getWorkoutDates(): array {
        $dates = [];
        foreach ($this->program->weeks as $week) {
            foreach ($week->days as $day) {
                // Skip rest days
                if ($day->is_rest_day) {
                    continue;
                }
                $dates[] = [
                    'week' => $week->week_number,
                    'day' => $day->day_number,
                    'date' => $this->getScheduledDate($week->week_number, $day->day_number),
                    'program_day_id' => $day->id,
                    'is_rest_day' => false,
                ];
            }
        }
        return $dates;
    }

    // Check if a date has a scheduled workout (excluding rest days)
    public function hasScheduledWorkout($date): ?array {
        $workoutDates = $this->getWorkoutDates();
        foreach ($workoutDates as $scheduled) {
            if ($scheduled['date']->isSameDay($date)) {
                return $scheduled;
            }
        }
        return null;
    }

    // Check if a date is a rest day
    public function isRestDay($date): bool {
        $scheduledDates = $this->getScheduledDates();
        foreach ($scheduledDates as $scheduled) {
            if ($scheduled['date']->isSameDay($date)) {
                return $scheduled['is_rest_day'] ?? false;
            }
        }
        return false;
    }

    // Get workout log for a specific date
    public function getWorkoutLogForDate($date) {
        return $this->workoutLogs()
            ->whereDate('workout_date', $date)
            ->first();
    }

    // Get today's scheduled workout or next scheduled workout
    public function getTodayOrNextWorkout(): ?array {
        // Use user's timezone to determine "today"
        $userTimezone = Auth::user()?->getTimezone() ?? 'UTC';
        $today = Carbon::today($userTimezone);
        $scheduledDates = $this->getScheduledDates();
        
        // First, try to find today's workout
        foreach ($scheduledDates as $scheduled) {
            if ($scheduled['date']->isSameDay($today)) {
                return $scheduled;
            }
        }
        
        // If no workout today, find the next scheduled workout
        $nextWorkout = null;
        foreach ($scheduledDates as $scheduled) {
            if ($scheduled['date']->isFuture() || $scheduled['date']->isToday()) {
                if ($nextWorkout === null || $scheduled['date']->lt($nextWorkout['date'])) {
                    $nextWorkout = $scheduled;
                }
            }
        }
        
        return $nextWorkout;
    }

    // Check if today has a workout and if it's logged (excluding rest days)
    public function getTodayWorkoutStatus(): ?array {
        // Use user's timezone to determine "today"
        $userTimezone = Auth::user()?->getTimezone() ?? 'UTC';
        $today = Carbon::today($userTimezone);
        
        // Check if today is a rest day
        if ($this->isRestDay($today)) {
            return null;
        }
        
        $todayWorkout = $this->hasScheduledWorkout($today);
        
        if (!$todayWorkout) {
            return null;
        }
        
        $workoutLog = $this->getWorkoutLogForDate($today);
        
        return [
            'scheduled' => $todayWorkout,
            'isLogged' => $workoutLog !== null,
            'workoutLog' => $workoutLog,
        ];
    }

    // Update progress based on logged workouts
    public function updateProgress(): void {
        $workoutDates = $this->getWorkoutDates();
        $loggedWorkouts = $this->workoutLogs()
            ->whereIn('program_day_id', collect($workoutDates)->pluck('program_day_id'))
            ->get()
            ->keyBy('program_day_id');
        
        $lastCompletedWeek = 0;
        $lastCompletedDay = 0;
        
        // Find the last completed workout
        foreach ($workoutDates as $workout) {
            if (isset($loggedWorkouts[$workout['program_day_id']])) {
                if ($workout['week'] > $lastCompletedWeek || 
                    ($workout['week'] == $lastCompletedWeek && $workout['day'] > $lastCompletedDay)) {
                    $lastCompletedWeek = $workout['week'];
                    $lastCompletedDay = $workout['day'];
                }
            }
        }
        
        // Update current week and day to the next uncompleted workout
        $nextWorkout = null;
        foreach ($workoutDates as $workout) {
            if (!isset($loggedWorkouts[$workout['program_day_id']])) {
                if ($workout['week'] > $lastCompletedWeek || 
                    ($workout['week'] == $lastCompletedWeek && $workout['day'] > $lastCompletedDay)) {
                    $nextWorkout = $workout;
                    break;
                }
            }
        }
        
        if ($nextWorkout) {
            $this->update([
                'current_week' => $nextWorkout['week'],
                'current_day' => $nextWorkout['day'],
            ]);
        } else {
            // All workouts completed
            $this->update([
                'status' => 'completed',
                'current_week' => $this->program->length_weeks,
                'current_day' => 7,
            ]);
        }
    }

    // Stop the program (preserves all data)
    public function stop(): void {
        $this->update([
            'status' => 'stopped',
            'stopped_at' => now(),
        ]);

        // Note: Program status remains 'template' so multiple users can have instances
        // No need to update program status - it should always stay as 'template'
    }

    // Check if program is stopped
    public function isStopped(): bool {
        return $this->status === 'stopped';
    }

    // Check if program is active
    public function isActive(): bool {
        return $this->status === 'active';
    }

    // Check if program is completed
    public function isCompleted(): bool {
        return $this->status === 'completed';
    }

    // Restart a stopped program (creates new active instance)
    public function restart($startDate = null): ActiveProgram {
        if (!$this->isStopped()) {
            throw new \Exception('Can only restart stopped programs');
        }

        // Use provided start date or default to today
        if (!$startDate) {
            $startDate = now();
        }
        if (!$startDate instanceof Carbon) {
            $startDate = Carbon::parse($startDate);
        }

        // Create new active program instance
        // Note: Program status remains 'template' so multiple users can restart the same program
        // Each user gets their own ActiveProgram instance to track individual progress
        $newActiveProgram = ActiveProgram::create([
            'user_id' => $this->user_id,
            'program_id' => $this->program_id,
            'started_at' => $startDate,
            'current_week' => 1,
            'current_day' => 1,
            'status' => 'active',
        ]);

        // Don't update program status - keep it as 'template' so other users can restart it too
        // The program dates are not updated either, as they're user-specific in ActiveProgram

        return $newActiveProgram;
    }
}
