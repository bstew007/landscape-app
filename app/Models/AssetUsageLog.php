<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'checked_out_at',
        'checked_in_at',
        'mileage_out',
        'mileage_in',
        'inspection_data',
        'notes',
        'status',
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'inspection_data' => 'array',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isCheckedOut()
    {
        return $this->status === 'checked_out';
    }

    public function isCheckedIn()
    {
        return $this->status === 'checked_in';
    }
}
