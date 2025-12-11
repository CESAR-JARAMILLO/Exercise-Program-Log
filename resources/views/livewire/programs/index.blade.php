<?php

use App\Models\ActiveProgram;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();

        // Get owned programs
        $ownedPrograms = Program::where('user_id', $user->id)
            ->with([
                'activePrograms' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)->where('status', 'active');
                },
                'activeProgramsStopped' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)->where('status', 'stopped')->orderBy('stopped_at', 'desc');
                },
                'assignments.trainer',
                'assignments.client', // Load all assignments to count clients
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get programs where user is the trainer (assigned to clients)
        $trainerPrograms = Program::where('trainer_id', $user->id)
            ->where('user_id', '!=', $user->id) // Exclude programs already in ownedPrograms
            ->with([
                'activePrograms' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)->where('status', 'active');
                },
                'activeProgramsStopped' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)->where('status', 'stopped')->orderBy('stopped_at', 'desc');
                },
                'assignments.trainer',
                'assignments.client', // Load all assignments to count clients
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get assigned programs (programs assigned to this user as a client)
        $assignedPrograms = Program::whereHas('assignments', function ($query) use ($user) {
            $query->where('client_id', $user->id);
        })
            ->with([
                'activePrograms' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)->where('status', 'active');
                },
                'activeProgramsStopped' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)->where('status', 'stopped')->orderBy('stopped_at', 'desc');
                },
                'assignments' => function ($query) use ($user) {
                    $query->where('client_id', $user->id);
                },
                'assignments.trainer',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Combine and merge (avoid duplicates if a program appears in multiple categories)
        $programs = $ownedPrograms->merge($trainerPrograms)->merge($assignedPrograms)->unique('id');

        // For each program, load all active programs count (for client count) if user is trainer/owner
        $programsWithClientCounts = [];
        foreach ($programs as $program) {
            // If user is owner or trainer, count all active programs (all clients)
            if ($program->user_id === $user->id || $program->trainer_id === $user->id) {
                // Use direct query to get ALL active programs, not just current user's
                $allActiveCount = ActiveProgram::where('program_id', $program->id)->where('status', 'active')->count();
                $programsWithClientCounts[$program->id] = [
                    'assigned_count' => $program->assignments->count(),
                    'active_count' => $allActiveCount,
                ];
            }
        }

        // Get today's workout status for each active program
        $activeProgramsWithWorkouts = [];
        foreach ($programs as $program) {
            foreach ($program->activePrograms as $activeProgram) {
                $todayStatus = $activeProgram->getTodayWorkoutStatus();
                if ($todayStatus) {
                    $activeProgramsWithWorkouts[$activeProgram->id] = [
                        'active_program' => $activeProgram,
                        'todayStatus' => $todayStatus,
                    ];
                }
            }
        }

        return [
            'programs' => $programs,
            'activeProgramsWithWorkouts' => $activeProgramsWithWorkouts,
            'programsWithClientCounts' => $programsWithClientCounts,
            'user' => $user,
            'programCount' => $user->getProgramCount(), // Only counts owned programs
            'maxPrograms' => $user->getMaxPrograms(),
            'canCreateProgram' => $user->canCreateProgram(),
        ];
    }

    // DELETE LATER : This is local function for the delete button.
    public function delete(Program $program): void
    {
        // Ensure user can delete this program (only owner/trainer can delete)
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
    }
}; ?>

