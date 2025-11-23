<?php

use App\Models\ActiveProgram;
use App\Models\WorkoutExercise;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();
        
        // Overview statistics
        $totalWorkouts = $user->getTotalWorkouts();
        $activeProgramsCount = $user->getActiveProgramsCount();
        $completionRate = $user->getAverageCompletionRate();
        
        // Workout frequency (last 12 weeks)
        $workoutFrequency = $user->getWorkoutFrequency('week');
        $last12Weeks = [];
        for ($i = 11; $i >= 0; $i--) {
            $week = Carbon::now()->subWeeks($i);
            $weekKey = $week->format('Y-W');
            $last12Weeks[] = [
                'week' => $week->format('M d'),
                'count' => $workoutFrequency[$weekKey] ?? 0,
            ];
        }
        
        // Personal records
        $personalRecords = WorkoutExercise::getPersonalRecordsForUser($user->id);
        
        // Most performed exercises
        $mostPerformed = WorkoutExercise::getMostPerformedExercises($user->id, 5);
        
        // Active program progress
        $activePrograms = $user->activePrograms()
            ->where('status', 'active')
            ->with('program')
            ->get();
        
        $programProgress = [];
        foreach ($activePrograms as $activeProgram) {
            $workoutDates = $activeProgram->getWorkoutDates();
            $totalWorkouts = count($workoutDates);
            $loggedWorkouts = $activeProgram->workoutLogs()
                ->whereIn('program_day_id', collect($workoutDates)->pluck('program_day_id'))
                ->count();
            
            $progress = $totalWorkouts > 0 ? round(($loggedWorkouts / $totalWorkouts) * 100, 1) : 0;
            
            $programProgress[] = [
                'program' => $activeProgram->program,
                'activeProgram' => $activeProgram,
                'progress' => $progress,
                'logged' => $loggedWorkouts,
                'total' => $totalWorkouts,
            ];
        }
        
        // Recent workouts (last 7 days)
        $recentWorkouts = $user->workoutLogs()
            ->with(['activeProgram.program', 'exercises'])
            ->where('workout_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('workout_date', 'desc')
            ->limit(5)
            ->get();
        
        return [
            'totalWorkouts' => $totalWorkouts,
            'activeProgramsCount' => $activeProgramsCount,
            'completionRate' => $completionRate,
            'workoutFrequency' => $last12Weeks,
            'personalRecords' => $personalRecords,
            'mostPerformed' => $mostPerformed,
            'programProgress' => $programProgress,
            'recentWorkouts' => $recentWorkouts,
        ];
    }
}; ?>

