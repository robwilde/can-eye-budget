<?php

declare(strict_types=1);

use App\Livewire\CalendarViewSimple;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

uses()->group('dashboard', 'calendar');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create(['user_id' => $this->user->id]);
    $this->category = Category::factory()->create(['user_id' => $this->user->id]);
});

test('calendar view simple component renders correctly', function () {
    $this->actingAs($this->user);

    Livewire::test(CalendarViewSimple::class)
        ->assertStatus(200)
        ->assertSee('Planned')
        ->assertSee('Entered');
});

test('planned vs entered calculations work correctly', function () {
    $this->actingAs($this->user);

    // Create planned transactions
    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'income',
        'amount'           => 1000,
        'status'           => 'planned',
        'transaction_date' => Carbon::now(),
    ]);

    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'expense',
        'amount'           => 500,
        'status'           => 'planned',
        'transaction_date' => Carbon::now(),
    ]);

    // Create entered transactions
    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'income',
        'amount'           => 800,
        'status'           => 'entered',
        'transaction_date' => Carbon::now(),
    ]);

    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'expense',
        'amount'           => 300,
        'status'           => 'entered',
        'transaction_date' => Carbon::now(),
    ]);

    $component = Livewire::test(CalendarViewSimple::class);
    $periodTotals = $component->get('periodTotals');

    expect($periodTotals['planned']['income'])->toBe(1000.0);
    expect($periodTotals['planned']['expenses'])->toBe(500.0);
    expect($periodTotals['planned']['net'])->toBe(500.0);

    expect($periodTotals['entered']['income'])->toBe(800.0);
    expect($periodTotals['entered']['expenses'])->toBe(300.0);
    expect($periodTotals['entered']['net'])->toBe(500.0);

    expect($periodTotals['percentage_saved'])->toBe(100.0);
});

test('navigation buttons update calculations', function () {
    $this->actingAs($this->user);

    // Create transaction for current month
    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'income',
        'amount'           => 1000,
        'status'           => 'planned',
        'transaction_date' => Carbon::now(),
    ]);

    // Create transaction for next month
    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'income',
        'amount'           => 2000,
        'status'           => 'planned',
        'transaction_date' => Carbon::now()->addMonth(),
    ]);

    $component = Livewire::test(CalendarViewSimple::class);

    // Current month should show $1000
    $periodTotals = $component->get('periodTotals');
    expect($periodTotals['planned']['income'])->toBe(1000.0);

    // Navigate to next month
    $component->call('nextPeriod');

    // Next month should show $2000
    $periodTotals = $component->get('periodTotals');
    expect($periodTotals['planned']['income'])->toBe(2000.0);

    // Navigate back to current month
    $component->call('previousPeriod');

    // Should be back to $1000
    $periodTotals = $component->get('periodTotals');
    expect($periodTotals['planned']['income'])->toBe(1000.0);
});

test('visual distinction shows planned transactions prominently and entered with reduced alpha', function () {
    $this->actingAs($this->user);

    $plannedTransaction = Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'expense',
        'amount'           => 100,
        'status'           => 'planned',
        'description'      => 'Planned expense',
        'transaction_date' => Carbon::now(),
    ]);

    $enteredTransaction = Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'expense',
        'amount'           => 200,
        'status'           => 'entered',
        'description'      => 'Entered expense',
        'transaction_date' => Carbon::now(),
    ]);

    $component = Livewire::test(CalendarViewSimple::class);

    // Planned transactions should have font-bold class (prominent)
    $component->assertSeeHtml('font-bold');

    // Entered transactions should have opacity-70 class (reduced alpha)
    $component->assertSeeHtml('opacity-70');

    // Should see both transactions
    $component->assertSee('Planned expense');
    $component->assertSee('Entered expense');
});

test('go to today functionality works', function () {
    $this->actingAs($this->user);

    $component = Livewire::test(CalendarViewSimple::class);

    // Navigate to a different month
    $component->call('nextPeriod');

    // Go back to today
    $component->call('goToToday');

    // Should be at current month
    $currentDate = $component->get('currentDate');
    expect($currentDate->format('Y-m'))->toBe(Carbon::now()->format('Y-m'));
});

