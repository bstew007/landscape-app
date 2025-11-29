<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Labor Items in Catalog:\n";
echo "========================\n\n";

$items = \App\Models\LaborItem::all();

if ($items->isEmpty()) {
    echo "❌ No labor items found in catalog!\n";
    echo "   Go to Labor page and add some labor items first.\n";
} else {
    foreach ($items as $item) {
        echo "✅ ID: {$item->id} - {$item->name}\n";
        echo "   Wage: \${$item->average_wage}\n";
        echo "   Active: " . ($item->is_active ? 'Yes' : 'No') . "\n\n";
    }
    echo "\nTotal: " . $items->count() . " labor items\n";
}
