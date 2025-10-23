<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionRate;

class ProductionRateController extends Controller
{
    // Show all production rates
    public function index(Request $request)
{
    $query = \App\Models\ProductionRate::query();

    if ($request->filled('calculator')) {
        $query->where('calculator', $request->calculator);
    }

    if ($request->filled('task')) {
        $query->where('task', 'like', '%' . $request->task . '%');
    }

    $productionRates = $query->orderBy('task')->get();

    // For dropdown filter
    $calculators = \App\Models\ProductionRate::select('calculator')
                    ->distinct()
                    ->pluck('calculator')
                    ->filter();

    return view('production-rates.index', compact('productionRates', 'calculators'));
}


    // Store new production rate
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'rate' => 'required|numeric|min:0',
            'calculator' => 'nullable|string|max:255',
        ]);

        ProductionRate::create($validated);

        return redirect()->route('production-rates.index')->with('success', 'Production rate added.');
    }

    // Update existing production rate
    public function update(Request $request, ProductionRate $productionRate)
    {
        $validated = $request->validate([
            'task' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'rate' => 'required|numeric|min:0',
            'calculator' => 'nullable|string|max:255',
        ]);

        $productionRate->update($validated);

        return redirect()->route('production-rates.index')->with('success', 'Production rate updated.');
    }

    // (Optional) Delete a production rate
    public function destroy(ProductionRate $productionRate)
    {
        $productionRate->delete();
        return redirect()->route('production-rates.index')->with('success', 'Production rate deleted.');
    }
}
