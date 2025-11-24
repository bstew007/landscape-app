# Budget Data Quick Reference

## Common Code Patterns

### Get Active Budget
```php
// Standard (with cache)
$budget = app(\App\Services\BudgetService::class)->active();

// Without cache (admin contexts)
$budget = app(\App\Services\BudgetService::class)->active(false);

// Always check for null!
if (!$budget) {
    // Handle no budget scenario
    $margin = 0.2; // default 20%
    $overheadRate = 0;
}
```

### Get Profit Margin
```php
$budget = app(\App\Services\BudgetService::class)->active();
$margin = (float) ($budget->desired_profit_margin ?? 0.2); // fraction (0-1)
$marginPercent = $margin * 100; // convert to percentage
```

### Get Overhead Rate
```php
$budget = app(\App\Services\BudgetService::class)->active();
$overheadRate = 0.0;

if ($budget) {
    // Method 1: From inputs (set by controller after calculation)
    $overheadRate = (float) data_get($budget->inputs, 'oh_recovery.labor_hour.markup_per_hour', 0);
    
    // Method 2: From outputs (calculated by BudgetService)
    if ($overheadRate == 0 && $budget->outputs) {
        $overheadRate = (float) data_get($budget->outputs, 'labor.ohr', 0);
    }
}
```

### Calculate Labor Unit Price from Catalog
```php
$budget = app(\App\Services\BudgetService::class)->active();
$margin = (float) ($budget->desired_profit_margin ?? 0.2);
$overheadRate = (float) data_get($budget->outputs, 'labor.ohr', 0);

// From labor catalog item
$wage = (float) $laborItem->average_wage;
$otFactor = (float) ($laborItem->overtime_factor ?? 1);
$burdenPct = (float) ($laborItem->labor_burden_percentage ?? 0);
$unbillPct = (float) ($laborItem->unbillable_percentage ?? 0);

// Calculate
$effectiveWage = $wage * $otFactor;
$unitCost = $effectiveWage * (1 + ($burdenPct / 100));
$billableFraction = max(0.01, 1 - ($unbillPct / 100));
$unitCostPerBillableHour = $unitCost / $billableFraction;
$breakeven = $unitCostPerBillableHour + $overheadRate;
$unitPrice = $breakeven / (1 - $margin);
```

### Calculate Material Unit Price from Catalog
```php
$budget = app(\App\Services\BudgetService::class)->active();
$margin = (float) ($budget->desired_profit_margin ?? 0.2);

// From material catalog item
$unitCost = (float) $material->unit_cost;
$taxRate = (float) ($material->tax_rate ?? 0);

// Calculate
$breakeven = $unitCost * (1 + $taxRate);
$unitPrice = $breakeven / (1 - $margin);
```

### Get Budget Totals
```php
$budget = app(\App\Services\BudgetService::class)->active();

if ($budget && $budget->outputs) {
    $totalFieldHours = (float) data_get($budget->outputs, 'labor.total_field_hours', 0);
    $totalOverhead = (float) data_get($budget->outputs, 'overhead.total', 0);
    $blc = (float) data_get($budget->outputs, 'labor.blc', 0); // Burdened Labor Cost
    $dlc = (float) data_get($budget->outputs, 'labor.dlc', 0); // Direct Labor Cost
}
```

### Get Sales Forecast
```php
$budget = app(\App\Services\BudgetService::class)->active();
$salesRows = data_get($budget->inputs, 'sales.rows', []);
$totalForecast = array_sum(array_column($salesRows, 'forecast'));
```

### Get Equipment Costs
```php
$budget = app(\App\Services\BudgetService::class)->active();

// Field equipment
$equipmentRows = data_get($budget->inputs, 'equipment.rows', []);
$equipmentTotal = 0;
foreach ($equipmentRows as $row) {
    $qty = (float) ($row['qty'] ?? 1);
    $costPerYear = (float) ($row['cost_per_year'] ?? 0);
    $equipmentTotal += ($qty * $costPerYear);
}

// Add general costs
$general = data_get($budget->inputs, 'equipment.general', []);
$equipmentTotal += (float) ($general['fuel'] ?? 0);
$equipmentTotal += (float) ($general['repairs'] ?? 0);
$equipmentTotal += (float) ($general['insurance_misc'] ?? 0);
$equipmentTotal += (float) data_get($budget->inputs, 'equipment.rentals', 0);
```

### Calculate Recommended Rates
```php
$budgetService = app(\App\Services\BudgetService::class);
$budget = $budgetService->active();

if ($budget) {
    $rates = $budgetService->recommendedRates($budget);
    // $rates['break_even_rate'] - minimum to cover costs
    // $rates['charge_out_rate'] - target rate with profit
    // $rates['profit_per_hour'] - profit per hour
    // $rates['gross_margin'] - as fraction (0-1)
}
```

## Blade Template Patterns

### Display Budget Name
```blade
@php
    $budget = app(\App\Services\BudgetService::class)->active();
@endphp

<div>Active Budget: {{ $budget->name ?? 'None' }}</div>
```

### Display Overhead Rate
```blade
@php
    $budget = app(\App\Services\BudgetService::class)->active();
    $overheadRate = $budget ? (float) data_get($budget->outputs, 'labor.ohr', 0) : 0;
@endphp

<div>Overhead Rate: ${{ number_format($overheadRate, 2) }}/hr</div>
```

### Display Profit Margin
```blade
@php
    $budget = app(\App\Services\BudgetService::class)->active();
    $margin = $budget ? $budget->desired_profit_margin : 0.2;
@endphp

<div>Target Margin: {{ number_format($margin * 100, 1) }}%</div>
```

