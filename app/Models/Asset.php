<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $identifier
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $purchase_date
 * @property string|null $purchase_price
 * @property string|null $assigned_to
 * @property int|null $mileage_hours
 * @property \Illuminate\Support\Carbon|null $next_service_date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $reminder_enabled
 * @property int $reminder_days_before
 * @property \Illuminate\Support\Carbon|null $last_reminder_sent_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetIssue> $issues
 * @property-read int|null $issues_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetMaintenance> $maintenances
 * @property-read int|null $maintenances_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereLastReminderSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereMileageHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereNextServiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereReminderDaysBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereReminderEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
