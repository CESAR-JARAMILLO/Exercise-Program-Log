<?php

use App\Models\ActiveProgram;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public int $activeProgramId;
    public string $startDate;

    public function mount($activeProgram): void
    {
        // Handle route parameter
        if ($activeProgram instanceof ActiveProgram) {
            $this->activeProgramId = $activeProgram->id;
        } else {
            $this->activeProgramId = (int) $activeProgram;
        }

        // Default to today
        $this->startDate = now()->format('Y-m-d');
    }

    public function with(): array
    {
        $activeProgram = ActiveProgram::with('program')->findOrFail($this->activeProgramId);
        
        abort_unless($activeProgram->user_id === Auth::id(), 403);
        abort_unless($activeProgram->isStopped(), 403);

        return [
            'activeProgram' => $activeProgram,
        ];
    }

    public function restart(): void
    {
        $activeProgram = ActiveProgram::findOrFail($this->activeProgramId);
        
        abort_unless($activeProgram->user_id === Auth::id(), 403);
        abort_unless($activeProgram->isStopped(), 403);

        $validated = $this->validate([
            'startDate' => ['required', 'date'],
        ]);

        $activeProgram->restart($validated['startDate']);

        session()->flash('success', __('Program restarted successfully!'));
        
        $this->redirect(route('dashboard'));
    }
}; ?>

<section class="w-full">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('Restart Program') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Restart this program with a new start date. Your previous progress will be preserved.') }}
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
                    <span class="font-medium">{{ __('Previously Started:') }}</span>
                    <span class="ml-2">{{ $activeProgram->started_at->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                    <span class="font-medium">{{ __('Stopped:') }}</span>
                    <span class="ml-2">{{ $activeProgram->stopped_at ? $activeProgram->stopped_at->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                    <span class="font-medium">{{ __('Previous Progress:') }}</span>
                    <span class="ml-2">{{ __('Week :week, Day :day', ['week' => $activeProgram->current_week, 'day' => $activeProgram->current_day]) }}</span>
                </div>
                <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                    <span class="font-medium">{{ __('Workouts Logged:') }}</span>
                    <span class="ml-2">{{ $activeProgram->workoutLogs()->count() }}</span>
                </div>
            </div>

            <form wire:submit="restart" class="space-y-4">
                <flux:input 
                    wire:model="startDate" 
                    type="date" 
                    :label="__('New Start Date')" 
                    required
                    :min="now()->format('Y-m-d')"
                />

                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>{{ __('Note:') }}</strong> {{ __('Restarting will create a new active instance of this program. Your previous workout logs and progress will remain saved.') }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <flux:button href="{{ route('dashboard') }}" variant="ghost" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ __('Restart Program') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</section>

