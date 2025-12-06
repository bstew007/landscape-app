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
 * @property \Illuminate\Support\Carbon|null $qbo_last_synced_at
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $mobile
 * @property numeric|null $qbo_balance
 * @property string|null $qbo_vendor_id
 * @property-read string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Material> $materials
 * @property-read int|null $materials_count
 * @property-read \App\Models\Property|null $primaryProperty
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SiteVisit> $siteVisits
 * @property-read int|null $site_visits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereContactType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereEmail2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereQboBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereQboCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereQboLastSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereQboSyncToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereQboVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        'qbo_vendor_id',
        'qbo_sync_token',
        'qbo_last_synced_at',
        'qbo_balance',
    ];

    protected $casts = [
        'qbo_last_synced_at' => 'datetime',
        'qbo_balance' => 'decimal:2',
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

    public function materials()
    {
        return $this->hasMany(Material::class, 'supplier_id');
    }

    public function tags()
    {
        return $this->belongsToMany(ContactTag::class, 'contact_tag_pivot', 'contact_id', 'tag_id')
            ->withTimestamps();
    }

    public function hasTag(string $tagSlug): bool
    {
        return $this->tags()->where('slug', $tagSlug)->exists();
    }

    public function hasAllTags(array $tagSlugs): bool
    {
        return $this->tags()->whereIn('slug', $tagSlugs)->count() === count($tagSlugs);
    }

    public function hasAnyTag(array $tagSlugs): bool
    {
        return $this->tags()->whereIn('slug', $tagSlugs)->exists();
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
