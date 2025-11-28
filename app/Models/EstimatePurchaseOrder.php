<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
