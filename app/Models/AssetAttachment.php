<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $asset_id
 * @property string|null $label
 * @property string $path
 * @property string|null $mime_type
 * @property int|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Asset $asset
 * @property-read string $url
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment whereAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttachment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AssetAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'label',
        'path',
        'mime_type',
        'size',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
