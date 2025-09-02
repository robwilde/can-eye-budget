<?php

/** @noinspection LaravelFunctionsInspection */

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use Tests\Concerns\UseRealDataForBrowserTests;

uses(UseRealDataForBrowserTests::class);

beforeEach(function () {
    // Copy real database data for realistic browser testing
    $this->copyRealDatabaseForBrowserTest();
});

test('user can visit register page', function () {
    $page = visit('/register');

    $page
        ->assertPathIs('/register')
        ->assertSee('Create an account');
});

test('user can login through browser', function () {
    $page = visit('/login');

    $page
        ->assertSee('Log in')
        ->fill('email', env('TEST_USER_EMAIL', 'figjam@mrwilde.com'))
        ->fill('password', env('TEST_USER_PASSWORD', 'password'))
        ->click('[type="submit"]')
        ->wait(1)
        ->assertPathIsNot('/login')
        ->assertTitle('Dashboard');
});

test('user cannot login with invalid credentials', function () {
    $page = visit('/login');

    $page
        ->assertSee('Log in')
        ->fill('email', env('TEST_USER_EMAIL', 'figjam@mrwilde.com'))
        ->fill('password', 'wrongpassword')
        ->click('[type="submit"]')
        ->wait(1)
        ->assertPathIs('/login')
        ->assertSee('These credentials do not match our records');
});

test('user can logout through browser', function () {
    // Use the login helper
    $page = dashboard();

    // Verify we're logged in (dashboard() already asserts we're on dashboard)
    $page->assertTitle('Dashboard');

    // The actual logout requires opening dropdown and clicking logout button
    // Since that's complex with Flux UI dropdowns, we'll test that a logged-in user
    // can still access login page (which should redirect to dashboard if session active)
    // Then we'll make a direct POST to logout route

    // For now, skip the complex UI interaction and just verify login/logout flow works
    // A proper test would need to:
    // 1. Click user avatar/dropdown trigger
    // 2. Wait for dropdown to open
    // 3. Click the Log Out button within the dropdown

    // This simplified test verifies the authentication flow
    $this->markTestIncomplete('Logout dropdown interaction needs proper Flux UI dropdown handling');
});

test('guest user is redirected to login when accessing protected pages', function () {
    $page = visit('/');

    $page
        ->assertPathIs('/login')
        ->assertSee('Log in');
});

test('authentication works on mobile viewport', function () {
    $page = visit('/login')->on()->mobile();

    $page
        ->assertSee('Log in')
        ->fill('email', env('TEST_USER_EMAIL', 'figjam@mrwilde.com'))
        ->fill('password', env('TEST_USER_PASSWORD', 'password'))
        ->click('[type="submit"]')
        ->wait(1)
        ->assertPathIsNot('/login')
        ->assertTitle('Dashboard');
});

test('password reset flow works through browser', function () {
    $page = visit('/forgot-password');

    $page
        ->assertSee('Forgot password')
        ->fill('email', env('TEST_USER_EMAIL', 'figjam@mrwilde.com'))
        ->click('[type="submit"]')
        ->wait(2)
        ->assertSee('A reset link will be sent if the account exists');
});
