<?php

namespace App\Console\Commands;

use App\Models\EstimateItem;
use Illuminate\Console\Command;

class FixBrokenCatalogLinks extends Command
{
    protected $signature = 'catalog:fix-broken-links {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Fix catalog_type values that use full class names instead of simple types';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made\n");
        }
        
        // Find items with full class names as catalog_type
        $laborItems = EstimateItem::where('catalog_type', 'like', '%LaborItem%')->get();
        $materialItems = EstimateItem::where('catalog_type', 'like', '%Material%')->get();
        
        $totalFixed = 0;
        
        // Fix Labor Items
        if ($laborItems->isNotEmpty()) {
            $this->info("Found {$laborItems->count()} items with Labor class names:");
            
            foreach ($laborItems as $item) {
                $this->line("  Item #{$item->id} (Estimate #{$item->estimate_id}): '{$item->catalog_type}' → 'labor'");
                
                if (!$dryRun) {
                    $item->catalog_type = 'labor';
                    $item->saveQuietly();
                    $totalFixed++;
                }
            }
        }
        
        // Fix Material Items
        if ($materialItems->isNotEmpty()) {
            $this->info("\nFound {$materialItems->count()} items with Material class names:");
            
            foreach ($materialItems as $item) {
                $this->line("  Item #{$item->id} (Estimate #{$item->estimate_id}): '{$item->catalog_type}' → 'material'");
                
                if (!$dryRun) {
                    $item->catalog_type = 'material';
                    $item->saveQuietly();
                    $totalFixed++;
                }
            }
        }
        
        if ($laborItems->isEmpty() && $materialItems->isEmpty()) {
            $this->info("No broken links found. All catalog_type values are correct!");
            return 0;
        }
        
        $this->newLine();
        
        if ($dryRun) {
            $this->comment("Would fix " . ($laborItems->count() + $materialItems->count()) . " items.");
            $this->comment("Run without --dry-run to apply changes.");
        } else {
            $this->info("✓ Fixed {$totalFixed} items!");
            $this->comment("\nRun 'php artisan catalog:audit-links' to verify.");
        }
        
        return 0;
    }
}
