<?php

/** @noinspection PhpUndefinedFieldInspection */

declare(strict_types=1);

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(Transaction::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create(['user_id' => $this->user->id]);
});

describe('Description search scopes', function () {
    test('uniqueDescriptions returns distinct descriptions with usage counts', function () {
        // Create transactions with duplicate descriptions
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Purchase',
        ]);
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Purchase',
        ]);
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Amazon Order',
        ]);

        // Get aggregated results with computed usage_count from SQL GROUP BY
        $aggregatedResults = Transaction::uniqueDescriptions()->get();

        expect($aggregatedResults)->toHaveCount(2);

        // Find specific description in aggregated results
        $starbucksUsageData = $aggregatedResults->firstWhere('description', 'Starbucks Purchase');
        // usage_count is computed by COUNT(*) GROUP BY description, not a model attribute
        expect($starbucksUsageData->usage_count)->toBe(2);

        $amazonUsageData = $aggregatedResults->firstWhere('description', 'Amazon Order');
        expect($amazonUsageData->usage_count)->toBe(1);
    });

    test('uniqueDescriptions filters by account when provided', function () {
        $otherAccount = Account::factory()->create(['user_id' => $this->user->id]);

        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Account 1 Transaction',
        ]);
        Transaction::factory()->create([
            'account_id'  => $otherAccount->id,
            'description' => 'Account 2 Transaction',
        ]);

        $results = Transaction::uniqueDescriptions($this->account->id)->get();

        expect($results)
            ->toHaveCount(1)
            ->and($results->first()->description)->toBe('Account 1 Transaction');
    });

    test('uniqueDescriptions ignores empty descriptions', function () {
        // Note: NULL descriptions aren't allowed by database constraint, so we only test empty strings
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => '',
        ]);
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => '   ', // whitespace only
        ]);
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Valid Description',
        ]);

        $results = Transaction::uniqueDescriptions()->get();

        expect($results)
            ->toHaveCount(1)
            ->and($results->first()->description)->toBe('Valid Description');
    });

    test('descriptionSearch finds matching descriptions with relevance scoring', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee Shop', // exact word match should score high
        ]);
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Downtown Location', // starts with match
        ]);
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Coffee at Starbucks', // contains match
        ]);
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Amazon Purchase', // should not match
        ]);

        $results = Transaction::descriptionSearch('Starbucks')->get();

        expect($results)
            ->toHaveCount(3)
            ->and($results->pluck('description'))->not
            ->toContain('Amazon Purchase')
            ->and($results->first()->relevance_score)->toBeGreaterThanOrEqual(2);
    });

    test('descriptionSearch is case insensitive', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee',
        ]);

        $results = Transaction::descriptionSearch('STARBUCKS')->get();

        expect($results)
            ->toHaveCount(1)
            ->and($results->first()->description)->toBe('Starbucks Coffee');
    });

    test('descriptionSearch returns empty for empty search term', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Any Description',
        ]);

        $results = Transaction::descriptionSearch('')->get();

        expect($results)->toBeEmpty();
    });

    test('descriptionSearch limits results to 10', function () {
        // Create 15 transactions with similar descriptions
        for ($i = 1; $i <= 15; $i++) {
            Transaction::factory()->create([
                'account_id'  => $this->account->id,
                'description' => "Coffee Shop #$i",
            ]);
        }

        $results = Transaction::descriptionSearch('Coffee')->get();

        expect($results)->toHaveCount(10);
    });

    test('descriptionSearch filters by account when provided', function () {
        $otherAccount = Account::factory()->create(['user_id' => $this->user->id]);

        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Coffee Shop A',
        ]);
        Transaction::factory()->create([
            'account_id'  => $otherAccount->id,
            'description' => 'Coffee Shop B',
        ]);

        $results = Transaction::descriptionSearch('Coffee', $this->account->id)->get();

        expect($results)
            ->toHaveCount(1)
            ->and($results->first()->description)->toBe('Coffee Shop A');
    });
});
