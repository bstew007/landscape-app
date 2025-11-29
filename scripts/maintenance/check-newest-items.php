<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Latest Estimate Items (Estimate #9):\n";
echo "=====================================\n\n";

$items = \App\Models\EstimateItem::where('estimate_id', 9)
    ->latest()
    ->take(5)
    ->get();

foreach ($items as $item) {
    echo "ID: {$item->id} - {$item->name}\n";
    echo "  catalog_type: " . ($item->catalog_type ?? 'NULL') . "\n";
    echo "  catalog_id: " . ($item->catalog_id ?? 'NULL') . "\n";
    echo "  created: {$item->created_at}\n\n";
}
