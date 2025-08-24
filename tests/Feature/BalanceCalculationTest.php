<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Carbon\Carbon;

test('account current balance excludes planned transactions', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['initial_balance' => 1000.00]);
    $category = Category::factory()->for($user)->create();

    // Create an entered transaction that should affect balance
    Transaction::factory()->for($account)->create([
        'type'        => 'expense',
        'amount'      => 100.00,
        'status'      => 'entered',
        'category_id' => $category->id,
    ]);

    // Create a planned transaction that should NOT affect balance
    Transaction::factory()->for($account)->create([
        'type'        => 'expense',
        'amount'      => 200.00,
        'status'      => 'planned',
        'category_id' => $category->id,
    ]);

    // Balance should be: 1000.00 (initial) - 100.00 (entered expense) = 900.00
    // The planned expense of 200.00 should not affect the balance
    expect($account->getCurrentBalance())->toBe(900.00);
});

test('account current balance includes entered income but excludes planned income', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['initial_balance' => 500.00]);
    $category = Category::factory()->for($user)->create();

    // Create an entered income that should affect balance
    Transaction::factory()->for($account)->create([
        'type'        => 'income',
        'amount'      => 300.00,
        'status'      => 'entered',
        'category_id' => $category->id,
    ]);

    // Create a planned income that should NOT affect balance
    Transaction::factory()->for($account)->create([
        'type'        => 'income',
        'amount'      => 500.00,
        'status'      => 'planned',
        'category_id' => $category->id,
    ]);

    // Balance should be: 500.00 (initial) + 300.00 (entered income) = 800.00
    // The planned income of 500.00 should not affect the balance
    expect($account->getCurrentBalance())->toBe(800.00);
});

test('account current balance handles transfers correctly with status filtering', function () {
    $user = User::factory()->create();
    $fromAccount = Account::factory()->for($user)->create(['initial_balance' => 1000.00]);
    $toAccount = Account::factory()->for($user)->create(['initial_balance' => 500.00]);

    // Create an entered transfer (outgoing from source account)
    Transaction::factory()->for($fromAccount)->create([
        'type'                   => 'transfer',
        'amount'                 => 200.00,
        'status'                 => 'entered',
        'transfer_to_account_id' => $toAccount->id,
    ]);

    // Create a planned transfer (should NOT affect balances)
    Transaction::factory()->for($fromAccount)->create([
        'type'                   => 'transfer',
        'amount'                 => 300.00,
        'status'                 => 'planned',
        'transfer_to_account_id' => $toAccount->id,
    ]);

    // From account balance: 1000.00 (initial) - 200.00 (entered transfer) = 800.00
    expect($fromAccount->getCurrentBalance())->toBe(800.00);

    // To account balance: 500.00 (initial) + 200.00 (transfer in via transfersIn relationship) = 700.00
    expect($toAccount->getCurrentBalance())->toBe(700.00);
});

test('transaction service running balance excludes planned transactions', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['initial_balance' => 1000.00]);
    $category = Category::factory()->for($user)->create();
    $transactionService = app(TransactionService::class);

    $date = Carbon::now();

    // Create entered transactions before the date
    Transaction::factory()->for($account)->create([
        'type'             => 'expense',
        'amount'           => 150.00,
        'status'           => 'entered',
        'transaction_date' => $date->copy()->subDays(2),
        'category_id'      => $category->id,
    ]);

    Transaction::factory()->for($account)->create([
        'type'             => 'income',
        'amount'           => 50.00,
        'status'           => 'entered',
        'transaction_date' => $date->copy()->subDay(),
        'category_id'      => $category->id,
    ]);

    // Create planned transactions before the date (should be excluded)
    Transaction::factory()->for($account)->create([
        'type'             => 'expense',
        'amount'           => 300.00,
        'status'           => 'planned',
        'transaction_date' => $date->copy()->subDays(3),
        'category_id'      => $category->id,
    ]);

    Transaction::factory()->for($account)->create([
        'type'             => 'income',
        'amount'           => 100.00,
        'status'           => 'planned',
        'transaction_date' => $date->copy()->subDay(),
        'category_id'      => $category->id,
    ]);

    // Running balance should be: 1000.00 (initial) - 150.00 (entered expense) + 50.00 (entered income) = 900.00
    $runningBalance = $transactionService->getRunningBalance($account, $date);
    expect($runningBalance)->toBe(900.00);
});

test('mixed entered and planned transactions only affect balance when entered', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['initial_balance' => 1000.00]);
    $category = Category::factory()->for($user)->create();

    // Create multiple transactions with different statuses
    $transactions = [
        ['type' => 'expense', 'amount' => 100.00, 'status' => 'entered'],   // Should affect: -100
        ['type' => 'income', 'amount' => 200.00, 'status' => 'entered'],    // Should affect: +200
        ['type' => 'expense', 'amount' => 150.00, 'status' => 'planned'],   // Should NOT affect
        ['type' => 'income', 'amount' => 300.00, 'status' => 'planned'],    // Should NOT affect
        ['type' => 'transfer', 'amount' => 50.00, 'status' => 'entered'],   // Should affect: -50
        ['type' => 'transfer', 'amount' => 75.00, 'status' => 'planned'],   // Should NOT affect
    ];

    foreach ($transactions as $transactionData) {
        Transaction::factory()->for($account)->create(array_merge($transactionData, [
            'category_id' => $category->id,
        ]));
    }

    // Expected balance: 1000.00 (initial) - 100.00 (entered expense) + 200.00 (entered income) - 50.00 (entered transfer) = 1050.00
    expect($account->getCurrentBalance())->toBe(1050.00);
});

test('changing transaction status from planned to entered updates balance', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['initial_balance' => 1000.00]);
    $category = Category::factory()->for($user)->create();

    // Create a planned transaction
    $transaction = Transaction::factory()->for($account)->create([
        'type'        => 'expense',
        'amount'      => 200.00,
        'status'      => 'planned',
        'category_id' => $category->id,
    ]);

    // Balance should still be 1000.00 (planned transaction doesn't affect balance)
    expect($account->fresh()->getCurrentBalance())->toBe(1000.00);

    // Change status to entered
    $transaction->update(['status' => 'entered']);

    // Now balance should be affected: 1000.00 - 200.00 = 800.00
    expect($account->fresh()->getCurrentBalance())->toBe(800.00);
});

test('changing transaction status from entered to planned updates balance', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['initial_balance' => 1000.00]);
    $category = Category::factory()->for($user)->create();

    // Create an entered transaction
    $transaction = Transaction::factory()->for($account)->create([
        'type'        => 'income',
        'amount'      => 300.00,
        'status'      => 'entered',
        'category_id' => $category->id,
    ]);

    // Balance should be affected: 1000.00 + 300.00 = 1300.00
    expect($account->fresh()->getCurrentBalance())->toBe(1300.00);

    // Change status to planned
    $transaction->update(['status' => 'planned']);

    // Now balance should revert: 1000.00 (planned transaction doesn't affect balance)
    expect($account->fresh()->getCurrentBalance())->toBe(1000.00);
});
