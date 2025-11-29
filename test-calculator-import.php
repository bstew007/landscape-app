<?php
/**
 * Test Calculator Import to Estimate
 * 
 * Usage: php test-calculator-import.php [calculation_id] [estimate_id] [area_name]
 * Example: php test-calculator-import.php 1 5 "Front Yard Planting"
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Calculation;
use App\Models\Estimate;
use App\Models\EstimateArea;
use App\Services\CalculationImportService;

// Get arguments
$calculationId = $argv[1] ?? null;
$estimateId = $argv[2] ?? null;
$areaName = $argv[3] ?? 'Calculator Import Test';

if (!$calculationId || !$estimateId) {
    echo "❌ Please provide calculation ID and estimate ID\n";
    echo "Usage: php test-calculator-import.php [calculation_id] [estimate_id] [area_name]\n\n";
    
    echo "Recent calculations:\n";
    $recent = Calculation::orderBy('created_at', 'desc')->take(5)->get(['id', 'calculation_type', 'created_at']);
    foreach ($recent as $calc) {
        echo "  Calculation ID: {$calc->id} - {$calc->calculation_type} - {$calc->created_at->format('Y-m-d H:i')}\n";
    }
    
    echo "\nRecent estimates:\n";
    $estimates = Estimate::orderBy('created_at', 'desc')->take(5)->get(['id', 'name', 'created_at']);
    foreach ($estimates as $est) {
        echo "  Estimate ID: {$est->id} - {$est->name} - {$est->created_at->format('Y-m-d H:i')}\n";
    }
    
    exit(1);
}

// Load models
$calculation = Calculation::find($calculationId);
$estimate = Estimate::find($estimateId);

if (!$calculation) {
    echo "❌ Calculation #{$calculationId} not found\n";
    exit(1);
}

if (!$estimate) {
    echo "❌ Estimate #{$estimateId} not found\n";
    exit(1);
}

echo "✅ Found Calculation #{$calculationId} ({$calculation->calculation_type})\n";
echo "✅ Found Estimate #{$estimateId} ({$estimate->name})\n\n";

// Check if calculation has enhanced format
$hasEnhancedFormat = isset($calculation->data['labor_tasks']);
echo "Enhanced Format: " . ($hasEnhancedFormat ? "✅ YES" : "⚠️  NO (will use legacy import)") . "\n\n";

// Get or create work area
echo "=== CREATING WORK AREA ===\n";
$area = EstimateArea::create([
    'estimate_id' => $estimate->id,
    'name' => $areaName,
    'description' => "Imported from {$calculation->calculation_type} calculator",
    'calculation_id' => $calculation->id,
    'site_visit_id' => $calculation->site_visit_id,
    'sort_order' => EstimateArea::where('estimate_id', $estimate->id)->max('sort_order') + 1,
]);

echo "✅ Created Work Area: {$area->name} (ID: {$area->id})\n\n";

// Import using the enhanced service
echo "=== IMPORTING CALCULATION ===\n";
$importService = app(CalculationImportService::class);

try {
    $area = $importService->importCalculationToArea($estimate, $calculation, $area->id);
    
    echo "✅ IMPORT SUCCESSFUL!\n\n";
    
    // Show what was created
    $items = $area->items()->orderBy('item_type')->orderBy('sort_order')->get();
    
    echo "=== IMPORTED ITEMS ===\n";
    echo "Total Items: " . $items->count() . "\n\n";
    
    $laborItems = $items->where('item_type', 'labor');
    $materialItems = $items->where('item_type', 'material');
    $feeItems = $items->where('item_type', 'fee');
    
    if ($laborItems->count() > 0) {
        echo "LABOR ITEMS ({$laborItems->count()}):\n";
        foreach ($laborItems as $item) {
            echo "  • {$item->name}\n";
            echo "    Description: {$item->description}\n";
            echo "    Qty: {$item->quantity} {$item->unit}\n";
            echo "    Unit Cost: \${$item->unit_cost} | Unit Price: \${$item->unit_price}\n";
            echo "    Cost Total: \${$item->cost_total} | Line Total: \${$item->line_total}\n\n";
        }
    }
    
    if ($materialItems->count() > 0) {
        echo "MATERIAL ITEMS ({$materialItems->count()}):\n";
        foreach ($materialItems as $item) {
            echo "  • {$item->name}\n";
            echo "    Description: {$item->description}\n";
            echo "    Qty: {$item->quantity} {$item->unit} × \${$item->unit_cost} = \${$item->cost_total}\n";
            echo "    Line Total (w/ margin): \${$item->line_total}\n\n";
        }
    }
    
    if ($feeItems->count() > 0) {
        echo "FEE/OVERHEAD ITEMS ({$feeItems->count()}):\n";
        foreach ($feeItems as $item) {
            echo "  • {$item->name}\n";
            echo "    Qty: {$item->quantity} {$item->unit} × \${$item->unit_cost}\n";
            echo "    Cost Total: \${$item->cost_total} | Line Total: \${$item->line_total}\n\n";
        }
    }
    
    // Show area metadata
    echo "=== AREA METADATA ===\n";
    echo "Calculation ID: {$area->calculation_id}\n";
    echo "Site Visit ID: {$area->site_visit_id}\n";
    echo "Planned Hours: {$area->planned_hours}\n";
    echo "Crew Size: {$area->crew_size}\n";
    echo "Drive Time Hours: {$area->drive_time_hours}\n";
    echo "Overhead %: {$area->overhead_percent}\n\n";
    
    echo "✅ Test complete! Check estimate #{$estimate->id} in your app.\n";
    
} catch (\Exception $e) {
    echo "❌ IMPORT FAILED!\n";
    echo "Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
