<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string $type
 * @property string|null $contact_name
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property string|null $address_line1
 * @property string|null $address_line2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $notes
 * @property bool $is_primary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Client $client
 * @property-read string|null $display_address
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SiteVisit> $siteVisits
 * @property-read int|null $site_visits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property primary()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
