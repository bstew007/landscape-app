<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'cost_code_id',
        'unit',
        'unit_cost',
        'tax_rate',
        'vendor_name',
        'vendor_sku',
        'description',
        'is_taxable',
        'is_active',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
        'cost_code_id' => 'integer',
    ];
}
