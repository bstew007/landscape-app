<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\Estimate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalculatorTemplateController extends Controller
{
    // GET /estimates/{estimate}/calculator/templates
    public function index(Request $request, Estimate $estimate)
    {
        $type = $request->query('type');
        $query = Calculation::query()
            ->where('is_template', true)
            ->when($type, fn($q) => $q->where('calculation_type', $type))
            ->where('is_active', true)
            ->where(function ($q) use ($estimate) {
                $q->where(fn($q) => $q->where('template_scope', 'property')->where('property_id', $estimate->property_id))
                  ->orWhere(fn($q) => $q->where('template_scope', 'client')->where('client_id', $estimate->client_id))
                  ->orWhere('template_scope', 'global');
            })
            ->latest();

        $templates = $query->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($templates);
        }

        return view('estimates.partials.templates-list', compact('templates', 'estimate', 'type'));
    }

    // POST /templates (store reusable template)
    public function store(Request $request)
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

        if ($data['template_scope'] === 'client' && empty($data['client_id'])) {
            $data['client_id'] = optional(Estimate::find($data['estimate_id']))->client_id;
        }
        if ($data['template_scope'] === 'property' && empty($data['property_id'])) {
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

        return response()->json(['status' => 'ok', 'template' => $template]);
    }
}
