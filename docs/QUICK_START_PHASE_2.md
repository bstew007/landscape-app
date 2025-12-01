# 🚀 Quick Start Guide - Jobs System Day 2

**Date:** December 1, 2025  
**Previous Session:** November 30, 2025 - Phase 1 Complete ✅

---

## 📋 What We Completed Yesterday

### ✅ Phase 1: Jobs System (COMPLETE & TESTED)
- Created 4 database migrations (jobs, job_work_areas, job_labor_items, job_material_items)
- Built 4 models with relationships and computed attributes
- Implemented JobCreationService with 8 methods
- Created JobController with index, show, update, createFromEstimate
- Built all views with modular partials (following your modular/clean code requirement)
- Added "Convert to Job" button on estimates
- Fixed foreign key constraints (labor_catalog, materials tables)
- **Tested successfully:** Created Job #JOB-2025-0001 from estimate #8

**Result:** You can now convert approved estimates to trackable jobs! 🎉

---

## 🎯 Today's Focus: Phase 2 - Timesheets System

### What We're Building
Track actual labor hours so you can see estimated vs actual costs in real-time.

### Key Features
1. **Clock in/out system** - Employees log hours daily
2. **Work area tracking** - Know which area they worked on
3. **Approval workflow** - Foreman approves before processing
4. **Auto-calculations** - Job costs update automatically from timesheets
5. **Variance tracking** - See labor overruns immediately
6. **Basic mobile support** - Simple endpoints for future mobile app

---

## 🗂️ Phase 2 Implementation Order

### Step 1: Database (Day 1 Morning)
Create timesheet migration with:
- Link to job, user, work area, labor item
- Clock in/out times
- Break tracking
- Auto-calculated total hours
- Hourly rate snapshot
- Approval status

### Step 2: Models & Relationships (Day 1 Morning)
- Timesheet model
- Update Job model (add timesheets relationship)
- Update User model (add timesheets relationship)
- Update JobWorkArea model (add timesheets relationship)

### Step 3: Service Layer (Day 1 Afternoon)
TimesheetService with methods:
- `clockIn()` - Start timesheet
- `clockOut()` - End timesheet with auto-calculation
- `submitForApproval()` - Change status to submitted
- `approve()` - Approve and update job costs
- `reject()` - Reject with reason

### Step 4: Controller & Routes (Day 1 Afternoon)
TimesheetController with:
- `index()` - List timesheets with filters
- `store()` - Create new timesheet
- `update()` - Edit draft timesheet
- `clockIn()` - API endpoint for clock in
- `clockOut()` - API endpoint for clock out
- `approve()` - Approve timesheet
- `reject()` - Reject timesheet

### Step 5: Views (Day 2)
Following the same modular pattern from Phase 1:
- `timesheets/index.blade.php` - List view
- `timesheets/create.blade.php` - Entry form
- `timesheets/partials/timesheet-row.blade.php` - Table row
- `timesheets/partials/status-badge.blade.php` - Status indicator
- Add timesheet section to `jobs/show.blade.php`

### Step 6: Job Cost Updates (Day 2)
- Add observer or event listener to update job costs when timesheet approved
- Update JobWorkArea actual_hours and actual_cost
- Update Job actual_labor_cost
- Recalculate variance

---

## 📁 Key Files to Reference

### Architecture Patterns (Follow These)
```
/app/Services/JobCreationService.php
└── Pattern for TimesheetService

/app/Http/Controllers/JobController.php
└── Pattern for TimesheetController

/resources/views/jobs/index.blade.php
└── Pattern for timesheets/index.blade.php

/database/migrations/2025_11_30_000001_create_jobs_table.php
└── Pattern for create_timesheets_table migration
```

### Integration Points
```
/app/Models/Job.php
└── Add timesheets() relationship

/app/Models/User.php
└── Add timesheets() relationship

/resources/views/jobs/show.blade.php
└── Add timesheet section showing hours logged
```

---

## 💻 Useful Commands

### Start Development Server
```bash
cd c:\laragon\www\landscape-app
php artisan serve
```

### Create New Files
```bash
# Migration
php artisan make:migration create_timesheets_table

# Model
php artisan make:model Timesheet

# Controller
php artisan make:controller TimesheetController

# Service (manual)
touch app/Services/TimesheetService.php
```

### Database
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Tinker (test models)
php artisan tinker
```

### Clear Caches
```bash
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

---

## 🧪 Testing Strategy

### Test Each Step
1. **After migration:** Check table exists in database
2. **After model:** Test relationships in Tinker
3. **After service:** Test business logic in Tinker
4. **After controller:** Test routes work
5. **After views:** Visual check in browser

