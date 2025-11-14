<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Calculation;
use App\Policies\CalculationPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Calculation::class => CalculationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-catalogs', function ($user) {
            return ($user->role ?? 'user') === 'admin';
        });
    }
}
