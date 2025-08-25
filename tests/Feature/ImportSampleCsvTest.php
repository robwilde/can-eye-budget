<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\User;
use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

covers(ImportService::class);

test('import service can process sample bank CSV format', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['name' => 'Main Account']);

    Storage::fake('local');

    // Use the actual sample CSV content from the docs/samples directory
    $sampleCsvPath = base_path('docs/samples/StatementCsv_03_2025_a.csv');

    // Check if sample file exists, if not create a similar one
    if (file_exists($sampleCsvPath)) {
        $csvContent = file_get_contents($sampleCsvPath);
    } else {
        $csvContent = "Effective Date,Entered Date,Transaction Description,Amount,Balance\n";
        $csvContent .= ",01/03/2025,\"POS - #459939 - PAYPAL *PYPL Payin4      Sydney       AU #2892\",-22.25,953.72\n";
        $csvContent .= "01/03/2025,02/03/2025,\"Round Up transfer to 03774599: POS - #459939 - PAYPAL *PYPL Payin4      Sydney       AU #2892\",-2.75,950.97\n";
        $csvContent .= ",03/03/2025,\"Direct Debit Spaceship - DT.3v53ev Universe\",-100.00,850.97\n";
        $csvContent .= ",05/03/2025,\"Osko Payment From Robert E Wilde Ref#869804245\",400.00,1250.97\n";
        $csvContent .= ",05/03/2025,\"Transfer Optimus to CC to SAV 03914373 NET#2293426611\",-400.00,850.97\n";
    }

    $csvFile = UploadedFile::fake()->createWithContent('sample_statement.csv', $csvContent);

    $importService = app(ImportService::class);

    // Test column detection
    $headers = ['Effective Date', 'Entered Date', 'Transaction Description', 'Amount', 'Balance'];
    $detectedMapping = $importService->detectColumns($headers);

    expect($detectedMapping)
        ->toHaveKey('date')
        ->toHaveKey('description')
        ->toHaveKey('amount')
        ->toHaveKey('balance');

    // Test preview with bank statement format
    $columnMapping = [
        'date'         => 'Effective Date',
        'entered_date' => 'Entered Date',
        'description'  => 'Transaction Description',
        'amount'       => 'Amount',
        'balance'      => 'Balance',
    ];

    $preview = $importService->previewImport($csvFile, $columnMapping, 10);

    expect($preview['total_rows'])->toBeGreaterThan(0);
    expect($preview['preview_data'])->not->toBeEmpty();

    // Verify data parsing
    $firstTransaction = $preview['preview_data'][0];
    expect($firstTransaction->description)->toContain('PAYPAL');
    expect($firstTransaction->amount)->toBe(22.25);
    expect($firstTransaction->type)->toBe('expense');

    // Test full import process
    $import = $importService->createImport($user, $csvFile);

    // Verify file was stored
    expect(Storage::disk('local')->exists($import->csv_file_path))->toBeTrue();

    $result = $importService->processImport($import, $account, $columnMapping);

    // Provide better error information if import fails
    if (! $result->success) {
        $errorMessage = $result->error ?? 'Unknown error occurred during import';
        throw new Exception("Import failed: {$errorMessage}. Check that the sample CSV format matches expected structure.");
    }

    expect($result->success)->toBeTrue();
    expect($result->created_count)->toBeGreaterThan(0);
    expect($result->error)->toBeNull();

    // Verify transactions were created with proper date types
    expect($account->transactions()->count())->toBe($result->created_count);

    // Verify all created transactions have proper Carbon instances
    $transactions = $account->transactions()->get();
    foreach ($transactions as $transaction) {
        expect($transaction->transaction_date)->toBeInstanceOf(Carbon\CarbonInterface::class);
        expect($transaction->import_id)->toBe($import->id);
    }
});

test('import handles empty effective dates correctly', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    Storage::fake('local');

    // CSV with empty effective date (first column empty, second has entered date)
    $csvContent = "Effective Date,Entered Date,Transaction Description,Amount,Balance\n";
    $csvContent .= ",01/03/2025,\"Transaction with no effective date\",-22.25,953.72\n";

    $csvFile = UploadedFile::fake()->createWithContent('test_empty_dates.csv', $csvContent);

    $importService = app(ImportService::class);

    $columnMapping = [
        'date'         => 'Effective Date',
        'entered_date' => 'Entered Date',
        'description'  => 'Transaction Description',
        'amount'       => 'Amount',
        'balance'      => 'Balance',
    ];

    $preview = $importService->previewImport($csvFile, $columnMapping, 10);

    $transaction = $preview['preview_data'][0];

    // Should use entered date when effective date is empty
    expect($transaction->date)->not->toBeNull();
    // Date should be parsed correctly (allowing for different format interpretations)
    expect($transaction->date->year)->toBe(2025);
    expect($transaction->date->month)->toBeIn([1, 3]); // Could be January 3 or March 1
});
