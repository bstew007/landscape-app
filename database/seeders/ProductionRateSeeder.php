<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\ProductionRate;

class ProductionRateSeeder extends Seeder
{
    public function run()
    {
        $json = File::get(database_path('seeders/data/production_rates.json'));
        $data = json_decode($json, true);

        if (!is_array($data)) {
            $this->command->error('❌ Invalid JSON format.');
            return;
        }

        foreach ($data as $index => $item) {
            // Skip entries missing required keys
            if (!isset($item['task'], $item['calculator'], $item['unit'], $item['rate'])) {
                $this->command->warn("⚠️ Skipping invalid item at index $index: " . json_encode($item));
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

        $this->command->info('✅ Production rates seeded successfully!');
    }
}
