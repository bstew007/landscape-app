<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $company_name
 * @property string $contact_type
 * @property string|null $email2
 * @property string|null $phone2
 * @property string|null $qbo_customer_id
 * @property string|null $qbo_sync_token
 * @property string|null $qbo_last_synced_at
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $mobile
 * @property string|null $qbo_balance
 * @property string|null $qbo_vendor_id
 * @property-read string $name
 * @property-read \App\Models\Property|null $primaryProperty
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SiteVisit> $siteVisits
 * @property-read int|null $site_visits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereContactType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEmail2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereQboBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereQboCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereQboLastSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereQboSyncToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereQboVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'contact_type',
        'email',
        'email2',
        'phone',
        'phone2',
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

    public static function types(): array
    {
        return ['lead', 'client', 'vendor', 'owner'];
    }
}