### Check if Budget Exists
```blade
@php
    $budget = app(\App\Services\BudgetService::class)->active();
@endphp

@if($budget)
    <!-- Show budget-dependent features -->
@else
    <div class="alert alert-warning">
        No active budget found. Please <a href="{{ route('admin.budgets.create') }}">create one</a>.
    </div>
@endif
```

## JavaScript/Alpine Access Patterns

### Pass Budget Data to Alpine
```blade
<div x-data="{
    margin: {{ $budget->desired_profit_margin ?? 0.2 }},
    overheadRate: {{ data_get($budget->outputs, 'labor.ohr', 0) }},
    calculatePrice(cost) {
        let breakeven = cost + this.overheadRate;
        return breakeven / (1 - this.margin);
    }
}">
    <!-- Your component -->
</div>
```

## API Endpoint Pattern

### Catalog Defaults Endpoint
```
GET /api/catalog/{type}/{id}

Response (Labor):
{
    "unit_cost": 45.50,
    "unit_price": 75.25,
    "overhead_rate": 15.25,
    "name": "Crew Leader",
    "unit": "HR"
}

Response (Material):
{
    "unit_cost": 12.50,
    "unit_price": 18.75,
    "tax_rate": 0.0825,
    "name": "2x4 Lumber",
    "unit": "EA"
}
```

## Budget Input Structure Quick Reference

```
inputs: {
    sales: {
        rows: [
            { account_id, division, previous, forecast, comments }
        ]
    },
    labor: {
        burden_pct,
        ot_multiplier,
        industry_avg_ratio,
        hourly: {
            rows: [
                { type, staff, hrs, ot_hrs, avg_wage, bonus }
            ]
        },
        salary: {
            rows: [
                { type, staff, ann_hrs, ann_salary, bonus }
            ]
        }
    },
    equipment: {
        rows: [
            { 
                type, qty, class, description, cost_per_year,
                owned: { replacement_value, fees, years, salvage_value, months_per_year, division_months, interest_rate_pct },
                leased: { monthly_payment, payments_per_year, months_per_year, division_months },
                group: { items: [ { name, qty, purchase_price, resale_value, years } ] }
            }
        ],
        general: { fuel, repairs, insurance_misc },
        rentals,
        industry_avg_ratio
    },
    materials: {
        rows: [
            { account_id, expense, previous, current, comments }
        ],
        tax_pct,
        industry_avg_ratio
    },
    subcontracting: {
        rows: [
            { account_id, expense, previous, current, comments }
        ]
    },
    overhead: {
        labor_burden_pct,
        industry_avg_ratio,
        expenses: {
            rows: [
                { account_id, expense, previous, current, comments }
            ]
        },
        wages: {
            rows: [
                { title, previous, forecast, comments }
            ]
        },
        equipment: {
            rows: [ /* same as equipment.rows */ ],
            general: { fuel, repairs, insurance_misc },
            rentals
        }
    },
    oh_recovery: {
        method: 'labor_hour' | 'revenue' | 'dual',
        labor_hour: {
            activated,
            markup_per_hour
        },
        revenue: {
            activated,
            markup_fraction
        },
        dual: {
            activated,
            labor_share_pct,
            labor_markup_per_hour,
            revenue_markup_fraction
        }
    }
}
```

## Common Gotchas

1. **Margin is a fraction (0-1), not percentage** - Always multiply by 100 for display
2. **Check for null budget** - New installations may not have a budget
3. **Use data_get() with defaults** - Safely access nested JSON with fallback values
4. **Cache may be stale** - Use `active(false)` in admin contexts
5. **Overhead rate has two locations** - Check both `inputs.oh_recovery.labor_hour.markup_per_hour` and `outputs.labor.ohr`
6. **Equipment qty can be empty string** - Default to 1 if empty: `$qty = ($row['qty'] === '' || $row['qty'] === null) ? 1 : (float) ($row['qty'] ?? 0);`
7. **One active budget only** - Setting one active automatically deactivates others
8. **Updates merge data** - Controller merges with existing inputs to preserve unchanged data
9. **Always round financial values** - Use `round($value, 2)` for money
10. **Division by zero** - Always check denominator before dividing (e.g., `max(0.0001, $value)`)

## Validation Rules

```php
'desired_profit_margin' => 'required|numeric|max:0.999' // Max 99.9%
'inputs.*.*.previous' => 'nullable|numeric|min:0'
'inputs.*.*.current' => 'nullable|numeric|min:0'
'inputs.*.*.forecast' => 'nullable|numeric|min:0'
'inputs.labor.burden_pct' => 'nullable|numeric|min:0|max:100'
'inputs.equipment.*.owned.interest_rate_pct' => 'nullable|numeric|min:0|max:100'
```

## Testing Budget Calculations

```php
// Create test budget
$budget = CompanyBudget::create([
    'name' => 'Test Budget 2025',
    'year' => 2025,
    'is_active' => true,
    'desired_profit_margin' => 0.25,
    'inputs' => [
        'labor' => [
            'hourly' => [
                'rows' => [
                    ['staff' => 10, 'hrs' => 2000, 'avg_wage' => 25]
                ]
            ]
        ],
        'overhead' => [
            'expenses' => [
                'rows' => [
                    ['current' => 50000]
                ]
            ]
        ]
    ]
]);

// Compute outputs
$budgetService = app(\App\Services\BudgetService::class);
$outputs = $budgetService->computeOutputs($budget->inputs);
$budget->outputs = $outputs;
$budget->save();

// Verify
// Total hours = 10 staff × 2000 hrs = 20,000
// Overhead = $50,000
// Overhead rate = 50000 / 20000 = $2.50/hr
assert($outputs['labor']['ohr'] == 2.50);
```
