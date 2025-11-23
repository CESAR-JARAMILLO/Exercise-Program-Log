<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $timezone = 'UTC';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->timezone = Auth::user()->timezone ?? 'UTC';
    }

    /**
     * Get all available timezones
     */
    public function getTimezones(): array
    {
        return [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'America/New_York' => 'Eastern Time (ET)',
            'America/Chicago' => 'Central Time (CT)',
            'America/Denver' => 'Mountain Time (MT)',
            'America/Los_Angeles' => 'Pacific Time (PT)',
            'America/Phoenix' => 'Arizona (MST)',
            'America/Anchorage' => 'Alaska Time (AKT)',
            'Pacific/Honolulu' => 'Hawaii Time (HST)',
            'America/Toronto' => 'Toronto (ET)',
            'America/Vancouver' => 'Vancouver (PT)',
            'Europe/London' => 'London (GMT)',
            'Europe/Paris' => 'Paris (CET)',
            'Europe/Berlin' => 'Berlin (CET)',
            'Europe/Rome' => 'Rome (CET)',
            'Europe/Madrid' => 'Madrid (CET)',
            'Europe/Amsterdam' => 'Amsterdam (CET)',
            'Europe/Stockholm' => 'Stockholm (CET)',
            'Europe/Zurich' => 'Zurich (CET)',
            'Europe/Vienna' => 'Vienna (CET)',
            'Europe/Brussels' => 'Brussels (CET)',
            'Europe/Dublin' => 'Dublin (GMT)',
            'Europe/Lisbon' => 'Lisbon (WET)',
            'Europe/Athens' => 'Athens (EET)',
            'Europe/Helsinki' => 'Helsinki (EET)',
            'Europe/Warsaw' => 'Warsaw (CET)',
            'Europe/Prague' => 'Prague (CET)',
            'Europe/Budapest' => 'Budapest (CET)',
            'Europe/Bucharest' => 'Bucharest (EET)',
            'Europe/Copenhagen' => 'Copenhagen (CET)',
            'Europe/Oslo' => 'Oslo (CET)',
            'Asia/Tokyo' => 'Tokyo (JST)',
            'Asia/Shanghai' => 'Shanghai (CST)',
            'Asia/Hong_Kong' => 'Hong Kong (HKT)',
            'Asia/Singapore' => 'Singapore (SGT)',
            'Asia/Seoul' => 'Seoul (KST)',
            'Asia/Dubai' => 'Dubai (GST)',
            'Asia/Kolkata' => 'Mumbai (IST)',
            'Asia/Bangkok' => 'Bangkok (ICT)',
            'Asia/Jakarta' => 'Jakarta (WIB)',
            'Asia/Manila' => 'Manila (PHT)',
            'Australia/Sydney' => 'Sydney (AEST)',
            'Australia/Melbourne' => 'Melbourne (AEST)',
            'Australia/Brisbane' => 'Brisbane (AEST)',
            'Australia/Perth' => 'Perth (AWST)',
            'Australia/Adelaide' => 'Adelaide (ACST)',
            'Pacific/Auckland' => 'Auckland (NZST)',
            'America/Mexico_City' => 'Mexico City (CST)',
            'America/Sao_Paulo' => 'São Paulo (BRT)',
            'America/Buenos_Aires' => 'Buenos Aires (ART)',
            'America/Santiago' => 'Santiago (CLT)',
            'America/Lima' => 'Lima (PET)',
            'America/Bogota' => 'Bogotá (COT)',
            'Africa/Johannesburg' => 'Johannesburg (SAST)',
            'Africa/Cairo' => 'Cairo (EET)',
            'Africa/Lagos' => 'Lagos (WAT)',
            'Africa/Nairobi' => 'Nairobi (EAT)',
        ];
    }

    /**
     * Update the timezone for the currently authenticated user.
     */
    public function updateTimezone(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'timezone' => ['required', 'string', 'max:255'],
        ]);

        $user->update($validated);

        $this->dispatch('timezone-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Timezone')" :subheading="__('Set your timezone to ensure dates and times are displayed correctly')">
        <form wire:submit="updateTimezone" class="my-6 w-full space-y-6">
            <flux:select wire:model="timezone" :label="__('Timezone')" required>
                @foreach($this->getTimezones() as $tz => $label)
                    <option value="{{ $tz }}">{{ $label }}</option>
                @endforeach
            </flux:select>

            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>{{ __('Note:') }}</strong> {{ __('Your timezone affects how dates and times are displayed throughout the application.') }}
                </p>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-timezone-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="timezone-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>

