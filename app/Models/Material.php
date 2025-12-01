<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $sku
 * @property string|null $category
 * @property string $unit
 * @property numeric $unit_cost
 * @property numeric $tax_rate
 * @property string|null $vendor_name
 * @property string|null $vendor_sku
 * @property string|null $description
 * @property bool $is_taxable
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $category_id
 * @property numeric|null $unit_price
 * @property numeric|null $breakeven
 * @property numeric|null $profit_percent
 * @property int|null $supplier_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialCategory> $categories
 * @property-read int|null $categories_count
 * @property-read string $supplier_name
 * @property-read \App\Models\MaterialCategory|null $materialCategory
 * @property-read \App\Models\Contact|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material bySupplier(int $supplierId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material searchByName(string $name)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material searchBySku(string $sku, bool $exact = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereBreakeven($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereIsTaxable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereProfitPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereVendorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereVendorSku($value)
 * @mixin \Eloquent
 */
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
