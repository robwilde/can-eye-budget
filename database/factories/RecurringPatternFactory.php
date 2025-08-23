<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringPattern;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringPattern>
 */
final class RecurringPatternFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecurringPattern::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $frequencies = ['daily', 'weekly', 'bi-weekly', 'monthly', 'yearly'];
        $types = ['income', 'expense'];

        $type = fake()->randomElement($types);

        return [
            'account_id'             => Account::factory(),
            'name'                   => fake()->words(2, true),
            'type'                   => $type,
            'amount'                 => fake()->randomFloat(2, 10, 1000),
            'description'            => fake()->sentence(),
            'category_id'            => Category::factory(),
            'transfer_to_account_id' => null,
            'frequency'              => fake()->randomElement($frequencies),
            'frequency_interval'     => fake()->numberBetween(1, 3),
            'start_date'             => fake()->dateTimeBetween('-6 months', 'now'),
            'end_date'               => fake()->optional(0.3)->dateTimeBetween('now', '+2 years'),
            'last_generated_date'    => fake()->optional(0.7)->dateTimeBetween('-3 months', 'now'),
            'is_active'              => fake()->boolean(80),
        ];
    }

    /**
     * Indicate that the recurring pattern is for income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'   => 'income',
            'amount' => fake()->randomFloat(2, 1000, 5000),
        ]);
    }

    /**
     * Indicate that the recurring pattern is for expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'   => 'expense',
            'amount' => fake()->randomFloat(2, 10, 500),
        ]);
    }

    /**
     * Indicate that the recurring pattern is for a transfer.
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'                   => 'transfer',
            'transfer_to_account_id' => Account::factory(),
            'amount'                 => fake()->randomFloat(2, 100, 2000),
        ]);
    }

    /**
     * Indicate that the recurring pattern is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the recurring pattern is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the recurring pattern is monthly.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency'          => 'monthly',
            'frequency_interval' => 1,
        ]);
    }

    /**
     * Indicate that the recurring pattern is weekly.
     */
    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency'          => 'weekly',
            'frequency_interval' => 1,
        ]);
    }

    /**
     * Indicate that the recurring pattern is due for generation.
     */
    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_generated_date' => fake()->dateTimeBetween('-1 month', '-1 week'),
            'is_active'           => true,
        ]);
    }
}
