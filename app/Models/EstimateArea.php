<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $estimate_id
 * @property string $name
 * @property string|null $description
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $identifier
 * @property int|null $cost_code_id
 * @property numeric|null $custom_price_override
 * @property numeric|null $custom_profit_override
 * @property string|null $price_distribution_method
 * @property \Illuminate\Support\Carbon|null $override_applied_at
 * @property int|null $override_applied_by
 * @property int|null $calculation_id
 * @property int|null $site_visit_id
 * @property numeric|null $planned_hours
 * @property int|null $crew_size
 * @property numeric|null $drive_time_hours
 * @property numeric|null $overhead_percent
 * @property array<array-key, mixed>|null $calculator_metadata
 * @property-read \App\Models\Calculation|null $calculation
 * @property-read \App\Models\Estimate $estimate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $overrideAppliedBy
 * @property-read \App\Models\SiteVisit|null $siteVisit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereCalculationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereCalculatorMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereCostCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereCrewSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereCustomPriceOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereCustomProfitOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereDriveTimeHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereOverheadPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereOverrideAppliedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereOverrideAppliedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea wherePlannedHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea wherePriceDistributionMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereSiteVisitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateArea whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
