<?php

declare(strict_types=1);

use App\Livewire\AutomationRules;
use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryRule;
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
    expect($rule->category_id)->toBe($category->id);
    expect($rule->account_id)->toBe($account->id);
    expect($rule->field)->toBe('description');
    expect($rule->operator)->toBe('contains');
    expect($rule->value)->toBe('STARBUCKS');
    expect($rule->priority)->toBe(10);
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
    expect($rule->category_id)->toBe($newCategory->id);
    expect($rule->value)->toBe('NEW_VALUE');
    expect($rule->priority)->toBe(20);
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
    $component->set('searchTerm', 'WHOLE')
        ->assertSet('searchTerm', 'WHOLE');

    expect($component->get('categoryRules')->count())->toBe(1);
    expect($component->get('categoryRules')->first()->value)->toBe('WHOLE_FOODS');
});

test('field operators update when field changes', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(AutomationRules::class)
        ->call('createRule')
        ->assertSet('field', 'description')
        ->assertSet('operator', 'contains');

    $operators = $component->get('fieldOperators');
    expect($operators)->toHaveKey('contains');
    expect($operators)->toHaveKey('equals');

    $component->set('field', 'amount');

    $operators = $component->get('fieldOperators');
    expect($operators)->toHaveKey('greater_than');
    expect($operators)->toHaveKey('less_than');
    expect($operators)->not->toHaveKey('contains');
});

test('validation works for required fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AutomationRules::class)
        ->call('createRule')
        ->set('categoryId', null)
        ->set('value', '')
        ->call('saveRule')
        ->assertHasErrors(['categoryId', 'value']);
});
