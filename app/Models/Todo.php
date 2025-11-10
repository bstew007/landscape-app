<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public const STATUSES = ['pending', 'in_progress', 'completed'];
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
