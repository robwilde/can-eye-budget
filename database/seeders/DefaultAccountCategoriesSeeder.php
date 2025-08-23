<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AccountCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DefaultAccountCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Get the default user
        $user = User::where('email', 'figjam@mrwilde.com')->first();
        
        if (!$user) {
            $this->command->warn('Default user not found. Please run DefaultUserSeeder first.');
            return;
        }

        // Create default account categories
        $categories = [
            [
                'name' => 'day-to-day',
                'display_in_list' => true,
                'sort_order' => 0,
            ],
            [
                'name' => 'hidden',
                'display_in_list' => false,
                'sort_order' => 1,
            ],
        ];

        foreach ($categories as $categoryData) {
            AccountCategory::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $categoryData['name'],
                ],
                array_merge($categoryData, ['user_id' => $user->id])
            );
        }

        $this->command->info('Default account categories created successfully.');
    }
}