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
        'overtime_rate',
        'burden_percentage',
        'is_billable',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'burden_percentage' => 'decimal:2',
        'is_billable' => 'boolean',
        'is_active' => 'boolean',
        'cost_code_id' => 'integer',
    ];
}
