<?php

namespace App\Services\Enhancements;

use App\Models\ProductionRate;

class PineNeedleCalculatorService implements EnhancementCalculatorInterface
{
    protected array $tasks = [
        'pine_needles_open_area' => ['label' => 'Pine Needle Installation – Open Areas'],
        'pine_needles_around_plants' => ['label' => 'Pine Needle Installation – Around Plants / Beds'],
        'pine_needles_heavy_prep' => ['label' => 'Pine Needle Installation – Heavy Prep / Re-Edge'],
        'pine_needles_refresh_light' => ['label' => 'Pine Needle Refresh / Touch-Up'],
        'pine_needles_delivery_stage' => ['label' => 'Pine Needle Delivery & Staging'],
        'pine_needles_cleanup_final' => ['label' => 'Final Straw Cleanup / Detail'],
    ];

    public function calculate(array $input): array
    {
        $sqft = $input['sqft'] ?? null;
        $depth = $input['depth_in_inches'] ?? 2;
        $laborRate = $input['labor_rate'] ?? 45.00;
        $crewSize = $input['crew_size'] ?? 1;

        $BALE_COVERAGE_AT_2_INCHES = 45;

        // Calculate total bales needed if square footage is given
        $calculatedBales = 0;
        if ($sqft) {
            $depthRatio = $depth / 2;
            $calculatedBales = round(($sqft / $BALE_COVERAGE_AT_2_INCHES) * $depthRatio, 2);
        }

        $tasks = [];
        $totalHours = 0;
        $totalCost = 0;
        $totalBales = 0;

        foreach ($this->tasks as $key => $meta) {
            // Use explicit bales if given per task, fallback to proportion of calculatedBales
            $bales = $input[$key]['bales'] ?? null;

            // Optional: evenly split calculated bales if none provided
            if ($bales === null && $calculatedBales > 0) {
                $bales = round($calculatedBales / count($this->tasks), 2);
            }

            if (!$bales || $bales <= 0) {
                continue;
            }

            $rate = ProductionRate::getRate('pine_needles', $key);
            $hours = round($bales * $rate, 2);
            $cost = round($hours * $laborRate * $crewSize, 2);

            $tasks[] = [
                'task' => $meta['label'],
                'qty' => $bales,
                'unit' => 'bales',
                'prod_rate' => $rate,
                'hours' => $hours,
                'cost' => $cost,
            ];

            $totalBales += $bales;
            $totalHours += $hours;
            $totalCost += $cost;
        }

        // Optional: Material pricing
        $unitCost = $input['cost_per_bale'] ?? 6.00; // or override input
        $materialCost = round($totalBales * $unitCost, 2);

        return [
            'description' => 'Pine Needle Mulching',
            'sqft' => $sqft,
            'depth' => $depth,
            'bales' => $totalBales,
            'cost_per_bale' => $unitCost,
            'material_cost' => $materialCost,
            'tasks' => $tasks,
            'labor_hours' => $totalHours,
            'labor_cost' => $totalCost,
            'total_cost' => $materialCost + $totalCost,
            'crew_size' => $crewSize,
            'labor_rate' => $laborRate,

             // ✅ Add this block:
            'materials' => [
            'label' => 'Pine Straw',
            'qty' => $totalBales,
            'unit' => 'bales',
            'unit_cost' => $unitCost,
            'total_cost' => $materialCost,
    ],
        ];
    }
}

