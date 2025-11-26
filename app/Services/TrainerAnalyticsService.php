<?php

namespace App\Services;

use App\Models\ProgramAssignment;
use App\Models\User;
use App\Models\WorkoutExercise;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class TrainerAnalyticsService
{
    /**
     * Verify the trainer has access to the given client.
     *
     * @throws AuthorizationException
     */
    protected function authorizeTrainerForClient(User $trainer, User $client): void
    {
        if (! $trainer->hasClient($client->id)) {
            throw new AuthorizationException('You do not have access to this client.');
        }
    }

    /**
     * Get analytics overview data for all clients.
     */
    public function getAggregateStats(User $trainer): array
    {
        $clients = $trainer->clients;

        if ($clients->isEmpty()) {
            return [
                'total_clients' => 0,
                'total_active_programs' => 0,
                'average_completion_rate' => 0,
                'total_workouts_last_week' => 0,
            ];
        }

        $totalActivePrograms = 0;
        $totalCompletionRate = 0;
        $completionCount = 0;
        $totalWorkoutsLastWeek = 0;

        foreach ($clients as $client) {
            $totalActivePrograms += $client->getActiveProgramsCount();

            $rate = $client->getAverageCompletionRate();
            if ($rate > 0) {
                $totalCompletionRate += $rate;
                $completionCount++;
            }

            $frequency = $client->getWorkoutFrequency('week');
            $lastWeekKey = now()->subWeek()->format('Y-W');
            $totalWorkoutsLastWeek += $frequency[$lastWeekKey] ?? 0;
        }

        $averageCompletionRate = $completionCount > 0
            ? round($totalCompletionRate / $completionCount, 1)
            : 0;

        return [
            'total_clients' => $clients->count(),
            'total_active_programs' => $totalActivePrograms,
            'average_completion_rate' => $averageCompletionRate,
            'total_workouts_last_week' => $totalWorkoutsLastWeek,
        ];
    }

    /**
     * Get quick stats for each client (used on dashboard list).
     */
    public function getClientSummaries(User $trainer): Collection
    {
        $clients = $trainer->clients;

        return $clients->map(function (User $client) {
            $frequency = $client->getWorkoutFrequency('week');
            $thisWeekKey = now()->format('Y-W');

            return [
                'client' => $client,
                'workouts_this_week' => $frequency[$thisWeekKey] ?? 0,
                'active_programs' => $client->getActiveProgramsCount(),
                'completion_rate' => $client->getAverageCompletionRate(),
            ];
        });
    }

    /**
     * Get detailed analytics for a specific client.
     */
    public function getClientAnalytics(User $trainer, User $client): array
    {
        $this->authorizeTrainerForClient($trainer, $client);

        $workoutFrequency = $client->getWorkoutFrequency('week');
        $recentWorkouts = $client->workoutLogs()
            ->with(['activeProgram.program', 'exercises'])
            ->where('workout_date', '>=', now()->subDays(7))
            ->orderBy('workout_date', 'desc')
            ->limit(7)
            ->get();

        return [
            'totalWorkouts' => $client->getTotalWorkouts(),
            'activeProgramsCount' => $client->getActiveProgramsCount(),
            'completionRate' => $client->getAverageCompletionRate(),
            'workoutFrequency' => $workoutFrequency,
            'recentWorkouts' => $recentWorkouts,
            'assignedPrograms' => $this->getAssignedProgramSummaries($trainer, $client),
            'mostPerformedExercises' => WorkoutExercise::getMostPerformedExercises($client->id, 5),
            'personalRecords' => WorkoutExercise::getPersonalRecordsForUser($client->id),
        ];
    }

    /**
     * Build program progress summaries for programs this trainer assigned to this client.
     */
    protected function getAssignedProgramSummaries(User $trainer, User $client): array
    {
        $assignments = ProgramAssignment::with('program')
            ->where('trainer_id', $trainer->id)
            ->where('client_id', $client->id)
            ->get();

        if ($assignments->isEmpty()) {
            return [];
        }

        $assignedProgramIds = $assignments->pluck('program_id');

        $activePrograms = $client->activePrograms()
            ->whereIn('program_id', $assignedProgramIds)
            ->with('program')
            ->get()
            ->keyBy('program_id');

        return $assignments->map(function (ProgramAssignment $assignment) use ($activePrograms) {
            $activeProgram = $activePrograms->get($assignment->program_id);
            $progress = null;
            $loggedWorkouts = null;
            $totalWorkouts = null;

            if ($activeProgram) {
                $workoutDates = $activeProgram->getWorkoutDates();
                $totalWorkouts = count($workoutDates);
                $loggedWorkouts = $activeProgram->workoutLogs()
                    ->whereIn('program_day_id', collect($workoutDates)->pluck('program_day_id'))
                    ->count();
                $progress = $totalWorkouts > 0 ? round(($loggedWorkouts / $totalWorkouts) * 100, 1) : 0;
            }

            return [
                'program' => $assignment->program,
                'status' => $assignment->status,
                'assigned_at' => $assignment->assigned_at,
                'active_program' => $activeProgram,
                'progress' => $progress,
                'logged' => $loggedWorkouts,
                'total' => $totalWorkouts,
            ];
        })->toArray();
    }
}
