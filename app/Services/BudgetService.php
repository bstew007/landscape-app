<?php

namespace App\Services;

use App\Models\CompanyBudget;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class BudgetService
{
    const CACHE_KEY = 'active_company_budget';

    public function active(bool $useCache = true): ?CompanyBudget
    {
        if ($useCache) {
            return Cache::remember(self::CACHE_KEY, 300, function () {
                return $this->resolveActiveBudget();
            });
        }

        return $this->resolveActiveBudget();
    }

    protected function resolveActiveBudget(): ?CompanyBudget
    {
        return CompanyBudget::query()
            ->where('is_active', true)
            ->orderByDesc('effective_from')
            ->orderByDesc('updated_at')
            ->first();
    }

    public function computeOutputs(array $inputs): array
    {
        // Calculate total field labor hours from detailed labor inputs
        $totalFieldHours = $this->calculateTotalFieldLaborHours($inputs);
        
        // Calculate total overhead from all overhead sources
        $totalOverhead = $this->calculateTotalOverhead($inputs);
        
        // Calculate overhead recovery rate per hour
        $ohr = $totalFieldHours > 0 ? ($totalOverhead / $totalFieldHours) : 0;
        
        // Legacy simple calculation (for backwards compatibility)
        $headcount = (float) Arr::get($inputs, 'labor.headcount', 1);
        $wage = (float) Arr::get($inputs, 'labor.wage', 25);
        $payrollTaxes = (float) Arr::get($inputs, 'labor.payroll_taxes', 0.09);
        $benefits = (float) Arr::get($inputs, 'labor.benefits', 0.12);
        $workersComp = (float) Arr::get($inputs, 'labor.workers_comp', 0.03);
        if ($payrollTaxes > 1) $payrollTaxes = $payrollTaxes / 100;
        if ($benefits > 1) $benefits = $benefits / 100;
        if ($workersComp > 1) $workersComp = $workersComp / 100;
        $ptoHours = (float) Arr::get($inputs, 'labor.pto_hours', 80);
        $hoursPerWeek = (float) Arr::get($inputs, 'labor.hours_per_week', 40);
        $weeks = (float) Arr::get($inputs, 'labor.weeks_per_year', 52);
        $utilization = (float) Arr::get($inputs, 'labor.utilization', 0.85);
        $productivity = (float) Arr::get($inputs, 'labor.productivity', 0.95);

        $annualHours = $hoursPerWeek * $weeks;
        $ptoPerHour = $annualHours > 0 ? ($ptoHours / $annualHours) : 0;
        $dlc = $wage * (1 + $payrollTaxes + $benefits + $workersComp) + ($wage * $ptoPerHour);
        $plhPerPerson = $annualHours * $utilization * $productivity;
        $plh = $plhPerPerson * $headcount;
        $blc = $dlc + $ohr;

        return [
            'labor' => [
                'dlc' => round($dlc, 2),
                'ohr' => round($ohr, 2),
                'blc' => round($blc, 2),
                'plh' => round($plh, 1),
                'plh_per_person' => round($plhPerPerson, 1),
                'total_field_hours' => round($totalFieldHours, 1),
            ],
            'overhead' => [
                'total' => round($totalOverhead, 2),
            ],
        ];
    }
    
    protected function calculateTotalFieldLaborHours(array $inputs): float
    {
        $totalHours = 0;
        
        // Sum hourly labor hours
        $hourlyRows = Arr::get($inputs, 'labor.hourly.rows', []);
        foreach ($hourlyRows as $row) {
            $staff = (float) ($row['staff'] ?? 0);
            $hrs = (float) ($row['hrs'] ?? 0);
            $otHrs = (float) ($row['ot_hrs'] ?? 0);
            $totalHours += ($staff * ($hrs + $otHrs));
        }
        
        // Sum salary labor hours (if they have annual hours)
        $salaryRows = Arr::get($inputs, 'labor.salary.rows', []);
        foreach ($salaryRows as $row) {
            $annHrs = (float) ($row['ann_hrs'] ?? 0);
            $totalHours += $annHrs;
        }
        
        return $totalHours;
    }
    
    protected function calculateTotalOverhead(array $inputs): float
    {
        $total = 0;
        
        // Overhead expenses
        $expenseRows = Arr::get($inputs, 'overhead.expenses.rows', []);
        foreach ($expenseRows as $row) {
            $total += (float) ($row['current'] ?? 0);
        }
        
        // Overhead wages
        $wageRows = Arr::get($inputs, 'overhead.wages.rows', []);
        foreach ($wageRows as $row) {
            $total += (float) ($row['forecast'] ?? 0);
        }
        
        // Overhead equipment
        $equipmentRows = Arr::get($inputs, 'overhead.equipment.rows', []);
        foreach ($equipmentRows as $row) {
            $qty = (float) ($row['qty'] ?? 0);
            $costPerYear = (float) ($row['cost_per_year'] ?? 0);
            $total += ($qty * $costPerYear);
        }
        
        // Add general equipment costs
        $general = Arr::get($inputs, 'overhead.equipment.general', []);
        $total += (float) ($general['fuel'] ?? 0);
        $total += (float) ($general['repairs'] ?? 0);
        $total += (float) ($general['insurance_misc'] ?? 0);
        
        // Add equipment rentals
        $total += (float) Arr::get($inputs, 'overhead.equipment.rentals', 0);
        
        return $total;
    }

    public function recommendedRates(CompanyBudget $budget): array
    {
        $outputs = $budget->outputs ?? [];
        $desiredMargin = (float) ($budget->desired_profit_margin ?? 0.2);
        $blc = (float) Arr::get($outputs, 'labor.blc', 0);

        $breakEven = $blc;
        $chargeRate = $desiredMargin < 0.999 ? ($blc / max(0.0001, (1 - $desiredMargin))) : $blc; // guard
        $profitPerHour = $chargeRate - $blc;
        $grossMargin = $chargeRate > 0 ? ($profitPerHour / $chargeRate) : 0; // as fraction

        return [
            'break_even_rate' => round($breakEven, 2),
            'charge_out_rate' => round($chargeRate, 2),
            'profit_per_hour' => round($profitPerHour, 2),
            'gross_margin' => round($grossMargin, 4),
        ];
    }
}
