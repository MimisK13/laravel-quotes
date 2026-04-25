<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteAccepted;
use Mimisk\LaravelQuotes\Exceptions\InvalidQuoteTransition;
use Mimisk\LaravelQuotes\Models\Quote;

final class AcceptQuoteAction
{
    public function handle(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {

            if ($quote->status === QuoteStatus::ACCEPTED) {
                throw InvalidQuoteTransition::alreadyAccepted();
            }

            if ($quote->status !== QuoteStatus::SENT) {
                throw InvalidQuoteTransition::onlySentQuotesCanBeAccepted();
            }

            $quote->update([
                'status' => QuoteStatus::ACCEPTED,
                'accepted_at' => now(),
            ]);

            event(new QuoteAccepted($quote));

            return $quote->refresh();
        });
    }
}

