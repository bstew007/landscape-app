# Landscape App - Current State Summary

**Last Updated:** November 30, 2025  
**Current Status:** Production-ready estimate system + Jobs system (Phase 1 complete)

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    LANDSCAPE BUSINESS APP                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Site Visit → Calculator → Estimate → Job → Timesheets → QBO  │
│     ✅          ✅          ✅        ✅        ⏳           ✅    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

**Legend:**
- ✅ = Production ready and working
- ⏳ = In progress (Phase 2)
- 🔜 = Planned (Phase 3+)

---

## ✅ What's Currently Working (Production)

### 1. Budget System
**Location:** `/app/Models/Budget.php`, `/app/Services/BudgetService.php`

**Features:**
- Overhead rate calculation (41.62% currently)
- Labor cost multipliers
- Profit margin targets
- Division-specific rates

**Key Tables:**
- `budgets` - Budget records with overhead rates
- `divisions` - Company divisions
- `cost_codes` - Project categorization

### 2. Material Catalog
**Location:** `/app/Models/Material.php`

**Features:**
- 1597+ materials with costs
- Category organization
- Unit tracking (sqft, ea, lf, etc.)
- QuickBooks integration ready (qbo_item_id)

**Key Tables:**
- `materials` - Material catalog
- `material_categories` - Categories
- `material_material_category` - Pivot table

### 3. Labor Catalog
**Location:** `/app/Models/LaborItem.php`

**Features:**
- Labor items with hourly rates
- Crew classifications
- Equipment tracking
- QuickBooks integration ready

**Key Tables:**
- `labor_catalog` - Labor items and rates

### 4. Site Visit Calculators
**Location:** `/app/Http/Controllers/CalculatorController.php`

**Features:**
- Turf removal calculator
- Installation calculator
- Hardscape calculator
- Converts calculations to structured estimates

**Key Data:**
- Scope-based pricing
- Automated labor/material selection
- Instant cost calculations

### 5. Estimate System
**Location:** `/app/Models/Estimate.php`, `/app/Http/Controllers/EstimateController.php`

**Features:**
- Work area breakdown
- Granular labor/material line items
- Cost tracking (estimated vs budgeted)
- Profit margin calculations
- Status workflow (draft → pending → approved → rejected)
- QuickBooks sync ready
- Beautiful print layout
- Email sending
- Custom pricing per line item

**Key Tables:**
- `estimates` - Main estimate records
- `estimate_areas` - Work area breakdown
- `estimate_items` - Labor and material line items

**Views:**
- List view with stats and filters
- Detail view with live calculations
- Print view with branded layout
- Edit view with calculator integration

### 6. Jobs System (NEW - Phase 1 Complete!)
**Location:** `/app/Models/Job.php`, `/app/Services/JobCreationService.php`, `/app/Http/Controllers/JobController.php`

**Features:**
- Convert approved estimates to trackable jobs
- Work area tracking with variance
- Labor item tracking
- Material item tracking
- Foreman assignment
- Crew size tracking
- Scheduled vs actual dates
- Status workflow (scheduled → in_progress → on_hold → completed → cancelled)
- Financial variance tracking (estimated vs actual)
- QuickBooks integration ready

**Key Tables:**
- `jobs` - Main job records (23 fields)
- `job_work_areas` - Work area breakdown (14 fields)
- `job_labor_items` - Labor tracking (12 fields)
- `job_material_items` - Material tracking (12 fields)

**Views:**
- Job listing with stats, filters, progress bars
- Job detail with financial summary, work areas, sidebar
- Modular partials (stats-cards, status-badge, financial-summary, work-area-card)
- "Convert to Job" button on estimates

**What Works:**
- ✅ Create job from approved estimate
- ✅ Sequential job numbering (JOB-2025-0001)
- ✅ Auto-populate financial data from estimate
- ✅ Work areas with labor/material breakdown
- ✅ Variance calculation (once actuals entered)
- ✅ Status tracking
- ✅ Theme-compliant views

