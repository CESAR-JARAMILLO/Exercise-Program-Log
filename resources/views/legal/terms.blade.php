<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <title>Terms of Service | {{ config('app.name', 'Program Log') }}</title>
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <!-- Navigation -->
        <nav class="border-b border-neutral-200 dark:border-neutral-800 bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-400 dark:to-blue-600 bg-clip-text text-transparent">
                            {{ config('app.name', 'Program Log') }}
                        </a>
                    </div>
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                Log in
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="bg-background flex min-h-svh flex-col items-center justify-center p-6 md:p-10">
            <div class="flex w-full max-w-4xl flex-col gap-8">
                <!-- Header -->
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-zinc-900 dark:text-white mb-2">
                        Terms of Service
                    </h1>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Last updated: {{ date('F j, Y') }}
                    </p>
                </div>

                <!-- Content -->
                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">1. Acceptance of Terms</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            By accessing and using {{ config('app.name', 'Program Log') }} ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">2. Use License</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            Permission is granted to temporarily use {{ config('app.name', 'Program Log') }} for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:
                        </p>
                        <ul class="list-disc list-inside text-zinc-700 dark:text-zinc-300 space-y-2 ml-4">
                            <li>Modify or copy the materials</li>
                            <li>Use the materials for any commercial purpose or for any public display</li>
                            <li>Attempt to reverse engineer any software contained in the Service</li>
                            <li>Remove any copyright or other proprietary notations from the materials</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">3. User Accounts</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            When you create an account with us, you must provide information that is accurate, complete, and current at all times. You are responsible for safeguarding the password and for all activities that occur under your account.
                        </p>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            You agree not to disclose your password to any third party. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">4. Subscription Plans</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            {{ config('app.name', 'Program Log') }} offers various subscription plans. By subscribing to a paid plan, you agree to pay the fees associated with that plan. Subscriptions are billed on a recurring basis and will automatically renew unless cancelled.
                        </p>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            You may cancel your subscription at any time. Cancellation will take effect at the end of the current billing period.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">5. User Content</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            You retain ownership of any content you submit, post, or display on or through the Service ("User Content"). By submitting User Content, you grant us a worldwide, non-exclusive, royalty-free license to use, reproduce, modify, and distribute your User Content solely for the purpose of providing and improving the Service.
                        </p>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            You are solely responsible for your User Content and represent and warrant that you have all rights necessary to grant us the license described above.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">6. Prohibited Uses</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            You agree not to use the Service:
                        </p>
                        <ul class="list-disc list-inside text-zinc-700 dark:text-zinc-300 space-y-2 ml-4">
                            <li>In any way that violates any applicable law or regulation</li>
                            <li>To transmit any malicious code or viruses</li>
                            <li>To impersonate or attempt to impersonate another user or person</li>
                            <li>To engage in any automated use of the system that interferes with the Service</li>
                            <li>To collect or store personal data about other users without their express permission</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">7. Disclaimer</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            The materials on {{ config('app.name', 'Program Log') }} are provided on an 'as is' basis. {{ config('app.name', 'Program Log') }} makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.
                        </p>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            {{ config('app.name', 'Program Log') }} does not warrant or make any representations concerning the accuracy, likely results, or reliability of the use of the materials on its website or otherwise relating to such materials or on any sites linked to this site.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">8. Limitations</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            In no event shall {{ config('app.name', 'Program Log') }} or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on {{ config('app.name', 'Program Log') }}, even if {{ config('app.name', 'Program Log') }} or an authorized representative has been notified orally or in writing of the possibility of such damage.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">9. Revisions</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            {{ config('app.name', 'Program Log') }} may revise these terms of service at any time without notice. By using this website you are agreeing to be bound by the then current version of these terms of service.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">10. Contact Information</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            If you have any questions about these Terms of Service, please contact us through the support channels provided in the application.
                        </p>
                    </section>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center mt-8 pt-8 border-t border-neutral-200 dark:border-neutral-800">
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
                    <flux:button href="{{ route('legal.privacy') }}" variant="ghost" wire:navigate>
                        {{ __('Privacy Policy') }}
                    </flux:button>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>

