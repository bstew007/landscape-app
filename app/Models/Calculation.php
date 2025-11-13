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
    ];

    protected $casts = [
        'data' => 'array',
        'is_template' => 'boolean',
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

