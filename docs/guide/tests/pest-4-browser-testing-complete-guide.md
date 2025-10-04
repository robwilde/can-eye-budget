# PEST 4 Browser Testing - Complete Guide

This comprehensive guide covers PEST 4 browser testing for Laravel applications with Livewire and Flux UI components. It's based on real-world investigation findings and established patterns from this codebase.

## Table of Contents

1. [Introduction & Overview](#introduction--overview)
2. [Setup & Configuration](#setup--configuration)
3. [Core API Reference](#core-api-reference)
4. [Project-Specific Patterns](#project-specific-patterns)
5. [Authentication & Login Helper](#authentication--login-helper)
6. [Livewire Integration](#livewire-integration)
7. [Flux UI Component Testing](#flux-ui-component-testing)
8. [Common Issues & Solutions](#common-issues--solutions)
9. [Advanced Features](#advanced-features)
10. [Best Practices](#best-practices)
11. [Troubleshooting](#troubleshooting)

## Introduction & Overview

PEST 4 introduces powerful browser testing capabilities built on Playwright, providing a Laravel-friendly API for end-to-end testing. Unlike traditional unit/feature tests, browser tests:

- Run in a real browser environment
- Test complete user workflows
- Verify JavaScript functionality
- Validate UI interactions and visual elements

### Why Browser Testing Matters

Browser tests complement unit and feature tests by:
- Testing the complete user experience
- Verifying complex UI interactions (dropdowns, modals, forms)
- Ensuring JavaScript and Livewire components work correctly
- Catching integration issues between frontend and backend

## Setup & Configuration

### Installation Requirements

PEST 4 browser testing is already configured in this project. For reference, the setup includes:

```bash
# PEST browser testing plugin (already installed)
composer require pestphp/pest-plugin-browser --dev

# Playwright dependencies (already configured)
npm install playwright@latest
npx playwright install
```

### Environment Configuration

Browser tests use `.env.testing` for configuration:

```env
# Test user credentials (used by login helper)
TEST_USER_EMAIL=figjam@mrwilde.com
TEST_USER_PASSWORD=password

# Browser testing configuration
PEST_BROWSER_HEADLESS=false  # Set to true for CI/CD
```

### Directory Structure

```
tests/
├── Browser/                    # Browser tests
│   ├── AuthenticationTest.php
│   ├── AccountsPageTest.php
│   └── ...
├── Concerns/                   # Shared test utilities
│   └── UseRealDataForBrowserTests.php
└── Pest.php                   # Test configuration and helpers
```

## Core API Reference

### Navigation

```php
// Visit a page
$page = visit('/dashboard');

// Navigate with specific browser settings
$page = visit('/login')
    ->on()->desktop()          // Desktop viewport
    ->on()->mobile()           // Mobile viewport
    ->inDarkMode();            // Dark mode testing
```

### Element Interaction

```php
// Fill form fields
$page->fill('email', 'user@example.com');
$page->fill('[wire\\:model="accountName"]', 'Savings Account');

// Click elements
$page->click('Login');                    // By text content
$page->click('[type="submit"]');         // By selector (preferred for forms)
$page->press('Add Account');             // Button by text (Flux UI compatible)

// Other interactions
$page->check('#terms');                  // Check checkbox
$page->uncheck('#newsletter');           // Uncheck checkbox
$page->selectOption('select[name="plan"]', 'pro');  // Select dropdown option
```

### Assertions

```php
// Content assertions
$page->assertSee('Welcome');
$page->assertDontSee('Error');

// URL/Path assertions
$page->assertPathIs('/dashboard');       // Path only (recommended)
$page->assertUrlIs('http://localhost:8000/dashboard');  // Full URL

// Element state assertions
$page->assertVisible('form');
$page->assertHidden('.loading');
$page->assertChecked('#terms');
$page->assertValue('input[name="email"]', 'user@example.com');
```

### Timing & Waits

```php
// Fixed delays
$page->wait(1);                         // Wait 1 second
$page->wait(2);                         // Wait 2 seconds

// Wait for specific conditions
$page->waitFor('.success-message');     // Wait for element
$page->waitForText('Updated successfully');  // Wait for text
$page->waitForNavigation();             // Wait for page navigation
```

## Project-Specific Patterns

### Environment Variables vs Config

❌ **Wrong** - Using config() for test credentials:
```php
->fill('email', config('USERNAME', 'default@example.com'))
```

✅ **Correct** - Using env() for test credentials:
```php
->fill('email', env('TEST_USER_EMAIL', 'figjam@mrwilde.com'))
```

### Button Interaction Patterns

❌ **Wrong** - Using generic text-based selectors:
```php
->click('Log in')  // May fail if text changes or multiple buttons exist
```

✅ **Correct** - Using specific selectors:
```php
->click('[type="submit"]')     // Form submission buttons
->press('Add Account')         // Flux UI buttons by text
```

### Assertion Best Practices

❌ **Wrong** - Using incorrect assertion methods:
```php
->assertPresent('form')        // Method doesn't exist
->assertUrlIs('/login')        // Too specific, includes domain
```

✅ **Correct** - Using proper assertion methods:
```php
->assertSee('Create an account')  // Check visible content
->assertPathIs('/login')          // Check path only
```

## Authentication & Login Helper

This project includes a centralized `login()` helper function in `tests/Pest.php`:

### Using the Login Helper

```php
// Login with default test user from .env.testing
$page = login();

// Login with specific user
$user = User::factory()->create();
$page = login($user);

// Continue testing after login
$page = login()
    ->assertSee('Dashboard')
    ->click('Settings')
    ->assertPathIs('/settings');
```

### Login Helper Implementation

The helper function (lines 85-108 in `tests/Pest.php`):

```php
function login(?User $user = null): Webpage 
{
    $user = $user ?: User::where('email', env('TEST_USER_EMAIL'))->first();
    
    if (!$user) {
        $user = User::factory()->create([
            'email' => env('TEST_USER_EMAIL'),
            'password' => Hash::make(env('TEST_USER_PASSWORD'))
        ]);
    }

    return visit('/login')
        ->fill('email', $user->email)
        ->fill('password', env('TEST_USER_PASSWORD', 'password'))
        ->click('[type="submit"]')
        ->wait(2)
        ->assertDontSee('Sign in to your account');
}
```

### Key Features:

- **Flexible Parameters**: Accepts User object or uses environment credentials
- **Environment Fallback**: Uses `TEST_USER_EMAIL` and `TEST_USER_PASSWORD`
- **Proper Selectors**: Uses `[type="submit"]` for form submission
- **Wait Handling**: Includes 2-second wait for page transition
- **Verification**: Asserts successful login by checking text disappears

## Livewire Integration

### Wire Model Selectors

When testing Livewire components, escape the colon in `wire:model` selectors:

```php
// Escape the colon in wire:model
$page->fill('[wire\\:model="accountName"]', 'Savings Account');
$page->fill('[wire\\:model="email"]', 'user@example.com');
```

### Livewire-Specific Patterns

```php
// Wait for Livewire to process updates
$page->fill('[wire\\:model="search"]', 'query')
    ->wait(1)  // Allow Livewire to process
    ->assertSee('Search results');

// Test Livewire form submission
$page->fill('[wire\\:model="name"]', 'John Doe')
    ->press('Save')  // Triggers Livewire action
    ->wait(1)
    ->assertSee('Saved successfully');
```

## Flux UI Component Testing

### Form Components

```php
// Test Flux input components
$page->fill('[placeholder="Enter email"]', 'user@example.com');
$page->fill('[placeholder="Full name"]', 'John Doe');

// Test Flux buttons
$page->press('Create Account');  // Button text matching
$page->press('Log Out');         // Works with Flux UI buttons
```

### Dropdown Interactions

Complex Flux UI dropdowns may require special handling:

```php
// For simple dropdowns
$page->selectOption('select[name="category"]', 'income');

// For complex Flux dropdowns (may need investigation)
// Some dropdowns require clicking the trigger first
$page->click('[data-dropdown-trigger]')
    ->wait(0.5)
    ->click('[data-option="value"]');
```

### Modal Testing

```php
// Test modal interactions
$page->click('Open Modal')
    ->wait(0.5)  // Wait for modal animation
    ->assertSee('Modal Title')
    ->fill('[name="field"]', 'value')
    ->press('Save')
    ->wait(1)
    ->assertDontSee('Modal Title');  // Modal closed
```

## Common Issues & Solutions

### Issue 1: Wrong Config Usage

**Problem**: Tests failing because `config('USERNAME')` returns null
```php
->fill('email', config('USERNAME', 'default@example.com'))  // ❌
```

**Solution**: Use environment variables for test credentials
```php
->fill('email', env('TEST_USER_EMAIL', 'figjam@mrwilde.com'))  // ✅
```

### Issue 2: Incorrect Assertion Methods

**Problem**: `assertPresent()` method doesn't exist
```php
->assertPresent('form')  // ❌
```

**Solution**: Use proper assertion methods
```php
->assertSee('Create an account')  // ✅ Check visible content
->assertVisible('form')           // ✅ Check element visibility
```

### Issue 3: URL vs Path Assertions

**Problem**: Full URL assertions are too brittle
```php
->assertUrlIs('http://127.0.0.1:59297/login')  // ❌ Includes random port
```

**Solution**: Use path-only assertions
```php
->assertPathIs('/login')  // ✅ Path only
```

### Issue 4: Button Click Failures

**Problem**: Generic text selectors may fail
```php
->click('Log in')  // ❌ May match multiple elements or fail on text changes
```

**Solution**: Use specific selectors
```php
->click('[type="submit"]')  // ✅ Form submission buttons
->press('Log in')          // ✅ Button text (more robust than click)
```

### Issue 5: Timing Issues

**Problem**: Tests failing due to timing
```php
->fill('email', 'user@example.com')
->click('[type="submit"]')
->assertPathIs('/dashboard')  // ❌ May fail before redirect completes
```

**Solution**: Add appropriate waits
```php
->fill('email', 'user@example.com')
->click('[type="submit"]')
->wait(1)                    // ✅ Wait for redirect
->assertPathIs('/dashboard')
```

### Issue 6: Complex UI Interactions

**Problem**: Dropdown or menu interactions failing
```php
->click('Log Out')  // ❌ Button might be in unopened dropdown
```

**Solution**: Break down complex interactions
```php
// Option 1: Use helper functions
$page = login();  // Uses centralized login helper

// Option 2: Handle complex UI step by step
->click('[data-user-menu]')  // Open dropdown
->wait(0.5)                  // Wait for animation
->click('Log Out')           // Click logout option

// Option 3: Mark as incomplete for complex cases
$this->markTestIncomplete('Logout dropdown interaction needs investigation');
```

## Advanced Features

### Running Tests

```bash
# Run all browser tests
./vendor/bin/pest tests/Browser/

# Run specific test file
./vendor/bin/pest tests/Browser/AuthenticationTest.php

# Run in headed mode (see browser)
PEST_BROWSER_HEADLESS=false ./vendor/bin/pest tests/Browser/ --headed

# Run with slow motion for debugging
PEST_BROWSER_SLOWMO=1000 ./vendor/bin/pest tests/Browser/

# Use project alias for headed testing
op pest-headed tests/Browser/
```

### Real Data Testing

Use the `UseRealDataForBrowserTests` trait for realistic testing:

```php
use Tests\Concerns\UseRealDataForBrowserTests;

uses(UseRealDataForBrowserTests::class);

beforeEach(function () {
    $this->copyRealDatabaseForBrowserTest();
    $this->user = User::first(); // Use real user data
});

test('dashboard works with real data', function () {
    $page = login($this->user);
    
    $page->assertSee('Dashboard')
        ->assertSee($this->user->name);
});
```

### Screenshot Capture

```php
// Take screenshot for debugging
test('complex interaction', function () {
    $page = visit('/form');
    
    $page->screenshot('before-submission');  // Save screenshot
    
    $page->fill('email', 'test@example.com')
        ->screenshot('after-email-fill')    // Another screenshot
        ->press('Submit');
});
```

### Viewport Testing

```php
// Test on different viewports
test('responsive design', function () {
    $page = visit('/dashboard')
        ->on()->mobile()
        ->assertSee('Mobile Menu');
        
    $page->on()->desktop()
        ->assertSee('Desktop Navigation');
});
```

### Dark Mode Testing

```php
test('dark mode support', function () {
    $page = visit('/dashboard')
        ->inDarkMode()
        ->assertSee('Dashboard')
        ->screenshot('dark-mode-dashboard');
});
```

## Best Practices

### 1. Use Helper Functions

Leverage the `login()` helper and create your own helpers for common patterns:

```php
// Good: Use existing helper
$page = login();

// Better: Create custom helpers for common workflows
function createAccount(string $name): Webpage {
    return login()
        ->visit('/accounts')
        ->press('Add Account')
        ->fill('[wire\\:model="accountName"]', $name)
        ->press('Save')
        ->wait(1);
}
```

### 2. Organize Tests Logically

```php
// Group related tests
describe('Authentication', function () {
    test('user can register');
    test('user can login');
    test('user can logout');
});

describe('Account Management', function () {
    test('user can create account');
    test('user can edit account');
    test('user can delete account');
});
```

### 3. Use Descriptive Test Names

```php
// Good: Descriptive test names
test('user can create savings account with initial balance');
test('user receives validation error for duplicate account name');
test('account deletion requires confirmation dialog');
```

### 4. Handle Cleanup

```php
// Clean up after tests that create data
test('user can upload profile photo', function () {
    $page = login()
        ->visit('/profile')
        ->uploadFile('#photo', 'test-photo.jpg');
    
    // Cleanup is handled by RefreshDatabase or real data trait
});
```

### 5. Use Appropriate Waits

```php
// Use specific waits when possible
$page->waitForText('Success')      // Wait for specific text
    ->waitFor('.notification')     // Wait for element
    ->wait(1);                     // Fixed wait as last resort
```

## Troubleshooting

### Test Failures Investigation

1. **Check Screenshots**: Browser tests automatically save screenshots on failure
2. **Use Headed Mode**: Run tests with `PEST_BROWSER_HEADLESS=false` to see what's happening
3. **Add Debug Waits**: Use `->wait(5)` to pause and inspect the page state
4. **Check Selectors**: Verify element selectors exist and are unique

### Common Error Messages

**"Expected element [selector] to be present"**
- Element selector is incorrect or element doesn't exist
- Check HTML structure and verify selector

**"Timeout exceeded"**
- Page is taking too long to load or element to appear
- Add appropriate waits or increase timeout

**"Call to undefined method"**
- Using incorrect API method
- Check this guide for correct method names

**"Authentication required"**
- Test needs authentication but isn't using login helper
- Add `$page = login();` before protected actions

### Performance Optimization

```php
// Run tests in parallel (faster execution)
./vendor/bin/pest --parallel

// Use headless mode for CI/CD
PEST_BROWSER_HEADLESS=true ./vendor/bin/pest tests/Browser/

// Limit test scope when debugging
./vendor/bin/pest tests/Browser/AuthenticationTest.php --filter="user can login"
```

### Container Environment Notes

This project runs in a Distrobox container environment:
- Playwright runs in Ubuntu container
- Laravel app runs on host
- Tests connect via container networking
- Screenshots and artifacts save to `tests/Browser/Screenshots/`

## Summary

This guide provides a comprehensive reference for PEST 4 browser testing in this Laravel/Livewire/Flux UI application. Key takeaways:

1. **Use the `login()` helper** for authentication in tests
2. **Use `env()` instead of `config()`** for test credentials
3. **Use proper selectors** (`[type="submit"]` for forms, escaped wire:model)
4. **Use correct assertion methods** (`assertSee()`, `assertPathIs()`)
5. **Add appropriate waits** after actions that trigger navigation or updates
6. **Handle complex UI interactions** step by step or mark as incomplete

For future development, refer to this guide to avoid common pitfalls and follow established patterns. When encountering new testing scenarios, consider:

1. Can existing patterns be applied?
2. Does a new helper function need to be created?
3. Are there timing issues that need addressing?
4. Is the selector strategy robust and maintainable?

This guide should minimize investigation time and provide a solid foundation for browser testing in this application.