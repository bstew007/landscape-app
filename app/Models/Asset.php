<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'identifier',
        'status',
        'purchase_date',
        'purchase_price',
        'assigned_to',
        'mileage_hours',
        'next_service_date',
        'notes',
        'reminder_enabled',
        'reminder_days_before',
        'last_reminder_sent_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'next_service_date' => 'date',
        'reminder_enabled' => 'boolean',
        'last_reminder_sent_at' => 'datetime',
    ];

    public const STATUSES = ['active', 'in_maintenance', 'retired'];
    public const TYPES = ['vehicle', 'trailer', 'skid_steer', 'equipment', 'other'];

    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function issues()
    {
        return $this->hasMany(AssetIssue::class);
    }

    public function attachments()
    {
        return $this->hasMany(AssetAttachment::class);
    }
}
