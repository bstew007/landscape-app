<?php

namespace App\Services;

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Material;
use App\Models\LaborItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EstimateItemService
{
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
        $payload = [
            'estimate_id' => $estimate->id,
            'item_type' => $data['item_type'],
            'catalog_type' => $data['catalog_type'] ?? null,
            'catalog_id' => $data['catalog_id'] ?? null,
            'calculation_id' => $data['calculation_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'unit' => $data['unit'] ?? null,
            'quantity' => (float) $data['quantity'],
            'unit_cost' => (float) $data['unit_cost'],
            'tax_rate' => (float) ($data['tax_rate'] ?? 0),
            'line_total' => round($data['quantity'] * $data['unit_cost'], 2),
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
            'quantity' => $data['quantity'] ?? $item->quantity,
            'unit_cost' => $data['unit_cost'] ?? $item->unit_cost,
            'tax_rate' => $data['tax_rate'] ?? $item->tax_rate,
        ]);

        $item->line_total = round($item->quantity * $item->unit_cost, 2);
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

        $materialSubtotal = $estimate->items->where('item_type', 'material')->sum('line_total');
        $laborSubtotal = $estimate->items->where('item_type', 'labor')->sum('line_total');
        $feeTotal = $estimate->items->where('item_type', 'fee')->sum('line_total');
        $discountTotal = $estimate->items->where('item_type', 'discount')->sum('line_total');
        $taxTotal = $estimate->items->sum(function (EstimateItem $item) {
            return $item->tax_rate > 0 ? round($item->line_total * $item->tax_rate, 2) : 0;
        });

        $grandTotal = $materialSubtotal + $laborSubtotal + $feeTotal - $discountTotal + $taxTotal;

        $estimate->forceFill([
            'material_subtotal' => $materialSubtotal,
            'labor_subtotal' => $laborSubtotal,
            'fee_total' => $feeTotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal,
            'total' => $grandTotal,
        ])->saveQuietly();
    }

    public function resolveCatalogDefaults(string $catalogType, int $catalogId): array
    {
        if ($catalogType === 'material') {
            $material = Material::find($catalogId);
            if (!$material) return [];
            return [
                'name' => $material->name,
                'unit' => $material->unit,
                'unit_cost' => $material->unit_cost,
                'tax_rate' => $material->is_taxable ? $material->tax_rate : 0,
                'description' => $material->description,
                'catalog_type' => Material::class,
            ];
        }

        if ($catalogType === 'labor') {
            $labor = LaborItem::find($catalogId);
            if (!$labor) return [];
            return [
                'name' => $labor->name,
                'unit' => $labor->unit,
                'unit_cost' => $labor->base_rate,
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
        $total = (float) ($legacyItem['total'] ?? ($qty * $rate));
        $type = $legacyItem['type'] ?? $legacyItem['item_type'] ?? 'material';

        return [
            'estimate_id' => $estimateId,
            'item_type' => $type,
            'catalog_type' => null,
            'catalog_id' => null,
            'name' => $legacyItem['label'] ?? $legacyItem['name'] ?? 'Line Item',
            'description' => $legacyItem['description'] ?? null,
            'unit' => $legacyItem['unit'] ?? null,
            'quantity' => $qty,
            'unit_cost' => $rate,
            'tax_rate' => (float) ($legacyItem['tax_rate'] ?? 0),
            'line_total' => $total,
            'source' => $legacyItem['source'] ?? 'legacy',
            'sort_order' => $index,
            'metadata' => $legacyItem,
        ];
    }
}
