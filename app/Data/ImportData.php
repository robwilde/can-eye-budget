<?php

namespace App\Data;

use App\Models\User;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ImportData extends Data
{
    public function __construct(
        public Optional|int $id,

        #[Required]
        public int $user_id,

        #[Required]
        public string $filename,

        #[Required, WithCast(DateTimeInterfaceCast::class)]
        public Carbon $imported_at,

        public int $row_count,
        public int $matched_count,

        #[Rule('in:pending,processing,completed,failed')]
        public string $status,

        // Relationships
        public Optional|User $user,

        // Computed properties
        public Optional|float $match_percentage,
        public Optional|bool $is_complete,
        public Optional|bool $is_failed,
        public Optional|bool $is_processing,
    ) {
        // Set defaults
        $this->row_count = $this->row_count ?? 0;
        $this->matched_count = $this->matched_count ?? 0;
        $this->status = $this->status ?? 'pending';

        // Compute derived values
        $this->match_percentage = $this->row_count > 0
            ? round(($this->matched_count / $this->row_count) * 100, 2)
            : 0;

        $this->is_complete = $this->status === 'completed';
        $this->is_failed = $this->status === 'failed';
        $this->is_processing = in_array($this->status, ['pending', 'processing']);
    }

    public static function fromModel(\App\Models\Import $import): self
    {
        return new self(
            id: $import->id,
            user_id: $import->user_id,
            filename: $import->filename,
            imported_at: $import->imported_at,
            row_count: $import->row_count,
            matched_count: $import->matched_count,
            status: $import->status,
            user: Optional::create()->when($import->relationLoaded('user'), $import->user),
        );
    }

    public function toCreateArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'filename' => $this->filename,
            'imported_at' => $this->imported_at,
            'row_count' => $this->row_count,
            'matched_count' => $this->matched_count,
            'status' => $this->status,
        ];
    }
}
