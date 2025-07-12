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
}
