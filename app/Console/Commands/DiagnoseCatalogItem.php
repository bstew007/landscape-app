<?php

namespace App\Console\Commands;

use App\Models\LaborItem;
use App\Models\Material;
use App\Models\EstimateItem;
use Illuminate\Console\Command;

class DiagnoseCatalogItem extends Command
{
    protected $signature = 'catalog:diagnose {type} {id}';
    protected $description = 'Diagnose a catalog item lookup issue';

    public function handle()
    {
        $type = strtolower($this->argument('type'));
        $id = $this->argument('id');

        $this->info("Diagnosing catalog {$type} item with ID: {$id}");
        $this->newLine();

        if ($type === 'labor') {
            $this->diagnoseLaborItem($id);
        } elseif ($type === 'material') {
            $this->diagnoseMaterialItem($id);
        } else {
            $this->error("Invalid type: {$type}. Use 'labor' or 'material'.");
            return 1;
        }

        return 0;
    }

    protected function diagnoseLaborItem($id)
    {
        $this->info('Looking up Labor Item...');
        
        $item = LaborItem::find($id);
        
        if (!$item) {
            $this->error("✗ Labor item with ID {$id} NOT FOUND in labor_catalog table");
            $this->newLine();
            
            // Check if any items reference this
            $estimateItems = EstimateItem::where('catalog_type', 'labor')
                ->where('catalog_id', $id)
                ->get();
            
            if ($estimateItems->isNotEmpty()) {
                $this->warn("⚠ Found {$estimateItems->count()} estimate items that reference this deleted labor catalog item:");
                foreach ($estimateItems as $ei) {
                    $this->line("  - Estimate #{$ei->estimate_id}, Item: {$ei->name}");
                }
                $this->newLine();
                $this->comment('These items should be updated to remove the catalog reference or re-linked to a valid item.');
            }
            
            return;
        }

        $this->info("✓ Labor item FOUND");
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $item->id],
                ['Name', $item->name],
                ['Type', $item->type],
                ['Unit', $item->unit],
                ['Average Wage', '$' . number_format($item->average_wage, 2)],
                ['Base Rate', '$' . number_format($item->base_rate ?? 0, 2)],
                ['Labor Burden %', $item->labor_burden_percentage . '%'],
                ['Unbillable %', $item->unbillable_percentage . '%'],
                ['Overtime Factor', $item->overtime_factor],
                ['Is Active', $item->is_active ? 'Yes' : 'No'],
                ['Is Billable', $item->is_billable ? 'Yes' : 'No'],
            ]
        );

        if (!$item->is_active) {
            $this->warn('⚠ This item is INACTIVE. It will still work but won\'t appear in catalog lists.');
        }

        $this->newLine();
        $this->info('Checking estimate items that reference this catalog item...');
        
        $estimateItems = EstimateItem::where('catalog_type', 'labor')
            ->where('catalog_id', $id)
            ->get();
        
        if ($estimateItems->isEmpty()) {
            $this->comment('No estimate items are currently linked to this catalog item.');
        } else {
            $this->info("Found {$estimateItems->count()} estimate items:");
            foreach ($estimateItems as $ei) {
                $this->line("  • Estimate #{$ei->estimate_id}: {$ei->name} (Item ID: {$ei->id})");
            }
        }
    }

    protected function diagnoseMaterialItem($id)
    {
        $this->info('Looking up Material Item...');
        
        $item = Material::find($id);
        
        if (!$item) {
            $this->error("✗ Material with ID {$id} NOT FOUND in materials table");
            $this->newLine();
            
            // Check if any items reference this
            $estimateItems = EstimateItem::where('catalog_type', 'material')
                ->where('catalog_id', $id)
                ->get();
            
            if ($estimateItems->isNotEmpty()) {
                $this->warn("⚠ Found {$estimateItems->count()} estimate items that reference this deleted material:");
                foreach ($estimateItems as $ei) {
                    $this->line("  - Estimate #{$ei->estimate_id}, Item: {$ei->name}");
                }
                $this->newLine();
                $this->comment('These items should be updated to remove the catalog reference or re-linked to a valid item.');
            }
            
            return;
        }

        $this->info("✓ Material FOUND");
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $item->id],
                ['Name', $item->name],
                ['Category', $item->category],
                ['Unit', $item->unit],
                ['Unit Cost', '$' . number_format($item->unit_cost, 2)],
                ['Is Taxable', $item->is_taxable ? 'Yes' : 'No'],
                ['Tax Rate', ($item->tax_rate * 100) . '%'],
                ['Is Active', $item->is_active ? 'Yes' : 'No'],
            ]
        );

        if (!$item->is_active) {
            $this->warn('⚠ This item is INACTIVE. It will still work but won\'t appear in catalog lists.');
        }

        $this->newLine();
        $this->info('Checking estimate items that reference this catalog item...');
        
        $estimateItems = EstimateItem::where('catalog_type', 'material')
            ->where('catalog_id', $id)
            ->get();
        
        if ($estimateItems->isEmpty()) {
            $this->comment('No estimate items are currently linked to this catalog item.');
        } else {
            $this->info("Found {$estimateItems->count()} estimate items:");
            foreach ($estimateItems as $ei) {
                $this->line("  • Estimate #{$ei->estimate_id}: {$ei->name} (Item ID: {$ei->id})");
            }
        }
    }
}
