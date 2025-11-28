<?php

namespace App\Models;

use App\Models\Calculation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Get the catalog item based on catalog_type.
     * This is an accessor, not a relationship.
     */
    public function getCatalogAttribute()
    {
        if ($this->catalog_type === 'material') {
            return $this->material;
        } elseif ($this->catalog_type === 'labor') {
            return $this->laborItem;
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
