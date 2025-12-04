<?php

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    // Track which weeks are expanded
    public array $expandedWeeks = [];
    public int $programId;

    public function mount($program = null): void
    {
        // Get program ID from route parameter
        $programId = $program ?? request()->route('program');

        // Handle both ID and model instance
        if ($programId instanceof Program) {
            $this->programId = $programId->id;
        } else {
            $this->programId = (int) $programId;
        }
    }

    public function with(): array
    {
        // Get program from stored ID
        $program = Program::findOrFail($this->programId);

        // Ensure user can view this program (owner, trainer, or assigned client)
        abort_unless($program->canBeViewedBy(Auth::user()), 403);

        $program = $program->load([
            'weeks.days.exercises' => function ($query) {
                $query->orderBy('order');
            },
            'activePrograms' => function ($query) {
                $query->where('user_id', Auth::id())->where('status', 'active');
            },
            'activeProgramsStopped' => function ($query) {
                $query->where('user_id', Auth::id())->where('status', 'stopped')->orderBy('stopped_at', 'desc')->limit(1);
            },
            'assignments.client',
            'trainer',
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

        $user = Auth::user();
        $isAssigned = $program->isAssignedToMe();
        $assignedBy = null;
        if ($isAssigned) {
            $assignment = $program->assignments()->where('client_id', $user->id)->first();
            $assignedBy = $assignment ? $assignment->trainer : null;
        }

        return [
            'program' => $program,
            'activeProgramsWithWorkouts' => $activeProgramsWithWorkouts,
            'user' => $user,
            'isAssigned' => $isAssigned,
            'assignedBy' => $assignedBy,
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
        abort_unless($program->canBeDeletedBy(Auth::user()), 403);

        // Check if program has active instances
        $hasActivePrograms = $program->activePrograms()->where('status', 'active')->exists();
        if ($hasActivePrograms) {
            session()->flash('error', __('Cannot delete program: There are active instances of this program. Please stop all active instances first.'));
            return;
        }

        // Check if program has assignments
        $hasAssignments = $program->assignments()->exists();
        if ($hasAssignments) {
            session()->flash('error', __('Cannot delete program: This program has been assigned to clients. Please unassign it first.'));
            return;
        }

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

    <div class="mb-6 flex flex-col md:flex-row items-center md:items-center md:justify-between gap-4">
        <div class="flex-1 min-w-0 w-full md:w-auto text-center lg:text-left">
            <div
                class="flex flex-col sm:flex-row items-center sm:items-center justify-center lg:justify-start gap-2 mb-1">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $program->name }}
                </h1>
                @if ($isAssigned && $assignedBy)
                    <span
                        class="inline-flex items-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 px-3 py-1 text-sm font-medium">
                        {{ __('Assigned by :name', ['name' => $assignedBy->name]) }}
                    </span>
                @endif
            </div>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $program->description ?? __('View your training program details') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center justify-center md:justify-end gap-2 w-full md:w-auto">
            @if ($program->isTemplate() && $program->canBeEditedBy($user) && $user->isTrainer())
                <flux:button href="{{ route('programs.assign', $program) }}" variant="primary" wire:navigate>
                    {{ __('Assign to Client') }}
                </flux:button>
            @endif
            @if ($program->isTemplate() && !$isAssigned)
                <flux:button href="{{ route('programs.start', $program) }}" variant="primary" wire:navigate>
                    {{ __('Start Program') }}
                </flux:button>
            @elseif ($program->isTemplate() && $isAssigned)
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
                        href="{{ route('workouts.log', ['activeProgram' => $firstActiveProgram->id,'date' => now()->setTimezone(auth()->user()?->getTimezone() ?? 'UTC')->format('Y-m-d')]) }}"
                        variant="primary" wire:navigate>
                        {{ __('Log Workout') }}
                    </flux:button>
                @else
                    {{-- Show calendar button otherwise --}}
                    <flux:button href="{{ route('workouts.calendar', $program) }}" variant="primary" wire:navigate>
                        {{ __('Calendar') }}
                    </flux:button>
                @endif
                <flux:button href="{{ route('active-programs.stop', $firstActiveProgram) }}" variant="ghost"
                    wire:navigate class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                    {{ __('Stop') }}
                </flux:button>
            @elseif($program->activeProgramsStopped->isNotEmpty())
                @php
                    $lastStoppedProgram = $program->activeProgramsStopped->first();
                @endphp
                <flux:button href="{{ route('active-programs.restart', $lastStoppedProgram) }}" variant="primary"
                    wire:navigate>
                    {{ __('Restart Program') }}
                </flux:button>
            @endif
            @if ($program->canBeEditedBy($user))
                <flux:button href="{{ route('programs.edit', $program) }}" variant="ghost" wire:navigate>
                    {{ __('Edit') }}
                </flux:button>
            @endif
            @if ($program->canBeDeletedBy($user))
                <flux:button wire:click="delete"
                    wire:confirm="{{ __('Are you sure you want to delete this program?') }}" variant="ghost"
                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                    {{ __('Delete') }}
                </flux:button>
            @endif
        </div>
    </div>

    <!-- Assigned Clients (for trainers) -->
    @if ($user->isTrainer() && $program->canBeEditedBy($user) && $program->assignments->isNotEmpty())
        <div class="mb-6 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Assigned Clients') }}
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('Client Name') }}</th>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('Email') }}</th>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('Status') }}</th>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('Assigned At') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900">
                        @foreach ($program->assignments as $assignment)
                            <tr>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $assignment->client->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ $assignment->client->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                        @if ($assignment->status === 'assigned') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300
                                        @elseif($assignment->status === 'started') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                                        @elseif($assignment->status === 'completed') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 @endif">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ $assignment->assigned_at->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

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

            @if ($program->activePrograms->isNotEmpty())
                @php
                    $firstActiveProgram = $program->activePrograms->first();
                    $endDate = $firstActiveProgram->started_at->copy()->addWeeks($program->length_weeks);
                @endphp
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ __('Start Date') }}
                    </p>
                    <p class="mt-1 text-zinc-900 dark:text-zinc-100">
                        {{ $firstActiveProgram->started_at->format('M d, Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ __('End Date') }}
                    </p>
                    <p class="mt-1 text-zinc-900 dark:text-zinc-100">
                        {{ $endDate->format('M d, Y') }}
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
                                        <h4
                                            class="mb-3 font-medium text-zinc-900 dark:text-zinc-100 text-center md:text-left">
                                            {{ $day->label ?: __('Day :number', ['number' => $day->day_number]) }}
                                        </h4>

                                        @if ($day->exercises->isEmpty())
                                            <p
                                                class="text-sm text-zinc-500 dark:text-zinc-400 italic text-center md:text-left">
                                                {{ __('No exercises for this day.') }}
                                            </p>
                                        @else
                                            <div class="space-y-3">
                                                @foreach ($day->exercises as $exercise)
                                                    <div
                                                        class="rounded border border-neutral-200 dark:border-neutral-600 p-4 bg-white dark:bg-neutral-900">
                                                        <div class="mb-3 text-center md:text-left">
                                                            <h5 class="font-medium text-zinc-900 dark:text-zinc-100">
                                                                {{ $exercise->name }}
                                                            </h5>
                                                            <span
                                                                class="mt-1 inline-block rounded-full bg-primary-100 dark:bg-primary-900/30 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300">
                                                                {{ ucfirst($exercise->type) }}
                                                            </span>
                                                        </div>

                                                        <div class="grid gap-3 md:grid-cols-4">
                                                            @if ($exercise->sets || ($exercise->sets_min && $exercise->sets_max))
                                                                <div class="text-center md:text-left">
                                                                    <p
                                                                        class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                        {{ __('Sets') }}
                                                                    </p>
                                                                    <p
                                                                        class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                        @if ($exercise->sets_min && $exercise->sets_max)
                                                                            {{ $exercise->sets_min }}-{{ $exercise->sets_max }}
                                                                        @else
                                                                            {{ $exercise->sets }}
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            @endif

                                                            @if ($exercise->reps || ($exercise->reps_min && $exercise->reps_max))
                                                                <div class="text-center md:text-left">
                                                                    <p
                                                                        class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                        {{ __('Reps') }}
                                                                    </p>
                                                                    <p
                                                                        class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                        @if ($exercise->reps_min && $exercise->reps_max)
                                                                            {{ $exercise->reps_min }}-{{ $exercise->reps_max }}
                                                                        @else
                                                                            {{ $exercise->reps }}
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            @endif

                                                            @if ($exercise->weight || ($exercise->weight_min && $exercise->weight_max))
                                                                <div class="text-center md:text-left">
                                                                    <p
                                                                        class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                        {{ __('Weight') }}
                                                                    </p>
                                                                    <p
                                                                        class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                        @if ($exercise->weight_min && $exercise->weight_max)
                                                                            {{ $exercise->weight_min }}-{{ $exercise->weight_max }}
                                                                            lbs
                                                                        @else
                                                                            {{ $exercise->weight }} lbs
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            @endif

                                                            @if ($exercise->distance || ($exercise->distance_min && $exercise->distance_max))
                                                                <div class="text-center md:text-left">
                                                                    <p
                                                                        class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                        {{ __('Distance') }}
                                                                    </p>
                                                                    <p
                                                                        class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                        @if ($exercise->distance_min && $exercise->distance_max)
                                                                            {{ $exercise->distance_min }}-{{ $exercise->distance_max }}
                                                                            miles
                                                                        @else
                                                                            {{ $exercise->distance }} miles
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @if ($exercise->time_seconds || ($exercise->time_seconds_min && $exercise->time_seconds_max))
                                                            <div class="mt-3 text-center md:text-left">
                                                                <p
                                                                    class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                                                    {{ __('Time') }}
                                                                </p>
                                                                <p
                                                                    class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                                                    @if ($exercise->time_seconds_min && $exercise->time_seconds_max)
                                                                        {{ gmdate('H:i:s', $exercise->time_seconds_min) }}-{{ gmdate('H:i:s', $exercise->time_seconds_max) }}
                                                                    @else
                                                                        {{ gmdate('H:i:s', $exercise->time_seconds) }}
                                                                    @endif
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
