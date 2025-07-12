<?php

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

    Livewire::test('transaction-form')
        ->set('account_id', $account->id)
        ->set('type', 'expense')
        ->set('amount', 50.00)
        ->set('description', 'Test Expense')
        ->set('transaction_date', Carbon::now()->format('Y-m-d'))
        ->set('category_id', $category->id)
        ->call('save')
        ->assertHasNoErrors();

    expect(Transaction::count())->toBe(1);

    $transaction = Transaction::first();
    expect($transaction->description)->toBe('Test Expense');
    expect($transaction->amount)->toBe(50.00);
    expect($transaction->type)->toBe('expense');
    expect($transaction->account_id)->toBe($account->id);
    expect($transaction->category_id)->toBe($category->id);
});

test('transaction form validates required fields', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('transaction-form')
        ->call('save')
        ->assertHasErrors(['account_id', 'amount', 'description']);
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
    expect($sourceTransaction->type)->toBe('transfer');
    expect($sourceTransaction->transfer_to_account_id)->toBe($toAccount->id);

    $destinationTransaction = Transaction::where('account_id', $toAccount->id)->first();
    expect($destinationTransaction->type)->toBe('income');
    expect($destinationTransaction->amount)->toBe(200.00);
});

test('transaction form can create new category', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

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
    expect($category->name)->toBe('New Category');
    expect($category->color)->toBe('#ff0000');
    expect($category->user_id)->toBe($user->id);
});

test('transaction form can edit existing transaction', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $transaction = Transaction::factory()->for($account)->create([
        'description' => 'Original Description',
        'amount' => 100.00,
        'type' => 'expense',
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
    expect($transaction->description)->toBe('Updated Description');
    expect($transaction->amount)->toBe(150.00);
});
