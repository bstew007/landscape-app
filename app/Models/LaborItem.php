<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
