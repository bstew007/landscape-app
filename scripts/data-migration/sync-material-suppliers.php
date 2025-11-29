<?php

/**
 * Sync Material supplier_id from vendor_name
 * 
 * This script populates the supplier_id field for existing materials
 * by matching their vendor_name against Contact records.
 * 
 * Run this on production after deploying the supplier_id migration:
 * php sync-material-suppliers.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Material;
use App\Models\Contact;

echo "Starting material supplier sync...\n\n";

$materials = Material::whereNotNull('vendor_name')
    ->where('vendor_name', '!=', '')
    ->whereNull('supplier_id')
    ->get();

echo "Found {$materials->count()} materials with vendor_name but no supplier_id\n\n";

$matched = 0;
$notMatched = 0;
$notMatchedList = [];

foreach ($materials as $material) {
    $vendorName = trim($material->vendor_name);
    
    // Try to find matching contact
    $contact = Contact::where('contact_type', 'vendor')
        ->where(function ($query) use ($vendorName) {
            $query->where('company_name', $vendorName)
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) = ?", [$vendorName]);
        })
        ->first();
    
    if ($contact) {
        $material->supplier_id = $contact->id;
        $material->save();
        $matched++;
        echo "✓ Matched '{$vendorName}' → {$contact->company_name} (ID: {$contact->id})\n";
    } else {
        $notMatched++;
        $notMatchedList[] = $vendorName;
        echo "✗ No match for '{$vendorName}'\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "Summary:\n";
echo "  Matched:     {$matched}\n";
echo "  Not Matched: {$notMatched}\n";
echo "═══════════════════════════════════════════════════════\n";

if (!empty($notMatchedList)) {
    echo "\nVendors not found in contacts (need to be created):\n";
    $unique = array_unique($notMatchedList);
    sort($unique);
    foreach ($unique as $vendor) {
        echo "  - {$vendor}\n";
    }
    echo "\nYou can create these vendors at: /contacts/create?type=vendor\n";
}

echo "\nSync complete!\n";
