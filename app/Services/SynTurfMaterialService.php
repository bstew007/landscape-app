<?php

namespace App\Services;

use Illuminate\Support\Arr;

class SynTurfMaterialService
{
    public function buildMaterials(float $areaSqft, float $edgingLf, string $grade, array $depthOptions = []): array
    {
        $config = config('syn_turf.materials', []);

        $turfTiers = $config['turf_tiers'] ?? [];
        $defaultTier = $turfTiers[$grade] ?? reset($turfTiers) ?? [
            'label' => ucfirst($grade),
            'unit_cost' => 0,
        ];

        $turfName = "{$defaultTier['label']} Synthetic Turf";
        $turfUnitCost = 0;

        $infillCoverage = Arr::get($config, 'infill.coverage_sqft_per_bag', 50);
        $infillBags = $areaSqft > 0 ? (int) ceil($areaSqft / max($infillCoverage, 1)) : 0;

        $boardLength = Arr::get($config, 'edging.board_length_ft', 20);
        $edgingBoards = $edgingLf > 0 ? (int) ceil($edgingLf / max($boardLength, 1)) : 0;

        $weedCoverage = Arr::get($config, 'weed_barrier.coverage_sqft_per_roll', 1800);
        $weedBarrierRolls = $areaSqft > 0 ? (int) ceil($areaSqft / max($weedCoverage, 1)) : 0;

        // Base materials (ABC and Rock Dust) based on per-layer depths
        $abcDepthIn = isset($depthOptions['abc_depth_in']) ? (float) $depthOptions['abc_depth_in'] : 0.0;
        $rockDepthIn = isset($depthOptions['rock_dust_depth_in']) ? (float) $depthOptions['rock_dust_depth_in'] : 0.0;
        $abcCY = 0.0; $rockDustCY = 0.0;
        if ($abcDepthIn > 0) {
            $abcCY = round(($areaSqft * ($abcDepthIn / 12)) / 27, 2);
        }
        if ($rockDepthIn > 0) {
            $rockDustCY = round(($areaSqft * ($rockDepthIn / 12)) / 27, 2);
        }

        $materials = [
            $turfName => [
                'qty' => round($areaSqft, 2),
                'unit_cost' => 0,
                'total' => 0,
            ],
            'Infill Bags' => [
                'qty' => $infillBags,
                'unit_cost' => 0,
                'total' => 0,
                'meta' => "Coverage {$infillCoverage} sq ft each",
            ],
            'Composite Edging Boards' => [
                'qty' => $edgingBoards,
                'unit_cost' => 0,
                'total' => 0,
                'meta' => "{$boardLength}' sections (input: {$edgingLf} lf)",
            ],
            'Weed Barrier Rolls' => [
                'qty' => $weedBarrierRolls,
                'unit_cost' => 0,
                'total' => 0,
                'meta' => "Coverage {$weedCoverage} sq ft per roll",
            ],
        ];

        if ($abcCY > 0) {
            $materials['ABC Base (cy)'] = [
                'qty' => $abcCY,
                'unit_cost' => 0,
                'total' => 0,
                'meta' => $abcDepthIn ? sprintf('Depth %.2f in', $abcDepthIn) : null,
            ];
        }
        if ($rockDustCY > 0) {
            $materials['Rock Dust (cy)'] = [
                'qty' => $rockDustCY,
                'unit_cost' => 0,
                'total' => 0,
                'meta' => $rockDepthIn ? sprintf('Depth %.2f in', $rockDepthIn) : null,
            ];
        }

        $materials = array_filter($materials, fn ($item) => $item['qty'] > 0);

        return [
            'materials' => $materials,
            'material_total' => 0,
            'turf_name' => $turfName,
            'turf_unit_cost' => 0,
            'turf_grade' => $grade,
            'infill_bags' => $infillBags,
            'edging_boards' => $edgingBoards,
            'weed_barrier_rolls' => $weedBarrierRolls,
            'abc_cy' => $abcCY,
            'rock_dust_cy' => $rockDustCY,
        ];
    }
}
