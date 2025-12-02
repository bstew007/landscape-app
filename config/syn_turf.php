<?php

return [
    'materials' => [
        'turf_tiers' => [
            'good' => [
                'label' => 'Good',
                'unit_cost' => 0,
            ],
            'better' => [
                'label' => 'Better',
                'unit_cost' => 0,
            ],
            'best' => [
                'label' => 'Best',
                'unit_cost' => 0,
            ],
        ],
        'infill' => [
            'coverage_sqft_per_bag' => 50,
            'unit_cost' => 0,
        ],
        'edging' => [
            'board_length_ft' => 20,
            'unit_cost' => 0,
        ],
        'weed_barrier' => [
            'coverage_sqft_per_roll' => 6 * 300, // 1,800 sq ft roll
            'unit_cost' => 0,
        ],
        'base' => [
            'abc' => [ 'unit_cost' => 0 ],
            'rock_dust' => [ 'unit_cost' => 0 ],
        ],
        'rentals' => [
            'tamper_daily_cost' => 0,
        ],
    ],
];
