<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $unit
 * @property numeric $base_rate
 * @property numeric|null $overtime_rate
 * @property numeric $burden_percentage
 * @property bool $is_billable
 * @property bool $is_active
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $cost_code_id
 * @property string|null $description
 * @property string|null $internal_notes
 * @property numeric|null $average_wage
 * @property numeric|null $overtime_factor
 * @property numeric $unbillable_percentage
 * @property numeric $labor_burden_percentage
 * @property numeric|null $breakeven
 * @property numeric|null $profit_percent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereAverageWage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereBaseRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereBreakeven($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereBurdenPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereCostCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereIsBillable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereLaborBurdenPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereOvertimeFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereOvertimeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereProfitPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereUnbillablePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaborItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LaborItem extends Model
{
    use HasFactory;

    protected $table = 'labor_catalog';

    protected $fillable = [
        'name',
        'type',
        'cost_code_id',
        'unit',
        'base_rate',
        'breakeven',
        'profit_percent',
        'overtime_rate',
        'burden_percentage', // legacy
        'labor_burden_percentage',
        'unbillable_percentage',
        'average_wage',
        'overtime_factor',
        'is_billable',
        'is_active',
        'description',
        'notes',
        'internal_notes',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'breakeven' => 'decimal:2',
        'profit_percent' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'burden_percentage' => 'decimal:2',
        'labor_burden_percentage' => 'decimal:2',
        'unbillable_percentage' => 'decimal:2',
        'average_wage' => 'decimal:2',
        'overtime_factor' => 'decimal:2',
        'is_billable' => 'boolean',
        'is_active' => 'boolean',
        'cost_code_id' => 'integer',
    ];
}
