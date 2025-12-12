<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="bell" :href="route('notifications.index')" :current="request()->routeIs('notifications.*')" wire:navigate>
                        <span class="flex w-full items-center justify-between">
                            <span>{{ __('Notifications') }}</span>
                            @if(auth()->user()->unreadNotifications()->count() > 0)
                                <span class="ml-2 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                                    {{ auth()->user()->unreadNotifications()->count() > 9 ? '9+' : auth()->user()->unreadNotifications()->count() }}
                                </span>
                            @endif
                        </span>
                    </flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document-list" :href="route('programs.index')" :current="request()->routeIs('programs.*')" wire:navigate>{{ __('Programs') }}</flux:navlist.item>
                    <flux:navlist.item icon="chart-bar" :href="route('statistics.index')" :current="request()->routeIs('statistics.*')" wire:navigate>{{ __('Statistics') }}</flux:navlist.item>
                </flux:navlist.group>

                @if(auth()->user()->isTrainer())
                    <flux:navlist.group :heading="__('Trainer Tools')" class="grid">
                        <flux:navlist.item
                            icon="users"
                            :href="route('trainers.clients')"
                            :current="request()->routeIs('trainers.clients')"
                            wire:navigate
                        >
                            {{ __('Clients') }}
                        </flux:navlist.item>
                        <flux:navlist.item
                            icon="presentation-chart-line"
                            :href="route('trainers.analytics.index')"
                            :current="request()->routeIs('trainers.analytics.*')"
                            wire:navigate
                        >
                            {{ __('Analytics') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                @endif

                <flux:navlist.group :heading="__('Theme')" class="grid">
                    <button
                        type="button"
                        x-data="{
                            get appearance() { 
                                return $flux.appearance;
                            },
                            toggle() {
                                const current = this.appearance === 'system' 
                                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                                    : this.appearance;
                                $flux.appearance = current === 'dark' ? 'light' : 'dark';
                            },
                            get icon() {
                                const current = this.appearance === 'system'
                                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                                    : this.appearance;
                                return current === 'dark' ? 'sun' : 'moon';
                            },
                            get themeLabel() {
                                const current = this.appearance === 'system'
                                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                                    : this.appearance;
                                return current === 'dark' ? '{{ __('Dark') }}' : '{{ __('Light') }}';
                            },
                            get label() {
                                const current = this.appearance === 'system'
                                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                                    : this.appearance;
                                return current === 'dark' ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}';
                            }
                        }"
                        @click="toggle()"
                        class="flex h-8 w-full items-center gap-3 rounded-lg px-3 text-sm font-medium leading-none text-zinc-500 transition-colors hover:bg-zinc-800/5 hover:text-zinc-800 dark:text-white/80 dark:hover:bg-white/[7%] dark:hover:text-white"
                        :title="label"
                    >
                        <svg x-show="icon === 'sun'" x-cloak class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="icon === 'moon'" x-cloak class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <span x-text="themeLabel"></span>
                    </button>
                </flux:navlist.group>
            </flux:navlist>

            @if(!auth()->user()->isTrainer())
                <div class="mx-4 my-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                    <p class="font-medium">{{ __('Upgrade to Trainer') }}</p>
                    <p class="mt-1">{{ __('Share programs with clients and view their analytics.') }}</p>
                </div>
            @endif

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                    data-test="sidebar-menu-button"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        <flux:menu.item :href="route('subscriptions.plans')" icon="credit-card" wire:navigate>{{ __('Subscription Plans') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <!-- Theme Toggle -->
            <flux:tooltip content="{{ __('Toggle theme') }}" position="bottom">
                <button
                    type="button"
                    x-data="{
                        get appearance() { return $flux.appearance },
                        toggle() {
                            const current = this.appearance === 'system' 
                                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                                : this.appearance;
                            $flux.appearance = current === 'dark' ? 'light' : 'dark';
                        },
                        get icon() {
                            const current = this.appearance === 'system'
                                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                                : this.appearance;
                            return current === 'dark' ? 'sun' : 'moon';
                        },
                        get tooltipText() {
                            const current = this.appearance === 'system'
                                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                                : this.appearance;
                            return current === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
                        }
                    }"
                    @click="toggle()"
                    class="flex h-10 w-10 items-center justify-center rounded-lg text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 me-1.5"
                    :title="tooltipText"
                >
                    <svg x-show="icon === 'sun'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg x-show="icon === 'moon'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </flux:tooltip>

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        <flux:menu.item :href="route('subscriptions.plans')" icon="credit-card" wire:navigate>{{ __('Subscription Plans') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
