<?php

namespace App\Http\Controllers;

use App\Mail\SynTurfEstimateMail;
use App\Models\Calculation;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Models\Estimate;
use App\Services\LaborCostCalculatorService;
use App\Services\SynTurfMaterialService;
use App\Services\BudgetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SynTurfCalculatorController extends Controller
{
    public function showForm(Request $request)
    {
        $mode = $request->query('mode');
        $estimateId = $request->query('estimate_id');
        $siteVisitId = $request->query('site_visit_id');
        $siteVisit = null;

        if ($mode === 'template') {
            // Template mode does not require a site visit
            $siteVisitId = $siteVisitId ?: null;
        } else {
            $siteVisit = SiteVisit::with('client')->findOrFail($siteVisitId);
        }

        $budgetService = app(BudgetService::class);
        $defaultLaborRate = $budgetService->getLaborRateForCalculators();

        return view('calculators.syn-turf.form', [
            'siteVisitId' => $siteVisitId,
            'siteVisit' => $siteVisit,
            'clientId' => $siteVisit?->client?->id,
            'editMode' => false,
            'formData' => [],
            'mode' => $mode,
            'estimateId' => $estimateId,
            'defaultLaborRate' => $defaultLaborRate,
        ]);
    }

    public function edit(Calculation $calculation)
    {
        $siteVisit = $calculation->siteVisit()->with('client')->first();
        // Treat calculations without a site visit (e.g., created via Estimate import) as template-mode edits
        $isTemplateMode = $calculation->is_template || (empty($calculation->site_visit_id) && !empty($calculation->estimate_id));

        $budgetService = app(BudgetService::class);
        $defaultLaborRate = $budgetService->getLaborRateForCalculators();

        return view('calculators.syn-turf.form', [
            'editMode' => true,
            'formData' => $calculation->data,
            'calculation' => $calculation,
            'siteVisitId' => $calculation->site_visit_id,
            'siteVisit' => $siteVisit,
            'mode' => $isTemplateMode ? 'template' : null,
            'estimateId' => $calculation->estimate_id,
            'defaultLaborRate' => $defaultLaborRate,
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
            'area_sqft' => 'required|numeric|min:1',
            'edging_linear_ft' => 'required|numeric|min:0',
            'turf_grade' => 'required|string|in:good,better,best',
            'turf_custom_name' => 'nullable|string|max:255',
            'override_turf_price' => 'nullable|numeric|min:0',
            'override_infill_price' => 'nullable|numeric|min:0',
            'override_edging_price' => 'nullable|numeric|min:0',
            'override_weed_barrier_price' => 'nullable|numeric|min:0',
            'materials_override_enabled' => 'nullable|boolean',
            // New optional fields
            'excavation_depth_in' => 'nullable|numeric|min:0',
            'abc_depth_in' => 'nullable|numeric|min:0',
            'rock_dust_depth_in' => 'nullable|numeric|min:0',
            'rent_tamper' => 'nullable|boolean',
            'tamper_days' => 'nullable|integer|min:1',
        ];
        // site_visit_id required unless template mode
        $rules['site_visit_id'] = ($mode === 'template') ? 'nullable' : 'required|exists:site_visits,id';
        $validated = $request->validate($rules);

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

        // Depths (inches) for excavation and base layers
        $excavationDepthIn = (float) ($request->input('excavation_depth_in') ?: 3);
        $baseDepthIn = (float) ($request->input('base_depth_in') ?: 3);

        // Convert to cubic yards for excavation and base
        $areaCubicFeetExc = $areaSqft * ($excavationDepthIn / 12);
        $excavationCY = $areaCubicFeetExc / 27;

        $materialService = app(SynTurfMaterialService::class);
        $materialData = $materialService->buildMaterials($areaSqft, $edgingLf, $validated['turf_grade'], [
            'turf_price' => $request->input('override_turf_price'),
            'turf_name' => $request->input('turf_custom_name'),
            'infill_price' => $request->input('override_infill_price'),
            'edging_price' => $request->input('override_edging_price'),
            'weed_barrier_price' => $request->input('override_weed_barrier_price'),
            // Pass per-layer base depths
            'abc_depth_in' => $request->input('abc_depth_in'),
            'rock_dust_depth_in' => $request->input('rock_dust_depth_in'),
        ]);

        $materials = $materialData['materials'];
        $materialTotal = $materialData['material_total'];
        $turfName = $materialData['turf_name'];
        $turfUnitCost = $materialData['turf_unit_cost'];
        $overridesEnabled = $materialData['overrides_enabled'];
        $abcCY = (float) ($materialData['abc_cy'] ?? 0);
        $rockDustCY = (float) ($materialData['rock_dust_cy'] ?? 0);
        $baseCY = $abcCY + $rockDustCY;

        // Apply editable materials if provided
        $materialsEdit = $request->input('materials_edit', []);
        if (is_array($materialsEdit) && !empty($materialsEdit)) {
            $labelMap = [
                'turf' => $turfName,
                'infill_bags' => 'Infill Bags',
                'edging_boards' => 'Composite Edging Boards',
                'weed_barrier_rolls' => 'Weed Barrier Rolls',
                'abc_cy' => 'ABC Base (cy)',
                'rock_dust_cy' => 'Rock Dust (cy)',
            ];

            foreach ($materialsEdit as $key => $row) {
                if (!array_key_exists($key, $labelMap)) continue;
                $label = $labelMap[$key];
                $qty = (float) ($row['qty'] ?? 0);
                $unitCost = (float) ($row['unit_cost'] ?? 0);
                if ($qty > 0) {
                    $materials[$label] = [
                        'qty' => round($qty, 2),
                        'unit_cost' => round($unitCost, 2),
                        'total' => round($qty * $unitCost, 2),
                    ];
                    if ($key === 'turf') {
                        $turfUnitCost = round($unitCost, 2);
                    }
                } else {
                    unset($materials[$label]);
                }
            }
            // Recalculate material total
            $materialTotal = array_sum(array_map(fn($m) => (float) ($m['total'] ?? 0), $materials));
            $materialTotal = round($materialTotal, 2);
        }

        // Adjust excavation tasks to use cy-based production if present
        $laborByTask = collect($results)->keyBy(fn($r)=>strtolower(str_replace(' ','_', $r['task'])));
        $excTasks = ['excavation_skid_steer','excavation_mini_skid'];
        // If user chose an excavation method, ensure we include hours even without qty
        $selectedMethod = $request->input('excavation_method');
        if ($selectedMethod === 'skid') {
            $key = 'excavation_skid_steer';
            $rate = (float) ($dbRates[$key] ?? 0);
            if ($rate > 0) {
                $laborByTask->put($key, [ 'task' => str_replace('_',' ', $key), 'qty' => $excavationCY, 'rate' => $rate ]);
            }
        } elseif ($selectedMethod === 'mini') {
            $key = 'excavation_mini_skid';
            $rate = (float) ($dbRates[$key] ?? 0);
            if ($rate > 0) {
                $laborByTask->put($key, [ 'task' => str_replace('_',' ', $key), 'qty' => $excavationCY, 'rate' => $rate ]);
            }
        }
        // Replace excavation hours using cy-based production for equipment methods
        foreach ($excTasks as $t) {
            if ($laborByTask->has($t)) {
                $rate = (float) ($dbRates[$t] ?? 0);
                $row = $laborByTask->get($t);
                $row['qty'] = $excavationCY;
                $row['rate'] = $rate;
                $laborByTask->put($t, $row);
            }
        }
        // Add base_install hours from base CY, if present
        if ($baseCY > 0) {
            $baseRate = (float) ($dbRates['base_install'] ?? 0);
            if ($baseRate > 0) {
                $laborByTask->put('base_install', [ 'task' => 'base install', 'qty' => $baseCY, 'rate' => $baseRate ]);
            }
        }
        // Rebuild results with corrected quantities
        $results = [];
        $totalHours = 0;
        foreach ($laborByTask as $key => $row) {
            $rate = (float) ($dbRates[$key] ?? $row['rate']);
            if (in_array($key, $excTasks)) {
                $hours = max(0, $excavationCY) * $rate;
                $qty = $excavationCY;
                $unit = 'cy';
            } else {
                $qty = (float) ($row['qty'] ?? 0);
                $hours = $qty * $rate;
                $unit = null;
            }
            if ($hours <= 0) continue;
            $cost = $hours * $laborRate;
            $results[] = [
                'task' => str_replace('_',' ', $key),
                'qty' => round($qty, 2),
                'rate' => $rate,
                'hours' => round($hours, 2),
                'cost' => round($cost, 2),
                'unit' => $unit,
            ];
            $totalHours += $hours;
        }

        $calculator = new LaborCostCalculatorService();
        $totals = $calculator->calculate(
            $totalHours,
            $laborRate,
            array_merge($request->all(), [
                'material_total' => $materialTotal,
                'excavation_cy' => round($excavationCY, 2),
                'base_cy' => round($baseCY, 2),
            ])
        );

        // Build enhanced labor_tasks array for import
        $laborTasks = [];
        foreach ($results as $result) {
            $taskKey = strtolower(str_replace(' ', '_', $result['task']));
            $taskName = ucwords($result['task']);
            $unit = $result['unit'] ?? 'sqft';
            
            $laborTasks[] = [
                'task_key' => $taskKey,
                'task_name' => $taskName,
                'description' => $taskName . " - {$result['qty']} {$unit}",
                'quantity' => $result['qty'],
                'unit' => $unit,
                'production_rate' => $result['rate'],
                'hours' => $result['hours'],
                'hourly_rate' => $laborRate,
                'total_cost' => $result['cost'],
            ];
        }

        // Fees (tamper rental)
        $fees = [];
        if ($request->boolean('rent_tamper')) {
            $days = max(1, (int) $request->input('tamper_days', 1));
            $daily = (float) config('syn_turf.materials.rentals.tamper_daily_cost', 125);
            $fees[] = [
                'name' => 'Motorized Hand Tamper (rental)',
                'quantity' => $days,
                'unit' => 'day',
                'unit_cost' => 0,
                'unit_price' => round($daily, 2),
            ];
        }

        $data = array_merge(
            $validated,
            [
                'tasks' => $results,
                'labor_tasks' => $laborTasks, // Enhanced format for import
                'labor_by_task' => collect($results)
                    ->pluck('hours', 'task')
                    ->map(fn ($h) => round($h, 2))
                    ->toArray(),
                'labor_hours' => round($totalHours, 2),
                'materials' => $materials,
                'material_total' => round($materialTotal, 2),
                'fees' => $fees,
                'area_sqft' => round($areaSqft, 2),
                'edging_linear_ft' => round($edgingLf, 2),
                'excavation_depth_in' => $excavationDepthIn,
                'base_depth_in' => $baseDepthIn,
                'turf_grade' => $validated['turf_grade'],
                'turf_unit_cost' => $turfUnitCost,
                'turf_name' => $turfName,
                'override_turf_price' => $request->input('override_turf_price'),
                'override_infill_price' => $request->input('override_infill_price'),
                'override_edging_price' => $request->input('override_edging_price'),
                'override_weed_barrier_price' => $request->input('override_weed_barrier_price'),
                'turf_custom_name' => $request->input('turf_custom_name'),
                'materials_override_enabled' => $overridesEnabled,
            ],
            $totals
        );

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
                    'calculation_type' => 'syn_turf',
                    'data' => $data,
                    'is_template' => true,
                    'template_name' => $request->input('template_name') ?: null,
                    'template_scope' => $request->input('template_scope') ?: 'global',
                ]);
            } else {
                $calc = Calculation::create([
                    'site_visit_id' => $validated['site_visit_id'],
                    'calculation_type' => 'syn_turf',
                    'data' => $data,
                ]);
            }
        }

        if ($mode === 'template') {
            return redirect()->route('estimates.show', $request->input('estimate_id'))
                ->with('success', 'Synthetic Turf template saved.');
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
