<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Services\CalculationImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CalculatorTemplateController extends Controller
{
    // GET /calculator/templates (Gallery)
    public function index(Request $request)
    {
        $query = Calculation::query()
            ->where('is_template', true)
            ->when($request->filled('type'), fn($q) => $q->where('calculation_type', $request->string('type')))
            ->when($request->filled('q'), fn($q) => $q->where('template_name', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('scope'), function($q) use ($request) {
                $scope = $request->string('scope');
                if ($scope === 'global') {
                    $q->where(function($qq){ $qq->where('template_scope','global')->orWhere('is_global', true); });
                } elseif ($scope === 'client') {
                    $q->where('template_scope','client');
                } elseif ($scope === 'property') {
                    $q->where('template_scope','property');
                } elseif ($scope === 'mine') {
                    $q->where('created_by', Auth::id());
                }
            })
            ->when($request->filled('from'), fn($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->latest();

        $templates = $query->paginate(24)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($templates);
        }

        return view('calculators.templates.index', [
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, Calculation $calculation)
    {
        abort_unless($calculation->is_template, 404);
        $this->authorize('update', $calculation);
        $data = $request->validate([
            'template_name' => ['required','string','max:255'],
            'is_global' => ['sometimes','boolean'],
        ]);
        $calculation->fill($data);
        $calculation->save();
        return back()->with('status', 'Template updated.');
    }

    public function destroy(Calculation $calculation)
    {
        abort_unless($calculation->is_template, 404);
        $this->authorize('delete', $calculation);
        $calculation->delete();
        return back()->with('status', 'Template deleted.');
    }

    public function duplicate(Calculation $calculation)
    {
        abort_unless($calculation->is_template, 404);
        $this->authorize('duplicate', $calculation);
        $copy = $calculation->replicate(['created_at','updated_at']);
        $copy->template_name = trim(($calculation->template_name ?: 'Template').' Copy');
        $copy->created_by = Auth::id();
        $copy->save();
        return back()->with('status', 'Template duplicated.');
    }

    public function import(Request $request, Calculation $calculation)
    {
        abort_unless($calculation->is_template, 404);
        $this->authorize('view', $calculation);
        $data = $request->validate([
            'estimate_id' => ['required','exists:estimates,id'],
            'replace' => ['sometimes','boolean'],
            'area_id' => ['nullable','integer'],
        ]);

        $estimate = Estimate::findOrFail($data['estimate_id']);
        // Create a calc record attached to estimate from this template
        $calc = Calculation::create([
            'calculation_type' => $calculation->calculation_type,
            'data' => $calculation->data,
            'estimate_id' => $estimate->id,
        ]);

        $importer = app(CalculationImportService::class);
        $importer->importCalculation($estimate, $calc, (bool)($data['replace'] ?? false), $request->input('area_id'));

        return redirect()->route('estimates.show', $estimate)->with('status', 'Template imported into estimate.');
    }

    // AJAX: GET /calculator/templates/estimates/search?q=
    public function estimateSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $idFromQuery = null;
        if ($q !== '' && preg_match('/\d+/', $q, $m)) {
            $idFromQuery = (int) $m[0];
        }
        $results = Estimate::query()
            ->with(['client','property'])
            ->when($q !== '', function($qq) use ($q, $idFromQuery) {
                $qq->where(function($w) use ($q, $idFromQuery) {
                    $w->where('title', 'like', '%'.$q.'%');
                    if ($idFromQuery) {
                        $w->orWhere('id', $idFromQuery);
                    }
                });
            })
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(function($e){
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'client' => $e->client?->name,
                    'property' => $e->property?->name,
                ];
            });
        return response()->json(['results' => $results]);
    }

    // AJAX: GET /calculator/templates/estimates/{estimate}/areas
    public function estimateAreas(Estimate $estimate)
    {
        $areas = $estimate->areas()->orderBy('sort_order')->get(['id','name']);
        return response()->json(['areas' => $areas]);
    }
}
