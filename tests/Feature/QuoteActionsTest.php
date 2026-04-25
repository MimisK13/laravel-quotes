<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mimisk\LaravelQuotes\Actions\AcceptQuoteAction;
use Mimisk\LaravelQuotes\Actions\CreateQuoteAction;
use Mimisk\LaravelQuotes\Actions\DeleteQuoteAction;
use Mimisk\LaravelQuotes\Actions\ExpireQuoteAction;
use Mimisk\LaravelQuotes\Actions\RejectQuoteAction;
use Mimisk\LaravelQuotes\Actions\SendQuoteAction;
use Mimisk\LaravelQuotes\Actions\UpdateQuoteAction;
use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteAccepted;
use Mimisk\LaravelQuotes\Events\QuoteCreated;
use Mimisk\LaravelQuotes\Events\QuoteExpired;
use Mimisk\LaravelQuotes\Events\QuoteRejected;
use Mimisk\LaravelQuotes\Events\QuoteSent;
use Mimisk\LaravelQuotes\Events\QuoteUpdated;
use Mimisk\LaravelQuotes\Exceptions\InvalidQuoteTransition;
use Mimisk\LaravelQuotes\Models\Quote;
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

afterEach(function (): void {
    Carbon::setTestNow();
});

function quoteOwner(): TestOwner
{
    return TestOwner::query()->create([
        'name' => 'ACME',
    ]);
}

/**
 * @param  array<int, array<string, mixed>>  $items
 * @param  array<string, mixed>  $overrides
 */
function quoteData(TestOwner $owner, array $items, array $overrides = []): QuoteData
{
    return QuoteData::fromArray([
        'owner' => $owner,
        'number' => $overrides['number'] ?? null,
        'title' => $overrides['title'] ?? 'Initial Quote',
        'notes' => $overrides['notes'] ?? null,
        'currency' => $overrides['currency'] ?? 'EUR',
        'discount_type' => $overrides['discount_type'] ?? 'fixed',
        'discount_value' => $overrides['discount_value'] ?? 0,
        'valid_until' => $overrides['valid_until'] ?? null,
        'items' => $items,
    ]);
}

it('creates quote in draft status with calculated totals and default valid until', function (): void {
    Event::fake([QuoteCreated::class]);

    Carbon::setTestNow('2026-05-01 10:00:00');
    config()->set('quotes.valid_until.default_days', 10);

    $owner = quoteOwner();

    $quote = app(CreateQuoteAction::class)->handle(
        quoteData($owner, [
            ['name' => 'Item A', 'quantity' => 2, 'unit_price' => 10, 'tax_rate' => 24],
            ['name' => 'Item B', 'quantity' => 1, 'unit_price' => 5, 'tax_rate' => 0],
        ])
    );

    expect($quote->status)->toBe(QuoteStatus::DRAFT)
        ->and($quote->items)->toHaveCount(2)
        ->and((float) $quote->subtotal)->toBe(25.0)
        ->and((float) $quote->tax_total)->toBe(4.8)
        ->and((float) $quote->discount_total)->toBe(0.0)
        ->and((float) $quote->total)->toBe(29.8)
        ->and($quote->valid_until?->toDateString())->toBe('2026-05-11');

    Event::assertDispatched(QuoteCreated::class);
});

it('resolves owner morph relation on quote model', function (): void {
    $owner = quoteOwner();

    $quote = app(CreateQuoteAction::class)->handle(
        quoteData($owner, [
            ['name' => 'Item A', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 24],
        ])
    );

    expect($quote->owner)->toBeInstanceOf(TestOwner::class)
        ->and($quote->owner?->is($owner))->toBeTrue();
});

it('creates quote with provided valid_until instead of default fallback', function (): void {
    Carbon::setTestNow('2026-05-01 10:00:00');

    $owner = quoteOwner();

    $quote = app(CreateQuoteAction::class)->handle(
        quoteData($owner, [
            ['name' => 'Item A', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 24],
        ], [
            'valid_until' => '2026-06-15',
        ])
    );

    expect($quote->valid_until?->toDateString())->toBe('2026-06-15');
});

