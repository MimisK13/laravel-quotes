<?php

namespace Mimisk\LaravelQuotes\Events;

use Mimisk\LaravelQuotes\Models\Quote;

class QuoteAccepted
{
    public function __construct(
        public Quote $quote,
    ) {}
}
