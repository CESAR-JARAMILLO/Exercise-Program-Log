<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramWeek extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'week_number',
    ];

    public function program() {
        return $this->belongsTo(Program::class);
    }

    public function days() {
        return $this->hasMany(ProgramDay::class);
    }
}
