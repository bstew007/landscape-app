# Asset Management System

## Overview

The Asset Management System is a comprehensive module designed to track, maintain, and manage company assets such as vehicles, equipment, trailers, and tools. It provides complete lifecycle management from purchase to retirement, including maintenance scheduling, issue tracking, usage logs, expense tracking, and QuickBooks Online integration.

## Table of Contents

- [Core Entities](#core-entities)
- [Database Schema](#database-schema)
- [Features](#features)
- [Controllers](#controllers)
- [Routes](#routes)
- [Views](#views)
- [Workflows](#workflows)
- [QuickBooks Integration](#quickbooks-integration)
- [Known Issues](#known-issues)
- [Future Enhancements](#future-enhancements)

---

## Core Entities

### 1. Asset (`Asset.php`)

**Purpose:** The main entity representing any physical asset owned by the company.

**Key Properties:**
- `id` - Primary key
- `name` - Asset name/description
- `model` - Asset model/make
- `type` - Asset category (crew_truck, dump_truck, skid_steer, excavator, mowers, hand_tools, shop_tools, enclosed_trailer, dump_trailer, equipment_trailer)
- `identifier` - VIN, serial number, or unique identifier
- `status` - Current status (active, in_maintenance, retired)
- `purchase_date` - Date of purchase
- `purchase_price` - Purchase cost
- `assigned_to` - User/employee name assigned to asset (stored as string)
- `mileage_hours` - Current mileage or operating hours
- `next_service_date` - Scheduled maintenance date
- `notes` - General notes
- `reminder_enabled` - Whether service reminders are enabled
- `reminder_days_before` - Days before service date to send reminder
- `last_reminder_sent_at` - Timestamp of last reminder sent

**Relationships:**
- `maintenances()` - HasMany AssetMaintenance
- `issues()` - HasMany AssetIssue
- `attachments()` - HasMany AssetAttachment
- `usageLogs()` - HasMany AssetUsageLog
- `expenses()` - HasMany AssetExpense
- `assignedUser()` - BelongsTo User (via name match)
- `linkedAssets()` - BelongsToMany Asset (parent-child relationships)
- `parentAssets()` - BelongsToMany Asset (reverse of linkedAssets)

**Constants:**
- `STATUSES` = ['active', 'in_maintenance', 'retired']
- `TYPES` = [array of 10 asset type options]

### 2. AssetMaintenance (`AssetMaintenance.php`)

**Purpose:** Tracks scheduled and completed maintenance activities.

**Key Properties:**
- `asset_id` - Foreign key to assets
- `scheduled_at` - When maintenance is scheduled
- `completed_at` - When maintenance was completed
- `type` - Type of maintenance (Inspection, Oil Change, Service, Repair)
- `notes` - Maintenance details
- `mileage_hours` - Odometer/hours at time of maintenance

**Relationships:**
- `asset()` - BelongsTo Asset

### 3. AssetIssue (`AssetIssue.php`)

**Purpose:** Logs problems, defects, or issues with assets.

**Key Properties:**
- `asset_id` - Foreign key to assets
- `title` - Issue title/summary
- `description` - Detailed description
- `status` - Current status (open, in_progress, resolved)
- `severity` - Issue priority (low, normal, high, critical)
- `reported_on` - Date issue was reported
- `resolved_on` - Date issue was resolved

**Relationships:**
- `asset()` - BelongsTo Asset

**Constants:**
- `STATUSES` = ['open', 'in_progress', 'resolved']
- `SEVERITIES` = ['low', 'normal', 'high', 'critical']

### 4. AssetAttachment (`AssetAttachment.php`)

**Purpose:** Stores file attachments related to assets (photos, documents, receipts).

**Key Properties:**
- `asset_id` - Foreign key to assets
- `label` - Optional label/description
- `path` - File storage path
- `mime_type` - File MIME type
- `size` - File size in bytes

**Relationships:**
- `asset()` - BelongsTo Asset

**Computed Properties:**
- `url` - Full URL to access the file

### 5. AssetUsageLog (`AssetUsageLog.php`)

**Purpose:** Tracks check-in/check-out events for assets, recording who used what and when.

**Key Properties:**
- `asset_id` - Foreign key to assets
- `user_id` - Foreign key to users
- `checked_out_at` - Checkout timestamp
- `checked_in_at` - Check-in timestamp
- `mileage_out` - Odometer reading at checkout
- `mileage_in` - Odometer reading at check-in
- `inspection_data` - JSON field for inspection checklist data
- `notes` - Usage notes
- `status` - Current status (checked_out, checked_in)

**Relationships:**
- `asset()` - BelongsTo Asset
- `user()` - BelongsTo User

**Methods:**
- `isCheckedOut()` - Returns true if status is 'checked_out'
- `isCheckedIn()` - Returns true if status is 'checked_in'

### 6. AssetExpense (`AssetExpense.php`)

**Purpose:** Tracks expenses related to assets (fuel, repairs, general costs).

**Key Properties:**
- `asset_id` - Foreign key to assets
- `asset_issue_id` - Optional link to related issue (required for repairs)
- `category` - Expense category (validated against active ExpenseAccountMapping)
- `subcategory` - Subcategory (gas/diesel/oil, insurance/registration/permit)
- `vendor` - Vendor name
- `amount` - Expense amount
- `expense_date` - Date of expense
- `odometer_hours` - Odometer reading at time of expense
- `description` - Expense description
- `notes` - Additional notes
- `receipt_number` - Receipt/invoice number
- `is_reimbursable` - Whether expense is reimbursable
- `submitted_by` - User who submitted expense
- `approved_by` - User who approved expense
- `qbo_expense_id` - QuickBooks expense ID
- `qbo_synced_at` - Timestamp when synced to QBO
- `qbo_account_id` - QuickBooks account ID for categorization

**Relationships:**
- `asset()` - BelongsTo Asset
- `assetIssue()` - BelongsTo AssetIssue
- `submittedBy()` - BelongsTo User
- `approvedBy()` - BelongsTo User
- `attachments()` - HasMany AssetExpenseAttachment

**Methods:**
- `isSyncedToQbo()` - Check if synced to QuickBooks
- `isApproved()` - Check if expense is approved
- Scopes: `fuel()`, `repairs()`, `general()`

**Business Rules:**
- Category must be from active ExpenseAccountMapping
- Repairs category requires an asset_issue_id

### 7. AssetExpenseAttachment (`AssetExpenseAttachment.php`)

**Purpose:** File attachments for expense records (receipts, invoices).

**Key Properties:**
- `asset_expense_id` - Foreign key to asset_expenses
- `file_path` - Storage path
- `file_name` - Original filename
- `file_type` - MIME type
- `file_size` - File size in bytes
- `uploaded_by` - User who uploaded

**Relationships:**
- `expense()` - BelongsTo AssetExpense
- `uploadedBy()` - BelongsTo User

**Computed Properties:**
- `url` - Full URL to file
- `fileSizeHuman` - Human-readable file size

**Events:**
- Auto-deletes physical file when model is deleted

### 8. Asset Links (Pivot Table)

**Purpose:** Creates parent-child relationships between assets (e.g., trailer contains mowers).

**Key Fields:**
- `parent_asset_id` - Asset that contains/carries
- `child_asset_id` - Asset being contained/carried
- `relationship_type` - Type of relationship (e.g., 'contains', 'attached_to', 'towing')
- `notes` - Additional notes

**Constraints:**
- Unique combination of parent and child
- Prevents linking asset to itself
- Cascade deletes when either asset is deleted

---

## Database Schema

### Migration Files (Chronological Order)

1. **2025_11_10_170000_create_assets_table.php**
   - Creates core assets table
   - Defines basic asset properties

2. **2025_11_10_170100_create_asset_maintenances_table.php**
   - Creates maintenance tracking table
   - Links to assets with cascade delete

3. **2025_11_10_170200_create_asset_issues_table.php**
   - Creates issue tracking table
   - Supports status and severity tracking

4. **2025_11_10_170300_create_asset_attachments_table.php**
   - Creates attachment storage table
   - Tracks files related to assets

5. **2025_11_10_171000_add_reminder_columns_to_assets_table.php**
   - Adds service reminder functionality
   - Fields: reminder_enabled, reminder_days_before, last_reminder_sent_at

6. **2025_12_05_085553_add_model_to_assets_table.php**
   - Adds 'model' field to assets
   - Allows tracking manufacturer/model info

7. **2025_12_05_121334_create_asset_links_table.php**
   - Creates many-to-many relationship table
   - Enables parent-child asset linking

8. **2025_12_05_122949_create_asset_usage_logs_table.php**
   - Creates check-in/check-out tracking
   - Supports inspection data as JSON

9. **2025_12_05_135918_create_asset_expenses_table.php**
   - Creates expense tracking table
   - Initially uses ENUM for category

10. **2025_12_05_135925_create_asset_expense_attachments_table.php**
    - Creates expense attachment storage
    - Links receipts/invoices to expenses

11. **2025_12_05_143803_add_qbo_account_id_to_asset_expenses_table.php**
    - Adds QuickBooks account mapping
    - Enables better QBO integration

12. **2025_12_05_194150_change_asset_expenses_category_to_string.php**
    - Changes category from ENUM to string
    - Allows dynamic categories via ExpenseAccountMapping

---

## Features

### 1. Asset Dashboard

**Location:** `AssetController@index`

**Capabilities:**
- View all assets with filtering by status, type, assigned user, service window
- Search by name, identifier, or assigned user
- Summary statistics:
  - Total assets
  - Active assets
  - Maintenance due (within 14 days)
  - Open issues count
- Type breakdown chart
- Upcoming services list
- Overdue services list
- Reminder candidates (assets needing reminders)
- Checked-out assets indicator
- Pagination with query string preservation

**Filters:**
- Status (active, in_maintenance, retired)
- Type (10 predefined types)
- Search term
- Assigned to (dropdown of existing assignments)
- Service window (overdue, upcoming)

### 2. Asset Details

**Location:** `AssetController@show`

**Displays:**
- Asset information card
- Maintenance history
- Issue tracker
- File attachments
- Linked assets (parent and child)
- Usage logs (last 10)
- Expense summary (if expenses exist)

**Actions:**
- Edit asset
- Delete asset
- Add maintenance record
- Log issue
- Upload attachment
- Link/unlink other assets
- Check out/check in
- Add expense

### 3. Maintenance Management

**Features:**
- Schedule future maintenance
- Log completed maintenance
- Track maintenance type (Inspection, Oil Change, Service, Repair)
- Record mileage/hours at time of service
- Auto-clear next_service_date when maintenance completed

**Workflow:**
1. Set next_service_date on asset
2. Enable reminders with days_before setting
3. System identifies reminder candidates
4. Log maintenance when completed
5. next_service_date automatically cleared

### 4. Issue Tracking

**Features:**
- Create issues with severity (low, normal, high, critical)
- Track status (open, in_progress, resolved)
- Link issues to repair expenses
- Quick issue logging from dashboard
- Filter by status and severity

**Workflow:**
1. Report issue via asset detail or quick form
2. Set severity and status
3. Update status as work progresses
4. If repairs needed, create expense linked to issue
5. Mark resolved when complete

### 5. Usage Logging (Check-In/Check-Out)

**Features:**
- Check out assets to users
- Record start mileage/hours
- Inspection checklist support (JSON data)
- Check in with end mileage/hours
- Calculate usage time
- Prevent double checkout
- Edit/delete usage logs

**Workflow:**
1. User checks out asset (records user_id, mileage_out, timestamp)
2. Optional inspection data captured
3. User checks in asset (records mileage_in, check-in time)
4. Asset mileage/hours updated automatically
5. Usage log marked as checked_in

### 6. Expense Tracking

**Features:**
- Track fuel, repairs, and general expenses
- Link repair expenses to issues
- Upload receipt attachments
- QuickBooks Online sync
- Approval workflow
- Vendor tracking
- Reimbursable expense flagging

**Category System:**
- Categories are managed via ExpenseAccountMapping model
- Dynamic validation ensures only active categories can be used
- Each category can map to different QBO accounts

**Workflow:**
1. Select asset
2. Choose category (fuel/repairs/general)
3. If repairs, must select related issue
4. Enter amount, date, vendor, description
5. Upload receipt attachments
6. Submit expense
7. Awaits approval
8. Sync to QuickBooks when approved

### 7. Asset Linking

**Features:**
- Link related assets (e.g., trailer contains mowers)
- Define relationship types
- Add notes to relationships
- View both parent and child relationships
- Prevent self-linking
- Prevent duplicate links

**Use Cases:**
- Trailer carrying multiple mowers
- Truck towing trailer
- Equipment attached to vehicle
- Tool sets grouped together

### 8. Service Reminders

**Features:**
- Enable/disable reminders per asset
- Set days before service date for reminder
- Track last reminder sent timestamp
- Dashboard shows reminder candidates
- Prevents duplicate reminders

**Candidate Logic:**
- Asset must have reminder_enabled = true
- Must have next_service_date set
- Days until service ≤ reminder_days_before
- Days until service ≥ -1 (allows 1 day grace period)

### 9. Reporting

**Location:** `AssetReportController`

**Available Reports:**

#### a. Usage Report
- Filter by date range, asset, user
- Shows checkout/check-in history
- Displays usage duration
- Tracks mileage/hours used

#### b. Maintenance Report
- Filter by date range, asset
- Lists completed maintenance
- Shows maintenance type
- Displays cost (if available)

#### c. Issues Report
- Filter by status, severity, asset
- Lists all issues
- Shows resolution dates
- Tracks time to resolution

#### d. Utilization Report
- Shows usage statistics per asset
- Calculates total hours used
- Tracks total mileage
- Identifies underutilized assets

#### e. Costs Report
- Aggregates expenses by asset
- Breaks down by category (fuel, repairs, general)
- Shows total cost of ownership
- Maintenance and issue counts

---

## Controllers

### AssetController

**Responsibilities:**
- CRUD operations for assets
- Maintenance logging
- Issue logging
- Attachment management
- Asset linking/unlinking
- Check-in/check-out operations
- Usage log management

**Key Methods:**
- `index()` - Dashboard with filtering and statistics
- `create()` - Create asset form
- `store()` - Save new asset
- `show()` - Asset detail page
- `edit()` - Edit asset form
- `update()` - Update asset
- `destroy()` - Delete asset
- `storeMaintenance()` - Add maintenance record
- `storeIssue()` - Add issue
- `createIssue()` - Quick issue form
- `storeIssueQuick()` - Save quick issue
- `createReminder()` - Reminder setup form
- `storeReminder()` - Save reminder settings
- `storeAttachment()` - Upload file
- `destroyAttachment()` - Delete file
- `linkAsset()` - Link two assets
- `unlinkAsset()` - Unlink assets
- `showCheckout()` - Checkout form
- `storeCheckout()` - Process checkout
- `showCheckin()` - Check-in form
- `storeCheckin()` - Process check-in
- `editUsageLog()` - Edit usage log
- `updateUsageLog()` - Update usage log
- `destroyUsageLog()` - Delete usage log
- `validateAsset()` - Validation rules

### AssetExpenseController

**Responsibilities:**
- Expense CRUD operations
- Attachment management
- QuickBooks sync
- Approval workflow

**Key Methods:**
- `selectAsset()` - Asset selection page
- `create()` - Create expense form
- `store()` - Save expense with validations
- `edit()` - Edit expense form
- `update()` - Update expense
- `destroy()` - Delete expense
- `approve()` - Approve expense
- `syncToQbo()` - Sync to QuickBooks
- `deleteAttachment()` - Delete expense attachment
- `downloadAttachment()` - Download expense attachment
- `getQboExpenseAccounts()` - Fetch QBO accounts

**Validations:**
- Category must be from active ExpenseAccountMapping
- Repairs must have asset_issue_id
- Amount must be positive
- File uploads limited to 10MB per file

### AssetReportController

**Responsibilities:**
- Generate various asset reports
- Aggregate data for analysis
- Date range filtering

**Key Methods:**
- `index()` - Report selection page
- `usageReport()` - Usage history report
- `maintenanceReport()` - Maintenance history
- `issuesReport()` - Issues tracking
- `utilizationReport()` - Asset utilization analysis
- `costsReport()` - Cost breakdown by asset

---

## Routes

### Asset Management Routes

```php
// Quick forms (must be before resource routes)
Route::get('asset-issues/create', [AssetController, 'createIssue'])->name('assets.issues.create');
Route::post('asset-issues', [AssetController, 'storeIssueQuick'])->name('assets.issues.quickStore');
Route::get('asset-reminders/create', [AssetController, 'createReminder'])->name('assets.reminders.create');
Route::post('asset-reminders', [AssetController, 'storeReminder'])->name('assets.reminders.store');

// Resource routes
Route::resource('assets', AssetController::class);

// Nested routes
Route::post('assets/{asset}/maintenance', [AssetController, 'storeMaintenance'])->name('assets.maintenance.store');
Route::post('assets/{asset}/issues', [AssetController, 'storeIssue'])->name('assets.issues.store');
Route::post('assets/{asset}/attachments', [AssetController, 'storeAttachment'])->name('assets.attachments.store');
Route::delete('assets/{asset}/attachments/{attachment}', [AssetController, 'destroyAttachment'])->name('assets.attachments.destroy');
Route::post('assets/{asset}/link', [AssetController, 'linkAsset'])->name('assets.link');
Route::post('assets/{asset}/unlink/{linkedAsset}', [AssetController, 'unlinkAsset'])->name('assets.unlink');

// Check-in/Check-out
Route::get('assets/{asset}/checkout', [AssetController, 'showCheckout'])->name('assets.checkout');
Route::post('assets/{asset}/checkout', [AssetController, 'storeCheckout'])->name('assets.checkout.store');
Route::get('assets/{asset}/checkin', [AssetController, 'showCheckin'])->name('assets.checkin');
Route::post('assets/{asset}/checkin', [AssetController, 'storeCheckin'])->name('assets.checkin.store');

// Usage logs
Route::get('assets/{asset}/usage-logs/{usageLog}/edit', [AssetController, 'editUsageLog'])->name('assets.usage-logs.edit');
Route::put('assets/{asset}/usage-logs/{usageLog}', [AssetController, 'updateUsageLog'])->name('assets.usage-logs.update');
Route::delete('assets/{asset}/usage-logs/{usageLog}', [AssetController, 'destroyUsageLog'])->name('assets.usage-logs.destroy');
```

### Expense Routes

```php
Route::get('assets-expenses/select-asset', [AssetExpenseController, 'selectAsset'])->name('assets.expenses.select-asset');
Route::get('assets/{asset}/expenses/create', [AssetExpenseController, 'create'])->name('assets.expenses.create');
Route::post('assets/{asset}/expenses', [AssetExpenseController, 'store'])->name('assets.expenses.store');
Route::get('assets/{asset}/expenses/{expense}/edit', [AssetExpenseController, 'edit'])->name('assets.expenses.edit');
Route::put('assets/{asset}/expenses/{expense}', [AssetExpenseController, 'update'])->name('assets.expenses.update');
Route::delete('assets/{asset}/expenses/{expense}', [AssetExpenseController, 'destroy'])->name('assets.expenses.destroy');
Route::post('assets/{asset}/expenses/{expense}/approve', [AssetExpenseController, 'approve'])->name('assets.expenses.approve');
Route::post('assets/{asset}/expenses/{expense}/sync-qbo', [AssetExpenseController, 'syncToQbo'])->name('assets.expenses.sync-qbo');
Route::delete('assets/{asset}/expenses/{expense}/attachments/{attachment}', [AssetExpenseController, 'deleteAttachment'])->name('assets.expenses.attachments.delete');
Route::get('assets/{asset}/expenses/{expense}/attachments/{attachment}/download', [AssetExpenseController, 'downloadAttachment'])->name('assets.expenses.attachments.download');
```

### Report Routes

```php
Route::get('asset-reports', [AssetReportController, 'index'])->name('asset-reports.index');
Route::get('asset-reports/usage', [AssetReportController, 'usageReport'])->name('asset-reports.usage');
Route::get('asset-reports/maintenance', [AssetReportController, 'maintenanceReport'])->name('asset-reports.maintenance');
Route::get('asset-reports/issues', [AssetReportController, 'issuesReport'])->name('asset-reports.issues');
Route::get('asset-reports/utilization', [AssetReportController, 'utilizationReport'])->name('asset-reports.utilization');
Route::get('asset-reports/costs', [AssetReportController, 'costsReport'])->name('asset-reports.costs');
```

---

## Views

### Asset Views

- `resources/views/assets/index.blade.php` - Dashboard with filters and summary
- `resources/views/assets/show.blade.php` - Detailed asset page
- `resources/views/assets/create.blade.php` - Create new asset
- `resources/views/assets/edit.blade.php` - Edit asset
- `resources/views/assets/_form.blade.php` - Shared form partial
- `resources/views/assets/quick-issue.blade.php` - Quick issue creation
- `resources/views/assets/reminder.blade.php` - Service reminder setup
- `resources/views/assets/checkout.blade.php` - Check-out form
- `resources/views/assets/checkin.blade.php` - Check-in form
- `resources/views/assets/edit-usage-log.blade.php` - Edit usage log

### Expense Views

- `resources/views/assets/expenses/select-asset.blade.php` - Asset selection for expense
- `resources/views/assets/expenses/create.blade.php` - Create expense
- `resources/views/assets/expenses/edit.blade.php` - Edit expense

### Report Views

- `resources/views/asset-reports/index.blade.php` - Report dashboard
- `resources/views/asset-reports/usage.blade.php` - Usage report
- `resources/views/asset-reports/maintenance.blade.php` - Maintenance report
- `resources/views/asset-reports/issues.blade.php` - Issues report
- `resources/views/asset-reports/utilization.blade.php` - Utilization report
- `resources/views/asset-reports/costs.blade.php` - Costs report

---

## Workflows

### Complete Asset Lifecycle

```
1. ACQUISITION
   ├─ Create asset record (name, type, identifier, purchase info)
   └─ Upload documents (title, registration, warranty)

2. ASSIGNMENT
   ├─ Assign to user/employee
   └─ Set initial mileage/hours

3. OPERATION
   ├─ Check-out/check-in tracking
   ├─ Usage logging
   ├─ Fuel expenses
   └─ General expenses

4. MAINTENANCE
   ├─ Schedule service (set next_service_date)
   ├─ Enable reminders
   ├─ Receive reminder notification
   ├─ Perform maintenance
   ├─ Log maintenance record
   └─ Update mileage/hours

5. ISSUE MANAGEMENT
   ├─ Report issue
   ├─ Set severity
   ├─ Track progress (in_progress)
   ├─ Create repair expense (linked to issue)
   ├─ Complete repair
   └─ Resolve issue

6. RETIREMENT
   ├─ Change status to 'retired'
   ├─ Record disposal details in notes
   └─ Maintain historical data
```

### Expense Processing Workflow

```
1. EXPENSE CREATION
   ├─ Select asset
   ├─ Choose category
   ├─ If repairs → select related issue
   ├─ Enter details (amount, date, vendor)
   ├─ Upload receipts
   └─ Submit

2. APPROVAL
   ├─ Review expense
   ├─ Verify receipts
   ├─ Approve expense
   └─ approved_by field populated

3. QUICKBOOKS SYNC
   ├─ Map to QBO account
   ├─ Sync to QuickBooks
   ├─ Receive qbo_expense_id
   ├─ Set qbo_synced_at timestamp
   └─ Expense appears in QBO
```

### Asset Linking Workflow

```
1. IDENTIFY RELATIONSHIP
   ├─ Parent asset (trailer)
   └─ Child assets (mowers)

2. CREATE LINKS
   ├─ Navigate to parent asset
   ├─ Click "Link Asset"
   ├─ Select child asset
   ├─ Choose relationship type
   ├─ Add notes
   └─ Save

3. VIEW RELATIONSHIPS
   ├─ Parent shows all linked children
   └─ Children show parent assets

4. USAGE TRACKING
   ├─ Check out parent
   └─ Children automatically associated
```

---

## QuickBooks Integration

### Connected Services

1. **QboExpenseService**
   - Syncs AssetExpense to QuickBooks
   - Maps categories to QBO accounts
   - Handles vendor lookups
   - Creates purchase/expense entries

### Integration Points

#### Expense Sync
- Field Mappings:
  - `amount` → Transaction Amount
  - `expense_date` → Transaction Date
  - `vendor` → Vendor (requires Contact with qbo_vendor_id)
  - `qbo_account_id` → Expense Account
  - `description` + `notes` → Memo

#### Account Mapping
- Uses ExpenseAccountMapping model
- Maps local categories to QBO accounts
- Supports dynamic category validation
- Account selection in expense forms

#### Sync Status Tracking
- `qbo_expense_id` - QuickBooks expense ID
- `qbo_synced_at` - Sync timestamp
- Method: `isSyncedToQbo()` - Check sync status

### Configuration

Located in `config/qbo.php` (assumed based on integration patterns):
- QBO credentials
- Account mappings
- Default settings

---

## Known Issues

### 1. Assigned User Mismatch
**Problem:** `assigned_to` field stores user name as string, but relationship tries to match User model
**Impact:** `assignedUser()` relationship may fail if names don't match exactly
**Workaround:** Store user_id instead of name, or ensure consistent name matching
**Priority:** Medium

### 2. Maintenance Cost Tracking
**Problem:** AssetMaintenance table doesn't have a `cost` field
**Impact:** Cost reports show $0 for maintenance
**Workaround:** Track maintenance costs as general expenses
**Priority:** Low
**Future:** Add `cost` field to asset_maintenances table

### 3. Usage Log Time Calculation
**Problem:** No built-in method to calculate total usage time
**Impact:** Must calculate manually in views
**Solution:** Add accessor method `getTotalUsageHoursAttribute()` to AssetUsageLog
**Priority:** Low

### 4. Asset Type Dropdown
**Problem:** Asset types are hardcoded in model constant
**Impact:** Cannot add new types without code change
**Future:** Move to database-driven configuration
**Priority:** Low

### 5. Expense Category Migration
**Problem:** Changed from ENUM to string, requires ExpenseAccountMapping
**Impact:** Old expenses may have categories not in mapping table
**Workaround:** Ensure all old categories have mapping records
**Priority:** Medium

### 6. Reminder Implementation
**Problem:** Reminder system identifies candidates but doesn't send emails
**Impact:** Manual checking of reminder_candidates required
**Solution:** Implement scheduled job to send reminder emails
**Priority:** High

### 7. Double Checkout Prevention
**Problem:** Check validates last usage log but not global status
**Impact:** Edge case where multiple checkouts possible if logs deleted
**Solution:** Add `is_checked_out` boolean to assets table
**Priority:** Medium

### 8. File Storage
**Problem:** Attachments stored in public disk without access control
**Impact:** Files accessible to anyone with URL
**Solution:** Move to private disk with signed URLs
**Priority:** Medium

---

## Future Enhancements

### Priority 1 (Critical)

1. **Automated Service Reminders**
   - Scheduled job to identify reminder candidates
   - Email notifications to asset owners/managers
   - SMS/Slack integration options
   - Reminder history log

2. **Comprehensive Mobile App**
   - Field check-in/check-out via mobile
   - Photo upload for issues
   - Quick fuel logging
   - Offline support with sync

3. **Advanced Reporting Dashboard**
   - Real-time cost tracking
   - Depreciation calculations
   - ROI analysis per asset
   - Predictive maintenance scheduling
   - Export to Excel/PDF

### Priority 2 (Important)

4. **Asset Barcode/QR Code System**
   - Generate unique codes for each asset
   - Mobile scanning for quick check-in/out
   - Print barcode labels
   - Scan-to-view asset details

5. **GPS/Telematics Integration**
   - Track asset location in real-time
   - Automatic mileage logging
   - Geofencing alerts
   - Route optimization

6. **Preventive Maintenance Scheduler**
   - Auto-schedule based on mileage/hours
   - Recurring maintenance plans
   - Parts inventory integration
   - Service provider management

7. **Asset Transfer Module**
   - Transfer assets between locations/departments
   - Transfer approval workflow
   - Transfer history tracking
   - Custody chain documentation

8. **Enhanced Expense Management**
   - Bulk expense import
   - Credit card transaction matching
   - Budget vs. actual tracking
   - Approval routing based on amount

### Priority 3 (Nice to Have)

9. **Asset Insurance Tracking**
   - Insurance policy management
   - Coverage limits tracking
   - Renewal reminders
   - Claims history

10. **Warranty Management**
    - Warranty expiration tracking
    - Claim filing workflow
    - Vendor warranty terms
    - Warranty cost recovery

11. **Asset Lifecycle Costing**
    - Total cost of ownership (TCO)
    - Depreciation schedules
    - Replacement analysis
    - Lease vs. buy calculations

12. **Asset Performance Metrics**
    - Uptime/downtime tracking
    - Mean time between failures (MTBF)
    - Utilization rate trends
    - Cost per usage hour

13. **Document Management**
    - Version control for attachments
    - Document expiration tracking (registration, permits)
    - Bulk document upload
    - Document templates

14. **Multi-Location Support**
    - Asset location tracking
    - Location-based filtering
    - Inter-location transfers
    - Location-specific reports

15. **Asset Audit Trail**
    - Complete change history
    - User action logging
    - Compliance reporting
    - Audit-ready exports

16. **Parts Inventory Integration**
    - Parts used in maintenance
    - Parts cost tracking
    - Inventory depletion on maintenance
    - Reorder point alerts

17. **Vendor Management**
    - Vendor performance tracking
    - Preferred vendor lists
    - Contract management
    - Vendor contact directory

18. **User Permissions Refinement**
    - Role-based access (viewer, editor, approver)
    - Department-based asset access
    - Custom permission sets
    - Delegation of approvals

19. **Asset Disposal Workflow**
    - Disposal request process
    - Disposal method tracking (sale, donation, scrap)
    - Disposal documentation
    - Asset value recovery tracking

20. **Advanced Analytics**
    - Machine learning for maintenance prediction
    - Anomaly detection for expenses
    - Usage pattern analysis
    - Cost optimization recommendations

---

## Technical Debt

### Code Quality
- Add comprehensive unit tests for models
- Add feature tests for controllers
- Implement request form validation classes
- Add API endpoints for mobile app

### Performance
- Add database indexes for frequently queried fields
- Implement caching for dashboard statistics
- Optimize N+1 query issues in reports
- Add pagination to expense lists

### Security
- Implement policy classes for authorization
- Add CSRF protection verification
- Sanitize file uploads more strictly
- Add rate limiting to expense sync

### Documentation
- Add inline PHPDoc comments
- Create API documentation
- Document expense account mapping setup
- Create user guide for each feature

---

## Dependencies

### Laravel Packages
- Laravel Framework 10.x
- Laravel Filesystem (for file uploads)
- Carbon (date/time manipulation)

### External Services
- QuickBooks Online API (expense sync)
- Storage system (local/S3 for files)

### Related Models
- User (for assignments and usage tracking)
- Contact (for vendor relationships)
- ExpenseAccountMapping (for QBO category mapping)

---

## Configuration

### Environment Variables
```env
# File Storage
FILESYSTEM_DISK=public

# QuickBooks
QBO_CLIENT_ID=
QBO_CLIENT_SECRET=
QBO_REDIRECT_URI=
QBO_ENVIRONMENT=sandbox|production
```

### Asset Types Configuration
Located in `Asset::TYPES` constant. To add new types, modify:
```php
public const TYPES = [
    'crew_truck',
    'dump_truck',
    // Add new types here
];
```

### Expense Categories
Managed via ExpenseAccountMapping model in database. Categories are dynamic and validated at runtime.

---

## Summary

The Asset Management System is a robust, full-featured module providing end-to-end asset lifecycle management. It successfully integrates with QuickBooks for financial tracking, provides comprehensive reporting, and supports complex workflows like check-in/out, issue tracking, and expense management.

**Strengths:**
- Comprehensive feature set
- Strong relationships between entities
- QuickBooks integration
- Flexible expense categorization
- Usage tracking with inspection support
- Asset linking for complex relationships

**Areas for Improvement:**
- Implement automated reminders
- Add more granular permissions
- Improve mobile experience
- Enhance reporting visualizations
- Add maintenance cost tracking
- Strengthen file access controls

**Overall Assessment:** The system provides excellent foundational functionality with clear paths for future enhancement. The modular design allows for incremental improvements without disrupting existing features.
