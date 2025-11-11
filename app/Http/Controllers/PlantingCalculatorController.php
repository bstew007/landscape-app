<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Services\LaborCostCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlantingCalculatorController extends Controller
{
    protected array $taskLabels = [
        'annual_flats' => 'Annual Flats',
        'annual_pots' => 'Annual Pots',
        'container_1g' => '1 Gallon Containers',
        'container_3g' => '3 Gallon Containers',
        'container_5g' => '5 Gallon Containers',
        'container_7g' => '7 Gallon Containers',
        'container_10g' => '10 Gallon Containers',
        'container_15g' => '15 Gallon Containers',
        'container_25g' => '25 Gallon Containers',
        'ball_and_burlap' => 'B&B Trees / Shrubs',
        'palm_8_12' => "Palms 8'-12'",
    ];

    public function showForm(Request $request)
    {
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);

        return view('calculators.planting.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit->client->id,
            'editMode' => false,
            'formData' => [],
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.planting.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $siteVisit->id,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit->client->id,
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
            'site_visit_id' => 'required|exists:site_visits,id',
            'calculation_id' => 'nullable|exists:calculations,id',
            'job_notes' => 'nullable|string|max:2000',
            'tasks' => 'required|array',
            'tasks.*.qty' => 'nullable|numeric|min:0',
            'tasks.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        $productionRates = ProductionRate::where('calculator', 'planting')->pluck('rate', 'task');
        $taskInputs = [];
        $unitCostInputs = [];
        $inputTasks = $request->input('tasks', []);

        $results = [];
        $materials = [];
        $materialTotal = 0;
        $totalHours = 0;
        $laborRate = (float) $validated['labor_rate'];

        foreach ($productionRates as $taskKey => $ratePerUnit) {
            $qty = (float) ($inputTasks[$taskKey]['qty'] ?? 0);
            $unitCost = (float) ($inputTasks[$taskKey]['unit_cost'] ?? 0);

            $taskInputs[$taskKey] = $qty;
            $unitCostInputs[$taskKey] = $unitCost;

            if ($qty <= 0) {
                continue;
            }

            $hours = $qty * $ratePerUnit;
            $results[] = [
                'task' => $this->taskLabels[$taskKey] ?? Str::title(str_replace('_', ' ', $taskKey)),
                'qty' => $qty,
                'rate' => $ratePerUnit,
                'hours' => round($hours, 2),
                'cost' => round($hours * $laborRate, 2),
            ];

            $totalHours += $hours;

            if ($unitCost > 0) {
                $lineTotal = $qty * $unitCost;
                $materials[$this->taskLabels[$taskKey] ?? Str::title(str_replace('_', ' ', $taskKey))] = [
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'total' => round($lineTotal, 2),
                ];

                $materialTotal += $lineTotal;
            }
        }

        $calculator = new LaborCostCalculatorService();
        $totals = $calculator->calculate(
            $totalHours,
            $laborRate,
            array_merge($request->all(), ['material_total' => $materialTotal])
        );

        $data = array_merge($validated, $totals, [
            'tasks' => $results,
            'labor_by_task' => collect($results)->pluck('hours', 'task')->map(fn ($hours) => round($hours, 2))->toArray(),
            'labor_hours' => round($totalHours, 2),
            'materials' => $materials,
            'material_total' => round($materialTotal, 2),
            'task_inputs' => $taskInputs,
            'unit_costs' => $unitCostInputs,
        ]);

        $calc = ! empty($validated['calculation_id'])
            ? tap(Calculation::findOrFail($validated['calculation_id']))->update(['data' => $data])
            : Calculation::create([
                'site_visit_id' => $validated['site_visit_id'],
                'calculation_type' => 'planting',
                'data' => $data,
            ]);

        return redirect()->route('calculators.planting.showResult', $calc->id);
    }

    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.planting.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.planting.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('planting_estimate.pdf');
    }
}
