<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Import;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses()->group('import', 'status');

beforeEach(function () {
    Storage::fake('local');
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create(['user_id' => $this->user->id]);
    $this->importService = app(ImportService::class);
});

test('imported transactions are created with planned status', function () {
    // Create a CSV file with sample data
    $csvContent = "Date,Description,Amount\n2024-01-01,Test Expense,100.50\n2024-01-02,Test Income,-50.25";
    $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    // Create import record
    $import = $this->importService->createImport($this->user, $file);

    // Process the import with column mapping
    $columnMapping = [
        'date'        => 'Date',
        'description' => 'Description',
        'amount'      => 'Amount',
    ];

    $result = $this->importService->processImport($import, $this->account, $columnMapping);

    expect($result->success)->toBeTrue();
    expect($result->created_count)->toBe(2);

    // Check that all imported transactions have 'planned' status
    $importedTransactions = Transaction::where('import_id', $import->id)->get();

    expect($importedTransactions)->toHaveCount(2);

    foreach ($importedTransactions as $transaction) {
        expect($transaction->status)->toBe('planned');
        expect($transaction->isPlanned())->toBeTrue();
        expect($transaction->isEntered())->toBeFalse();
    }
});

test('imported transactions can be manually changed to entered status', function () {
    // Create an imported transaction
    $import = Import::factory()->create(['user_id' => $this->user->id]);

    $transaction = Transaction::factory()->create([
        'account_id'  => $this->account->id,
        'status'      => 'planned',
        'import_id'   => $import->id,
        'type'        => 'expense',
        'amount'      => 100,
        'description' => 'Imported expense',
    ]);

    expect($transaction->isPlanned())->toBeTrue();

    // Manually update to entered
    $transaction->update(['status' => 'entered']);
    $transaction->refresh();

    expect($transaction->isEntered())->toBeTrue();
    expect($transaction->isPlanned())->toBeFalse();
});

test('import service correctly processes mixed transaction types with planned status', function () {
    // Create CSV with mixed income/expense transactions (negative amounts are expenses, positive are income)
    $csvContent = "Date,Description,Amount\n2024-01-01,Salary,1000.00\n2024-01-02,Groceries,-50.25\n2024-01-03,Freelance Income,250.00";
    $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    $import = $this->importService->createImport($this->user, $file);

    $columnMapping = [
        'date'        => 'Date',
        'description' => 'Description',
        'amount'      => 'Amount',
    ];

    $result = $this->importService->processImport($import, $this->account, $columnMapping);

    expect($result->success)->toBeTrue();
    expect($result->created_count)->toBe(3);

    $transactions = Transaction::where('import_id', $import->id)->get();

    // All should be planned status regardless of type
    foreach ($transactions as $transaction) {
        expect($transaction->status)->toBe('planned');
        expect($transaction->isPlanned())->toBeTrue();
    }

    // Check specific transaction types were detected correctly
    $salaryTransaction = $transactions->where('description', 'Salary')->first();
    expect($salaryTransaction->type)->toBe('income');
    expect($salaryTransaction->status)->toBe('planned');

    $groceriesTransaction = $transactions->where('description', 'Groceries')->first();
    expect($groceriesTransaction->type)->toBe('expense');
    expect($groceriesTransaction->status)->toBe('planned');
});
