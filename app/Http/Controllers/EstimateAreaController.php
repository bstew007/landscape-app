<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\EstimateArea;
use App\Models\CostCode;
use App\Services\PricingOverrideService;
use Illuminate\Http\Request;

class EstimateAreaController extends Controller
{
    public function store(Request $request, Estimate $estimate)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'identifier' => ['nullable', 'string', 'max:255'],
            'cost_code_id' => ['nullable', 'exists:cost_codes,id'],
            'description' => ['nullable', 'string'],
        ]);
        $max = (int) ($estimate->areas()->max('sort_order') ?? 0);
        $area = $estimate->areas()->create([
            'name' => $data['name'],
            'identifier' => $data['identifier'] ?? null,
            'cost_code_id' => $data['cost_code_id'] ?? null,
            'description' => $data['description'] ?? null,
            'sort_order' => $max + 1,
        ]);

        if ($request->wantsJson()) {
            $costCodes = CostCode::orderBy('code')->get();
            $areaHtml = view('estimates.partials.area', [
                'estimate' => $estimate,
                'area' => $area,
                'allItems' => $estimate->items,
                'costCodes' => $costCodes,
                'initiallyOpen' => true,
            ])->render();
            return response()->json([
                'area' => $area,
                'area_html' => $areaHtml,
                'recent_area_id' => $area->id,
            ]);
        }
        return back()
            ->with('success', 'Work area added.')
            ->with('recent_area_id', $area->id);
    }

    public function update(Request $request, Estimate $estimate, EstimateArea $area)
    {
        if ($area->estimate_id !== $estimate->id) {
            abort(404);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'identifier' => ['nullable', 'string', 'max:255'],
            'cost_code_id' => ['nullable', 'exists:cost_codes,id'],
            'description' => ['nullable', 'string'],
        ]);
        $area->update($data);
        return response()->json(['area' => $area]);
    }

    public function destroy(Estimate $estimate, EstimateArea $area)
    {
        if ($area->estimate_id !== $estimate->id) {
            abort(404);
        }
        $area->delete();
        
        // Recalculate estimate totals after deleting area and its items
        app(\App\Services\EstimateItemService::class)->recalculateTotals($estimate->fresh());
        
        return back()->with('success', 'Work area removed.');
    }

    public function reorder(Request $request, Estimate $estimate)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);
        $ids = $validated['order'];
        $areas = $estimate->areas()->whereIn('id', $ids)->get(['id']);
        $validIds = $areas->pluck('id')->all();
        $ordered = array_values(array_filter($ids, fn ($id) => in_array($id, $validIds, true)));
        foreach ($ordered as $index => $id) {
            EstimateArea::where('id', $id)->update(['sort_order' => $index + 1]);
        }
        return response()->json(['status' => 'ok']);
    }

    public function customPrice(Request $request, Estimate $estimate, EstimateArea $area)
    {
        if ($area->estimate_id !== $estimate->id) {
            abort(404);
        }

        $validated = $request->validate([
            'value' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'in:proportional,line_item'],
        ]);

        $service = app(PricingOverrideService::class);
        $result = $service->applyCustomPrice(
            $area,
            $validated['value'],
            $validated['method'],
            auth()->id()
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'details' => $result['details'],
        ]);
    }

    public function customProfit(Request $request, Estimate $estimate, EstimateArea $area)
    {
        if ($area->estimate_id !== $estimate->id) {
            abort(404);
        }

        $validated = $request->validate([
            'value' => ['required', 'numeric', 'min:0', 'max:99'],
            'method' => ['required', 'in:proportional,line_item'],
        ]);

        $service = app(PricingOverrideService::class);
        $result = $service->applyCustomProfit(
            $area,
            $validated['value'],
            $validated['method'],
            auth()->id()
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'details' => $result['details'],
        ]);
    }

    public function clearCustomPricing(Request $request, Estimate $estimate, EstimateArea $area)
    {
        if ($area->estimate_id !== $estimate->id) {
            abort(404);
        }

        $service = app(PricingOverrideService::class);
        $result = $service->clearCustomPricing($area, true);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }
}
