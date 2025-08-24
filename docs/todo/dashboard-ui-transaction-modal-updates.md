# Dashboard UI & Transaction Modal Update Plan

## Overview
This plan addresses the dashboard UI changes and transaction modal enhancements based on requirements document and reference screenshots. The implementation will focus on improving the user experience with period totals, enhanced calendar views, and a more functional transaction entry system.

## Key Requirements Summary

### Dashboard UI Changes
1. **Replace account balances with period totals** - Show income/expenses for selected period
2. **Dual display**: Planned transactions (left) vs Entered transactions (right)  
3. **Period totals**: Show net amount and percentage saved
4. **Default to week view**: Sunday-Saturday calendar display

### Transaction Modal Enhancements
1. **Transaction type dropdown** - Replace radio buttons with dropdown (expense/income/transfer)
2. **Prominent date display** - Show date in top-right with clickable date picker
3. **Enter vs Plan toggle** - Switch between immediate and planned transactions
4. **Recurring transaction support** - Add frequency selection with "until date" option
5. **Status tracking** - Distinguish between planned and entered transactions

## Phase 1: Database Schema Updates

### 1.1 Add Transaction Status Field
- Add `status` enum field to transactions table ('planned', 'entered')
- Default to 'entered' for backward compatibility
- Add index on status field for performance

### 1.2 Enhance Recurring Patterns
- Ensure recurring_patterns table supports all required frequencies
- Add support for "until date" functionality

## Phase 2: Dashboard UI - Period Totals Display

### 2.1 Replace Account Balances with Period Totals
- Remove individual account balance display from CalendarViewSimple
- Add computed properties for period income/expense totals
- Create dual-column display: Planned (left) vs Entered (right)
- Add "Net" calculation and "% saved" indicator
- Style with green for income, red for expenses

### 2.2 Update CalendarViewSimple Component
- Modify `balances()` method to calculate period totals
- Add separate calculations for planned vs entered transactions
- Update view template to display new totals format

## Phase 3: Calendar Transaction Display

### 3.1 Week View Enhancement
- Default to week view (Sunday-Saturday)
- Display individual transactions per day
- Color coding: Green (Income), Red (Expense), Yellow (Transfer)
- Darker shades for entered transactions, lighter for planned

### 3.2 Transaction Interactivity
- Make each transaction clickable to view/edit details
- Add click handler on calendar days to open transaction modal
- Pass date context when opening modal from calendar

## Phase 4: Transaction Modal Redesign

### 4.1 Type Selection Dropdown
- Replace radio buttons with dropdown selector
- Options: expense, income, transfer
- Style to match reference screenshots

### 4.2 Date Display & Selection
- Display selected date prominently in top-right
- Add date picker on click
- Format: "Jul 28, 2025"

### 4.3 Enter vs Plan Toggle
- Add toggle switch for "Enter" vs "Plan" modes
- "Enter" = immediate transaction (status: 'entered')
- "Plan" = future transaction (status: 'planned')
- Update button text based on mode

### 4.4 Recurring Transaction Support
- Add repeat dropdown (visible in Plan mode)
- Options: Don't repeat, Everyday, Every week, Every month, etc.
- Add "Until date" option with date picker
- Calculate and create multiple transaction instances

## Phase 5: Service Layer Updates

### 5.1 TransactionService Enhancements
- Add support for transaction status
- Implement bulk creation for recurring transactions
- Add method to generate transactions from pattern

### 5.2 RecurringService Creation
- Create service to handle recurring logic
- Calculate occurrences based on frequency and end date
- Generate transaction instances

## Phase 6: Component Integration

### 6.1 Update TransactionForm Component
- Add status property (planned/entered)
- Add recurring pattern properties
- Implement frequency selection logic
- Handle bulk transaction creation

### 6.2 Calendar View Updates
- Update transaction queries to include status
- Modify transaction display based on status
- Refresh on transaction updates

## Phase 7: Styling & UX Polish

### 7.1 Modal Styling
- Match reference screenshot aesthetics
- Add proper spacing and typography
- Implement hover states and transitions

