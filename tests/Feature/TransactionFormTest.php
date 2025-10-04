<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

test('transaction form can create a new transaction', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();

    $this->actingAs($user);

    $component = Livewire::test('transaction-form')
                         ->set('account_id', $account->id)
                         ->set('type', 'expense')
                         ->set('amount', 50.00)
                         ->set('description', 'Test Expense')
                         ->set('transaction_date', Carbon::now()->format('Y-m-d'))
                         ->set('category_id', $category->id)
                         ->call('save');

    $component->assertHasNoErrors();

    expect(Transaction::count())->toBe(1);

    $transaction = Transaction::first();
    expect($transaction->description)
        ->toBe('Test Expense')
        ->and((float) $transaction->amount)->toBe(50.00)
        ->and($transaction->type)->toBe('expense')
        ->and($transaction->account_id)->toBe($account->id)
        ->and($transaction->category_id)->toBe($category->id);
});

test('transaction form validates required fields', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('transaction-form')
            ->call('save')
            ->assertHasErrors(['account_id', 'description']);
});

test('transaction form can create transfer transaction', function () {
    $user = User::factory()->create();
    $fromAccount = Account::factory()->for($user)->create(['name' => 'Checking']);
    $toAccount = Account::factory()->for($user)->create(['name' => 'Savings']);

    $this->actingAs($user);

    Livewire::test('transaction-form')
            ->set('account_id', $fromAccount->id)
            ->set('type', 'transfer')
            ->set('amount', 200.00)
            ->set('description', 'Transfer to savings')
            ->set('transaction_date', Carbon::now()->format('Y-m-d'))
            ->set('transfer_to_account_id', $toAccount->id)
            ->call('save')
            ->assertHasNoErrors();

    expect(Transaction::count())->toBe(2); // Source and destination transactions

    $sourceTransaction = Transaction::where('account_id', $fromAccount->id)->first();
    expect($sourceTransaction->type)
        ->toBe('transfer')
        ->and($sourceTransaction->transfer_to_account_id)->toBe($toAccount->id);

    $destinationTransaction = Transaction::where('account_id', $toAccount->id)->first();
    expect($destinationTransaction->type)
        ->toBe('transfer')
        ->and((float) $destinationTransaction->amount)->toBe(200.00)
        ->and($sourceTransaction->transfer_pair_id)->not()->toBeNull()
        ->and($destinationTransaction->transfer_pair_id)->not()->toBeNull()
        ->and($sourceTransaction->transfer_pair_id)->toBe($destinationTransaction->transfer_pair_id)
        ->and($sourceTransaction->description)->toBe("Transfer to $toAccount->name")
        ->and($destinationTransaction->description)->toBe("Transfer from $fromAccount->name");
    // Test transfer pair relationship
    // Test improved descriptions
});

test('transaction form can create new category', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test('transaction-form')
            ->set('showCategoryForm', true)
            ->set('newCategoryName', 'New Category')
            ->set('newCategoryColor', '#ff0000')
            ->call('createCategory')
            ->assertHasNoErrors()
            ->assertSet('showCategoryForm', false);

    expect(Category::count())->toBe(1);

    $category = Category::first();
    expect($category->name)
        ->toBe('New Category')
        ->and($category->color)->toBe('#ff0000')
        ->and($category->user_id)->toBe($user->id);
});

test('transaction form can edit existing transaction', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $transaction = Transaction::factory()->for($account)->create([
        'description' => 'Original Description',
        'amount'      => 100.00,
        'type'        => 'expense',
    ]);

    $this->actingAs($user);

    Livewire::test('transaction-form')
            ->call('open', $transaction)
            ->assertSet('description', 'Original Description')
            ->assertSet('amount', 100.00)
            ->assertSet('mode', 'edit')
            ->set('description', 'Updated Description')
            ->set('amount', 150.00)
            ->call('save')
            ->assertHasNoErrors();

    $transaction->refresh();
    expect($transaction->description)
        ->toBe('Updated Description')
        ->and((float) $transaction->amount)->toBe(150.00);
});

test('transaction form button text changes based on type', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->create();

    $this->actingAs($user);

    // Test button text in create mode (default status is 'planned' = 'Add')
    $component = Livewire::test('transaction-form')
                         ->call('open')
                         ->set('type', 'expense');

    $component->assertSee('Add Expense');

    // Test with entered status shows 'Enter'
    $component->set('status', 'entered')
              ->assertSee('Enter Expense');

    // Test different transaction types
    $component->set('type', 'income')
              ->assertSee('Enter Income');

    $component->set('type', 'transfer')
              ->assertSee('Enter Transfer');
});

test('transaction form button text shows Update in edit mode', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $transaction = Transaction::factory()->for($account)->create([
        'type'   => 'expense',
        'status' => 'entered',
    ]);

    $this->actingAs($user);

    Livewire::test('transaction-form')
            ->call('open', $transaction)
            ->assertSet('mode', 'edit')
            ->assertSee('Confirm Expense'); // Edit mode with 'entered' status shows 'Confirm'
});
