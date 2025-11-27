<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CategorizeShrubsByUnit extends Command
{
    protected $signature = 'materials:categorize-by-unit {--units=1g,3g,5g,7g,10g,15g : Comma-separated units to target} {--category=Plant Material - Shrubs : Category name to assign}';

    protected $description = 'Assign a category to materials whose unit matches configured values (e.g., 1g pots → shrubs).';

    public function handle(): int
    {
        $units = collect(explode(',', $this->option('units')))
            ->map(fn($u) => Str::lower(trim($u)))
            ->filter();
        if ($units->isEmpty()) {
            $this->error('No units provided.');
            return self::FAILURE;
        }

        $targetName = $this->option('category');

        // Ensure category exists
        $cat = MaterialCategory::firstOrCreate(
            ['name' => $targetName],
            ['is_active' => true, 'sort_order' => MaterialCategory::max('sort_order') + 1]
        );

        $updated = 0; $linked = 0; $total = 0;
        Material::whereIn('unit', $units)->chunkById(500, function ($chunk) use (&$updated, &$linked, &$total, $cat) {
            foreach ($chunk as $m) {
                $total++;
                $m->category = $cat->name;
                $m->save();
                $m->categories()->syncWithoutDetaching([$cat->id]);
                $updated++;
                $linked++;
            }
        });

        $this->info("Materials matched: {$total}. Updated category string: {$updated}. Pivot linked: {$linked}. Category: {$cat->name}");
        return self::SUCCESS;
    }
}
