<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'draft_data',
        'last_saved_at',
    ];

    protected $casts = [
        'draft_data' => 'array',
        'last_saved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
