<?php

use App\Models\ActiveProgram;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $currentMonth;
    public string $currentYear;
    public ?int $selectedProgramId = null;

    public function mount($program = null): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->currentYear = now()->format('Y');
        
        // If program is passed via route, use it
        if ($program) {
            // Handle both slug and model instance
            if ($program instanceof Program) {
                $programModel = $program;
            } else {
                $programModel = Program::where('slug', $program)->orWhere('id', $program)->first();
            }
            
            if ($programModel) {
                // Find the active program for this user
                $activeProgram = ActiveProgram::where('user_id', Auth::id())
                    ->where('program_id', $programModel->id)
                    ->where('status', 'active')
                    ->first();
                
                if ($activeProgram) {
                    $this->selectedProgramId = (int) $activeProgram->program_id;
                }
            }
        }
        
        // Set default to first active program if none selected
        if (!$this->selectedProgramId) {
            $firstActiveProgram = ActiveProgram::where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();
            if ($firstActiveProgram) {
                $this->selectedProgramId = (int) $firstActiveProgram->program_id;
            }
        }
    }

    public function with(): array
    {
        // Get all active programs for the selector dropdown
        $allActivePrograms = ActiveProgram::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('program')
            ->get();
        
        // Only show calendar for selected program - require selection
        $activePrograms = collect();
        if ($this->selectedProgramId) {
            $activePrograms = ActiveProgram::where('user_id', Auth::id())
                ->where('status', 'active')
                ->where('program_id', $this->selectedProgramId)
                ->with(['program', 'workoutLogs'])
                ->get();
        }

        // Get all scheduled dates for the current month
        $scheduledDates = [];
        $loggedDates = [];
        
        foreach ($activePrograms as $activeProgram) {
            $dates = $activeProgram->getScheduledDates();
            foreach ($dates as $scheduled) {
                $date = $scheduled['date'];
                if ($date->format('Y-m') === $this->currentMonth) {
                    $scheduledDates[$date->format('Y-m-d')] = [
                        'active_program' => $activeProgram,
                        'program' => $activeProgram->program, // Store program directly
                        'week' => $scheduled['week'],
                        'day' => $scheduled['day'],
                        'program_day_id' => $scheduled['program_day_id'],
                        'is_rest_day' => $scheduled['is_rest_day'] ?? false,
                    ];
                }
            }

            // Get logged workouts for this month
            $logs = $activeProgram->workoutLogs()
                ->whereYear('workout_date', $this->currentYear)
                ->whereMonth('workout_date', Carbon::parse($this->currentMonth)->month)
                ->get();
            
            foreach ($logs as $log) {
                $loggedDates[$log->workout_date->format('Y-m-d')] = $log;
            }
        }

        // Build calendar
        $startOfMonth = Carbon::parse($this->currentMonth . '-01');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();

        $calendarDays = [];
        $currentDay = $startOfCalendar->copy();
        
        while ($currentDay <= $endOfCalendar) {
            $dateKey = $currentDay->format('Y-m-d');
            $scheduledWorkout = $scheduledDates[$dateKey] ?? null;
            $isRestDay = isset($scheduledWorkout) && ($scheduledWorkout['is_rest_day'] ?? false);
            
            $calendarDays[] = [
                'date' => $currentDay->copy(),
                'isCurrentMonth' => $currentDay->format('Y-m') === $this->currentMonth,
                'isToday' => $currentDay->isToday(),
                'hasScheduledWorkout' => isset($scheduledDates[$dateKey]) && !$isRestDay,
                'isRestDay' => $isRestDay,
                'scheduledWorkout' => $scheduledWorkout,
                'hasLoggedWorkout' => isset($loggedDates[$dateKey]),
                'workoutLog' => $loggedDates[$dateKey] ?? null,
            ];
            $currentDay->addDay();
        }

        return [
            'activePrograms' => $activePrograms,
            'allActivePrograms' => $allActivePrograms,
            'calendarDays' => $calendarDays,
            'startOfMonth' => $startOfMonth,
        ];
    }

    public function previousMonth(): void
    {
        $date = Carbon::parse($this->currentMonth . '-01');
        $this->currentMonth = $date->subMonth()->format('Y-m');
        $this->currentYear = $date->format('Y');
    }

    public function nextMonth(): void
    {
        $date = Carbon::parse($this->currentMonth . '-01');
        $this->currentMonth = $date->addMonth()->format('Y-m');
        $this->currentYear = $date->format('Y');
    }

    public function goToToday(): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->currentYear = now()->format('Y');
    }

    public function updatedSelectedProgramId(): void
    {
        // This will automatically trigger a re-render when the selection changes
    }
}; ?>

