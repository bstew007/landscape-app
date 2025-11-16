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
        // QBO linkage
        'qbo_invoice_id',
        'qbo_sync_token',
        'qbo_last_synced_at',
        'qbo_doc_number',
        'qbo_total',
        'qbo_balance',
        'qbo_status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'qbo_total' => 'decimal:2',
        'qbo_balance' => 'decimal:2',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
