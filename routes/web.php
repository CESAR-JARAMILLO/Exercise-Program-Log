<?php

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
    Volt::route('active-programs/{activeProgram}/stop', 'active-programs.stop')->name('active-programs.stop');
    Volt::route('active-programs/{activeProgram}/restart', 'active-programs.restart')->name('active-programs.restart');
    
    // Workout logging routes
    Volt::route('workouts', 'workouts.calendar')->name('workouts.calendar');
    Volt::route('workouts/log/{activeProgram}/{date}', 'workouts.log')->name('workouts.log');
    Volt::route('workouts/history', 'workouts.history')->name('workouts.history');
});
