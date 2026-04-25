<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteExpired;
use Mimisk\LaravelQuotes\Models\Quote;
use RuntimeException;

final class ExpireQuoteAction
{
    public function handle(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {
            if ($quote->status !== QuoteStatus::SENT) {
                throw new RuntimeException('Only sent quotes can be expired.');
            }

            $quote->update([
                'status' => QuoteStatus::EXPIRED,
            ]);

            event(new QuoteExpired($quote));

            return $quote->refresh();
        });
    }
}

