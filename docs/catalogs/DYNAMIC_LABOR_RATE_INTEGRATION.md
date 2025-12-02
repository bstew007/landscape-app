# Dynamic Labor Rate Integration

## Overview
All calculator labor rates are now dynamically wired to the company budget system through the labor catalog breakeven rates. This creates a fully integrated pricing flow where budget changes automatically cascade to all calculators.

## Pricing Flow Architecture

```
Company Budget (inputs/outputs)
    ↓
Labor Catalog (breakeven rates calculated)
    ↓
Calculator Default Labor Rate (average of all active breakeven rates)
    ↓
Individual Calculations (can override per-calculation)
```

## Implementation Details

### 1. BudgetService Methods

**File:** `app/Services/BudgetService.php`

Added two methods:

#### `getAverageLaborRate(): float`
- Queries all active labor items from the labor catalog
- Calculates average of `breakeven` column values
- Falls back to budget BLC if no labor items exist
- Final fallback to $50.00 if no budget exists

#### `getLaborRateForCalculators(): float`
- Alias method for semantic clarity
- Simply calls `getAverageLaborRate()`

### 2. Controller Updates

**All 10 calculator controllers updated:**
1. PlantingCalculatorController
2. WeedingCalculatorController
3. MulchingCalculatorController
4. PineNeedleCalculatorController
5. PruningCalculatorController
6. TurfMowingCalculatorController
7. SynTurfCalculatorController
8. PaverPatioCalculatorController
9. RetainingWallCalculatorController
10. FenceCalculatorController

**Changes per controller:**

#### Import Statement
```php
use App\Services\BudgetService;
```

#### showForm() Method
```php
public function showForm(Request $request)
{
    // ... existing code ...
    
    $budgetService = app(BudgetService::class);
    $defaultLaborRate = $budgetService->getLaborRateForCalculators();
    
    return view('calculators.xxx.form', [
        // ... existing parameters ...
        'defaultLaborRate' => $defaultLaborRate,
    ]);
}
```

#### edit() Method
```php
public function edit(Calculation $calculation)
{
    // ... existing code ...
    
    $budgetService = app(BudgetService::class);
    $defaultLaborRate = $budgetService->getLaborRateForCalculators();
    
    return view('calculators.xxx.form', [
        // ... existing parameters ...
        'defaultLaborRate' => $defaultLaborRate,
    ]);
}
```

### 3. View Template Update

**File:** `resources/views/calculators/partials/overhead_inputs.blade.php`

**Changed line 13 from:**
```blade
value="{{ old('labor_rate', $formData['labor_rate'] ?? 65) }}"
```

**To:**
```blade
value="{{ old('labor_rate', $formData['labor_rate'] ?? $defaultLaborRate ?? 65) }}"
```

This provides:
1. First priority: Old input (validation errors)
2. Second priority: Saved calculation data (edit mode)
3. Third priority: Dynamic budget-based rate
4. Final fallback: Hardcoded $65

## Data Flow Example

### Creating New Calculation
1. User clicks "New Calculation" → showForm() loads
2. BudgetService queries: `SELECT AVG(breakeven) FROM labor_items WHERE is_active = true`
3. Result: $52.75 (average of all active labor breakeven rates)
4. Form displays with labor_rate input showing $52.75
5. User can accept default or override with custom value
6. Calculation saves with chosen rate (locked to that calculation)

### Editing Existing Calculation
1. User clicks "Edit" → edit() loads
2. BudgetService queries current average (may have changed since original calc)
3. Form displays SAVED rate from `$formData['labor_rate']` (e.g., $50.00)
4. If user clears the field, it shows NEW default (e.g., $52.75)
5. Historical calculations preserve original rates

### Budget Change Impact
1. Admin updates company budget inputs
2. Budget recalculates → Labor catalog breakeven values updated
3. Next time ANY calculator form loads → New average is fetched
4. All new calculations use updated rate automatically
5. Existing calculations unchanged (correct behavior)

## Labor Catalog Calculation

The labor catalog breakeven rates are calculated by the budget system using this formula:

