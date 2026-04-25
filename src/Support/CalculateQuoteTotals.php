<?php

namespace Mimisk\LaravelQuotes\Support;

use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\Enums\DiscountType;

final class CalculateQuoteTotals
{
    public function handle(QuoteData $data): array
    {
        $subtotal = $data->items->sum(fn ($i) => $i->subtotal());
        $taxTotal = $data->items->sum(fn ($i) => $i->taxAmount());

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

