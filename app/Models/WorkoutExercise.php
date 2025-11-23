<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'workout_log_id',
        'day_exercise_id',
        'name',
        'type',
        'sets_actual',
        'reps_actual',
        'weight_actual',
        'distance_actual',
        'time_seconds_actual',
        'notes',
        'order',
    ];

    public function workoutLog() {
        return $this->belongsTo(WorkoutLog::class);
    }

    public function dayExercise() {
        return $this->belongsTo(DayExercise::class, 'day_exercise_id');
    }

    // Get target metrics from day exercise
    public function getTarget() {
        return $this->dayExercise;
    }

    /**
     * Check if this is a personal record for weight
     */
    public static function getPersonalRecordsForUser($userId, $exerciseName = null): array
    {
        $query = static::whereHas('workoutLog', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });

        if ($exerciseName) {
            $query->where('name', $exerciseName);
        }

        $records = [
            'max_weight' => null,
            'max_weight_exercise' => null,
            'max_weight_date' => null,
            'max_reps' => null,
            'max_reps_exercise' => null,
            'max_reps_date' => null,
            'max_distance' => null,
            'max_distance_exercise' => null,
            'max_distance_date' => null,
            'best_time' => null,
            'best_time_exercise' => null,
            'best_time_date' => null,
        ];

        // Max weight
        $maxWeight = $query->clone()
            ->whereNotNull('weight_actual')
            ->orderBy('weight_actual', 'desc')
            ->first();
        
        if ($maxWeight) {
            $records['max_weight'] = $maxWeight->weight_actual;
            $records['max_weight_exercise'] = $maxWeight->name;
            $records['max_weight_date'] = $maxWeight->workoutLog->workout_date;
        }

        // Max reps
        $maxReps = $query->clone()
            ->whereNotNull('reps_actual')
            ->orderBy('reps_actual', 'desc')
            ->first();
        
        if ($maxReps) {
            $records['max_reps'] = $maxReps->reps_actual;
            $records['max_reps_exercise'] = $maxReps->name;
            $records['max_reps_date'] = $maxReps->workoutLog->workout_date;
        }

        // Max distance
        $maxDistance = $query->clone()
            ->whereNotNull('distance_actual')
            ->orderBy('distance_actual', 'desc')
            ->first();
        
        if ($maxDistance) {
            $records['max_distance'] = $maxDistance->distance_actual;
            $records['max_distance_exercise'] = $maxDistance->name;
            $records['max_distance_date'] = $maxDistance->workoutLog->workout_date;
        }

        // Best time (lowest time_seconds)
        $bestTime = $query->clone()
            ->whereNotNull('time_seconds_actual')
            ->orderBy('time_seconds_actual', 'asc')
            ->first();
        
        if ($bestTime) {
            $records['best_time'] = $bestTime->time_seconds_actual;
            $records['best_time_exercise'] = $bestTime->name;
            $records['best_time_date'] = $bestTime->workoutLog->workout_date;
        }

        return $records;
    }

    /**
     * Get most performed exercises
     */
    public static function getMostPerformedExercises($userId, $limit = 10): array
    {
        return static::whereHas('workoutLog', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->selectRaw('name, COUNT(*) as count')
        ->groupBy('name')
        ->orderBy('count', 'desc')
        ->limit($limit)
        ->get()
        ->map(function ($exercise) {
            return [
                'name' => $exercise->name,
                'count' => $exercise->count,
            ];
        })
        ->toArray();
    }
}
