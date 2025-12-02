# Calculator to Estimate Integration - Implementation Guide

This document provides concrete code examples and step-by-step instructions for implementing the calculator → estimate integration.

## Table of Contents
1. [Quick Reference](#quick-reference)
2. [Phase 1: Foundation Setup](#phase-1-foundation-setup)
3. [Phase 2: Enhanced Import Service](#phase-2-enhanced-import-service)
4. [Phase 3: Calculator Updates](#phase-3-calculator-updates)
5. [Phase 4: UI Integration](#phase-4-ui-integration)
6. [Testing Strategies](#testing-strategies)

---

## Quick Reference

### Current Flow
```
Site Visit → Calculator Form → Calculate → Save to calculations table (JSON)
                                              ↓
                                        Estimate Import
                                              ↓
                                    CalculationImportService
                                              ↓
                                    Collapsed Line Items
```

### Enhanced Flow
```
Site Visit → Calculator Form → Calculate → Structured Output (JSON)
                                              ↓
                                        Estimate Import
                                              ↓
                            WorkAreaTemplateService + CalculationImportService
                                              ↓
                                    Granular Work Area with Task-Level Items
```

---

## Phase 1: Foundation Setup

### Step 1.1: Database Migration for Work Area Metadata

**File:** `database/migrations/2025_11_28_create_calculator_metadata_for_areas.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimate_areas', function (Blueprint $table) {
            // Link to source calculator
            $table->foreignId('calculation_id')
                ->nullable()
                ->constrained('calculations')
                ->nullOnDelete()
                ->comment('Source calculator if created from import');
            
            $table->foreignId('site_visit_id')
                ->nullable()
                ->constrained('site_visits')
                ->nullOnDelete()
                ->comment('Source site visit if applicable');
            
            // Planning metadata
            $table->decimal('planned_hours', 10, 2)
                ->nullable()
                ->comment('Total planned labor hours from calculator');
            
            $table->integer('crew_size')
                ->nullable()
                ->comment('Recommended crew size from calculator');
            
            $table->decimal('drive_time_hours', 10, 2)
                ->nullable()
                ->comment('Calculated drive time');
            
            $table->decimal('overhead_percent', 5, 2)
                ->nullable()
                ->comment('Total overhead percentage applied');
            
            // Flexible metadata storage
            $table->json('calculator_metadata')
                ->nullable()
                ->comment('Additional calculator parameters and settings');
            
            // Indexes for performance
            $table->index('calculation_id');
            $table->index('site_visit_id');
        });
    }

    public function down(): void
    {
        Schema::table('estimate_areas', function (Blueprint $table) {
            $table->dropForeign(['calculation_id']);
            $table->dropForeign(['site_visit_id']);
            $table->dropColumn([
                'calculation_id',
                'site_visit_id',
                'planned_hours',
                'crew_size',
                'drive_time_hours',
                'overhead_percent',
                'calculator_metadata',
            ]);
        });
    }
};
```

### Step 1.2: Create Calculator Output Formatter Service

**File:** `app/Services/CalculatorOutputFormatter.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Standardizes calculator outputs for consistent import to estimates
 */
class CalculatorOutputFormatter
{
    /**
     * Format labor tasks for estimate import
     * 
     * @param array $tasks Raw task data from calculator
     * @param float $laborRate Hourly labor rate
     * @param string $calculatorType Type of calculator (for context)
     * @return array Formatted labor tasks
     */
    public function formatLaborTasks(array $tasks, float $laborRate, string $calculatorType): array
    {
        $formatted = [];
        
        foreach ($tasks as $task) {
            if (empty($task['hours']) || $task['hours'] <= 0) {
                continue;
            }
            
            $formatted[] = [
                'name' => $this->formatTaskName($task['task'] ?? 'Labor'),
                'description' => $this->buildTaskDescription($task),
                'type' => 'labor',
                'unit' => 'hour',
                'quantity' => round($task['hours'], 2),
                'unit_cost' => $laborRate,
                'unit_price' => null, // Will be calculated by budget service
                'production_rate' => $task['rate'] ?? null,
                'production_unit' => $this->getProductionUnit($task),
                'production_quantity' => $task['qty'] ?? null,
                'task_key' => $task['task_key'] ?? Str::slug($task['task'] ?? 'labor'),
                'calculator_type' => $calculatorType,
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Format overhead/administrative tasks
     */
    public function formatOverheadTasks(array $data, float $laborRate): array
    {
        $overhead = [];
        
        // Drive time
        if (!empty($data['drive_time_hours']) && $data['drive_time_hours'] > 0) {
            $overhead[] = [
                'name' => 'Drive Time',
                'description' => $this->buildDriveTimeDescription($data),
                'type' => 'labor',
                'unit' => 'hour',
                'quantity' => round($data['drive_time_hours'], 2),
                'unit_cost' => $laborRate,
                'task_category' => 'overhead',
            ];
        }
        
        // Overhead (site conditions, cleanup, etc.)
        if (!empty($data['overhead_hours']) && $data['overhead_hours'] > 0) {
            $overhead[] = [
                'name' => 'Site Overhead',
                'description' => $this->buildOverheadDescription($data),
                'type' => 'labor',
                'unit' => 'hour',
                'quantity' => round($data['overhead_hours'], 2),
                'unit_cost' => $laborRate,
                'task_category' => 'overhead',
            ];
        }
        
        return $overhead;
    }
    
    /**
     * Format materials for estimate import
     */
    public function formatMaterials(array $materials, string $calculatorType): array
    {
        $formatted = [];
        
        foreach ($materials as $name => $material) {
            if (!is_array($material)) {
                continue;
            }
            
            $formatted[] = [
                'name' => $name,
                'description' => $material['description'] ?? null,
                'type' => 'material',
                'unit' => $material['unit'] ?? 'ea',
                'quantity' => $material['qty'] ?? $material['quantity'] ?? 1,
                'unit_cost' => $material['unit_cost'] ?? $material['cost'] ?? 0,
                'unit_price' => null, // Budget service will calculate
                'tax_rate' => $material['tax_rate'] ?? 0,
                'catalog_id' => $material['catalog_id'] ?? null,
                'supplier_id' => $material['supplier_id'] ?? null,
                'calculator_type' => $calculatorType,
                'is_custom' => $material['is_custom'] ?? false,
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Extract work area metadata from calculator data
     */
    public function extractAreaMetadata(array $data): array
    {
        $overheadPercent = ($data['site_conditions'] ?? 0) +
                          ($data['material_pickup'] ?? 0) +
                          ($data['cleanup'] ?? 0);
        
        return [
            'planned_hours' => $data['total_hours'] ?? null,
            'crew_size' => $data['crew_size'] ?? null,
            'drive_time_hours' => $data['drive_time_hours'] ?? null,
            'overhead_percent' => $overheadPercent > 0 ? $overheadPercent : null,
            'calculator_metadata' => [
                'labor_rate' => $data['labor_rate'] ?? null,
                'drive_distance' => $data['drive_distance'] ?? null,
                'drive_speed' => $data['drive_speed'] ?? null,
                'visits' => $data['visits'] ?? null,
                'measurements' => $this->extractMeasurements($data),
                'site_conditions' => $data['site_conditions'] ?? null,
                'material_pickup' => $data['material_pickup'] ?? null,
                'cleanup' => $data['cleanup'] ?? null,
                'job_notes' => $data['job_notes'] ?? null,
            ],
        ];
    }
    
    // Helper methods
    
    protected function formatTaskName(string $taskName): string
    {
        return Str::title(str_replace('_', ' ', $taskName));
    }
    
    protected function buildTaskDescription(array $task): ?string
    {
        $parts = [];
        
        if (!empty($task['qty'])) {
            $unit = $this->getProductionUnit($task);
            $parts[] = number_format($task['qty'], 2) . ' ' . $unit;
        }
        
        if (!empty($task['rate'])) {
            $parts[] = '@ ' . number_format($task['rate'], 4) . ' hrs/unit';
        }
        
        return !empty($parts) ? implode(' ', $parts) : null;
    }
    
    protected function buildDriveTimeDescription(array $data): string
    {
        $parts = [];
        
        if (!empty($data['drive_distance'])) {
            $parts[] = number_format($data['drive_distance'], 1) . ' miles';
        }
        
        if (!empty($data['crew_size'])) {
            $parts[] = 'crew of ' . $data['crew_size'];
        }
        
        if (!empty($data['visits'])) {
            $parts[] = $data['visits'] . ' visit' . ($data['visits'] > 1 ? 's' : '');
        }
        
        return !empty($parts) ? implode(', ', $parts) : 'Round-trip drive time';
    }
    
    protected function buildOverheadDescription(array $data): string
    {
        $factors = [];
        
        if (!empty($data['site_conditions'])) {
            $factors[] = 'Site Conditions (' . $data['site_conditions'] . '%)';
        }
        
        if (!empty($data['material_pickup'])) {
            $factors[] = 'Material Pickup (' . $data['material_pickup'] . '%)';
        }
        
        if (!empty($data['cleanup'])) {
            $factors[] = 'Cleanup (' . $data['cleanup'] . '%)';
        }
        
        return !empty($factors) ? implode(', ', $factors) : 'Site overhead and administration';
    }
    
    protected function getProductionUnit(array $task): string
    {
        return $task['production_unit'] ?? $task['unit'] ?? 'unit';
    }
    
    protected function extractMeasurements(array $data): array
    {
        $measurements = [];
        
        // Common measurement fields across calculators
        $measurementFields = [
            'length', 'width', 'height', 'area', 'area_sqft',
            'depth', 'diameter', 'perimeter', 'volume',
            'linear_feet', 'square_feet', 'cubic_yards',
        ];
        
        foreach ($measurementFields as $field) {
            if (isset($data[$field])) {
                $measurements[$field] = $data[$field];
            }
        }
        
        return $measurements;
    }
}
```

---

## Phase 2: Enhanced Import Service

### Step 2.1: Work Area Template Service

**File:** `app/Services/WorkAreaTemplateService.php`

```php
<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Models\EstimateArea;
use Illuminate\Support\Str;

/**
 * Creates and configures work areas from calculator templates
 */
class WorkAreaTemplateService
{
    public function __construct(
        protected CalculatorOutputFormatter $formatter
    ) {}
    
    /**
     * Create or get work area for calculator import
     * 
     * @param Estimate $estimate
     * @param Calculation $calculation
     * @param int|null $areaId Existing area to use, or null to create new
     * @param array $options Additional options (name, description, etc.)
     * @return EstimateArea
     */
    public function getOrCreateArea(
        Estimate $estimate,
        Calculation $calculation,
        ?int $areaId = null,
        array $options = []
    ): EstimateArea {
        
        // Use existing area if specified
        if ($areaId) {
            $area = $estimate->areas()->findOrFail($areaId);
            $this->updateAreaMetadata($area, $calculation);
            return $area;
        }
        
        // Create new area
        $data = $calculation->data ?? [];
        $metadata = $this->formatter->extractAreaMetadata($data);
        
        $areaName = $options['name'] ?? $this->generateAreaName($calculation);
        $description = $options['description'] ?? $this->generateAreaDescription($calculation, $data);
        
        $area = $estimate->areas()->create([
            'name' => $areaName,
            'description' => $description,
            'calculation_id' => $calculation->id,
            'site_visit_id' => $calculation->site_visit_id,
            'planned_hours' => $metadata['planned_hours'],
            'crew_size' => $metadata['crew_size'],
            'drive_time_hours' => $metadata['drive_time_hours'],
            'overhead_percent' => $metadata['overhead_percent'],
            'calculator_metadata' => $metadata['calculator_metadata'],
            'sort_order' => $estimate->areas()->max('sort_order') + 1,
        ]);
        
        return $area;
    }
    
    /**
     * Update existing area with calculator metadata
     */
    public function updateAreaMetadata(EstimateArea $area, Calculation $calculation): void
    {
        $data = $calculation->data ?? [];
        $metadata = $this->formatter->extractAreaMetadata($data);
        
        $area->update([
            'calculation_id' => $calculation->id,
            'site_visit_id' => $calculation->site_visit_id,
            'planned_hours' => $metadata['planned_hours'],
            'crew_size' => $metadata['crew_size'],
            'drive_time_hours' => $metadata['drive_time_hours'],
            'overhead_percent' => $metadata['overhead_percent'],
            'calculator_metadata' => array_merge(
                $area->calculator_metadata ?? [],
                $metadata['calculator_metadata']
            ),
        ]);
    }
    
    /**
     * Generate user-friendly area name from calculator
     */
    protected function generateAreaName(Calculation $calculation): string
    {
        $baseName = Str::headline($calculation->calculation_type);
        
        // Add descriptor if available
        $data = $calculation->data ?? [];
        if (!empty($data['job_notes'])) {
            $notes = Str::limit($data['job_notes'], 30);
            return "{$baseName} - {$notes}";
        }
        
        // Add site visit date if available
        if ($calculation->siteVisit) {
            $date = $calculation->siteVisit->visit_date?->format('M d');
            return "{$baseName} ({$date})";
        }
        
        return $baseName;
    }
    
    /**
     * Generate area description from calculator data
     */
    protected function generateAreaDescription(Calculation $calculation, array $data): ?string
    {
        $parts = [];
        
        // Add job notes if available
        if (!empty($data['job_notes'])) {
            $parts[] = $data['job_notes'];
        }
        
        // Add key measurements
        $measurements = $this->formatter->extractAreaMetadata($data)['calculator_metadata']['measurements'] ?? [];
        if (!empty($measurements)) {
            $measText = [];
            foreach ($measurements as $key => $value) {
                $measText[] = Str::headline($key) . ': ' . $value;
            }
            if (!empty($measText)) {
                $parts[] = 'Measurements: ' . implode(', ', $measText);
            }
        }
        
        // Add crew/hours summary
        if (!empty($data['total_hours']) && !empty($data['crew_size'])) {
            $parts[] = "Estimated {$data['total_hours']} hours with crew of {$data['crew_size']}";
        }
        
        return !empty($parts) ? implode("\n\n", $parts) : null;
    }
}
```

### Step 2.2: Enhanced Calculation Import Service

**File:** `app/Services/CalculationImportService.php` (additions)

```php
<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Models\EstimateArea;
use App\Models\SiteVisit;
use Illuminate\Support\Str;

class CalculationImportService
{
    public function __construct(
        protected EstimateItemService $items,
        protected ?BudgetService $budget = null,
        protected ?CalculatorOutputFormatter $formatter = null,
        protected ?WorkAreaTemplateService $areaService = null,
    ) {
        $this->budget ??= app(BudgetService::class);
        $this->formatter ??= app(CalculatorOutputFormatter::class);
        $this->areaService ??= app(WorkAreaTemplateService::class);
    }

    /**
     * NEW METHOD: Enhanced import to specific work area with granular tasks
     * 
     * @param Estimate $estimate
     * @param Calculation $calculation
     * @param int|null $areaId Target work area (null = create new)
     * @param array $options Import options
     * @return EstimateArea The work area containing imported items
     */
    public function importCalculationToArea(
        Estimate $estimate,
        Calculation $calculation,
        ?int $areaId = null,
        array $options = []
    ): EstimateArea {
        
        $data = $calculation->data ?? [];
        $calcType = $calculation->calculation_type;
        $laborRate = (float) ($data['labor_rate'] ?? 25.00);
        
        // Get margin from active budget
        $activeBudget = $this->budget?->active();
        $marginRate = (float) ($activeBudget?->desired_profit_margin ?? 0.0);
        
        // Remove old items from this calculation if replace option set
        if ($options['replace'] ?? true) {
            $this->items->removeCalculationItems($estimate, $calculation->id);
        }
        
        // Create or get work area
        $area = $this->areaService->getOrCreateArea($estimate, $calculation, $areaId, $options);
        
        // Import labor tasks (granular)
        $this->importLaborTasks($estimate, $area, $data, $laborRate, $calcType, $marginRate);
        
        // Import overhead tasks
        if ($options['include_overhead'] ?? true) {
            $this->importOverheadTasks($estimate, $area, $data, $laborRate, $marginRate);
        }
        
        // Import materials
        $this->importMaterialItems($estimate, $area, $data, $calcType, $marginRate);
        
        return $area;
    }
    
    /**
     * Import individual labor tasks (not collapsed)
     */
    protected function importLaborTasks(
        Estimate $estimate,
        EstimateArea $area,
        array $data,
        float $laborRate,
        string $calcType,
        float $marginRate
    ): void {
        
        // Format labor tasks
        $tasks = $data['tasks'] ?? [];
        $formattedTasks = $this->formatter->formatLaborTasks($tasks, $laborRate, $calcType);
        
        foreach ($formattedTasks as $task) {
            $this->items->createManualItem($estimate, [
                'item_type' => 'labor',
                'area_id' => $area->id,
                'name' => $task['name'],
                'description' => $task['description'],
                'unit' => $task['unit'],
                'quantity' => $task['quantity'],
                'unit_cost' => $task['unit_cost'],
                'unit_price' => $marginRate > 0 ? round($task['unit_cost'] * (1 + $marginRate), 2) : null,
                'margin_rate' => $marginRate > 0 ? $marginRate : null,
                'tax_rate' => 0,
                'source' => "calculator:{$calcType}",
                'metadata' => [
                    'calculator_type' => $calcType,
                    'production_rate' => $task['production_rate'],
                    'production_unit' => $task['production_unit'],
                    'production_quantity' => $task['production_quantity'],
                    'task_key' => $task['task_key'],
                ],
            ]);
        }
    }
    
    /**
     * Import overhead tasks (drive time, site conditions, etc.)
     */
    protected function importOverheadTasks(
        Estimate $estimate,
        EstimateArea $area,
        array $data,
        float $laborRate,
        float $marginRate
    ): void {
        
        $overheadTasks = $this->formatter->formatOverheadTasks($data, $laborRate);
        
        foreach ($overheadTasks as $task) {
            $this->items->createManualItem($estimate, [
                'item_type' => 'labor',
                'area_id' => $area->id,
                'name' => $task['name'],
                'description' => $task['description'],
                'unit' => $task['unit'],
                'quantity' => $task['quantity'],
                'unit_cost' => $task['unit_cost'],
                'unit_price' => $marginRate > 0 ? round($task['unit_cost'] * (1 + $marginRate), 2) : null,
                'margin_rate' => $marginRate > 0 ? $marginRate : null,
                'tax_rate' => 0,
                'source' => "calculator:overhead",
                'metadata' => [
                    'task_category' => $task['task_category'],
                ],
            ]);
        }
    }
    
    /**
     * Import material items
     */
    protected function importMaterialItems(
        Estimate $estimate,
        EstimateArea $area,
        array $data,
        string $calcType,
        float $marginRate
    ): void {
        
        $materials = $data['materials'] ?? [];
        $formattedMaterials = $this->formatter->formatMaterials($materials, $calcType);
        
        foreach ($formattedMaterials as $material) {
            $this->items->createManualItem($estimate, [
                'item_type' => 'material',
                'area_id' => $area->id,
                'catalog_id' => $material['catalog_id'],
                'name' => $material['name'],
                'description' => $material['description'],
                'unit' => $material['unit'],
                'quantity' => $material['quantity'],
                'unit_cost' => $material['unit_cost'],
                'unit_price' => $marginRate > 0 ? round($material['unit_cost'] * (1 + $marginRate), 2) : null,
                'margin_rate' => $marginRate > 0 ? $marginRate : null,
                'tax_rate' => $material['tax_rate'],
                'source' => "calculator:{$calcType}",
                'metadata' => [
                    'calculator_type' => $calcType,
                    'is_custom' => $material['is_custom'],
                ],
            ]);
        }
    }
    
    // KEEP EXISTING METHOD FOR BACKWARD COMPATIBILITY
    public function importCalculation(Estimate $estimate, Calculation $calculation, bool $replace = true, ?int $areaId = null): void
    {
        // Existing implementation stays for backward compatibility
        // ... (keep current code)
    }
    
    // ... (keep other existing methods)
}
```

---

## Phase 3: Calculator Updates

### Step 3.1: Example - Enhanced Planting Calculator

**File:** `app/Http/Controllers/PlantingCalculatorController.php` (enhancements)

```php
public function calculate(Request $request)
{
    // ... (existing validation code)
    
    $productionRates = ProductionRate::where('calculator', 'planting')->pluck('rate', 'task');
    $taskInputs = [];
    $unitCostInputs = [];
    $inputTasks = $request->input('tasks', []);

    $results = []; // Keep for display
    $laborTasks = []; // NEW: Structured for import
    $materials = [];
    $materialTotal = 0;
    $totalHours = 0;
    $laborRate = (float) $validated['labor_rate'];

    foreach ($productionRates as $taskKey => $ratePerUnit) {
        $qty = (float) ($inputTasks[$taskKey]['qty'] ?? 0);
        $unitCost = (float) ($inputTasks[$taskKey]['unit_cost'] ?? 0);

        $taskInputs[$taskKey] = $qty;
        $unitCostInputs[$taskKey] = $unitCost;

        if ($qty <= 0) {
            continue;
        }

        $hours = $qty * $ratePerUnit;
        $taskLabel = $this->taskLabels[$taskKey] ?? Str::title(str_replace('_', ' ', $taskKey));
        
        // EXISTING: For display in results view
        $results[] = [
            'task' => $taskLabel,
            'qty' => $qty,
            'rate' => $ratePerUnit,
            'hours' => round($hours, 2),
            'cost' => round($hours * $laborRate, 2),
        ];
        
        // NEW: Structured for import
        $laborTasks[] = [
            'task' => $taskLabel,
            'task_key' => $taskKey,
            'qty' => $qty,
            'unit' => $this->getTaskUnit($taskKey),
            'rate' => $ratePerUnit,
            'hours' => $hours,
            'production_unit' => $this->getProductionUnit($taskKey),
            'production_quantity' => $qty,
        ];

        $totalHours += $hours;

        // Materials handling
        if ($unitCost > 0) {
            $lineTotal = $qty * $unitCost;
            $materials[$taskLabel] = [
                'qty' => $qty,
                'unit' => $this->getMaterialUnit($taskKey),
                'unit_cost' => $unitCost,
                'total' => round($lineTotal, 2),
            ];
            $materialTotal += $lineTotal;
        }
    }

    // Use shared calculator service
    $calculator = new LaborCostCalculatorService();
    $totals = $calculator->calculate(
        $totalHours,
        $laborRate,
        array_merge($request->all(), ['material_total' => $materialTotal])
    );

    $data = array_merge($validated, $totals, [
        'tasks' => $results, // Display format
        'labor_tasks' => $laborTasks, // NEW: Import format
        'labor_by_task' => collect($results)->pluck('hours', 'task')->map(fn ($hours) => round($hours, 2))->toArray(),
        'labor_hours' => round($totalHours, 2),
        'materials' => $materials,
        'material_total' => round($materialTotal, 2),
        'task_inputs' => $taskInputs,
        'unit_costs' => $unitCostInputs,
    ]);

    // ... (rest of save logic)
}

// Helper methods
protected function getTaskUnit(string $taskKey): string
{
    $units = [
        'annual_flats' => 'flat',
        'annual_pots' => 'pot',
        'container_1g' => 'plant',
        'container_3g' => 'plant',
        'container_5g' => 'plant',
        'container_7g' => 'plant',
        'container_10g' => 'plant',
        'container_15g' => 'plant',
        'container_25g' => 'plant',
        'ball_and_burlap' => 'plant',
        'palm_8_12' => 'plant',
    ];
    
    return $units[$taskKey] ?? 'unit';
}

protected function getProductionUnit(string $taskKey): string
{
    return $this->getTaskUnit($taskKey);
}

protected function getMaterialUnit(string $taskKey): string
{
    return $this->getTaskUnit($taskKey);
}
```

### Step 3.2: Example - Enhanced Retaining Wall Calculator

**File:** `app/Http/Controllers/RetainingWallCalculatorController.php` (enhancements)

```php
public function calculate(Request $request)
{
    // ... (existing validation and material calculation code)
    
    $rates = ProductionRate::where('calculator', 'retaining_wall')->pluck('rate', 'task');
    $equipmentFactor = $validated['equipment'] === 'excavator' ? '_excavator' : '_manual';

    // EXISTING: Collapsed labor calculation
    $labor = [
        'excavation' => $length * ($rates["excavation$equipmentFactor"] ?? $rates['excavation'] ?? 0.1),
        'base_install' => $sqft * ($rates["base_install$equipmentFactor"] ?? $rates['base_install'] ?? 0.01),
        'block_setting' => $blockCount * ($rates['block_setting'] ?? 0.05),
        'backfill' => $sqft * ($rates['backfill'] ?? 0.015),
        'cleanup' => $sqft * ($rates['cleanup'] ?? 0.005),
    ];

    $wallLabor = array_sum($labor);
    
    // NEW: Structured labor tasks for import
    $laborTasks = [
        [
            'task' => 'Excavation',
            'task_key' => 'excavation',
            'qty' => $length,
            'unit' => 'lf',
            'rate' => $rates["excavation$equipmentFactor"] ?? $rates['excavation'] ?? 0.1,
            'hours' => $labor['excavation'],
            'production_unit' => 'linear foot',
            'production_quantity' => $length,
            'notes' => "Equipment: {$validated['equipment']}",
        ],
        [
            'task' => 'Base Installation',
            'task_key' => 'base_install',
            'qty' => $sqft,
            'unit' => 'sqft',
            'rate' => $rates["base_install$equipmentFactor"] ?? $rates['base_install'] ?? 0.01,
            'hours' => $labor['base_install'],
            'production_unit' => 'square foot',
            'production_quantity' => $sqft,
        ],
        [
            'task' => 'Block Setting',
            'task_key' => 'block_setting',
            'qty' => $blockCount,
            'unit' => 'block',
            'rate' => $rates['block_setting'] ?? 0.05,
            'hours' => $labor['block_setting'],
            'production_unit' => 'block',
            'production_quantity' => $blockCount,
        ],
        [
            'task' => 'Backfill & Compaction',
            'task_key' => 'backfill',
            'qty' => $sqft,
            'unit' => 'sqft',
            'rate' => $rates['backfill'] ?? 0.015,
            'hours' => $labor['backfill'],
            'production_unit' => 'square foot',
            'production_quantity' => $sqft,
        ],
        [
            'task' => 'Site Cleanup',
            'task_key' => 'cleanup',
            'qty' => $sqft,
            'unit' => 'sqft',
            'rate' => $rates['cleanup'] ?? 0.005,
            'hours' => $labor['cleanup'],
            'production_unit' => 'square foot',
            'production_quantity' => $sqft,
        ],
    ];

    // Use shared calculator
    $calculator = new LaborCostCalculatorService();
    $totals = $calculator->calculate(
        $wallLabor,
        $validated['labor_rate'],
        array_merge($validated, ['material_total' => $material_total])
    );

    $data = array_merge($validated, [
        // ... existing fields
        'labor_by_task' => $labor, // Keep for display
        'labor_tasks' => $laborTasks, // NEW: For import
        'labor_hours' => $wallLabor,
        'materials' => $materials,
        'material_total' => round($material_total, 2),
    ], $totals);

    // ... (rest of save logic)
}
```

---

## Phase 4: UI Integration

### Step 4.1: Enhanced Import Button/Modal

**File:** `resources/views/estimates/partials/import-calculator-modal.blade.php` (new file)

```blade
<div x-data="importCalculatorModal()" 
     x-show="show" 
     x-cloak
     @open-import-calculator.window="openModal($event.detail)"
     class="fixed inset-0 z-50 overflow-y-auto">
    
    <!-- Backdrop -->
    <div x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

    <!-- Modal -->
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             class="relative inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">
            
            <!-- Header -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Import Calculator to Estimate
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Configure how this calculator should be imported
                        </p>
                    </div>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <form @submit.prevent="importCalculator()" class="px-4 pb-4 sm:px-6">
                <!-- Work Area Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Work Area
                    </label>
                    <div class="space-y-2">
                        <!-- Create New Area (default) -->
                        <label class="flex items-center">
                            <input type="radio" 
                                   x-model="workAreaOption" 
                                   value="new" 
                                   class="form-radio">
                            <span class="ml-2">Create new work area</span>
                        </label>
                        
                        <!-- Area Name Input (shown when creating new) -->
                        <div x-show="workAreaOption === 'new'" class="ml-6">
                            <input type="text" 
                                   x-model="newAreaName" 
                                   class="form-input w-full rounded-md"
                                   placeholder="e.g., Front Yard Planting">
                        </div>

                        <!-- Use Existing Area -->
                        <label class="flex items-center">
                            <input type="radio" 
                                   x-model="workAreaOption" 
                                   value="existing" 
                                   class="form-radio">
                            <span class="ml-2">Add to existing work area</span>
                        </label>
                        
                        <!-- Area Selection (shown when using existing) -->
                        <div x-show="workAreaOption === 'existing'" class="ml-6">
                            <select x-model="existingAreaId" class="form-select w-full rounded-md">
                                <option value="">Select work area...</option>
                                <template x-for="area in areas" :key="area.id">
                                    <option :value="area.id" x-text="area.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Import Options -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Import Options
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   x-model="includeOverhead" 
                                   class="form-checkbox">
                            <span class="ml-2">Include overhead tasks (drive time, site conditions)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   x-model="replacePrevious" 
                                   class="form-checkbox">
                            <span class="ml-2">Replace previous import from this calculator</span>
                        </label>
                    </div>
                </div>

                <!-- Preview -->
                <div class="mb-4 rounded-md bg-gray-50 p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Import Summary</h4>
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <dt class="text-gray-500">Labor Hours</dt>
                            <dd class="font-medium" x-text="preview.laborHours + ' hrs'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Material Cost</dt>
                            <dd class="font-medium" x-text="'$' + preview.materialCost"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Labor Tasks</dt>
                            <dd class="font-medium" x-text="preview.laborTasks + ' items'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Material Items</dt>
                            <dd class="font-medium" x-text="preview.materialItems + ' items'"></dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button type="button" 
                            @click="show = false"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-md bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                        Import Calculator
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function importCalculatorModal() {
    return {
        show: false,
        calculationId: null,
        workAreaOption: 'new',
        newAreaName: '',
        existingAreaId: '',
        includeOverhead: true,
        replacePrevious: true,
        areas: window.__estimateSetup?.areas || [],
        preview: {
            laborHours: 0,
            materialCost: 0,
            laborTasks: 0,
            materialItems: 0,
        },
        
        openModal(detail) {
            this.calculationId = detail.calculationId;
            this.newAreaName = detail.suggestedName || '';
            this.loadPreview(detail.calculation);
            this.show = true;
        },
        
        loadPreview(calculation) {
            const data = calculation.data || {};
            this.preview = {
                laborHours: data.total_hours || 0,
                materialCost: data.material_total || 0,
                laborTasks: (data.labor_tasks || []).length,
                materialItems: Object.keys(data.materials || {}).length,
            };
        },
        
        async importCalculator() {
            const areaId = this.workAreaOption === 'existing' ? this.existingAreaId : null;
            
            const response = await fetch(`/api/estimates/${window.__estimateId}/import-calculation`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    calculation_id: this.calculationId,
                    area_id: areaId,
                    area_name: this.newAreaName,
                    include_overhead: this.includeOverhead,
                    replace: this.replacePrevious,
                }),
            });
            
            if (response.ok) {
                const result = await response.json();
                window.location.reload(); // Or update UI dynamically
            } else {
                alert('Import failed. Please try again.');
            }
        }
    };
}
</script>
```

### Step 4.2: Import API Endpoint

**File:** `routes/web.php` or `routes/api.php`

```php
Route::post('/estimates/{estimate}/import-calculation', [EstimateCalculatorController::class, 'importCalculation'])
    ->name('estimates.import-calculation');
```

**File:** `app/Http/Controllers/EstimateCalculatorController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Services\CalculationImportService;
use Illuminate\Http\Request;

class EstimateCalculatorController extends Controller
{
    public function __construct(
        protected CalculationImportService $importService
    ) {}
    
    public function importCalculation(Request $request, Estimate $estimate)
    {
        $validated = $request->validate([
            'calculation_id' => 'required|exists:calculations,id',
            'area_id' => 'nullable|exists:estimate_areas,id',
            'area_name' => 'nullable|string|max:255',
            'include_overhead' => 'boolean',
            'replace' => 'boolean',
        ]);
        
        $calculation = Calculation::findOrFail($validated['calculation_id']);
        
        $area = $this->importService->importCalculationToArea(
            $estimate,
            $calculation,
            $validated['area_id'] ?? null,
            [
                'name' => $validated['area_name'] ?? null,
                'include_overhead' => $validated['include_overhead'] ?? true,
                'replace' => $validated['replace'] ?? true,
            ]
        );
        
        // Recalculate estimate totals
        $estimate->recalculate();
        
        return response()->json([
            'success' => true,
            'area' => $area,
            'message' => 'Calculator imported successfully',
        ]);
    }
}
```

---

## Testing Strategies

### Unit Tests

**File:** `tests/Unit/Services/CalculatorOutputFormatterTest.php`

```php
<?php

namespace Tests\Unit\Services;

use App\Services\CalculatorOutputFormatter;
use Tests\TestCase;

class CalculatorOutputFormatterTest extends TestCase
{
    protected CalculatorOutputFormatter $formatter;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new CalculatorOutputFormatter();
    }
    
    /** @test */
    public function it_formats_labor_tasks_correctly()
    {
        $tasks = [
            [
                'task' => 'container_5g',
                'qty' => 50,
                'rate' => 0.22,
                'hours' => 11,
            ],
        ];
        
        $formatted = $this->formatter->formatLaborTasks($tasks, 25.00, 'planting');
        
        $this->assertCount(1, $formatted);
        $this->assertEquals('Container 5g', $formatted[0]['name']);
        $this->assertEquals(11, $formatted[0]['quantity']);
        $this->assertEquals(25.00, $formatted[0]['unit_cost']);
        $this->assertEquals(0.22, $formatted[0]['production_rate']);
    }
    
    /** @test */
    public function it_extracts_area_metadata()
    {
        $data = [
            'total_hours' => 14.2,
            'crew_size' => 2,
            'drive_time_hours' => 1.0,
            'site_conditions' => 10,
            'material_pickup' => 5,
            'cleanup' => 5,
        ];
        
        $metadata = $this->formatter->extractAreaMetadata($data);
        
        $this->assertEquals(14.2, $metadata['planned_hours']);
        $this->assertEquals(2, $metadata['crew_size']);
        $this->assertEquals(1.0, $metadata['drive_time_hours']);
        $this->assertEquals(20, $metadata['overhead_percent']);
    }
}
```

### Integration Tests

**File:** `tests/Feature/CalculatorImportTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Models\User;
use App\Services\CalculationImportService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalculatorImportTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_imports_calculator_to_new_work_area()
    {
        $user = User::factory()->create();
        $estimate = Estimate::factory()->create();
        
        $calculation = Calculation::factory()->create([
            'calculation_type' => 'planting',
            'data' => [
                'labor_rate' => 25.00,
                'crew_size' => 2,
                'total_hours' => 14.2,
                'labor_tasks' => [
                    [
                        'task' => '5 Gallon Containers',
                        'qty' => 50,
                        'rate' => 0.22,
                        'hours' => 11,
                    ],
                ],
                'materials' => [
                    '5 Gallon Containers' => [
                        'qty' => 50,
                        'unit_cost' => 15.00,
                        'total' => 750,
                    ],
                ],
                'material_total' => 750,
            ],
        ]);
        
        $importService = app(CalculationImportService::class);
        $area = $importService->importCalculationToArea($estimate, $calculation);
        
        $this->assertDatabaseHas('estimate_areas', [
            'estimate_id' => $estimate->id,
            'calculation_id' => $calculation->id,
            'planned_hours' => 14.2,
            'crew_size' => 2,
        ]);
        
        // Check labor items created
        $this->assertDatabaseHas('estimate_items', [
            'estimate_id' => $estimate->id,
            'area_id' => $area->id,
            'item_type' => 'labor',
            'quantity' => 11,
        ]);
        
        // Check material items created
        $this->assertDatabaseHas('estimate_items', [
            'estimate_id' => $estimate->id,
            'area_id' => $area->id,
            'item_type' => 'material',
            'quantity' => 50,
        ]);
    }
}
```

---

## Rollout Checklist

### Pre-Launch
- [ ] Database migrations run on staging
- [ ] All calculators updated with labor_tasks format
- [ ] Import service tested with real site visit data
- [ ] UI tested across different screen sizes
- [ ] Performance tested with large estimates (100+ items)
- [ ] Backup plan documented for rollback

### Launch Day
- [ ] Deploy during low-usage window
- [ ] Monitor error logs closely
- [ ] Have team available for quick fixes
- [ ] Communicate changes to estimators
- [ ] Provide quick reference guide

### Post-Launch
- [ ] Gather user feedback
- [ ] Monitor import success rates
- [ ] Track time savings metrics
- [ ] Identify edge cases
- [ ] Plan refinements based on usage

---

## Support & Troubleshooting

### Common Issues

**Issue:** Imported labor hours don't match calculator total
- **Cause:** Overhead tasks not included or double-counted
- **Fix:** Check `include_overhead` option, verify LaborCostCalculatorService consistency

**Issue:** Materials missing catalog IDs
- **Cause:** Material names don't match catalog
- **Fix:** Improve fuzzy matching in formatter, or manual catalog assignment post-import

**Issue:** Work areas multiply on repeated imports
- **Cause:** Replace option not working
- **Fix:** Ensure calculation_id properly tracks and removes old items

### Debug Tools

Add to `.env`:
```
CALCULATOR_DEBUG=true
```

Log calculator imports:
```php
if (config('app.calculator_debug')) {
    \Log::channel('calculator')->info('Import started', [
        'calculation_id' => $calculation->id,
        'estimate_id' => $estimate->id,
    ]);
}
```

---

## Next Steps

After implementation:
1. **User Training:** Create video walkthrough of import flow
2. **Documentation:** Update estimator handbook with new workflow
3. **Templates:** Build library of common calculator templates
4. **Analytics:** Track which calculators are most used
5. **Optimization:** Refine production rates based on actual job data

Ready to revolutionize your estimate workflow! 🚀
