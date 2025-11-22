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
        'reps',
        'weight',
        'distance',
        'time_seconds',
        'order',
    ];

    public function programDay() {
        return $this->belongsTo(ProgramDay::class, 'program_day_id');
    }
}
