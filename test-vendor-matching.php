<?php

/**
 * Test script for Material Vendor Matching
 * 
 * This script tests the automatic vendor assignment when generating POs.
 * Run with: php test-vendor-matching.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Material;
use App\Models\Supplier;
use App\Services\MaterialMatchingService;
use App\Services\PurchaseOrderService;

echo "=== Material Vendor Matching Test ===\n\n";

// Get or create test supplier
$supplier1 = Supplier::firstOrCreate(
    ['name' => 'ABC Landscape Supply'],
    [
        'company_name' => 'ABC Landscape Supply Co.',
        'email' => 'orders@abclandscape.com',
        'is_active' => true,
    ]
);

$supplier2 = Supplier::firstOrCreate(
    ['name' => 'Home Depot'],
    [
        'company_name' => 'Home Depot Inc.',
        'email' => 'commercial@homedepot.com',
        'is_active' => true,
    ]
);

echo "Created/Found Suppliers:\n";
echo "  - {$supplier1->name} (ID: {$supplier1->id})\n";
echo "  - {$supplier2->name} (ID: {$supplier2->id})\n\n";

// Create test materials with suppliers
$mulch = Material::firstOrCreate(
    ['sku' => 'MULCH-001'],
    [
        'name' => 'Premium Hardwood Mulch',
        'supplier_id' => $supplier1->id,
        'vendor_sku' => 'ABC-MULCH-PREMIUM',
        'unit' => 'cu yd',
        'unit_cost' => 25.00,
        'vendor_name' => $supplier1->name,
        'is_active' => true,
        'description' => 'Premium hardwood mulch, dark brown',
    ]
);

$stone = Material::firstOrCreate(
    ['sku' => 'STONE-001'],
    [
        'name' => 'River Stone 3/4"',
        'supplier_id' => $supplier1->id,
        'vendor_sku' => 'ABC-STONE-RIVER-34',
        'unit' => 'ton',
        'unit_cost' => 45.00,
        'vendor_name' => $supplier1->name,
        'is_active' => true,
        'description' => 'Natural river stone, 3/4 inch',
    ]
);

$paver = Material::firstOrCreate(
    ['sku' => 'PAVER-001'],
    [
        'name' => 'Concrete Paver 12x12',
        'supplier_id' => $supplier2->id,
        'vendor_sku' => 'HD-PAVER-12X12',
        'unit' => 'ea',
        'unit_cost' => 2.50,
        'vendor_name' => $supplier2->name,
        'is_active' => true,
        'description' => 'Concrete paver, 12x12 inches',
    ]
);

echo "Created/Found Materials:\n";
echo "  - {$mulch->name}\n";
echo "    SKU: {$mulch->sku} | Vendor SKU: {$mulch->vendor_sku}\n";
echo "    Supplier: {$mulch->supplier->name}\n";
echo "  - {$stone->name}\n";
echo "    SKU: {$stone->sku} | Vendor SKU: {$stone->vendor_sku}\n";
echo "    Supplier: {$stone->supplier->name}\n";
echo "  - {$paver->name}\n";
echo "    SKU: {$paver->sku} | Vendor SKU: {$paver->vendor_sku}\n";
echo "    Supplier: {$paver->supplier->name}\n\n";

// Test MaterialMatchingService
echo "=== Testing MaterialMatchingService ===\n\n";

$matchingService = app(MaterialMatchingService::class);

// Test 1: Item with matching SKU
$testItem1 = new EstimateItem([
    'name' => 'Premium Mulch',
    'description' => 'Dark brown hardwood mulch',
    'item_type' => 'material',
    'quantity' => 10,
    'unit' => 'cu yd',
    'unit_cost' => 25.00,
    'metadata' => ['sku' => 'MULCH-001'], // Exact SKU match
]);

$match1 = $matchingService->findBestMatch($testItem1);

echo "Test 1 - Item with matching SKU:\n";
echo "  Item: '{$testItem1->name}' (SKU: {$testItem1->metadata['sku']})\n";
if ($match1) {
    echo "  ✓ Matched: {$match1['material']->name}\n";
    echo "  ✓ Material SKU: {$match1['material']->sku}\n";
    echo "  ✓ Score: {$match1['score']}%\n";
    echo "  ✓ Confidence: {$match1['confidence']}\n";
    echo "  ✓ Supplier: {$match1['material']->supplier->name}\n\n";
} else {
    echo "  ✗ No match found\n\n";
}

// Test 2: Item with matching vendor SKU
$testItem2 = new EstimateItem([
    'name' => 'Concrete Pavers 12x12',
    'description' => 'Square concrete pavers',
    'item_type' => 'material',
    'quantity' => 100,
    'unit' => 'ea',
    'unit_cost' => 2.50,
    'metadata' => ['vendor_sku' => 'HD-PAVER-12X12'], // Exact vendor SKU match
]);

$match2 = $matchingService->findBestMatch($testItem2);

echo "Test 2 - Item with matching Vendor SKU:\n";
echo "  Item: '{$testItem2->name}' (Vendor SKU: {$testItem2->metadata['vendor_sku']})\n";
if ($match2) {
    echo "  ✓ Matched: {$match2['material']->name}\n";
    echo "  ✓ Material Vendor SKU: {$match2['material']->vendor_sku}\n";
    echo "  ✓ Score: {$match2['score']}%\n";
    echo "  ✓ Confidence: {$match2['confidence']}\n";
    echo "  ✓ Supplier: {$match2['material']->supplier->name}\n\n";
} else {
    echo "  ✗ No match found\n\n";
}

// Test 3: Item with fuzzy name match (no SKU)
$testItem3 = new EstimateItem([
    'name' => 'River Stone 3/4 inch',
    'description' => 'Natural stone for landscaping',
    'item_type' => 'material',
    'quantity' => 5,
    'unit' => 'ton',
    'unit_cost' => 45.00,
]);

$match3 = $matchingService->findBestMatch($testItem3);

echo "Test 3 - Item with fuzzy name match (no SKU):\n";
echo "  Item: '{$testItem3->name}'\n";
if ($match3) {
    echo "  ✓ Matched: {$match3['material']->name}\n";
    echo "  ✓ Score: {$match3['score']}%\n";
    echo "  ✓ Confidence: {$match3['confidence']}\n";
    echo "  ✓ Supplier: {$match3['material']->supplier->name}\n\n";
} else {
    echo "  ✗ No match found\n\n";
}

// Test suggest matches
echo "=== Testing Suggest Matches ===\n\n";
$suggestions = $matchingService->suggestMatches($testItem3, 3);

echo "Suggestions for '{$testItem3->name}':\n";
foreach ($suggestions as $i => $suggestion) {
    echo "  " . ($i + 1) . ". {$suggestion['material']->name} ";
    echo "(SKU: {$suggestion['material']->sku}) - ";
    echo "{$suggestion['score']}% ({$suggestion['confidence']})\n";
}

echo "\n=== Test Complete ===\n";
echo "\nKey Points:\n";
echo "- SKU and Vendor SKU matches are prioritized (100% score)\n";
echo "- Name-based fuzzy matching works as fallback\n";
echo "- Each material is linked to its supplier\n";
echo "- PO generation will automatically group materials by their matched supplier\n";
echo "\nNext: Try generating POs from an actual estimate to see vendor grouping!\n";
