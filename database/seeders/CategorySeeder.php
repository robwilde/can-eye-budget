<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users to seed categories for each
        $users = User::all();

        foreach ($users as $user) {
            $this->seedCategoriesForUser($user);
        }
    }

    private function seedCategoriesForUser(User $user): void
    {
        // Define comprehensive category hierarchy
        $categories = [
            // INCOME CATEGORIES
            'Income' => [
                'color'    => '#10B981', // green-500
                'icon'     => 'banknotes',
                'children' => [
                    'Salary'       => ['color' => '#16A34A', 'icon' => 'briefcase'],
                    'Freelance'    => ['color' => '#22C55E', 'icon' => 'computer-desktop'],
                    'Business'     => ['color' => '#84CC16', 'icon' => 'building-office'],
                    'Investments'  => ['color' => '#65A30D', 'icon' => 'chart-bar'],
                    'Interest'     => ['color' => '#A3A3A3', 'icon' => 'currency-dollar'],
                    'Gifts'        => ['color' => '#F97316', 'icon' => 'gift'],
                    'Other Income' => ['color' => '#6B7280', 'icon' => 'plus-circle'],
                ],
            ],

            // EXPENSE CATEGORIES
            'Housing' => [
                'color'    => '#3B82F6', // blue-500
                'icon'     => 'home',
                'children' => [
                    'Rent/Mortgage'         => ['color' => '#2563EB', 'icon' => 'key'],
                    'Property Tax'          => ['color' => '#1D4ED8', 'icon' => 'document-text'],
                    'Home Insurance'        => ['color' => '#1E40AF', 'icon' => 'shield-check'],
                    'HOA Fees'              => ['color' => '#1E3A8A', 'icon' => 'building-office-2'],
                    'Repairs & Maintenance' => ['color' => '#312E81', 'icon' => 'wrench-screwdriver'],
                ],
            ],

            'Utilities' => [
                'color'    => '#F59E0B', // amber-500
                'icon'     => 'bolt',
                'children' => [
                    'Electricity'     => ['color' => '#D97706', 'icon' => 'lightning-bolt'],
                    'Gas'             => ['color' => '#B45309', 'icon' => 'fire'],
                    'Water'           => ['color' => '#92400E', 'icon' => 'beaker'],
                    'Internet'        => ['color' => '#78350F', 'icon' => 'wifi'],
                    'Phone'           => ['color' => '#451A03', 'icon' => 'phone'],
                    'Cable/Streaming' => ['color' => '#7C2D12', 'icon' => 'tv'],
                ],
            ],

            'Transportation' => [
                'color'    => '#8B5CF6', // violet-500
                'icon'     => 'truck',
                'children' => [
                    'Car Payment'    => ['color' => '#7C3AED', 'icon' => 'credit-card'],
                    'Auto Insurance' => ['color' => '#6D28D9', 'icon' => 'shield-exclamation'],
                    'Gas'            => ['color' => '#5B21B6', 'icon' => 'beaker'],
                    'Maintenance'    => ['color' => '#4C1D95', 'icon' => 'cog-6-tooth'],
                    'Public Transit' => ['color' => '#3730A3', 'icon' => 'map'],
                    'Parking'        => ['color' => '#312E81', 'icon' => 'square-3-stack-3d'],
                ],
            ],

            'Food & Dining' => [
                'color'    => '#EF4444', // red-500
                'icon'     => 'cake',
                'children' => [
                    'Groceries'    => ['color' => '#DC2626', 'icon' => 'shopping-cart'],
                    'Restaurants'  => ['color' => '#B91C1C', 'icon' => 'building-storefront'],
                    'Fast Food'    => ['color' => '#991B1B', 'icon' => 'truck'],
                    'Coffee Shops' => ['color' => '#7F1D1D', 'icon' => 'beaker'],
                    'Delivery'     => ['color' => '#450A0A', 'icon' => 'truck'],
                ],
            ],

            'Healthcare' => [
                'color'    => '#EC4899', // pink-500
                'icon'     => 'heart',
                'children' => [
                    'Health Insurance' => ['color' => '#DB2777', 'icon' => 'shield-check'],
                    'Doctor Visits'    => ['color' => '#BE185D', 'icon' => 'user'],
                    'Prescriptions'    => ['color' => '#9D174D', 'icon' => 'beaker'],
                    'Dental'           => ['color' => '#831843', 'icon' => 'face-smile'],
                    'Vision'           => ['color' => '#701A75', 'icon' => 'eye'],
                ],
            ],

            'Entertainment' => [
                'color'    => '#06B6D4', // cyan-500
                'icon'     => 'film',
                'children' => [
                    'Movies'        => ['color' => '#0891B2', 'icon' => 'film'],
                    'Games'         => ['color' => '#0E7490', 'icon' => 'puzzle-piece'],
                    'Books'         => ['color' => '#155E75', 'icon' => 'book-open'],
                    'Music'         => ['color' => '#164E63', 'icon' => 'musical-note'],
                    'Sports Events' => ['color' => '#0F3460', 'icon' => 'trophy'],
                ],
            ],

            'Shopping' => [
                'color'    => '#84CC16', // lime-500
                'icon'     => 'shopping-bag',
                'children' => [
                    'Clothing'      => ['color' => '#65A30D', 'icon' => 'user'],
                    'Electronics'   => ['color' => '#4D7C0F', 'icon' => 'computer-desktop'],
                    'Home & Garden' => ['color' => '#365314', 'icon' => 'home'],
                    'Gifts'         => ['color' => '#1A2E05', 'icon' => 'gift'],
                ],
            ],

            'Financial' => [
                'color'    => '#6366F1', // indigo-500
                'icon'     => 'banknotes',
                'children' => [
                    'Bank Fees'            => ['color' => '#4F46E5', 'icon' => 'building-library'],
                    'Investment Fees'      => ['color' => '#4338CA', 'icon' => 'chart-bar'],
                    'Credit Card Interest' => ['color' => '#3730A3', 'icon' => 'credit-card'],
                    'Loan Interest'        => ['color' => '#312E81', 'icon' => 'calculator'],
                    'Insurance'            => ['color' => '#1E1B4B', 'icon' => 'shield-check'],
                ],
            ],

            'Personal Care' => [
                'color'    => '#F97316', // orange-500
                'icon'     => 'user-circle',
                'children' => [
                    'Haircuts'        => ['color' => '#EA580C', 'icon' => 'scissors'],
                    'Beauty Products' => ['color' => '#C2410C', 'icon' => 'heart'],
                    'Gym Membership'  => ['color' => '#9A3412', 'icon' => 'trophy'],
                    'Clothing'        => ['color' => '#7C2D12', 'icon' => 'user'],
                ],
            ],

            'Education' => [
                'color'    => '#059669', // emerald-600
                'icon'     => 'academic-cap',
                'children' => [
                    'Tuition'          => ['color' => '#047857', 'icon' => 'building-library'],
                    'Books & Supplies' => ['color' => '#065F46', 'icon' => 'book-open'],
                    'Online Courses'   => ['color' => '#064E3B', 'icon' => 'computer-desktop'],
                    'Workshops'        => ['color' => '#022C22', 'icon' => 'users'],
                ],
            ],

            'Miscellaneous' => [
                'color'    => '#6B7280', // gray-500
                'icon'     => 'ellipsis-horizontal',
                'children' => [
                    'Uncategorized' => ['color' => '#4B5563', 'icon' => 'question-mark-circle'],
                    'Other'         => ['color' => '#374151', 'icon' => 'squares-plus'],
                ],
            ],
        ];

        $this->createCategoriesHierarchy($categories, $user, null);
    }

    private function createCategoriesHierarchy(array $categories, User $user, ?int $parentId = null): void
    {
        foreach ($categories as $name => $config) {
            $category = Category::create([
                'user_id'   => $user->id,
                'name'      => $name,
                'parent_id' => $parentId,
                'color'     => $config['color'],
                'icon'      => $config['icon'],
            ]);

            // Create children if they exist
            if (isset($config['children'])) {
                $this->createCategoriesHierarchy($config['children'], $user, $category->id);
            }
        }
    }
}
