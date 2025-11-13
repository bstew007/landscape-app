<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Services\CalculationImportService;
use Illuminate\Http\Request;

class EstimateCalculatorController extends Controller
{
    public function __construct(protected CalculationImportService $importer)
    {
    }

    // List templates for a given calculator type
    public function templates(Request $request)
    {
        $type = $request->query('type');
        $templates = Calculation::query()
            ->where('is_template', true)
            ->when($type, fn($q) => $q->where('calculation_type', $type))
            ->latest()
            ->limit(50)
            ->get(['id','template_name','calculation_type','created_at']);

        return response()->json(['templates' => $templates]);
    }

    // Save a new template from posted calculator payload
    public function saveTemplate(Request $request)
    {
        $data = $request->validate([
            'calculation_type' => 'required|string',
            'data' => 'required|array',
            'template_name' => 'required|string|max:255',
        ]);

        $calc = Calculation::create([
            'calculation_type' => $data['calculation_type'],
            'data' => $data['data'],
            'is_template' => true,
            'template_name' => $data['template_name'],
        ]);

        return response()->json(['status' => 'ok', 'template' => $calc], 201);
    }

    // Import a template or raw payload into an estimate
    public function import(Request $request, Estimate $estimate)
    {
        $payload = $request->validate([
            'calculation_type' => 'required_without:template_id|string',
            'data' => 'required_without:template_id|array',
            'replace' => 'sometimes|boolean',
            'template_id' => 'nullable|exists:calculations,id',
        ]);

        if (!empty($payload['template_id'])) {
            $template = Calculation::where('is_template', true)->findOrFail($payload['template_id']);
            $calc = Calculation::create([
                'calculation_type' => $template->calculation_type,
                'data' => $template->data,
                'estimate_id' => $estimate->id,
            ]);
        } else {
            $calc = Calculation::create([
                'calculation_type' => $payload['calculation_type'],
                'data' => $payload['data'],
                'estimate_id' => $estimate->id,
            ]);
        }

        $replace = (bool) ($payload['replace'] ?? false);
        $this->importer->importCalculation($estimate, $calc, $replace);

        return response()->json(['status' => 'ok', 'message' => $replace ? 'Replaced items from calculator.' : 'Appended items from calculator.']);
    }
}
