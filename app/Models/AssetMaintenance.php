<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $asset_id
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $type
 * @property string|null $notes
 * @property int|null $mileage_hours
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Asset $asset
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance whereAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance whereMileageHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetMaintenance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AssetMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'scheduled_at',
        'completed_at',
        'type',
        'notes',
        'mileage_hours',
    ];

    protected $casts = [
        'scheduled_at' => 'date',
        'completed_at' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
