<?php

namespace Mimisk\LaravelQuotes\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mimisk\LaravelQuotes\Models\Quote;

class QuoteCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Quote $quote) {}
}

