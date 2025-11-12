<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id', 'name', 'description', 'sort_order',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
