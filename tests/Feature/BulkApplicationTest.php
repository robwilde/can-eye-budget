<?php

/** @noinspection PhpUnhandledExceptionInspection */

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

describe('Bulk Rule Application', function () {
    it('can apply rules to uncategorized transactions', function () {
        // Create a rule
        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'grocery',
            'priority'    => 1,
        ]);

        // Create uncategorized transactions that should match
        $transactions = Transaction::factory()->count(3)->create([
            'account_id'  => $this->account->id,
            'description' => 'Grocery Store Purchase',
            'amount'      => 50.00,
            'category_id' => null, // Uncategorized
        ]);

        // Create transaction that shouldn't match
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Gas Station',
            'amount'      => 30.00,
            'category_id' => null,
        ]);

        $results = $this->service->applyRulesToTransactions($this->user);

        expect($results['processed'])
            ->toBe(4)
            ->and($results['categorized'])->toBe(3)
            ->and($results['recategorized'])->toBe(0)
            ->and($results['errors'])->toBe(0);

        // Verify transactions were categorized and tracked
        foreach ($transactions as $transaction) {
            $transaction->refresh();
            expect($transaction->category_id)
                ->toBe($this->category->id)
                ->and($transaction->applied_rule_id)->toBe($rule->id)
                ->and($transaction->auto_categorized_at)->not->toBeNull();
        }
    });

    it('can recategorize already categorized transactions', function () {
        $oldCategory = Category::factory()->for($this->user)->create(['name' => 'Dining']);
        $newCategory = Category::factory()->for($this->user)->create(['name' => 'Groceries']);

        // Create a rule with higher priority
        $rule = CategoryRule::factory()->create([
            'category_id' => $newCategory->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'market',
            'priority'    => 1,
        ]);

        // Create already categorized transaction
        $transaction = Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Farmers Market',
            'amount'      => 25.00,
            'category_id' => $oldCategory->id,
        ]);

        $results = $this->service->applyRulesToTransactions($this->user, [$rule->id]);

        expect($results['processed'])
            ->toBe(1)
            ->and($results['categorized'])->toBe(0)
            ->and($results['recategorized'])->toBe(1);

        $transaction->refresh();
        expect($transaction->category_id)
            ->toBe($newCategory->id)
            ->and($transaction->applied_rule_id)->toBe($rule->id);
    });

    it('applies rules in priority order', function () {
        $category1 = Category::factory()->for($this->user)->create(['name' => 'Category 1']);
        $category2 = Category::factory()->for($this->user)->create(['name' => 'Category 2']);

        // Create rules with different priorities - lower number = higher priority
        CategoryRule::factory()->create([
            'category_id' => $category1->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'store',
            'priority'    => 2, // Lower priority (higher number)
        ]);

        $highPriorityRule = CategoryRule::factory()->create([
            'category_id' => $category2->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'grocery',
            'priority'    => 1, // Higher priority (lower number)
        ]);

        // Create transaction that matches both rules
        $transaction = Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Grocery Store Purchase',
            'amount'      => 40.00,
            'category_id' => null,
        ]);

        $results = $this->service->applyRulesToTransactions($this->user);

        expect($results['categorized'])->toBe(1);

        $transaction->refresh();
        // Should be categorized by the higher priority rule
        expect($transaction->category_id)
            ->toBe($category2->id)
            ->and($transaction->applied_rule_id)->toBe($highPriorityRule->id);
    });

    it('respects account-specific rules', function () {
        $account1 = $this->account;
        $account2 = Account::factory()->for($this->user)->create(['name' => 'Account 2']);

        // Create account-specific rule
        CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'account_id'  => $account1->id, // Only for account1
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'coffee',
            'priority'    => 1,
        ]);

        // Create matching transactions in both accounts
        $transaction1 = Transaction::factory()->create([
            'account_id'  => $account1->id,
            'description' => 'Coffee Shop',
            'amount'      => 5.00,
            'category_id' => null,
        ]);

        $transaction2 = Transaction::factory()->create([
            'account_id'  => $account2->id,
            'description' => 'Coffee Shop',
            'amount'      => 5.00,
            'category_id' => null,
        ]);

        $results = $this->service->applyRulesToTransactions($this->user);

        expect($results['categorized'])->toBe(1); // Only one should be categorized

        $transaction1->refresh();
        $transaction2->refresh();

        // Only the transaction in the specified account should be categorized
        expect($transaction1->category_id)
            ->toBe($this->category->id)
            ->and($transaction2->category_id)->toBeNull();
    });

    it('processes transactions without errors in normal operation', function () {
        // Create a rule and transaction that should match
        CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'test',
            'priority'    => 1,
        ]);

        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Test Transaction',
            'amount'      => 25.00,
            'category_id' => null,
        ]);

        $results = $this->service->applyRulesToTransactions($this->user);

        // The operation should complete successfully
        expect($results['processed'])
            ->toBe(1)
            ->and($results['categorized'])->toBe(1)
            ->and($results['errors'])->toBe(0)
            ->and($results['error_messages'])->toBeEmpty();
    });

    it('limits the number of transactions processed', function () {
        CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'test',
            'priority'    => 1,
        ]);

        // Create more transactions than the limit
        Transaction::factory()->count(150)->create([
            'account_id'  => $this->account->id,
            'description' => 'Test Transaction',
            'amount'      => 25.00,
            'category_id' => null,
        ]);

        $results = $this->service->applyRulesToTransactions($this->user, [], 100); // Limit to 100

        expect($results['processed'])
            ->toBe(100)
            ->and($results['categorized'])->toBe(100);
    });

    it('tracks rule application history', function () {
        $rule = CategoryRule::factory()->create([
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'grocery',
            'priority'    => 1,
        ]);

        $transaction = Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Grocery Store',
            'amount'      => 30.00,
            'category_id' => null,
        ]);

        $this->service->applyRulesToTransactions($this->user);

        $transaction->refresh();

        expect($transaction->applied_rule_id)
            ->toBe($rule->id)
            ->and($transaction->auto_categorized_at)->not
            ->toBeNull()
            ->and($transaction->isAutoCategorized())->toBeTrue();
    });
});

describe('Rule Application to Specific Rules', function () {
    it('can apply only specific rules when rule IDs are provided', function () {
        $category1 = Category::factory()->for($this->user)->create(['name' => 'Category 1']);
        $category2 = Category::factory()->for($this->user)->create(['name' => 'Category 2']);

        $rule1 = CategoryRule::factory()->create([
            'category_id' => $category1->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'coffee',
            'priority'    => 1,
        ]);

        CategoryRule::factory()->create([
            'category_id' => $category2->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'lunch',
            'priority'    => 1,
        ]);

        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Coffee Shop',
            'amount'      => 5.00,
            'category_id' => null,
        ]);

        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Lunch Restaurant',
            'amount'      => 15.00,
            'category_id' => null,
        ]);

        // Apply only rule1
        $results = $this->service->applyRulesToTransactions($this->user, [$rule1->id]);

        expect($results['categorized'])->toBe(1);

        $transactions = Transaction::where('account_id', $this->account->id)->get();
        $coffeeTransaction = $transactions->where('description', 'Coffee Shop')->first();
        $lunchTransaction = $transactions->where('description', 'Lunch Restaurant')->first();

        expect($coffeeTransaction->category_id)
            ->toBe($category1->id)
            ->and($lunchTransaction->category_id)->toBeNull();
        // Should not be categorized
    });
});
