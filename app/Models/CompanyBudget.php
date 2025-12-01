<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int|null $year
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $effective_from
 * @property numeric $desired_profit_margin
 * @property array<array-key, mixed>|null $inputs
 * @property array<array-key, mixed>|null $outputs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereDesiredProfitMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereEffectiveFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereInputs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereOutputs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyBudget whereYear($value)
 * @mixin \Eloquent
 */
class CompanyBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'is_active',
        'effective_from',
        'desired_profit_margin',
        'inputs',
        'outputs',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'desired_profit_margin' => 'decimal:4',
        'inputs' => 'array',
        'outputs' => 'array',
    ];
}
