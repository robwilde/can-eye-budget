<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

test('calendar view component can be rendered', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test('calendar-view')
            ->assertStatus(200)
            ->assertSee('Add Transaction')
            ->assertSee($account->name);
});

test('calendar view can switch between different views', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test('calendar-view')
            ->assertSet('view', 'month')
            ->call('setView', 'day')
            ->assertSet('view', 'day')
            ->call('setView', 'week')
            ->assertSet('view', 'week')
            ->call('setView', 'year')
            ->assertSet('view', 'year');
});

test('calendar view can navigate between periods', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('calendar-view');
    $originalDate = $component->get('currentDate');

    $component->call('nextPeriod');
    expect($component->get('currentDate'))->not->toEqual($originalDate);

    $component->call('goToToday');
    expect($component->get('currentDate')->isToday())->toBeTrue();
});

test('calendar view displays transactions correctly', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();

    $transaction = Transaction::factory()->for($account)->create([
        'category_id'      => $category->id,
        'description'      => 'Test Transaction',
        'amount'           => 100.00,
        'type'             => 'income',
        'transaction_date' => now()->format('Y-m-d'), // Ensure it's stored as a date without time
    ]);

    $this->actingAs($user);

    $component = Livewire::test('calendar-view');

    // Month view should display:
    // 1. Account name (in account balance section)
    // 2. Transaction amount in income summary
    // 3. Transaction description in tooltip (as title attribute)
    $component->assertSee($account->name) // Account name in balance section
            ->assertSee('+$100') // Income amount in day summary
            ->assertSeeHtml('title="Test Transaction"'); // Description in tooltip
});
