<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test the exact API logic
$type = 'labor';
$id = 5;

echo "Testing catalog API for type={$type}, id={$id}\n\n";

// Normalize type
$type = strtolower($type);
if (strpos($type, 'laboritem') !== false || $type === 'labor') {
    $type = 'labor';
} elseif (strpos($type, 'material') !== false) {
    $type = 'material';
}

echo "Normalized type: {$type}\n";

if ($type === 'labor') {
    $item = \App\Models\LaborItem::find($id);
    
    echo "Labor item found: " . ($item ? 'YES' : 'NO') . "\n";
    
    if ($item) {
        echo "Item details:\n";
        echo "  ID: {$item->id}\n";
        echo "  Name: {$item->name}\n";
        echo "  Is Active: " . ($item->is_active ? 'true' : 'false') . "\n";
        echo "  Average Wage: {$item->average_wage}\n";
        echo "  Overtime Factor: {$item->overtime_factor}\n";
        echo "  Labor Burden %: {$item->labor_burden_percentage}\n";
        echo "  Unbillable %: {$item->unbillable_percentage}\n";
    }
}

echo "\n";
echo "Now checking estimate item #145:\n";

$estimateItem = \App\Models\EstimateItem::find(145);
if ($estimateItem) {
    echo "  ID: {$estimateItem->id}\n";
    echo "  Name: {$estimateItem->name}\n";
    echo "  catalog_type: " . ($estimateItem->catalog_type ?? 'NULL') . "\n";
    echo "  catalog_id: " . ($estimateItem->catalog_id ?? 'NULL') . "\n";
    echo "  item_type: {$estimateItem->item_type}\n";
}
