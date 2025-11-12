<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Models\SiteVisit;
use Illuminate\Support\Str;

class CalculationImportService
{
    public function __construct(protected EstimateItemService $items)
    {
    }

    public function importCalculation(Estimate $estimate, Calculation $calculation, bool $replace = true): void
    {
        $data = $calculation->data ?? [];
        $calcLabel = Str::headline($calculation->calculation_type);

        if ($replace) {
            $this->items->removeCalculationItems($estimate, $calculation->id);
        }

        $materialsCreated = $this->importMaterials($estimate, $data, $calcLabel, $calculation);
        $laborTotal = $this->importLabor($estimate, $data, $calcLabel, $calculation);

        $materialTotal = array_reduce($materialsCreated, fn ($carry, $item) => $carry + $item, 0);
        $this->importFeeOrMarkup($estimate, $data, $materialTotal + $laborTotal, $calcLabel, $calculation);
    }

    protected function importMaterials(Estimate $estimate, array $data, string $calcLabel, Calculation $calculation): array
    {
        $materials = $data['materials'] ?? [];
        $totals = [];

        if (is_array($materials) && !empty($materials)) {
            foreach ($materials as $name => $material) {
                if (!is_array($material)) {
                    continue;
                }

                $qty = (float) ($material['qty'] ?? $material['quantity'] ?? 1);
                $unitCost = (float) ($material['unit_cost'] ?? $material['cost'] ?? 0);
                $total = (float) ($material['total'] ?? ($qty * $unitCost));

                $this->items->createManualItem($estimate, [
                    'item_type' => 'material',
                    'name' => is_string($name) ? $name : $calcLabel . ' Material',
                    'description' => $material['description'] ?? null,
                    'unit' => $material['unit'] ?? null,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'tax_rate' => (float) ($material['tax_rate'] ?? 0),
                    'source' => 'calculator:' . $calculation->calculation_type,
                    'calculation_id' => $calculation->id,
                    'metadata' => [
                        'calculation_id' => $calculation->id,
                        'calculation_type' => $calculation->calculation_type,
                    ],
                ]);

                $totals[] = $total;
            }

            return $totals;
        }

        $materialTotal = (float) ($data['material_total'] ?? 0);
        if ($materialTotal > 0) {
            $this->items->createManualItem($estimate, [
                'item_type' => 'material',
                'name' => "{$calcLabel} Materials",
                'quantity' => 1,
                'unit' => 'lot',
                'unit_cost' => $materialTotal,
                'tax_rate' => 0,
                'source' => 'calculator:' . $calculation->calculation_type,
                'calculation_id' => $calculation->id,
                'metadata' => [
                    'calculation_id' => $calculation->id,
                    'calculation_type' => $calculation->calculation_type,
                ],
            ]);

            return [$materialTotal];
        }

        return [];
    }

    protected function importLabor(Estimate $estimate, array $data, string $calcLabel, Calculation $calculation): float
    {
        $laborCost = (float) ($data['labor_cost'] ?? 0);

        if ($laborCost <= 0) {
            return 0;
        }

        $hours = (float) ($data['total_hours'] ?? $data['base_hours'] ?? 1);
        if ($hours <= 0) {
            $hours = 1;
        }

        $unitCost = $laborCost / $hours;

        $this->items->createManualItem($estimate, [
            'item_type' => 'labor',
            'name' => "{$calcLabel} Labor",
            'unit' => 'hr',
            'quantity' => $hours,
            'unit_cost' => $unitCost,
            'tax_rate' => 0,
            'source' => 'calculator:' . $calculation->calculation_type,
            'calculation_id' => $calculation->id,
            'metadata' => [
                'calculation_id' => $calculation->id,
                'calculation_type' => $calculation->calculation_type,
            ],
        ]);

        return $laborCost;
    }

    protected function importFeeOrMarkup(Estimate $estimate, array $data, float $currentSum, string $calcLabel, Calculation $calculation): void
    {
        $finalPrice = (float) ($data['final_price'] ?? $data['total'] ?? 0);

        if ($finalPrice <= 0) {
            return;
        }

        $difference = $finalPrice - $currentSum;

        if ($difference >= 1) {
            $this->items->createManualItem($estimate, [
                'item_type' => 'fee',
                'name' => "{$calcLabel} Markup/Overhead",
                'unit' => 'lot',
                'quantity' => 1,
                'unit_cost' => $difference,
                'tax_rate' => 0,
                'source' => 'calculator:' . $calculation->calculation_type,
                'calculation_id' => $calculation->id,
                'metadata' => [
                    'calculation_id' => $calculation->id,
                    'calculation_type' => $calculation->calculation_type,
                ],
            ]);
        }
    }

    public function importSiteVisitCalculations(Estimate $estimate, SiteVisit $siteVisit, bool $replace = true): void
    {
        $siteVisit->loadMissing('calculations');

        foreach ($siteVisit->calculations as $calculation) {
            $this->importCalculation($estimate, $calculation, $replace);
        }
    }
}
