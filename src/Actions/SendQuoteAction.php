<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteSent;
use Mimisk\LaravelQuotes\Exceptions\InvalidQuoteTransition;
use Mimisk\LaravelQuotes\Models\Quote;

final class SendQuoteAction
{
    public function handle(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {

            if ($quote->status === QuoteStatus::SENT) {
                throw InvalidQuoteTransition::alreadySent();
            }

            if ($quote->status !== QuoteStatus::DRAFT) {
                throw InvalidQuoteTransition::onlyDraftQuotesCanBeSent();
            }

            $quote->update([
                'status' => QuoteStatus::SENT,
                'sent_at' => now(),
            ]);

            event(new QuoteSent($quote));

            return $quote->refresh();
        });
    }
}

