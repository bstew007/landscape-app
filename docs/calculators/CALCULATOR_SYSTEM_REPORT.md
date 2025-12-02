# Calculator System - Comprehensive Report

**Last Updated:** December 2, 2025  
**System Status:** Cost-Only Architecture (No Pricing)

---

## Executive Summary

The landscape application features **11 specialized calculators** for estimating labor costs and material quantities for common landscape tasks. As of December 2025, the system has been fundamentally restructured to operate as **cost-only calculators** - they calculate internal labor costs and material quantities but do not generate client-facing pricing or profit margins.

### Key Changes (December 2025)
- ✅ All calculators converted to **cost-only** (no markup/profit calculations)
- ✅ Removed all **override pricing inputs** from forms and controllers
- ✅ Removed **custom material pricing sections** (simplified to catalog-only where applicable)
- ✅ Changed terminology: "Final Price" → "Total Cost" throughout UI
- ✅ All default material pricing removed (calculators show quantities only)

---

## Calculator Inventory

### 1. **Paver Patio Calculator**
- **Controller:** `PaverPatioCalculatorController.php`
- **Purpose:** Calculate labor and material quantities for paver patio installations
- **Material Handling:** Catalog picker integration (user selects materials from company catalog)
- **Labor Tasks:**
  - Site preparation
  - Excavation
  - Base installation
  - Paver laying
  - Edge restraint installation
  - Sand compaction
- **Status:** ✅ Fully functional, cost-only, catalog integration

### 2. **Retaining Wall Calculator**
- **Controller:** `RetainingWallCalculatorController.php`
- **Purpose:** Calculate labor costs for retaining wall construction
- **Special Features:**
  - Standard vs Allan Block system modes
  - Geogrid calculation for walls 4ft+
  - Capstone inclusion option
  - Curved/stepped wall support (Allan Block)
- **Labor Tasks:**
  - Excavation
  - Base installation
  - Block laying
  - Capstone installation
  - Backfill/drainage
- **Material Handling:** Custom materials only (no default pricing)
- **Status:** ✅ Recently rewritten - clean 4-section form, cost-only
- **Known Issues:** Form was broken with override inputs, now completely rebuilt

### 3. **Fence Calculator**  
- **Controller:** `FenceCalculatorController.php`
- **Purpose:** Wood and vinyl fence installation cost estimation
- **Fence Types:** Wood, Vinyl
- **Labor Calculation:** Uses `FenceLaborEstimationService`
- **Labor Tasks:**
  - Post hole digging (hand or auger)
  - Post installation
  - Rail installation
  - Picket/panel installation
  - Gate installation
- **Material Handling:** Custom materials input (no defaults)
- **Status:** ✅ Functional, cost-only, override inputs removed

### 4. **Mulching Calculator**
- **Controller:** `MulchingCalculatorController.php`
- **Purpose:** Calculate mulch bed installation/refresh costs
- **Production Rates:** Database-driven (`production_rates` table)
- **Labor Tasks:**
  - Bed preparation
  - Mulch spreading
  - Edge installation
- **Material Handling:** Custom materials only (removed $35/cy default)
- **Status:** ✅ Functional, cost-only

### 5. **Pine Needle Calculator**
- **Controller:** `PineNeedleCalculatorController.php`
- **Purpose:** Pine straw/needle installation for beds
- **Production Rates:** Database-driven
- **Labor Tasks:**
  - Pine needle spreading
- **Material Handling:** Custom materials only (removed $7/unit default)
- **Status:** ✅ Functional, cost-only

### 6. **Planting Calculator**
- **Controller:** `PlantingCalculatorController.php`
- **Purpose:** Plant installation with material catalog integration
- **Material Handling:** **Catalog integration** - users select plants from material catalog with costs
- **Labor Calculation:** Variable production rates based on plant size
- **Production Rates:**
  - Small plants (1-gallon): 0.1 hrs/ea
  - Medium plants (3-gallon): 0.25 hrs/ea
  - Large plants (7-gallon+): 0.5 hrs/ea
  - Trees: 1.0+ hrs/ea
