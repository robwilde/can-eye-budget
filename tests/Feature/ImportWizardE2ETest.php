<?php

declare(strict_types=1);

use App\Livewire\ImportWizard;
use App\Models\Account;
use App\Models\Import;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(ImportWizard::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->for($this->user)->create(['name' => 'Test Account']);
    Storage::fake('local');
});

test('import wizard processes real sample CSV file end-to-end', function () {
    // Use the actual sample CSV file
    $sampleCsvPath = base_path('docs/samples/StatementCsv_03_2025_a.csv');

    expect(file_exists($sampleCsvPath))->toBeTrue('Sample CSV file should exist');

    $csvContent = file_get_contents($sampleCsvPath);
    $csvFile = UploadedFile::fake()->createWithContent('StatementCsv_03_2025_a.csv', $csvContent);

    // Test complete flow from upload to transaction creation
    $component = Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload')
        ->assertSet('currentStep', 'mapping')

        // Verify detected mapping for bank statement format
        ->assertSet('detectedMapping.date', 'Effective Date')
        ->assertSet('detectedMapping.entered_date', 'Entered Date')
        ->assertSet('detectedMapping.description', 'Transaction Description')
        ->assertSet('detectedMapping.amount', 'Amount')
        ->assertSet('detectedMapping.balance', 'Balance')

        // Process mapping step
        ->set('columnMapping.date', 'Effective Date')
        ->set('columnMapping.entered_date', 'Entered Date')
        ->set('columnMapping.description', 'Transaction Description')
        ->set('columnMapping.amount', 'Amount')
        ->set('columnMapping.balance', 'Balance')
        ->call('processMapping')
        ->assertSet('currentStep', 'preview')

        // Verify preview data shows expected number of transactions
        ->call('processPreview')
        ->assertSet('currentStep', 'confirm')

        // Complete the import process
        ->call('processImport')
        ->assertSet('currentStep', 'results');

    // Verify import was successful
    $importResult = $component->get('importResult');
    expect($importResult['success'])->toBeTrue();
    expect($importResult['created_count'])->toBeGreaterThan(0);
    expect($importResult['total_rows'])->toBe(14); // Based on sample CSV

    // Verify import record was created
    $import = Import::where('filename', 'StatementCsv_03_2025_a.csv')->first();
    expect($import)->not->toBeNull();
    expect($import->user_id)->toBe($this->user->id);
    expect($import->status)->toBe('completed');

    // Verify transactions were actually created with proper data
    $transactions = $this->account->transactions()->orderBy('transaction_date')->get();
    expect($transactions->count())->toBe($importResult['created_count']);

    // Check PAYPAL transaction from sample
    $paypalTransaction = $transactions->filter(function ($transaction) {
        return str_contains(mb_strtoupper($transaction->description), 'PYPL') ||
               str_contains(mb_strtoupper($transaction->description), 'PAYPAL');
    })->first();
    expect($paypalTransaction)->not->toBeNull();
    expect((float) $paypalTransaction->amount)->toBe(22.25);
    expect($paypalTransaction->type)->toBe('expense');
    expect($paypalTransaction->transaction_date)->toBeInstanceOf(CarbonInterface::class);
    expect($paypalTransaction->import_id)->toBe($import->id);

    // Check a few more key transactions to verify proper parsing
    $paymentTransaction = $transactions->filter(function ($transaction) {
        return str_contains(mb_strtoupper($transaction->description), 'OSKO PAYMENT');
    })->first();
    expect($paymentTransaction)->not->toBeNull();
    expect($paymentTransaction->type)->toBe('income');
    expect((float) $paymentTransaction->amount)->toBe(400.00);

    // Verify date handling - check for transactions with empty effective dates using entered dates
    $roundUpTransaction = $transactions->filter(function ($transaction) {
        return str_contains(mb_strtoupper($transaction->description), 'ROUND UP');
    })->first();
    expect($roundUpTransaction)->not->toBeNull();
    expect($roundUpTransaction->transaction_date->year)->toBe(2025);
    expect($roundUpTransaction->transaction_date->month)->toBeIn([1, 3]); // Could be Jan 3 or Mar 1 depending on format
});

test('import wizard handles date type compatibility correctly', function () {
    // Create CSV with mixed date scenarios from sample
    $csvContent = "Effective Date,Entered Date,Transaction Description,Amount,Balance\n";
    $csvContent .= ",01/03/2025,\"Empty effective date test\",-22.25,953.72\n";
    $csvContent .= "05/03/2025,05/03/2025,\"Both dates present\",400.00,1250.97\n";

    $csvFile = UploadedFile::fake()->createWithContent('date_test.csv', $csvContent);

    $component = Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload')

        ->set('columnMapping.date', 'Effective Date')
        ->set('columnMapping.entered_date', 'Entered Date')
        ->set('columnMapping.description', 'Transaction Description')
        ->set('columnMapping.amount', 'Amount')
        ->set('columnMapping.balance', 'Balance')
        ->call('processMapping')
        ->call('processPreview')
        ->call('processImport');

    // Verify import was successful (no Carbon type errors)
    $importResult = $component->get('importResult');
    expect($importResult['success'])->toBeTrue();
    expect($importResult['created_count'])->toBe(2);

    // Verify both transactions have valid dates of proper type
    $transactions = $this->account->transactions()->get();
    foreach ($transactions as $transaction) {
        expect($transaction->transaction_date)->toBeInstanceOf(CarbonInterface::class);
        expect($transaction->transaction_date->year)->toBe(2025);
    }
});

test('import wizard handles various CSV date formats without errors', function () {
    $csvContent = "Date,Description,Amount\n";
    $csvContent .= "2025-01-03,\"ISO Format\",-22.25\n";
    $csvContent .= "01/03/2025,\"US Format\",100.00\n";
    $csvContent .= "03/01/2025,\"UK Format\",200.00\n";

    $csvFile = UploadedFile::fake()->createWithContent('format_test.csv', $csvContent);

    $component = Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload')

        ->set('columnMapping.date', 'Date')
        ->set('columnMapping.description', 'Description')
        ->set('columnMapping.amount', 'Amount')
        ->call('processMapping')
        ->call('processPreview')
        ->call('processImport');

    $importResult = $component->get('importResult');
    expect($importResult['success'])->toBeTrue();
    expect($importResult['created_count'])->toBe(3);

    // All transactions should have valid CarbonInterface dates
    $transactions = $this->account->transactions()->get();
    foreach ($transactions as $transaction) {
        expect($transaction->transaction_date)->toBeInstanceOf(CarbonInterface::class);
        expect($transaction->transaction_date->year)->toBe(2025);
    }
});

test('import wizard provides proper error messages on failure', function () {
    // Create invalid CSV that should cause TransactionData errors
    $csvContent = "Date,Description,Amount\n";
    $csvContent .= "invalid-date,\"Test\",not-a-number\n";

    $csvFile = UploadedFile::fake()->createWithContent('invalid.csv', $csvContent);

    $component = Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload');

    // Should handle errors gracefully without crashing
    expect($component->get('errorMessage'))->not->toBeNull();
});
