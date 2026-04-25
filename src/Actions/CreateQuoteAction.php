<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteCreated;
use Mimisk\LaravelQuotes\Models\Quote;
use Mimisk\LaravelQuotes\Support\CalculateQuoteTotals;
use Mimisk\LaravelQuotes\Support\GenerateQuoteNumber;

final class CreateQuoteAction
{
    public function handle(QuoteData $data): Quote
    {
        return DB::transaction(function () use ($data): Quote {
            $totals = app(CalculateQuoteTotals::class)->handle($data);

            $number = $data->number
                ?? app(GenerateQuoteNumber::class)->handle();

            $quote = Quote::query()->create([
                'owner_id' => $data->owner->getKey(),
                'owner_type' => $data->owner->getMorphClass(),

                'number' => $number,
                'status' => QuoteStatus::DRAFT,

                'title' => $data->title,
                'notes' => $data->notes,
                'currency' => $data->currency ?? config('laravel-quotes.currency'),

                ...$totals,
            ]);

            $quote->items()->createMany(
                $this->mapItems($data)
            );

            event(new QuoteCreated($quote));

            return $quote->load('items');
        });
    }

    private function mapItems(QuoteData $data): array
    {
        return $data->items
            ->map(function ($item, int $index): array {
                $subtotal = $item->subtotal();
                $taxTotal = $item->taxAmount();

                return [
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unitPrice,
                    'tax_rate' => $item->taxRate,
                    'subtotal' => $subtotal,
                    'tax_total' => $taxTotal,
                    'total' => $subtotal + $taxTotal,
                    'sort_order' => $index,
                ];
            })
            ->toArray();
    }
}

