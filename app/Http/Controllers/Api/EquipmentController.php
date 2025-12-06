<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EquipmentItem;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Get active equipment for catalog picker
     */
    public function active(Request $request)
    {
        $query = EquipmentItem::where('is_active', true);

        // Optional category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Optional ownership filter
        if ($request->has('ownership_type')) {
            $query->where('ownership_type', $request->ownership_type);
        }

        // Optional search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%");
            });
        }

        $equipment = $query->orderBy('category')
                          ->orderBy('name')
                          ->get([
                              'id',
                              'name',
                              'sku',
                              'category',
                              'ownership_type',
                              'unit',
                              'hourly_cost',
                              'daily_cost',
                              'hourly_rate',
                              'daily_rate',
                              'model',
                              'description',
                              'vendor_name'
                          ]);

        return response()->json([
            'success' => true,
            'equipment' => $equipment,
            'count' => $equipment->count(),
        ]);
    }

    /**
     * Get equipment by ID
     */
    public function show(EquipmentItem $equipment)
    {
        return response()->json([
            'success' => true,
            'equipment' => $equipment,
        ]);
    }

    /**
     * Search equipment by name (for autocomplete)
     */
    public function search(Request $request)
    {
        $search = $request->input('q', '');
        $category = $request->input('category');
        $ownership = $request->input('ownership_type');

        $query = EquipmentItem::where('is_active', true)
                        ->where('name', 'LIKE', "%{$search}%");

        if ($category) {
            $query->where('category', 'LIKE', "%{$category}%");
        }

        if ($ownership) {
            $query->where('ownership_type', $ownership);
        }

        $equipment = $query->orderBy('name')
                          ->limit(20)
                          ->get([
                              'id',
                              'name',
                              'sku',
                              'category',
                              'ownership_type',
                              'unit',
                              'hourly_rate',
                              'daily_rate',
                          ]);

        return response()->json([
            'success' => true,
            'equipment' => $equipment,
        ]);
    }
}
