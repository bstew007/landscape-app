<?php

namespace App\Services;

use App\Models\Estimate;
use App\Models\EstimatePurchaseOrder;
use App\Models\EstimatePurchaseOrderItem;
use App\Models\Material;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderService
{
    protected MaterialMatchingService $matchingService;
    protected QboPurchaseOrderService $qboService;

    public function __construct(
        MaterialMatchingService $matchingService,
        QboPurchaseOrderService $qboService
    ) {
        $this->matchingService = $matchingService;
        $this->qboService = $qboService;
    }
    /**
     * Generate purchase orders from an estimate.
     * Groups material items by supplier and creates a PO for each supplier.
     *
     * @param Estimate $estimate
     * @param bool $replaceExisting If true, delete existing draft POs first
     * @return Collection Collection of created EstimatePurchaseOrder models
     */
    public function generatePOsFromEstimate(Estimate $estimate, bool $replaceExisting = false): Collection
    {
        return DB::transaction(function () use ($estimate, $replaceExisting) {
            // Delete existing draft POs if requested
            if ($replaceExisting) {
                $estimate->purchaseOrders()->draft()->delete();
            }

            // Get all material items from the estimate
            $materialItems = $estimate->items()
                ->where('item_type', 'material')
                ->with('material')
                ->get();

            if ($materialItems->isEmpty()) {
                return collect();
            }

            // Group materials by supplier
            $itemsBySupplier = $this->groupItemsBySupplier($materialItems);

            // Create a PO for each supplier
            $purchaseOrders = collect();

            foreach ($itemsBySupplier as $supplierId => $items) {
                $po = $this->createPurchaseOrder($estimate, $supplierId, $items);
                $purchaseOrders->push($po);
            }

            return $purchaseOrders;
        });
    }

    /**
     * Group estimate items by their material's supplier.
     * Uses MaterialMatchingService to automatically match items without catalog_id.
     *
     * @param Collection $materialItems
     * @return Collection Keyed by supplier_id with additional metadata
     */
    protected function groupItemsBySupplier(Collection $materialItems): Collection
    {
        $grouped = collect();
        $matchLog = []; // Track matching results for logging

        foreach ($materialItems as $item) {
            $supplierId = null;
            $matchedMaterial = null;
            $matchScore = null;

            // Strategy 1: Use existing catalog link (highest priority)
            if ($item->catalog_type === 'material' && $item->catalog_id && $item->material) {
                $supplierId = $item->material->supplier_id;
                $matchedMaterial = $item->material;
                $matchScore = 100; // Perfect match via catalog
                $matchLog[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'strategy' => 'catalog_link',
                    'matched_material' => $matchedMaterial->name,
                    'supplier_id' => $supplierId,
                    'score' => 100,
                ];
            } 
            // Strategy 2: Attempt fuzzy matching for items without catalog link
            else {
                $matchResult = $this->matchingService->findBestMatch($item);
                
                if ($matchResult) {
                    $matchedMaterial = $matchResult['material'];
                    $supplierId = $matchedMaterial->supplier_id;
                    $matchScore = $matchResult['score'];
                    
                    $matchLog[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'strategy' => 'fuzzy_match',
                        'matched_material' => $matchedMaterial->name,
                        'supplier_id' => $supplierId,
                        'score' => $matchScore,
                        'confidence' => $matchResult['confidence'],
                    ];
                } else {
                    // No match found
                    $matchLog[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'strategy' => 'no_match',
                        'matched_material' => null,
                        'supplier_id' => null,
                        'score' => 0,
                    ];
                }
            }

            // Group by supplier_id (null for items without supplier)
            $key = $supplierId ?? 'no_supplier';

            if (!$grouped->has($key)) {
                $grouped[$key] = collect();
            }

            // Add item with matching metadata
            $grouped[$key]->push([
                'item' => $item,
                'matched_material' => $matchedMaterial,
                'match_score' => $matchScore,
            ]);
        }

        // Log matching results for debugging/auditing
        if (!empty($matchLog)) {
            Log::info('PO Material Matching Results', [
                'total_items' => $materialItems->count(),
                'catalog_links' => collect($matchLog)->where('strategy', 'catalog_link')->count(),
                'fuzzy_matches' => collect($matchLog)->where('strategy', 'fuzzy_match')->count(),
                'no_matches' => collect($matchLog)->where('strategy', 'no_match')->count(),
                'details' => $matchLog,
            ]);
        }

        return $grouped;
    }

    /**
     * Create a purchase order for a supplier with the given items.
     *
     * @param Estimate $estimate
     * @param int|string|null $supplierId
     * @param Collection $itemsWithMetadata Collection of ['item' => EstimateItem, 'matched_material' => Material|null, 'match_score' => int|null]
     * @return EstimatePurchaseOrder
     */
    protected function createPurchaseOrder(Estimate $estimate, $supplierId, Collection $itemsWithMetadata): EstimatePurchaseOrder
    {
        // Generate PO number
        $poNumber = $this->generatePONumber();

        // Create the purchase order
        $po = EstimatePurchaseOrder::create([
            'estimate_id' => $estimate->id,
            'supplier_id' => $supplierId === 'no_supplier' ? null : $supplierId,
            'po_number' => $poNumber,
            'status' => 'draft',
            'total_amount' => 0, // Will be calculated from items
        ]);

        // Create PO items
        foreach ($itemsWithMetadata as $itemData) {
            $item = $itemData['item'];
            $matchedMaterial = $itemData['matched_material'];
            
            // Use matched material if available, otherwise fall back to catalog_id
            $materialId = $matchedMaterial?->id ?? ($item->catalog_type === 'material' ? $item->catalog_id : null);

            EstimatePurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'estimate_item_id' => $item->id,
                'material_id' => $materialId,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
                'total_cost' => $item->quantity * $item->unit_cost,
            ]);
        }

        // The total will be auto-calculated via model events
        $po->refresh();

        return $po;
    }

    /**
     * Generate a unique PO number in format: PO-YYYY-0001
     *
     * @return string
     */
    protected function generatePONumber(): string
    {
        $year = date('Y');
        $prefix = "PO-{$year}-";

        // Get the last PO number for this year
        $lastPO = EstimatePurchaseOrder::where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPO) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastPO->po_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update purchase order status.
     *
     * @param EstimatePurchaseOrder $po
     * @param string $status
     * @return EstimatePurchaseOrder
     */
    public function updateStatus(EstimatePurchaseOrder $po, string $status): EstimatePurchaseOrder
    {
        $po->update(['status' => $status]);
        return $po->fresh();
    }

    /**
     * Delete a purchase order and optionally remove it from QuickBooks.
     *
     * @param EstimatePurchaseOrder $po
     * @return bool
     */
    public function deletePurchaseOrder(EstimatePurchaseOrder $po): bool
    {
        // If PO is synced to QuickBooks, delete it there first
        if ($po->qbo_id) {
            try {
                $result = $this->qboService->deletePurchaseOrder($po);
                if (!$result['success']) {
                    Log::warning('Failed to delete PO from QuickBooks', [
                        'po_id' => $po->id,
                        'qbo_id' => $po->qbo_id,
                        'error' => $result['message']
                    ]);
                    // Continue with local deletion even if QBO delete fails
                }
            } catch (\Exception $e) {
                Log::error('Error deleting PO from QuickBooks', [
                    'po_id' => $po->id,
                    'qbo_id' => $po->qbo_id,
                    'error' => $e->getMessage()
                ]);
                // Continue with local deletion even if QBO delete fails
            }
        }

        return $po->delete();
    }

    /**
     * Get all purchase orders for an estimate.
     *
     * @param Estimate $estimate
     * @return Collection
     */
    public function getPurchaseOrdersForEstimate(Estimate $estimate): Collection
    {
        return $estimate->purchaseOrders()
            ->with(['supplier', 'items.material', 'items.estimateItem'])
            ->orderBy('po_number')
            ->get();
    }
}
