<?php

namespace App\Data;

use App\Models\Category;
use Spatie\LaravelData\Data;

class CategorySuggestionData extends Data
{
    public function __construct(
        public Category $category,
        public float $confidence,
        public string $reason,
    ) {}
}
