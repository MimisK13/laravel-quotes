<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteSent;
use Mimisk\LaravelQuotes\Models\Quote;
use RuntimeException;

final class SendQuoteAction
{
    public function handle(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {
            if ($quote->status !== QuoteStatus::DRAFT) {
                throw new RuntimeException('Only draft quotes can be sent.');
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

