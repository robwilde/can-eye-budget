<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['income', 'expense', 'transfer']);

        return [
            'type'             => $type,
            'amount'           => $this->faker->randomFloat(2, 1, 1000),
            'description'      => $this->faker->sentence(3),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'reconciled'       => $this->faker->boolean(20), // 20% chance of being reconciled
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
        ]);
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'transfer',
        ]);
    }

    public function reconciled(): static
    {
        return $this->state(fn (array $attributes) => [
            'reconciled' => true,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => Carbon::today(),
        ]);
    }
}
