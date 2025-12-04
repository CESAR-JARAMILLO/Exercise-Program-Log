<?php

use App\Models\ActiveProgram;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $activePrograms = ActiveProgram::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with(['program', 'workoutLogs'])
            ->orderBy('started_at', 'desc')
            ->get();

        // Get today's workout status for each active program
        $activeProgramsWithStatus = [];
        foreach ($activePrograms as $activeProgram) {
            $todayStatus = $activeProgram->getTodayWorkoutStatus();
            $activeProgramsWithStatus[] = [
                'activeProgram' => $activeProgram,
                'todayStatus' => $todayStatus,
            ];
        }

        // Get template programs for "Start Program" button
        $templatePrograms = Program::where('user_id', Auth::id())->where('status', 'template')->orderBy('created_at', 'desc')->get();

        return [
            'activePrograms' => $activePrograms,
            'activeProgramsWithStatus' => $activeProgramsWithStatus,
            'templatePrograms' => $templatePrograms,
        ];
    }
}; ?>

<section class="w-full px-2 sm:px-0">
    <div class="flex h-full w-full flex-1 flex-col gap-4 sm:gap-6">
        <div class="flex flex-col md:flex-row items-center md:items-center justify-between gap-4">
            <div class="flex-1 min-w-0 w-full md:w-auto text-center lg:text-left">
                <div
                    class="flex flex-col sm:flex-row items-center sm:items-center justify-center lg:justify-start gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        {{ __('Dashboard') }}
                    </h1>
                    <flux:button href="{{ route('statistics.index') }}" variant="ghost" size="sm" wire:navigate>
                        {{ __('View Statistics') }}
                    </flux:button>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-center lg:justify-start gap-2 sm:gap-3">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('Your active training programs') }}
                    </p>
                    <span class="hidden sm:inline text-zinc-400 dark:text-zinc-500">•</span>
                    <div class="flex items-center justify-center lg:justify-start gap-2 text-sm text-zinc-600 dark:text-zinc-400"
                        x-data="{
                            timezone: '{{ auth()->user()?->getTimezone() ?? 'UTC' }}',
                            updateTime() {
                                const now = new Date();
                                const formatter = new Intl.DateTimeFormat('en-US', {
                                    timeZone: this.timezone,
                                    month: 'short',
                                    day: 'numeric',
                                    year: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit',
                                    hour12: true
                                });
                                const parts = formatter.formatToParts(now);
                                const month = parts.find(p => p.type === 'month')?.value || '';
                                const day = parts.find(p => p.type === 'day')?.value || '';
                                const year = parts.find(p => p.type === 'year')?.value || '';
                                const hour = parts.find(p => p.type === 'hour')?.value || '';
                                const minute = parts.find(p => p.type === 'minute')?.value || '';
                                const period = parts.find(p => p.type === 'dayPeriod')?.value || '';
                        
                                this.$el.querySelector('[data-date]').textContent = `${month} ${day}, ${year}`;
                                this.$el.querySelector('[data-time]').textContent = `${hour}:${minute} ${period}`;
                            },
                            init() {
                                this.updateTime();
                                setInterval(() => this.updateTime(), 1000);
                            }
                        }">
                        <span class="font-medium"
                            data-date>{{ now()->setTimezone(auth()->user()?->getTimezone() ?? 'UTC')->format('M d, Y') }}</span>
                        <span
                            data-time>{{ now()->setTimezone(auth()->user()?->getTimezone() ?? 'UTC')->format('g:i A') }}</span>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-auto">
                <flux:button href="{{ route('programs.create') }}" variant="primary" wire:navigate
                    class="w-full md:w-auto">
                    {{ __('Create Program') }}
                </flux:button>
            </div>
        </div>
        @if ($activePrograms->isEmpty())
            <div
                class="flex flex-1 items-center justify-center rounded-xl border border-neutral-200 dark:border-neutral-700 p-12">
                <div class="text-center">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">
                        {{ __('No Active Programs') }}
                    </h2>
                    <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                        {{ __('Get started by creating a program or starting an existing template.') }}
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <flux:button href="{{ route('programs.index') }}" variant="primary" wire:navigate
                            class="w-full sm:w-auto">
                            {{ __('Start a Program') }}
                        </flux:button>
                        <flux:button href="{{ route('programs.create') }}" variant="primary" wire:navigate
                            class="w-full sm:w-auto">
                            {{ __('Create Program') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @else
            <div class="grid gap-3 sm:gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($activeProgramsWithStatus as $item)
                    @php
                        $activeProgram = $item['activeProgram'];
                        $todayStatus = $item['todayStatus'];
                        $program = $activeProgram->program;
                    @endphp
                    <div
                        class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-3 sm:p-4 md:p-6 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors w-full max-w-full sm:max-w-sm md:max-w-none mx-auto md:mx-0">
                        <div class="mb-4">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3
                                    class="text-base sm:text-lg font-semibold text-zinc-900 dark:text-zinc-100 flex-1 min-w-0">
                                    {{ $program->name }}
                                </h3>
                                <span
                                    class="rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-300 flex-shrink-0">
                                    {{ __('Active') }}
                                </span>
                            </div>
                            @if ($program->description)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                                    {{ $program->description }}
                                </p>
                            @endif
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium">{{ __('Started:') }}</span>
                                <span class="ml-2">{{ $activeProgram->started_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium">{{ __('Week:') }}</span>
                                <span class="ml-2">{{ $activeProgram->current_week }} /
                                    {{ $program->length_weeks }}</span>
                            </div>
                            @if ($todayStatus)
                                <div class="flex items-center text-sm">
                                    @if ($todayStatus['isLogged'])
                                        <span class="text-green-600 dark:text-green-400">
                                            ✓ {{ __('Workout logged today') }}
                                        </span>
                                    @else
                                        <span class="text-blue-600 dark:text-blue-400">
                                            {{ __('Workout scheduled today') }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div
                            class="flex flex-wrap items-center gap-2 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                            <flux:button href="{{ route('programs.show', $program) }}" variant="ghost" size="sm"
                                wire:navigate class="flex-1 sm:flex-none min-w-0">
                                {{ __('View') }}
                            </flux:button>
                            @if ($todayStatus && !$todayStatus['isLogged'])
                                <flux:button
                                    href="{{ route('workouts.log', ['activeProgram' => $activeProgram->id,'date' => now()->setTimezone(auth()->user()?->getTimezone() ?? 'UTC')->format('Y-m-d')]) }}"
                                    variant="primary" size="sm" wire:navigate class="flex-1 sm:flex-none min-w-0">
                                    {{ __('Log Workout') }}
                                </flux:button>
                            @else
                                <flux:button href="{{ route('workouts.calendar') }}" variant="primary" size="sm"
                                    wire:navigate class="flex-1 sm:flex-none min-w-0">
                                    {{ __('Calendar') }}
                                </flux:button>
                            @endif
                            <flux:button href="{{ route('active-programs.stop', $activeProgram) }}" variant="ghost"
                                size="sm" wire:navigate
                                class="flex-1 sm:flex-none min-w-0 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                {{ __('Stop') }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