<section class="w-full px-2 sm:px-0">
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

    <div class="mb-6 flex flex-col md:flex-row items-center md:items-center justify-between gap-4">
        <div class="flex-1 min-w-0 w-full md:w-auto text-center lg:text-left">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('My Programs') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Manage your training programs') }}
                @if ($maxPrograms !== null)
                    <span class="ml-2 font-medium">
                        ({{ $programCount }}/{{ $maxPrograms }})
                    </span>
                @else
                    <span class="ml-2 font-medium">
                        ({{ $programCount }})
                    </span>
                @endif
            </p>
        </div>
        <div class="w-full md:w-auto">
            @if ($canCreateProgram)
                <flux:button href="{{ route('programs.create') }}" variant="primary" wire:navigate
                    class="w-full md:w-auto">
                    {{ __('Create Program') }}
                </flux:button>
            @else
                <flux:button href="{{ route('programs.create') }}" variant="primary" disabled class="w-full md:w-auto">
                    {{ __('Create Program') }}
                </flux:button>
            @endif
        </div>
    </div>

    @if (!$canCreateProgram)
        <div
            class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium">{{ __('Program Limit Reached') }}</p>
                    <p class="mt-1">
                        {{ __('You\'ve reached your limit of :max programs. Upgrade your subscription to create more programs.', ['max' => $maxPrograms]) }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if ($programs->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <p class="text-zinc-600 dark:text-zinc-400">
                {{ __('No programs yet. Create your first program to get started!') }}
            </p>
            @if ($canCreateProgram)
                <flux:button href="{{ route('programs.create') }}" variant="primary" class="mt-4" wire:navigate>
                    {{ __('Create Program') }}
                </flux:button>
            @endif
        </div>
    @else
        <div class="grid gap-3 sm:gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($programs as $program)
                <div
                    class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-3 sm:p-4 md:p-6 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors w-full max-w-full sm:max-w-sm md:max-w-none mx-auto md:mx-0">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $program->name }}
                                </h3>
                                @if ($program->isAssignedToMe())
                                    @php
                                        $assignment = $program->assignments->where('client_id', Auth::id())->first();
                                        $trainer = $assignment ? $assignment->trainer : null;
                                    @endphp
                                    @if ($trainer)
                                        <span
                                            class="rounded-full bg-blue-100 dark:bg-blue-900/30 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300">
                                            {{ __('Assigned by :name', ['name' => $trainer->name]) }}
                                        </span>
                                    @endif
                                @endif
                                @if ($program->activePrograms->isNotEmpty())
                                    <span
                                        class="rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-300">
                                        {{ __('Active') }}
                                    </span>
                                @else
                                    <span
                                        class="rounded-full bg-neutral-100 dark:bg-neutral-800 px-2 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        {{ __('Template') }}
                                    </span>
                                @endif
                                @php
                                    // Show client assignment badge for trainers/owners
                                    $isOwnerOrTrainer =
                                        $program->user_id === Auth::id() || $program->trainer_id === Auth::id();
                                    $clientCounts = $programsWithClientCounts[$program->id] ?? null;
                                    $assignedClientsCount = $clientCounts ? $clientCounts['assigned_count'] : 0;
                                    $activeClientsCount = $clientCounts ? $clientCounts['active_count'] : 0;
                                @endphp
                                @if ($isOwnerOrTrainer && $assignedClientsCount > 0)
                                    <span
                                        class="rounded-full bg-purple-100 dark:bg-purple-900/30 px-2 py-1 text-xs font-medium text-purple-700 dark:text-purple-300"
                                        title="{{ __(':active active out of :total assigned clients', ['active' => $activeClientsCount, 'total' => $assignedClientsCount]) }}">
                                        {{ __(':active/:total Clients', ['active' => $activeClientsCount, 'total' => $assignedClientsCount]) }}
                                    </span>
                                @endif
                            </div>
                            @if ($program->description)
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                                    {{ $program->description }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        @if ($program->length_weeks)
                            <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium">{{ __('Duration:') }}</span>
                                <span class="ml-2">{{ $program->length_weeks }} {{ __('Weeks') }}</span>
                            </div>
                        @endif
                        @if ($program->activePrograms->isNotEmpty())
                            @php
                                $firstActiveProgram = $program->activePrograms->first();
                            @endphp
                            <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium">{{ __('Started:') }}</span>
                                <span class="ml-2">{{ $firstActiveProgram->started_at->format('M d, Y') }}</span>
                            </div>
                            @if ($program->activePrograms->count() > 1)
                                <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                                    <span class="font-medium">{{ __('Active Instances:') }}</span>
                                    <span class="ml-2">{{ $program->activePrograms->count() }}</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-2 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <flux:button href="{{ route('programs.show', $program) }}" variant="ghost" size="sm"
                            wire:navigate>
                            {{ __('View') }}
                        </flux:button>
                        @if ($program->isTemplate() && $program->canBeEditedBy($user) && $user->isTrainer())
                            <flux:button href="{{ route('programs.assign', $program) }}" variant="primary"
                                size="sm" wire:navigate>
                                {{ __('Assign') }}
                            </flux:button>
                        @endif
                        @if ($program->isTemplate() && $program->activePrograms->isEmpty())
                            <flux:button href="{{ route('programs.start', $program) }}" variant="primary"
                                size="sm" wire:navigate>
                                {{ __('Start') }}
                            </flux:button>
                        @elseif ($program->activePrograms->isNotEmpty())
                            @php
                                $firstActiveProgram = $program->activePrograms->first();
                                $workoutData = $activeProgramsWithWorkouts[$firstActiveProgram->id] ?? null;
                                $todayStatus = $workoutData['todayStatus'] ?? null;
                            @endphp
                            @if ($todayStatus && !$todayStatus['isLogged'])
                                {{-- Today has workout and it's not logged yet --}}
                                <flux:button
                                    href="{{ route('workouts.log', ['activeProgram' => $firstActiveProgram->id,'date' => now()->setTimezone(auth()->user()?->getTimezone() ?? 'UTC')->format('Y-m-d')]) }}"
                                    variant="primary" size="sm" wire:navigate>
                                    {{ __('Log Workout') }}
                                </flux:button>
                            @else
                                {{-- Show calendar button otherwise --}}
                                <flux:button href="{{ route('workouts.calendar', $program) }}" variant="primary"
                                    size="sm" wire:navigate>
                                    {{ __('Calendar') }}
                                </flux:button>
                            @endif
                        @elseif($program->activeProgramsStopped->isNotEmpty())
                            @php
                                $lastStoppedProgram = $program->activeProgramsStopped->first();
                            @endphp
                            <flux:button href="{{ route('active-programs.restart', $lastStoppedProgram) }}"
                                variant="primary" size="sm" wire:navigate>
                                {{ __('Restart') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
