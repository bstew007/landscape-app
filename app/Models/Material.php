<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'category_id',
        'supplier_id',
        'unit',
        'unit_cost',
        'unit_price',
        'breakeven',
        'profit_percent',
        'tax_rate',
        'vendor_name',
        'vendor_sku',
        'description',
        'is_taxable',
        'is_active',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'breakeven' => 'decimal:2',
        'profit_percent' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
        'category_id' => 'integer',
    ];

    public function materialCategory()
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Contact::class, 'supplier_id');
    }

    public function categories()
    {
        return $this->belongsToMany(MaterialCategory::class, 'material_material_category');
    }

    /**
     * Scope: Search materials by name (fuzzy).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByName($query, string $name)
    {
        return $query->where('name', 'LIKE', '%' . $name . '%');
    }

    /**
     * Scope: Search materials by SKU (exact or fuzzy).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sku
     * @param bool $exact
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchBySku($query, string $sku, bool $exact = false)
    {
        if ($exact) {
            return $query->where('sku', $sku);
        }
        
        return $query->where('sku', 'LIKE', '%' . $sku . '%');
    }

    /**
     * Scope: Filter by supplier.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $supplierId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope: Only active materials.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper: Check if material has a supplier assigned.
     *
     * @return bool
     */
    public function hasSupplier(): bool
    {
        return !is_null($this->supplier_id);
    }

    /**
     * Helper: Get supplier name or default text.
     *
     * @return string
     */
    public function getSupplierNameAttribute(): string
    {
        return $this->supplier?->name ?? 'No Supplier';
    }
}
