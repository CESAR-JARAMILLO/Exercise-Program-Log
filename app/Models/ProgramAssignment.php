<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProgramAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'trainer_id',
        'client_id',
        'assigned_at',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function markAsStarted(): void
    {
        $this->update(['status' => 'started']);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function isStarted(): bool
    {
        return $this->status === 'started';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeStarted($query)
    {
        return $query->where('status', 'started');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
