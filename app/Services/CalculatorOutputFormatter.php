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
            // Handle both old and new task formats
            $hours = $task['hours'] ?? 0;
            $taskName = $task['task_name'] ?? $task['task'] ?? 'Labor';
            $description = $task['description'] ?? '';
            $quantity = $task['quantity'] ?? null;
            $unit = $task['unit'] ?? 'hour';
            $productionRate = $task['production_rate'] ?? $task['rate'] ?? null;
            $taskKey = $task['task_key'] ?? Str::slug($taskName);
            
            if ($hours <= 0) {
                continue;
            }
            
            $formatted[] = [
                'name' => $this->formatTaskName($taskName),
                'description' => $description ?: $this->buildTaskDescription($task),
                'type' => 'labor',
                'unit' => 'hour',
                'quantity' => round($hours, 2),
                'unit_cost' => $laborRate,
                'unit_price' => null, // Will be calculated by budget service
                'production_rate' => $productionRate,
                'production_unit' => $unit !== 'hour' ? $unit : $this->getProductionUnit($task),
                'production_quantity' => $quantity ?? $task['qty'] ?? null,
                'task_key' => $taskKey,
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
            
            // Support both old format (name as key) and new format (name as property)
            $materialName = $material['name'] ?? $name;
            
            $formatted[] = [
                'name' => $materialName,
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
