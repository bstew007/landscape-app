<?php

namespace App\Http\Controllers;

use App\Models\LaborItem;
use App\Services\BudgetService;
use Illuminate\Http\Request;

class LaborController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $labor = LaborItem::query()
            ->when($search, function ($query, $term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('type', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $budgetStats = $this->computeBudgetStats();

        return view('labor.index', array_merge([
            'labor' => $labor,
            'search' => $search,
        ], $budgetStats));
    }

    public function importForm()
    {
        return view('labor.import');
    }

    public function export(Request $request)
    {
        $filename = 'labor-' . now()->format('Ymd-His') . '.csv';
        $rows = \App\Models\LaborItem::orderBy('name')->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $columns = ['name','type','unit','base_rate','overtime_rate','burden_percentage','labor_burden_percentage','unbillable_percentage','average_wage','overtime_factor','is_billable','is_active','description','notes','internal_notes'];
        $callback = function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->name,
                    $r->type,
                    $r->unit,
                    $r->base_rate,
                    $r->overtime_rate,
                    $r->burden_percentage,
                    $r->labor_burden_percentage,
                    $r->unbillable_percentage,
                    $r->average_wage,
                    $r->overtime_factor,
                    $r->is_billable ? 'true' : 'false',
                    $r->is_active ? 'true' : 'false',
                    $r->description,
                    $r->notes,
                    $r->internal_notes,
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:json,txt', 'max:5120'],
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $rows = json_decode($json, true);
        if (!is_array($rows)) {
            return back()->withErrors(['file' => 'Invalid JSON structure. Expecting an array of labor entries.']);
        }

        $created = 0; $updated = 0; $skipped = 0;
        foreach ($rows as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') { $skipped++; continue; }

            $attrs = [
                'type' => $row['type'] ?? 'crew',
                'unit' => $row['unit'] ?? 'hr',
                'base_rate' => (float) ($row['base_rate'] ?? 0),
                'overtime_rate' => (float) ($row['overtime_rate'] ?? 0),
                'burden_percentage' => (float) ($row['burden_percentage'] ?? 0),
                'labor_burden_percentage' => (float) ($row['labor_burden_percentage'] ?? 0),
                'unbillable_percentage' => (float) ($row['unbillable_percentage'] ?? 0),
                'average_wage' => isset($row['average_wage']) ? (float) $row['average_wage'] : null,
                'overtime_factor' => isset($row['overtime_factor']) ? (float) $row['overtime_factor'] : null,
                'is_billable' => array_key_exists('is_billable', $row) ? (bool)$row['is_billable'] : true,
                'is_active' => array_key_exists('is_active', $row) ? (bool)$row['is_active'] : true,
                'description' => $row['description'] ?? null,
                'notes' => $row['notes'] ?? null,
                'internal_notes' => $row['internal_notes'] ?? null,
            ];

            $existing = \App\Models\LaborItem::where('name', $name)
                ->where('type', $attrs['type'])
                ->first();


            if ($existing) {
                $existing->update($attrs);
                $updated++;
            } else {
                \App\Models\LaborItem::create(array_merge(['name' => $name], $attrs));
                $created++;
            }
        }

        return redirect()->route('labor.index')->with('success', "Import complete. Created: $created, Updated: $updated, Skipped: $skipped");
    }

    public function create()
    {
        return view('labor.create');
    }

    public function store(Request $request)
    {
        $data = $this->normalizeNumericFields($this->validateLabor($request));
        $data['is_billable'] = (bool) ($data['is_billable'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $labor = LaborItem::create($data);

        // Return JSON for AJAX requests (modal mode)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Labor item '{$labor->name}' created.",
                'labor' => $labor,
            ]);
        }

        $returnTo = $request->input('return_to');
        if ($returnTo) {
            return redirect($returnTo)->with('success', "Labor item '{$labor->name}' created.");
        }
        return redirect()->route('labor.index')->with('success', 'Labor entry created.');
    }

    public function edit(LaborItem $labor)
    {
        return view('labor.edit', compact('labor'));
    }

    public function update(Request $request, LaborItem $labor)
    {
        $data = $this->normalizeNumericFields($this->validateLabor($request));
        $data['is_billable'] = (bool) ($data['is_billable'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $labor->update($data);

        return redirect()->route('labor.index')->with('success', 'Labor entry updated.');
    }

    public function destroy(LaborItem $labor)
    {
        $labor->delete();

        return redirect()->route('labor.index')->with('success', 'Labor entry deleted.');
    }

    protected function parseCsvLabor(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return [];
        $header = null; $rows = [];
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if ($header === null) { $header = array_map(fn($h) => strtolower(trim($h)), $data); continue; }
            if (count(array_filter($data, fn($v) => $v !== null && $v !== '')) === 0) continue;
            $row = [];
            foreach ($data as $i => $value) { $row[$header[$i] ?? ('col'.$i)] = $value; }
            $map = [
                'name' => ['name','labor','entry'],
                'type' => ['type','category'],
                'unit' => ['unit','uom'],
                'base_rate' => ['base_rate','rate','price'],
                'overtime_rate' => ['overtime_rate','ot_rate'],
                'burden_percentage' => ['burden_percentage','burden','burden_pct'],
                'labor_burden_percentage' => ['labor_burden_percentage','labor_burden','labor_burden_pct'],
                'unbillable_percentage' => ['unbillable_percentage','unbillable','unbillable_pct'],
                'average_wage' => ['average_wage','avg_wage','wage'],
                'overtime_factor' => ['overtime_factor','ot_factor','ot_mult'],
                'is_billable' => ['is_billable','billable'],
                'is_active' => ['is_active','active'],
                'description' => ['description','desc'],
                'notes' => ['notes'],
                'internal_notes' => ['internal_notes','internal'],
            ];
            $normalized = [];
            foreach ($map as $canonical => $aliases) {
                foreach ($aliases as $alias) {
                    if (array_key_exists($alias, $row)) { $normalized[$canonical] = $row[$alias]; break; }
                }
            }
            // Casts
            foreach (['base_rate','overtime_rate','burden_percentage','labor_burden_percentage','unbillable_percentage','average_wage','overtime_factor'] as $k) {
                if (isset($normalized[$k])) $normalized[$k] = (float) $normalized[$k];
            }
            if (isset($normalized['is_billable'])) $normalized['is_billable'] = filter_var($normalized['is_billable'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
            if (isset($normalized['is_active'])) $normalized['is_active'] = filter_var($normalized['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
            $rows[] = $normalized + ['name' => $row['name'] ?? ($row['labor'] ?? '')];
        }
        fclose($handle);
        return $rows;
    }

    protected function validateLabor(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'base_rate' => ['required', 'numeric', 'min:0'],
            'overtime_rate' => ['nullable', 'numeric', 'min:0'],
            'burden_percentage' => ['nullable', 'numeric', 'min:0'],
            'labor_burden_percentage' => ['nullable', 'numeric', 'min:0'],
            'unbillable_percentage' => ['nullable', 'numeric', 'min:0'],
            'average_wage' => ['nullable', 'numeric', 'min:0'],
            'overtime_factor' => ['nullable', 'numeric', 'min:0'],
            'is_billable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'cost_code_id' => ['nullable','integer','exists:cost_codes,id'],
        ]);
    }

    protected function normalizeNumericFields(array $data): array
    {
        $defaults = [
            'overtime_rate',
            'burden_percentage',
            'labor_burden_percentage',
            'unbillable_percentage',
            'average_wage',
            'overtime_factor',
        ];

        foreach ($defaults as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                $data[$field] = 0;
            }
        }

        return $data;
    }
    protected function computeBudgetStats(): array
    {
        $activeBudget = app(BudgetService::class)->active(false);
        if (!$activeBudget) {
            return [
                'budgetName' => null,
                'overheadRate' => 0.0,
                'overheadHours' => 0.0,
                'profitMarginPct' => null,
            ];
        }

        $inputs = $activeBudget->inputs ?? [];
        $expensesRows = (array) data_get($inputs, 'overhead.expenses.rows', []);
        $wagesRows = (array) data_get($inputs, 'overhead.wages.rows', []);
        $ohEquipRows = (array) data_get($inputs, 'overhead.equipment.rows', []);

        $ohExpenses = 0.0;
        foreach ($expensesRows as $r) {
            $ohExpenses += (float) ($r['current'] ?? 0);
        }
        $ohWages = 0.0;
        foreach ($wagesRows as $r) {
            $ohWages += (float) ($r['forecast'] ?? 0);
        }
        $ohEquip = 0.0;
        foreach ($ohEquipRows as $r) {
            $qty = (float) ($r['qty'] ?? 1);
            $per = (float) ($r['cost_per_year'] ?? 0);
            $ohEquip += ($qty * $per);
        }
        $ohTotal = $ohExpenses + $ohWages + $ohEquip;

        $hourlyRows = (array) data_get($inputs, 'labor.hourly.rows', []);
        $salaryRows = (array) data_get($inputs, 'labor.salary.rows', []);
        $totalHours = 0.0;
        foreach ($hourlyRows as $r) {
            $staff = (float) ($r['staff'] ?? 0);
            $hrs = (float) ($r['hrs'] ?? 0);
            $ot = (float) ($r['ot_hrs'] ?? 0);
            $totalHours += $staff * ($hrs + $ot);
        }
        foreach ($salaryRows as $r) {
            $staff = (float) ($r['staff'] ?? 0);
            $hrs = (float) ($r['ann_hrs'] ?? 0);
            $totalHours += $staff * $hrs;
        }

        $ohr = $totalHours > 0 ? ($ohTotal / $totalHours) : 0.0;
        $profit = (float) ($activeBudget->desired_profit_margin ?? 0);

        return [
            'budgetName' => $activeBudget->name ?? 'Active Budget',
            'overheadRate' => round($ohr, 2),
            'overheadHours' => round($totalHours, 1),
            'profitMarginPct' => round($profit * 100, 1),
        ];
    }
}
