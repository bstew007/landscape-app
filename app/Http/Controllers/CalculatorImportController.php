<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Services\CalculationImportService;
use Illuminate\Http\Request;

class CalculatorImportController extends Controller
{
    public function __construct(
        private CalculationImportService $importService
    ) {}
    
    /**
     * Import a calculation to an estimate with granular or collapsed format
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'calculation_id' => 'required|exists:calculations,id',
            'estimate_id' => 'required|exists:estimates,id',
            'area_name' => 'nullable|string|max:255',
            'import_type' => 'required|in:granular,collapsed',
            'action' => 'required|in:import,save_only',
        ]);
        
        $calculation = Calculation::findOrFail($validated['calculation_id']);
        $estimate = Estimate::findOrFail($validated['estimate_id']);
        
        // Just save, don't import
        if ($validated['action'] === 'save_only') {
            // Calculation is already saved when it was created
            return redirect()
                ->route('site-visits.show', $calculation->site_visit_id)
                ->with('success', '✅ Calculation saved successfully.');
        }
        
        // Import to estimate
        if ($validated['import_type'] === 'granular') {
            // Use new enhanced import with work areas
            $area = $this->importService->importCalculationToArea(
                $estimate, 
                $calculation,
                null, // Let the service find/create the work area
                ['area_name' => $validated['area_name']]
            );
            
            return redirect()
                ->route('estimates.show', $estimate->id)
                ->with('success', "✅ Successfully imported to Work Area: {$area->name}");
        } else {
            // Use legacy collapsed import
            $this->importService->importCalculation($estimate, $calculation, true);
            
            return redirect()
                ->route('estimates.show', $estimate->id)
                ->with('success', '✅ Calculation imported using collapsed format.');
        }
    }
}
