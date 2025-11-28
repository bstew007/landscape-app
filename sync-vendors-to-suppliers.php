<?php

/**
 * Sync vendor_name to Suppliers table
 * 
 * This script creates Supplier records from unique vendor_name values
 * and links materials to those suppliers.
 * 
 * Run with: php sync-vendors-to-suppliers.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Material;
use App\Models\Supplier;

echo "=== Sync Vendor Names to Suppliers ===\n\n";

// Get all unique vendor names from materials
$vendorNames = Material::whereNotNull('vendor_name')
    ->where('vendor_name', '!=', '')
    ->distinct()
    ->pluck('vendor_name');

echo "Found " . $vendorNames->count() . " unique vendor names in materials:\n";

$created = 0;
$existing = 0;
$linked = 0;

foreach ($vendorNames as $vendorName) {
    echo "\n" . str_repeat('-', 60) . "\n";
    echo "Processing: $vendorName\n";
    
    // Check if supplier already exists (case-insensitive)
    $supplier = Supplier::whereRaw('LOWER(name) = ?', [strtolower($vendorName)])->first();
    
    if ($supplier) {
        echo "  ✓ Supplier already exists (ID: {$supplier->id})\n";
        $existing++;
    } else {
        // Create new supplier
        $supplier = Supplier::create([
            'name' => $vendorName,
            'company_name' => $vendorName,
            'is_active' => true,
        ]);
        echo "  ✓ Created new supplier (ID: {$supplier->id})\n";
        $created++;
    }
    
    // Link all materials with this vendor_name to the supplier
    $materials = Material::where('vendor_name', $vendorName)->get();
    
    foreach ($materials as $material) {
        if ($material->supplier_id !== $supplier->id) {
            $material->supplier_id = $supplier->id;
            $material->save();
            $linked++;
            echo "  → Linked material: {$material->name} (ID: {$material->id})\n";
        } else {
            echo "  - Already linked: {$material->name} (ID: {$material->id})\n";
        }
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Summary:\n";
echo "  • Suppliers created: $created\n";
echo "  • Suppliers already existed: $existing\n";
echo "  • Materials linked: $linked\n";
echo "\n✅ Done! All vendor names are now suppliers with linked materials.\n";
echo "\nYou can now generate POs and they will be grouped by supplier!\n";
