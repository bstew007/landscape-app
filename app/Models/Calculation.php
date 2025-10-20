<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calculation extends Model
{
    protected $fillable = ['site_visit_id', 'calculation_type', 'data'];

    protected $casts = [
        'data' => 'array',
    ];

    public function siteVisit()
    {
        return $this->belongsTo(SiteVisit::class);
    }
}
