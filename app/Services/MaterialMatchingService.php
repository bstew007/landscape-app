<?php

namespace App\Services;

use App\Models\Material;
use App\Models\EstimateItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Service for matching estimate items with catalog materials.
 * Uses fuzzy matching algorithms to find the best material match based on name, SKU, and description.
 */
class MaterialMatchingService
{
    /**
     * Minimum match score threshold (0-100) to consider a match valid.
     */
    protected int $minMatchScore = 70;

    /**
     * Find the best matching material for an estimate item.
     *
     * @param EstimateItem $item
     * @return array|null ['material' => Material, 'score' => int, 'confidence' => string] or null
     */
    public function findBestMatch(EstimateItem $item): ?array
    {
        // If already has a catalog material, return it with 100% confidence
        if ($item->catalog_type === 'material' && $item->catalog_id && $item->material) {
            return [
                'material' => $item->material,
                'score' => 100,
                'confidence' => 'exact',
            ];
        }

        // Only match material-type items
        if ($item->item_type !== 'material') {
            return null;
        }

        // Get all active materials
        $materials = Material::where('is_active', true)->get();

        if ($materials->isEmpty()) {
            return null;
        }

        // Calculate match scores for all materials
        $matches = $materials->map(function ($material) use ($item) {
            return [
                'material' => $material,
                'score' => $this->calculateMatchScore($item, $material),
            ];
        });

        // Sort by score descending and get the best match
        $bestMatch = $matches->sortByDesc('score')->first();

        // Only return if score meets minimum threshold
        if ($bestMatch['score'] >= $this->minMatchScore) {
            return [
                'material' => $bestMatch['material'],
                'score' => $bestMatch['score'],
                'confidence' => $this->getConfidenceLevel($bestMatch['score']),
            ];
        }

        return null;
    }

    /**
     * Calculate match score between an estimate item and a catalog material.
     * Prioritizes SKU/vendor SKU matches over name similarity.
     *
     * @param EstimateItem $item
     * @param Material $material
     * @return int Score from 0-100
     */
    public function calculateMatchScore(EstimateItem $item, Material $material): int
    {
        $scores = [];
        
        // Extract SKU from item metadata or name
        $itemSku = $this->extractSku($item);
        $itemVendorSku = $item->metadata['vendor_sku'] ?? null;

        // PRIORITY 1: Exact SKU match = 100 points (most reliable)
        if (!empty($itemSku) && !empty($material->sku)) {
            if ($this->normalizeString($itemSku) === $this->normalizeString($material->sku)) {
                return 100;
            }
        }

        // PRIORITY 2: Exact vendor SKU match = 100 points (also very reliable)
        if (!empty($itemVendorSku) && !empty($material->vendor_sku)) {
            if ($this->normalizeString($itemVendorSku) === $this->normalizeString($material->vendor_sku)) {
                return 100;
            }
        }

        $itemName = $this->normalizeString($item->name);
        $materialName = $this->normalizeString($material->name);

        // PRIORITY 3: Exact name match = 100 points
        if ($itemName === $materialName) {
            return 100;
        }

        // Now calculate fuzzy matching scores

        // Check if one name contains the other (partial substring match)
        if (!empty($itemName) && !empty($materialName)) {
            if (str_contains($materialName, $itemName) || str_contains($itemName, $materialName)) {
                // If one is contained in the other, give a high base score
                $scores['substring'] = 30;
            }
        }

        // SKU similarity (weighted: 35% - increased importance)
        if (!empty($itemSku) && !empty($material->sku)) {
            $skuScore = $this->stringSimilarity($itemSku, $material->sku);
            $scores['sku'] = $skuScore * 0.35;
        }

        // Vendor SKU similarity (weighted: 25%)
        if (!empty($itemVendorSku) && !empty($material->vendor_sku)) {
            $vendorSkuScore = $this->stringSimilarity($itemVendorSku, $material->vendor_sku);
            $scores['vendor_sku'] = $vendorSkuScore * 0.25;
        }

        // Name similarity (weighted: 40% - reduced since SKU is more reliable)
        $nameScore = $this->stringSimilarity($item->name, $material->name);
        $scores['name'] = $nameScore * 0.4;

        // Description/keywords match (weighted: 10%)
        if (!empty($item->description) && !empty($material->description)) {
            $descScore = $this->stringSimilarity($item->description, $material->description);
            $scores['description'] = $descScore * 0.1;
        }

        // Keyword matching for common terms
        $keywordBonus = $this->calculateKeywordBonus($item, $material);
        if ($keywordBonus > 0) {
            $scores['keywords'] = $keywordBonus;
        }

        $totalScore = (int) round(array_sum($scores));
        
        // Cap at 100
        $totalScore = min($totalScore, 100);
        
        // Debug logging (can be removed in production)
        if (config('app.debug')) {
            \Log::debug("Match Score Details", [
                'item_name' => $item->name,
                'item_sku' => $itemSku,
                'item_vendor_sku' => $itemVendorSku,
                'material_name' => $material->name,
                'material_sku' => $material->sku,
                'material_vendor_sku' => $material->vendor_sku,
                'scores' => $scores,
                'total' => $totalScore,
            ]);
        }

        return $totalScore;
    }

