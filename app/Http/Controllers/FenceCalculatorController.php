<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calculation;

class FenceCalculatorController extends Controller
{
    public function showForm(Request $request)
{
    $siteVisitId = $request->query('site_visit_id'); // or however you're passing it
    return view('calculators.fence.fence_form', compact('siteVisitId'));
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
        ]);

        $length = $validated['length'];
        $gate_4ft = $validated['gate_4ft'] ?? 0;
        $gate_5ft = $validated['gate_5ft'] ?? 0;
        $gate_total = ($gate_4ft * 4) + ($gate_5ft * 5);
        $adjusted_length = $length - $gate_total;

        $results = [
            'fence_type' => $validated['fence_type'],
            'height' => $validated['height'],
            'total_length' => $length,
            'gate_4ft' => $gate_4ft,
            'gate_5ft' => $gate_5ft,
            'adjusted_length' => $adjusted_length,
            'shadow_box' => !empty($validated['shadow_box']), // ✅ Always set, true/false
        ];

        if ($validated['fence_type'] === 'wood') {
            $post_spacing = 8;
            $post_count = ceil($adjusted_length / $post_spacing);
            $gate_posts = ($gate_4ft + $gate_5ft) * 2;

            $picket_spacing = $validated['picket_spacing'] ?? 0.25;
            $visible_width = 5.5 + $picket_spacing;
            $pickets_per_foot = ceil(12 / $visible_width);
            $total_pickets = $pickets_per_foot * $length;
            if (!empty($validated['shadow_box'])) {
                $total_pickets *= 2;
            }

            $concrete_bags = ($post_count * 2) + ($gate_posts * 3);

            $results += [
                'post_count' => $post_count,
                'gate_posts' => $gate_posts,
                'total_posts' => $post_count + $gate_posts,
                'pickets_per_foot' => $pickets_per_foot,
                'total_pickets' => $total_pickets,
                'concrete_bags' => $concrete_bags,
            ];
        } else {
            $post_spacing = 6;
            $corner_posts = $validated['vinyl_corner_posts'] ?? 0;
            $end_posts = $validated['vinyl_end_posts'] ?? 0;
            $panel_count = ceil($adjusted_length / $post_spacing);
            $line_posts = $panel_count - ($corner_posts + $end_posts);
            $gate_posts = ($gate_4ft + $gate_5ft) * 2;
            $total_posts = $line_posts + $corner_posts + $end_posts + $gate_posts;
            $concrete_bags = ($total_posts * 2);

            $results += [
                'panel_count' => $panel_count,
                'line_posts' => $line_posts,
                'corner_posts' => $corner_posts,
                'end_posts' => $end_posts,
                'gate_posts' => $gate_posts,
                'total_posts' => $total_posts,
                'concrete_bags' => $concrete_bags,
            ];
        }

        $data = $results;

        $calc = Calculation::create([
            'site_visit_id' => $validated['site_visit_id'],
            'calculation_type' => 'fence',
            'data' => $results, // ✅ Correct variable
        ]);

        return view('calculators.fence.fence_results', [
            'data' => $results,
            'calculation' => $calc,
        ]);
    }
}
