<?php
namespace App\Services\Enhancements;

use App\Models\ProductionRate;

class PineNeedleCalculatorService implements EnhancementCalculatorInterface
{
    protected array $tasks = [
        'pine_needles_open_area' => ['label' => 'Pine Needle Installation – Open Areas', 'input_key' => 'bales'],
        'pine_needles_around_plants' => ['label' => 'Pine Needle Installation – Around Plants / Beds', 'input_key' => 'bales'],
        'pine_needles_heavy_prep' => ['label' => 'Pine Needle Installation – Heavy Prep / Re-Edge', 'input_key' => 'bales'],
        'pine_needles_refresh_light' => ['label' => 'Pine Needle Refresh / Touch-Up', 'input_key' => 'bales'],
        'pine_needles_delivery_stage' => ['label' => 'Pine Needle Delivery & Staging', 'input_key' => 'bales'],
        'pine_needles_cleanup_final' => ['label' => 'Final Straw Cleanup / Detail', 'input_key' => 'bales'],
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

            if ($qty <= 0) {
                continue;
            }

            $rate = ProductionRate::getRate('pine_needles', $key);
            $hours = round($qty * $rate, 2);
            $cost = round($hours * $laborRate * $crewSize, 2);

            $tasks[] = [
                'task' => $meta['label'],
                'qty' => $qty,
                'unit' => 'bales',
                'prod_rate' => $rate,
                'hours' => $hours,
                'cost' => $cost,
            ];

            $totalHours += $hours;
            $totalCost += $cost;
        }

        return [
            'description' => 'Pine Needle Mulching',
            'tasks' => $tasks,
            'labor_hours' => $totalHours,
            'labor_cost' => $totalCost,
            'total_cost' => $totalCost,
            'crew_size' => $crewSize,
            'labor_rate' => $laborRate,
        ];
    }
}
