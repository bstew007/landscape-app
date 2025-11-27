<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncMaterialCategories extends Command
{
    protected $signature = 'materials:sync-categories {--clear-string : Clear the legacy materials.category column after syncing}';

    protected $description = 'Create missing MaterialCategories from material.category strings and attach materials to them.';

    public function handle(): int
    {
        $clear = $this->option('clear-string');

        // Build or fetch categories from string values
        $strings = Material::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->pluck('category')
            ->unique()
            ->values();

        if ($strings->isEmpty()) {
            $this->info('No string categories found on materials.');
            return self::SUCCESS;
        }

        // Existing categories map (case-insensitive)
        $existing = MaterialCategory::all();
        $lookup = [];
        foreach ($existing as $cat) {
            $lookup[Str::lower(trim($cat->name))] = $cat;
        }

        $createdCats = 0;
        foreach ($strings as $name) {
            $key = Str::lower(trim($name));
            if (!isset($lookup[$key])) {
                $cat = MaterialCategory::create([
                    'name' => $name,
                    'is_active' => true,
                    'sort_order' => MaterialCategory::max('sort_order') + 1,
                ]);
                $lookup[$key] = $cat;
                $createdCats++;
            }
        }

        $this->info("Categories ensured: {$strings->count()} strings, {$createdCats} created.");

        // Attach materials to categories
        $updated = 0; $processed = 0;
        Material::chunk(500, function ($chunk) use (&$updated, &$processed, $lookup, $clear) {
            foreach ($chunk as $m) {
                $processed++;
                $catName = trim((string) $m->category);
                if ($catName === '') continue;
                $key = Str::lower($catName);
                if (!isset($lookup[$key])) continue;
                $catId = $lookup[$key]->id;
                // Attach without dropping any existing links
                $m->categories()->syncWithoutDetaching([$catId]);
                if ($clear) {
                    $m->category = null;
                    $m->save();
                }
                $updated++;
            }
        });

        $this->info("Materials processed: {$processed}. Linked: {$updated}." . ($clear ? ' Cleared legacy category column.' : ''));
        $this->info('Use --clear-string to null out materials.category after linking.');
        return self::SUCCESS;
    }
}
