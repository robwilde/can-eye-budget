<?php

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
        'category_id' => $category->id,
        'description' => 'Test Transaction',
        'amount' => 100.00,
        'type' => 'income',
        'transaction_date' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test('calendar-view')
            ->assertSee('Test Transaction')
            ->assertSee('$100.00')
            ->assertSee($account->name)
            ->assertSee($category->name);
});
