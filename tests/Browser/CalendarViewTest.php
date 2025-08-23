<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->for($this->user)->create([
        'name'    => 'Main Checking',
        'type'    => 'checking',
        'balance' => 1000.00,
    ]);
    $this->category = Category::factory()->create([
        'name'    => 'Groceries',
        'user_id' => $this->user->id,
    ]);
});

test('calendar view loads and displays correctly', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in')
         ->assertUrlIs('/dashboard');

    // Navigate to calendar or verify it's on dashboard
    $page->assertSee('Calendar')
         ->assertSee($this->account->name)
         ->assertSee('Add Transaction');
});

test('user can switch between calendar views', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Test view switching
    $page->click('Day')
         ->assertSee('Day View')
         ->click('Week')
         ->assertSee('Week View')
         ->click('Month')
         ->assertSee('Month View')
         ->click('Year')
         ->assertSee('Year View');
});

test('user can navigate between calendar periods', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Test navigation
    $page->click('[data-test="next-period"]')
         ->wait(1000) // Wait for navigation to complete
         ->click('[data-test="previous-period"]')
         ->wait(1000)
         ->click('[data-test="today-button"]')
         ->wait(1000);

    // Should return to current period
    $page->assertPresent('[data-test="calendar-view"]');
});

test('calendar displays existing transactions', function () {
    // Create a transaction for today
    $transaction = Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'category_id' => $this->category->id,
        'description' => 'Test Grocery Purchase',
        'amount'      => -50.00,
        'date'        => now(),
        'type'        => 'expense',
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Should see the transaction
    $page->assertSee('Test Grocery Purchase')
         ->assertSee('-$50.00')
         ->assertSee('Groceries');
});

test('calendar shows correct balance calculations', function () {
    // Create some transactions
    Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'category_id' => $this->category->id,
        'amount'      => 500.00,
        'date'        => now()->subDay(),
        'type'        => 'income',
    ]);

    Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'category_id' => $this->category->id,
        'amount'      => -200.00,
        'date'        => now(),
        'type'        => 'expense',
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Should show running balance calculations
    $page->assertSee('$1,300.00') // Starting balance + income + expense
         ->assertPresent('[data-test="running-balance"]');
});

test('user can filter calendar by account', function () {
    // Create another account
    $savingsAccount = Account::factory()->for($this->user)->create([
        'name' => 'Savings',
        'type' => 'savings',
    ]);

    // Create transactions for different accounts
    Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'category_id' => $this->category->id,
        'description' => 'Checking Transaction',
        'amount'      => -100.00,
        'date'        => now(),
        'type'        => 'expense',
    ]);

    Transaction::factory()->create([
        'account_id'  => $savingsAccount->id,
        'category_id' => $this->category->id,
        'description' => 'Savings Transaction',
        'amount'      => 500.00,
        'date'        => now(),
        'type'        => 'income',
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Should see all transactions initially
    $page->assertSee('Checking Transaction')
         ->assertSee('Savings Transaction');

    // Filter by specific account
    if ($page->text('.account-filter') !== null) {
        $page->select('.account-filter', $this->account->id)
             ->assertSee('Checking Transaction')
             ->assertDontSee('Savings Transaction');
    }
});

test('calendar works on mobile devices', function () {
    $page = visit('/login')->on()->mobile();

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Calendar should adapt to mobile layout
    $page->assertSee('Calendar')
         ->assertPresent('[data-test="mobile-calendar"]');

    // Test mobile-specific navigation
    $page->click('[data-test="mobile-nav-next"]')
         ->wait(1000)
         ->click('[data-test="mobile-nav-prev"]')
         ->wait(1000);
});

test('user can access transaction form from calendar', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Click add transaction button
    $page->click('Add Transaction')
         ->wait(1000) // Wait for modal/form to appear
         ->assertSee('Add Transaction')
         ->assertPresent('[data-test="transaction-form"]');
});

test('calendar refreshes after transaction changes', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Add a new transaction through the form
    $page->click('Add Transaction')
         ->wait(1000)
         ->fill('description', 'Browser Test Transaction')
         ->fill('amount', '25.00')
         ->select('type', 'expense')
         ->click('Save');

    // Calendar should refresh and show new transaction
    $page->wait(2000) // Wait for form to close and calendar to refresh
         ->assertSee('Browser Test Transaction')
         ->assertSee('-$25.00');
});
