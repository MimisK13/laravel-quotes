<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\DTOs\QuoteItemData;
use Mimisk\LaravelQuotes\Enums\DiscountType;
use Mimisk\LaravelQuotes\Support\CalculateQuoteTotals;
use Mimisk\LaravelQuotes\Support\GenerateQuoteNumber;
use Mimisk\LaravelQuotes\Tests\Fixtures\TestOwner;

beforeEach(function (): void {
    Schema::dropIfExists('quote_test_owners');

    Schema::create('quote_test_owners', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function dtoOwner(): TestOwner
{
    return TestOwner::query()->create([
        'name' => 'DTO Owner',
    ]);
}

it('builds quote item data with default tax rate', function (): void {
    config()->set('quotes.tax.default_rate', 24.0);

    $item = QuoteItemData::fromArray([
        'name' => 'Service',
        'quantity' => 2,
        'unit_price' => 50,
    ]);

    expect($item->taxRate)->toBe(24.0)
        ->and($item->subtotal())->toBe(100.0)
        ->and($item->taxAmount())->toBe(24.0);
});

it('builds quote data with defaults and normalized items', function (): void {
    $owner = dtoOwner();
    config()->set('quotes.currency', 'EUR');
    config()->set('quotes.discount.default_type', 'fixed');

    $data = QuoteData::fromArray([
        'owner' => $owner,
        'items' => [
            ['name' => 'Item 1', 'quantity' => 1, 'unit_price' => 10, 'tax_rate' => 24],
            ['name' => 'Item 2', 'quantity' => 2, 'unit_price' => 5, 'tax_rate' => 0],
        ],
    ]);

    expect($data->owner->is($owner))->toBeTrue();
    expect($data->currency)->toBe('EUR');
    expect($data->discountType)->toBe(DiscountType::FIXED);
    expect($data->items)->toHaveCount(2);
    expect($data->items->first())->toBeInstanceOf(QuoteItemData::class);
});

it('throws when quote data owner is invalid', function (): void {
    expect(fn () => QuoteData::fromArray([
        'owner' => null,
        'items' => [],
    ]))->toThrow(InvalidArgumentException::class, 'QuoteData owner must be an Eloquent model.');
});

it('calculates totals with fixed discount capped at subtotal', function (): void {
    $owner = dtoOwner();

    $data = QuoteData::fromArray([
        'owner' => $owner,
        'discount_type' => 'fixed',
        'discount_value' => 999,
        'items' => [
            ['name' => 'Item', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 24],
        ],
    ]);

    $totals = app(CalculateQuoteTotals::class)->handle($data);

    expect($totals['subtotal'])->toBe(100.0)
        ->and($totals['tax_total'])->toBe(24.0)
        ->and($totals['discount_total'])->toBe(100.0)
        ->and($totals['total'])->toBe(24.0);
});

it('calculates totals with percentage discount', function (): void {
    $owner = dtoOwner();

    $data = QuoteData::fromArray([
        'owner' => $owner,
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'items' => [
            ['name' => 'Item', 'quantity' => 2, 'unit_price' => 50, 'tax_rate' => 24],
        ],
    ]);

    $totals = app(CalculateQuoteTotals::class)->handle($data);

    expect($totals['subtotal'])->toBe(100.0)
        ->and($totals['tax_total'])->toBe(24.0)
        ->and($totals['discount_total'])->toBe(10.0)
        ->and($totals['total'])->toBe(114.0);
});

it('generates quote number using configured format separator and length', function (): void {
    Carbon::setTestNow('2026-06-01 09:00:00');
    config()->set('quotes.number.prefix', 'Q-');
    config()->set('quotes.number.date_format', 'Ymd');
    config()->set('quotes.number.separator', '/');
    config()->set('quotes.number.random_length', 6);

    $number = app(GenerateQuoteNumber::class)->handle();

    expect($number)->toMatch('/^Q\-20260601\/\d{6}$/');
});
