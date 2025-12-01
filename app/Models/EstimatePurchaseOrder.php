<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $estimate_id
 * @property int|null $supplier_id
 * @property string $po_number
 * @property string $status
 * @property numeric $total_amount
 * @property string|null $notes
 * @property string|null $qbo_id
 * @property \Illuminate\Support\Carbon|null $qbo_synced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Estimate $estimate
 * @property-read string $status_color
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimatePurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Contact|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder draft()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder sent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder wherePoNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereQboId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereQboSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrder whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EstimatePurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'supplier_id',
        'po_number',
        'status',
        'total_amount',
        'notes',
        'qbo_id',
        'qbo_synced_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'qbo_synced_at' => 'datetime',
    ];

    /**
     * Get the estimate that owns the purchase order.
     */
    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    /**
     * Get the supplier for the purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'supplier_id');
    }

    /**
     * Get the items for the purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(EstimatePurchaseOrderItem::class, 'purchase_order_id');
    }

    /**
     * Check if the PO has been synced to QuickBooks.
     */
    public function isSyncedToQuickBooks(): bool
    {
        return !is_null($this->qbo_id);
    }

    /**
     * Get the status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'received' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope to only include draft purchase orders.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to only include sent purchase orders.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Recalculate the total amount from items.
     */
    public function recalculateTotal(): void
    {
        $this->total_amount = $this->items->sum('total_cost');
        $this->saveQuietly();
    }
}
