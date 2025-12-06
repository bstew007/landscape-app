<?php

namespace App\Models;

use App\Models\Calculation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $estimate_id
 * @property string $item_type
 * @property string|null $catalog_type
 * @property int|null $catalog_id
 * @property string $name
 * @property string|null $description
 * @property string|null $unit
 * @property numeric $quantity
 * @property numeric $unit_cost
 * @property numeric $tax_rate
 * @property numeric $line_total
 * @property string|null $source
 * @property int $sort_order
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $calculation_id
 * @property numeric $unit_price
 * @property numeric $margin_rate
 * @property numeric $cost_total
 * @property numeric $margin_total
 * @property int|null $area_id
 * @property-read \App\Models\EstimateArea|null $area
 * @property-read Calculation|null $calculation
 * @property-read \App\Models\Estimate $estimate
 * @property-read mixed $catalog
 * @property-read \App\Models\LaborItem|null $laborItem
 * @property-read \App\Models\Material|null $material
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereCalculationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereCatalogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereCatalogType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereCostTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereItemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereMarginTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EstimateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'calculation_id',
        'item_type',
        'catalog_type',
        'catalog_id',
        'area_id',
        'name',
        'description',
        'unit',
        'quantity',
        'unit_cost',
        'unit_price',
        'margin_rate',
        'tax_rate',
        'line_total',
        'cost_total',
        'margin_total',
        'source',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'margin_rate' => 'decimal:4',
        'tax_rate' => 'decimal:4',
        'line_total' => 'decimal:2',
        'cost_total' => 'decimal:2',
        'margin_total' => 'decimal:2',
        'metadata' => 'array',
        'calculation_id' => 'integer',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }

    public function area()
    {
        return $this->belongsTo(EstimateArea::class, 'area_id');
    }

    /**
     * Get the material catalog item.
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'catalog_id');
    }

    /**
     * Get the labor catalog item.
     */
    public function laborItem()
    {
        return $this->belongsTo(LaborItem::class, 'catalog_id');
    }

    /**
     * Get the equipment catalog item.
     */
    public function equipmentItem()
    {
        return $this->belongsTo(\App\Models\EquipmentItem::class, 'catalog_id');
    }

    /**
     * Get the catalog item based on catalog_type.
     * This is an accessor, not a relationship.
     */
    public function getCatalogAttribute()
    {
        if ($this->catalog_type === 'material') {
            return $this->material;
        } elseif ($this->catalog_type === 'labor') {
            return $this->laborItem;
        } elseif ($this->catalog_type === 'equipment') {
            return $this->equipmentItem;
        }
        
        return null;
    }

    protected static function booted()
    {
        static::saved(function ($item) {
            if ($item->estimate) {
                $item->estimate->recalculate();
            }
        });

        static::deleted(function ($item) {
            if ($item->estimate) {
                $item->estimate->recalculate();
            }
        });
    }
}
