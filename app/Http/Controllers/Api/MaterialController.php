<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Get active materials for catalog picker
     */
    public function active(Request $request)
    {
        $query = Material::where('is_active', true);
        
        // Optional category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Optional search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        $materials = $query->orderBy('category')
                          ->orderBy('name')
                          ->get([
                              'id',
                              'name',
                              'sku',
                              'category',
                              'unit',
                              'unit_cost',
                              'unit_price',
                              'description',
                              'vendor_name',
                              'is_taxable',
                              'tax_rate'
                          ]);
        
        return response()->json([
            'success' => true,
            'materials' => $materials,
            'count' => $materials->count(),
        ]);
    }
    
    /**
     * Get material by ID
     */
    public function show(Material $material)
    {
        return response()->json([
            'success' => true,
            'material' => $material,
        ]);
    }
    
    /**
     * Search materials by name (for autocomplete)
     */
    public function search(Request $request)
    {
        $search = $request->input('q', '');
        $category = $request->input('category');
        
        $query = Material::where('is_active', true)
                        ->where('name', 'LIKE', "%{$search}%");
        
        if ($category) {
            $query->where('category', 'LIKE', "%{$category}%");
        }
        
        $materials = $query->limit(20)
                          ->get(['id', 'name', 'sku', 'category', 'unit', 'unit_cost']);
        
        return response()->json([
            'success' => true,
            'materials' => $materials,
        ]);
    }
}
