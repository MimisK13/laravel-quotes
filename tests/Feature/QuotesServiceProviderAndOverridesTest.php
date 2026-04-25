<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\LaravelQuotesServiceProvider;
use Mimisk\LaravelQuotes\Services\QuotesService;
use Mimisk\LaravelQuotes\Tests\Fixtures\CustomQuote;
use Mimisk\LaravelQuotes\Tests\Fixtures\CustomQuoteItem;
use Mimisk\LaravelQuotes\Tests\Fixtures\TestOwner;

beforeEach(function (): void {
    Schema::dropIfExists('quote_items');
    Schema::dropIfExists('quotes');
    Schema::dropIfExists('quote_test_owners');

    Schema::create('quote_test_owners', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $quotesMigration = require __DIR__.'/../../database/migrations/2026_04_25_000000_create_quotes_table.php';
    $quoteItemsMigration = require __DIR__.'/../../database/migrations/2026_04_25_000001_create_quote_items_table.php';

    $quotesMigration->up();
    $quoteItemsMigration->up();
});

function serviceOwner(): TestOwner
{
    return TestOwner::query()->create([
        'name' => 'Service Owner',
    ]);
}

/**
 * @param  array<int, array<string, mixed>>  $items
 */
function serviceQuoteData(TestOwner $owner, array $items, ?string $title = null): QuoteData
{
    return QuoteData::fromArray([
        'owner' => $owner,
        'title' => $title,
        'items' => $items,
    ]);
}

it('delegates quote lifecycle operations through quotes service', function (): void {
    $service = app(QuotesService::class);
    $owner = serviceOwner();

    $quote = $service->create(serviceQuoteData($owner, [
        ['name' => 'Line A', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 24],
    ], 'Draft Title'));

    expect($quote->status)->toBe(QuoteStatus::DRAFT)
        ->and($quote->items)->toHaveCount(1);

    $updated = $service->update($quote, serviceQuoteData($owner, [
        ['name' => 'Line B', 'quantity' => 2, 'unit_price' => 50, 'tax_rate' => 24],
    ], 'Updated Title'));

    expect($updated->title)->toBe('Updated Title')
        ->and($updated->items)->toHaveCount(1)
        ->and($updated->items->first()?->name)->toBe('Line B');

    $accepted = $service->accept($service->send($updated));
    expect($accepted->status)->toBe(QuoteStatus::ACCEPTED);

    $rejected = $service->reject($service->send($service->create(serviceQuoteData($owner, [
        ['name' => 'Reject Line', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
    ]))));
    expect($rejected->status)->toBe(QuoteStatus::REJECTED);

    $service->delete($rejected);
    expect($rejected->fresh())->toBeNull();

    $expired = $service->expire($service->send($service->create(serviceQuoteData($owner, [
        ['name' => 'Expire Line', 'quantity' => 1, 'unit_price' => 30, 'tax_rate' => 24],
    ]))));
    expect($expired->status)->toBe(QuoteStatus::EXPIRED);
});

it('registers publish paths for config and migrations tags', function (): void {
    $configPaths = ServiceProvider::pathsToPublish(
        LaravelQuotesServiceProvider::class,
        'quotes-config'
    );
    $migrationPaths = ServiceProvider::pathsToPublish(
        LaravelQuotesServiceProvider::class,
        'quotes-migrations'
    );

    $hasConfigPublishMapping = collect($configPaths)->contains(
        static fn (string $destination, string $source): bool => str_ends_with(str_replace('\\', '/', $source), '/config/quotes.php')
            && $destination === config_path('quotes.php')
    );
    $hasMigrationPublishMapping = collect($migrationPaths)->contains(
        static fn (string $destination, string $source): bool => str_ends_with(str_replace('\\', '/', $source), '/database/migrations')
            && $destination === database_path('migrations')
    );

    expect($hasConfigPublishMapping)->toBeTrue()
        ->and($hasMigrationPublishMapping)->toBeTrue();
});

it('respects configured quote and quote item model overrides in relationships', function (): void {
    config()->set('quotes.models.quote', CustomQuote::class);
    config()->set('quotes.models.quote_item', CustomQuoteItem::class);

    $owner = serviceOwner();

    $quote = CustomQuote::query()->create([
        'owner_id' => $owner->getKey(),
        'owner_type' => $owner->getMorphClass(),
        'number' => 'Q-OVERRIDE-1',
        'status' => QuoteStatus::DRAFT,
        'currency' => 'EUR',
    ]);

    $quote->items()->create([
        'name' => 'Custom Item',
        'quantity' => 1,
        'unit_price' => 10,
        'tax_rate' => 24,
        'subtotal' => 10,
        'tax_total' => 2.4,
        'total' => 12.4,
        'sort_order' => 0,
    ]);

    $item = $quote->items()->first();

    expect($item)->toBeInstanceOf(CustomQuoteItem::class)
        ->and($item?->quote)->toBeInstanceOf(CustomQuote::class);
});
