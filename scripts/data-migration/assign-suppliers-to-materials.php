<?php

/**
 * Assign suppliers to materials in bulk
 * 
 * This script helps you assign suppliers to materials that don't have one yet.
 * Run with: php assign-suppliers-to-materials.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Material;
use App\Models\Supplier;

echo "=== Assign Suppliers to Materials ===\n\n";

// Get or create default suppliers
$suppliers = [
    'ABC Landscape Supply' => Supplier::firstOrCreate(
        ['name' => 'ABC Landscape Supply'],
        [
            'company_name' => 'ABC Landscape Supply Co.',
            'email' => 'orders@abclandscape.com',
            'phone' => '(555) 123-4567',
            'is_active' => true,
        ]
    ),
    'Home Depot' => Supplier::firstOrCreate(
        ['name' => 'Home Depot'],
        [
            'company_name' => 'Home Depot Inc.',
            'email' => 'commercial@homedepot.com',
            'phone' => '(555) 987-6543',
            'is_active' => true,
        ]
    ),
    'Local Nursery' => Supplier::firstOrCreate(
        ['name' => 'Local Nursery'],
        [
            'company_name' => 'Local Nursery & Garden Center',
            'email' => 'orders@localnursery.com',
            'phone' => '(555) 555-1234',
            'is_active' => true,
        ]
    ),
];

echo "Available Suppliers:\n";
foreach ($suppliers as $name => $supplier) {
    echo "  [{$supplier->id}] {$name}\n";
}
echo "\n";

// Get materials without suppliers
$materialsWithoutSuppliers = Material::whereNull('supplier_id')->get();

if ($materialsWithoutSuppliers->isEmpty()) {
    echo "✓ All materials already have suppliers assigned!\n";
    exit(0);
}

echo "Found {$materialsWithoutSuppliers->count()} materials without suppliers.\n\n";

// Auto-assign based on patterns or vendor_name
$updated = 0;

foreach ($materialsWithoutSuppliers as $material) {
    $supplierId = null;
    
    // Strategy 1: Match vendor_name if it exists
    if (!empty($material->vendor_name)) {
        foreach ($suppliers as $name => $supplier) {
            if (stripos($material->vendor_name, $name) !== false || 
                stripos($name, $material->vendor_name) !== false) {
                $supplierId = $supplier->id;
                break;
            }
        }
    }
    
    // Strategy 2: Match based on material category/type
    if (!$supplierId) {
        $materialName = strtolower($material->name);
        
        // Mulch, soil, stone, gravel -> ABC Landscape Supply
        if (str_contains($materialName, 'mulch') || 
            str_contains($materialName, 'soil') || 
            str_contains($materialName, 'stone') || 
            str_contains($materialName, 'gravel') ||
            str_contains($materialName, 'sand') ||
            str_contains($materialName, 'base')) {
            $supplierId = $suppliers['ABC Landscape Supply']->id;
        }
        // Plants, shrubs, trees -> Local Nursery
        elseif (str_contains($materialName, 'plant') ||
                str_contains($materialName, 'shrub') ||
                str_contains($materialName, 'tree') ||
                str_contains($materialName, 'azalea') ||
                str_contains($materialName, 'viburnum') ||
                str_contains($materialName, 'holly')) {
            $supplierId = $suppliers['Local Nursery']->id;
        }
        // Pavers, concrete, lumber -> Home Depot
        elseif (str_contains($materialName, 'paver') ||
                str_contains($materialName, 'concrete') ||
                str_contains($materialName, 'lumber') ||
                str_contains($materialName, 'post') ||
                str_contains($materialName, 'board')) {
            $supplierId = $suppliers['Home Depot']->id;
        }
        // Default to ABC Landscape Supply
        else {
            $supplierId = $suppliers['ABC Landscape Supply']->id;
        }
    }
    
    // Update material
    if ($supplierId) {
        $material->update(['supplier_id' => $supplierId]);
        $supplierName = collect($suppliers)->firstWhere('id', $supplierId)?->name ?? 'Unknown';
        echo "  ✓ {$material->name} → {$supplierName}\n";
        $updated++;
    }
}

echo "\n=== Summary ===\n";
echo "Updated {$updated} materials with supplier assignments.\n";

// Show breakdown by supplier
echo "\nMaterials by Supplier:\n";
foreach ($suppliers as $name => $supplier) {
    $count = Material::where('supplier_id', $supplier->id)->count();
    echo "  {$name}: {$count} materials\n";
}

$stillWithoutSupplier = Material::whereNull('supplier_id')->count();
if ($stillWithoutSupplier > 0) {
    echo "  No Supplier: {$stillWithoutSupplier} materials\n";
}

echo "\nDone! You can now generate POs and materials will be grouped by supplier.\n";
