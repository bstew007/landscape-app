<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $site_visit_id
 * @property string $calculation_type
 * @property array<array-key, mixed> $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_template
 * @property string|null $template_name
 * @property int|null $estimate_id
 * @property string|null $template_scope
 * @property int|null $client_id
 * @property int|null $property_id
 * @property bool $is_active
 * @property int|null $created_by
 * @property bool $is_global
 * @property-read \App\Models\Estimate|null $estimate
 * @property-read \App\Models\SiteVisit|null $siteVisit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereCalculationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereIsGlobal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereIsTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereSiteVisitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereTemplateName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereTemplateScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calculation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Calculation extends Model
{
    protected $fillable = [
        'site_visit_id',
        'estimate_id',
        'calculation_type',
        'data',
        'is_template',
        'template_name',
        'template_scope',
        'client_id',
        'property_id',
        'created_by',
        'is_global',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
        'is_global' => 'boolean',
    ];

    public function siteVisit()
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}

