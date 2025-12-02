# Custom Pricing Feature for Work Areas

## Overview

This feature allows users to override the total price or profit percentage for work areas in estimates. When a custom price or profit is set, the system automatically distributes the adjustment across line items, maintaining audit trails and data integrity.

## Key Features

### 1. **Two Override Modes**
- **Custom Total Price**: Set a specific dollar amount for the work area total
- **Custom Profit %**: Set a target profit percentage (the system calculates the required price)

### 2. **Two Distribution Methods**

#### Proportional Distribution (Default)
- Spreads the price change across all items based on their current prices
- Maintains relative proportions of the estimate
- Each item's unit price is adjusted by the same multiplier
- Automatic rounding adjustment line item added if needed (for penny differences)

#### Single Adjustment Line Item
- Keeps all items at their catalog prices
- Adds one "Custom Profit Adjustment" or "Custom Price Discount" line item
- Easier to understand and reverse
- Shows exactly how much was added/removed

### 3. **Visual Indicators**
- Amber "Custom" badge appears next to the Price in the work area summary
- Icon shows whether it's a price override (dollar sign) or profit override (chart)
- Tooltip displays when the override was applied
- Clear visual feedback that pricing is no longer catalog-based

### 4. **Data Tracking**
Each work area tracks:
- `custom_price_override`: The target price (if price mode was used)
- `custom_profit_override`: The target profit % (if profit mode was used)
- `price_distribution_method`: How the change was applied ('proportional' or 'line_item')
- `override_applied_at`: Timestamp of when override was set
- `override_applied_by`: User ID who applied the override

### 5. **Easy Reversal**
- "Clear Custom Pricing" option in the work area menu (only shows when active)
- Removes adjustment line items created by the system
- Restores items to their previous state
- Recalculates estimate totals automatically

## User Interface

### Accessing Custom Pricing
1. Open an estimate
2. Find the work area you want to adjust
3. Click the **Options** button (three dots)
4. Select either:
   - **Custom Total Price** (dollar icon)
   - **Custom Profit %** (chart icon)

### The Modal Interface
The modal shows:
- **Current Values**: Total Price, Total Cost, Current Profit Margin (color-coded)
- **Input Field**: Enter target price or profit percentage
- **Distribution Method**: Radio buttons to choose proportional vs. line item
- **Preview Section**: Shows what the new total and profit will be
- **Warning**: Reminds user this overrides catalog pricing
- **Actions**: Cancel or Apply Custom Pricing buttons

### Live Calculations
The modal provides real-time preview as you type:
- If setting custom price → shows what profit % you'll get
- If setting custom profit % → shows what price is required
- Shows the dollar amount of adjustment (positive or negative)

## Technical Architecture

### Service Layer: `PricingOverrideService`

Located at: `app/Services/PricingOverrideService.php`

**Methods:**
- `applyCustomPrice($area, $targetPrice, $method, $userId)` - Set custom total price
- `applyCustomProfit($area, $targetProfitPercent, $method, $userId)` - Set custom profit %
- `clearCustomPricing($area, $removeAdjustments)` - Remove custom pricing

**The service handles:**
- Calculating price multipliers
- Distributing changes across items
- Creating rounding adjustment items when needed
- Updating work area override fields
- Triggering estimate total recalculation
- Transaction safety (all changes are atomic)

### Controller: `EstimateAreaController`

**New Endpoints:**
```php
POST /estimates/{estimate}/areas/{area}/custom-price
POST /estimates/{estimate}/areas/{area}/custom-profit
POST /estimates/{estimate}/areas/{area}/clear-custom-pricing
```

**Request Validation:**
- `value`: Required numeric (price or percentage)
- `method`: Required, must be 'proportional' or 'line_item'

**Response:**
```json
{
  "success": true,
  "message": "Custom pricing applied successfully.",
  "details": {
    "target_total": 5000.00,
    "actual_total": 5000.00,
    "rounding_adjustment": 0.00,
    "items_adjusted": 15,
    "adjusted_items": [...],
    "rounding_item_id": null
  }
}
```

### Frontend: Alpine.js Component

Located in: `resources/views/estimates/partials/modals/_custom-pricing.blade.php`

**Component State:**
- `mode`: 'price' or 'profit'
- `targetValue`: User's input
- `distributionMethod`: 'proportional' or 'line_item'
- `currentTotal`, `currentCost`, `currentProfit`: Work area data

**Methods:**
- `openModal(data)` - Initialize and show modal
- `closeModal()` - Hide and reset modal
- `calculateNewProfit()` - Preview profit from target price
- `calculateNewPrice()` - Preview price from target profit
- `submitOverride()` - Send AJAX request to apply changes

### Database Schema

**Migration:** `2025_11_29_021334_add_custom_pricing_to_estimate_areas_table.php`

**Added Columns to `estimate_areas`:**
```sql
custom_price_override      DECIMAL(12,2) NULL
custom_profit_override     DECIMAL(8,2)  NULL
price_distribution_method  VARCHAR(50)   NULL DEFAULT 'proportional'
override_applied_at        TIMESTAMP     NULL
override_applied_by        BIGINT UNSIGNED NULL (FK to users.id)
```

**Model Methods (`EstimateArea`):**
- `hasCustomPricing()` - Returns true if any override is active
- `clearCustomPricing()` - Clears all override fields
- `overrideAppliedBy()` - Relationship to User model

