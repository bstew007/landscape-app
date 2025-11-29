<?php
/**
 * Test Material Catalog Integration
 * 
 * This tests:
 * 1. Material catalog API
 * 2. Creating a mulching calculator with catalog material
 * 3. Importing to estimate with catalog linkage
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Material;
use App\Models\Calculation;
use App\Models\Estimate;
use App\Services\CalculationImportService;

echo "=== MATERIAL CATALOG TEST ===\n\n";

// 1. Check materials in catalog
echo "Step 1: Checking Material Catalog...\n";
$materials = Material::where('is_active', true)
    ->where('category', 'LIKE', '%mulch%')
    ->orderBy('name')
    ->get(['id', 'name', 'sku', 'category', 'unit', 'unit_cost']);

if ($materials->count() === 0) {
    echo "⚠️  No mulch materials found in catalog!\n";
    echo "   Add some mulch materials to test the integration.\n\n";
} else {
    echo "✅ Found {$materials->count()} mulch materials:\n";
    foreach ($materials as $mat) {
        echo "   - {$mat->name} (ID: {$mat->id}) - \${$mat->unit_cost}/{$mat->unit}\n";
    }
    echo "\n";
}

// 2. Check recent mulching calculations
echo "Step 2: Checking Recent Mulching Calculations...\n";
$mulchCalcs = Calculation::where('calculation_type', 'mulching')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

if ($mulchCalcs->count() === 0) {
    echo "⚠️  No mulching calculations found.\n";
    echo "   Create a mulching calculator to test catalog integration.\n\n";
} else {
    echo "✅ Found {$mulchCalcs->count()} mulching calculations:\n";
    foreach ($mulchCalcs as $calc) {
        $materials = $calc->data['materials'] ?? [];
        $hasCatalogId = false;
        $catalogIds = [];
        
        foreach ($materials as $mat) {
            if (isset($mat['catalog_id']) && $mat['catalog_id']) {
                $hasCatalogId = true;
                $catalogIds[] = $mat['catalog_id'];
            }
        }
        
        $status = $hasCatalogId ? "✅ HAS CATALOG" : "⚠️  NO CATALOG";
        echo "   ID: {$calc->id} - {$calc->created_at->format('Y-m-d H:i')} - {$status}\n";
        
        if ($hasCatalogId) {
            echo "      Catalog IDs: " . implode(', ', $catalogIds) . "\n";
        }
    }
    echo "\n";
}

// 3. Test import with catalog linkage
if ($mulchCalcs->count() > 0 && isset($argv[1])) {
    $estimateId = $argv[1];
    $estimate = Estimate::find($estimateId);
    
    if ($estimate) {
        echo "Step 3: Testing Import to Estimate #{$estimateId}...\n";
        $calc = $mulchCalcs->first();
        
        $importService = app(CalculationImportService::class);
        
        try {
            $area = $importService->importCalculationToArea($estimate, $calc, null, [
                'area_name' => 'Mulch Test - Catalog Integration'
            ]);
            
            echo "✅ Import successful! Area ID: {$area->id}\n";
            
            // Check if materials have catalog_id
            $items = $area->items()->where('item_type', 'material')->get();
            
            if ($items->count() > 0) {
                echo "\nMaterial Items:\n";
                foreach ($items as $item) {
                    $catalogStatus = $item->catalog_id ? "✅ Linked (ID: {$item->catalog_id})" : "⚠️  Not linked";
                    echo "   - {$item->name}: {$catalogStatus}\n";
                    echo "     Cost: \${$item->unit_cost}/{$item->unit} | Total: \${$item->cost_total}\n";
                }
            }
            
        } catch (\Exception $e) {
            echo "❌ Import failed: {$e->getMessage()}\n";
        }
    } else {
        echo "❌ Estimate #{$estimateId} not found\n";
    }
} else {
    echo "Step 3: Skipped (provide estimate_id as argument to test import)\n";
    echo "   Usage: php test-material-catalog.php [estimate_id]\n";
}

echo "\n=== TEST COMPLETE ===\n";
