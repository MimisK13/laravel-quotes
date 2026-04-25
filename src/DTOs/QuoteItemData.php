<?php

namespace Mimisk\LaravelQuotes\DTOs;

final readonly class QuoteItemData
{
    public function __construct(
        public string  $name,
        public ?string $description,
        public float   $quantity,
        public float   $unitPrice,
        public ?float  $taxRate,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            description: isset($data['description']) ? (string) $data['description'] : null,
            quantity: (float) $data['quantity'],
            unitPrice: (float) $data['unit_price'],
            taxRate: isset($data['tax_rate'])
                ? (float) $data['tax_rate']
                : (float) config('laravel-quotes.tax.default_rate'),
        );
    }

    public function subtotal(): float
    {
        return $this->quantity * $this->unitPrice;
    }

    public function taxAmount(): float
    {
        if (!$this->taxRate || $this->taxRate <= 0) {
            return 0;
        }

        return $this->subtotal() * ($this->taxRate / 100);
    }
}

