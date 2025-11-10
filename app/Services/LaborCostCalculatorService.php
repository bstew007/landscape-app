<?php

namespace App\Services;

class LaborCostCalculatorService
{
    public function calculate(float $baseHours, float $laborRate, array $inputs): array
    {
        $crewSize = max(1, (int) ($inputs['crew_size'] ?? 1));
        $overheadPercent = (float) ($inputs['site_conditions'] ?? 0)
                         + (float) ($inputs['material_pickup'] ?? 0)
                         + (float) ($inputs['cleanup'] ?? 0);
        $driveDistance = (float) ($inputs['drive_distance'] ?? 0);
        $driveSpeed = (float) ($inputs['drive_speed'] ?? 30); // default 30mph
        $markup = (float) ($inputs['markup'] ?? 0);
        $materialTotal = (float) ($inputs['material_total'] ?? 0);
        $hoursPerPersonPerDay = 10; // 10-hour day

        // 🕒 Drive time per person (one-way)
        $driveTimePerPerson = $driveSpeed > 0 ? $driveDistance / $driveSpeed : 0;

        // 🔁 Round-trip drive time per person
        $roundTripDriveTimePerPerson = $driveTimePerPerson * 2;

        // 📦 Crew output per day (man-hours)
        $crewDailyOutput = $hoursPerPersonPerDay * $crewSize;

        // 📅 Number of site visits
        $visits = (int) ceil($baseHours / $crewDailyOutput);

        // 🚗 Adjusted drive time (crew * drive time * visits)
        $adjustedDriveTime = $roundTripDriveTimePerPerson * $crewSize * $visits;

        // 🧮 Overhead time (on baseHours)
        $overheadTime = $baseHours * ($overheadPercent / 100);

        // ⏱️ Total labor hours
        $totalHours = $baseHours + $overheadTime + $adjustedDriveTime;

        // 💵 Costs
        $laborCost = $totalHours * $laborRate;
        $preMarkup = $laborCost + $materialTotal;
        $margin = $markup / 100;

        if ($margin <= 0) {
            $finalPrice = $preMarkup;
        } elseif ($margin >= 1) {
            $finalPrice = $preMarkup;
        } else {
            $finalPrice = $preMarkup / (1 - $margin);
        }

        $markupAmount = $finalPrice - $preMarkup;

        return [
            'visits' => $visits,
            'drive_time_hours' => round($adjustedDriveTime, 2),
            'overhead_hours' => round($overheadTime, 2),
            'total_hours' => round($totalHours, 2),
            'labor_cost' => round($laborCost, 2),
            'markup' => $markup,
            'markup_amount' => round($markupAmount, 2),
            'final_price' => round($finalPrice, 2),
        ];
    }
}
