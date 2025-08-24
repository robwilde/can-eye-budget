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
    | Date Format Patterns
    |--------------------------------------------------------------------------
    |
    | Date formats to try when parsing CSV dates. These are attempted in order,
    | with the most common formats first for performance.
    |
    */
    'date_formats' => [
        'Y-m-d',
        'd/m/Y',
        'm/d/Y',
        'Y-m-d H:i:s',
        'd/m/Y H:i:s',
        'm/d/Y H:i:s',
        'j/n/Y',
        'n/j/Y',
        'd-m-Y',
        'm-d-Y',
    ],

    /*
    |--------------------------------------------------------------------------
    | Duplicate Detection Settings
    |--------------------------------------------------------------------------
    |
    | Settings for detecting duplicate transactions during import.
    |
    */
    'duplicate_threshold' => env('IMPORT_DUPLICATE_THRESHOLD', 0.7),
    'auto_match_confidence' => env('IMPORT_AUTO_MATCH_CONFIDENCE', 0.9),
    'date_tolerance_days' => env('IMPORT_DATE_TOLERANCE_DAYS', 2),

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
    'skip_empty_rows' => true,
    'trim_whitespace' => true,
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
    'queue_connection' => env('IMPORT_QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Settings for storing uploaded CSV files.
    |
    */
    'storage_disk' => env('IMPORT_STORAGE_DISK', 'local'),
    'storage_path' => 'imports',
    'cleanup_after_days' => env('IMPORT_CLEANUP_DAYS', 30),
];