<section class="w-full">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('Statistics') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Track your progress and achievements') }}
            </p>
        </div>

        <!-- Overview Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Total Workouts') }}</p>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalWorkouts }}</p>
                    </div>
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900/30 p-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Active Programs') }}</p>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $activeProgramsCount }}</p>
                    </div>
                    <div class="rounded-full bg-green-100 dark:bg-green-900/30 p-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Completion Rate') }}</p>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $completionRate }}%</p>
                    </div>
                    <div class="rounded-full bg-purple-100 dark:bg-purple-900/30 p-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Personal Records -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    {{ __('Personal Records') }}
                </h2>
                <div class="space-y-3">
                    @if($personalRecords['max_weight'])
                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Max Weight') }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $personalRecords['max_weight_exercise'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-500">{{ $personalRecords['max_weight_date']->format('M d, Y') }}</p>
                            </div>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $personalRecords['max_weight'] }} lbs</p>
                        </div>
                    @endif

                    @if($personalRecords['max_reps'])
                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Max Reps') }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $personalRecords['max_reps_exercise'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-500">{{ $personalRecords['max_reps_date']->format('M d, Y') }}</p>
                            </div>
                            <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $personalRecords['max_reps'] }} reps</p>
                        </div>
                    @endif

                    @if($personalRecords['max_distance'])
                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Max Distance') }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $personalRecords['max_distance_exercise'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-500">{{ $personalRecords['max_distance_date']->format('M d, Y') }}</p>
                            </div>
                            <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ number_format($personalRecords['max_distance'], 2) }} miles</p>
                        </div>
                    @endif

                    @if($personalRecords['best_time'])
                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Best Time') }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $personalRecords['best_time_exercise'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-500">{{ $personalRecords['best_time_date']->format('M d, Y') }}</p>
                            </div>
                            <p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ gmdate('H:i:s', $personalRecords['best_time']) }}</p>
                        </div>
                    @endif

                    @if(!$personalRecords['max_weight'] && !$personalRecords['max_reps'] && !$personalRecords['max_distance'] && !$personalRecords['best_time'])
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center py-4">
                            {{ __('No personal records yet. Keep logging workouts!') }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Most Performed Exercises -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    {{ __('Most Performed Exercises') }}
                </h2>
                <div class="space-y-2">
                    @forelse($mostPerformed as $exercise)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-neutral-800">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $exercise['name'] }}</span>
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $exercise['count'] }} {{ __('times') }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center py-4">
                            {{ __('No exercises logged yet.') }}
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Workout Frequency Chart -->
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                {{ __('Workout Frequency (Last 12 Weeks)') }}
            </h2>
            @php
                $maxCount = collect($workoutFrequency)->max('count') ?? 1;
            @endphp
            <div class="flex items-end justify-between gap-2 h-48">
                @foreach($workoutFrequency as $week)
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full flex items-end justify-center" style="height: 180px;">
                            @if($week['count'] > 0)
                                <div 
                                    class="w-full rounded-t bg-blue-500 dark:bg-blue-600 transition-all hover:bg-blue-600 dark:hover:bg-blue-500 cursor-pointer"
                                    style="height: {{ max(10, ($week['count'] / $maxCount) * 100) }}%"
                                    title="{{ $week['count'] }} {{ __('workouts') }}"
                                ></div>
                            @else
                                <div 
                                    class="w-full rounded-t bg-zinc-200 dark:bg-neutral-700"
                                    style="height: 2px"
                                    title="0 {{ __('workouts') }}"
                                ></div>
                            @endif
                        </div>
                        <span class="text-xs text-zinc-600 dark:text-zinc-400 transform -rotate-45 origin-top-left whitespace-nowrap">{{ $week['week'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Program Progress -->
        @if(!empty($programProgress))
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    {{ __('Active Program Progress') }}
                </h2>
                <div class="space-y-4">
                    @foreach($programProgress as $progress)
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $progress['program']->name }}</h3>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $progress['logged'] }} / {{ $progress['total'] }} {{ __('workouts') }}
                                </span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-neutral-700 rounded-full h-2">
                                <div 
                                    class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full transition-all"
                                    style="width: {{ $progress['progress'] }}%"
                                ></div>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">{{ $progress['progress'] }}% {{ __('complete') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Recent Workouts -->
        @if($recentWorkouts->isNotEmpty())
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-900">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ __('Recent Workouts') }}
                    </h2>
                    <flux:button href="{{ route('workouts.history') }}" variant="ghost" size="sm" wire:navigate>
                        {{ __('View All') }}
                    </flux:button>
                </div>
                <div class="space-y-3">
                    @foreach($recentWorkouts as $log)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-neutral-800">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $log->activeProgram->program->name }}
                                </p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ $log->workout_date->format('M d, Y') }} • {{ $log->exercises->count() }} {{ __('exercises') }}
                                </p>
                            </div>
                            <flux:button 
                                href="{{ route('workouts.log', ['activeProgram' => $log->active_program_id, 'date' => $log->workout_date->format('Y-m-d')]) }}" 
                                variant="ghost" 
                                size="sm"
                                wire:navigate
                            >
                                {{ __('View') }}
                            </flux:button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

