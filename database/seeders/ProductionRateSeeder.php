<?php

namespace Database\Seeders;

use App\Models\ProductionRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductionRateSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('seeders/data/production_rates.json'));
        $parsed = json_decode($json, true);

        if (! is_array($parsed)) {
            $this->command?->error('Invalid JSON format.');
            return;
        }

        $tableData = collect($parsed)->firstWhere('type', 'table')['data'] ?? [];

        if (empty($tableData)) {
            $this->command?->error('No production rate data found.');
            return;
        }

        foreach ($tableData as $index => $item) {
            if (! isset($item['task'], $item['calculator'], $item['unit'], $item['rate'])) {
                $this->command?->warn("Skipping invalid item at index {$index}: " . json_encode($item));
                continue;
            }

            ProductionRate::updateOrCreate(
                ['task' => $item['task'], 'calculator' => $item['calculator']],
                [
                    'unit' => $item['unit'],
                    'rate' => $item['rate'],
                    'note' => $item['note'] ?? null,
                ]
            );
        }

        $plantingRates = [
            ['task' => 'annual_flats', 'unit' => 'flat', 'rate' => 0.08, 'note' => 'Install, face, and water annual flats', 'calculator' => 'planting'],
            ['task' => 'annual_pots', 'unit' => 'pot', 'rate' => 0.06, 'note' => 'Install, face, and water annual pots', 'calculator' => 'planting'],
            ['task' => 'container_1g', 'unit' => 'plant', 'rate' => 0.12, 'note' => '1-gallon container planting with facing/watering', 'calculator' => 'planting'],
            ['task' => 'container_3g', 'unit' => 'plant', 'rate' => 0.18, 'note' => '3-gallon container planting with facing/watering', 'calculator' => 'planting'],
            ['task' => 'container_5g', 'unit' => 'plant', 'rate' => 0.22, 'note' => '5-gallon container planting with facing/watering', 'calculator' => 'planting'],
            ['task' => 'container_7g', 'unit' => 'plant', 'rate' => 0.28, 'note' => '7-gallon container planting with facing/watering', 'calculator' => 'planting'],
            ['task' => 'container_10g', 'unit' => 'plant', 'rate' => 0.35, 'note' => '10-gallon container planting with facing/watering', 'calculator' => 'planting'],
            ['task' => 'container_15g', 'unit' => 'plant', 'rate' => 0.45, 'note' => '15-gallon container planting with facing/watering', 'calculator' => 'planting'],
            ['task' => 'container_25g', 'unit' => 'plant', 'rate' => 0.60, 'note' => '25-gallon container planting with facing/watering', 'calculator' => 'planting'],
            ['task' => 'ball_and_burlap', 'unit' => 'plant', 'rate' => 0.75, 'note' => 'B&B shrub/tree planting incl. staking/facing', 'calculator' => 'planting'],
            ['task' => 'palm_8_12', 'unit' => 'plant', 'rate' => 1.00, 'note' => 'Install palms 8-12 ft incl. facing/watering', 'calculator' => 'planting'],
        ];

        foreach ($plantingRates as $rate) {
            ProductionRate::updateOrCreate(
                ['task' => $rate['task'], 'calculator' => $rate['calculator']],
                [
                    'unit' => $rate['unit'],
                    'rate' => $rate['rate'],
                    'note' => $rate['note'],
                ]
            );
        }

        $this->command?->info('Production rates seeded successfully!');
    }
}
