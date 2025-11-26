<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Subscription Tier Display -->
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 bg-neutral-50 dark:bg-neutral-800/50">
                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-2">
                    {{ __('Subscription Tier') }}
                </p>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold
                        @if(auth()->user()->isFree()) bg-neutral-200 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300
                        @elseif(auth()->user()->isBasic()) bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                        @elseif(auth()->user()->isTrainer()) bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                        @elseif(auth()->user()->isProTrainer()) bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300
                        @endif">
                        {{ config("subscription.tiers." . auth()->user()->subscription_tier->value . ".name", 'Free') }}
                    </span>
                    @if(auth()->user()->getMaxPrograms() !== null)
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                            ({{ auth()->user()->getProgramCount() }}/{{ auth()->user()->getMaxPrograms() }} {{ __('programs') }})
                        </span>
                    @else
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                            ({{ auth()->user()->getProgramCount() }} {{ __('programs') }})
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
