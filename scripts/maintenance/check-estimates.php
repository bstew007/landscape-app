<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Estimate;

echo "=== ALL ESTIMATES ===\n\n";
$estimates = Estimate::orderBy('id')->get();

foreach ($estimates as $est) {
    echo "ID: {$est->id}\n";
    echo "  Name: '{$est->name}'\n";
    echo "  Client ID: {$est->client_id}\n";
    echo "  Status: {$est->status}\n";
    echo "  Created: {$est->created_at->format('Y-m-d H:i')}\n";
    
    $areas = $est->areas;
    echo "  Work Areas: {$areas->count()}\n";
    foreach ($areas as $area) {
        echo "    - {$area->name} (Items: {$area->items->count()})\n";
    }
    echo "\n";
}

echo "\n=== ESTIMATE #5 SPECIFIC ===\n";
$est5 = Estimate::find(5);
if ($est5) {
    echo "Found! Name: '{$est5->name}'\n";
    echo "Client: {$est5->client_id}\n";
    echo "Property: {$est5->property_id}\n";
    echo "Areas: {$est5->areas->count()}\n";
} else {
    echo "NOT FOUND\n";
}
