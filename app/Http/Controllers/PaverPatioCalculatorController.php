<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\SiteVisit;
use App\Models\Estimate;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductionRate;
use App\Services\LaborCostCalculatorService;


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

        return view('calculators.paver-patio.form', [
            'siteVisit' => $siteVisit,
            'siteVisitId' => $siteVisitId,
            'clientId' => $siteVisit?->client?->id,
            'editMode' => false,
            'formData' => [],
            'mode' => $mode,
            'estimateId' => $estimateId,
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->first();

        return view('calculators.paver-patio.form', [
            'siteVisit' => $siteVisit,
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'mode' => $calculation->is_template ? 'template' : null,
            'estimateId' => $calculation->estimate_id,
        ]);
    }

    public function calculate(Request $request)
{
    $mode = $request->input('mode');
    $rules = [
        'length' => 'required|numeric|min:1',
        'width' => 'required|numeric|min:1',
        'paver_type' => 'required|string|in:belgard,techo',
        'edge_restraint' => 'required|string|in:plastic,concrete',
        'crew_size' => 'required|integer|min:1',
        'drive_distance' => 'required|numeric|min:0',
        'drive_speed' => 'required|numeric|min:1',
        'labor_rate' => 'required|numeric|min:1',
        'site_conditions' => 'nullable|numeric|min:0',
        'material_pickup' => 'nullable|numeric|min:0',
        'cleanup' => 'nullable|numeric|min:0',
        'calculation_id' => 'nullable|exists:calculations,id',
        'job_notes' => 'nullable|string|max:2000',
        'materials_override_enabled' => 'nullable|boolean',
        'custom_materials' => 'nullable|array',
        'custom_materials.*.name' => 'nullable|string|max:255',
        'custom_materials.*.qty' => 'nullable|numeric|min:0',
        'custom_materials.*.unit_cost' => 'nullable|numeric|min:0',
        // Material cost overrides
        'override_paver_cost' => 'nullable|numeric|min:0',
        'override_base_cost' => 'nullable|numeric|min:0',
        'override_plastic_edge_cost' => 'nullable|numeric|min:0',
        'override_concrete_edge_cost' => 'nullable|numeric|min:0',
        'override_polymeric_sand_cost' => 'nullable|numeric|min:0',
    ];
    // site_visit_id depends on mode
    $rules['site_visit_id'] = ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id';
    $validated = $request->validate($rules);

    $length = $validated['length'];
    $width = $validated['width'];
    $area = $length * $width;

    // --------------------------------------------
    // 🔨 Material Calculations
    // --------------------------------------------
    $paverUnitCoverage = 0.94;
    $paverCount = ceil($area / $paverUnitCoverage);
    $baseDepthFeet = 2.5 / 12;
    $baseTons = ceil(($area * $baseDepthFeet) / 21.6);
    $polymericCoverageSqft = 60; // average coverage per bag for standard joint widths
    $polymericBags = $area > 0 ? (int) ceil($area / $polymericCoverageSqft) : 0;

    $paverUnitCost = $validated['override_paver_cost'] ?? 3.25;
    $baseUnitCost = $validated['override_base_cost'] ?? 45.00;
    $plasticEdgeCostPer20ft = $validated['override_plastic_edge_cost'] ?? 5.00;
    $concreteEdgeCostPer20ft = $validated['override_concrete_edge_cost'] ?? 12.00;
    $polymericSandCost = $validated['override_polymeric_sand_cost'] ?? 28.00;

    $edgeUnitCost = $validated['edge_restraint'] === 'plastic'
        ? $plasticEdgeCostPer20ft
        : $concreteEdgeCostPer20ft;

    $edgeLF = $area / 20;

    $materials = [
        'Pavers' => [
            'qty' => $paverCount,
            'unit_cost' => $paverUnitCost,
            'total' => $paverCount * $paverUnitCost
        ],
        '#78 Base Gravel' => [
            'qty' => $baseTons,
            'unit_cost' => $baseUnitCost,
            'total' => $baseTons * $baseUnitCost
        ],
        'Edge Restraints' => [
            'qty' => round($edgeLF, 2),
            'unit_cost' => $edgeUnitCost,
            'total' => $edgeLF * $edgeUnitCost
        ],
        'Polymeric Sand' => [
            'qty' => $polymericBags,
            'unit_cost' => $polymericSandCost,
            'total' => $polymericBags * $polymericSandCost
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

    $material_total = array_sum(array_column($materials, 'total'));

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
    // 💾 Prepare and Save
    // --------------------------------------------
    $data = array_merge($validated, [
        'area_sqft' => round($area, 2),
        'paver_count' => $paverCount,
        'base_tons' => $baseTons,
        'paver_unit_cost' => $paverUnitCost,
        'base_unit_cost' => $baseUnitCost,
        'edge_unit_cost' => $edgeUnitCost,
        'edge_lf' => round($edgeLF, 2),
        'polymeric_bags' => $polymericBags,
        'labor_by_task' => array_map(fn($h) => round($h, 2), $labor),
        'labor_hours' => round($baseLaborHours, 2),
        'materials' => $materials,
        'material_total' => round($material_total, 2),
        'materials_override_enabled' => !empty($validated['materials_override_enabled']),
        'custom_materials' => $customMaterials,
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
