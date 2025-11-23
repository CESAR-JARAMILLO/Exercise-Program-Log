<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'workout_log_id',
        'day_exercise_id',
        'name',
        'type',
        'sets_actual',
        'reps_actual',
        'weight_actual',
        'distance_actual',
        'time_seconds_actual',
        'notes',
        'order',
    ];

    public function workoutLog() {
        return $this->belongsTo(WorkoutLog::class);
    }

    public function dayExercise() {
        return $this->belongsTo(DayExercise::class, 'day_exercise_id');
    }

    // Get target metrics from day exercise
    public function getTarget() {
        return $this->dayExercise;
    }
}
