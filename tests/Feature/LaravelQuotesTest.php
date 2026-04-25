<?php

use Mimisk\LaravelQuotes\LaravelQuotesServiceProvider;
use Mimisk\LaravelQuotes\Facades\Quotes;
use Mimisk\LaravelQuotes\Services\QuotesService;

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

it('resolves quotes facade to quotes service binding', function (): void {
    expect(app('quotes'))->toBeInstanceOf(QuotesService::class)
        ->and(Quotes::getFacadeRoot())->toBeInstanceOf(QuotesService::class);
});
