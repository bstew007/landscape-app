<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'status',
        'amount',
        'due_date',
        'pdf_path',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
