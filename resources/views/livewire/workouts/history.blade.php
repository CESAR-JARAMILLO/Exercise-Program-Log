<?php

use App\Models\WorkoutLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $workoutLogs = WorkoutLog::where('user_id', Auth::id())
            ->with(['activeProgram.program', 'programDay', 'exercises.dayExercise'])
            ->orderBy('workout_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return [
            'workoutLogs' => $workoutLogs,
        ];
    }
}; ?>

<section class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('Workout History') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('View all your logged workouts') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <flux:button href="{{ route('workouts.calendar') }}" variant="ghost" wire:navigate>
                {{ __('Calendar') }}
            </flux:button>
            <flux:button href="{{ route('programs.index') }}" variant="ghost" wire:navigate>
                {{ __('Programs') }}
            </flux:button>
        </div>
    </div>

    @if($workoutLogs->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                {{ __('No workouts logged yet.') }}
            </p>
            <flux:button href="{{ route('workouts.calendar') }}" variant="primary" wire:navigate>
                {{ __('Go to Calendar') }}
            </flux:button>
        </div>
    @else
        <div class="space-y-4">
            @foreach($workoutLogs as $log)
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="mb-4 flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $log->activeProgram->program->name }}
                            </h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $log->workout_date->format('M d, Y') }} | 
                                Week {{ $log->programDay->week->week_number }}, Day {{ $log->programDay->day_number }}
                            </p>
                        </div>
                        <flux:button 
                            href="{{ route('workouts.log', ['activeProgram' => $log->active_program_id, 'date' => $log->workout_date->format('Y-m-d')]) }}" 
                            variant="ghost" 
                            size="sm"
                            wire:navigate
                        >
                            {{ __('Edit') }}
                        </flux:button>
                    </div>

                    @if($log->notes)
                        <div class="mb-4 rounded-lg bg-neutral-50 dark:bg-neutral-800/50 p-3">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $log->notes }}
                            </p>
                        </div>
                    @endif

                    <div class="space-y-3">
                        @foreach($log->exercises as $workoutExercise)
                            <div class="rounded-lg border border-neutral-200 dark:border-neutral-600 p-4 bg-white dark:bg-neutral-900">
                                <div class="mb-2">
                                    <h4 class="font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $workoutExercise->name }}
                                    </h4>
                                    <span class="mt-1 inline-block rounded-full bg-primary-100 dark:bg-primary-900/30 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300">
                                        {{ ucfirst($workoutExercise->type) }}
                                    </span>
                                </div>

                                <div class="grid gap-3 md:grid-cols-4 text-sm">
                                    @if($workoutExercise->sets_actual)
                                        <div>
                                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Sets:') }}</span>
                                            <span class="ml-2 text-zinc-900 dark:text-zinc-100">{{ $workoutExercise->sets_actual }}</span>
                                        </div>
                                    @endif
                                    @if($workoutExercise->reps_actual)
                                        <div>
                                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Reps:') }}</span>
                                            <span class="ml-2 text-zinc-900 dark:text-zinc-100">{{ $workoutExercise->reps_actual }}</span>
                                        </div>
                                    @endif
                                    @if($workoutExercise->weight_actual)
                                        <div>
                                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Weight:') }}</span>
                                            <span class="ml-2 text-zinc-900 dark:text-zinc-100">{{ $workoutExercise->weight_actual }} lbs</span>
                                        </div>
                                    @endif
                                    @if($workoutExercise->distance_actual)
                                        <div>
                                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Distance:') }}</span>
                                            <span class="ml-2 text-zinc-900 dark:text-zinc-100">{{ $workoutExercise->distance_actual }} miles</span>
                                        </div>
                                    @endif
                                    @if($workoutExercise->time_seconds_actual)
                                        <div>
                                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Time:') }}</span>
                                            <span class="ml-2 text-zinc-900 dark:text-zinc-100">{{ gmdate('H:i:s', $workoutExercise->time_seconds_actual) }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if($workoutExercise->notes)
                                    <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                                        <p><em>{{ $workoutExercise->notes }}</em></p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="mt-6">
                {{ $workoutLogs->links() }}
            </div>
        </div>
    @endif
</section>

