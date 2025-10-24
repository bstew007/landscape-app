<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductionRate;

class RetainingWallCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);

        return view('calculators.retaining-wall.form', [
            'siteVisitId' => $siteVisit->id,
            'clientId' => $siteVisit->client->id,
            'editMode' => false,
            'formData' => [],
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.retaining-wall.form', [
            'siteVisitId' => $siteVisit->id,
            'clientId' => $siteVisit->client->id,
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
        ]);
    }

    public function calculate(Request $request)
{
    // ✅ Normalize booleans from checkbox inputs
    $request->merge([
        'use_capstones' => $request->has('use_capstones'),
        'include_geogrid' => $request->has('include_geogrid'),
    ]);

    // ✅ Validate inputs
    $validated = $request->validate([
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
        'markup' => 'required|numeric|min:0',
        'site_conditions' => 'nullable|numeric|min:0',
        'material_pickup' => 'nullable|numeric|min:0',
        'cleanup' => 'nullable|numeric|min:0',
        'site_visit_id' => 'required|exists:site_visits,id',
        'use_capstones' => 'nullable|boolean',
        'include_geogrid' => 'nullable|boolean',
        'calculation_id' => 'nullable|exists:calculations,id',
        'ab_straight_length' => 'nullable|numeric|min:0',
        'ab_straight_height' => 'nullable|numeric|min:0',
        'ab_curved_length' => 'nullable|numeric|min:0',
        'ab_curved_height' => 'nullable|numeric|min:0',
        'ab_step_count' => 'nullable|integer|min:0',
        'ab_column_count' => 'nullable|integer|min:0',
        'override_block_cost' => 'nullable|numeric|min:0',
        'override_capstone_cost' => 'nullable|numeric|min:0',
        // Add these new optional override fields
        'override_pipe_cost' => 'nullable|numeric|min:0',
        'override_gravel_cost' => 'nullable|numeric|min:0',
        'override_topsoil_cost' => 'nullable|numeric|min:0',
        'override_fabric_cost' => 'nullable|numeric|min:0',
        'override_geogrid_cost' => 'nullable|numeric|min:0',
        'override_adhesive_cost' => 'nullable|numeric|min:0',   
    ]);

    // --------------------------------------------
    // 🔢 Geometry & Inputs
    // --------------------------------------------
    $length = $validated['length'];
    $height = $validated['height'];
    $sqft = $length * $height;
    $blockSystem = $validated['block_system'];

    // --------------------------------------------
    // 🧱 Block Calculations
    // --------------------------------------------
    $blockCoverage = $validated['block_brand'] === 'belgard' ? 0.67 : 0.65;
    $blockCount = ceil($sqft / $blockCoverage);
    $blockUnitCost = $validated['override_block_cost'] ?? 11.00;
    $blockCost = $blockCount * $blockUnitCost;

    // --------------------------------------------
    // 🧱 Capstone Calculations (optional)
    // --------------------------------------------
    $includeCaps = $validated['use_capstones'] ?? false;
    $capCount = $includeCaps ? ceil($length) : 0;
    $capUnitCost = $validated['override_capstone_cost'] ?? 18.00;
    $capCost = $capCount * $capUnitCost;
    $adhesiveTubeCount = ceil($capCount / 20);
 
      // --------------------------------------------
    // 🧵 Geogrid (optional)
    // --------------------------------------------
    $includeGeogrid = $validated['include_geogrid'] ?? false;
    $geogridLayers = $includeGeogrid && $height >= 4 ? floor($height / 2) : 0;
    $geogridLF = $length * $geogridLayers;

    // --------------------------------------------
    // Materials Costs
    // --------------------------------------------
    $pipeUnitCost = $validated['override_pipe_cost'] ?? 2.00;
    $gravelUnitCost = $validated['override_gravel_cost'] ?? 85.00;
    $topsoilUnitCost = $validated['override_topsoil_cost'] ?? 5.00;
    $fabricUnitCost = $validated['override_fabric_cost'] ?? 0.30;
    $geogridUnitCost = $validated['override_geogrid_cost'] ?? 1.50;
    $adhesiveUnitCost = $validated['override_adhesive_cost'] ?? 8.00;

    $pipeCost = $length * $pipeUnitCost;

    $gravelVolumeCF = $length * ($height - 0.5) * 1.5;
    $gravelTons = $gravelVolumeCF / 21.6;
    $gravelCost = ceil($gravelTons) * $gravelUnitCost;

    $topsoilVolumeCF = $length * 0.5 * 1.5;
    $topsoilYards = $topsoilVolumeCF / 27;
    $topsoilCost = ceil($topsoilYards) * $topsoilUnitCost;

    $fabricArea = $length * $height * 2;
    $fabricCost = $fabricArea * $fabricUnitCost;

    $geogridCost = $geogridLF * $height * $geogridUnitCost;

    $adhesiveCost = $adhesiveTubeCount * $adhesiveUnitCost;


  
   

    // --------------------------------------------
    // 📦 Material Summary
    // --------------------------------------------
    $materials = [
        'Wall Blocks' => round($blockCost, 2),
        'Capstones' => round($capCost, 2),
        'Drain Pipe' => round($pipeCost, 2),
        '#57 Gravel' => round($gravelCost, 2),
        'Topsoil' => round($topsoilCost, 2),
        'Underlayment Fabric' => round($fabricCost, 2),
        'Geogrid' => round($geogridCost, 2),
        'Adhesive for Capstones' => round($adhesiveCost, 2),
    ];
    $material_total = array_sum($materials);

    // --------------------------------------------
    // 🧠 Labor Calculations
    // --------------------------------------------
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

    // --------------------------------------------
    // 🧱 Allan Block Specific Labor
    // --------------------------------------------
    if ($blockSystem === 'allan_block') {
        $ab_straight_sqft = ($validated['ab_straight_length'] ?? 0) * ($validated['ab_straight_height'] ?? 0);
        $ab_curved_sqft = ($validated['ab_curved_length'] ?? 0) * ($validated['ab_curved_height'] ?? 0);
        $step_count = $validated['ab_step_count'] ?? 0;
        $column_count = $validated['ab_column_count'] ?? 0;

        $labor['ab_straight_wall'] = $ab_straight_sqft * ($rates['allan_block_laying_straight_wall'] ?? 0.2);
        $labor['ab_curved_wall'] = $ab_curved_sqft * ($rates['allan_block_laying_curved_wall'] ?? 0.25);
        $labor['ab_stairs'] = $step_count * ($rates['allan_block_stairs'] ?? 0.75);
        $labor['ab_columns'] = $column_count * ($rates['allan_block_column'] ?? 1.2);
    } else {
        $labor['block_laying'] = $sqft * ($rates['block_laying'] ?? 0.08);
    }

    // --------------------------------------------
    // 💰 Labor Costs
    // --------------------------------------------
    $wallLabor = array_sum($labor);
    $siteCondPct = ($validated['site_conditions'] ?? 0) / 100;
    $pickupPct = ($validated['material_pickup'] ?? 0) / 100;
    $cleanupPct = ($validated['cleanup'] ?? 0) / 100;

    $overheadHours = $wallLabor * ($siteCondPct + $pickupPct + $cleanupPct);
    $driveTime = $validated['drive_distance'] / $validated['drive_speed'];

    $totalLaborHours = $wallLabor + $overheadHours + $driveTime;
    $laborCost = $totalLaborHours * $validated['labor_rate'];

    // --------------------------------------------
    // 📈 Final Pricing
    // --------------------------------------------
$costBeforeMargin = $laborCost + $material_total;
$finalPrice = $costBeforeMargin / (1 - ($validated['markup'] / 100));
$markupAmount = $finalPrice - $costBeforeMargin;

    // --------------------------------------------
    // 💾 Save or Update Calculation
    // --------------------------------------------
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
        'labor_hours' => round($wallLabor, 2),
        'overhead_hours' => round($overheadHours, 2),
        'drive_time' => round($driveTime, 2),
        'total_hours' => round($totalLaborHours, 2),
        'labor_cost' => round($laborCost, 2),
        'material_total' => round($material_total, 2),
        'markup_amount' => round($markupAmount, 2),
        'final_price' => round($finalPrice, 2),
        'materials' => $materials,
        'ab_straight_sqft' => round($ab_straight_sqft ?? 0, 2),
        'ab_curved_sqft' => round($ab_curved_sqft ?? 0, 2),
        'job_notes' => $validated['job_notes'] ?? null,
    ]);
    
    $calc = !empty($validated['calculation_id'])
        ? tap(Calculation::find($validated['calculation_id']))->update(['data' => $data])
        : Calculation::create([
            'site_visit_id' => $validated['site_visit_id'],
            'calculation_type' => 'retaining_wall',
            'data' => $data,
        ]);

    return redirect()->route('calculations.showResult', $calc->id);
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
