<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_day_id',
        'name',
        'type',
        'sets',
        'sets_min',
        'sets_max',
        'reps',
        'reps_min',
        'reps_max',
        'weight',
        'weight_min',
        'weight_max',
        'distance',
        'distance_min',
        'distance_max',
        'time_seconds',
        'time_seconds_min',
        'time_seconds_max',
        'order',
    ];

    public function programDay() {
        return $this->belongsTo(ProgramDay::class, 'program_day_id');
    }
}
