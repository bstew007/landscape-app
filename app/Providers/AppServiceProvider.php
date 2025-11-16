<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Contact;
use App\Observers\ContactObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $mailPaths = [
            resource_path('views/vendor/mail'),
            base_path('vendor/laravel/framework/src/Illuminate/Mail/resources/views'),
        ];
        $this->app['view']->addNamespace('mail', $mailPaths);

        // Observe contact changes for QBO auto-sync
        if (config('qbo.auto_sync')) {
            Contact::observe(ContactObserver::class);
        }
    }
}
