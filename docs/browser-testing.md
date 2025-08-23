# Browser Testing with PEST 4 & Playwright

This document provides comprehensive guidance for using browser testing in the CanEye Budget application with PEST 4 and Playwright.

## Overview

Browser testing allows you to test your application's user interface and interactions in real browsers, ensuring that features work correctly from a user's perspective. This setup uses:

- **PEST PHP 4** - Testing framework
- **pestphp/pest-plugin-browser** - Browser testing plugin
- **Playwright** - Browser automation engine

## Setup

### Prerequisites

1. **Composer Dependencies**: The `pestphp/pest-plugin-browser` package is installed as a dev dependency
2. **Node.js Dependencies**: Playwright is installed via `npm install playwright@latest`
3. **Browser Drivers**: Installed via `npx playwright install`

### Configuration

Browser testing is configured in `tests/Pest.php`:

```php
// Configure browser tests
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Browser');

// Set default browser timeout to 10 seconds (10000ms)
pest()->browser()->timeout(10000);
```

### Directory Structure

```
tests/
├── Browser/
│   ├── Screenshots/        # Auto-generated failure screenshots
│   ├── AuthenticationTest.php
│   ├── CalendarViewTest.php
│   ├── TransactionFormTest.php
│   ├── CategoryManagerTest.php
│   └── BasicBrowserTest.php
└── Pest.php               # Test configuration
```

## Writing Browser Tests

### Basic Test Structure

```php
<?php

declare(strict_types=1);

test('can visit homepage', function () {
    $page = visit('/');
    
    $page->assertSee('Laravel');
});
```

### Authentication Tests

Browser tests can test complete user flows:

```php
test('user can login through browser', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
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
```

### Component Interactions

Test complex UI components like forms:

```php
test('user can create expense transaction', function () {
    // Setup test data
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    
    $page = visit('/login');
    
    // Login first
    $page->fill('email', $user->email)
         ->fill('password', 'password')
         ->click('Log in');
    
    // Open transaction form
    $page->click('Add Transaction')
         ->wait(1000);
    
    // Fill and submit form
    $page->fill('[data-test="description"]', 'Test Expense')
         ->fill('[data-test="amount"]', '75.50')
         ->select('[data-test="type"]', 'expense')
         ->click('[data-test="save-transaction"]');
    
    // Verify results
    $page->wait(2000)
         ->assertSee('Test Expense');
});
```

## Available Methods

### Page Navigation
- `visit('/path')` - Navigate to a URL
- `navigate('/new-path')` - Navigate from current page
- `assertPathIs('/expected')` - Assert current path
- `assertUrlIs('http://example.com/path')` - Assert full URL

### Element Interaction
- `click('selector')` - Click element
- `fill('input-name', 'value')` - Fill input field
- `select('select-name', 'option')` - Select dropdown option
- `check('checkbox-name')` - Check checkbox
- `uncheck('checkbox-name')` - Uncheck checkbox
- `press('Enter')` - Press keyboard key

### Assertions
- `assertSee('text')` - Assert text is visible
- `assertDontSee('text')` - Assert text is not visible
- `assertPresent('selector')` - Assert element exists
- `assertMissing('selector')` - Assert element doesn't exist
- `assertVisible('selector')` - Assert element is visible
- `assertEnabled('input')` - Assert input is enabled
- `assertDisabled('input')` - Assert input is disabled

### Waiting
- `wait(milliseconds)` - Wait for specified time
- `waitFor('selector')` - Wait for element to appear
- `waitForKey('key')` - Wait for keyboard key press

### Device & Browser Options
- `visit('/path')->on()->mobile()` - Use mobile viewport
- `visit('/path')->on()->iPhone14Pro()` - Use specific device
- `visit('/path')->onDarkMode()` - Use dark color scheme

## Running Browser Tests

### Basic Commands

```bash
# Run all browser tests
./vendor/bin/pest tests/Browser/

# Run specific test file
./vendor/bin/pest tests/Browser/AuthenticationTest.php

# Run tests in parallel for faster execution
./vendor/bin/pest tests/Browser/ --parallel

# Run tests with different browsers
./vendor/bin/pest tests/Browser/ --browser=firefox
./vendor/bin/pest tests/Browser/ --browser=safari

# Debug mode (headed browser, pauses on failure)
./vendor/bin/pest tests/Browser/ --debug
```

### Browser Selection

Available browsers:
- `chrome` (default)
- `firefox`
- `safari` (macOS only)
- `webkit`

### Environment Requirements

