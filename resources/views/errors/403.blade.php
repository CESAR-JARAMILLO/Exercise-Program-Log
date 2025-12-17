<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <title>403 - Forbidden | {{ config('app.name', 'Program Log') }}</title>
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6 text-center">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium">
                    <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Program Log') }}</span>
                </a>

                <div class="flex flex-col gap-4">
                    <!-- Error Code -->
                    <div class="flex flex-col items-center gap-2">
                        <h1 class="text-8xl font-bold text-zinc-900 dark:text-white">403</h1>
                        <h2 class="text-2xl font-semibold text-zinc-700 dark:text-zinc-300">
                            {{ __('Access Forbidden') }}
                        </h2>
                    </div>

                    <!-- Error Message -->
                    <p class="text-zinc-600 dark:text-zinc-400">
                        {{ __('You don\'t have permission to access this resource.') }}
                    </p>

                    @if(isset($message) && $message)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <!-- Illustration -->
                    <div class="flex justify-center my-4">
                        <svg class="h-48 w-48 text-zinc-300 dark:text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center mt-6">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="primary" wire:navigate>
                                {{ __('Go to Dashboard') }}
                            </flux:button>
                            @if(auth()->user()->isFree() || auth()->user()->isBasic())
                                <flux:button href="{{ route('subscriptions.plans') }}" variant="ghost" wire:navigate>
                                    {{ __('View Plans') }}
                                </flux:button>
                            @endif
                        @else
                            <flux:button href="{{ route('login') }}" variant="primary" wire:navigate>
                                {{ __('Go to Login') }}
                            </flux:button>
                        @endauth
                        <flux:button href="{{ route('home') }}" variant="ghost" wire:navigate>
                            {{ __('Go Home') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>

