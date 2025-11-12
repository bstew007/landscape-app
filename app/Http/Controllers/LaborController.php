<?php

namespace App\Http\Controllers;

use App\Models\LaborItem;
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

        return view('labor.index', compact('labor', 'search'));
    }

    public function importForm()
    {
        return view('labor.import');
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
                'is_billable' => array_key_exists('is_billable', $row) ? (bool)$row['is_billable'] : true,
                'is_active' => array_key_exists('is_active', $row) ? (bool)$row['is_active'] : true,
                'notes' => $row['notes'] ?? null,
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
        $data = $this->validateLabor($request);
        $data['is_billable'] = (bool) ($data['is_billable'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        LaborItem::create($data);

        return redirect()->route('labor.index')->with('success', 'Labor entry created.');
    }

    public function edit(LaborItem $labor)
    {
        return view('labor.edit', compact('labor'));
    }

    public function update(Request $request, LaborItem $labor)
    {
        $data = $this->validateLabor($request);
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
                'is_billable' => ['is_billable','billable'],
                'is_active' => ['is_active','active'],
                'notes' => ['notes','desc','description'],
            ];
            $normalized = [];
            foreach ($map as $canonical => $aliases) {
                foreach ($aliases as $alias) {
                    if (array_key_exists($alias, $row)) { $normalized[$canonical] = $row[$alias]; break; }
                }
            }
            // Casts
            if (isset($normalized['base_rate'])) $normalized['base_rate'] = (float) $normalized['base_rate'];
            if (isset($normalized['overtime_rate'])) $normalized['overtime_rate'] = (float) $normalized['overtime_rate'];
            if (isset($normalized['burden_percentage'])) $normalized['burden_percentage'] = (float) $normalized['burden_percentage'];
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
            'is_billable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