1. **Laravel Development Server**: Must be running on http://localhost:8000
   ```bash
   php artisan serve
   # or
   composer dev  # Starts server + queue + logs + vite
   ```

2. **Database**: Uses RefreshDatabase trait, so tests run in isolated database transactions

## Test Data Attributes

Use `data-test` attributes in your Blade templates for reliable element selection:

```php
// In Blade template
<button data-test="add-transaction">Add Transaction</button>
<form data-test="transaction-form">
    <input name="description" data-test="description">
    <select name="type" data-test="type">
    </select>
</form>

// In test
$page->click('[data-test="add-transaction"]')
     ->fill('[data-test="description"]', 'Test Transaction')
     ->select('[data-test="type"]', 'expense');
```

## Debugging

### Screenshots
Failed tests automatically generate screenshots saved to `tests/Browser/Screenshots/`.

### Console Logs
Access browser console logs in tests:
```php
$page->script('console.log("Debug info")');
// or check for console errors
$page->assertNoJavaScriptErrors();
```

### Headed Mode
Run tests with visible browser for debugging:
```bash
./vendor/bin/pest tests/Browser/ --debug
```

## Common Patterns

### Authentication Setup
```php
beforeEach(function () {
    $this->user = User::factory()->create();
    // Login helper
    $this->loginAs = function ($page) {
        return $page->visit('/login')
                   ->fill('email', $this->user->email)
                   ->fill('password', 'password')
                   ->click('Log in');
    };
});
```

### Form Testing
```php
test('form validation works', function () {
    $page = visit('/transaction/create');
    
    // Submit empty form
    $page->click('Save')
         ->assertSee('The description field is required')
         ->assertSee('The amount field is required');
         
    // Fill and resubmit
    $page->fill('description', 'Valid Transaction')
         ->fill('amount', '100.00')
         ->click('Save')
         ->assertPathIs('/dashboard')
         ->assertSee('Transaction created');
});
```

### Component State Testing
```php
test('calendar view switching works', function () {
    $page = visit('/dashboard');
    
    $page->click('Day View')
         ->wait(500)
         ->assertVisible('[data-test="day-calendar"]')
         ->click('Month View')
         ->wait(500)
         ->assertVisible('[data-test="month-calendar"]')
         ->assertMissing('[data-test="day-calendar"]');
});
```

## Troubleshooting

### Common Issues

1. **Timeout Errors**: Increase timeout or add explicit waits
   ```php
   $page->wait(2000); // Wait 2 seconds
   ```

2. **Element Not Found**: Use data-test attributes instead of CSS classes
   ```php
   // Instead of: $page->click('.btn-primary')
   $page->click('[data-test="submit-button"]');
   ```

3. **Flaky Tests**: Add waits after dynamic content changes
   ```php
   $page->click('Add Transaction')
        ->wait(1000) // Wait for modal to appear
        ->assertPresent('[data-test="transaction-form"]');
   ```

4. **Server Not Running**: Ensure Laravel dev server is active
   ```bash
   php artisan serve
   ```

### Performance Tips

1. **Use Parallel Execution**: `--parallel` flag speeds up test runs
2. **Batch Related Tests**: Group similar tests in same file
3. **Minimize Navigation**: Reuse browser sessions when possible
4. **Use Headless Mode**: Default headless mode is faster than headed

## Best Practices

1. **Use Data Attributes**: Always use `data-test` attributes for element selection
2. **Test User Flows**: Focus on complete user journeys, not just individual components
3. **Keep Tests Independent**: Each test should set up its own data and state
4. **Handle Async Operations**: Use appropriate waits for dynamic content
5. **Screenshot Failures**: Check failure screenshots to debug issues
6. **Mobile Testing**: Test responsive layouts with mobile viewports
7. **Real Data**: Use factories to create realistic test data

## Integration with CI/CD

Browser tests can be integrated into continuous integration pipelines. Ensure:

1. **Headless Mode**: Tests run in headless mode by default
2. **Server Management**: CI must start Laravel dev server before tests
3. **Browser Drivers**: Install Playwright browsers in CI environment
4. **Artifact Storage**: Save failure screenshots as CI artifacts

Example GitHub Actions setup:
```yaml
- name: Install Playwright Browsers
  run: npx playwright install --with-deps

- name: Start Laravel Server
  run: php artisan serve &
  
- name: Run Browser Tests
  run: ./vendor/bin/pest tests/Browser/ --parallel
```

This browser testing setup provides a robust foundation for end-to-end testing of your Laravel application's user interface.