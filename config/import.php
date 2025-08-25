<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | CSV Import Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings for CSV import functionality
    | including file size limits, processing options, and validation rules.
    |
    */

    'chunk_size' => env('IMPORT_CHUNK_SIZE', 500),

    'max_file_size' => env('IMPORT_MAX_FILE_SIZE', 10 * 1024 * 1024), // 10MB

    'allowed_extensions' => ['csv', 'txt'],

    'allowed_mime_types' => [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/excel',
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Format Patterns by Locale
    |--------------------------------------------------------------------------
    |
    | Date formats organized by locale. The DateParserFactory uses these
    | based on APP_LOCALE setting to ensure correct date interpretation.
    |
    | DEPRECATED: The 'date_formats' array below is kept for backward
    | compatibility but is superseded by locale-specific parsing.
    |
    */
    'date_formats_by_locale' => [
        'en_AU' => [
            'd/m/Y',        // 01/03/2025 = March 1st (most common)
            'j/n/Y',        // 1/3/2025 = March 1st (single digits)
            'd-m-Y',        // 01-03-2025 = March 1st (hyphens)
            'j-n-Y',        // 1-3-2025 = March 1st (single digits, hyphens)
            'd/m/Y H:i:s',  // With time
            'j/n/Y H:i:s',  // Single digits with time
            'd-m-Y H:i:s',  // Hyphens with time
            'Y-m-d',        // ISO format fallback
            'Y-m-d H:i:s',  // ISO with time
        ],
        'en_US' => [
            'm/d/Y',        // 03/01/2025 = March 1st (US format)
            'n/j/Y',        // 3/1/2025 = March 1st (single digits)
            'm-d-Y',        // 03-01-2025 = March 1st (hyphens)
            'n-j-Y',        // 3-1-2025 = March 1st (single digits, hyphens)
            'm/d/Y H:i:s',  // With time
            'n/j/Y H:i:s',  // Single digits with time
            'm-d-Y H:i:s',  // Hyphens with time
            'Y-m-d',        // ISO format fallback
            'Y-m-d H:i:s',  // ISO with time
        ],
        'en_GB' => [
            'd/m/Y',        // Same as Australian
            'j/n/Y',
            'd-m-Y',
            'j-n-Y',
            'd/m/Y H:i:s',
            'j/n/Y H:i:s',
            'd-m-Y H:i:s',
            'Y-m-d',
            'Y-m-d H:i:s',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Date Format Patterns (DEPRECATED)
    |--------------------------------------------------------------------------
    |
    | These formats are kept for backward compatibility but should not be used
    | for new development. Use locale-specific parsing via DateParserFactory.
    |
    */
    'date_formats' => [
        'Y-m-d',
        'd/m/Y',        // Australian/UK format first - handles 13/03/2025
        'j/n/Y',        // Australian with single digits - handles 1/3/2025
        'd-m-Y',        // Australian with hyphens
        'm/d/Y',        // US format tried later
        'n/j/Y',        // US with single digits
        'm-d-Y',        // US with hyphens
        'Y-m-d H:i:s',
        'd/m/Y H:i:s',
        'm/d/Y H:i:s',
    ],

    /*
    |--------------------------------------------------------------------------
    | Duplicate Detection Settings
    |--------------------------------------------------------------------------
    |
    | Settings for detecting duplicate transactions during import.
    |
    */
    'duplicate_threshold'   => env('IMPORT_DUPLICATE_THRESHOLD', 0.7),
    'auto_match_confidence' => env('IMPORT_AUTO_MATCH_CONFIDENCE', 0.9),
    'date_tolerance_days'   => env('IMPORT_DATE_TOLERANCE_DAYS', 2),

    /*
    |--------------------------------------------------------------------------
    | Column Detection Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns used to automatically detect CSV column types from headers.
    |
    */
    'column_patterns' => [
        'date' => [
            'date',
            'transaction_date',
            'posted_date',
            'trans_date',
            'effective_date',
            'effective date',
            'transaction date',
        ],
        'entered_date' => [
            'entered_date',
            'entered date',
            'processed_date',
            'processed date',
        ],
        'description' => [
            'description',
            'memo',
            'details',
            'transaction_description',
            'transaction description',
            'reference',
            'payee',
        ],
        'amount' => [
            'amount',
            'transaction_amount',
            'transaction amount',
        ],
        'debit' => [
            'debit',
            'withdrawal',
            'outgoing',
            'debit_amount',
            'debit amount',
        ],
        'credit' => [
            'credit',
            'deposit',
            'incoming',
            'credit_amount',
            'credit amount',
        ],
        'balance' => [
            'balance',
            'running_balance',
            'account_balance',
            'running balance',
            'account balance',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Options
    |--------------------------------------------------------------------------
    |
    | Options for CSV processing behavior.
    |
    */
    'skip_empty_rows'      => true,
    'trim_whitespace'      => true,
    'auto_detect_encoding' => true,

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Settings for background processing of large CSV files.
    |
    */
    'queue_threshold_rows' => env('IMPORT_QUEUE_THRESHOLD', 1000),
    'queue_connection'     => env('IMPORT_QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Settings for storing uploaded CSV files.
    |
    */
    'storage_disk'       => env('IMPORT_STORAGE_DISK', 'local'),
    'storage_path'       => 'imports',
    'cleanup_after_days' => env('IMPORT_CLEANUP_DAYS', 30),
];
