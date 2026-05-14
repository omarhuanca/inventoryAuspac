<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

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
        // Restrict Horizon dashboard access.
        // In production, replace with actual admin email check or role check.
        Horizon::auth(function ($request) {
            return app()->environment('local')
                || (auth()->check() && in_array(auth()->user()->email, explode(',', env('HORIZON_ALLOWED_EMAILS', ''))));
        });
    }
}
