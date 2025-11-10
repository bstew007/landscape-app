<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'type',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'notes',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function getDisplayAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            trim("{$this->city}, {$this->state}") !== ','
                ? trim("{$this->city}, {$this->state}") : null,
            $this->postal_code,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }
}
