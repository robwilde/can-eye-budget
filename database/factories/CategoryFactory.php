<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Food & Dining', 'Transportation', 'Shopping', 'Entertainment',
                'Bills & Utilities', 'Healthcare', 'Travel', 'Education',
                'Investment', 'Income', 'Business', 'Gifts & Donations',
            ]),
            'color' => $this->faker->hexColor(),
            'icon' => $this->faker->randomElement([
                'shopping-cart', 'car', 'home', 'heart', 'star', 'gift',
            ]),
        ];
    }
}
