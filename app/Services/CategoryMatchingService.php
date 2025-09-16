<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Services;

use App\Data\CategorySuggestionData;
use App\Data\DescriptionGroupData;
use App\Data\RuleConflictData;
use App\Data\RuleTestResultData;
use App\Data\SuggestedRuleData;
use App\Data\TransactionAnalysisData;
use App\Data\TransactionChangeData;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\DataCollection;
use Throwable;

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

    /**
     * Test a rule against recent transactions to see what it would match
     */
    public function testRule(CategoryRule $rule, int $limit = 50): RuleTestResultData
    {
        $cacheKey = "rule_test_{$rule->id}_$limit";

        return Cache::remember($cacheKey, 300, function () use ($rule, $limit) {
            try {
                // Get recent transactions that would match this rule
                $query = Transaction::with(['category', 'account'])
                                    ->orderBy('transaction_date', 'desc')
                                    ->limit($limit);

                // Apply account filter if rule is account-specific
                if ($rule->account_id) {
                    $query->where('account_id', $rule->account_id);
                }

                $recentTransactions = $query->get();
                $matchingTransactions = collect();

                foreach ($recentTransactions as $transaction) {
                    if ($rule->matches($transaction->description, (float) $transaction->amount)) {
                        $matchingTransactions->push($transaction);
                    }
                }

                // Check for conflicts with other rules
                $conflicts = $this->findRuleConflicts($rule, $matchingTransactions);

                // Create transaction change data
                $transactionChanges = $matchingTransactions->map(function (Transaction $transaction) use ($rule) {
                    return TransactionChangeData::from([
                        'transaction_id'    => $transaction->id,
                        'description'       => $transaction->description,
                        'amount'            => (float) $transaction->amount,
                        'date'              => $transaction->transaction_date,
                        'current_category'  => $transaction->category?->name,
                        'proposed_category' => $rule->category->name,
                        'account_name'      => $transaction->account->name,
                        'confidence'        => $this->calculateConfidence($rule, $transaction->description, (float) $transaction->amount),
                        'match_reason'      => $this->getMatchReason($rule),
                    ]);
                });

                return new RuleTestResultData(
                    totalMatches             : $matchingTransactions->count(),
                    totalAmount              : $matchingTransactions->sum('amount'),
                    uncategorizedMatches     : $matchingTransactions->whereNull('category_id')->count(),
                    alreadyCategorizedMatches: $matchingTransactions->whereNotNull('category_id')->count(),
                    transactions             : TransactionChangeData::collect($transactionChanges, DataCollection::class),
                    conflicts                : RuleConflictData::collect(collect($conflicts), DataCollection::class),
                );
            } catch (Exception $e) {
                Log::error('Error testing rule', [
                    'rule_id' => $rule->id,
                    'error'   => $e->getMessage(),
                ]);

                return new RuleTestResultData(
                    totalMatches             : 0,
                    totalAmount              : 0.0,
                    uncategorizedMatches     : 0,
                    alreadyCategorizedMatches: 0,
                    transactions             : TransactionChangeData::collect(collect(), DataCollection::class),
                    conflicts                : RuleConflictData::collect(collect(), DataCollection::class),
                    error                    : 'Error testing rule: '.$e->getMessage(),
                );
            }
        });
    }

    /**
     * Test multiple rules together to see their combined effect
     */
    public function testMultipleRules(Collection $rules): Collection
    {
        return $rules->map(function (CategoryRule $rule) {
            return [
                'rule'    => $rule,
                'results' => $this->testRule($rule),
            ];
        });
    }

    /**
     * Find rules that might conflict with the given rule
     */
    public function getConflictingRules(CategoryRule $rule): Collection
    {
        // Get recent transactions to test against
        $testTransactions = Transaction::with(['category', 'account'])
                                       ->orderBy('transaction_date', 'desc')
                                       ->limit(100)
                                       ->get();

        // Filter transactions that match the current rule
        $matchingTransactions = $testTransactions->filter(function ($transaction) use ($rule) {
            // Check account filter if applicable
            if ($rule->account_id && $rule->account_id !== $transaction->account_id) {
                return false;
            }

            return $rule->matches($transaction->description, (float) $transaction->amount);
        });

        // Get other rules that might conflict
        $existingRules = CategoryRule::whereHas('category', static function ($query) use ($rule) {
            $query->where('user_id', $rule->category->user_id);
        })
                                     ->where('id', '!=', $rule->id)
                                     ->with(['category'])
                                     ->get();

        $conflicts = collect();

        foreach ($existingRules as $existingRule) {
            // Check if this rule would match any of the same transactions
            $hasConflictingTransaction = $matchingTransactions->contains(function ($transaction) use ($existingRule) {
                // Check account filter if applicable
                if ($existingRule->account_id && $existingRule->account_id !== $transaction->account_id) {
                    return false;
                }

                return $existingRule->matches($transaction->description, (float) $transaction->amount);
            });

            if ($hasConflictingTransaction) {
                // Determine the type of conflict
                $conflictType = $this->detectConflictType($rule, $existingRule);
                if (! $conflictType) {
                    // If no specific type detected, it's still a transaction overlap
                    $conflictType = 'transaction_overlap';
                }

                $conflicts->push($existingRule);
            }
        }

        return $conflicts;
    }

    /**
     * Apply rules to existing transactions in bulk
     *
     * @throws Throwable
     */
    public function applyRulesToTransactions(User $user, array $ruleIds = [], int $limit = 100): array
    {
        $results = [
            'processed'      => 0,
            'categorized'    => 0,
            'recategorized'  => 0,
            'errors'         => 0,
            'error_messages' => [],
        ];

        try {
            DB::beginTransaction();

            // Get rules to apply
            $rules = empty($ruleIds)
                ? $this->getAllUserRules($user)
                : CategoryRule::whereIn('id', $ruleIds)->with(['category'])->byPriority()->get();

            // Get uncategorized or specified transactions
            $query = Transaction::whereHas('account', static function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                                ->with(['category', 'account'])
                                ->orderBy('transaction_date', 'desc')
                                ->limit($limit);

            // Note: When no specific rule IDs provided, we check ALL transactions
            // to allow for recategorization based on priority

            $transactions = $query->get();

            foreach ($transactions as $transaction) {
                $results['processed']++;

                // Find the first matching rule
                $matchingRule = null;
                foreach ($rules as $rule) {
                    // Skip if rule is account-specific and doesn't match
                    if ($rule->account_id && $rule->account_id !== $transaction->account_id) {
                        continue;
                    }

                    if ($rule->matches($transaction->description, (float) $transaction->amount)) {
                        $matchingRule = $rule;
                        break;
                    }
                }

                if ($matchingRule) {
                    $wasAlreadyCategorized = $transaction->category_id !== null;
                    $shouldApply = true;

                    // Check if we should apply based on priority
                    if ($wasAlreadyCategorized && $transaction->applied_rule_id) {
                        // Get the previously applied rule
                        $previousRule = CategoryRule::find($transaction->applied_rule_id);

                        // Only apply if new rule has higher priority (lower number)
                        if ($previousRule && $previousRule->priority <= $matchingRule->priority) {
                            $shouldApply = false;
                        }
                    }
                    // If transaction is categorized but has no applied_rule_id,
                    // we consider it manually categorized with lowest priority, so any rule can override it

                    if ($shouldApply) {
                        // Apply the rule
                        $transaction->applyRule($matchingRule);

                        if ($wasAlreadyCategorized) {
                            $results['recategorized']++;
                        } else {
                            $results['categorized']++;
                        }
                    }
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error applying rules to transactions', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            $results['errors']++;
            $results['error_messages'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Get all rules for a user (for bulk operations)
     */
    private function getAllUserRules(User $user): Collection
    {
        $cacheKey = "all_category_rules_user_$user->id";

        return Cache::remember($cacheKey, 3600, static function () use ($user) {
            return CategoryRule::whereHas('category', static function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                               ->with(['category', 'account'])
                               ->byPriority()
                               ->get();
        });
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

    /**
     * Find conflicts between rules for specific transactions
     */
    private function findRuleConflicts(CategoryRule $rule, Collection $transactions): array
    {
        if ($transactions->isEmpty()) {
            return [];
        }

        // Get other rules that could also match these transactions
        $otherRules = CategoryRule::whereHas('category', static function ($query) use ($rule) {
            $query->where('user_id', $rule->category->user_id);
        })
                                  ->where('id', '!=', $rule->id)
                                  ->with(['category'])
                                  ->get();

        $conflicts = [];

        foreach ($otherRules as $otherRule) {
            $conflictingTransactionCount = 0;

            foreach ($transactions as $transaction) {
                if ($otherRule->matches($transaction->description, (float) $transaction->amount)) {
                    $conflictingTransactionCount++;
                }
            }

            if ($conflictingTransactionCount > 0) {
                $conflictType = $this->detectConflictType($rule, $otherRule);
                if ($conflictType) {
                    $conflicts[] = RuleConflictData::fromRules($rule, $otherRule, $conflictType, $conflictingTransactionCount);
                }
            }
        }

        return $conflicts;
    }

    /**
     * Detect the type of conflict between two rules
     */
    private function detectConflictType(CategoryRule $rule1, CategoryRule $rule2): ?string
    {
        // Exact match conflict
        if ($rule1->field === $rule2->field &&
            $rule1->operator === $rule2->operator &&
            mb_strtolower($rule1->value) === mb_strtolower($rule2->value)) {
            return 'exact_match';
        }

        // Category conflict (same category, different conditions)
        if ($rule1->category_id === $rule2->category_id) {
            return 'category_conflict';
        }

        // Overlapping conditions
        if ($rule1->field === $rule2->field && $this->conditionsOverlap($rule1, $rule2)) {
            return 'overlapping_conditions';
        }

        // Similar patterns
        if ($rule1->field === 'description' && $rule2->field === 'description' &&
            $this->calculateSimilarity($rule1->value, $rule2->value) > 0.8) {
            return 'similar_patterns';
        }

        return null;
    }

    /**
     * Check if two rule conditions overlap
     */
    private function conditionsOverlap(CategoryRule $rule1, CategoryRule $rule2): bool
    {
        // For description rules
        if ($rule1->field === 'description') {
            return $this->descriptionConditionsOverlap($rule1, $rule2);
        }

        // For amount rules
        if ($rule1->field === 'amount') {
            return $this->amountConditionsOverlap($rule1, $rule2);
        }

        return false;
    }

    /**
     * Check if description conditions overlap
     */
    private function descriptionConditionsOverlap(CategoryRule $rule1, CategoryRule $rule2): bool
    {
        $value1 = mb_strtolower($rule1->value);
        $value2 = mb_strtolower($rule2->value);

        // Contains overlap
        if (($rule1->operator === 'contains' && str_contains($value2, $value1)) ||
            ($rule2->operator === 'contains' && str_contains($value1, $value2))) {
            return true;
        }

        // Starts with overlap
        if (($rule1->operator === 'starts_with' && str_starts_with($value2, $value1)) ||
            ($rule2->operator === 'starts_with' && str_starts_with($value1, $value2))) {
            return true;
        }

        // Ends with overlap
        if (($rule1->operator === 'ends_with' && str_ends_with($value2, $value1)) ||
            ($rule2->operator === 'ends_with' && str_ends_with($value1, $value2))) {
            return true;
        }

        return false;
    }

    /**
     * Check if amount conditions overlap
     */
    private function amountConditionsOverlap(CategoryRule $rule1, CategoryRule $rule2): bool
    {
        $amount1 = (float) $rule1->value;
        $amount2 = (float) $rule2->value;

        // Same amount with different operators can overlap
        if (abs($amount1 - $amount2) < 0.01) {
            return true;
        }

        // Range overlaps (greater_than and less_than)
        if (($rule1->operator === 'greater_than' && $rule2->operator === 'less_than' && $amount1 < $amount2) ||
            ($rule2->operator === 'greater_than' && $rule1->operator === 'less_than' && $amount2 < $amount1)) {
            return true;
        }

        return false;
    }
}
