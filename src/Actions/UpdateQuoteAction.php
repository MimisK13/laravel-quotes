<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\Models\Quote;
use Mimisk\LaravelQuotes\Events\QuoteUpdated;
use Mimisk\LaravelQuotes\Support\CalculateQuoteTotals;

final class UpdateQuoteAction
{
    public function handle(Quote $quote, QuoteData $data): Quote
    {
        return DB::transaction(function () use ($quote, $data): Quote {

            // 👉 guard (important)
            if ($quote->status->value !== 'draft') {
                throw new \RuntimeException('Only draft quotes can be updated.');
            }

            $totals = app(CalculateQuoteTotals::class)->handle($data);

            // 👉 update quote
            $quote->update([
                'title' => $data->title,
                'notes' => $data->notes,
                'currency' => $data->currency ?? config('laravel-quotes.currency'),
                'valid_until' => $data->validUntil,

                ...$totals,
            ]);

            // 👉 replace items (simple + safe)
            $quote->items()->delete();

            $quote->items()->createMany(
                $this->mapItems($data)
            );

            event(new QuoteUpdated($quote));

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

