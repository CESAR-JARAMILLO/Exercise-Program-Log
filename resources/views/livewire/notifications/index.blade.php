<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();
        
        return [
            'notifications' => $user->notifications()->latest()->get(),
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

    public function deleteNotification($notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->delete();
        }
    }
}; ?>

<section class="w-full">
    <x-slot:header>
        <div>
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('Notifications') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('View and manage your notifications') }}
            </p>
        </div>
    </x-slot:header>

    <div class="space-y-4">
        @if($unreadCount > 0)
            <div class="flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    {{ __('You have :count unread notification(s)', ['count' => $unreadCount]) }}
                </p>
                <flux:button
                    wire:click="markAllAsRead"
                    variant="ghost"
                    size="sm"
                >
                    {{ __('Mark all as read') }}
                </flux:button>
            </div>
        @endif

        @if($notifications->isEmpty())
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('No notifications') }}
                </h3>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('You don\'t have any notifications yet.') }}
                </p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($notifications as $notification)
                    <div
                        class="rounded-lg border border-neutral-200 bg-white p-4 transition-colors dark:border-neutral-700 dark:bg-zinc-900 {{ $notification->read_at ? '' : 'border-blue-300 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20' }}"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $notification->data['message'] ?? 'Notification' }}
                                        </p>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                        @if(isset($notification->data['action_url']))
                                            <a
                                                href="{{ $notification->data['action_url'] }}"
                                                wire:navigate
                                                class="mt-2 inline-block text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
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
                            <div class="flex items-center gap-2">
                                @if(!$notification->read_at)
                                    <flux:button
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        variant="ghost"
                                        size="sm"
                                        class="text-xs"
                                    >
                                        {{ __('Mark as read') }}
                                    </flux:button>
                                @endif
                                <flux:button
                                    wire:click="deleteNotification('{{ $notification->id }}')"
                                    wire:confirm="{{ __('Are you sure you want to delete this notification?') }}"
                                    variant="ghost"
                                    size="sm"
                                    class="text-xs text-red-600 hover:text-red-700 dark:text-red-400"
                                >
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>




