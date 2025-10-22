<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PruningCalculatorService;
use App\Services\MulchingCalculatorService;

class LandscapeEnhancementController extends Controller
{
    /**
     * Show the Landscape Enhancements Calculator form
     */
    public function create()
    {
        return view('calculators.enhancements.form');
    }

    /**
     * Handle calculator form submission
     */
    public function calculate(Request $request)
    {
        // Pull pruning and mulching data from request
        $pruningInput = $request->input('pruning', []);
        $mulchingInput = $request->input('mulching', []);

        // Run each calculator
        $pruning = (new PruningCalculatorService)->calculate($pruningInput);
        $mulching = (new MulchingCalculatorService)->calculate($mulchingInput);

        // Pass results to result view
        return view('calculators.enhancements.result', compact('pruning', 'mulching'));
    }
}


