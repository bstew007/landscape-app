<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\EstimateArea;
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
            return response()->json(['area' => $area]);
        }
        return back()->with('success', 'Work area added.');
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
}
