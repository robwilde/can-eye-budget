<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CategoryRule;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CategoryRuleFactory extends Factory
{
    protected $model = CategoryRule::class;

    public function definition(): array
    {
        return [
            'field'    => $this->faker->randomElement(['description', 'amount']),
            'operator' => $this->faker->randomElement(['contains', 'equals', 'starts_with', 'ends_with', 'greater_than', 'less_than']),
            'value'    => $this->faker->randomElement(['Starbucks', 'Walmart', 'Amazon', 'Target', '100.00', '50.00']),
            'priority' => $this->faker->numberBetween(1, 10),
        ];
    }
}
