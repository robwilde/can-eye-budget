<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

final class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'name'            => $this->faker->randomElement(['Checking', 'Savings', 'Credit Card']),
            'type'            => $this->faker->randomElement(['checking', 'savings', 'credit']),
            'initial_balance' => $this->faker->randomFloat(2, 0, 10000),
            'currency'        => 'USD',
        ];
    }

    /**
     * Indicate that the account is a checking account.
     */
    public function checking(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'checking',
            'name' => fake()->randomElement([
                'Primary Checking',
                'Business Checking',
                'Joint Checking',
                'Main Checking Account',
            ]),
            'initial_balance' => fake()->randomFloat(2, 500, 5000),
        ]);
    }

    /**
     * Indicate that the account is a savings account.
     */
    public function savings(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'savings',
            'name' => fake()->randomElement([
                'Emergency Fund',
                'High-Yield Savings',
                'Vacation Fund',
                'Investment Savings',
            ]),
            'initial_balance' => fake()->randomFloat(2, 1000, 15000),
        ]);
    }

    /**
     * Indicate that the account is a credit account.
     */
    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'credit',
            'name' => fake()->randomElement([
                'Main Credit Card',
                'Rewards Credit Card',
                'Business Credit Card',
                'Travel Credit Card',
            ]),
            'initial_balance' => fake()->randomFloat(2, -5000, -100),
        ]);
    }
}
