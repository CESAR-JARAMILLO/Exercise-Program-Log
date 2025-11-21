<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'type',
        'weight',
        'sets',
        'reps',
        'distance',
        'time_seconds',
    ];

    public function program() {
        return $this->belongsTo(Program::class);
    }
}
