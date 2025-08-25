<?php

declare(strict_types=1);

use App\Data\CsvRowData;
use App\Data\TransactionData;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Optional;

covers(TransactionData::class, CsvRowData::class);

test('TransactionData accepts Carbon instances', function () {
    $carbon = Carbon::createFromFormat('Y-m-d', '2025-01-03');

    $transactionData = new TransactionData(
        id: Optional::create(),
        account_id: 1,
        type: 'expense',
        amount: 100.0,
        description: 'Test with Carbon',
        transaction_date: $carbon,
        category_id: Optional::create(),
        transferToAccountId: Optional::create(),
        recurringPatternId: Optional::create(),
        importId: Optional::create(),
        reconciled: Optional::create(),
        status: Optional::create(),
        account: Optional::create(),
        category: Optional::create(),
        transferToAccount: Optional::create(),
        recurringPattern: Optional::create(),
        import: Optional::create(),
        signed_amount: Optional::create(),
        is_transfer: Optional::create(),
        is_recurring: Optional::create(),
    );

    expect($transactionData->transaction_date)->toBeInstanceOf(CarbonInterface::class);
    expect($transactionData->transaction_date->format('Y-m-d'))->toBe('2025-01-03');
});

test('TransactionData accepts CarbonImmutable instances', function () {
    $carbonImmutable = CarbonImmutable::createFromFormat('Y-m-d', '2025-01-03');

    $transactionData = new TransactionData(
        id: Optional::create(),
        account_id: 1,
        type: 'expense',
        amount: 100.0,
        description: 'Test with CarbonImmutable',
        transaction_date: $carbonImmutable,
        category_id: Optional::create(),
        transferToAccountId: Optional::create(),
        recurringPatternId: Optional::create(),
        importId: Optional::create(),
        reconciled: Optional::create(),
        status: Optional::create(),
        account: Optional::create(),
        category: Optional::create(),
        transferToAccount: Optional::create(),
        recurringPattern: Optional::create(),
        import: Optional::create(),
        signed_amount: Optional::create(),
        is_transfer: Optional::create(),
        is_recurring: Optional::create(),
    );

    expect($transactionData->transaction_date)->toBeInstanceOf(CarbonInterface::class);
    expect($transactionData->transaction_date->format('Y-m-d'))->toBe('2025-01-03');
});

test('CsvRowData creates with Carbon instances', function () {
    $csvData = CsvRowData::fromArray([
        'raw_data'     => ['date' => '01/03/2025'],
        'csv_row_hash' => 'test-hash',
        'type'         => 'expense',
        'amount'       => 100.0,
        'description'  => 'Test Carbon creation',
        'date'         => '01/03/2025',
    ]);

    expect($csvData->date)->toBeInstanceOf(CarbonInterface::class);
    expect($csvData->date->year)->toBe(2025);
});

test('CsvRowData handles CarbonImmutable input correctly', function () {
    $immutableDate = CarbonImmutable::createFromFormat('d/m/Y', '01/03/2025');

    $csvData = CsvRowData::fromArray([
        'raw_data'     => ['date' => '01/03/2025'],
        'csv_row_hash' => 'test-hash',
        'type'         => 'expense',
        'amount'       => 100.0,
        'description'  => 'Test CarbonImmutable input',
        'date'         => $immutableDate,
    ]);

    expect($csvData->date)->toBeInstanceOf(CarbonInterface::class);
    expect($csvData->date->format('Y-m-d'))->toBe('2025-03-01');
});

test('Carbon::instance() converts CarbonImmutable to Carbon properly', function () {
    $immutable = CarbonImmutable::createFromFormat('Y-m-d', '2025-01-03');
    $mutable = Carbon::instance($immutable);

    expect($mutable)->toBeInstanceOf(Carbon::class);
    expect($mutable)->toBeInstanceOf(CarbonInterface::class);
    expect($mutable->format('Y-m-d'))->toBe('2025-01-03');
    expect($mutable->isMutable())->toBeTrue();
});

