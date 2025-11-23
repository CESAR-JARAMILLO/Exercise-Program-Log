<?php

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $programs = Program::where('user_id', Auth::id())
            ->with([
                'activePrograms' => function ($query) {
                    $query->where('user_id', Auth::id())->where('status', 'active');
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'programs' => $programs,
        ];
    }

    public function delete(Program $program): void
    {
        // Ensure user owns this program
        abort_unless($program->user_id === Auth::id(), 403);

        $program->delete();

        session()->flash('success', __('Program deleted successfully.'));
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
                {{ __('My Programs') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Manage your training programs') }}
            </p>
        </div>
        <flux:button href="{{ route('programs.create') }}" variant="primary" wire:navigate>
            {{ __('Create Program') }}
        </flux:button>
    </div>

    @if ($programs->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <p class="text-zinc-600 dark:text-zinc-400">
                {{ __('No programs yet. Create your first program to get started!') }}
            </p>
            <flux:button href="{{ route('programs.create') }}" variant="primary" class="mt-4" wire:navigate>
                {{ __('Create Program') }}
            </flux:button>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($programs as $program)
                <div
                    class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $program->name }}
                                </h3>
                                @if ($program->isTemplate())
                                    <span
                                        class="rounded-full bg-blue-100 dark:bg-blue-900/30 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300">
                                        {{ __('Template') }}
                                    </span>
                                @elseif($program->isActive())
                                    <span
                                        class="rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-300">
                                        {{ __('Active') }}
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
                        @if ($program->isActive() && $program->start_date)
                            <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium">{{ __('Started:') }}</span>
                                <span class="ml-2">{{ $program->start_date->format('M d, Y') }}</span>
                            </div>
                        @endif
                        @if ($program->activePrograms->isNotEmpty())
                            <div class="flex items-center text-sm text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium">{{ __('Active Instances:') }}</span>
                                <span class="ml-2">{{ $program->activePrograms->count() }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <flux:button href="{{ route('programs.show', $program) }}" variant="ghost" size="sm"
                            wire:navigate>
                            {{ __('View') }}
                        </flux:button>
                        @if ($program->isTemplate())
                            <flux:button href="{{ route('programs.start', $program) }}" variant="primary"
                                size="sm" wire:navigate>
                                {{ __('Start') }}
                            </flux:button>
                        @else
                            <flux:button href="{{ route('programs.edit', $program) }}" variant="ghost" size="sm"
                                wire:navigate>
                                {{ __('Edit') }}
                            </flux:button>
                        @endif
                        <flux:button wire:click="delete({{ $program->id }})"
                            wire:confirm="{{ __('Are you sure you want to delete this program?') }}" variant="ghost"
                            size="sm"
                            class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                            {{ __('Delete') }}
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
