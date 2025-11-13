<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
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

