<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Services;

use App\Data\CategorySuggestionData;
use App\Data\DescriptionGroupData;
use App\Data\SuggestedRuleData;
use App\Data\TransactionAnalysisData;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelData\DataCollection;

final class CategoryMatchingService
{
    public function findMatchingCategory(User $user, string $description, float $amount, ?int $accountId = null): ?Category
    {
        $rules = $this->getCachedRules($user, $accountId);

        foreach ($rules as $rule) {
            if ($rule->matches($description, $amount)) {
                return $rule->category;
            }
        }

        return null;
    }

    public function suggestCategories(User $user, string $description, float $amount, ?int $accountId = null, int $limit = 5): DataCollection
    {
        $rules = $this->getCachedRules($user, $accountId);
        $suggestions = collect();

        // Find exact matches first
        foreach ($rules as $rule) {
            if ($rule->matches($description, $amount)) {
                $suggestions->push(CategorySuggestionData::from([
                    'category'   => $rule->category,
                    'confidence' => $this->calculateConfidence($rule, $description, $amount),
                    'reason'     => $this->getMatchReason($rule),
                ]));
            }
        }

        // Add fuzzy matches if we don't have enough suggestions
        if ($suggestions->count() < $limit) {
            $fuzzyMatches = $this->getFuzzyMatches($user, $description, $amount, $accountId, $limit - $suggestions->count());
            $suggestions = $suggestions->merge($fuzzyMatches);
        }

        return CategorySuggestionData::collect(
            $suggestions->sortByDesc('confidence')->take($limit)->values(),
            DataCollection::class,
        );
    }

    public function learnFromTransaction(User $user, string $description, float $amount, Category $category): void
    {
        // Extract meaningful keywords from description
        $keywords = $this->extractKeywords($description);

        foreach ($keywords as $keyword) {
            // Check if a similar rule already exists
            $existingRule = CategoryRule::where('category_id', $category->id)
                                        ->where('field', 'description')
                                        ->where('operator', 'contains')
                                        ->where('value', $keyword)
                                        ->first();

            if (! $existingRule) {
                CategoryRule::create([
                    'category_id' => $category->id,
                    'field'       => 'description',
                    'operator'    => 'contains',
                    'value'       => $keyword,
                    'priority'    => $this->calculatePriority($keyword),
                ]);
            }
        }

        // Create amount-based rules for round numbers
        if ($this->isRoundAmount($amount)) {
            $existingAmountRule = CategoryRule::where('category_id', $category->id)
                                              ->where('field', 'amount')
                                              ->where('operator', 'equals')
                                              ->where('value', (string) $amount)
                                              ->first();

            if (! $existingAmountRule) {
                CategoryRule::create([
                    'category_id' => $category->id,
                    'field'       => 'amount',
                    'operator'    => 'equals',
                    'value'       => (string) $amount,
                    'priority'    => 2, // Lower priority than description rules
                ]);
            }
        }

        // Clear cache to ensure fresh rules are loaded
        $this->clearRulesCache($user);
    }

    public function analyzeTransactionPatterns(User $user): TransactionAnalysisData
    {
        // Get recent uncategorized transactions
        $uncategorizedTransactions = $user
            ->accounts()
            ->with('transactions')
            ->get()
            ->pluck('transactions')
            ->flatten()
            ->whereNull('category_id')
            ->take(100);

        $uncategorizedCount = $uncategorizedTransactions->count();

        // TODO: Implement auto-categorized count calculation
        $autoCategorizedCount = 0;

        // Group by similar descriptions
        $descriptionGroups = $uncategorizedTransactions
            ->groupBy(function ($transaction) {
                return $this->normalizeDescription($transaction->description);
            })
            ->filter(function ($group) {
                return $group->count() >= 2; // Only groups with multiple occurrences
            })
            ->sortByDesc(function ($group) {
                return $group->count();
            })
            ->take(10);

        $topDescriptions = collect();
        $suggestedRules = collect();

        foreach ($descriptionGroups as $normalizedDesc => $transactions) {
            $topDescriptions->push(DescriptionGroupData::from([
                'description'         => $normalizedDesc,
                'count'               => $transactions->count(),
                'total_amount'        => $transactions->sum('amount'),
                'sample_transactions' => $transactions->take(3)->values(),
            ]));

            // Suggest rules based on patterns
            $keywords = $this->extractKeywords($normalizedDesc);
            foreach ($keywords as $keyword) {
                $suggestedRules->push(SuggestedRuleData::from([
                    'field'              => 'description',
                    'operator'           => 'contains',
                    'value'              => $keyword,
                    'frequency'          => $transactions->count(),
                    'suggested_category' => null, // User will need to set this
                ]));
            }
        }

        return TransactionAnalysisData::from([
            'uncategorized_count'            => $uncategorizedCount,
            'auto_categorized_count'         => $autoCategorizedCount,
            'top_uncategorized_descriptions' => DescriptionGroupData::collect($topDescriptions, DataCollection::class),
            'suggested_rules'                => SuggestedRuleData::collect($suggestedRules, DataCollection::class),
        ]);
    }

    public function createRulesFromSuggestions(array $suggestions): Collection
    {
        $createdRules = collect();

        foreach ($suggestions as $suggestion) {
            if (isset($suggestion['category_id'])) {
                $rule = CategoryRule::create([
                    'category_id' => $suggestion['category_id'],
                    'field'       => $suggestion['field'],
                    'operator'    => $suggestion['operator'],
                    'value'       => $suggestion['value'],
                    'priority'    => $suggestion['priority'] ?? 1,
                ]);

                $createdRules->push($rule);
            }
        }

        return $createdRules;
    }

