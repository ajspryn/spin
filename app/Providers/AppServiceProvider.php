<?php

namespace App\Providers;

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
        // Force DB reconnect if connection is lost (for long idle apps)
        \Illuminate\Support\Facades\DB::reconnector(function ($connection) {
            $connection->disconnect();
            $connection->reconnect();
        });
    }
}
