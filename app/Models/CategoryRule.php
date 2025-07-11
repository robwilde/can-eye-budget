<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'field',
        'operator',
        'value',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    public function matches(string $description, float $amount): bool
    {
        $testValue = $this->field === 'description' ? $description : $amount;

        return match ($this->operator) {
            'contains' => str_contains(strtolower($testValue), strtolower($this->value)),
            'equals' => strtolower($testValue) === strtolower($this->value),
            'starts_with' => str_starts_with(strtolower($testValue), strtolower($this->value)),
            'ends_with' => str_ends_with(strtolower($testValue), strtolower($this->value)),
            'greater_than' => is_numeric($testValue) && (float) $testValue > (float) $this->value,
            'less_than' => is_numeric($testValue) && (float) $testValue < (float) $this->value,
            default => false
        };
    }

    public static function findMatchingCategory(string $description, float $amount): ?Category
    {
        $rules = static::with('category')
            ->byPriority()
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matches($description, $amount)) {
                return $rule->category;
            }
        }

        return null;
    }
}
