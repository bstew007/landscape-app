# Estimate Line Item Reactive Calculations

## Overview

Added real-time reactive calculations to estimate line items, allowing users to:
- **Change unit price** → profit % updates automatically
- **Change profit %** → unit price updates automatically  
- **Change unit cost** → breakeven & profit recalculate
- **Reset button** → restores catalog defaults for cost & price

---

## Features Implemented

### 1. **Real-Time Calculations** ✅

Each line item now has Alpine.js reactivity:

```
User edits → Alpine component recalculates → UI updates instantly (no save needed)
```

**Calculations:**
- `Breakeven = Unit Cost + Overhead (for labor)` or `Unit Cost × (1 + Tax) (for materials)`
- `Profit % = (Unit Price - Breakeven) / Unit Price × 100`
- `Unit Price = Breakeven / (1 - Profit % / 100)`

### 2. **Profit % Input Field** ✅

Replaced static profit % display with an editable input:

**Before:**
```blade
<span>25.2%</span>
```

**After:**
```blade
<input type="number" x-model="profitPercent" @input="recalculateFromProfit()">
```

### 3. **Two-Way Sync** ✅

- Edit **Unit Price** → Profit % recalculates
- Edit **Profit %** → Unit Price recalculates
- Edit **Unit Cost** → Both breakeven and profit % recalculate

### 4. **Reset to Catalog Defaults** ✅

Blue "Reset" button on each line item:

```blade
<button @click="resetToCatalogDefaults()">Reset</button>
```

**Functionality:**
- Fetches latest catalog item data via API
- Resets unit cost & unit price to catalog defaults
- Recalculates profit % based on current budget margin
- Shows toast notification on success

---

## Files Modified

### 1. **resources/views/estimates/partials/area.blade.php** ✅

**Changes:**
- Added `x-data="lineItemCalculator(...)"` to each `<tr>` row
- Converted static values to `x-model` inputs and `x-text` bindings
- Added `@input` listeners for real-time recalculation
- Added Reset button with `@click="resetToCatalogDefaults()"`
- Added data attributes for catalog type/id tracking

**Key Bindings:**
```blade
<!-- Unit Cost: editable with recalc -->
<input x-model.number="unitCost" @input="recalculateFromCost()">

<!-- Breakeven: reactive display -->
<span x-text="'$' + breakeven.toFixed(2)"></span>

<!-- Unit Price: editable with recalc -->
<input x-model.number="unitPrice" @input="recalculateFromPrice()">

<!-- Profit %: editable input with recalc -->
<input x-model.number="profitPercent" @input="recalculateFromProfit()">

<!-- Total Cost: reactive -->
<span x-text="'$' + (unitCost * quantity).toFixed(2)"></span>

<!-- Total Price: reactive -->
<span x-text="'$' + (unitPrice * quantity).toFixed(2)"></span>
```

### 2. **resources/js/estimate-show.js** ✅

Added `lineItemCalculator()` Alpine component:

**Methods:**
- `calculateBreakeven()` - Computes breakeven based on item type
- `calculateProfitFromPrice()` - Calculates profit % when price changes
- `calculatePriceFromProfit()` - Calculates price when profit % changes
- `recalculateFromCost()` - Triggered when unit cost changes
- `recalculateFromPrice()` - Triggered when unit price changes
- `recalculateFromProfit()` - Triggered when profit % changes
- `resetToCatalogDefaults()` - Fetches & applies catalog defaults

**Formula:**
```javascript
// Profit % = (Price - Breakeven) / Price × 100
profitPercent = ((unitPrice - breakeven) / unitPrice) * 100

// Price = Breakeven / (1 - Profit % / 100)
unitPrice = breakeven / (1 - profitPercent / 100)
```

### 3. **routes/web.php** ✅

Added API endpoint for fetching catalog defaults:

```php
Route::get('/api/catalog/{type}/{id}', function ($type, $id) {
    // Fetches labor or material catalog item
    // Calculates unit_cost, unit_price with current budget
    // Returns JSON with defaults
});
```

**Endpoints:**
- `GET /api/catalog/labor/{id}` - Returns labor item defaults
- `GET /api/catalog/material/{id}` - Returns material item defaults

**Response Format:**
```json
{
    "unit_cost": 22.50,
    "unit_price": 75.00,
    "overhead_rate": 41.62,
    "name": "Commercial Labor",
    "unit": "hour"
}
```

---

## User Workflow

### Scenario 1: Adjust Price for Negotiation

1. Customer wants a discount
2. User edits **Unit Price** from $75.00 → $65.00
3. Profit % automatically updates from 25.2% → 18.8%
4. User can see if margin is still acceptable
5. Click "Save" to persist changes

### Scenario 2: Target Specific Profit Margin

1. User wants exactly 30% profit on an item
2. Edit **Profit %** input from 25.2% → 30.0%
3. Unit Price automatically updates from $75.00 → $90.89
4. User sees new price instantly
5. Click "Save" to persist changes

### Scenario 3: Cost Changes from Supplier

1. Supplier increases material cost
2. User edits **Unit Cost** from $10.00 → $12.00
3. Breakeven updates from $10.88 → $13.05
4. Profit % automatically recalculates (price stays same, margin drops)
5. User can increase price or accept lower margin
6. Click "Save" to persist changes

### Scenario 4: Reset After Manual Edits

1. User made manual price adjustments
2. Labor rate in catalog was updated
3. Click **Reset** button on line item
4. Unit cost & price restore to catalog defaults
5. Profit % recalculates based on current budget margin
6. Toast: "Reset to catalog defaults"
7. Click "Save" to persist changes

---

## Calculation Logic

