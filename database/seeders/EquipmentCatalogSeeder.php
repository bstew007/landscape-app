<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\EquipmentItem;
use Illuminate\Database\Seeder;

class EquipmentCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pull all active company equipment assets
        $assets = Asset::whereIn('type', [
            'skid_steer',
            'excavator',
            'mowers',
            'dump_truck',
            'crew_truck',
            'enclosed_trailer',
            'dump_trailer',
            'equipment_trailer',
        ])
        ->where('status', 'active')
        ->get();

        foreach ($assets as $asset) {
            // Check if already exists
            $existing = EquipmentItem::where('asset_id', $asset->id)->first();
            
            if ($existing) {
                continue; // Skip if already seeded
            }

            // Create equipment catalog entry
            EquipmentItem::create([
                'name' => $asset->name,
                'sku' => $asset->identifier ?: null,
                'category' => $this->getCategoryFromType($asset->type),
                'ownership_type' => 'company',
                'unit' => 'hr', // Default to hourly
                'model' => $asset->model,
                'asset_id' => $asset->id,
                'is_active' => true,
                'notes' => 'Seeded from existing asset: ' . $asset->name,
                // Rates can be calculated/set later from budget or manually
            ]);
        }

        $this->command->info('Equipment catalog seeded with ' . $assets->count() . ' company-owned items.');
    }

    /**
     * Convert asset type to readable category name
     */
    private function getCategoryFromType(string $type): string
    {
        $mapping = [
            'skid_steer' => 'Skid Steer',
            'excavator' => 'Excavator',
            'mowers' => 'Mower',
            'dump_truck' => 'Dump Truck',
            'crew_truck' => 'Crew Truck',
            'enclosed_trailer' => 'Enclosed Trailer',
            'dump_trailer' => 'Dump Trailer',
            'equipment_trailer' => 'Equipment Trailer',
        ];

        return $mapping[$type] ?? ucwords(str_replace('_', ' ', $type));
    }
}
