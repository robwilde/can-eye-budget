<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\CsvRowData;
use App\Data\ImportData;
use App\Data\ImportResultData;
use App\Data\TransactionData;
use App\Models\Account;
use App\Models\Import;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DateParsing\DateParserFactory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Spatie\LaravelData\Optional;
use Throwable;

final class ImportService
{
    private TransactionService $transactionService;

    private DateParserFactory $dateParserFactory;

    private CategoryMatchingService $categoryMatchingService;

    public function __construct(
        TransactionService $transactionService,
        CategoryMatchingService $categoryMatchingService,
        DateParserFactory $dateParserFactory,
    ) {
        $this->transactionService = $transactionService;
        $this->categoryMatchingService = $categoryMatchingService;
        $this->dateParserFactory = $dateParserFactory;
    }

    public function createImport(User $user, UploadedFile $file): Import
    {
        $filename = $file->getClientOriginalName();
        $path = $file->store('imports', 'local');

        return Import::create([
            'user_id'       => $user->id,
            'filename'      => $filename,
            'csv_file_path' => $path,
            'imported_at'   => now(),
            'status'        => 'pending',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function processImport(Import $import, Account $account, array $columnMapping): ImportResultData
    {
        $import->update(['status' => 'processing']);

        try {
            $csvData = $this->readCsvFile($import);
            $processedData = $this->processCsvData($csvData, $columnMapping);

            $duplicates = $this->detectDuplicates($account, $processedData);
            $uniqueTransactions = $processedData->reject(function ($csvRow) use ($duplicates) {
                return $duplicates->contains('csv_row_hash', $csvRow->csv_row_hash);
            });

            $categorizedTransactions = $this->autoCategorizeTransactions($import->user, $uniqueTransactions);

            $createdTransactions = $this->createTransactions($account, $categorizedTransactions, $import);

            $import->update([
                'status'        => 'completed',
                'row_count'     => $processedData->count(),
                'matched_count' => $createdTransactions->count(),
            ]);

            return new ImportResultData(
                success             : true,
                import              : ImportData::fromModel($import),
                created_count       : $createdTransactions->count(),
                duplicate_count     : $duplicates->count(),
                total_rows          : $processedData->count(),
                duplicates          : $duplicates,
                created_transactions: $createdTransactions,
            );
        } catch (Exception $e) {
            $import->update(['status' => 'failed']);

            return new ImportResultData(
                success             : false,
                import              : ImportData::fromModel($import),
                created_count       : 0,
                duplicate_count     : 0,
                total_rows          : 0,
                duplicates          : collect(),
                created_transactions: collect(),
                error               : $e->getMessage(),
            );
        }
    }

    public function previewImport(UploadedFile $file, array $columnMapping, int $previewRows = 10): array
    {
        $csvData = $this->readCsvFromFile($file);
        $processedData = $this->processCsvData($csvData->take($previewRows), $columnMapping);

        return [
            'total_rows'       => $csvData->count(),
            'preview_data'     => $processedData,
            'column_mapping'   => $columnMapping,
            'detected_columns' => $this->detectColumns(array_keys((array) $csvData->first())),
        ];
    }

    public function detectColumns(array $headers): array
    {
        $detectedMapping = [];
        $originalHeaders = $headers;
        $headers = array_map('strtolower', $headers);

        $columnPatterns = config('import.column_patterns');

        foreach ($columnPatterns as $field => $patterns) {
            foreach ($headers as $index => $header) {
                foreach ($patterns as $pattern) {
                    if (str_contains($header, $pattern)) {
                        $detectedMapping[$field] = $originalHeaders[$index];
                        break 2;
                    }
                }
            }
        }

        return $detectedMapping;
    }

    /**
     * @throws JsonException
     */
    public function getDuplicates(Account $account, Collection $csvData): Collection
    {
        return $this->detectDuplicates($account, $csvData);
    }

    public function resolveDuplicates(Import $import, array $resolutions): int
    {
        $resolvedCount = 0;

        foreach ($resolutions as $resolution) {
            if ($resolution['action'] === 'import') {
                $this->createTransactionFromResolution($import, $resolution);
                $resolvedCount++;
            }
            // 'skip' action requires no processing
        }

        return $resolvedCount;
    }

    private function readCsvFile(Import $import): Collection
    {
        $content = Storage::disk('local')->get($import->csv_file_path);

        $csvData = collect();
        $lines = explode("\n", $content);

        if (empty($lines)) {
            return $csvData;
        }

        $headers = str_getcsv(array_shift($lines));

        foreach ($lines as $line) {
            $line = mb_trim($line);
            if (! empty($line)) {
                $row = str_getcsv($line);
                if (count($row) === count($headers)) {
                    $csvData->push(array_combine($headers, $row));
                }
            }
        }

        return $csvData;
    }

    private function readCsvFromFile(UploadedFile $file): Collection
    {
        return $this->readCsvFromPath($file->getRealPath());
    }

    private function readCsvFromPath(string $path): Collection
    {
        $csvData = collect();

        if (($handle = fopen($path, 'rb')) !== false) {
            $headers = fgetcsv($handle, 0, ',', '"');

            while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
                if (count($row) === count($headers)) {
                    $csvData->push(array_combine($headers, $row));
                }
            }

            fclose($handle);
        }

        return $csvData;
    }

    private function processCsvData(Collection $csvData, array $columnMapping): Collection
    {
        return $csvData->map(function ($row) use ($columnMapping) {
            $processed = [
                'raw_data'     => $row,
                'csv_row_hash' => $this->hashCsvRow($row),
            ];

            foreach ($columnMapping as $field => $csvColumn) {
                if (isset($row[$csvColumn])) {
                    $processed[$field] = $this->normalizeValue($field, $row[$csvColumn]);
                }
            }

            // Handle priority date selection: prefer effective_date over date if both exist
            // If effective date is empty/null but entered date exists, use entered date
            if (empty($processed['date']) && ! empty($processed['entered_date'])) {
                $processed['date'] = $processed['entered_date'];
            }

            // Handle separate debit/credit columns
            if (isset($processed['debit'], $processed['credit'])) {
                $debit = (float) ($processed['debit'] ?: 0);
                $credit = (float) ($processed['credit'] ?: 0);

                if ($debit > 0) {
                    $processed['amount'] = $debit;
                    $processed['type'] = 'expense';
                } elseif ($credit > 0) {
                    $processed['amount'] = $credit;
                    $processed['type'] = 'income';
                }
            } elseif (isset($processed['amount'])) {
                // Determine type based on amount sign
                $amount = (float) $processed['amount'];
                if ($amount < 0) {
                    $processed['amount'] = abs($amount);
                    $processed['type'] = 'expense';
                } else {
                    $processed['type'] = 'income';
                }
            }

            return CsvRowData::fromArray($processed);
        });
    }

    private function normalizeValue(string $field, string $value): string|null|float|Carbon
    {
        $value = mb_trim($value);

        return match ($field) {
            'date' => $this->parseDate($value),
            'amount', 'debit', 'credit' => $this->parseAmount($value),
            default => $value
        };
    }

    private function parseDate(string $value): ?Carbon
    {
        $parser = $this->dateParserFactory->make();
        $parsed = $parser->parse($value);

        // Convert to Carbon instance for consistency with existing code
        return $parsed ? Carbon::instance($parsed) : null;
    }

    private function parseAmount(string $value): float
    {
        // Remove currency symbols and spaces
        $cleaned = preg_replace('/[^\d.\-+]/', '', $value);

        return (float) $cleaned;
    }

    /**
     * @throws JsonException
     */
    private function hashCsvRow(array $row): string
    {
        return md5(json_encode($row, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    private function detectDuplicates(Account $account, Collection $csvData): Collection
    {
        $duplicates = collect();
        $dateRange = $this->getDateRangeFromCsv($csvData);

        // Get existing transactions in the date range
        $existingTransactions = $account
            ->transactions()
            ->whereBetween('transaction_date', $dateRange)
            ->get();

        foreach ($csvData as $csvRow) {
            $potentialDuplicates = $existingTransactions->filter(function ($transaction) use ($csvRow) {
                return $this->isLikelyDuplicate($transaction, $csvRow);
            });

            if ($potentialDuplicates->isNotEmpty()) {
                $duplicates->push([
                    'csv_row'               => $csvRow,
                    'csv_row_hash'          => $this->hashCsvRow($csvRow->raw_data),
                    'existing_transactions' => $potentialDuplicates->values(),
                    'confidence'            => $this->calculateDuplicateConfidence($potentialDuplicates->first(), $csvRow),
                ]);
            }
        }

        return $duplicates;
    }

    private function isLikelyDuplicate(Transaction $transaction, CsvRowData $csvRow): bool
    {
        // Check date match (with configurable tolerance)
        $csvDate = $csvRow->date;
        $dateTolerance = config('import.date_tolerance_days', 2);
        if (! $csvDate || abs($transaction->transaction_date->diffInDays($csvDate)) > $dateTolerance) {
            return false;
        }

        // Check amount match
        if (abs((float) $transaction->amount - $csvRow->amount) > 0.01) {
            return false;
        }

        // Check description similarity
        $descriptionSimilarity = $this->calculateSimilarity(
            $transaction->description,
            $csvRow->description,
        );

        $threshold = config('import.duplicate_threshold', 0.7);

        return $descriptionSimilarity > $threshold;
    }

    private function calculateDuplicateConfidence(Transaction $transaction, CsvRowData $csvRow): float
    {
        $factors = [];

        // Date exactness
        $daysDiff = abs($transaction->transaction_date->diffInDays($csvRow->date));
        $factors['date'] = max(0, 1 - ($daysDiff * 0.5));

        // Amount exactness
        $amountDiff = abs((float) $transaction->amount - $csvRow->amount);
        $factors['amount'] = $amountDiff < 0.01 ? 1.0 : 0.5;

        // Description similarity
        $factors['description'] = $this->calculateSimilarity(
            $transaction->description,
            $csvRow->description,
        );

        return collect($factors)->average();
    }

    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = mb_strtolower(mb_trim($str1));
        $str2 = mb_strtolower(mb_trim($str2));

        similar_text($str1, $str2, $percent);

        return $percent / 100;
    }

    private function getDateRangeFromCsv(Collection $csvData): array
    {
        $dates = $csvData->pluck('date')->filter();

        return [
            $dates->min()->subDays(1), // Add buffer
            $dates->max()->addDays(1), // Add buffer
        ];
    }

    private function autoCategorizeTransactions(User $user, Collection $transactions): Collection
    {
        return $transactions->map(function (CsvRowData $csvRow) use ($user) {
            $category = $this->categoryMatchingService->findMatchingCategory(
                $user,
                $csvRow->description,
                $csvRow->amount,
            );

            $csvRow->suggested_category = $category;
            $csvRow->category_id = $category?->id;

            return $csvRow;
        });
    }

    /**
     * @throws Throwable
     */
    private function createTransactions(Account $account, Collection $transactions, Import $import): Collection
    {
        $createdTransactions = collect();

        DB::transaction(function () use ($account, $transactions, $import, &$createdTransactions) {
            foreach ($transactions as $csvRow) {
                $transactionData = new TransactionData(
                    id                 : Optional::create(),
                    account_id         : $account->id,
                    type               : $csvRow->type,
                    amount             : $csvRow->amount,
                    description        : $csvRow->description,
                    transaction_date   : $csvRow->date,
                    category_id        : $csvRow->category_id instanceof Optional ? null : $csvRow->category_id,
                    transferToAccountId: Optional::create(),
                    recurringPatternId : Optional::create(),
                    importId           : $import->id,
                    reconciled         : false,
                    status             : 'entered',
                    account            : Optional::create(),
                    category           : Optional::create(),
                    transferToAccount  : Optional::create(),
                    recurringPattern   : Optional::create(),
                    import             : Optional::create(),
                    signed_amount      : Optional::create(),
                    is_transfer        : Optional::create(),
                    is_recurring       : Optional::create(),
                );

                $transaction = $this->transactionService->createTransaction(
                    $account->user,
                    $transactionData,
                );

                $createdTransactions->push($transaction);
            }
        });

        return $createdTransactions;
    }

    private function createTransactionFromResolution(Import $import, array $resolution): void
    {
        $csvRow = $resolution['csv_row'];
        $account = Account::find($resolution['account_id']);

        $transactionData = [
            'account_id'       => $account->id,
            'type'             => $csvRow->type,
            'amount'           => $csvRow->amount,
            'description'      => $csvRow->description,
            'transaction_date' => $csvRow->date->toDateString(),
            'category_id'      => $resolution['category_id'] ?? null,
            'import_id'        => $import->id,
        ];

        $this->transactionService->createTransaction(
            $import->user,
            $transactionData,
        );
    }
}
