<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CompanyBudget;

$budget = CompanyBudget::latest()->first();

if (!$budget) {
    echo "No budgets found\n";
    exit;
}

echo "Budget ID: {$budget->id}\n";
echo "Budget Name: {$budget->name}\n";
echo "Updated: {$budget->updated_at}\n\n";

echo "=== Field Labor ===\n";
$hourlyRows = data_get($budget->inputs, 'labor.hourly.rows', []);
echo "Hourly Rows: " . count($hourlyRows) . "\n";
if (count($hourlyRows) > 0) {
    echo json_encode($hourlyRows, JSON_PRETTY_PRINT) . "\n";
}

$salaryRows = data_get($budget->inputs, 'labor.salary.rows', []);
echo "Salary Rows: " . count($salaryRows) . "\n";
if (count($salaryRows) > 0) {
    echo json_encode($salaryRows, JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== Equipment ===\n";
$equipmentRows = data_get($budget->inputs, 'equipment.rows', []);
echo "Equipment Rows: " . count($equipmentRows) . "\n";

echo "\n=== Materials ===\n";
$materialsRows = data_get($budget->inputs, 'materials.rows', []);
echo "Materials Rows: " . count($materialsRows) . "\n";

echo "\n=== Overhead ===\n";
$overheadExpenses = data_get($budget->inputs, 'overhead.expenses.rows', []);
echo "Overhead Expense Rows: " . count($overheadExpenses) . "\n";

$overheadWages = data_get($budget->inputs, 'overhead.wages.rows', []);
echo "Overhead Wage Rows: " . count($overheadWages) . "\n";

echo "\n=== Other Fields ===\n";
echo "Labor Burden %: " . data_get($budget->inputs, 'labor.burden_pct', 'not set') . "\n";
echo "OT Multiplier: " . data_get($budget->inputs, 'labor.ot_multiplier', 'not set') . "\n";
