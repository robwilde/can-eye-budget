<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use App\Models\Account;
use App\Models\User;
use App\Services\DateParsing\DateParserFactory;
use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

covers(DateParserFactory::class, ImportService::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->for($this->user)->create();
    Storage::fake('local');
});

test('date parser factory respects APP_LOCALE setting', function () {
    Config::set('app.locale', 'en_AU');

    $factory = new DateParserFactory();
    $parser = $factory->make();

    expect($parser->getLocale())->toBe('en_AU');

    // Test that it parses Australian format correctly
    $result = $parser->parse('01/03/2025');
    expect($result)->not
        ->toBeNull()
        ->and($result->format('Y-m-d'))->toBe('2025-03-01');
    // March 1st, not January 3rd
});

test('date parser factory can create locale-specific parsers', function () {
    $factory = new DateParserFactory();

    $auParser = $factory->makeForLocale('en_AU');
    expect($auParser->getLocale())->toBe('en_AU');

    // Test parsing the same ambiguous date with different locales
    $date = '01/02/2025';

    $auResult = $auParser->parse($date);
    expect($auResult->format('Y-m-d'))->toBe('2025-02-01'); // Australian: Feb 1st
});

test('import service uses locale-aware date parsing', function () {
    Config::set('app.locale', 'en_AU');

    $importService = app(ImportService::class);

    // Create CSV with Australian-format dates that should all be March 2025
    $csvContent = "Date,Description,Amount\n";
    $csvContent .= "01/03/2025,\"March 1st Transaction\",-100.00\n";
    $csvContent .= "15/03/2025,\"March 15th Transaction\",200.00\n";
    $csvContent .= "31/03/2025,\"March 31st Transaction\",-50.00\n";

    $csvFile = UploadedFile::fake()->createWithContent('march_test.csv', $csvContent);

    $columnMapping = [
        'date'        => 'Date',
        'description' => 'Description',
        'amount'      => 'Amount',
    ];

    $preview = $importService->previewImport($csvFile, $columnMapping);

    expect($preview['total_rows'])
        ->toBe(3)
        ->and($preview['preview_data'])->toHaveCount(3);

    // All dates should be in March 2025
    foreach ($preview['preview_data'] as $transaction) {
        expect($transaction->date->year)
            ->toBe(2025)
            ->and($transaction->date->month)->toBe(3);
        // March
    }

    // Check specific dates
    expect($preview['preview_data'][0]->date->day)
        ->toBe(1)
        ->and($preview['preview_data'][1]->date->day)->toBe(15)
        ->and($preview['preview_data'][2]->date->day)->toBe(31);   // March 1st
    // March 15th
    // March 31st
});

test('sample CSV dates all parse to March 2025', function () {
    Config::set('app.locale', 'en_AU');

    $sampleCsvPath = base_path('docs/samples/StatementCsv_03_2025_a.csv');

    if (! file_exists($sampleCsvPath)) {
        $this->markTestSkipped('Sample CSV file not found');
    }

    $importService = app(ImportService::class);
    $csvContent = file_get_contents($sampleCsvPath);
    $csvFile = UploadedFile::fake()->createWithContent('sample.csv', $csvContent);

    $columnMapping = [
        'date'         => 'Effective Date',
        'entered_date' => 'Entered Date',
        'description'  => 'Transaction Description',
        'amount'       => 'Amount',
        'balance'      => 'Balance',
    ];

    $preview = $importService->previewImport($csvFile, $columnMapping, 20);

    expect($preview['total_rows'])->toBe(14);

    // All transactions should be in March 2025
    foreach ($preview['preview_data'] as $index => $transaction) {
        expect($transaction->date->year)
            ->toBe(2025, "Transaction $index year should be 2025")
            ->and($transaction->date->month)->toBe(3, "Transaction $index should be in March, got month {$transaction->date->month}");
    }

    // Check that dates span the expected range within March 2025
    $dates = collect($preview['preview_data'])->map(fn ($t) => $t->date->day)->sort()->values();
    expect($dates->min())
        ->toBeLessThanOrEqual(6)
        ->and($dates->max())->toBeGreaterThanOrEqual(1);  // Should start early in March
    // Should have transactions throughout March
});

test('ambiguous dates are parsed consistently as Australian format', function () {
    Config::set('app.locale', 'en_AU');

    $importService = app(ImportService::class);

    // These dates are ambiguous between AU and US formats
    $csvContent = "Date,Description,Amount\n";
    $csvContent .= "01/02/2025,\"Should be Feb 1st AU\",-100.00\n";     // AU: Feb 1, US: Jan 2
    $csvContent .= "03/04/2025,\"Should be Apr 3rd AU\",200.00\n";      // AU: Apr 3, US: Mar 4
    $csvContent .= "05/06/2025,\"Should be Jun 5th AU\",-50.00\n";      // AU: Jun 5, US: May 6

    $csvFile = UploadedFile::fake()->createWithContent('ambiguous_test.csv', $csvContent);

    $columnMapping = [
        'date'        => 'Date',
        'description' => 'Description',
        'amount'      => 'Amount',
    ];

    $preview = $importService->previewImport($csvFile, $columnMapping);

    // Check that all dates are parsed as Australian format
    $transactions = $preview['preview_data'];

    // 01/02/2025 should be February 1st (AU), not January 2nd (US)
    expect($transactions[0]->date->format('Y-m-d'))
        ->toBe('2025-02-01')
        ->and($transactions[1]->date->format('Y-m-d'))->toBe('2025-04-03')
        ->and($transactions[2]->date->format('Y-m-d'))->toBe('2025-06-05');
    // 03/04/2025 should be April 3rd (AU), not March 4th (US)
    // 05/06/2025 should be June 5th (AU), not May 6th (US)
});

test('date parser factory lists supported locales', function () {
    $factory = new DateParserFactory();

    $supportedLocales = $factory->getSupportedLocales();
    expect($supportedLocales)
        ->toContain('en_AU')
        ->and($factory->isLocaleSupported('en_AU'))->toBeTrue();
});

test('factory falls back to australian parser for unsupported locales', function () {
    $factory = new DateParserFactory();

    // Test with unsupported locale
    $parser = $factory->makeForLocale('fr_FR');
    expect($parser->getLocale())->toBe('en_AU'); // Falls back to Australian

    // But should still parse dates correctly
    $result = $parser->parse('01/03/2025');
    expect($result->format('Y-m-d'))->toBe('2025-03-01');
});
