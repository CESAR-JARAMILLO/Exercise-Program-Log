@props([
    'feature' => null,
    'requiredTier' => null,
    'message' => null,
    'showLink' => true,
])

@php
    $user = auth()->user();
    
    // Determine required tier name
    if ($requiredTier) {
        $tierName = config("subscription.tiers.{$requiredTier}.name", ucfirst($requiredTier));
    } elseif ($feature) {
        $allowedTiers = config("subscription.features.{$feature}", []);
        $tierNames = array_map(function($tier) {
            return config("subscription.tiers.{$tier}.name", ucfirst($tier));
        }, $allowedTiers);
        $tierName = implode(' or ', $tierNames);
    } else {
        $tierName = 'upgraded';
    }
    
    // Default message
    if (!$message) {
        if ($feature) {
            $message = __('This feature requires a :tier subscription.', ['tier' => $tierName]);
        } else {
            $message = __('Upgrade to :tier to access this feature.', ['tier' => $tierName]);
        }
    }
@endphp

<div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
    <div class="flex items-start gap-3">
        <svg class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
            <p class="font-medium">{{ __('Upgrade Required') }}</p>
            <p class="mt-1">{{ $message }}</p>
            @if($showLink)
                <div class="mt-3">
                    <flux:button 
                        href="{{ route('subscriptions.plans') }}" 
                        variant="ghost" 
                        size="sm"
                        wire:navigate
                    >
                        {{ __('View Plans') }}
                    </flux:button>
                </div>
            @endif
            @if(isset($slot) && !empty(trim($slot)))
                <div class="mt-2">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>

