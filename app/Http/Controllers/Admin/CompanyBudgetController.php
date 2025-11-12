<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyBudget;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CompanyBudgetController extends Controller
{
    public function __construct(protected BudgetService $budget)
    {
        // Add real authorization in your app (e.g., middleware or policies)
        // $this->middleware('can:admin');
    }

    public function index()
    {
        $budgets = CompanyBudget::orderByDesc('is_active')->orderByDesc('effective_from')->paginate(10);
        return view('admin.budgets.index', compact('budgets'));
    }

    public function create()
    {
        return view('admin.budgets.edit', ['budget' => new CompanyBudget()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $outputs = $this->budget->computeOutputs($data['inputs'] ?? []);
        $budget = CompanyBudget::create([
            'name' => $data['name'] ?? 'Budget',
            'year' => $data['year'] ?? null,
            'effective_from' => $data['effective_from'] ?? null,
            'desired_profit_margin' => $data['desired_profit_margin'] ?? 0.2,
            'inputs' => $data['inputs'] ?? [],
            'outputs' => $outputs,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        if ($budget->is_active) {
            CompanyBudget::where('id', '!=', $budget->id)->update(['is_active' => false]);
        }

        Cache::forget(\App\Services\BudgetService::CACHE_KEY);
        return redirect()->route('admin.budgets.edit', $budget)->with('success', 'Budget created.');
    }

    public function edit(CompanyBudget $budget)
    {
        return view('admin.budgets.edit', compact('budget'));
    }

    public function update(Request $request, CompanyBudget $budget)
    {
        $data = $this->validatePayload($request);
        $outputs = $this->budget->computeOutputs($data['inputs'] ?? []);

        $budget->fill([
            'name' => $data['name'] ?? $budget->name,
            'year' => $data['year'] ?? $budget->year,
            'effective_from' => $data['effective_from'] ?? $budget->effective_from,
            'desired_profit_margin' => $data['desired_profit_margin'] ?? $budget->desired_profit_margin,
            'inputs' => $data['inputs'] ?? $budget->inputs,
            'outputs' => $outputs,
            'is_active' => (bool) ($data['is_active'] ?? $budget->is_active),
        ])->save();

        if ($budget->is_active) {
            CompanyBudget::where('id', '!=', $budget->id)->update(['is_active' => false]);
        }

        Cache::forget(\App\Services\BudgetService::CACHE_KEY);
        return redirect()->route('admin.budgets.edit', $budget)->with('success', 'Budget updated.');
    }

    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'nullable|integer|min:2000|max:2100',
            'effective_from' => 'nullable|date',
            'desired_profit_margin' => 'required|numeric|min:0|max:0.999',
            'is_active' => 'sometimes|boolean',
            'inputs' => 'nullable|array',
            'inputs.labor.headcount' => 'nullable|numeric|min:0',
            'inputs.labor.wage' => 'nullable|numeric|min:0',
            'inputs.labor.payroll_taxes' => 'nullable|numeric|min:0',
            'inputs.labor.benefits' => 'nullable|numeric|min:0',
            'inputs.labor.workers_comp' => 'nullable|numeric|min:0',
            'inputs.labor.pto_hours' => 'nullable|numeric|min:0',
            'inputs.labor.hours_per_week' => 'nullable|numeric|min:0',
            'inputs.labor.weeks_per_year' => 'nullable|numeric|min:0',
            'inputs.labor.utilization' => 'nullable|numeric|min:0|max:1',
            'inputs.labor.productivity' => 'nullable|numeric|min:0|max:1',
            'inputs.overhead.total' => 'nullable|numeric|min:0',
        ]);
    }
}