### 7. QuickBooks Integration
**Location:** `/app/Services/QuickBooksService.php`

**Features:**
- OAuth 2.0 authentication
- Customer sync (clients)
- Vendor sync
- Purchase order export
- Ready for job costing sync

**Key Tables:**
- `clients` - Customer records with qbo_customer_id
- `vendors` - Vendor records with qbo_vendor_id

### 8. Client & Property Management
**Location:** `/app/Models/Client.php`, `/app/Models/Property.php`

**Features:**
- Client database with contact info
- Property tracking per client
- Address management
- QuickBooks sync

**Key Tables:**
- `clients` - Client records
- `properties` - Property records

### 9. User Management
**Location:** `/app/Models/User.php`

**Features:**
- User authentication
- Role-based access (admin, foreman, crew)
- Jobs relationship (foreman assignment)

**Key Tables:**
- `users` - User accounts

---

## ⏳ What's In Progress (Phase 2)

### Timesheets System (Starting Tomorrow!)
**Goal:** Track actual labor hours with clock in/out functionality

**What Will Be Built:**
- Timesheet entry (desktop & mobile API)
- Clock in/out tracking
- Work area assignment
- Break time tracking
- Approval workflow (submitted → approved/rejected)
- Auto-update job costs from approved timesheets
- Variance tracking (estimated vs actual hours)
- Foreman dashboard

**Estimated Time:** 2-3 weeks

---

## 🔜 What's Planned (Future Phases)

### Phase 3: Material Expense Tracking (2 weeks)
- Material purchases against jobs
- Photo receipt capture
- Quantity tracking (ordered vs delivered vs used)
- Cost variance
- QBO purchase order integration

### Phase 4: Mobile App (3-4 weeks)
- React Native (iOS/Android)
- Simple login
- My Jobs list
- Clock in/out
- Material photo capture
- Daily summary

### Phase 5: Reports & Analytics (2 weeks)
- Job profitability dashboard
- Foreman performance
- Labor efficiency
- Material waste analysis
- QBO export for payroll/job costing

---

## 📁 Project Structure

