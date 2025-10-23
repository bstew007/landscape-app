<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductionRate;


class ProductionRateSeeder extends Seeder
{
    public function run()
    {
        $rates = [
            ['task' => 'excavation', 'unit' => 'lf', 'rate' => 0.1],
            ['task' => 'base_install', 'unit' => 'sqft', 'rate' => 0.125],
            ['task' => 'block_laying_excavator', 'unit' => 'sqft', 'rate' => 0.05],
            ['task' => 'block_laying_manual', 'unit' => 'sqft', 'rate' => 0.09],
            ['task' => 'pipe_install', 'unit' => 'lf', 'rate' => 0.02],
            ['task' => 'gravel_backfill', 'unit' => 'sqft', 'rate' => 0.075],
            ['task' => 'topsoil_backfill', 'unit' => 'sqft', 'rate' => 0.06],
            ['task' => 'underlayment', 'unit' => 'sqft', 'rate' => 0.025],
            ['task' => 'geogrid', 'unit' => 'sqft', 'rate' => 0.045],
            ['task' => 'capstone', 'unit' => 'unit', 'rate' => 0.03],
        ];

        foreach ($rates as $rate) {
            ProductionRate::updateOrCreate(
                ['task' => $rate['task'], 'calculator' => 'retaining_wall'],
                [
                    'unit' => $rate['unit'],
                    'rate' => $rate['rate'],
                ]
            );
        }
    }
}