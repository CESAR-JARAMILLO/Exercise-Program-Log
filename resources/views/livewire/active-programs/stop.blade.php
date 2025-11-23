<?php

use App\Models\ActiveProgram;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public int $activeProgramId;

    public function mount($activeProgram): void
    {
        // Handle route parameter
        if ($activeProgram instanceof ActiveProgram) {
            $this->activeProgramId = $activeProgram->id;
        } else {
            $this->activeProgramId = (int) $activeProgram;
        }
    }

    public function with(): array
    {
        $activeProgram = ActiveProgram::with('program')->findOrFail($this->activeProgramId);
        
        abort_unless($activeProgram->user_id === Auth::id(), 403);
        abort_unless($activeProgram->isActive(), 403);

        return [
            'activeProgram' => $activeProgram,
        ];
    }

    public function stop(): void
    {
        $activeProgram = ActiveProgram::findOrFail($this->activeProgramId);
        
        abort_unless($activeProgram->user_id === Auth::id(), 403);
        abort_unless($activeProgram->isActive(), 403);

        $activeProgram->stop();

        session()->flash('success', __('Program stopped successfully. Your progress has been saved.'));
        
        $this->redirect(route('dashboard'));
    }
}; ?>

<section class="w-full">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('Stop Program') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Are you sure you want to stop this program?') }}
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/50 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $activeProgram->program->name }}
                </h2>
                @if ($activeProgram->program->description)
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $activeProgram->program->description }}
                    </p>
                @endif
            </div>

            <div class="space-y-2 mb-6">
                <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                    <span class="font-medium">{{ __('Started:') }}</span>
                    <span class="ml-2">{{ $activeProgram->started_at->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                    <span class="font-medium">{{ __('Current Progress:') }}</span>
                    <span class="ml-2">{{ __('Week :week, Day :day', ['week' => $activeProgram->current_week, 'day' => $activeProgram->current_day]) }}</span>
                </div>
                <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                    <span class="font-medium">{{ __('Workouts Logged:') }}</span>
                    <span class="ml-2">{{ $activeProgram->workoutLogs()->count() }}</span>
                </div>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    <strong>{{ __('Note:') }}</strong> {{ __('Stopping this program will preserve all your workout logs and progress. You can view your history later, but the program will no longer appear in your active programs.') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <flux:button href="{{ route('dashboard') }}" variant="ghost" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button wire:click="stop" variant="danger">
                {{ __('Stop Program') }}
            </flux:button>
        </div>
    </div>
</section>

