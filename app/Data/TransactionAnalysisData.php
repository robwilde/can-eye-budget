<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class TransactionAnalysisData extends Data
{
    public function __construct(
        public int $uncategorized_count,
        public int $auto_categorized_count,
        #[DataCollectionOf(DescriptionGroupData::class)]
        public DataCollection $top_uncategorized_descriptions,
        #[DataCollectionOf(SuggestedRuleData::class)]
        public DataCollection $suggested_rules,
    ) {}
}
