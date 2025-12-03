<?php

use App\Models\ActiveProgram;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $currentMonth;
    public string $currentYear;

    public function mount(): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->currentYear = now()->format('Y');
    }

    public function with(): array
    {
        $activePrograms = ActiveProgram::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with(['program', 'workoutLogs'])
            ->get();

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

    @if($activePrograms->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                {{ __('You don\'t have any active programs yet.') }}
            </p>
            <flux:button href="{{ route('programs.index') }}" variant="primary" wire:navigate>
                {{ __('Browse Programs') }}
            </flux:button>
        </div>
    @else
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

        <!-- Calendar -->
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
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