```
Breakeven Rate = Direct Labor Cost (DLC) + Overhead Recovery Rate (OHR)

Where:
- DLC = Wage × (1 + payroll_taxes + benefits + workers_comp) + (Wage × PTO per hour)
- OHR = Total Annual Overhead ÷ Total Annual Field Labor Hours
```

Each labor item in the catalog has its own breakeven rate based on its specific wage and burden percentages.

## Benefits

### ✅ Fully Integrated Pricing
- Budget drives everything
- One source of truth for labor costs
- Automatic updates cascade through system

### ✅ User Control Preserved
- Defaults are smart and automatic
- Users can override per calculation if needed
- Historical calculations maintain original rates

### ✅ Real-time Updates
- No caching of labor rates (except budget itself - 5 min)
- Each form load gets current average
- Budget changes immediately available

### ✅ Fallback Protection
- Graceful degradation if no labor items exist
- Falls back to budget BLC
- Final fallback to reasonable default ($50)

## Testing the Integration

### Test 1: Verify Dynamic Rate Display
1. Go to Labor Catalog
2. Note the breakeven rates (e.g., Foreman: $55, Laborer: $48, Technician: $52)
3. Calculate average: ($55 + $48 + $52) ÷ 3 = $51.67
4. Open any calculator form
5. Verify labor rate field shows $51.67

### Test 2: Verify Budget Changes Cascade
1. Open Budget Manager
2. Increase total overhead by 20%
3. Recalculate budget
4. Check Labor Catalog - breakeven rates should increase
5. Open calculator form - default rate should reflect new average

### Test 3: Verify Historical Preservation
1. Create calculation with current rate (e.g., $50)
2. Change budget to increase rates
3. Edit the old calculation
4. Verify it still shows $50 in the input (saved rate)
5. Clear the field - should show new default

### Test 4: Verify Fallback Behavior
1. Deactivate all labor items in catalog
2. Open calculator form
3. Should fall back to budget BLC
4. If no active budget exists, should show $50.00

## Files Modified

### Controllers (10 files)
- app/Http/Controllers/PlantingCalculatorController.php
- app/Http/Controllers/WeedingCalculatorController.php
- app/Http/Controllers/MulchingCalculatorController.php
- app/Http/Controllers/PineNeedleCalculatorController.php
- app/Http/Controllers/PruningCalculatorController.php
- app/Http/Controllers/TurfMowingCalculatorController.php
- app/Http/Controllers/SynTurfCalculatorController.php
- app/Http/Controllers/PaverPatioCalculatorController.php
- app/Http/Controllers/RetainingWallCalculatorController.php
- app/Http/Controllers/FenceCalculatorController.php

### Services (1 file)
- app/Services/BudgetService.php

### Views (1 file)
- resources/views/calculators/partials/overhead_inputs.blade.php

## Future Enhancements

### Potential Improvements
1. **Rate History Tracking**: Log when rates change for audit purposes
2. **Rate Variance Alerts**: Notify if override significantly differs from recommended
3. **Department-Specific Rates**: Allow different rates for different labor types
4. **Seasonal Adjustments**: Support seasonal labor rate variations
5. **Customer-Specific Rates**: Override defaults per client if needed

### Integration Points
- Could tie into QuickBooks labor cost imports
- Could display rate trend graphs in calculator forms
- Could add "Use Recommended Rate" button to quickly reset overrides

## Migration Notes

### No Database Changes Required
- Uses existing `labor_items.breakeven` column
- Uses existing `company_budgets` table
- No migrations needed

### Backwards Compatibility
- Existing calculations unaffected
- Forms work with or without budget/labor catalog
- Graceful fallbacks ensure no breaking changes

### Deployment Checklist
- [x] Update BudgetService with new methods
- [x] Update all 10 calculator controllers
- [x] Update shared overhead_inputs.blade.php partial
- [ ] Clear any controller/view caches: `php artisan cache:clear`
- [ ] Verify labor catalog has active items with breakeven values
- [ ] Test one calculator form to confirm rate displays correctly
