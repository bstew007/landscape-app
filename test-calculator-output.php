<?php
/**
 * Test Calculator Output - Verify Enhanced Format
 * 
 * Usage: php test-calculator-output.php [calculation_id]
 * Example: php test-calculator-output.php 123
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Calculation;

// Get calculation ID from command line argument
$calculationId = $argv[1] ?? null;

if (!$calculationId) {
    echo "❌ Please provide a calculation ID\n";
    echo "Usage: php test-calculator-output.php [calculation_id]\n\n";
    echo "Recent calculations:\n";
    
    $recent = Calculation::orderBy('created_at', 'desc')
        ->take(10)
        ->get(['id', 'calculation_type', 'created_at', 'is_template']);
    
    foreach ($recent as $calc) {
        $template = $calc->is_template ? ' (TEMPLATE)' : '';
        echo "  ID: {$calc->id} - {$calc->calculation_type} - {$calc->created_at->format('Y-m-d H:i')}{$template}\n";
    }
    exit(1);
}

// Load the calculation
$calculation = Calculation::find($calculationId);

if (!$calculation) {
    echo "❌ Calculation #{$calculationId} not found\n";
    exit(1);
}

echo "✅ Found Calculation #{$calculationId}\n";
echo "   Type: {$calculation->calculation_type}\n";
echo "   Created: {$calculation->created_at->format('Y-m-d H:i:s')}\n";
echo "   Template: " . ($calculation->is_template ? 'Yes' : 'No') . "\n\n";

// Check for enhanced format
$data = $calculation->data;

echo "=== DATA STRUCTURE CHECK ===\n\n";

// Check for labor_tasks (new format)
if (isset($data['labor_tasks'])) {
    echo "✅ ENHANCED FORMAT DETECTED - labor_tasks array exists!\n";
    echo "   Count: " . count($data['labor_tasks']) . " tasks\n\n";
    
    echo "Labor Tasks:\n";
    foreach ($data['labor_tasks'] as $index => $task) {
        echo "  [" . ($index + 1) . "] {$task['task_name']}\n";
        echo "      Description: {$task['description']}\n";
        echo "      Quantity: {$task['quantity']} {$task['unit']}\n";
        echo "      Production Rate: {$task['production_rate']} hrs/{$task['unit']}\n";
        echo "      Hours: {$task['hours']}\n";
        echo "      Cost: \${$task['total_cost']}\n\n";
    }
} else {
    echo "⚠️  OLD FORMAT - labor_tasks array NOT found\n";
    echo "   This calculator hasn't been updated yet or was created before the enhancement.\n\n";
}

// Check for materials
if (isset($data['materials']) && !empty($data['materials'])) {
    echo "Materials:\n";
    $materials = is_array($data['materials']) ? $data['materials'] : [];
    
    foreach ($materials as $key => $material) {
        if (is_array($material)) {
            $name = $material['name'] ?? $key;
            $qty = $material['quantity'] ?? $material['qty'] ?? 'N/A';
            $unit = $material['unit'] ?? '';
            $cost = $material['total_cost'] ?? $material['total'] ?? 0;
            
            echo "  • {$name}: {$qty} {$unit} - \${$cost}\n";
        }
    }
    echo "\n";
} else {
    echo "No materials in this calculation.\n\n";
}

// Check overhead tasks
echo "=== OVERHEAD DATA ===\n";
echo "Drive Time Hours: " . ($data['drive_time_hours'] ?? 'N/A') . "\n";
echo "Crew Size: " . ($data['crew_size'] ?? 'N/A') . "\n";
echo "Overhead %: " . ($data['overhead_percent'] ?? 'N/A') . "%\n";
echo "Total Labor Hours: " . ($data['labor_hours'] ?? 'N/A') . "\n";
echo "Total Labor Cost: \$" . ($data['labor_total'] ?? 'N/A') . "\n\n";

// Show full JSON for debugging
echo "=== FULL JSON (First 1000 chars) ===\n";
$json = json_encode($data, JSON_PRETTY_PRINT);
echo substr($json, 0, 1000);
if (strlen($json) > 1000) {
    echo "\n... (truncated, total length: " . strlen($json) . " chars)\n";
}
echo "\n";
