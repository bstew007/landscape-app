<?php

namespace App\Services;

class LaborCostCalculatorService
{
    public function calculate(float $baseHours, float $laborRate, array $inputs): array
{
    $crewSize = (int) ($inputs['crew_size'] ?? 1);

    $overheadPercent = (float) ($inputs['site_conditions'] ?? 0)
                     + (float) ($inputs['material_pickup'] ?? 0)
                     + (float) ($inputs['cleanup'] ?? 0);

    $driveDistance = (float) ($inputs['drive_distance'] ?? 0);
    $driveSpeed = (float) ($inputs['drive_speed'] ?? 30); // Default fallback
    $markup = (float) ($inputs['markup'] ?? 0);

    // 🕒 Drive time per trip (in hours)
    $driveTimePerPerson = $driveSpeed > 0 ? $driveDistance / $driveSpeed : 0;

    // 🔁 Round-trip for entire crew
    $driveTimeTotal = $driveTimePerPerson * 2 * $crewSize;

    // 🧮 Overhead time for entire crew
    $overheadTimeTotal = $baseHours * ($overheadPercent / 100) * $crewSize;

    // 👷 Total labor hours = base + overhead + drive
    $totalHours = $baseHours + $overheadTimeTotal + $driveTimeTotal;
    $laborCost = $totalHours * $laborRate;

    $finalPrice = $markup > 0 ? $laborCost / (1 - ($markup / 100)) : $laborCost;
    $markupAmount = $finalPrice - $laborCost;

    return [
        'drive_time_hours' => round($driveTimeTotal, 2),
        'overhead_hours' => round($overheadTimeTotal, 2),
        'total_hours' => round($totalHours, 2),
        'labor_cost' => round($laborCost, 2),
        'markup' => $markup,
        'markup_amount' => round($markupAmount, 2),
        'final_price' => round($finalPrice, 2),
    ];
}

}
