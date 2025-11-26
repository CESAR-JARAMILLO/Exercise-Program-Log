<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\SubscriptionTier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'subscription_tier',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_tier' => SubscriptionTier::class,
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function programs() {
        return $this->hasMany(Program::class);
    }

    public function activePrograms() {
        return $this->hasMany(ActiveProgram::class);
    }

    public function workoutLogs() {
        return $this->hasMany(WorkoutLog::class);
    }

    /**
     * Get the user's timezone or default to UTC
     */
    public function getTimezone(): string
    {
        return $this->timezone ?? 'UTC';
    }

    /**
     * Get total workouts logged
     */
    public function getTotalWorkouts(): int
    {
        return $this->workoutLogs()->count();
    }

    /**
     * Get active programs count
     */
    public function getActiveProgramsCount(): int
    {
        return $this->activePrograms()->where('status', 'active')->count();
    }

    /**
     * Get workout frequency data (workouts per week/month)
     */
    public function getWorkoutFrequency($period = 'week'): array
    {
        $workouts = $this->workoutLogs()
            ->selectRaw('DATE(workout_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $data = [];
        foreach ($workouts as $workout) {
            $date = Carbon::parse($workout->date);
            $key = $period === 'week' 
                ? $date->format('Y-W') 
                : $date->format('Y-m');
            
            if (!isset($data[$key])) {
                $data[$key] = 0;
            }
            $data[$key] += $workout->count;
        }

        return $data;
    }

    /**
     * Get completion rate for active programs
     */
    public function getAverageCompletionRate(): float
    {
        $activePrograms = $this->activePrograms()->where('status', 'active')->get();
        
        if ($activePrograms->isEmpty()) {
            return 0;
        }

        $totalRate = 0;
        foreach ($activePrograms as $program) {
            $workoutDates = $program->getWorkoutDates();
            $totalWorkouts = count($workoutDates);
            
            if ($totalWorkouts > 0) {
                $loggedWorkouts = $program->workoutLogs()
                    ->whereIn('program_day_id', collect($workoutDates)->pluck('program_day_id'))
                    ->count();
                $totalRate += ($loggedWorkouts / $totalWorkouts) * 100;
            }
        }

        return round($totalRate / $activePrograms->count(), 1);
    }

    /**
     * Get the user's subscription tier
     */
    public function getSubscriptionTier(): SubscriptionTier
    {
        return $this->subscription_tier ?? SubscriptionTier::FREE;
    }

    /**
     * Check if user is on free tier
     */
    public function isFree(): bool
    {
        return $this->getSubscriptionTier() === SubscriptionTier::FREE;
    }

    /**
     * Check if user is on basic tier
     */
    public function isBasic(): bool
    {
        return $this->getSubscriptionTier() === SubscriptionTier::BASIC;
    }

    /**
     * Check if user is a trainer (trainer or pro_trainer)
     */
    public function isTrainer(): bool
    {
        $tier = $this->getSubscriptionTier();
        return in_array($tier, [
            SubscriptionTier::TRAINER,
            SubscriptionTier::PRO_TRAINER,
        ]);
    }

    /**
     * Check if user is a pro trainer
     */
    public function isProTrainer(): bool
    {
        return $this->getSubscriptionTier() === SubscriptionTier::PRO_TRAINER;
    }

    /**
     * Check if user can share programs with clients
     */
    public function canSharePrograms(): bool
    {
        return $this->isTrainer();
    }

    /**
     * Check if user can view client analytics
     */
    public function canViewClientAnalytics(): bool
    {
        return $this->isTrainer();
    }

    /**
     * Check if user has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        $tierValue = $this->getSubscriptionTier()->value;
        $tierConfig = config("subscription.tiers.{$tierValue}");
        
        if (!$tierConfig) {
            return false;
        }

        return in_array($feature, $tierConfig['features'] ?? []);
    }

    /**
     * Upgrade user to a different tier (useful for testing/admin)
     */
    public function upgradeTo(SubscriptionTier $tier): void
    {
        $this->update(['subscription_tier' => $tier]);
    }

    /**
     * Get maximum number of programs allowed for current tier
     */
    public function getMaxPrograms(): ?int
    {
        $tierValue = $this->getSubscriptionTier()->value;
        $tierConfig = config("subscription.tiers.{$tierValue}");
        
        return $tierConfig['max_programs'] ?? null;
    }

    /**
     * Get current number of programs user has
     */
    public function getProgramCount(): int
    {
        return $this->programs()->count();
    }

    /**
     * Check if user can create another program
     */
    public function canCreateProgram(): bool
    {
        $max = $this->getMaxPrograms();
        
        // null means unlimited
        if ($max === null) {
            return true;
        }

        return $this->getProgramCount() < $max;
    }

    /**
     * Check if user has reached their program limit
     */
    public function hasReachedProgramLimit(): bool
    {
        return !$this->canCreateProgram();
    }

    // --- Trainer-Client Relationships ---

    /**
     * Relationships where this user is the trainer
     */
    public function trainerRelationships()
    {
        return $this->hasMany(TrainerClientRelationship::class, 'trainer_id');
    }

    /**
     * Relationships where this user is the client
     */
    public function clientRelationships()
    {
        return $this->hasMany(TrainerClientRelationship::class, 'client_id');
    }

    /**
     * Get accepted trainers for this user (when user is client)
     */
    public function trainers()
    {
        return $this->belongsToMany(User::class, 'trainer_client_relationships', 'client_id', 'trainer_id')
            ->wherePivot('status', 'accepted')
            ->withPivot(['status', 'invited_at', 'accepted_at', 'invited_by'])
            ->withTimestamps();
    }

    /**
     * Get accepted clients for this user (when user is trainer)
     */
    public function clients()
    {
        return $this->belongsToMany(User::class, 'trainer_client_relationships', 'trainer_id', 'client_id')
            ->wherePivot('status', 'accepted')
            ->withPivot(['status', 'invited_at', 'accepted_at', 'invited_by'])
            ->withTimestamps();
    }

    /**
     * Check if user has client relationship (accepted)
     */
    public function hasClient($clientId): bool
    {
        return $this->trainerRelationships()
            ->where('client_id', $clientId)
            ->where('status', 'accepted')
            ->exists();
    }

    /**
     * Check if user has trainer relationship (accepted)
     */
    public function hasTrainer($trainerId): bool
    {
        return $this->clientRelationships()
            ->where('trainer_id', $trainerId)
            ->where('status', 'accepted')
            ->exists();
    }

    /**
     * Get count of accepted clients (for tier limits)
     */
    public function getClientsCount(): int
    {
        return $this->trainerRelationships()
            ->where('status', 'accepted')
            ->count();
    }

    /**
     * Get pending requests where user is client
     */
    public function getPendingClientRequests()
    {
        return $this->clientRelationships()
            ->where('status', 'pending')
            ->with('trainer')
            ->get();
    }

    /**
     * Get pending requests where user is trainer
     */
    public function getPendingTrainerRequests()
    {
        return $this->trainerRelationships()
            ->where('status', 'pending')
            ->with('client')
            ->get();
    }
}