<section class="w-full">
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/50 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col md:flex-row items-center md:items-center md:justify-between gap-4">
        <div class="flex-1 min-w-0 w-full md:w-auto text-center lg:text-left">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('Workout Calendar') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('View and log your scheduled workouts') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center justify-center md:justify-end gap-2 w-full md:w-auto">
            <flux:button href="{{ route('workouts.history') }}" variant="ghost" wire:navigate>
                {{ __('History') }}
            </flux:button>
            <flux:button href="{{ route('programs.index') }}" variant="ghost" wire:navigate>
                {{ __('Programs') }}
            </flux:button>
        </div>
    </div>

    @if($allActivePrograms->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                {{ __('You don\'t have any active programs yet.') }}
            </p>
            <flux:button href="{{ route('programs.index') }}" variant="primary" wire:navigate>
                {{ __('Browse Programs') }}
            </flux:button>
        </div>
    @elseif(!$selectedProgramId)
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                {{ __('Please select a program to view its calendar.') }}
            </p>
            <div class="max-w-md mx-auto">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                    {{ __('Select Program') }}
                </label>
                <select 
                    wire:model.live="selectedProgramId"
                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <option value="">{{ __('Select a program...') }}</option>
                    @foreach($allActivePrograms as $activeProgram)
                        <option value="{{ $activeProgram->program_id }}">{{ $activeProgram->program->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @else
        <!-- Program Selector -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                {{ __('Program') }}
            </label>
            <select 
                wire:model.live="selectedProgramId"
                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                @foreach($allActivePrograms as $activeProgram)
                    <option value="{{ (int) $activeProgram->program_id }}" {{ $selectedProgramId == $activeProgram->program_id ? 'selected' : '' }}>
                        {{ $activeProgram->program->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Calendar Navigation -->
        <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 w-full sm:w-auto order-2 sm:order-1">
                <flux:button wire:click="previousMonth" variant="ghost" size="sm" class="flex-1 sm:flex-none">
                    {{ __('← Previous') }}
                </flux:button>
                <flux:button wire:click="nextMonth" variant="ghost" size="sm" class="flex-1 sm:flex-none">
                    {{ __('Next →') }}
                </flux:button>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 order-1 sm:order-2">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 text-center sm:text-left">
                    {{ $startOfMonth->format('F Y') }}
                </h2>
                <flux:button wire:click="goToToday" variant="ghost" size="sm" class="w-full sm:w-auto">
                    {{ __('Today') }}
                </flux:button>
            </div>
        </div>

        <!-- Mobile List View -->
        <div class="md:hidden space-y-3">
            @php
                $currentMonthDays = collect($calendarDays)->filter(fn($day) => $day['isCurrentMonth']);
            @endphp
            @foreach($currentMonthDays as $day)
                @if($day['hasScheduledWorkout'] || ($day['isRestDay'] ?? false))
                    <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-4 {{ $day['isToday'] ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-300 dark:border-primary-700' : 'bg-white dark:bg-neutral-900' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="flex flex-col items-center">
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">
                                        {{ $day['date']->format('D') }}
                                    </span>
                                    <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $day['date']->format('j') }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    @if($day['isRestDay'] ?? false)
                                        <div class="flex items-center gap-2">
                                            <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                                            <span class="text-base font-medium text-zinc-600 dark:text-zinc-400 italic">
                                                {{ __('Rest Day') }}
                                            </span>
                                        </div>
                                    @elseif($day['hasScheduledWorkout'])
                                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $day['scheduledWorkout']['active_program']->program->name }}
                                        </h3>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Week {{ $day['scheduledWorkout']['week'] }}, Day {{ $day['scheduledWorkout']['day'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @if($day['hasLoggedWorkout'])
                                <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0" title="Workout logged"></span>
                            @elseif($day['hasScheduledWorkout'])
                                <span class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0" title="Scheduled workout"></span>
                            @endif
                        </div>
                        
                        @if($day['hasScheduledWorkout'] && $day['isCurrentMonth'])
                            <div class="mt-3">
                                @if($day['hasLoggedWorkout'])
                                    <flux:button 
                                        href="{{ route('workouts.log', ['activeProgram' => $day['scheduledWorkout']['active_program']->id, 'date' => $day['date']->format('Y-m-d')]) }}" 
                                        variant="ghost" 
                                        size="sm"
                                        class="w-full"
                                        wire:navigate
                                    >
                                        {{ __('View/Edit Workout') }}
                                    </flux:button>
                                @else
                                    <flux:button 
                                        href="{{ route('workouts.log', ['activeProgram' => $day['scheduledWorkout']['active_program']->id, 'date' => $day['date']->format('Y-m-d')]) }}" 
                                        variant="primary" 
                                        size="sm"
                                        class="w-full"
                                        wire:navigate
                                    >
                                        {{ __('Log Workout') }}
                                    </flux:button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
            
            @if($currentMonthDays->filter(fn($day) => $day['hasScheduledWorkout'] || ($day['isRestDay'] ?? false))->isEmpty())
                <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-8 text-center bg-white dark:bg-neutral-900">
                    <p class="text-zinc-600 dark:text-zinc-400">
                        {{ __('No workouts scheduled for this month.') }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Desktop Calendar Grid -->
        <div class="hidden md:block rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 bg-neutral-50 dark:bg-neutral-800">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="p-3 text-center text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ $day }}
                    </div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7">
                @foreach($calendarDays as $day)
                    <div 
                        class="min-h-[80px] border border-neutral-200 dark:border-neutral-700 p-2 {{ !$day['isCurrentMonth'] ? 'bg-neutral-50 dark:bg-neutral-900/50' : '' }} {{ $day['isToday'] ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium {{ $day['isCurrentMonth'] ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400' }}">
                                {{ $day['date']->format('j') }}
                            </span>
                            @if($day['isRestDay'] ?? false)
                                <span class="w-2 h-2 rounded-full bg-gray-400" title="Rest day"></span>
                            @elseif($day['hasLoggedWorkout'])
                                <span class="w-2 h-2 rounded-full bg-green-500" title="Workout logged"></span>
                            @elseif($day['hasScheduledWorkout'])
                                <span class="w-2 h-2 rounded-full bg-blue-500" title="Scheduled workout"></span>
                            @endif
                        </div>
                        
                        @if(($day['isRestDay'] ?? false) && $day['isCurrentMonth'])
                            <div class="mt-1">
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 italic">
                                    {{ __('Rest Day') }}
                                </span>
                            </div>
                        @elseif($day['hasScheduledWorkout'] && $day['isCurrentMonth'])
                            <div class="mt-1">
                                @if($day['hasLoggedWorkout'])
                                    <flux:button 
                                        href="{{ route('workouts.log', ['activeProgram' => $day['scheduledWorkout']['active_program']->id, 'date' => $day['date']->format('Y-m-d')]) }}" 
                                        variant="ghost" 
                                        size="sm"
                                        class="w-full text-xs"
                                        wire:navigate
                                    >
                                        {{ __('View/Edit') }}
                                    </flux:button>
                                @else
                                    <flux:button 
                                        href="{{ route('workouts.log', ['activeProgram' => $day['scheduledWorkout']['active_program']->id, 'date' => $day['date']->format('Y-m-d')]) }}" 
                                        variant="ghost" 
                                        size="sm"
                                        class="w-full text-xs"
                                        wire:navigate
                                    >
                                        {{ __('Log Workout') }}
                                    </flux:button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-4 flex flex-wrap items-center justify-center sm:justify-start gap-4 sm:gap-6 text-sm text-zinc-600 dark:text-zinc-400">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <span>{{ __('Scheduled') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span>{{ __('Logged') }}</span>
            </div>
        </div>
    @endif
</section>

