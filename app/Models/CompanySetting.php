<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
