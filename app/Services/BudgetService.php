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
        // Inputs (sensible defaults)
        $headcount = (float) Arr::get($inputs, 'labor.headcount', 1);
        $wage = (float) Arr::get($inputs, 'labor.wage', 25); // $/hr
        $payrollTaxes = (float) Arr::get($inputs, 'labor.payroll_taxes', 0.09); // 9% of wage
        $benefits = (float) Arr::get($inputs, 'labor.benefits', 0.12); // 12% of wage
        $workersComp = (float) Arr::get($inputs, 'labor.workers_comp', 0.03); // 3% of wage
        // Accept either decimals (0.09) or percents (9)
        if ($payrollTaxes > 1) $payrollTaxes = $payrollTaxes / 100;
        if ($benefits > 1) $benefits = $benefits / 100;
        if ($workersComp > 1) $workersComp = $workersComp / 100;
        $ptoHours = (float) Arr::get($inputs, 'labor.pto_hours', 80); // annual PTO hours per person
        $hoursPerWeek = (float) Arr::get($inputs, 'labor.hours_per_week', 40);
        $weeks = (float) Arr::get($inputs, 'labor.weeks_per_year', 52);
        $utilization = (float) Arr::get($inputs, 'labor.utilization', 0.85); // billable fraction
        $productivity = (float) Arr::get($inputs, 'labor.productivity', 0.95); // efficiency of billable time

        $overheadAnnual = (float) Arr::get($inputs, 'overhead.total', 150000); // $/year

        // Derived
        $annualHours = $hoursPerWeek * $weeks; // per person
        $ptoPerHour = $annualHours > 0 ? ($ptoHours / $annualHours) : 0; // PTO hourly loading

        // Direct Labor Cost per hour (DLC) as loaded wage
        // DLC = wage * (1 + payrollTaxes + benefits + workersComp) + wage * ptoPerHour
        $dlc = $wage * (1 + $payrollTaxes + $benefits + $workersComp) + ($wage * $ptoPerHour);

        // Productive Labor Hours (PLH)
        $plhPerPerson = $annualHours * $utilization * $productivity; // per person
        $plh = $plhPerPerson * $headcount; // total company productive hours

        // Overhead Recovery per hour
        $ohr = $plh > 0 ? ($overheadAnnual / $plh) : 0;

        // Burdened Labor Cost
        $blc = $dlc + $ohr;

        return [
            'labor' => [
                'dlc' => round($dlc, 2),
                'ohr' => round($ohr, 2),
                'blc' => round($blc, 2),
                'plh' => round($plh, 1),
                'plh_per_person' => round($plhPerPerson, 1),
            ],
        ];
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
