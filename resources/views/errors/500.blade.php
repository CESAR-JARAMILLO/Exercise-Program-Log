<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <title>500 - Server Error | {{ config('app.name', 'Program Log') }}</title>
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
                        <h1 class="text-8xl font-bold text-zinc-900 dark:text-white">500</h1>
                        <h2 class="text-2xl font-semibold text-zinc-700 dark:text-zinc-300">
                            {{ __('Server Error') }}
                        </h2>
                    </div>

                    <!-- Error Message -->
                    <p class="text-zinc-600 dark:text-zinc-400">
                        {{ __('Oops! Something went wrong on our end. We\'re working to fix the issue. Please try again later.') }}
                    </p>

                    <!-- Illustration -->
                    <div class="flex justify-center my-4">
                        <svg class="h-48 w-48 text-zinc-300 dark:text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center mt-6">
                        <flux:button href="{{ route('home') }}" variant="primary" wire:navigate>
                            {{ __('Go Home') }}
                        </flux:button>
                        <flux:button onclick="window.location.reload()" variant="ghost">
                            {{ __('Try Again') }}
                        </flux:button>
                    </div>

                    @if(app()->hasDebugModeEnabled())
                        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-left text-sm text-red-800 dark:border-red-800 dark:bg-red-900/50 dark:text-red-200">
                            <p class="font-medium mb-2">{{ __('Debug Information') }}</p>
                            @if(isset($exception))
                                <p class="font-mono text-xs break-all">{{ $exception->getMessage() }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>



