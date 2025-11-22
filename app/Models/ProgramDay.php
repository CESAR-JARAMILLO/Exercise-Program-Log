<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_week_id',
        'day_number',
        'label',
    ];

    public function week() {
        return $this->belongsTo(ProgramWeek::class, 'program_week_id');
    }

    public function exercises() {
        return $this->hasMany(DayExercise::class);
    }
}
