<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Category;
use Spatie\LaravelData\Data;

final class SuggestedRuleData extends Data
{
    public function __construct(
        public string $field,
        public string $operator,
        public string $value,
        public int $frequency,
        public ?Category $suggested_category = null,
    ) {}
}