- **Status:** ✅ Fully functional, catalog integration

### 7. **SynTurf (Synthetic Turf) Calculator**
- **Controller:** `SynTurfCalculatorController.php`
- **Service:** `SynTurfMaterialService.php`
- **Purpose:** Synthetic turf installation cost estimation
- **Turf Grades:** Good, Better, Best (quality tiers)
- **Labor Tasks:**
  - Excavation
  - Base preparation (ABC, rock dust)
  - Turf installation
  - Infill application
  - Edge restraint
- **Material Quantities Calculated:**
  - Turf (sqft)
  - Infill bags
  - Edging boards
  - Weed barrier rolls
  - ABC base (cubic yards)
  - Rock dust (cubic yards)
- **Equipment:** Optional tamper rental tracking
- **Status:** ✅ Functional, cost-only, all override inputs removed
- **Config:** `config/syn_turf.php` - all default costs set to 0

### 8. **Pruning Calculator**
- **Controller:** `PruningCalculatorController.php`
- **Purpose:** Tree and shrub pruning cost estimation
- **Production Rates:** Database-driven, varies by plant type/size
- **Labor Tasks:**
  - Small shrub pruning
  - Large shrub pruning
  - Small tree pruning
  - Large tree pruning
  - Cleanup and debris removal
- **Status:** ✅ Functional, cost-only

### 9. **Weeding Calculator**
- **Controller:** `WeedingCalculatorController.php`
- **Purpose:** Weed bed maintenance cost estimation
- **Production Rates:** Database-driven (sqft-based)
- **Labor Tasks:**
  - Weed pulling/removal
  - Bed cleanup
- **Status:** ✅ Functional, cost-only

### 10. **Turf Mowing Calculator**
- **Controller:** `TurfMowingCalculatorController.php`
- **Purpose:** Lawn mowing service cost estimation
- **Production Rates:** Database-driven (sqft-based)
- **Labor Tasks:**
  - Mowing
  - Trimming
  - Edging
  - Cleanup
- **Status:** ✅ Functional, cost-only

### 11. **Estimate Calculator** (Meta-Calculator)
- **Controller:** `EstimateCalculatorController.php`
- **Purpose:** Aggregate calculator for combining multiple services into single estimate
- **Status:** ⚠️ Meta-calculator, different architecture

---

## System Architecture

### Core Philosophy: Cost-Only Calculators

**What This Means:**
- Calculators estimate **internal labor costs** (crew hours × hourly rate)
- Calculators track **material quantities** and costs (at cost, no markup)
- **Total Cost** = Labor Cost + Material Cost
- NO profit margins, NO markups, NO client-facing pricing
- Pricing happens at the **Estimate level** when imported

### Data Flow

```
User Input (Form)
    ↓
Controller Validation
    ↓
Production Rates (Database) → Labor Hour Calculation
    ↓
Material Quantities → Material Cost Calculation
    ↓
LaborCostCalculatorService → Overhead/Totals
    ↓
Results Display → Total Cost
    ↓
Optional: Import to Estimate (with pricing applied there)
```

### Key Components

#### 1. Controllers (`app/Http/Controllers/*CalculatorController.php`)

