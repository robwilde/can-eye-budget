<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Account;
use App\Models\Import;
use App\Services\ImportService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

final class ImportWizard extends Component
{
    use WithFileUploads;

    public string $currentStep = 'upload';

    public array $steps = ['upload', 'mapping', 'preview', 'confirm', 'results'];

    public int $selectedAccountId = 0;

    #[Validate('required|file|mimes:csv,txt|max:10240')] // 10MB max
    public ?UploadedFile $csvFile = null;

    public array $csvHeaders = [];

    public array $columnMapping = [];

    public array $detectedMapping = [];

    public array $previewData = [];

    public int $totalRows = 0;

    public ?Import $import = null;

    public array $importResult = [];

    public string $errorMessage = '';

    protected ImportService $importService;

    public function boot(ImportService $importService): void
    {
        $this->importService = $importService;
    }

    public function mount(): void
    {
        $this->resetWizard();
    }

    public function resetWizard(): void
    {
        $this->currentStep = 'upload';
        $this->selectedAccountId = 0;
        $this->csvFile = null;
        $this->csvHeaders = [];
        $this->columnMapping = [];
        $this->detectedMapping = [];
        $this->previewData = [];
        $this->totalRows = 0;
        $this->import = null;
        $this->importResult = [];
        $this->errorMessage = '';
    }

    public function getUserAccountsProperty(): Collection
    {
        return Auth::user()->accounts()->orderBy('name')->get();
    }

    public function nextStep(): void
    {
        $currentIndex = array_search($this->currentStep, $this->steps, true);

        if ($currentIndex !== false && $currentIndex < count($this->steps) - 1) {
            $this->currentStep = $this->steps[$currentIndex + 1];
        }
    }

    public function previousStep(): void
    {
        $currentIndex = array_search($this->currentStep, $this->steps, true);

        if ($currentIndex !== false && $currentIndex > 0) {
            $this->currentStep = $this->steps[$currentIndex - 1];
        }
    }

    public function goToStep(string $step): void
    {
        if (in_array($step, $this->steps, true)) {
            $this->currentStep = $step;
        }
    }

    public function processUpload(): void
    {
        $this->validate([
            'selectedAccountId' => 'required|exists:accounts,id',
            'csvFile'           => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            // Read CSV headers
            $this->csvHeaders = $this->readCsvHeaders();

            // Auto-detect column mapping
            $this->detectedMapping = $this->importService->detectColumns($this->csvHeaders);
            $this->columnMapping = $this->detectedMapping;

            $this->nextStep();
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to process CSV file: '.$e->getMessage();
        }
    }

    public function processMapping(): void
    {
        $this->validate([
            'columnMapping.date'        => 'required',
            'columnMapping.description' => 'required',
        ]);

        // Validate that we have either amount OR both debit/credit
        if (empty($this->columnMapping['amount']) &&
            (empty($this->columnMapping['debit']) || empty($this->columnMapping['credit']))) {
            $this->addError('columnMapping.amount', 'You must map either Amount OR both Debit and Credit columns.');

            return;
        }

        try {
            // Generate preview data
            $previewResult = $this->importService->previewImport(
                $this->csvFile,
                $this->columnMapping,
            );

            $this->previewData = $previewResult['preview_data']->toArray();
            $this->totalRows = $previewResult['total_rows'];

            $this->nextStep();
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to process column mapping: '.$e->getMessage();
        }
    }

    public function processPreview(): void
    {
        // User has reviewed the preview, proceed to confirmation
        $this->nextStep();
    }

    public function processImport(): void
    {
        try {
            $account = Account::findOrFail($this->selectedAccountId);

            // Create import record
            $this->import = $this->importService->createImport(Auth::user(), $this->csvFile);

            // Process the import
            $result = $this->importService->processImport(
                $this->import,
                $account,
                $this->columnMapping,
            );

            $this->importResult = [
                'success'         => $result->success,
                'created_count'   => $result->created_count,
                'duplicate_count' => $result->duplicate_count,
                'total_rows'      => $result->total_rows,
                'error'           => $result->error ?? null,
            ];

            $this->nextStep();
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to import CSV: '.$e->getMessage();
        }
    }

    public function startOver(): void
    {
        $this->resetWizard();
    }

    public function getSelectedAccountProperty(): ?Account
    {
        return $this->selectedAccountId > 0 ? Account::find($this->selectedAccountId) : null;
    }

    public function getAvailableColumnsProperty(): array
    {
        return array_map(static function ($index, $header) {
            return [
                'value' => $header,
                'label' => $header,
                'index' => $index,
            ];
        }, array_keys($this->csvHeaders), $this->csvHeaders);
    }

    public function getCurrentStepIndexProperty(): int
    {
        return array_search($this->currentStep, $this->steps, true) ?: 0;
    }

    public function getProgressPercentageProperty(): float
    {
        return ($this->getCurrentStepIndexProperty() / (count($this->steps) - 1)) * 100;
    }

    public function render(): View
    {
        return view('livewire.import-wizard')->layout('components.layouts.app', ['title' => 'Import Wizard']);
    }

    private function readCsvHeaders(): array
    {
        $path = $this->csvFile->getRealPath();
        $headers = [];

        if (($handle = fopen($path, 'rb')) !== false) {
            $headers = fgetcsv($handle) ?: [];
            fclose($handle);
        }

        return $headers;
    }
}
