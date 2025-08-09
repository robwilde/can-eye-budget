# Development Plan – CanEye Budget

This document outlines the next phases of development with actionable tickets, sequencing, acceptance criteria, testing plans, and risks. It builds on the existing codebase (Phases 1–3 complete) and focuses on Phase 4–5 features first.

## Overview
- Tech: Laravel 12, PHP 8.4, Livewire, Tailwind, Alpine.js, SQLite (dev)
- Implemented: CalendarView, TransactionForm, CategoryManager; Service layer for Import, Recurring, Projections, Category Matching; Comprehensive tests in places
- Next: Import & Reconciliation UI, Recurring UI + Commands, Projections charting; Config and QA polish later

## Sprint 1 – Import & Reconciliation MVP (UI + Synchronous Processing)

Preferences applied:
- Single Livewire ImportWizard with stepper: Upload → Map → Preview → Process → Result
- Entry route: GET /imports/new; Add simple link from dashboard/menu; CSV ≤10MB, mime: text/csv
- Processing: synchronous for now; store files under storage/app/imports
- Duplicates: skip during initial import; write report to a file for later review (CSV/JSON); show counts in results

Tickets:
1. T1: Scaffold ImportWizard
   - Files:
     - app/Livewire/ImportWizard.php
     - resources/views/livewire/import-wizard.blade.php
     - routes/web.php: routes for /imports and /imports/new
     - resources/views/imports/index.blade.php (simple history page)
   - Stepper UI with validation placeholders, progress state
   - Dashboard/menu link to /imports/new and /imports
   - AC:
     - Visiting /imports/new shows stepper with disabled Next until valid
     - Upload validates size ≤10MB and mime text/csv

2. T2: CSV Preview & Mapping
   - Use ImportService::detectColumns and ::previewImport
   - Allow mapping: date, description, amount, type, (optional) account_name, category
   - Persist mapping in Livewire component state (in-memory for MVP)
   - AC:
     - User can see sample preview with chosen mappings (first N rows)
     - Mapping errors are validated and shown inline

3. T3: Process & Duplicates Strategy
   - Use ImportService::processImport
   - Behavior: do not create duplicates; generate duplicates report at storage/app/imports/logs/{import_id}-duplicates.csv (or .json)
   - Expose counts: total, created, skipped_duplicates, categorized
   - AC:
     - After processing, Result step shows counts and link to duplicates report if present
     - No duplicate transactions are created

4. T4: Import History (Read-only)
   - /imports lists Import records with filename, date range (if available), counts, status
   - Link to download duplicates report
   - AC:
     - History shows latest imports scoped per user and links work

5. T5: Tests (PEST Feature)
   - Add fixtures: tests/Fixtures/imports/basic.csv, with-dupes.csv
   - Feature coverage: success flow; validation errors (bad mime, >10MB simulated); duplicates logged and skipped; history list shows imports

6. T6: UX Polish
   - Inline errors, disabled navigation, confirmation before processing

## Sprint 2 – Duplicates Review & Merge Tool

Goal: Allow reviewing the duplicates after import and choosing Skip or Merge.

Tickets:
7. T7: Duplicates Review Page
   - Route: GET /imports/{import}/duplicates
   - Show candidate duplicate matches (existing transaction context: amount, date, description, account)
   - Actions: Skip (default), Merge (per row)
   - AC: User can navigate, view candidates, and choose actions (not persisted yet)

8. T8: Merge Flow & Persist Resolutions
   - Merge allows selecting fields to override (e.g., description, category)
   - Persist decisions (new service method or reuse ImportService createTransactionFromResolution for merges)
   - Update Import summary (merged_count, skipped_count) and status (reconciled)
   - Technical debt: rename ImportService::resolveHiplicates to resolveDuplicates; keep BC alias method forwarding to the new method
   - AC: Resolutions are saved; DB reflects merges; counts updated; a resolutions report is downloadable

9. T9: Tests (PEST Feature + Unit)
   - Feature: review page, Skip/Merge flows
   - Unit: ImportService duplicate confidence, idempotency via row hash, merge field overrides

## Sprint 3 – Recurring Transactions UI + Command

Tickets:
10. T10: RecurringPattern Management UI
    - app/Livewire/RecurringManager.php + Blade template
    - CRUD for patterns (type, amount, frequency, dates, category/account, transfer target)
    - Preview next occurrences using RecurringService::previewNextOccurrences
    - Pause/Resume/Skip next occurrence (service hooks available)
    - AC: Create/edit/pause/resume/skip; preview visible; validations

11. T11: Artisan Command & Scheduling
    - Command: php artisan budget:generate-recurring → calls RecurringService::generateDueTransactions
    - Optional: add to app/Console/Kernel schedule (daily) with feature flag to disable in dev
    - AC: Running command generates due transactions; idempotent per day

12. T12: Tests
    - Feature tests for UI; unit tests for due calculation edge cases (end_date, bi-weekly)

## Sprint 4 – Projections & Charting

Tickets:
13. T13: BalanceChart Component
    - Livewire component rendering chart (Chart.js/ApexCharts) using ProjectionService::calculateMonthlyProjections
    - Show total balance, income, expenses trend; highlight negative months

14. T14: Calendar Enhancements
    - Use ProjectionService::findNegativeBalanceDates to annotate CalendarView with warnings

## Configuration & Performance (Phase 7–8)

15. Config Files
- config/budget.php: feature toggles, currency, limits
- config/import.php: default mappings, size limit, delimiter
- config/categories.php: default category tree
- .env: IMPORT_CHUNK_SIZE (later), PROJECTION_MONTHS (later)

16. Performance & Polish
- Cache category hierarchy
- Paginate import history
- DB indexes (transaction_date, category_id) if missing
- Future: queue large imports (>2k rows)

## Risks & Mitigations
- Duplicate handling: ensure idempotency with row hash (ImportService::hashCsvRow)
- Large files: synchronous only for MVP; guard size and row count
- Security: validate CSV; sanitize descriptions; enforce user scoping on imports and review
- Technical debt: method rename resolveHiplicates → resolveDuplicates (BC alias)

## Acceptance Criteria Summary
- Sprint 1: User can complete an end-to-end import; duplicates are deferred to a report and not created; history shows imports; tests pass
- Sprint 2: User can review duplicates and Skip/Merge; merges update existing transactions; reports exist; tests pass
- Sprint 3: User can manage recurring patterns and generate due transactions via command; tests pass

## Testing Strategy
- PEST Feature tests for flows; unit tests for services (CSV parsing, duplicate detection, projections)
- Fixtures in tests/Fixtures/imports
- Use RefreshDatabase and factories for data setup

## Implementation Notes
- Storage: storage/app/imports and storage/app/imports/logs; use Storage facade
- UI: single-component stepper for ImportWizard; Livewire events for state transitions if needed
- Navigation: add lightweight links on dashboard until full nav exists
