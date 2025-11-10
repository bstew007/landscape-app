<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
