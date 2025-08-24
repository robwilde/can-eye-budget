# CSV Import Guide

Complete guide for importing bank statements and CSV transaction files into Can-Eye Budget.

## 🚀 Quick Start

### Access the Import Wizard

The import wizard is available at: **http://localhost:8000/import**

*Note: Direct URL access required until navigation links are added to the UI*

### Import History

View all your imports at: **http://localhost:8000/imports**

## 📋 Step-by-Step Import Process

### Step 1: Upload Your CSV File

1. **Select Target Account**
   - Choose which account the transactions should be imported into
   - Account selection is required before proceeding

2. **Upload CSV File**
   - Click to browse or drag-and-drop your CSV file
   - **Supported formats**: `.csv`, `.txt`
   - **Maximum file size**: 10MB
   - **File validation**: Automatic checking for format and size

**Supported File Formats:**
- CSV files exported from online banking
- Files with columns for: Date, Description, Amount (or Debit/Credit)
- Optional columns: Balance, Reference, Category

### Step 2: Column Mapping

The wizard automatically detects and maps CSV columns based on common banking patterns.

**Auto-Detection Patterns:**
- **Date columns**: "Date", "Transaction Date", "Effective Date", "Posted Date"
- **Description**: "Description", "Transaction Description", "Memo", "Details"
- **Amount**: "Amount", "Transaction Amount"
- **Debit/Credit**: "Debit"/"Credit", "Withdrawal"/"Deposit"
- **Balance**: "Balance", "Running Balance", "Account Balance"

**Manual Override Available:**
- Adjust any column mapping using dropdown selectors
- Required fields: Date and Description
- Amount handling: Either single Amount column OR separate Debit/Credit columns

### Step 3: Preview Data

Review the first 10 transactions to ensure correct parsing:

- **Transaction Types**: Automatically detected (income/expense)
- **Date Parsing**: Multiple international formats supported
- **Amount Handling**: Negative values = expenses, positive = income
- **Data Validation**: Real-time feedback on parsing issues

### Step 4: Import Confirmation

Final review before processing:
- Import summary with total row count
- Selected account confirmation
- Column mapping review
- Processing options (duplicate detection, auto-categorization)

### Step 5: View Results

Import results page displays:
- **Transactions Created**: Number of successfully imported transactions
- **Duplicates Skipped**: Potential duplicates automatically detected
- **Success Rate**: Overall import success percentage
- **Error Details**: Any issues encountered during processing

## 🏦 Bank Statement Format Support

### Your Sample CSV Format

The system is specifically designed to handle your bank statement format:

```csv
Effective Date,Entered Date,Transaction Description,Amount,Balance
,01/03/2025,"POS - #459939 - PAYPAL *PYPL Payin4      Sydney       AU #2892",-22.25,953.72
01/03/2025,02/03/2025,"Round Up transfer to 03774599",-2.75,950.97
,03/03/2025,"Direct Debit Spaceship - DT.3v53ev Universe",-100.00,850.97
,05/03/2025,"Osko Payment From Robert E Wilde Ref#869804245",400.00,1250.97
```

**Intelligent Handling:**
- ✅ **Empty Effective Dates**: Uses Entered Date when Effective Date is blank
- ✅ **Amount Interpretation**: Negative = expense, positive = income
- ✅ **Complex Descriptions**: Handles long transaction descriptions with special characters
- ✅ **Australian Date Format**: Supports dd/mm/yyyy format
- ✅ **Balance Reconciliation**: Optional balance column for verification

### Supported Date Formats

The system automatically recognizes these date formats:
- `YYYY-MM-DD` (ISO format)
- `DD/MM/YYYY` (Australian/UK format)
- `MM/DD/YYYY` (US format)
- `DD-MM-YYYY` (European format)
- `MM-DD-YYYY` (US alternative)
- Plus timestamps for any of the above

### Multiple Date Columns

When your CSV has multiple date columns:
1. **Priority**: Effective Date takes precedence
2. **Fallback**: Uses Entered/Processed Date if Effective Date is empty
3. **Flexible**: Configurable column mapping for any date field names

