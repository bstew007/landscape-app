<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $client_id
 * @property \Illuminate\Support\Carbon $visit_date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $property_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Calculation> $calculations
 * @property-read int|null $calculations_count
 * @property-read \App\Models\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SiteVisitPhoto> $photos
 * @property-read int|null $photos_count
 * @property-read \App\Models\Property|null $property
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteVisit whereVisitDate($value)
 * @mixin \Eloquent
 */
class SiteVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_date',
        'notes',
        'client_id', // �+? Add this
        'property_id',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function calculations()
    {
        return $this->hasMany(Calculation::class);
    }

    public function photos()
    {
        return $this->hasMany(SiteVisitPhoto::class);
    }

    public function estimates()
    {
        return $this->hasMany(Estimate::class);
    }
}
