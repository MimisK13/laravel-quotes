<?php

namespace Mimisk\LaravelQuotes;

use Illuminate\Support\ServiceProvider;

class LaravelQuotesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-quotes.php', 'laravel-quotes');

        $this->app->singleton(LaravelQuotes::class, fn (): LaravelQuotes => new LaravelQuotes());
        $this->app->alias(LaravelQuotes::class, 'laravel-quotes');
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return ['laravel-quotes'];
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__.'/../config/laravel-quotes.php' => config_path('laravel-quotes.php'),
        ], 'laravel-quotes.config');
    }
}
