<?php

declare(strict_types=1);

use App\Livewire\TransactionForm;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

uses()->group('transaction-form', 'status');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create(['user_id' => $this->user->id]);
});

test('new transactions default to planned status', function () {
    $this->actingAs($this->user);

    $component = Livewire::test(TransactionForm::class);
    $component->call('open');

    expect($component->get('status'))->toBe('planned');
});

test('transactions can be created with planned status', function () {
    $this->actingAs($this->user);

    Livewire::test(TransactionForm::class)
        ->call('open')
        ->set('account_id', $this->account->id)
        ->set('type', 'expense')
        ->set('amount', 100.50)
        ->set('description', 'Test planned expense')
        ->set('status', 'planned')
        ->call('save');

    $transaction = Transaction::latest()->first();
    expect($transaction->status)->toBe('planned');
    expect($transaction->isPlanned())->toBeTrue();
    expect($transaction->isEntered())->toBeFalse();
});

test('transactions can be created with entered status', function () {
    $this->actingAs($this->user);

    Livewire::test(TransactionForm::class)
        ->call('open')
        ->set('account_id', $this->account->id)
        ->set('type', 'expense')
        ->set('amount', 100.50)
        ->set('description', 'Test entered expense')
        ->set('status', 'entered')
        ->call('save');

    $transaction = Transaction::latest()->first();
    expect($transaction->status)->toBe('entered');
    expect($transaction->isEntered())->toBeTrue();
    expect($transaction->isPlanned())->toBeFalse();
});

test('existing planned transaction can be updated to entered', function () {
    $this->actingAs($this->user);

    $transaction = Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'type'        => 'expense',
        'amount'      => 100,
        'description' => 'Original planned transaction',
        'status'      => 'planned',
    ]);

    Livewire::test(TransactionForm::class)
        ->call('open', $transaction)
        ->set('status', 'entered')
        ->call('save');

    $transaction->refresh();
    expect($transaction->status)->toBe('entered');
    expect($transaction->isEntered())->toBeTrue();
    expect($transaction->isPlanned())->toBeFalse();
});

test('transaction form button text changes based on status', function () {
    $this->actingAs($this->user);

    // Test new planned transaction
    $component = Livewire::test(TransactionForm::class);
    $component->call('open')
        ->set('type', 'expense')
        ->set('status', 'planned');

    $component->assertSee('Add Expense');

    // Test new entered transaction
    $component->set('status', 'entered');
    $component->assertSee('Enter Expense');
});

test('transaction form button text for edit mode changes based on status', function () {
    $this->actingAs($this->user);

    $plannedTransaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'status'     => 'planned',
        'type'       => 'expense',
    ]);

    $enteredTransaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'status'     => 'entered',
        'type'       => 'expense',
    ]);

    // Test editing planned transaction
    $component = Livewire::test(TransactionForm::class);
    $component->call('open', $plannedTransaction);
    $component->assertSee('Update Expense');

    // Test editing entered transaction (should show "Confirm")
    $component->call('open', $enteredTransaction);
    $component->assertSee('Confirm Expense');
});

test('status field is required and validated', function () {
    $this->actingAs($this->user);

    Livewire::test(TransactionForm::class)
        ->call('open')
        ->set('account_id', $this->account->id)
        ->set('type', 'expense')
        ->set('amount', 100.50)
        ->set('description', 'Test expense')
        ->set('status', '') // Invalid empty status
        ->call('save')
        ->assertHasErrors(['status']);

    Livewire::test(TransactionForm::class)
        ->call('open')
        ->set('account_id', $this->account->id)
        ->set('type', 'expense')
        ->set('amount', 100.50)
        ->set('description', 'Test expense')
        ->set('status', 'invalid') // Invalid status value
        ->call('save')
        ->assertHasErrors(['status']);
});

test('status radio buttons render correctly in form', function () {
    $this->actingAs($this->user);

    $component = Livewire::test(TransactionForm::class);
    $component->call('open');

    // Should see the status radio buttons
    $component->assertSee('Transaction Status:');
    $component->assertSee('Planned (Future/Budgeted)');
    $component->assertSee('Entered (Confirmed/Reconciled)');

    // Planned should be selected by default
    expect($component->get('status'))->toBe('planned');
});