```
app/
├── Http/Controllers/
│   ├── BudgetController.php ✅
│   ├── CalculatorController.php ✅
│   ├── ClientController.php ✅
│   ├── EstimateController.php ✅
│   ├── JobController.php ✅ NEW
│   ├── MaterialController.php ✅
│   ├── LaborItemController.php ✅
│   └── QuickBooksController.php ✅
├── Models/
│   ├── Budget.php ✅
│   ├── Client.php ✅
│   ├── CostCode.php ✅
│   ├── Division.php ✅
│   ├── Estimate.php ✅
│   ├── EstimateArea.php ✅
│   ├── EstimateItem.php ✅
│   ├── Job.php ✅ NEW
│   ├── JobWorkArea.php ✅ NEW
│   ├── JobLaborItem.php ✅ NEW
│   ├── JobMaterialItem.php ✅ NEW
│   ├── LaborItem.php ✅
│   ├── Material.php ✅
│   ├── Property.php ✅
│   └── User.php ✅
├── Services/
│   ├── BudgetService.php ✅
│   ├── JobCreationService.php ✅ NEW
│   └── QuickBooksService.php ✅
└── Observers/
    └── EstimateObserver.php ✅

database/migrations/
├── [timestamps]_create_budgets_table.php ✅
├── [timestamps]_create_clients_table.php ✅
├── [timestamps]_create_materials_table.php ✅
├── [timestamps]_create_labor_catalog_table.php ✅
├── [timestamps]_create_estimates_table.php ✅
├── [timestamps]_create_estimate_areas_table.php ✅
├── [timestamps]_create_estimate_items_table.php ✅
├── 2025_11_30_000001_create_jobs_table.php ✅ NEW
├── 2025_11_30_000002_create_job_work_areas_table.php ✅ NEW
├── 2025_11_30_000003_create_job_labor_items_table.php ✅ NEW
└── 2025_11_30_000004_create_job_material_items_table.php ✅ NEW

resources/views/
├── budgets/ ✅
├── calculators/ ✅
├── clients/ ✅
├── estimates/ ✅
│   └── partials/
│       └── create-job-button.blade.php ✅ NEW
├── jobs/ ✅ NEW
│   ├── index.blade.php ✅ NEW
│   ├── show.blade.php ✅ NEW
│   └── partials/ ✅ NEW
│       ├── stats-cards.blade.php ✅ NEW
│       ├── status-badge.blade.php ✅ NEW
│       ├── financial-summary.blade.php ✅ NEW
│       └── work-area-card.blade.php ✅ NEW
├── materials/ ✅
├── labor/ ✅
└── layouts/
    ├── app.blade.php ✅
    └── sidebar.blade.php ✅ (updated with JOBS section)

routes/
└── web.php ✅ (includes job routes)

docs/
├── BUDGET_QUICK_REFERENCE.md ✅
├── BUDGET_SYSTEM_OVERVIEW.md ✅
├── CALCULATOR_ESTIMATE_INTEGRATION_ANALYSIS.md ✅
├── CALCULATOR_IMPORT_FIXES_SUMMARY.md ✅
├── CALCULATOR_IMPORT_PLAN.md ✅
├── CALCULATOR_INTEGRATION_IMPLEMENTATION_GUIDE.md ✅
├── CALCULATOR_OVERHAUL_STATUS.md ✅
├── CUSTOM_PRICING_FEATURE.md ✅
├── DEPLOYMENT_INSTRUCTIONS.md ✅
├── DYNAMIC_LABOR_RATE_INTEGRATION.md ✅
├── ESTIMATE_BUDGET_INTEGRATION_SUMMARY.md ✅
├── ESTIMATE_LINE_ITEM_REACTIVE_CALCULATIONS.md ✅
├── JOBS_TIMESHEETS_MOBILE_IMPLEMENTATION_PLAN.md ✅ NEW
├── JOBS_PHASE_1_COMPLETION_STATUS.md ✅ NEW
├── QUICK_START_PHASE_2.md ✅ NEW
└── CURRENT_STATE_SUMMARY.md ✅ NEW (this file)
```

---

## 🎨 Design System (CFL Charcoal/Brand Theme)

### Color Palette
```css
/* Primary Brand Colors */
brand-50: #f0f9ff     /* Lightest blue-gray */
brand-100: #e0f2fe    /* Light blue-gray */
brand-200: #bae6fd    /* Soft blue */
brand-300: #7dd3fc    /* Medium blue */
brand-400: #38bdf8    /* Bright blue */
brand-500: #0ea5e9    /* Primary blue */
brand-600: #0284c7    /* Deep blue */
brand-700: #0369a1    /* Darker blue */
brand-800: #075985    /* Primary dark blue */
brand-900: #0c4a6e    /* Darkest blue */

/* Grays (Charcoal) */
gray-800: #1f2937     /* Dark charcoal */
gray-700: #374151     /* Medium charcoal */
```

### Component Patterns
```html
<!-- Primary Button -->
<button class="bg-brand-800 hover:bg-brand-700 text-white px-4 py-2 rounded-lg">

<!-- Secondary Button -->
<button class="bg-white/10 border-white/40 text-white px-4 py-2 rounded-lg">

<!-- Card -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

<!-- Gradient Header -->
<div class="bg-gradient-to-r from-gray-800 to-gray-700 p-6">

<!-- Icon Badge -->
<div class="h-12 w-12 bg-brand-100 rounded-xl flex items-center justify-center">
    <svg class="h-6 w-6 text-brand-800">...</svg>
</div>
```

---

## 🔑 Key Relationships

