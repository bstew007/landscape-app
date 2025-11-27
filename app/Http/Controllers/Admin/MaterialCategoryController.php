<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;

class MaterialCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $categories = MaterialCategory::query()
            ->when($search, function ($query, $term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.material-categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('admin.material-categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:material_categories,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        MaterialCategory::create($data);

        return redirect()->route('admin.material-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(MaterialCategory $materialCategory)
    {
        return view('admin.material-categories.edit', compact('materialCategory'));
    }

    public function update(Request $request, MaterialCategory $materialCategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:material_categories,name,' . $materialCategory->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $materialCategory->update($data);

        return redirect()->route('admin.material-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(MaterialCategory $materialCategory)
    {
        // Check if category has materials
        $materialCount = $materialCategory->materials()->count();
        
        if ($materialCount > 0) {
            return back()->with('error', "Cannot delete category '{$materialCategory->name}' because it has {$materialCount} materials. Please reassign or delete those materials first.");
        }

        $materialCategory->delete();

        return redirect()->route('admin.material-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
