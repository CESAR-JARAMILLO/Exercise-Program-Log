<?php

namespace App\Models;

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
        'start_date',
        'end_date',
        'notes',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function weeks() {
        return $this->hasMany(ProgramWeek::class);
    }
}
