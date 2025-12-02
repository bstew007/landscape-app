<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Models\SiteVisit;
use Illuminate\Support\Str;
use App\Services\BudgetService;

class CalculationImportService
{
    public function __construct(
        protected EstimateItemService $items,
        protected ?BudgetService $budget = null,
        protected ?CalculatorOutputFormatter $formatter = null,
        protected ?WorkAreaTemplateService $areaService = null,
    ) {
        if (!$this->budget) {
            $this->budget = app(BudgetService::class);
        }
        if (!$this->formatter) {
            $this->formatter = app(CalculatorOutputFormatter::class);
        }
        if (!$this->areaService) {
            $this->areaService = app(WorkAreaTemplateService::class);
        }
    }

    /**
     * Enhanced import to specific work area with granular task-level items
     * 
     * @param Estimate $estimate
     * @param Calculation $calculation
     * @param int|null $areaId Target work area (null = create new)
     * @param array $options Import options
     * @return \App\Models\EstimateArea The work area containing imported items
     */
    public function importCalculationToArea(
        Estimate $estimate,
        Calculation $calculation,
        ?int $areaId = null,
        array $options = []
    ): \App\Models\EstimateArea {
        
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
        
        // Check if calculator has new labor_tasks format
        $hasLaborTasks = !empty($data['labor_tasks']) && is_array($data['labor_tasks']);
        
        if ($hasLaborTasks) {
            // Import granular labor tasks
            $this->importLaborTasks($estimate, $area, $data, $laborRate, $calcType, $marginRate);
        } else {
            // Fall back to old collapsed labor import
            $this->importLabor($estimate, $data, Str::headline($calcType), $calculation, $marginRate, $area->id);
        }
        
        // Import overhead tasks
        if ($options['include_overhead'] ?? true) {
            $this->importOverheadTasks($estimate, $area, $data, $laborRate, $marginRate);
        }
        
        // Import materials
        $this->importMaterialItems($estimate, $area, $data, $calcType, $marginRate, $calculation);
        
        return $area;
    }

