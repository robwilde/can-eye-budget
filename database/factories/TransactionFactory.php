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
        $transactionDate = $this->faker->dateTimeBetween('-1 year');

        // Determine status based on date: future = planned, today/past = entered
        $status = Carbon::instance($transactionDate)->greaterThan(Carbon::today()) ? 'planned' : 'entered';

        return [
            'type'             => $type,
            'amount'           => $this->faker->randomFloat(2, 1, 1000),
            'description'      => $this->faker->sentence(3),
            'transaction_date' => $transactionDate,
            'reconciled'       => $this->faker->boolean(20), // 20% chance of being reconciled
            'status'           => $status,
        ];
    }

    public function income(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
        ]);
    }

    public function expense(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
        ]);
    }

    public function transfer(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'transfer',
        ]);
    }

    public function reconciled(): self
    {
        return $this->state(fn (array $attributes) => [
            'reconciled' => true,
        ]);
    }

    public function recent(): self
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => $this->faker->dateTimeBetween('-30 days'),
        ]);
    }

    public function today(): self
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => Carbon::today(),
            'status'           => 'entered', // Today's transactions are entered
        ]);
    }

    public function planned(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planned',
        ]);
    }

    public function entered(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'entered',
        ]);
    }

    public function future(): self
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => $this->faker->dateTimeBetween('+1 day', '+1 year'),
            'status'           => 'planned', // Future transactions are planned
        ]);
    }
}
