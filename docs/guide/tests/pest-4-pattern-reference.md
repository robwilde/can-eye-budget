# PEST 4 Critical Patterns - Quick Reference for Claude Code

## 🚨 CRITICAL: wait() uses SECONDS, not milliseconds!

```php
// ❌ WRONG - These cause catastrophic delays:
->wait(1000)   // WRONG: Waits 1000 SECONDS (16+ minutes)!
->wait(500)    // WRONG: Waits 500 SECONDS (8+ minutes)!  
->wait(2000)   // WRONG: Waits 2000 SECONDS (33+ minutes)!
->wait(250)    // WRONG: Waits 250 SECONDS (4+ minutes)!

// ✅ CORRECT - Always use seconds:
->wait(1)      // Correct: Wait 1 second
->wait(2)      // Correct: Wait 2 seconds
->wait(0.5)    // Correct: Wait 500 milliseconds
->wait(0.25)   // Correct: Wait 250 milliseconds
->wait(3)      // Correct: Wait 3 seconds
```

## 📋 Common Patterns Reference

### Browser Test Login Pattern

```php
// Always use the login() helper when available
$page = login();

// Login helper implementation uses SECONDS
function login(?User $user = null): Webpage 
{
    return visit('/login')
        ->fill('email', $user->email)
        ->fill('password', env('TEST_USER_PASSWORD'))
        ->click('[type="submit"]')
        ->wait(2)  // 2 SECONDS, not milliseconds!
        ->assertDontSee('Sign in to your account');
}
```

### Form Interactions

```php
// Fill and submit forms
$page->fill('email', env('TEST_USER_EMAIL'))  // Use env(), not config()
    ->fill('password', env('TEST_USER_PASSWORD'))
    ->click('[type="submit"]')  // Use type selector for forms
    ->wait(1)  // Wait 1 SECOND for navigation
    ->assertPathIs('/dashboard');  // Use path, not full URL
```

### Livewire Components

```php
// Always escape wire:model colons
$page->fill('[wire\\:model="accountName"]', 'Savings')
    ->fill('[wire\\:model="balance"]', '1000')
    ->press('Save')  // Use press() for Flux UI buttons
    ->wait(1);  // 1 SECOND wait for Livewire
```

### Common Wait Scenarios

```php
// After form submission
->click('[type="submit"]')
->wait(2)  // 2 seconds for redirect

// After Livewire update
->fill('[wire\\:model="search"]', 'query')
->wait(1)  // 1 second for Livewire

// After modal open
->click('Open Modal')
->wait(0.5)  // Half second for animation

// After AJAX request
->press('Load More')
->wait(1)  // 1 second for data

// Never do this:
->wait(1000)  // THIS IS 16+ MINUTES!
```

## ⚠️ Common Mistakes to Avoid

| Wrong                       | Right                              | Why                    |
|-----------------------------|------------------------------------|------------------------|
| `wait(1000)`                | `wait(1)`                          | wait() uses SECONDS    |
| `wait(500)`                 | `wait(0.5)`                        | 500 seconds ≠ 500ms    |
| `config('USERNAME')`        | `env('TEST_USER_EMAIL')`           | Use env for test data  |
| `assertPresent()`           | `assertSee()` or `assertVisible()` | Method doesn't exist   |
| `assertUrlIs('http://...')` | `assertPathIs('/path')`            | Use path only          |
| `click('Log in')`           | `click('[type="submit"]')`         | Use specific selectors |
| `[wire:model="..."]`        | `[wire\\:model="..."]`             | Escape the colon       |

## 🎯 Quick Validation Checklist

Before running any browser test:

1. ✅ All `wait()` calls use seconds (1, 2, 0.5) not milliseconds (1000, 500)
2. ✅ Test credentials use `env()` not `config()`
3. ✅ Form submissions use `[type="submit"]` selector
4. ✅ Livewire selectors escape the colon: `[wire\\:model="..."]`
5. ✅ URL assertions use `assertPathIs()` not `assertUrlIs()`
6. ✅ Waits are added after navigation/submission (1-2 seconds)
7. ✅ Using `press()` for Flux UI buttons, `click()` for elements

## 💡 Rule of Thumb

**If your wait value is greater than 10, you're probably using milliseconds incorrectly!**

Normal wait times in PEST:

- `0.25` to `0.5` - Quick animations
- `1` to `2` - Page navigation
- `3` to `5` - Slow operations
- Anything > 10 is suspicious!

## 🚀 Usage in Claude Code

When writing PEST tests with Claude Code, always start with:

```
Remember: PEST wait() uses SECONDS not milliseconds!
- wait(1) = 1 second
- wait(0.5) = 500 milliseconds
- wait(1000) = 1000 seconds = WRONG!
```

This will help Claude Code remember this critical pattern.
