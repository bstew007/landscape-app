<?php

namespace App\Http\Controllers;

use App\Mail\SynTurfEstimateMail;
use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Services\LaborCostCalculatorService;
use App\Services\SynTurfMaterialService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SynTurfCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);

        return view('calculators.syn-turf.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit->client->id,
            'editMode' => false,
            'formData' => [],
        ]);
    }

    public function edit(Calculation $calculation)
    {
        return view('calculators.syn-turf.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'siteVisit' => $calculation->siteVisit()->with('client')->first(),
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'labor_rate' => 'required|numeric|min:1',
            'crew_size' => 'required|integer|min:1',
            'drive_distance' => 'required|numeric|min:0',
            'drive_speed' => 'required|numeric|min:1',
            'site_conditions' => 'nullable|numeric|min:0',
            'material_pickup' => 'nullable|numeric|min:0',
            'cleanup' => 'nullable|numeric|min:0',
            'site_visit_id' => 'required|exists:site_visits,id',
            'calculation_id' => 'nullable|exists:calculations,id',
            'job_notes' => 'nullable|string|max:2000',
            'tasks' => 'required|array',
            'tasks.*.qty' => 'nullable|numeric|min:0',
            'area_sqft' => 'required|numeric|min:1',
            'edging_linear_ft' => 'required|numeric|min:0',
            'turf_grade' => 'required|string|in:good,better,best',
            'turf_custom_name' => 'nullable|string|max:255',
            'override_turf_price' => 'nullable|numeric|min:0',
            'override_infill_price' => 'nullable|numeric|min:0',
            'override_edging_price' => 'nullable|numeric|min:0',
            'override_weed_barrier_price' => 'nullable|numeric|min:0',
            'materials_override_enabled' => 'nullable|boolean',
        ]);

        $inputTasks = $request->input('tasks', []);
        $laborRate = (float) $validated['labor_rate'];

        $dbRates = ProductionRate::where('calculator', 'syn_turf')->pluck('rate', 'task');

        $results = [];
        $totalHours = 0;

        foreach ($inputTasks as $taskKey => $taskData) {
            $qty = (float) ($taskData['qty'] ?? 0);
            if ($qty <= 0 || !isset($dbRates[$taskKey])) {
                continue;
            }

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

        $areaSqft = (float) $validated['area_sqft'];
        $edgingLf = (float) $validated['edging_linear_ft'];

        $materialService = app(SynTurfMaterialService::class);
        $materialData = $materialService->buildMaterials($areaSqft, $edgingLf, $validated['turf_grade'], [
            'turf_price' => $validated['override_turf_price'],
            'turf_name' => $validated['turf_custom_name'],
            'infill_price' => $validated['override_infill_price'],
            'edging_price' => $validated['override_edging_price'],
            'weed_barrier_price' => $validated['override_weed_barrier_price'],
        ]);

        $materials = $materialData['materials'];
        $materialTotal = $materialData['material_total'];
        $turfName = $materialData['turf_name'];
        $turfUnitCost = $materialData['turf_unit_cost'];
        $overridesEnabled = $materialData['overrides_enabled'];

        $calculator = new LaborCostCalculatorService();
        $totals = $calculator->calculate(
            $totalHours,
            $laborRate,
            array_merge($request->all(), ['material_total' => $materialTotal])
        );

        $data = array_merge(
            $validated,
            [
                'tasks' => $results,
                'labor_by_task' => collect($results)
                    ->pluck('hours', 'task')
                    ->map(fn ($h) => round($h, 2))
                    ->toArray(),
                'labor_hours' => round($totalHours, 2),
                'materials' => $materials,
                'material_total' => round($materialTotal, 2),
                'area_sqft' => round($areaSqft, 2),
                'edging_linear_ft' => round($edgingLf, 2),
                'turf_grade' => $validated['turf_grade'],
                'turf_unit_cost' => $turfUnitCost,
                'turf_name' => $turfName,
                'override_turf_price' => $validated['override_turf_price'],
                'override_infill_price' => $validated['override_infill_price'],
                'override_edging_price' => $validated['override_edging_price'],
                'override_weed_barrier_price' => $validated['override_weed_barrier_price'],
                'turf_custom_name' => $validated['turf_custom_name'],
                'materials_override_enabled' => $overridesEnabled,
            ],
            $totals
        );

        if (!empty($validated['calculation_id'])) {
            $calc = Calculation::findOrFail($validated['calculation_id']);
            $calc->update(['data' => $data]);
        } else {
            $calc = Calculation::create([
                'site_visit_id' => $validated['site_visit_id'],
                'calculation_type' => 'syn_turf',
                'data' => $data,
            ]);
        }

        return redirect()->route('calculators.syn_turf.showResult', $calc->id);
    }

    public function showResult(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->firstOrFail();

        return view('calculators.syn-turf.result', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);
    }

    public function downloadPdf(Calculation $calculation)
    {
        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdf = Pdf::loadView('calculators.syn-turf.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ]);

        return $pdf->download('synthetic_turf_estimate.pdf');
    }

    public function emailEstimate(Request $request, Calculation $calculation)
    {
        $validated = $request->validate([
            'recipient' => 'required|email',
            'cc' => 'nullable|email',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        $siteVisit = SiteVisit::with('client')->findOrFail($calculation->site_visit_id);

        $pdfBinary = Pdf::loadView('calculators.syn-turf.pdf', [
            'data' => $calculation->data,
            'siteVisit' => $siteVisit,
            'calculation' => $calculation,
        ])->output();

        $mailable = new SynTurfEstimateMail(
            $siteVisit,
            $calculation,
            $validated['subject'],
            $validated['message'] ?? '',
            base64_encode($pdfBinary)
        );

        $mail = Mail::to($validated['recipient']);

        if (!empty($validated['cc'])) {
            $mail->cc($validated['cc']);
        }

        $mail->send($mailable);

        return back()->with('status', 'Estimate emailed successfully.');
    }
}
