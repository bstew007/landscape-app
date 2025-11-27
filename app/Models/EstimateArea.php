<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id', 'name', 'identifier', 'cost_code_id', 'description', 'sort_order',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function items()
    {
        return $this->hasMany(EstimateItem::class, 'area_id');
    }

    protected static function booted()
    {
        // When an area is deleted, delete all associated items
        static::deleting(function ($area) {
            $area->items()->delete();
        });
    }
}
