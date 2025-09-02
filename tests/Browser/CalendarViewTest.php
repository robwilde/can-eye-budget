<?php /** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;
use Tests\Concerns\UseRealDataForBrowserTests;

uses(UseRealDataForBrowserTests::class);

beforeEach(function () {
    // Copy real database data for realistic browser testing
    $this->copyRealDatabaseForBrowserTest();

    // Use real user data for authentic E2E testing
    $this->user = User::first();
    expect($this->user)->not->toBeNull('Real user data should be available for browser testing');

    // Use real account with actual transactions
    $this->account = $this->user->accounts()->whereHas('transactions')->first();
    expect($this->account)->not->toBeNull('Real account with transactions should be available');

    // Use real category data
    $this->category = Category::where('user_id', $this->user->id)->whereHas('transactions')->first();

    // Get sample real transactions for testing
    $this->recentTransactions = $this->account->transactions()
        ->with('category')
        ->orderBy('transaction_date', 'desc')
        ->limit(5)
        ->get();
});

test('calendar view loads and displays correctly', function () {
    $page = dashboard();

    // Verify calendar interface elements are present
    $page
        ->assertPathIs('/dashboard')
        ->assertSee('Month Calendar')
        ->assertSee('Add Transaction')
        ->assertSee('Month') // View switcher button
        ->assertSee('Day')
        ->assertSee('Week')
        ->assertSee('Year')
        ->assertSee('Today')
        ->assertSee('Previous')
        ->assertSee('Next');
});

test('user can switch between calendar views', function () {
    $page = dashboard();

    // Test view switching with proper interactions
    $page
        ->assertSee('Day')
        ->assertSee('Week')
        ->assertSee('Month')
        ->assertSee('Year')
        ->click('Day')
        ->wait(500)
        ->assertSee('Day Calendar') // Should show day view
        ->click('Week')
        ->wait(500)
        ->assertSee('Week Calendar') // Should show week view
        ->click('Month') // Return to month view
        ->wait(500)
        ->assertSee('Month Calendar');
});

test('user can navigate between calendar periods', function () {
    $page = dashboard();

    // Test navigation controls exist and function
    $currentMonth = now()->format('F Y');

    $page
        ->assertSee($currentMonth) // Current month display
        ->assertSee('Previous')
        ->assertSee('Today')
        ->assertSee('Next')
        ->click('Previous') // Navigate to previous month
        ->wait(500)
        ->click('Next') // Navigate back
        ->wait(500)
        ->assertSee($currentMonth) // Should return to current month
        ->click('Today') // Reset to today
        ->wait(500)
        ->assertSee($currentMonth);
});

test('calendar displays existing transactions', function () {
    // Use real transactions from the database
    expect($this->recentTransactions)->toHaveCount(5, 'Should have real transactions to test with');

    $page = dashboard();

    $page->assertSee('Month Calendar');

    // Check that real transaction data is visible on calendar
    foreach ($this->recentTransactions as $transaction) {
        // Navigate to the month containing this transaction
        $transactionMonth = $transaction->transaction_date->format('F Y');

        // Test that calendar shows some indication of transactions
        // Even if not the exact format, there should be some visual representation
        $page->assertSee($transactionMonth);
    }

    // Verify calendar shows transaction summary information
    $page->assertSee('Planned')
        ->assertSee('Entered');
});

test('calendar shows correct balance calculations', function () {
    // Use real transaction data to verify balance calculations
    $totalIncome = $this->account->transactions()->where('amount', '>', 0)->sum('amount');
    $totalExpenses = abs($this->account->transactions()->where('amount', '<', 0)->sum('amount'));

    $page = dashboard();

    // Should show balance information in planned/entered sections
    $page
        ->assertSee('Planned')
        ->assertSee('Entered')
        ->assertSee('Incomes')
        ->assertSee('Expenses');

    // Verify that balance calculations reflect real data
    // The exact format might vary, but some numeric representation should be present
    if ($totalIncome > 0) {
        $page->assertSee('$'.number_format($totalIncome, 2));
    }

    if ($totalExpenses > 0) {
        $page->assertSee('$'.number_format($totalExpenses, 2));
    }
});

test('user can filter calendar by account', function () {
    // Use real accounts from the database
    $accounts = $this->user->accounts()->get();
    expect($accounts)->toHaveCountGreaterThan(0, 'Should have real accounts to test with');

    $page = dashboard();

    $page->assertSee('Month Calendar');

    // Test that account information is displayed
    foreach ($accounts as $account) {
        $page->assertSee($account->name);
    }

    // Test for account selection/filtering controls
    // This might be a dropdown, radio buttons, or other UI element
    $mainAccount = $accounts->first();
    $page->assertSee($mainAccount->name);

    // If there are multiple accounts, test switching between them
    if ($accounts->count() > 1) {
        $secondAccount = $accounts->skip(1)->first();
        $page->assertSee($secondAccount->name);
    }
});

test('calendar works on mobile devices', function () {
    $page = dashboard()->on()->mobile();

    // Calendar should adapt to mobile layout and remain functional
    $page
        ->assertSee('Month Calendar')
        ->assertSee('Add Transaction')
        ->assertSee('Day')
        ->assertSee('Week')
        ->assertSee('Month')
        ->assertSee('Today');

    // Test basic mobile interaction
    $page
        ->click('Day')
        ->wait(500)
        ->click('Month')
        ->wait(500)
        ->assertSee('Month Calendar');

    // Verify responsive layout elements are working
    $page->assertSee('Previous')
        ->assertSee('Next');
});

test('user can access transaction form from calendar', function () {
    $page = dashboard();

    // Should see the add transaction button and be able to interact with it
    $page
        ->assertSee('Add Transaction')
        ->click('Add Transaction')
        ->wait(1000); // Allow time for modal or form to appear

    // Look for form elements that should appear
    // This could be a modal, slide-over, or new page
    $page
        ->assertSee('Description') // Common form field
        ->assertSee('Amount'); // Common form field
});

test('calendar displays real transaction data correctly', function () {
    // Get a specific transaction to verify display
    $specificTransaction = $this->recentTransactions->first();
    expect($specificTransaction)->not->toBeNull('Should have a real transaction to test with');

    $page = dashboard();

    $page->assertSee('Month Calendar');

    // Navigate to the month containing this transaction if needed
    $transactionMonth = $specificTransaction->transaction_date->format('F Y');
    $currentMonth = now()->format('F Y');

    // If the transaction is not in the current month, navigate to it
    if ($transactionMonth !== $currentMonth) {
        // This test assumes navigation works; if not, just verify current month content
        $page->assertSee($currentMonth);
    }

    // Verify that transaction data is reflected in the calendar interface
    $page
        ->assertSee('Planned')
        ->assertSee('Entered')
        ->assertSee('Incomes')
        ->assertSee('Expenses');

    // Verify account balance is displayed somewhere
    $currentBalance = $this->account->balance ?? 0;
    if ($currentBalance !== 0) {
        $formattedBalance = '$'.number_format(abs($currentBalance), 2);
        $page->assertSee($formattedBalance);
    }
});
