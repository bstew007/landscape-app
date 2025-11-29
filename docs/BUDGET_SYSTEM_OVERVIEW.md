# Budget System Overview

## Purpose
The Company Budget system is **critical** for pricing labor and materials and accounting for overhead. It provides the foundation for all estimate calculations, catalog item pricing, and profit margin targeting.

## Key Components

### 1. Data Model
**Model:** `App\Models\CompanyBudget`
**Table:** `company_budgets`

**Key Fields:**
- `name` - Budget identifier
- `year` - Fiscal year
- `is_active` - Only one budget can be active at a time
- `effective_from` - When this budget takes effect
- `desired_profit_margin` - Target profit margin (0-0.999, e.g., 0.2 = 20%)
- `inputs` - JSON structure with all budget inputs
- `outputs` - JSON structure with calculated values

### 2. Service Layer
**Service:** `App\Services\BudgetService`

**Key Methods:**
- `active($useCache = true)` - Retrieves the currently active budget
- `computeOutputs($inputs)` - Calculates overhead rates and labor costs from inputs
- `recommendedRates($budget)` - Calculates break-even and charge-out rates

### 3. Controller
**Controller:** `App\Http\Controllers\Admin\CompanyBudgetController`

**Routes:**
- `GET /admin/budgets` - List all budgets
- `GET /admin/budgets/create` - Create new budget
- `GET /admin/budgets/{budget}/edit` - Edit existing budget
- `POST /admin/budgets` - Store new budget
- `PUT /admin/budgets/{budget}` - Update budget

### 4. Views
**Location:** `resources/views/admin/budgets/`

**Files:**
- `index.blade.php` - Budget list
- `edit.blade.php` - Main budget editor (tabbed interface)
- `partials/_sales.blade.php` - Sales revenue forecasting
- `partials/_field_labor.blade.php` - Field labor costs and hours
- `partials/_equipment.blade.php` - Equipment costs (owned, leased, groups)
- `partials/_materials.blade.php` - Material costs and tax rates
- `partials/_subcontracting.blade.php` - Subcontractor expenses
- `partials/_overhead.blade.php` - Overhead expenses, wages, equipment
- `partials/_profit_loss.blade.php` - P&L statement view
- `partials/_oh_recovery.blade.php` - Overhead recovery method selection
- `partials/_analysis.blade.php` - Budget analysis and industry comparisons

## Budget Input Structure

The `inputs` JSON field contains:

### Sales Budget
```
sales.rows[] - Revenue forecasts by division/account
```

### Field Labor
```
labor.burden_pct - Labor burden percentage
labor.ot_multiplier - Overtime multiplier (e.g., 1.5)
labor.industry_avg_ratio - Industry average labor ratio
labor.hourly.rows[] - Hourly workers (type, staff count, hours, OT hours, wage, bonus)
labor.salary.rows[] - Salaried workers (type, staff count, annual hours, salary, bonus)
```

### Equipment
```
equipment.rows[] - Equipment items (type, qty, class, cost calculations)
  - Classes: Custom, Owned, Leased, Group
  - Owned: replacement value, fees, useful life, salvage, interest
  - Leased: monthly payment, payment frequency
  - Group: collection of small tools with depreciation
equipment.general.fuel - Fuel costs
equipment.general.repairs - Repair costs
equipment.general.insurance_misc - Insurance and misc costs
equipment.rentals - Equipment rental costs
equipment.industry_avg_ratio - Industry average equipment ratio
```

### Materials
```
materials.rows[] - Material expense accounts
materials.tax_pct - Sales tax percentage
materials.industry_avg_ratio - Industry average materials ratio
```

### Subcontracting
```
subcontracting.rows[] - Subcontractor expense accounts
```

### Overhead
```
overhead.expenses.rows[] - General overhead expenses
overhead.wages.rows[] - Overhead staff salaries
overhead.equipment.rows[] - Overhead equipment (same structure as field equipment)
overhead.equipment.general - Overhead equipment general costs
overhead.equipment.rentals - Overhead equipment rentals
overhead.labor_burden_pct - Labor burden applied to overhead
overhead.industry_avg_ratio - Industry average overhead ratio
```

