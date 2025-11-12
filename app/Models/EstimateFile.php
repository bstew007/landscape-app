<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id', 'disk', 'path', 'filename', 'mime', 'size', 'uploaded_by',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
