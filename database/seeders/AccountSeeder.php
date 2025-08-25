<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

final class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // Get the default user
        $user = User::where('email', 'figjam@mrwilde.com')->first();

        if (! $user) {
            $this->command->warn('Default user not found. Please run DefaultUserSeeder first.');

            return;
        }

        // Get the day-to-day account category
        $dayToDayCategory = AccountCategory::where('user_id', $user->id)
            ->where('name', 'day-to-day')
            ->first();

        if (! $dayToDayCategory) {
            $this->command->warn('Default account categories not found. Please run DefaultAccountCategoriesSeeder first.');

            return;
        }

        // Create default accounts
        $accounts = [
            [
                'user_id'              => $user->id,
                'account_category_id'  => $dayToDayCategory->id,
                'name'                 => 'BB Optimus',
                'type'                 => 'debit',
                'initial_balance'      => '2899.00',
                'credit_limit'         => null,
                'currency'             => 'AUD',
                'description'          => 'Primary debit account for income',
                'is_visible_in_totals' => true,
            ],
            [
                'user_id'              => $user->id,
                'account_category_id'  => $dayToDayCategory->id,
                'name'                 => 'Credit Card',
                'type'                 => 'credit',
                'initial_balance'      => '-1902.00',
                'credit_limit'         => '5000.00',
                'currency'             => 'USD',
                'description'          => 'Primary credit card for bills',
                'is_visible_in_totals' => true,
            ],
        ];

        foreach ($accounts as $accountData) {
            Account::firstOrCreate(
                [
                    'user_id' => $accountData['user_id'],
                    'name'    => $accountData['name'],
                ],
                $accountData,
            );
        }

        $this->command->info('Default accounts created successfully.');
    }
}
