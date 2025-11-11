<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $rates = [
            ['task' => 'annual_flats', 'unit' => 'flat', 'rate' => 0.08, 'note' => 'Install, face, and water annual flats'],
            ['task' => 'annual_pots', 'unit' => 'pot', 'rate' => 0.06, 'note' => 'Install, face, and water annual pots'],
            ['task' => 'container_1g', 'unit' => 'plant', 'rate' => 0.12, 'note' => '1-gallon container planting with facing/watering'],
            ['task' => 'container_3g', 'unit' => 'plant', 'rate' => 0.18, 'note' => '3-gallon container planting with facing/watering'],
            ['task' => 'container_5g', 'unit' => 'plant', 'rate' => 0.22, 'note' => '5-gallon container planting with facing/watering'],
            ['task' => 'container_7g', 'unit' => 'plant', 'rate' => 0.28, 'note' => '7-gallon container planting with facing/watering'],
            ['task' => 'container_10g', 'unit' => 'plant', 'rate' => 0.35, 'note' => '10-gallon container planting with facing/watering'],
            ['task' => 'container_15g', 'unit' => 'plant', 'rate' => 0.45, 'note' => '15-gallon container planting with facing/watering'],
            ['task' => 'container_25g', 'unit' => 'plant', 'rate' => 0.60, 'note' => '25-gallon container planting with facing/watering'],
            ['task' => 'ball_and_burlap', 'unit' => 'plant', 'rate' => 0.75, 'note' => 'B&B shrub/tree planting incl. staking/facing'],
            ['task' => 'palm_8_12', 'unit' => 'plant', 'rate' => 1.00, 'note' => 'Install palms 8-12 ft incl. facing/watering'],
        ];

        foreach ($rates as $rate) {
            DB::table('production_rates')->updateOrInsert(
                ['task' => $rate['task'], 'calculator' => 'planting'],
                [
                    'unit' => $rate['unit'],
                    'rate' => $rate['rate'],
                    'note' => $rate['note'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tasks = [
            'annual_flats',
            'annual_pots',
            'container_1g',
            'container_3g',
            'container_5g',
            'container_7g',
            'container_10g',
            'container_15g',
            'container_25g',
            'ball_and_burlap',
            'palm_8_12',
        ];

        DB::table('production_rates')
            ->where('calculator', 'planting')
            ->whereIn('task', $tasks)
            ->delete();
    }
};