### Overhead Recovery
```
oh_recovery.method - 'labor_hour', 'revenue', or 'dual'
oh_recovery.labor_hour.markup_per_hour - Overhead rate per labor hour
oh_recovery.labor_hour.activated - Whether this method is active
oh_recovery.revenue.markup_fraction - Overhead as % of revenue
oh_recovery.revenue.activated - Whether this method is active
oh_recovery.dual - Split recovery between labor and revenue
```

## Budget Output Structure

The `outputs` JSON field contains calculated values:

```json
{
  "labor": {
    "dlc": 45.50,              // Direct Labor Cost per hour
    "ohr": 15.25,              // Overhead Rate per hour
    "blc": 60.75,              // Burdened Labor Cost (dlc + ohr)
    "plh": 8500,               // Productive Labor Hours (annual)
    "plh_per_person": 1700,    // Productive hours per worker
    "total_field_hours": 10000 // Total field labor hours budgeted
  },
  "overhead": {
    "total": 152500.00         // Total annual overhead
  }
}
```

## How Budget Data is Used

### 1. Catalog Item Pricing
**Location:** `routes/web.php` - `/api/catalog/{type}/{id}` endpoint

**Labor Items:**
- Fetches catalog item base wage and burden
- Retrieves overhead rate from active budget
- Calculates break-even cost per billable hour
- Applies desired profit margin for default unit price

**Material Items:**
- Fetches catalog item unit cost
- Applies tax rate from item or budget
- Applies desired profit margin for default unit price

### 2. Estimate Line Items
**Services:**
- `App\Services\EstimateItemService` - Uses budget for item calculations
- `App\Services\CalculationImportService` - Uses budget when importing calculators

**Controllers:**
- `App\Http\Controllers\EstimateController` - Passes budget margin to views
- `App\Http\Controllers\LaborController` - Uses budget for labor catalog defaults

### 3. Site Visit Calculators
All calculators reference the active budget through `BudgetService::active()` to:
- Apply overhead rates to labor
- Calculate break-even pricing
- Apply profit margins

## Critical Calculations

### Overhead Recovery Rate (per labor hour)
```php
Total Overhead = 
  SUM(overhead.expenses.rows[].current) +
  SUM(overhead.wages.rows[].forecast) +
  SUM(overhead.equipment.rows[].qty * cost_per_year) +
  overhead.equipment.general.fuel +
  overhead.equipment.general.repairs +
  overhead.equipment.general.insurance_misc +
  overhead.equipment.rentals

Total Field Hours = 
  SUM(labor.hourly.rows[].staff * (hrs + ot_hrs)) +
  SUM(labor.salary.rows[].ann_hrs)

Overhead Rate = Total Overhead / Total Field Hours
```

### Labor Pricing
```php
// From catalog item:
base_wage = item.average_wage
ot_factor = item.overtime_factor (default 1.0)
burden_pct = item.labor_burden_percentage
unbillable_pct = item.unbillable_percentage

// Calculate burdened cost:
effective_wage = base_wage * ot_factor
unit_cost_per_actual_hour = effective_wage * (1 + burden_pct/100)
billable_fraction = 1 - (unbillable_pct/100)
unit_cost_per_billable_hour = unit_cost_per_actual_hour / billable_fraction

// Add overhead from budget:
overhead_rate = budget.outputs.labor.ohr (or inputs.oh_recovery.labor_hour.markup_per_hour)
break_even = unit_cost_per_billable_hour + overhead_rate

// Apply margin:
margin = budget.desired_profit_margin
unit_price = break_even / (1 - margin)
```

### Material Pricing
```php
unit_cost = material.unit_cost
tax_rate = material.tax_rate
break_even = unit_cost * (1 + tax_rate)
margin = budget.desired_profit_margin
unit_price = break_even / (1 - margin)
```

## Data Access Patterns

### Getting Active Budget
```php
// With caching (5 minutes):
$budget = app(\App\Services\BudgetService::class)->active();

// Without caching (for admin edits):
$budget = app(\App\Services\BudgetService::class)->active(false);

// Direct query:
$budget = CompanyBudget::where('is_active', true)
    ->orderByDesc('effective_from')
    ->first();
```

