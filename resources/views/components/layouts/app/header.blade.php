<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a href="{{ route('dashboard') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                <flux:navbar.item icon="clipboard-document-list" :href="route('programs.index')" :current="request()->routeIs('programs.*')" wire:navigate>
                    {{ __('Programs') }}
                </flux:navbar.item>
                <flux:navbar.item icon="chart-bar" :href="route('statistics.index')" :current="request()->routeIs('statistics.*')" wire:navigate>
                    {{ __('Statistics') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            @auth
                <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400 me-4 max-lg:hidden" 
                     x-data="{ 
                         timezone: '{{ auth()->user()->getTimezone() }}',
                         updateTime() {
                             const now = new Date();
                             const formatter = new Intl.DateTimeFormat('en-US', {
                                 timeZone: this.timezone,
                                 month: 'short',
                                 day: 'numeric',
                                 year: 'numeric',
                                 hour: 'numeric',
                                 minute: '2-digit',
                                 hour12: true
                             });
                             const parts = formatter.formatToParts(now);
                             const month = parts.find(p => p.type === 'month')?.value || '';
                             const day = parts.find(p => p.type === 'day')?.value || '';
                             const year = parts.find(p => p.type === 'year')?.value || '';
                             const hour = parts.find(p => p.type === 'hour')?.value || '';
                             const minute = parts.find(p => p.type === 'minute')?.value || '';
                             const period = parts.find(p => p.type === 'dayPeriod')?.value || '';
                             
                             this.$el.querySelector('[data-date]').textContent = `${month} ${day}, ${year}`;
                             this.$el.querySelector('[data-time]').textContent = `${hour}:${minute} ${period}`;
                         },
                         init() {
                             this.updateTime();
                             setInterval(() => this.updateTime(), 1000);
                         }
                     }">
                    <span class="font-medium" data-date>{{ now()->setTimezone(auth()->user()->getTimezone())->format('M d, Y') }}</span>
                    <span class="text-zinc-500 dark:text-zinc-400">•</span>
                    <span data-time>{{ now()->setTimezone(auth()->user()->getTimezone())->format('g:i A') }}</span>
                </div>
            @endauth

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
                <flux:tooltip :content="__('Repository')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="folder-git-2"
                        href="https://github.com/laravel/livewire-starter-kit"
                        target="_blank"
                        :label="__('Repository')"
                    />
                </flux:tooltip>
                <flux:tooltip :content="__('Documentation')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="book-open-text"
                        href="https://laravel.com/docs/starter-kits#livewire"
                        target="_blank"
                        label="Documentation"
                    />
                </flux:tooltip>
            </flux:navbar>

            <!-- Desktop User Menu -->
            <flux:dropdown position="top" align="end">
                <flux:profile
                    class="cursor-pointer"
                    :initials="auth()->user()->initials()"
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

        <!-- Mobile Menu -->
        <flux:sidebar stashable sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')">
                    <flux:navlist.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document-list" :href="route('programs.index')" :current="request()->routeIs('programs.*')" wire:navigate>
                    {{ __('Programs') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="chart-bar" :href="route('statistics.index')" :current="request()->routeIs('statistics.*')" wire:navigate>
                    {{ __('Statistics') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
