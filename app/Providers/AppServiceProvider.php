<?php

namespace App\Providers;

use Illuminate\Support\Carbon; //(DELETE LATER)
use Illuminate\Support\ServiceProvider;

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
        if (env('DEMO_NOW')) {
            Carbon::setTestNow(Carbon::parse(env('DEMO_NOW')));
        }
    }
}