<?php

namespace Mimisk\LaravelQuotes;

use Illuminate\Support\ServiceProvider;
use Mimisk\LaravelQuotes\Services\QuotesService;

class LaravelQuotesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );

        $this->publishes([
            __DIR__ . '/../config/quotes.php' => config_path('quotes.php'),
        ], 'quotes-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'quotes-migrations');
    }

    public function register(): void
    {
        $this->app->singleton('quotes', QuotesService::class);

        $this->mergeConfigFrom(__DIR__.'/../config/quotes.php', 'quotes');
    }
}
