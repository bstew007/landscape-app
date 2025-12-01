<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobMaterialItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_work_area_id',
        'estimate_item_id',
        'material_id',
        'name',
        'description',
        'unit',
        'estimated_quantity',
        'estimated_unit_cost',
        'estimated_cost',
        'actual_quantity',
        'actual_unit_cost',
        'actual_cost',
        'sort_order',
    ];

    protected $casts = [
        'estimated_quantity' => 'decimal:2',
        'estimated_unit_cost' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'actual_unit_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    // Relationships
    public function workArea(): BelongsTo
    {
        return $this->belongsTo(JobWorkArea::class, 'job_work_area_id');
    }

    public function estimateItem(): BelongsTo
    {
        return $this->belongsTo(EstimateItem::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    // Computed attributes
    public function getVarianceQuantityAttribute(): float
    {
        return $this->estimated_quantity - $this->actual_quantity;
    }

    public function getVarianceCostAttribute(): float
    {
        return $this->estimated_cost - $this->actual_cost;
    }
}
