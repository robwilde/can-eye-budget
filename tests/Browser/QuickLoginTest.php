<?php

declare(strict_types=1);

use Tests\Concerns\UseRealDataForBrowserTests;

uses(UseRealDataForBrowserTests::class);

test('quick login test with env credentials', function () {
    // Copy real database data for realistic browser testing
    $this->copyRealDatabaseForBrowserTest();

    // Visit login page and take screenshot
    $loginPage = visit('/login');
    $loginPage->screenshot(filename: 'quick-login-start');

    // Use the login helper with environment credentials
    $page = dashboard();

    // Take screenshot after login
    $page->screenshot(filename: 'quick-login-after-submit');

    // Check if we're redirected successfully
    $page->assertDontSee('Sign in to your account');
    $page->assertSee('Dashboard');

    // Take final screenshot
    $page->screenshot(filename: 'quick-login-final');
});
