<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Services\LaborCostCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;

class PineNeedleCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);

        return view('calculators.pine_needles.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit->client->id,
            'editMode' => false,
            'formData' => [],
        ]);
    }

    public function edit(Calculation $calculation)
    {
        return view('calculators.pine_needles.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
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
        'area_sqft' => 'nullable|numeric|min:0',
        //'depth_inches' => 'nullable|numeric|min:0',
        'mulch_type' => 'nullable|string|max:255',
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

    $materialTotal = array_sum(array_column($materials, 'total'));

    // ✅ Labor Calculations
    $inputTasks = $request->input('tasks', []);
    $laborRate = (float) $validated['labor_rate'];
    $dbRates = ProductionRate::where('calculator', 'pine_needles')->pluck('rate', 'task');

    $results = [];
    $totalHours = 0;

    foreach ($inputTasks as $taskKey => $taskData) {
        $qty = (float) ($taskData['qty'] ?? 0);
        if ($qty <= 0 || !isset($dbRates[$taskKey])) continue;

        $rate = $dbRates[$taskKey];
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

    // ✅ Overhead and Totals (via service)
    $calculator = new LaborCostCalculatorService();
    $totals = $calculator->calculate(
        $totalHours,
        $laborRate,
        array_merge($request->all(), ['material_total' => $materialTotal])
    );

    // ✅ Combine labor + materials + markup
    $laborCost = $totals['labor_cost'] ?? 0;
    $markup = $validated['markup'] ?? 0;
    $marginDecimal = $markup / 100;

    $preMarkup = $laborCost + $materialTotal;
    $finalPrice = $marginDecimal >= 1 ? $preMarkup : $preMarkup / (1 - $marginDecimal);
    $markupAmount = $finalPrice - $preMarkup;

    // ✅ Prepare data to save
    $data = array_merge($validated, $totals,[
        'tasks' => $results,
        'labor_by_task' => collect($results)->pluck('hours', 'task')->map(fn($h) => round($h, 2))->toArray(),
        'area_sqft' => $areaSqft,
        //'depth_inches' => $depthInches,
        'mulch_yards' => $mulchYards,
        'labor_hours' => round($totalHours, 2),
        'materials' => $materials,
        'material_total' => $materialTotal,
        'labor_cost' => $laborCost,
        'markup_amount' => $markupAmount,
        'final_price' => $finalPrice,
    ]);

    // ✅ Save or update calculation
    $calc = !empty($validated['calculation_id'])
        ? tap(Calculation::find($validated['calculation_id']))->update(['data' => $data])
        : Calculation::create([
            'site_visit_id' => $validated['site_visit_id'],
            'calculation_type' => 'pine_needles',
            'data' => $data,
        ]);

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
