<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

covers(ImportService::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->for($this->user)->create();
    $this->category = Category::factory()->for($this->user)->create(['name' => 'Test Category']);
    
    Storage::fake('local');
    
    $this->importService = app(ImportService::class);
});

test('import service detects CSV columns from configuration', function () {
    $headers = ['Effective Date', 'Transaction Description', 'Amount', 'Balance'];
    
    $detectedMapping = $this->importService->detectColumns($headers);
    
    expect($detectedMapping)
        ->toHaveKey('date', 'Effective Date')
        ->toHaveKey('description', 'Transaction Description')
        ->toHaveKey('amount', 'Amount')
        ->toHaveKey('balance', 'Balance');
});

test('import service detects multiple date columns', function () {
    $headers = ['Effective Date', 'Entered Date', 'Transaction Description', 'Amount'];
    
    $detectedMapping = $this->importService->detectColumns($headers);
    
    expect($detectedMapping)
        ->toHaveKey('date', 'Effective Date')
        ->toHaveKey('entered_date', 'Entered Date');
});

test('import service handles bank statement CSV format', function () {
    // Create CSV matching our sample bank statement
    $csvContent = "Effective Date,Entered Date,Transaction Description,Amount,Balance\n";
    $csvContent .= ",01/03/2025,\"POS - #459939 - PAYPAL *PYPL Payin4      Sydney       AU #2892\",-22.25,953.72\n";
    $csvContent .= "05/03/2025,05/03/2025,\"Osko Payment From Robert E Wilde Ref#869804245\",400.00,1250.97\n";
    
    $csvFile = UploadedFile::fake()->createWithContent('bank_statement.csv', $csvContent);
    
    $columnMapping = [
        'date' => 'Effective Date',
        'entered_date' => 'Entered Date',
        'description' => 'Transaction Description',
        'amount' => 'Amount',
        'balance' => 'Balance',
    ];
    
    $preview = $this->importService->previewImport($csvFile, $columnMapping, 10);
    
    expect($preview['total_rows'])->toBe(2);
    expect($preview['preview_data'])->toHaveCount(2);
    
    $firstTransaction = $preview['preview_data'][0];
    expect($firstTransaction->type)->toBe('expense');
    expect($firstTransaction->amount)->toBe(22.25);
    expect($firstTransaction->description)->toBe('POS - #459939 - PAYPAL *PYPL Payin4      Sydney       AU #2892');
    
    $secondTransaction = $preview['preview_data'][1];
    expect($secondTransaction->type)->toBe('income');
    expect($secondTransaction->amount)->toBe(400.00);
});

test('import service handles empty effective dates by using entered dates', function () {
    $csvContent = "Effective Date,Entered Date,Transaction Description,Amount\n";
    $csvContent .= ",01/03/2025,\"Transaction with no effective date\",-22.25\n";
    
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    
    $columnMapping = [
        'date' => 'Effective Date',
        'entered_date' => 'Entered Date',
        'description' => 'Transaction Description',
        'amount' => 'Amount',
    ];
    
    $preview = $this->importService->previewImport($csvFile, $columnMapping, 10);
    
    $transaction = $preview['preview_data'][0];
    
    // Should use entered date when effective date is empty
    expect($transaction->date->format('m/d/Y'))->toBe('01/03/2025');
});

test('import service creates import record with metadata', function () {
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', "Date,Description,Amount\n01/03/2025,Test,-22.25\n");
    
    $import = $this->importService->createImport($this->user, $csvFile);
    
    expect($import->user_id)->toBe($this->user->id);
    expect($import->filename)->toBe('test.csv');
    expect($import->status)->toBe('pending');
    expect($import->imported_at)->not->toBeNull();
});

test('import service processes CSV with duplicate detection', function () {
    // Create existing transaction
    Transaction::factory()->for($this->account)->create([
        'transaction_date' => '2025-01-03',
        'description' => 'Existing Transaction',
        'amount' => 22.25,
        'type' => 'expense',
    ]);
    
    $csvContent = "Date,Description,Amount\n";
    $csvContent .= "01/03/2025,\"Existing Transaction\",-22.25\n";
    $csvContent .= "05/03/2025,\"New Transaction\",400.00\n";
    
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    $import = $this->importService->createImport($this->user, $csvFile);
    
    $columnMapping = [
        'date' => 'Date',
        'description' => 'Description',
        'amount' => 'Amount',
    ];
    
    $result = $this->importService->processImport($import, $this->account, $columnMapping);
    
    expect($result->success)->toBeTrue();
    expect($result->created_count)->toBe(1); // Only new transaction created
    expect($result->duplicate_count)->toBe(1); // Duplicate detected
    expect($result->total_rows)->toBe(2);
});

