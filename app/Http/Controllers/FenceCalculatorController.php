<?php

namespace App\Http\Controllers;
    
use Illuminate\Http\Request;
use App\Models\Calculation;
use App\Models\SiteVisit;
use App\Models\Estimate;
use App\Services\FenceLaborEstimatorService;
use App\Services\BudgetService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;


class FenceCalculatorController extends Controller
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

    return view('calculators.fence.form', [
        'siteVisitId' => $siteVisit?->id ?? $siteVisitId,
        'clientId' => $siteVisit?->client?->id,
        'editMode' => false,
        'formData' => [],
        'mode' => $mode,
        'estimateId' => $estimateId,
        'defaultLaborRate' => $defaultLaborRate,
    ]);
}




public function downloadPdf($id)
{
    $calculation = Calculation::findOrFail($id);
    $data = $calculation->data;
    $siteVisit = $calculation->siteVisit;

    $labor_breakdown = $data['labor_breakdown'] ?? [];

    $pdf = PDF::loadView('calculators.fence.pdf', [
        'data' => $data,
        'siteVisit' => $siteVisit,
        'labor_breakdown' => $labor_breakdown,
    ]);

    return $pdf->download('fence-estimate-' . $calculation->id . '.pdf');
}


   public function edit($id)
{
    $calculation = Calculation::findOrFail($id);
    $data = $calculation->data;

    // Flash scalar data for old() fallback
    foreach ($data as $key => $value) {
        if (is_scalar($value)) {
            session()->flash('_old_input.' . $key, $value);
        }
    }

      // ✅ Load the related site visit and client
    $siteVisit = $calculation->siteVisit()->with('client')->first();

    $budgetService = app(BudgetService::class);
    $defaultLaborRate = $budgetService->getLaborRateForCalculators();

    return view('calculators.fence.form', [
        'siteVisitId' => $siteVisit?->id,
        'clientId' => $siteVisit?->client?->id,
        'existingCalculation' => $calculation,
        'formData' => $data,
        'editMode' => true,
        'mode' => $calculation->is_template ? 'template' : null,
        'estimateId' => $calculation->estimate_id,
        'defaultLaborRate' => $defaultLaborRate,
    ]);
}

   public function calculate(Request $request)
{
    $mode = $request->input('mode');
     $validated = $request->validate([
        'fence_type' => 'required|in:wood,vinyl',
        'length' => 'required|numeric|min:1',
        'height' => 'required|in:4,6',
        'gate_4ft' => 'nullable|integer|min:0',
        'gate_5ft' => 'nullable|integer|min:0',
        'picket_spacing' => 'nullable|numeric|min:0|max:1',
        'shadow_box' => 'nullable|boolean',
        'vinyl_corner_posts' => 'nullable|integer|min:0',
        'vinyl_end_posts' => 'nullable|integer|min:0',
        'site_visit_id' => ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id',
        'labor_rate' => 'nullable|numeric|min:0',
        'crew_size' => 'nullable|integer|min:1',
        'drive_distance' => 'nullable|numeric|min:0',
        'drive_speed' => 'nullable|numeric|min:1',
        'site_conditions' => 'nullable|numeric|min:0',
        'material_pickup' => 'nullable|numeric|min:0',
        'cleanup' => 'nullable|numeric|min:0',
        'dig_method' => 'required|in:hand,auger',
        'calculation_id' => 'nullable|exists:calculations,id',
        'job_notes' => 'nullable|string|max:2000',
        'custom_materials' => 'nullable|array',
        'custom_materials.*.name' => 'nullable|string|max:255',
        'custom_materials.*.qty' => 'nullable|numeric|min:0',
        'custom_materials.*.unit_cost' => 'nullable|numeric|min:0',
    ]);

    // 🔧 Basic setup
    $fenceType = $validated['fence_type'];
    $height = $validated['height'];
    $length = $validated['length'];
    $gate_4ft = $validated['gate_4ft'] ?? 0;
    $gate_5ft = $validated['gate_5ft'] ?? 0;
    $gate_total_length = ($gate_4ft * 4) + ($gate_5ft * 5);
    $adjusted_length = max(0, $length - $gate_total_length);
    $shadow_box = !empty($validated['shadow_box']);
    $materials = [];

    $defaults = [
        'wood' => [
            '4x4' => 12.00,
            '4x6' => 18.00,
            'rail' => 6.50,
            'picket' => 2.25,
            'screw_cost_per_picket' => 0.25,
            'hardware' => 12.00
        ],
        'vinyl' => [
            'panel_6' => 75,
            'line_6' => 48,
            'corner_6' => 45,
            'end_6' => 45,
            'panel_4' => 125,
            'line_4' => 28,
            'corner_4' => 25,
            'end_4' => 25,
            'gate_4' => 145,
            'gate_6' => 145,
            'metal_insert' => 75,
        ]
    ];

    // 🪵 Wood Fence Calculation - quantities only for reference
    if ($fenceType === 'wood') {
        $post_spacing = 8;
        $post_count = ceil($adjusted_length / $post_spacing);
        $gate_count = $gate_4ft + $gate_5ft;
        $gate_posts = $gate_count * 2;

        $picket_spacing_inch = $validated['picket_spacing'] ?? 0.25;
        $visible_width = 5.5 + $picket_spacing_inch;
        $pickets_per_foot = ceil(12 / $visible_width);
        $total_pickets = $pickets_per_foot * $length;
        if ($shadow_box) {
            $total_pickets *= 2;
        }

        $rails_per_section = 3;
        $rail_length = 10;
        $rail_sections = ceil($adjusted_length / $rail_length);
        $total_rails = $rails_per_section * $rail_sections;

        $concrete_bags = ($post_count + $gate_posts) * 2;
    }

    // 🧱 Vinyl Fence Calculation - quantities only for reference
    if ($fenceType === 'vinyl') {
        $panel_length = $height == 4 ? 8 : 6;
        $corner_posts = $validated['vinyl_corner_posts'] ?? 0;
        $end_posts = $validated['vinyl_end_posts'] ?? 0;
        $gate_count = $gate_4ft + $gate_5ft;
        $panel_count = ceil($adjusted_length / $panel_length);
        $line_posts = max(0, ($panel_count + 2) - $corner_posts - $end_posts);
        $post_total = $line_posts + $corner_posts + $end_posts + $gate_count;
        $concrete_bags = $post_total * 2;
    }
    
    // Materials only from custom_materials input
    $materials = [];
    $customMaterialsInput = $validated['custom_materials'] ?? [];
    $customMaterials = collect($customMaterialsInput)
        ->map(function ($item) {
            $name = trim($item['name'] ?? '');
            $qty = isset($item['qty']) ? (float) $item['qty'] : null;
            $unitCost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : null;

            if ($name === '' || $qty === null || $unitCost === null) {
                return null;
            }

            $total = $qty * $unitCost;

            return [
                'name' => $name,
                'qty' => round($qty, 2),
                'unit_cost' => round($unitCost, 2),
                'total' => round($total, 2),
            ];
        })
        ->filter()
        ->values()
        ->all();

    foreach ($customMaterials as $customMaterial) {
        $materials[$customMaterial['name']] = [
            'qty' => $customMaterial['qty'],
            'unit_cost' => $customMaterial['unit_cost'],
            'total' => $customMaterial['total'],
            'is_custom' => true,
        ];
    }

     $material_total = array_sum(array_column($materials, 'total'));
     // Normalize post counts for labor input
    $post_total = $fenceType === 'wood'
    ? ($post_count + $gate_posts)
    : ($line_posts + $corner_posts + $end_posts + $gate_4ft + $gate_5ft);

    $crew_size = $validated['crew_size'] ?? 2;


    // Labor estimate via custom service
    $laborService = new FenceLaborEstimatorService();
    $laborInput = [
    'fence_type' => $fenceType,
    'dig_method' => $validated['dig_method'],
    'total_posts' => $post_total,
    'adjusted_length' => $adjusted_length,
    'length' => $length,
    'gate_count' => $gate_4ft + $gate_5ft,
];

    $laborData = $laborService->estimate($laborInput);
    $base_hours = $laborData['base_hours'];
    $labor_breakdown = $laborData['breakdown'];

    // ✅ Use shared LaborCostCalculatorService
    $calculator = new \App\Services\LaborCostCalculatorService();
    $totals = $calculator->calculate(
        $base_hours,
        $validated['labor_rate'] ?? 45,
        array_merge($validated, ['material_total' => $material_total])
    );

    // Build enhanced labor_tasks array from labor_breakdown
    $laborTasks = [];
    foreach ($labor_breakdown as $taskKey => $hours) {
        $taskName = ucwords(str_replace('_', ' ', $taskKey));
        $laborTasks[] = [
            'task_key' => $taskKey,
            'task_name' => $taskName,
            'description' => $taskName . " - {$hours} hrs",
            'quantity' => $hours, // For fence, hours is the main quantity
            'unit' => 'hr',
            'production_rate' => null, // Not available in fence estimator
            'hours' => round($hours, 2),
            'hourly_rate' => $validated['labor_rate'] ?? 45,
            'total_cost' => round($hours * ($validated['labor_rate'] ?? 45), 2),
        ];
    }

    // Final data array
    $data = array_merge($validated, [
        'fence_type' => $fenceType,
        'height' => $height,
        'length' => $length,
        'adjusted_length' => $adjusted_length,
        'gate_4ft' => $gate_4ft,
        'gate_5ft' => $gate_5ft,
        'dig_method' => $validated['dig_method'],
        'crew_size' => $validated['crew_size'] ?? 2,
        'materials' => $materials,
        'material_total' => round($material_total, 2),
        'labor_by_task' => array_map(fn($h) => round($h, 2), $labor_breakdown),
        'labor_tasks' => $laborTasks, // Enhanced format for import
        'job_notes' => $request->input('job_notes'),
        'vinyl_corner_posts' => $validated['vinyl_corner_posts'] ?? 0,
        'vinyl_end_posts' => $validated['vinyl_end_posts'] ?? 0,
        'base_hours' => round($base_hours, 2),
        'custom_materials' => $customMaterials,
    ], $totals);

    // Save or update
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
                'calculation_type' => 'fence',
                'data' => $data,
                'is_template' => true,
                'template_name' => $request->input('template_name') ?: null,
                'template_scope' => $request->input('template_scope') ?: 'global',
            ]);
        } else {
            $calc = Calculation::create([
                'site_visit_id' => $validated['site_visit_id'],
                'calculation_type' => 'fence',
                'data' => $data,
            ]);
        }
    }

    if ($mode === 'template') {
        return redirect()->route('estimates.show', $request->input('estimate_id'))
            ->with('success', 'Fence template saved.');
    }

    return redirect()->route('calculators.fence.showResult', $calc->id);
}


public function showResult(Calculation $calculation)
{
    $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

    return view('calculators.fence.results', [
        'data' => $calculation->data,
        'calculation' => $calculation,
        'siteVisit' => $siteVisit,
        'labor_breakdown' => $calculation->data['labor_by_task'] ?? [],
    ]);
}
}
