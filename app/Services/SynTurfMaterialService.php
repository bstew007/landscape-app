<?php

namespace App\Services;

use Illuminate\Support\Arr;

class SynTurfMaterialService
{
    public function buildMaterials(float $areaSqft, float $edgingLf, string $grade, array $overrides = []): array
    {
        $config = config('syn_turf.materials', []);

        $turfTiers = $config['turf_tiers'] ?? [];
        $defaultTier = $turfTiers[$grade] ?? reset($turfTiers) ?? [
            'label' => ucfirst($grade),
            'unit_cost' => 0,
        ];

        $turfUnitCost = $overrides['turf_price'] ?? $defaultTier['unit_cost'];
        $turfName = $overrides['turf_name'] ?: "{$defaultTier['label']} Synthetic Turf";

        $infillCoverage = Arr::get($config, 'infill.coverage_sqft_per_bag', 50);
        $infillUnitCost = $overrides['infill_price'] ?? Arr::get($config, 'infill.unit_cost', 25);
        $infillBags = $areaSqft > 0 ? (int) ceil($areaSqft / max($infillCoverage, 1)) : 0;

        $boardLength = Arr::get($config, 'edging.board_length_ft', 20);
        $edgingUnitCost = $overrides['edging_price'] ?? Arr::get($config, 'edging.unit_cost', 45);
        $edgingBoards = $edgingLf > 0 ? (int) ceil($edgingLf / max($boardLength, 1)) : 0;

        $weedCoverage = Arr::get($config, 'weed_barrier.coverage_sqft_per_roll', 1800);
        $weedUnitCost = $overrides['weed_barrier_price'] ?? Arr::get($config, 'weed_barrier.unit_cost', 75);
        $weedBarrierRolls = $areaSqft > 0 ? (int) ceil($areaSqft / max($weedCoverage, 1)) : 0;

        $materials = [
            $turfName => [
                'qty' => round($areaSqft, 2),
                'unit_cost' => $turfUnitCost,
                'total' => round($areaSqft * $turfUnitCost, 2),
            ],
            'Infill Bags' => [
                'qty' => $infillBags,
                'unit_cost' => $infillUnitCost,
                'total' => round($infillBags * $infillUnitCost, 2),
                'meta' => "Coverage {$infillCoverage} sq ft each",
            ],
            'Composite Edging Boards' => [
                'qty' => $edgingBoards,
                'unit_cost' => $edgingUnitCost,
                'total' => round($edgingBoards * $edgingUnitCost, 2),
                'meta' => "{$boardLength}' sections (input: {$edgingLf} lf)",
            ],
            'Weed Barrier Rolls' => [
                'qty' => $weedBarrierRolls,
                'unit_cost' => $weedUnitCost,
                'total' => round($weedBarrierRolls * $weedUnitCost, 2),
                'meta' => "Coverage {$weedCoverage} sq ft per roll",
            ],
        ];

        $materials = array_filter($materials, fn ($item) => $item['qty'] > 0);
        $materialTotal = array_sum(array_column($materials, 'total'));

        $overridesEnabled = collect($overrides)
            ->filter(fn ($value) => !is_null($value) && $value !== '')
            ->isNotEmpty();

        return [
            'materials' => $materials,
            'material_total' => round($materialTotal, 2),
            'turf_name' => $turfName,
            'turf_unit_cost' => (float) $turfUnitCost,
            'turf_grade' => $grade,
            'infill_bags' => $infillBags,
            'edging_boards' => $edgingBoards,
            'weed_barrier_rolls' => $weedBarrierRolls,
            'overrides_enabled' => $overridesEnabled,
        ];
    }
}
