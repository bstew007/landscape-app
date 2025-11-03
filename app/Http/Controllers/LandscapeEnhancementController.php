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
        'editMode'
    ));
}

    /**
     * Run calculations and return results
     */
    public function calculate(Request $request)
    {
        $siteVisitId = $request->input('site_visit_id');
        $siteVisit = \App\Models\SiteVisit::with('client')->findOrFail($siteVisitId);

        // Input sections
        $pruningInput = $request->input('pruning', []);
        $mulchingInput = $request->input('mulching', []);
        $weedingInput = $request->input('weeding', []);
        $pineNeedleInput = $request->input('pine_needles', []);

        // Run calculators
        $pruning = (new PruningCalculatorService)->calculate($pruningInput);
        $mulching = (new MulchingCalculatorService)->calculate($mulchingInput);
        $weeding = (new WeedingCalculatorService)->calculate($weedingInput);
        $pine_needles = (new PineNeedleCalculatorService)->calculate($pineNeedleInput);

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

        // If Save button was clicked, persist to DB
        if ($request->has('save')) {
            $payload = [
                'pruning' => $pruningInput,
                'mulching' => $mulchingInput,
                'weeding' => $weedingInput,
                'pine_needles' => $pineNeedleInput,
            ];

            $data = json_encode($payload);

            if ($request->filled('calculation_id')) {
                $calculation = Calculation::find($request->input('calculation_id'));
                if ($calculation) {
                    $calculation->data = $data;
                    $calculation->save();
                }
            } else {
                // Prevent duplicate save
                $existing = Calculation::where('site_visit_id', $siteVisitId)
                    ->where('calculation_type', 'enhancements')
                    ->first();

                if (!$existing) {
                    Calculation::create([
                        'site_visit_id' => $siteVisitId,
                        'calculation_type' => 'enhancements',
                        'data' => $data,
                    ]);
                }
            }
        }

        $materials = [];

        // Include mulching material
        if (!empty($mulching['material_cost'])) {
        $materials['Mulch'] = [
        'description' => $mulching['mulch_type'] ?? 'Mulch',
        'qty' => $mulching['cubic_yards'] ?? 0,
        'unit' => 'cubic yards',
        'unit_cost' => $mulching['cost_per_cy'] ?? 0,
        'total' => $mulching['material_cost'] ?? 0,
        ];
}

        // Include pine needles
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

                    // Get combined labor cost
                    $laborCost = 
                        ($pruning['labor_cost'] ?? 0) +
                        ($mulching['labor_cost'] ?? 0) +
                        ($weeding['labor_cost'] ?? 0) +
                        ($pine_needles['labor_cost'] ?? 0);


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
                    'job_notes' => 'nullable|string|max:2000',
             
                ];
        return view('calculators.enhancements.result', compact(
            'siteVisit',
           // 'siteVisitId',
           // 'pruning',
           // 'mulching',
           // 'weeding',
           // 'pine_needles',
            'data'
        ));
    }

    /**
     * Generate PDF of enhancement results
     */
    public function downloadPdf($id)
    {
        $calculation = Calculation::findOrFail($id);

        $data = json_decode($calculation->data, true);

        $pruning = (new PruningCalculatorService)->calculate($data['pruning'] ?? []);
        $mulching = (new MulchingCalculatorService)->calculate($data['mulching'] ?? []);
        $weeding = (new WeedingCalculatorService)->calculate($data['weeding'] ?? []);
        $pineNeedles = (new PineNeedleCalculatorService)->calculate($data['pine_needles'] ?? []);

        $pdf = \PDF::loadView('calculators.enhancements.pdf', compact(
            'pruning',
            'mulching',
            'weeding',
            'pine_needles'
        ));

        return $pdf->download('enhancements-estimate.pdf');
    }
}
