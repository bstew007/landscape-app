<?php

namespace App\Console\Commands;

use App\Models\LaborItem;
use App\Services\BudgetService;
use Illuminate\Console\Command;

class PopulateLaborBreakeven extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'labor:populate-breakeven';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate breakeven and profit_percent for existing labor items based on current budget';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get overhead rate from active budget
        $budgetService = app(BudgetService::class);
        $activeBudget = $budgetService->active(false);
        
        if (!$activeBudget) {
            $this->error('No active budget found. Cannot calculate overhead rate.');
            return 1;
        }

        $inputs = $activeBudget->inputs ?? [];
        
        // Calculate overhead rate
        $expensesRows = (array) data_get($inputs, 'overhead.expenses.rows', []);
        $wagesRows = (array) data_get($inputs, 'overhead.wages.rows', []);
        $ohEquipRows = (array) data_get($inputs, 'overhead.equipment.rows', []);

        $ohExpenses = 0.0;
        foreach ($expensesRows as $r) {
            $ohExpenses += (float) ($r['current'] ?? 0);
        }
        $ohWages = 0.0;
        foreach ($wagesRows as $r) {
            $ohWages += (float) ($r['forecast'] ?? 0);
        }
        $ohEquip = 0.0;
        foreach ($ohEquipRows as $r) {
            $qty = (float) ($r['qty'] ?? 1);
            $per = (float) ($r['cost_per_year'] ?? 0);
            $ohEquip += ($qty * $per);
        }
        $ohTotal = $ohExpenses + $ohWages + $ohEquip;

        $hourlyRows = (array) data_get($inputs, 'labor.hourly.rows', []);
        $salaryRows = (array) data_get($inputs, 'labor.salary.rows', []);
        $totalHours = 0.0;
        foreach ($hourlyRows as $r) {
            $staff = (float) ($r['staff'] ?? 0);
            $hrs = (float) ($r['hrs'] ?? 0);
            $ot = (float) ($r['ot_hrs'] ?? 0);
            $totalHours += $staff * ($hrs + $ot);
        }
        foreach ($salaryRows as $r) {
            $staff = (float) ($r['staff'] ?? 0);
            $hrs = (float) ($r['ann_hrs'] ?? 0);
            $totalHours += $staff * $hrs;
        }

        $overheadRate = $totalHours > 0 ? ($ohTotal / $totalHours) : 0.0;
        
        $this->info("Active Budget: {$activeBudget->name}");
        $this->info("Overhead Rate: \${$overheadRate}/hr");
        
        // Get all labor items
        $laborItems = LaborItem::all();
        $updated = 0;
        
        foreach ($laborItems as $labor) {
            $wage = (float) ($labor->average_wage ?? 0);
            $otFactor = (float) ($labor->overtime_factor ?? 0);
            $burdenPct = (float) ($labor->labor_burden_percentage ?? 0);
            $unbillablePct = (float) ($labor->unbillable_percentage ?? 0);
            $baseRate = (float) ($labor->base_rate ?? 0);
            
            // Calculate breakeven
            $effectiveWage = $wage * (1 + ($otFactor / 100));
            $loadedWage = $effectiveWage * (1 + ($burdenPct / 100));
            $billableFraction = 1 - ($unbillablePct / 100);
            $breakeven = $billableFraction > 0 ? ($loadedWage / $billableFraction) + $overheadRate : 0;
            
            // Calculate profit percent
            $profitPercent = $baseRate > 0 ? (($baseRate - $breakeven) / $baseRate) * 100 : 0;
            
            // Update the labor item
            $labor->breakeven = round($breakeven, 2);
            $labor->profit_percent = round($profitPercent, 2);
            $labor->save();
            
            $this->line("Updated: {$labor->name} - Breakeven: \${$labor->breakeven}, Profit: {$labor->profit_percent}%");
            $updated++;
        }
        
        $this->info("\nSuccessfully updated {$updated} labor items.");
        return 0;
    }
}
