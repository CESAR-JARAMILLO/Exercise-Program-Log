<?php

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    // Track which weeks are expanded
    public array $expandedWeeks = [];

    public function with(): array
    {
        // Get program ID from route parameter
        $programId = request()->route('program');

        // Handle both ID and model instance
        if ($programId instanceof Program) {
            $program = $programId;
        } else {
            $program = Program::findOrFail($programId);
        }

        // Ensure user owns this program
        abort_unless($program->user_id === Auth::id(), 403);

        $program = $program->load([
            'weeks.days.exercises' => function ($query) {
                $query->orderBy('order');
            },
            'activePrograms' => function ($query) {
                $query->where('user_id', Auth::id())->where('status', 'active');
            },
        ]);

        // Get today's workout status for active programs
        $activeProgramsWithWorkouts = [];
        foreach ($program->activePrograms as $activeProgram) {
            $todayStatus = $activeProgram->getTodayWorkoutStatus();
            if ($todayStatus) {
                $activeProgramsWithWorkouts[$activeProgram->id] = [
                    'active_program' => $activeProgram,
                    'todayStatus' => $todayStatus,
                ];
            }
        }

        // Expand all weeks by default on first load
        if (empty($this->expandedWeeks)) {
            foreach ($program->weeks as $week) {
                $this->expandedWeeks[$week->week_number] = true;
            }
        }

        return [
            'program' => $program,
            'activeProgramsWithWorkouts' => $activeProgramsWithWorkouts,
        ];
    }

    public function toggleWeek($weekNumber): void
    {
        $weekNumber = (int) $weekNumber;
        $this->expandedWeeks[$weekNumber] = !($this->expandedWeeks[$weekNumber] ?? false);
    }

    public function delete(): void
    {
        $programId = request()->route('program');
        $program = $programId instanceof Program ? $programId : Program::findOrFail($programId);
        abort_unless($program->user_id === Auth::id(), 403);
        $program->delete();
        session()->flash('success', __('Program deleted successfully.'));
        $this->redirect(route('programs.index'));
    }
}; ?>

