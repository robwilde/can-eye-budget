<?php

declare(strict_types=1);

use App\Livewire\CalendarViewSimple;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

covers(CalendarViewSimple::class);

beforeEach(function () {
    $this->user = User::factory()->createOne();
    $this->actingAs($this->user);
});

it('can select a specific date from calendar', function () {
    $testDate = '2024-07-15';

    Livewire::test(CalendarViewSimple::class)
        ->call('selectDate', $testDate)
        ->assertSet('currentDate', Carbon::parse($testDate))
        ->assertSet('view', 'day');
});

it('can select a specific month from year view', function () {
    $testDate = '2024-07-01';

    Livewire::test(CalendarViewSimple::class)
        ->call('selectMonth', $testDate)
        ->assertSet('currentDate', Carbon::parse($testDate))
        ->assertSet('view', 'month');
});

it('properly handles different date formats', function () {
    $testDate = '2024-12-25';

    $component = Livewire::test(CalendarViewSimple::class)
        ->call('selectDate', $testDate);

    expect($component->get('currentDate')->format('Y-m-d'))->toBe($testDate);
    expect($component->get('view'))->toBe('day');
});
