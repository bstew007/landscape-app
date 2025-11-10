<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'line_items' => 'array',
        'expires_at' => 'date',
        'total' => 'decimal:2',
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
}
