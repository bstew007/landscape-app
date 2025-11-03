<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calculation;
use App\Services\Enhancements\PruningCalculatorService;
use App\Services\Enhancements\MulchingCalculatorService;
use App\Services\Enhancements\WeedingCalculatorService;
use App\Services\Enhancements\PineNeedleCalculatorService;
use Illuminate\Support\Facades\View;

class LandscapeEnhancementController extends Controller
{
    /**
     * Show the form for new or edit mode
     */
    public function create(Request $request)
{
    $siteVisitId = $request->input('site_visit_id');

    $siteVisit = \App\Models\SiteVisit::with('client')->findOrFail($siteVisitId);
    $calculation = \App\Models\Calculation::where('site_visit_id', $siteVisitId)
        ->where('calculation_type', 'enhancements')
        ->first();

    $formData = $calculation ? json_decode($calculation->data, true) : [];
    $editMode = $calculation !== null;

    return view('calculators.enhancements.form', compact(
        'siteVisit',
        'siteVisitId',
        'formData',
        'calculation',
       'editMode',
    ));
}

public function edit($id)
{
    $calculation = Calculation::findOrFail($id);
    $data = json_decode($calculation->data, true); // ← decode stored JSON

    // Flash scalar data for old() fallback
    if (is_array($data)) {
    foreach ($data as $key => $value) {
        if (is_scalar($value)) {
            session()->flash('_old_input.' . $key, $value);
        }
    }
}

    // Load related site visit and client
    $siteVisit = \App\Models\SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

    return view('calculators.enhancements.form', [
        'siteVisit' => $siteVisit,
        'siteVisitId' => $siteVisit->id,
        'clientId' => $siteVisit->client->id,
        'existingCalculation' => $calculation,
        'formData' => $data,
        'editMode' => true,
    ]);
}


    /**
     * Run calculations and return results
     */
   public function calculate(Request $request)
{
    $siteVisitId = $request->input('site_visit_id');
    $siteVisit = \App\Models\SiteVisit::with('client')->findOrFail($siteVisitId);

    // ✅ STEP 1: Pull inputs
    $laborRate = (float) $request->input('labor_rate', 65);
    $pruningInput = $request->input('pruning', []);
    $mulchingInput = $request->input('mulching', []);
    $weedingInput = $request->input('weeding', []);
    $pineNeedleInput = $request->input('pine_needles', []);

    // ✅ STEP 2: Run calculators with correct data
    $pruning = (new PruningCalculatorService)->calculate($pruningInput, $laborRate);
    $mulching = (new MulchingCalculatorService)->calculate($mulchingInput, $laborRate);
    $weeding = (new WeedingCalculatorService)->calculate($weedingInput, $laborRate);
    $pine_needles = (new PineNeedleCalculatorService)->calculate($pineNeedleInput, $laborRate);

    // Run calculators
   // $pruning = (new PruningCalculatorService)->calculate($pruningInput);
   // $mulching = (new MulchingCalculatorService)->calculate($mulchingInput);
  //  $weeding = (new WeedingCalculatorService)->calculate($weedingInput);
  //  $pine_needles = (new PineNeedleCalculatorService)->calculate($pineNeedleInput);

    // Total up labor and material
    $totalLabor = 
        ($pruning['labor_cost'] ?? 0) +
        ($mulching['labor_cost'] ?? 0) +
        ($weeding['labor_cost'] ?? 0) +
        ($pine_needles['labor_cost'] ?? 0);

    $totalMaterial = 
        ($mulching['material_cost'] ?? 0) +
        ($pine_needles['material_cost'] ?? 0);  

    $finalPrice = $totalLabor + $totalMaterial;

    // Build materials summary
    $materials = [];

    if (!empty($mulching['material_cost'])) {
        $materials['Mulch'] = [
            'description' => $mulching['mulch_type'] ?? 'Mulch',
            'qty' => $mulching['cubic_yards'] ?? 0,
            'unit' => 'cubic yards',
            'unit_cost' => $mulching['cost_per_cy'] ?? 0,
            'total' => $mulching['material_cost'] ?? 0,
        ];
    }

    if (!empty($pine_needles['material_cost'])) {
        $materials['Pine Needles'] = [
            'description' => 'Pine Needles',
            'qty' => $pine_needles['bales'] ?? 0,
            'unit' => 'bales',
            'unit_cost' => $pine_needles['cost_per_bale'] ?? 0,
            'total' => $pine_needles['material_cost'] ?? 0,
        ];
    }

    $material_total = array_sum(array_column($materials, 'total'));

    // Build labor task breakdown
    $laborByTask = [];
    foreach (['pruning', 'mulching', 'weeding', 'pine_needles'] as $section) {
        foreach ($$section['tasks'] as $task) {
            $label = $task['task'];
            $hours = $task['hours'] ?? 0;

            if (!isset($laborByTask[$label])) {
                $laborByTask[$label] = 0;
            }

            $laborByTask[$label] += $hours;
        }
    }

    $baseLaborHours = array_sum($laborByTask);
    $overheadPercent = $request->input('overhead_percent', 15);
    $overheadHours = round($baseLaborHours * ($overheadPercent / 100), 2);
    $totalHours = round($baseLaborHours + $overheadHours, 2);

    // Final structured output
    $data = [
        'pruning' => $pruning,
        'mulching' => $mulching,
        'weeding' => $weeding,
        'pine_needles' => $pine_needles,
        'labor_cost' => $totalLabor,
        'material_cost' => $totalMaterial,
        'final_price' => $finalPrice,
        'materials' => $materials,
        'material_total' => $material_total,
        'labor_by_task' => $laborByTask,
        'labor_hours' => $baseLaborHours,
        'overhead_hours' => $overheadHours,
        'total_hours' => $totalHours,
        'overhead_percent' => $overheadPercent,
        'job_notes' => $request->input('job_notes', null),
    ];

    // 💾 Save or update the calculation if requested
   if ($request->has('save')) {
    $calculation = $request->filled('calculation_id')
        ? Calculation::find($request->input('calculation_id'))
        : Calculation::firstOrNew([
            'site_visit_id' => $siteVisitId,
            'calculation_type' => 'enhancements',
        ]);

    $calculation->site_visit_id = $siteVisitId;
    $calculation->calculation_type = 'enhancements';
    $calculation->data = $data; // Save array directly (Eloquent will cast to JSON if needed)
    $calculation->save();
}


    // ⏎ Return result view
    return view('calculators.enhancements.result', compact('siteVisit', 'data'));
}


