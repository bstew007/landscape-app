<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');

        $categories = MaterialCategory::orderBy('name', 'asc')->pluck('name');

        $materials = Material::query()
            ->with('categories')
            ->when($search, function ($query, $term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%");
            })
            ->when($category, function ($query, $cat) {
                if ($cat === '_none') {
                    $query->where(function ($q) {
                        $q->whereNull('category')->orWhere('category', '');
                    })->whereDoesntHave('categories');
                } else {
                    $query->where(function ($q) use ($cat) {
                        $q->where('category', $cat)
                          ->orWhereHas('categories', function ($subq) use ($cat) {
                              $subq->where('name', $cat);
                          });
                    });
                }
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('materials.index', compact('materials', 'search', 'categories', 'category'));
    }

    public function importForm()
    {
        return view('materials.import');
    }

    public function export(Request $request)
    {
        $filename = 'materials-' . now()->format('Ymd-His') . '.csv';
        $materials = \App\Models\Material::orderBy('name')->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $columns = ['name','sku','category','unit','unit_cost','tax_rate','vendor_name','vendor_sku','description','is_taxable','is_active'];

        $callback = function () use ($materials, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($materials as $m) {
                fputcsv($out, [
                    $m->name,
                    $m->sku,
                    $m->category,
                    $m->unit,
                    $m->unit_cost,
                    $m->tax_rate,
                    $m->vendor_name,
                    $m->vendor_sku,
                    $m->description,
                    $m->is_taxable ? 'true' : 'false',
                    $m->is_active ? 'true' : 'false',
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
        $rows = [];
        if ($ext === 'csv') {
            $rows = $this->parseCsvMaterials($path);
        } else {
            $json = file_get_contents($path);
            $rows = json_decode($json, true) ?: [];
        }
        if (!is_array($rows)) {
            return back()->withErrors(['file' => 'Invalid file structure. Expecting an array of materials.']);
        }

        $created = 0; $updated = 0; $skipped = 0;
        foreach ($rows as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') { $skipped++; continue; }

            $attrs = [
                'category' => $row['category'] ?? null,
                'unit' => $row['unit'] ?? 'ea',
                'unit_cost' => $this->normalizeNumber($row['unit_cost'] ?? 0),
                'tax_rate' => $this->normalizeNumber($row['tax_rate'] ?? 0),
                'vendor_name' => $row['vendor_name'] ?? null,
                'vendor_sku' => $row['vendor_sku'] ?? null,
                'description' => $row['description'] ?? null,
                'is_taxable' => array_key_exists('is_taxable', $row) ? (bool)$row['is_taxable'] : true,
                'is_active' => array_key_exists('is_active', $row) ? (bool)$row['is_active'] : true,
            ];

            $existing = \App\Models\Material::query()
                ->when(($row['sku'] ?? null), fn($q) => $q->where('sku', $row['sku']))
                ->orWhere('name', $name)
                ->first();

            if ($existing) {
                $existing->update($attrs);
                $updated++;
            } else {
                \App\Models\Material::create(array_merge(['name' => $name, 'sku' => $row['sku'] ?? null], $attrs));
                $created++;
            }
        }

        return redirect()->route('materials.index')->with('success', "Import complete. Created: $created, Updated: $updated, Skipped: $skipped");
    }

    protected function parseCsvMaterials(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return [];
        $header = null; $rows = [];
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if ($header === null) {
                $header = array_map(fn($h) => strtolower(trim($h)), $data);
                continue;
            }
            if (count(array_filter($data, fn($v) => $v !== null && $v !== '')) === 0) continue;
            $row = [];
            foreach ($data as $i => $value) {
                $key = $header[$i] ?? ('col'.$i);
                $row[$key] = $value;
            }
            // Map common column aliases
            $map = [
                'name' => ['name','material','item','material name','item name','material_name','item_name'],
                'sku' => ['sku','code'],
                'category' => ['category','cat'],
                'unit' => ['unit','uom'],
                'unit_cost' => ['unit_cost','cost','price'],
                'tax_rate' => ['tax_rate','tax'],
                'vendor_name' => ['vendor_name','vendor'],
                'vendor_sku' => ['vendor_sku','vendor_code'],
                'description' => ['description','desc'],
                'is_taxable' => ['is_taxable','taxable'],
                'is_active' => ['is_active','active'],
            ];
            $normalized = [];
            foreach ($map as $canonical => $aliases) {
                foreach ($aliases as $alias) {
                    if (array_key_exists($alias, $row)) { $normalized[$canonical] = $row[$alias]; break; }
                }
            }
            // Casts
            if (isset($normalized['unit_cost'])) $normalized['unit_cost'] = $this->normalizeNumber($normalized['unit_cost']);
            if (isset($normalized['tax_rate'])) $normalized['tax_rate'] = $this->normalizeNumber($normalized['tax_rate']);
            if (isset($normalized['is_taxable'])) $normalized['is_taxable'] = filter_var($normalized['is_taxable'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
            if (isset($normalized['is_active'])) $normalized['is_active'] = filter_var($normalized['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
            // Ensure a name by falling back to the first non-empty column if needed
            $fallbackName = $row['name'] ?? ($row['material'] ?? ($row['item'] ?? ''));
            if (empty($fallbackName)) {
                foreach ($row as $val) {
                    if (is_string($val) && trim($val) !== '') { $fallbackName = $val; break; }
                }
            }
            $rows[] = $normalized + ['name' => $fallbackName];
        }
        fclose($handle);
        return $rows;
    }

    protected function normalizeNumber(mixed $value): float
    {
        if (is_numeric($value)) return (float) $value;
        if (is_string($value)) {
            // Strip currency symbols, percent signs, commas, and whitespace
            $clean = preg_replace('/[^0-9.\-]/', '', $value);
            if ($clean === '' || $clean === null) return 0.0;
            return (float) $clean;
        }
        return 0.0;
    }

    public function create()
    {
        $vendors = Contact::where('contact_type', 'vendor')
            ->orderBy('company_name')
            ->orderBy('last_name')
            ->get();
        
        return view('materials.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $data = $this->validateMaterial($request);
        $data['is_taxable'] = (bool) ($data['is_taxable'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        
        // Sync vendor_name with supplier for backward compatibility
        if (!empty($data['supplier_id'])) {
            $supplier = Contact::find($data['supplier_id']);
            if ($supplier) {
                $data['vendor_name'] = $supplier->company_name ?: ($supplier->first_name . ' ' . $supplier->last_name);
            }
        }
        
        $material = Material::create($data);

        // Sync categories
        if ($request->has('categories')) {
            $material->categories()->sync($request->input('categories', []));
        }

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material created.');
    }

    public function edit(Material $material)
    {
        $vendors = Contact::where('contact_type', 'vendor')
            ->orderBy('company_name')
            ->orderBy('last_name')
            ->get();
        
        return view('materials.edit', compact('material', 'vendors'));
    }

    public function update(Request $request, Material $material)
    {
        $data = $this->validateMaterial($request, $material->id);
        $data['is_taxable'] = (bool) ($data['is_taxable'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        
        // Sync vendor_name with supplier for backward compatibility
        if (!empty($data['supplier_id'])) {
            $supplier = Contact::find($data['supplier_id']);
            if ($supplier) {
                $data['vendor_name'] = $supplier->company_name ?: ($supplier->first_name . ' ' . $supplier->last_name);
            }
        } elseif (array_key_exists('supplier_id', $data) && is_null($data['supplier_id'])) {
            // If supplier was cleared, clear vendor_name too
            $data['vendor_name'] = null;
        }
        
        $material->update($data);

        // Sync categories
        if ($request->has('categories')) {
            $material->categories()->sync($request->input('categories', []));
        }

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material updated.');
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material deleted.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer','exists:materials,id'],
            'action' => ['required','in:delete,set_active,set_inactive,set_category'],
            'category' => ['nullable','string','max:255'],
        ]);

        $ids = $data['ids'];
        $action = $data['action'];
        $count = 0;

        if ($action === 'delete') {
            $count = Material::whereIn('id', $ids)->delete();
            return back()->with('success', "Deleted {$count} material(s).");
        }

        if ($action === 'set_active') {
            $count = Material::whereIn('id', $ids)->update(['is_active' => true]);
            return back()->with('success', "Updated {$count} material(s) to Active.");
        }

        if ($action === 'set_inactive') {
            $count = Material::whereIn('id', $ids)->update(['is_active' => false]);
            return back()->with('success', "Updated {$count} material(s) to Inactive.");
        }

        if ($action === 'set_category') {
            $cat = $data['category'] ?? null;
            if ($cat === '_none') {
                $materials = Material::whereIn('id', $ids)->get();
                foreach ($materials as $material) {
                    $material->category = null;
                    $material->save();
                    $material->categories()->detach();
                }
                return back()->with('success', "Cleared categories for {$materials->count()} material(s).");
            }

            if (!$cat) return back()->withErrors(['category' => 'Category is required for this action.']);
            // Ensure category exists (case-insensitive)
            $existing = \App\Models\MaterialCategory::whereRaw('LOWER(name) = ?', [strtolower($cat)])->first();
            $categoryModel = $existing ?? \App\Models\MaterialCategory::create([
                'name' => $cat,
                'is_active' => true,
                'sort_order' => \App\Models\MaterialCategory::max('sort_order') + 1,
            ]);

            $materials = Material::whereIn('id', $ids)->get();
            foreach ($materials as $material) {
                $material->category = $cat;
                $material->save();
                $material->categories()->sync([$categoryModel->id]);
            }
            return back()->with('success', "Updated category for {$materials->count()} material(s).");
        }

        return back()->with('success', 'No changes applied.');
    }

    protected function validateMaterial(Request $request, ?int $materialId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('materials', 'sku')->ignore($materialId),
            ],
            'category' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'breakeven' => ['nullable', 'numeric', 'min:0'],
            'profit_percent' => ['nullable', 'numeric'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'integer', 'exists:clients,id'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'vendor_sku' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_taxable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
