<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Contact;
use App\Models\EquipmentItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $ownership = $request->query('ownership');

        $categories = EquipmentItem::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $equipment = EquipmentItem::query()
            ->when($search, function ($query, $term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%")
                    ->orWhere('model', 'like', "%{$term}%");
            })
            ->when($category, function ($query, $cat) {
                $query->where('category', $cat);
            })
            ->when($ownership, function ($query, $type) {
                $query->where('ownership_type', $type);
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('equipment.index', compact('equipment', 'search', 'categories', 'category', 'ownership'));
    }

    public function importForm()
    {
        return view('equipment.import');
    }

    public function export(Request $request)
    {
        $filename = 'equipment-' . now()->format('Ymd-His') . '.csv';
        $equipment = EquipmentItem::orderBy('name')->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $columns = ['name', 'sku', 'category', 'ownership_type', 'unit', 'hourly_cost', 'daily_cost', 'hourly_rate', 'daily_rate', 'vendor_name', 'model', 'description', 'is_active'];

        $callback = function () use ($equipment, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($equipment as $eq) {
                fputcsv($out, [
                    $eq->name,
                    $eq->sku,
                    $eq->category,
                    $eq->ownership_type,
                    $eq->unit,
                    $eq->hourly_cost,
                    $eq->daily_cost,
                    $eq->hourly_rate,
                    $eq->daily_rate,
                    $eq->vendor_name,
                    $eq->model,
                    $eq->description,
                    $eq->is_active ? 'true' : 'false',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:json,txt,csv', 'max:5120'],
        ]);

        $path = $request->file('file')->getRealPath();
        $ext = strtolower($request->file('file')->getClientOriginalExtension());

        if ($ext === 'json' || $ext === 'txt') {
            $rows = $this->parseJsonEquipment($path);
        } else {
            $rows = $this->parseCsvEquipment($path);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (empty($row['name'])) {
                $skipped++;
                continue;
            }

            $name = $row['name'];
            $attrs = [
                'sku' => $row['sku'] ?? null,
                'category' => $row['category'] ?? null,
                'ownership_type' => $row['ownership_type'] ?? 'company',
                'unit' => $row['unit'] ?? 'hr',
                'hourly_cost' => $row['hourly_cost'] ?? null,
                'daily_cost' => $row['daily_cost'] ?? null,
                'hourly_rate' => $row['hourly_rate'] ?? null,
                'daily_rate' => $row['daily_rate'] ?? null,
                'vendor_name' => $row['vendor_name'] ?? null,
                'model' => $row['model'] ?? null,
                'description' => $row['description'] ?? null,
                'is_active' => array_key_exists('is_active', $row) ? (bool)$row['is_active'] : true,
            ];

            $existing = EquipmentItem::where('name', $name)->first();

            if ($existing) {
                $existing->update($attrs);
                $updated++;
            } else {
                EquipmentItem::create(array_merge(['name' => $name], $attrs));
                $created++;
            }
        }

        return redirect()->route('equipment.index')->with('success', "Import complete. Created: $created, Updated: $updated, Skipped: $skipped");
    }

    public function create()
    {
        $assets = Asset::whereIn('type', [
            'skid_steer', 'excavator', 'mowers', 'dump_truck', 'crew_truck',
            'enclosed_trailer', 'dump_trailer', 'equipment_trailer'
        ])
        ->where('status', 'active')
        ->orderBy('name')
        ->get();

        // Get distinct categories and vendors for dropdowns
        $categories = EquipmentItem::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Get rental vendors from contacts with 'vendor' AND 'rental' tags
        $vendors = Contact::where('contact_type', 'vendor')
            ->whereHas('tags', function ($query) {
                $query->where('slug', 'vendor');
            })
            ->whereHas('tags', function ($query) {
                $query->where('slug', 'rental');
            })
            ->orderBy('company_name')
            ->orderBy('last_name')
            ->get();

        return view('equipment.create', compact('assets', 'categories', 'vendors'));
    }

    public function store(Request $request)
    {
        $data = $this->normalizeNumericFields($this->validateEquipment($request));
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $equipment = EquipmentItem::create($data);

        return redirect()
            ->route('equipment.index')
            ->with('success', 'Equipment created.');
    }

    public function edit(EquipmentItem $equipment)
    {
        $assets = Asset::whereIn('type', [
            'skid_steer', 'excavator', 'mowers', 'dump_truck', 'crew_truck',
            'enclosed_trailer', 'dump_trailer', 'equipment_trailer'
        ])
        ->where('status', 'active')
        ->orderBy('name')
        ->get();

        // Get distinct categories and vendors for dropdowns
        $categories = EquipmentItem::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Get rental vendors from contacts with 'vendor' AND 'rental' tags
        $vendors = Contact::where('contact_type', 'vendor')
            ->whereHas('tags', function ($query) {
                $query->where('slug', 'vendor');
            })
            ->whereHas('tags', function ($query) {
                $query->where('slug', 'rental');
            })
            ->orderBy('company_name')
            ->orderBy('last_name')
            ->get();

        return view('equipment.edit', compact('equipment', 'assets', 'categories', 'vendors'));
    }

    public function update(Request $request, EquipmentItem $equipment)
    {
        $data = $this->normalizeNumericFields($this->validateEquipment($request, $equipment->id));
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $equipment->update($data);

        return redirect()
            ->route('equipment.index')
            ->with('success', 'Equipment updated.');
    }

    public function destroy(EquipmentItem $equipment)
    {
        $equipment->delete();

        return redirect()
            ->route('equipment.index')
            ->with('success', 'Equipment deleted.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:equipment_catalog,id'],
            'action' => ['required', 'in:delete,set_active,set_inactive,set_category'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $ids = $data['ids'];
        $action = $data['action'];
        $count = 0;

        if ($action === 'delete') {
            $count = EquipmentItem::whereIn('id', $ids)->delete();
            return back()->with('success', "Deleted {$count} equipment item(s).");
        }

        if ($action === 'set_active') {
            $count = EquipmentItem::whereIn('id', $ids)->update(['is_active' => true]);
            return back()->with('success', "Activated {$count} equipment item(s).");
        }

        if ($action === 'set_inactive') {
            $count = EquipmentItem::whereIn('id', $ids)->update(['is_active' => false]);
            return back()->with('success', "Deactivated {$count} equipment item(s).");
        }

        if ($action === 'set_category' && !empty($data['category'])) {
            $cat = $data['category'];
            $count = EquipmentItem::whereIn('id', $ids)->update(['category' => $cat]);
            return back()->with('success', "Updated category for {$count} equipment item(s).");
        }

        return back()->with('success', 'No changes applied.');
    }

    protected function parseJsonEquipment(string $path): array
    {
        $content = file_get_contents($path);
        $json = json_decode($content, true);
        if (!is_array($json)) return [];
        return isset($json[0]) ? $json : [$json];
    }

    protected function parseCsvEquipment(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return [];
        $header = null;
        $rows = [];
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if (!$header) {
                $header = $data;
            } else {
                $row = array_combine($header, $data);
                if ($row) $rows[] = $row;
            }
        }
        fclose($handle);
        return $rows;
    }

    protected function validateEquipment(Request $request, ?int $equipmentId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('equipment_catalog', 'sku')->ignore($equipmentId),
            ],
            'category' => ['nullable', 'string', 'max:255'],
            'ownership_type' => ['required', 'in:company,rental'],
            'unit' => ['required', 'string', 'in:hr,day'],
            'hourly_cost' => ['nullable', 'numeric', 'min:0'],
            'daily_cost' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'breakeven' => ['nullable', 'numeric', 'min:0'],
            'profit_percent' => ['nullable', 'numeric', 'min:0'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
        ]);
    }

    protected function normalizeNumericFields(array $data): array
    {
        $numericFields = ['hourly_cost', 'daily_cost', 'hourly_rate', 'daily_rate', 'breakeven', 'profit_percent'];
        foreach ($numericFields as $field) {
            if (isset($data[$field])) {
                $val = $data[$field];
                if ($val === '' || $val === null) {
                    $data[$field] = null;
                } else {
                    $data[$field] = (float) $val;
                }
            }
        }
        return $data;
    }
}
