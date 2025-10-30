<?php

namespace App\Http\Controllers;
    
use Illuminate\Http\Request;
use App\Models\Calculation;
use App\Models\SiteVisit;
use App\Services\FenceLaborEstimatorService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;


class FenceCalculatorController extends Controller
{
public function showForm(Request $request)
{
    $siteVisitId = $request->query('site_visit_id'); // ✅ You need this
    $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId); // ✅ This line is missing

    return view('calculators.fence.fence_form', [
        'siteVisitId' => $siteVisit->id,
        'clientId' => $siteVisit->client->id,
        'editMode' => false,
        'formData' => [],
    ]);
}




public function downloadPdf($id)
{
    $calculation = Calculation::findOrFail($id);
    $data = $calculation->data;
    $siteVisit = $calculation->siteVisit;

    $labor_breakdown = $data['labor_breakdown'] ?? [];

    $pdf = PDF::loadView('calculators.fence.fence_pdf', [
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

    return view('calculators.fence.fence_form', [
        'siteVisitId' => $calculation->site_visit_id,
        'existingCalculation' => $calculation,
        'formData' => $data,
        'editMode' => true,
    ]);
}



    public function calculate(Request $request)
    {
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
            'site_visit_id' => 'required|exists:site_visits,id',

            'labor_rate' => 'nullable|numeric|min:0',
            'crew_size' => 'nullable|integer|min:1',
            'drive_distance' => 'nullable|numeric|min:0',
            'drive_speed' => 'nullable|numeric|min:1',
            'site_conditions' => 'nullable|numeric|min:0',
            'material_pickup' => 'nullable|numeric|min:0',
            'cleanup' => 'nullable|numeric|min:0',
            'markup' => 'nullable|numeric|min:0',
            'dig_method' => 'required|in:hand,auger',

        ]);

        // ------------------------------
        // 🔧 Basic setup
        // ------------------------------
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
                '4x4' => 18.00,
                '4x6' => 12.00,
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

        // ------------------------------
        // 🪵 Wood Fence Calculation
        // ------------------------------
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

            // ✅ Safe material overrides with defaults
            $wood_4x4_cost = filled($request->input('override_wood_post_4x4_cost'))
                ? floatval($request->input('override_wood_post_4x4_cost'))
                : $defaults['wood']['4x4'];

            $wood_4x6_cost = filled($request->input('override_wood_post_4x6_cost'))
                ? floatval($request->input('override_wood_post_4x6_cost'))
                : $defaults['wood']['4x6'];

            $rail_cost = filled($request->input('override_wood_rail_cost'))
                ? floatval($request->input('override_wood_rail_cost'))
                : $defaults['wood']['rail'];

            $picket_cost = filled($request->input('override_wood_picket_cost'))
                ? floatval($request->input('override_wood_picket_cost'))
                : $defaults['wood']['picket'];

            $screw_cost = filled($request->input('override_screws_cost'))
                ? floatval($request->input('override_screws_cost'))
                : $defaults['wood']['screw_cost_per_picket'];

            $hardware_cost = filled($request->input('override_wood_gate_hardware_cost'))
                ? floatval($request->input('override_wood_gate_hardware_cost'))
                : $defaults['wood']['hardware'];

            $materials = [
                '4x4 Posts'      => ['qty' => $post_count, 'unit_cost' => $wood_4x4_cost, 'total' => $post_count * $wood_4x4_cost],
                '4x6 Gate Posts' => ['qty' => $gate_posts, 'unit_cost' => $wood_4x6_cost, 'total' => $gate_posts * $wood_4x6_cost],
                '2x4 Rails'      => ['qty' => $total_rails, 'unit_cost' => $rail_cost, 'total' => $total_rails * $rail_cost],
                'Pickets'        => ['qty' => $total_pickets, 'unit_cost' => $picket_cost, 'total' => $total_pickets * $picket_cost],
                'Screws'         => ['qty' => $total_pickets, 'unit_cost' => $screw_cost, 'total' => $total_pickets * $screw_cost],
                'Gate Hardware'  => ['qty' => $gate_count, 'unit_cost' => $hardware_cost, 'total' => $gate_count * $hardware_cost],
                'Concrete Bags'  => ['qty' => $concrete_bags, 'unit_cost' => 8.50, 'total' => $concrete_bags * 8.50],
            ];
        }

        // ------------------------------
        // 🧱 Vinyl Fence Calculation
        // ------------------------------
        if ($fenceType === 'vinyl') {
            $panel_length = $height == 4 ? 8 : 6;
            $corner_posts = $validated['vinyl_corner_posts'] ?? 0;
            $end_posts = $validated['vinyl_end_posts'] ?? 0;
            $gate_count = $gate_4ft + $gate_5ft;
            $panel_count = ceil($adjusted_length / $panel_length);

            // ✅ Correct line post calculation
           $line_posts = max(0, ($panel_count + 2) - $corner_posts - $end_posts);
            $post_total = $line_posts + $corner_posts + $end_posts + ($gate_count * 1);
            $concrete_bags = $post_total * 2;

            $height_suffix = $height == 6 ? '_6' : '_4';

            // ✅ Safe override with filled() checks
            $panel_cost = filled($request->input("override_vinyl_panel{$height_suffix}_cost"))
                ? floatval($request->input("override_vinyl_panel{$height_suffix}_cost"))
                : $defaults['vinyl']["panel$height_suffix"];

            $line_post_cost = filled($request->input("override_vinyl_line_post{$height_suffix}_cost"))
                ? floatval($request->input("override_vinyl_line_post{$height_suffix}_cost"))
                : $defaults['vinyl']["line$height_suffix"];

            $end_post_cost = filled($request->input("override_vinyl_end_post{$height_suffix}_cost"))
                ? floatval($request->input("override_vinyl_end_post{$height_suffix}_cost"))
                : $defaults['vinyl']["end$height_suffix"];

            $corner_post_cost = filled($request->input("override_vinyl_corner_post{$height_suffix}_cost"))
                ? floatval($request->input("override_vinyl_corner_post{$height_suffix}_cost"))
                : $defaults['vinyl']["corner$height_suffix"];

            $gate_cost = filled($request->input("override_vinyl_gate{$height_suffix}_cost"))
                ? floatval($request->input("override_vinyl_gate{$height_suffix}_cost"))
                : $defaults['vinyl']["gate$height_suffix"];

            $insert_cost = filled($request->input('override_metal_insert_cost'))
                ? floatval($request->input('override_metal_insert_cost'))
                : $defaults['vinyl']['metal_insert'];

            $materials = [
                "Vinyl Panels ({$height}')" => ['qty' => $panel_count, 'unit_cost' => $panel_cost, 'total' => $panel_count * $panel_cost],
                "Line Posts ({$height}')"   => ['qty' => $line_posts, 'unit_cost' => $line_post_cost, 'total' => $line_posts * $line_post_cost],
                "End Posts ({$height}')"    => ['qty' => $end_posts, 'unit_cost' => $end_post_cost, 'total' => $end_posts * $end_post_cost],
                "Corner Posts ({$height}')" => ['qty' => $corner_posts, 'unit_cost' => $corner_post_cost, 'total' => $corner_posts * $corner_post_cost],
                "Gates ({$height}')"        => ['qty' => $gate_count, 'unit_cost' => $gate_cost, 'total' => $gate_count * $gate_cost],
               "Metal Inserts" => ['qty' => $gate_count, 'unit_cost' => $insert_cost, 'total' => $gate_count * $insert_cost],
                "Concrete Bags"             => ['qty' => $concrete_bags, 'unit_cost' => 8.50, 'total' => $concrete_bags * 8.50],
            ];
        }

        // ------------------------------
        // 💰 Totals & Labor
        // ------------------------------
        $material_total = array_sum(array_column($materials, 'total'));

        $laborInput = [
    'fence_type' => $fenceType,
    'dig_method' => $validated['dig_method'],
    'total_posts' => $fenceType === 'wood' ? ($post_count + $gate_posts) : $post_total,
    'post_total' => $post_total ?? 0,
    'adjusted_length' => $adjusted_length,
    'length' => $length,
    'gate_count' => $gate_count,
];


        $labor_rate = $validated['labor_rate'] ?? 45;
        $crew_size = $validated['crew_size'] ?? 2;
        $drive_distance = $validated['drive_distance'] ?? 10;
        $drive_speed = $validated['drive_speed'] ?? 30;

        $travel_time = ($drive_distance / $drive_speed) * 2;
        $base_hours = ceil($length / 30);

        $site_conditions = ($validated['site_conditions'] ?? 0) / 100;
        $material_pickup = ($validated['material_pickup'] ?? 0) / 100;
        $cleanup = ($validated['cleanup'] ?? 0) / 100;

        $overhead_hours = $base_hours * ($site_conditions + $material_pickup + $cleanup);
        $total_hours = $base_hours + $overhead_hours + $travel_time;
        $labor_cost = $total_hours * $labor_rate;

       // ------------------------------
