<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\Estimate;
use App\Models\SiteVisit;
use App\Services\LaborCostCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;

class MulchingCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $mode = $request->query('mode');
        $estimateId = $request->query('estimate_id');
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = $siteVisitId ? SiteVisit::with('client')->findOrFail($siteVisitId) : null;

        return view('calculators.mulching.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit?->client?->id,
            'editMode' => false,
            'formData' => [],
            'mode' => $mode,
            'estimateId' => $estimateId,
        ]);
    }

    public function edit(Calculation $calculation)
    {
        return view('calculators.mulching.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'siteVisit' => $calculation->siteVisit()->with('client')->first(),
            'mode' => $calculation->is_template ? 'template' : null,
            'estimateId' => $calculation->estimate_id,
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
        'depth_inches' => 'nullable|numeric|min:0',
        'mulch_type' => 'nullable|string|max:255',
        'custom_materials' => 'nullable|array',
        'custom_materials.*.name' => 'nullable|string|max:255',
        'custom_materials.*.qty' => 'nullable|numeric|min:0',
        'custom_materials.*.unit_cost' => 'nullable|numeric|min:0',
        'material_catalog_id' => 'nullable|exists:materials,id',
        'material_unit_cost' => 'nullable|numeric|min:0',
    ]);

    // ✅ Material catalog integration
    $materialCatalogId = $request->input('material_catalog_id');
    $materialUnitCost = $request->input('material_unit_cost');
    
    // Use catalog pricing if available, otherwise use default
    $unitCost = $materialUnitCost ? (float) $materialUnitCost : 35;

    // ✅ Calculate mulch volume in cubic yards
    $areaSqft = (float) $request->input('area_sqft', 0);
    $depthInches = (float) $request->input('depth_inches', 0);
    $mulchYards = 0;

    if ($areaSqft > 0 && $depthInches > 0) {
        $mulchYards = round(($areaSqft * ($depthInches / 12)) / 27, 2);
    }

    // ✅ Materials
    $materials = [];
    $materials[] = [ // NEW: Enhanced array format with catalog linkage
        'name' => $validated['mulch_type'] ?? 'Mulch',
        'description' => 'Mulch material',
        'quantity' => $mulchYards,
        'unit' => 'cy',
        'unit_cost' => $unitCost,
        'total_cost' => round($mulchYards * $unitCost, 2),
        'category' => 'Materials',
        'catalog_id' => $materialCatalogId, // Link to material catalog
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
        // Keep old format for backward compatibility
        $materials[$customMaterial['name']] = [
            'qty' => $customMaterial['qty'],
            'unit_cost' => $customMaterial['unit_cost'],
            'total' => $customMaterial['total'],
            'is_custom' => true,
        ];
        // NEW: Add enhanced format too
        $materials[] = [
            'name' => $customMaterial['name'],
            'description' => $customMaterial['name'],
            'quantity' => $customMaterial['qty'],
            'unit' => 'ea',
            'unit_cost' => $customMaterial['unit_cost'],
            'total_cost' => $customMaterial['total'],
            'category' => 'Materials',
            'is_custom' => true,
        ];
    }

    $materialTotal = collect($materials)->sum('total_cost');

    // ✅ Labor Calculations
    $inputTasks = $request->input('tasks', []);
    $laborRate = (float) $validated['labor_rate'];
    $dbRates = ProductionRate::where('calculator', 'mulching')->pluck('rate', 'task');

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
            'description' => "Mulch {$qty} cubic yards",
            'quantity' => $qty,
            'unit' => 'cubic_yard',
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

    // ✅ Build data (service totals already include labor + materials)
    $data = array_merge(
        $validated,
        $totals, // first, so your custom totals overwrite theirs
        [
            'tasks' => $results,
            'labor_tasks' => $laborTasks, // NEW: Enhanced format for import
            'labor_by_task' => collect($results)->pluck('hours', 'task')->map(fn($h) => round($h, 2))->toArray(),
            'area_sqft' => $areaSqft,
            'depth_inches' => $depthInches,
            'mulch_yards' => $mulchYards,
            'labor_hours' => round($totalHours, 2),
            'materials' => $materials,
            'material_total' => $materialTotal,
            'custom_materials' => $customMaterials,
        ]
    );

    // ✅ Save or update calculation
    if (!empty($validated['calculation_id'])) {
        $calc = Calculation::find($validated['calculation_id']);
        $calc->update(['data' => $data]);
    } else {
        $calc = Calculation::create([
            'site_visit_id' => $mode === 'template' ? null : $validated['site_visit_id'],
            'estimate_id' => $mode === 'template' ? ($request->input('estimate_id') ?: null) : null,
            'calculation_type' => 'mulching',
            'data' => $data,
            'is_template' => $mode === 'template',
            'template_name' => $mode === 'template' ? ($request->input('template_name') ?: null) : null,
            'template_scope' => $mode === 'template' ? ($request->input('template_scope') ?: 'global') : null,
            'client_id' => $mode === 'template' ? (Estimate::find($request->input('estimate_id'))?->client_id) : null,
            'property_id' => $mode === 'template' ? (Estimate::find($request->input('estimate_id'))?->property_id) : null,
        ]);
    }

    if ($mode === 'template') {
        return redirect()->route('estimates.show', $calc->estimate_id ?: request('estimate_id'))
            ->with('success', 'Mulching template saved.');
    }

    // ✅ Redirect to results in site-visit mode
    return redirect()->route('calculators.mulching.showResult', $calc->id);
}


    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.mulching.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.mulching.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('mulching_estimate.pdf');
    }
}
