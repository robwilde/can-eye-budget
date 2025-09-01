<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Data;

use App\Models\CategoryRule;
use Spatie\LaravelData\Data;

final class RuleConflictData extends Data
{
    public function __construct(
        public int $rule_id,
        public string $rule_description,
        public int $conflicting_rule_id,
        public string $conflicting_rule_description,
        public string $conflict_type,
        public string $resolution,
        public int $affected_transaction_count = 0,
    ) {}

    public static function fromRules(CategoryRule $rule, CategoryRule $conflictingRule, string $conflictType, int $transactionCount = 0): self
    {
        $resolution = $rule->priority <= $conflictingRule->priority
            ? "'{$rule->getDisplayName()}' will take precedence (higher priority)"
            : "'{$conflictingRule->getDisplayName()}' will take precedence (higher priority)";

        return new self(
            rule_id                     : $rule->id,
            rule_description            : $rule->getDisplayName(),
            conflicting_rule_id         : $conflictingRule->id,
            conflicting_rule_description: $conflictingRule->getDisplayName(),
            conflict_type               : $conflictType,
            resolution                  : $resolution,
            affected_transaction_count  : $transactionCount,
        );
    }

    public function isHighPriority(): bool
    {
        return in_array($this->conflict_type, ['exact_match', 'category_conflict'], true);
    }

    public function getSeverityLevel(): string
    {
        return match ($this->conflict_type) {
            'exact_match', 'category_conflict' => 'high',
            'overlapping_conditions' => 'medium',
            default                  => 'low',
        };
    }

    public function getConflictIcon(): string
    {
        return match ($this->getSeverityLevel()) {
            'high'   => 'exclamation-triangle',
            'medium' => 'exclamation-circle',
            default  => 'information-circle',
        };
    }
}
