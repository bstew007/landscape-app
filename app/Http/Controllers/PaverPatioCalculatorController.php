<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\SiteVisit;
use App\Models\Estimate;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductionRate;
use App\Services\LaborCostCalculatorService;
use App\Services\BudgetService;


class PaverPatioCalculatorController extends Controller
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

        return view('calculators.paver-patio.form', [
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
        $siteVisit = $calculation->siteVisit()->with('client')->first();

        $budgetService = app(BudgetService::class);
        $defaultLaborRate = $budgetService->getLaborRateForCalculators();

        return view('calculators.paver-patio.form', [
            'siteVisit' => $siteVisit,
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'mode' => $calculation->is_template ? 'template' : null,
            'estimateId' => $calculation->estimate_id,
            'defaultLaborRate' => $defaultLaborRate,
        ]);
    }

    public function calculate(Request $request)
{
    $mode = $request->input('mode');
    $rules = [
        'length' => 'required|numeric|min:1',
        'width' => 'required|numeric|min:1',
        'edge_restraint' => 'required|string|in:plastic,concrete',
        'edging_linear_feet' => 'nullable|numeric|min:0',
        'crew_size' => 'required|integer|min:1',
        'drive_distance' => 'required|numeric|min:0',
        'drive_speed' => 'required|numeric|min:1',
        'labor_rate' => 'required|numeric|min:1',
        'site_conditions' => 'nullable|numeric|min:0',
        'material_pickup' => 'nullable|numeric|min:0',
        'cleanup' => 'nullable|numeric|min:0',
        'calculation_id' => 'nullable|exists:calculations,id',
        'job_notes' => 'nullable|string|max:2000',
        'materials' => 'nullable|array',
        'materials.*.catalog_id' => 'nullable|integer',
        'materials.*.name' => 'nullable|string|max:255',
        'materials.*.quantity' => 'nullable|numeric|min:0',
        'materials.*.unit_cost' => 'nullable|numeric|min:0',
        'materials.*.unit' => 'nullable|string|max:50',
        'custom_materials' => 'nullable|array',
        'custom_materials.*.name' => 'nullable|string|max:255',
        'custom_materials.*.qty' => 'nullable|numeric|min:0',
        'custom_materials.*.unit_cost' => 'nullable|numeric|min:0',
    ];
    // site_visit_id depends on mode
    $rules['site_visit_id'] = ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id';
    $validated = $request->validate($rules);

    $length = $validated['length'];
    $width = $validated['width'];
    $area = $length * $width;

    // --------------------------------------------
    // 🔨 Calculations - Quantities Only (No Pricing)
    // --------------------------------------------
    // Calculate quantities for reference/display only
    $paverUnitCoverage = 0.94;
    $paverCount = ceil($area / $paverUnitCoverage);
    $baseDepthFeet = 2.5 / 12;
    $baseTons = ceil(($area * $baseDepthFeet) / 21.6);
    $polymericCoverageSqft = 60;
    $polymericBags = $area > 0 ? (int) ceil($area / $polymericCoverageSqft) : 0;
    $edgeLF = $validated['edging_linear_feet'] ?? ($area / 20);

    // --------------------------------------------
    // 📦 Process Catalog Materials from Picker
    // --------------------------------------------
    $catalogMaterials = [];
    $material_total = 0;
    
    if (!empty($validated['materials'])) {
        foreach ($validated['materials'] as $mat) {
            if (!empty($mat['name']) && isset($mat['quantity'])) {
                $qty = (float) $mat['quantity'];
                $unitCost = (float) ($mat['unit_cost'] ?? 0);
                $total = $qty * $unitCost;
                
                $catalogMaterials[] = [
                    'catalog_id' => $mat['catalog_id'] ?? null,
                    'name' => $mat['name'],
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'unit' => $mat['unit'] ?? 'ea',
                ];
                
                $material_total += $total;
            }
        }
    }

    // --------------------------------------------
    // 👷 Labor Calculations (from DB)
    // --------------------------------------------
    $dbRates = ProductionRate::where('calculator', 'paver_patio')->pluck('rate', 'task');

    $labor = [
        'excavation' => $area * ($dbRates['excavation'] ?? 0.03),
        'base_compaction' => $area * ($dbRates['base_compaction'] ?? 0.04),
        'laying_pavers' => $area * ($dbRates['laying_pavers'] ?? 0.06),
        'cutting_borders' => $area * ($dbRates['cutting_borders'] ?? 0.015),
        'install_edging' => $area * ($dbRates['install_edging'] ?? 0.007),
        'cleanup' => $area * ($dbRates['cleanup'] ?? 0.005),
    ];

    $baseLaborHours = array_sum($labor);

    // --------------------------------------------
    // 🧮 Use the Shared LaborCostCalculatorService
    // --------------------------------------------
    $calculator = new LaborCostCalculatorService();
    $totals = $calculator->calculate(
        baseHours: $baseLaborHours,
        laborRate: (float) $validated['labor_rate'],
        inputs: array_merge($request->all(), ['material_total' => $material_total])
    );

    // --------------------------------------------
    // 📋 Enhanced Labor Tasks Array
    // --------------------------------------------
    $laborRate = (float) $validated['labor_rate'];
    $laborTasks = [];
    $taskRates = ProductionRate::where('calculator', 'paver_patio')->get()->keyBy('task');
    
    foreach ($labor as $taskKey => $hours) {
        if ($hours <= 0) continue;
        
        $taskName = ucwords(str_replace('_', ' ', $taskKey));
        $rate = $taskRates->get($taskKey);
        $productionRate = $rate ? $rate->rate : 0;
        $unit = $rate ? $rate->unit : 'sqft';
        
        $laborTasks[] = [
            'task_key' => $taskKey,
            'task_name' => $taskName,
            'description' => $taskName . " - " . round($area, 2) . " {$unit}",
            'quantity' => round($area, 2),
            'unit' => $unit,
            'production_rate' => $productionRate,
            'hours' => round($hours, 2),
            'hourly_rate' => $laborRate,
            'total_cost' => round($hours * $laborRate, 2),
        ];
    }

    // --------------------------------------------
    // 💾 Prepare and Save
    // --------------------------------------------
    
    $data = array_merge($validated, [
        'area_sqft' => round($area, 2),
        'paver_count' => $paverCount,
        'base_tons' => $baseTons,
        'edge_lf' => round($edgeLF, 2),
        'polymeric_bags' => $polymericBags,
        'labor_by_task' => array_map(fn($h) => round($h, 2), $labor),
        'labor_hours' => round($baseLaborHours, 2),
        'labor_tasks' => $laborTasks,
        'materials' => $catalogMaterials,
        'material_total' => round($material_total, 2),
    ], $totals);

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
                'calculation_type' => 'paver_patio',
                'data' => $data,
                'is_template' => true,
                'template_name' => $request->input('template_name') ?: null,
                'template_scope' => $request->input('template_scope') ?: 'global',
            ]);
        } else {
            $calc = Calculation::create([
                'site_visit_id' => $validated['site_visit_id'],
                'calculation_type' => 'paver_patio',
                'data' => $data,
            ]);
        }
    }

    if ($mode === 'template') {
        return redirect()->route('estimates.show', $request->input('estimate_id'))
            ->with('success', 'Paver Patio template saved.');
    }

    return redirect()->route('calculations.patio.showResult', $calc->id);
}


    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.paver-patio.results', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.paver-patio.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('paver_patio_estimate.pdf');
    }
}
