<?php

declare(strict_types=1);

use App\Services\DateParsing\Parsers\AustralianDateParser;
use Carbon\Carbon;

covers(AustralianDateParser::class);

beforeEach(function () {
    $this->parser = new AustralianDateParser();
});

test('parser identifies as Australian locale', function () {
    expect($this->parser->getLocale())->toBe('en_AU');
});

test('parser provides Australian date formats in priority order', function () {
    $formats = $this->parser->getFormats();

    expect($formats)->toContain('d/m/Y');
    expect($formats)->toContain('j/n/Y');
    expect($formats)->toContain('Y-m-d');

    // d/m/Y should be first for Australian parsing
    expect($formats[0])->toBe('d/m/Y');
});

test('parses bank statement dates as March 2025', function () {
    // Test the exact dates from our sample CSV
    $testCases = [
        '01/03/2025' => '2025-03-01', // March 1st
        '02/03/2025' => '2025-03-02', // March 2nd
        '03/03/2025' => '2025-03-03', // March 3rd
        '05/03/2025' => '2025-03-05', // March 5th
        '06/03/2025' => '2025-03-06', // March 6th
    ];

    foreach ($testCases as $input => $expected) {
        $result = $this->parser->parse($input);
        expect($result)->not->toBeNull("Failed to parse: {$input}");
        expect($result->format('Y-m-d'))->toBe($expected, "Expected {$input} to be {$expected}, got {$result->format('Y-m-d')}");
    }
});

test('parses various Australian date formats', function () {
    $testCases = [
        // Standard format
        '15/03/2025' => '2025-03-15',
        '31/12/2025' => '2025-12-31',

        // Single digit format
        '1/3/2025'  => '2025-03-01',
        '5/12/2025' => '2025-12-05',

        // Hyphenated format
        '15-03-2025' => '2025-03-15',
        '1-3-2025'   => '2025-03-01',

        // With time
        '15/03/2025 14:30:25' => '2025-03-15',
        '1/3/2025 09:15:00'   => '2025-03-01',
    ];

    foreach ($testCases as $input => $expected) {
        $result = $this->parser->parse($input);
        expect($result)->not->toBeNull("Failed to parse: {$input}");
        expect($result->format('Y-m-d'))->toBe($expected);
    }
});

test('handles ISO format as fallback', function () {
    $result = $this->parser->parse('2025-03-15');
    expect($result)->not->toBeNull();
    expect($result->format('Y-m-d'))->toBe('2025-03-15');
});

test('rejects invalid dates', function () {
    $invalidDates = [
        '32/03/2025', // Invalid day
        '15/13/2025', // Invalid month
        '29/02/2025', // Invalid leap year
        'invalid-date',
        '',
    ];

    foreach ($invalidDates as $invalid) {
        $result = $this->parser->parse($invalid);
        expect($result)->toBeNull("Should reject invalid date: {$invalid}");
    }
});

test('handles empty and null inputs', function () {
    expect($this->parser->parse(''))->toBeNull();
    expect($this->parser->parse('   '))->toBeNull();
});

test('prioritizes d/m/Y over m/d/Y for ambiguous dates', function () {
    // These dates are ambiguous between Australian and US formats
    // Australian parser should interpret as d/m/Y
    $ambiguousCases = [
        '01/02/2025' => '2025-02-01', // Feb 1st (AU) not Jan 2nd (US)
        '03/04/2025' => '2025-04-03', // Apr 3rd (AU) not Mar 4th (US)
        '05/06/2025' => '2025-06-05', // Jun 5th (AU) not May 6th (US)
    ];

    foreach ($ambiguousCases as $input => $expectedAustralian) {
        $result = $this->parser->parse($input);
        expect($result)->not->toBeNull();
        expect($result->format('Y-m-d'))->toBe($expectedAustralian,
            "Expected {$input} to be parsed as Australian format: {$expectedAustralian}");
    }
});

test('validates parsed dates make sense', function () {
    // This should work fine
    $valid = $this->parser->parse('15/03/2025');
    expect($valid)->not->toBeNull();
    expect($valid->format('Y-m-d'))->toBe('2025-03-15');

    // This should be rejected (day > 12 eliminates US format ambiguity)
    $unambiguous = $this->parser->parse('25/03/2025');
    expect($unambiguous)->not->toBeNull();
    expect($unambiguous->format('Y-m-d'))->toBe('2025-03-25');
});

test('handles reasonable date range validation', function () {
    $now = Carbon::now();

    // Recent past should work
    $recentPast = $now->copy()->subMonths(6)->format('d/m/Y');
    $result = $this->parser->parse($recentPast);
    expect($result)->not->toBeNull();

    // Near future should work
    $nearFuture = $now->copy()->addMonths(6)->format('d/m/Y');
    $result = $this->parser->parse($nearFuture);
    expect($result)->not->toBeNull();
});