test('import service handles various date formats', function () {
    $csvContent = "Date,Description,Amount\n";
    $csvContent .= "2025-01-03,\"ISO Format\",-22.25\n";
    $csvContent .= "01/03/2025,\"US Format\",100.00\n";
    $csvContent .= "03/01/2025,\"UK Format\",200.00\n";
    
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    
    $columnMapping = [
        'date' => 'Date',
        'description' => 'Description',
        'amount' => 'Amount',
    ];
    
    $preview = $this->importService->previewImport($csvFile, $columnMapping, 10);
    
    expect($preview['preview_data'])->toHaveCount(3);
    
    foreach ($preview['preview_data'] as $transaction) {
        expect($transaction->date)->not->toBeNull();
        expect($transaction->date->year)->toBe(2025);
    }
});

test('import service handles debit credit columns', function () {
    $csvContent = "Date,Description,Debit,Credit\n";
    $csvContent .= "01/03/2025,\"Expense Transaction\",22.25,\n";
    $csvContent .= "05/03/2025,\"Income Transaction\",,400.00\n";
    
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    
    $columnMapping = [
        'date' => 'Date',
        'description' => 'Description',
        'debit' => 'Debit',
        'credit' => 'Credit',
    ];
    
    $preview = $this->importService->previewImport($csvFile, $columnMapping, 10);
    
    expect($preview['preview_data'])->toHaveCount(2);
    
    $expenseTransaction = $preview['preview_data'][0];
    expect($expenseTransaction->type)->toBe('expense');
    expect($expenseTransaction->amount)->toBe(22.25);
    
    $incomeTransaction = $preview['preview_data'][1];
    expect($incomeTransaction->type)->toBe('income');
    expect($incomeTransaction->amount)->toBe(400.00);
});

test('import service applies auto-categorization', function () {
    // Create a category rule for PAYPAL transactions
    $category = Category::factory()->for($this->user)->create(['name' => 'Online Shopping']);
    
    $csvContent = "Date,Description,Amount\n";
    $csvContent .= "01/03/2025,\"PAYPAL *STORE Purchase\",-22.25\n";
    
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    $import = $this->importService->createImport($this->user, $csvFile);
    
    $columnMapping = [
        'date' => 'Date',
        'description' => 'Description',
        'amount' => 'Amount',
    ];
    
    // Mock the category matching service to return our category
    $this->mock(App\Services\CategoryMatchingService::class, function ($mock) use ($category) {
        $mock->shouldReceive('findMatchingCategory')
             ->andReturn($category);
    });
    
    $result = $this->importService->processImport($import, $this->account, $columnMapping);
    
    expect($result->success)->toBeTrue();
    
    $createdTransaction = $this->account->transactions()->first();
    expect($createdTransaction->category_id)->toBe($category->id);
});

test('import service calculates duplicate confidence scores', function () {
    // Create similar but not identical transaction
    Transaction::factory()->for($this->account)->create([
        'transaction_date' => '2025-01-03',
        'description' => 'PAYPAL Store Purchase',
        'amount' => 22.25,
        'type' => 'expense',
    ]);
    
    $csvContent = "Date,Description,Amount\n";
    $csvContent .= "01/03/2025,\"PAYPAL *STORE Purchase\",-22.25\n";
    
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    
    $columnMapping = [
        'date' => 'Date',
        'description' => 'Description',
        'amount' => 'Amount',
    ];
    
    $preview = $this->importService->previewImport($csvFile, $columnMapping, 10);
    $csvData = $preview['preview_data'];
    
    $duplicates = $this->importService->getDuplicates($this->account, $csvData);
    
    expect($duplicates)->toHaveCount(1);
    
    $duplicate = $duplicates->first();
    expect($duplicate['confidence'])->toBeGreaterThan(0.8); // High confidence due to similarity
});