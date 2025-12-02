# Calculator Import System - Implementation Plan

## Current State (November 28, 2025)

### How It Works NOW:
1. User fills out calculator form
2. Clicks "Calculate" → Redirects to **Results Page**
3. Results page shows calculation summary + import options:
   - **"Save to Site Visit"** - Saves calculation only (no estimate import)
   - **"Save & Append to Estimate"** - Uses OLD `importCalculation()` method
   - **"Save & Replace on Estimate"** - Replaces previous calculation data

### Problem with Current System:
- Uses **legacy import method** (`CalculationImportService::importCalculation()`)
- Creates **collapsed line items** (1 material line, 1 labor line)
- **No work area support**
- **No granular task breakdown**
- Catalog linkage works but not optimized

---

## New Enhanced System ✅ BUILT & TESTED

### What's Already Working:
1. **Database Migration** - `estimate_areas` table has new fields:
   - `calculation_id` - Links to source calculation
   - `site_visit_id` - Links to site visit
   - `planned_hours`, `crew_size`, `drive_time_hours`, `overhead_percent`
   - `calculator_metadata` (JSON) - Stores all calculator-specific data

2. **Enhanced Import Service** - `CalculationImportService::importCalculationToArea()`
   - Creates work areas automatically
   - Imports granular line items (one per task)
   - Separates drive time and overhead into own line items
   - Links materials to catalog via `catalog_id`

3. **Enhanced Calculator Output** - All simple calculators updated:
   - ✅ Mulching (with material catalog)
   - ✅ Planting
   - ✅ Weeding
   - ✅ Pine Needles
   - ✅ Turf Mowing
   - Each outputs `labor_tasks` array with task-level detail

4. **Test Results**:
   ```
   Mulching Calculator → Estimate #5
   ✅ Created Work Area "Mulching (Nov 19)"
   ✅ 3 labor items (Mulch Install, Drive Time, Site Overhead)
   ✅ 1 material item (Forest Brown Mulch, catalog ID 512)
   ✅ Total: $1,381.50
   ```

---

## Proposed User Flow

### Option A: Single-Step Import (Recommended)
**On Results Page - Add prominent import section:**

```
┌─────────────────────────────────────────────────────┐
│  ✨ IMPORT TO ESTIMATE                              │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Select Target Estimate:                           │
│  [Dropdown of site visit estimates] [+ New]        │
│                                                     │
│  Work Area Name:                                    │
│  [Mulching - Front Beds    ] (auto-generated)      │
│                                                     │
│  Import Type:                                       │
│  ⦿ Granular Line Items (Recommended)               │
│     └─ Separate line items per task                │
│  ◯ Collapsed (Legacy)                              │
│     └─ Single labor + material line                │
│                                                     │
│  [💾 Save Only]  [➕ Import to Estimate]           │
└─────────────────────────────────────────────────────┘
```

**Buttons:**
- **"Save Only"** → Saves calculation to site visit (no estimate)
- **"Import to Estimate"** → Uses `importCalculationToArea()` with work area

### Option B: Auto-Import (Aggressive)
- After "Calculate" → Automatically creates work area on linked estimate
- Shows success message: "✅ Imported to Estimate #5 - Work Area: Mulching (Front Beds)"
- User can edit/remove from estimate page

### Option C: Two-Step (Current + Enhanced)
- Keep current "Save to Site Visit" flow
- Add **NEW** button on estimate page: "Import Calculations"
- Shows list of site visit calculations → Click to import

---

## Recommended Implementation: **Option A**

### Why:
1. **User Control** - They choose when to import
2. **Clear Preview** - See what will be imported before committing
3. **Flexible** - Can save without importing, or import immediately
4. **Future-Proof** - Easy to add batch import later

### Changes Needed:

#### 1. Update Results Page Template
File: `resources/views/calculators/mulching/result.blade.php`

**Replace** the actions partial with new import section:
```blade
<div class="bg-white border rounded-xl shadow-lg p-6 mb-8">
    <h2 class="text-2xl font-bold mb-6">Import to Estimate</h2>
    
    <form method="POST" action="{{ route('calculators.import-to-estimate') }}">
        @csrf
        <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        
        {{-- Estimate Selection --}}
        <div class="mb-4">
            <label class="block font-semibold mb-2">Select Target Estimate:</label>
            <div class="flex gap-3">
                <select name="estimate_id" required class="flex-1 form-select">
                    <option value="">-- Choose Estimate --</option>
                    @foreach($estimates as $est)
                        <option value="{{ $est->id }}">
                            #{{ $est->id }} - {{ $est->name }} ({{ $est->status }})
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('estimates.create', ['site_visit_id' => $siteVisit->id]) }}" 
                   class="btn btn-primary">
                    + New Estimate
                </a>
            </div>
        </div>
        
        {{-- Work Area Name --}}
        <div class="mb-4">
            <label class="block font-semibold mb-2">Work Area Name:</label>
            <input type="text" 
                   name="area_name" 
                   value="Mulching - {{ date('M d') }}"
                   class="form-input w-full">
        </div>
        
        {{-- Import Type --}}
        <div class="mb-6">
            <label class="block font-semibold mb-2">Import Type:</label>
            <div class="space-y-2">
                <label class="flex items-start p-3 border rounded hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="import_type" value="granular" checked class="mt-1">
                    <div class="ml-3">
                        <p class="font-semibold">Granular Line Items (Recommended)</p>
                        <p class="text-sm text-gray-600">Separate line items per task with full detail</p>
                    </div>
                </label>
                <label class="flex items-start p-3 border rounded hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="import_type" value="collapsed">
                    <div class="ml-3">
                        <p class="font-semibold">Collapsed (Legacy)</p>
                        <p class="text-sm text-gray-600">Single labor + material line items</p>
                    </div>
                </label>
            </div>
        </div>
        
        {{-- Actions --}}
        <div class="flex gap-3">
            <button type="submit" name="action" value="import" class="btn btn-primary flex-1">
                ➕ Import to Estimate
            </button>
            <button type="submit" name="action" value="save_only" class="btn btn-secondary">
                💾 Save Only
            </button>
        </div>
    </form>
</div>
```