    /**
     * Generate PDF of enhancement results
     */
    public function downloadPdf($id)
{
    $calculation = Calculation::findOrFail($id);
    $rawData = json_decode($calculation->data, true) ?? [];

    // Recalculate each enhancement section
    $pruning = (new PruningCalculatorService)->calculate($rawData['pruning'] ?? []);
    $mulching = (new MulchingCalculatorService)->calculate($rawData['mulching'] ?? []);
    $weeding = (new WeedingCalculatorService)->calculate($rawData['weeding'] ?? []);
    $pine_needles = (new PineNeedleCalculatorService)->calculate($rawData['pine_needles'] ?? []);

    // Build materials summary
    $materials = [];

    if (!empty($mulching['material_cost'])) {
        $materials['Mulch'] = [
            'description' => $mulching['mulch_type'] ?? 'Mulch',
            'qty' => $mulching['cubic_yards'] ?? 0,
            'unit' => 'cubic yards',
            'unit_cost' => $mulching['cost_per_cy'] ?? 0,
            'total' => $mulching['material_cost'] ?? 0,
        ];
    }

    if (!empty($pine_needles['material_cost'])) {
        $materials['Pine Needles'] = [
            'description' => 'Pine Needles',
            'qty' => $pine_needles['bales'] ?? 0,
            'unit' => 'bales',
            'unit_cost' => $pine_needles['cost_per_bale'] ?? 0,
            'total' => $pine_needles['material_cost'] ?? 0,
        ];
    }

    $material_total = array_sum(array_column($materials, 'total'));

    // Labor breakdown
    $laborByTask = [];
    foreach (['pruning', 'mulching', 'weeding', 'pine_needles'] as $section) {
        foreach ($$section['tasks'] as $task) {
            $label = $task['task'];
            $hours = $task['hours'] ?? 0;

            if (!isset($laborByTask[$label])) {
                $laborByTask[$label] = 0;
            }

            $laborByTask[$label] += $hours;
        }
    }

    $baseLaborHours = array_sum($laborByTask);
    $overheadHours = round($baseLaborHours * 0.15, 2);
    $driveTime = 0;
    $totalHours = $baseLaborHours + $overheadHours + $driveTime;

    $laborCost = ($pruning['labor_cost'] ?? 0)
               + ($mulching['labor_cost'] ?? 0)
               + ($weeding['labor_cost'] ?? 0)
               + ($pine_needles['labor_cost'] ?? 0);

    $finalPrice = $laborCost + $material_total;

    // Final data structure
    $data = [
        'pruning' => $pruning,
        'mulching' => $mulching,
        'weeding' => $weeding,
        'pine_needles' => $pine_needles,
        'labor_by_task' => $laborByTask,
        'labor_hours' => $baseLaborHours,
        'overhead_hours' => $overheadHours,
        'drive_time' => $driveTime,
        'total_hours' => $totalHours,
        'labor_cost' => $laborCost,
        'material_total' => $material_total,
        'materials' => $materials,
        'final_price' => $finalPrice,
        'markup_amount' => 0,
        'job_notes' => $rawData['job_notes'] ?? '',
    ];

    $siteVisit = \App\Models\SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

    $pdf = \PDF::loadView('calculators.enhancements.pdf', compact('siteVisit', 'data'));

    return $pdf->download('enhancements-estimate.pdf');
}



}