### 7.2 Calendar Styling
- Apply color coding consistently
- Add visual indicators for planned vs entered
- Ensure responsive design

## Implementation Order

1. **Database migrations** - Add status field to transactions
2. **Service layer updates** - Enhance TransactionService, create RecurringService
3. **Transaction modal updates** - Implement new UI and functionality
4. **Dashboard totals** - Replace account view with period totals
5. **Calendar enhancements** - Update transaction display and interactivity
6. **Testing & refinement** - Ensure all features work together

## Files to Modify

### Backend:
- `database/migrations/` - New migration for status field
- `app/Models/Transaction.php` - Add status field and scopes
- `app/Services/TransactionService.php` - Enhanced transaction handling
- `app/Services/RecurringService.php` - New service for recurring logic
- `app/Livewire/TransactionForm.php` - Modal logic updates
- `app/Livewire/CalendarViewSimple.php` - Period totals logic

### Frontend:
- `resources/views/livewire/transaction-form.blade.php` - Modal UI redesign
- `resources/views/livewire/calendar-view-simple.blade.php` - Totals display
- `resources/views/livewire/calendar-view-week.blade.php` - Week view updates

## Testing Strategy
- Unit tests for new service methods
- Feature tests for transaction creation with status
- Feature tests for recurring transaction generation
- UI testing for modal interactions
- Integration testing for calendar updates

## Progress Tracking

### Original Implementation ✅ (COMPLETED)
- [x] Database schema updates
- [x] Service layer enhancements
- [x] Transaction modal redesign
- [x] Dashboard period totals
- [x] Calendar enhancements
- [x] Testing and refinement

### Critical Bug Fixes (IN PROGRESS)

#### Issue #1: Recurring Transactions Not Creating All Occurrences ✅ FIXED
**Problem**: When creating a recurring transaction (e.g., every 2 weeks ending in August), only one transaction is created instead of all occurrences.
**Root Cause**: In RecurringService.php, the counter increment and date logic was incorrect.
**Status**: ✅ **FIXED** - Updated loop logic to properly create all occurrences within date range.

#### Issue #2: Calendar Views Not Showing Current Month/Period ✅ FIXED
**Problem**: Week and month views need to show the current month/period by default
**Status**: ✅ **FIXED** - Updated CalendarViewSimple mount() to use Carbon::today()

#### Issue #3: Month View Shows Only Dots Instead of Transaction Details ⏳ PENDING
**Problem**: Month view displays minimal dots instead of amount and description
**Status**: ⏳ **PENDING** - Need to replace dot indicators with transaction details

#### Issue #4: Transaction Button Text Needs Verification ⏳ PENDING  
**Problem**: Button should show "Enter Expense" or "Plan Income" based on type and status
**Status**: ⏳ **PENDING** - Need to verify current implementation

## Reference Screenshots
- `docs/issues/totals-suggested.png` - Period totals format
- `docs/issues/transaction-suggested-type.png` - Type dropdown
- `docs/issues/transaction-suggested.png` - Enter/Plan toggle
- `docs/issues/transaction-suggested-expense-plan.png` - Plan mode
- `docs/issues/transaction-suggested-repeat.png` - Repeat options
- `docs/issues/transactions-suggested-frequency.png` - Until date selector
- `docs/issues/transaction-suggested-date-select.png` - Date picker

## Next Session Tasks (Tomorrow Morning)
1. **Fix month view transaction display** - Replace dots with amounts and descriptions  
2. **Verify button text specificity** - Ensure "Enter Expense" vs "Plan Income" works correctly
3. **Test all fixes thoroughly** - Test recurring transactions, calendar views, and button text
4. **Final integration testing** - Ensure all features work together seamlessly

## Files Modified Today
- `app/Services/RecurringService.php` - Fixed recurring transaction generation loop
- `app/Livewire/CalendarViewSimple.php` - Fixed current date initialization  
- `docs/todo/dashboard-ui-transaction-modal-updates.md` - Updated with current progress

## Notes
This implementation will significantly improve the user experience by providing clearer financial insights through period totals and more flexible transaction management through the enhanced modal system. The critical recurring transaction bug has been resolved, and calendar views now properly show current periods.
