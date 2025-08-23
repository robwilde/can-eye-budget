<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Import;
use App\Models\RecurringPattern;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        $testUser = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create additional demo users
        $demoUsers = User::factory(2)->create();
        $allUsers = collect([$testUser])->merge($demoUsers);

        // Seed categories for all users
        $this->call(CategorySeeder::class);

        // Create sample data for each user
        foreach ($allUsers as $user) {
            $this->seedUserData($user);
        }
    }

    private function seedUserData(User $user): void
    {
        // Create accounts for the user
        $checkingAccount = Account::factory()->checking()->for($user)->create([
            'name'            => 'Primary Checking',
            'initial_balance' => 2500.00,
        ]);

        $savingsAccount = Account::factory()->savings()->for($user)->create([
            'name'            => 'Emergency Fund',
            'initial_balance' => 8500.00,
        ]);

        $creditAccount = Account::factory()->credit()->for($user)->create([
            'name'            => 'Main Credit Card',
            'initial_balance' => -1200.00,
        ]);

        $accounts = collect([$checkingAccount, $savingsAccount, $creditAccount]);

        // Get categories for this user
        $incomeCategories = Category::where('user_id', $user->id)
            ->whereHas('parent', fn ($query) => $query->where('name', 'Income'))
            ->get();

        $expenseCategories = Category::where('user_id', $user->id)
            ->whereDoesntHave('parent', fn ($query) => $query->where('name', 'Income'))
            ->where('name', '!=', 'Income')
            ->whereNotNull('parent_id')
            ->get();

        // Create sample transactions (last 6 months)
        for ($i = 0; $i < 200; $i++) {
            $account = $accounts->random();

            // 70% expense, 25% income, 5% transfer
            $transactionType = match (rand(1, 100)) {
                1, 2, 3, 4, 5 => 'transfer',
                6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30 => 'income',
                default => 'expense',
            };

            $transactionData = [
                'account_id'       => $account->id,
                'type'             => $transactionType,
                'transaction_date' => fake()->dateTimeBetween('-6 months', 'now'),
                'reconciled'       => fake()->boolean(85),
            ];

            if ($transactionType === 'income' && $incomeCategories->isNotEmpty()) {
                $transactionData['category_id'] = $incomeCategories->random()->id;
                $transactionData['amount'] = fake()->randomFloat(2, 500, 4000);
                $transactionData['description'] = fake()->randomElement([
                    'Salary Payment',
                    'Freelance Project',
                    'Investment Dividend',
                    'Interest Payment',
                    'Bonus Payment',
                    'Side Business Income',
                ]);
            } elseif ($transactionType === 'expense' && $expenseCategories->isNotEmpty()) {
                $transactionData['category_id'] = $expenseCategories->random()->id;
                $transactionData['amount'] = fake()->randomFloat(2, 5, 500);
                $transactionData['description'] = fake()->randomElement([
                    'Grocery Store',
                    'Gas Station',
                    'Restaurant',
                    'Coffee Shop',
                    'Online Purchase',
                    'Utility Bill',
                    'Insurance Payment',
                    'Subscription Service',
                ]);
            } elseif ($transactionType === 'transfer') {
                $transferToAccount = $accounts->where('id', '!=', $account->id)->random();
                $transactionData['transfer_to_account_id'] = $transferToAccount->id;
                $transactionData['amount'] = fake()->randomFloat(2, 50, 1000);
                $transactionData['description'] = "Transfer to {$transferToAccount->name}";
            }

            Transaction::factory()->create($transactionData);
        }

        // Create recurring patterns
        if ($incomeCategories->isNotEmpty()) {
            // Monthly salary
            RecurringPattern::factory()
                ->for($checkingAccount, 'account')
                ->for($incomeCategories->first(), 'category')
                ->monthly()
                ->income()
                ->active()
                ->create([
                    'name'        => 'Monthly Salary',
                    'description' => 'Regular monthly salary payment',
                    'amount'      => 4500.00,
                ]);

            // Bi-weekly freelance
            RecurringPattern::factory()
                ->for($checkingAccount, 'account')
                ->for($incomeCategories->random(), 'category')
                ->create([
                    'name'               => 'Freelance Payment',
                    'description'        => 'Regular freelance income',
                    'type'               => 'income',
                    'amount'             => 800.00,
                    'frequency'          => 'bi-weekly',
                    'frequency_interval' => 1,
                    'is_active'          => true,
                ]);
        }

        if ($expenseCategories->isNotEmpty()) {
            // Monthly rent
            $housingCategory = Category::where('user_id', $user->id)
                ->where('name', 'Rent/Mortgage')
                ->first();

            if ($housingCategory) {
                RecurringPattern::factory()
                    ->for($checkingAccount, 'account')
                    ->for($housingCategory, 'category')
                    ->monthly()
                    ->expense()
                    ->active()
                    ->create([
                        'name'        => 'Monthly Rent',
                        'description' => 'Monthly rent payment',
                        'amount'      => 1200.00,
                    ]);
            }

            // Weekly groceries
            $groceryCategory = Category::where('user_id', $user->id)
                ->where('name', 'Groceries')
                ->first();

            if ($groceryCategory) {
                RecurringPattern::factory()
                    ->for($checkingAccount, 'account')
                    ->for($groceryCategory, 'category')
                    ->weekly()
                    ->expense()
                    ->active()
                    ->create([
                        'name'        => 'Weekly Groceries',
                        'description' => 'Weekly grocery shopping',
                        'amount'      => 150.00,
                    ]);
            }
        }

        // Create sample imports
        Import::factory(3)->for($user)->completed()->create();
        Import::factory(1)->for($user)->pending()->create();
        Import::factory(1)->for($user)->failed()->create();
    }
}
