<?php

use Mimisk\LaravelQuotes\Facades\LaravelQuotes as LaravelQuotesFacade;
use Mimisk\LaravelQuotes\LaravelQuotes;
use Mimisk\LaravelQuotes\LaravelQuotesServiceProvider;

it('resolves the package service from the container', function (): void {
    $service = app(LaravelQuotes::class);

    expect($service)
        ->toBeInstanceOf(LaravelQuotes::class);
});

it('registers the expected service provider', function (): void {
    $providers = app()->getLoadedProviders();

    expect($providers)
        ->toHaveKey(LaravelQuotesServiceProvider::class)
        ->and($providers[LaravelQuotesServiceProvider::class])->toBeTrue();
});

it('registers the facade alias class', function (): void {
    expect(class_exists(LaravelQuotesFacade::class))->toBeTrue();
});

it('loads core package config values', function (): void {
    expect(config('laravel-quotes.currency'))->toBe('EUR')
        ->and(config('laravel-quotes.discount.default_type'))->toBe('fixed')
        ->and(config('laravel-quotes.owner.morph_name'))->toBe('owner');
});
