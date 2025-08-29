<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
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

test('calendar view simple defaults to current month', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('calendar-view-simple');

    // Should default to month view (from config)
    $component->assertSet('view', 'month');

    // Should default to current date
    $currentDate = $component->get('currentDate');
    expect($currentDate->isToday())->toBeTrue();
});

test('calendar view simple shows current month in month view', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('calendar-view-simple')
        ->call('setView', 'month');

    $currentDate = $component->get('currentDate');
    expect($currentDate->month)->toBe(now()->month);
    expect($currentDate->year)->toBe(now()->year);

    // Should display current month's name in title
    $monthName = now()->format('F Y');
    $component->assertSee($monthName);
});

test('calendar view simple week view shows current week range', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('calendar-view-simple')
        ->call('setView', 'week');

    // Get the current week range (Monday to Sunday - default Carbon behavior)
    $startOfWeek = now()->startOfWeek();
    $endOfWeek = now()->endOfWeek();

    // Should show week range in title
    $expectedTitle = 'Week of '.$startOfWeek->format('M j').' - '.$endOfWeek->format('M j, Y');
    $component->assertSee($expectedTitle);
});

test('calendar view simple day view shows current date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('calendar-view-simple')
        ->call('setView', 'day');

    $currentDate = $component->get('currentDate');
    expect($currentDate->isToday())->toBeTrue();

    // Should display today's date in title
    $todayTitle = now()->format('F j, Y');
    $component->assertSee($todayTitle);
});

test('calendar view simple year view shows current year', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('calendar-view-simple')
        ->call('setView', 'year');

    $currentDate = $component->get('currentDate');
    expect($currentDate->year)->toBe(now()->year);

    // Should display current year in title
    $yearTitle = now()->format('Y');
    $component->assertSee($yearTitle);
});

test('calendar view simple go to today button resets to current date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('calendar-view-simple');

    // Navigate to next period
    $component->call('nextPeriod');
    $nextDate = $component->get('currentDate');

    // Go back to today
    $component->call('goToToday');
    $currentDate = $component->get('currentDate');

    // Should be back to today
    expect($currentDate->isToday())->toBeTrue();
    expect($nextDate->isToday())->toBeFalse();
});
