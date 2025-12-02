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
            'estimate_id' => 'nullable|string', // Can be 'new' or existing ID
            'new_estimate_title' => 'required_if:estimate_id,new|string|max:255',
            'area_name' => 'nullable|string|max:255',
            'import_type' => 'required|in:granular,collapsed',
            'action' => 'required|in:import,save_only',
        ]);
        
        $calculation = Calculation::findOrFail($validated['calculation_id']);
        
        // Create new estimate if needed
        if ($validated['estimate_id'] === 'new') {
            // Get first active cost code with QBO mapping (required for estimates)
            $costCodeId = \App\Models\CostCode::where('is_active', true)
                ->whereNotNull('qbo_item_id')
                ->first()?->id;
            
            if (!$costCodeId) {
                return back()->withErrors([
                    'cost_code_id' => 'No active cost code with QuickBooks mapping found. Please create one in Settings → Cost Codes before creating estimates.'
                ])->withInput();
            }
            
            $estimate = Estimate::create([
                'title' => $validated['new_estimate_title'],
                'client_id' => $calculation->client_id ?? $calculation->siteVisit->client_id,
                'property_id' => $calculation->property_id ?? $calculation->siteVisit->property_id,
                'site_visit_id' => $calculation->site_visit_id,
                'status' => 'draft',
                'estimate_type' => 'design_build',
                'cost_code_id' => $costCodeId,
            ]);
        } else {
            $estimate = Estimate::findOrFail($validated['estimate_id']);
        }
        
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
                ['name' => $validated['area_name']]
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
