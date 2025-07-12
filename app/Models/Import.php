<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'imported_at',
        'row_count',
        'matched_count',
        'status',
    ];

    protected $casts = [
        'imported_at'   => 'datetime',
        'row_count'     => 'integer',
        'matched_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function getMatchPercentageAttribute(): float
    {
        if ($this->row_count === 0) {
            return 0;
        }

        return round(($this->matched_count / $this->row_count) * 100, 2);
    }

    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}