### Example Tinker Tests
```php
// Test timesheet creation
$timesheet = Timesheet::create([
    'job_id' => 1,
    'user_id' => 2,
    'work_date' => today(),
    'clock_in' => '08:00:00',
    'clock_out' => '16:30:00',
    'break_minutes' => 30,
    'hourly_rate' => 65.00
]);

// Test relationships
$timesheet->job; // Should return Job
$timesheet->user; // Should return User

// Test service
$service = new App\Services\TimesheetService();
$timesheet = $service->clockIn($job, $user);
$service->clockOut($timesheet);
```

---

## 🎨 Theme Compliance (CRITICAL)

**Always use CFL charcoal/brand colors:**
- Primary buttons: `bg-brand-800 hover:bg-brand-700`
- Secondary buttons: `bg-white/10 border-white/40`
- Cards: `bg-white rounded-2xl shadow-sm`
- Headers: `gradient-to-r from-gray-800 to-gray-700`
- Icon backgrounds: `bg-brand-100` with `text-brand-800` icons
- Status badges: Use brand colors, not generic colors

**Card Pattern:**
```html
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <!-- Content -->
</div>
```

**Button Pattern:**
```html
<button class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg bg-brand-800 text-white hover:bg-brand-700 transition">
    <svg class="h-4 w-4"><!-- icon --></svg>
    Button Text
</button>
```

---

## 🚨 Known Issues & Solutions

### Issue: Foreign Keys to Wrong Tables
**Solution:** Check table names first!
- Labor catalog = `labor_catalog` (NOT labor_items)
- Materials = `materials` (NOT materials_catalog)

### Issue: Generic Error Messages
**Solution:** Always add detailed logging and JSON responses for AJAX:
```php
\Log::error('Operation failed', [
    'context' => $data,
    'error' => $e->getMessage()
]);

if (request()->wantsJson()) {
    return response()->json([
        'success' => false,
        'message' => $e->getMessage()
    ], 422);
}
```

### Issue: Relationship Not Loading
**Solution:** Use eager loading:
```php
$job = Job::with(['workAreas.laborItems', 'client', 'property'])->find($id);
```

---

## 📊 Database Schema Preview (Timesheets)

```sql
CREATE TABLE timesheets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Relationships
    job_id BIGINT UNSIGNED NOT NULL,
    job_work_area_id BIGINT UNSIGNED NULL,
    job_labor_item_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Time Tracking
    work_date DATE NOT NULL,
    clock_in TIME NOT NULL,
    clock_out TIME NULL,
    break_minutes INT DEFAULT 0,
    
    -- Calculated (use virtual/generated columns or model accessors)
    total_hours DECIMAL(5,2), -- (clock_out - clock_in - break) in hours
    
    -- Financial
    hourly_rate DECIMAL(10,2) NOT NULL, -- Snapshot at time of entry
    total_cost DECIMAL(12,2), -- total_hours * hourly_rate
    
    -- Metadata
    description TEXT NULL,
    status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Foreign Keys
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (job_work_area_id) REFERENCES job_work_areas(id) ON DELETE SET NULL,
    FOREIGN KEY (job_labor_item_id) REFERENCES job_labor_items(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_work_date (work_date),
    INDEX idx_status (status),
    INDEX idx_user_date (user_id, work_date)
);
```

---

## 🎯 Success Criteria for Today

By end of today, you should have:
- [x] Timesheets migration created and run
- [x] Timesheet model with relationships
- [x] TimesheetService with clock in/out methods
- [x] TimesheetController with basic CRUD
- [x] Routes for timesheets
- [x] Basic timesheet entry form (view)
- [x] Timesheet list page
- [x] Job costs auto-update when timesheet approved

**Stretch Goal:** Add timesheet section to job detail page showing hours logged per work area

---

## 📞 Important Notes

1. **Modular Code:** Keep using partials like you did in Phase 1
2. **Service Layer:** Put business logic in TimesheetService, not controller
3. **Transaction Wrapping:** Wrap approve() in DB::transaction
4. **Error Handling:** Log everything, return helpful messages
5. **Theme Compliance:** Brand colors on all new views
6. **Testing:** Test each piece before moving to next

---

## 🔗 Documentation References

- **Full roadmap:** `/docs/JOBS_TIMESHEETS_MOBILE_IMPLEMENTATION_PLAN.md`
- **Phase 1 status:** `/docs/JOBS_PHASE_1_COMPLETION_STATUS.md`
- **This guide:** `/docs/QUICK_START_PHASE_2.md`

---

**You've got this! Phase 1 was perfect, Phase 2 will be just as smooth.** 🚀

Remember:
- Follow the patterns from Phase 1
- Test as you go
- Keep it modular
- Stay theme-compliant
- Log errors for debugging

**Let's build an amazing timesheet system!** 💪
