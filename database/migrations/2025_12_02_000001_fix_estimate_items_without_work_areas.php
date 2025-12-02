<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Estimate;
use App\Models\EstimateArea;
use App\Models\EstimateItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes estimates that have items but no work areas
     * by creating a default work area and assigning orphaned items to it.
     */
    public function up(): void
    {
        // Find all estimates with items but no work areas
        $estimatesNeedingFix = Estimate::has('items')
            ->doesntHave('areas')
            ->with('items')
            ->get();

        foreach ($estimatesNeedingFix as $estimate) {
            // Create a default work area for this estimate
            $area = EstimateArea::create([
                'estimate_id' => $estimate->id,
                'name' => 'General Work Area',
                'identifier' => 'GEN-001',
                'description' => 'Auto-created work area for legacy estimate items',
                'status' => 'active',
                'sort_order' => 0,
            ]);

            // Assign all items without an area_id to this work area
            EstimateItem::where('estimate_id', $estimate->id)
                ->whereNull('area_id')
                ->update(['area_id' => $area->id]);

            echo "Fixed Estimate #{$estimate->id}: Created work area '{$area->name}' and assigned {$estimate->items->count()} items\n";
        }

        // Also fix any orphaned items (items with area_id but area doesn't exist)
        $orphanedItems = EstimateItem::whereNotNull('area_id')
            ->whereDoesntHave('area')
            ->with('estimate')
            ->get();

        foreach ($orphanedItems as $item) {
            // Try to find or create a work area for this item
            $area = EstimateArea::where('estimate_id', $item->estimate_id)
                ->orderBy('sort_order')
                ->first();

            if (!$area) {
                // Create a default work area
                $area = EstimateArea::create([
                    'estimate_id' => $item->estimate_id,
                    'name' => 'General Work Area',
                    'identifier' => 'GEN-001',
                    'description' => 'Auto-created work area for orphaned items',
                    'status' => 'active',
                    'sort_order' => 0,
                ]);
            }

            $item->update(['area_id' => $area->id]);
            echo "Fixed orphaned item #{$item->id}: Assigned to area #{$area->id}\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove auto-created work areas
        EstimateArea::where('name', 'General Work Area')
            ->where('description', 'like', 'Auto-created work area for%')
            ->delete();
    }
};
