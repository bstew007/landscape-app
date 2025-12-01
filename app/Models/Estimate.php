<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EstimateItem;

/**
 * @property int $id
 * @property int $client_id
 * @property int|null $property_id
 * @property int|null $site_visit_id
 * @property string $title
 * @property string $status
 * @property numeric|null $total
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property array<array-key, mixed>|null $line_items
 * @property string|null $notes
 * @property string|null $terms
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $email_sent_at
 * @property \Illuminate\Support\Carbon|null $email_last_sent_at
 * @property int $email_send_count
 * @property int|null $email_last_sent_by
 * @property numeric $material_subtotal
 * @property numeric $labor_subtotal
 * @property numeric $fee_total
 * @property numeric $discount_total
 * @property numeric $tax_total
 * @property numeric $grand_total
 * @property numeric $material_cost_total
 * @property numeric $labor_cost_total
 * @property numeric $revenue_total
 * @property numeric $cost_total
 * @property numeric $profit_total
 * @property numeric $net_profit_total
 * @property numeric $profit_margin
 * @property numeric $net_margin
 * @property string|null $crew_notes
 * @property string|null $terms_header
 * @property string|null $terms_footer
 * @property string $estimate_type
 * @property int|null $division_id
 * @property int|null $cost_code_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateArea> $areas
 * @property-read int|null $areas_count
 * @property-read \App\Models\Client $client
 * @property-read User|null $emailSender
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateFile> $files
 * @property-read int|null $files_count
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Property|null $property
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimatePurchaseOrder> $purchaseOrders
 * @property-read int|null $purchase_orders_count
 * @property-read \App\Models\SiteVisit|null $siteVisit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereCostCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereCostTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereCrewNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereDiscountTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereEmailLastSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereEmailLastSentBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereEmailSendCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereEstimateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereFeeTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereLaborCostTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereLaborSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereLineItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereMaterialCostTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereMaterialSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereNetMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereNetProfitTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereProfitMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereProfitTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereRevenueTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereSiteVisitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereTaxTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereTermsFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereTermsHeader($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estimate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Estimate extends Model
{
    public const STATUSES = [
        'draft',
        'pending',
        'sent',
        'approved',
        'rejected',
    ];
    
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
        return $this->hasMany(EstimateItem::class);
    }

    /**
     * Recalculate totals based on line items and save.
     */
    public function recalculate()
    {
        // Delegate to the comprehensive service method
        app(\App\Services\EstimateItemService::class)->recalculateTotals($this);
    }

    public function areas()
    {
        return $this->hasMany(EstimateArea::class)->orderBy('sort_order');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(EstimatePurchaseOrder::class);
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
