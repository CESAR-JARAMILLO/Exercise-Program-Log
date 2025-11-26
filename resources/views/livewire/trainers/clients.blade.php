<?php

use App\Models\TrainerClientRelationship;
use App\Models\User;
use App\Notifications\TrainerRequestSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $email = '';

    public function with(): array
    {
        $trainer = Auth::user();

        $acceptedClients = $trainer->clients;

        $pendingRequests = $trainer->getPendingTrainerRequests();

        return [
            'acceptedClients' => $acceptedClients,
            'pendingRequests' => $pendingRequests,
        ];
    }

    public function addClient(): void
    {
        $this->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'No user found with this email address.',
        ]);

        $trainer = Auth::user();

        // Check if user is trying to add themselves
        if ($trainer->email === $this->email) {
            throw ValidationException::withMessages([
                'email' => 'You cannot add yourself as a client.',
            ]);
        }

        // Find the client user
        $client = User::where('email', $this->email)->first();

        // Check if relationship already exists
        $existingRelationship = TrainerClientRelationship::where('trainer_id', $trainer->id)
            ->where('client_id', $client->id)
            ->first();

        if ($existingRelationship) {
            throw ValidationException::withMessages([
                'email' => 'A relationship with this client already exists.',
            ]);
        }

        // Create pending relationship
        TrainerClientRelationship::create([
            'trainer_id' => $trainer->id,
            'client_id' => $client->id,
            'status' => 'pending',
            'invited_at' => now(),
            'invited_by' => 'trainer',
        ]);

        // Send notification to client
        $client->notify(new TrainerRequestSent($trainer));

        $this->email = '';
        session()->flash('success', __('Client request sent successfully.'));
    }

    public function removeClient($clientId): void
    {
        $trainer = Auth::user();

        $relationship = TrainerClientRelationship::where('trainer_id', $trainer->id)
            ->where('client_id', $clientId)
            ->first();

        if ($relationship) {
            $relationship->delete();
            session()->flash('success', __('Client removed successfully.'));
        }
    }

    public function cancelRequest($relationshipId): void
    {
        $trainer = Auth::user();

        $relationship = TrainerClientRelationship::find($relationshipId);

        if ($relationship && $relationship->trainer_id === $trainer->id && $relationship->status === 'pending') {
            $relationship->delete();
            session()->flash('success', __('Request cancelled successfully.'));
        }
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
            {{ __('My Clients') }}
        </h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Manage your client relationships') }}
        </p>
    </div>

    <!-- Add Client Form -->
    <div class="mb-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-6">
        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
            {{ __('Add Client by Email') }}
        </h2>
        <form wire:submit="addClient" class="space-y-4">
            <div>
                <flux:input
                    type="email"
                    wire:model="email"
                    label="{{ __('Email Address') }}"
                    placeholder="{{ __('Enter client email address') }}"
                    :error="$errors->first('email')"
                />
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Enter the email address of an existing user to send a connection request.') }}
                </p>
            </div>
            <flux:button type="submit" variant="primary">
                {{ __('Send Request') }}
            </flux:button>
        </form>
    </div>

    <!-- Pending Requests -->
    @if ($pendingRequests->isNotEmpty())
        <div class="mb-6 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-6">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Pending Requests') }}
            </h2>
            <div class="space-y-3">
                @foreach ($pendingRequests as $request)
                    <div class="flex items-center justify-between rounded-lg border border-amber-200 dark:border-amber-800 bg-white dark:bg-zinc-800 p-4">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $request->client->name }}</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $request->client->email }}</p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Request sent') }}: {{ $request->invited_at->diffForHumans() }}
                            </p>
                        </div>
                        <flux:button
                            wire:click="cancelRequest({{ $request->id }})"
                            variant="ghost"
                            size="sm"
                        >
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Accepted Clients -->
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
        <div class="border-b border-neutral-200 dark:border-neutral-700 p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Accepted Clients') }}
            </h2>
        </div>

        @if ($acceptedClients->isEmpty())
            <div class="p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">
                    {{ __('No clients yet. Add a client by email to get started.') }}
                </p>
            </div>
        @else
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach ($acceptedClients as $client)
                    <div class="flex items-center justify-between p-6">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $client->name }}</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $client->email }}</p>
                        </div>
                        <flux:button
                            wire:click="removeClient({{ $client->id }})"
                            wire:confirm="{{ __('Are you sure you want to remove this client?') }}"
                            variant="ghost"
                            size="sm"
                        >
                            {{ __('Remove') }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

