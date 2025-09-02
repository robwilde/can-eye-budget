<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use App\Livewire\AutomationRules;
use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('automation rules page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/automation');

    $response->assertSuccessful();
    $response->assertSeeLivewire(AutomationRules::class);
});

test('automation rules page shows empty state when no rules exist', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
            ->test(AutomationRules::class)
            ->assertSee('No automation rules yet')
            ->assertSee('Create your first rule');
});

test('can create a new rule', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    $account = Account::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
            ->test(AutomationRules::class)
            ->call('createRule')
            ->assertSet('showForm', true)
            ->set('categoryId', $category->id)
            ->set('accountId', $account->id)
            ->set('field', 'description')
            ->set('operator', 'contains')
            ->set('value', 'STARBUCKS')
            ->set('priority', 10)
            ->call('saveRule')
            ->assertSet('showForm', false);

    // Check the rule was created in the database
    expect(CategoryRule::count())->toBe(1);

    $rule = CategoryRule::first();
    expect($rule->category_id)
        ->toBe($category->id)
        ->and($rule->account_id)->toBe($account->id)
        ->and($rule->field)->toBe('description')
        ->and($rule->operator)->toBe('contains')
        ->and($rule->value)->toBe('STARBUCKS')
        ->and($rule->priority)->toBe(10);
});

test('can edit an existing rule', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    $newCategory = Category::factory()->create(['user_id' => $user->id]);

    $rule = CategoryRule::factory()->create([
        'category_id' => $category->id,
        'field'       => 'description',
        'operator'    => 'contains',
        'value'       => 'OLD_VALUE',
        'priority'    => 5,
    ]);

    Livewire::actingAs($user)
            ->test(AutomationRules::class)
            ->call('editRule', $rule->id)
            ->assertSet('showForm', true)
            ->assertSet('editingRuleId', $rule->id)
            ->assertSet('categoryId', $category->id)
            ->assertSet('value', 'OLD_VALUE')
            ->set('categoryId', $newCategory->id)
            ->set('value', 'NEW_VALUE')
            ->set('priority', 20)
            ->call('saveRule')
            ->assertSet('showForm', false);

    // Check the rule was updated in the database
    $rule->refresh();
    expect($rule->category_id)
        ->toBe($newCategory->id)
        ->and($rule->value)->toBe('NEW_VALUE')
        ->and($rule->priority)->toBe(20);
});

test('can delete a rule', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $rule = CategoryRule::factory()->create([
        'category_id' => $category->id,
    ]);

    expect(CategoryRule::count())->toBe(1);

    Livewire::actingAs($user)
            ->test(AutomationRules::class)
            ->call('deleteRule', $rule->id);

    // Check the rule was deleted from the database
    expect(CategoryRule::count())->toBe(0);
});

test('can search rules', function () {
    $user = User::factory()->create();
    $category1 = Category::factory()->create(['user_id' => $user->id, 'name' => 'Groceries']);
    $category2 = Category::factory()->create(['user_id' => $user->id, 'name' => 'Gas']);

    CategoryRule::factory()->create([
        'category_id' => $category1->id,
        'value'       => 'WHOLE_FOODS',
    ]);

    CategoryRule::factory()->create([
        'category_id' => $category2->id,
        'value'       => 'SHELL',
    ]);

    $component = Livewire::actingAs($user)
                         ->test(AutomationRules::class);

    // Should see both rules initially
    expect($component->get('categoryRules')->count())->toBe(2);

    // Search should filter results
    $component
        ->set('searchTerm', 'WHOLE')
        ->assertSet('searchTerm', 'WHOLE');

    expect($component->get('categoryRules')->count())
        ->toBe(1)
        ->and($component->get('categoryRules')->first()->value)->toBe('WHOLE_FOODS');
});

test('field operators update when field changes', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
                         ->test(AutomationRules::class)
                         ->call('createRule')
                         ->assertSet('field', 'description')
                         ->assertSet('operator', 'contains');

    $operators = $component->get('fieldOperators');
    expect($operators)
        ->toHaveKey('contains')
        ->and($operators)->toHaveKey('equals');

    $component->set('field', 'amount');

    $operators = $component->get('fieldOperators');
    expect($operators)
        ->toHaveKey('greater_than')
        ->and($operators)->toHaveKey('less_than')
        ->and($operators)->not->toHaveKey('contains');
});

test('validation works for required fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
            ->test(AutomationRules::class)
            ->call('createRule')
            ->set('categoryId')
            ->set('value', '')
            ->call('saveRule')
            ->assertHasErrors(['categoryId', 'value']);
});

