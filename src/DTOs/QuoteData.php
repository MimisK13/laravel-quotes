<?php

namespace Mimisk\LaravelQuotes\DTOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mimisk\LaravelQuotes\Enums\DiscountType;

final readonly class QuoteData
{
    /**
     * @param Collection<int, QuoteItemData> $items
     */
    public function __construct(
        public Model        $owner,

        public ?string      $number,

        public ?string      $title,
        public ?string      $notes,

        public ?string      $currency,

        public DiscountType $discountType,
        public float        $discountValue,

        public ?string      $validUntil,

        public Collection   $items,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // Owner validation.
        $owner = $data['owner'] ?? null;

        if (! $owner instanceof Model) {
            throw new InvalidArgumentException('QuoteData owner must be an Eloquent model.');
        }

        // Normalize items.
        $rawItems = is_array($data['items'] ?? null)
            ? $data['items']
            : [];

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

            currency: $data['currency'] ?? config('quotes.currency'),

            discountType: isset($data['discount_type'])
                ? DiscountType::from($data['discount_type'])
                : DiscountType::from(config('quotes.discount.default_type')),

            discountValue: (float) ($data['discount_value'] ?? 0),

            validUntil: $data['valid_until'] ?? null,

            items: $items,
        );
    }
}

