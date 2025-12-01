<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Models\Estimate;
use App\Services\LaborCostCalculatorService;
use App\Services\BudgetService;
use Barryvdh\DomPDF\Facade\Pdf;

class PineNeedleCalculatorController extends Controller
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

        return view('calculators.pine_needles.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
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

        return view('calculators.pine_needles.form', [
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
        'area_sqft' => 'nullable|numeric|min:0',
        'mulch_type' => 'nullable|string|max:255',
        'custom_materials' => 'nullable|array',
        'custom_materials.*.name' => 'nullable|string|max:255',
        'custom_materials.*.qty' => 'nullable|numeric|min:0',
        'custom_materials.*.unit_cost' => 'nullable|numeric|min:0',
    ]);

    // ✅ Define mulch unit cost
    $unitCost = 7;

    // ✅ Calculate mulch volume in cubic yards
    $areaSqft = (float) $request->input('area_sqft', 0);
    //$depthInches = (float) $request->input('depth_inches', 0);
    $mulchYards = 0;

    if ($areaSqft > 0) {
        $mulchYards = round($areaSqft / 50, 0);
    }

    // ✅ Materials
    $materials = [
        $validated['mulch_type'] ?? 'Pine Needles' => [
            'qty' => $mulchYards,
            'unit_cost' => $unitCost,
            'total' => round($mulchYards * $unitCost, 2),
        ],
    ];

    $customMaterialsInput = $validated['custom_materials'] ?? [];
    $customMaterials = collect($customMaterialsInput)
        ->map(function ($item) {
            $name = trim($item['name'] ?? '');
            $qty = isset($item['qty']) ? (float) $item['qty'] : null;
            $unitCost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : null;

            if ($name === '' || $qty === null || $unitCost === null) {
                return null;
            }

            $total = $qty * $unitCost;

            return [
                'name' => $name,
                'qty' => round($qty, 2),
                'unit_cost' => round($unitCost, 2),
                'total' => round($total, 2),
            ];
        })
        ->filter()
        ->values()
        ->all();

    foreach ($customMaterials as $customMaterial) {
        $materials[$customMaterial['name']] = [
            'qty' => $customMaterial['qty'],
            'unit_cost' => $customMaterial['unit_cost'],
            'total' => $customMaterial['total'],
            'is_custom' => true,
        ];
    }

    $materialTotal = array_sum(array_column($materials, 'total'));

    // ✅ Labor Calculations
    $inputTasks = $request->input('tasks', []);
    $laborRate = (float) $validated['labor_rate'];
    $dbRates = ProductionRate::where('calculator', 'pine_needles')->pluck('rate', 'task');

    $results = [];
    $laborTasks = []; // NEW: Enhanced format for import service
    $totalHours = 0;

    foreach ($inputTasks as $taskKey => $taskData) {
        $qty = (float) ($taskData['qty'] ?? 0);
        if ($qty <= 0 || !isset($dbRates[$taskKey])) continue;

        $rate = $dbRates[$taskKey];
        $hours = $qty * $rate;
        $cost = $hours * $laborRate;
        $taskName = str_replace('_', ' ', $taskKey);

        $results[] = [
            'task' => $taskName,
            'qty' => $qty,
            'rate' => $rate,
            'hours' => round($hours, 2),
            'cost' => round($cost, 2),
        ];

        // NEW: Enhanced labor task format
        $laborTasks[] = [
            'task_key' => $taskKey,
            'task_name' => ucwords($taskName),
            'description' => "Spread pine needles on {$qty} sq ft",
            'quantity' => $qty,
            'unit' => 'sqft',
            'production_rate' => $rate,
            'hours' => round($hours, 2),
            'hourly_rate' => $laborRate,
            'total_cost' => round($cost, 2),
        ];

        $totalHours += $hours;
    }

    // ✅ Overhead and Totals (via service)
    $calculator = new LaborCostCalculatorService();
    $totals = $calculator->calculate(
        $totalHours,
        $laborRate,
        array_merge($request->all(), ['material_total' => $materialTotal])
    );

    // ✅ Prepare data to save
    $data = array_merge($validated, $totals,[
        'tasks' => $results,
        'labor_tasks' => $laborTasks, // NEW: Enhanced format for import
        'labor_by_task' => collect($results)->pluck('hours', 'task')->map(fn($h) => round($h, 2))->toArray(),
        'area_sqft' => $areaSqft,
        //'depth_inches' => $depthInches,
        'mulch_yards' => $mulchYards,
        'labor_hours' => round($totalHours, 2),
        'materials' => $materials,
        'material_total' => $materialTotal,
        'custom_materials' => $customMaterials,
    ]);

    // ✅ Save or update calculation
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
                'calculation_type' => 'pine_needles',
                'data' => $data,
                'is_template' => true,
                'template_name' => $request->input('template_name') ?: null,
                'template_scope' => $request->input('template_scope') ?: 'global',
            ]);
        } else {
            $calc = Calculation::create([
                'site_visit_id' => $validated['site_visit_id'],
                'calculation_type' => 'pine_needles',
                'data' => $data,
            ]);
        }
    }

    if ($mode === 'template') {
        return redirect()->route('estimates.show', $request->input('estimate_id'))
            ->with('success', 'Pine Needles template saved.');
    }

    // ✅ Redirect to results
    return redirect()->route('calculators.pine_needles.showResult', $calc->id);
}


    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.pine_needles.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.pine_needles.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('pine_needles_estimate.pdf');
    }
}
