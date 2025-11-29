<?php
/**
 * Add sample mulch materials to catalog for testing
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Material;

$mulchMaterials = [
    [
        'name' => 'Forest Brown Mulch',
        'sku' => 'MULCH-FB-01',
        'category' => 'Mulch',
        'unit' => 'cy',
        'unit_cost' => 35.00,
        'unit_price' => 45.00,
        'description' => 'Premium hardwood mulch, natural brown color',
        'is_active' => true,
        'is_taxable' => false,
    ],
    [
        'name' => 'Black Dyed Mulch',
        'sku' => 'MULCH-BK-01',
        'category' => 'Mulch',
        'unit' => 'cy',
        'unit_cost' => 38.00,
        'unit_price' => 48.00,
        'description' => 'Color-enhanced black mulch',
        'is_active' => true,
        'is_taxable' => false,
    ],
    [
        'name' => 'Red Cedar Mulch',
        'sku' => 'MULCH-RC-01',
        'category' => 'Mulch',
        'unit' => 'cy',
        'unit_cost' => 42.00,
        'unit_price' => 52.00,
        'description' => 'Natural cedar chips, insect-repellent',
        'is_active' => true,
        'is_taxable' => false,
    ],
    [
        'name' => 'Pine Straw',
        'sku' => 'MULCH-PS-01',
        'category' => 'Pine Needles',
        'unit' => 'bale',
        'unit_cost' => 6.50,
        'unit_price' => 9.00,
        'description' => 'Longleaf pine needles, per bale',
        'is_active' => true,
        'is_taxable' => false,
    ],
];

echo "Adding sample mulch materials to catalog...\n\n";

foreach ($mulchMaterials as $data) {
    $existing = Material::where('sku', $data['sku'])->first();
    
    if ($existing) {
        echo "⚠️  {$data['name']} already exists (SKU: {$data['sku']})\n";
    } else {
        $material = Material::create($data);
        echo "✅ Created: {$data['name']} - \${$data['unit_cost']}/{$data['unit']}\n";
    }
}

echo "\n✅ Done! You now have " . Material::where('is_active', true)->where('category', 'LIKE', '%mulch%')->orWhere('category', 'LIKE', '%pine%')->count() . " mulch materials in catalog.\n";
