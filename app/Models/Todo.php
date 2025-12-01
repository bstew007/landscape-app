<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $client_id
 * @property int|null $property_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $priority
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\Property|null $property
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo status(?string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Todo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'property_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public const STATUSES = ['future', 'pending', 'in_progress', 'completed'];
    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function scopeStatus($query, ?string $status)
    {
        if ($status && in_array($status, self::STATUSES, true)) {
            $query->where('status', $status);
        }
    }
}
