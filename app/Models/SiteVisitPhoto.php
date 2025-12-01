<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $site_visit_id
 * @property string $path
 * @property string|null $caption
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SiteVisit $siteVisit
 * @property-read \App\Models\User|null $uploader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto whereCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto whereSiteVisitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisitPhoto whereUploadedBy($value)
 * @mixin \Eloquent
 */
class SiteVisitPhoto extends Model
{
    protected $fillable = [
        'site_visit_id',
        'path',
        'caption',
        'uploaded_by',
    ];

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