<section class="w-full">
    @if (session('success'))
        <div
            class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/50 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/50 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ $program->name }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ $program->description ?? __('View your training program details') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if ($program->isTemplate())
                <flux:button href="{{ route('programs.start', $program) }}" variant="primary" wire:navigate>
                    {{ __('Start Program') }}
                </flux:button>
            @elseif($program->activePrograms->isNotEmpty())
                @php
                    $firstActiveProgram = $program->activePrograms->first();
                    $workoutData = $activeProgramsWithWorkouts[$firstActiveProgram->id] ?? null;
                    $todayStatus = $workoutData['todayStatus'] ?? null;
                @endphp
                @if ($todayStatus && !$todayStatus['isLogged'])
                    {{-- Today has workout and it's not logged yet --}}
                    <flux:button
                        href="{{ route('workouts.log', ['activeProgram' => $firstActiveProgram->id, 'date' => now()->format('Y-m-d')]) }}"
                        variant="primary" wire:navigate>
                        {{ __('Log Workout') }}
                    </flux:button>
                @else
                    {{-- Show calendar button otherwise --}}
                    <flux:button href="{{ route('workouts.calendar') }}" variant="primary" wire:navigate>
                        {{ __('Calendar') }}
                    </flux:button>
                @endif
            @endif
            <flux:button href="{{ route('programs.edit', $program) }}" variant="ghost" wire:navigate>
                {{ __('Edit') }}
            </flux:button>
            <flux:button wire:click="delete" wire:confirm="{{ __('Are you sure you want to delete this program?') }}"
                variant="ghost" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                {{ __('Delete') }}
            </flux:button>
        </div>
    </div>

    <!-- Program Details -->
    <div class="mb-6 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
            {{ __('Program Information') }}
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                    {{ __('Length') }}
                </p>
                <p class="mt-1 text-zinc-900 dark:text-zinc-100">
                    {{ $program->length_weeks }} {{ __('weeks') }}
                </p>
            </div>

            @if ($program->start_date)
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ __('Start Date') }}
                    </p>
                    <p class="mt-1 text-zinc-900 dark:text-zinc-100">
                        {{ $program->start_date->format('M d, Y') }}
                    </p>
                </div>
            @endif

            @if ($program->end_date)
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ __('End Date') }}
                    </p>
                    <p class="mt-1 text-zinc-900 dark:text-zinc-100">
                        {{ $program->end_date->format('M d, Y') }}
                    </p>
                </div>
            @endif
        </div>

        @if ($program->notes)
            <div class="mt-4">
                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                    {{ __('Notes') }}
                </p>
                <p class="mt-1 text-zinc-900 dark:text-zinc-100">
                    {{ $program->notes }}
                </p>
            </div>
        @endif
    </div>

    <!-- Weeks, Days, and Exercises -->
    @if ($program->weeks->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <p class="text-zinc-600 dark:text-zinc-400">
                {{ __('No weeks have been added to this program yet.') }}
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($program->weeks->sortBy('week_number') as $week)
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <button type="button" wire:click="toggleWeek({{ $week->week_number }})"
                        class="w-full flex items-center justify-between p-6 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('Week :number', ['number' => $week->week_number]) }}
                        </h3>
                        <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400 transition-transform {{ $expandedWeeks[$week->week_number] ?? true ? 'rotate-180' : '' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    @if ($expandedWeeks[$week->week_number] ?? true)
                        <div class="px-6 pb-6">
                            <div class="space-y-4">
                                @foreach ($week->days->sortBy('day_number') as $day)
                                    <div
                                        class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-4 bg-neutral-50 dark:bg-neutral-800/50">
                                        <h4 class="mb-3 font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $day->label ?: __('Day :number', ['number' => $day->day_number]) }}
                                        </h4>

                                        @if ($day->exercises->isEmpty())
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                                {{ __('No exercises for this day.') }}
                                            </p>
                                        @else
                                            <div class="space-y-3">
                                                @foreach ($day->exercises as $exercise)
                                                    <div
                                                        class="rounded border border-neutral-200 dark:border-neutral-600 p-4 bg-white dark:bg-neutral-900">
                                                        <div class="mb-3">
                                                            <h5 class="font-medium text-zinc-900 dark:text-zinc-100">
                                                                {{ $exercise->name }}
                                                            </h5>
                                                            <span
                                                                class="mt-1 inline-block rounded-full bg-primary-100 dark:bg-primary-900/30 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300">
                                                                {{ ucfirst($exercise->type) }}
                                                            </span>
                                                        </div>

                                                        <div class="grid gap-3 md:grid-cols-4">
                                                            @if ($exercise->sets)
                                                                <div>
                                                                    <p
                                                                        class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                        {{ __('Sets') }}
                                                                    </p>
                                                                    <p
                                                                        class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                        {{ $exercise->sets }}
                                                                    </p>
                                                                </div>
                                                            @endif

                                                            @if ($exercise->reps)
                                                                <div>
                                                                    <p
                                                                        class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                        {{ __('Reps') }}
                                                                    </p>
                                                                    <p
                                                                        class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                        {{ $exercise->reps }}
                                                                    </p>
                                                                </div>
                                                            @endif

                                                            @if ($exercise->weight)
                                                                <div>
                                                                    <p
                                                                        class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                        {{ __('Weight') }}
                                                                    </p>
                                                                    <p
                                                                        class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                        {{ $exercise->weight }} lbs
                                                                    </p>
                                                                </div>
                                                            @endif

                                                            @if ($exercise->distance)
                                                                <div>
                                                                    <p
                                                                        class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                        {{ __('Distance') }}
                                                                    </p>
                                                                    <p
                                                                        class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                        {{ $exercise->distance }} miles
                                                                    </p>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @if ($exercise->time_seconds)
                                                            <div class="mt-3">
                                                                <p
                                                                    class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                    {{ __('Time') }}
                                                                </p>
                                                                <p
                                                                    class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                    {{ gmdate('H:i:s', $exercise->time_seconds) }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Back Button -->
    <div class="mt-6 flex items-center gap-4 border-t border-neutral-200 dark:border-neutral-700 pt-6">
        <flux:button href="{{ route('programs.index') }}" variant="ghost" wire:navigate>
            {{ __('← Back to Programs') }}
        </flux:button>
    </div>
</section>
