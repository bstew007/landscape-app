<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
