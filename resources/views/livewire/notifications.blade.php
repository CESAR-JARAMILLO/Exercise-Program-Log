<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [
                'notifications' => collect(),
                'unreadCount' => 0,
            ];
        }
        
        return [
            'notifications' => $user->notifications()->latest()->take(10)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ];
    }

    public function markAsRead($notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }
}; ?>

@auth
<div class="relative" x-data="{ open: false }" wire:ignore.self>
    <button
        type="button"
        @click="open = !open"
        class="group relative flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
    >
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span class="flex-1 text-left">{{ __('Notifications') }}</span>
        
        @if($unreadCount > 0)
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="fixed left-64 top-16 z-50 w-80 rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-800 lg:absolute lg:left-full lg:top-0 lg:ml-2"
        style="display: none;"
    >
        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Notifications') }}
                </h3>
                @if($unreadCount > 0)
                    <flux:button
                        wire:click="markAllAsRead"
                        variant="ghost"
                        size="sm"
                        class="text-xs"
                    >
                        {{ __('Mark all as read') }}
                    </flux:button>
                @endif
            </div>
        </div>

        <div class="max-h-96 overflow-y-auto">
            @if($notifications->isEmpty())
                <div class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('No notifications') }}
                </div>
            @else
                @foreach($notifications as $notification)
                    <div
                        wire:click="markAsRead('{{ $notification->id }}')"
                        class="border-b border-zinc-100 p-4 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-700/50 cursor-pointer transition-colors {{ $notification->read_at ? '' : 'bg-blue-50 dark:bg-blue-900/20' }}"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                                @if(isset($notification->data['action_url']))
                                    <a
                                        href="{{ $notification->data['action_url'] }}"
                                        wire:navigate
                                        class="mt-2 inline-block text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                        @click.stop
                                    >
                                        {{ $notification->data['action_text'] ?? 'View' }} →
                                    </a>
                                @endif
                            </div>
                            @if(!$notification->read_at)
                                <div class="h-2 w-2 rounded-full bg-blue-500 flex-shrink-0 mt-1"></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endauth

