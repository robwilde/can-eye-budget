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
        // Define comprehensive category hierarchy based on HoneyMoney.csv
        $categories = [
            // INCOME CATEGORIES
            'Income' => [
                'color'    => '#10B981', // green-500
                'icon'     => 'banknotes',
                'children' => [
                    'Salary' => ['color' => '#16A34A', 'icon' => 'briefcase'],
                ],
            ],

            // OFFICE EXPENSES
            'Office' => [
                'color'    => '#3B82F6', // blue-500
                'icon'     => 'briefcase',
                'children' => [
                    '3D Printing'   => ['color' => '#2563EB', 'icon' => 'cube'],
                    'AI Apps'       => ['color' => '#1D4ED8', 'icon' => 'cpu-chip'],
                    'Mobile App'    => ['color' => '#1E40AF', 'icon' => 'device-phone-mobile'],
                    'Newsletter'    => ['color' => '#1E3A8A', 'icon' => 'envelope'],
                    'Software'      => ['color' => '#312E81', 'icon' => 'code-bracket'],
                    'Online Service' => [
                        'color'    => '#60A5FA', // blue-400
                        'icon'     => 'cloud',
                        'children' => [
                            'Apple' => ['color' => '#93C5FD', 'icon' => 'device-phone-mobile'],
                        ],
                    ],
                    'Hardware' => [
                        'color'    => '#2563EB', // blue-600
                        'icon'     => 'computer-desktop',
                        'children' => [
                            'Rentals' => ['color' => '#1D4ED8', 'icon' => 'arrow-path'],
                        ],
                    ],
                    'Training' => [
                        'color'    => '#3B82F6', // blue-500
                        'icon'     => 'academic-cap',
                        'children' => [
                            'Subscription' => ['color' => '#60A5FA', 'icon' => 'book-open'],
                        ],
                    ],
                ],
            ],

            // PERSONAL EXPENSES
            'Personal' => [
                'color'    => '#EC4899', // pink-500
                'icon'     => 'user-circle',
                'children' => [
                    'Health'       => ['color' => '#DB2777', 'icon' => 'heart'],
                    'Subscription' => ['color' => '#BE185D', 'icon' => 'star'],
                    'Charity'      => ['color' => '#9D174D', 'icon' => 'hand-raised'],
                    'Hunter'       => ['color' => '#831843', 'icon' => 'user'],
                    'Finance' => [
                        'color'    => '#F472B6', // pink-400
                        'icon'     => 'calculator',
                        'children' => [
                            'Bank Fees' => ['color' => '#F9A8D4', 'icon' => 'building-library'],
                        ],
                    ],
                ],
            ],

            // ENTERTAINMENT
            'Entertainment' => [
                'color'    => '#06B6D4', // cyan-500
                'icon'     => 'film',
                'children' => [
                    'Streaming' => ['color' => '#0891B2', 'icon' => 'tv'],
                    'Gaming'    => ['color' => '#0E7490', 'icon' => 'puzzle-piece'],
                    'Twitch'    => ['color' => '#155E75', 'icon' => 'video-camera'],
                    'Apps'      => ['color' => '#164E63', 'icon' => 'device-phone-mobile'],
                    'Adult'     => ['color' => '#0F3460', 'icon' => 'eye'],
                    'Patreon'   => ['color' => '#083344', 'icon' => 'heart'],
                ],
            ],

            // BILLS
            'Bills' => [
                'color'    => '#F59E0B', // amber-500
                'icon'     => 'document-text',
                'children' => [
                    'Rent'        => ['color' => '#D97706', 'icon' => 'home'],
                    'Cleaning'    => ['color' => '#B45309', 'icon' => 'sparkles'],
                    'Internet'    => ['color' => '#92400E', 'icon' => 'wifi'],
                    'Electricity' => ['color' => '#78350F', 'icon' => 'bolt'],
                    'Hotwater'    => ['color' => '#451A03', 'icon' => 'fire'],
                    'Mobile'      => ['color' => '#7C2D12', 'icon' => 'phone'],
                ],
            ],

            // TRANSPORT
            'Transport' => [
                'color'    => '#8B5CF6', // violet-500
                'icon'     => 'truck',
                'children' => [
                    'Motorcycle' => ['color' => '#7C3AED', 'icon' => 'bolt'],
                    'Uber'       => ['color' => '#6D28D9', 'icon' => 'map-pin'],
                ],
            ],

            // TRANSFER
            'Transfer' => [
                'color'    => '#6366F1', // indigo-500
                'icon'     => 'arrow-path',
                'children' => [
                    'Optimus to Spaceship' => ['color' => '#4F46E5', 'icon' => 'arrow-right-circle'],
                ],
            ],

            // FOOD
            'Food' => [
                'color'    => '#EF4444', // red-500
                'icon'     => 'cake',
                'children' => [
                    'Groceries' => ['color' => '#DC2626', 'icon' => 'shopping-cart'],
                ],
            ],

            // LOAN
            'Loan' => [
                'color'    => '#64748B', // slate-500
                'icon'     => 'banknotes',
                'children' => [
                    'Motorcycle' => ['color' => '#475569', 'icon' => 'bolt'],
                    'Latitude' => [
                        'color'    => '#334155', // slate-700
                        'icon'     => 'credit-card',
                        'children' => [
                            'Interest' => ['color' => '#1E293B', 'icon' => 'calculator'],
                            'Fees'     => ['color' => '#0F172A', 'icon' => 'currency-dollar'],
                        ],
                    ],
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
