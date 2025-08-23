<?php

declare(strict_types=1);

use App\Models\User;

test('user can visit register page', function () {
    $page = visit('/register');

    $page->assertPathIs('/register')
         ->assertPresent('form');
});

test('user can login through browser', function () {
    $user = User::factory()->create([
        'email'    => 'jane@example.com',
        'password' => 'password123',
    ]);

    $page = visit('/login');

    $page->assertSee('Log in')
         ->fill('email', 'jane@example.com')
         ->fill('password', 'password123')
         ->click('Log in')
         ->assertPathIs('/dashboard')
         ->assertSee('Dashboard');
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'email'    => 'jane@example.com',
        'password' => 'password123',
    ]);

    $page = visit('/login');

    $page->assertSee('Log in')
         ->fill('email', 'jane@example.com')
         ->fill('password', 'wrongpassword')
         ->click('Log in')
         ->assertPathIs('/login')
         ->assertSee('These credentials do not match our records');
});

test('user can logout through browser', function () {
    $user = User::factory()->create();

    $page = visit('/login');

    // Login first
    $page->fill('email', $user->email)
         ->fill('password', 'password')
         ->click('Log in')
         ->assertPathIs('/dashboard');

    // Then logout
    $page->click('Log Out')
         ->assertPathIs('/')
         ->assertSee('Log in');
});

test('guest user is redirected to login when accessing protected pages', function () {
    $page = visit('/dashboard');

    $page->assertUrlIs('/login')
         ->assertSee('Log in');
});

test('authentication works on mobile viewport', function () {
    $user = User::factory()->create([
        'email'    => 'mobile@example.com',
        'password' => 'password123',
    ]);

    $page = visit('/login')->on()->mobile();

    $page->assertSee('Log in')
         ->fill('email', 'mobile@example.com')
         ->fill('password', 'password123')
         ->click('Log in')
         ->assertPathIs('/dashboard')
         ->assertSee('Dashboard');
});

test('password reset flow works through browser', function () {
    $user = User::factory()->create([
        'email' => 'reset@example.com',
    ]);

    $page = visit('/forgot-password');

    $page->assertSee('Forgot Password')
         ->fill('email', 'reset@example.com')
         ->click('Email Password Reset Link')
         ->assertSee('We have emailed your password reset link');
});
