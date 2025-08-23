<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = AccountCategory::factory()->create([
        'user_id'         => $this->user->id,
        'name'            => 'Personal Banking',
        'display_in_list' => true,
    ]);
});

test('user can access accounts page', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to accounts page
    $page->click('[href="/accounts"]')
         ->wait(1000);

    expect($page->text())->toContain('Accounts');
    expect($page->text())->toContain('Manage your financial accounts and categories');
});

test('user can see total balance on accounts page', function () {
    // Create test accounts with balances
    Account::factory()->create([
        'user_id'              => $this->user->id,
        'name'                 => 'Checking Account',
        'type'                 => 'checking',
        'initial_balance'      => 1500.00,
        'is_visible_in_totals' => true,
    ]);

    Account::factory()->create([
        'user_id'              => $this->user->id,
        'name'                 => 'Savings Account',
        'type'                 => 'savings',
        'initial_balance'      => 5000.00,
        'is_visible_in_totals' => true,
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/accounts"]')
         ->wait(1000);

    expect($page->text())->toContain('Total Balance');
    expect($page->text())->toContain('$6,500.00');
});

test('user can add new account', function () {
    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/accounts"]')
         ->wait(1000);

    // Click add account button
    $page->click('button:contains("Add Account")')
         ->wait(1000);

    // Fill out account form
    $page->fill('input[wire\\:model="name"]', 'Test Checking Account')
         ->select('select[wire\\:model.live="type"]', 'checking')
         ->fill('input[wire\\:model="initialBalance"]', '2500.50')
         ->select('select[wire\\:model="currency"]', 'USD');

    // Submit form
    $page->click('button:contains("Create Account")')
         ->wait(1000);

    expect($page->text())->toContain('Account created successfully');
    expect($page->text())->toContain('Test Checking Account');
});

test('user can add new account category', function () {
    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/accounts"]')
         ->wait(1000);

    // Click add category button
    $page->click('button:contains("Add Category")')
         ->wait(1000);

    // Fill out category form
    $page->fill('input[wire\\:model="name"]', 'Business Accounts')
         ->check('input[wire\\:model="displayInList"]')
         ->fill('input[wire\\:model="sortOrder"]', '10');

    // Submit form
    $page->click('button:contains("Create Category")')
         ->wait(1000);

    expect($page->text())->toContain('Category created successfully');
    expect($page->text())->toContain('Business Accounts');
});

test('credit card account shows available credit', function () {
    Account::factory()->create([
        'user_id'         => $this->user->id,
        'name'            => 'Credit Card',
        'type'            => 'credit',
        'initial_balance' => -500.00, // $500 owed
        'credit_limit'    => 2000.00,
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/accounts"]')
         ->wait(1000);

    expect($page->text())->toContain('Available Credit: $1,500.00');
});

test('user can edit existing account', function () {
    $account = Account::factory()->create([
        'user_id'         => $this->user->id,
        'name'            => 'Original Name',
        'type'            => 'checking',
        'initial_balance' => 1000.00,
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/accounts"]')
         ->wait(1000);

    // Click the dropdown menu for the account
    $page->click('[data-flux-dropdown-trigger]')
         ->wait(500)
         ->click('button:contains("Edit")')
         ->wait(1000);

    // Update the account name
    $page->fill('input[wire\\:model="name"]', 'Updated Account Name')
         ->click('button:contains("Update Account")')
         ->wait(1000);

    expect($page->text())->toContain('Account updated successfully');
    expect($page->text())->toContain('Updated Account Name');
});

test('accounts are properly grouped by category', function () {
    $businessCategory = AccountCategory::factory()->create([
        'user_id'         => $this->user->id,
        'name'            => 'Business',
        'display_in_list' => true,
        'sort_order'      => 1,
    ]);

    // Create accounts in different categories
    Account::factory()->create([
        'user_id'             => $this->user->id,
        'account_category_id' => $this->category->id,
        'name'                => 'Personal Checking',
        'type'                => 'checking',
        'initial_balance'     => 1000.00,
    ]);

    Account::factory()->create([
        'user_id'             => $this->user->id,
        'account_category_id' => $businessCategory->id,
        'name'                => 'Business Checking',
        'type'                => 'checking',
        'initial_balance'     => 5000.00,
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/accounts"]')
         ->wait(1000);

    expect($page->text())->toContain('Personal Banking');
    expect($page->text())->toContain('Business');
    expect($page->text())->toContain('Personal Checking');
    expect($page->text())->toContain('Business Checking');
});

test('uncategorized accounts appear in separate section', function () {
    Account::factory()->create([
        'user_id'             => $this->user->id,
        'account_category_id' => null,
        'name'                => 'Uncategorized Account',
        'type'                => 'savings',
        'initial_balance'     => 2500.00,
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/accounts"]')
         ->wait(1000);

    expect($page->text())->toContain('Uncategorized Accounts');
    expect($page->text())->toContain('Uncategorized Account');
});
