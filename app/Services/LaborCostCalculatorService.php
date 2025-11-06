<?php

namespace App\Services;

class LaborCostCalculatorService
{
    public function calculate(float $baseHours, float $laborRate, array $inputs): array
    {
        $overheadPercent = (float) ($inputs['overhead_percent'] ?? 0);
        $materialPickupPercent = (float) ($inputs['material_pickup'] ?? 0);
        $cleanupPercent = (float) ($inputs['cleanup'] ?? 0);
        $crewSize = (int) ($inputs['crew_size'] ?? 1);

        $driveDistance = (float) ($inputs['drive_distance'] ?? 0);
        $driveSpeed = (float) ($inputs['drive_speed'] ?? 30); // Default safe fallback
        $markup = (float) ($inputs['markup'] ?? 0);

        $driveTime = (($driveDistance * 2) / $driveSpeed) * $crewSize;
        $overheadHours = $baseHours * ($overheadPercent + $materialPickupPercent + $cleanupPercent) / 100;
        $totalHours = $baseHours + $overheadHours + $driveTime;

        $laborCost = $totalHours * $laborRate;

        $finalPrice = $markup > 0 ? $laborCost / (1 - ($markup / 100)) : $laborCost;
        $markupAmount = $finalPrice - $laborCost;

        return [
            'overhead_hours' => round($overheadHours, 2),
            'drive_time_hours' => round($driveTime, 2),
            'total_hours' => round($totalHours, 2),
            'labor_cost' => round($laborCost, 2),
            'markup' => $markup,
            'markup_amount' => round($markupAmount, 2),
            'final_price' => round($finalPrice, 2),
        ];
    }
}
