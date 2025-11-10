<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_date',
        'notes',
        'client_id', // ← Add this
    ];

    protected $casts = [
    'visit_date' => 'date',
    ];  

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function calculations()
    {
     return $this->hasMany(Calculation::class);
    }

    public function photos()
    {
        return $this->hasMany(SiteVisitPhoto::class);
    }
}
