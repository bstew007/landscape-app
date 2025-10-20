<?php

namespace App\Http\Controllers;

use App\Models\SiteVisit;
use Illuminate\Http\Request;

class RetainingWallCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $siteVisitId = $request->query('site_visit_id'); // Optional for context
        return view('calculators.retaining-wall.form', compact('siteVisitId'));
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'length' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:0.5',
            'equipment' => 'required|string',
            'block_type' => 'required|string',
            'crew_size' => 'required|integer|min:1',
            'drive_distance' => 'required|numeric|min:0',
            'drive_speed' => 'required|numeric|min:1',
            'labor_rate' => 'required|numeric|min:1',
            'markup' => 'required|numeric|min:0',
            'site_conditions' => 'nullable|numeric|min:0',
            'material_pickup' => 'nullable|numeric|min:0',
            'cleanup' => 'nullable|numeric|min:0',
            'site_visit_id' => 'required|exists:site_visits,id',
        ]);

        // Core inputs
        $length = $validated['length'];
        $height = $validated['height'];
        $crewSize = $validated['crew_size'];
        $driveDistance = $validated['drive_distance'];
        $driveSpeed = $validated['drive_speed'];
        $laborRate = $validated['labor_rate'];
        $markup = $validated['markup'];

        // Overhead
        $siteCondPct = ($validated['site_conditions'] ?? 0) / 100;
        $pickupPct = ($validated['material_pickup'] ?? 0) / 100;
        $cleanupPct = ($validated['cleanup'] ?? 0) / 100;

        // Labor calculation
        $sqft = $length * $height;
        $baseLaborPerSqft = $validated['equipment'] === 'excavator' ? 0.05 : 0.09;
        $wallLabor = $sqft * $baseLaborPerSqft;
        $driveTime = ($driveDistance / $driveSpeed) * $crewSize;
        $overheadHours = $wallLabor * ($siteCondPct + $pickupPct + $cleanupPct);
        $totalLaborHours = $wallLabor + $overheadHours + $driveTime;
        $laborCost = $totalLaborHours * $laborRate;

        // Material cost — replace with your real logic
        $materials = [
            'Wall Blocks' => 1250.00,
            'Pipe' => 180.00,
        ];
        $material_total = array_sum($materials);
        $markupAmount = ($laborCost + $material_total) * ($markup / 100);
        $finalPrice = $laborCost + $material_total + $markupAmount;

        // Merge and pass data
        $data = array_merge($validated, [
            'labor_hours' => round($wallLabor, 2),
            'overhead_hours' => round($overheadHours + $driveTime, 2),
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