    /**
     * Import individual labor tasks (not collapsed)
     */
    protected function importLaborTasks(
        Estimate $estimate,
        \App\Models\EstimateArea $area,
        array $data,
        float $laborRate,
        string $calcType,
        float $marginRate
    ): void {
        
        // Format labor tasks
        $tasks = $data['labor_tasks'] ?? [];
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
                'unit_price' => $marginRate > 0 ? round($task['unit_cost'] / (1 - $marginRate), 2) : $task['unit_cost'],
                'margin_rate' => $marginRate > 0 ? $marginRate : 0,
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
        \App\Models\EstimateArea $area,
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
                'unit_price' => $marginRate > 0 ? round($task['unit_cost'] / (1 - $marginRate), 2) : $task['unit_cost'],
                'margin_rate' => $marginRate > 0 ? $marginRate : 0,
                'tax_rate' => 0,
                'source' => "calculator:overhead",
                'metadata' => [
                    'task_category' => $task['task_category'],
                ],
            ]);
        }
    }
    
    /**
     * Import material items with enhanced formatting
     */
    protected function importMaterialItems(
        Estimate $estimate,
        \App\Models\EstimateArea $area,
        array $data,
        string $calcType,
        float $marginRate,
        Calculation $calculation
    ): void {
        
        // All calculators now use 'materials' field for catalog materials only
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
                'unit_price' => $marginRate > 0 ? round($material['unit_cost'] / (1 - $marginRate), 2) : $material['unit_cost'],
                'margin_rate' => $marginRate > 0 ? $marginRate : 0,
                'tax_rate' => $material['tax_rate'],
                'source' => "calculator:{$calcType}",
                'calculation_id' => $calculation->id,
                'metadata' => [
                    'calculation_id' => $calculation->id,
                    'calculation_type' => $calcType,
                    'is_custom' => $material['is_custom'],
                ],
            ]);
        }
    }

    /**
     * LEGACY METHOD - Keep for backward compatibility
     * Use importCalculationToArea() for new implementations
     */
    public function importCalculation(Estimate $estimate, Calculation $calculation, bool $replace = true, ?int $areaId = null): void
    {
        $data = $calculation->data ?? [];
        $calcLabel = Str::headline($calculation->calculation_type);
        $activeBudget = $this->budget?->active();
        $marginRate = (float) (($activeBudget?->desired_profit_margin) ?? 0.0);

        if ($replace) {
            $this->items->removeCalculationItems($estimate, $calculation->id);
        }

        $materialsCreated = $this->importMaterials($estimate, $data, $calcLabel, $calculation, $marginRate, $areaId);
        $laborTotal = $this->importLabor($estimate, $data, $calcLabel, $calculation, $marginRate, $areaId);

        $materialTotal = array_reduce($materialsCreated, fn ($carry, $item) => $carry + $item, 0);
        // If a budget margin is set (> 0), we distribute profit in line items and skip fee markup
        if ($marginRate <= 0) {
            $this->importFeeOrMarkup($estimate, $data, $materialTotal + $laborTotal, $calcLabel, $calculation);
        }
    }

    protected function importMaterials(Estimate $estimate, array $data, string $calcLabel, Calculation $calculation, float $marginRate = 0, ?int $areaId = null): array
    {
        $materials = $data['materials'] ?? [];
        $totals = [];

        if (is_array($materials) && !empty($materials)) {
            foreach ($materials as $name => $material) {
                if (!is_array($material)) {
                    continue;
                }

                $qty = (float) ($material['qty'] ?? $material['quantity'] ?? 1);
                $unitCost = (float) ($material['unit_cost'] ?? $material['cost'] ?? 0);
                $total = (float) ($material['total'] ?? ($qty * $unitCost));

                $this->items->createManualItem($estimate, [
                    'item_type' => 'material',
                    'area_id' => $areaId,
                    'name' => is_string($name) ? $name : $calcLabel . ' Material',
                    'description' => $material['description'] ?? null,
                    'unit' => $material['unit'] ?? null,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'unit_price' => $marginRate > 0 ? round($unitCost * (1 + $marginRate), 2) : null,
                    'margin_rate' => $marginRate > 0 ? $marginRate : null,
                    'tax_rate' => (float) ($material['tax_rate'] ?? 0),
                    'source' => 'calculator:' . $calculation->calculation_type,
                    'calculation_id' => $calculation->id,
                    'metadata' => [
                        'calculation_id' => $calculation->id,
                        'calculation_type' => $calculation->calculation_type,
                    ],
                ]);

                $totals[] = $total;
            }

            return $totals;
        }

        $materialTotal = (float) ($data['material_total'] ?? 0);
        if ($materialTotal > 0) {
            $this->items->createManualItem($estimate, [
                'item_type' => 'material',
                'area_id' => $areaId,
                'name' => "{$calcLabel} Materials",
                'quantity' => 1,
                'unit' => 'lot',
                'unit_cost' => $materialTotal,
                'unit_price' => $marginRate > 0 ? round($materialTotal * (1 + $marginRate), 2) : null,
                'margin_rate' => $marginRate > 0 ? $marginRate : null,
                'tax_rate' => 0,
                'source' => 'calculator:' . $calculation->calculation_type,
                'calculation_id' => $calculation->id,
                'metadata' => [
                    'calculation_id' => $calculation->id,
                    'calculation_type' => $calculation->calculation_type,
                ],
            ]);

            return [$materialTotal];
        }

        return [];
    }

    protected function importLabor(Estimate $estimate, array $data, string $calcLabel, Calculation $calculation, float $marginRate = 0, ?int $areaId = null): float
    {
        $laborCost = (float) ($data['labor_cost'] ?? 0);

        if ($laborCost <= 0) {
            return 0;
        }

        $hours = (float) ($data['total_hours'] ?? $data['base_hours'] ?? 1);
        if ($hours <= 0) {
            $hours = 1;
        }

        $unitCost = $laborCost / $hours;

        $this->items->createManualItem($estimate, [
            'item_type' => 'labor',
            'area_id' => $areaId,
            'name' => "{$calcLabel} Labor",
            'unit' => 'hr',
            'quantity' => $hours,
            'unit_cost' => $unitCost,
            'unit_price' => $marginRate > 0 ? round($unitCost * (1 + $marginRate), 2) : null,
            'margin_rate' => $marginRate > 0 ? $marginRate : null,
            'tax_rate' => 0,
            'source' => 'calculator:' . $calculation->calculation_type,
            'calculation_id' => $calculation->id,
            'metadata' => [
                'calculation_id' => $calculation->id,
                'calculation_type' => $calculation->calculation_type,
            ],
        ]);

        return $laborCost;
    }

    protected function importFeeOrMarkup(Estimate $estimate, array $data, float $currentSum, string $calcLabel, Calculation $calculation): void
    {
        $finalPrice = (float) ($data['final_price'] ?? $data['total'] ?? 0);

        if ($finalPrice <= 0) {
            return;
        }

        $difference = $finalPrice - $currentSum;

        if ($difference >= 1) {
            $this->items->createManualItem($estimate, [
                'item_type' => 'fee',
                'name' => "{$calcLabel} Markup/Overhead",
                'unit' => 'lot',
                'quantity' => 1,
                // Treat fee as pure revenue (no cost), so margin reflects the difference
                'unit_cost' => 0,
                'unit_price' => $difference,
                'tax_rate' => 0,
                'source' => 'calculator:' . $calculation->calculation_type,
                'calculation_id' => $calculation->id,
                'metadata' => [
                    'calculation_id' => $calculation->id,
                    'calculation_type' => $calculation->calculation_type,
                ],
            ]);
        }
    }

    public function importSiteVisitCalculations(Estimate $estimate, SiteVisit $siteVisit, bool $replace = true): void
    {
        $siteVisit->loadMissing('calculations');

        foreach ($siteVisit->calculations as $calculation) {
            $this->importCalculation($estimate, $calculation, $replace);
        }
    }
}
