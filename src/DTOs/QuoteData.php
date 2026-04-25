<?php

namespace Mimisk\LaravelQuotes\DTOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
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

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $owner = $data['owner'] ?? null;

        if (! $owner instanceof Model) {
            throw new InvalidArgumentException('QuoteData owner must be an Eloquent model.');
        }

        $rawItems = $data['items'] ?? [];

        if (! is_array($rawItems)) {
            $rawItems = [];
        }

        /** @var Collection<int, QuoteItemData> $items */
        $items = collect($rawItems)
            ->filter(static fn (mixed $item): bool => is_array($item))
            ->map(static fn (array $item): QuoteItemData => QuoteItemData::fromArray($item))
            ->values();

        return new self(
            owner: $owner,

            number: $data['number'] ?? null,

            title: $data['title'] ?? null,
            notes: $data['notes'] ?? null,

            currency: $data['currency'] ?? config('laravel-quotes.currency'),

            discountType: isset($data['discount_type'])
                ? DiscountType::from($data['discount_type'])
                : DiscountType::from(config('laravel-quotes.discount.default_type')),

            discountValue: (float) ($data['discount_value'] ?? 0),

            validUntil: $data['valid_until'] ?? null,

            items: $items,
        );
    }
}

