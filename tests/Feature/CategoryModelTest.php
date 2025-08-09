<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create category directly', function () {
    $user = User::factory()
                ->create();

    $category = Category::create([
        'user_id' => $user->id,
        'name'    => 'Test Category',
        'color'   => '#ff0000',
        'icon'    => 'shopping-cart',
    ]);

    expect($category)->not
        ->toBeNull()
        ->and($category->name)
        ->toBe('Test Category');

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name'    => 'Test Category',
        'color'   => '#ff0000',
        'icon'    => 'shopping-cart',
    ]);
});
