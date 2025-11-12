<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EstimateItem;

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
        'material_subtotal',
        'labor_subtotal',
        'fee_total',
        'discount_total',
        'tax_total',
        'grand_total',
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
        'material_subtotal' => 'decimal:2',
        'labor_subtotal' => 'decimal:2',
        'fee_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
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

    public function items()
    {
        return $this->hasMany(EstimateItem::class)->orderBy('sort_order');
    }

    public function emailSender()
    {
        return $this->belongsTo(User::class, 'email_last_sent_by');
    }
}
