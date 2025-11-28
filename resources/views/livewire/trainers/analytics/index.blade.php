<?php

use App\Services\TrainerAnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $trainer = Auth::user();
        abort_unless($trainer && $trainer->canViewClientAnalytics(), 403);

        $analyticsService = app(TrainerAnalyticsService::class);

        return [
            'trainer' => $trainer,
            'aggregateStats' => $analyticsService->getAggregateStats($trainer),
            'clientSummaries' => $analyticsService->getClientSummaries($trainer),
        ];
    }
}; ?>

<section class="w-full">
    <x-slot:header>
        <div>
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('Client Analytics') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Overview of your clients\' performance metrics.') }}
            </p>
        </div>
    </x-slot:header>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Total Clients') }}</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $aggregateStats['total_clients'] }}
            </p>
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Active Programs') }}</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $aggregateStats['total_active_programs'] }}
            </p>
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Avg. Completion Rate') }}</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $aggregateStats['average_completion_rate'] }}%
            </p>
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Workouts Last Week') }}</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $aggregateStats['total_workouts_last_week'] }}
            </p>
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
        <div class="flex items-center justify-between border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
            <div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('Clients') }}</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Quick stats for each client') }}
                </p>
            </div>
            <flux:button href="{{ route('trainers.clients') }}" variant="ghost" wire:navigate>
                {{ __('Manage Clients') }}
            </flux:button>
        </div>

        @if ($clientSummaries->isEmpty())
            <div class="p-12 text-center">
                <p class="text-neutral-600 dark:text-neutral-400">
                    {{ __('You have no accepted clients yet. Add a client to view analytics.') }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                {{ __('Client') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                {{ __('Workouts (This Week)') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                {{ __('Active Programs') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                {{ __('Completion Rate') }}
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900">
                        @foreach ($clientSummaries as $summary)
                            <tr>
                                <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                    <div class="font-medium">{{ $summary['client']->name }}</div>
                                    <div class="text-neutral-500 dark:text-neutral-400 text-xs">{{ $summary['client']->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-300">
                                    {{ $summary['workouts_this_week'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-300">
                                    {{ $summary['active_programs'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-300">
                                    {{ $summary['completion_rate'] }}%
                                </td>
                                <td class="px-6 py-4 text-sm text-right">
                                    <flux:button
                                        href="{{ route('trainers.analytics.client', $summary['client']) }}"
                                        variant="ghost"
                                        size="sm"
                                        wire:navigate
                                    >
                                        {{ __('View Analytics') }}
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>




