<?php

use Mimisk\LaravelQuotes\LaravelQuotesServiceProvider;

it('registers the expected service provider', function (): void {
    $providers = app()->getLoadedProviders();

    expect($providers)
        ->toHaveKey(LaravelQuotesServiceProvider::class)
        ->and($providers[LaravelQuotesServiceProvider::class])->toBeTrue();
});

it('loads core package config values', function (): void {
    expect(config('quotes.currency'))->toBe('EUR')
        ->and(config('quotes.discount.default_type'))->toBe('fixed')
        ->and(config('quotes.owner.morph_name'))->toBe('owner');
});