```
Budget
└── has many Divisions
    └── has many Estimates
        └── has one Job
            ├── has many JobWorkAreas
            │   ├── has many JobLaborItems
            │   └── has many JobMaterialItems
            └── belongs to User (foreman)

Estimate
├── belongs to Client
├── belongs to Property
├── belongs to Division
├── belongs to CostCode
├── has many EstimateAreas
│   └── has many EstimateItems
└── has one Job

Job
├── belongs to Estimate
├── belongs to Client
├── belongs to Property
├── belongs to User (foreman)
├── belongs to Division
├── belongs to CostCode
└── has many JobWorkAreas

Client
├── has many Properties
├── has many Estimates
└── has many Jobs

User
└── has many Jobs (as foreman)
```

---

## 🚀 Quick Commands Reference

### Development
```bash
# Start server
php artisan serve

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Clear all caches
php artisan config:clear && php artisan cache:clear && php artisan view:clear

# Test in Tinker
php artisan tinker
```

### Database
```bash
# Check tables
sqlite3 database/database.sqlite ".tables"

# Check migration status
php artisan migrate:status

# Fresh migration (WARNING: deletes data)
php artisan migrate:fresh
```

### Testing
```bash
# Run tests
php artisan test

# Test specific file
php artisan test --filter=JobTest
```

---

## 📊 Database Stats (Approximate)

- **Total Tables:** 25+
- **Total Records:**
  - Materials: 1597
  - Labor Items: 50+
  - Clients: 100+
  - Estimates: 100+
  - Jobs: 1 (as of Nov 30, 2025)

---

## 🐛 Known Issues & Workarounds

### None Currently! 🎉
Phase 1 tested thoroughly and all issues resolved:
- ✅ Foreign key constraints fixed
- ✅ Error handling improved
- ✅ Validation comprehensive
- ✅ Theme compliance verified

---

## 📈 Performance Considerations

### Current Performance
- Small dataset, no optimization needed yet
- All queries under 100ms

### Future Optimization (When Needed)
1. Add indexes when job count > 1000
2. Cache statistics on index pages
3. Lazy load work areas if count > 10
4. Add pagination to listings
5. Implement Redis for session storage

---

## 🔒 Security Status

### Current State
- ✅ CSRF protection enabled
- ✅ SQL injection prevention (Eloquent)
- ✅ XSS protection (Blade escaping)
- ✅ Authentication working

### Todo (Future)
- [ ] Authorization policies (who can view/edit)
- [ ] Permission checks for job creation
- [ ] Audit logging for changes
- [ ] Role-based access (foreman sees only their jobs)
- [ ] API rate limiting

---

## 📞 Support Information

### Technology Stack
- **Framework:** Laravel 11
- **Database:** SQLite (development), MySQL (production ready)
- **Frontend:** Blade templates, Alpine.js, Tailwind CSS
- **Authentication:** Laravel Breeze
- **API:** Laravel Sanctum ready
- **Queue:** Sync (database queue ready)

### Server Requirements
- PHP 8.1+
- Composer
- Node.js 18+
- SQLite or MySQL

### Environment
- Development: Local (macOS)
- Production: Ready for deployment (see DEPLOYMENT_INSTRUCTIONS.md)

---

## 🎯 Next Steps

**Tomorrow (December 1, 2025):**
1. Start Phase 2: Timesheets System
2. Follow the Quick Start guide (QUICK_START_PHASE_2.md)
3. Use same modular patterns from Phase 1
4. Test thoroughly as you build

**This Week:**
- Complete timesheet database and models
- Build timesheet entry forms
- Implement approval workflow
- Auto-update job costs

**Next Week:**
- Timesheet reports
- Mobile API endpoints
- Begin Phase 3 planning

---

**Current Status: Ready to Rock Phase 2! 🚀**

All Phase 1 code is production-ready, well-documented, and follows Laravel best practices. The foundation is solid for building the timesheet system.
