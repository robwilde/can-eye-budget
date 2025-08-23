<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Import>
 */
final class ImportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Import::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'processing', 'completed', 'failed'];
        $rowCount = fake()->numberBetween(10, 500);
        $matchedCount = fake()->numberBetween(0, $rowCount);

        return [
            'user_id'  => User::factory(),
            'filename' => fake()->randomElement([
                'bank_export_2024_01.csv',
                'transactions_december.csv',
                'checking_account_export.csv',
                'credit_card_statement.csv',
                'savings_export.csv',
            ]),
            'imported_at'   => fake()->optional(0.8)->dateTimeBetween('-6 months', 'now'),
            'row_count'     => $rowCount,
            'matched_count' => $matchedCount,
            'status'        => fake()->randomElement($statuses),
        ];
    }

    /**
     * Indicate that the import is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => 'pending',
            'imported_at'   => null,
            'matched_count' => 0,
        ]);
    }

    /**
     * Indicate that the import is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => 'processing',
            'imported_at'   => null,
            'matched_count' => fake()->numberBetween(0, $attributes['row_count'] ?? 100),
        ]);
    }

    /**
     * Indicate that the import is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => 'completed',
            'imported_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    /**
     * Indicate that the import failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => 'failed',
            'imported_at'   => null,
            'matched_count' => 0,
        ]);
    }

    /**
     * Indicate a large import with many rows.
     */
    public function large(): static
    {
        $rowCount = fake()->numberBetween(1000, 5000);

        return $this->state(fn (array $attributes) => [
            'row_count'     => $rowCount,
            'matched_count' => fake()->numberBetween(
                (int) ($rowCount * 0.7),
                $rowCount
            ),
        ]);
    }

    /**
     * Indicate a small import with few rows.
     */
    public function small(): static
    {
        $rowCount = fake()->numberBetween(5, 25);

        return $this->state(fn (array $attributes) => [
            'row_count'     => $rowCount,
            'matched_count' => fake()->numberBetween(0, $rowCount),
        ]);
    }

    /**
     * Indicate high match percentage import.
     */
    public function highMatch(): static
    {
        return $this->state(function (array $attributes) {
            $rowCount = $attributes['row_count'] ?? 100;

            return [
                'matched_count' => fake()->numberBetween(
                    (int) ($rowCount * 0.8),
                    $rowCount
                ),
                'status'      => 'completed',
                'imported_at' => fake()->dateTimeBetween('-3 months', 'now'),
            ];
        });
    }

    /**
     * Indicate low match percentage import.
     */
    public function lowMatch(): static
    {
        return $this->state(function (array $attributes) {
            $rowCount = $attributes['row_count'] ?? 100;

            return [
                'matched_count' => fake()->numberBetween(
                    0,
                    (int) ($rowCount * 0.3)
                ),
                'status'      => 'completed',
                'imported_at' => fake()->dateTimeBetween('-3 months', 'now'),
            ];
        });
    }
}
