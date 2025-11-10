<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Estimate extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'property_id',
        'site_visit_id',
        'title',
        'status',
        'total',
        'expires_at',
        'line_items',
        'notes',
        'terms',
        'email_sent_at',
        'email_last_sent_at',
        'email_send_count',
        'email_last_sent_by',
    ];

    protected $casts = [
        'line_items' => 'array',
        'expires_at' => 'date',
        'total' => 'decimal:2',
        'email_sent_at' => 'datetime',
        'email_last_sent_at' => 'datetime',
    ];

    public const STATUSES = ['draft', 'pending', 'sent', 'approved', 'rejected'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function siteVisit()
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function emailSender()
    {
        return $this->belongsTo(User::class, 'email_last_sent_by');
    }
}
