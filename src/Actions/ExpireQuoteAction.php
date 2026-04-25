<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteExpired;
use Mimisk\LaravelQuotes\Exceptions\InvalidQuoteTransition;
use Mimisk\LaravelQuotes\Models\Quote;

final class ExpireQuoteAction
{
    public function handle(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {

            if ($quote->status === QuoteStatus::EXPIRED) {
                throw InvalidQuoteTransition::alreadyExpired();
            }

            if ($quote->status !== QuoteStatus::SENT) {
                throw InvalidQuoteTransition::onlySentQuotesCanBeExpired();
            }

            $quote->update([
                'status' => QuoteStatus::EXPIRED,
            ]);

            event(new QuoteExpired($quote));

            return $quote->refresh();
        });
    }
}

