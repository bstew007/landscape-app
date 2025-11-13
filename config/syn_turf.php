<?php

return [
    'materials' => [
        'turf_tiers' => [
            'good' => [
                'label' => 'Good',
                'unit_cost' => 2.00,
            ],
            'better' => [
                'label' => 'Better',
                'unit_cost' => 3.00,
            ],
            'best' => [
                'label' => 'Best',
                'unit_cost' => 4.00,
            ],
        ],
        'infill' => [
            'coverage_sqft_per_bag' => 50,
            'unit_cost' => 25.00,
        ],
        'edging' => [
            'board_length_ft' => 20,
            'unit_cost' => 45.00,
        ],
        'weed_barrier' => [
            'coverage_sqft_per_roll' => 6 * 300, // 1,800 sq ft roll
            'unit_cost' => 75.00,
        ],
        'base' => [
            'abc' => [ 'unit_cost' => 38.00 ],
            'rock_dust' => [ 'unit_cost' => 42.00 ],
        ],
        'rentals' => [
            'tamper_daily_cost' => 125.00,
        ],
    ],
];
