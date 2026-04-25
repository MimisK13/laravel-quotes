<?php

use Mimisk\LaravelQuotes\Facades\LaravelQuotes as LaravelQuotesFacade;
use Mimisk\LaravelQuotes\LaravelQuotes;

it('resolves the package service from the container', function (): void {
    $service = app(LaravelQuotes::class);

    expect($service)
        ->toBeInstanceOf(LaravelQuotes::class);
});

it('returns a random quote from configuration', function (): void {
    config()->set('laravel-quotes.quotes', ['Alpha', 'Beta', 'Gamma']);

    $quote = app(LaravelQuotes::class)->random();

    expect($quote)->toBeIn(['Alpha', 'Beta', 'Gamma']);
});

it('returns fallback quote when list is empty', function (): void {
    config()->set('laravel-quotes.quotes', []);

    $quote = app(LaravelQuotes::class)->random();

    expect($quote)->toBe('No quotes available.');
});

it('works through the facade', function (): void {
    config()->set('laravel-quotes.quotes', ['Ship']);

    expect(LaravelQuotesFacade::all())->toBe(['Ship'])
        ->and(LaravelQuotesFacade::random())->toBe('Ship');
});
