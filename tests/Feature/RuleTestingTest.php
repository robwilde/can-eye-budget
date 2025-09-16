<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CategoryMatchingService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->for($this->user)->create(['name' => 'Test Account']);
    $this->category = Category::factory()->for($this->user)->create(['name' => 'Groceries']);
    $this->service = new CategoryMatchingService();

    $this->actingAs($this->user);
});

describe('Rule Testing Engine', function () {
    it('can test a rule and return matching transactions', function () {
        // Create a rule
        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'grocery',
            'priority'    => 1,
        ]);

        // Create transactions that should match
        Transaction::factory()->count(3)->create([
            'account_id'  => $this->account->id,
            'description' => 'Grocery Store Purchase',
            'amount'      => 50.00,
            'category_id' => null, // Uncategorized
        ]);

        // Create transactions that should not match
        Transaction::factory()->count(2)->create([
            'account_id'  => $this->account->id,
            'description' => 'Gas Station',
            'amount'      => 30.00,
            'category_id' => null,
        ]);

        // Test the rule
        $results = $this->service->testRule($rule);

        expect($results->totalMatches)
            ->toBe(3)
            ->and($results->uncategorizedMatches)->toBe(3)
            ->and($results->alreadyCategorizedMatches)->toBe(0)
            ->and($results->transactions)->toHaveCount(3)
            ->and($results->error)->toBeNull();
    });

    it('can identify re-categorizations when testing rules', function () {
        $otherCategory = Category::factory()->for($this->user)->create(['name' => 'Dining']);

        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'restaurant',
            'priority'    => 1,
        ]);

        // Create already categorized transaction that would match
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Restaurant Meal',
            'amount'      => 25.00,
            'category_id' => $otherCategory->id, // Already categorized
        ]);

        $results = $this->service->testRule($rule);

        expect($results->totalMatches)
            ->toBe(1)
            ->and($results->uncategorizedMatches)->toBe(0)
            ->and($results->alreadyCategorizedMatches)->toBe(1);
    });

    it('can detect conflicts between rules', function () {
        // Create first rule
        $rule1 = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'food',
            'priority'    => 2,
        ]);

        // Create conflicting rule (exact match)
        CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'food',
            'priority'    => 1,
        ]);

        // Create transaction that matches both
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Food Store',
            'amount'      => 40.00,
        ]);

        $results = $this->service->testRule($rule1);

        expect($results->hasConflicts())
            ->toBeTrue()
            ->and($results->conflicts)->toHaveCount(1)
            ->and($results->conflicts->first()->conflict_type)->toBe('exact_match');
    });

    it('respects account filtering when testing rules', function () {
        $otherAccount = Account::factory()->for($this->user)->create(['name' => 'Other Account']);

        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'account_id'  => $this->account->id, // Account-specific rule
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'coffee',
            'priority'    => 1,
        ]);

        // Create matching transaction in the specified account
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Coffee Shop',
            'amount'      => 5.00,
        ]);

        // Create matching transaction in a different account
        Transaction::factory()->create([
            'account_id'  => $otherAccount->id,
            'description' => 'Coffee Shop',
            'amount'      => 5.00,
        ]);

        $results = $this->service->testRule($rule);

        // Should only match the transaction from the specified account
        expect($results->totalMatches)
            ->toBe(1)
            ->and($results->transactions->first()->account_name)->toBe($this->account->name);
    });

    it('handles amount-based rules correctly', function () {
        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'amount',
            'operator'    => 'greater_than',
            'value'       => '100.00',
            'priority'    => 1,
        ]);

        // Create transactions with different amounts
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Large Purchase',
            'amount'      => 150.00,
        ]);

        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Small Purchase',
            'amount'      => 50.00,
        ]);

        $results = $this->service->testRule($rule);

        expect($results->totalMatches)
            ->toBe(1)
            ->and($results->transactions->first()->amount)->toBe(150.00);
    });

    it('returns empty results when rule has no matches', function () {
        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'nonexistent',
            'priority'    => 1,
        ]);

        // Create transactions that don't match
        Transaction::factory()->count(2)->create([
            'account_id'  => $this->account->id,
            'description' => 'Regular Purchase',
            'amount'      => 25.00,
        ]);

        $results = $this->service->testRule($rule);

        expect($results->totalMatches)
            ->toBe(0)
            ->and($results->transactions)->toBeEmpty()
            ->and($results->error)->toBeNull();
    });
});

describe('Multiple Rule Testing', function () {
    it('can test multiple rules together', function () {
        $rules = collect([
            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'grocery',
                'priority'    => 1,
            ]),
            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'market',
                'priority'    => 2,
            ]),
        ]);

        // Create matching transactions
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Grocery Store',
            'amount'      => 50.00,
        ]);

        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Market Purchase',
            'amount'      => 30.00,
        ]);

        $results = $this->service->testMultipleRules($rules);

        expect($results)
            ->toHaveCount(2)
            ->and($results->first()['rule']->id)->toBe($rules[0]->id)
            ->and($results->first()['results']->totalMatches)->toBe(1);
    });
});

describe('Conflict Detection', function () {
    it('can detect exact match conflicts', function () {
        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'test',
            'priority'    => 1,
        ]);

        // Create identical rule
        $conflictingRule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'test',
            'priority'    => 2,
        ]);

        // Create transactions that match both rules (required for conflict detection)
        Transaction::factory()->count(3)->create([
            'account_id'  => $this->account->id,
            'description' => 'Test transaction for conflict detection',
            'amount'      => 50.00,
            'category_id' => null,
        ]);

        $conflicts = $this->service->getConflictingRules($rule);

        expect($conflicts)
            ->toHaveCount(1)
            ->and($conflicts->first()->id)->toBe($conflictingRule->id);
    });

    it('can detect overlapping condition conflicts', function () {
        $differentCategory = Category::factory()->for($this->user)->create(['name' => 'Different Category']);

        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'grocery store',
            'priority'    => 1,
        ]);

        // Create rule with overlapping condition but different category
        $conflictingRule = CategoryRule::factory()->create([
            'category_id' => $differentCategory->id, // Different category
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'grocery',
            'priority'    => 2,
        ]);

        // Create transactions that match both rules (required for conflict detection)
        Transaction::factory()->count(2)->create([
            'account_id'  => $this->account->id,
            'description' => 'Grocery store purchase',
            'amount'      => 75.00,
            'category_id' => null,
        ]);

        $conflicts = $this->service->getConflictingRules($rule);

        expect($conflicts)
            ->toHaveCount(1)
            ->and($conflicts->first()->id)->toBe($conflictingRule->id);
    });

    it('can detect similar pattern conflicts', function () {
        $differentCategory = Category::factory()->for($this->user)->create(['name' => 'Different Category']);

        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains', // Changed to contains to match transactions
            'value'       => 'starbuck',
            'priority'    => 1,
        ]);

        // Create rule with very similar pattern (will trigger similarity threshold)
        $conflictingRule = CategoryRule::factory()->create([
            'category_id' => $differentCategory->id, // Different category
            'field'       => 'description',
            'operator'    => 'contains', // Changed to contains to match transactions
            'value'       => 'starbucks',
            'priority'    => 2,
        ]);

        // Create transactions that match both rules (starbuck matches both 'starbuck' and 'starbucks')
        Transaction::factory()->count(2)->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks coffee purchase',
            'amount'      => 5.50,
            'category_id' => null,
        ]);

        $conflicts = $this->service->getConflictingRules($rule);

        expect($conflicts)
            ->toHaveCount(1)
            ->and($conflicts->first()->id)->toBe($conflictingRule->id);
    });
});
