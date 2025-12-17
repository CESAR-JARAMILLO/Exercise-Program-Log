<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <title>404 - Page Not Found | {{ config('app.name', 'Program Log') }}</title>
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
                        <h1 class="text-8xl font-bold text-zinc-900 dark:text-white">404</h1>
                        <h2 class="text-2xl font-semibold text-zinc-700 dark:text-zinc-300">
                            {{ __('Page Not Found') }}
                        </h2>
                    </div>

                    <!-- Error Message -->
                    <p class="text-zinc-600 dark:text-zinc-400">
                        {{ __('Sorry, the page you are looking for could not be found.') }}
                    </p>

                    <!-- Illustration -->
                    <div class="flex justify-center my-4">
                        <svg class="h-48 w-48 text-zinc-300 dark:text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center mt-6">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="primary" wire:navigate>
                                {{ __('Go to Dashboard') }}
                            </flux:button>
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

