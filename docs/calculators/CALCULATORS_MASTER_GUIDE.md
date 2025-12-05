# 🧮 Calculator System - Master Guide

**Last Updated:** December 2, 2025  
**Status:** 10 calculators fully operational, 2 modernized with reusable partials (20% modular)

---

## 📚 Table of Contents

1. [Quick Start](#quick-start)
2. [System Overview](#system-overview)
3. [Calculator Types](#calculator-types)
4. [Implementation Status](#implementation-status)
5. [Reusable Partials System](#reusable-partials-system)
6. [Material Catalog Integration](#material-catalog-integration)
7. [Production Rates System](#production-rates-system)
8. [Universal Excavation Rates](#universal-excavation-rates)
9. [Import Workflow](#import-workflow)
10. [Development Guide](#development-guide)
11. [Migration & Deployment](#migration--deployment)
12. [Related Documentation](#related-documentation)

---

## 🚀 Quick Start

### For Users
1. Navigate to site visit or select "Template Mode" for estimate-only calculations
2. Click calculator type (Syn-Turf, Paver Patio, Mulching, etc.)
3. Fill in measurements and crew details
4. Review auto-calculated quantities in real-time (blue boxes)
5. Select materials from catalog or enter custom materials
6. Labor tasks auto-populate based on production rates
7. Click "Calculate" to save and view results
8. Choose "Import to Estimate" (granular line items) or save for later

### For Developers
- **Backend Logic:** `app/Http/Controllers/*CalculatorController.php`
- **Views:** `resources/views/calculators/*/form.blade.php` and `result.blade.php` (or `results.blade.php`)
- **Partials:** `resources/views/calculators/partials/*.blade.php`
- **Services:** 
  - `app/Services/LaborCostCalculatorService.php` - Labor cost calculations
  - `app/Services/CalculationImportService.php` - Import to estimates
- **Production Rates:** `production_rates` database table
- **Seeders:** `database/seeders/ProductionRateSeeder.php`
- **Migrations:** `database/migrations/*production*.php`, `*excavation*.php`

---

## 🎯 System Overview

### Architecture

The calculator system uses a **database-driven production rate model** with **reusable Blade partials** for UI consistency:

```
User Input → Alpine.js Calculations → Form Submission → Controller
    ↓
Controller: Fetch production rates from DB
    ↓
Calculate labor hours = quantity × production_rate
    ↓
LaborCostCalculatorService: overhead, drive time, profit markup
    ↓
Save to calculations table → Display results → Import to estimate
```

### Key Features

- **10 Calculator Types**: Fence, Mulching, Paver Patio, Pine Needles, Planting, Pruning, Retaining Wall, Syn-Turf, Turf Mowing, Weeding
- **Material Catalog Integration**: Pick materials from centralized catalog with pricing
- **Production Rate System**: Database-driven labor hour calculations per unit (sqft, lf, cy, ea, etc.)
- **Universal Excavation Rates**: 3 excavation methods (manual, mini skid steer, skid steer) shared across calculators
- **Reusable Partials**: Modular UI components for consistent user experience
- **Alpine.js Reactivity**: Real-time quantity calculations and labor auto-population
- **Template Mode**: Create calculations without site visits for estimate building
- **Import to Estimate**: Granular line-item import with material and labor breakdowns

---

## 📋 Calculator Types

### 1. **Syn-Turf** ⭐ (Fully Modernized)
- **Status**: Reference implementation with all 3 reusable partials
- **Form**: `resources/views/calculators/syn-turf/form.blade.php`
- **Controller**: `app/Http/Controllers/SynTurfCalculatorController.php`
- **Production Rates**: 7 tasks (excavation methods, base install, turf install, edging, infill)
- **Features**: 
  - Material catalog picker
  - Calculated quantities box (Area, Excavation CY, ABC CY, Rock Dust CY, Base CY, Perimeter LF)
  - Excavation method selector (manual, mini skid steer, skid steer)
  - Labor tasks section with auto-population
  - Blue color theme
- **Partials Used**: `calculated_quantities_box`, `excavation_method_selector`, `labor_tasks_section`, `overhead_inputs`, `client_info`

### 2. **Paver Patio** ⭐ (Fully Modernized)
- **Status**: Matches syn-turf implementation exactly
- **Form**: `resources/views/calculators/paver-patio/form.blade.php`
- **Controller**: `app/Http/Controllers/PaverPatioCalculatorController.php`
- **Production Rates**: 5 tasks (base compaction, laying pavers, cutting borders, install edging, cleanup)
- **Features**:
  - Single area input (simplified from length/width)
  - Material catalog picker
  - Calculated quantities box (Area, Excavation, Base Gravel, Polymeric Sand, Pavers, Edging LF)
  - Excavation method selector (uses universal excavation rates)
  - Labor tasks section with auto-population
  - Blue color theme
- **Partials Used**: `calculated_quantities_box`, `excavation_method_selector`, `labor_tasks_section`, `overhead_inputs`, `client_info`

### 3. **Fence**
- **Status**: Operational, uses overhead_inputs partial
- **Form**: `resources/views/calculators/fence/form.blade.php`
- **Production Rates**: 7 tasks (post install manual/auger, panel install, picket install, rail install, gate install, concrete mix)
- **Partials Used**: `overhead_inputs` (1 partial)

### 4. **Mulching**
- **Status**: Operational, uses client_info and overhead_inputs
- **Form**: `resources/views/calculators/mulching/form.blade.php`
- **Production Rates**: 9 tasks (bed edge manual/mechanical, install standard/heavy/refresh by wheelbarrow/tractor, cleanup)
- **Partials Used**: `client_info`, `overhead_inputs` (2 partials)

### 5. **Pine Needles**
- **Status**: Operational, uses client_info and overhead_inputs
- **Form**: `resources/views/calculators/pine_needles/form.blade.php`
- **Production Rates**: 6 tasks (open area, around plants, heavy prep, refresh light, delivery stage, cleanup)
- **Partials Used**: `client_info`, `overhead_inputs` (2 partials)

### 6. **Planting**
- **Status**: Operational, uses client_info and overhead_inputs
- **Form**: `resources/views/calculators/planting/form.blade.php`
- **Production Rates**: 10 tasks (annual flats, pots, container sizes 1g-25g, ball & burlap, palm 8-12')
- **Partials Used**: `client_info`, `overhead_inputs` (2 partials)

### 7. **Pruning**
- **Status**: Operational, uses client_info and overhead_inputs
- **Form**: `resources/views/calculators/pruning/form.blade.php`
- **Production Rates**: 21 tasks (hand/ladder/tree pruning, shearing, hedge shearing, cut back annuals/grasses, deadheading, palm pruning 5 sizes, palm cleanup/seed removal - all with normal/overgrown variants)
- **Partials Used**: `client_info`, `overhead_inputs` (2 partials)

### 8. **Retaining Wall**
- **Status**: Operational, no partials yet
- **Form**: `resources/views/calculators/retaining-wall/form.blade.php`
- **Production Rates**: 17 tasks (excavation manual/excavator, base install, block laying manual/excavator, capstone, geogrid, backfill, pipe install, Allan Block variants for curved/straight walls, capstone, columns, stairs)
- **Partials Used**: None (0 partials) - **Candidate for modernization**

### 9. **Turf Mowing**
- **Status**: Operational, uses client_info and overhead_inputs
- **Form**: `resources/views/calculators/turf-mowing/form.blade.php`
- **Production Rates**: 5 tasks (mowing 48" Z-turn, stick edging, weed eater bed edging/general, blowing cleanup)
- **Partials Used**: `client_info`, `overhead_inputs` (2 partials)

### 10. **Weeding**
- **Status**: Operational, uses client_info and overhead_inputs
- **Form**: `resources/views/calculators/weeding/form.blade.php`
- **Production Rates**: 14 tasks (bed weeding light/normal/heavy, hand weeding light/heavy/natural areas, shrub/tree ring weeding, bed edging, weed eat bed edges/natural areas, spray broadcast/spot beds/natural areas)
- **Partials Used**: `client_info`, `overhead_inputs` (2 partials)

---

## 🎨 Implementation Status

| Calculator | Status | Partials | Material Catalog | Production Rates | Excavation | Color Theme | Notes |
|-----------|--------|----------|------------------|------------------|------------|-------------|-------|
| **Syn-Turf** | ✅ Modernized | 5/5 | ✅ Yes | 7 tasks | ✅ Universal | Blue | Reference implementation |
| **Paver Patio** | ✅ Modernized | 5/5 | ✅ Yes | 5 tasks | ✅ Universal | Blue | Matches syn-turf |
| Fence | ✅ Operational | 1/5 | ❌ No | 7 tasks | ❌ N/A | Default | Basic partial usage |
| Mulching | ✅ Operational | 2/5 | ❌ No | 9 tasks | ❌ N/A | Default | Standard implementation |
| Pine Needles | ✅ Operational | 2/5 | ❌ No | 6 tasks | ❌ N/A | Default | Standard implementation |
| Planting | ✅ Operational | 2/5 | ❌ No | 10 tasks | ❌ N/A | Default | Standard implementation |
| Pruning | ✅ Operational | 2/5 | ❌ No | 21 tasks | ❌ N/A | Default | Most complex task set |
| Retaining Wall | ✅ Operational | 0/5 | ❌ No | 17 tasks | ⚠️ Old method | Default | **Needs modernization** |
| Turf Mowing | ✅ Operational | 2/5 | ❌ No | 5 tasks | ❌ N/A | Default | Standard implementation |
| Weeding | ✅ Operational | 2/5 | ❌ No | 14 tasks | ❌ N/A | Default | Standard implementation |

**Summary:**
- **10/10** calculators operational
- **2/10** calculators fully modernized (20%)
- **8/10** calculators ready for partial integration
- **1** calculator (Retaining Wall) has old excavation method needing update

---

## 🧩 Reusable Partials System

### Overview
Reusable Blade partials provide consistent UI/UX across calculators while reducing code duplication.

### Available Partials

#### 1. **calculated_quantities_box.blade.php**
**Purpose**: Display calculated material quantities in a static, color-themed box

**Props**:
- `$color` (string): Color theme - `'blue'`, `'amber'`, `'green'`
- `$quantities` (array): Array of quantity items

**Quantity Array Format**:
```php
[
    ['label' => 'Area', 'value' => "area.toFixed(2) + ' sqft'", 'alpine' => true],
    ['label' => 'Excavation', 'value' => "excavationCY.toFixed(2) + ' cy'", 'alpine' => true],
    ['label' => 'Static Value', 'value' => '100 sqft', 'alpine' => false],
]
```

**Features**:
- Responsive grid (2 cols mobile, 4 cols desktop)
- Alpine.js `x-text` binding support
- Color-themed borders and text

**Usage Example**:
```blade
@include('calculators.partials.calculated_quantities_box', [
    'color' => 'blue',
    'quantities' => [
        ['label' => 'Area', 'value' => "area.toFixed(2) + ' sqft'", 'alpine' => true],
        ['label' => 'Excavation', 'value' => "excavationCY.toFixed(2) + ' cy'", 'alpine' => true],
    ]
])
```

#### 2. **excavation_method_selector.blade.php**
**Purpose**: Radio button selector for excavation method with production rate info

**Props**:
- `$color` (string): Color theme - `'blue'`, `'amber'`, `'green'`
- `$formData` (array): Form data for old values
- `$alpineModel` (string, optional): Alpine.js model name (default: `'excavationMethod'`)

**Features**:
- 3 excavation methods: Manual, Mini Skid Steer, Skid Steer
- Displays production rates from universal excavation rates
- Color-themed hover states
- Alpine.js reactive binding

**Usage Example**:
```blade
@include('calculators.partials.excavation_method_selector', [
    'color' => 'blue',
    'formData' => $formData
])
```

#### 3. **labor_tasks_section.blade.php**
**Purpose**: Display labor task inputs with production rate information and auto-population

**Props**:
- `$calculator` (string): Calculator name (matches DB `calculator` column)
- `$formData` (array): Form data for saved/old values
- `$color` (string): Color theme - `'blue'`, `'amber'`, `'green'`
- `$includeExcavation` (bool): Whether to include universal excavation tasks

**Features**:
- Dynamically pulls production rates from database
- Displays task name, production rate, and unit
- Integrates with Alpine.js `x-model` for labor quantities
- Handles both saved values and auto-populated values
- Color-themed section styling

**Usage Example**:
```blade
@include('calculators.partials.labor_tasks_section', [
    'calculator' => 'paver_patio',
    'formData' => $formData,
    'color' => 'blue',
    'includeExcavation' => true
])
```

#### 4. **overhead_inputs.blade.php**
**Purpose**: Standard crew & logistics inputs (crew size, drive distance/speed, labor rate, overhead percentages)

**Usage**: All 10 calculators use this partial

#### 5. **client_info.blade.php**
**Purpose**: Display client and site visit information banner

**Usage**: 9/10 calculators use this partial (fence does not)

---

## 🏗️ Material Catalog Integration

### Overview
Syn-Turf and Paver Patio calculators use the centralized material catalog for consistent pricing.

### Material Catalog Picker Component
**Location**: `resources/views/components/material-catalog-picker.blade.php`

**Features**:
- Search materials by name
- Filter by category
- Real-time search
- Shows unit cost and unit type
- Emits Alpine.js event `@material-selected.window`

### Integration Pattern

**Form Setup**:
```blade
<div x-data="calculatorFunction()">
    <x-material-catalog-picker />
</div>
```

**Alpine.js Handler**:
```javascript
handleMaterialSelected(event) {
    const material = event.detail.material;
    this.selectedMaterials.push({
        catalog_id: material.id,
        name: material.name,
        quantity: 0,
        unit_cost: parseFloat(material.unit_cost),
        unit: material.unit
    });
}
```

**Controller Processing**:
```php
$materials = [];
if (!empty($validated['materials'])) {
    foreach ($validated['materials'] as $mat) {
        $qty = (float) $mat['quantity'];
        $unitCost = (float) $mat['unit_cost'];
        $total = $qty * $unitCost;
        
        $materials[] = [
            'catalog_id' => $mat['catalog_id'] ?? null,
            'name' => $mat['name'],
            'quantity' => round($qty, 2),
            'unit_cost' => round($unitCost, 2),
            'unit' => $mat['unit'] ?? 'ea',
            'total' => round($total, 2),
        ];
    }
}
```

### Data Structure
Materials are stored in `calculations.data` as:
```json
{
    "materials": [
        {
            "catalog_id": 123,
            "name": "Synthetic Turf - Premium Grade",
            "quantity": 500.00,
            "unit_cost": 3.50,
            "unit": "sqft",
            "total": 1750.00
        }
    ],
    "material_total": 1750.00
}
```

---

## ⚙️ Production Rates System

### Overview
Production rates define how many labor hours are required per unit of work. Stored in `production_rates` table.

### Database Schema
```sql
CREATE TABLE production_rates (
    id INTEGER PRIMARY KEY,
    calculator VARCHAR(50),  -- e.g., 'syn_turf', 'paver_patio', 'excavation'
    task VARCHAR(100),       -- e.g., 'turf_install', 'laying_pavers'
    rate DECIMAL(10,4),      -- hours per unit (e.g., 0.022 hrs/sqft)
    unit VARCHAR(50),        -- sqft, lf, cy, ea, etc.
    note TEXT,               -- optional description
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Current Production Rates (December 2, 2025)

#### Universal Excavation (calculator='excavation')
- `excavation_manual`: 0.014 hrs/sqft
- `excavation_mini_skid`: 0.14 hrs/cy
- `excavation_skid_steer`: 0.1 hrs/cy

#### Syn-Turf (calculator='syn_turf')
- `base_install`: 0.2 hrs/cy
- `edging_install`: 0.06 hrs/lf
- `excavation_manual`: 0.014 hrs/sqft (references universal)
- `excavation_mini_skid`: 0.14 hrs/cy (references universal)
- `excavation_skid_steer`: 0.1 hrs/cy (references universal)
- `infill_application`: 0.0025 hrs/sqft
- `turf_install`: 0.022 hrs/sqft

#### Paver Patio (calculator='paver_patio')
- `base_compaction`: 0.04 hrs/sqft
- `cleanup`: 0.005 hrs/sqft
- `cutting_borders`: 0.015 hrs/sqft
- `install_edging`: 0.007 hrs/lf
- `laying_pavers`: 0.06 hrs/sqft
- **Note**: Uses universal excavation rates (no local excavation task)

#### Fence (calculator='fence')
- `concrete_mix`: 0.4 hrs/ea
- `gate_install`: 1.5 hrs/ea
- `panel_install`: 0.25 hrs/lf
- `picket_install`: 0.25 hrs/lf
- `post_install_auger`: 0.12 hrs/ea
- `post_install_manual`: 0.22 hrs/ea
- `rail_install`: 0.15 hrs/lf

#### Mulching (calculator='mulching')
- `mulch_bed_edge_manual`: 0.02 hrs/linear foot
- `mulch_bed_edge_mechanical`: 0.005 hrs/linear foot
- `mulch_cleanup_final`: 0.02 hrs/cubic yard
- `mulch_install_heavy_tractor`: 0.35 hrs/cubic yard
- `mulch_install_heavy_wheelbarrow`: 0.9 hrs/cubic yard
- `mulch_install_refresh_wheelbarrow`: 0.5 hrs/cubic yard
- `mulch_install_standard_tractor`: 0.25 hrs/cubic yard
- `mulch_install_standard_wheelbarrow`: 0.7 hrs/cubic yard
- `mulch_refresh_tractor`: 0.035 hrs/cubic yard

#### Pine Needles (calculator='pine_needles')
- `pine_needles_around_plants`: 0.06 hrs/bale
- `pine_needles_cleanup_final`: 0.01 hrs/bale
- `pine_needles_delivery_stage`: 0.01 hrs/bale
- `pine_needles_heavy_prep`: 0.08 hrs/bale
- `pine_needles_open_area`: 0.04 hrs/bale
- `pine_needles_refresh_light`: 0.03 hrs/bale

#### Planting (calculator='planting')
- `annual_flats`: 0.08 hrs/flat
- `annual_pots`: 0.06 hrs/pot
- `ball_and_burlap`: 0.75 hrs/plant
- `container_10g`: 0.35 hrs/plant
- `container_15g`: 0.45 hrs/plant
- `container_1g`: 0.12 hrs/plant
- `container_25g`: 0.6 hrs/plant
- `container_3g`: 0.18 hrs/plant
- `container_5g`: 0.22 hrs/plant
- `container_7g`: 0.28 hrs/plant
- `palm_8_12`: 1.0 hrs/plant

#### Pruning (calculator='pruning')
- `cut_back_annuals`: 0.02 hrs/plant
- `cut_back_annuals_overgrown`: 0.03 hrs/plant
- `cut_back_grasses`: 0.07 hrs/grass plant
- `cut_back_grasses_overgrown`: 0.105 hrs/grass plant
- `deadheading`: 0.007 hrs/sqft
- `deadheading_overgrown`: 0.0105 hrs/sqft
- `hand_pruning`: 0.08 hrs/shrub
- `hand_pruning_overgrown`: 0.12 hrs/shrub
- `hedge_shearing`: 0.008 hrs/sqft face area
- `hedge_shearing_overgrown`: 0.012 hrs/sqft face area
- `ladder_pruning`: 0.13 hrs/shrub
- `ladder_pruning_overgrown`: 0.2 hrs/shrub
- `palm_cleanup_heavy`: 0.05 hrs/palm
- `palm_prune_extra_tall`: 0.6 hrs/palm
- `palm_prune_medium`: 0.2 hrs/palm
- `palm_prune_short`: 0.1 hrs/palm
- `palm_prune_tall`: 0.3 hrs/palm
- `palm_seed_removal`: 0.08 hrs/palm
- `shearing`: 0.04 hrs/shrub
- `shearing_overgrown`: 0.06 hrs/shrub
- `tree_pruning`: 0.2 hrs/tree
- `tree_pruning_overgrown`: 0.3 hrs/tree

#### Retaining Wall (calculator='retaining_wall')
- `allan_block_capstone`: 0.15 hrs/lf
- `allan_block_columns`: 1.5 hrs/column
- `allan_block_laying_curved_wall`: 0.2 hrs/sqft
- `allan_block_laying_straight_wall`: 0.2 hrs/sqft
- `allan_block_stairs`: 1.0 hrs/step
- `base_install`: 0.01 hrs/sqft
- `block_laying_excavator`: 0.05 hrs/sqft
- `block_laying_manual`: 0.09 hrs/sqft
- `capstone`: 0.03 hrs/ea
- `excavation`: 0.1 hrs/lf
- `excavation_excavator`: 0.05 hrs/ft
- `excavation_manual`: 0.12 hrs/ft
- `geogrid`: 0.04 hrs/lf
- `gravel_backfill`: 0.01 hrs/cf
- `pipe_install`: 0.02 hrs/lf
- `topsoil_backfill`: 0.008 hrs/cf
- `underlayment`: 0.002 hrs/sqft

#### Turf Mowing (calculator='turf_mowing')
- `blowing_cleanup`: 0.000015 hrs/sqft
- `mowing_48z_turn`: 0.00002 hrs/sqft
- `stick_edging`: 0.00045 hrs/linear ft
- `weed_eater_bed_edging`: 0.00035 hrs/linear ft
- `weed_eater_general`: 0.00005 hrs/sqft

#### Weeding (calculator='weeding')
- `bed_edging`: 0.01 hrs/linear ft
- `bed_weeding`: 0.0033 hrs/sqft
- `bed_weeding_heavy`: 0.005 hrs/sqft
- `bed_weeding_light`: 0.002 hrs/sqft
- `hand_weeding_heavy`: 0.015 hrs/sqft
- `hand_weeding_light`: 0.008 hrs/sqft
- `hand_weeding_natural_areas`: 0.01 hrs/sqft
- `shrub_weeding`: 0.05 hrs/each
- `spray_broadcast_beds`: 0.0008 hrs/sqft
- `spray_spot_beds`: 0.0015 hrs/sqft
- `spray_spot_natural_areas`: 0.0012 hrs/sqft
- `tree_ring_weeding`: 0.1 hrs/each
- `weed_eat_bed_edges`: 0.002 hrs/linear foot
- `weed_eat_natural_areas`: 0.006 hrs/sqft

### Usage in Controllers

```php
// Fetch production rates
$rates = ProductionRate::where('calculator', 'syn_turf')->get()->keyBy('task');

// Calculate labor hours
foreach ($taskInput as $taskKey => $taskData) {
    $qty = (float) ($taskData['qty'] ?? 0);
    if ($qty <= 0) continue;
    
    $rate = $rates->get($taskKey);
    if (!$rate) continue;
    
    $hours = $qty * $rate->rate;
    
    $laborTasks[] = [
        'task' => ucwords(str_replace('_', ' ', $taskKey)),
        'qty' => round($qty, 2),
        'unit' => $rate->unit,
        'production_rate' => $rate->rate,
        'hours' => round($hours, 2),
        'hourly_rate' => $laborRate,
        'total_cost' => round($hours * $laborRate, 2),
    ];
}
```

---

## 🚜 Universal Excavation Rates

### Concept
Excavation rates are shared across multiple calculators to maintain consistency.

### Implementation
**Database**: `calculator='excavation'` (special calculator type)

**Three Methods**:
1. **Manual** (`excavation_manual`): 0.014 hrs/sqft - hand digging
2. **Mini Skid Steer** (`excavation_mini_skid`): 0.14 hrs/cy - compact equipment
3. **Skid Steer** (`excavation_skid_steer`): 0.1 hrs/cy - full-size equipment

### Calculators Using Universal Excavation
- ✅ **Syn-Turf**: References all 3 methods
- ✅ **Paver Patio**: References all 3 methods
- ⚠️ **Retaining Wall**: Has old local excavation methods (needs update)

### Controller Pattern
```php
// Fetch both calculator-specific and universal excavation rates
$synTurfRates = ProductionRate::where('calculator', 'syn_turf')->get()->keyBy('task');
$excavationRates = ProductionRate::where('calculator', 'excavation')->get()->keyBy('task');

// Try calculator rates first, fallback to excavation rates
$rate = $synTurfRates->get($taskKey) ?? $excavationRates->get($taskKey);
```

### Migration History
- **2025_12_02_211527**: Created universal excavation rates, updated syn-turf
- **2025_12_02_221148**: Removed paver_patio local excavation, updated edging unit to 'lf'

---

## 📥 Import Workflow

### Process
1. User completes calculation → Results page
2. Click "Import to Estimate" button
3. Form submits with `action=import` to `calculators.import-to-estimate` route
4. `CalculationImportService` creates granular line items:
   - Each material → separate estimate line item
   - Each labor task → separate estimate line item
5. Redirect to estimate view

### Import Service
**Location**: `app/Services/CalculationImportService.php`

**Key Methods**:
- `importToEstimate(Calculation $calculation, Estimate $estimate)`: Main import logic
- Material line items: `name`, `quantity`, `unit_cost`, `line_total`, `is_material=true`
- Labor line items: `name`, `quantity` (hours), `unit_cost` (labor rate), `line_total`, `is_labor=true`

---

## 🛠️ Development Guide

### Creating a New Calculator

#### 1. Create Controller
```bash
php artisan make:controller NewCalculatorController
```

Extend base controller pattern with:
- `showForm()`: Display form
- `calculate(Request $request)`: Process and save
- `showResult(Calculation $calculation)`: Display results
- `downloadPdf(Calculation $calculation)`: Generate PDF

#### 2. Create Views
- `resources/views/calculators/new-calculator/form.blade.php`
- `resources/views/calculators/new-calculator/result.blade.php`

#### 3. Add Production Rates
```php
// database/seeders/ProductionRateSeeder.php
$newCalculatorRates = [
    ['calculator' => 'new_calculator', 'task' => 'task_name', 'rate' => 0.05, 'unit' => 'sqft'],
];

foreach ($newCalculatorRates as $rate) {
    ProductionRate::updateOrCreate(
        ['calculator' => $rate['calculator'], 'task' => $rate['task']],
        ['rate' => $rate['rate'], 'unit' => $rate['unit']]
    );
}
```

#### 4. Create Routes
```php
// routes/web.php
Route::prefix('calculators')->name('calculations.')->group(function () {
    Route::get('/new-calculator/{siteVisit}', [NewCalculatorController::class, 'showForm'])->name('new-calculator.form');
    Route::post('/new-calculator/calculate', [NewCalculatorController::class, 'calculate'])->name('new-calculator.calculate');
    Route::get('/new-calculator/{calculation}/result', [NewCalculatorController::class, 'showResult'])->name('new-calculator.showResult');
});
```

#### 5. Integrate Partials
**Recommended partial usage**:
```blade
{{-- Overhead inputs --}}
@include('calculators.partials.overhead_inputs')

{{-- Calculated quantities box --}}
@include('calculators.partials.calculated_quantities_box', [
    'color' => 'blue',
    'quantities' => [...]
])

{{-- Excavation selector (if needed) --}}
@include('calculators.partials.excavation_method_selector', [
    'color' => 'blue',
    'formData' => $formData
])

{{-- Labor tasks section --}}
@include('calculators.partials.labor_tasks_section', [
    'calculator' => 'new_calculator',
    'formData' => $formData,
    'color' => 'blue',
    'includeExcavation' => false
])
```

### Modernizing Existing Calculators

**Priority Order**:
1. ✅ Syn-Turf (Complete - reference implementation)
2. ✅ Paver Patio (Complete - matches syn-turf)
3. 🔜 Retaining Wall (update excavation, add partials)
4. 🔜 Fence, Mulching, Pine Needles, Planting, Pruning, Turf Mowing, Weeding (add partials)

**Modernization Checklist**:
- [ ] Review current form structure
- [ ] Identify calculator-specific inputs vs. reusable components
- [ ] Integrate `calculated_quantities_box` for real-time quantity display
- [ ] Integrate `excavation_method_selector` (if excavation applies)
- [ ] Integrate `labor_tasks_section` for production rate-driven labor inputs
- [ ] Add Alpine.js for reactive calculations
- [ ] Update controller to include `labor_by_task` array
- [ ] Test end-to-end workflow
- [ ] Update color theme for consistency
- [ ] Create migration for production rate changes (if any)

---

## 🚀 Migration & Deployment

### Production Rate Migrations

#### Migration: 2025_12_02_211527 (Excavation & Syn-Turf)
**Purpose**: Create universal excavation rates, update syn-turf tasks

**Up**:
- Creates 3 universal excavation rates (`calculator='excavation'`)
- Updates syn-turf excavation tasks to reference universal rates
- Deletes old syn-turf local excavation tasks

**Down**:
- Rolls back to old syn-turf excavation rates
- Deletes universal excavation rates

#### Migration: 2025_12_02_221148 (Paver Patio Fixes)
**Purpose**: Remove duplicate excavation, fix edging unit

**Up**:
- Deletes paver_patio excavation task (0.03 hrs/sqft)
- Updates install_edging unit from 'sqft' to 'lf'

**Down**:
- Restores excavation task
- Reverts install_edging unit to 'sqft'

### Seeder Structure
**Location**: `database/seeders/ProductionRateSeeder.php`

**Pattern**:
```php
public function run()
{
    $excavationRates = [
        ['calculator' => 'excavation', 'task' => 'excavation_manual', 'rate' => 0.014, 'unit' => 'sqft'],
        // ...
    ];

    foreach ($excavationRates as $rate) {
        ProductionRate::updateOrCreate(
            ['calculator' => $rate['calculator'], 'task' => $rate['task']],
            ['rate' => $rate['rate'], 'unit' => $rate['unit']]
        );
    }
    
    // Repeat for synTurfRates, paverPatioRates, etc.
}
```

### Deployment Steps

1. **Commit migrations and seeders**:
   ```bash
   git add database/migrations/*
   git add database/seeders/ProductionRateSeeder.php
   git commit -m "Add production rate migrations and seeder updates"
   ```

2. **Push to production**:
   ```bash
   git push origin main
   ```

3. **SSH into production server**:
   ```bash
   ssh user@production-server
   cd /path/to/landscape-app
   ```

4. **Pull latest code**:
   ```bash
   git pull origin main
   ```

5. **Run migrations**:
   ```bash
   php artisan migrate
   ```

6. **Verify production rates**:
   ```bash
   php artisan tinker
   >>> DB::table('production_rates')->where('calculator', 'excavation')->get();
   >>> DB::table('production_rates')->where('calculator', 'paver_patio')->get();
   ```

7. **Clear caches**:
   ```bash
   php artisan view:clear
   php artisan config:clear
   php artisan cache:clear
   ```

8. **Rebuild assets**:
   ```bash
   npm run build
   ```

### Rollback Plan
If issues occur:
```bash
php artisan migrate:rollback --step=1  # Roll back last migration
php artisan migrate:rollback --step=2  # Roll back last 2 migrations
```

---

## 📚 Related Documentation

- **Calculators Overview**: `docs/calculators/README.md`
- **Current State Summary**: `docs/CURRENT_STATE_SUMMARY.md`
- **Budget System**: `docs/budget/`
- **Estimate System**: `docs/estimates/`
- **Material Catalog**: See Material model and catalog views
- **Labor Cost Service**: `app/Services/LaborCostCalculatorService.php`

---

## 🎯 Next Steps

### Immediate Priorities
1. ✅ Complete syn-turf modernization (Done)
2. ✅ Complete paver-patio modernization (Done)
3. ✅ Deploy migrations to production (Pending)
4. 🔜 Modernize Retaining Wall calculator (update excavation, add partials)
5. 🔜 Apply partials to remaining 7 calculators

### Long-term Goals
- Achieve 100% partial usage across all calculators
- Create standardized color themes for each calculator type
- Expand material catalog integration to all applicable calculators
- Create comprehensive test suite for calculator workflows
- Document calculator-specific business logic and formulas

---

**Document Version**: 2.0  
**Contributors**: Development Team  
**Review Cycle**: Monthly or after major calculator updates

