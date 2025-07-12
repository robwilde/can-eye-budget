<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;
use Kalnoy\Nestedset\QueryBuilder;

final class Category extends Model
{
    use HasFactory, NodeTrait;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'icon',
        'parent_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringPatterns(): HasMany
    {
        return $this->hasMany(RecurringPattern::class);
    }

    public function categoryRules(): HasMany
    {
        return $this->hasMany(CategoryRule::class);
    }

    public function scopeForUser($query, $userId): QueryBuilder
    {
        return $query->where('user_id', $userId);
    }

    public function getFullNameAttribute(): string
    {
        $ancestors = $this->ancestors()->pluck('name')->toArray();
        $ancestors[] = $this->name;

        return implode(' > ', $ancestors);
    }
}
