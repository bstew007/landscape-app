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
        $inputs = $data['inputs'] ?? [];
        $outputs = $this->budget->computeOutputs($inputs);
        $budget = CompanyBudget::create([
            'name' => $data['name'] ?? 'Budget',
            'year' => $data['year'] ?? null,
            'effective_from' => $data['effective_from'] ?? null,
            'desired_profit_margin' => $data['desired_profit_margin'] ?? 0.2,
            'inputs' => $inputs,
            'outputs' => $outputs,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        if ($budget->is_active) {
            CompanyBudget::where('id', '!=', $budget->id)->update(['is_active' => false]);
        }

        Cache::forget(\App\Services\BudgetService::CACHE_KEY);
        return redirect()->route('admin.budgets.edit', ['budget' => $budget->id, 'section' => $request->input('section', 'Budget Info')])
            ->with('success', 'Budget created.');
    }

    public function edit(CompanyBudget $budget)
    {
        return view('admin.budgets.edit', compact('budget'));
    }

    public function update(Request $request, CompanyBudget $budget)
    {
        $data = $this->validatePayload($request);
        // Preserve existing inputs and only overwrite changed keys (deep merge)
        $mergedInputs = array_replace_recursive($budget->inputs ?? [], $data['inputs'] ?? []);
        // For list arrays that support deletions, overwrite the entire list with the posted value
        // Equipment rows: overwrite the entire list so deletions persist (treat missing as empty)
        $postedEquipmentRows = data_get($data, 'inputs.equipment.rows', null);
        if ($postedEquipmentRows === null) {
            $mergedInputs['equipment']['rows'] = [];
        } else {
            $mergedInputs['equipment']['rows'] = array_values($postedEquipmentRows);
        }
        // Overhead equipment rows: overwrite list to persist deletions (treat missing as empty)
        $postedOverheadEquipRows = data_get($data, 'inputs.overhead.equipment.rows', null);
        if ($postedOverheadEquipRows === null) {
            $mergedInputs['overhead']['equipment']['rows'] = [];
        } else {
            $mergedInputs['overhead']['equipment']['rows'] = array_values($postedOverheadEquipRows);
        }

        $outputs = $this->budget->computeOutputs($mergedInputs);

        $budget->fill([
            'name' => $data['name'] ?? $budget->name,
            'year' => $data['year'] ?? $budget->year,
            'effective_from' => $data['effective_from'] ?? $budget->effective_from,
            'desired_profit_margin' => $data['desired_profit_margin'] ?? $budget->desired_profit_margin,
            'inputs' => $mergedInputs,
            'outputs' => $outputs,
            'is_active' => (bool) ($data['is_active'] ?? $budget->is_active),
        ])->save();

        if ($budget->is_active) {
            CompanyBudget::where('id', '!=', $budget->id)->update(['is_active' => false]);
        }

        Cache::forget(\App\Services\BudgetService::CACHE_KEY);
        return redirect()->route('admin.budgets.edit', ['budget' => $budget->id, 'section' => $request->input('section', 'Budget Info')])
            ->with('success', 'Budget updated.');
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
            // Sales Budget rows
            'inputs.sales.rows' => 'nullable|array',
            'inputs.sales.rows.*.account_id' => 'nullable|string|max:50',
            'inputs.sales.rows.*.division' => 'nullable|string|max:100',
            'inputs.sales.rows.*.previous' => 'nullable|numeric|min:0',
            'inputs.sales.rows.*.forecast' => 'nullable|numeric|min:0',
            'inputs.sales.rows.*.comments' => 'nullable|string|max:255',
            // Labor inputs (legacy/simple model used by BudgetService outputs for now)
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
            // Field Labor detailed inputs (persist UI tables)
            'inputs.labor.burden_pct' => 'nullable|numeric|min:0|max:100',
            'inputs.labor.ot_multiplier' => 'nullable|numeric|min:1|max:3',
            'inputs.labor.industry_avg_ratio' => 'nullable|numeric|min:0|max:100',
            'inputs.labor.hourly.rows' => 'nullable|array',
            'inputs.labor.hourly.rows.*.type' => 'nullable|string|max:100',
            'inputs.labor.hourly.rows.*.staff' => 'nullable|numeric|min:0',
            'inputs.labor.hourly.rows.*.hrs' => 'nullable|numeric|min:0',
            'inputs.labor.hourly.rows.*.ot_hrs' => 'nullable|numeric|min:0',
            'inputs.labor.hourly.rows.*.avg_wage' => 'nullable|numeric|min:0',
            'inputs.labor.hourly.rows.*.bonus' => 'nullable|numeric|min:0',
            'inputs.labor.salary.rows' => 'nullable|array',
            'inputs.labor.salary.rows.*.type' => 'nullable|string|max:100',
            'inputs.labor.salary.rows.*.staff' => 'nullable|numeric|min:0',
            'inputs.labor.salary.rows.*.ann_hrs' => 'nullable|numeric|min:0',
            'inputs.labor.salary.rows.*.ann_salary' => 'nullable|numeric|min:0',
            'inputs.labor.salary.rows.*.bonus' => 'nullable|numeric|min:0',
            // Overhead
            'inputs.overhead.total' => 'nullable|numeric|min:0',
            // Overhead complex inputs
            'inputs.overhead.labor_burden_pct' => 'nullable|numeric|min:0|max:100',
            'inputs.overhead.industry_avg_ratio' => 'nullable|numeric|min:0|max:100',
            'inputs.overhead.expenses.rows' => 'nullable|array',
            'inputs.overhead.expenses.rows.*.account_id' => 'nullable|string|max:50',
            'inputs.overhead.expenses.rows.*.expense' => 'nullable|string|max:100',
            'inputs.overhead.expenses.rows.*.previous' => 'nullable|numeric|min:0',
            'inputs.overhead.expenses.rows.*.current' => 'nullable|numeric|min:0',
            'inputs.overhead.expenses.rows.*.comments' => 'nullable|string|max:255',
            'inputs.overhead.wages.rows' => 'nullable|array',
            'inputs.overhead.wages.rows.*.title' => 'nullable|string|max:100',
            'inputs.overhead.wages.rows.*.previous' => 'nullable|numeric|min:0',
            'inputs.overhead.wages.rows.*.forecast' => 'nullable|numeric|min:0',
            'inputs.overhead.wages.rows.*.comments' => 'nullable|string|max:255',
            // Overhead equipment (simplified accept all)
            'inputs.overhead.equipment' => 'nullable|array',
            'inputs.overhead.equipment.rows' => 'nullable|array',
            'inputs.overhead.equipment.rows.*.type' => 'nullable|string|max:100',
            'inputs.overhead.equipment.rows.*.qty' => 'nullable|numeric|min:0',
            'inputs.overhead.equipment.rows.*.class' => 'nullable|string|in:Custom,Owned,Leased,Group',
            'inputs.overhead.equipment.rows.*.description' => 'nullable|string|max:255',
            'inputs.overhead.equipment.rows.*.cost_per_year' => 'nullable|numeric|min:0',
            'inputs.overhead.equipment.rows.*.owned' => 'nullable|array',
            'inputs.overhead.equipment.rows.*.owned.replacement_value' => 'nullable|numeric|min:0',
            'inputs.overhead.equipment.rows.*.owned.fees' => 'nullable|numeric|min:0',
            'inputs.overhead.equipment.rows.*.owned.years' => 'nullable|numeric|min:0.1',
            'inputs.overhead.equipment.rows.*.owned.salvage_value' => 'nullable|numeric|min:0',
            'inputs.overhead.equipment.rows.*.owned.months_per_year' => 'nullable|integer|min:1|max:12',
            'inputs.overhead.equipment.rows.*.owned.division_months' => 'nullable|integer|min:1|max:12',
            'inputs.overhead.equipment.rows.*.owned.interest_rate_pct' => 'nullable|numeric|min:0|max:100',
            'inputs.overhead.equipment.rows.*.leased' => 'nullable|array',
            'inputs.overhead.equipment.rows.*.leased.monthly_payment' => 'nullable|numeric|min:0',
            'inputs.overhead.equipment.rows.*.leased.payments_per_year' => 'nullable|integer|min:1|max:12',
            'inputs.overhead.equipment.rows.*.leased.months_per_year' => 'nullable|integer|min:1|max:12',
            'inputs.overhead.equipment.rows.*.leased.division_months' => 'nullable|integer|min:1|max:12',
            'inputs.overhead.equipment.general' => 'nullable|array',
            'inputs.overhead.equipment.general.fuel' => 'nullable|numeric|min:0',
            'inputs.overhead.equipment.general.repairs' => 'nullable|numeric|min:0',
            'inputs.overhead.equipment.general.insurance_misc' => 'nullable|numeric|min:0',
            'inputs.overhead.equipment.rentals' => 'nullable|numeric|min:0',
            // Equipment rows
            'inputs.equipment.rows' => 'nullable|array',
            'inputs.equipment.rows.*.type' => 'nullable|string|max:100',
            'inputs.equipment.rows.*.qty' => 'nullable|numeric|min:0',
            'inputs.equipment.rows.*.class' => 'nullable|string|in:Custom,Owned,Leased,Group',
            'inputs.equipment.rows.*.description' => 'nullable|string|max:255',
            'inputs.equipment.rows.*.cost_per_year' => 'nullable|numeric|min:0',
            // Owned-class details (optional)
            'inputs.equipment.rows.*.owned' => 'nullable|array',
            'inputs.equipment.rows.*.owned.replacement_value' => 'nullable|numeric|min:0',
            'inputs.equipment.rows.*.owned.fees' => 'nullable|numeric|min:0',
            'inputs.equipment.rows.*.owned.years' => 'nullable|numeric|min:0.1',
            'inputs.equipment.rows.*.owned.salvage_value' => 'nullable|numeric|min:0',
            'inputs.equipment.rows.*.owned.months_per_year' => 'nullable|integer|min:1|max:12',
            'inputs.equipment.rows.*.owned.division_months' => 'nullable|integer|min:1|max:12',
            'inputs.equipment.rows.*.owned.interest_rate_pct' => 'nullable|numeric|min:0|max:100',
            // Leased-class details (optional)
            'inputs.equipment.rows.*.leased' => 'nullable|array',
            'inputs.equipment.rows.*.leased.monthly_payment' => 'nullable|numeric|min:0',
            'inputs.equipment.rows.*.leased.payments_per_year' => 'nullable|integer|min:1|max:12',
            'inputs.equipment.rows.*.leased.months_per_year' => 'nullable|integer|min:1|max:12',
            'inputs.equipment.rows.*.leased.division_months' => 'nullable|integer|min:1|max:12',
            // Group-class details (optional)
            'inputs.equipment.rows.*.group' => 'nullable|array',
            'inputs.equipment.rows.*.group.items' => 'nullable|array',
            'inputs.equipment.rows.*.group.items.*.name' => 'nullable|string|max:100',
            'inputs.equipment.rows.*.group.items.*.qty' => 'nullable|numeric|min:0',
            'inputs.equipment.rows.*.group.items.*.purchase_price' => 'nullable|numeric|min:0',
            'inputs.equipment.rows.*.group.items.*.resale_value' => 'nullable|numeric|min:0',
            'inputs.equipment.rows.*.group.items.*.years' => 'nullable|numeric|min:0.1',
            // Equipment general/summary inputs
            'inputs.equipment.general' => 'nullable|array',
            'inputs.equipment.general.fuel' => 'nullable|numeric|min:0',
            'inputs.equipment.general.repairs' => 'nullable|numeric|min:0',
            'inputs.equipment.general.insurance_misc' => 'nullable|numeric|min:0',
            'inputs.equipment.rentals' => 'nullable|numeric|min:0',
            'inputs.equipment.industry_avg_ratio' => 'nullable|numeric|min:0|max:100',
            // Materials inputs
            'inputs.materials' => 'nullable|array',
            'inputs.materials.tax_pct' => 'nullable|numeric|min:0|max:100',
            'inputs.materials.industry_avg_ratio' => 'nullable|numeric|min:0|max:100',
            'inputs.materials.rows' => 'nullable|array',
            'inputs.materials.rows.*.account_id' => 'nullable|string|max:50',
            'inputs.materials.rows.*.expense' => 'nullable|string|max:100',
            'inputs.materials.rows.*.previous' => 'nullable|numeric|min:0',
            'inputs.materials.rows.*.current' => 'nullable|numeric|min:0',
            'inputs.materials.rows.*.comments' => 'nullable|string|max:255',
            // Subcontracting inputs
            'inputs.subcontracting' => 'nullable|array',
            'inputs.subcontracting.rows' => 'nullable|array',
            'inputs.subcontracting.rows.*.account_id' => 'nullable|string|max:50',
            'inputs.subcontracting.rows.*.expense' => 'nullable|string|max:100',
            'inputs.subcontracting.rows.*.previous' => 'nullable|numeric|min:0',
            'inputs.subcontracting.rows.*.current' => 'nullable|numeric|min:0',
            'inputs.subcontracting.rows.*.comments' => 'nullable|string|max:255',
            // Overhead Recovery (persist UI selections and computed markups)
            'inputs.oh_recovery' => 'nullable|array',
            'inputs.oh_recovery.method' => 'nullable|string|in:labor_hour,revenue,dual',
            'inputs.oh_recovery.labor_hour' => 'nullable|array',
            'inputs.oh_recovery.labor_hour.activated' => 'nullable|boolean',
            'inputs.oh_recovery.labor_hour.markup_per_hour' => 'nullable|numeric|min:0',
            'inputs.oh_recovery.revenue' => 'nullable|array',
            'inputs.oh_recovery.revenue.activated' => 'nullable|boolean',
            'inputs.oh_recovery.revenue.markup_fraction' => 'nullable|numeric|min:0',
            'inputs.oh_recovery.dual' => 'nullable|array',
            'inputs.oh_recovery.dual.activated' => 'nullable|boolean',
            'inputs.oh_recovery.dual.labor_share_pct' => 'nullable|numeric|min:0|max:100',
            'inputs.oh_recovery.dual.labor_markup_per_hour' => 'nullable|numeric|min:0',
            'inputs.oh_recovery.dual.revenue_markup_fraction' => 'nullable|numeric|min:0',
        ]);
    }
}