## Mathematical Logic

### Proportional Distribution for Custom Price

```
multiplier = target_price / current_total

For each item:
  new_unit_price = old_unit_price × multiplier
  new_line_total = quantity × new_unit_price (rounded to 2 decimals)

If sum(new_line_totals) ≠ target_price (due to rounding):
  Create adjustment line item for the difference
```

### Proportional Distribution for Custom Profit %

```
profit_decimal = target_profit_percent / 100
target_revenue = current_cost / (1 - profit_decimal)
multiplier = target_revenue / current_revenue

Then same as custom price distribution above
```

### Line Item Method

```
difference = target_price - current_price

Create one line item:
  type: 'fee' if positive, 'discount' if negative
  unit_price: abs(difference)
  line_total: difference
  source: 'custom_pricing'
```

## Edge Cases Handled

1. **Empty Work Area**: Returns error if no items to adjust
2. **Zero Current Total**: Returns error (can't calculate multiplier)
3. **Profit ≥ 100%**: Validation error (mathematically impossible)
4. **Rounding Differences**: Automatic adjustment line item ensures exact match
5. **Item Below Cost**: Proportional method may push items below cost (consider adding warning)
6. **Concurrent Updates**: Database transactions ensure atomicity

## Best Practices

### When to Use Proportional Distribution
- Client budget is fixed at a specific number
- Want to maintain the "shape" of the estimate
- All items should share in the discount/markup
- Professional appearance (no obvious adjustment line)

### When to Use Line Item Distribution
- Want transparency about the adjustment
- Easier to explain to client
- Quick to reverse or modify
- Catalog prices remain unchanged

### Managing Custom Pricing
1. **Always check the preview** before applying
2. **Use the badge** to remember which areas have custom pricing
3. **Document in area description** why pricing was customized
4. **Clear custom pricing** before making major estimate changes
5. **Review custom areas** if catalog prices change

## Future Enhancements (Ideas)

1. **Minimum Margin Protection**: Warn if profit % falls below company minimum
2. **Approval Workflow**: Require manager approval for large discounts
3. **Price Lock**: Option to lock specific items from distribution
4. **History Log**: Track all pricing changes with before/after values
5. **Bulk Operations**: Apply custom pricing to multiple areas at once
6. **Templates**: Save common pricing adjustments as presets
7. **Comparison View**: Show catalog vs. custom pricing side-by-side
8. **Export Report**: Generate pricing override report for management

## Testing Checklist

- [ ] Open modal from work area options menu
- [ ] Enter custom total price - verify preview calculations
- [ ] Apply proportional distribution - check all items updated
- [ ] Verify rounding adjustment created if needed
- [ ] Check custom badge appears in area summary
- [ ] Apply custom profit % - verify price calculated correctly
- [ ] Use line item distribution - verify single adjustment created
- [ ] Clear custom pricing - verify items restore and badge disappears
- [ ] Verify estimate totals recalculate correctly
- [ ] Test with empty work area - should show error
- [ ] Test with zero current total - should show error
- [ ] Test profit > 99% - should show validation error
- [ ] Verify override tracking fields populated
- [ ] Check tooltip shows correct timestamp
- [ ] Test multiple work areas with different overrides

## Files Modified/Created

### Created Files
- `database/migrations/2025_11_29_021334_add_custom_pricing_to_estimate_areas_table.php`
- `app/Services/PricingOverrideService.php`
- `resources/views/estimates/partials/modals/_custom-pricing.blade.php`

### Modified Files
- `app/Models/EstimateArea.php` - Added fillable fields, casts, methods
- `app/Http/Controllers/EstimateAreaController.php` - Added 3 new methods
- `routes/web.php` - Added 3 new routes
- `resources/views/estimates/partials/area.blade.php` - Added menu options, badge, clearCustomPricing method
- `resources/views/estimates/show.blade.php` - Included modal partial

## Support & Troubleshooting

### Common Issues

**Modal doesn't open:**
- Check browser console for JavaScript errors
- Verify Alpine.js is loaded
- Check CSRF token is present in page

**Pricing doesn't update:**
- Check network tab for API errors
- Verify user has permission to edit estimates
- Check database connection

**Wrong calculations:**
- Verify current COGS and Price are correct
- Check for items with zero prices
- Review overhead rates and tax settings

**Can't clear custom pricing:**
- Check if adjustment items were manually deleted
- Verify work area still exists
- Check database foreign key constraints

## API Documentation

### Apply Custom Price
```
POST /estimates/{estimate}/areas/{area}/custom-price

Headers:
  Content-Type: application/json
  X-CSRF-TOKEN: {token}

Body:
{
  "value": 5000.00,
  "method": "proportional"
}

Response: 200 OK
{
  "success": true,
  "message": "Custom pricing applied successfully.",
  "details": { ... }
}
```

### Apply Custom Profit
```
POST /estimates/{estimate}/areas/{area}/custom-profit

Body:
{
  "value": 25.5,
  "method": "line_item"
}
```

### Clear Custom Pricing
```
POST /estimates/{estimate}/areas/{area}/clear-custom-pricing

Response:
{
  "success": true,
  "message": "Custom pricing cleared successfully."
}
```

---

**Feature implemented:** November 28, 2025
**Version:** 1.0
**Status:** ✅ Complete and ready for testing
