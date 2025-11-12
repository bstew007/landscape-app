<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/materials.json');
        if (!file_exists($path)) {
            $this->seedDefaults();
            return;
        }

        $json = file_get_contents($path);
        $rows = json_decode($json, true);
        if (!is_array($rows)) {
            $this->seedDefaults();
            return;
        }

        foreach ($rows as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') continue;

            Material::updateOrCreate(
                ['sku' => $row['sku'] ?? null, 'name' => $name],
                [
                    'category' => $row['category'] ?? null,
                    'unit' => $row['unit'] ?? 'ea',
                    'unit_cost' => (float) ($row['unit_cost'] ?? 0),
                    'tax_rate' => (float) ($row['tax_rate'] ?? 0),
                    'vendor_name' => $row['vendor_name'] ?? null,
                    'vendor_sku' => $row['vendor_sku'] ?? null,
                    'description' => $row['description'] ?? null,
                    'is_taxable' => array_key_exists('is_taxable', $row) ? (bool)$row['is_taxable'] : true,
                    'is_active' => array_key_exists('is_active', $row) ? (bool)$row['is_active'] : true,
                ]
            );
        }
    }

    protected function seedDefaults(): void
    {
        $defaults = [
            [
                'name' => 'Concrete Mix (Bag)',
                'sku' => 'CONC-BAG',
                'category' => 'Concrete',
                'unit' => 'bag',
                'unit_cost' => 8.50,
                'tax_rate' => 0.07,
                'description' => '60–80 lb bags, general purpose',
            ],
            [
                'name' => '57 Gravel (Ton)',
                'sku' => 'GRAV57-TON',
                'category' => 'Aggregates',
                'unit' => 'ton',
                'unit_cost' => 85.00,
                'tax_rate' => 0.07,
            ],
            [
                'name' => 'Underlayment Fabric (Sq Ft)',
                'sku' => 'FABRIC-SQFT',
                'category' => 'Geotextile',
                'unit' => 'sqft',
                'unit_cost' => 0.30,
                'tax_rate' => 0.07,
            ],
        ];

        foreach ($defaults as $row) {
            Material::updateOrCreate(
                ['sku' => $row['sku'], 'name' => $row['name']],
                $row
            );
        }
    }
}