## 🛡️ Built-in Safety Features

### Duplicate Detection

**Automatic Detection Criteria:**
- Date match within ±2 days tolerance
- Exact amount match
- Description similarity >70%
- Confidence scoring for manual review

**Detection Process:**
1. Scans existing transactions in date range
2. Compares imported rows against existing data
3. Calculates confidence score (0-100%)
4. Automatically skips high-confidence duplicates
5. Flags low-confidence matches for review

### Auto-Categorization

**Smart Categorization:**
- Pattern matching based on transaction descriptions
- Learning from existing categorization rules
- Merchant recognition (e.g., PAYPAL, grocery stores)
- Configurable rules for specific transaction types

### Data Validation

**Quality Assurance:**
- Date format validation and normalization
- Amount parsing with currency symbol removal
- Description cleaning and standardization
- Balance verification (when available)

## 📊 Import Management

### Import History Features

**Track All Imports:**
- Complete import history with timestamps
- Success rates and statistics
- File information and processing details
- Status tracking (pending, processing, completed, failed)

**Import Actions:**
- View import details and statistics
- Re-process failed imports
- Delete imports and associated transactions
- Download original CSV files (when available)

**Filtering & Sorting:**
- Filter by status (all, completed, failed, processing)
- Sort by date, filename, or success rate
- Search functionality for specific imports

## 🚀 Pro Tips & Best Practices

### Before Importing

1. **Test with Small Files**: Start with 10-20 transactions for first import
2. **Clean CSV Data**: Ensure proper column headers and consistent formatting
3. **Backup Data**: Keep original CSV files as backup
4. **Account Verification**: Double-check you're importing to the correct account

### During Import

1. **Review Column Mapping**: Verify auto-detection is correct
2. **Check Preview Data**: Ensure transaction types and amounts look correct
3. **Date Validation**: Confirm dates are being parsed properly
4. **Description Review**: Check that transaction descriptions are complete

### After Import

1. **Review Results**: Check success rate and any skipped transactions
2. **Categorization**: Review and adjust auto-categorized transactions
3. **Balance Verification**: Compare imported balance with account balance
4. **Duplicate Review**: Check flagged duplicates for accuracy

### Troubleshooting

**Common Issues:**

1. **File Format Problems**
   - Ensure CSV uses proper delimiters (commas)
   - Check for special characters in descriptions
   - Verify file encoding (UTF-8 recommended)

2. **Date Parsing Issues**
   - Check date format consistency
   - Ensure dates are in recognized formats
   - Verify date columns aren't mixed up

3. **Amount Recognition**
   - Check for currency symbols or formatting
   - Ensure negative numbers are properly formatted
   - Verify debit/credit column usage

**Error Recovery:**
- Failed imports can be retried from Import History
- Column mappings are saved for future imports
- Transaction rollback available for failed imports

## 🔧 Setup Commands

```bash
# Ensure database is up to date
php artisan migrate

# Start development environment
composer dev

# Access import wizard
# Navigate to: http://localhost:8000/import

# View import history
# Navigate to: http://localhost:8000/imports
```

## 📈 Advanced Features

### Large File Processing

**Automatic Optimization:**
- Files >1000 rows are processed in background
- Chunked processing for memory efficiency
- Progress tracking for long-running imports
- Email notifications when complete (future feature)

### Custom Column Patterns

**Configuration Options:**
- Add custom column detection patterns in `config/import.php`
- Define institution-specific mappings
- Create reusable import profiles

### API Integration

**Programmatic Access:**
- RESTful endpoints for import automation
- Bulk processing capabilities
- Integration with external banking APIs (future feature)

## 🎯 Next Steps

After successful import:

1. **Review Transactions**: Check imported data in main dashboard
2. **Set Up Categories**: Configure category rules for future auto-categorization  
3. **Reconciliation**: Compare with bank statements for accuracy
4. **Recurring Patterns**: Set up recurring transaction rules
5. **Budget Planning**: Use imported data for budget creation

---

*This guide covers the Phase 4 Import & Reconciliation system implemented for Can-Eye Budget. For technical implementation details, see the developer documentation.*