**Standard Structure:**
```php
public function calculate(Request $request) {
    // 1. Validate inputs
    $validated = $request->validate([...]);
    
    // 2. Calculate labor hours from production rates
    $dbRates = ProductionRate::where('calculator', 'calculator_name')
        ->pluck('rate', 'task');
    
    $totalHours = 0;
    $laborTasks = [];
    foreach ($inputTasks as $taskKey => $taskData) {
        $hours = $qty * $dbRates[$taskKey];
        $totalHours += $hours;
        
        // Build enhanced labor task array for import
        $laborTasks[] = [
            'task_key' => $taskKey,
            'task_name' => 'Descriptive Name',
            'description' => '...',
            'quantity' => $qty,
            'unit' => 'sqft',
            'production_rate' => $rate,
            'hours' => $hours,
            'hourly_rate' => $laborRate,
            'total_cost' => $hours * $laborRate,
        ];
    }
    
    // 3. Process materials (catalog or custom)
    $materials = [...];
    $materialTotal = array_sum(array_column($materials, 'total'));
    
    // 4. Calculate overhead/totals
    $calculator = new LaborCostCalculatorService();
    $totals = $calculator->calculate(
        $totalHours,
        $laborRate,
        array_merge($request->all(), ['material_total' => $materialTotal])
    );
    
    // 5. Merge data and save/return
    $data = array_merge($validated, $totals, [
        'labor_tasks' => $laborTasks,
        'materials' => $materials,
        'material_total' => $materialTotal,
        ...
    ]);
    
    // Save to calculations table
    // Return results view
}
```

**Key Fields in `$data` Array:**
- `labor_hours` - Pure task hours (no overhead)
- `total_hours` - Includes drive time, site conditions, cleanup
- `labor_cost` - Total hours × hourly rate
- `material_total` - Sum of all material costs
- `labor_tasks` - Enhanced array for import service
- `materials` - Material line items
- `labor_by_task` - Hours breakdown by task name

#### 2. LaborCostCalculatorService (`app/Services/LaborCostCalculatorService.php`)

**Purpose:** Centralized overhead calculation service

**What It Does:**
- Calculates drive time from distance/speed
- Adds site conditions hours
- Adds cleanup hours
- Adds material pickup time
- Computes total hours
- Computes labor cost (total hours × rate)
- Returns **total_cost** = labor_cost + material_total

**Key Method:**
```php
public function calculate(
    float $laborHours,
    float $laborRate,
    array $inputs
): array {
    $driveHours = ($inputs['drive_distance'] ?? 0) / ($inputs['drive_speed'] ?? 35);
    $driveHours = $driveHours * 2; // Round trip
    
    $totalHours = $laborHours
        + $driveHours
        + ($inputs['site_conditions'] ?? 0)
        + ($inputs['material_pickup'] ?? 0)
        + ($inputs['cleanup'] ?? 0);
    
    $laborCost = $totalHours * $laborRate;
    $materialTotal = $inputs['material_total'] ?? 0;
    
    return [
        'labor_hours' => $laborHours,
        'total_hours' => $totalHours,
        'labor_cost' => $laborCost,
        'total_cost' => $laborCost + $materialTotal,
        'final_price' => $laborCost + $materialTotal, // Compatibility
        'drive_hours' => $driveHours,
        ...
    ];
}
```

**IMPORTANT:** This service does NOT apply profit margins or markups. It's cost-only.

#### 3. Production Rates (`production_rates` table)

**Database Table:**
```sql
CREATE TABLE production_rates (
    id BIGINT PRIMARY KEY,
    calculator VARCHAR(50),  -- 'mulching', 'paver_patio', etc.
    task VARCHAR(100),        -- 'bed_prep', 'excavation', etc.
    rate DECIMAL(10,4),       -- Hours per unit
    unit VARCHAR(50),         -- 'sqft', 'cubic_yard', 'lf', etc.
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Example Entries:**
```
calculator: 'mulching'
task: 'bed_prep'
rate: 0.0050
unit: 'sqft'
→ 0.005 hours per square foot
→ 1000 sqft = 5 hours
```

**Benefits:**
- Centralized rate management
- No hardcoded values in controllers
- Easy updates without code changes
- Consistent across application

#### 4. Material Catalog Integration

**Two Material Systems:**

**A. Catalog Picker (Paver, Planting):**
- User selects from company's material catalog
- Materials include unit costs from catalog
- Form uses `<select>` or autocomplete picker
- Controller receives:
```php
[
    'materials' => [
        [
            'catalog_id' => 123,
            'name' => 'Clay Paver 4x8',
            'quantity' => 500,
            'unit_cost' => 0.85,
            'unit' => 'ea'
        ]
    ]
]
```

**B. Custom Materials Input:**
- Manual entry for non-catalog items
- User provides name, quantity, unit cost
- Used in calculators without specific material catalogs
- Controller receives:
```php
[
    'custom_materials' => [
        [
            'name' => 'Drainage Gravel',
            'qty' => 5,
            'unit_cost' => 85
        ]
    ]
]
```

**Current Status:**
- Paver: Catalog picker ✅
- Planting: Catalog picker ✅
- Others: Custom materials or none

#### 5. Calculation Import Service (`app/Services/CalculationImportService.php`)

**Purpose:** Import calculator results into estimate line items

**Key Features:**
- Imports labor tasks as line items
- Imports materials as line items
- Imports overhead items as line items
- Applies company profit margin at import time
- Links to original calculation for reference

**Import Flow:**
```
Calculator Result
    ↓
