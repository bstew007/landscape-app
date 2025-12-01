<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $asset_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $severity
 * @property \Illuminate\Support\Carbon|null $reported_on
 * @property \Illuminate\Support\Carbon|null $resolved_on
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Asset $asset
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereReportedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereResolvedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereSeverity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetIssue whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AssetIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'title',
        'description',
        'status',
        'severity',
        'reported_on',
        'resolved_on',
    ];

    protected $casts = [
        'reported_on' => 'date',
        'resolved_on' => 'date',
    ];

    public const STATUSES = ['open', 'in_progress', 'resolved'];
    public const SEVERITIES = ['low', 'normal', 'high', 'critical'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
