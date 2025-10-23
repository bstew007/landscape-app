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
            'clientId' => $siteVisit->client->id,
            'editMode' => false,
            'formData' => [],
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.paver-patio.form', [
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit->client->id,
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
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

            // Optional override inputs
            'paver_cost' => 'nullable|numeric|min:0',
            'base_cost' => 'nullable|numeric|min:0',
            'plastic_edge_cost' => 'nullable|numeric|min:0',
            'concrete_edge_cost' => 'nullable|numeric|min:0',

            // Labor rates override
            'rate_excavation' => 'nullable|numeric|min:0',
            'rate_base_compaction' => 'nullable|numeric|min:0',
            'rate_laying_pavers' => 'nullable|numeric|min:0',
            'rate_cutting_borders' => 'nullable|numeric|min:0',
            'rate_install_edging' => 'nullable|numeric|min:0',
            'rate_cleanup' => 'nullable|numeric|min:0',
        ]);

        $area = $validated['length'] * $validated['width'];

        // ✅ Material costs with fallback values
        $paverUnitCoverage = 0.94;
        $paverCount = ceil($area / $paverUnitCoverage);
        $paverCost = $paverCount * ($validated['paver_cost'] ?? 3.25);

        $baseTons = ceil(($area * (2.5 / 12)) / 21.6);
        $baseCost = $baseTons * ($validated['base_cost'] ?? 45.00);

        $plasticEdgeCost = ($area / 20) * ($validated['plastic_edge_cost'] ?? 5.00);
        $concreteEdgeCost = ($area / 20) * ($validated['concrete_edge_cost'] ?? 12.00);
        $edgeCost = $validated['edge_restraint'] === 'plastic' ? $plasticEdgeCost : $concreteEdgeCost;

        $materials = [
            'Pavers' => round($paverCost, 2),
            '#78 Base Gravel' => round($baseCost, 2),
            'Edge Restraints' => round($edgeCost, 2),
        ];
        $material_total = array_sum($materials);

        // ✅ Labor per sqft with override support
       $rates = ProductionRate::where('calculator', 'paver_patio')
    ->pluck('rate', 'task');

$labor = [];
$labor['excavation']         = $area * ($rates['excavation'] ?? 0.03);
$labor['base_compaction']    = $area * ($rates['base_compaction'] ?? 0.04);
$labor['laying_pavers']      = $area * ($rates['laying_pavers'] ?? 0.06);
$labor['cutting_borders']    = $area * ($rates['cutting_borders'] ?? 0.015);
$labor['install_edging']     = $area * ($rates['install_edging'] ?? 0.007);
$labor['cleanup']            = $area * ($rates['cleanup'] ?? 0.005);


        $baseLabor = array_sum($labor);

        $siteCondPct = ($validated['site_conditions'] ?? 0) / 100;
        $pickupPct = ($validated['material_pickup'] ?? 0) / 100;
        $cleanupPct = ($validated['cleanup'] ?? 0) / 100;
        $overheadHours = $baseLabor * ($siteCondPct + $pickupPct + $cleanupPct);
        $driveTime = $validated['drive_distance'] / $validated['drive_speed'];

        $totalLaborHours = $baseLabor + $overheadHours + $driveTime;
        $laborCost = $totalLaborHours * $validated['labor_rate'];

        $markupAmount = ($laborCost + $material_total) * ($validated['markup'] / 100);
        $finalPrice = $laborCost + $material_total + $markupAmount;

        $data = array_merge($validated, [
            'area_sqft' => round($area, 2),
            'paver_count' => $paverCount,
            'base_tons' => $baseTons,
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

        if (!empty($validated['calculation_id'])) {
            $calc = Calculation::findOrFail($validated['calculation_id']);
            $calc->update(['data' => $data]);

            return view('calculators.paver-patio.results', [
                'data' => $data,
                'siteVisit' => SiteVisit::with('client')->findOrFail($validated['site_visit_id']),
                'calculation' => $calc,
            ]);
        }

        $calc = Calculation::create([
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
