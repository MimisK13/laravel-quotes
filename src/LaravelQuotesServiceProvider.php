<?php

namespace Mimisk\LaravelQuotes;

use Illuminate\Support\ServiceProvider;

class LaravelQuotesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );

        $this->publishes([
            __DIR__ . '/../config/laravel-quotes.php' => config_path('laravel-quotes.php'),
        ], 'quotes-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'quotes-migrations');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-quotes.php', 'laravel-quotes');
    }
}

