<?php

namespace App\Services\Enhancements;

use App\Models\ProductionRate;

class PruningCalculatorService implements EnhancementCalculatorInterface
{
    protected array $tasks = [
        'shearing' => ['label' => 'Shearing Shrubs', 'unit_key' => 'count'],
        'hand_pruning' => ['label' => 'Hand Pruning Shrubs', 'unit_key' => 'count'],
        'ladder_pruning' => ['label' => 'Ladder Pruning', 'unit_key' => 'count'],
        'tree_pruning' => ['label' => 'Tree Pruning', 'unit_key' => 'count'],
        'deadheading' => ['label' => 'Deadheading Perennials', 'unit_key' => 'sqft'],
        'cut_back_grasses' => ['label' => 'Cutting Back Grasses', 'unit_key' => 'count'],
        'cut_back_annuals' => ['label' => 'Cutting Back Annuals', 'unit_key' => 'plants'],
    ];

    public function calculate(array $pruningData, float $defaultLaborRate = 45.00): array
{
    $tasks = [
        // Standard pruning
        'shearing' => ['label' => 'Shearing Shrubs', 'unit_key' => 'count'],
        'hand_pruning' => ['label' => 'Hand Pruning Shrubs', 'unit_key' => 'count'],
        'ladder_pruning' => ['label' => 'Ladder Pruning', 'unit_key' => 'count'],
        'tree_pruning' => ['label' => 'Tree Pruning', 'unit_key' => 'count'],
        'deadheading' => ['label' => 'Deadheading Perennials', 'unit_key' => 'sqft'],
        'cut_back_grasses' => ['label' => 'Cutting Back Grasses', 'unit_key' => 'count'],
        'cut_back_annuals' => ['label' => 'Cutting Back Annuals', 'unit_key' => 'plants'],
        'hedge_shearing' => ['label' => 'Shearing Hedges (Face Area)', 'unit_key' => 'sqft'],

        // Palm pruning
        'palm_prune_short' => ['label' => 'Palm Pruning – Short (Under 8 ft)', 'unit_key' => 'palms'],
        'palm_prune_medium' => ['label' => 'Palm Pruning – Medium (8–12 ft)', 'unit_key' => 'palms'],
        'palm_prune_tall' => ['label' => 'Palm Pruning – Tall (12–20 ft)', 'unit_key' => 'palms'],
        'palm_prune_extra_tall' => ['label' => 'Palm Pruning – Extra Tall (20+ ft)', 'unit_key' => 'palms'],
        'palm_seed_removal' => ['label' => 'Palm Seed / Inflorescence Removal', 'unit_key' => 'palms'],
        'palm_cleanup_heavy' => ['label' => 'Palm Debris Cleanup / Haul Prep', 'unit_key' => 'palms'],
    ];

    $results = [];
    $totalHours = 0;
    $totalCost = 0;

    foreach ($tasks as $taskKey => $meta) {
        if (!isset($pruningData[$taskKey])) continue;

        $task = $pruningData[$taskKey];
        $qty = $task[$meta['unit_key']] ?? 0;
        $overgrown = isset($task['overgrown']) && $task['overgrown'] == 1;

        if ($qty <= 0) continue;

        $rateKey = $overgrown ? "{$taskKey}_overgrown" : $taskKey;
        $rate = \App\Models\ProductionRate::getRate('pruning', $rateKey);

        $laborRate = $task['labor_rate'] ?? $defaultLaborRate;

        $hours = round($qty * $rate, 2);
        $cost = round($hours * $laborRate, 2);

        $results[] = [
            'task' => $meta['label'] . ($overgrown ? ' (Overgrown)' : ''),
            'units' => $qty,
            'prod_rate' => $rate,
            'hours' => $hours,
            'cost' => $cost,
        ];

        $totalHours += $hours;
        $totalCost += $cost;
    }

    // Cleanup logic remains unchanged
    // ...

    return [
        'tasks' => $results,
        'total_hours' => $totalHours,
        'total_cost' => $totalCost,
    ];
}
}