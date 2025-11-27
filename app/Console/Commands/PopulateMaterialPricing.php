<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Services\BudgetService;
use Illuminate\Console\Command;

class PopulateMaterialPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'materials:populate-pricing {--profit-margin= : Profit margin percentage to use (defaults to active budget margin)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate unit_price, breakeven, and profit_percent for existing materials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get profit margin from budget if not specified
        $profitMarginPct = $this->option('profit-margin');
        
        if ($profitMarginPct === null) {
            // Get from active budget
            $budgetService = app(BudgetService::class);
            $activeBudget = $budgetService->active(false);
            
            if ($activeBudget) {
                $profitMarginPct = (float) ($activeBudget->desired_profit_margin ?? 0) * 100;
                $this->info("Using profit margin from active budget '{$activeBudget->name}': {$profitMarginPct}%");
            } else {
                $profitMarginPct = 20;
                $this->warn("No active budget found. Using default: {$profitMarginPct}%");
            }
        } else {
            $profitMarginPct = (float) $profitMarginPct;
            $this->info("Using specified profit margin: {$profitMarginPct}%");
        }
        
        $profitMarginRate = $profitMarginPct / 100;
        
        // Get all materials
        $materials = Material::all();
        $updated = 0;
        
        foreach ($materials as $material) {
            $unitCost = (float) ($material->unit_cost ?? 0);
            $taxRate = $material->is_taxable ? (float) ($material->tax_rate ?? 0) : 0.0;
            
            // Calculate breakeven (cost + purchase tax)
            $breakeven = $taxRate > 0 ? $unitCost * (1 + $taxRate) : $unitCost;
            
            // Calculate unit price with profit margin
            // Price = Breakeven / (1 - Profit Margin)
            $unitPrice = $profitMarginRate < 1 && $profitMarginRate >= 0 
                ? $breakeven / (1 - $profitMarginRate) 
                : $breakeven;
            
            // Calculate profit percent: (Price - Breakeven) / Price * 100
            $profitPercent = $unitPrice > 0 ? (($unitPrice - $breakeven) / $unitPrice) * 100 : 0;
            
            // Update the material
            $material->breakeven = round($breakeven, 2);
            $material->unit_price = round($unitPrice, 2);
            $material->profit_percent = round($profitPercent, 2);
            $material->save();
            
            $taxInfo = $taxRate > 0 ? " (Tax: " . ($taxRate * 100) . "%)" : "";
            $this->line("Updated: {$material->name} - Cost: \${$unitCost}{$taxInfo}, Breakeven: \${$material->breakeven}, Price: \${$material->unit_price}, Profit: {$material->profit_percent}%");
            $updated++;
        }
        
        $this->info("\nSuccessfully updated {$updated} materials.");
        return 0;
    }
}
