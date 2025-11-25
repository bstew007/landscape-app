<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Models\Estimate;
use App\Services\LaborCostCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;

class WeedingCalculatorController extends Controller
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

        return view('calculators.weeding.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit?->client?->id,
            'editMode' => false,
            'formData' => [],
            'mode' => $mode,
            'estimateId' => $estimateId,
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $siteVisit = $calculation->site_visit_id 
            ? SiteVisit::with('client')->find($calculation->site_visit_id)
            : null;

        return view('calculators.weeding.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit?->client?->id,
            'mode' => $calculation->is_template ? 'template' : null,
            'estimateId' => $calculation->estimate_id,
        ]);
    }

  public function calculate(Request $request)
{
    $mode = $request->input('mode');
    $rules = [
       'labor_rate' => 'required|numeric|min:1',
    'crew_size' => 'required|integer|min:1',
    'drive_distance' => 'required|numeric|min:0',
    'drive_speed' => 'required|numeric|min:1',
    'site_conditions' => 'nullable|numeric|min:0',
    'material_pickup' => 'nullable|numeric|min:0',
    'cleanup' => 'nullable|numeric|min:0',
    'calculation_id' => 'nullable|exists:calculations,id',
    'job_notes' => 'nullable|string|max:2000',
    'tasks' => 'required|array',
    'tasks.*.qty' => 'nullable|numeric|min:0',
    ];
    $rules['site_visit_id'] = ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id';
    $validated = $request->validate($rules);

    $inputTasks = $request->input('tasks', []);
    $laborRate = (float) $validated['labor_rate'];

    // 🔍 Load production rates from DB
    $dbRates = ProductionRate::where('calculator', 'weeding')->pluck('rate', 'task');

    $results = [];
    $totalHours = 0;

    foreach ($inputTasks as $taskKey => $taskData) {
        $qty = (float) ($taskData['qty'] ?? 0);
        if ($qty <= 0 || !isset($dbRates[$taskKey])) continue;

        $rate = $dbRates[$taskKey];
        $hours = $qty * $rate;
        $cost = $hours * $laborRate;

        $results[] = [
            'task' => str_replace('_', ' ', $taskKey),
            'qty' => $qty,
            'rate' => $rate,
            'hours' => round($hours, 2),
            'cost' => round($cost, 2),
        ];

        $totalHours += $hours;
    }

    // ✅ Now call your overhead/margin service
    $calculator = new LaborCostCalculatorService();
    $totals = $calculator->calculate($totalHours, $laborRate, $request->all());

    // ✅ Prepare data to save
    $data = array_merge($validated, [
        'tasks' => $results,
        'labor_by_task' => collect($results)->pluck('hours', 'task')->map(fn($h) => round($h, 2))->toArray(),
        'labor_hours' => round($totalHours, 2),
        'materials' => [],
        'material_total' => 0,
    ], $totals);

    // 💾 Save or update
    if (!empty($validated['calculation_id'])) {
        $calc = Calculation::find($validated['calculation_id']);
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
                'calculation_type' => 'weeding',
                'data' => $data,
                'is_template' => true,
                'template_name' => $request->input('template_name') ?: null,
                'template_scope' => $request->input('template_scope') ?: 'global',
            ]);
        } else {
            $calc = Calculation::create([
                'site_visit_id' => $validated['site_visit_id'],
                'calculation_type' => 'weeding',
                'data' => $data,
            ]);
        }
    }

    if ($mode === 'template') {
        return redirect()->route('estimates.show', $request->input('estimate_id'))
            ->with('success', 'Weeding template saved.');
    }

    return redirect()->route('calculators.weeding.showResult', $calc->id);
}


    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.weeding.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.weeding.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('weeding_estimate.pdf');
    }
}
