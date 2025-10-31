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

    $calculation = \App\Models\Calculation::where('site_visit_id', $siteVisitId)
        ->where('calculation_type', 'enhancements')
        ->first();

    $formData = $calculation ? json_decode($calculation->data, true) : [];
    $editMode = $calculation !== null;

    return view('calculators.enhancements.form', compact(
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

        // Input sections
        $pruningInput = $request->input('pruning', []);
        $mulchingInput = $request->input('mulching', []);
        $weedingInput = $request->input('weeding', []);
        $pineNeedleInput = $request->input('pine_needles', []);

        // Run calculators
        $pruning = (new PruningCalculatorService)->calculate($pruningInput);
        $mulching = (new MulchingCalculatorService)->calculate($mulchingInput);
        $weeding = (new WeedingCalculatorService)->calculate($weedingInput);
        $pineNeedles = (new PineNeedleCalculatorService)->calculate($pineNeedleInput);

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

        return view('calculators.enhancements.result', compact(
            'siteVisitId',
            'pruning',
            'mulching',
            'weeding',
            'pineNeedles'
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
            'pineNeedles'
        ));

        return $pdf->download('enhancements-estimate.pdf');
    }
}
