<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'contact_type',
        'email',
        'email2',
        'phone',
        'mobile',
        'phone2',
        'address',
        'city',
        'state',
        'postal_code',
        // QBO linkage
        'qbo_customer_id',
        'qbo_sync_token',
        'qbo_last_synced_at',
        'qbo_balance',
    ];

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class, 'client_id');
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'client_id');
    }

    public function primaryProperty()
    {
        return $this->hasOne(Property::class, 'client_id')->where('is_primary', true);
    }

    public function getNameAttribute(): string
    {
        $contact = trim("{$this->first_name} {$this->last_name}");

        if ($this->company_name && $contact) {
            return "{$this->company_name} ({$contact})";
        }

        return $this->company_name ?: $contact;
    }

    public static function types(): array
    {
        return ['lead', 'client', 'vendor', 'owner'];
    }
}