### Reading Budget Data
```php
// Get desired profit margin:
$margin = $budget->desired_profit_margin; // e.g., 0.20 for 20%

// Get overhead rate:
$overheadRate = data_get($budget->inputs, 'oh_recovery.labor_hour.markup_per_hour');
// or from outputs:
$overheadRate = data_get($budget->outputs, 'labor.ohr');

// Get sales forecast:
$salesRows = data_get($budget->inputs, 'sales.rows', []);
$totalForecast = array_sum(array_column($salesRows, 'forecast'));

// Get field labor hours:
$totalHours = data_get($budget->outputs, 'labor.total_field_hours');

// Get overhead total:
$totalOverhead = data_get($budget->outputs, 'overhead.total');
```

### Updating Budget
```php
// Controller automatically:
// 1. Validates inputs
// 2. Merges with existing data (preserves unchanged fields)
// 3. Recalculates outputs via BudgetService
// 4. Saves both inputs and outputs
// 5. Clears cache
// 6. Ensures only one active budget

// When is_active is set, all other budgets are marked inactive
```

## Industry Benchmarks

The budget system includes industry average ratios for comparison:

- **Labor Ratio:** 26.6% (of revenue)
- **Equipment Ratio:** 13.7% (of revenue)
- **Materials Ratio:** 22.3% (of revenue)
- **Overhead Ratio:** 24.8% (of revenue)

These are displayed in the Analysis section and help users understand if their budget is aligned with industry standards.

## Cache Management

- **Cache Key:** `active_company_budget`
- **TTL:** 300 seconds (5 minutes)
- **Clear Trigger:** Whenever a budget is created or updated
- **Service:** Laravel Cache facade

## User Interface Features

### Budget Editor Navigation
- **Tabbed Interface:** 10 sections accessible via sidebar
- **Live Calculations:** Real-time updates as you type
- **Status Indicators:** Shows active/inactive, last updated
- **Industry Comparison:** Visual indicators when ratios exceed benchmarks
- **Equipment Calculators:** Owned/Leased/Group equipment with detailed breakdown modals
- **Move Between Sections:** Equipment can be moved between Field and Overhead

### Visual Feedback
- **Pills:** Color-coded ratio indicators (green = good, yellow = caution, red = high)
- **Live Totals:** Section navigation shows running totals
- **Profit/Loss:** Real-time P&L calculation
- **Validation:** Inline validation for all numeric inputs

## Best Practices

1. **Always use BudgetService** to retrieve active budget (don't query directly)
2. **Use cache = false** in admin contexts where immediate updates matter
3. **Check for null** - budget may not exist in new installations
4. **Provide defaults** - Always have fallback values (e.g., 0.2 for margin)
5. **Clear cache** after budget updates (controller does this automatically)
6. **Only one active** - Setting a budget active deactivates all others
7. **Preserve data** - Updates merge with existing inputs to avoid data loss

## Quick Reference: Getting Budget Data

```php
// Get active budget with defaults
$budget = app(\App\Services\BudgetService::class)->active();
$margin = (float) ($budget->desired_profit_margin ?? 0.2);
$overheadRate = (float) data_get($budget->inputs, 'oh_recovery.labor_hour.markup_per_hour', 0);

// Alternative: from outputs
if (!$overheadRate && $budget->outputs) {
    $overheadRate = (float) data_get($budget->outputs, 'labor.ohr', 0);
}

// Get total field hours
$fieldHours = (float) data_get($budget->outputs, 'labor.total_field_hours', 0);

// Get overhead total
$overhead = (float) data_get($budget->outputs, 'overhead.total', 0);

// Calculate break-even labor rate
$blc = (float) data_get($budget->outputs, 'labor.blc', 0);
```

## Migration & Setup

New installations should:
1. Create an initial budget via `/admin/budgets/create`
2. Set it as active
3. Define at minimum:
   - Desired profit margin
   - Field labor hours and wages
   - Overhead expenses
   - Equipment if applicable

The system will work with minimal data but accuracy improves with complete inputs.

## Related Documentation

- `ESTIMATE_BUDGET_INTEGRATION_SUMMARY.md` - How estimates use budget data
- `ESTIMATE_LINE_ITEM_REACTIVE_CALCULATIONS.md` - Line item calculation details
- `labor_catolog.md` - Labor catalog and budget integration
- `docs/labor_catalog.md` - Detailed labor catalog documentation
