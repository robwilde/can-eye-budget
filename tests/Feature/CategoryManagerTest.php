<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

test('category manager component can be rendered', function () {
    $user = User::factory()
                ->create();
    $this->actingAs($user);

    Livewire::test('category-manager')
            ->assertStatus(200)
            ->assertSee('Category Management')
            ->assertSee('Add Category')
            ->assertSee('Add Rule');
});

test('category manager can create a new category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('category-manager')
                         ->call('openCategoryForm')
                         ->set('categoryName', 'Test Category')
                         ->call('saveCategory');

    // Check for validation errors
    $component->assertHasNoErrors();
    $component->assertDispatched('category-saved');

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name'    => 'Test Category',
    ]);
});

test('category manager can edit existing category', function () {
    $user = User::factory()
                ->create();
    $category = Category::factory()
                        ->for($user)
                        ->create([
                            'name'  => 'Original Name',
                            'color' => '#000000',
                        ]);

    $this->actingAs($user);

    Livewire::test('category-manager')
            ->call('openCategoryForm', $category->id)
            ->set('categoryName', 'Updated Name')
            ->set('categoryColor', '#ff0000')
            ->call('saveCategory')
            ->assertDispatched('category-saved');

    $category->refresh();
    expect($category->name)
        ->toBe('Updated Name')
        ->and($category->color)
        ->toBe('#ff0000');
});

test('category manager can create hierarchical categories', function () {
    $user = User::factory()
                ->create();
    $parent = Category::factory()
                      ->for($user)
                      ->create(['name' => 'Parent Category']);

    $this->actingAs($user);

    Livewire::test('category-manager')
            ->call('openCategoryForm')
            ->set('categoryName', 'Child Category')
            ->set('parentCategoryId', $parent->id)
            ->call('saveCategory')
            ->assertDispatched('category-saved');

    $child = Category::where('name', 'Child Category')
                     ->first();
    expect($child->parent_id)
        ->toBe($parent->id)
        ->and($child->full_name)
        ->toBe('Parent Category > Child Category');
});

test('category manager can delete category without transactions', function () {
    $user = User::factory()
                ->create();
    $category = Category::factory()
                        ->for($user)
                        ->create();

    $this->actingAs($user);

    Livewire::test('category-manager')
            ->call('deleteCategory', $category->id)
            ->assertDispatched('category-deleted');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('category manager prevents deletion of category with transactions', function () {
    $user = User::factory()
                ->create();
    $account = Account::factory()
                      ->for($user)
                      ->create();
    $category = Category::factory()
                        ->for($user)
                        ->create();

    // Create a transaction for this category
    Transaction::factory()
               ->for($account)
               ->create([
                   'category_id' => $category->id,
               ]);

    $this->actingAs($user);

    Livewire::test('category-manager')
            ->call('deleteCategory', $category->id)
            ->assertHasErrors(['general']);

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('category manager can create categorization rules', function () {
    $user = User::factory()
                ->create();
    $category = Category::factory()
                        ->for($user)
                        ->create();

    $this->actingAs($user);

    Livewire::test('category-manager')
            ->call('openRuleForm')
            ->set('ruleCategoryId', $category->id)
            ->set('ruleField', 'description')
            ->set('ruleOperator', 'contains')
            ->set('ruleValue', 'Starbucks')
            ->set('rulePriority', 1)
            ->call('saveRule')
            ->assertDispatched('rule-saved');

    $this->assertDatabaseHas('category_rules', [
        'category_id' => $category->id,
        'field'       => 'description',
        'operator'    => 'contains',
        'value'       => 'Starbucks',
        'priority'    => 1,
    ]);
});

test('category manager can edit categorization rules', function () {
    $user = User::factory()
                ->create();
    $category = Category::factory()
                        ->for($user)
                        ->create();
    $rule = CategoryRule::factory()
                        ->for($category)
                        ->create([
                            'field'    => 'description',
                            'operator' => 'contains',
                            'value'    => 'Old Value',
                            'priority' => 1,
                        ]);

    $this->actingAs($user);

    Livewire::test('category-manager')
            ->call('openRuleForm', $rule->id)
            ->set('ruleValue', 'New Value')
            ->set('rulePriority', 5)
            ->call('saveRule')
            ->assertDispatched('rule-saved');

    $rule->refresh();
    expect($rule->value)
        ->toBe('New Value')
        ->and($rule->priority)
        ->toBe(5);
});

test('category manager can delete categorization rules', function () {
    $user = User::factory()
                ->create();
    $category = Category::factory()
                        ->for($user)
                        ->create();
    $rule = CategoryRule::factory()
                        ->for($category)
                        ->create();

    $this->actingAs($user);

    Livewire::test('category-manager')
            ->call('deleteRule', $rule->id)
            ->assertDispatched('rule-deleted');

    $this->assertDatabaseMissing('category_rules', ['id' => $rule->id]);
});

test('category manager displays categories hierarchically', function () {
    $user = User::factory()
                ->create();
    $parent = Category::factory()
                      ->for($user)
                      ->create(['name' => 'Parent']);
    Category::factory()
            ->for($user)
            ->create(['name' => 'Child', 'parent_id' => $parent->id]);

    $this->actingAs($user);

    Livewire::test('category-manager')
            ->assertSee('Parent')
            ->assertSee('Child');
});

test('category manager validates required fields', function () {
    $user = User::factory()
                ->create();
    $this->actingAs($user);

    Livewire::test('category-manager')
            ->call('openCategoryForm')
            ->set('categoryName', '')
            ->call('saveCategory')
            ->assertHasErrors(['categoryName']);
});
