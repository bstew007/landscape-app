<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\SiteVisit;
use App\Models\Estimate;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductionRate;
use App\Services\LaborCostCalculatorService;
use App\Services\BudgetService;


class RetainingWallCalculatorController extends Controller
{
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

        $budgetService = app(BudgetService::class);
        $defaultLaborRate = $budgetService->getLaborRateForCalculators();

        return view('calculators.retaining-wall.form', [
            'siteVisit' => $siteVisit,
            'siteVisitId' => $siteVisitId,
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
        $siteVisit = $calculation->siteVisit()->with('client')->first();

        $budgetService = app(BudgetService::class);
        $defaultLaborRate = $budgetService->getLaborRateForCalculators();

        return view('calculators.retaining-wall.form', [
            'siteVisit' => $siteVisit,
            'siteVisitId' => $calculation->site_visit_id,
            'clientId' => $siteVisit?->client?->id,
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'mode' => $calculation->is_template ? 'template' : null,
            'estimateId' => $calculation->estimate_id,
            'defaultLaborRate' => $defaultLaborRate,
        ]);
    }

 public function calculate(Request $request)
{
    $mode = $request->input('mode');
    $request->merge([
        'use_capstones' => $request->has('use_capstones'),
        'include_geogrid' => $request->has('include_geogrid'),
    ]);

    $rules = [
        'job_notes' => 'nullable|string|max:1000',
        'length' => 'required|numeric|min:1',
        'height' => 'required|numeric|min:0.5',
        'block_system' => 'required|string|in:standard,allan_block',
        'block_brand' => 'required|string|in:belgard,techo,allan_block',
        'equipment' => 'required|string|in:excavator,skid_steer,manual',
        'crew_size' => 'required|integer|min:1',
        'drive_distance' => 'required|numeric|min:0',
        'drive_speed' => 'required|numeric|min:1',
        'labor_rate' => 'required|numeric|min:1',
        'site_conditions' => 'nullable|numeric|min:0',
        'material_pickup' => 'nullable|numeric|min:0',
        'cleanup' => 'nullable|numeric|min:0',
        'use_capstones' => 'nullable|boolean',
        'include_geogrid' => 'nullable|boolean',
        'calculation_id' => 'nullable|exists:calculations,id',
        'ab_straight_length' => 'nullable|numeric|min:0',
        'ab_straight_height' => 'nullable|numeric|min:0',
        'ab_curved_length' => 'nullable|numeric|min:0',
        'ab_curved_height' => 'nullable|numeric|min:0',
        'ab_step_count' => 'nullable|integer|min:0',
        'ab_column_count' => 'nullable|integer|min:0',
        'custom_materials' => 'nullable|array',
        'custom_materials.*.name' => 'nullable|string|max:255',
        'custom_materials.*.qty' => 'nullable|numeric|min:0',
        'custom_materials.*.unit_cost' => 'nullable|numeric|min:0',
    ];
    $rules['site_visit_id'] = ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id';
    $validated = $request->validate($rules);

    $length = $validated['length'];
    $height = $validated['height'];
    $sqft = $length * $height;

    // Calculate quantities only (no pricing)
    $blockCoverage = $validated['block_brand'] === 'belgard' ? 0.67 : 0.65;
    $blockCount = ceil($sqft / $blockCoverage);
    
    $includeCaps = $validated['use_capstones'] ?? false;
    $capCount = $includeCaps ? ceil($length) : 0;
    $adhesiveTubeCount = ceil($capCount / 20);

    $includeGeogrid = $validated['include_geogrid'] ?? false;
    $geogridLayers = $includeGeogrid && $height >= 4 ? floor($height / 2) : 0;
    $geogridLF = $length * $geogridLayers;

    $gravelVolumeCF = $length * ($height - 0.5) * 1.5;
    $gravelTons = $gravelVolumeCF / 21.6;

    $topsoilVolumeCF = $length * 0.5 * 1.5;
    $topsoilYards = $topsoilVolumeCF / 27;

    $fabricArea = $length * $height * 2;

    // Process custom materials from user input only
    $materials = [];
    $material_total = 0;
    
    $customMaterialsInput = $validated['custom_materials'] ?? [];
    foreach ($customMaterialsInput as $item) {
        $name = trim($item['name'] ?? '');
        $qty = isset($item['qty']) ? (float) $item['qty'] : null;
        $unitCost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : null;

        if ($name === '' || $qty === null || $unitCost === null) {
            continue;
        }

        $total = round($qty * $unitCost, 2);
        $materials[$name] = [
            'qty' => round($qty, 2),
            'unit_cost' => round($unitCost, 2),
            'total' => $total,
            'is_custom' => true,
        ];
        
        $material_total += $total;
    }

    $rates = ProductionRate::where('calculator', 'retaining_wall')->pluck('rate', 'task');
    $equipmentFactor = $validated['equipment'] === 'excavator' ? '_excavator' : '_manual';

    $labor = [
        'excavation' => $length * ($rates["excavation$equipmentFactor"] ?? $rates['excavation'] ?? 0.1),
        'base_install' => $sqft * ($rates["base_install$equipmentFactor"] ?? $rates['base_install'] ?? 0.01),
        'pipe_install' => $length * ($rates["pipe_install$equipmentFactor"] ?? $rates['pipe_install'] ?? 0.02),
        'gravel_backfill' => $gravelVolumeCF * ($rates["gravel_backfill$equipmentFactor"] ?? $rates['gravel_backfill'] ?? 0.03),
        'topsoil_backfill' => $topsoilVolumeCF * ($rates["topsoil_backfill$equipmentFactor"] ?? $rates['topsoil_backfill'] ?? 0.06),
        'underlayment' => $fabricArea * ($rates["underlayment$equipmentFactor"] ?? $rates['underlayment'] ?? 0.02),
        'geogrid' => $geogridLF * ($rates['geogrid'] ?? 0.04),
        'capstone' => $includeCaps ? $capCount * ($rates['capstone'] ?? 0.03) : 0,
    ];

    if ($validated['block_system'] === 'allan_block') {
        $labor['ab_straight_wall'] = ($validated['ab_straight_length'] ?? 0) * ($validated['ab_straight_height'] ?? 0) * ($rates['allan_block_laying_straight_wall'] ?? 0.2);
        $labor['ab_curved_wall'] = ($validated['ab_curved_length'] ?? 0) * ($validated['ab_curved_height'] ?? 0) * ($rates['allan_block_laying_curved_wall'] ?? 0.25);
        $labor['ab_stairs'] = ($validated['ab_step_count'] ?? 0) * ($rates['allan_block_stairs'] ?? 0.75);
        $labor['ab_columns'] = ($validated['ab_column_count'] ?? 0) * ($rates['allan_block_column'] ?? 1.2);
    } else {
        $labor['block_laying'] = $sqft * ($rates['block_laying'] ?? 0.08);
    }

    $wallLabor = array_sum($labor);

    // ✅ Use shared calculator
    $calculator = new \App\Services\LaborCostCalculatorService();
    $totals = $calculator->calculate(
        $wallLabor,
        $validated['labor_rate'],
        array_merge($validated, ['material_total' => $material_total])
    );

    // Build labor_tasks array
    $laborTasks = [];
    $taskDefinitions = [
        'excavation' => 'Excavate trench for wall foundation',
        'base_install' => 'Install and compact base material',
        'pipe_install' => 'Install drainage pipe',
        'gravel_backfill' => 'Place and compact gravel backfill',
        'topsoil_backfill' => 'Place topsoil backfill',
        'underlayment' => 'Install underlayment fabric',
        'geogrid' => 'Install geogrid reinforcement',
        'capstone' => 'Install capstones',
        'block_laying' => 'Lay standard retaining wall blocks',
        'ab_straight_wall' => 'Install Allan Block straight wall sections',
        'ab_curved_wall' => 'Install Allan Block curved wall sections',
        'ab_stairs' => 'Build Allan Block stair sections',
        'ab_columns' => 'Build Allan Block columns',
    ];

    foreach ($labor as $taskKey => $hours) {
        if ($hours > 0) {
            $taskName = ucwords(str_replace('_', ' ', $taskKey));
            $description = $taskDefinitions[$taskKey] ?? $taskName;
            
            // Determine quantity and unit based on task
            $quantity = 0;
            $unit = 'sqft';
            
            if ($taskKey === 'excavation' || $taskKey === 'base_install') {
                $quantity = $sqft;
                $unit = 'sqft';
            } elseif ($taskKey === 'pipe_install') {
                $quantity = $length;
                $unit = 'lf';
            } elseif ($taskKey === 'gravel_backfill') {
                $quantity = $gravelVolumeCF;
                $unit = 'cf';
            } elseif ($taskKey === 'topsoil_backfill') {
                $quantity = $topsoilVolumeCF;
                $unit = 'cf';
            } elseif ($taskKey === 'underlayment') {
                $quantity = $fabricArea;
                $unit = 'sqft';
            } elseif ($taskKey === 'geogrid') {
                $quantity = $geogridLF;
                $unit = 'lf';
            } elseif ($taskKey === 'capstone') {
                $quantity = $capCount;
                $unit = 'ea';
            } elseif ($taskKey === 'block_laying') {
                $quantity = $sqft;
                $unit = 'sqft';
            } elseif ($taskKey === 'ab_straight_wall') {
                $quantity = $validated['ab_straight_length'] ?? 0;
                $unit = 'sqft';
            } elseif ($taskKey === 'ab_curved_wall') {
                $quantity = $validated['ab_curved_length'] ?? 0;
                $unit = 'sqft';
            } elseif ($taskKey === 'ab_stairs') {
                $quantity = $validated['ab_step_count'] ?? 0;
                $unit = 'ea';
            } elseif ($taskKey === 'ab_columns') {
                $quantity = $validated['ab_column_count'] ?? 0;
                $unit = 'ea';
            }

            $productionRate = $quantity > 0 ? round($hours / $quantity, 4) : 0;
            $totalCost = round($hours * $validated['labor_rate'], 2);

            $laborTasks[] = [
                'task_key' => $taskKey,
                'task_name' => $taskName,
                'description' => $description,
                'quantity' => round($quantity, 2),
                'unit' => $unit,
                'production_rate' => $productionRate,
                'hours' => round($hours, 2),
                'hourly_rate' => $validated['labor_rate'],
                'total_cost' => $totalCost,
            ];
        }
    }

    $data = array_merge($validated, [
    'block_count' => $blockCount,
    'cap_count' => $capCount,
    'block_unit_cost' => $blockUnitCost,
    'capstone_unit_cost' => $capUnitCost,
    'gravel_tons' => ceil($gravelTons),
    'topsoil_yards' => ceil($topsoilYards),
    'fabric_area' => round($fabricArea, 2),
    'geogrid_layers' => $geogridLayers,
    'geogrid_lf' => $geogridLF,
    'adhesive_tubes' => $adhesiveTubeCount,

    'labor_by_task' => array_map(fn($h) => round($h, 2), $labor),
    'labor_hours' => round($wallLabor, 2), // ✅ Add this line
    'labor_tasks' => $laborTasks,
    'material_total' => round($material_total, 2),
    'materials' => $materials,
    'custom_materials' => $customMaterials,

    'ab_straight_sqft' => round(($validated['ab_straight_length'] ?? 0) * ($validated['ab_straight_height'] ?? 0), 2),
    'ab_curved_sqft' => round(($validated['ab_curved_length'] ?? 0) * ($validated['ab_curved_height'] ?? 0), 2),
    'job_notes' => $validated['job_notes'] ?? null,
], $totals);

    if (!empty($validated['calculation_id'])) {
        $calc = Calculation::find($validated['calculation_id']);
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
                'calculation_type' => 'retaining_wall',
                'data' => $data,
                'is_template' => true,
                'template_name' => $request->input('template_name') ?: null,
                'template_scope' => $request->input('template_scope') ?: 'global',
            ]);
        } else {
            $calc = Calculation::create([
                'site_visit_id' => $validated['site_visit_id'],
                'calculation_type' => 'retaining_wall',
                'data' => $data,
            ]);
        }
    }

    if ($mode === 'template') {
        return redirect()->route('estimates.show', $request->input('estimate_id'))
            ->with('success', 'Retaining Wall template saved.');
    }

    return redirect()->route('calculations.wall.showResult', $calc->id);
}



    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.retaining-wall.results', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.retaining-wall.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('retaining_wall_estimate.pdf');
    }
}
