<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteRejected;
use Mimisk\LaravelQuotes\Exceptions\InvalidQuoteTransition;
use Mimisk\LaravelQuotes\Models\Quote;

final class RejectQuoteAction
{
    public function handle(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {

            if ($quote->status === QuoteStatus::REJECTED) {
                throw InvalidQuoteTransition::alreadyRejected();
            }

            if ($quote->status !== QuoteStatus::SENT) {
                throw InvalidQuoteTransition::onlySentQuotesCanBeRejected();
            }

            $quote->update([
                'status' => QuoteStatus::REJECTED,
                'rejected_at' => now(),
            ]);

            event(new QuoteRejected($quote));

            return $quote->refresh();
        });
    }
}

