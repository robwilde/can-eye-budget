<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CategoryMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
covers(CategoryMatchingService::class);

describe('CategoryMatchingService', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create(['user_id' => $this->user->id]);
        $this->category = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Test Category']);
        $this->service = new CategoryMatchingService();
    });

    describe('findMatchingCategory', function () {
        test('returns matching category when rule matches', function () {
            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $result = $this->service->findMatchingCategory(
                $this->user,
                'Netflix Monthly Subscription',
                15.99,
                $this->account->id,
            );

            expect($result)->not
                ->toBeNull()
                ->and($result->id)->toBe($this->category->id);
        });

        test('returns null when no rule matches', function () {
            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $result = $this->service->findMatchingCategory(
                $this->user,
                'Amazon Prime Subscription',
                12.99,
                $this->account->id,
            );

            expect($result)->toBeNull();
        });

        test('returns highest priority matching rule', function () {
            $highPriorityCategory = Category::factory()->create([
                'user_id' => $this->user->id,
                'name'    => 'High Priority Category',
            ]);

            // Create two rules that both match, but with different priorities
            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Subscription',
                'priority'    => 10,
            ]);

            CategoryRule::factory()->create([
                'category_id' => $highPriorityCategory->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 1, // Higher priority (lower number)
            ]);

            $result = $this->service->findMatchingCategory(
                $this->user,
                'Netflix Subscription',
                15.99,
                $this->account->id,
            );

            expect($result->id)->toBe($highPriorityCategory->id);
        });

        test('account-specific rules only match for correct account', function () {
            $otherAccount = Account::factory()->create(['user_id' => $this->user->id]);

            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            // Should match for the correct account
            $result = $this->service->findMatchingCategory(
                $this->user,
                'Netflix Subscription',
                15.99,
                $this->account->id,
            );
            expect($result)->not->toBeNull();

            // Should not match for a different account
            $result = $this->service->findMatchingCategory(
                $this->user,
                'Netflix Subscription',
                15.99,
                $otherAccount->id,
            );
            expect($result)->toBeNull();
        });

        test('global rules match for any account', function () {
            $otherAccount = Account::factory()->create(['user_id' => $this->user->id]);

            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => null, // Global rule
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            // Should match for any account
            $result1 = $this->service->findMatchingCategory(
                $this->user,
                'Netflix Subscription',
                15.99,
                $this->account->id,
            );
            expect($result1)->not->toBeNull();

            $result2 = $this->service->findMatchingCategory(
                $this->user,
                'Netflix Subscription',
                15.99,
                $otherAccount->id,
            );
            expect($result2)->not->toBeNull();
        });
    });

    describe('testRule', function () {
        test('returns correct match count for existing transactions', function () {
            // Create some test transactions
            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Netflix Monthly Payment',
                'amount'           => 15.99,
                'transaction_date' => now()->subDays(),
            ]);

            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Netflix Annual Payment',
                'amount'           => 120.00,
                'transaction_date' => now()->subDays(2),
            ]);

            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Amazon Prime Payment',
                'amount'           => 12.99,
                'transaction_date' => now()->subDays(3),
            ]);

            $rule = CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $result = $this->service->testRule($rule);

            expect($result->totalMatches)
                ->toBe(2)
                ->and($result->transactions)->toHaveCount(2)
                ->and($result->totalAmount)->toBe(135.99);
        });

        test('respects account filtering in test rule', function () {
            $otherAccount = Account::factory()->create(['user_id' => $this->user->id]);

            // Create transactions in both accounts
            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Netflix Payment',
                'amount'           => 15.99,
                'transaction_date' => now()->subDays(),
            ]);

            Transaction::factory()->create([
                'account_id'       => $otherAccount->id,
                'description'      => 'Netflix Payment',
                'amount'           => 15.99,
                'transaction_date' => now()->subDays(2),
            ]);

            // Rule specific to first account
            $rule = CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $result = $this->service->testRule($rule);

            expect($result->totalMatches)->toBe(1);
        });

        test('handles amount-based rules correctly', function () {
            // Create transactions with different amounts
            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Test Transaction',
                'amount'           => 50.00,
                'transaction_date' => now()->subDays(),
            ]);

            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Test Transaction',
                'amount'           => 150.00,
                'transaction_date' => now()->subDays(2),
            ]);

            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Test Transaction',
                'amount'           => 25.00,
                'transaction_date' => now()->subDays(3),
            ]);

            $rule = CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'amount',
                'operator'    => 'greater_than',
                'value'       => '100.00',
                'priority'    => 10,
            ]);

            $result = $this->service->testRule($rule);

            expect($result->totalMatches)
                ->toBe(1)
                ->and($result->totalAmount)->toBe(150.00);
        });

        test('respects transaction limit parameter', function () {
            // Create more transactions than the limit
            for ($i = 0; $i < 10; $i++) {
                Transaction::factory()->create([
                    'account_id'       => $this->account->id,
                    'description'      => 'Netflix Payment '.$i,
                    'amount'           => 15.99,
                    'transaction_date' => now()->subDays($i),
                ]);
            }

            $rule = CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $result = $this->service->testRule($rule, 5); // Limit to 5 transactions

            // Should only check the 5 most recent transactions
            expect($result->totalMatches)->toBe(5);
        });
    });

    describe('getConflictingRules', function () {
        test('identifies rules that would match same transaction', function () {
            $category2 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Category 2']);

            $rule1 = CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $rule2 = CategoryRule::factory()->create([
                'category_id' => $category2->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Subscription',
                'priority'    => 20,
            ]);

            // Create a transaction that would match both rules
            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Netflix Subscription',
                'amount'           => 15.99,
                'transaction_date' => now()->subDays(),
            ]);

            $conflicts = $this->service->getConflictingRules($rule1);

            expect($conflicts)
                ->toHaveCount(1)
                ->and($conflicts->first()->id)->toBe($rule2->id);
        });

        test('does not identify non-conflicting rules', function () {
            $category2 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Category 2']);

            $rule1 = CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            CategoryRule::factory()->create([
                'category_id' => $category2->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Amazon',
                'priority'    => 20,
            ]);

            $conflicts = $this->service->getConflictingRules($rule1);

            expect($conflicts)->toHaveCount(0);
        });
    });

    describe('applyRulesToTransactions', function () {
        test('applies matching rules to uncategorized transactions', function () {
            $transaction = Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Netflix Payment',
                'amount'           => 15.99,
                'category_id'      => null, // Uncategorized
                'transaction_date' => now()->subDays(),
            ]);

            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $result = $this->service->applyRulesToTransactions($this->user);

            expect($result['categorized'])
                ->toBe(1)
                ->and($result['recategorized'])->toBe(0)
                ->and($result['errors'])->toBe(0);

            // Verify transaction was categorized
            $transaction->refresh();
            expect($transaction->category_id)->toBe($this->category->id);
        });

        test('recategorizes already categorized transactions when they have lower priority', function () {
            $lowPriorityCategory = Category::factory()->create([
                'user_id' => $this->user->id,
                'name'    => 'Low Priority Category',
            ]);

            $transaction = Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Netflix Payment',
                'amount'           => 15.99,
                'category_id'      => $lowPriorityCategory->id,
                'transaction_date' => now()->subDays(),
            ]);

            // Create a higher priority rule (lower number = higher priority)
            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 5, // Higher priority
            ]);

            $result = $this->service->applyRulesToTransactions($this->user);

            expect($result['categorized'])
                ->toBe(0)
                ->and($result['recategorized'])->toBe(1)
                ->and($result['errors'])->toBe(0);

            // Verify transaction was recategorized
            $transaction->refresh();
            expect($transaction->category_id)->toBe($this->category->id);
        });

        test('respects transaction limit parameter', function () {
            // Create multiple uncategorized transactions
            for ($i = 0; $i < 10; $i++) {
                Transaction::factory()->create([
                    'account_id'       => $this->account->id,
                    'description'      => 'Netflix Payment '.$i,
                    'amount'           => 15.99,
                    'category_id'      => null,
                    'transaction_date' => now()->subDays($i),
                ]);
            }

            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $result = $this->service->applyRulesToTransactions($this->user, [], 5); // Limit to 5

            expect($result['categorized'])->toBe(5);
        });

        test('applies only specific rules when rule IDs provided', function () {
            $category2 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Category 2']);

            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Netflix Payment',
                'amount'           => 15.99,
                'category_id'      => null,
                'transaction_date' => now()->subDays(),
            ]);

            Transaction::factory()->create([
                'account_id'       => $this->account->id,
                'description'      => 'Amazon Payment',
                'amount'           => 12.99,
                'category_id'      => null,
                'transaction_date' => now()->subDays(2),
            ]);

            $rule1 = CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            CategoryRule::factory()->create([
                'category_id' => $category2->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Amazon',
                'priority'    => 10,
            ]);

            // Apply only the Netflix rule
            $result = $this->service->applyRulesToTransactions($this->user, [$rule1->id]);

            expect($result['categorized'])->toBe(1); // Only Netflix transaction should be categorized
        });
    });

    describe('Edge Cases and Error Handling', function () {
        test('handles empty transaction list gracefully', function () {
            $rule = CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $result = $this->service->testRule($rule);

            expect($result->totalMatches)
                ->toBe(0)
                ->and($result->transactions)->toHaveCount(0)
                ->and($result->totalAmount)->toBe(0.0);
        });

        test('handles invalid rule gracefully', function () {
            $rule = new CategoryRule([
                'category_id' => 999,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'invalid_operator',
                'value'       => 'test',
                'priority'    => 10,
            ]);

            $result = $this->service->testRule($rule);

            expect($result->totalMatches)->toBe(0);
        });

        test('findMatchingCategory returns null for non-existent user rules', function () {
            $otherUser = User::factory()->create();

            CategoryRule::factory()->create([
                'category_id' => $this->category->id,
                'account_id'  => $this->account->id,
                'field'       => 'description',
                'operator'    => 'contains',
                'value'       => 'Netflix',
                'priority'    => 10,
            ]);

            $result = $this->service->findMatchingCategory(
                $otherUser, // Different user
                'Netflix Payment',
                15.99,
                $this->account->id,
            );

            expect($result)->toBeNull();
        });
    });
});