// 👷 Labor Estimation via Service
// ------------------------------
$laborService = new FenceLaborEstimatorService();
$laborInput = [
    'fence_type' => $fenceType,
    'dig_method' => $validated['dig_method'],
    'total_posts' => $fenceType === 'wood' ? $post_count + $gate_posts : $post_total,
    'post_total' => $post_total ?? 0,
    'adjusted_length' => $adjusted_length,
    'length' => $length,
    'gate_count' => $gate_4ft + $gate_5ft,
];

$laborData = $laborService->estimate($laborInput);
$base_hours = $laborData['base_hours'];
$labor_breakdown = $laborData['breakdown'];

// Travel + overhead
$drive_distance = $validated['drive_distance'] ?? 10;
$drive_speed = $validated['drive_speed'] ?? 30;
$travel_time = ($drive_distance / $drive_speed) * 2;

$site_conditions = ($validated['site_conditions'] ?? 0) / 100;
$material_pickup = ($validated['material_pickup'] ?? 0) / 100;
$cleanup = ($validated['cleanup'] ?? 0) / 100;

$overhead_hours = $base_hours * ($site_conditions + $material_pickup + $cleanup);
$total_hours = $base_hours + $overhead_hours + $travel_time;

$labor_rate = $validated['labor_rate'] ?? 45;
$labor_cost = $total_hours * $labor_rate;




        // ------------------------------
        // 💰 Markup & Final Price
        // ------------------------------
        $markup = $validated['markup'] ?? 20;
        $margin_decimal = $markup / 100;
        $pre_markup = $labor_cost + $material_total;
        $final_price = $margin_decimal >= 1 ? $pre_markup : $pre_markup / (1 - $margin_decimal);
        $markup_amount = $final_price - $pre_markup;


        $siteVisit = SiteVisit::find($validated['site_visit_id']);

       $data = [
    'fence_type' => $fenceType,
    'height' => $height,
    'length' => $length,
    'adjusted_length' => $adjusted_length,
    'gate_4ft' => $gate_4ft,
    'gate_5ft' => $gate_5ft,
    'dig_method' => $validated['dig_method'],
    'labor_rate' => $validated['labor_rate'] ?? 45,
    'crew_size' => $validated['crew_size'] ?? 2,
    'drive_distance' => $validated['drive_distance'] ?? 10,
    'drive_speed' => $validated['drive_speed'] ?? 30,
    'site_conditions' => $validated['site_conditions'] ?? 0,
    'material_pickup' => $validated['material_pickup'] ?? 0,
    'cleanup' => $validated['cleanup'] ?? 0,
    'markup' => $markup,
    'markup_amount' => $markup_amount,
    'final_price' => $final_price,
    'material_total' => $material_total,
    'labor_hours' => $base_hours,
    'overhead_hours' => $overhead_hours + $travel_time,
    'total_hours' => $total_hours,
    'labor_cost' => $labor_cost,
    'materials' => $materials,
    'job_notes' => $request->input('job_notes'),
    'labor_by_task' => $labor_breakdown, // ✅ Add this
];


      //  $calc = Calculation::create([
      //      'site_visit_id' => $validated['site_visit_id'],
       //     'calculation_type' => 'fence',
        //    'data' => $data,
      //  ]);

     //   return view('calculators.fence.fence_results', [
   // 'data' => $data,
   // 'calculation' => $calc,
   // 'siteVisit' => $siteVisit,
   // 'labor_breakdown' => $labor_breakdown, // ✅ add this

        return view('calculators.fence.fence_results', [
        'data' => $data,
        'siteVisit' => $siteVisit,
        ]);

    }

}