#### 2. Create Import Route
File: `routes/web.php`

```php
Route::post('/calculators/import-to-estimate', [App\Http\Controllers\CalculatorImportController::class, 'import'])
    ->name('calculators.import-to-estimate');
```

#### 3. Create Import Controller
File: `app/Http/Controllers/CalculatorImportController.php`

```php
<?php
namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Services\CalculationImportService;
use Illuminate\Http\Request;

class CalculatorImportController extends Controller
{
    public function __construct(
        private CalculationImportService $importService
    ) {}
    
    public function import(Request $request)
    {
        $validated = $request->validate([
            'calculation_id' => 'required|exists:calculations,id',
            'estimate_id' => 'required|exists:estimates,id',
            'area_name' => 'nullable|string|max:255',
            'import_type' => 'required|in:granular,collapsed',
            'action' => 'required|in:import,save_only',
        ]);
        
        $calculation = Calculation::findOrFail($validated['calculation_id']);
        $estimate = Estimate::findOrFail($validated['estimate_id']);
        
        // Just save, don't import
        if ($validated['action'] === 'save_only') {
            return redirect()
                ->route('site-visits.show', $calculation->site_visit_id)
                ->with('success', 'Calculation saved successfully.');
        }
        
        // Import to estimate
        if ($validated['import_type'] === 'granular') {
            // Use new enhanced import
            $area = $this->importService->importCalculationToArea(
                $estimate, 
                $calculation,
                null,
                ['area_name' => $validated['area_name']]
            );
            
            return redirect()
                ->route('estimates.show', $estimate->id)
                ->with('success', "✅ Imported to Work Area: {$area->name}");
        } else {
            // Use legacy collapsed import
            $this->importService->importCalculation($estimate, $calculation, true);
            
            return redirect()
                ->route('estimates.show', $estimate->id)
                ->with('success', 'Calculation imported (collapsed format).');
        }
    }
}
```

---

## Migration Path

### Phase 1: ✅ COMPLETE
- Database migration
- Enhanced services
- Simple calculators updated
- Material catalog integration
- Import tested successfully

### Phase 2: IN PROGRESS
- Update calculator UIs (Mulching ✅)
- Apply modern theme
- Streamline forms

### Phase 3: NEXT STEPS
1. Update results pages with new import UI
2. Create `CalculatorImportController`
3. Add route
4. Test full flow: Calculator → Results → Import → Estimate
5. Update remaining calculator results pages

### Phase 4: COMPLEX CALCULATORS
- Retaining Wall
- Paver Patio
- Fence
- Synthetic Turf
- Pruning

### Phase 5: ESTIMATE UI
- Add "View Source Calculation" link on work areas
- Add "Re-import" button to refresh from calculator
- Add bulk import from site visit calculations

---

## User Experience Goals

### Before (Current):
```
Calculator → Results → [Append/Replace] → Estimate
                             ↓
                    Collapsed line items
                    No work area grouping
                    Can't track source
```

### After (Enhanced):
```
Calculator → Results → [Import Options] → Estimate
                             ↓
                    Work area created
                    Granular line items
                    Catalog linkage
                    Source tracking
                    Can re-import/update
```

---

## Testing Checklist

- [x] Database migration runs successfully
- [x] Import service creates work areas
- [x] Granular line items import correctly
- [x] Catalog materials link properly
- [x] Calculator metadata stored
- [ ] Results page UI updated
- [ ] Import controller created
- [ ] Full flow test: Calculator → Import → Estimate
- [ ] Legacy import still works
- [ ] Batch import tested
- [ ] Edge cases handled (no estimate, duplicate imports, etc.)

---

## Next Action Items

1. **Update Mulching Results Page** - Add new import UI
2. **Create Import Controller** - Handle granular vs collapsed
3. **Test Full Flow** - Calculator → Results → Import
4. **Apply to Other Calculators** - Pine Needles, Planting, etc.
5. **Document for Users** - Training on new import workflow
