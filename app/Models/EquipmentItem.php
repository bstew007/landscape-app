<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $sku
 * @property string|null $category
 * @property string $ownership_type
 * @property string $unit
 * @property numeric|null $hourly_cost
 * @property numeric|null $daily_cost
 * @property numeric|null $hourly_rate
 * @property numeric|null $daily_rate
 * @property numeric|null $breakeven
 * @property numeric|null $profit_percent
 * @property string|null $vendor_name
 * @property string|null $model
 * @property string|null $description
 * @property string|null $notes
 * @property bool $is_active
 * @property int|null $asset_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Asset|null $asset
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentItem active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentItem companyOwned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentItem rental()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentItem query()
 * @mixin \Eloquent
 */
class EquipmentItem extends Model
{
    use HasFactory;

    protected $table = 'equipment_catalog';

    protected $fillable = [
        'name',
        'sku',
        'category',
        'ownership_type',
        'unit',
        'hourly_cost',
        'daily_cost',
        'hourly_rate',
        'daily_rate',
        'breakeven',
        'profit_percent',
        'vendor_name',
        'model',
        'description',
        'notes',
        'is_active',
        'asset_id',
    ];

    protected $casts = [
        'hourly_cost' => 'decimal:2',
        'daily_cost' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'breakeven' => 'decimal:2',
        'profit_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'asset_id' => 'integer',
    ];

    /**
     * Get the asset associated with this equipment (for company-owned).
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Check if equipment is company-owned.
     */
    public function isCompanyOwned(): bool
    {
        return $this->ownership_type === 'company';
    }

    /**
     * Check if equipment is rental.
     */
    public function isRental(): bool
    {
        return $this->ownership_type === 'rental';
    }

    /**
     * Get the primary rate based on unit type.
     */
    public function getPrimaryRate(): ?float
    {
        return $this->unit === 'day' ? $this->daily_rate : $this->hourly_rate;
    }

    /**
     * Get the primary cost based on unit type.
     */
    public function getPrimaryCost(): ?float
    {
        return $this->unit === 'day' ? $this->daily_cost : $this->hourly_cost;
    }

    /**
     * Scope: Get only active equipment.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get only company-owned equipment.
     */
    public function scopeCompanyOwned($query)
    {
        return $query->where('ownership_type', 'company');
    }

    /**
     * Scope: Get only rental equipment.
     */
    public function scopeRental($query)
    {
        return $query->where('ownership_type', 'rental');
    }
}
