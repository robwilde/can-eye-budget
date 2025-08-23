<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

test('user can access categories page', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to categories page
    $page->click('[href="/categories"]')
         ->wait(1000);

    expect($page->text())->toContain('Transaction Categories');
    expect($page->text())->toContain('Organize your transactions with hierarchical categories');
});

test('user can add simple category', function () {
    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    // Add a simple category
    $page->fill('input[wire\\:model.live="newCategoryName"]', 'Food')
         ->click('button:contains("Add Category")')
         ->wait(1000);

    expect($page->text())->toContain('Category created successfully');
    expect($page->text())->toContain('Food');
});

test('user can add hierarchical category using slash notation', function () {
    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    // Add a hierarchical category
    $page->fill('input[wire\\:model.live="newCategoryName"]', 'Food/Groceries')
         ->click('button:contains("Add Category")')
         ->wait(1000);

    expect($page->text())->toContain('Category created successfully');
    expect($page->text())->toContain('Food');
    expect($page->text())->toContain('Groceries');
});

test('categories show transaction count', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Test Category',
    ]);

    // Create transactions for this category
    Transaction::factory()->count(3)->create([
        'account_id'  => $this->account->id,
        'category_id' => $category->id,
        'type'        => 'expense',
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    expect($page->text())->toContain('Test Category');
    expect($page->text())->toContain('3 transactions');
});

test('user can search categories', function () {
    Category::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Food',
    ]);

    Category::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Transportation',
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    // Both categories should be visible initially
    expect($page->text())->toContain('Food');
    expect($page->text())->toContain('Transportation');

    // Search for specific category
    $page->fill('input[wire\\:model.live.debounce.300ms="searchTerm"]', 'Food')
         ->wait(1000);

    expect($page->text())->toContain('Food');
    expect($page->text())->not->toContain('Transportation');
});

test('user can delete category without transactions', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Deletable Category',
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    expect($page->text())->toContain('Deletable Category');

    // Click delete button and confirm
    $page->click('button[wire\\:click="deleteCategory('.$category->id.')"]')
         ->acceptDialog()
         ->wait(1000);

    expect($page->text())->toContain('Category deleted successfully');
    expect($page->text())->not->toContain('Deletable Category');
});

test('user cannot delete category with transactions', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Category With Transactions',
    ]);

    // Add a transaction to this category
    Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'category_id' => $category->id,
        'type'        => 'expense',
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    expect($page->text())->toContain('Category With Transactions');
    expect($page->text())->toContain('1 transactions');

    // Delete button should not be present for categories with transactions
    expect($page->hasElement('button[wire\\:click="deleteCategory('.$category->id.')"]'))->toBeFalse();
});

test('system shows suggestions while typing category name', function () {
    // Create existing categories
    Category::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Food',
    ]);

    $groceriesCategory = Category::factory()->create([
        'user_id'   => $this->user->id,
        'name'      => 'Groceries',
        'parent_id' => null,
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    // Start typing a category name that matches existing ones
    $page->fill('input[wire\\:model.live="newCategoryName"]', 'Foo')
         ->wait(500);

    // Should see suggestions
    expect($page->text())->toContain('Existing similar categories');
    expect($page->text())->toContain('Food');
});

test('hierarchical categories show proper indentation', function () {
    $parentCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Food',
    ]);

    $childCategory = Category::factory()->create([
        'user_id'   => $this->user->id,
        'name'      => 'Groceries',
        'parent_id' => $parentCategory->id,
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    expect($page->text())->toContain('Food');
    expect($page->text())->toContain('Groceries');

    // Check that child category has visual indentation
    expect($page->hasElement('.ml-6'))->toBeTrue();
});

test('empty state shows helpful message', function () {
    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    expect($page->text())->toContain('No categories found');
    expect($page->text())->toContain('Create your first category above to get started');
});

test('search with no results shows appropriate message', function () {
    Category::factory()->create([
        'user_id' => $this->user->id,
        'name'    => 'Food',
    ]);

    $page = visit('/login');

    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    $page->click('[href="/categories"]')
         ->wait(1000);

    // Search for non-existent category
    $page->fill('input[wire\\:model.live.debounce.300ms="searchTerm"]', 'NonExistentCategory')
         ->wait(1000);

    expect($page->text())->toContain('No categories found');
    expect($page->text())->toContain('Try adjusting your search term');
});
