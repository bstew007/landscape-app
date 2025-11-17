<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'division_id', 'qbo_item_id', 'qbo_item_name', 'is_active'
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
