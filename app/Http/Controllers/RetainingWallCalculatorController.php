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
        ];
        
        // site_visit_id depends on mode
        $rules['site_visit_id'] = ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id';
        $validated = $request->validate($rules);

        $length = $validated['length'];
        $height = $validated['height'];
        $sqft = $length * $height;

        // --------------------------------------------
        // 🔨 Calculations - Quantities Only (No Pricing)
        // --------------------------------------------
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

        // --------------------------------------------
        // 📦 Materials from catalog picker only (custom materials feature removed)
        // --------------------------------------------
        $materials = [];
        $material_total = 0;

        // --------------------------------------------
        // 👷 Labor Calculations (from DB)
        // --------------------------------------------
        $dbRates = ProductionRate::where('calculator', 'retaining_wall')->pluck('rate', 'task');
        $equipmentFactor = $validated['equipment'] === 'excavator' ? '_excavator' : '_manual';

        $labor = [
            'excavation' => $length * ($dbRates["excavation$equipmentFactor"] ?? $dbRates['excavation'] ?? 0.1),
            'base_install' => $sqft * ($dbRates["base_install$equipmentFactor"] ?? $dbRates['base_install'] ?? 0.01),
            'pipe_install' => $length * ($dbRates["pipe_install$equipmentFactor"] ?? $dbRates['pipe_install'] ?? 0.02),
            'gravel_backfill' => $gravelVolumeCF * ($dbRates["gravel_backfill$equipmentFactor"] ?? $dbRates['gravel_backfill'] ?? 0.03),
            'topsoil_backfill' => $topsoilVolumeCF * ($dbRates["topsoil_backfill$equipmentFactor"] ?? $dbRates['topsoil_backfill'] ?? 0.06),
            'underlayment' => $fabricArea * ($dbRates["underlayment$equipmentFactor"] ?? $dbRates['underlayment'] ?? 0.02),
            'geogrid' => $geogridLF * ($dbRates['geogrid'] ?? 0.04),
            'capstone' => $includeCaps ? $capCount * ($dbRates['capstone'] ?? 0.03) : 0,
        ];

        if ($validated['block_system'] === 'allan_block') {
            $labor['ab_straight_wall'] = ($validated['ab_straight_length'] ?? 0) * ($validated['ab_straight_height'] ?? 0) * ($dbRates['allan_block_laying_straight_wall'] ?? 0.2);
            $labor['ab_curved_wall'] = ($validated['ab_curved_length'] ?? 0) * ($validated['ab_curved_height'] ?? 0) * ($dbRates['allan_block_laying_curved_wall'] ?? 0.25);
            $labor['ab_stairs'] = ($validated['ab_step_count'] ?? 0) * ($dbRates['allan_block_stairs'] ?? 0.75);
            $labor['ab_columns'] = ($validated['ab_column_count'] ?? 0) * ($dbRates['allan_block_column'] ?? 1.2);
        } else {
            $labor['block_laying'] = $sqft * ($dbRates['block_laying'] ?? 0.08);
        }

        $baseLaborHours = array_sum($labor);

        // --------------------------------------------
        // 🧮 Use the Shared LaborCostCalculatorService
        // --------------------------------------------
        $calculator = new LaborCostCalculatorService();
        $totals = $calculator->calculate(
            baseHours: $baseLaborHours,
            laborRate: (float) $validated['labor_rate'],
            inputs: array_merge($request->all(), ['material_total' => $material_total])
        );

        // --------------------------------------------
        // 📋 Enhanced Labor Tasks Array
        // --------------------------------------------
        $laborRate = (float) $validated['labor_rate'];
        $laborTasks = [];
        $taskRates = ProductionRate::where('calculator', 'retaining_wall')->get()->keyBy('task');
        
        $taskDefinitions = [
            'excavation' => ['desc' => 'Excavate trench for wall foundation', 'qty' => $sqft, 'unit' => 'sqft'],
            'base_install' => ['desc' => 'Install and compact base material', 'qty' => $sqft, 'unit' => 'sqft'],
            'pipe_install' => ['desc' => 'Install drainage pipe', 'qty' => $length, 'unit' => 'lf'],
            'gravel_backfill' => ['desc' => 'Place and compact gravel backfill', 'qty' => $gravelVolumeCF, 'unit' => 'cf'],
            'topsoil_backfill' => ['desc' => 'Place topsoil backfill', 'qty' => $topsoilVolumeCF, 'unit' => 'cf'],
            'underlayment' => ['desc' => 'Install underlayment fabric', 'qty' => $fabricArea, 'unit' => 'sqft'],
            'geogrid' => ['desc' => 'Install geogrid reinforcement', 'qty' => $geogridLF, 'unit' => 'lf'],
            'capstone' => ['desc' => 'Install capstones', 'qty' => $capCount, 'unit' => 'ea'],
            'block_laying' => ['desc' => 'Lay standard retaining wall blocks', 'qty' => $sqft, 'unit' => 'sqft'],
            'ab_straight_wall' => ['desc' => 'Install Allan Block straight wall sections', 'qty' => ($validated['ab_straight_length'] ?? 0) * ($validated['ab_straight_height'] ?? 0), 'unit' => 'sqft'],
            'ab_curved_wall' => ['desc' => 'Install Allan Block curved wall sections', 'qty' => ($validated['ab_curved_length'] ?? 0) * ($validated['ab_curved_height'] ?? 0), 'unit' => 'sqft'],
            'ab_stairs' => ['desc' => 'Build Allan Block stair sections', 'qty' => $validated['ab_step_count'] ?? 0, 'unit' => 'ea'],
            'ab_columns' => ['desc' => 'Build Allan Block columns', 'qty' => $validated['ab_column_count'] ?? 0, 'unit' => 'ea'],
        ];
        
        foreach ($labor as $taskKey => $hours) {
            if ($hours <= 0) continue;
            
            $taskName = ucwords(str_replace('_', ' ', $taskKey));
            $taskInfo = $taskDefinitions[$taskKey] ?? ['desc' => $taskName, 'qty' => 0, 'unit' => 'sqft'];
            $rate = $taskRates->get($taskKey);
            $productionRate = $rate ? $rate->rate : 0;
            
            $laborTasks[] = [
                'task_key' => $taskKey,
                'task_name' => $taskName,
                'description' => $taskInfo['desc'],
                'quantity' => round($taskInfo['qty'], 2),
                'unit' => $taskInfo['unit'],
                'production_rate' => $productionRate,
                'hours' => round($hours, 2),
                'hourly_rate' => $laborRate,
                'total_cost' => round($hours * $laborRate, 2),
            ];
        }

        // --------------------------------------------
        // 💾 Prepare and Save
        // --------------------------------------------
        $data = array_merge($validated, [
            'sqft' => round($sqft, 2),
            'block_count' => $blockCount,
            'cap_count' => $capCount,
            'gravel_tons' => ceil($gravelTons),
            'topsoil_yards' => ceil($topsoilYards),
            'fabric_area' => round($fabricArea, 2),
            'geogrid_layers' => $geogridLayers,
            'geogrid_lf' => round($geogridLF, 2),
            'adhesive_tubes' => $adhesiveTubeCount,
            'ab_straight_sqft' => round(($validated['ab_straight_length'] ?? 0) * ($validated['ab_straight_height'] ?? 0), 2),
            'ab_curved_sqft' => round(($validated['ab_curved_length'] ?? 0) * ($validated['ab_curved_height'] ?? 0), 2),
            'labor_by_task' => array_map(fn($h) => round($h, 2), $labor),
            'labor_hours' => round($baseLaborHours, 2),
            'labor_tasks' => $laborTasks,
            'materials' => $materials,
            'material_total' => round($material_total, 2),
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
