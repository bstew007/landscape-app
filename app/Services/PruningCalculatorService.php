<?php

namespace App\Services;

class PruningCalculatorService
{
    public function calculate(array $pruningData, float $defaultLaborRate = 45.00): array
    {
        $tasks = [
            'shearing' => ['label' => 'Shearing Shrubs', 'unit_key' => 'count'],
            'hand_pruning' => ['label' => 'Hand Pruning Shrubs', 'unit_key' => 'count'],
            'ladder_pruning' => ['label' => 'Ladder Pruning', 'unit_key' => 'count'],
            'tree_pruning' => ['label' => 'Tree Pruning', 'unit_key' => 'count'],
            'deadheading' => ['label' => 'Deadheading Perennials', 'unit_key' => 'sqft'],
            'cut_back_grasses' => ['label' => 'Cutting Back Grasses', 'unit_key' => 'count'],
            'cut_back_annuals' => ['label' => 'Cutting Back Annuals', 'unit_key' => 'plants'],
        ];

        $results = [];
        $totalHours = 0;
        $totalCost = 0;

        foreach ($tasks as $taskKey => $meta) {
            if (!isset($pruningData[$taskKey])) continue;

            $task = $pruningData[$taskKey];
            $count = $task[$meta['unit_key']] ?? 0;
            $rate = $task['rate_per_hour'] ?? 1;
            $laborRate = $task['labor_rate'] ?? $defaultLaborRate;

            $hours = $rate > 0 ? round($count / $rate, 2) : 0;
            $cost = round($hours * $laborRate, 2);

            $results[] = [
                'task' => $meta['label'],
                'units' => $count,
                'prod_rate' => $rate,
                'hours' => $hours,
                'cost' => $cost,
            ];

            $totalHours += $hours;
            $totalCost += $cost;
        }

        // Handle Cleanup
        $cleanupHours = 0;
        if (isset($pruningData['cleanup'])) {
            $cleanup = $pruningData['cleanup'];

            if (($cleanup['method'] ?? 'auto') === 'auto') {
                $percent = $cleanup['percent_additional_time'] ?? 15;
                $cleanupHours = round($totalHours * ($percent / 100), 2);
            } else {
                $cleanupHours = round($cleanup['manual_hours'] ?? 0, 2);
            }

            $cleanupCost = round($cleanupHours * $defaultLaborRate, 2);

            $results[] = [
                'task' => 'Cleanup of Pruning Debris',
                'units' => '-',
                'prod_rate' => '-',
                'hours' => $cleanupHours,
                'cost' => $cleanupCost,
            ];

            $totalHours += $cleanupHours;
            $totalCost += $cleanupCost;
        }

        return [
            'tasks' => $results,
            'total_hours' => $totalHours,
            'total_cost' => $totalCost,
        ];
    }
}
