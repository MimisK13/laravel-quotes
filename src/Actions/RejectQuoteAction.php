<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Events\QuoteRejected;
use Mimisk\LaravelQuotes\Models\Quote;
use RuntimeException;

final class RejectQuoteAction
{
    public function handle(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {
            if ($quote->status !== QuoteStatus::SENT) {
                throw new RuntimeException('Only sent quotes can be rejected.');
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