test('view switching works correctly', function () {
    $this->actingAs($this->user);

    $component = Livewire::test(CalendarViewSimple::class);

    // Test switching to different views
    $component->call('setView', 'day');
    expect($component->get('view'))->toBe('day');

    $component->call('setView', 'week');
    expect($component->get('view'))->toBe('week');

    $component->call('setView', 'month');
    expect($component->get('view'))->toBe('month');

    $component->call('setView', 'year');
    expect($component->get('view'))->toBe('year');
});

test('percentage saved calculation works correctly', function () {
    $this->actingAs($this->user);

    // Planned: $1000 income - $600 expenses = $400 net
    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'income',
        'amount'           => 1000,
        'status'           => 'planned',
        'transaction_date' => Carbon::now(),
    ]);

    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'expense',
        'amount'           => 600,
        'status'           => 'planned',
        'transaction_date' => Carbon::now(),
    ]);

    // Entered: $800 income - $400 expenses = $400 net
    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'income',
        'amount'           => 800,
        'status'           => 'entered',
        'transaction_date' => Carbon::now(),
    ]);

    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'expense',
        'amount'           => 400,
        'status'           => 'entered',
        'transaction_date' => Carbon::now(),
    ]);

    $component = Livewire::test(CalendarViewSimple::class);
    $periodTotals = $component->get('periodTotals');

    // Entered net / Planned net * 100 = 400/400 * 100 = 100%
    expect($periodTotals['percentage_saved'])->toBe(100.0);
});

test('transfers are excluded from income expense totals', function () {
    $this->actingAs($this->user);

    $destinationAccount = Account::factory()->create(['user_id' => $this->user->id]);

    // Create income, expense, and transfer
    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'income',
        'amount'           => 1000,
        'status'           => 'planned',
        'transaction_date' => Carbon::now(),
    ]);

    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'expense',
        'amount'           => 500,
        'status'           => 'planned',
        'transaction_date' => Carbon::now(),
    ]);

    Transaction::factory()->create([
        'account_id'             => $this->account->id,
        'type'                   => 'transfer',
        'amount'                 => 200,
        'status'                 => 'planned',
        'transfer_to_account_id' => $destinationAccount->id,
        'transaction_date'       => Carbon::now(),
    ]);

    $component = Livewire::test(CalendarViewSimple::class);
    $periodTotals = $component->get('periodTotals');

    // Transfers should not affect income/expense totals
    expect($periodTotals['planned']['income'])->toBe(1000.0);
    expect($periodTotals['planned']['expenses'])->toBe(500.0);
    expect($periodTotals['planned']['net'])->toBe(500.0);
});

test('account filtering works correctly', function () {
    $this->actingAs($this->user);

    $secondAccount = Account::factory()->create(['user_id' => $this->user->id]);

    // Create transactions in different accounts
    Transaction::factory()->create([
        'account_id'       => $this->account->id,
        'type'             => 'income',
        'amount'           => 1000,
        'status'           => 'planned',
        'transaction_date' => Carbon::now(),
    ]);

    Transaction::factory()->create([
        'account_id'       => $secondAccount->id,
        'type'             => 'income',
        'amount'           => 500,
        'status'           => 'planned',
        'transaction_date' => Carbon::now(),
    ]);

    $component = Livewire::test(CalendarViewSimple::class);

    // Without filtering - should see both
    $periodTotals = $component->get('periodTotals');
    expect($periodTotals['planned']['income'])->toBe(1500.0);

    // Filter by first account
    $component->call('selectAccount', $this->account->id);
    $periodTotals = $component->get('periodTotals');
    expect($periodTotals['planned']['income'])->toBe(1000.0);

    // Filter by second account
    $component->call('selectAccount', $secondAccount->id);
    $periodTotals = $component->get('periodTotals');
    expect($periodTotals['planned']['income'])->toBe(500.0);

    // Clear filter
    $component->call('selectAccount', null);
    $periodTotals = $component->get('periodTotals');
    expect($periodTotals['planned']['income'])->toBe(1500.0);
});
