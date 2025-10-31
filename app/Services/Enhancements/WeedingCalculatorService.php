<?php
namespace App\Services\Enhancements;

use App\Models\ProductionRate;

class WeedingCalculatorService implements EnhancementCalculatorInterface
{
    protected array $tasks = [
        'hand_weeding_light' => ['label' => 'Hand Weeding – Light', 'unit' => 'sqft', 'input_key' => 'sqft'],
        'hand_weeding_heavy' => ['label' => 'Hand Weeding – Heavy', 'unit' => 'sqft', 'input_key' => 'sqft'],
        'hand_weeding_natural_areas' => ['label' => 'Hand Weeding – Natural Areas', 'unit' => 'sqft', 'input_key' => 'sqft'],

        'spray_spot_beds' => ['label' => 'Spot Spraying – Ornamental Beds', 'unit' => 'sqft', 'input_key' => 'sqft'],
        'spray_broadcast_beds' => ['label' => 'Broadcast Spraying – Ornamental Beds', 'unit' => 'sqft', 'input_key' => 'sqft'],
        'spray_spot_natural_areas' => ['label' => 'Spot Spraying – Natural Areas', 'unit' => 'sqft', 'input_key' => 'sqft'],

        'weed_eat_bed_edges' => ['label' => 'Weed-Eating – Bed Edge Control', 'unit' => 'linear feet', 'input_key' => 'lf'],
        'weed_eat_natural_areas' => ['label' => 'Weed-Eating – Naturalized / Rough Areas', 'unit' => 'sqft', 'input_key' => 'sqft'],
    ];

    public function calculate(array $input): array
    {
        $laborRate = $input['labor_rate'] ?? 45.00;
        $crewSize = $input['crew_size'] ?? 1;

        $totalHours = 0;
        $totalCost = 0;
        $tasks = [];

        foreach ($this->tasks as $key => $meta) {
            $qty = $input[$key][$meta['input_key']] ?? 0;

            if ($qty <= 0) continue;

            $rate = ProductionRate::getRate('weeding', $key);
            $hours = round($qty * $rate, 2);
            $cost = round($hours * $laborRate * $crewSize, 2);

            $tasks[] = [
                'task' => $meta['label'],
                'qty' => $qty,
                'unit' => $meta['unit'],
                'prod_rate' => $rate,
                'hours' => $hours,
                'cost' => $cost,
            ];

            $totalHours += $hours;
            $totalCost += $cost;
        }

        return [
            'description' => 'Weeding',
            'tasks' => $tasks,
            'labor_hours' => $totalHours,
            'labor_cost' => $totalCost,
            'total_cost' => $totalCost,
            'crew_size' => $crewSize,
            'labor_rate' => $laborRate,
        ];
    }
}
