<?php

namespace App\Services;

use App\Models\EstimateArea;
use App\Models\EstimateItem;
use Illuminate\Support\Facades\DB;

class PricingOverrideService
{
    /**
     * Apply a custom total price to a work area by distributing the change proportionally.
     * 
     * @param EstimateArea $area
     * @param float $targetPrice
     * @param string $method 'proportional' or 'line_item'
     * @param int|null $userId
     * @return array ['success' => bool, 'message' => string, 'details' => array]
     */
    public function applyCustomPrice(EstimateArea $area, float $targetPrice, string $method = 'proportional', ?int $userId = null): array
    {
        return DB::transaction(function () use ($area, $targetPrice, $method, $userId) {
            $items = $area->items()->whereIn('item_type', ['material', 'labor'])->get();
            
            if ($items->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No items found in this work area to adjust pricing.',
                    'details' => [],
                ];
            }
            
            // Calculate current total
            $currentTotal = $items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
            
            if ($currentTotal == 0) {
                return [
                    'success' => false,
                    'message' => 'Current total is zero. Cannot distribute pricing.',
                    'details' => [],
                ];
            }
            
            $difference = $targetPrice - $currentTotal;
            
            if ($method === 'proportional') {
                return $this->distributeProportionally($area, $items, $targetPrice, $currentTotal, $userId);
            } else {
                return $this->distributAsLineItem($area, $items, $difference, $userId);
            }
        });
    }

    /**
     * Apply a custom profit percentage to a work area by adjusting prices while maintaining costs.
     * 
     * @param EstimateArea $area
     * @param float $targetProfitPercent
     * @param string $method 'proportional' or 'line_item'
     * @param int|null $userId
     * @return array
     */
    public function applyCustomProfit(EstimateArea $area, float $targetProfitPercent, string $method = 'proportional', ?int $userId = null): array
    {
        return DB::transaction(function () use ($area, $targetProfitPercent, $method, $userId) {
            $items = $area->items()->whereIn('item_type', ['material', 'labor'])->get();
            
            if ($items->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No items found in this work area to adjust profit.',
                    'details' => [],
                ];
            }
            
            // Calculate current totals
            $currentCost = $items->sum(function ($item) {
                return $item->quantity * $item->unit_cost;
            });
            
            $currentRevenue = $items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
            
            if ($currentRevenue == 0) {
                return [
                    'success' => false,
                    'message' => 'Current revenue is zero. Cannot calculate profit.',
                    'details' => [],
                ];
            }
            
            // Calculate target price based on desired profit %
            // Profit % = (Revenue - Cost) / Revenue
            // targetProfitPercent = (targetRevenue - cost) / targetRevenue
            // targetProfitPercent * targetRevenue = targetRevenue - cost
            // cost = targetRevenue - (targetProfitPercent * targetRevenue)
            // cost = targetRevenue * (1 - targetProfitPercent)
            // targetRevenue = cost / (1 - targetProfitPercent)
            
            $targetProfitDecimal = $targetProfitPercent / 100;
            
            if ($targetProfitDecimal >= 1) {
                return [
                    'success' => false,
                    'message' => 'Profit percentage must be less than 100%.',
                    'details' => [],
                ];
            }
            
            $targetRevenue = $currentCost / (1 - $targetProfitDecimal);
            
            if ($method === 'proportional') {
                return $this->distributeProportionally($area, $items, $targetRevenue, $currentRevenue, $userId, $targetProfitPercent);
            } else {
                $difference = $targetRevenue - $currentRevenue;
                return $this->distributAsLineItem($area, $items, $difference, $userId, $targetProfitPercent);
            }
        });
    }

    /**
     * Distribute price change proportionally across all items.
     */
    protected function distributeProportionally(EstimateArea $area, $items, float $targetTotal, float $currentTotal, ?int $userId, ?float $profitPercent = null): array
    {
        $multiplier = $targetTotal / $currentTotal;
        $adjustedItems = [];
        $actualTotal = 0;
        
        foreach ($items as $item) {
            $newUnitPrice = round($item->unit_price * $multiplier, 2);
            $newLineTotal = round($item->quantity * $newUnitPrice, 2);
            
            // Update the item
            $item->update([
                'unit_price' => $newUnitPrice,
                'line_total' => $newLineTotal,
                'cost_total' => round($item->quantity * $item->unit_cost, 2),
                'margin_total' => round($newLineTotal - ($item->quantity * $item->unit_cost), 2),
            ]);
            
            $actualTotal += $newLineTotal;
            $adjustedItems[] = [
                'id' => $item->id,
                'name' => $item->name,
                'old_unit_price' => $item->getOriginal('unit_price'),
                'new_unit_price' => $newUnitPrice,
                'quantity' => $item->quantity,
            ];
        }
        
        // Handle rounding difference
        $roundingDifference = round($targetTotal - $actualTotal, 2);
        $roundingItem = null;
        
        if (abs($roundingDifference) >= 0.01) {
            // Create a rounding adjustment line item
            $roundingItem = EstimateItem::create([
                'estimate_id' => $area->estimate_id,
                'area_id' => $area->id,
                'item_type' => 'fee',
                'name' => 'Price Adjustment (Rounding)',
                'description' => 'Automatic adjustment to match custom price target',
                'quantity' => 1,
                'unit_cost' => 0,
                'unit_price' => $roundingDifference,
                'line_total' => $roundingDifference,
                'cost_total' => 0,
                'margin_total' => $roundingDifference,
                'margin_rate' => 0,
                'tax_rate' => 0,
                'source' => 'custom_pricing',
                'sort_order' => 9999,
            ]);
        }
        
        // Update area with override info
        $area->update([
            'custom_price_override' => $profitPercent === null ? $targetTotal : null,
            'custom_profit_override' => $profitPercent,
            'price_distribution_method' => 'proportional',
            'override_applied_at' => now(),
            'override_applied_by' => $userId,
        ]);
        
        // Recalculate estimate totals
        app(EstimateItemService::class)->recalculateTotals($area->estimate->fresh());
        
        return [
            'success' => true,
            'message' => 'Custom pricing applied successfully.',
            'details' => [
                'target_total' => $targetTotal,
                'actual_total' => $actualTotal + ($roundingItem ? $roundingDifference : 0),
                'rounding_adjustment' => $roundingDifference,
                'items_adjusted' => count($adjustedItems),
                'adjusted_items' => $adjustedItems,
                'rounding_item_id' => $roundingItem?->id,
            ],
        ];
    }

    /**
     * Add price change as a single line item instead of distributing.
     */
    protected function distributAsLineItem(EstimateArea $area, $items, float $difference, ?int $userId, ?float $profitPercent = null): array
    {
        $itemName = $difference > 0 
            ? 'Custom Profit Adjustment' 
            : 'Custom Price Discount';
            
        $adjustmentItem = EstimateItem::create([
            'estimate_id' => $area->estimate_id,
            'area_id' => $area->id,
            'item_type' => $difference > 0 ? 'fee' : 'discount',
            'name' => $itemName,
            'description' => $profitPercent !== null 
                ? "Adjustment to achieve {$profitPercent}% profit margin"
                : 'Custom pricing adjustment',
            'quantity' => 1,
            'unit_cost' => 0,
            'unit_price' => abs($difference),
            'line_total' => $difference,
            'cost_total' => 0,
            'margin_total' => $difference,
            'margin_rate' => 0,
            'tax_rate' => 0,
            'source' => 'custom_pricing',
            'sort_order' => 9999,
        ]);
        
        // Update area with override info
        $currentTotal = $items->sum(fn($i) => $i->quantity * $i->unit_price);
        $targetTotal = $currentTotal + $difference;
        
        $area->update([
            'custom_price_override' => $profitPercent === null ? $targetTotal : null,
            'custom_profit_override' => $profitPercent,
            'price_distribution_method' => 'line_item',
            'override_applied_at' => now(),
            'override_applied_by' => $userId,
        ]);
        
        // Recalculate estimate totals
        app(EstimateItemService::class)->recalculateTotals($area->estimate->fresh());
        
        return [
            'success' => true,
            'message' => 'Custom pricing applied as line item.',
            'details' => [
                'adjustment_amount' => $difference,
                'adjustment_item_id' => $adjustmentItem->id,
                'adjustment_item_name' => $itemName,
            ],
        ];
    }

    /**
     * Clear custom pricing from a work area and optionally remove adjustment items.
     */
    public function clearCustomPricing(EstimateArea $area, bool $removeAdjustments = true): array
    {
        return DB::transaction(function () use ($area, $removeAdjustments) {
            if ($removeAdjustments) {
                // Remove any custom pricing adjustment items
                $area->items()
                    ->where('source', 'custom_pricing')
                    ->delete();
            }
            
            $area->clearCustomPricing();
            
            // Recalculate estimate totals
            app(EstimateItemService::class)->recalculateTotals($area->estimate->fresh());
            
            return [
                'success' => true,
                'message' => 'Custom pricing cleared successfully.',
                'details' => [
                    'adjustments_removed' => $removeAdjustments,
                ],
            ];
        });
    }
}
