<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class RetainingWallCalculatorController extends Controller
{
   public function showForm(Request $request)
{
    $siteVisitId = $request->query('site_visit_id');

    // Load the site visit with the related client
    $siteVisit = \App\Models\SiteVisit::with('client')->findOrFail($siteVisitId);

    return view('calculators.retaining-wall.form', [
        'siteVisitId' => $siteVisit->id,
        //'siteVisit' => SiteVisit::with('client')->findOrFail($validated['site_visit_id']),
        'clientId' => $siteVisit->client->id, // 👈 pass the actual client ID
        'editMode' => false,
        'formData' => [],
    ]);
}

    public function edit(Calculation $calculation)
{
    $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

    return view('calculators.retaining-wall.form', [
        'siteVisitId' => $siteVisit->id,
        'clientId' => $siteVisit->client->id,  // ✅ Pass the client ID
        'editMode' => true,
        'formData' => $calculation->data,
        'calculation' => $calculation,
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
            'calculation_id' => 'nullable|exists:calculations,id',
        ]);

        $length = $validated['length'];
        $height = $validated['height'];
        $sqft = $length * $height;

        $blockCoverage = $validated['block_brand'] === 'belgard' ? 0.67 : 0.65;
        $blockCount = ceil($sqft / $blockCoverage);
        $blockCost = $blockCount * 11.00;

        $includeCaps = $validated['include_capstones'] ?? false;
        $capCount = $includeCaps ? ceil($length) : 0;
        $capCost = $capCount * 18.00;

        $adhesiveCoveragePerTube = 20;
        $adhesiveTubeCount = ceil($capCount / $adhesiveCoveragePerTube);
        $adhesiveCost = $adhesiveTubeCount * 8.00;

        $pipeCost = $length * 2.00;

        $gravelVolumeCF = $length * $height * 1.5;
        $gravelTons = $gravelVolumeCF / 21.6;
        $gravelCost = ceil($gravelTons) * 45.00;

        $topsoilVolumeCF = $length * 0.5 * 1.5;
        $topsoilYards = $topsoilVolumeCF / 27;
        $topsoilCost = ceil($topsoilYards) * 35.00;

        $fabricArea = $length * $height * 2;
        $fabricCost = $fabricArea * 0.30;

        $geogridLayers = $height >= 4 ? floor($height / 2) : 0;
        $geogridLF = $length * $geogridLayers;
        $geogridCost = $geogridLF * $height * 1.50;

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

        $labor = [];
        $labor['excavation'] = $length * 0.1;
        $labor['base_install'] = $sqft * 0.15;
        $labor['block_laying'] = $sqft * ($validated['equipment'] === 'excavator' ? 0.05 : 0.09);
        $labor['pipe_install'] = $length * 0.02;
        $labor['gravel_backfill'] = $sqft * 0.08;
        $labor['topsoil_backfill'] = $sqft * 0.06;
        $labor['underlayment'] = $fabricArea * 0.03;
        $labor['geogrid'] = $geogridLF * 0.04;
        $labor['capstone'] = $includeCaps ? $capCount * 0.03 : 0;

        $wallLabor = array_sum($labor);

        $siteCondPct = ($validated['site_conditions'] ?? 0) / 100;
        $pickupPct = ($validated['material_pickup'] ?? 0) / 100;
        $cleanupPct = ($validated['cleanup'] ?? 0) / 100;
        $overheadHours = $wallLabor * ($siteCondPct + $pickupPct + $cleanupPct);
        $driveTime = $validated['drive_distance'] / $validated['drive_speed'];

        $totalLaborHours = $wallLabor + $overheadHours + $driveTime;
        $laborCost = $totalLaborHours * $validated['labor_rate'];

        $markupAmount = ($laborCost + $material_total) * ($validated['markup'] / 100);
        $finalPrice = $laborCost + $material_total + $markupAmount;

        $data = array_merge($validated, [
            'block_count' => $blockCount,
            'cap_count' => $capCount,
            'gravel_tons' => ceil($gravelTons),
            'topsoil_yards' => ceil($topsoilYards),
            'fabric_area' => round($fabricArea, 2),
            'geogrid_layers' => $geogridLayers,
            'geogrid_lf' => $geogridLF,
            'labor_by_task' => array_map(fn($h) => round($h, 2), $labor),
            'labor_hours' => round($wallLabor, 2),
            'overhead_hours' => round($overheadHours + $driveTime, 2),
            'total_hours' => round($totalLaborHours, 2),
            'labor_cost' => round($laborCost, 2),
            'material_total' => round($material_total, 2),
            'markup_amount' => round($markupAmount, 2),
            'final_price' => round($finalPrice, 2),
            'materials' => $materials,
            'adhesive_tubes' => $adhesiveTubeCount,
        ]);

        if (!empty($validated['calculation_id'])) {
            $calc = Calculation::find($validated['calculation_id']);
            $calc->update(['data' => $data]);

             //return redirect()->route('clients.site-visits.show', [$calc->siteVisit->client_id, $calc->site_visit_id])
                      //   ->with('success', 'Calculation updated successfully.');
            return view('calculators.retaining-wall.results', [
                'data' => $data,
                'siteVisit' => SiteVisit::with('client')->findOrFail($validated['site_visit_id']), // ✅ fixed
                'calculation' => $calc, // ✅ This is what makes the PDF button work
            ]);
        }

        $calc = Calculation::create([
    'site_visit_id' => $validated['site_visit_id'],
    'calculation_type' => 'retaining_wall',
    'data' => $data,
]);

return redirect()->route('calculations.showResult', $calc->id);
    }
    // Generate PDF of the calculation
    public function downloadPdf(Calculation $calculation)
{
   // $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail(); bad
   $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id); // ✅ good


    $data = $calculation->data;

    $pdf = Pdf::loadView('calculators.retaining-wall.pdf', [
        'data' => $data,
        'siteVisit' => $siteVisit,
        'calculation' => $calculation,
    ]);

    return $pdf->download('retaining_wall_estimate.pdf');
}
// Show calculation results -- pdf stuff
public function showResult(Calculation $calculation)
{
    $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

    return view('calculators.retaining-wall.results', [
        'data' => $calculation->data,
        'siteVisit' => $siteVisit,
        'calculation' => $calculation,
    ]);
}


}

