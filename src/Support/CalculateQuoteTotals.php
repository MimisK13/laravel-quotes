<?php

namespace Mimisk\LaravelQuotes\Support;

use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\DTOs\QuoteItemData;
use Mimisk\LaravelQuotes\Enums\DiscountType;

final class CalculateQuoteTotals
{
    /**
     * @return array{
     *   subtotal: float,
     *   tax_total: float,
     *   discount_total: float,
     *   total: float
     * }
     */
    public function handle(QuoteData $data): array
    {
        $subtotal = (float) $data->items->sum(
            static fn (QuoteItemData $item): float => $item->subtotal()
        );
        $taxTotal = (float) $data->items->sum(
            static fn (QuoteItemData $item): float => $item->taxAmount()
        );

        $discountTotal = match ($data->discountType) {
            DiscountType::FIXED => min($data->discountValue, $subtotal),
            DiscountType::PERCENTAGE => ($subtotal * $data->discountValue) / 100,
        };

        return [
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'discount_total' => $discountTotal,
            'total' => $subtotal + $taxTotal - $discountTotal,
        ];
    }
}

