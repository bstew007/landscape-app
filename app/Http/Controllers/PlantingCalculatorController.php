<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Models\Estimate;
use App\Services\LaborCostCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlantingCalculatorController extends Controller
{
    protected array $taskLabels = [
        'annual_flats' => 'Annual Flats',
        'annual_pots' => 'Annual Pots',
        'container_1g' => '1 Gallon Containers',
        'container_3g' => '3 Gallon Containers',
        'container_5g' => '5 Gallon Containers',
        'container_7g' => '7 Gallon Containers',
        'container_10g' => '10 Gallon Containers',
        'container_15g' => '15 Gallon Containers',
        'container_25g' => '25 Gallon Containers',
        'ball_and_burlap' => 'B&B Trees / Shrubs',
        'palm_8_12' => "Palms 8'-12'",
    ];

    public function showForm(Request $request)
    {
        $mode = $request->query('mode');
        $estimateId = $request->query('estimate_id');
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = null;

        if ($mode === 'template') {
            $siteVisitId = $siteVisitId ?: null;
        } else {
            $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);
        }

        return view('calculators.planting.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit?->client?->id,
            'editMode' => false,
            'formData' => [],
            'mode' => $mode,
            'estimateId' => $estimateId,
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->first();

        return view('calculators.planting.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit?->client?->id,
            'mode' => $calculation->is_template ? 'template' : null,
            'estimateId' => $calculation->estimate_id,
        ]);
    }

    public function calculate(Request $request)
    {
        $mode = $request->input('mode');
        $rules = [
            'labor_rate' => 'required|numeric|min:1',
            'crew_size' => 'required|integer|min:1',
            'drive_distance' => 'required|numeric|min:0',
            'drive_speed' => 'required|numeric|min:1',
            'site_conditions' => 'nullable|numeric|min:0',
            'material_pickup' => 'nullable|numeric|min:0',
            'cleanup' => 'nullable|numeric|min:0',
            'calculation_id' => 'nullable|exists:calculations,id',
            'job_notes' => 'nullable|string|max:2000',
            'plants' => 'required|array|min:1',
            'plants.*.catalog_id' => 'nullable|integer',
            'plants.*.name' => 'required|string',
            'plants.*.unit_cost' => 'required|numeric|min:0',
            'plants.*.unit' => 'required|string',
            'plants.*.quantity' => 'required|numeric|min:0',
            'tasks' => 'required|array',
            'tasks.*.qty' => 'nullable|numeric|min:0',
        ];
        $rules['site_visit_id'] = ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id';
        $validated = $request->validate($rules);

        $productionRates = ProductionRate::where('calculator', 'planting')->pluck('rate', 'task');
        $taskInputs = [];
        $inputTasks = $request->input('tasks', []);
        $inputPlants = $request->input('plants', []);

        $results = [];
        $laborTasks = []; // Enhanced format for import service
        $materials = [];
        $materialTotal = 0;
        $totalHours = 0;
        $laborRate = (float) $validated['labor_rate'];

        // Process plants from material catalog
        foreach ($inputPlants as $plant) {
            $qty = (float) ($plant['quantity'] ?? 0);
            $unitCost = (float) ($plant['unit_cost'] ?? 0);
            $plantName = $plant['name'] ?? 'Unknown Plant';
            $unit = $plant['unit'] ?? 'ea';
            $catalogId = $plant['catalog_id'] ?? null;

            if ($qty > 0) {
                // Determine production rate based on plant unit/size
                $taskKey = $this->determineTaskKeyFromUnit($unit);
                $ratePerUnit = $productionRates[$taskKey] ?? 0.1; // Default if not found

                // Calculate labor
                $hours = $qty * $ratePerUnit;
                $taskLabel = $this->taskLabels[$taskKey] ?? Str::title(str_replace('_', ' ', $taskKey));
                
                $results[] = [
                    'task' => $plantName,
                    'qty' => $qty,
                    'rate' => $ratePerUnit,
                    'hours' => round($hours, 2),
                    'cost' => round($hours * $laborRate, 2),
                ];

                // Enhanced labor task format for import
                $laborTasks[] = [
                    'task_key' => $taskKey,
                    'task_name' => $plantName,
                    'description' => "Install {$qty} {$plantName}",
                    'quantity' => $qty,
                    'unit' => $unit,
                    'production_rate' => $ratePerUnit,
                    'hours' => round($hours, 2),
                    'hourly_rate' => $laborRate,
                    'total_cost' => round($hours * $laborRate, 2),
                ];

                $totalHours += $hours;

                // Add material
                if ($unitCost > 0) {
                    $lineTotal = $qty * $unitCost;
                    $materials[] = [
                        'catalog_id' => $catalogId,
                        'task_key' => $taskKey,
                        'name' => $plantName,
                        'description' => "{$plantName} - Plant Material",
                        'quantity' => $qty,
                        'unit' => $unit,
                        'unit_cost' => $unitCost,
                        'total_cost' => round($lineTotal, 2),
                        'category' => 'Plants',
                    ];

                    $materialTotal += $lineTotal;
                }
            }
        }

        // Also track raw labor quantity inputs for editing
        foreach ($productionRates as $taskKey => $ratePerUnit) {
            $qty = (float) ($inputTasks[$taskKey]['qty'] ?? 0);
            $taskInputs[$taskKey] = $qty;
        }

        $calculator = new LaborCostCalculatorService();
        $totals = $calculator->calculate(
            $totalHours,
            $laborRate,
            array_merge($request->all(), ['material_total' => $materialTotal])
        );

        $data = array_merge($validated, $totals, [
            'tasks' => $results,
            'labor_tasks' => $laborTasks, // Enhanced format for import
            'labor_by_task' => collect($results)->pluck('hours', 'task')->map(fn ($hours) => round($hours, 2))->toArray(),
            'labor_hours' => round($totalHours, 2),
            'materials' => $materials,
            'material_total' => round($materialTotal, 2),
            'task_inputs' => $taskInputs,
            'plants' => $inputPlants, // Store selected plants for editing
        ]);

        if (!empty($validated['calculation_id'])) {
            $calc = Calculation::findOrFail($validated['calculation_id']);
            $calc->update(['data' => $data]);
        } else {
            if ($mode === 'template') {
                $estimateId = $request->input('estimate_id');
                $estimate = $estimateId ? Estimate::find($estimateId) : null;
                $calc = Calculation::create([
                    'site_visit_id' => null,
                    'estimate_id' => $estimateId ?: null,
                    'client_id' => $estimate?->client_id,
                    'property_id' => $estimate?->property_id,
                    'calculation_type' => 'planting',
                    'data' => $data,
                    'is_template' => true,
                    'template_name' => $request->input('template_name') ?: null,
                    'template_scope' => $request->input('template_scope') ?: 'global',
                ]);
            } else {
                $calc = Calculation::create([
                    'site_visit_id' => $validated['site_visit_id'],
                    'calculation_type' => 'planting',
                    'data' => $data,
                ]);
            }
        }

        if ($mode === 'template') {
            return redirect()->route('estimates.show', $request->input('estimate_id'))
                ->with('success', 'Planting template saved.');
        }

        return redirect()->route('calculators.planting.showResult', $calc->id);
    }

    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.planting.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.planting.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('planting_estimate.pdf');
    }

    /**
     * Get the unit of measurement for a planting task
     */
    protected function getTaskUnit(string $taskKey): string
    {
        return match($taskKey) {
            'annual_flats' => 'flats',
            'annual_pots' => 'pots',
            'container_1g', 'container_3g', 'container_5g', 
            'container_7g', 'container_10g', 'container_15g', 
            'container_25g' => 'ea',
            'ball_and_burlap' => 'ea',
            'palm_8_12' => 'ea',
            default => 'ea',
        };
    }

    /**
     * Determine the task key based on plant unit from material catalog
     */
    protected function determineTaskKeyFromUnit(string $unit): string
    {
        // Map catalog units to production rate task keys
        return match(strtolower($unit)) {
            'flats', 'flat' => 'annual_flats',
            'pots', 'pot', '4" pot', '6" pot' => 'annual_pots',
            '1 gal', '1g', '#1' => 'container_1g',
            '3 gal', '3g', '#3' => 'container_3g',
            '5 gal', '5g', '#5' => 'container_5g',
            '7 gal', '7g', '#7' => 'container_7g',
            '10 gal', '10g', '#10' => 'container_10g',
            '15 gal', '15g', '#15' => 'container_15g',
            '25 gal', '25g', '#25' => 'container_25g',
            'b&b', 'ball and burlap', 'balled and burlapped' => 'ball_and_burlap',
            'palm' => 'palm_8_12',
            default => 'container_1g', // Default to 1 gallon if unknown
        };
    }
}
