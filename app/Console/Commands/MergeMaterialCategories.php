<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MergeMaterialCategories extends Command
{
    protected $signature = 'materials:merge-categories';

    protected $description = 'Merge near-duplicate material categories into canonical names and update materials/pivots.';

    public function handle(): int
    {
        $map = [
            'aggregate' => 'Aggregates',
            'aggregates & sand' => 'Aggregates',
            'hardscapes' => 'Hardscape',
            'concrete' => 'Concrete & Mortar',
            'hardscape' => 'Pavers',
            'natural stone paving' => 'Pavers',
            'plants - perennials' => 'Plant Material - Perennials',
            'plants - trees & shrubs' => 'Plant Material - Trees',
            'plants - grassses' => 'Turf & Seed',
        ];

        // Lookup existing categories case-insensitively
        $categories = MaterialCategory::all();
        $lookup = [];
        foreach ($categories as $cat) {
            $lookup[Str::lower(trim($cat->name))] = $cat;
        }

        $createdTargets = 0;
        $stringUpdates = 0;
        $pivotLinks = 0;
        $removed = 0;

        foreach ($map as $source => $targetName) {
            $sourceKey = Str::lower(trim($source));
            $targetKey = Str::lower(trim($targetName));

            // Ensure target category exists
            if (!isset($lookup[$targetKey])) {
                $lookup[$targetKey] = MaterialCategory::create([
                    'name' => $targetName,
                    'is_active' => true,
                    'sort_order' => MaterialCategory::max('sort_order') + 1,
                ]);
                $createdTargets++;
            }
            $targetCat = $lookup[$targetKey];

            // Update string column (case-insensitive match)
            $stringMatched = Material::whereRaw('LOWER(category) = ?', [$sourceKey])->update(['category' => $targetName]);
            $stringUpdates += $stringMatched;

            // Attach pivot for materials that had the source string (even if no source category record)
            Material::whereRaw('LOWER(category) = ?', [$sourceKey])->chunkById(500, function ($chunk) use ($targetCat, &$pivotLinks) {
                foreach ($chunk as $m) {
                    $m->categories()->syncWithoutDetaching([$targetCat->id]);
                    $pivotLinks++;
                }
            });

            // Move pivot links from source category (if it exists)
            if (isset($lookup[$sourceKey]) && $lookup[$sourceKey]->id !== $targetCat->id) {
                $srcCat = $lookup[$sourceKey];
                foreach ($srcCat->materials()->pluck('materials.id') as $matId) {
                    $material = Material::find($matId);
                    if ($material) {
                        $material->categories()->syncWithoutDetaching([$targetCat->id]);
                        $material->categories()->detach($srcCat->id);
                        $pivotLinks++;
                    }
                }
                // Remove the old category record
                $srcCat->delete();
                $removed++;
            }
        }

        $this->info("Targets created: {$createdTargets}");
        $this->info("Materials string categories updated: {$stringUpdates}");
        $this->info("Pivot links moved: {$pivotLinks}");
        $this->info("Old categories removed: {$removed}");
        return Command::SUCCESS;
    }
}
