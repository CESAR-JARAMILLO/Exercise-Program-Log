<?php

use App\Http\Controllers\ProgramPdfController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Volt::route('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Volt::route('settings/timezone', 'settings.timezone')->name('timezone.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Volt::route('programs', 'programs.index')->name('programs.index');
    Volt::route('programs/create', 'programs.create')->name('programs.create');
    Volt::route('programs/{program}', 'programs.show')->name('programs.show');
    Volt::route('programs/{program}/edit', 'programs.edit')->name('programs.edit');
    Volt::route('programs/{program}/start', 'programs.start')->name('programs.start');
    Volt::route('programs/{program}/assign', 'programs.assign')
        ->middleware('tier:trainer')
        ->name('programs.assign');
    Route::get('programs/{program}/preview', [ProgramPdfController::class, 'preview'])->name('programs.preview');
    Route::get('programs/{program}/export-pdf', [ProgramPdfController::class, 'export'])->name('programs.export-pdf');
    Volt::route('active-programs/{activeProgram}/stop', 'active-programs.stop')->name('active-programs.stop');
    Volt::route('active-programs/{activeProgram}/restart', 'active-programs.restart')->name('active-programs.restart');
    
    // Workout logging routes
    Volt::route('workouts', 'workouts.calendar')->name('workouts.calendar');
    Volt::route('workouts/log/{activeProgram}/{date}', 'workouts.log')->name('workouts.log');
    Volt::route('workouts/history', 'workouts.history')->name('workouts.history');
    
    // Statistics route
    Volt::route('statistics', 'statistics.index')->name('statistics.index');
    
    // Trainer routes (protected by tier middleware)
    Volt::route('trainers/clients', 'trainers.clients')
        ->middleware('tier:trainer')
        ->name('trainers.clients');
    Volt::route('trainers/analytics', 'trainers.analytics.index')
        ->middleware('tier:trainer')
        ->name('trainers.analytics.index');
    Volt::route('trainers/analytics/{client}', 'trainers.analytics.client')
        ->middleware('tier:trainer')
        ->name('trainers.analytics.client');
    
    // Client routes (all users can receive trainer requests)
    Volt::route('clients/requests', 'clients.requests')->name('clients.requests');
    
    // Subscription routes
    Volt::route('subscriptions/plans', 'subscriptions.plans')->name('subscriptions.plans');
});
