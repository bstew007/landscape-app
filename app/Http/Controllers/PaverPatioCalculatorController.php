<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductionRate;
use App\Services\LaborCostCalculatorService;


class PaverPatioCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);

        return view('calculators.paver-patio.form', [
            'siteVisit' => $siteVisit,
             'siteVisitId' => $siteVisit->id, // add this line
            'clientId' => $siteVisit->client->id,
            'editMode' => false,
            'formData' => [],
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.paver-patio.form', [
            //'siteVisit' => $siteVisit,
            //'clientId' => $siteVisit->client->id,
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id, // ✅ Add this
        ]);
    }

    public function calculate(Request $request)
{
    $validated = $request->validate([
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
        'site_visit_id' => 'required|exists:site_visits,id',
        'calculation_id' => 'nullable|exists:calculations,id',
        'job_notes' => 'nullable|string|max:2000',
        'materials_override_enabled' => 'nullable|boolean',

        // Material cost overrides
        'override_paver_cost' => 'nullable|numeric|min:0',
        'override_base_cost' => 'nullable|numeric|min:0',
        'override_plastic_edge_cost' => 'nullable|numeric|min:0',
        'override_concrete_edge_cost' => 'nullable|numeric|min:0',
    ]);

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

    $paverUnitCost = $validated['override_paver_cost'] ?? 3.25;
    $baseUnitCost = $validated['override_base_cost'] ?? 45.00;
    $plasticEdgeCostPer20ft = $validated['override_plastic_edge_cost'] ?? 5.00;
    $concreteEdgeCostPer20ft = $validated['override_concrete_edge_cost'] ?? 12.00;

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
        ]
    ];

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
        'labor_by_task' => array_map(fn($h) => round($h, 2), $labor),
        'labor_hours' => round($baseLaborHours, 2),
        'materials' => $materials,
        'material_total' => round($material_total, 2),
        'materials_override_enabled' => !empty($validated['materials_override_enabled']),
    ], $totals);

    $calc = !empty($validated['calculation_id'])
        ? tap(Calculation::find($validated['calculation_id']))->update(['data' => $data])
        : Calculation::create([
            'site_visit_id' => $validated['site_visit_id'],
            'calculation_type' => 'paver_patio',
            'data' => $data,
        ]);

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
