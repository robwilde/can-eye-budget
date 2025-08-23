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
    $this->secondAccount = Account::factory()->for($this->user)->create([
        'name'    => 'Savings',
        'type'    => 'savings',
        'balance' => 2000.00,
    ]);
    $this->category = Category::factory()->create([
        'name'    => 'Groceries',
        'user_id' => $this->user->id,
    ]);
});

test('user can create a new expense transaction', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Open transaction form
    $page->click('Add Transaction')
         ->wait(1000);

    // Fill out expense form
    $page->assertSee('Add Transaction')
         ->fill('[data-test="description"]', 'Grocery Shopping')
         ->fill('[data-test="amount"]', '75.50')
         ->select('[data-test="type"]', 'expense')
         ->select('[data-test="account"]', (string) $this->account->id)
         ->select('[data-test="category"]', (string) $this->category->id)
         ->click('[data-test="save-transaction"]');

    // Verify transaction was created
    $page->wait(2000) // Wait for form to close
         ->assertSee('Grocery Shopping')
         ->assertSee('-$75.50');

    // Verify in database
    expect(Transaction::where('description', 'Grocery Shopping')->exists())->toBeTrue();
});

test('user can create a new income transaction', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Open transaction form
    $page->click('Add Transaction')
         ->wait(1000);

    // Fill out income form
    $page->fill('[data-test="description"]', 'Freelance Payment')
         ->fill('[data-test="amount"]', '500.00')
         ->select('[data-test="type"]', 'income')
         ->select('[data-test="account"]', (string) $this->account->id)
         ->select('[data-test="category"]', (string) $this->category->id)
         ->click('[data-test="save-transaction"]');

    // Verify transaction was created
    $page->wait(2000)
         ->assertSee('Freelance Payment')
         ->assertSee('+$500.00');

    // Verify in database
    expect(Transaction::where('description', 'Freelance Payment')->exists())->toBeTrue();
});

test('user can create a transfer transaction', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Open transaction form
    $page->click('Add Transaction')
         ->wait(1000);

    // Fill out transfer form
    $page->fill('[data-test="description"]', 'Transfer to Savings')
         ->fill('[data-test="amount"]', '200.00')
         ->select('[data-test="type"]', 'transfer')
         ->select('[data-test="from-account"]', (string) $this->account->id)
         ->select('[data-test="to-account"]', (string) $this->secondAccount->id)
         ->click('[data-test="save-transaction"]');

    // Verify transfer was created
    $page->wait(2000)
         ->assertSee('Transfer to Savings');

    // Should create two transactions (debit and credit)
    expect(Transaction::where('description', 'Transfer to Savings')->count())->toBe(2);
});

test('user can edit existing transaction', function () {
    // Create existing transaction
    $transaction = Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'category_id' => $this->category->id,
        'description' => 'Original Description',
        'amount'      => -50.00,
        'type'        => 'expense',
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Click edit button on transaction
    $page->click('[data-test="edit-transaction-'.$transaction->id.'"]')
         ->wait(1000);

    // Modify transaction
    $page->clear('[data-test="description"]')
         ->fill('[data-test="description"]', 'Updated Description')
         ->clear('[data-test="amount"]')
         ->fill('[data-test="amount"]', '60.00')
         ->click('[data-test="save-transaction"]');

    // Verify changes
    $page->wait(2000)
         ->assertSee('Updated Description')
         ->assertSee('-$60.00')
         ->assertDontSee('Original Description');

    // Verify in database
    expect(Transaction::find($transaction->id)->description)->toBe('Updated Description');
});

test('user can delete transaction', function () {
    // Create existing transaction
    $transaction = Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'category_id' => $this->category->id,
        'description' => 'Transaction to Delete',
        'amount'      => -25.00,
        'type'        => 'expense',
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Should see the transaction initially
    $page->assertSee('Transaction to Delete');

    // Click delete button
    $page->click('[data-test="delete-transaction-'.$transaction->id.'"]')
         ->wait(1000);

    // Confirm deletion (if there's a confirmation modal)
    if ($page->text('.confirmation-modal') !== null) {
        $page->click('[data-test="confirm-delete"]');
    }

    // Verify transaction is removed
    $page->wait(2000)
         ->assertDontSee('Transaction to Delete');

    // Verify in database
    expect(Transaction::find($transaction->id))->toBeNull();
});

test('form validates required fields', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Open transaction form
    $page->click('Add Transaction')
         ->wait(1000);

    // Try to save without required fields
    $page->click('[data-test="save-transaction"]');

    // Should show validation errors
    $page->assertSee('The description field is required')
         ->assertSee('The amount field is required');
});

test('user can create new category from transaction form', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Open transaction form
    $page->click('Add Transaction')
         ->wait(1000);

    // Try to create new category
    $page->fill('[data-test="description"]', 'Test Transaction')
         ->fill('[data-test="amount"]', '25.00')
         ->select('[data-test="type"]', 'expense')
         ->select('[data-test="account"]', (string) $this->account->id)
         ->click('[data-test="new-category-button"]')
         ->wait(1000);

    // Fill new category form
    $page->fill('[data-test="new-category-name"]', 'New Test Category')
         ->click('[data-test="save-new-category"]')
         ->wait(1000);

    // Category should now be available
    $page->assertPresent('[data-test="category"] option[value*="New Test Category"]');

    // Complete the transaction
    $page->click('[data-test="save-transaction"]');

    // Verify category was created
    expect(Category::where('name', 'New Test Category')->where('user_id', $this->user->id)->exists())->toBeTrue();
});

test('transaction form works on mobile', function () {
    $page = visit('/login')->on()->mobile();

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Open transaction form
    $page->click('Add Transaction')
         ->wait(1000);

    // Should work on mobile layout
    $page->assertPresent('[data-test="mobile-transaction-form"]')
         ->fill('[data-test="description"]', 'Mobile Transaction')
         ->fill('[data-test="amount"]', '15.00')
         ->select('[data-test="type"]', 'expense')
         ->select('[data-test="account"]', (string) $this->account->id)
         ->select('[data-test="category"]', (string) $this->category->id)
         ->click('[data-test="save-transaction"]');

    // Verify transaction was created
    $page->wait(2000)
         ->assertSee('Mobile Transaction');
});

test('form remembers last used values', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Create first transaction
    $page->click('Add Transaction')
         ->wait(1000)
         ->fill('[data-test="description"]', 'First Transaction')
         ->fill('[data-test="amount"]', '100.00')
         ->select('[data-test="type"]', 'expense')
         ->select('[data-test="account"]', (string) $this->account->id)
         ->select('[data-test="category"]', (string) $this->category->id)
         ->click('[data-test="save-transaction"]')
         ->wait(2000);

    // Open form again
    $page->click('Add Transaction')
         ->wait(1000);

    // Should remember account and category selections
    $page->assertSelected('[data-test="account"]', (string) $this->account->id)
         ->assertSelected('[data-test="category"]', (string) $this->category->id);
});
