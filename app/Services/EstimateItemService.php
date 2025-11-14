<?php

namespace App\Services;

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Material;
use App\Models\LaborItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Services\BudgetService;

class EstimateItemService
{
    public function __construct(protected ?BudgetService $budgetService = null)
    {
        // Optional DI; if not provided, resolve on demand
        if (!$this->budgetService) {
            $this->budgetService = app(BudgetService::class);
        }
    }
    public function syncFromLegacyLineItems(Estimate $estimate, ?array $lineItems): void
    {
        if (!is_array($lineItems) || empty($lineItems)) {
            return;
        }

        DB::transaction(function () use ($estimate, $lineItems) {
            $estimate->items()->delete();

            foreach ($lineItems as $index => $legacyItem) {
                $payload = $this->mapLegacyItem($estimate->id, $legacyItem, $index);
                EstimateItem::create($payload);
            }

            $this->recalculateTotals($estimate);
        });
    }

    public function createManualItem(Estimate $estimate, array $data): EstimateItem
    {
        $financials = $this->computeFinancials($data['item_type'], $data);

        $payload = [
            'estimate_id' => $estimate->id,
            'item_type' => $data['item_type'],
            'catalog_type' => $data['catalog_type'] ?? null,
            'catalog_id' => $data['catalog_id'] ?? null,
            'calculation_id' => $data['calculation_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'unit' => $data['unit'] ?? null,
            'area_id' => $data['area_id'] ?? null,
            'quantity' => $financials['quantity'],
            'unit_cost' => $financials['unit_cost'],
            'unit_price' => $financials['unit_price'],
            'margin_rate' => $financials['margin_rate'],
            'tax_rate' => (float) ($data['tax_rate'] ?? 0),
            'line_total' => $financials['line_total'],
            'cost_total' => $financials['cost_total'],
            'margin_total' => $financials['margin_total'],
            'source' => $data['source'] ?? 'manual',
            'sort_order' => (int) ($estimate->items()->max('sort_order') ?? 0) + 1,
            'metadata' => $data['metadata'] ?? null,
        ];

        $item = EstimateItem::create($payload);
        $this->recalculateTotals($estimate->fresh());

        return $item;
    }

    public function removeCalculationItems(Estimate $estimate, int $calculationId): void
    {
        EstimateItem::where('estimate_id', $estimate->id)
            ->where('calculation_id', $calculationId)
            ->delete();

        $this->recalculateTotals($estimate->fresh());
    }

    public function updateItem(EstimateItem $item, array $data): EstimateItem
    {
        $item->fill([
            'name' => $data['name'] ?? $item->name,
            'description' => $data['description'] ?? $item->description,
            'unit' => $data['unit'] ?? $item->unit,
            'area_id' => $data['area_id'] ?? $item->area_id,
        ]);

        $financials = $this->computeFinancials($item->item_type, [
            'quantity' => $data['quantity'] ?? $item->quantity,
            'unit_cost' => $data['unit_cost'] ?? $item->unit_cost,
            'unit_price' => $data['unit_price'] ?? $item->unit_price,
            'margin_rate' => $data['margin_rate'] ?? $item->margin_rate,
        ]);

        $item->quantity = $financials['quantity'];
        $item->unit_cost = $financials['unit_cost'];
        $item->unit_price = $financials['unit_price'];
        $item->margin_rate = $financials['margin_rate'];
        $item->line_total = $financials['line_total'];
        $item->cost_total = $financials['cost_total'];
        $item->margin_total = $financials['margin_total'];
        $item->tax_rate = (float) ($data['tax_rate'] ?? $item->tax_rate);
        $item->save();

        $this->recalculateTotals($item->estimate->fresh());

        return $item;
    }

    public function deleteItem(EstimateItem $item): void
    {
        $estimate = $item->estimate;
        $item->delete();
        $this->recalculateTotals($estimate->fresh());
    }

    public function recalculateTotals(Estimate $estimate): void
    {
        $estimate->loadMissing('items');

        $items = $estimate->items;
        $materialItems = $items->where('item_type', 'material');
        $laborItems = $items->where('item_type', 'labor');
        $feeItems = $items->where('item_type', 'fee');
        $discountItems = $items->where('item_type', 'discount');

        $materialSubtotal = $materialItems->sum('line_total');
        $materialCostSubtotal = $materialItems->sum('cost_total');
        $laborSubtotal = $laborItems->sum('line_total');
        $laborCostSubtotal = $laborItems->sum('cost_total');
        $feeTotal = $feeItems->sum('line_total');
        $discountTotal = $discountItems->sum('line_total');

        $taxTotal = $items->sum(function (EstimateItem $item) {
            return $item->tax_rate > 0 ? round($item->line_total * $item->tax_rate, 2) : 0;
        });

        $revenueSubtotal = $materialSubtotal + $laborSubtotal + $feeTotal - $discountTotal;
        $costTotal = $items->sum('cost_total');
        $profitTotal = $items->sum('margin_total');
        $grandTotal = $revenueSubtotal + $taxTotal;
        $netProfitTotal = $profitTotal - $taxTotal;

        $profitMargin = $revenueSubtotal > 0 ? round(($profitTotal / $revenueSubtotal) * 100, 2) : 0;
        $netMargin = $revenueSubtotal > 0 ? round(($netProfitTotal / $revenueSubtotal) * 100, 2) : 0;

        $estimate->forceFill([
            'material_subtotal' => $materialSubtotal,
            'material_cost_total' => $materialCostSubtotal,
            'labor_subtotal' => $laborSubtotal,
            'labor_cost_total' => $laborCostSubtotal,
            'fee_total' => $feeTotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal,
            'revenue_total' => $revenueSubtotal,
            'cost_total' => $costTotal,
            'profit_total' => $profitTotal,
            'net_profit_total' => $netProfitTotal,
            'profit_margin' => $profitMargin,
            'net_margin' => $netMargin,
            'total' => $grandTotal,
        ])->saveQuietly();
    }

    protected function computeFinancials(string $itemType, array $data): array
    {
        $quantity = max(0, (float) Arr::get($data, 'quantity', 0));
        $unitCost = max(0, (float) Arr::get($data, 'unit_cost', 0));
        $marginRateInput = Arr::get($data, 'margin_rate');
        $unitPriceInput = Arr::get($data, 'unit_price');

        $marginRate = is_numeric($marginRateInput) ? (float) $marginRateInput : null;
        $unitPrice = is_numeric($unitPriceInput) ? (float) $unitPriceInput : null;

        if ($itemType === 'discount') {
            $unitPrice = $unitPrice ?? $unitCost;
            $unitPrice = max(0, $unitPrice);
            $lineTotal = round($quantity * $unitPrice, 2);

            return [
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'unit_price' => $unitPrice,
                'margin_rate' => 0.0,
                'line_total' => $lineTotal,
                'cost_total' => 0.0,
                'margin_total' => -$lineTotal,
            ];
        }

        if ($unitPrice !== null) {
            $unitPrice = max(0, $unitPrice);
            if ($unitCost > 0) {
                $marginRate = ($unitPrice - $unitCost) / $unitCost;
            } elseif ($unitPrice === 0.0) {
                $marginRate = 0.0;
            } else {
                $marginRate = $marginRate ?? 0.0;
            }
        } elseif ($marginRate !== null) {
            $unitPrice = $unitCost * (1 + $marginRate);
        } else {
            $marginRate = 0.0;
            $unitPrice = $unitCost;
        }

        if ($unitPrice === null) {
            $unitPrice = $unitCost;
        }

        if ($marginRate === null && $unitCost > 0) {
            $marginRate = ($unitPrice - $unitCost) / $unitCost;
        }

        $marginRate = $marginRate ?? 0.0;
        $marginRate = max(-0.99, min(10, $marginRate));

        $unitPrice = round(max(0, $unitPrice), 2);
        $lineTotal = round($quantity * $unitPrice, 2);
        $costTotal = round($quantity * $unitCost, 2);
        $marginTotal = round($lineTotal - $costTotal, 2);

        return [
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'unit_price' => $unitPrice,
            'margin_rate' => $marginRate,
            'line_total' => $lineTotal,
            'cost_total' => $costTotal,
            'margin_total' => $marginTotal,
        ];
    }

    public function resolveCatalogDefaults(string $catalogType, int $catalogId): array
    {
        // Use active budget to prefill default margin or labor charge rate when applicable
        $activeBudget = $this->budgetService?->active();
        $defaultMargin = (float) (($activeBudget?->desired_profit_margin) ?? 0.2);
        $recommended = $activeBudget ? $this->budgetService->recommendedRates($activeBudget) : null;
        $laborChargeRate = (float) ($recommended['charge_out_rate'] ?? 0);
        $laborBurdenCost = (float) (data_get($activeBudget?->outputs, 'labor.blc', 0));

        if ($catalogType === 'material') {
            $material = Material::find($catalogId);
            if (!$material) return [];
            return [
                'name' => $material->name,
                'unit' => $material->unit,
                'unit_cost' => $material->unit_cost,
                'unit_price' => round($material->unit_cost * (1 + $defaultMargin), 2),
                'margin_rate' => $defaultMargin,
                'tax_rate' => $material->is_taxable ? $material->tax_rate : 0,
                'description' => $material->description,
                'catalog_type' => Material::class,
            ];
        }

        if ($catalogType === 'labor') {
            $labor = LaborItem::find($catalogId);
            if (!$labor) return [];
            // Prefer budget-derived burdened cost and charge-out rate
            $unitCost = $laborBurdenCost ?: $labor->base_rate;
            $unitPrice = $laborChargeRate ?: round($unitCost * (1 + $defaultMargin), 2);
            $marginRate = $unitCost > 0 ? (($unitPrice - $unitCost) / $unitCost) : $defaultMargin;

            return [
                'name' => $labor->name,
                'unit' => $labor->unit,
                'unit_cost' => $unitCost,
                'unit_price' => $unitPrice,
                'margin_rate' => $marginRate,
                'tax_rate' => 0,
                'description' => $labor->notes,
                'catalog_type' => LaborItem::class,
            ];
        }

        return [];
    }

    protected function mapLegacyItem(int $estimateId, array $legacyItem, int $index): array
    {
        $qty = (float) ($legacyItem['qty'] ?? $legacyItem['quantity'] ?? 1);
        $rate = (float) ($legacyItem['rate'] ?? $legacyItem['unit_cost'] ?? 0);
        $unitCost = (float) ($legacyItem['cost'] ?? $rate);
        $total = (float) ($legacyItem['total'] ?? ($qty * $rate));
        $type = $legacyItem['type'] ?? $legacyItem['item_type'] ?? 'material';

        $unitPrice = (float) ($legacyItem['unit_price'] ?? $legacyItem['price'] ?? $rate);
        if ($qty > 0 && Arr::has($legacyItem, 'total')) {
            $unitPrice = $total / max($qty, 1);
        }

        $financials = $this->computeFinancials($type, [
            'quantity' => $qty,
            'unit_cost' => $unitCost,
            'unit_price' => $unitPrice,
            'margin_rate' => $legacyItem['margin_rate'] ?? $legacyItem['margin'] ?? null,
        ]);

        return [
            'estimate_id' => $estimateId,
            'item_type' => $type,
            'catalog_type' => null,
            'catalog_id' => null,
            'name' => $legacyItem['label'] ?? $legacyItem['name'] ?? 'Line Item',
            'description' => $legacyItem['description'] ?? null,
            'unit' => $legacyItem['unit'] ?? null,
            'quantity' => $financials['quantity'],
            'unit_cost' => $financials['unit_cost'],
            'unit_price' => $financials['unit_price'],
            'margin_rate' => $financials['margin_rate'],
            'tax_rate' => (float) ($legacyItem['tax_rate'] ?? 0),
            'line_total' => $financials['line_total'],
            'cost_total' => $financials['cost_total'],
            'margin_total' => $financials['margin_total'],
            'source' => $legacyItem['source'] ?? 'legacy',
            'sort_order' => $index,
            'metadata' => $legacyItem,
        ];
    }
}