### Labor Items
```
Unit Cost (from catalog): $22.00/hr
Overhead Rate (from budget): $41.62/hr
Breakeven: $22.00 + $41.62 = $63.62

User sets Profit %: 25%
Unit Price: $63.62 / (1 - 0.25) = $84.83

OR

User sets Unit Price: $75.00
Profit %: ($75.00 - $63.62) / $75.00 × 100 = 15.2%
```

### Material Items
```
Unit Cost (from catalog): $10.00
Tax Rate: 8.75%
Breakeven: $10.00 × 1.0875 = $10.88

User sets Profit %: 30%
Unit Price: $10.88 / (1 - 0.30) = $15.54

OR

User sets Unit Price: $15.00
Profit %: ($15.00 - $10.88) / $15.00 × 100 = 27.5%
```

### Fee & Discount Items
```
Breakeven = Unit Cost (no overhead or tax)
Same calculation as materials with 0% tax
```

---

## Data Flow

### 1. Initial Page Load
```
Controller calculates overhead rate from budget
    ↓
Pass to view: $overheadRate = 41.62
    ↓
Blade renders each line item with x-data="lineItemCalculator({
    itemType: 'labor',
    unitCost: 22.00,
    unitPrice: 75.00,
    overheadRate: 41.62,
    ...
})"
    ↓
Alpine initializes component
    ↓
calculateBreakeven() → 63.62
calculateProfitFromPrice() → 15.2%
```

### 2. User Edits Price
```
User changes input → Alpine detects @input event
    ↓
recalculateFromPrice() fires
    ↓
calculateProfitFromPrice()
    ↓
profitPercent updates → UI updates via x-text
    ↓
User clicks Save → Form submits normally
```

### 3. User Edits Profit %
```
User changes profit input → Alpine detects @input event
    ↓
recalculateFromProfit() fires
    ↓
calculatePriceFromProfit()
    ↓
unitPrice updates → UI updates via x-model
    ↓
User clicks Save → Form submits normally
```

### 4. User Clicks Reset
```
User clicks Reset button → @click fires
    ↓
resetToCatalogDefaults() executes
    ↓
fetch('/api/catalog/labor/123')
    ↓
API fetches labor item + budget
API calculates default cost & price
API returns JSON
    ↓
Alpine updates unitCost & unitPrice
    ↓
recalculateFromCost() fires
    ↓
All dependent values update
    ↓
Toast notification shown
    ↓
User clicks Save to persist
```

---

## Constraints & Validation

### Profit % Limits
- **Minimum:** -99% (prevents division by zero)
- **Maximum:** 99% (99% profit is high but valid)
- Enforced in `calculatePriceFromProfit()`

```javascript
if (profitPercent >= 100) profitPercent = 99;
if (profitPercent <= -99) profitPercent = -99;
```

### Price Formula Protection
```javascript
const marginRate = profitPercent / 100;
if (marginRate < 1) {
    unitPrice = breakeven / (1 - marginRate);
} else {
    unitPrice = breakeven; // fallback if >= 100%
}
```

---

## Testing Checklist

### Basic Calculations
- [x] Labor: breakeven includes overhead
- [x] Material (taxable): breakeven includes tax
- [x] Material (non-taxable): breakeven = cost
- [x] Fee: breakeven = cost
- [x] Discount: breakeven = cost

### Reactive Updates
- [x] Edit price → profit % updates
- [x] Edit profit % → price updates
- [x] Edit cost → breakeven & profit % update
- [x] Edit quantity → totals update

### Reset Button
- [ ] Reset labor item → fetches catalog defaults
- [ ] Reset material item → fetches catalog defaults
- [ ] Reset shows toast notification
- [ ] Reset with no catalog link shows warning
- [ ] Reset handles API errors gracefully

### Edge Cases
- [ ] Profit % = 99% → price calculates correctly
- [ ] Profit % = -50% → price below breakeven (valid for discounts)
- [ ] Unit cost = 0 → breakeven = overhead only (labor)
- [ ] Unit price = 0 → profit % = 0
- [ ] Quantity = 0 → total = 0

### Save Persistence
- [ ] Changes persist after Save
- [ ] Page reload shows saved values
- [ ] Summary cards update after save
- [ ] Form validation still works

---

## Future Enhancements

### Short Term
1. **Bulk Reset:** Reset all items in an area to catalog defaults
2. **Profit % Presets:** Quick buttons for 10%, 20%, 30% profit
3. **Keyboard Shortcuts:** Tab through price/profit fields efficiently
4. **Undo/Redo:** Revert changes before saving

### Long Term
1. **Price History:** Track price changes over time
2. **Margin Warnings:** Visual alert if profit < 10%
3. **Batch Pricing:** Apply same profit % to multiple items
4. **Catalog Sync Indicator:** Show if item is out-of-sync with catalog
5. **Auto-save:** Save changes without clicking Save button

---

## API Documentation

### GET /api/catalog/{type}/{id}

Fetches catalog item defaults with current budget calculations.

**Parameters:**
- `type` (string) - "labor" or "material"
- `id` (int) - Catalog item ID

**Response (Labor):**
```json
{
    "unit_cost": 22.50,
    "unit_price": 75.83,
    "overhead_rate": 41.62,
    "name": "Commercial Labor",
    "unit": "hour"
}
```

**Response (Material):**
```json
{
    "unit_cost": 10.00,
    "unit_price": 15.54,
    "tax_rate": 0.0875,
    "name": "Mulch - Dark Brown",
    "unit": "cy"
}
```

**Errors:**
- `404` - Item not found
- `400` - Invalid type (not labor or material)

---

*Last Updated: 2025-11-24*
