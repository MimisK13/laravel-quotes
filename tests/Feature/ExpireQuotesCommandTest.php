<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
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

function quoteOwnerForExpiry(): TestOwner
{
    return TestOwner::query()->create([
        'name' => 'Expiry Owner',
    ]);
}

function createQuoteForExpiry(TestOwner $owner, string $number, QuoteStatus $status, ?string $validUntil): Quote
{
    return Quote::query()->create([
        'owner_type' => $owner->getMorphClass(),
        'owner_id' => $owner->getKey(),
        'number' => $number,
        'status' => $status,
        'currency' => 'EUR',
        'valid_until' => $validUntil,
    ]);
}

it('expires only sent quotes that are past valid_until', function (): void {
    Carbon::setTestNow('2026-04-25 12:00:00');

    $owner = quoteOwnerForExpiry();

    $shouldExpire = createQuoteForExpiry($owner, 'Q-EXP-1', QuoteStatus::SENT, '2026-04-24 23:59:59');
    $futureSent = createQuoteForExpiry($owner, 'Q-EXP-2', QuoteStatus::SENT, '2026-04-26 00:00:00');
    $sentWithoutValidity = createQuoteForExpiry($owner, 'Q-EXP-3', QuoteStatus::SENT, null);
    $draftPastValidity = createQuoteForExpiry($owner, 'Q-EXP-4', QuoteStatus::DRAFT, '2026-04-20 00:00:00');

    $exitCode = Artisan::call('quotes:expire');

    expect($exitCode)->toBe(0)
        ->and(Artisan::output())->toContain('Expired quotes processed.');

    expect($shouldExpire->fresh()?->status)->toBe(QuoteStatus::EXPIRED)
        ->and($futureSent->fresh()?->status)->toBe(QuoteStatus::SENT)
        ->and($sentWithoutValidity->fresh()?->status)->toBe(QuoteStatus::SENT)
        ->and($draftPastValidity->fresh()?->status)->toBe(QuoteStatus::DRAFT);
});

it('can be wired in a scheduler definition', function (): void {
    $schedule = app(Schedule::class);

    $schedule->command('quotes:expire')->daily();

    $hasQuotesExpireEvent = collect($schedule->events())->contains(
        static fn (object $event): bool => str_contains((string) $event->command, 'quotes:expire')
    );

    expect($hasQuotesExpireEvent)->toBeTrue();
});
