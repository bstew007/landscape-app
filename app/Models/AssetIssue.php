<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
