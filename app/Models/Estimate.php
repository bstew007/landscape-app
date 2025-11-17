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
        'estimate_type',
        'title',
        'status',
        'division_id',
        'cost_code_id',
        'total',
        'material_subtotal',
        'material_cost_total',
        'labor_subtotal',
        'labor_cost_total',
        'fee_total',
        'discount_total',
        'tax_total',
        'grand_total',
        'revenue_total',
        'crew_notes',
        'terms_header',
        'terms_footer',
        'cost_total',
        'profit_total',
        'net_profit_total',
        'profit_margin',
        'net_margin',
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
        'material_cost_total' => 'decimal:2',
        'labor_subtotal' => 'decimal:2',
        'labor_cost_total' => 'decimal:2',
        'fee_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'revenue_total' => 'decimal:2',
        'crew_notes' => 'string',
        'terms_header' => 'string',
        'terms_footer' => 'string',
        'cost_total' => 'decimal:2',
        'profit_total' => 'decimal:2',
        'net_profit_total' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'net_margin' => 'decimal:2',
        'email_sent_at' => 'datetime',
        'email_last_sent_at' => 'datetime',
        'division_id' => 'integer',
        'cost_code_id' => 'integer',
        'estimate_type' => 'string',
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

    public function areas()
    {
        return $this->hasMany(EstimateArea::class)->orderBy('sort_order');
    }

    public function files()
    {
        return $this->hasMany(EstimateFile::class)->latest();
    }

    public function emailSender()
    {
        return $this->belongsTo(User::class, 'email_last_sent_by');
    }
}
