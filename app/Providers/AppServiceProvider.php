<?php

declare(strict_types=1);

namespace App\Providers;

use App\Livewire\Synthesizers\DataSynthesizer;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::propertySynthesizer(DataSynthesizer::class);
    }
}
