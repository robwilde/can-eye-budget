<?php

declare(strict_types=1);

use App\Livewire\AutomationRules;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

covers(AutomationRules::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->account = Account::factory()->create(['user_id' => $this->user->id]);
    $this->category = Category::factory()->create(['user_id' => $this->user->id]);
});

describe('Description search functionality', function () {
    test('searches descriptions when value changes for description field', function () {
        // Create test transactions
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee Shop',
        ]);
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Amazon Purchase',
        ]);

        Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('value', 'Star')
            ->assertSet('showDescriptionSuggestions', true)
            ->assertCount('descriptionSearchResults', 1)
            ->assertSet('descriptionSearchResults.0.description', 'Starbucks Coffee Shop');
    });

    test('does not search for amount field', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Test Transaction',
        ]);

        Livewire::test(AutomationRules::class)
            ->set('field', 'amount')
            ->set('value', '100')
            ->assertSet('showDescriptionSuggestions', false)
            ->assertCount('descriptionSearchResults', 0);
    });

    test('requires minimum 2 characters for search', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Test Transaction',
        ]);

        Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('value', 'T')
            ->assertSet('showDescriptionSuggestions', false)
            ->assertCount('descriptionSearchResults', 0);
    });

    test('filters search results by account when account is selected', function () {
        $otherAccount = Account::factory()->create(['user_id' => $this->user->id]);

        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Coffee Shop A',
        ]);
        Transaction::factory()->create([
            'account_id'  => $otherAccount->id,
            'description' => 'Coffee Shop B',
        ]);

        Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('accountId', $this->account->id)
            ->set('value', 'Coffee')
            ->assertCount('descriptionSearchResults', 1)
            ->assertSet('descriptionSearchResults.0.description', 'Coffee Shop A');
    });

    test('shows usage count for each suggestion', function () {
        // Create multiple transactions with same description
        Transaction::factory()->count(3)->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee',
        ]);

        Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('value', 'Star')
            ->assertCount('descriptionSearchResults', 1)
            ->assertSet('descriptionSearchResults.0.usage_count', 3);
    });

    test('can select suggestion by index', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee Shop',
        ]);

        Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('value', 'Star')
            ->call('selectDescriptionSuggestion', 0)
            ->assertSet('value', 'Starbucks Coffee Shop')
            ->assertSet('showDescriptionSuggestions', false)
            ->assertSet('selectedSuggestionIndex', -1);
    });

    test('can navigate suggestions with keyboard', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee',
        ]);
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Latte',
        ]);

        $component = Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('value', 'Star')
            ->assertSet('selectedSuggestionIndex', -1);

        $component->call('navigateDescriptionSuggestions', 'down')
            ->assertSet('selectedSuggestionIndex', 0);

        $component->call('navigateDescriptionSuggestions', 'down')
            ->assertSet('selectedSuggestionIndex', 1);

        $component->call('navigateDescriptionSuggestions', 'up')
            ->assertSet('selectedSuggestionIndex', 0);
    });

    test('can select current highlighted suggestion', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee Shop',
        ]);

        Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('value', 'Star')
            ->call('navigateDescriptionSuggestions', 'down')
            ->assertSet('selectedSuggestionIndex', 0)
            ->call('selectCurrentSuggestion')
            ->assertSet('value', 'Starbucks Coffee Shop')
            ->assertSet('showDescriptionSuggestions', false);
    });

    test('can hide suggestions', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee Shop',
        ]);

        Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('value', 'Star')
            ->assertSet('showDescriptionSuggestions', true)
            ->call('hideDescriptionSuggestions')
            ->assertSet('showDescriptionSuggestions', false)
            ->assertSet('selectedSuggestionIndex', -1);
    });

    test('resets suggestions when field changes', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Test Transaction',
        ]);

        Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('value', 'Test')
            ->assertSet('showDescriptionSuggestions', true)
            ->set('field', 'amount')
            ->assertSet('showDescriptionSuggestions', false)
            ->assertSet('selectedSuggestionIndex', -1);
    });

    test('integrates with full rule creation flow', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee Shop',
        ]);

        Livewire::test(AutomationRules::class)
            ->call('createRule')
            ->assertSet('showForm', true)
            ->set('categoryId', $this->category->id)
            ->set('field', 'description')
            ->set('value', 'Star')
            ->call('selectDescriptionSuggestion', 0)
            ->assertSet('value', 'Starbucks Coffee Shop')
            ->set('priority', 5)
            ->call('saveRule')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('category_rules', [
            'category_id' => $this->category->id,
            'field'       => 'description',
            'operator'    => 'contains',
            'value'       => 'Starbucks Coffee Shop',
            'priority'    => 5,
        ]);
    });

    test('caches search results for performance', function () {
        Transaction::factory()->create([
            'account_id'  => $this->account->id,
            'description' => 'Starbucks Coffee',
        ]);

        $component = Livewire::test(AutomationRules::class)
            ->set('field', 'description')
            ->set('value', 'Star');

        // First search should cache the results
        $component->assertSet('showDescriptionSuggestions', true);

        // Check cache exists
        $cacheKey = 'description_search:'.md5('Star|');
        expect(cache()->has($cacheKey))->toBeTrue();
    });
});