it('updates only draft quotes and replaces items', function (): void {
    Event::fake([QuoteUpdated::class]);

    $owner = quoteOwner();

    $quote = app(CreateQuoteAction::class)->handle(
        quoteData($owner, [
            ['name' => 'Old Item', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 24],
        ], [
            'title' => 'Old Title',
        ])
    );

    $updated = app(UpdateQuoteAction::class)->handle(
        $quote,
        quoteData($owner, [
            ['name' => 'New Item', 'quantity' => 2, 'unit_price' => 30, 'tax_rate' => 24],
        ], [
            'title' => 'New Title',
            'valid_until' => '2026-07-01',
        ])
    );

    expect($updated->status)->toBe(QuoteStatus::DRAFT);
    expect($updated->title)->toBe('New Title');
    expect($updated->items)->toHaveCount(1);
    expect($updated->items->first()?->name)->toBe('New Item');
    expect($updated->valid_until?->toDateString())->toBe('2026-07-01');

    Event::assertDispatched(QuoteUpdated::class);
});

it('does not allow updating non-draft quotes', function (): void {
    $owner = quoteOwner();

    $quote = app(CreateQuoteAction::class)->handle(
        quoteData($owner, [
            ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
        ])
    );

    $quote->update(['status' => QuoteStatus::SENT]);

    expect(fn () => app(UpdateQuoteAction::class)->handle(
        $quote->fresh(),
        quoteData($owner, [
            ['name' => 'Item 2', 'quantity' => 1, 'unit_price' => 30, 'tax_rate' => 24],
        ])
    ))->toThrow(InvalidQuoteTransition::class, 'Only draft quotes can be updated.');
});

it('sends quote from draft to sent', function (): void {
    Event::fake([QuoteSent::class]);

    Carbon::setTestNow('2026-05-02 12:00:00');
    $quote = app(CreateQuoteAction::class)->handle(
        quoteData(quoteOwner(), [
            ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
        ])
    );

    $sent = app(SendQuoteAction::class)->handle($quote);

    expect($sent->status)->toBe(QuoteStatus::SENT)
        ->and($sent->sent_at?->toDateTimeString())->toBe('2026-05-02 12:00:00');

    Event::assertDispatched(QuoteSent::class);
});

it('does not allow sending non-draft quotes', function (): void {
    $quote = app(CreateQuoteAction::class)->handle(
        quoteData(quoteOwner(), [
            ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
        ])
    );

    $quote->update(['status' => QuoteStatus::ACCEPTED]);

    expect(fn () => app(SendQuoteAction::class)->handle($quote->fresh()))
        ->toThrow(InvalidQuoteTransition::class, 'Only draft quotes can be sent.');
});

it('does not allow sending an already sent quote', function (): void {
    $quote = app(SendQuoteAction::class)->handle(
        app(CreateQuoteAction::class)->handle(
            quoteData(quoteOwner(), [
                ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
            ])
        )
    );

    expect(fn () => app(SendQuoteAction::class)->handle($quote->fresh()))
        ->toThrow(InvalidQuoteTransition::class, 'This quote has already been sent.');
});

it('accepts sent quote', function (): void {
    Event::fake([QuoteAccepted::class]);

    Carbon::setTestNow('2026-05-03 13:00:00');
    $quote = app(SendQuoteAction::class)->handle(
        app(CreateQuoteAction::class)->handle(
            quoteData(quoteOwner(), [
                ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
            ])
        )
    );

    $accepted = app(AcceptQuoteAction::class)->handle($quote);

    expect($accepted->status)->toBe(QuoteStatus::ACCEPTED)
        ->and($accepted->accepted_at?->toDateTimeString())->toBe('2026-05-03 13:00:00');

    Event::assertDispatched(QuoteAccepted::class);
});

it('rejects sent quote', function (): void {
    Event::fake([QuoteRejected::class]);

    Carbon::setTestNow('2026-05-04 14:00:00');
    $quote = app(SendQuoteAction::class)->handle(
        app(CreateQuoteAction::class)->handle(
            quoteData(quoteOwner(), [
                ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
            ])
        )
    );

    $rejected = app(RejectQuoteAction::class)->handle($quote);

    expect($rejected->status)->toBe(QuoteStatus::REJECTED)
        ->and($rejected->rejected_at?->toDateTimeString())->toBe('2026-05-04 14:00:00');

    Event::assertDispatched(QuoteRejected::class);
});

