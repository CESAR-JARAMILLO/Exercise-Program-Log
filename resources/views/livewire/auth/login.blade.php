<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header 
            :title="__('Log in to your account')" 
            :description="__('Enter your email and password below to log in')" 
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ __('Authentication failed') }}
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
                :value="old('email')"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button 
                    variant="primary" 
                    type="submit" 
                    class="w-full" 
                    data-test="login-button"
                    x-bind:disabled="loading"
                >
                    <span x-show="!loading">{{ __('Log in') }}</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Logging in...') }}
                    </span>
                </flux:button>
            </div>
        </form>

        <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-2">
                @csrf
                <input type="hidden" name="email" value="cesarjaramillodev@gmail.com">
                <input type="hidden" name="password" value="Hikelife89!">
                <input type="hidden" name="remember" value="1">
                <flux:button 
                    variant="ghost" 
                    type="submit" 
                    class="w-full text-xs"
                >
                    {{ __('🧪 Test Login as Trainer') }}
                </flux:button>
            </form>
        </div>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
            </div>
        @endif

        <div class="pt-4 mt-4 border-t border-neutral-200 dark:border-neutral-700">
            <div class="flex flex-wrap justify-center gap-x-4 gap-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:link :href="route('legal.terms')" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">
                    {{ __('Terms of Service') }}
                </flux:link>
                <span class="text-zinc-400 dark:text-zinc-600">•</span>
                <flux:link :href="route('legal.privacy')" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">
                    {{ __('Privacy Policy') }}
                </flux:link>
            </div>
        </div>
    </div>
</x-layouts.auth>
