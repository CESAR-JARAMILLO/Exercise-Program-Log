<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'active_program_id',
        'program_day_id',
        'workout_date',
        'notes',
    ];

    protected $casts = [
        'workout_date' => 'date',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function activeProgram() {
        return $this->belongsTo(ActiveProgram::class);
    }

    public function programDay() {
        return $this->belongsTo(ProgramDay::class);
    }

    public function exercises() {
        return $this->hasMany(WorkoutExercise::class)->orderBy('order');
    }

    // Get target exercises for this day
    public function getTargetExercises() {
        return $this->programDay->exercises;
    }
}
