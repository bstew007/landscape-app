<?php

// Run this from command line: php check-orphaned-items.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking for orphaned estimate items...\n\n";

$items = \App\Models\EstimateItem::whereNotNull('catalog_id')
    ->whereNotNull('catalog_type')
    ->get();

$orphaned = [];

foreach ($items as $item) {
    $exists = false;
    
    if ($item->catalog_type === 'labor') {
        $exists = \App\Models\LaborItem::find($item->catalog_id) !== null;
    } elseif ($item->catalog_type === 'material') {
        $exists = \App\Models\Material::find($item->catalog_id) !== null;
    }
    
    if (!$exists) {
        $orphaned[] = $item;
        echo "❌ Estimate #{$item->estimate_id} - {$item->name}\n";
        echo "   Type: {$item->catalog_type}, ID: {$item->catalog_id}\n";
        echo "   → Catalog item no longer exists\n\n";
    }
}

if (empty($orphaned)) {
    echo "✅ No orphaned items found. All catalog references are valid!\n";
} else {
    echo "\n" . count($orphaned) . " orphaned item(s) found.\n\n";
    echo "Options:\n";
    echo "1. Delete these line items and re-add from catalog\n";
    echo "2. Keep them as-is (Reset button won't work)\n";
    echo "3. Unlink from catalog (set catalog_type and catalog_id to NULL)\n";
}
