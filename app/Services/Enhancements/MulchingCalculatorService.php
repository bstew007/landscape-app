<?php

namespace App\Services\Enhancements;

use App\Models\ProductionRate;

class MulchingCalculatorService implements EnhancementCalculatorInterface
{
    public function calculate(array $data): array
    {
        // Base Inputs
        $sqft = $data['sqft'] ?? 0;
        $depth = $data['depth_in_inches'] ?? 0;
        $mulchType = $data['mulch_type'] ?? 'Triple Shredded Hardwood';
        $delivery = $data['delivery_method'] ?? 'wheelbarrow'; // 'wheelbarrow' or 'tractor'
        $installType = $data['install_type'] ?? 'standard'; // 'standard', 'heavy', 'refresh'
        $laborRate = $data['labor_rate'] ?? 45.00;
        $crewSize = $data['crew_size'] ?? 1;

        // Cubic yards = sqft × depth / 324
        $cubicYards = round($sqft * $depth / 324, 2);

        // Material cost
        $costPerCYMap = [
            'Triple Shredded Hardwood' => 35,
            'Forest Brown' => 40,
            'Red' => 42,
            'Pine Fines' => 38,
            'Big Nuggets' => 50,
            'Mini Nuggets' => 48,
        ];

        $overrideCostPerCY = $data['override_material_cost_per_cy'] ?? null;
        $costPerCY = is_numeric($overrideCostPerCY)
            ? $overrideCostPerCY
            : ($costPerCYMap[$mulchType] ?? 40);

        $materialCost = round($cubicYards * $costPerCY, 2);

        // ➤ INSTALLATION TASK
        $installTaskKey = "mulch_install_{$installType}_{$delivery}";
        $installRate = ProductionRate::getRate('mulching', $installTaskKey);
        $installHours = round($cubicYards * $installRate, 2);
        $installCost = round($installHours * $laborRate * $crewSize, 2);

        $tasks = [[
            'task' => "Mulch Installation ({$installType}, {$delivery})",
            'qty' => $cubicYards,
            'unit' => 'cubic yards',
            'prod_rate' => $installRate,
            'hours' => $installHours,
            'cost' => $installCost,
        ]];

        $totalHours = $installHours;
        $totalLaborCost = $installCost;

        // ➤ BED EDGING
        if (!empty($data['include_bed_edging'])) {
            $edgeMethod = $data['bed_edging_method'] ?? 'manual';
            $edgeLength = $data['bed_edging_length_lf'] ?? 0;
            $edgeTaskKey = $edgeMethod === 'mechanical'
                ? 'mulch_bed_edge_mechanical'
                : 'mulch_bed_edge_manual';

            $edgeRate = ProductionRate::getRate('mulching', $edgeTaskKey);
            $edgeHours = round($edgeLength * $edgeRate, 2);
            $edgeCost = round($edgeHours * $laborRate * $crewSize, 2);

            $tasks[] = [
                'task' => "Bed Edging ({$edgeMethod})",
                'qty' => $edgeLength,
                'unit' => 'linear feet',
                'prod_rate' => $edgeRate,
                'hours' => $edgeHours,
                'cost' => $edgeCost,
            ];

            $totalHours += $edgeHours;
            $totalLaborCost += $edgeCost;
        }

        // ➤ FINAL CLEANUP
        if (!empty($data['include_final_cleanup'])) {
            $cleanupTaskKey = 'mulch_cleanup_final';
            $cleanupRate = ProductionRate::getRate('mulching', $cleanupTaskKey);
            $cleanupHours = round($cubicYards * $cleanupRate, 2);
            $cleanupCost = round($cleanupHours * $laborRate * $crewSize, 2);

            $tasks[] = [
                'task' => 'Final Mulch Cleanup / Detailing',
                'qty' => $cubicYards,
                'unit' => 'cubic yards',
                'prod_rate' => $cleanupRate,
                'hours' => $cleanupHours,
                'cost' => $cleanupCost,
            ];

            $totalHours += $cleanupHours;
            $totalLaborCost += $cleanupCost;
        }

        // 📦 Return Standardized Output
        return [
            'description' => 'Mulching',
            'mulch_type' => $mulchType,
            'sqft' => $sqft,
            'depth' => $depth,
            'delivery_method' => $delivery,
            'install_type' => $installType,
            'cubic_yards' => $cubicYards,
            'material_cost' => $materialCost,
            'rate_per_hour_per_person' => $installRate,
            'labor_rate' => $laborRate,
            'crew_size' => $crewSize,
            'labor_hours' => $totalHours,
            'labor_cost' => $totalLaborCost,
            'total_cost' => round($materialCost + $totalLaborCost, 2),
            'cost_per_cy' => $costPerCY,
            'tasks' => $tasks,

            // ✅ ADD THIS BLOCK for materials summary:
            'materials' => [
            'label' => $mulchType,
            'qty' => $cubicYards,
            'unit' => 'cubic yards',
            'unit_cost' => $costPerCY,
            'total_cost' => $materialCost,
    ],
        ];
    }
}

