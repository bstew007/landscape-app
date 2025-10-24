<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductionRate;


class ProductionRateSeeder extends Seeder
{
    public function run()
    {
        $file = database_path('seeders/data/production_rates.json');

        if (!file_exists($file)) {
            $this->command->error("Missing data file: $file");
            return;
        }

        $data = json_decode(file_get_contents($file), true);

        foreach ($data as $rate) {
            ProductionRate::updateOrCreate(
                ['task' => $rate['task'], 'calculator' => $rate['calculator']],
                $rate
            );
        }

        $this->command->info('Production rates seeded successfully.');
    }
}