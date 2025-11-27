<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MergeFenceCategories extends Command
{
    protected $signature = 'materials:merge-fence-categories';

    protected $description = 'Merge all fence-related categories into Fence and update materials/pivots.';

    public function handle(): int
    {
        $targets = ['fence', 'fences'];
        $sources = MaterialCategory::where(function ($q) use ($targets) {
            foreach ($targets as $t) {
                $q->orWhere('name', 'like', "%{$t}%");
            }
        })->pluck('name');

        $targetName = 'Fence';
        $targetCat = MaterialCategory::firstOrCreate(
            ['name' => $targetName],
            ['is_active' => true, 'sort_order' => MaterialCategory::max('sort_order') + 1]
        );

        $stringUpdates = 0; $pivotLinks = 0; $removed = 0;

        foreach ($sources as $source) {
            $sourceKey = Str::lower(trim($source));
            $targetKey = Str::lower($targetName);
            if ($sourceKey === $targetKey) continue;

            // Update materials.category string (case-insensitive)
            $stringUpdates += Material::whereRaw('LOWER(category) = ?', [$sourceKey])->update(['category' => $targetName]);

            // Pivot moves
            $srcCat = MaterialCategory::whereRaw('LOWER(name) = ?', [$sourceKey])->first();
            if ($srcCat) {
                foreach ($srcCat->materials()->pluck('materials.id') as $matId) {
                    $mat = Material::find($matId);
                    if ($mat) {
                        $mat->categories()->syncWithoutDetaching([$targetCat->id]);
                        $mat->categories()->detach($srcCat->id);
                        $pivotLinks++;
                    }
                }
                $srcCat->delete();
                $removed++;
            }
        }

        $this->info("Fence merge complete. String updates: {$stringUpdates}. Pivot links moved: {$pivotLinks}. Old categories removed: {$removed}.");
        return Command::SUCCESS;
    }
}
