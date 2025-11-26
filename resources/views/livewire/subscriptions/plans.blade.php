<?php

use App\Enums\SubscriptionTier;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function upgrade(string $tierValue): void
    {
        $user = Auth::user();
        $tier = SubscriptionTier::from($tierValue);
        
        // Prevent downgrades (optional - remove if you want to allow downgrades)
        $currentTierValue = $user->getSubscriptionTier()->value;
        $tierHierarchy = ['free', 'basic', 'trainer', 'pro_trainer'];
        $currentIndex = array_search($currentTierValue, $tierHierarchy);
        $newIndex = array_search($tier->value, $tierHierarchy);
        
        if ($newIndex < $currentIndex) {
            session()->flash('error', __('You cannot downgrade your subscription tier.'));
            return;
        }
        
        // In production, this would trigger Stripe checkout
        // For now, just update the tier directly
        $user->upgradeTo($tier);
        
        session()->flash('status', __('Subscription upgraded to :tier successfully!', [
            'tier' => $tier->name()
        ]));
        
        $this->redirect(route('subscriptions.plans'), navigate: true);
    }
    
    public function with(): array
    {
        $user = Auth::user();
        $currentTier = $user->getSubscriptionTier();
        
        $tiers = collect(config('subscription.tiers'))
            ->map(function ($config, $key) use ($currentTier) {
                $tier = SubscriptionTier::from($key);
                return [
                    'key' => $key,
                    'tier' => $tier,
                    'name' => $config['name'],
                    'max_programs' => $config['max_programs'],
                    'features' => $config['features'],
                    'is_current' => $currentTier === $tier,
                    'is_upgrade' => $this->isUpgrade($currentTier, $tier),
                ];
            });
        
        return [
            'tiers' => $tiers,
            'currentTier' => $currentTier,
        ];
    }
    
    protected function isUpgrade(SubscriptionTier $current, SubscriptionTier $proposed): bool
    {
        $hierarchy = ['free', 'basic', 'trainer', 'pro_trainer'];
        $currentIndex = array_search($current->value, $hierarchy);
        $proposedIndex = array_search($proposed->value, $hierarchy);
        return $proposedIndex > $currentIndex;
    }
}; ?>

<section class="w-full">
    <x-slot:header>
        <div>
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('Subscription Plans') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Choose the plan that works best for you') }}
            </p>
        </div>
    </x-slot:header>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/50 dark:text-green-200">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/50 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        @foreach($tiers as $tierData)
            <div class="relative rounded-xl border-2 p-6 transition-all
                @if($tierData['is_current'])
                    border-blue-500 bg-blue-50 dark:bg-blue-900/20
                @else
                    border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900
                @endif">
                
                @if($tierData['is_current'])
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="rounded-full bg-blue-500 px-3 py-1 text-xs font-medium text-white">
                            {{ __('Current Plan') }}
                        </span>
                    </div>
                @endif

                <div class="text-center">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $tierData['name'] }}
                    </h3>
                    
                    <div class="mt-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('Programs') }}
                        </p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $tierData['max_programs'] == 50 ? __('Unlimited') : $tierData['max_programs'] }}
                        </p>
                    </div>

                    <ul class="mt-6 space-y-2 text-left text-sm">
                        @foreach($tierData['features'] as $feature)
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-zinc-700 dark:text-zinc-300">
                                    {{ ucfirst(str_replace('_', ' ', $feature)) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6">
                        @if($tierData['is_current'])
                            <flux:button variant="ghost" disabled class="w-full">
                                {{ __('Current Plan') }}
                            </flux:button>
                        @elseif($tierData['is_upgrade'])
                            <flux:button 
                                wire:click="upgrade('{{ $tierData['tier']->value }}')"
                                class="w-full"
                            >
                                {{ __('Upgrade') }}
                            </flux:button>
                        @else
                            <flux:button variant="ghost" disabled class="w-full">
                                {{ __('Downgrade') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
        <p class="font-medium">{{ __('Note') }}</p>
        <p class="mt-1">{{ __('This is a testing interface. In production, upgrades will be processed through Stripe payment integration.') }}</p>
    </div>
</section>

