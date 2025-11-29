<?php

/**
 * Merge Duplicate Vendors
 * 
 * This script helps merge duplicate vendor contacts that are the same company
 * with different spellings or IDs.
 * 
 * Run with: php merge-duplicate-vendors.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Contact;
use App\Models\Material;
use App\Models\EstimatePurchaseOrder;
use Illuminate\Support\Facades\DB;

echo "=== Merge Duplicate Vendors ===\n\n";

// Define duplicates to merge (keep_id => [duplicate_ids])
$merges = [
    // Home Depot - keep ID 59, merge 33, 49, 62
    59 => [33, 49, 62],
    
    // Seaside Mulch - keep ID 48, merge 37 (Seaside)
    48 => [37],
    
    // The Plant Place - keep ID 41, merge 43 (Plant Place)
    41 => [43],
    
    // Tinga Nursery - keep ID 39, merge 53 (Tinga) and 54 (tn)
    39 => [53, 54],
    
    // Outdoor Supply - keep ID 56, merge 45 (Stone Garden)
    56 => [45],
];

// Vendors to delete completely (no merge, just delete)
$toDelete = [
    32 => 'ABC Landscape Supply Co.',
    58 => 'ABC Landscape Supply Co.',
    61 => 'ABC Landscape Supply Co.',
    34 => 'Local Nursery & Garden Center',
    60 => 'Local Nursery & Garden Center',
    63 => 'Local Nursery & Garden Center',
];

$totalMerged = 0;
$materialsUpdated = 0;
$posUpdated = 0;
$totalDeleted = 0;

// First, handle deletions (vendors to completely remove)
foreach ($toDelete as $deleteId => $vendorName) {
    echo "\n" . str_repeat('-', 60) . "\n";
    echo "Deleting: $vendorName (ID: $deleteId)\n";
    
    $vendor = Contact::find($deleteId);
    if (!$vendor) {
        echo "  ⚠️  Warning: Vendor ID $deleteId not found. Skipping.\n";
        continue;
    }
    
    // Check for materials
    $matCount = Material::where('supplier_id', $deleteId)->count();
    if ($matCount > 0) {
        echo "  ⚠️  WARNING: This vendor has $matCount materials assigned!\n";
        echo "  Setting materials to NULL (unassigned)...\n";
        Material::where('supplier_id', $deleteId)->update(['supplier_id' => null]);
        $materialsUpdated += $matCount;
    }
    
    // Check for POs
    $poCount = EstimatePurchaseOrder::where('supplier_id', $deleteId)->count();
    if ($poCount > 0) {
        echo "  ⚠️  WARNING: This vendor has $poCount purchase orders!\n";
        echo "  Setting POs to NULL (unassigned)...\n";
        EstimatePurchaseOrder::where('supplier_id', $deleteId)->update(['supplier_id' => null]);
        $posUpdated += $poCount;
    }
    
    // Delete the vendor
    $vendor->delete();
    echo "  ✓ Deleted vendor\n";
    $totalDeleted++;
}

// Now handle merges
foreach ($merges as $keepId => $duplicateIds) {
    echo "\n" . str_repeat('-', 60) . "\n";
    
    $keepVendor = Contact::find($keepId);
    if (!$keepVendor) {
        echo "❌ Error: Vendor ID $keepId not found. Skipping.\n";
        continue;
    }
    
    echo "Keeping: {$keepVendor->company_name} (ID: $keepId)\n";
    echo "Merging duplicates:\n";
    
    foreach ($duplicateIds as $dupId) {
        $duplicate = Contact::find($dupId);
        if (!$duplicate) {
            echo "  ⚠️  Warning: Vendor ID $dupId not found. Skipping.\n";
            continue;
        }
        
        echo "  → {$duplicate->company_name} (ID: $dupId)\n";
        
        // Update materials that reference the duplicate
        $matCount = Material::where('supplier_id', $dupId)->update(['supplier_id' => $keepId]);
        $materialsUpdated += $matCount;
        if ($matCount > 0) {
            echo "    ✓ Updated $matCount materials\n";
        }
        
        // Update purchase orders that reference the duplicate
        $poCount = EstimatePurchaseOrder::where('supplier_id', $dupId)->update(['supplier_id' => $keepId]);
        $posUpdated += $poCount;
        if ($poCount > 0) {
            echo "    ✓ Updated $poCount purchase orders\n";
        }
        
        // Delete the duplicate contact
        $duplicate->delete();
        echo "    ✓ Deleted duplicate contact\n";
        
        $totalMerged++;
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Summary:\n";
echo "  • Vendors completely deleted: $totalDeleted\n";
echo "  • Duplicate vendors merged: $totalMerged\n";
echo "  • Materials updated: $materialsUpdated\n";
echo "  • Purchase orders updated: $posUpdated\n";
echo "\n✅ Done! Vendor list is now clean.\n";

// Show final count
$finalCount = Contact::where('contact_type', 'vendor')->count();
echo "\nFinal vendor count: $finalCount vendors\n";