test('date format parsing produces CarbonInterface instances', function () {
    $formats = [
        'Y-m-d' => '2025-01-03',
        'd/m/Y' => '01/03/2025',
        'm/d/Y' => '03/01/2025',
        'j/n/Y' => '1/3/2025',
    ];

    foreach ($formats as $format => $dateString) {
        $carbon = Carbon::createFromFormat($format, $dateString);
        expect($carbon)->toBeInstanceOf(CarbonInterface::class);
        expect($carbon->year)->toBe(2025);
    }
});

test('TransactionData can handle mixed CarbonInterface types from CSV processing', function () {
    // Simulate what happens during CSV import
    $csvRowWithCarbon = CsvRowData::fromArray([
        'raw_data'     => ['date' => '2025-01-03'],
        'csv_row_hash' => 'test1',
        'type'         => 'expense',
        'amount'       => 50.0,
        'description'  => 'Regular Carbon date',
        'date'         => Carbon::createFromFormat('Y-m-d', '2025-01-03'),
    ]);

    $csvRowWithImmutable = CsvRowData::fromArray([
        'raw_data'     => ['date' => '2025-01-03'],
        'csv_row_hash' => 'test2',
        'type'         => 'income',
        'amount'       => 100.0,
        'description'  => 'CarbonImmutable date',
        'date'         => CarbonImmutable::createFromFormat('Y-m-d', '2025-01-03'),
    ]);

    // Both should work with TransactionData
    $transactionData1 = new TransactionData(
        id: Optional::create(),
        account_id: 1,
        type: $csvRowWithCarbon->type,
        amount: $csvRowWithCarbon->amount,
        description: $csvRowWithCarbon->description,
        transaction_date: Carbon::instance($csvRowWithCarbon->date),
        category_id: Optional::create(),
        transferToAccountId: Optional::create(),
        recurringPatternId: Optional::create(),
        importId: Optional::create(),
        reconciled: Optional::create(),
        status: Optional::create(),
        account: Optional::create(),
        category: Optional::create(),
        transferToAccount: Optional::create(),
        recurringPattern: Optional::create(),
        import: Optional::create(),
        signed_amount: Optional::create(),
        is_transfer: Optional::create(),
        is_recurring: Optional::create(),
    );

    $transactionData2 = new TransactionData(
        id: Optional::create(),
        account_id: 1,
        type: $csvRowWithImmutable->type,
        amount: $csvRowWithImmutable->amount,
        description: $csvRowWithImmutable->description,
        transaction_date: Carbon::instance($csvRowWithImmutable->date),
        category_id: Optional::create(),
        transferToAccountId: Optional::create(),
        recurringPatternId: Optional::create(),
        importId: Optional::create(),
        reconciled: Optional::create(),
        status: Optional::create(),
        account: Optional::create(),
        category: Optional::create(),
        transferToAccount: Optional::create(),
        recurringPattern: Optional::create(),
        import: Optional::create(),
        signed_amount: Optional::create(),
        is_transfer: Optional::create(),
        is_recurring: Optional::create(),
    );

    expect($transactionData1->transaction_date)->toBeInstanceOf(CarbonInterface::class);
    expect($transactionData2->transaction_date)->toBeInstanceOf(CarbonInterface::class);
    expect($transactionData1->transaction_date->format('Y-m-d'))->toBe('2025-01-03');
    expect($transactionData2->transaction_date->format('Y-m-d'))->toBe('2025-01-03');
});

test('date conversion handles edge cases without errors', function () {
    // Test null date handling in CsvRowData
    $csvWithNullDate = CsvRowData::fromArray([
        'raw_data'     => [],
        'csv_row_hash' => 'test-null',
        'type'         => 'expense',
        'amount'       => 10.0,
        'description'  => 'Null date test',
        'date'         => null,
    ]);

    // Should default to now() when date is null
    expect($csvWithNullDate->date)->toBeInstanceOf(CarbonInterface::class);

    // Test with invalid date string (should fallback)
    $csvWithInvalidDate = CsvRowData::fromArray([
        'raw_data'     => ['date' => 'invalid-date'],
        'csv_row_hash' => 'test-invalid',
        'type'         => 'expense',
        'amount'       => 10.0,
        'description'  => 'Invalid date test',
        'date'         => 'invalid-date-string',
    ]);

    // Should still have a valid date (fallback to now())
    expect($csvWithInvalidDate->date)->toBeInstanceOf(CarbonInterface::class);
});
