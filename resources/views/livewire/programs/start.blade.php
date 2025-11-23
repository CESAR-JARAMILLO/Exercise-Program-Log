<?php

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public int $programId;
    public ?string $start_date = null;

    public function mount($program): void
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
        
        // Ensure program is a template
        abort_unless($program->isTemplate(), 403, 'This program is already active or completed.');
        
        $this->programId = $program->id;
        $this->start_date = now()->format('Y-m-d');
    }

    public function with(): array
    {
        $program = Program::findOrFail($this->programId);
        abort_unless($program->user_id === Auth::id(), 403);
        
        return [
            'program' => $program,
        ];
    }

    public function start(): void
    {
        $validated = $this->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $program = Program::findOrFail($this->programId);
        abort_unless($program->user_id === Auth::id(), 403);
        abort_unless($program->isTemplate(), 403, 'This program is already active or completed.');

        // Start the program
        $activeProgram = $program->startForUser(Auth::id(), $validated['start_date']);

        session()->flash('success', __('Program started successfully! You can now log your workouts.'));
        $this->redirect(route('workouts.calendar'));
    }
}; ?>

<section class="w-full">
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/50 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/50 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
            {{ __('Start Program') }}
        </h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Choose a start date for :name', ['name' => $program->name]) }}
        </p>
    </div>

    <form wire:submit="start" class="space-y-6">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Program Information') }}
            </h2>

            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                    {{ __('Program Name') }}
                </p>
                <p class="text-zinc-900 dark:text-zinc-100">
                    {{ $program->name }}
                </p>
            </div>

            @if($program->description)
                <div class="space-y-2">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ __('Description') }}
                    </p>
                    <p class="text-zinc-900 dark:text-zinc-100">
                        {{ $program->description }}
                    </p>
                </div>
            @endif

            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                    {{ __('Duration') }}
                </p>
                <p class="text-zinc-900 dark:text-zinc-100">
                    {{ $program->length_weeks }} {{ __('weeks') }}
                </p>
            </div>

            <div class="pt-4 border-t border-neutral-200 dark:border-neutral-700">
                <flux:input 
                    wire:model="start_date" 
                    :label="__('Start Date')" 
                    type="date" 
                    required
                    min="{{ now()->format('Y-m-d') }}"
                />
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Select the date you want to start this program. Workouts will be scheduled based on this date.') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 border-t border-neutral-200 dark:border-neutral-700 pt-6">
            <flux:button type="submit" variant="primary" class="w-full md:w-auto">
                {{ __('Start Program') }}
            </flux:button>

            <flux:button href="{{ route('programs.show', $program) }}" variant="ghost" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</section>

