# Import System Developer Reference

Technical reference for the CSV Import & Reconciliation system implementation.

## 🏗️ Architecture Overview

### Core Components

```
app/Livewire/ImportWizard.php          # Multi-step import wizard UI
app/Livewire/ImportHistory.php         # Import management interface
app/Services/ImportService.php         # Core CSV processing logic
app/Data/CsvRowData.php               # CSV row data structure
app/Data/ImportData.php               # Import metadata structure
config/import.php                     # Import configuration
```

### Database Schema

```sql
-- Import tracking
imports: id, user_id, filename, csv_file_path, imported_at, status, 
         row_count, matched_count, column_mapping, processing_metadata

-- Transaction enhancements  
transactions: + original_description, confidence_score, 
              auto_categorized, import_metadata
```

## 🔧 Key Classes & Methods

### ImportService

**Core Methods:**
- `createImport(User, UploadedFile): Import` - Store uploaded file
- `processImport(Import, Account, array): ImportResultData` - Process full import
- `previewImport(UploadedFile, array, int): array` - Generate preview data
- `detectColumns(array): array` - Auto-detect column mapping
- `getDuplicates(Account, Collection): Collection` - Find potential duplicates

**Configuration Integration:**
- Uses `config/import.php` for all settings
- Supports custom date formats and column patterns
- Configurable duplicate detection thresholds

### ImportWizard Livewire Component

**Properties:**
- `currentStep` - Wizard step tracking
- `selectedAccountId` - Target account selection  
- `csvFile` - Uploaded file handling
- `columnMapping` - User-defined column mapping
- `previewData` - Parsed preview transactions

**Methods:**
- `processUpload()` - Handle file upload and column detection
- `processMapping()` - Validate and process column mapping
- `processPreview()` - Generate transaction preview
- `processImport()` - Execute final import

### Data Transfer Objects

**CsvRowData:**
```php
public function __construct(
    public array $raw_data,
    public string $csv_row_hash,
    public string $type,           // income|expense|transfer
    public float $amount,
    public string $description,
    public CarbonInterface $date,
    public Optional|float $debit,
    public Optional|float $credit,
    public Optional|float $balance,
    public Optional|Category|null $suggested_category,
    public Optional|int|null $category_id,
) {}
```

## ⚙️ Configuration Reference

### config/import.php

**File Processing:**
```php
'chunk_size' => 500,                    // Rows per processing chunk
'max_file_size' => 10 * 1024 * 1024,   // 10MB max file size
'allowed_extensions' => ['csv', 'txt'],  // Supported file types
```

**Date Handling:**
```php
'date_formats' => [
    'Y-m-d', 'd/m/Y', 'm/d/Y',         // Common date formats
    'Y-m-d H:i:s', 'd/m/Y H:i:s',      // With timestamps
],
```

**Duplicate Detection:**
```php
'duplicate_threshold' => 0.7,           // 70% similarity threshold
'auto_match_confidence' => 0.9,         // 90% auto-skip confidence
'date_tolerance_days' => 2,             // ±2 days date tolerance
```

**Column Detection Patterns:**
```php
'column_patterns' => [
    'date' => ['date', 'transaction_date', 'effective_date'],
    'description' => ['description', 'memo', 'transaction_description'],
    'amount' => ['amount', 'transaction_amount'],
    // ... more patterns
],
```

## 🧪 Testing Framework

### Test Coverage

**Feature Tests:**
- `ImportWizardTest.php` - UI component testing
- `ImportServiceTest.php` - Service layer testing  
- `ImportSampleCsvTest.php` - Real-world CSV testing

**Test Scenarios:**
- Column auto-detection
- Date format parsing
- Duplicate detection algorithms
- Error handling and validation
- End-to-end import workflow

### Sample Test Data

**Bank Statement Format:**
```csv
Effective Date,Entered Date,Transaction Description,Amount,Balance
,01/03/2025,"POS - PAYPAL *STORE",-22.25,953.72
05/03/2025,05/03/2025,"Osko Payment From John Doe",400.00,1250.97
```

**Test Factories:**
- `ImportFactory` - Generate test imports
- `CsvRowDataFactory` - Create test CSV data
- Enhanced `TransactionFactory` - Import-related transactions

## 🔀 Workflow Integration

### Import Process Flow

```
1. File Upload → 2. Column Detection → 3. Mapping Validation
       ↓                  ↓                      ↓
4. Data Preview → 5. Import Confirmation → 6. Transaction Creation
       ↓                  ↓                      ↓
7. Duplicate Detection → 8. Auto-Categorization → 9. Results Display
```

### Service Layer Integration

**TransactionService Integration:**
- Uses existing `createTransaction()` method
- Maintains data consistency with manual transactions
- Preserves categorization and tagging logic

**CategoryMatchingService Integration:**
- Auto-applies existing category rules
- Learns from user categorization patterns
- Supports bulk categorization operations

## 🚀 Performance Considerations

### Memory Management

**Large File Handling:**
- Chunked processing for files >1000 rows
- Streaming CSV parsing to reduce memory usage
- Background job queue integration ready

**Database Optimization:**
- Batch transaction creation
- Indexed columns for duplicate detection
- Optimized queries for date range searches

### Caching Strategy

**Column Detection Caching:**
- Cache mapping decisions per account
- Remember user preferences for similar files
- Reduce processing time for repeat imports

## 🔍 Debugging & Monitoring

### Error Handling

**Exception Types:**
- `CSV_PARSE_ERROR` - File format issues
- `COLUMN_MAPPING_ERROR` - Invalid column configuration  
- `DUPLICATE_DETECTION_ERROR` - Duplicate processing failures
- `TRANSACTION_CREATE_ERROR` - Transaction creation issues

**Error Recovery:**
- Partial import rollback capability
- Failed row isolation and reporting
- Resume functionality for interrupted imports

### Logging

**Import Tracking:**
```php
// Stored in import.processing_metadata
'processing_log' => [
    'started_at' => '2025-08-24 12:34:56',
    'rows_processed' => 150,
    'errors_encountered' => [],
    'duplicate_count' => 5,
    'auto_categorized' => 120,
]
```

## 🔧 Extension Points

### Custom Column Patterns

Add institution-specific patterns:
```php
// config/import.php
'column_patterns' => [
    'custom_bank_date' => ['settlement_date', 'value_date'],
    'custom_reference' => ['ref_number', 'transaction_ref'],
]
```

### Custom Duplicate Detection

Override duplicate detection logic:
```php
// Extend ImportService
protected function isLikelyDuplicate(Transaction $transaction, array $csvRow): bool
{
    // Custom duplicate detection logic
    return parent::isLikelyDuplicate($transaction, $csvRow);
}
```

### Custom Auto-Categorization

Extend CategoryMatchingService:
```php
public function findMatchingCategory(User $user, string $description, float $amount): ?Category
{
    // Custom categorization logic
    // Institution-specific merchant codes
    // Amount-based categorization rules
}
```

## 📊 Metrics & Analytics

### Import Statistics

**Tracked Metrics:**
- Import success rates by file type
- Average processing time per row
- Duplicate detection accuracy
- Auto-categorization success rates
- User column mapping patterns

**Performance Monitoring:**
- Memory usage during large imports
- Database query performance
- File processing bottlenecks
- Error occurrence patterns

---

*This developer reference covers the technical implementation of the Phase 4 Import & Reconciliation system. For user-facing documentation, see the CSV Import Guide.*