test('can test a rule and show preview modal with array data', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Groceries']);

    $rule = CategoryRule::factory()->create([
        'category_id' => $category->id,
        'field'       => 'description',
        'operator'    => 'contains',
        'value'       => 'grocery',
        'priority'    => 1,
    ]);

    // Create some transactions that should match the rule
    Transaction::factory()->count(2)->create([
        'account_id'  => $account->id,
        'description' => 'Grocery Store Purchase',
        'amount'      => 50.00,
        'category_id' => null, // Uncategorized
    ]);

    $component = Livewire::actingAs($user)
                         ->test(AutomationRules::class)
                         ->call('testRule', $rule)
                         ->assertHasNoErrors();

    // Assert the modal is shown
    $component->assertSet('showPreviewModal', true);
    $component->assertSet('testingRuleId', $rule->id);

    // Assert testResults is an array with expected structure
    $testResults = $component->get('testResults');
    expect($testResults)
        ->toBeArray()
        ->and($testResults['totalMatches'])->toBe(2)
        ->and($testResults['uncategorizedMatches'])->toBe(2)
        ->and($testResults['alreadyCategorizedMatches'])->toBe(0)
        ->and($testResults['transactions'])->toBeArray()
        ->and($testResults['conflicts'])->toBeArray()
        ->and($testResults['hasConflicts'])->toBeFalse()
        ->and($testResults['affectedTransactionIds'])->toBeArray();
});

test('can test an unsaved rule from the form', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Groceries']);

    // Create some transactions that should match the unsaved rule
    Transaction::factory()->count(3)->create([
        'account_id'  => $account->id,
        'description' => 'Grocery Store Purchase',
        'amount'      => 50.00,
        'category_id' => null,
    ]);

    $component = Livewire::actingAs($user)
                         ->test(AutomationRules::class)
                         ->call('createRule')
                         ->set('categoryId', $category->id)
                         ->set('accountId', $account->id)
                         ->set('field', 'description')
                         ->set('operator', 'contains')
                         ->set('value', 'grocery')
                         ->set('priority', 10)
                         ->call('testUnsavedRule')
                         ->assertHasNoErrors();

    // Assert the preview modal is shown with results
    $component->assertSet('showPreviewModal', true);
    $component->assertSet('testingRuleId', null); // Should be null for unsaved rule

    $testResults = $component->get('testResults');
    expect($testResults)
        ->toBeArray()
        ->and($testResults['totalMatches'])->toBe(3)
        ->and($testResults['uncategorizedMatches'])->toBe(3);
});

test('can apply all rules and see detailed results modal', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Groceries']);

    // Create a rule
    CategoryRule::factory()->create([
        'category_id' => $category->id,
        'field'       => 'description',
        'operator'    => 'contains',
        'value'       => 'grocery',
        'priority'    => 1,
    ]);

    // Create transactions that should match
    Transaction::factory()->count(2)->create([
        'account_id'  => $account->id,
        'description' => 'Grocery Store Purchase',
        'amount'      => 50.00,
        'category_id' => null,
    ]);

    $component = Livewire::actingAs($user)
                         ->test(AutomationRules::class)
                         ->call('previewBulkApplication')
                         ->assertSet('showConfirmBulkModal', true)
                         ->call('applyRulesToExisting')
                         ->assertHasNoErrors();

    // Should show results modal instead of confirmation modal
    $component
        ->assertSet('showConfirmBulkModal', false)
        ->assertSet('showResultsModal', true);

    // Check detailed results structure
    $detailedResults = $component->get('detailedResults');
    expect($detailedResults)
        ->toBeArray()
        ->and($detailedResults['summary'])->toBeArray()
        ->and($detailedResults['rules_applied'])->toBeArray()
        ->and($detailedResults['timestamp'])->toBeString()
        ->and($detailedResults['summary']['categorized'])->toBe(2)
        ->and($detailedResults['summary']['processed'])->toBe(2)
        ->and($detailedResults['summary']['errors'])->toBe(0);
});

test('shows no matches message when unsaved rule has no matching transactions', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    // No transactions created that would match
    $component = Livewire::actingAs($user)
                         ->test(AutomationRules::class)
                         ->call('createRule')
                         ->set('categoryId', $category->id)
                         ->set('field', 'description')
                         ->set('operator', 'contains')
                         ->set('value', 'nonexistent')
                         ->set('priority', 10)
                         ->call('testUnsavedRule')
                         ->assertHasNoErrors();

    $component->assertSet('showPreviewModal', true);

    $testResults = $component->get('testResults');
    expect($testResults['totalMatches'])->toBe(0);
});

test('bulk application shows informative message when no rules match', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['user_id' => $user->id]);

    // Create a rule that won't match any transactions
    CategoryRule::factory()->create([
        'category_id' => $category->id,
        'field'       => 'description',
        'operator'    => 'contains',
        'value'       => 'nonexistent',
        'priority'    => 1,
    ]);

    // Create transactions that won't match
    Transaction::factory()->count(2)->create([
        'account_id'  => $account->id,
        'description' => 'Different Transaction',
        'amount'      => 50.00,
        'category_id' => null,
    ]);

    $component = Livewire::actingAs($user)
                         ->test(AutomationRules::class)
                         ->call('previewBulkApplication')
                         ->call('applyRulesToExisting')
                         ->assertHasNoErrors();

    $component->assertSet('showResultsModal', true);

    $detailedResults = $component->get('detailedResults');
    expect($detailedResults['summary']['categorized'])
        ->toBe(0)
        ->and($detailedResults['summary']['processed'])->toBe(2);
});
