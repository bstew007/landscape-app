<?php
/**
 * Full End-to-End Test: Material Catalog Integration
 * 
 * Tests the complete flow:
 * 1. Create mulching calculator with catalog material
 * 2. Import to estimate
 * 3. Verify catalog_id is linked
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Calculation;
use App\Models\Estimate;
use App\Models\Material;
use App\Services\CalculationImportService;

$calculationId = $argv[1] ?? null;
$estimateId = $argv[2] ?? null;

if (!$calculationId || !$estimateId) {
    echo "Usage: php test-full-flow.php [calculation_id] [estimate_id]\n\n";
    
    echo "Recent Mulching Calculations:\n";
    $calcs = Calculation::where('calculation_type', 'mulching')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get(['id', 'created_at']);
    foreach ($calcs as $c) {
        echo "  Calculation ID: {$c->id} - {$c->created_at->format('Y-m-d H:i')}\n";
    }
    
    echo "\nRecent Estimates:\n";
    $estimates = Estimate::orderBy('created_at', 'desc')->take(5)->get(['id', 'name', 'created_at']);
    foreach ($estimates as $e) {
        echo "  Estimate ID: {$e->id} - {$e->name} - {$e->created_at->format('Y-m-d H:i')}\n";
    }
    
    exit(1);
}

echo "=== FULL FLOW TEST ===\n\n";

// 1. Check the calculation
$calculation = Calculation::find($calculationId);
if (!$calculation) {
    echo "❌ Calculation #{$calculationId} not found\n";
    exit(1);
}

echo "Step 1: Analyzing Calculation #{$calculationId}\n";
echo "  Type: {$calculation->calculation_type}\n";
echo "  Created: {$calculation->created_at->format('Y-m-d H:i:s')}\n\n";

$data = $calculation->data;

// Check for materials
if (empty($data['materials'])) {
    echo "⚠️  No materials found in calculation\n\n";
} else {
    echo "Materials in Calculation:\n";
    foreach ($data['materials'] as $mat) {
        $catalogId = $mat['catalog_id'] ?? null;
        $name = $mat['name'] ?? 'Unknown';
        $qty = $mat['quantity'] ?? 0;
        $unit = $mat['unit'] ?? 'ea';
        $cost = $mat['unit_cost'] ?? 0;
        
        if ($catalogId) {
            $catalogMaterial = Material::find($catalogId);
            echo "  ✅ {$name}\n";
            echo "     Quantity: {$qty} {$unit}\n";
            echo "     Unit Cost: \${$cost}\n";
            echo "     🔗 Catalog Link: ID {$catalogId} ({$catalogMaterial->name})\n";
            echo "     Catalog Price: \${$catalogMaterial->unit_cost}/{$catalogMaterial->unit}\n\n";
        } else {
            echo "  ⚠️  {$name}\n";
            echo "     Quantity: {$qty} {$unit}\n";
            echo "     Unit Cost: \${$cost}\n";
            echo "     ⚠️  NO CATALOG LINK\n\n";
        }
    }
}

// Check for enhanced labor format
$hasEnhancedFormat = isset($data['labor_tasks']) && count($data['labor_tasks']) > 0;
echo "Enhanced Labor Format: " . ($hasEnhancedFormat ? "✅ YES (" . count($data['labor_tasks']) . " tasks)" : "⚠️  NO") . "\n\n";

// 2. Import to estimate
$estimate = Estimate::find($estimateId);
if (!$estimate) {
    echo "❌ Estimate #{$estimateId} not found\n";
    exit(1);
}

echo "Step 2: Importing to Estimate #{$estimateId} ({$estimate->name})\n";

$importService = app(CalculationImportService::class);

try {
    $area = $importService->importCalculationToArea($estimate, $calculation, null, [
        'area_name' => 'E2E Test - Mulching with Catalog'
    ]);
    
    echo "✅ Import Successful!\n";
    echo "   Work Area ID: {$area->id}\n";
    echo "   Area Name: {$area->name}\n\n";
    
    // 3. Verify imported items
    echo "Step 3: Verifying Imported Items\n\n";
    
    $laborItems = $area->items()->where('item_type', 'labor')->get();
    $materialItems = $area->items()->where('item_type', 'material')->get();
    
    echo "LABOR ITEMS ({$laborItems->count()}):\n";
    foreach ($laborItems as $item) {
        echo "  • {$item->name}\n";
        echo "    Qty: {$item->quantity} {$item->unit} × \${$item->unit_cost} = \${$item->cost_total}\n";
        if ($item->description) {
            echo "    Description: {$item->description}\n";
        }
        echo "\n";
    }
    
    echo "MATERIAL ITEMS ({$materialItems->count()}):\n";
    foreach ($materialItems as $item) {
        $catalogStatus = $item->catalog_id ? "🔗 LINKED" : "⚠️  NOT LINKED";
        
        echo "  • {$item->name} [{$catalogStatus}]\n";
        echo "    Qty: {$item->quantity} {$item->unit} × \${$item->unit_cost} = \${$item->cost_total}\n";
        
        if ($item->catalog_id) {
            $catalogMaterial = Material::find($item->catalog_id);
            echo "    📦 Catalog: {$catalogMaterial->name} (ID: {$item->catalog_id})\n";
            echo "    💰 Catalog Price: \${$catalogMaterial->unit_cost}/{$catalogMaterial->unit}\n";
            
            if ($item->unit_cost != $catalogMaterial->unit_cost) {
                echo "    ⚠️  PRICE OVERRIDE: Using \${$item->unit_cost} instead of catalog \${$catalogMaterial->unit_cost}\n";
            } else {
                echo "    ✅ Using Catalog Price\n";
            }
        }
        
        if ($item->description) {
            echo "    Description: {$item->description}\n";
        }
        echo "\n";
    }
    
    // 4. Summary
    echo "=== SUMMARY ===\n";
    echo "✅ Calculator created with catalog materials\n";
    echo "✅ Imported to estimate #{$estimateId}\n";
    echo "✅ Work area '{$area->name}' created\n";
    echo "✅ {$laborItems->count()} labor items imported\n";
    echo "✅ {$materialItems->count()} material items imported\n";
    
    $linkedCount = $materialItems->where('catalog_id', '!=', null)->count();
    echo "🔗 {$linkedCount}/{$materialItems->count()} materials linked to catalog\n\n";
    
    echo "View in app: Estimate #{$estimateId} → Work Area: {$area->name}\n";
    
} catch (\Exception $e) {
    echo "❌ Import Failed!\n";
    echo "Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    exit(1);
}
