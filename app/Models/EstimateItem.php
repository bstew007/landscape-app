<?php

namespace App\Models;

use App\Models\Calculation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'calculation_id',
        'item_type',
        'catalog_type',
        'catalog_id',
        'name',
        'description',
        'unit',
        'quantity',
        'unit_cost',
        'tax_rate',
        'line_total',
        'source',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'line_total' => 'decimal:2',
            'metadata' => 'array',
            'calculation_id' => 'integer',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }
}
