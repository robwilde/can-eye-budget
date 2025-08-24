<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Import;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

final class ImportHistory extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public string $sortBy = 'imported_at';

    public string $sortDirection = 'desc';

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'sortBy' => ['except' => 'imported_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function getImportsProperty()
    {
        $query = Auth::user()->imports();

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(10);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function deleteImport(Import $import): void
    {
        // Ensure user owns the import
        if ($import->user_id !== Auth::id()) {
            abort(403);
        }

        // Delete associated CSV file if it exists
        if ($import->csv_file_path && Storage::disk('local')->exists($import->csv_file_path)) {
            Storage::disk('local')->delete($import->csv_file_path);
        }

        // Delete associated transactions
        $import->transactions()->delete();

        // Delete the import record
        $import->delete();

        $this->dispatch('import-deleted', [
            'message' => 'Import deleted successfully',
            'import_id' => $import->id,
        ]);
    }

    public function downloadCsvFile(Import $import): void
    {
        $this->dispatch('import-info', [
            'message' => 'File download feature coming soon',
        ]);
    }

    public function reprocessImport(Import $import): void
    {
        // Ensure user owns the import
        if ($import->user_id !== Auth::id()) {
            abort(403);
        }

        if ($import->status !== 'failed') {
            $this->dispatch('import-error', [
                'message' => 'Only failed imports can be reprocessed',
            ]);
            return;
        }

        // Redirect to import wizard with this import
        $this->redirect(route('import.wizard', ['retry' => $import->id]));
    }

    public function getStatusCounts(): array
    {
        $imports = Auth::user()->imports();

        return [
            'all' => $imports->count(),
            'completed' => $imports->where('status', 'completed')->count(),
            'failed' => $imports->where('status', 'failed')->count(),
            'processing' => $imports->where('status', 'processing')->count(),
            'pending' => $imports->where('status', 'pending')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.import-history', [
            'imports' => $this->getImportsProperty(),
            'statusCounts' => $this->getStatusCounts(),
        ])->layout('layouts.app');
    }
}