    private function getCachedRules(User $user, ?int $accountId = null): Collection
    {
        $cacheKey = "category_rules_user_$user->id";
        if ($accountId !== null) {
            $cacheKey .= "_account_$accountId";
        }

        return Cache::remember($cacheKey, 3600, static function () use ($user, $accountId) {
            return CategoryRule::whereHas('category', static function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                               ->forAccount($accountId)
                               ->with(['category', 'account'])
                               ->byPriority()
                               ->get();
        });
    }

    private function clearRulesCache(User $user, ?int $accountId = null): void
    {
        if ($accountId !== null) {
            Cache::forget("category_rules_user_{$user->id}_account_$accountId");
        } else {
            // Clear global rules cache for this user
            Cache::forget("category_rules_user_$user->id");

            // Note: For full wildcard clearing, we'd need Redis-specific implementation
            // For now, we clear the most common cache keys manually
            $user->accounts()->pluck('id')->each(function ($accountId) use ($user) {
                Cache::forget("category_rules_user_{$user->id}_account_$accountId");
            });
        }
    }

    private function getFuzzyMatches(User $user, string $description, float $amount, ?int $accountId, int $limit): Collection
    {
        $categories = $user->categories()->get();
        $fuzzyMatches = collect();

        foreach ($categories as $category) {
            $similarity = $this->calculateSimilarity($description, $category->name);

            if ($similarity > 0.3) { // 30% similarity threshold
                $fuzzyMatches->push(CategorySuggestionData::from([
                    'category'   => $category,
                    'confidence' => $similarity * 0.5, // Lower confidence for fuzzy matches
                    'reason'     => "Similar to category name: $category->name",
                ]));
            }
        }

        return $fuzzyMatches->sortByDesc('confidence')->take($limit);
    }

    private function calculateConfidence(CategoryRule $rule, string $description, float $amount): float
    {
        $baseConfidence = match ($rule->operator) {
            'equals'      => 1.0,
            'contains'    => 0.8,
            'starts_with' => 0.9,
            'ends_with'   => 0.7,
            'greater_than', 'less_than' => 0.6,
            default => 0.5
        };

        // Adjust based on rule priority (higher priority = higher confidence)
        $priorityMultiplier = 1 + (5 - $rule->priority) * 0.1;

        // Adjust based on value length for description rules
        if ($rule->field === 'description') {
            $valueLength = mb_strlen($rule->value);
            $lengthMultiplier = min(1.2, 1 + ($valueLength - 3) * 0.05);
            $baseConfidence *= $lengthMultiplier;
        }

        return min(1.0, $baseConfidence * $priorityMultiplier);
    }

    private function getMatchReason(CategoryRule $rule): string
    {
        return match ($rule->operator) {
            'equals'       => "Exact match for $rule->field: '$rule->value'",
            'contains'     => "Contains '$rule->value' in $rule->field",
            'starts_with'  => "$rule->field starts with '$rule->value'",
            'ends_with'    => "$rule->field ends with '$rule->value'",
            'greater_than' => "$rule->field is greater than $rule->value",
            'less_than'    => "$rule->field is less than $rule->value",
            default        => "Matches rule for $rule->field"
        };
    }

    private function extractKeywords(string $description): array
    {
        // Normalize and clean the description
        $normalized = $this->normalizeDescription($description);

        // Split into words and filter
        $words = preg_split('/\s+/', $normalized);
        $keywords = [];

        foreach ($words as $word) {
            // Skip common words and very short words
            if (mb_strlen($word) >= 3 && ! in_array(mb_strtolower($word), $this->getStopWords(), true)) {
                $keywords[] = $word;
            }
        }

        return array_unique($keywords);
    }

    private function normalizeDescription(string $description): string
    {
        // Remove common transaction patterns
        $patterns = [
            '/\d{4}\s*\d{4}\s*\d{4}\s*\d{4}/', // Credit card numbers
            '/\d{2}\/\d{2}\/\d{4}/', // Dates
            '/\$\d+\.\d{2}/', // Dollar amounts
            '/\b\d+\b/', // Other numbers
        ];

        $normalized = $description;
        foreach ($patterns as $pattern) {
            $normalized = preg_replace($pattern, '', $normalized);
        }

        // Clean up extra spaces and convert to lowercase
        return mb_trim(preg_replace('/\s+/', ' ', mb_strtolower($normalized)));
    }

    private function calculatePriority(string $keyword): int
    {
        $keywordLength = mb_strlen($keyword);

        // Longer keywords get higher priority (lower number)
        if ($keywordLength >= 8) {
            return 1;
        }
        if ($keywordLength >= 5) {
            return 2;
        }

        return 3;
    }

    private function isRoundAmount(float $amount): bool
    {
        return $amount === round($amount) && $amount >= 10;
    }

    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = mb_strtolower($str1);
        $str2 = mb_strtolower($str2);

        similar_text($str1, $str2, $percent);

        return $percent / 100;
    }

    private function getStopWords(): array
    {
        return [
            'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with',
            'by', 'from', 'up', 'about', 'into', 'through', 'during', 'before',
            'after', 'above', 'below', 'between', 'among', 'through', 'during',
            'purchase', 'payment', 'transaction', 'debit', 'credit', 'card',
            'pos', 'withdrawal', 'deposit', 'transfer', 'fee', 'charge',
        ];
    }
}