    /**
     * Extract SKU from item metadata or parse from name.
     *
     * @param EstimateItem $item
     * @return string|null
     */
    protected function extractSku(EstimateItem $item): ?string
    {
        // First check metadata
        if (!empty($item->metadata['sku'])) {
            return $item->metadata['sku'];
        }

        // Try to extract SKU-like patterns from name (e.g., "MULCH-001" or "SKU: ABC123")
        if (preg_match('/\b([A-Z0-9]{3,}-[A-Z0-9]+)\b/i', $item->name, $matches)) {
            return $matches[1];
        }

        // Check for "SKU:" prefix in name
        if (preg_match('/SKU:\s*([A-Z0-9\-]+)/i', $item->name, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get suggested matches for an estimate item.
     *
     * @param EstimateItem $item
     * @param int $limit Number of suggestions to return
     * @return Collection Collection of ['material' => Material, 'score' => int, 'confidence' => string]
     */
    public function suggestMatches(EstimateItem $item, int $limit = 5): Collection
    {
        // Only match material-type items
        if ($item->item_type !== 'material') {
            return collect();
        }

        // Get all active materials
        $materials = Material::where('is_active', true)->get();

        if ($materials->isEmpty()) {
            return collect();
        }

        // Calculate match scores for all materials
        $matches = $materials->map(function ($material) use ($item) {
            $score = $this->calculateMatchScore($item, $material);
            
            return [
                'material' => $material,
                'score' => $score,
                'confidence' => $this->getConfidenceLevel($score),
            ];
        });

        // Filter by minimum score and sort by score descending
        return $matches
            ->filter(fn($match) => $match['score'] >= $this->minMatchScore)
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /**
     * Calculate string similarity percentage using Levenshtein distance.
     *
     * @param string $str1
     * @param string $str2
     * @return float Similarity from 0-100
     */
    protected function stringSimilarity(?string $str1, ?string $str2): float
    {
        if (empty($str1) || empty($str2)) {
            return 0;
        }

        $str1 = $this->normalizeString($str1);
        $str2 = $this->normalizeString($str2);

        // Use similar_text for better matching
        similar_text($str1, $str2, $percent);

        return $percent;
    }

    /**
     * Normalize a string for comparison (lowercase, trim, remove extra spaces).
     *
     * @param string|null $str
     * @return string
     */
    protected function normalizeString(?string $str): string
    {
        if (empty($str)) {
            return '';
        }

        // Convert to lowercase
        $str = Str::lower($str);
        
        // Remove special characters except spaces and numbers
        $str = preg_replace('/[^a-z0-9\s]/', ' ', $str);
        
        // Replace multiple spaces with single space
        $str = preg_replace('/\s+/', ' ', $str);
        
        return trim($str);
    }

    /**
     * Calculate bonus points for matching keywords.
     *
     * @param EstimateItem $item
     * @param Material $material
     * @return int Bonus points (0-10)
     */
    protected function calculateKeywordBonus(EstimateItem $item, Material $material): int
    {
        $bonus = 0;

        // Extract keywords from item name and description
        $itemText = $this->normalizeString($item->name . ' ' . ($item->description ?? ''));
        $materialText = $this->normalizeString($material->name . ' ' . ($material->description ?? ''));

        // Common important keywords in landscaping materials
        $importantKeywords = [
            'mulch', 'soil', 'stone', 'gravel', 'sand', 'paver', 'brick',
            'sod', 'seed', 'fertilizer', 'topsoil', 'compost', 'edging',
            'block', 'wall', 'timber', 'post', 'concrete', 'asphalt',
        ];

        foreach ($importantKeywords as $keyword) {
            if (Str::contains($itemText, $keyword) && Str::contains($materialText, $keyword)) {
                $bonus += 2;
            }
        }

        return min($bonus, 10); // Cap at 10 bonus points
    }

    /**
     * Get confidence level description based on match score.
     *
     * @param int $score
     * @return string
     */
    protected function getConfidenceLevel(int $score): string
    {
        if ($score >= 95) {
            return 'exact';
        } elseif ($score >= 85) {
            return 'high';
        } elseif ($score >= 75) {
            return 'medium';
        } elseif ($score >= 60) {
            return 'low';
        }

        return 'very_low';
    }

    /**
     * Set the minimum match score threshold.
     *
     * @param int $score
     * @return self
     */
    public function setMinMatchScore(int $score): self
    {
        $this->minMatchScore = max(0, min(100, $score));
        return $this;
    }

    /**
     * Batch match multiple estimate items.
     *
     * @param Collection $items Collection of EstimateItem models
     * @return Collection Collection of arrays with 'item', 'match', 'material', 'score', 'confidence'
     */
    public function batchMatch(Collection $items): Collection
    {
        return $items->map(function ($item) {
            $match = $this->findBestMatch($item);
            
            return [
                'item' => $item,
                'match' => $match !== null,
                'material' => $match['material'] ?? null,
                'score' => $match['score'] ?? 0,
                'confidence' => $match['confidence'] ?? 'none',
            ];
        });
    }
}
