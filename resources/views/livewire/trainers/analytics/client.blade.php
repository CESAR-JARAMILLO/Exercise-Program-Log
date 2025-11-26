<?php

use App\Models\User;
use App\Services\TrainerAnalyticsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $trainer = Auth::user();
        abort_unless($trainer && $trainer->canViewClientAnalytics(), 403);

        $clientParam = request()->route('client');
        $client = $clientParam instanceof User
            ? $clientParam
            : User::findOrFail($clientParam);

        $analyticsService = app(TrainerAnalyticsService::class);
        $analytics = $analyticsService->getClientAnalytics($trainer, $client);

        $workoutTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $week = Carbon::now()->subWeeks($i);
            $weekKey = $week->format('Y-W');
            $workoutTrend[] = [
                'label' => $week->format('M d'),
                'count' => $analytics['workoutFrequency'][$weekKey] ?? 0,
            ];
        }

        return [
            'trainer' => $trainer,
            'client' => $client,
            'totalWorkouts' => $analytics['totalWorkouts'],
            'activeProgramsCount' => $analytics['activeProgramsCount'],
            'completionRate' => $analytics['completionRate'],
            'recentWorkouts' => $analytics['recentWorkouts'],
            'assignedPrograms' => $analytics['assignedPrograms'],
            'mostPerformedExercises' => $analytics['mostPerformedExercises'],
            'personalRecords' => $analytics['personalRecords'],
            'workoutTrend' => $workoutTrend,
        ];
    }
}; ?>

<section class="w-full">
    <x-slot:header>
        <div>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 uppercase">{{ __('Client Analytics') }}</p>
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $client->name }}
            </h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $client->email }}</p>
        </div>
        <flux:button href="{{ route('trainers.analytics.index') }}" variant="ghost" wire:navigate>
            {{ __('← Back to Analytics') }}
        </flux:button>
    </x-slot:header>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Total Workouts') }}</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $totalWorkouts }}
            </p>
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Active Programs') }}</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $activeProgramsCount }}
            </p>
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Completion Rate') }}</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $completionRate }}%
            </p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                {{ __('Workout Frequency (Last 12 Weeks)') }}
            </h3>
            @php
                $maxCount = collect($workoutTrend)->max('count') ?: 1;
            @endphp
            <div class="flex items-end gap-3 h-48">
                @foreach ($workoutTrend as $week)
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full flex items-end justify-center" style="height: 180px;">
                            <div
                                class="{{ $week['count'] > 0 ? 'bg-green-500 dark:bg-green-400' : 'bg-neutral-200 dark:bg-neutral-700' }} w-full rounded-t"
                                style="height: {{ $week['count'] > 0 ? max(10, ($week['count'] / $maxCount) * 100) : 2 }}%"
                                title="{{ $week['count'] }} {{ __('workouts') }}"
                            ></div>
                        </div>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400 rotate-[-45deg] origin-top-left">{{ $week['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                {{ __('Assigned Programs') }}
            </h3>
            @if (empty($assignedPrograms))
                <p class="text-neutral-600 dark:text-neutral-400 text-sm">
                    {{ __('No programs have been assigned to this client yet.') }}
                </p>
            @else
                <div class="space-y-4">
                    @foreach ($assignedPrograms as $assignment)
                        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="font-semibold text-neutral-900 dark:text-neutral-100">
                                        {{ $assignment['program']->name }}
                                    </p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ __('Assigned :date', ['date' => $assignment['assigned_at']->format('M d, Y')]) }}
                                    </p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full
                                    @class([
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' => $assignment['status'] === 'assigned',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $assignment['status'] === 'started',
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $assignment['status'] === 'completed',
                                    ])">
                                    {{ ucfirst($assignment['status']) }}
                                </span>
                            </div>
                            @if ($assignment['progress'] !== null)
                                <div class="w-full bg-neutral-200 dark:bg-neutral-800 rounded-full h-2 mt-2">
                                    <div
                                        class="bg-green-500 dark:bg-green-400 h-2 rounded-full"
                                        style="width: {{ $assignment['progress'] }}%"
                                    ></div>
                                </div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                    {{ $assignment['logged'] }} / {{ $assignment['total'] }} {{ __('workouts logged') }}
                                    ({{ $assignment['progress'] }}%)
                                </p>
                            @else
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                                    {{ __('Client has not started this program yet.') }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                {{ __('Recent Workouts') }}
            </h3>
            @if ($recentWorkouts->isEmpty())
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('No workouts logged in the past week.') }}
                </p>
            @else
                <div class="space-y-3">
                    @foreach ($recentWorkouts as $log)
                        <div class="flex items-center justify-between rounded-lg border border-neutral-200 dark:border-neutral-700 p-3">
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ optional($log->activeProgram->program)->name ?? __('Program') }}
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ $log->workout_date->format('M d, Y') }} • {{ $log->exercises->count() }} {{ __('exercises') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                {{ __('Most Performed Exercises') }}
            </h3>
            @if (empty($mostPerformedExercises))
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('No exercise data available yet.') }}
                </p>
            @else
                <ul class="space-y-3">
                    @foreach ($mostPerformedExercises as $exercise)
                        <li class="flex items-center justify-between text-sm text-neutral-600 dark:text-neutral-300">
                            <span>{{ $exercise['name'] }}</span>
                            <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ $exercise['count'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
            {{ __('Personal Records') }}
        </h3>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-xs uppercase text-neutral-500 dark:text-neutral-400">{{ __('Max Weight') }}</p>
                @if ($personalRecords['max_weight'])
                    <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                        {{ $personalRecords['max_weight'] }} {{ __('lbs') }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        {{ $personalRecords['max_weight_exercise'] }} • {{ optional($personalRecords['max_weight_date'])->format('M d, Y') }}
                    </p>
                @else
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('N/A') }}</p>
                @endif
            </div>
            <div>
                <p class="text-xs uppercase text-neutral-500 dark:text-neutral-400">{{ __('Max Reps') }}</p>
                @if ($personalRecords['max_reps'])
                    <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                        {{ $personalRecords['max_reps'] }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        {{ $personalRecords['max_reps_exercise'] }} • {{ optional($personalRecords['max_reps_date'])->format('M d, Y') }}
                    </p>
                @else
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('N/A') }}</p>
                @endif
            </div>
            <div>
                <p class="text-xs uppercase text-neutral-500 dark:text-neutral-400">{{ __('Max Distance') }}</p>
                @if ($personalRecords['max_distance'])
                    <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                        {{ $personalRecords['max_distance'] }} {{ __('mi') }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        {{ $personalRecords['max_distance_exercise'] }} • {{ optional($personalRecords['max_distance_date'])->format('M d, Y') }}
                    </p>
                @else
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('N/A') }}</p>
                @endif
            </div>
            <div>
                <p class="text-xs uppercase text-neutral-500 dark:text-neutral-400">{{ __('Best Time') }}</p>
                @if ($personalRecords['best_time'])
                    <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                        {{ gmdate('H:i:s', $personalRecords['best_time']) }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        {{ $personalRecords['best_time_exercise'] }} • {{ optional($personalRecords['best_time_date'])->format('M d, Y') }}
                    </p>
                @else
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('N/A') }}</p>
                @endif
            </div>
        </div>
    </div>
</section>


