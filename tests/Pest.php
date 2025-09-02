<?php

/** @noinspection LaravelFunctionsInspection */

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Models\User;
use Pest\Browser\Api\ArrayablePendingAwaitablePage;
use Pest\Browser\Api\PendingAwaitablePage;

pest()
    ->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

// Configure browser tests
pest()
    ->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Browser');

// Configure compact printer for cleaner output
pest()->printer()->compact();

/*
|--------------------------------------------------------------------------
| Browser Testing Configuration
|--------------------------------------------------------------------------
|
| Configure default settings for browser testing with Playwright.
| These settings control timeouts, browser preferences, and viewport options.
| Special configuration for Distrobox container environment.
|
*/

// Basic browser configuration for Pest 4
// Note: Many browser settings are configured through environment variables or test-specific methods
// Base URL is automatically read from APP_URL in .env.testing

// Only use methods that definitely exist in Pest 4
// pest()->browser()->timeout(15000); // Commenting out until we verify the correct method

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Helper function to handle browser test login flow.
 *
 * @return ArrayablePendingAwaitablePage|PendingAwaitablePage The browser page object for chaining
 */
function dashboard(): ArrayablePendingAwaitablePage|PendingAwaitablePage
{
    $page = visit('/');

    if (! str_ends_with($page->url(), '/login')) {
        return $page;
    }

    // Use environment credentials when no user provided
    $email = env('TEST_USER_EMAIL', 'figjam@mrwilde.com');
    $password = env('TEST_USER_PASSWORD', 'password');

    // Perform login
    $page
        ->fill('email', $email)
        ->fill('password', $password)
        ->click('[type="submit"]')
        ->wait(1);

    // Verify we're no longer on the login page
    $page->assertPathIsNot('/login')->assertTitle('Dashboard');

    return $page;
}

/*
|--------------------------------------------------------------------------
| Project Configuration & Architecture
|--------------------------------------------------------------------------
|
| Configure the project repository for team management features and apply
| architecture presets for consistent code quality across the application.
|
*/

pest()->project()->github('MrWilde/can-eye-budget');

// Apply Laravel architecture preset with security best practices
arch()->preset()->laravel();
arch()->preset()->security();

/*
|--------------------------------------------------------------------------
| Application Architecture Rules
|--------------------------------------------------------------------------
|
| Custom architectural expectations specific to the budget application,
| enforcing domain boundaries and coding standards.
|
*/

// Ensure all models extend Eloquent Model
arch('models')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

// Ensure all Livewire components extend proper base class
arch('livewire components')
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component');

// Ensure all service classes follow naming convention
arch('services')
    ->expect('App\Services')
    ->toHaveSuffix('Service');

// Ensure controllers follow naming convention and extend base controller
arch('controllers')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->toExtend('App\Http\Controllers\Controller');
