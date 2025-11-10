<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Services\LaborCostCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TurfMowingCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);

        return view('calculators.turf-mowing.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit->client->id,
            'editMode' => false,
            'formData' => [],
        ]);
    }

    public function edit(Calculation $calculation)
    {
        return view('calculators.turf-mowing.form', [
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
        ]);

        $inputTasks = $request->input('tasks', []);
        $laborRate = (float) $validated['labor_rate'];

        $dbRates = ProductionRate::where('calculator', 'turf_mowing')->pluck('rate', 'task');

        $results = [];
        $totalHours = 0;

        foreach ($inputTasks as $taskKey => $taskData) {
            $qty = (float) ($taskData['qty'] ?? 0);
            $rate = (float) ($dbRates[$taskKey] ?? 0);

            if ($qty <= 0 || $rate <= 0) {
                continue;
            }

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

        $data = array_merge(
            $validated,
            [
                'tasks' => $results,
                'labor_by_task' => collect($results)
                    ->pluck('hours', 'task')
                    ->map(fn ($h) => round($h, 2))
                    ->toArray(),
                'labor_hours' => round($totalHours, 2),
                'materials' => [],
                'material_total' => 0,
            ],
            $totals
        );

        if (!empty($validated['calculation_id'])) {
            $calc = Calculation::findOrFail($validated['calculation_id']);
            $calc->update(['data' => $data]);
        } else {
            $calc = Calculation::create([
                'site_visit_id' => $validated['site_visit_id'],
                'calculation_type' => 'turf_mowing',
                'data' => $data,
            ]);
        }

        return redirect()->route('calculators.turf_mowing.showResult', $calc->id);
    }

    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.turf-mowing.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.turf-mowing.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('turf_mowing_estimate.pdf');
    }
}