CalculationImportService::importToEstimate()
    ↓
For each labor task:
    - Create line item
    - unit_cost = cost (no margin here)
    - Apply margin: unit_price = unit_cost / (1 - margin_rate)
    ↓
For each material:
    - Create line item with catalog reference
    - Apply margin to cost
    ↓
Return line item IDs
```

**Important:** Margin formula is `price = cost / (1 - margin)` NOT `cost * (1 + markup)`
- 15% margin: `price = cost / 0.85`
- This ensures margin = (price - cost) / price = 15%

---

## Form Structure

### Standard Calculator Form Sections

Most calculators follow this pattern:

**Section 1: Job Measurements**
- Primary dimensions (length, width, area, etc.)
- Job-specific options (fence type, plant sizes, etc.)

**Section 2: Labor & Overhead Inputs**
- Labor rate ($/hour)
- Crew size
- Drive distance & speed
- Site conditions (extra hours)
- Material pickup time
- Cleanup time

**Section 3: Materials** (Optional, varies by calculator)
- Catalog material picker, OR
- Custom material inputs, OR
- None (quantity-only calculators)

**Section 4: Notes**
- Job notes textarea

### Removed Sections (December 2025)
- ❌ Material Cost Overrides (removed from all)
- ❌ Additional Custom Materials (removed from RetainingWall)
- ❌ Materials Preview with pricing (removed)

---

## Results Display

### Standard Results View Structure

**Top Card: Total Cost**
```
┌─────────────────────────┐
│   Total Cost            │
│   $X,XXX.XX             │
│                         │
│ Labor: $X,XXX  Materials: $X,XXX  Hours: XX.XX │
└─────────────────────────┘
```

**Labor Breakdown Table:**
- Task name
- Quantity
- Hours
- Cost

**Material Breakdown Table:**
- Material name
- Quantity
- Unit cost (at cost, no markup)
- Total

**Pricing Summary:**
- Labor Cost: $X,XXX
- Material Cost: $X,XXX
- **Total Cost: $X,XXX** (NOT "Final Price")

**Action Buttons:**
- Import to Estimate
- Save Calculation
- Edit
- Print/PDF

---

## Database Schema

### calculations Table

```sql
CREATE TABLE calculations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_visit_id BIGINT NULL,
    estimate_id BIGINT NULL,
    client_id BIGINT NULL,
    property_id BIGINT NULL,
    calculation_type VARCHAR(50),  -- 'mulching', 'paver_patio', etc.
    data JSON,                      -- All calculation results
    is_template BOOLEAN DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (site_visit_id) REFERENCES site_visits(id),
    FOREIGN KEY (estimate_id) REFERENCES estimates(id),
    FOREIGN KEY (client_id) REFERENCES clients(id)
);
```

**Key Points:**
- `data` column stores entire calculation as JSON
- Linked to site visit (for workflow) OR estimate (for templates)
- `calculation_type` identifies calculator used
- Templates have `is_template = 1` and can be reused

### production_rates Table

```sql
CREATE TABLE production_rates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    calculator VARCHAR(50) NOT NULL,
    task VARCHAR(100) NOT NULL,
    rate DECIMAL(10,4) NOT NULL,
    unit VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_calculator_task (calculator, task)
);
```

---

## Known Issues & Limitations

### Current Issues

1. **RetainingWall Calculator**
   - ✅ **FIXED:** Form was completely broken with undefined cost variables
   - ✅ **FIXED:** Removed Materials Preview section
   - Status: Clean rewrite completed December 2, 2025

2. **Override Inputs**
   - ✅ **FIXED:** All override price inputs removed from forms
   - ✅ **FIXED:** Validation rules cleaned up in controllers
   - ✅ **FIXED:** Config files set to 0 for defaults

3. **Custom Materials**
   - Some calculators still have custom materials sections
   - Inconsistent across calculators
   - User requested removal but not yet implemented everywhere

4. **Material Handling Inconsistency**
   - Paver & Planting: Catalog integration
   - Others: Custom input or none
   - Need standardization decision

5. **Terminology**
   - Some old "Final Price" references may still exist in PDFs
   - Need comprehensive audit

### Design Decisions Needed

1. **Should all calculators have material catalog integration?**
   - Currently only Paver and Planting
   - Would improve consistency
   - Requires catalog expansion

2. **Custom materials - keep or remove?**
   - User requested removal
   - Some calculators still have it
   - Useful for non-catalog items

3. **Quantity-only vs Cost tracking**
   - Some materials show quantities only (no pricing)
   - SynTurf shows quantities but all costs = 0
   - Inconsistent user experience

---

## Cost-Only Architecture Impact

### What Changed

**Before (Pricing Mode):**
- Calculators applied markup/profit
- "Final Price" displayed
- Client-facing pricing generated
- Margin calculations in controllers

**After (Cost-Only Mode):**
- Calculators show internal costs only
- "Total Cost" displayed
- No markup applied
- Pricing happens at estimate level

### Benefits

1. **Separation of Concerns**
   - Calculators = cost estimation
   - Estimates = pricing with margins
   - Clearer responsibility

2. **Flexibility**
   - Same calculation can be priced differently
   - Margins set at company level
   - No hardcoded markup logic

3. **Accuracy**
   - Cost estimates more accurate
   - Pricing controlled centrally
   - Easier to audit costs

### Trade-offs

1. **User Confusion**
   - "Total Cost" might be confusing
   - Users expect pricing
   - Need clear documentation

2. **Two-Step Process**
   - Calculate cost, then import to price
   - Extra step for users
   - Could streamline

3. **Lost Features**
   - Material cost overrides removed
   - Less granular control
   - Rely on catalog accuracy

---

## Import Workflow

### How Import Works

1. **User Clicks "Import to Estimate"** on results page

2. **CalculationImportService Processes:**
   ```php
   importToEstimate($calculationId, $estimateId, $options)
   ```

3. **For Each Labor Task:**
   - Create estimate line item
   - Type: 'labor'
   - Quantity: hours
   - Unit cost: labor cost (at cost)
   - Apply margin: `unit_price = unit_cost / (1 - margin_rate)`
   - Link to calculation

4. **For Each Material:**
   - Create estimate line item
   - Type: 'material'
   - Quantity: material quantity
   - Unit cost: material cost (at cost)
   - Apply margin
   - Link to catalog if available
   - Link to calculation

5. **For Each Overhead Item:**
   - Drive time, site conditions, etc.
   - Create as separate line items
   - Apply margin

6. **Result:** Estimate has detailed line items with pricing

### Import Options

**Granular Import (Default):**
- Each task = separate line item
- Each material = separate line item
- Full detail in estimate

**Summary Import:**
- Group by category
- Labor as one line
- Materials as one line
- Less detail, cleaner estimate

---

## Production Rates Management

### How Rates Are Set

Rates are managed in the `production_rates` database table:

```sql
INSERT INTO production_rates (calculator, task, rate, unit) VALUES
('mulching', 'bed_prep', 0.0050, 'sqft'),
('mulching', 'spread_mulch', 0.0030, 'sqft'),
('paver_patio', 'excavation', 0.0080, 'sqft'),
('paver_patio', 'base_install', 0.0060, 'sqft');
```

### Rate Calculation Example

**Mulching Bed Prep:**
- Rate: 0.0050 hrs/sqft
- Area: 1000 sqft
- Hours: 1000 × 0.0050 = 5 hours
- Labor Cost: 5 hrs × $85/hr = $425

### Updating Rates

**Option 1: Database Seeder**
```php
// database/seeders/ProductionRateSeeder.php
DB::table('production_rates')->updateOrInsert(
    ['calculator' => 'mulching', 'task' => 'bed_prep'],
    ['rate' => 0.0055, 'unit' => 'sqft']
);
```

**Option 2: Admin Interface** (if implemented)
- Navigate to Production Rates
- Edit rate value
- Save

**Option 3: Direct SQL**
```sql
UPDATE production_rates 
SET rate = 0.0055 
WHERE calculator = 'mulching' AND task = 'bed_prep';
```

---

## Future Improvements

### Recommended Enhancements

1. **Standardize Material Handling**
   - Expand catalog to all calculators
   - Remove custom material inputs
   - Consistent user experience

2. **Production Rate UI**
   - Admin interface for rate management
   - Visual rate editor
   - Historical rate tracking

3. **Calculator Templates**
   - Pre-built common scenarios
   - "Standard 500sqft Paver Patio"
   - Quick estimate generation

4. **Better Mobile Support**
   - Optimize forms for tablets
   - Field crew can calculate on-site
   - Offline capability

5. **Calculation Comparison**
   - Compare multiple scenarios
   - "With geogrid vs without"
   - Side-by-side cost analysis

6. **Photo Upload**
   - Attach site photos to calculations
   - Visual reference in estimates
   - Better documentation

7. **Integration Improvements**
   - Sync with QuickBooks for actual costs
   - Update production rates from actuals
   - Learning system

---

## Developer Guidelines

### Adding a New Calculator

1. **Create Controller:** `app/Http/Controllers/NewCalculatorController.php`
2. **Create Form View:** `resources/views/calculators/new-calculator/form.blade.php`
3. **Create Results View:** `resources/views/calculators/new-calculator/results.blade.php`
4. **Add Routes:** `routes/web.php`
5. **Seed Production Rates:** `database/seeders/ProductionRateSeeder.php`
6. **Update Navigation:** Add to calculator menu

### Controller Template

See existing calculators like `MulchingCalculatorController.php` for reference structure.

### Testing Checklist

- [ ] Form validation works
- [ ] Production rates pull from database
- [ ] Labor hours calculate correctly
- [ ] Material costs sum correctly
- [ ] LaborCostCalculatorService integrates
- [ ] Results display properly
- [ ] Save calculation works
- [ ] Import to estimate works
- [ ] PDF generation works

---

## Related Documentation

- `CALCULATORS_MASTER_GUIDE.md` - Original system guide
- `CALCULATOR_IMPORT_FIXES_SUMMARY.md` - Import service documentation
- `CALCULATOR_INTEGRATION_IMPLEMENTATION_GUIDE.md` - Integration details
- `../estimates/README.md` - Estimate system documentation
- `../budget/README.md` - Company budget and margin settings

---

## Conclusion

The calculator system provides comprehensive cost estimation for 11 different landscape services. The recent conversion to a **cost-only architecture** (December 2025) has simplified the system by removing pricing logic from calculators and centralizing profit margin application at the estimate level.

**Current Status:**
- ✅ All calculators functional
- ✅ Cost-only architecture implemented
- ✅ Override inputs removed
- ✅ Default material pricing removed
- ⚠️ Some inconsistencies remain in material handling
- ⚠️ Custom materials sections need review

**Next Steps:**
- Standardize material handling across all calculators
- Remove remaining custom material sections (if not needed)
- Audit for any remaining "Final Price" terminology
- Consider catalog expansion for consistent UX
