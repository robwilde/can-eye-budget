<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('home');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Budget management routes
    Route::get('accounts', App\Livewire\AccountsPage::class)->name('accounts');
    Route::get('categories', App\Livewire\CategoriesPage::class)->name('categories');
    Route::get('automation', App\Livewire\AutomationRules::class)->name('automation');

    // Import routes
    Route::get('import', App\Livewire\ImportWizard::class)->name('import.wizard');
    Route::get('imports', App\Livewire\ImportHistory::class)->name('import.history');

    // Settings routes - these will need traditional Livewire components
    Route::get('settings/profile', static function () {
        return view('settings.profile');
    })->name('settings.profile');

    Route::get('settings/password', static function () {
        return view('settings.password');
    })->name('settings.password');

    Route::get('settings/appearance', static function () {
        return view('settings.appearance');
    })->name('settings.appearance');
});

require __DIR__.'/auth.php';
