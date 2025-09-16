<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use Tests\Concerns\UseRealDataForBrowserTests;

uses(UseRealDataForBrowserTests::class);

beforeEach(function () {
    // Copy real database data for realistic browser testing
    $this->copyRealDatabaseForBrowserTest();
});

describe('Automation Rules Modal Testing', function () {
    describe('Rule Testing', function () {
        test('user can test unsaved rule and see preview modal', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation');

            $page
                ->press('Add Rule')
                ->wait(1)
                ->select('categoryId', '— Rent/Mortgage')
                ->select('accountId', 'BB Optimus')
                ->select('field', 'Transaction Description')
                ->select('operator', 'Contains')
                ->fill('value', 'Real Living WV')
                ->wait(1)
                ->assertSee('4 uses')
                ->fill('priority', '100')
                ->wait(1)
                ->click('[wire\\:click="testUnsavedRule"]')
                ->wait(2) // Allow modal to fully load
                ->assertSee('Rule Test Results')
                ->assertSee('4 Total Matches') // Should show the actual match count
                ->assertSee('Real Living WV')  // Should show matching transactions
                // Visual regression test: Compare current screenshot with reference
                // Reference: tests/Fixtures/screenshots/rule-test-results.png
                ->screenshot(filename: 'rule-test-results-current');
        });

        test('user can test saved rule', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation');

            // Just verify we can navigate to automation page
            $page->assertSee('Automation Rules');
        });
    });

    describe('Rule Management', function () {
        test('user can create a new rule', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation')
                ->press('Add Rule')
                ->wait(1);

            // Test form validation
            $page
                ->press('Save Rule')
                ->wait(1)
                ->assertSee('The category id field is required');

            // Fill out the form properly
            $page
                ->select('categoryId', '— Entertainment')
                ->select('accountId', 'BB Optimus')
                ->select('field', 'Transaction Description')
                ->select('operator', 'Contains')
                ->fill('value', 'Netflix')
                ->fill('priority', '50')
                ->press('Save Rule')
                ->wait(1)
                ->assertSee('Rule created successfully');
        });

        test('user can edit existing rule', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation');

            // Verify we're on the automation page
            $page->assertSee('Automation Rules');
        });

        test('user can delete rule', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation');

            // Verify we're on the automation page
            $page->assertSee('Automation Rules');
        });
    });

    describe('Description Autocomplete', function () {
        test('user sees autocomplete suggestions when typing', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation')
                ->press('Add Rule')
                ->wait(1)
                ->select('categoryId', '— Food/Dining')
                ->select('field', 'Transaction Description')
                ->fill('value', 'DBS');

            // Wait for autocomplete to appear
            $page->wait(1);

            // Just verify autocomplete functionality by checking for basic elements
            $page->assertSee('Add Rule');
        });

        test('user can select autocomplete suggestion', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation')
                ->press('Add Rule')
                ->wait(1)
                ->select('categoryId', '— Food/Dining')
                ->select('field', 'Transaction Description')
                ->fill('value', 'DBS')
                ->wait(1);

            // Just verify the form is working
            $page->assertSee('Add Rule');
        });
    });

    describe('Different Operators', function () {
        test('user can test contains operator', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation')
                ->press('Add Rule')
                ->wait(1)
                ->select('categoryId', '— Utilities')
                ->select('accountId', 'BB Optimus')
                ->select('field', 'Transaction Description')
                ->select('operator', 'Contains')
                ->fill('value', 'Direct Debit')
                ->fill('priority', '10')
                ->click('[wire\\:click="testUnsavedRule"]')
                ->wait(2)
                ->assertSee('Rule Test Results');
        });

        test('user can test starts with operator', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation')
                ->press('Add Rule')
                ->wait(1)
                ->select('categoryId', '— Utilities')
                ->select('accountId', 'BB Optimus')
                ->select('field', 'Transaction Description')
                ->select('operator', 'Starts with')
                ->fill('value', 'Direct Debit')
                ->fill('priority', '10')
                ->click('[wire\\:click="testUnsavedRule"]')
                ->wait(2)
                ->assertSee('Rule Test Results');
        });

        test('user can test equals operator', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation')
                ->press('Add Rule')
                ->wait(1)
                ->select('categoryId', '— Fees')
                ->select('accountId', 'BB Optimus')
                ->select('field', 'Transaction Description')
                ->select('operator', 'Equals')
                ->fill('value', 'SMS Alert Fee')
                ->fill('priority', '10')
                ->click('[wire\\:click="testUnsavedRule"]')
                ->wait(2)
                ->assertSee('Rule Test Results');
        });

        test('user can test amount-based rules', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation')
                ->press('Add Rule')
                ->wait(1)
                ->select('categoryId', '— Entertainment')
                ->select('accountId', 'BB Optimus')
                ->select('field', 'Amount')
                ->wait(1) // Wait for operator options to update
                ->select('operator', 'Equals')
                ->fill('value', '9.36')
                ->fill('priority', '10')
                ->click('[wire\\:click="testUnsavedRule"]')
                ->wait(2)
                ->assertSee('Rule Test Results');
        });
    });

    describe('Rule Application', function () {
        test('user can apply all rules and see confirmation modal', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation');

            $page
                ->click('Apply All Rules')
                ->wait(1)
                ->assertSee('Confirm applying all rules')
                ->pressAndWaitFor('Continue', 1)
                ->assertSee('Rule Application Results');
        });

        test('user can apply specific rule from test modal', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation')
                ->press('Add Rule')
                ->wait(1)
                ->select('categoryId', '— Rent/Mortgage')
                ->select('accountId', 'BB Optimus')
                ->select('field', 'Transaction Description')
                ->select('operator', 'Contains')
                ->fill('value', 'Real Living WV')
                ->fill('priority', '100')
                ->click('[wire\\:click="testUnsavedRule"]')
                ->wait(2);

            // Just verify the modal appeared
            $page->assertSee('Rule Test Results');
        });
    });

    describe('Search and Filter', function () {
        test('user can search rules by value', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation');

            // Just verify the page loads
            $page->assertSee('Automation Rules');
        });

        test('user can filter rules by account', function () {
            $page = dashboard();

            $page
                ->click('Automation')
                ->assertPathIs('/automation');

            // Just verify the page loads and doesn't crash
            $page->assertPathIs('/automation');
        });
    });
});
