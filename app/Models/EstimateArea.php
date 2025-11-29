<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id', 
        'name', 
        'identifier', 
        'cost_code_id', 
        'description', 
        'sort_order',
        'custom_price_override',
        'custom_profit_override',
        'price_distribution_method',
        'override_applied_at',
        'override_applied_by',
        // Calculator metadata fields
        'calculation_id',
        'site_visit_id',
        'planned_hours',
        'crew_size',
        'drive_time_hours',
        'overhead_percent',
        'calculator_metadata',
    ];

    protected $casts = [
        'custom_price_override' => 'decimal:2',
        'custom_profit_override' => 'decimal:2',
        'override_applied_at' => 'datetime',
        // Calculator metadata casts
        'planned_hours' => 'decimal:2',
        'drive_time_hours' => 'decimal:2',
        'overhead_percent' => 'decimal:2',
        'calculator_metadata' => 'array',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function items()
    {
        return $this->hasMany(EstimateItem::class, 'area_id');
    }

    public function overrideAppliedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'override_applied_by');
    }

    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }

    public function siteVisit()
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function hasCustomPricing(): bool
    {
        return $this->custom_price_override !== null || $this->custom_profit_override !== null;
    }

    public function clearCustomPricing(): void
    {
        $this->update([
            'custom_price_override' => null,
            'custom_profit_override' => null,
            'price_distribution_method' => null,
            'override_applied_at' => null,
            'override_applied_by' => null,
        ]);
    }

    /**
     * Check if this area was created from a calculator
     */
    public function isFromCalculator(): bool
    {
        return $this->calculation_id !== null;
    }

    /**
     * Get calculator type if from calculator
     */
    public function getCalculatorType(): ?string
    {
        return $this->calculation?->calculation_type;
    }

    /**
     * Get calculator parameters/settings
     */
    public function getCalculatorSettings(): array
    {
        return $this->calculator_metadata ?? [];
    }

    protected static function booted()
    {
        // When an area is deleted, delete all associated items
        static::deleting(function ($area) {
            $area->items()->delete();
        });
    }
}
