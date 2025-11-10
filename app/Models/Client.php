<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'address',
    ];

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function primaryProperty()
    {
        return $this->hasOne(Property::class)->where('is_primary', true);
    }

    public function getNameAttribute(): string
    {
        $contact = trim("{$this->first_name} {$this->last_name}");

        if ($this->company_name && $contact) {
            return "{$this->company_name} ({$contact})";
        }

        return $this->company_name ?: $contact;
    }
}
