<?php

namespace Mimisk\LaravelQuotes\Events;

use Mimisk\LaravelQuotes\Models\Quote;

final readonly class QuoteExpired
{
    public function __construct(
        public Quote $quote,
    ) {}
}
