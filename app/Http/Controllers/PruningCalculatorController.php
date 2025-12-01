<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\SiteVisit;
use App\Models\Estimate;
use Illuminate\Http\Request;
use App\Models\ProductionRate;
use App\Services\LaborCostCalculatorService;
use App\Services\BudgetService;
use Barryvdh\DomPDF\Facade\Pdf;


class PruningCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $mode = $request->query('mode');
        $estimateId = $request->query('estimate_id');
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = null;

        if ($mode === 'template') {
            $siteVisitId = $siteVisitId ?: null;
        } else {
            $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);
        }

        $budgetService = app(BudgetService::class);
        $defaultLaborRate = $budgetService->getLaborRateForCalculators();

        return view('calculators.pruning.form', [
            'siteVisit' => $siteVisit,
            'siteVisitId' => $siteVisitId,
            'clientId' => $siteVisit?->client?->id,
            'editMode' => false,
            'formData' => [],
            'mode' => $mode,
            'estimateId' => $estimateId,
            'defaultLaborRate' => $defaultLaborRate,
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $budgetService = app(BudgetService::class);
        $defaultLaborRate = $budgetService->getLaborRateForCalculators();

        return view('calculators.pruning.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'siteVisit' => $calculation->siteVisit()->with('client')->first(),
            'mode' => $calculation->is_template ? 'template' : null,
            'estimateId' => $calculation->estimate_id,
            'defaultLaborRate' => $defaultLaborRate,
        ]);
    }

    public function calculate(Request $request)
    {
        $mode = $request->input('mode');
        $validated = $request->validate([
            'labor_rate' => 'required|numeric|min:1',
            'crew_size' => 'required|integer|min:1',
            'drive_distance' => 'required|numeric|min:0',
            'drive_speed' => 'required|numeric|min:1',
            'site_conditions' => 'nullable|numeric|min:0',
            'material_pickup' => 'nullable|numeric|min:0',
            'cleanup' => 'nullable|numeric|min:0',
            'site_visit_id' => ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id',
            'calculation_id' => 'nullable|exists:calculations,id',
            'job_notes' => 'nullable|string|max:2000',
            'tasks' => 'required|array',
            'tasks.*.qty' => 'nullable|numeric|min:0',
        ]);

        $inputTasks = $request->input('tasks', []);
        $laborRate = (float) $validated['labor_rate'];

        // Load production rates from DB
        $productionRates = ProductionRate::where('calculator', 'pruning')->get()->keyBy('task');

        $results = [];
        $laborTasks = []; // Enhanced format for import service
        $totalHours = 0;

        foreach ($inputTasks as $taskKey => $taskData) {
            $qty = (float) ($taskData['qty'] ?? 0);
            $rateModel = $productionRates[$taskKey] ?? null;
            if ($qty <= 0 || ! $rateModel) {
                continue;
            }

            $rate = $rateModel->rate;
            $unitLabel = $rateModel->unit ?? '';
            $hours = $qty * $rate;
            $cost = $hours * $laborRate;
            $taskName = str_replace('_', ' ', $taskKey);

            $results[] = [
                'task' => $taskName,
                'qty' => $qty,
                'unit' => $unitLabel,
                'rate' => $rate,
                'hours' => round($hours, 2),
                'cost' => round($cost, 2),
            ];

            // Enhanced labor task format for import
            $laborTasks[] = [
                'task_key' => $taskKey,
                'task_name' => ucwords($taskName),
                'description' => ucwords($taskName) . " - {$qty} {$unitLabel}",
                'quantity' => $qty,
                'unit' => $unitLabel,
                'production_rate' => $rate,
                'hours' => round($hours, 2),
                'hourly_rate' => $laborRate,
                'total_cost' => round($cost, 2),
            ];

            $totalHours += $hours;
        }

        // Calculate overhead/margin with labor cost service
        $calculator = new LaborCostCalculatorService();
        $totals = $calculator->calculate($totalHours, $laborRate, $request->all());

        // Prepare data to save
        $data = array_merge($validated, $totals, [
            'tasks' => $results,
            'labor_tasks' => $laborTasks, // Enhanced format for import
            'labor_by_task' => collect($results)->pluck('hours', 'task')->map(fn($h) => round($h, 2))->toArray(),
            'labor_hours' => round($totalHours, 2),
            'materials' => [],
            'material_total' => 0,
        ]);

        // Save or update
        if (!empty($validated['calculation_id'])) {
            $calc = Calculation::find($validated['calculation_id']);
            $calc->update(['data' => $data]);
        } else {
            if ($mode === 'template') {
                $estimateId = $request->input('estimate_id');
                $estimate = $estimateId ? Estimate::find($estimateId) : null;
                $calc = Calculation::create([
                    'site_visit_id' => null,
                    'estimate_id' => $estimateId ?: null,
                    'client_id' => $estimate?->client_id,
                    'property_id' => $estimate?->property_id,
                    'calculation_type' => 'pruning',
                    'data' => $data,
                    'is_template' => true,
                    'template_name' => $request->input('template_name') ?: null,
                    'template_scope' => $request->input('template_scope') ?: 'global',
                ]);
            } else {
                $calc = Calculation::create([
                    'site_visit_id' => $validated['site_visit_id'],
                    'calculation_type' => 'pruning',
                    'data' => $data,
                ]);
            }
        }

        if ($mode === 'template') {
            return redirect()->route('estimates.show', $request->input('estimate_id'))
                ->with('success', 'Pruning template saved.');
        }

        return redirect()->route('calculators.pruning.showResult', $calc->id);
    }


    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();
        
       // dd($calculation->data);//debugger
        return view('calculators.pruning.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.pruning.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('pruning_estimate.pdf');
    }


}
