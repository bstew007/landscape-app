<?php

namespace App\Console\Commands;

use App\Models\EstimateItem;
use App\Models\LaborItem;
use App\Models\Material;
use Illuminate\Console\Command;

class ClearOrphanedCatalogLinks extends Command
{
    protected $signature = 'catalog:clear-orphaned {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Clear catalog references for items that point to deleted catalog entries';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made\n");
        }
        
        $this->info("Finding orphaned catalog links...\n");
        
        $orphaned = [];
        
        // Check labor items
        $laborItems = EstimateItem::whereNotNull('catalog_id')
            ->where('catalog_type', 'labor')
            ->get();
        
        foreach ($laborItems as $item) {
            $catalogItem = LaborItem::find($item->catalog_id);
            if (!$catalogItem) {
                $orphaned[] = $item;
                $this->line("Item #{$item->id} (Estimate #{$item->estimate_id}): {$item->name} → Labor #{$item->catalog_id} NOT FOUND");
            }
        }
        
        // Check material items
        $materialItems = EstimateItem::whereNotNull('catalog_id')
            ->where('catalog_type', 'material')
            ->get();
        
        foreach ($materialItems as $item) {
            $catalogItem = Material::find($item->catalog_id);
            if (!$catalogItem) {
                $orphaned[] = $item;
                $this->line("Item #{$item->id} (Estimate #{$item->estimate_id}): {$item->name} → Material #{$item->catalog_id} NOT FOUND");
            }
        }
        
        if (empty($orphaned)) {
            $this->info("✓ No orphaned catalog links found!");
            return 0;
        }
        
        $this->newLine();
        $this->warn("Found " . count($orphaned) . " orphaned catalog links.");
        
        if ($dryRun) {
            $this->comment("\nWould clear catalog_type and catalog_id for these items.");
            $this->comment("Run without --dry-run to apply changes.");
            $this->comment("\nNote: Items will continue to work normally, they just won't have the 'Reset' button.");
            return 0;
        }
        
        if (!$this->confirm('Clear catalog references for these items? They will still work, but the Reset button will not be available.')) {
            $this->comment('Aborted.');
            return 1;
        }
        
        $cleared = 0;
        foreach ($orphaned as $item) {
            $item->catalog_type = null;
            $item->catalog_id = null;
            $item->saveQuietly();
            $cleared++;
        }
        
        $this->newLine();
        $this->info("✓ Cleared {$cleared} orphaned catalog links!");
        $this->comment("\nThese items will continue to function normally with their current pricing.");
        $this->comment("To restore catalog linkage, delete the items and re-add them from the catalog.");
        
        return 0;
    }
}
