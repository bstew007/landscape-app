<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Material;

class MaterialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder imports materials from the local database.
     * Materials are inserted with ON DUPLICATE KEY UPDATE to avoid conflicts.
     */
    public function run(): void
    {
        // Get all materials from current database
        $materials = Material::all();
        
        $this->command->info("Preparing to seed {$materials->count()} materials...");
        
        // Chunk materials for efficient insertion
        $materials->chunk(100)->each(function ($chunk) {
            foreach ($chunk as $material) {
                DB::table('materials')->updateOrInsert(
                    ['sku' => $material->sku], // Match on SKU
                    [
                        'name' => $material->name,
                        'category' => $material->category,
                        'unit' => $material->unit,
                        'unit_cost' => $material->unit_cost,
                        'unit_price' => $material->unit_price,
                        'breakeven' => $material->breakeven,
                        'profit_percent' => $material->profit_percent,
                        'tax_rate' => $material->tax_rate,
                        'vendor_name' => $material->vendor_name,
                        'vendor_sku' => $material->vendor_sku,
                        'description' => $material->description,
                        'is_taxable' => $material->is_taxable,
                        'is_active' => $material->is_active,
                        'category_id' => $material->category_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
        
        $this->command->info("Successfully seeded {$materials->count()} materials!");
    }
}
