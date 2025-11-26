<?php

use App\Models\TrainerClientRelationship;
use App\Notifications\TrainerRequestAccepted;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $client = Auth::user();

        $pendingRequests = $client->getPendingClientRequests();

        return [
            'pendingRequests' => $pendingRequests,
        ];
    }

    public function acceptRequest($relationshipId): void
    {
        $client = Auth::user();

        $relationship = TrainerClientRelationship::find($relationshipId);

        if ($relationship && $relationship->client_id === $client->id && $relationship->status === 'pending') {
            $relationship->accept();
            
            // Send notification to trainer
            $trainer = $relationship->trainer;
            $trainer->notify(new TrainerRequestAccepted($client));
            
            session()->flash('success', __('Trainer request accepted successfully.'));
        }
    }

    public function declineRequest($relationshipId): void
    {
        $client = Auth::user();

        $relationship = TrainerClientRelationship::find($relationshipId);

        if ($relationship && $relationship->client_id === $client->id && $relationship->status === 'pending') {
            $relationship->delete();
            session()->flash('success', __('Trainer request declined.'));
        }
    }
}; ?>

<section class="w-full">
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/50 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
            {{ __('Trainer Requests') }}
        </h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Accept or decline requests from trainers') }}
        </p>
    </div>

    @if ($pendingRequests->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-12 text-center">
            <p class="text-zinc-600 dark:text-zinc-400">
                {{ __('No pending trainer requests.') }}
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($pendingRequests as $request)
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $request->trainer->name }}
                            </h3>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $request->trainer->email }}
                            </p>
                            <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Request received') }}: {{ $request->invited_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="ml-4 flex gap-2">
                            <flux:button
                                wire:click="acceptRequest({{ $request->id }})"
                                variant="primary"
                                size="sm"
                            >
                                {{ __('Accept') }}
                            </flux:button>
                            <flux:button
                                wire:click="declineRequest({{ $request->id }})"
                                variant="ghost"
                                size="sm"
                            >
                                {{ __('Decline') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

