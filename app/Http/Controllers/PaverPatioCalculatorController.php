<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductionRate;


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
        'markup' => 'required|numeric|min:0',
        'site_conditions' => 'nullable|numeric|min:0',
        'material_pickup' => 'nullable|numeric|min:0',
        'cleanup' => 'nullable|numeric|min:0',
        'site_visit_id' => 'required|exists:site_visits,id',
        'calculation_id' => 'nullable|exists:calculations,id',
        'job_notes' => 'nullable|string|max:2000',


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
    $edgeCost = $edgeLF * $edgeUnitCost;

    $paverCost = $paverCount * $paverUnitCost;
    $baseCost = $baseTons * $baseUnitCost;

    $materials = [
    'Pavers' => [
        'qty' => $paverCount,
        'unit_cost' => $paverUnitCost,
        'total' => $paverCount * $paverUnitCost
    ],
    '#78 Base Gravel' => [
        'qty' => $baseTons . ' tons',
        'unit_cost' => $baseUnitCost,
        'total' => $baseTons * $baseUnitCost
    ],
    'Edge Restraints' => [
        'qty' => round($edgeLF, 2) . ' lf',
        'unit_cost' => $edgeUnitCost,
        'total' => $edgeLF * $edgeUnitCost
    ]
];


   $material_total = array_sum(array_column($materials, 'total'));

    // --------------------------------------------
    // 👷 Labor Calculations (DB + Override)
    // --------------------------------------------
    $dbRates = ProductionRate::where('calculator', 'paver_patio')->pluck('rate', 'task');

    $labor = [
        'excavation' => $area * ($request->input('rate_excavation') ?? $dbRates['excavation'] ?? 0.03),
        'base_compaction' => $area * ($request->input('rate_base_compaction') ?? $dbRates['base_compaction'] ?? 0.04),
        'laying_pavers' => $area * ($request->input('rate_laying_pavers') ?? $dbRates['laying_pavers'] ?? 0.06),
        'cutting_borders' => $area * ($request->input('rate_cutting_borders') ?? $dbRates['cutting_borders'] ?? 0.015),
        'install_edging' => $area * ($request->input('rate_install_edging') ?? $dbRates['install_edging'] ?? 0.007),
        'cleanup' => $area * ($request->input('rate_cleanup') ?? $dbRates['cleanup'] ?? 0.005),
    ];

    $baseLabor = array_sum($labor);

    // --------------------------------------------
    // 🧾 Overhead + Final Costs
    // --------------------------------------------
    $siteCondPct = ($validated['site_conditions'] ?? 0) / 100;
    $pickupPct = ($validated['material_pickup'] ?? 0) / 100;
    $cleanupPct = ($validated['cleanup'] ?? 0) / 100;

    $overheadHours = $baseLabor * ($siteCondPct + $pickupPct + $cleanupPct);
    $driveTime = $validated['drive_distance'] / $validated['drive_speed'];

    $totalLaborHours = $baseLabor + $overheadHours + $driveTime;
    $laborCost = $totalLaborHours * $validated['labor_rate'];

    $markupAmount = ($laborCost + $material_total) * ($validated['markup'] / 100);
    $finalPrice = $laborCost + $material_total + $markupAmount;

    // --------------------------------------------
    // 💾 Save Data
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
        'labor_hours' => round($baseLabor, 2),
        'overhead_hours' => round($overheadHours + $driveTime, 2),
        'total_hours' => round($totalLaborHours, 2),
        'labor_cost' => round($laborCost, 2),

        'material_total' => round($material_total, 2),
        'markup_amount' => round($markupAmount, 2),
        'final_price' => round($finalPrice, 2),

        'materials' => $materials,
    ]);

    // Save or update calculation
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
