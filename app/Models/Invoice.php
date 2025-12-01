<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $estimate_id
 * @property string $status
 * @property numeric|null $amount
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string|null $pdf_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $qbo_invoice_id
 * @property string|null $qbo_sync_token
 * @property string|null $qbo_last_synced_at
 * @property string|null $qbo_doc_number
 * @property numeric|null $qbo_total
 * @property numeric|null $qbo_balance
 * @property string|null $qbo_status
 * @property-read \App\Models\Estimate $estimate
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePdfPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereQboBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereQboDocNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereQboInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereQboLastSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereQboStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereQboSyncToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereQboTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
