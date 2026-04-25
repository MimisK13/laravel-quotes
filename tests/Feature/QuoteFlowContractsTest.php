<?php

use Mimisk\LaravelQuotes\Actions\AcceptQuoteAction;
use Mimisk\LaravelQuotes\Actions\CreateQuoteAction;
use Mimisk\LaravelQuotes\Actions\ExpireQuoteAction;
use Mimisk\LaravelQuotes\Actions\RejectQuoteAction;
use Mimisk\LaravelQuotes\Actions\SendQuoteAction;
use Mimisk\LaravelQuotes\Actions\UpdateQuoteAction;
use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\DTOs\QuoteItemData;
use Mimisk\LaravelQuotes\Support\CalculateQuoteTotals;
use Mimisk\LaravelQuotes\Support\GenerateQuoteNumber;

it('autoloads all quote flow classes in the package namespace', function (): void {
    $classes = [
        CreateQuoteAction::class,
        UpdateQuoteAction::class,
        SendQuoteAction::class,
        AcceptQuoteAction::class,
        RejectQuoteAction::class,
        ExpireQuoteAction::class,
        QuoteData::class,
        QuoteItemData::class,
        CalculateQuoteTotals::class,
        GenerateQuoteNumber::class,
    ];

    foreach ($classes as $class) {
        expect(class_exists($class))->toBeTrue("Class {$class} should autoload.");
    }
});

it('exposes flow config under quotes key', function (): void {
    expect(config('quotes.currency'))->toBe('EUR')
        ->and(config('quotes.number.prefix'))->toBe('Q-')
        ->and(config('quotes.valid_until.default_days'))->toBe(10)
        ->and(config('quotes.tax.default_rate'))->toBe(24.0)
        ->and(config('quotes.discount.default_type'))->toBe('fixed');
});

it('points model config to autoloadable classes', function (): void {
    $quoteClass = config('quotes.models.quote');
    $quoteItemClass = config('quotes.models.quote_item');

    expect(is_string($quoteClass))->toBeTrue()
        ->and(is_string($quoteItemClass))->toBeTrue()
        ->and(class_exists($quoteClass))->toBeTrue("Configured quote model [{$quoteClass}] should exist.")
        ->and(class_exists($quoteItemClass))->toBeTrue("Configured quote item model [{$quoteItemClass}] should exist.");
});
