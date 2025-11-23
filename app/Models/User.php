<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
}
