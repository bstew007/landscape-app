<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionRate extends Model
{
    protected $fillable = [
        'task',
        'unit',
        'rate',
        'calculator',
        'note'
    ];
}

