<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name'    => $this->faker->randomElement([
                'Food & Dining', 'Transportation', 'Shopping', 'Entertainment',
                'Bills & Utilities', 'Healthcare', 'Travel', 'Education',
                'Investment', 'Income', 'Business', 'Gifts & Donations',
            ]),
            'color' => $this->faker->hexColor(),
            'icon'  => $this->faker->randomElement([
                'shopping-cart', 'home', 'heart', 'star', 'gift', 'cash',
            ]),
        ];
    }
}
