<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobLaborItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_work_area_id',
        'estimate_item_id',
        'labor_item_id',
        'name',
        'description',
        'unit',
        'estimated_quantity',
        'estimated_hours',
        'estimated_rate',
        'estimated_cost',
        'actual_hours',
        'actual_cost',
        'sort_order',
    ];

    protected $casts = [
        'estimated_quantity' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'estimated_rate' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_hours' => 'decimal:2',
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

    public function laborItem(): BelongsTo
    {
        return $this->belongsTo(LaborItem::class);
    }

    // Computed attributes
    public function getVarianceHoursAttribute(): float
    {
        return $this->estimated_hours - $this->actual_hours;
    }

    public function getVarianceCostAttribute(): float
    {
        return $this->estimated_cost - $this->actual_cost;
    }
}
