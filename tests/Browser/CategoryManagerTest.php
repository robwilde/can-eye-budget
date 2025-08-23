<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->for($this->user)->create();
    $this->parentCategory = Category::factory()->create([
        'name'      => 'Parent Category',
        'user_id'   => $this->user->id,
        'parent_id' => null,
    ]);
});

test('user can access category manager', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager (assuming it's in settings or main navigation)
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    $page->assertSee('Category Manager')
         ->assertSee('Add Category')
         ->assertSee($this->parentCategory->name);
});

test('user can create new parent category', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Create new category
    $page->click('[data-test="add-category"]')
         ->wait(1000)
         ->fill('[data-test="category-name"]', 'New Parent Category')
         ->select('[data-test="category-color"]', 'blue')
         ->select('[data-test="category-icon"]', 'home')
         ->click('[data-test="save-category"]');

    // Verify category appears in list
    $page->wait(2000)
         ->assertSee('New Parent Category');

    // Verify in database
    expect(Category::where('name', 'New Parent Category')->where('user_id', $this->user->id)->exists())->toBeTrue();
});

test('user can create subcategory', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Create subcategory
    $page->click('[data-test="add-category"]')
         ->wait(1000)
         ->fill('[data-test="category-name"]', 'Subcategory')
         ->select('[data-test="parent-category"]', (string) $this->parentCategory->id)
         ->select('[data-test="category-color"]', 'green')
         ->select('[data-test="category-icon"]', 'tag')
         ->click('[data-test="save-category"]');

    // Verify subcategory appears under parent
    $page->wait(2000)
         ->assertSee('Subcategory')
         ->assertSeeIn('[data-test="category-tree-'.$this->parentCategory->id.'"]', 'Subcategory');

    // Verify in database with correct parent
    $subcategory = Category::where('name', 'Subcategory')->where('user_id', $this->user->id)->first();
    expect($subcategory)->not->toBeNull();
    expect($subcategory->parent_id)->toBe($this->parentCategory->id);
});

test('user can edit existing category', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Edit existing category
    $page->click('[data-test="edit-category-'.$this->parentCategory->id.'"]')
         ->wait(1000)
         ->clear('[data-test="category-name"]')
         ->fill('[data-test="category-name"]', 'Updated Category Name')
         ->select('[data-test="category-color"]', 'red')
         ->click('[data-test="save-category"]');

    // Verify changes
    $page->wait(2000)
         ->assertSee('Updated Category Name')
         ->assertDontSee('Parent Category');

    // Verify in database
    expect(Category::find($this->parentCategory->id)->name)->toBe('Updated Category Name');
});

test('user can delete category without transactions', function () {
    $emptyCategory = Category::factory()->create([
        'name'    => 'Empty Category',
        'user_id' => $this->user->id,
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Delete category
    $page->click('[data-test="delete-category-'.$emptyCategory->id.'"]')
         ->wait(1000);

    // Confirm deletion
    $page->click('[data-test="confirm-delete"]')
         ->wait(2000);

    // Verify category is removed
    $page->assertDontSee('Empty Category');

    // Verify in database
    expect(Category::find($emptyCategory->id))->toBeNull();
});

test('user cannot delete category with transactions', function () {
    // Create transaction using the parent category
    Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'category_id' => $this->parentCategory->id,
        'amount'      => -50.00,
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Try to delete category
    $page->click('[data-test="delete-category-'.$this->parentCategory->id.'"]')
         ->wait(1000);

    // Should show warning about transactions
    $page->assertSee('This category has transactions and cannot be deleted')
         ->assertPresent('[data-test="cannot-delete-warning"]');

    // Category should still exist
    expect(Category::find($this->parentCategory->id))->not->toBeNull();
});

test('user can set up category rules', function () {
    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Click on rules tab or button
    $page->click('[data-test="category-rules-tab"]')
         ->wait(1000);

    // Add new rule
    $page->click('[data-test="add-rule"]')
         ->wait(1000)
         ->fill('[data-test="rule-pattern"]', 'Walmart')
         ->select('[data-test="rule-category"]', (string) $this->parentCategory->id)
         ->click('[data-test="save-rule"]');

    // Verify rule was created
    $page->wait(2000)
         ->assertSee('Walmart')
         ->assertSeeIn('[data-test="rule-list"]', $this->parentCategory->name);
});

test('category hierarchy displays correctly', function () {
    // Create a more complex hierarchy
    $childCategory = Category::factory()->create([
        'name'      => 'Child Category',
        'user_id'   => $this->user->id,
        'parent_id' => $this->parentCategory->id,
    ]);

    $grandchildCategory = Category::factory()->create([
        'name'      => 'Grandchild Category',
        'user_id'   => $this->user->id,
        'parent_id' => $childCategory->id,
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Verify hierarchy is displayed with proper indentation
    $page->assertSee('Parent Category')
         ->assertSeeIn('[data-test="category-tree"]', 'Child Category')
         ->assertSeeIn('[data-test="category-tree"]', 'Grandchild Category');

    // Check that child is indented under parent
    $page->assertPresent('[data-test="category-level-1-'.$childCategory->id.'"]')
         ->assertPresent('[data-test="category-level-2-'.$grandchildCategory->id.'"]');
});

test('user can collapse and expand category tree', function () {
    $childCategory = Category::factory()->create([
        'name'      => 'Child Category',
        'user_id'   => $this->user->id,
        'parent_id' => $this->parentCategory->id,
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Initially should see child category
    $page->assertSee('Child Category');

    // Collapse parent category
    $page->click('[data-test="collapse-category-'.$this->parentCategory->id.'"]')
         ->wait(1000);

    // Child should be hidden
    $page->assertDontSee('Child Category');

    // Expand again
    $page->click('[data-test="expand-category-'.$this->parentCategory->id.'"]')
         ->wait(1000);

    // Child should be visible again
    $page->assertSee('Child Category');
});

test('category manager shows transaction counts', function () {
    // Create some transactions
    Transaction::factory(3)->create([
        'account_id'  => $this->account->id,
        'category_id' => $this->parentCategory->id,
    ]);

    $page = visit('/login');

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="settings-nav"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Should show transaction count
    $page->assertSee('3 transactions')
         ->assertSeeIn('[data-test="category-stats-'.$this->parentCategory->id.'"]', '3');
});

test('category manager works on mobile', function () {
    $page = visit('/login')->on()->mobile();

    // Login first
    $page->fill('email', $this->user->email)
         ->fill('password', 'password')
         ->click('Log in');

    // Navigate to category manager
    $page->click('[data-test="mobile-menu"]')
         ->wait(1000)
         ->click('[data-test="category-manager"]')
         ->wait(1000);

    // Should work on mobile layout
    $page->assertPresent('[data-test="mobile-category-manager"]')
         ->assertSee('Category Manager')
         ->assertSee($this->parentCategory->name);

    // Test mobile-specific functionality
    $page->click('[data-test="mobile-add-category"]')
         ->wait(1000)
         ->assertPresent('[data-test="mobile-category-form"]');
});
