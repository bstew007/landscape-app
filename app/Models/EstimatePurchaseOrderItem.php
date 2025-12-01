<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int|null $estimate_item_id
 * @property int|null $material_id
 * @property numeric $quantity
 * @property numeric $unit_cost
 * @property numeric $total_cost
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EstimateItem|null $estimateItem
 * @property-read string $material_name
 * @property-read string|null $unit
 * @property-read \App\Models\Material|null $material
 * @property-read \App\Models\EstimatePurchaseOrder $purchaseOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem whereEstimateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimatePurchaseOrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EstimatePurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'estimate_item_id',
        'material_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Recalculate total cost when quantity or unit cost changes
        static::saving(function ($item) {
            $item->total_cost = $item->quantity * $item->unit_cost;
        });

        // Recalculate PO total when item is saved or deleted
        static::saved(function ($item) {
            $item->purchaseOrder?->recalculateTotal();
        });

        static::deleted(function ($item) {
            $item->purchaseOrder?->recalculateTotal();
        });
    }

    /**
     * Get the purchase order that owns the item.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(EstimatePurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Get the estimate item this PO item is based on.
     */
    public function estimateItem(): BelongsTo
    {
        return $this->belongsTo(EstimateItem::class);
    }

    /**
     * Get the material catalog item.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the material name (from estimate item or catalog).
     */
    public function getMaterialNameAttribute(): string
    {
        return $this->estimateItem?->name 
            ?? $this->material?->name 
            ?? 'Unknown Material';
    }

    /**
     * Get the unit label.
     */
    public function getUnitAttribute(): ?string
    {
        return $this->estimateItem?->unit 
            ?? $this->material?->unit;
    }
}
