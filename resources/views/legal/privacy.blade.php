<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <title>Privacy Policy | {{ config('app.name', 'Program Log') }}</title>
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
                        Privacy Policy
                    </h1>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Last updated: {{ date('F j, Y') }}
                    </p>
                </div>

                <!-- Content -->
                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">1. Introduction</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            {{ config('app.name', 'Program Log') }} ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our Service.
                        </p>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            Please read this Privacy Policy carefully. If you do not agree with the terms of this Privacy Policy, please do not access the Service.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">2. Information We Collect</h2>
                        
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3 mt-6">2.1 Information You Provide</h3>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            We collect information that you provide directly to us, including:
                        </p>
                        <ul class="list-disc list-inside text-zinc-700 dark:text-zinc-300 space-y-2 ml-4">
                            <li>Account information (name, email address, password)</li>
                            <li>Profile information (timezone, appearance preferences)</li>
                            <li>Training programs, workouts, and exercise data</li>
                            <li>Communication data when you contact us for support</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3 mt-6">2.2 Automatically Collected Information</h3>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            When you use the Service, we automatically collect certain information, including:
                        </p>
                        <ul class="list-disc list-inside text-zinc-700 dark:text-zinc-300 space-y-2 ml-4">
                            <li>Device information (IP address, browser type, operating system)</li>
                            <li>Usage data (pages visited, features used, time spent)</li>
                            <li>Log data (access times, error logs)</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">3. How We Use Your Information</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            We use the information we collect to:
                        </p>
                        <ul class="list-disc list-inside text-zinc-700 dark:text-zinc-300 space-y-2 ml-4">
                            <li>Provide, maintain, and improve the Service</li>
                            <li>Process your transactions and manage your account</li>
                            <li>Send you technical notices, updates, and support messages</li>
                            <li>Respond to your comments, questions, and requests</li>
                            <li>Monitor and analyze trends, usage, and activities</li>
                            <li>Detect, prevent, and address technical issues and security threats</li>
                            <li>Comply with legal obligations</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">4. Information Sharing and Disclosure</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:
                        </p>
                        <ul class="list-disc list-inside text-zinc-700 dark:text-zinc-300 space-y-2 ml-4">
                            <li><strong>Service Providers:</strong> We may share information with third-party service providers who perform services on our behalf (e.g., hosting, analytics, payment processing)</li>
                            <li><strong>Legal Requirements:</strong> We may disclose information if required by law or in response to valid requests by public authorities</li>
                            <li><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets, your information may be transferred</li>
                            <li><strong>With Your Consent:</strong> We may share information with your explicit consent</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">5. Data Security</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.
                        </p>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">6. Data Retention</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. When you delete your account, we will delete or anonymize your personal information, except where we are required to retain it for legal or legitimate business purposes.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">7. Your Rights</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            Depending on your location, you may have certain rights regarding your personal information, including:
                        </p>
                        <ul class="list-disc list-inside text-zinc-700 dark:text-zinc-300 space-y-2 ml-4">
                            <li><strong>Access:</strong> Request access to your personal information</li>
                            <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
                            <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                            <li><strong>Portability:</strong> Request transfer of your data to another service</li>
                            <li><strong>Objection:</strong> Object to processing of your personal information</li>
                            <li><strong>Withdrawal:</strong> Withdraw consent where processing is based on consent</li>
                        </ul>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4 mt-4">
                            To exercise these rights, please contact us through the support channels provided in the application.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">8. Cookies and Tracking Technologies</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            We use cookies and similar tracking technologies to track activity on our Service and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our Service.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">9. Children's Privacy</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            Our Service is not intended for children under the age of 13. We do not knowingly collect personal information from children under 13. If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">10. Changes to This Privacy Policy</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date. You are advised to review this Privacy Policy periodically for any changes.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-4">11. Contact Us</h2>
                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed mb-4">
                            If you have any questions about this Privacy Policy, please contact us through the support channels provided in the application.
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
                    <flux:button href="{{ route('legal.terms') }}" variant="ghost" wire:navigate>
                        {{ __('Terms of Service') }}
                    </flux:button>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>

