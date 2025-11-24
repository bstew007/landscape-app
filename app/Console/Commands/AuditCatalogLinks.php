<?php

namespace App\Console\Commands;

use App\Models\EstimateItem;
use App\Models\LaborItem;
use App\Models\Material;
use Illuminate\Console\Command;

class AuditCatalogLinks extends Command
{
    protected $signature = 'catalog:audit-links {--estimate= : Only check specific estimate ID}';
    protected $description = 'Audit all estimate items and verify their catalog linkages';

    public function handle()
    {
        $estimateId = $this->option('estimate');
        
        $query = EstimateItem::with('estimate')
            ->whereNotNull('catalog_id')
            ->whereNotNull('catalog_type');
        
        if ($estimateId) {
            $query->where('estimate_id', $estimateId);
            $this->info("Auditing catalog links for Estimate #{$estimateId}...");
        } else {
            $this->info("Auditing all catalog links...");
        }
        
        $items = $query->get();
        
        if ($items->isEmpty()) {
            $this->info('No catalog-linked items found.');
            return 0;
        }
        
        $this->info("Found {$items->count()} catalog-linked items to check.\n");
        
        $results = [
            'valid' => [],
            'broken' => [],
            'inactive' => [],
        ];
        
        foreach ($items as $item) {
            $status = $this->checkItem($item);
            $results[$status][] = $item;
        }
        
        // Summary
        $this->newLine();
        $this->info("=== AUDIT SUMMARY ===");
        $this->info("✓ Valid links: " . count($results['valid']));
        $this->warn("⚠ Inactive items: " . count($results['inactive']));
        $this->error("✗ Broken links: " . count($results['broken']));
        
        // Show broken links
        if (!empty($results['broken'])) {
            $this->newLine();
            $this->error("BROKEN LINKS FOUND:");
            $data = [];
            foreach ($results['broken'] as $item) {
                $data[] = [
                    $item->id,
                    "Estimate #{$item->estimate_id}",
                    $item->name,
                    $item->catalog_type,
                    $item->catalog_id,
                ];
            }
            $this->table(['Item ID', 'Estimate', 'Name', 'Type', 'Catalog ID'], $data);
            
            $this->newLine();
            $this->comment("To fix broken links, either:");
            $this->comment("1. Restore the deleted catalog items");
            $this->comment("2. Run: php artisan catalog:fix-broken-links");
        }
        
        // Show inactive links
        if (!empty($results['inactive'])) {
            $this->newLine();
            $this->warn("INACTIVE CATALOG ITEMS:");
            $data = [];
            foreach ($results['inactive'] as $item) {
                $data[] = [
                    $item->id,
                    "Estimate #{$item->estimate_id}",
                    $item->name,
                    $item->catalog_type,
                    $item->catalog_id,
                ];
            }
            $this->table(['Item ID', 'Estimate', 'Name', 'Type', 'Catalog ID'], $data);
            
            $this->newLine();
            $this->comment("Inactive items will still work but won't appear in the catalog.");
        }
        
        return empty($results['broken']) ? 0 : 1;
    }

    protected function checkItem(EstimateItem $item): string
    {
        $catalogType = $item->catalog_type;
        $catalogId = $item->catalog_id;
        
        if ($catalogType === 'labor') {
            $catalogItem = LaborItem::find($catalogId);
            
            if (!$catalogItem) {
                $this->line("✗ Item #{$item->id}: Labor #{$catalogId} NOT FOUND");
                return 'broken';
            }
            
            if (!$catalogItem->is_active) {
                $this->line("⚠ Item #{$item->id}: Labor #{$catalogId} is inactive");
                return 'inactive';
            }
            
            $this->line("✓ Item #{$item->id}: Labor #{$catalogId} ({$catalogItem->name}) - OK", 'verbose');
            return 'valid';
        }
        
        if ($catalogType === 'material') {
            $catalogItem = Material::find($catalogId);
            
            if (!$catalogItem) {
                $this->line("✗ Item #{$item->id}: Material #{$catalogId} NOT FOUND");
                return 'broken';
            }
            
            if (!$catalogItem->is_active) {
                $this->line("⚠ Item #{$item->id}: Material #{$catalogId} is inactive");
                return 'inactive';
            }
            
            $this->line("✓ Item #{$item->id}: Material #{$catalogId} ({$catalogItem->name}) - OK", 'verbose');
            return 'valid';
        }
        
        $this->line("? Item #{$item->id}: Unknown catalog type '{$catalogType}'");
        return 'broken';
    }
}
