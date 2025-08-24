<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RecurringService;
use Carbon\Carbon;
use Livewire\Livewire;

test('recurring service creates multiple transactions for every 2 weeks frequency', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();

    $recurringService = app(RecurringService::class);

    $startDate = Carbon::now()->startOfDay();
    $endDate = $startDate->copy()->addMonths(2); // 2 months out

    $transactionData = [
        'account_id'       => $account->id,
        'type'             => 'expense',
        'amount'           => 500.00,
        'description'      => 'Bi-weekly expense',
        'transaction_date' => $startDate->format('Y-m-d'),
        'category_id'      => $category->id,
        'status'           => 'planned',
    ];

    $transactions = $recurringService->createRecurringTransactions(
        $user,
        $transactionData,
        'every-2-weeks',
        $endDate->format('Y-m-d')
    );

    // Should create 4-5 transactions over 2 months (every 2 weeks)
    expect($transactions->count())->toBeGreaterThanOrEqual(4);
    expect($transactions->count())->toBeLessThanOrEqual(5);

    // Verify each transaction is created with correct data
    $transactions->each(function ($transaction) use ($account, $category) {
        expect($transaction->account_id)->toBe($account->id);
        expect($transaction->type)->toBe('expense');
        expect((float) $transaction->amount)->toBe(500.00);
        expect($transaction->description)->toBe('Bi-weekly expense');
        expect($transaction->category_id)->toBe($category->id);
        expect($transaction->status)->toBe('planned');
    });

    // Verify dates are spaced 2 weeks apart
    $sortedTransactions = $transactions->sortBy('transaction_date');
    $previousDate = null;

    foreach ($sortedTransactions as $transaction) {
        if ($previousDate) {
            $daysDiff = Carbon::parse($previousDate)->diffInDays(Carbon::parse($transaction->transaction_date));
            expect((int) $daysDiff)->toBe(14); // Exactly 2 weeks apart
        }
        $previousDate = $transaction->transaction_date;
    }
});

test('recurring service creates correct number of monthly transactions', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();

    $recurringService = app(RecurringService::class);

    $startDate = Carbon::now()->startOfDay();
    $endDate = $startDate->copy()->addMonths(3); // 3 months out

    $transactionData = [
        'account_id'       => $account->id,
        'type'             => 'income',
        'amount'           => 1000.00,
        'description'      => 'Monthly salary',
        'transaction_date' => $startDate->format('Y-m-d'),
        'category_id'      => $category->id,
        'status'           => 'planned',
    ];

    $transactions = $recurringService->createRecurringTransactions(
        $user,
        $transactionData,
        'every-month',
        $endDate->format('Y-m-d')
    );

    // Should create 4 transactions (start date + 3 months)
    expect($transactions->count())->toBe(4);

    // Verify transaction types and amounts
    $transactions->each(function ($transaction) {
        expect($transaction->type)->toBe('income');
        expect((float) $transaction->amount)->toBe(1000.00);
        expect($transaction->description)->toBe('Monthly salary');
    });
});

test('recurring service handles single transaction when frequency is dont-repeat', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();

    $recurringService = app(RecurringService::class);

    $transactionData = [
        'account_id'       => $account->id,
        'type'             => 'expense',
        'amount'           => 100.00,
        'description'      => 'One-time expense',
        'transaction_date' => Carbon::now()->format('Y-m-d'),
        'category_id'      => $category->id,
        'status'           => 'entered',
    ];

    $transactions = $recurringService->createRecurringTransactions(
        $user,
        $transactionData,
        'dont-repeat'
    );

    expect($transactions->count())->toBe(1);
    expect($transactions->first()->description)->toBe('One-time expense');
});

test('recurring service calculates occurrence count correctly', function () {
    $recurringService = app(RecurringService::class);

    $startDate = Carbon::now()->format('Y-m-d');
    $endDate = Carbon::now()->addMonths(2)->format('Y-m-d');

    // Test bi-weekly over 2 months
    $biWeeklyCount = $recurringService->calculateOccurrenceCount(
        $startDate,
        'every-2-weeks',
        $endDate
    );

    expect($biWeeklyCount)->toBeGreaterThanOrEqual(4);
    expect($biWeeklyCount)->toBeLessThanOrEqual(5);

    // Test monthly over 3 months
    $monthlyEndDate = Carbon::now()->addMonths(3)->format('Y-m-d');
    $monthlyCount = $recurringService->calculateOccurrenceCount(
        $startDate,
        'every-month',
        $monthlyEndDate
    );

    expect($monthlyCount)->toBe(4);

    // Test daily over 1 week
    $dailyEndDate = Carbon::now()->addWeek()->format('Y-m-d');
    $dailyCount = $recurringService->calculateOccurrenceCount(
        $startDate,
        'everyday',
        $dailyEndDate
    );

    expect($dailyCount)->toBe(8); // Start date + 7 days = 8 transactions
});

test('transaction form creates multiple recurring transactions via livewire', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();

    $this->actingAs($user);

    $startDate = Carbon::now()->format('Y-m-d');
    $endDate = Carbon::now()->addMonths(2)->format('Y-m-d');

    Livewire::test('transaction-form')
        ->set('account_id', $account->id)
        ->set('type', 'expense')
        ->set('amount', 250.00)
        ->set('description', 'Recurring grocery shopping')
        ->set('transaction_date', $startDate)
        ->set('category_id', $category->id)
        ->set('status', 'planned')
        ->set('recurring_frequency', 'every-week')
        ->set('recurring_duration', 'until-date')
        ->set('recurring_end_date', $endDate)
        ->call('save')
        ->assertHasNoErrors();

    $transactions = Transaction::where('description', 'Recurring grocery shopping')->get();

    // Should create approximately 8 transactions over 2 months (weekly)
    expect($transactions->count())->toBeGreaterThanOrEqual(8);
    expect($transactions->count())->toBeLessThanOrEqual(9);

    // Verify all transactions have correct properties
    $transactions->each(function ($transaction) use ($account, $category) {
        expect($transaction->account_id)->toBe($account->id);
        expect($transaction->type)->toBe('expense');
        expect((float) $transaction->amount)->toBe(250.00);
        expect($transaction->category_id)->toBe($category->id);
        expect($transaction->status)->toBe('planned');
    });
});

test('recurring service respects safety limits', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();

    $recurringService = app(RecurringService::class);

    $transactionData = [
        'account_id'       => $account->id,
        'type'             => 'expense',
        'amount'           => 10.00,
        'description'      => 'Daily expense',
        'transaction_date' => Carbon::now()->format('Y-m-d'),
        'category_id'      => $category->id,
        'status'           => 'planned',
    ];

    // Try to create daily transactions for 2 years (would be ~730 transactions)
    // Should be limited by the safety limit of 100
    $transactions = $recurringService->createRecurringTransactions(
        $user,
        $transactionData,
        'everyday',
        Carbon::now()->addYears(2)->format('Y-m-d')
    );

    expect($transactions->count())->toBe(100);
});
