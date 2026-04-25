<?php

namespace Mimisk\LaravelQuotes\DTOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mimisk\LaravelQuotes\Enums\DiscountType;

final class QuoteData
{
    /**
     * @param Collection<int, QuoteItemData> $items
     */
    public function __construct(
        public readonly Model $owner,

        public readonly ?string $number,

        public readonly ?string $title,
        public readonly ?string $notes,

        public readonly ?string $currency,

        public readonly DiscountType $discountType,
        public readonly float $discountValue,

        public readonly ?string $validUntil,

        public readonly Collection $items,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            owner: $data['owner'],

            number: $data['number'] ?? null,

            title: $data['title'] ?? null,
            notes: $data['notes'] ?? null,

            currency: $data['currency'] ?? config('laravel-quotes.currency'),

            discountType: isset($data['discount_type'])
                ? DiscountType::from($data['discount_type'])
                : DiscountType::from(config('laravel-quotes.discount.default_type')),

            discountValue: (float) ($data['discount_value'] ?? 0),

            validUntil: $data['valid_until'] ?? null,

            items: collect($data['items'] ?? [])
                ->map(fn ($item) => QuoteItemData::fromArray($item)),
        );
    }
}

