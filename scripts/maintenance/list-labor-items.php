<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Labor Catalog Items:\n";
echo "==================\n\n";

$items = \App\Models\LaborItem::orderBy('id')->get();

foreach ($items as $item) {
    $active = $item->is_active ? 'YES' : 'NO';
    echo "ID {$item->id}: {$item->name} (Active: {$active})\n";
}

echo "\nTotal: {$items->count()} items\n";
