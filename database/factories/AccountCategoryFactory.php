<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountCategory>
 */
final class AccountCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categoryNames = [
            'Personal Banking',
            'Business Accounts',
            'Investment Accounts',
            'Savings Goals',
            'Emergency Funds',
            'Credit Cards',
            'Loans & Debt',
        ];

        return [
            'user_id'         => User::factory(),
            'name'            => fake()->randomElement($categoryNames),
            'display_in_list' => fake()->boolean(80), // 80% chance of being displayed
            'sort_order'      => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Create a category that is displayed in lists
     */
    public function displayed(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_in_list' => true,
        ]);
    }

    /**
     * Create a category that is hidden from lists
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_in_list' => false,
        ]);
    }

    /**
     * Create a category with specific sort order
     */
    public function sortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }
}
