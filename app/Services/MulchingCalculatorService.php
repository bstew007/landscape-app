<?php
namespace App\Services;

class MulchingCalculatorService
{
    public function calculate(array $data): array
    {
        $sqft = $data['sqft'] ?? 0;
        $depth = $data['depth_in_inches'] ?? 0;
        $mulchType = $data['mulch_type'] ?? 'Triple Shredded Hardwood';
        $delivery = $data['delivery_method'] ?? 'wheelbarrow_hand';
        $laborRate = $data['labor_rate'] ?? 45;
        $crewSize = $data['crew_size'] ?? 1;

        // Mulch Type Cost (per CY)
        $costPerCYMap = [
            'Triple Shredded Hardwood' => 35,
            'Forest Brown' => 40,
            'Red' => 42,
            'Pine Fines' => 38,
            'Big Nuggets' => 50,
            'Mini Nuggets' => 48,
        ];
        $costPerCY = $costPerCYMap[$mulchType] ?? 40;

        // Production Rate (CY/hr/person)
        $rateMap = [
            'wheelbarrow_dump' => 1.75,
            'wheelbarrow_hand' => 1.25,
            'tractor_rake' => 3.5,
        ];
        $rateCYPerHour = $rateMap[$delivery] ?? 1.25;

        // Calculate CY from area and depth
        $cubicYards = $sqft * $depth / 324;
        $materialCost = round($cubicYards * $costPerCY, 2);

        // Labor
        $totalHours = $rateCYPerHour > 0 && $crewSize > 0
            ? round($cubicYards / ($rateCYPerHour * $crewSize), 2)
            : 0;

        $laborCost = round($totalHours * $laborRate * $crewSize, 2);
        $totalCost = round($materialCost + $laborCost, 2);

        return [
            'sqft' => $sqft,
            'depth' => $depth,
            'cubic_yards' => round($cubicYards, 2),
            'material_cost' => $materialCost,
            'labor_hours' => $totalHours,
            'labor_cost' => $laborCost,
            'total_cost' => $totalCost,
            'mulch_type' => $mulchType,
            'delivery_method' => $delivery,
            'rate_per_hour_per_person' => $rateCYPerHour,
            'cost_per_cy' => $costPerCY,
        ];
    }
}
