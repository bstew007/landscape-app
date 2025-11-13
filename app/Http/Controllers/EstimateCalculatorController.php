<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Services\CalculationImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstimateCalculatorController extends Controller
{
    public function __construct(protected CalculationImportService $importer)
    {
    }

    // GET estimates/{estimate}/calculator/templates?type=mulching
    public function templates(Request $request, Estimate $estimate)
    {
        $type = $request->query('type');

        $templates = Calculation::query()
            ->where('is_template', true)
            ->when($type, fn($q) => $q->where('calculation_type', $type))
            ->where('is_active', true)
            ->where(function ($q) use ($estimate) {
                $q->where(fn($q) => $q->where('template_scope', 'property')->where('property_id', $estimate->property_id))
                  ->orWhere(fn($q) => $q->where('template_scope', 'client')->where('client_id', $estimate->client_id))
                  ->orWhere('template_scope', 'global');
            })
            ->latest()
            ->limit(100)
            ->get(['id','template_name','calculation_type','template_scope','client_id','property_id','created_at']);

        return response()->json(['templates' => $templates]);
    }

    // POST calculator/templates (Save Template from Template Mode)
    public function saveTemplate(Request $request)
    {
        $data = $request->validate([
            'template_name' => ['required','string','max:255'],
            'calculation_type' => ['required','string','max:100'],
            'template_scope' => ['required', Rule::in(['global','client','property'])],
            'client_id' => ['nullable','exists:clients,id'],
            'property_id' => ['nullable','exists:properties,id'],
            'data' => ['required','array'],
            'estimate_id' => ['nullable','exists:estimates,id'],
        ]);

        if ($data['template_scope'] === 'client' && empty($data['client_id']) && !empty($data['estimate_id'])) {
            $data['client_id'] = optional(Estimate::find($data['estimate_id']))->client_id;
        }
        if ($data['template_scope'] === 'property' && empty($data['property_id']) && !empty($data['estimate_id'])) {
            $data['property_id'] = optional(Estimate::find($data['estimate_id']))->property_id;
        }

        $template = Calculation::create([
            'is_template' => true,
            'template_name' => $data['template_name'],
            'calculation_type' => $data['calculation_type'],
            'template_scope' => $data['template_scope'],
            'client_id' => $data['client_id'] ?? null,
            'property_id' => $data['property_id'] ?? null,
            'data' => $data['data'],
            'estimate_id' => $data['estimate_id'] ?? null,
        ]);

        return response()->json(['status' => 'ok', 'template' => $template], 201);
    }

    // POST estimates/{estimate}/calculator/import { template_id, replace }
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
        $estimate->refresh();

        $created = $estimate->items()
            ->where('calculation_id', $calc->id)
            ->orderBy('id')
            ->get(['id','item_type','name','description','unit','quantity','unit_cost','unit_price','margin_rate','tax_rate','cost_total','margin_total','line_total','calculation_id']);

        return response()->json([
            'status' => 'ok',
            'calculation_id' => $calc->id,
            'calculation_type' => $calc->calculation_type,
            'items' => $created,
            'totals' => [
                'material_subtotal' => $estimate->material_subtotal,
                'labor_subtotal' => $estimate->labor_subtotal,
                'fee_total' => $estimate->fee_total,
                'discount_total' => $estimate->discount_total,
                'tax_total' => $estimate->tax_total,
                'grand_total' => $estimate->grand_total,
                'revenue_total' => $estimate->revenue_total,
                'cost_total' => $estimate->cost_total,
                'profit_total' => $estimate->profit_total,
                'net_profit_total' => $estimate->net_profit_total,
                'profit_margin' => $estimate->profit_margin,
                'net_margin' => $estimate->net_margin,
            ],
        ]);
    }
}
