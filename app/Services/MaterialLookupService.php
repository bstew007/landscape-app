<?php

namespace App\Services;

use App\Models\Material;
use Illuminate\Support\Str;

/**
 * Looks up materials from catalog based on calculator material data
 */
class MaterialLookupService
{
    /**
     * Find a material in the catalog by name
     * 
     * @param string $name Material name from calculator
     * @param string|null $category Optional category hint
     * @return Material|null
     */
    public function findByName(string $name, ?string $category = null): ?Material
    {
        // Try exact match first
        $query = Material::where('is_active', true)
            ->where('name', $name);
        
        if ($category) {
            $query->where('category', $category);
        }
        
        $material = $query->first();
        if ($material) {
            return $material;
        }
        
        // Try fuzzy match
        $query = Material::where('is_active', true)
            ->where('name', 'LIKE', "%{$name}%");
            
        if ($category) {
            $query->where('category', $category);
        }
        
        return $query->first();
    }
    
    /**
     * Find material by SKU
     */
    public function findBySku(string $sku): ?Material
    {
        return Material::where('is_active', true)
            ->where('sku', $sku)
            ->first();
    }
    
    /**
     * Search for mulch materials by type
     * 
     * @param string $mulchType e.g., "Forest Brown", "Pine Needles", "Cedar"
     * @return Material|null
     */
    public function findMulch(string $mulchType): ?Material
    {
        $normalizedType = strtolower(trim($mulchType));
        
        // Common mulch mappings
        $mulchKeywords = [
            'forest brown' => ['forest brown', 'brown mulch', 'hardwood'],
            'black' => ['black mulch', 'black dyed'],
            'red' => ['red mulch', 'red dyed'],
            'cedar' => ['cedar', 'cedar chips'],
            'pine' => ['pine', 'pine straw', 'pine needle'],
        ];
        
        // Find matching keywords
        $searchTerms = [];
        foreach ($mulchKeywords as $key => $terms) {
            if (Str::contains($normalizedType, $key)) {
                $searchTerms = array_merge($searchTerms, $terms);
            }
        }
        
        // If no keywords matched, use the original name
        if (empty($searchTerms)) {
            $searchTerms = [$normalizedType];
        }
        
        // Search for materials matching these terms
        foreach ($searchTerms as $term) {
            $material = Material::where('is_active', true)
                ->where(function ($query) {
                    $query->where('category', 'LIKE', '%mulch%')
                          ->orWhere('category', 'LIKE', '%pine%');
                })
                ->where('name', 'LIKE', "%{$term}%")
                ->first();
                
            if ($material) {
                return $material;
            }
        }
        
        return null;
    }
    
    /**
     * Find plant material by common name or botanical name
     */
    public function findPlant(string $plantName): ?Material
    {
        return Material::where('is_active', true)
            ->where(function ($query) {
                $query->where('category', 'LIKE', '%plant%')
                      ->orWhere('category', 'LIKE', '%shrub%')
                      ->orWhere('category', 'LIKE', '%tree%')
                      ->orWhere('category', 'LIKE', '%annual%')
                      ->orWhere('category', 'LIKE', '%perennial%');
            })
            ->where('name', 'LIKE', "%{$plantName}%")
            ->first();
    }
    
    /**
     * Auto-detect material type and search catalog
     */
    public function autoLookup(string $materialName, string $calculatorType): ?Material
    {
        $normalizedName = strtolower(trim($materialName));
        
        // Calculator-specific lookups
        switch ($calculatorType) {
            case 'mulching':
            case 'pine_needles':
                return $this->findMulch($materialName);
                
            case 'planting':
                return $this->findPlant($materialName);
                
            default:
                // Generic search
                return $this->findByName($materialName);
        }
    }
}
