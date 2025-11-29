<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Program Log') }} - Track Your Fitness Journey</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 dark:bg-slate-900 antialiased">
        <!-- Navigation -->
        <nav class="border-b border-blue-100 dark:border-blue-900 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-400 dark:to-blue-600 bg-clip-text text-transparent">
                            {{ config('app.name', 'Program Log') }}
                        </a>
                    </div>
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg shadow-orange-500/50">
                                    Get started
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-blue-100 to-slate-50 dark:from-slate-900 dark:via-blue-900 dark:to-slate-900">
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-32 relative">
                <div class="text-center">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-sm font-medium mb-6">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 animate-pulse"></span>
                        Trusted by fitness professionals worldwide
                    </div>
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold text-slate-900 dark:text-white mb-6 leading-tight">
                        Track Your Fitness Journey
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-400 dark:to-blue-600 mt-2">
                            One Workout at a Time
                        </span>
                    </h1>
                    <p class="text-xl sm:text-2xl text-slate-600 dark:text-slate-400 max-w-3xl mx-auto mb-10 leading-relaxed">
                        Create structured training programs, log your workouts, and monitor your progress. Perfect for trainers and athletes alike.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl hover:from-orange-600 hover:to-orange-700 transition-all shadow-xl shadow-orange-500/50 hover:shadow-2xl hover:shadow-orange-500/60 transform hover:-translate-y-0.5">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl hover:from-orange-600 hover:to-orange-700 transition-all shadow-xl shadow-orange-500/50 hover:shadow-2xl hover:shadow-orange-500/60 transform hover:-translate-y-0.5">
                                Get Started Free
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border-2 border-blue-200 dark:border-blue-800 rounded-xl hover:border-blue-400 dark:hover:border-blue-600 transition-all">
                                Sign In
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-24 bg-white dark:bg-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4">
                        Everything You Need to Succeed
                    </h2>
                    <p class="text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Powerful features designed to help you create, track, and achieve your fitness goals.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="group p-8 rounded-2xl border-2 border-blue-100 dark:border-blue-900 bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 hover:border-orange-300 dark:hover:border-orange-700 transition-all hover:shadow-xl">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center mb-6 group-hover:from-orange-500 group-hover:to-orange-600 transition-all">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                            Create Programs
                        </h3>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Build comprehensive training programs with weeks, days, and exercises. Structure your workouts exactly how you want.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group p-8 rounded-2xl border-2 border-blue-100 dark:border-blue-900 bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 hover:border-orange-300 dark:hover:border-orange-700 transition-all hover:shadow-xl">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center mb-6 group-hover:from-orange-500 group-hover:to-orange-600 transition-all">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                            Log Workouts
                        </h3>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Track your daily workouts with ease. Log sets, reps, weights, and more. See your progress over time.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group p-8 rounded-2xl border-2 border-blue-100 dark:border-blue-900 bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 hover:border-orange-300 dark:hover:border-orange-700 transition-all hover:shadow-xl">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center mb-6 group-hover:from-orange-500 group-hover:to-orange-600 transition-all">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                            Trainer Features
                        </h3>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Assign programs to clients, track their progress, and view detailed analytics. Perfect for fitness professionals.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="group p-8 rounded-2xl border-2 border-blue-100 dark:border-blue-900 bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 hover:border-orange-300 dark:hover:border-orange-700 transition-all hover:shadow-xl">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center mb-6 group-hover:from-orange-500 group-hover:to-orange-600 transition-all">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                            View Statistics
                        </h3>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Monitor your progress with detailed statistics. See completion rates, workout frequency, and more.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="group p-8 rounded-2xl border-2 border-blue-100 dark:border-blue-900 bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 hover:border-orange-300 dark:hover:border-orange-700 transition-all hover:shadow-xl">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center mb-6 group-hover:from-orange-500 group-hover:to-orange-600 transition-all">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                            Calendar View
                        </h3>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Plan and view your workouts in a beautiful calendar interface. Never miss a training day.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="group p-8 rounded-2xl border-2 border-blue-100 dark:border-blue-900 bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 hover:border-orange-300 dark:hover:border-orange-700 transition-all hover:shadow-xl">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center mb-6 group-hover:from-orange-500 group-hover:to-orange-600 transition-all">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                            Analytics & Insights
                        </h3>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Track your progress with detailed statistics, workout frequency, and completion rates to stay motivated and achieve your goals.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="py-24 bg-gradient-to-br from-blue-50 via-slate-50 to-blue-50 dark:from-slate-900 dark:via-blue-900 dark:to-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4">
                        Simple, Transparent Pricing
                    </h2>
                    <p class="text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Choose the plan that's right for you. Start free and upgrade anytime.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Free Plan -->
                    <div class="relative p-8 rounded-2xl border-2 border-blue-200 dark:border-blue-800 bg-white dark:bg-slate-800 hover:border-orange-400 dark:hover:border-orange-600 transition-all hover:shadow-xl">
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Free</h3>
                            <div class="mb-6">
                                <span class="text-4xl font-bold text-slate-900 dark:text-white">$0</span>
                                <span class="text-slate-600 dark:text-slate-400">/month</span>
                            </div>
                            <ul class="space-y-4 mb-8 text-left">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">1 Training Program</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Basic Programs</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Personal Stats</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Workout Logging</span>
                                </li>
                            </ul>
                            @auth
                                <a href="{{ route('dashboard') }}" class="block w-full text-center px-6 py-3 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-lg font-semibold hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                                    Current Plan
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                                    Get Started
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Basic Plan -->
                    <div class="relative p-8 rounded-2xl border-2 border-blue-300 dark:border-blue-700 bg-white dark:bg-slate-800 hover:border-orange-400 dark:hover:border-orange-600 transition-all hover:shadow-xl">
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="px-4 py-1 bg-orange-500 text-white text-sm font-semibold rounded-full">Popular</span>
                        </div>
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Basic</h3>
                            <div class="mb-6">
                                <span class="text-4xl font-bold text-slate-900 dark:text-white">$9</span>
                                <span class="text-slate-600 dark:text-slate-400">/month</span>
                            </div>
                            <ul class="space-y-4 mb-8 text-left">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">5 Training Programs</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Everything in Free</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Enhanced Analytics</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Advanced Statistics</span>
                                </li>
                            </ul>
                            @auth
                                <a href="{{ route('subscriptions.plans') }}" class="block w-full text-center px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg shadow-orange-500/50">
                                    Upgrade Now
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg shadow-orange-500/50">
                                    Get Started
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Trainer Plan -->
                    <div class="relative p-8 rounded-2xl border-2 border-blue-400 dark:border-blue-600 bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 hover:border-orange-400 dark:hover:border-orange-600 transition-all hover:shadow-xl">
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Trainer</h3>
                            <div class="mb-6">
                                <span class="text-4xl font-bold text-slate-900 dark:text-white">$29</span>
                                <span class="text-slate-600 dark:text-slate-400">/month</span>
                            </div>
                            <ul class="space-y-4 mb-8 text-left">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">20 Training Programs</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Everything in Basic</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Share Programs</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Client Analytics</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Invite Clients</span>
                                </li>
                            </ul>
                            @auth
                                <a href="{{ route('subscriptions.plans') }}" class="block w-full text-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-all">
                                    Upgrade Now
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-all">
                                    Get Started
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Pro Trainer Plan -->
                    <div class="relative p-8 rounded-2xl border-2 border-orange-400 dark:border-orange-600 bg-gradient-to-br from-orange-50 to-white dark:from-slate-800 dark:to-slate-900 hover:border-orange-500 dark:hover:border-orange-500 transition-all hover:shadow-2xl shadow-xl shadow-orange-500/20">
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="px-4 py-1 bg-gradient-to-r from-orange-500 to-orange-600 text-white text-sm font-semibold rounded-full shadow-lg">Best Value</span>
                        </div>
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Pro Trainer</h3>
                            <div class="mb-6">
                                <span class="text-4xl font-bold text-slate-900 dark:text-white">$59</span>
                                <span class="text-slate-600 dark:text-slate-400">/month</span>
                            </div>
                            <ul class="space-y-4 mb-8 text-left">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">50 Training Programs</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Everything in Trainer</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Unlimited Clients</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Advanced Analytics</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-slate-600 dark:text-slate-400">Priority Support</span>
                                </li>
                            </ul>
                            @auth
                                <a href="{{ route('subscriptions.plans') }}" class="block w-full text-center px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg shadow-orange-500/50">
                                    Upgrade Now
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg shadow-orange-500/50">
                                    Get Started
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 dark:from-blue-900 dark:via-blue-800 dark:to-blue-900">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-4xl sm:text-5xl font-bold text-white mb-4">
                    Ready to Start Your Fitness Journey?
                </h2>
                <p class="text-xl text-blue-100 mb-10">
                    Join thousands of trainers and athletes who are already using Program Log to achieve their goals.
                </p>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-blue-900 bg-white rounded-xl hover:bg-blue-50 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-blue-900 bg-white rounded-xl hover:bg-blue-50 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5">
                        Get Started Free
                    </a>
                @endauth
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 dark:bg-black border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="text-center">
                    <p class="text-slate-400">
                        © {{ date('Y') }} {{ config('app.name', 'Program Log') }}. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>
