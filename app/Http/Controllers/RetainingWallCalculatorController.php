<?php

namespace App\Http\Controllers;

use App\Models\SiteVisit;
use App\Models\Calculation;
use Illuminate\Http\Request;

class RetainingWallCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $siteVisitId = $request->query('site_visit_id');

        return view('calculators.retaining-wall.form', [
            'siteVisitId' => $siteVisitId,
            'formData' => [],
            'editMode' => false
        ]);
    }

    public function edit(Calculation $calculation)
    {
        return view('calculators.retaining-wall.form', [
            'siteVisitId' => $calculation->site_visit_id,
            'editMode' => true,
            'calculation' => $calculation,
            'formData' => $calculation->data
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'length' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:0.5',
            'equipment' => 'required|string',
            'crew_size' => 'required|integer|min:1',
            'drive_distance' => 'required|numeric|min:0',
            'drive_speed' => 'required|numeric|min:1',
            'labor_rate' => 'required|numeric|min:1',
            'markup' => 'required|numeric|min:0',
            'site_conditions' => 'nullable|numeric|min:0',
            'material_pickup' => 'nullable|numeric|min:0',
            'cleanup' => 'nullable|numeric|min:0',
            'site_visit_id' => 'required|exists:site_visits,id',
            'block_brand' => 'required|string|in:belgard,techo',
            'include_capstones' => 'nullable|boolean',
        ]);

        // --- Dimensions ---
        $length = $validated['length'];
        $height = $validated['height'];
        $sqft = $length * $height;

        // --- Blocks based on brand ---
        $blockCoverage = $validated['block_brand'] === 'belgard' ? 0.67 : 0.65;
        $blockCount = ceil($sqft / $blockCoverage);
        $blockCost = $blockCount * 11;

        // --- Capstones + adhesive ---
        $includeCaps = $validated['include_capstones'] ?? false;
        $capCount = $includeCaps ? ceil($length) : 0;
        $capCost = $capCount * 18;
        $adhesiveTubeCount = ceil($capCount / 20);
        $adhesiveCost = $adhesiveTubeCount * 8;

        // --- Pipe ---
        $pipeLength = $length;
        $pipeCost = $pipeLength * 2;

        // --- Gravel ---
        $gravelDepth = 1.5;
        $gravelVolumeCF = $length * $height * $gravelDepth;
        $gravelTons = $gravelVolumeCF / 21.6;
        $gravelCost = ceil($gravelTons) * 45;

        // --- Topsoil ---
        $topsoilDepth = 0.5;
        $topsoilVolumeCF = $length * $topsoilDepth * $gravelDepth;
        $topsoilYards = $topsoilVolumeCF / 27;
        $topsoilCost = ceil($topsoilYards) * 35;

        // --- Underlayment ---
        $fabricArea = $length * $height * 2;
        $fabricCost = $fabricArea * 0.30;

        // --- Geogrid ---
        $geogridLayers = $height >= 4 ? floor($height / 2) : 0;
        $geogridLF = $length * $geogridLayers;
        $geogridCost = $geogridLF * $height * 1.50;

        // --- Materials ---
        $materials = [
            'Wall Blocks' => round($blockCost, 2),
            'Capstones' => round($capCost, 2),
            'Adhesive for Capstones' => round($adhesiveCost, 2),
            'Drain Pipe' => round($pipeCost, 2),
            '#57 Gravel' => round($gravelCost, 2),
            'Topsoil' => round($topsoilCost, 2),
            'Underlayment Fabric' => round($fabricCost, 2),
            'Geogrid' => round($geogridCost, 2),
        ];
        $material_total = array_sum($materials);

        // --- Labor Breakdown ---
        $labor = [
            'excavation' => $length * 0.1,
            'base_install' => $sqft * 0.15,
            'block_laying' => $sqft * ($validated['equipment'] === 'excavator' ? 0.05 : 0.09),
            'pipe_install' => $pipeLength * 0.02,
            'gravel_backfill' => $sqft * 0.08,
            'topsoil_backfill' => $sqft * 0.06,
            'underlayment' => $fabricArea * 0.03,
            'geogrid' => $geogridLF * 0.04,
            'capstone' => $includeCaps ? $capCount * 0.03 : 0,
        ];

        $wallLabor = array_sum($labor);
        $overhead = $wallLabor * (
            ($validated['site_conditions'] ?? 0) / 100 +
            ($validated['material_pickup'] ?? 0) / 100 +
            ($validated['cleanup'] ?? 0) / 100
        );
        $driveTime = $validated['drive_distance'] / $validated['drive_speed'];
        $totalLaborHours = $wallLabor + $overhead + $driveTime;
        $laborCost = $totalLaborHours * $validated['labor_rate'];

        // --- Final price ---
        $markupAmount = ($laborCost + $material_total) * ($validated['markup'] / 100);
        $finalPrice = $laborCost + $material_total + $markupAmount;

        // --- Return to results ---
        $data = array_merge($validated, [
            'block_count' => $blockCount,
            'cap_count' => $capCount,
            'adhesive_tubes' => $adhesiveTubeCount,
            'gravel_tons' => ceil($gravelTons),
            'topsoil_yards' => ceil($topsoilYards),
            'fabric_area' => round($fabricArea, 2),
            'geogrid_layers' => $geogridLayers,
            'geogrid_lf' => $geogridLF,
            'labor_by_task' => array_map(fn($h) => round($h, 2), $labor),
            'labor_hours' => round($wallLabor, 2),
            'overhead_hours' => round($overhead + $driveTime, 2),
            'total_hours' => round($totalLaborHours, 2),
            'labor_cost' => round($laborCost, 2),
            'material_total' => round($material_total, 2),
            'markup_amount' => round($markupAmount, 2),
            'final_price' => round($finalPrice, 2),
            'materials' => $materials,
        ]);

        return view('calculators.retaining-wall.results', [
            'data' => $data,
            'siteVisit' => SiteVisit::findOrFail($validated['site_visit_id']),
        ]);
    }
}
