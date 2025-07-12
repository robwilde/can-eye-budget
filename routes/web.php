<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Settings routes - these will need traditional Livewire components
    Route::get('settings/profile', function () {
        return view('settings.profile');
    })->name('settings.profile');

    Route::get('settings/password', function () {
        return view('settings.password');
    })->name('settings.password');

    Route::get('settings/appearance', function () {
        return view('settings.appearance');
    })->name('settings.appearance');
});

require __DIR__.'/auth.php';
