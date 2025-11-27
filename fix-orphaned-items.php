<?php
/**
 * Clean up orphaned estimate items
 * 
 * This script finds and deletes estimate items that reference non-existent work areas
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateArea;
use Illuminate\Support\Facades\DB;

echo "==============================================\n";
echo "ORPHANED ESTIMATE ITEMS CLEANUP\n";
echo "==============================================\n\n";

// Get all estimates
$estimates = Estimate::all();

$totalOrphaned = 0;
$totalFixed = 0;

foreach ($estimates as $estimate) {
    echo "Checking Estimate #{$estimate->id} - {$estimate->title}\n";
    
    // Get valid area IDs for this estimate
    $validAreaIds = $estimate->areas()->pluck('id')->toArray();
    
    // Find orphaned items (items with area_id not in valid areas)
    $orphanedItems = EstimateItem::where('estimate_id', $estimate->id)
        ->whereNotNull('area_id')
        ->whereNotIn('area_id', $validAreaIds)
        ->get();
    
    if ($orphanedItems->count() > 0) {
        echo "  Found {$orphanedItems->count()} orphaned items:\n";
        
        foreach ($orphanedItems as $item) {
            echo "    - {$item->name} (Area ID: {$item->area_id} - DOES NOT EXIST)\n";
        }
        
        // Delete orphaned items
        $deleted = EstimateItem::where('estimate_id', $estimate->id)
            ->whereNotNull('area_id')
            ->whereNotIn('area_id', $validAreaIds)
            ->delete();
        
        echo "  ✓ Deleted {$deleted} orphaned items\n";
        
        // Recalculate estimate totals
        $estimate->recalculate();
        echo "  ✓ Recalculated estimate totals\n";
        
        $totalOrphaned += $orphanedItems->count();
        $totalFixed++;
    } else {
        echo "  ✓ No orphaned items\n";
    }
    
    echo "\n";
}

echo "==============================================\n";
echo "SUMMARY\n";
echo "==============================================\n";
echo "Total estimates checked: {$estimates->count()}\n";
echo "Estimates with orphaned items: {$totalFixed}\n";
echo "Total orphaned items deleted: {$totalOrphaned}\n";
echo "\nCleanup complete!\n";
