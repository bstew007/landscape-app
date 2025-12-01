<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string|null $company_name
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $logo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $full_address
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanySetting whereWebsite($value)
 * @mixin \Eloquent
 */
class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'address',
        'city',
        'state',
        'postal_code',
        'phone',
        'email',
        'website',
        'logo_path',
    ];

    /**
     * Get the company settings singleton instance.
     * Creates default settings if none exist.
     */
    public static function getSettings(): self
    {
        return Cache::remember('company_settings', 3600, function () {
            $settings = self::first();
            
            if (!$settings) {
                $settings = self::create([
                    'company_name' => config('app.name', 'Your Company'),
                    'email' => config('mail.from.address'),
                ]);
            }
            
            return $settings;
        });
    }

    /**
     * Clear the cached settings.
     */
    public static function clearCache(): void
    {
        Cache::forget('company_settings');
    }

    /**
     * Boot the model and clear cache when updated.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saved(function () {
            self::clearCache();
        });
        
        static::deleted(function () {
            self::clearCache();
        });
    }

    /**
     * Get formatted full address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            collect([$this->city, $this->state])->filter()->join(', '),
            $this->postal_code,
        ]);
        
        return implode("\n", $parts);
    }
}
