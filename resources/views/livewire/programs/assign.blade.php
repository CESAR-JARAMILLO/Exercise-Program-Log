<?php

use App\Models\Program;
use App\Models\ProgramAssignment;
use App\Notifications\ProgramAssigned;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $selectedClients = [];
    public int $programId;

    public function mount($program = null): void
    {
        // Get program ID from route parameter
        $programId = $program ?? request()->route('program');
        
        // Handle both ID and model instance
        if ($programId instanceof Program) {
            $this->programId = $programId->id;
        } else {
            $this->programId = (int) $programId;
        }
    }

    public function with(): array
    {
        // Get program from stored ID
        $program = Program::findOrFail($this->programId);

        $trainer = Auth::user();

        // Ensure user is a trainer
        abort_unless($trainer->isTrainer(), 403, 'Only trainers can assign programs.');

        // Ensure user owns this program or is the trainer
        abort_unless($program->user_id === $trainer->id || $program->trainer_id === $trainer->id, 403, 'You can only assign programs you own or created.');

        // Ensure program is a template
        abort_unless($program->isTemplate(), 403, 'You can only assign template programs.');

        // Get trainer's accepted clients
        $acceptedClients = $trainer->clients;

        // Get already assigned clients
        $assignedClients = $program->assignedClients->pluck('id')->toArray();

        return [
            'program' => $program,
            'acceptedClients' => $acceptedClients,
            'assignedClients' => $assignedClients,
        ];
    }

    public function assign(): void
    {
        $this->validate([
            'selectedClients' => ['required', 'array', 'min:1'],
            'selectedClients.*' => ['exists:users,id'],
        ]);

        // Get program from stored ID
        $program = Program::findOrFail($this->programId);

        $trainer = Auth::user();

        // Ensure user is a trainer
        abort_unless($trainer->isTrainer(), 403);

        // Ensure user owns this program or is the trainer
        abort_unless($program->user_id === $trainer->id || $program->trainer_id === $trainer->id, 403);

        // Filter out already assigned clients
        $newClients = array_diff($this->selectedClients, $program->assignedClients->pluck('id')->toArray());

        if (empty($newClients)) {
            session()->flash('error', __('All selected clients already have this program assigned.'));
            return;
        }

        $assignedCount = 0;
        foreach ($newClients as $clientId) {
            // Check if assignment already exists
            if (!$program->isAssignedToClient($clientId)) {
                $program->assignToClient($clientId, $trainer->id);

                // Send notification to client
                $client = \App\Models\User::find($clientId);
                if ($client) {
                    $client->notify(new ProgramAssigned($program, $trainer));
                }

                $assignedCount++;
            }
        }

        if ($assignedCount > 0) {
            session()->flash('success', __('Program assigned to :count client(s) successfully.', ['count' => $assignedCount]));
            // Refresh program to ensure it's up to date
            $program->refresh();
            $this->redirect(route('programs.show', $program->id), navigate: true);
        } else {
            session()->flash('error', __('No new clients were assigned.'));
        }
    }

    public function unassign(int $clientId): void
    {
        $programId = request()->route('program');
        if ($programId instanceof Program) {
            $program = $programId;
        } else {
            $program = Program::findOrFail($programId);
        }

        $trainer = Auth::user();

        // Ensure user is a trainer
        abort_unless($trainer->isTrainer(), 403);

        // Ensure user owns this program or is the trainer
        abort_unless($program->user_id === $trainer->id || $program->trainer_id === $trainer->id, 403);

        if ($program->unassignFromClient($clientId)) {
            session()->flash('success', __('Program unassigned from client successfully.'));
            $this->dispatch('$refresh');
        } else {
            session()->flash('error', __('Failed to unassign program.'));
        }
    }
}; ?>

<section class="w-full">
    <x-slot:header>
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
            {{ __('Assign Program') }}
        </h2>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Assign ":name" to your clients', ['name' => $program->name]) }}
        </p>
    </x-slot:header>

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

    <div class="mb-6 rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 bg-neutral-50 dark:bg-neutral-800/50">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">{{ __('Program Details') }}</h3>
        <p class="text-sm text-zinc-600 dark:text-zinc-400"><strong>{{ __('Name:') }}</strong> {{ $program->name }}</p>
        @if($program->description)
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1"><strong>{{ __('Description:') }}</strong> {{ $program->description }}</p>
        @endif
    </div>

    @if ($acceptedClients->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 text-center">
            <p class="text-zinc-600 dark:text-zinc-400 mb-4">{{ __('You don\'t have any accepted clients yet.') }}</p>
            <flux:button href="{{ route('trainers.clients') }}" variant="primary" wire:navigate>
                {{ __('Manage Clients') }}
            </flux:button>
        </div>
    @else
        <form wire:submit="assign" class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Select Clients to Assign') }}</h3>
                <div class="space-y-2">
                    @foreach ($acceptedClients as $client)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 cursor-pointer">
                            <flux:checkbox wire:model="selectedClients" value="{{ $client->id }}" />
                            <div class="flex-1">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $client->name }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $client->email }}</p>
                            </div>
                            @if (in_array($client->id, $assignedClients))
                                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                                    {{ __('Already Assigned') }}
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
                @error('selectedClients')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Assign Program') }}
                </flux:button>
                <flux:button href="{{ route('programs.show', $program) }}" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>

        @if (!empty($assignedClients))
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Currently Assigned Clients') }}</h3>
                <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-neutral-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">{{ __('Name') }}</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">{{ __('Email') }}</th>
                                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900">
                            @foreach ($program->assignedClients as $client)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $client->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">{{ $client->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                        <flux:button wire:click="unassign({{ $client->id }})" wire:confirm="{{ __('Are you sure you want to unassign this program from this client?') }}" variant="ghost" size="sm" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                            {{ __('Unassign') }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</section>

