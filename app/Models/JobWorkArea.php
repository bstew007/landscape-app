<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobWorkArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'estimate_area_id',
        'name',
        'description',
        'estimated_labor_hours',
        'estimated_labor_cost',
        'estimated_material_cost',
        'actual_labor_hours',
        'actual_labor_cost',
        'actual_material_cost',
        'status',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'estimated_labor_hours' => 'decimal:2',
        'estimated_labor_cost' => 'decimal:2',
        'estimated_material_cost' => 'decimal:2',
        'actual_labor_hours' => 'decimal:2',
        'actual_labor_cost' => 'decimal:2',
        'actual_material_cost' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function estimateArea(): BelongsTo
    {
        return $this->belongsTo(EstimateArea::class);
    }

    public function laborItems(): HasMany
    {
        return $this->hasMany(JobLaborItem::class)->orderBy('sort_order');
    }

    public function materialItems(): HasMany
    {
        return $this->hasMany(JobMaterialItem::class)->orderBy('sort_order');
    }

    // Computed attributes
    public function getEstimatedTotalCostAttribute(): float
    {
        return $this->estimated_labor_cost + $this->estimated_material_cost;
    }

    public function getActualTotalCostAttribute(): float
    {
        return $this->actual_labor_cost + $this->actual_material_cost;
    }

    public function getVarianceTotalAttribute(): float
    {
        return $this->estimated_total_cost - $this->actual_total_cost;
    }

    public function getVariancePercentAttribute(): float
    {
        if ($this->estimated_total_cost == 0) {
            return 0;
        }
        
        return (($this->estimated_total_cost - $this->actual_total_cost) / $this->estimated_total_cost) * 100;
    }
}
