<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\DTOs\QuoteItemData;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteUpdated;
use Mimisk\LaravelQuotes\Models\Quote;
use Mimisk\LaravelQuotes\Support\CalculateQuoteTotals;
use RuntimeException;

final class UpdateQuoteAction
{
    public function handle(Quote $quote, QuoteData $data): Quote
    {
        return DB::transaction(function () use ($quote, $data): Quote {
            if ($quote->status !== QuoteStatus::DRAFT) {
                throw new RuntimeException('Only draft quotes can be updated.');
            }

            $totals = app(CalculateQuoteTotals::class)->handle($data);

            $quote->update([
                'title' => $data->title,
                'notes' => $data->notes,
                'currency' => $data->currency ?? config('laravel-quotes.currency'),
                'valid_until' => $data->validUntil,
                ...$totals,
            ]);

            $quote->items()->delete();
            $quote->items()->createMany($this->mapItems($data));

            event(new QuoteUpdated($quote));

            return $quote->load('items');
        });
    }

    /**
     * @return array<int, array{
     *   name: string,
     *   description: ?string,
     *   quantity: float,
     *   unit_price: float,
     *   tax_rate: ?float,
     *   subtotal: float,
     *   tax_total: float,
     *   total: float,
     *   sort_order: int
     * }>
     */
    private function mapItems(QuoteData $data): array
    {
        return $data->items
            ->map(function (QuoteItemData $item, int $index): array {
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
