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

    $formData = $calculation->data ?? []; // ✅ Laravel handles JSON decoding
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
    $data = $calculation->data ?? [];

    // ✅ Recursively flash all form data to old() session
    $flatten = function (array $array, string $prefix = '') use (&$flatten) {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : "{$prefix}.{$key}";
            if (is_array($value)) {
                $result += $flatten($value, $newKey);
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    };

    session()->flash('_old_input', $flatten($data));

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
    // --------------------------------------------
    // ✅ Step 1: Validate Basic Inputs
    // --------------------------------------------
    $validated = $request->validate([
        'site_visit_id' => 'required|exists:site_visits,id',
        'calculation_id' => 'nullable|exists:calculations,id',
        'labor_rate' => 'required|numeric|min:1',
        'overhead_percent' => 'nullable|numeric|min:0',
        'job_notes' => 'nullable|string|max:2000',
    ]);

    $siteVisitId = $validated['site_visit_id'];
    $laborRate = (float) $validated['labor_rate'];
    $overheadPercent = (float) ($validated['overhead_percent'] ?? 15);

    $siteVisit = \App\Models\SiteVisit::with('client')->findOrFail($siteVisitId);

    // --------------------------------------------
    // ✅ Step 2: Input Sections
    // --------------------------------------------
    $pruningInput = $request->input('pruning', []);
    $mulchingInput = $request->input('mulching', []);
    $weedingInput = $request->input('weeding', []);
    $pineNeedleInput = $request->input('pine_needles', []);

    // --------------------------------------------
    // ✅ Step 3: Run Calculators
    // --------------------------------------------
    $pruning = (new PruningCalculatorService)->calculate($pruningInput, $laborRate);
    $mulching = (new MulchingCalculatorService)->calculate($mulchingInput, $laborRate);
    $weeding = (new WeedingCalculatorService)->calculate($weedingInput, $laborRate);
    $pine_needles = (new PineNeedleCalculatorService)->calculate($pineNeedleInput, $laborRate);

    // --------------------------------------------
    // ✅ Step 4: Totals
    // --------------------------------------------
    $totalLabor = 
        ($pruning['labor_cost'] ?? 0) +
        ($mulching['labor_cost'] ?? 0) +
        ($weeding['labor_cost'] ?? 0) +
        ($pine_needles['labor_cost'] ?? 0);

    $totalMaterial = 
        ($mulching['material_cost'] ?? 0) +
        ($pine_needles['material_cost'] ?? 0);

    $finalPrice = $totalLabor + $totalMaterial;

    // --------------------------------------------
    // ✅ Step 5: Materials Summary
    // --------------------------------------------
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

    // --------------------------------------------
    // ✅ Step 6: Labor by Task (flattened)
    // --------------------------------------------
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
    $overheadHours = round($baseLaborHours * ($overheadPercent / 100), 2);
    $totalHours = round($baseLaborHours + $overheadHours, 2);

    // --------------------------------------------
    // ✅ Step 7: Final Structured Data (for saving)
    // --------------------------------------------
    $data = array_merge($validated, [
        'pruning' => $pruning,
        'mulching' => $mulching,
        'weeding' => $weeding,
        'pine_needles' => $pine_needles,

        'labor_by_task' => array_map(fn($h) => round($h, 2), $laborByTask),
        'labor_hours' => round($baseLaborHours, 2),
        'overhead_hours' => $overheadHours,
        'total_hours' => $totalHours,
        'labor_cost' => round($totalLabor, 2),

        'material_total' => $material_total,
        'materials' => $materials,
        'final_price' => round($finalPrice, 2),
    ]);

    // --------------------------------------------
    // ✅ Step 8: Save to DB
    // --------------------------------------------
    $calc = !empty($validated['calculation_id'])
        ? tap(Calculation::find($validated['calculation_id']))->update(['data' => $data])
        : Calculation::create([
            'site_visit_id' => $validated['site_visit_id'],
            'calculation_type' => 'enhancements',
            'data' => $data,
        ]);

    // --------------------------------------------
    // ✅ Step 9: Show result view
    // --------------------------------------------
  return redirect()->route('calculators.enhancements.result', $calc->id);
}

public function showResult($id)
{
    $calculation = Calculation::findOrFail($id);
    $data = $calculation->data; // JSON column automatically casts to array if in $casts
    $siteVisit = \App\Models\SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

    return view('calculators.enhancements.result', compact('siteVisit', 'data', 'calculation'));
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
