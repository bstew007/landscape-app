<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Services\LaborCostCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SynTurfCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);

        return view('calculators.syn-turf.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit->client->id,
            'editMode' => false,
            'formData' => [],
        ]);
    }

    public function edit(Calculation $calculation)
    {
        return view('calculators.syn-turf.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'siteVisit' => $calculation->siteVisit()->with('client')->first(),
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'labor_rate' => 'required|numeric|min:1',
            'crew_size' => 'required|integer|min:1',
            'drive_distance' => 'required|numeric|min:0',
            'drive_speed' => 'required|numeric|min:1',
            'site_conditions' => 'nullable|numeric|min:0',
            'material_pickup' => 'nullable|numeric|min:0',
            'cleanup' => 'nullable|numeric|min:0',
            'markup' => 'nullable|numeric|min:0',
            'site_visit_id' => 'required|exists:site_visits,id',
            'calculation_id' => 'nullable|exists:calculations,id',
            'job_notes' => 'nullable|string|max:2000',
            'tasks' => 'required|array',
            'tasks.*.qty' => 'nullable|numeric|min:0',
            'area_sqft' => 'required|numeric|min:1',
            'edging_linear_ft' => 'required|numeric|min:0',
            'turf_grade' => 'required|string|in:good,better,best',
            'turf_custom_name' => 'nullable|string|max:255',
            'override_turf_price' => 'nullable|numeric|min:0',
            'override_infill_price' => 'nullable|numeric|min:0',
            'override_edging_price' => 'nullable|numeric|min:0',
            'override_weed_barrier_price' => 'nullable|numeric|min:0',
        ]);

        $inputTasks = $request->input('tasks', []);
        $laborRate = (float) $validated['labor_rate'];

        $dbRates = ProductionRate::where('calculator', 'syn_turf')->pluck('rate', 'task');

        $results = [];
        $totalHours = 0;

        foreach ($inputTasks as $taskKey => $taskData) {
            $qty = (float) ($taskData['qty'] ?? 0);
            if ($qty <= 0 || !isset($dbRates[$taskKey])) {
                continue;
            }

            $rate = $dbRates[$taskKey];
            $hours = $qty * $rate;
            $cost = $hours * $laborRate;

            $results[] = [
                'task' => str_replace('_', ' ', $taskKey),
                'qty' => $qty,
                'rate' => $rate,
                'hours' => round($hours, 2),
                'cost' => round($cost, 2),
            ];

            $totalHours += $hours;
        }

        $calculator = new LaborCostCalculatorService();
        $totals = $calculator->calculate($totalHours, $laborRate, $request->all());

        $areaSqft = (float) $validated['area_sqft'];
        $edgingLf = (float) $validated['edging_linear_ft'];

        $turfOptions = [
            'good' => ['label' => 'Good', 'unit_cost' => 2.00],
            'better' => ['label' => 'Better', 'unit_cost' => 3.00],
            'best' => ['label' => 'Best', 'unit_cost' => 4.00],
        ];

        $selectedTurf = $turfOptions[$validated['turf_grade']];
        $turfUnitCost = $validated['override_turf_price'] ?? $selectedTurf['unit_cost'];
        $turfName = $validated['turf_custom_name'] ?: "{$selectedTurf['label']} Synthetic Turf";

        $infillCoveragePerBag = 50; // sq ft per bag
        $infillBags = $areaSqft > 0 ? (int) ceil($areaSqft / $infillCoveragePerBag) : 0;
        $infillUnitCost = $validated['override_infill_price'] ?? 25.00;

        $boardLength = 20;
        $edgingBoards = $edgingLf > 0 ? (int) ceil($edgingLf / $boardLength) : 0;
        $edgingUnitCost = $validated['override_edging_price'] ?? 45.00;

        $weedBarrierCoverage = 6 * 300; // 1 roll = 1800 sq ft
        $weedBarrierRolls = $areaSqft > 0 ? (int) ceil($areaSqft / $weedBarrierCoverage) : 0;
        $weedBarrierUnitCost = $validated['override_weed_barrier_price'] ?? 75.00;

        $materials = [
            $turfName => [
                'qty' => round($areaSqft, 2),
                'unit_cost' => $turfUnitCost,
                'total' => round($areaSqft * $turfUnitCost, 2),
            ],
            'Infill Bags' => [
                'qty' => $infillBags,
                'unit_cost' => $infillUnitCost,
                'total' => round($infillBags * $infillUnitCost, 2),
                'meta' => "Coverage {$infillCoveragePerBag} sq ft each",
            ],
            'Composite Edging Boards' => [
                'qty' => $edgingBoards,
                'unit_cost' => $edgingUnitCost,
                'total' => round($edgingBoards * $edgingUnitCost, 2),
                'meta' => "{$boardLength}' sections (input: {$edgingLf} lf)",
            ],
            'Weed Barrier Rolls' => [
                'qty' => $weedBarrierRolls,
                'unit_cost' => $weedBarrierUnitCost,
                'total' => round($weedBarrierRolls * $weedBarrierUnitCost, 2),
                'meta' => "6' x 300' (1,800 sq ft) coverage",
            ],
        ];

        $materials = array_filter($materials, fn ($item) => $item['qty'] > 0);
        $materialTotal = array_sum(array_column($materials, 'total'));
        $overridesEnabled = collect([
            $validated['override_turf_price'] ?? null,
            $validated['override_infill_price'] ?? null,
            $validated['override_edging_price'] ?? null,
            $validated['override_weed_barrier_price'] ?? null,
            $validated['turf_custom_name'] ?? null,
        ])->filter(fn ($value) => !is_null($value) && $value !== '')->isNotEmpty();

        $laborCost = $totals['labor_cost'] ?? 0;
        $markupPercent = (float) ($validated['markup'] ?? 0);
        $marginDecimal = $markupPercent / 100;
        $preMarkupTotal = $laborCost + $materialTotal;
        $finalPrice = $marginDecimal >= 1 ? $preMarkupTotal : ($marginDecimal > 0
            ? $preMarkupTotal / (1 - $marginDecimal)
            : $preMarkupTotal);
        $markupAmount = $finalPrice - $preMarkupTotal;

        $totals['labor_cost'] = round($laborCost, 2);
        $totals['final_price'] = round($finalPrice, 2);
        $totals['markup_amount'] = round($markupAmount, 2);
        $totals['markup'] = $markupPercent;

        $data = array_merge(
            $validated,
            [
                'tasks' => $results,
                'labor_by_task' => collect($results)
                    ->pluck('hours', 'task')
                    ->map(fn ($h) => round($h, 2))
                    ->toArray(),
                'labor_hours' => round($totalHours, 2),
                'materials' => $materials,
                'material_total' => round($materialTotal, 2),
                'area_sqft' => round($areaSqft, 2),
                'edging_linear_ft' => round($edgingLf, 2),
                'turf_grade' => $validated['turf_grade'],
                'turf_unit_cost' => $turfUnitCost,
                'turf_name' => $turfName,
                'override_turf_price' => $validated['override_turf_price'],
                'override_infill_price' => $validated['override_infill_price'],
                'override_edging_price' => $validated['override_edging_price'],
                'override_weed_barrier_price' => $validated['override_weed_barrier_price'],
                'turf_custom_name' => $validated['turf_custom_name'],
                'materials_override_enabled' => $overridesEnabled,
            ],
            $totals
        );

        if (!empty($validated['calculation_id'])) {
            $calc = Calculation::findOrFail($validated['calculation_id']);
            $calc->update(['data' => $data]);
        } else {
            $calc = Calculation::create([
                'site_visit_id' => $validated['site_visit_id'],
                'calculation_type' => 'syn_turf',
                'data' => $data,
            ]);
        }

        return redirect()->route('calculators.syn_turf.showResult', $calc->id);
    }

    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.syn-turf.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.syn-turf.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('synthetic_turf_estimate.pdf');
    }
}