it('does not allow accepting an already accepted quote', function (): void {
    $quote = app(AcceptQuoteAction::class)->handle(
        app(SendQuoteAction::class)->handle(
            app(CreateQuoteAction::class)->handle(
                quoteData(quoteOwner(), [
                    ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
                ])
            )
        )
    );

    expect(fn () => app(AcceptQuoteAction::class)->handle($quote->fresh()))
        ->toThrow(InvalidQuoteTransition::class, 'This quote has already been accepted.');
});

it('does not allow rejecting an already rejected quote', function (): void {
    $quote = app(RejectQuoteAction::class)->handle(
        app(SendQuoteAction::class)->handle(
            app(CreateQuoteAction::class)->handle(
                quoteData(quoteOwner(), [
                    ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
                ])
            )
        )
    );

    expect(fn () => app(RejectQuoteAction::class)->handle($quote->fresh()))
        ->toThrow(InvalidQuoteTransition::class, 'This quote has already been rejected.');
});

it('expires sent quote', function (): void {
    Event::fake([QuoteExpired::class]);

    $quote = app(SendQuoteAction::class)->handle(
        app(CreateQuoteAction::class)->handle(
            quoteData(quoteOwner(), [
                ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
            ])
        )
    );

    $expired = app(ExpireQuoteAction::class)->handle($quote);

    expect($expired->status)->toBe(QuoteStatus::EXPIRED);
    Event::assertDispatched(QuoteExpired::class);
});

it('does not allow expiring an already expired quote', function (): void {
    $quote = app(ExpireQuoteAction::class)->handle(
        app(SendQuoteAction::class)->handle(
            app(CreateQuoteAction::class)->handle(
                quoteData(quoteOwner(), [
                    ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
                ])
            )
        )
    );

    expect(fn () => app(ExpireQuoteAction::class)->handle($quote->fresh()))
        ->toThrow(InvalidQuoteTransition::class, 'This quote has already expired.');
});

it('does not allow accept reject expire from invalid status', function (): void {
    $quote = app(CreateQuoteAction::class)->handle(
        quoteData(quoteOwner(), [
            ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
        ])
    );

    expect(fn () => app(AcceptQuoteAction::class)->handle($quote))
        ->toThrow(InvalidQuoteTransition::class, 'Only sent quotes can be accepted.');

    expect(fn () => app(RejectQuoteAction::class)->handle($quote))
        ->toThrow(InvalidQuoteTransition::class, 'Only sent quotes can be rejected.');

    expect(fn () => app(ExpireQuoteAction::class)->handle($quote))
        ->toThrow(InvalidQuoteTransition::class, 'Only sent quotes can be expired.');
});

it('deletes draft and rejected quotes but blocks other statuses', function (): void {
    $draft = app(CreateQuoteAction::class)->handle(
        quoteData(quoteOwner(), [
            ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
        ])
    );

    app(DeleteQuoteAction::class)->handle($draft);
    expect(Quote::query()->find($draft->id))->toBeNull();

    $rejected = app(RejectQuoteAction::class)->handle(
        app(SendQuoteAction::class)->handle(
            app(CreateQuoteAction::class)->handle(
                quoteData(quoteOwner(), [
                    ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
                ])
            )
        )
    );

    app(DeleteQuoteAction::class)->handle($rejected);
    expect(Quote::query()->find($rejected->id))->toBeNull();

    $sent = app(SendQuoteAction::class)->handle(
        app(CreateQuoteAction::class)->handle(
            quoteData(quoteOwner(), [
                ['name' => 'Item', 'quantity' => 1, 'unit_price' => 20, 'tax_rate' => 24],
            ])
        )
    );

    expect(fn () => app(DeleteQuoteAction::class)->handle($sent))
        ->toThrow(InvalidQuoteTransition::class, 'Only draft or rejected quotes can be deleted.');
});
