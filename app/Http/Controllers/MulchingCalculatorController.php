<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\Estimate;
use App\Models\SiteVisit;
use App\Services\LaborCostCalculatorService;
use App\Services\BudgetService;
use Barryvdh\DomPDF\Facade\Pdf;

class MulchingCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $mode = $request->query('mode');
        $estimateId = $request->query('estimate_id');
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = $siteVisitId ? SiteVisit::with('client')->findOrFail($siteVisitId) : null;

        $budgetService = app(BudgetService::class);
        $defaultLaborRate = $budgetService->getLaborRateForCalculators();

        return view('calculators.mulching.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit?->client?->id,
            'editMode' => false,
            'formData' => [],
            'mode' => $mode,
            'estimateId' => $estimateId,
            'defaultLaborRate' => $defaultLaborRate,
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $budgetService = app(BudgetService::class);
        $defaultLaborRate = $budgetService->getLaborRateForCalculators();

        return view('calculators.mulching.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'siteVisit' => $calculation->siteVisit()->with('client')->first(),
            'mode' => $calculation->is_template ? 'template' : null,
            'estimateId' => $calculation->estimate_id,
            'defaultLaborRate' => $defaultLaborRate,
        ]);
    }

 public function calculate(Request $request)
{
    $mode = $request->input('mode');
    $validated = $this->validateCalculationRequest($request, $mode);
    
    // Calculate mulch volume
    $mulchYards = $this->calculateMulchVolume(
        $request->input('area_sqft', 0),
        $request->input('depth_inches', 0)
    );
    
    // Build materials array
    $materials = $this->buildMaterialsArray($request, $validated, $mulchYards);
    $materialTotal = collect($materials)->sum('total_cost');
    
    // Calculate labor
    $laborData = $this->calculateLabor($request, $validated, $materialTotal);
    
    // Build final data array
    $data = $this->buildCalculationData($validated, $laborData, $materials, $materialTotal, $mulchYards, $request);
    
    // Save calculation
    $calc = $this->saveCalculation($validated, $data, $mode, $request);
    
    // Redirect based on mode
    return $this->redirectAfterCalculation($calc, $mode, $request);
}

    /**
     * Validate the calculation request
     */
    private function validateCalculationRequest(Request $request, ?string $mode): array
    {
        return $request->validate([
            'labor_rate' => 'required|numeric|min:1',
            'crew_size' => 'required|integer|min:1',
            'drive_distance' => 'required|numeric|min:0',
            'drive_speed' => 'required|numeric|min:1',
            'site_conditions' => 'nullable|numeric|min:0',
            'material_pickup' => 'nullable|numeric|min:0',
            'cleanup' => 'nullable|numeric|min:0',
            'site_visit_id' => ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id',
            'calculation_id' => 'nullable|exists:calculations,id',
            'job_notes' => 'nullable|string|max:2000',
            'tasks' => 'required|array',
            'tasks.*.qty' => 'nullable|numeric|min:0',
            'area_sqft' => 'nullable|numeric|min:0',
            'depth_inches' => 'nullable|numeric|min:0',
            'mulch_type' => 'nullable|string|max:255',
            'material_catalog_id' => 'nullable|exists:materials,id',
            'material_unit_cost' => 'nullable|numeric|min:0',
        ]);
    }

    /**
     * Calculate mulch volume in cubic yards
     */
    private function calculateMulchVolume(float $areaSqft, float $depthInches): float
    {
        if ($areaSqft <= 0 || $depthInches <= 0) {
            return 0;
        }
        
        return round(($areaSqft * ($depthInches / 12)) / 27, 2);
    }

    /**
     * Build materials array - only from catalog picker (custom materials feature removed)
     */
    private function buildMaterialsArray(Request $request, array $validated, float $mulchYards): array
    {
        $materials = [];
        
        // Materials now only come from catalog picker
        // Custom materials feature has been removed
        
        return $materials;
    }

    /**
     * Calculate labor hours and costs
     */
    private function calculateLabor(Request $request, array $validated, float $materialTotal): array
    {
        $inputTasks = $request->input('tasks', []);
        $laborRate = (float) $validated['labor_rate'];
        $dbRates = ProductionRate::where('calculator', 'mulching')->pluck('rate', 'task');

        $results = [];
        $laborTasks = [];
        $totalHours = 0;

        foreach ($inputTasks as $taskKey => $taskData) {
            $qty = (float) ($taskData['qty'] ?? 0);
            if ($qty <= 0 || !isset($dbRates[$taskKey])) {
                continue;
            }

            $rate = $dbRates[$taskKey];
            $hours = $qty * $rate;
            $cost = $hours * $laborRate;
            $taskName = str_replace('_', ' ', $taskKey);

            $results[] = [
                'task' => $taskName,
                'qty' => $qty,
                'rate' => $rate,
                'hours' => round($hours, 2),
                'cost' => round($cost, 2),
            ];

            $laborTasks[] = [
                'task_key' => $taskKey,
                'task_name' => ucwords($taskName),
                'description' => "Mulch {$qty} cubic yards",
                'quantity' => $qty,
                'unit' => 'cubic_yard',
                'production_rate' => $rate,
                'hours' => round($hours, 2),
                'hourly_rate' => $laborRate,
                'total_cost' => round($cost, 2),
            ];

            $totalHours += $hours;
        }

        $calculator = new LaborCostCalculatorService();
        $totals = $calculator->calculate(
            $totalHours,
            $laborRate,
            array_merge($request->all(), ['material_total' => $materialTotal])
        );

        return [
            'results' => $results,
            'labor_tasks' => $laborTasks,
            'total_hours' => $totalHours,
            'totals' => $totals,
        ];
    }

    /**
     * Build final calculation data array
     */
    private function buildCalculationData(
        array $validated,
        array $laborData,
        array $materials,
        float $materialTotal,
        float $mulchYards,
        Request $request
    ): array {
        return array_merge(
            $validated,
            $laborData['totals'],
            [
                'tasks' => $laborData['results'],
                'labor_tasks' => $laborData['labor_tasks'],
                'labor_by_task' => collect($laborData['results'])->pluck('hours', 'task')->map(fn($h) => round($h, 2))->toArray(),
                'area_sqft' => (float) $request->input('area_sqft', 0),
                'depth_inches' => (float) $request->input('depth_inches', 0),
                'mulch_yards' => $mulchYards,
                'labor_hours' => round($laborData['total_hours'], 2),
                'materials' => $materials,
                'material_total' => $materialTotal,
            ]
        );
    }

    /**
     * Save or update calculation
     */
    private function saveCalculation(array $validated, array $data, ?string $mode, Request $request): Calculation
    {
        if (!empty($validated['calculation_id'])) {
            $calc = Calculation::find($validated['calculation_id']);
            $calc->update(['data' => $data]);
            return $calc;
        }

        return Calculation::create([
            'site_visit_id' => $mode === 'template' ? null : $validated['site_visit_id'],
            'estimate_id' => $mode === 'template' ? ($request->input('estimate_id') ?: null) : null,
            'calculation_type' => 'mulching',
            'data' => $data,
            'is_template' => $mode === 'template',
            'template_name' => $mode === 'template' ? ($request->input('template_name') ?: null) : null,
            'template_scope' => $mode === 'template' ? ($request->input('template_scope') ?: 'global') : null,
            'client_id' => $mode === 'template' ? (Estimate::find($request->input('estimate_id'))?->client_id) : null,
            'property_id' => $mode === 'template' ? (Estimate::find($request->input('estimate_id'))?->property_id) : null,
        ]);
    }

    /**
     * Redirect after calculation
     */
    private function redirectAfterCalculation(Calculation $calc, ?string $mode, Request $request)
    {
        if ($mode === 'template') {
            return redirect()->route('estimates.show', $calc->estimate_id ?: $request->input('estimate_id'))
                ->with('success', 'Mulching template saved.');
        }

        return redirect()->route('calculators.mulching.showResult', $calc->id);
    }


    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.mulching.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.mulching.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('mulching_estimate.pdf');
    }
}
