<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'started_at' => 'date',
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
    public function getScheduledDate($weekNumber, $dayNumber): Carbon {
        return $this->started_at->copy()
            ->addWeeks($weekNumber - 1)
            ->addDays($dayNumber - 1);
    }

    // Get all scheduled workout dates
    public function getScheduledDates(): array {
        $dates = [];
        foreach ($this->program->weeks as $week) {
            foreach ($week->days as $day) {
                $dates[] = [
                    'week' => $week->week_number,
                    'day' => $day->day_number,
                    'date' => $this->getScheduledDate($week->week_number, $day->day_number),
                    'program_day_id' => $day->id,
                ];
            }
        }
        return $dates;
    }

    // Check if a date has a scheduled workout
    public function hasScheduledWorkout($date): ?array {
        $scheduledDates = $this->getScheduledDates();
        foreach ($scheduledDates as $scheduled) {
            if ($scheduled['date']->isSameDay($date)) {
                return $scheduled;
            }
        }
        return null;
    }

    // Get workout log for a specific date
    public function getWorkoutLogForDate($date) {
        return $this->workoutLogs()
            ->whereDate('workout_date', $date)
            ->first();
    }

    // Get today's scheduled workout or next scheduled workout
    public function getTodayOrNextWorkout(): ?array {
        $today = Carbon::today();
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

    // Check if today has a workout and if it's logged
    public function getTodayWorkoutStatus(): ?array {
        $today = Carbon::today();
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
}
